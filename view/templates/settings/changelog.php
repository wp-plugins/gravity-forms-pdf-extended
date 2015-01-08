<?php

 /*
  * Template: Changelog
  * Module: Settings Page
  *
  */
  
  /*
   * Don't run if the correct class isn't present
   */
  if(!class_exists('GFPDF_Settings_Model'))
  {
	 exit;  
  }
  
  ?>

    <h2><?php _e('Changelog'); ?></h2>
    
    <p><strong>Current Version: <?php echo PDF_EXTENDED_VERSION; ?></strong></p>
    
    <h3>3.4.0</h3>
    <ul>
    <li>Feature - Added auto-print prompt ability when you add &amp;print=1 to the PDF URL</li>
    <li>Feature - Added ability to rotate absolute positioned text 180 degrees (previously only 90 and -90). Note: feature in beta</li>
    <li>Feature - Backup all template files that are overridden when initialising to a folder inside PDF_EXTENDED_TEMPLATE</li>
    <li>Feature - Added SSH initialisation support</li>
    <li>Feature - Allow MERGETAGS to be used in all PDF template, including default template but only in the HTML field.</li>
    <li>Feature - Updated mPDF to 3.7.1</li>
    <li>Feature - Enable text/image watermark support. Added new example template example-watermark09.php showing off its usage</li>
    <li>Feature - Added Quiz support to $form_data array</li>
    <li>Feature - Shortcodes will now be processed in all templates, including default template but only in the HTML field.</li>
    <li>Feature - Added 'save' configuration option so PDFs are saved to the local disk when 'notifications' aren't enabled</li>
    <li>Feature - Added 'dpi' configuration option to modify the PDF image DPI. Default 96dpi. Use 300dpi for printing.</li>
    <li>Feature - Added PDF/A1-b compliance option. Enable with 'pdfa1b' => true. See <a href="http://mpdf1.com/manual/index.php?tid=420&searchstring=pdf/a1-b">http://mpdf1.com/manual/index.php?tid=420&amp;searchstring=pdf/a1-b</a> for more details.</li>
    <li>Feature - Added PDF/X1-a compliance option. Enable with 'pdfx1a' => true. See <a href="http://mpdf1.com/manual/index.php?tid=481&searchstring=pdf/x-1a">http://mpdf1.com/manual/index.php?tid=481&amp;searchstring=pdf/x-1a</a> for more details</li>
    <li>Feature - Added new constant option 'PDF_REPACK_FONT' which when enabled may improve function with some PostScript printers (disabled by default)</li>

    <li>Housekeeping - Modified mPDF functions Image() and purify_utf8_text() to validate the input data so we don't have to do it every time through the template.</li>
    <li>Housekeeping - Added ability to not re-deploy every update (not enabled this release as template files were all updated)</li>
    <li>Housekeeping - Additional checks on load to see if any of the required file/folder structure is missing. If so, re-initilise.</li>
    <li>Housekeeping - Save resources and turn off automatic rtl identification. Users must set the RTL option when configuring form</li>
    <li>Housekeeping - Turn off mPDFs packTableData setting, decreasing processing time when working with large tables.</li>
    <li>Housekeeping - $gf_pdf_default_configuration options now merge down into existing PDF nodes, instead of applying to only unassigned forms</li>
    <li>Housekeeping - Center aligned Survey Likery field results</li>
    <li>Housekeeping - Partially refactored the pdf-entry-detail.php code</li>
    <li>Housekeeping - All default and example templates have been tidied. This won't affect custom templates.</li>
    <li>Housekeeping - Set the gform_notification order number to 100 which will prevent other functions (example snippets from Gravity Forms, for instance) from overridding the attached PDF.</li>
    <li>Housekeeping - Fix spelling mistake on initialising fonts</li>

    <li>Bug - Fixed issue with PDF not attaching to notification using Paypal's delayed notification feature</li>
    <li>Bug - Fixed strict standard warning about calling GFPDF_Settings::settings_page();</li>
    <li>Bug - Fixed strict standard warning about calling GFPDFEntryDetail::pdf_get_lead_field_display();</li>
    <li>Bug - Fixed issue with Gravity Form Post Category field causing fatal error generating PDF</li>
    <li>Bug - Fixed number field formatting issue when displaying on PDF.</li>
    <li>Bug - Do additional check for PHP's MB_String regex functions before initialising</li>
    <li>Bug - Fixed problem with multiple nodes assigned to a form using the same template</li>
    <li>Bug - Fixed path to fallback templates when not found</li>
    </ul> 

    <h3>3.3.4</h3>
    <ul>
    	<li>Bug - Fixed issue linking to PDF from front end</li>
        <li>Housekeeping - Removed autoredirect to initialisation page</li>
    </ul>
    <h3>3.3.3</h3>
    <ul>
    	<li>Bug - Correctly call javascript to control admin area 'View PDFs' drop down</li>
        <li>Bug - Some users still reported incorrect RAM. Convert MB/KB/GB values to M/K/G as per the PHP documentation.</li>
        <li>Housekeeping - Show initilisation prompt on all admin area pages instead of only on the Gravity Forms pages</li>
    </ul>
    
 	<h3>3.3.2.1</h3>
    <ul>
    	<li>Bug - Incorrectly showing assigned RAM to website</li>
    </ul>
 
 	<h3>3.3.2</h3>
    <ul>
    	<li>Bug - Some hosts reported SSL certificate errors when using the support API. Disabled HTTPS for further investigation. Using hash-based verification for authentication.</li>
    	<li>Housekeeping - Forgot to disable API debug feature after completing beta</li>
    </ul>
 
    <h3>3.3.1</h3>
    <ul>
    	<li>Bug - $form_data['list'] was mapped using an incremental key instead of via the field ID</li>
    </ul>
 
    <h3>3.3.0</h3>
    <ul>
      <li>Feature - Overhauled  the initialisation process so that the software better reviews the host for  potential problems before initialisation. This should help debug issues and  make users aware there could be a problem <strong>before</strong> they begin using the software.</li>
      <li>Feature - Overhauled the settings page to make it easier to access features of the software</li>
      <li>Feature - Added a Support tab to the settings page which allows users to securely (over HTTPS) submit a support ticket to the Gravity Form PDF Extended support desk</li>
      <li>Feature - Changed select, multiselect and radio fields so that the default templates use the name rather than the value. $form_data now also includes the name and values for all these fields.</li>
      <li>Feature - $form_data now includes all miscellaneous lead information in the $form_data['misc'] array.</li>
      <li>Feature - $form_data now contains 24 and 12 hour time of entry submission.</li>      
      <li>Feature - Added localisation support</li>
      <li>Compatibility - Added new multi-upload support which was added in Gravity Forms 1.8.</li>
      <li>Bug - Added 'aid' parametre to the PDF url when multiple configuration nodes present on a single form</li>
      <li>Bug - Fixed issue when Gravity Forms in No Conflict Mode</li>
      <li>Bug - Font config.php's array keys now in lower case</li>
      <li>Housekeeping - Moved all initialisation files to a folder called 'initialisation'.</li>
      <li>Housekeeping - Renamed the configuration.php file in the plugin folder to configuration.php.example to alleviate confusion for developers who unwittingly modify the plugin configuration file instead of the file in their active theme's PDF_EXTENDED_TEMPLATE folder.</li>
      <li>Housekeeping - Updated the plugin file system to a more MVC-style approach, with model and view folders.</li>
      <li>Housekeeping - Removed ability to directly access default and example template files.</li>
      <li>Housekeeping - Fixed PHP notices in default templates related to the default template-only configuration options</li>
      <li>Housekeeping - Update core styles to match Wordpress 3.8/Gravity Forms 1.8.</li>
      <li>Housekeeping - Updated header/footer examples to use @page in example.</li>
      
    </ul> 
    
    <h3>3.2.0</h3>
    <ul>
      <li>Feature - Can now view multiple PDFs assigned to a single form via the admin area. Note: You must provide a unique 'filename' parameter in configuration.php for multiple PDFs assigned to a single form. </li>
      <li>Feature - You can exclude a field from the default templates using the class name 'exclude'. See our <a rel="nofollow" href="http://gravityformspdfextended.com/faq/can-exclude-field-showing-pdf/">FAQ topic</a> for more details.</li>
      <li>Bug - Fixed issue viewing own PDF entry when logged in as anything lower than editor.</li>
      <li>Bug - Fixed data return bug in pdf-entry-details.php that was preventing all data returning correctly.</li>
      <li>Bug - Fixed PHP Warning when using products with no options</li>
      <li>Bug - Fixed issue with invalid characters being added to the PDF filename. Most notably the date mergetag.</li>
      <li>Bug - Limit filename length to 150 characters which should work on the majority of web servers.</li>
      <li>Bug - Fixed problem sending duplicate PDF when using mass resend notification feature</li>
      <li>Depreciated - Removed GF_FORM_ID and GF_LEAD_ID constants which were used in v2.x.x of the software. Ensure you follow <a rel="nofollow" href="http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/">v2.x.x upgrade guide</a> to your templates before upgrading.</li>
    </ul>
    
    <h3>3.1.4</h3>
    <ul>
      <li>Bug - Fixed issue with plugin breaking website's when the Gravity Forms plugin wasn't activated.</li>
      <li>Housekeeping - The plugin now only supports Gravity Forms 1.7 or higher and WordPress 3.5 or higher.</li>
      <li>Housekeeping - PDF template files can no longer be accessed directly. Instead, add &amp;html=1 to the end of your URL when viewing a PDF.</li>
      <li>Extension - Added additional filters to allow the lead ID and notifications to be overridden.</li>
    </ul>
    
    <h3>3.1.3</h3>
    <ul>
      <li>Feature - Added signature_details_id to $form_data array which maps a signatures field ID to the array.</li>
      <li>Extension - Added pre-PDF generator filter for use with extensions.</li>
      <li>Bug - Fixed issue with quotes in entry data breaking custom templates.</li>
      <li>Bug - Fixed issue with the plugin not correctly using the new default configuration template, if set.</li>
      <li>Bug - Fixed issue with signature not being removed correctly when only testing with file_exists(). Added second is_dir() test.</li>
      <li>Bug - Fixed issue with empty signature field not displaying when option 'default-show-empty' is set.</li>
      <li>Bug - Fixed initialisation prompt issue when the MPDF package wasn't unpacked.</li>
    </ul>
    
    <h3>3.1.2</h3>
    <ul>
      <li>Feature - Added list array, file path, form ID and lead ID to $form_data array in custom templates</li>
      <li>Bug - Fixed initialisation prompt issue when updating plugin</li>
      <li>Bug - Fixed window.open issue which prevented a new window from opening when viewing a PDF in the admin area</li>
      <li>Bug - Fixed issue with product dropdown and radio button data showing the value instead of the name field.</li>
      <li>Bug - Fixed incorrect URL pointing to signature in $form_data</li>
    </ul>
    
    <h3>3.1.1</h3>
    <ul>
      <li>Bug - Users whose server only supports FTP file manipulation using the WP_Filesystem API moved the files into the wrong directory due to FTP usually being rooted to the WordPress home directory. To fix this the plugin attempts to determine the FTP directory, otherwise assumes it is the WP base directory. </li>
      <li>Bug - Initialisation error message was being called but the success message was also showing. </li>
    </ul>
    <h3>3.1.0</h3>
    <ul>
      <li>Feature - Added defaults to configuration.php which allows users to define the default PDF settings for all Gravity Forms. See the <a rel="nofollow" href="http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/#default-configuration-options">installation and configuration documentation</a> for more details. </li>
      <li>Feature - Added three new configuration options 'default-show-html', 'default-show-empty' and 'default-show-page-names' which allow different display options to the three default templates. See the <a rel="nofollow" href="http://gravityformspdfextended.com/documentation-v3-x-x/installation-and-configuration/#default-template-only">installation and configuration documentation</a> for more details.</li>
      <li>Feature - Added filter hooks 'gfpdfe_pdf_name' and 'gfpdfe_template' which allows developers to further modify a PDF name and template file, respectively, outside of the configuration.php. This is useful if you have a special case naming convention based on user input. See <a rel="nofollow" href="http://gravityformspdfextended.com/filters-and-hooks/">http://gravityformspdfextended.com/filters-and-hooks/</a> for more details about using these filters.</li>
      <li>Feature - Custom font support. Any .ttf font file added to the PDF_EXTENDED_TEMPLATE/fonts/ folder will be automatically installed once the plugin has been initialised. Users also have the option to just initialise the fonts via the settings page. See the <a rel="nofollow" href="http://gravityformspdfextended.com/documentation-v3-x-x/language-support/#installing-fonts">font/language documentation </a> for details.</li>
      <li>Compatability - Use Gravity Forms get_upload_root() and get_upload_url_root() instead of hard coding the signature upload directory in pdf-entry-detail.php</li>
      <li>Compatability - Changed depreciated functions get_themes() and get_theme() to wp_get_theme() (added in WordPress v3.4). </li>
      <li>Compatability - The plugin now needs to be initialised on fresh installation and upgrade. This allows us to use the WP_Filesystem API for file manipulation.</li>
      <li>Compatability - Automatic copying of PDF_EXTENDED_TEMPLATE folder on a theme change was removed in favour of a user prompt. This allows us to take advantage of the WP_Filesystem API.</li>
      <li>Compatability - Added WordPress compatibility checker (minimum now 3.4 or higher).</li>
      <li>Bug - Removed ZipArchive in favour of WordPress's WP_Filesystem API unzip_file() command. Some users reported the plugin would stop their entire website working if this extension wasn't installed.</li>
      <li>Bug - Fixed Gravity Forms compatibility checker which wouldn't return the correct response.</li>
      <li>Bug - Fixed minor bug in pdf.php when using static call 'self' in add_filter hook. Changed to class name.</li>
      <li>Bug - Removed PHP notice about $even variable not being defined in pdf-entry-detail.php</li>
      <li>Bug - Prevent code from continuing to excecute after sending header redirect.</li>
    </ul>
    <h3>3.0.2</h3>
    <ul>
      <li>Backwards Compatibility - While PHP 5.3 has was released a number of years ago it seems a number of hosts do not currently offer this version to their clients. In the interest of backwards compatibility we've re-written the plugin to again work with PHP 5+.</li>
      <li>Signature / Image Display Bug - All URLs have been converted to a path so images should now display correctly in PDF.</li>
    </ul>
    <h3>3.0.1</h3>
    <ul>
      <li>Bug - Fixed issue that caused website to become unresponsive when Gravity Forms was disabled or upgraded</li>
      <li>Bug - New HTML fields weren't being displayed in $form_data array</li>
      <li>Feature - Options for default templates to disable HTML fields or empty fields (or both)</li>
    </ul>
    <h3>3.0.0</h3>
    <p>As of Gravity Forms PDF Extended v3.0.0 we have removed the DOMPDF package from our plugin and integrated the more advanced mPDF system. Along with a new HTML to PDF generator, we've rewritten the entire plugin's base code to make it more user friendly to both hobbyists and rock star web developers. Configuration time is cut in half and advanced features like adding security features is now accessible to users who have little experience with PHP.</p>
    <p>New Features include:</p>
    <ul>
      <li>Language Support - almost all languages are supported including RTL (right to left) languages like Arabic and Hebrew and CJK languages - Chinese, Japanese and Korean.</li>
      <li>HTML Page Numbering</li>
      <li>Odd and even paging with mirrored margins (most commonly used in printing).</li>
      <li>Nested Tables</li>
      <li>Text-justification and hyphenation</li>
      <li>Table of Contents</li>
      <li>Index</li>
      <li>Bookmarks</li>
      <li>Watermarks</li>
      <li>Password protection</li>
      <li>UTF-8 encoded HTML</li>
      <li>Better system resource handling</li>
    </ul>
    <p>A new HTML to PDF package wasn't the only change to this edition of the software. We have rewritten the entire configuration system and made it super easy to get the software up and running.</p>
    <p>Users will no longer place code in their active theme's functions.php file. Instead, configuration will happen in a new file called configuration.php, inside the PDF_EXTENDED_TEMPLATES folder (in your active theme).</p>
    <p>Other changes include
      * Improved security - further restrictions were placed on non-administrators viewing template files.
      * $form_data array tidied up - images won't be wrapped in anchor tags.</p>
    <p>For more details <a rel="nofollow" href="http://gravityformspdfextended.com/documentation-v3-x-x/introduction/">view the 3.x.x online documentation</a>.</p>
    <h3>2.2.3</h3>
    <ul>
      <li>Bug - Fixed mb_string error in the updated DOMPDF package.</li>
    </ul>
    <h3>2.2.2</h3>
    <ul>
      <li>DOMPDF - We updated to the latest version of DOMPDF - DOMPDF 0.6.0 beta 3.</li>
      <li>DOMPDF - We've enabled font subsetting by default which should help limit the increased PDF size when using DejaVu Sans (or any other font). </li>
    </ul>
    <h3>2.2.1</h3>
    <ul>
      <li>Bug - Fixed HTML error which caused list items to distort on PDF</li>
    </ul>
    <h3>2.2.0</h3>
    <ul>
      <li>Compatibility - Ensure compatibility with Gravity Forms 1.7. We've updated the functions.php code and remove gform_user_notification_attachments and gform_admin_notification_attachments hooks which are now depreciated. Functions gform_pdf_create and gform_add_attachment have been removed and replaced with gfpdfe_create_and_attach_pdf(). See upgrade documentation for details.</li>
      <li>Enhancement - Added deployment code switch so the template redeployment feature can be turned on and off. This release doesn't require redeployment.</li>
      <li>Enhancement - PDF_Generator() variables were getting long and complex so the third variable is now an array which will pass all the optional arguments. The new 1.7 compatible functions.php code includes this method by default. For backwards compatibility the function will still work with the variable structure prior to 2.2.0.</li>
      <li>Bug - Fixed error generated by legacy code in the function PDF_processing() which is located in render_to_pdf.php.</li>
      <li>Bug - Images and stylesheets will now try and be accessed with a local path instead of a URL. It fixes problem where some hosts were preventing read access from a URL. No template changes are required.</li>
    </ul>
    <h3>2.1.1</h3>
    <ul>
      <li>Bug - Signatures stopped displaying after 2.1.0 update. Fixed issue. </li>
      <li>Bug - First time install code now won't execute if already have configuration variables in database</li>
    </ul>
    <h3>2.1.0</h3>
    <ul>
      <li>Feature - Product table can now be accessed directly through custom templates by running GFPDFEntryDetail::product_table($form, $lead);. See documentation for more details.</li>
      <li>Feature - Update screen will ask you if you want to deploy new template files, instead of overriding your modified versions.</li>
      <li>Feature - Product subtotal, shipping and total have been added to $form_data['field'] array to make it easier to work with product details in the custom template.</li>
      <li>Feature - Added two new default template files. One displays field and name in two rows (like you see when viewing an entry in the admin area) and the other removes all styling. See documentation on use.</li>
      <li>Security - Tightened PDF template security so that custom templates couldn't be automatically generated by just anyone. Now only logged in users with the correct privileges and the user who submitted the form (matched against IP) can auto generate a PDF. See documentation on usage.</li>
      <li>Depreciated - Removed form data that was added directly to the $form_data array instead of $form_data['field'] array. Users upgrading will need to update their custom templates if not using field data from the $form_data[�field'] array. If using $form_data['field'] in your custom template this won't affect you.</li>
      <li>Bug - Fixed problem with default template not showing and displaying a timeout error. Removed table tags and replaced with divs that are styled appropriately.</li>
      <li>Bug - The new plugin theme folder will successfully create when upgrading. You won't have to deactivate and reactivate to get it working.</li>
      <li>Bug - some installs had plugins that included the function mb_string which is also included in DOMPDF. DOMPDF will now check if the function exists before creating it.</li>
      <li>Bug - Remove empty signature field from the default template.</li>
      <li>Bug - fixed problem with redirecting to login screen even when logged in while accessing template file through the browser window directly.</li>
      <li>Bug - fixed error where sample template would reimport itself automatically even after deleting it. Will now only reimport if any important changes to template need to be viewed straight after an update.</li>
      <li>Bug - Moved render_to_pdf.php constants to pdf.php so we can use the constants in the core files. Was previously generating an error.</li>
      <li>Housekeeping - Cleaned up core template files, moved functions into classes and added more in-file documentation.</li>
      <li>Housekeeping - moved install/upgrade code from pdf.php to installation-update-manager.php</li>
      <li>Housekeeping - changed pdf-entry-detail.php class name from GFEntryDetail to GFPDFEntryDetail to remove compatibility problems with Gravity Forms.</li>
      <li>Housekeeping - created pdf-settings.php file to house the settings page code.</li>
    </ul>
    <h3>2.0.1</h3>
    <ul>
      <li>Fixed Signature bug when checking if image file exists using URL instead of filesystem path</li>
      <li>Fixed PHP Constants Notice </li>
    </ul>
    <h3>2.0.0</h3>
    <ul>
      <li>Moved templates to active theme folder to prevent custom themes being removed on upgrade</li>
      <li>Allow PDFs to be saved using a custom name</li>
      <li>Fixed WP_Error bug when image/css file cannot be found</li>
      <li>Upgraded to latest version of DOMPDF</li>
      <li>Removed auto-load form bug which would see multiple instances of the example form loaded</li>
      <li>Created a number of constants to allow easier developer modification</li>
      <li>Plugin/Support moved to dedicated website.</li>
      <li>Pro/Business package offers the ability to write fields on an existing PDF.</li>
    </ul>
    <h3>1.2.3</h3>
    <ul>
      <li>Fixed $wpdb-&gt;prepare error</li>
    </ul>
    <h3>1.2.2</h3>
    <ul>
      <li>Fixed bug with tempalte shipping method MERGETAGS</li>
      <li>Fixed bug where attachment wasn't being sent</li>
      <li>Fixed problem when all_url_fopen was turned off on server and failed to retreive remote images. Now uses WP_HTTP class.</li>
    </ul>
    <h3>1.2.1</h3>
    <ul>
      <li>Fixed path to custom css file included in PDF template </li>
    </ul>
    <h3>1.2.0</h3>
    <ul>
      <li>Template files moved to the plugin's template folder</li>
      <li>Sample Form installed so developers have a working example to modify</li>
      <li>Fixed bug when using WordPress in another directory to the site</li>
    </ul>
    <h3>1.1.0</h3>
    <ul>
      <li>Now compatible with Gravity Forms Signature Add-On</li>
      <li>Moved the field data functions out side of the Gravity Forms core so users can freely style their form information (located in pdf-entry-detail.php)</li>
      <li>Simplified the field data output</li>
      <li>Fixed bug when using product information</li>
    </ul>
    <h3>1.0.0</h3>
    <ul>
      <li>First release.</li>
    </ul>

