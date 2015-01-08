<?php

/*
Plugin Name: Gravity Forms PDF Extended
Plugin URI: http://www.gravityformspdfextended.com
Description: Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. Our Business Plus package also allows you to overlay field onto an existing PDF.
Version: 3.4.0
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
 * As PDFs can't be generated if notices are displaying, turn off error reporting to the screen if not in debug mode.
 * Production servers should already have this done.
 */
 if(WP_DEBUG !== true)
 {
 	error_reporting(0);
 }
 
/*
 * Define our constants 
 */
 if(!defined('PDF_EXTENDED_VERSION')) { define('PDF_EXTENDED_VERSION', '3.4.0'); }
 if(!defined('GF_PDF_EXTENDED_SUPPORTED_VERSION')) { define('GF_PDF_EXTENDED_SUPPORTED_VERSION', '1.7'); } 
 if(!defined('GF_PDF_EXTENDED_WP_SUPPORTED_VERSION')) { define('GF_PDF_EXTENDED_WP_SUPPORTED_VERSION', '3.5'); } 
 if(!defined('GF_PDF_EXTENDED_PHP_SUPPORTED_VERSION')) { define('GF_PDF_EXTENDED_PHP_SUPPORTED_VERSION', '5'); }
  
 if(!defined('PDF_PLUGIN_DIR')) { define('PDF_PLUGIN_DIR', plugin_dir_path( __FILE__ )); } 
 if(!defined('PDF_PLUGIN_URL')) { define('PDF_PLUGIN_URL', plugin_dir_url( __FILE__ )); } 
 if(!defined('PDF_SETTINGS_URL')) { define("PDF_SETTINGS_URL", site_url() .'/wp-admin/admin.php?page=gf_settings&addon=PDF'); }
 if(!defined('PDF_SAVE_FOLDER')) { define('PDF_SAVE_FOLDER', 'PDF_EXTENDED_TEMPLATES'); }
 if(!defined('PDF_SAVE_LOCATION')) { define('PDF_SAVE_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/output/'); }
 if(!defined('PDF_FONT_LOCATION')) { define('PDF_FONT_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/fonts/'); }
 if(!defined('PDF_TEMPLATE_LOCATION')) { define('PDF_TEMPLATE_LOCATION', get_stylesheet_directory().'/'.PDF_SAVE_FOLDER.'/'); }
 if(!defined('PDF_TEMPLATE_URL_LOCATION')) { define('PDF_TEMPLATE_URL_LOCATION', get_stylesheet_directory_uri().'/'. PDF_SAVE_FOLDER .'/'); }  
 if(!defined('GF_PDF_EXTENDED_PLUGIN_BASENAME')) { define('GF_PDF_EXTENDED_PLUGIN_BASENAME', plugin_basename(__FILE__)); } 

 /*
  * Do we need to deploy template files this edition? If yes set to true. 
  */
  if(!defined('PDF_DEPLOY')) { define('PDF_DEPLOY', false); }

/* 
 * Include the core helper files
 */
 include PDF_PLUGIN_DIR . 'helper/api.php';
 include PDF_PLUGIN_DIR . 'helper/data.php'; 
 include PDF_PLUGIN_DIR . 'helper/pdf-configuration-indexer.php'; 	
 include PDF_PLUGIN_DIR . 'helper/installation-update-manager.php'; 				
 
 /*
  * Initialise our data helper class
  */
 global $gfpdfe_data;
 $gfpdfe_data = new GFPDFE_DATA();    
 
 include PDF_PLUGIN_DIR . 'pdf-settings.php';
 include PDF_PLUGIN_DIR . 'helper/pdf-common.php';

 /* 
  * Initiate the class after Gravity Forms has been loaded using the init hook.
  */
   add_action('init', array('GFPDF_Core', 'pdf_init'));
   add_action('wp_ajax_support_request', array('GFPDF_Settings_Model', 'gfpdf_support_request'));
   

class GFPDF_Core extends PDFGenerator
{
	public $render;
	static $model;
		
