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
			
			/* Check if we need to redeploy default PDF templates/styles to the theme folder */
			if(rgpost("gfpdf_deploy") && rgpost('upgrade') && wp_verify_nonce($_POST['gfpdf_deploy_nonce'],'gfpdf_deploy_nonce_action')){
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
                    <p>A number of changes have been made to Gravity Forms PDF Extended for this release that may affect you. It's important to review these updates to verify if you need to update your template files to make them v<?php echo PDF_EXTENDED_VERSION; ?> compatible.</p>
      <h3>Changes to PDF Extended that could cause errors if upgrading:</h3>
        <ol>
            <li>To prevent compatibility issues with Gravity Forms we've renamed the GFEntryDetail class to GFPDFEntryDetail. This is called twice in the custom templates and will need to be updated  manually:
              <ul>
                <li> <em>GFEntryDetail::lead_detail_grid_array($form, $lead);</em> becomes <strong>GFPDFEntryDetail::lead_detail_grid_array($form, $lead);</strong></li>
                <li><em> GFEntryDetail::notes_grid($notes, false);</em> becomes<strong> GFPDFEntryDetail::notes_grid($notes, false);</strong></li>
              </ul>
          </li>
            <li>The default template has been renamed from <em>pdf-print-entry.php </em>to <em>default-template.php</em>. This will affect users who use a custom naming convention with the default template or who have modified their default template file. If you have modified your default template file please <strong>back up your file</strong> before deploying the new templates. If you are using a custom name please update your <em>gform_pdf_create() </em>function (inside your active theme's functions.php file) to reflect the new change:
              <ul>
                <li><em>PDF_Generator($form_id, $user_id, 'save', true, 'pdf-print-entry.php', 'MyCustomPDF.pdf'); </em>becomes <strong>PDF_Generator($form_id, $user_id, 'save', true, 'default-template.php', 'MyCustomPDF.pdf'); </strong></li>
              </ul>
            </li>
            <li>We've reworked the custom template $form_data array slightly to remove duplicate data. All fields will still be accessed via $form_data['field'] but a bug in the last edition saw fields being placed outside of the ['field'] array. <em>If you're using $form_data['field'] in your custom templates you shouldn't be effected.</em></li>
        </ol>
        
        
 <h2>Deployment</h2>
            <p>One of the new features we've added to this release is the ability to redeploy the default template files that comes standard with the plugin. This means you can get the latest updates to the template files but doesn't automatically override your default templates (and any custom modifications you've made to them).</p>
            <p><strong>Updates to deployment files</strong></p>
            <p>The following is a quick overview of the important changes to the default template files:</p>
            <ul>
            <li>Resolved PDF generation timeout bug</li>
            <li>Added product table to PDF and product subtotal, shipping and total amounts to $form_data array</li>
            <li><em>GFEntryDetail</em> now <em>GFPDFEntryDetail</em></li>
            <li>Cleaner template files/comments</li>
            <li>Added two new default templates</li>
            <li>Fixed direct access redirect issue</li>
            <li>Removed empty signature fields from PDF</li>
            </ul>
            <p>For a full list of updates please review the change log on the right. </p>
            <p><strong>Default template files to be deployed:</strong></p>
            <?php $theme = wp_get_theme();
				  $activetheme = $theme->get( 'TextDomain' ); ?>
            <ul>            
            	<li><em>gravity-forms-pdf-extended/styles/template.css</em> to <strong><?php echo 'themes/'.$activetheme.'/'.PDF_SAVE_FOLDER.'/template.css'; ?></strong></li>
            	<li><em>gravity-forms-pdf-extended/templates/example-template.php</em> to <strong><?php echo 'themes/'.$activetheme.'/'.PDF_SAVE_FOLDER.'/example-template.php'; ?></strong></li>                
            	<li><em>gravity-forms-pdf-extended/templates/default-template.php</em> to <strong><?php echo 'themes/'.$activetheme.'/'.PDF_SAVE_FOLDER.'/default-template.php'; ?></strong></li>                
            	<li><em>gravity-forms-pdf-extended/templates/default-template-two-rows.php</em> to <strong><?php echo 'themes/'.$activetheme.'/'.PDF_SAVE_FOLDER.'/default-template-two-rows.php'; ?></strong></li>                
            	<li><em>gravity-forms-pdf-extended/templates/default-template-no-style.php</em> to <strong><?php echo 'themes/'.$activetheme.'/'.PDF_SAVE_FOLDER.'/default-template-no-style.php'; ?></strong></li>                                                               
            </ul>
            
            <p><strong>If you have made any modifications to the default templates please backup your files before deploying and then add your custom modifications to the updated templates.</strong></p>
            
          <form method="post">
                <!-- some inputs here ... -->
                <input type="hidden" name="gfpdf_deploy" value="1">
                <input type="submit" value="Deploy Templates" class="button" id="upgrade" name="upgrade">
          </form>        
        
        
        </div>
        <div class="rightcolumn">
        
 	<h2><?php _e('What\'s new in v'.PDF_EXTENDED_VERSION.'?' , "gravityformspdfextended") ?></h2>
          <p>A number of features and bug fixes have been added to the latest release, as well as preparation (or housekeeping as we're calling it) for future releases.</p>
            <ol>
           	    <li><strong>Feature &ndash; </strong>Product table can now be accessed directly through      custom templates by running <em>GFPDFEntryDetail::product_table($form, $lead);</em>. <a href="http://gravityformspdfextended.com/documentation/advanced-configuration/accessing-product-data/">See documentation for more details</a>.</li>
       	        <li><strong>Feature &ndash;</strong> Update screen will ask you if you want to deploy new      template files, instead of overriding your modified versions.</li>
   	            <li><strong>Feature &ndash;</strong> Product subtotal, shipping      and total have been added to $form_data[&lsquo;field&rsquo;] array to make it easier to work with product details in the custom template.</li>
                <li><strong>Feature </strong>&ndash; Added two new default template files. One displays      field and name in two rows (like you see when viewing an entry in the      admin area) and the other removes all styling. <a href="http://gravityformspdfextended.com/documentation/constants-and-default-templates/">See documentation on use</a>. </li>
                <li><strong>Security</strong> &ndash; Tightened PDF template security so that custom templates couldn&rsquo;t be automatically generated by just anyone. Now only logged in users with the correct privileges and the user who submitted the form (matched against IP) can auto generate a PDF. <a href="http://gravityformspdfextended.com/documentation/constants-and-default-templates/">See documentation on usage</a>.</li>
                <li><strong>Depreciated</strong> &ndash; Removed form data that was added directly to the      $form_data array instead of $form_data[&lsquo;field&rsquo;] array. Users upgrading      will need to update their custom templates if not using field data from      the $form_data[&lsquo;field&rsquo;] array. <em>If      using $form_data[&lsquo;field&rsquo;] in your custom template this won&rsquo;t affect you. </em></li>
                <li><strong>Bug</strong> &ndash;<strong> Fixed problem with default template not showing and      displaying a timeout error. Removed table tags and replaced with divs that      are styled appropriately.</strong></li>
           
              <li><strong>Bug</strong> &ndash; The new plugin theme folder will successfully create      when upgrading. You won't have to deactivate and reactivate to get it working.</li>
              <li><strong>Bug</strong> &ndash; some installs had plugins that included the function      mb_string which is also included in DOMPDF. DOMPDF will now check if the      function exists before creating it.</li>
              <li><strong>Bug</strong> &ndash; Remove empty signature field from the default template.            </li>
              <li><strong>Bug </strong>&ndash; fixed problem with redirecting to login screen even when      logged in while accessing template file through the browser window directly. </li>
              <li><strong>Bug</strong> &ndash; fixed error where sample template would reimport itself      automatically even after deleting it. Will now only reimport if any      important changes to template need to be viewed straight after an update.</li>
              <li><strong>Bug </strong>&ndash; Moved render_to_pdf.php constants to pdf.php so we can      use the constants in the core files. Was previously generating an error.</li>
              <li><strong>Housekeeping</strong> &ndash; Cleaned up core template files, moved functions      into classes and added more in-file documentation. </li>
                <li><strong>Housekeeping </strong>&ndash; moved      install/upgrade code from pdf.php to installation-update-manager.php </li>
                <li><strong>Housekeeping &ndash; </strong>changed pdf-entry-detail.php class name      from GFEntryDetail to GFPDFEntryDetail to remove compatibility problems      with Gravity Forms.</li>
                <li><strong>Housekeeping</strong> &ndash; created pdf-settings.php file to house the settings page code.</li>
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
	}
}

?>