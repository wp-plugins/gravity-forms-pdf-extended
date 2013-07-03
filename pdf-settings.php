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
	<li><strong>Extension</strong> - Added pre-PDF generator filter for use with extensions.</li>
	<li><strong>Bug</strong> - Fixed issue with quotes in entry data breaking custom templates.</li>
	<li><strong>Bug</strong> - Fixed issue with the view pdf feature in the admin area not using the new default configuration template, if set.</li>        
</ol>  
  
<h2>v3.1.2 Changelog</h2>   
                     
<ol>                     
<li><strong>Feature</strong> - Added list array, file path, form ID and lead ID to $form_data array in custom templates</li>
<li><strong>Bug</strong> - Fixed initialisation prompt issue when updating plugin</li>
<li><strong>Bug</strong> - Fixed window.open issue which prevented a new window from opening when viewing a PDF in the admin area</li>
<li><strong>Bug</strong> - Fixed issue with product dropdown and radio button data showing the value instead of the name field.</li>
<li><strong>Bug</strong> - Fixed incorrect URL pointing to signature file in $form_data</li></ol>                                  
       
<h2>v3.1.1 Changelog</h2>                                        
<ol>
<li><strong>Bug</strong> - Users whose server only supports FTP file manipulation using the WP_Filesystem API moved the files into the wrong directory due to FTP usually being rooted to the Wordpress home directory. To fix this the plugin attempts to determine the FTP directory, otherwise assumes it is the WP base directory. </li>
<li><strong>Bug</strong>- Initialisation error message was being called but the success message was also showing. </li>
</ol>    

                       <h2><?php _e('v3.1.0 Changelog' , "gravityformspdfextended") ?></h2>
                                    
<ol>                     
<li><strong>Feature</strong> - Added defaults to configuration.php which allows users to define the default PDF settings for all Gravity Forms. See the <a href="http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/#default-configuration-options">installation and configuration documentation</a> for more details. </li>
<li><strong>Feature</strong> - Added three new configuration options 'default-show-html', 'default-show-empty' and 'default-show-page-names' which allow different display options to the three default templates. See the <a href="http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/#default-template-only">installation and configuration documentation</a> for more details.</li>
<li><strong>Feature</strong> - Added filter hooks 'gfpdfe_pdf_name' and 'gfpdfe_template' which allows developers to further modify a PDF name and template file, respectively, outside of the configuration.php. This is useful if you have a special case naming convention based on user input. See <a href="http://gravityformspdfextended.com/filters-and-hooks/">http://gravityformspdfextended.com/filters-and-hooks/</a> for more details about using these filters.</li>
<li><strong>Feature</strong> - Custom font support. Any .ttf font file added to the PDF_EXTENDED_TEMPLATE/fonts/ folder will be automatically installed once the plugin has been initialised. Users also have the option to just initialise the fonts via the settings page. See the <a href="http://gravityformspdfextended.com/documentation-v3-x-x/language-support/#installing-fonts">fonts/language documentation</a> for details.</li>
<li><strong>Compatability</strong> - Use Gravity Forms get_upload_root() and get_upload_url_root() instead of hard coding the signature upload directory in pdf-entry-detail.php</li>
<li><strong>Compatability</strong> - Changed depreciated functions get_themes() and get_theme() to wp_get_theme() (added in Wordpress v3.4). </li>
<li><strong>Compatability</strong> - The plugin now needs to be initialised on fresh installation and upgrade. This allows us to use the WP_Filesystem API for file manipulation.</li>
<li><strong>Compatability</strong> - Automatic copying of PDF_EXTENDED_TEMPLATE folder on a theme change was removed in favour of a user prompt. This allows us to take advantage of the WP_Filesystem API.</li>
<li><strong>Compatability</strong> - Added Wordpress compatibility checker (minimum now 3.4 or higher).</li>
<li><strong>Bug</strong> - Removed ZipArchive in favour of Wordpress's WP_Filesystem API unzip_file() command. Some users reported the plugin would stop their entire website working if this extension wasn't installed.</li>
<li><strong>Bug</strong> - Fixed Gravity Forms compatibility checker which wouldn't return the correct response.</li>
<li><strong>Bug</strong> - Fixed minor bug in pdf.php when using static call 'self' in add_filter hook. Changed to class name.</li>
<li><strong>Bug</strong> - Removed PHP notice about $even variable not being defined in pdf-entry-detail.php</li>
<li><strong>Bug</strong> - Prevent code from continuing to excecute after sending header redirect.</li>   </ol>                  


        
        
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
