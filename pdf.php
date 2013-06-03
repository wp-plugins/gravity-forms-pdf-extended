<?php

/*
Plugin Name: Gravity Forms PDF Extended
Plugin URI: http://www.gravityformspdfextended.com
Description: Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. Our Business Plus package also allows you to overlay field onto an existing PDF.
Version: 3.0.1
Author: Blue Liquid Designs
Author URI: http://www.blueliquiddesigns.com.au

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
*/

/*
 * As PDFs can't be generated if notices are displaying turn off error reporting to the screen.
 * Production servers should already have this done.
 */
 //error_reporting(0);
 
/*
 * Define our constants 
 */
 if(!defined('PDF_EXTENDED_VERSION')) { define('PDF_EXTENDED_VERSION', '3.0.1'); }
 if(!defined('PDF_PLUGIN_DIR')) { define('PDF_PLUGIN_DIR', plugin_dir_path( __FILE__ )); } 
 if(!defined('PDF_PLUGIN_URL')) { define('PDF_PLUGIN_URL', plugin_dir_url( __FILE__ )); } 
 if(!defined('GF_PDF_SUPPORTED_VERSION')) { define('GF_PDF_SUPPORTED_VERSION', '1.6.0'); }
 if(!defined('PDF_SETTINGS_URL')) { define("PDF_SETTINGS_URL", site_url() .'/wp-admin/admin.php?page=gf_settings&addon=PDF'); }
 if(!defined('PDF_SAVE_FOLDER')) { define('PDF_SAVE_FOLDER', 'PDF_EXTENDED_TEMPLATES'); }
 if(!defined('PDF_SAVE_LOCATION')) { define('PDF_SAVE_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/output/'); }
 if(!defined('PDF_FONT_LOCATION')) { define('PDF_FONT_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/fonts/'); }
 if(!defined('PDF_TEMPLATE_LOCATION')) { define('PDF_TEMPLATE_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/'); }
 if(!defined('PDF_TEMPLATE_URL_LOCATION')) { define('PDF_TEMPLATE_URL_LOCATION', get_stylesheet_directory_uri().'/'. PDF_SAVE_FOLDER .'/'); } 
 if(!defined('GF_PDF_EXTENDED_SUPPORTED_VERSION')) { define('GF_PDF_EXTENDED_SUPPORTED_VERSION', '1.6'); } 
 if(!defined('GF_PDF_EXTENDED_PLUGIN_BASENAME')) { define('GF_PDF_EXTENDED_PLUGIN_BASENAME', plugin_basename(__FILE__)); } 

 /*
  * Do we need to deploy template files this edition? If yes set to true. 
  */
  if(!defined('PDF_DEPLOY')) { define('PDF_DEPLOY', true); } 

/* 
 * Include the core files
 */
 include PDF_PLUGIN_DIR . 'pdf-common.php';  
 include PDF_PLUGIN_DIR . 'pdf-configuration-indexer.php';
 include PDF_PLUGIN_DIR . 'installation-update-manager.php'; 
 include PDF_PLUGIN_DIR . 'pdf-render.php';
 include PDF_PLUGIN_DIR . 'pdf-settings.php';
 include PDF_PLUGIN_DIR . 'pdf-entry-detail.php';

 /* 
  * Initiate the class after Gravity Forms has been loaded using the init hook.
  * This technique requires PHP 5.3 or higher
  */
   add_action('init', function() {
		 /*
		  * Check if Gravity Forms is installed before we continue
		  * Include common functions for test
		  */
		  if(PDF_Common::is_gravityforms_supported(GF_PDF_EXTENDED_SUPPORTED_VERSION) === false)
		  {
			 add_action('after_plugin_row_' . GF_PDF_EXTENDED_PLUGIN_BASENAME, function() {
				 PDF_Common::display_compatibility_error();	 
			 }); 
			 return;  
		  }
		  else
		  {
			 add_action('after_plugin_row_' . GF_PDF_EXTENDED_PLUGIN_BASENAME, function() {
				 PDF_Common::display_documentation_details();	 
			 }); 	  
		  }	
		  	   
	   
	   /* 
	    * As it's called inside a undefined function we need to globalise the $gfpdf namespace
		*/
	    global $gfpdf;
		$gfpdf = new GFPDF_Core();  		 	
		
		if(PDFGenerator::$gf_compatibility != 'post 1.7')
		{
				/*
				 * Look up notifications from INDEX and determine whether to fire or not
				 */		  
				add_action("gform_pre_submission", function($form) {
					global $gfpdf;
	
					$form_id = $form['id'];
					
					/*
					 * Get indexes which include this form
					 * Only need the first value if it exists
					 */
					$config = $gfpdf->get_config($form_id);
					
					if(sizeof($config) > 0)
					{
						/*
						 * Get user configured notifications and call appropriate hook
						 */	
						 $notifications = $gfpdf->get_form_notifications($form, $config[0]);

						 if(in_array('Admin Notification', $notifications))
						 {									 
							add_filter("gform_admin_notification_attachments", array('GFPDF_Core', 'gfpdfe_create_and_attach_pdf'), 1, 3);				
						 }
						 
						 if(in_array('User Notification', $notifications))
						 {						
							add_filter("gform_user_notification_attachments", array('GFPDF_Core', 'gfpdfe_create_and_attach_pdf'), 1, 3);		 							 
						 } 
					}					
					return $form;
				}, 1, 1);
		}
   });  

