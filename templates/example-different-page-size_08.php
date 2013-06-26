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
   
	/**
	 * Added security measure. If the user is logged in but doesn't have permission to view entries then the PDF won't be displayed. 
	 */
	if(!GFCommon::current_user_can_any("gravityforms_view_entries") && !defined('GF_FORM_ID') )
	{
		die(__("You don't have adequate permission to view entries.", "gravityforms"));
	}   
}

/** 
 * Set up the form ID and lead ID, as well as we want page breaks displayed. 
 * Form ID and Lead ID can be set by passing it to the URL - ?fid=1&lid=10
 */
 PDF_Common::setup_ids();

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
   
    <title>Gravity Forms PDF Extended</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
	<body>
        <?php	

        foreach($lead_ids as $lead_id) {

            $lead = RGFormsModel::get_lead($lead_id);
            do_action("gform_print_entry_header", $form, $lead);
            $form_data = GFPDFEntryDetail::lead_detail_grid_array($form, $lead);
			/*
			 * Add &data=1 when viewing the PDF via the admin area to view the $form_data array
			 */
			PDF_Common::view_data($form_data);				
						
			/* get all the form values */
			/*$date_created		= $form_data['date_created'];
			
			$first_name 		= $form_data['1.Name']['first'];
			$last_name 			= $form_data['1.Name']['last'];*/			
		
			/* format the template */						
			?>                   
   <p>This should print on an A4 (portrait) sheet</p>
   
   <pagebreak sheet-size="A4-L" />
   <p>This page appears after the A4 portrait sheet and should print on an A4 (landscape) sheet</p>
   	<h1>mPDF Page Sizes</h1>
	<h3>Changing page (sheet) sizes within the document</h3>   
   <pagebreak sheet-size="A5-L" />
   
   <p>This should print on an A5 (landscape) sheet</p>
   	<h1>mPDF Page Sizes</h1>
	<h3>Changing page (sheet) sizes within the document</h3>   

            <?php
        }

        ?>
	</body>
</html>