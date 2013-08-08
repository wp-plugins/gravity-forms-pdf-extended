<?php

/**
 * Plugin: Gravity Forms PDF Extended
 * File: settings.php
 * 
 * Handles the Gravity Forms Settings page in Wordpress
 */

class GFPDF_Settings
{
	/* 
	 * Check if we're on the settings page 
	 */ 
	public function settings_page() {
		if(RGForms::get("page") == "gf_settings") {
					
			/* Call settings page */			
			RGForms::add_settings_page("PDF", array("GFPDF_Settings", "gfpdf_settings_page"),'');
		}			
	}
	
	private function run_setting_routing()
	{
		/* 
		 * Check if we need to redeploy default PDF templates/styles to the theme folder 
		 */
		if( PDF_DEPLOY === true && rgpost("gfpdf_deploy") && 
		( wp_verify_nonce($_POST['gfpdf_deploy_nonce'],'gfpdf_deploy_nonce_action') || wp_verify_nonce($_GET['_wpnonce'],'pdf-extended-filesystem') ) ) {				
			if(rgpost('upgrade'))
			{
				/* 
				 * Deploy new template styles 
				 * If we get false returned Wordpress is trying to get 
				 * access details to update files so don't display anything.
				 */
				if(self::deploy() === false)
				{
					return true;
				}
			}
			elseif(rgpost('font-initialise'))
			{
				/*
				 * We only want to reinitialise the font files and configuration
				 */	
				 if(GFPDF_InstallUpdater::initialise_fonts() === false)
				 {
					 return true;
				 }
			}
			/*elseif(rgpost('cancel'))
			{
				update_option('gf_pdf_extended_deploy', 'yes');	
			}*/
		}
		
		/*
		 * If the user hasn't requested deployment and there is a _wpnonce check which one it is 
		 * and call appropriate function
		 */	
		 if(isset($_GET['_wpnonce']))
		 {
			 /*
			  * Check if we want to copy the theme files
			  */
			 if(wp_verify_nonce($_GET['_wpnonce'], 'gfpdfe_sync_now') )
			 {
				 $themes = get_option('gfpdfe_switch_theme');
				 
				 if(isset($themes['old']) && isset($themes['new']) && GFPDF_InstallUpdater::do_theme_switch($themes['old'], $themes['new']) === false)
				 {
					return true; 
				 }
			 }
		 }		
	}
	
