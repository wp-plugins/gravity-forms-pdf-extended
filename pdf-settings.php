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
          
          <div class="updated"><p><strong>Note:</strong> We've depreciated v2.x.x templates in this release. If you are still using a v2.x.x template ensure you follow our <a href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">v3 migration guide</a> before initialising. <strong>This does not apply to fresh installations of the plugin</strong>.</p></div>                              
                               
           
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
<li><strong>Feature</strong> - Can now view multiple PDFs assigned to a single form via the admin area. Note: You must provide a unique 'filename' parameter in configuration.php for multiple PDFs assigned to a single form. </li>
<li><strong>Feature</strong> - You can exclude a field from the default templates using the class name 'exclude'. See our <a href="http://gravityformspdfextended.com/faq/can-exclude-field-showing-pdf/">FAQ topic</a> for more details.</li>
<li><strong>Bug</strong> - Fixed issue viewing own PDF entry when logged in as anything lower than editor.
<li><strong>Bug</strong> - Fix data return bug in pdf-entry-details.php that was preventing all data returning correctly.</li>
<li><strong>Bug</strong> - Fix PHP Warning when using products with no options</li>
<li><strong>Bug</strong> - Fixed issue with invalid characters being added to the PDF filename. Most notably the date mergetag.</li>
<li><strong>Bug</strong> - Limit filename length to 150 characters which should work on the majority of web servers.</li>
<li><strong>Bug</strong> - Fixed problem sending duplicate PDF when using mass resend notification feature</li>
<li><strong>Depreciated</strong> - Removed GF_FORM_ID and GF_LEAD_ID constants which were used in v2.x.x of the software. Ensure you follow <a href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">v2.x.x upgrade guide</a> to your templates before upgrading.</li>

</ol>

 <h2>Need Support?</h2>
 
 <p>Head to our <a href="http://gravityformspdfextended.com/support/gravity-forms-pdf-extended/">support forum and start a new topic</a>. Provide as much details as you can about the issue and we'll aid you in your problem.</p>

        </div>
        <div class="rightcolumn">

 	<h2>Upgraded from v2.x.x?</h2>
    <p>We&rsquo;ve spend a lot of time ensuring there won&rsquo;t be compatibility issues  when upgrading from v2.x.x to v3.x.x of the software. If you are upgrading ensure you read our <a href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">migration guide</a> to determine what changes you will need to make to take advantage of all the new features.</p>
    
    <h2>What is Gravity Forms PDF Extended capable of?</h2>
    
          <p>
We are utilising the most advanced open source HTML to PDF software available to turn Gravity Form data into PDFs. Development time is cut in half and advanced  features like adding PDF security features like password protection or  permissions is now accessible to users who have little experience with  PHP.</p>
          <p> <strong> Features</strong></p>
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
