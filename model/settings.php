<?php

/**
 * Plugin: Gravity Forms PDF Extended
 * File: mode/settings.php
 * 
 * The model that does all the processing and interacts with our controller and view
 */

class GFPDF_Settings_Model extends GFPDF_Settings
{
	public $navigation = array();

	/*
	 * Construct
	 */
	 public function __construct()
	 {
		/*
		 * Let's check if the web server is going to be compatible 
		 */ 
		 $this->check_compatibility();			 
		 $this->support_navigation();	 
	 }
	 
	 public function support_navigation()
	 {
			$this->navigation = array(
				10 => array(
					'name' => 'Initialisation',
					'id' => '#initialisation',
					'template' => PDF_PLUGIN_DIR . 'view/templates/settings/initialisation-tab.php',
				),
				
				20 => array(
					'name' => 'Support',
					'id' => '#support',
					'template' => PDF_PLUGIN_DIR . 'view/templates/settings/support.php' ,
				),	
				
				30 => array(
					'name' => 'Changelog',
					'id' => '#changelog',
					'template' => PDF_PLUGIN_DIR . 'view/templates/settings/changelog.php',
				),							
			); 
			
			/*
			 * Allow additional navigation to be added to the settings page
			 */
			$this->navigiation = apply_filters( 'pdf_extended_settings_navigiation', $this->navigation );
	 }
	 
	 public function check_compatibility()
	 {
		 $this->is_initialised();
		 $this->fresh_install();
		 
		 $this->check_wp_compatibility();
	 	 $this->check_gf_compatibility();
		 $this->check_php_compatibility();

		 $this->mb_string_installed();
		 $this->gd_installed();
		 $this->check_available_ram();
		 
		 $this->check_write_permissions();
	 }
	
	/*
	 * Used to check if this is a fresh installation or an upgrade
	 */
	
	private function fresh_install()
	{
		global $gfpdfe_data;	
		
		if(get_option('gf_pdf_extended_installed') !== 'installed')
		{
			$gfpdfe_data->fresh_install = true;
		}
		else
		{
			$gfpdfe_data->fresh_install = false;	
		}
	}
	 /*
	  * Check if the software has been initialised
	  */
	 private function is_initialised()
	 {
		 global $gfpdfe_data;
		 
		 /*
		  * Sniff the options to see if it exists
		  */
		  $gfpdfe_data->is_initialised = false;
		  if( (get_option('gf_pdf_extended_deploy') == 'yes') && (get_option('gf_pdf_extended_installed') == 'installed') && (is_dir(PDF_TEMPLATE_LOCATION) ) && get_option('gf_pdf_extended_version') == PDF_EXTENDED_VERSION && !file_exists(PDF_PLUGIN_DIR .'mPDF.zip') )
		  {
		 		$gfpdfe_data->is_initialised = true;
		  }
		 $gfpdfe_data->allow_initilisation = true;
	 }
	 
	 private function check_wp_compatibility()
	 {
		global $wp_version, $gfpdfe_data;
		$gfpdfe_data->wp_version = $wp_version;
		
		if(version_compare($gfpdfe_data->wp_version, GF_PDF_EXTENDED_WP_SUPPORTED_VERSION, ">=") === true)
		{
			$gfpdfe_data->wp_is_compatible = true;
			return;
		}		 
		$gfpdfe_data->wp_is_compatible = false;
		$gfpdfe_data->allow_initilisation = false;
	 }
	 
	 private function check_gf_compatibility()
	 { 
	 	 global $gfpdfe_data;
		 
		 if(class_exists('GFCommon'))
		 {
			 $gfpdfe_data->gf_installed = true;
			 $gfpdfe_data->gf_version = GFCommon::$version;
			  			 
			 if(version_compare($gfpdfe_data->gf_version, GF_PDF_EXTENDED_SUPPORTED_VERSION, '>=') === true)
			 {
				$gfpdfe_data->gf_is_compatible = true;
				return;
			 }	
		 }
		 $gfpdfe_data->gf_installed = false;
		 $gfpdfe_data->gf_is_compatible = false;
		 $gfpdfe_data->allow_initilisation = false;
	 }	 
	 
	 private function check_php_compatibility()
	 {
	 	 global $gfpdfe_data;	
		 $gfpdfe_data->php_version = (float) phpversion();
		 		
		 if(version_compare($gfpdfe_data->php_version, GF_PDF_EXTENDED_PHP_SUPPORTED_VERSION, '>=') === true)
		 {
			$gfpdfe_data->php_version_compatible = true; 
			return;
		 }
		 $gfpdfe_data->php_version_compatible = false; 
		 $gfpdfe_data->allow_initilisation = false;
	 }		

	 private function mb_string_installed()
	 {
	 	 global $gfpdfe_data;
		 		 
		 if(extension_loaded('mbstring'))
		 {
			$gfpdfe_data->mb_string_installed = true; 
			return;
		 }
		 $gfpdfe_data->mb_string_installed = false; 
		 $gfpdfe_data->allow_initilisation = false;
	 }	
	 