class GFPDF_Core extends PDFGenerator
{
	private $render;
	
	public function __construct()
	{
		global $gfpdf;
		/*
		 * Set up the PDF configuration and indexer
		 * Accessed through $this->configuration and $this->index.
		 */
		parent::__construct();
				
		/*
		 * Add our main hooks
		 */				
		add_action('admin_init',  array($this, 'gfe_admin_init'), 9);		
		add_action('gform_entries_first_column_actions', array($this, 'pdf_link'), 10, 4);
		add_action("gform_entry_info", array($this, 'detail_pdf_link'), 10, 2);
		add_action('wp', array($this, 'process_exterior_pages'));
		
		add_action('after_switch_theme', array('GFPDF_InstallUpdater', 'gf_pdf_on_switch_theme'), 10, 2);		
		register_activation_hook( __FILE__, array('GFPDF_InstallUpdater', 'install') );					
		
		/*
		 * Register render class
		 */		
		 $this->render = new PDFRender();
		 
		 /*
		  * Run PDF generate / email code based on version
		  * $gf_compatibility located in pdf-configuration-indexer.php
		  * Values are either pre 1.7 or post 1.7
		  */
		  if(PDFGenerator::$gf_compatibility == 'post 1.7')
		  {
				add_filter('gform_notification', array($this, 'gfpdfe_create_and_attach_pdf'), 10, 3);  
		  }	 	  
		 
		 /* TODO - ENABLE AND DELETE MPDF PACKAGE
		  * Check if mPDF package is zipped up.
		  * If so, unzip and delete
		  * Helps reduce the package file size
		  */	
		  /*PDF_Common::unpack_mPDF(); */
		 
	}
	
