<?php

/**
 * If the template is being loaded directy we'll call the Wordpress Core 
 * Used when attempting to debug the template
 */ 
if(!class_exists("RGForms")){
    for ( $i = 0; $i < $depth = 10; $i++ ) {
        $wp_root_path = str_repeat( '../', $i );

        if ( file_exists("{$wp_root_path}wp-load.php" ) ) {
            require_once("{$wp_root_path}wp-load.php");
            require_once("{$wp_root_path}wp-admin/includes/admin.php");
            break;
        }
    }

   /*stop the script if user isn't logged in*/
   if(!is_user_logged_in())
   {
	    echo 'You need to be logged in to view this document';
		exit();   
   }
}

/**
 * Added security measure. If the user is logged in but doesn't have permission to view entries then the PDF won't be displayed. 
 */
if(!GFCommon::current_user_can_any("gravityforms_view_entries") && !defined('GF_FORM_ID') )
{
    die(__("You don't have adequate permission to view entries.", "gravityforms"));
}

/** 
 * Set up the form ID and lead ID, as well as we want page breaks displayed. 
 * Form ID and Lead ID can be set by passing it to the URL - ?fid=1&lid=10
 * or by two constants GF_FORM_ID and GF_LEAD_ID which is set in load_entry_data($form_id, $lead_id, $template, $fpdf)
 * in render_to_pdf.php which allows PDFs to be generated publicly through gform_pdf_create() and gform_add_attachment() functions
 * which intergrates directly with Gravity Forms through the plugin's hooks. 
 */
$form_id = (defined('GF_FORM_ID')) ? GF_FORM_ID : absint(rgget("fid"));
$lead_ids = (defined('GF_LEAD_ID')) ? array(GF_LEAD_ID) : explode(',', rgget("lid"));
$page_break = rgget("page_break") ? 'print-page-break' : false;

/**
 * If form ID and lead ID hasn't been set stop the PDF from attempting to generate
 */
if(empty($form_id) || empty($lead_ids))
{
    die(__("Form Id and Lead Id are required parameters.", "gravityforms"));
}

/**
 * Load the form data, including the custom style sheet which looks in the plugin's theme folder before defaulting back to the plugin's file.
 */
$form = RGFormsModel::get_form_meta($form_id);
$stylesheet_location = (file_exists(PDF_TEMPLATE_LOCATION.'template.css')) ? PDF_TEMPLATE_URL_LOCATION.'template.css' : PDF_PLUGIN_URL .'styles/template.css' ;


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <link rel='stylesheet' href='<?php echo GFCommon::get_base_url(); ?>/css/print.css' type='text/css' />
    <link rel='stylesheet' href='<?php echo $stylesheet_location; ?>' type='text/css' />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>
	<body class="two-row">
       
		<div id="view-container">
        
        <?php
		/* Add image and copy above here to show before the entry details */

        require_once(ABSPATH. "wp-content/plugins/gravity-forms-pdf-extended/pdf-entry-detail.php");

        foreach($lead_ids as $lead_id) {

            $lead = RGFormsModel::get_lead($lead_id);

            do_action("gform_print_entry_header", $form, $lead);

            GFPDFEntryDetail::lead_detail_grid($form, $lead);

            if(rgget('notes')){
                $notes = RGFormsModel::get_lead_notes($lead["id"]);
                if(!empty($notes))
                    GFPDFEntryDetail::notes_grid($notes, false);
            }

            // output entry divider/page break
            if(array_search($lead_id, $lead_ids) < count($lead_ids) - 1)
			{
                echo '<div class="print-hr ' . $page_break . '"></div>';
			}

            do_action("gform_print_entry_footer", $form, $lead);
        }

        ?>
		</div>
	</body>
</html>