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

			/* No update to templates this edition so no need to redeploy files */
			/* Check if we need to redeploy default PDF templates/styles to the theme folder */
			if( PDF_DEPLOY === true && rgpost("gfpdf_deploy") && wp_verify_nonce($_POST['gfpdf_deploy_nonce'],'gfpdf_deploy_nonce_action')) {				
				if(rgpost('upgrade'))
				{
					/* deploy new template styles */
					self::deploy();
				}
				elseif(rgpost('cancel'))
				{
					update_option('gf_pdf_extended_deploy', 'yes');	
				}
			}
					
			/* Call settings page and */			
			RGForms::add_settings_page("PDF", array("GFPDF_Settings", "gfpdf_settings_page"),'');
		}			
	}
	
	/*
	 * Shows the GF PDF Extended settings page
	 */		
	public function gfpdf_settings_page() 
	{ 
		/* add deployment form */
		?>
        <div id="pdfextended-settings">
                   
                    <div class="leftcolumn">
                     <h2><?php _e('What\'s new in v'.PDF_EXTENDED_VERSION.'?' , "gravityformspdfextended") ?></h2>
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
<p> <strong>What else is new in v3.0.0?</strong></p>
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
<h3>&nbsp;</h3>

          <?php 
		  	if(PDF_DEPLOY === true)
			{							
		   ?>
		  	
<form method="post">
                <?php wp_nonce_field('gfpdf_deploy_nonce_action','gfpdf_deploy_nonce'); ?>
                <input type="hidden" name="gfpdf_deploy" value="1">
                <?php if(get_option('gf_pdf_extended_deploy') == 'no') { ?>				
                <input type="submit" value="Cancel Deployment" class="button" id="cancelupgrade" name="cancel">                
				<?php } ?>                
                <input type="submit" value="Deploy Templates" class="button" id="upgrade" name="upgrade">
          </form>   
          <?php } ?>     
        
        
        </div>
        <div class="rightcolumn">

 	<h2>Upgraded from a previous version?</h2>
    <p>We&rsquo;ve spend a lot of time ensuring there won&rsquo;t be compatibility issues  when upgrading from a previous version of the software. If you are upgrading ensure you read our <a href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">migration guide</a> to determine what changes you will need to make to take advantage of all the new features.</p>
		</div>            

</div>
        <?php
	}
	
	/*
	 * Deploy the latest template files
	 */
	private function deploy()
	{
		GFPDF_InstallUpdater::pdf_extended_activate(true);
		add_action('admin_notices', array("GFPDF_Settings", "gf_pdf_deploy_success")); 	
	}
	
	public function gf_pdf_deploy_success() {
			echo '<div id="message" class="updated"><p>';
			echo 'You\'ve successfully deployed the Gravity Forms PDF Extended template files.';
			echo '</p></div>';		
	}
}