	/**
	 * Check to see if Gravity Forms is actually installed
	 */
	function gfe_admin_init()
	{			
		
		/*
		 * Check if GF PDF Extended is correctly installed. If not we'll run the installer.
		 */	
		if( (get_option('gf_pdf_extended_installed') != 'installed') || (get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION) || (!is_dir(PDF_TEMPLATE_LOCATION)) )
		{		
			GFPDF_InstallUpdater::pdf_extended_activate();
		}
		
		/* 
		 * Check if database plugin version matches current plugin version and updates if needed
		 */
		if(get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION)
		{
			update_option('gf_pdf_extended_version', PDF_EXTENDED_VERSION);
			/* redirect */
			Header('Location: '.PDF_SETTINGS_URL);
		}
		
		/**
		 * Check if deployed new template files after update
		 */ 
		 if(get_option('gf_pdf_extended_deploy') == 'no' && !rgpost('upgrade') && PDF_DEPLOY === true) {
			/*show warning message */
			add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_not_deployed")); 	
		 }		
		 
		/*
		 * Check if user is now running GF1.7
		 */	
		if(GFCommon::$version == '1.7')
		{
			add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_now_1_7")); 		
		}
				 				
		/* 
		 * Configure the settings page
		 */
		wp_enqueue_style( 'pdfextended-admin-styles', PDF_PLUGIN_URL . 'styles/admin-styles.css' );		 
		 
    	 GFPDF_Settings::settings_page();	
		  
	}
	
	
	function detail_pdf_link($form_id, $lead) {  
		/*
		 * Get the template name
		 * Class: PDFGenerator
		 * File: pdf-configuration-indexer.php
		 */
		$template = $this->get_template($form_id);
		
		if($template !== false)
		{
			$lead_id = $lead['id'];
			
			?>
			PDF: <a href="javascript:;" onclick="var url='<?php echo home_url(); ?>/?gf_pdf=print-entry&fid=<?php echo $form_id; ?>&lid=<?php echo $lead_id; ?>&template=<?php echo $template; ?>'; window.open (url,'printwindow');" class="button">View</a> 
				 <a href="javascript:;" onclick="var url='<?php echo home_url(); ?>/?gf_pdf=print-entry&download=1&fid=<?php echo $form_id; ?>&lid=<?php echo $lead_id; ?>&template=<?php echo $template; ?>'; window.open (url,'printwindow');" class="button">Download</a>
			<?php
		}
	}
	
	function pdf_link($form_id, $field_id, $value, $lead) {
		/*
		 * Get the template name
		 * Class: PDFGenerator
		 * File: pdf-configuration-indexer.php
		 */
		$template = $this->get_template($form_id);
		
		if($template !== false)
		{
			$lead_id = $lead['id'];
			
			?>
			| <a href="javascript:;" onclick="var url='<?php echo home_url(); ?>/?gf_pdf=print-entry&fid=<?php echo $form_id; ?>&lid=<?php echo $lead_id; ?>&template=<?php echo $template; ?>'; window.open (url,'printwindow');"> View PDF</a> 
			<?php
		}
	}
	
	/*
	 * Handle incoming routes
	 * Look for $_GET['gf_pdf'] variable, authenticate user and generate/display PDF
	 */ 
	function process_exterior_pages() {	 	 
	  global $wpdb;
	  	
	  /*
	   * If $_GET variable isn't set then stop function
	   */ 	 	  
	  if(rgempty("gf_pdf", $_GET))
	  {
		return;
	  }
		
		$form_id = (int) $_GET['fid'];
		$lead_id = (int) $_GET['lid'];		
		$ip = PDF_Common::getRealIpAddr();

		/*
		 * Get the template name
		 * Class: PDFGenerator
		 * File: pdf-configuration-indexer.php
		 */
		$template = $this->get_template($form_id); 
		$all_indexes = $this->get_config($form_id);
		$index = $all_indexes[0];
		
		/*
		 * Run if user is not logged in
		 */ 
		 if(!is_user_logged_in())
		 {
			/* 
			 * Check the lead is in the database and the IP address matches (little security booster) 
			 */
			$form_entries = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM `".$wpdb->prefix."rg_lead` WHERE form_id = ".$form_id." AND status = 'active' AND id = ".$lead_id." AND ip = '".$ip."'", array() ) );	
			
			if($form_entries == 0 && $this->configuration[$index]['access'] !== 'all')
			{
				auth_redirect();		
			}
			
		 }
		 else
		 {
			  /*
			   * Ensure logged in users have the correct privilages 
			   */
			  if(!GFCommon::current_user_can_any("gravityforms_view_entries"))
			  {
				  /*
				   * User doesn't have the correct access privilages so don't generate PDF
				   */
				 break;  
			  }		
			  
			  /*
			   * Because this user is logged in with the correct access 
			   * we will allow a template to be shown by setting the template variable
			   */	 
			   
			   if( ($template != $_GET['template']) && (substr($_GET['template'], -4) == '.php') )
			   {			
					$template = $_GET['template'];
			   }
			   
		 }		
		 
  
	  switch(rgget("gf_pdf")){
		case "print-entry" :

		$pdf_arguments = $this->generate_pdf_parameters($index, $form_id, $lead_id, $template);
		
		/*
		 * Add output to arguments 
		 */
		$output = 'view';
		if(isset($_GET['download']))
		{
			$output = 'download';	
		}	
		
		$pdf_arguments['output'] = $output;					

		$this->render->PDF_Generator($form_id, $lead_id, $pdf_arguments);
		break;
	  }
	  exit();
	}
	
