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
			if( PDF_DEPLOY === true && rgpost("gfpdf_deploy") && rgpost('upgrade') && wp_verify_nonce($_POST['gfpdf_deploy_nonce'],'gfpdf_deploy_nonce_action')) {				
				/* deploy new template styles */
				self::deploy();
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
                     <h2><?php _e("Welcome to Gravity Forms PDF Extended v".PDF_EXTENDED_VERSION , "gravityformspdfextended") ?></h2>
                     <h3>Freshly Installed?</h3>
                     <p>Ensure you follow our <a href="http://gravityformspdfextended.com/documentation/introduction/">comprehensive installation guide</a> to get your website creating and emailing PDFs. If you run into any problems you can ask our friendly staff on our <a href="http://gravityformspdfextended.com/support/gravity-forms-pdf-extended/">support forums</a> or browse through the <a href="http://gravityformspdfextended.com/faq/category/developers/">FAQs</a>.</p>
                         <h3>Just upgraded?</h3>
                    <p>Changes have been made to Gravity Forms PDF Extended that may affect you. It's important to review these updates to ensure the plugin will continue working.</p>

      <h3>Are you running Gravity Forms 1.6 or lower? </h3>
      <p>If you're running Gravity Forms 1.6 you don't need to do a thing just yet. Once Gravity Forms 1.7 is released, and you upgrade, you'll need to modify the plugin to ensure compatibility. Until then, enjoy Gravity Forms PDF Extended.</p>
      <h3>Upgraded to Gravity Forms 1.7? You'll need to address the following:</h3>
        <ol>
          <li> Gravity Forms 1.7 has overhauled the notification process and replaced a number of key hooks which Gravity Forms PDF Extended relied on. To ensure compatibility with new forms you will need to remove the two functions <em>gform_pdf_create</em> and <em>gform_add_attachment </em>and replace them with <strong>gfpdfe_create_and_attach_pdf()</strong>. <a href="http://gravityformspdfextended.com/gravity-forms-pdf-extended-2-2-0-release-notes/">Please review our Gravity Forms 1.7 upgrade guide</a> which has a detailed explanation on changes you need to make.        </li>
        </ol>
        <p>If you need assistance upgrading please don't hesitate to <a href="http://gravityformspdfextended.com/support/gravity-forms-pdf-extended/">create a topic in our support forum</a> or email us directly on enquire@blueliquiddesigns.com.au.</p>

          <?php 
		  	if(PDF_DEPLOY === true)
			{
				/* No updated to template files this edition so no need to redeploy */
		   ?>
		  	
<form method="post">
                <?php wp_nonce_field('gfpdf_deploy_nonce_action','gfpdf_deploy_nonce'); ?>
                <input type="hidden" name="gfpdf_deploy" value="1">
                <input type="submit" value="Deploy Templates" class="button" id="upgrade" name="upgrade">
          </form>   
          <?php } ?>     
        
        
        </div>
        <div class="rightcolumn">

 	<h2><?php _e('What\'s new in v'.PDF_EXTENDED_VERSION.'?' , "gravityformspdfextended") ?></h2>
    <p>A small bug fix was released for v<?php echo PDF_EXTENDED_VERSION; ?>.</p>
    <ol>
    	<li><strong>Bug: </strong>Fixed HTML error which caused list items to distort on rendered PDF</li>
    </ol>
    
    <h2><?php _e('What\'s new in v2.2.0?' , "gravityformspdfextended") ?></h2>
    
          <p>A number of refinements have been added to this release to make the software easier to use. We've also ensured it will remain compatible with Gravity Forms 1.7 when it is publicly released.</p>
            <ol>
              <li><strong>Compatibility:</strong> Ensure compatibility with Gravity Forms 1.7. We&rsquo;ve      updated the functions.php code and remove <em>gform_user_notification_attachments</em> and <em>gform_admin_notification_attachments</em> hooks which <a href="http://www.gravityhelp.com/gravity-forms-v1-7-developer-notes/">are now      depreciated</a>.      Functions <em>gform_pdf_create</em> and <em>gform_add_attachment </em>have been      removed and replaced with <strong>gfpdfe_create_and_attach_pdf().</strong> <a href="http://gravityformspdfextended.com/gravity-forms-pdf-extended-2-2-0-release-notes/">See upgrade documentation for details</a>.</li>
             <li><strong>Enhancement:</strong> Added deployment code switch so the template redeployment feature can be turned on and off. This release doesn't require redeployment.</li>
              <li><strong>Enhancement: </strong><em>PDF_Generator()</em> variables were getting long and complex so the third variable is now an array      which will pass all the optional arguments. The new 1.7 compatible      functions.php code includes this method by default. For backwards      compatibility the function will still work with the variable structure prior      to 2.2.0.<strong></strong></li>
              <li><strong>Bug: </strong>Fixed error generated by legacy code in the function <em>PDF_processing()</em> which is located      in <em>render_to_pdf.php</em>.<strong></strong></li>
              <li><strong>Bug:</strong> Images and stylesheets will now try and be accessed with a local path instead of a URL. It fixes problem where some hosts were preventing read access from a URL. No template changes are required.</li>
              
            </ol>
<p>Detailed documentation on the latest updates can be found at the official <a href="http://gravityformspdfextended.com/documentation/">Gravity Forms PDF Extended website</a>.</p>
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

?>