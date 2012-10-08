<?php

//For backwards compatibility, load wordpress if it hasn't been loaded yet
//Will be used if this file is being called directly
if(!class_exists("RGForms")){
    for ( $i = 0; $i < $depth = 10; $i++ ) {
        $wp_root_path = str_repeat( '../', $i );

        if ( file_exists("{$wp_root_path}wp-load.php" ) ) {
            require_once("{$wp_root_path}wp-load.php");
            require_once("{$wp_root_path}wp-admin/includes/admin.php");
            break;
        }
    }

    //redirect to the login page if user is not authenticated
    auth_redirect();
}

if(!GFCommon::current_user_can_any("gravityforms_view_entries") && !defined('GF_FORM_ID') )
    die(__("You don't have adequate permission to view entries.", "gravityforms"));

$form_id = (defined('GF_FORM_ID')) ? GF_FORM_ID : absint(rgget("fid"));
$lead_ids = (defined('GF_LEAD_ID')) ? array(GF_LEAD_ID) : explode(',', rgget("lid"));
$page_break = rgget("page_break") ? 'print-page-break' : false;

// sort lead IDs numerically
sort($lead_ids);

if(empty($form_id) || empty($lead_ids))
    die(__("Form Id and Lead Id are required parameters.", "gravityforms"));

$form = RGFormsModel::get_form_meta($form_id);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="MSSmartTagsPreventParsing" content="true" />
    <meta name="Robots" content="noindex, nofollow" />
    <meta http-equiv="Imagetoolbar" content="No" />
    <title>
        Print Preview :
        <?php echo $form["title"] ?> :
        <?php echo count($lead_ids) > 1 ? __("Entry # ", "gravityforms") . $lead_ids[0] : 'Bulk Print' ?>
    </title>
    <link rel='stylesheet' href='<?php echo GFCommon::get_base_url(); ?>/css/print.css' type='text/css' />
    </head>
	<body>

	<div id="print_preview_hdr" style="display:none"></div>
        
        
		<div id="view-container">

        <?php
		/* Add image and copy above here to show before the entry details */

        require_once(ABSPATH. "wp-content/plugins/gravity-forms-pdf-extended/pdf-entry-detail.php");

        foreach($lead_ids as $lead_id){

            $lead = RGFormsModel::get_lead($lead_id);
            do_action("gform_print_entry_header", $form, $lead);
            $form_data = GFEntryDetail::lead_detail_grid_array($form, $lead);

			/** Uncomment the following line when figuring out how to access the $form_data array */
			// print_r($form_data); exit();
						
			/* get all the form values */
			$first_name 		= $form_data['field']['Name']['first'];
			$last_name 			= $form_data['field']['Name']['last'];			
		
			$address_street 	= $form_data['field']['Address']['street'];			
			$address_city 		= $form_data['field']['Address']['city'];			
			$address_state 		= $form_data['field']['Address']['state'];			
			$address_zip 		= $form_data['field']['Address']['zip'];	
			$address_country	= $form_data['field']['Address']['country'];
			
			$phone 				= $form_data['field']['Phone'];
			/* format the template */						
			?>
            
          
           	<img src="<?php echo home_url() ?>/wp-content/plugins/gravity-forms-pdf-extended/images/BlueLiquidDesigns.jpg" width="265" height="72"  />
           
           
           <div class="body_copy">
		   
		   	<p class="date"><?php echo date('d/m/Y'); ?></p>
            
            <p class="client_address">
            	<?php echo $first_name .' '. $last_name; ?><br />
                <?php echo $address_street; ?><br />
                <?php echo $address_city .', '. $address_state .' '. $address_zip; ?><br />
                <?php echo $address_country; ?>
            </p>
            
            <p class="whom_concern_intro">Dear User,</p>

			<p class="body_text">The Gravity Forms PDF Extended plugin now allows you to directly access field data so you can create custom PDFs like this one. You'll need to copy the <em>example-template.php</em> file now located in the gravity-forms-pdf-extended/template/ directory and style it appropriatly. There's a <strong>print_r()</strong> statement you can uncomment that will help you access the $form_data array when customising the PDF template.</p>
            
            <p>To create a PDF with the new template file you'll need to change the <em>PDF_Generator</em> call in your <em>gform_pdf_create()</em> function, which should be inside your theme's functions.php file, otherwise the standard template will be called. </p>
            
            <p><strong>$filename = PDF_Generator($form_id, $user_id, 'save', true, 'your-new-template.php');</strong></p>
            
            <p>When testing your new template file you can use the <em>View PDF</em> button on an entry in the admin area and tack <strong>&amp;template=your-new-template.php</strong> onto the end of the url.</p>
            
            <p><strong>Example:</strong> http://www.yourdomain.com/?gf_pdf=print-entry&amp;fid=5&amp;lid=142&amp;notes=1&amp;template=example-template.php</p>
            
            <p><strong>Remember to backup your template files when upgrading as Wordpress will DELETE all old plugin files before installing the latest version. Once done, drop your custom template back in the template folder.</strong></p> 
            
            <p>As always, all documentation can be found via the <a href="http://www.blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/">Plugin's official website.</a></p>
            
            <br /><br />
            
            <p class="signature">
                Jake Jackson<br />
                <img src="<?php echo home_url(); ?>/wp-content/plugins/gravity-forms-pdf-extended/images/signature.png" alt="Signature" width="100" height="60" /><br />
                Developer, Blue Liquid Designs<br />
                <a href="http://www.blueliquiddesigns.com.au">www.blueliquiddesigns.com.au</a>
            </p>

            
           
           </div>
           
           <?php 

            if(rgget('notes')){
                $notes = RGFormsModel::get_lead_notes($lead["id"]);
                if(!empty($notes))
				{
                    GFEntryDetail::notes_grid($notes, false);
				}
            }

            // output entry divider/page break
            if(array_search($lead_id, $lead_ids) < count($lead_ids) - 1)
                echo '<div class="print-hr ' . $page_break . '"></div>';

            do_action("gform_print_entry_footer", $form, $lead);
            
			?>
            
         
            <?php
        }

        ?>
		</div>
	</body>
</html>