	 private function gd_installed()
	 {
	 	 global $gfpdfe_data;
		 		 
		 if(extension_loaded('gd'))
		 {
			$gfpdfe_data->gd_installed = true; 
			return;
		 }
		 $gfpdfe_data->gd_installed = false;
		 $gfpdfe_data->allow_initilisation = false;
	 }	 
	 
	 private function check_available_ram()
	 {
	 	 global $gfpdfe_data;
		 		 
		 /*
		  * Get ram available in bytes and convert it to megabytes
		  */
		 $gfpdfe_data->ram_available = floor(memory_get_usage() / 1024 / 1024);
		 $gfpdfe_data->ram_compatible = true;
		 if($gfpdfe_data->ram_available < 128)
		 {
			$gfpdfe_data->ram_compatible = false; 
		 }
	 }	
 
	 private function check_write_permissions()
	 {
	 	 global $gfpdfe_data;
		 		 
		 /*
		  * Attempt to actually write a file and test if it works
		  */
		  
		  /*
		   * Check if the PDF_EXTENDED_FOLDER is already created
		   */
		  if($gfpdfe_data->is_initialised === false)
		  {
			  
			  /*
			   * Default our values
			   */
			   $gfpdfe_data->can_write_plugin_dir = false;	
			   $gfpdfe_data->can_write_theme_dir = false;		  
			   
			  /*
			   * Test the plugin folder so we can unzip mPDF
			   */
			  if($this->test_write_permissions(PDF_PLUGIN_DIR) === true)
			  {
				  $gfpdfe_data->can_write_plugin_dir = true;
			  }	
			  		   
			  /*
			   * Check if we can write in the user's active theme directory
			   */			  			   
			   if($this->test_write_permissions(get_stylesheet_directory() . '/') === true)
			   {
				   $gfpdfe_data->can_write_theme_dir = true;
			   }
		  }
		  else
		  {
			  /*
			   * Default our values
			   */
			  $gfpdfe_data->can_write_output_dir = false;
			  $gfpdfe_data->can_write_font_dir = false;	 
			  $gfpdfe_data->can_write_pdf_temp_dir = false;
			  
			  /*
			   * The PDF_EXTENDED_TEMPLATE folder is created so lets check our permissions
			   */
			  if($this->test_write_permissions(PDF_SAVE_LOCATION) === true)
			  {
  		  			$gfpdfe_data->can_write_output_dir = true;	  
			  }
			  
			  if($this->test_write_permissions(PDF_FONT_LOCATION) === true)
			  {
  		  			$gfpdfe_data->can_write_font_dir = true;	  
			  }		
			  
			  if($this->test_write_permissions(PDF_PLUGIN_DIR . 'mPDF/tmp/') === true)
			  {
					$gfpdfe_data->can_write_pdf_temp_dir = true;  
			  }
		  }
		  
	 }	 	 
	 
	 private function test_write_permissions($path)
	 {
	 	 global $gfpdfe_data;
		 		 
		if(is_writable($path))
		{
			file_put_contents($path . '.pdf_extended_temp', '');
			if(file_exists($path . '.pdf_extended_temp'))
			{
				/* clean up */
				unlink($path . '.pdf_extended_temp');
				return true;	
			}
		}
		return false;
	 }
	
	/*
	 * Shows the GF PDF Extended settings page
	 */		
	public function gfpdf_settings_page() 
	{ 
		global $gfpdfe_data;
	    /*
		 * Run the page's configuration/routing options
		 */  
		if($this->run_setting_routing() === true)
		{
			return;	
		}
		
		 include PDF_PLUGIN_DIR . 'view/settings.php';				 		 
		  
		 /*
		  * Pass any additional variables to the view templates
		  */
		  $gfpdfe_data->active_plugins 			= $this->get_active_plugins();
		  $gfpdfe_data->system_status 			= $this->get_system_status_html();
		  $gfpdfe_data->configuration_file 		= $this->get_configuration_file();				 
		 
		 new settingsView($this);
	}
	
	private static function get_configuration_file()
	{
		global $gfpdfe_data;
		
		if(isset($gfpdfe_data->configuration_file))
		{
			return $gfpdfe_data->configuration_file;	
		}				
			/*
			 * Include the current configuration, if available
			 */
			 if(file_exists(PDF_TEMPLATE_LOCATION . 'configuration.php'))
			 {
				 return esc_html(file_get_contents(PDF_TEMPLATE_LOCATION . 'configuration.php'));   
			 }
			 else
			 {
				 return __('Plugin not yet initialised', 'pdfextended');
			 }		
	}
	
	private static function get_system_status_html()
	{
		global $gfpdfe_data;
		
		if(isset($gfpdfe_data->system_status))
		{
			return $gfpdfe_data->system_status;	
		}
		
		 ob_start();
		 include PDF_PLUGIN_DIR . 'view/templates/settings/system-status.php';                         
		 $content = ob_get_contents();
		 ob_end_clean();
		 return esc_html($content);			
	}
	