	/*
	 * Main Controller 
	 * First function fired when plugin is loaded
	 * Determines if the plugin can run or not
	 */
	public static function pdf_init() 
	{
		global $gfpdfe_data;
   
	   /*
	    * Add localisation support
	    */ 
	    load_plugin_textdomain('pdfextended', false, PDF_PLUGIN_DIR . 'resources/languages' );

		/*
		 * Call our Settings class which will do our compatibility processing
		 */
		$gfpdfe_data->settingsClass = new GFPDF_Settings();		
		 
		/*
		 * We'll initialise our model which will do any function checks ect
		 */
		 include PDF_PLUGIN_DIR . 'model/pdf.php';			 
		 self::$model = new GFPDF_Core_Model();					 
		 			 	
		/*
		* Check for any major compatibility issues early
		*/
		if(self::$model->check_major_compatibility() === false)
		{
			/*
			 * Major compatibility errors (WP version or Gravity Forms)
			 * Exit to prevent conflicts
			 */
			return;  
		}
		
		/*
		* Some functions are required to monitor changes in the admin area
		* and ensure the plugin functions smoothly
		*/
		add_action('admin_init', array('GFPDF_Core', 'fully_loaded_admin'));	
		add_action('after_switch_theme', array('GFPDF_InstallUpdater', 'gf_pdf_on_switch_theme'), 10, 2);				 		 		
		
		/*
		 * Check if we need to deploy the software
		 */
		 if( is_admin() )
		 {
			self::check_deployment();
		 }
		
		/*
		 * Only load the plugin if the following requirements are met:
		 *  - Load on Gravity Forms Admin pages
		 *  - Load if requesting PDF file
		 *  - Load if on Gravity Form page on the front end
		 *  - Load if receiving Paypal IPN
		 */		 		
		 if( ( is_admin() && isset($_GET['page']) && (substr($_GET['page'], 0, 3) === 'gf_') ) ||
		 	  ( isset($_GET['gf_pdf']) ) ||
			  ( RGForms::get("page") == "gf_paypal_ipn") ||
			  ( isset($_POST["gform_submit"]) && GFPDF_Core_Model::valid_gravity_forms() || 
			  	(  defined( 'DOING_AJAX' ) && DOING_AJAX && isset($_POST['action']) && isset($_POST['gf_resend_notifications'])) )
			)
		 {			
			/*
			 * Initialise the core class which will load the __construct() function
			 */
			global $gfpdf;
			$gfpdf = new GFPDF_Core();  		 	
		 }
		 
		 return;
				  
   }	
	
	public function __construct()
	{
		global $gfpdfe_data;
		
	    /* 
		 * Include the core files
		 */ 
		 include PDF_PLUGIN_DIR . 'helper/pdf-render.php'; 
		 include PDF_PLUGIN_DIR . 'helper/pdf-entry-detail.php';  
		
		/*
		* Set up the PDF configuration and indexer
		* Accessed through $this->configuration and $this->index.
		*/
		parent::__construct();
			
		/*
		* Add our installation/file handling hooks
		*/				
		add_action('admin_init',  array($this, 'gfe_admin_init'), 9);															
				
		/*
		 * Ensure the system is fully insatlled		 
		 */
		if(GFPDF_Core_Model::is_fully_installed() === false)
		{
			return; 
		}		
		
		/*
		* Add our main hooks
		*/		
		add_action('gform_entries_first_column_actions', array('GFPDF_Core_Model', 'pdf_link'), 10, 4);
		add_action("gform_entry_info", array('GFPDF_Core_Model', 'detail_pdf_link'), 10, 2);
		add_action('wp', array('GFPDF_Core_Model', 'process_exterior_pages'));		
		

		/*
		* Apply default filters
		*/  
		add_filter('gfpdfe_pdf_template', array('PDF_Common', 'do_mergetags'), 10, 3); /* convert mergetags in PDF template automatically */
		add_filter('gfpdfe_pdf_template', 'do_shortcode', 10, 1); /* convert shortcodes in PDF template automatically */ 		


		/* Check if on the entries page and output javascript */
		if(is_admin() && rgget('page') == 'gf_entries')
		{
			wp_enqueue_script( 'gfpdfeentries', PDF_PLUGIN_URL . 'resources/javascript/entries-admin.js', array('jquery') );		
		}		
		
		/*
		* Register render class
		*/		
		$this->render = new PDFRender();
		
		/*
		* Run the notifications filter / save action hook if the web server can write to the output folder
		*/
		if($gfpdfe_data->can_write_output_dir === true)
		{
			add_action('gform_after_submission', array('GFPDF_Core_Model', 'gfpdfe_save_pdf'), 10, 2);
			add_filter('gform_notification', array('GFPDF_Core_Model', 'gfpdfe_create_and_attach_pdf'), 100, 3);  /* ensure it's called later than standard so the attachment array isn't overridden */	  		  
		}
		
	}
	