	public static function gfpdfe_create_and_attach_pdf($notification, $form, $entry)
	{											
		/*
		 * Allow the template/function access to these variables
		 */
		global $gfpdf, $form_id, $lead_id;
		
		if(self::$gf_compatibility != 'post 1.7')
		{
			/*
			 * Prior to 1.7 the notifications hook was $attachments, $lead, $form 
			 * Swap $form and $entry values around to match 1.7
			 */	
			 $temp_form = $form;
			 $temp_entry = $entry;
			 $form = $temp_entry;
			 $entry = $temp_form;
			 
			 $temp_form = $temp_entry = false;			 
		}
		else
		{
			$notification_name = (isset($notification['name'])) ? $notification['name'] : '';	
		}
		
		/*
		 * Set data used to determine if PDF needs to be created and attached to notification
		 * Don't change anything here.
		 */		
		$form_title        = $form['title'];
		$form_id           = $entry['form_id'];
		$lead_id           = $entry['id'];
		$folder_id 		   = $form_id.$lead_id.'/';		

		/*
		 * Depreciated - Backwards Compatibility
		 * Set Constants
		 */
		 if(!defined('GF_FORM_ID'))
		 {
			/* TODO */
			trigger_error('Gravity Forms PDF Extended constants GF_FORM_ID and GF_LEAD_ID depreciated in v3.0.0. Custom template files should be updated with the new code. See http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/ for upgrade instructions.');
			define('GF_FORM_ID', $form_id);
			define('GF_LEAD_ID', $lead_id);				 
		 }
		
		/*
		 * Check if form is in configuration
		 */	 			
		 if(!$config = $gfpdf->get_config($form_id))
		 {
			 return $notification;
		 }				  		

		/* 
		 * To have our configuration indexes so loop through the PDF template configuration
		 * and generate and attach PDF files.
		 */		
		 foreach($config as $index)
		 {
				$template = (isset($gfpdf->configuration[$index]['template'])) ? $gfpdf->configuration[$index]['template'] : '';
				
				$pdf_arguments = $gfpdf->generate_pdf_parameters($index, $form_id, $lead_id, $template);

				/* generate and save default PDF */
				$filename = $gfpdf->render->PDF_Generator($form_id, $lead_id, $pdf_arguments);
	
				/* Get notifications user wants PDF attached to and check if the correct notifications hook is running */				
				$notifications = $gfpdf->get_form_notifications($form, $index);
				
				/* Set attachment name */
				$attachment_file               = $filename;
						
				/*
				 * Version Control
				 * Do additional checks on 1.7 as it passed the notification name to the hook
				 * Checks done for prior versions done before assigning hook
				 */
				if(PDFGenerator::$gf_compatibility == 'post 1.7')
				{								
					if ($gfpdf->check_notification($notification_name, $notifications)) 
					{											
						$notification['attachments'][] = $attachment_file;
					}
				}
				else
				{
					/*
					 * Did notification validation prior to running hook as pre 1.7 only
					 * passed admin and user notifications and doesn't tell you which one is running
					 */
					$notification[] = $attachment_file;	
				}
		 }
    return $notification;
	}
	
	/*
	 * Check if name in notification_name String/Array matches value in $notifcations array	 
	 */
	public function check_notification($notification_name, $notifications)
	{		
		if(is_array($notification_name))
		{
			foreach($notification_name as $name)
			{
				if(in_array($name, $notifications))
				{
					return true;	
				}					
			}
		}
		else
		{
			if(in_array($notification_name, $notifications))
			{
				return true;	
			}
		}
		
		return false;
	}
	
    public static function get_notifications_name($action, $form){
        if(rgempty("notifications", $form))
            return array();

        $notifications = array();
        foreach($form["notifications"] as $notification){
            if(rgar($notification, "event") == $action)
                $notifications[] = $notification['name'];
        }

        return $notifications;
    }	
	