	/*
	 * Shows the GF PDF Extended settings page
	 */		
	public function gfpdf_settings_page() 
	{ 
	    /*
		 * Run the page's configuration/routing options
		 */ 
		if(self::run_setting_routing() === true)
		{
			return;	
		}
		
		/*
		 * Show any messages the plugin might have called
		 * Because we had to run inside the settings page to correctly display the FTP credential form admin_notices was already called.
		 * To get around this we can recall it here.
		 */
		 do_action('gfpdfe_notices');
		 
		/* 
		 * Show the settings page deployment form 
		 */
		?>
        
        
        <div id="pdfextended-settings">
                   
                    <div class="leftcolumn">
         <?php 
		  	if(PDF_DEPLOY === true)
			{							
		   ?>
           <h2>Initialise Plugin</h2>
           <p>Fresh installations and users who have just upgraded will need to initialise Gravity Forms PDF Extended to ensure it works correctly.</p>
           
           <p>Initialisation does a number of important things, including:</p>
           
           <ol>
           		<li><strong>Fresh Installation</strong>: Copies all the required template and configuration files to a folder called PDF_EXTENDED_TEMPLATE in your active theme's directory.<br />
                	<strong>Upgrading</strong>: Copies the latest default templates and template.css file to the PDF_EXTENDED_TEMPLATE folder. <strong>If you modified these files please back them up before re-initialising as they will be removed</strong>.
                </li>
           		<li>Unzips the mPDF package</li>
           		<li>Installs any fonts found in the PDF_EXTENDED_TEMPLATE/fonts/ folder</li>                
           </ol>
		  	
<form method="post">
                <?php wp_nonce_field('gfpdf_deploy_nonce_action','gfpdf_deploy_nonce'); ?>
                <input type="hidden" name="gfpdf_deploy" value="1">
                <?php 
				
				/*
				 * Remove the cancel feature for the moment
				 *
				
				if(get_option('gf_pdf_extended_deploy') == 'no') { ?>				
                <input type="submit" value="Cancel Deployment" class="button" id="cancelupgrade" name="cancel">                
				<?php } */ ?>                                                
                <input type="submit" value="Initialise Plugin" class="button" id="upgrade" name="upgrade">
                
                <input type="submit" value="Initialise Fonts Only" class="button" id="font-initialise" name="font-initialise">                
          </form>   
          <?php } ?>                         
                    
                     <h2><?php _e('What\'s new in v'.PDF_EXTENDED_VERSION.'?' , "gravityformspdfextended") ?></h2>
<ol>
	<li><strong>Bug</strong> - Fixed issue with plugin breaking website's when the Gravity Forms plugin wasn't activated.</li>
    <li><strong>Housekeeping</strong> - The plugin now only supports Gravity Forms 1.7 or higher and Wordpress 3.5 or higher.</li>          
    <li><strong>Housekeeping</strong> - PDF template files can no longer be accessed directly. Instead, add &amp;html=1 to the end of your URL when viewing a PDF.</li>              
	<li><strong>Extension</strong> - Added additional filters to allow the lead ID and notifications to be overridden.</li>  
</ol>
  
<h2>v3.1.3 Changelog</h2>   
                                                    
  
<ol>
    <li><strong>Feature</strong> - Added signature_details_id to $form_data array which maps a signatures field ID to the array.</li>   
	<li><strong>Extension</strong> - Added pre-PDF generator filter for use with extensions.</li>
	<li><strong>Bug</strong> - Fixed issue with quotes in entry data breaking custom templates.</li>
	<li><strong>Bug</strong> - Fixed issue with the plugin not correctly using the new default configuration template, if set.</li>  
    <li><strong>Bug</strong> - Fixed issue with signature not being removed when only testing with file_exists(). Added second is_dir() test.</li>      
    <li><strong>Bug</strong> - Fixed issue with empty signature field not displaying when option 'default-show-empty' is set.</li>   
    <li><strong>Bug</strong> - Fixed initialisation prompt issue when the MPDF package wasn't unpacked.</li>   
</ol>  


        
        
        </div>
        <div class="rightcolumn">

 	<h2>Upgraded from v2.x.x?</h2>
    <p>We&rsquo;ve spend a lot of time ensuring there won&rsquo;t be compatibility issues  when upgrading from v2.x.x to v3.x.x of the software. If you are upgrading ensure you read our <a href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">migration guide</a> to determine what changes you will need to make to take advantage of all the new features.</p>
    
    <h2>What changed in v3.x.x?</h2>
    
                     <p> <em>Gravity Forms PDF Extended has had some major </em><em>remodelling</em><em> for v3.0.x including removal of the DOMPDF package in favour of the more powerful <a href="http://www.mpdf1.com/mpdf/">mPDF</a> PDF package.</em></p>
Along with a new HTML to PDF generator, we&rsquo;ve rewritten the entire  plugin&rsquo;s base code to make it more user friendly to both hobbyists and  rock star web developers. Configuration time is cut in half and advanced  features like adding PDF security features like password protection or  permissions is now accessible to users who have little experience with  PHP.<br />
<p> <strong>New Features</strong></p>
<p> mPDF offers the following features out of the box:</p>
<ul>
  <li> Language Support – almost all languages are supported including RTL (right to left) languages like Arabic and Hebrew and <a href="http://gravityformspdfextended.com/documentation-v3-x-x/language-support/">CJK languages</a> – Chinese, Japanese and Korean.</li>
  <li> HTML Page Numbering</li>
  <li> Odd and even paging with mirrored margins (most commonly used in printing).</li>
  <li> Nested Tables</li>
  <li> Text-justification and hyphenation</li>
  <li> Table of Contents</li>
  <li> Index</li>
  <li> Bookmarks</li>
  <li> Watermarks</li>
  <li> Password protection</li>
  <li> UTF-8 encoded HTML</li>
  <li> Better system resource handling</li>
</ul>
<p> To see just what mPDF is capable of view the <a href="http://gravityformspdfextended.com/documentation-v3-x-x/templates/getting-started/">custom template examples</a> on our documentation pages.</p>
<p> <strong>What else is new in v3.x.x?</strong></p>
<p> A new HTML to PDF package wasn&rsquo;t the only change to this edition of the  software. We have rewritten the entire configuration system and made it  super easy to get the software up and running.</p>
<p> Users will no longer place code in their active theme&rsquo;s functions.php  file. Instead, configuration will happen in a new file called <a href="http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/">configuration.php</a>, inside the PDF_EXTENDED_TEMPLATES folder (in your active theme).</p>
<p> Generating the default PDF and sending it via your notifications is now  as easy as adding the following code to configuration.php.</p>
<pre>    $gf_pdf_config[] = array(    
        'form_id' =&gt; '1',    
        'notifications' =&gt; true 
     ); 
</pre>
<p> More advanced features can be <a href="http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/">added in a similar way</a>.</p>
<p> <strong>Documentation</strong></p>
<p> We've <a href="http://gravityformspdfextended.com/documentation-v3-x-x/">written the documentation from the ground up</a> with extensive examples of the capabilities of mPDF.</p>    
		</div>            

</div>
        <?php
	}
	
	/*
	 * Deploy the latest template files
	 */
	private function deploy()
	{
		$return = GFPDF_InstallUpdater::pdf_extended_activate();
		if($return !== true)
		{
			return $return;	
		}
		add_action('gfpdfe_notices', array("GFPDF_Settings", "gf_pdf_deploy_success")); 	
	}
	
	public function gf_pdf_deploy_success() {
			echo '<div id="message" class="updated"><p>';
			echo 'You\'ve successfully initialised Gravity Forms PDF Extended.';
			echo '</p></div>';		
	}
}
