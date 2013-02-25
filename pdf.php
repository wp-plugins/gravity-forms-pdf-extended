<?php

/*
Plugin Name: Gravity Forms PDF Extended
Plugin URI: http://www.gravityformspdfextended.com
Description: Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. Our Business Plus package also allows you to overlay field onto an existing PDF.
Version: 2.1.0
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
 * Define our constants 
 */
 if(!defined('PDF_EXTENDED_VERSION')) { define('PDF_EXTENDED_VERSION', '2.1.0'); }
 if(!defined('PDF_PLUGIN_DIR')) { define('PDF_PLUGIN_DIR', ABSPATH. 'wp-content/plugins/gravity-forms-pdf-extended/'); }
 if(!defined('PDF_PLUGIN_URL')) { define('PDF_PLUGIN_URL', site_url(). '/wp-content/plugins/gravity-forms-pdf-extended/'); } 
 if(!defined('GF_PDF_SUPPORTED_VERSION')) { define('GF_PDF_SUPPORTED_VERSION', '1.6.0'); }
 if(!defined('PDF_SETTINGS_URL')) { define("PDF_SETTINGS_URL", site_url() .'/wp-admin/admin.php?page=gf_settings&addon=PDF'); }
 if(!defined('PDF_SAVE_FOLDER')) { define('PDF_SAVE_FOLDER', 'PDF_EXTENDED_TEMPLATES'); }
 if(!defined('PDF_SAVE_LOCATION')) { define('PDF_SAVE_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/output/'); }
 if(!defined('PDF_TEMPLATE_LOCATION')) { define('PDF_TEMPLATE_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/'); }
 if(!defined('PDF_TEMPLATE_URL_LOCATION')) { define('PDF_TEMPLATE_URL_LOCATION', get_stylesheet_directory_uri().'/'. PDF_SAVE_FOLDER .'/'); } 

/* 
 * Include the core files
 */
 include PDF_PLUGIN_DIR . 'installation-update-manager.php';
 include PDF_PLUGIN_DIR . 'pdf-settings.php';

/*
 * Add our hooks
 */
add_action('admin_init',  array('GFPDF_Core', 'gfe_admin_init'), 9);
add_action("gform_entry_created", array('GFPDF_Core', 'gform_pdf_example_create'), 15, 2);
add_filter("gform_admin_notification_attachments", array('GFPDF_Core', 'gform_add_example_attachment'), 10, 3);
add_filter("gform_user_notification_attachments", array('GFPDF_Core', 'gform_add_example_attachment'), 10, 3);

add_action('after_switch_theme', array('GFPDF_InstallUpdater', 'gf_pdf_on_switch_theme'), 10, 2);
add_action('gform_entries_first_column_actions', array('GFPDF_Core', 'pdf_link'), 10, 4);
add_action("gform_entry_info", array('GFPDF_Core', 'detail_pdf_link'), 10, 2);
add_action('wp', array('GFPDF_Core', 'process_exterior_pages'));
register_activation_hook( __FILE__, array('GFPDF_InstallUpdater', 'install') );

class GFPDF_Core
{
	
	/*
	 * Generate our Sample PDF for our Sample Form   
	 */
	function gform_pdf_example_create($entry, $form)
	{
		$user_id = $entry['id'];
		$form_id = $entry['form_id'];
			
		if($form['title'] == 'Gravity Forms PDF Extended Custom Template Sample')
		{
			/* include the pdf processing file */
			require PDF_PLUGIN_DIR.'render_to_pdf.php';
			/* generate and save the PDF using a custom name*/
			$filename = PDF_Generator($form_id, $user_id, 'save', true, 'example-template.php');	
		}
	}
	
	/* Emails our sample PDF to the site administrator */
	function gform_add_example_attachment($attachments, $lead, $form) {
	 
		$form_id = $lead['form_id'];
		$user_id = $lead['id'];
		$attachments = (is_array($attachments)) ? $attachments : array();
		$folder_id = $form_id.$user_id.'/';
	 
		if($form['title'] == 'Gravity Forms PDF Extended Custom Template Sample')
		{
			/* include PDF converter plugin */
			include PDF_PLUGIN_DIR.'render_to_pdf.php';
			$attachment_file = PDF_SAVE_LOCATION. $folder_id . get_pdf_filename($form_id, $user_id);
			$attachments[] = $attachment_file;
			return $attachments;
		}  
	}
	