	public static function get_form_notifications($form, $index)
	{
		global $gfpdf;
		
		/*
		 * Check if notification field even exists
		 */
		 if(!isset($gfpdf->configuration[$index]['notifications']))
		 {
			return array(); 
		 }
		
		/*
		 * Get all form_submission notifications and 
		 */  
		if(self::$gf_compatibility != 'post 1.7')
		{		 			 
			 $notifications = self::$pre_1_7_notifications;			 			
		}
		else
		{				 
			$notifications = self::get_notifications_name('form_submission', $form);			
		}
		$new_notifications = array();				

		/*
		 * If notifications is true the user wants to attach the PDF to all notifications
		 */ 
		if($gfpdf->configuration[$index]['notifications'] === true)
		{					
			$new_notifications = $notifications;
		}
		/*
		 * Only a single notification is selected
		 */ 		
		else if(!is_array($gfpdf->configuration[$index]['notifications']))
		{
			/*
			 * Ensure that notification is valid
			 */
			 if(in_array($gfpdf->configuration[$index]['notifications'], $notifications))
			 {
					$new_notifications = array($gfpdf->configuration[$index]['notifications']); 
			 }
		}
		else
		{
			foreach($gfpdf->configuration[$index]['notifications'] as $name)
			{
				if(in_array($name, $notifications))
				{
					$new_notifications[] = $name;	
				}
			}
		}
		
		return $new_notifications;
	}
	
	/*
	 * Generate PDF parameters to pass to the PDF renderer
	 * $index Integer The configuration index number
	 */
	private function generate_pdf_parameters($index, $form_id, $lead_id, $template = '')
	{

				$pdf_name = (isset($this->configuration[$index]['filename']) && strlen($this->configuration[$index]['filename']) > 0) ? $this->get_pdf_name($index, $form_id, $lead_id) : PDF_Common::get_pdf_filename($form_id, $lead_id);
				$template = (isset($template) && strlen($template) > 0) ? $template : $this->get_template($index);	 
				
				$pdf_size = (isset($this->configuration[$index]['pdf_size']) && (is_array($this->configuration[$index]['pdf_size']) || strlen($this->configuration[$index]['pdf_size']) > 0)) ? $this->configuration[$index]['pdf_size'] : self::$default['pdf_size'];
				$orientation = (isset($this->configuration[$index]['orientation']) && strlen($this->configuration[$index]['orientation']) > 0) ? $this->configuration[$index]['orientation'] : self::$default['orientation'];
				$security = (isset($this->configuration[$index]['security']) && $this->configuration[$index]['security']) ? $this->configuration[$index]['security'] : self::$default['security'];			

				/*
				 * Validate privileges 
				 * If blank and security is true then set privileges to all
				 */ 
				$privileges = (isset($this->configuration[$index]['pdf_privileges'])) ? $this->validate_privileges($this->configuration[$index]['pdf_privileges']) : $this->validate_privileges('');	
				
				$pdf_password = (isset($this->configuration[$index]['pdf_password'])) ? $this->configuration[$index]['pdf_password'] : '';
				$master_password = (isset($this->configuration[$index]['pdf_master_password'])) ? $this->configuration[$index]['pdf_master_password'] : '';
				$rtl = (isset($this->configuration[$index]['rtl'])) ? $this->configuration[$index]['rtl'] : false;		

				$pdf_arguments = array(
					'pdfname' => $pdf_name,
					'template' => $template,
					
					'pdf_size' => $pdf_size, /* set to one of the following, or array - in millimeters */
					'orientation' => $orientation, /* landscape or portrait */
					
					'security' => $security, /* true or false. if true the security settings below will be applied. Default false. */
					'pdf_password' => $pdf_password, /* set a password to view the PDF */
					'pdf_privileges' => $privileges, /* assign user privliages to the PDF */
					'pdf_master_password' => $master_password, /* set a master password to the PDF can't be modified without it */	
					'rtl' => $rtl		 
				);	
				
				return $pdf_arguments;	
	}	
	
}
