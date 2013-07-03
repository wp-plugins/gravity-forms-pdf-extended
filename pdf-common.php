<?php

class PDF_Common
{
	public static function setup_ids()
	{
		global $form_id, $lead_id, $lead_ids;
		
		if(defined('GF_FORM_ID') && defined('GF_LEAD_ID'))
		{
			$form_id = GF_FORM_ID;
			$lead_ids = array(GF_LEAD_ID);		
		}
		else
		{
			$form_id 		=  ($form_id) ? $form_id : absint( rgget("fid") );
			$lead_ids 		=  ($lead_id) ? array($lead_id) : explode(',', rgget("lid"));
		}	
		
		/**
		 * If form ID and lead ID hasn't been set stop the PDF from attempting to generate
		 */
		if(empty($form_id) || empty($lead_ids))
		{
			trigger_error(__("Form Id and Lead Id are required parameters.", "gravityforms"));
			return;
		}				
	}
	
	 /*
	  * Check if the system is fully installed and return the correct values
	  */
	 public static function is_fully_installed()
	 {
		if( (get_option('gf_pdf_extended_installed') != 'installed') || (!is_dir(PDF_TEMPLATE_LOCATION)) )
		{		
			return false;
		}
		
		if(get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION)
		{
			return false;
		}
		
		 if(get_option('gf_pdf_extended_deploy') == 'no' && !rgpost('upgrade') && PDF_DEPLOY === true)		
		 {
			return false; 
		 }
		 
		 if(file_exists(PDF_PLUGIN_DIR .'mPDF.zip'))
		 {
			return false; 
		 }

		 return true;
	 }	
	
	public static function getRealIpAddr()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
		{
		  $ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
		{
		  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	public static function get_html_template($filename) 
	{
	  global $form_id, $lead_id, $lead_ids;

	  ob_start();
	  require($filename);	
	  
	  $page = ob_get_contents();
	  ob_end_clean();	    
	  
	  return $page;
	}	
	
	/**
	 * Get the name of the PDF based on the Form and the submission
	 */
	public static function get_pdf_filename($form_id, $lead_id)
	{
		return "form-$form_id-entry-$lead_id.pdf";
	}
	
	/*
	* Check if mPDF folder exists.
	* If so, unzip and delete
	* Helps reduce the package file size
	*/		
	public static function unpack_mPDF()
	{
		$file = PDF_PLUGIN_DIR .'mPDF.zip';
		$path = pathinfo(realpath($file), PATHINFO_DIRNAME);
		
		if(file_exists($file))
		{
			/* unzip folder and delete */
			$zip = new ZipArchive;
			$res = $zip->open($file);
			
			if ($res === TRUE) {
  				$zip->extractTo($path);
			    $zip->close();	
				unlink($file);
			}
		}
	}	
	
	public static function validate_pdf_name($name, $form_id = false, $lead_id = false)
	{
		if(substr($name, -4) != '.pdf')
		{
			$pdf_name = $name . '.pdf';	
		}
		$pdf_name = $name;
		
		if($form_id > 0)
		{
			$pdf_name = PDF_Common::do_mergetags($name, $form_id, $lead_id);	
		}
		
		return $pdf_name;
	}
	
	public static function do_mergetags($string, $form_id, $lead_id)
	{
		$form = RGFormsModel::get_form_meta($form_id);
		$lead = RGFormsModel::get_lead($lead_id);
		
		/* strip {all_fields} merge tag from $string */
		$string = str_replace('{all_fields}', '', $string);
		
		return trim(GFCommon::replace_variables($string, $form, $lead, false, false, false));		
	}
	
	public static function view_data($form_data)
	{
		if(isset($_GET['data']) && $_GET['data'] === '1' && GFCommon::current_user_can_any("gravityforms_view_entries"))
		{
			print '<pre>'; 
			print_r($form_data);
			print '</pre>';
			exit;
		}
	}
	
    public static function is_gravityforms_supported($version){
        if(class_exists("GFCommon"))
		{			
            if(version_compare(GFCommon::$version, $version, ">=") === true)
			{
            	return true;
			}
        }
		return false;
    }	
	
    public static function is_wordpress_supported($version){
		global $wp_version;
		if(version_compare($wp_version, $version, ">=") === true)
		{
			return true;
		}
		return false;
    }	
	
	public static function display_compatibility_error()
	{
		 $message = sprintf(__("Gravity Forms " . GF_PDF_EXTENDED_SUPPORTED_VERSION . " is required to use this plugin. Activate it now or %spurchase it today!%s"), "<a href='https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154'>", "</a>"); 
		 PDF_Common::display_plugin_message($message, true);			
	}
	
	public static function display_wp_compatibility_error()
	{
		 $message = "Wordpress " . GF_PDF_EXTENDED_WP_SUPPORTED_VERSION . " or higher is required to use this plugin."; 
		 PDF_Common::display_plugin_message($message, true);			
	}	
	
	public static function display_documentation_details()
	{
		 $message = sprintf(__("Please review the %sGravity Forms PDF Extended documentation%s for comprehensive installation instructions. %sUpgraded from v2.x.x? Review our migration guide%s.</span>"), "<a href='http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/'>", "</a>", '<a style="color: red;" href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">', '</a>'); 
		 PDF_Common::display_plugin_message($message);						
	}	
	
	public static function display_plugin_message($message, $is_error = false){

        $style = $is_error ? 'style="background-color: #ffebe8;"' : "";

        echo '</tr><tr class="plugin-update-tr"><td colspan="5" class="plugin-update"><div class="update-message" ' . $style . '>' . $message . '</div></td>';
    }
	
	/* 
	 * New to 3.0.2 we will use WP_Filesystem API to manipulate files instead of using in-built PHP functions	
	 * $post Array the post data to include in the request_filesystem_credntials API	 
	 */
	public static function initialise_WP_filesystem_API($post, $nonce)
	{

		$url = wp_nonce_url(PDF_SETTINGS_URL, $nonce);	
		
		if (false === ($creds = request_filesystem_credentials($url, '', false, false, $post) ) ) {
			/* 
			 * If we get here, then we don't have correct permissions and we need to get the FTP details.
			 * request_filesystem_credentials will handle all that
			 */			 
			return false; // stop the normal page form from displaying
		}		

		/*
		 * Check if the credentials are no good and display an error
		 */
		if ( ! WP_Filesystem($creds) ) {
			request_filesystem_credentials($url, '', true, false, $post_credentials);
			return false;
		}		
		
		return true;
				
	}
	
}