	/*
	 * Do processes that require Wordpress Admin to be fully loaded
	 */
	 public static function fully_loaded_admin()
	 {
		 
		 /*
		  * Check if the user has switched themes and they haven't yet prompt user to copy over directory structure
		  * If the plugin has just initialised we won't check for a theme swap as initialisation will reset this value
		  */ 
		  if(!rgpost('upgrade'))
		  {
		  	GFPDF_InstallUpdater::check_theme_switch();		 
		  }
	 }
	 
	 /*
	  * Check if the software needs to be deployed/redeployed
	  */
	  public static function check_deployment()
	  {
			/* 
			 * Check if database plugin version matches current plugin version and updates if needed
			 */
			if( PDF_DEPLOY === true
				&& get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION
				&& (
					(
						(isset($_GET['page']) && $_GET['page'] != 'gf_settings') &&
						(isset($_GET['addon']) && $_GET['addon'] != 'PDF')
					)
					 || empty($_GET['page'])
					)
			)
			{
				/* update the deploy option*/
				update_option('gf_pdf_extended_deploy', 'no');
			}
			elseif(PDF_DEPLOY === false && get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION)
			{
				/* bring the version inline */
				update_option('gf_pdf_extended_version', PDF_EXTENDED_VERSION);
			}
			
			/*
			 * Check if GF PDF Extended is correctly installed. If not we'll run the installer.
			 */	
			$theme_switch = get_option('gfpdfe_switch_theme'); 

			if( (
					(get_option('gf_pdf_extended_installed') != 'installed')
				) && (!rgpost('upgrade') )
				  && (empty($theme_switch['old']) )
			  )
			{
				/*
				 * Prompt user to initialise plugin
				 */
				 add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_not_deployed_fresh")); 	
			}
			elseif( (
						( !is_dir(PDF_TEMPLATE_LOCATION))  ||
						( !file_exists(PDF_TEMPLATE_LOCATION . 'configuration.php') ) ||
						( !is_dir(PDF_SAVE_LOCATION) ) ||
						( file_exists(PDF_PLUGIN_DIR .'mPDF.zip') )
					)
					&& (!rgpost('upgrade'))
					&& (empty($theme_switch['old']) )

				  )
			{
				/*
				 * Prompt user that a problem was detected and they need to redeploy
				 */
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_problem_detected"));
			}
			else
			{				
			
				/**
				 * Check if deployed new template files after update
				 */ 
				 if( (get_option('gf_pdf_extended_deploy') == 'no' && !rgpost('upgrade') )  && !rgpost('upgrade') ) {
					/*show warning message */
					add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_not_deployed")); 	
				 }	
			}		  
	  }
	
	/**
	 * Check to see if Gravity Forms is actually installed
	 */
	function gfe_admin_init()
	{					
									
		/* 
		 * Configure the settings page
		 */
		 
		  wp_enqueue_style( 'pdfextended-admin-styles', PDF_PLUGIN_URL . 'resources/css/admin-styles.css' );		
		  wp_enqueue_style( 'pdfextended-font-styles', PDF_PLUGIN_URL . 'resources/css/font-awesome.min.css' );	
		  
		  global $wp_styles;
		  wp_enqueue_style( 'pdfextended-font-styles-ie', PDF_PLUGIN_URL . 'resources/css/font-awesome-ie7.min.css' );		
		  $wp_styles->add_data( 'pdfextended-font-styles-ie', 'conditional', 'IE 7' ); 
		  
		  wp_enqueue_script( 'pdfextended-settings-script', PDF_PLUGIN_URL . 'resources/javascript/admin.js' );	
		 
		 /*
		  * Register our scripts/styles with Gravity Forms to prevent them being removed in no conflict mode
		  */
		  add_filter('gform_noconflict_scripts', array('GFPDF_Core', 'register_gravityform_scripts')); 
		  add_filter('gform_noconflict_styles', array('GFPDF_Core', 'register_gravityform_styles')); 		  
		 
    	 GFPDF_Settings::settings_page();	
		  
	}
	
	public static function register_gravityform_scripts($scripts)
	{
		$scripts[] = 'pdfextended-settings-script';
		$scripts[] = 'gfpdfeentries';
		
		return $scripts;
	}
	
	public static function register_gravityform_styles($styles)
	{
		$styles[] = 'pdfextended-admin-styles';
		$styles[] = 'pdfextended-font-styles';
		$styles[] = 'pdfextended-font-styles-ie';						
		
		return $styles;
	}	
	
}
