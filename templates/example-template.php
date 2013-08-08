<?php

/**
 * Debugging can be done by adding &html=1 to the end of the URL when viewing the PDF
 * We no longer need to access the file directly.
 */ 
if(!class_exists('RGForms') ) {
	/* Accessed directly */
    exit;
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
    
    <link rel='stylesheet' href='<?php echo $stylesheet_location; ?>' type='text/css' />
    <title>Gravity Forms PDF Extended</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
	<body>
        <?php	

        foreach($lead_ids as $lead_id) {

            $lead = RGFormsModel::get_lead($lead_id);
            $form_data = GFPDFEntryDetail::lead_detail_grid_array($form, $lead);
			/*
			 * Add &data=1 when viewing the PDF via the admin area to view the $form_data array
			 */
			PDF_Common::view_data($form_data);				
						
			/* get all the form values */
			/*$date_created		= $form_data['date_created'];
			
			$first_name 		= $form_data['1.Name']['first'];
			$last_name 			= $form_data['1.Name']['last'];			
		
			$address_street 	= $form_data['2.Address']['street'];			
			$address_city 		= $form_data['2.Address']['city'];			
			$address_state 		= $form_data['2.Address']['state'];			
			$address_zip 		= $form_data['2.Address']['zip'];	
			$address_country	= $form_data['2.Address']['country'];
			
			$phone 				= $form_data['3.Phone'];*/
			
			/* format the template */						
			?>            
          
           	<img src="<?php echo PDF_PLUGIN_DIR ?>images/gravityformspdfextended.jpg" width="311" height="110"  />
           
           
           <div class="body_copy">
		   
		   	<p class="date"><?php echo $date_created; ?></p>
            
            <p class="client_address">
            	<?php if(strlen($first_name) > 0) { ?>
            	<?php echo $first_name .' '. $last_name; ?><br />
            	<?php } ?>                
                <?php if(strlen($address_street) > 0) { ?>
                <?php echo $address_street; ?><br />
                <?php echo $address_city .', '. $address_state .' '. $address_zip; ?><br />
                <?php echo $address_country; ?>
                <?php } ?>
            </p>
            
            <p class="whom_concern_intro">Dear User,</p>
			
            <p>Anything you put here will output to the PDF</p>			
            
            
            <br /><br />
            
            <p class="signature">
                Jake Jackson<br />
                <img src="<?php echo PDF_PLUGIN_DIR ?>/images/signature.png" alt="Signature" width="100" height="60" /><br />
                Developer, Gravity Forms PDF Extended<br />
                <a href="http://www.gravityformspdfextended.com">www.gravityformspdfextended.com</a>
            </p>
           
           </div>

         
            <?php
        }

        ?>
	</body>
</html>