	private static function get_active_plugins()
	{
		global $gfpdfe_data;
		
		if(isset($gfpdfe_data->active_plugins))
		{
			return $gfpdfe_data->active_plugins;	
		}		
		$active_plugins = get_option('active_plugins');
		
		/*
		 * Look up the name of the plugin
		 */
		 $user_plugins = array();
		 foreach($active_plugins as $plugin)
		 {
				$data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin ); 
				$user_plugins[] = $data['Name'].', '. $data['Version'].' - '. $data['PluginURI'];
		 }
		$plugins = implode("\n", $user_plugins);
		return $plugins;		
	}
	
	/*
	 * Handle the AJAX Support Request
	 */
	public static function gfpdf_support_request()
	{
		/*
		 * Check the Nonce to make sure it is a valid request
		 */
		 $nonce = $_POST['nonce'];

		 if( ! wp_verify_nonce( $nonce, 'pdf_settings_nonce' ) )
		 {
				print json_encode(array('error' => array('msg' => __('There was a problem with your submission. Please reload the page and try again', 'pdfextended')) )); 
				exit; 
		 }
		 
		 /* 
		  * AJAX Automatically adding slashes so remove them
		  */
		 $email = stripslashes($_POST['email']);
		 $countType = stripslashes($_POST['supportType']);
		 $comments = stripslashes($_POST['comments']);

		 $error = array();
		 /*
		  * Check that email, support type and comments are valid
		  */
		  if( ! is_email($email) )
		  {
			  $error['email'] = __('Please enter a valid email address', 'pdfextended');
		  }
		  
		  $valid_support_types = array(__('Problem', 'pdfextended'), __('Question', 'pdfextended'), __('Suggestion', 'pdfextended'));
		  
		  if(in_array($countType, $valid_support_types) === false)
		  {
			  $error['supportType'] = __('Please select a valid support type.', 'pdfextended');
		  }
		  
		  if(strlen($comments) == 0)
		  {
				$error['comments'] = __('Please enter information about your support query so we can aid you more easily.', 'pdfextended');
		  }
		  
		  if(sizeof($error) > 0)
		  {
			    $error['msg'] = __('There is a problem with your support request. Please correct the marked issues above.', 'pdfextended');
				print json_encode(array('error' => $error));
				exit;  
		  }
		  
		  /*
		   * Do our POST request to the Gravity Forms PDF Extended API
		   */
		   self::send_support_request($email, $countType, $comments);
		 
		 print json_encode(array('msg' => __('Thank you for your support request. We\'ll respond to your request in the next 24-48 hours.', 'pdfextended')));
		 exit;

	}
	
	public static function send_support_request($email, $countType, $comments)
	{
		global $gfpdfe_data;
		/*
		 * Include our API class
		 */			 
		 $api = new gfpdfe_API();
		 
		 /*
		  * Build our support request array
		  */
		  
		  $active_plugins   = self::get_active_plugins();
		  $system_status 	= self::get_system_status_html();
		  $configuration	= self::get_configuration_file();
		  $website			= site_url('/');
		  $comments 		= stripslashes($comments);
		  
		  $body = array(
		  	'support_type' 			=> $countType,
			'email'					=> $email,
			'website' 				=> $website,
			'active_plugins' 		=> $active_plugins,
			'system_status'			=> $system_status,
			'configuration' 		=> $configuration,			
			'comments' 				=> $comments,
		  );  
		 		 
		 if($api->support_request($body) === false)
		 {
			/*
			 * API could not send
			 * Draft a plain text email and send to support
			 */ 
			 $configuration = htmlspecialchars_decode($configuration, ENT_QUOTES);
			 
			 $subject = 'Gravity Forms PDF Extended Support Request';
			 $to	  = 'support@gravityformspdfextended.com';
			 $from	  = $email;			 
			 $message = "Support Type: $countType\r\nWebsite: $website\r\n\r\nComments\r\n\r\n$comments\r\n\r\n\r\nActive Plugins\r\n\r\n$active_plugins\r\n\r\n\r\nConfiguration\r\n\r\n$configuration";
			 
			 $headers[] = 'From: '. $email;

			 if(wp_mail($to, $subject, $message, $headers) === false)
			 {			 
				/*
				 * Error
				 */ 
				 print json_encode(array('error' => array('msg' => $api->response_message )));
				 exit;			 
			 }
			 else
			 {
				 print json_encode(array('msg' => __('Support request received. We will responed in 24 to 48 hours.', 'pdfextended')));
				 exit;					 
			 }
		 }
		 
		 /*
		  * Create our 
		  */
		 exit;
	}
	
	
	public function gf_pdf_deploy_success() {
			echo '<div id="message" class="updated"><p>';
			echo __('You\'ve successfully initialised Gravity Forms PDF Extended.', 'pdfextended');
			echo '</p></div>';		
	}	
}