	/**
	 * Check to see if Gravity Forms is actually installed
	 */
	function gfe_admin_init()
	{	
		/*
		 * Check if Gravity Forms is installed 
		 */
		if(!class_exists("RGForms"))
		{ 
			/* throw error to the admin notice bar */
			add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_not_installed")); 
			return;
		}
		
		/*
		 * Check if current version of Gravity Forms is supported
		 */	
		if(!GFPDF_InstallUpdater::gform_pdf_is_gravityforms_supported())
		{
			add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_not_supported")); 	
			return;
		}
		
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
		}
		
		/**
		 * Check if deployed new template files after update
		 */ 
		 if(get_option('gf_pdf_extended_deploy') == 'no') {
			/*show warning message */
			add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_not_deployed")); 	
		 }		
		
		
		/* 
		 * Configure the settings page
		 */
		wp_enqueue_style( 'pdfextended-admin-styles', PDF_PLUGIN_URL . 'styles/admin-styles.css' );		 
		 
    	 GFPDF_Settings::settings_page();		
	}
	
	
	//Link for Entry Detail View (Provide both View Link and Download)
	function detail_pdf_link($form_id, $lead) {
	  $lead_id = $lead['id'];
	  echo "PDF:  ";
	  echo "<a href=\"javascript:;\" onclick=\"var notes_qs = jQuery('#gform_print_notes').is(':checked') ? '&notes=1' : ''; var url='".home_url()."/?gf_pdf=print-entry&fid=".$form_id."&lid=".$lead_id."' + notes_qs; window.open (url,'printwindow');\" class=\"button\"> View</a>";
	  echo " <a href=\"javascript:;\" onclick=\"var notes_qs = jQuery('#gform_print_notes').is(':checked') ? '&notes=1' : ''; var url='".home_url()."/?gf_pdf=print-entry&download=1&fid=".$form_id."&lid=".$lead_id."' + notes_qs; window.open (url,'printwindow');\" class=\"button\"> Download</a>";
	}
	
	// Made this first... figured i would leave it in.  View link on the Entry list view. 
	function pdf_link($form_id, $field_id, $value, $lead) {
	  $lead_id = $lead['id'];
	  echo "| <a href=\"javascript:;\" onclick=\"var notes_qs = '&notes=1'; var url='".home_url()."/?gf_pdf=print-entry&fid=".$form_id."&lid=".$lead_id."' + notes_qs; window.open (url,'printwindow');\"> View PDF</a>";
	}
	
	//Handle Incoming route.   Look for GF_PDF namespace 
	function process_exterior_pages() {
	  global $wpdb;
	  
	  if(rgempty("gf_pdf", $_GET))
		return;
		
		$form_id = (int) $_GET['fid'];
		$lead_id = (int) $_GET['lid'];
		$ip = self::getRealIpAddr();
		$template = (rgempty('template', $_GET)) ? 'default-template.php' : rgget('template');
		
		/* check the lead is in the database and the IP address matches (little security booster) */
		$form_entries = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM `".$wpdb->prefix."rg_lead` WHERE form_id = ".$form_id." AND status = 'active' AND id = ".$lead_id." AND ip = '".$ip."'", array() ) )	;	
	
	  //ensure users are logged in
	  if( !is_user_logged_in() && $form_entries == 0) {
		auth_redirect();
	  }
	  
	  // ensure logged in users have the correct privliages
	  if(!GFCommon::current_user_can_any("gravityforms_view_entries") && $form_entries == 0)
	  {
		 break;  
	  }
	
	  switch(rgget("gf_pdf")){
		case "print-entry" :
		/* include the pdf processing file */
		require PDF_PLUGIN_DIR.'render_to_pdf.php';
		
		/* call the creation class */
		$output = ($_GET['download']) ? 'download' : 'view';
		PDF_Generator((int) $_GET['fid'], (int) $_GET['lid'], $output, false, $template);
		break;
	  }
	  exit();
	}
	
	public function getRealIpAddr()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}	
}

?>