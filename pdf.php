<?php

/*
Plugin Name: Gravity Forms PDF Extended
Plugin URI: http://www.blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/
Description: Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. Note: If you're upgrading, backup your plugin files beforehand as your custom template will be overridden.
Version: 1.2.0
Author: Blue Liquid Designs
Author URI: http://www.blueliquiddesigns.com.au

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

*/

/*
 * Handles the admin area routing 
 * See render_to_pdf.php for PDF Output Functions
 */

add_action('gform_entries_first_column_actions', 'pdf_link', 10, 4);
add_action("gform_entry_info", "detail_pdf_link", 10, 2);
add_action('wp',   'process_exterior_pages');
register_activation_hook( __FILE__, 'pdf_extended_activate' );


add_action('admin_init',  'gfe_admin_init', 9);
add_action("gform_entry_created", "gform_pdf_example_create", 10, 2);
add_filter("gform_admin_notification_attachments", 'gform_add_example_attachment', 10, 3);


/*
 * Generate our Sample PDF for our Sample Form   
 */
function gform_pdf_example_create($entry, $form)
{
	$user_id = $entry['id'];
	$form_id = $entry['form_id'];
		
	if($form['title'] == 'Gravity Forms PDF Extended Custom Template Sample')
	{
		/* include the pdf processing file */
		require ABSPATH. 'wp-content/plugins/gravity-forms-pdf-extended/render_to_pdf.php';
                /* generate and save the PDF file*/
		$filename = PDF_Generator($form_id, $user_id, 'save', true, 'example-template.php');	
	}
}

/* Emails our sample PDF to the site administrator */
function gform_add_example_attachment($attachments, $lead, $form){
 
	$form_id = $lead['form_id'];
	$user_id = $lead['id'];
	$attachments = array();
 
	if($form['title'] == 'Gravity Forms PDF Extended Custom Template Sample')
	{
		/* include PDF converter plugin */
		include ABSPATH. 'wp-content/plugins/gravity-forms-pdf-extended/render_to_pdf.php';
		$attachment_file = PDF_SAVE_LOCATION. get_pdf_filename($form_id, $user_id);
		$attachments[] = $attachment_file;
	}
 
    return $attachments;
}

/**
 * Check to see if Gravity Forms is actually installed
 */
function gfe_admin_init()
{
	if(!class_exists("RGForms"))
	{
		/* throw error to the admin notice bar */
		add_action('admin_notices', 'gf_not_installed'); 	
	}
	
	if(get_option('gf_pdf_extended_sample') != 'installed')
	{
		pdf_extended_active();
		update_option('gf_pdf_extended_sample', 'installed');
	}
}

/**
 * Install an example form to show off the new template system
 */
function pdf_extended_activate()
{
	GFExport::import_file(ABSPATH .'/wp-content/plugins/gravity-forms-pdf-extended/example-form.xml');
}

/**
 * Gravity Forms hasn't been installed so throw error.
 * We make sure the user hasn't already dismissed the error
 */
function gf_not_installed()
{
	global $current_user;
    $user_id = $current_user->ID;
    
	/* Check that the user hasn't already clicked to ignore the message */
    if ( ! get_user_meta($user_id, 'gfpdfe_ignore_notice') ) {
		// Shows as an error message. You could add a link to the right page if you wanted.
		echo '<div id="message" class="error"><p>';
		printf(__('You need to install <a href="http://www.gravityforms.com/">Gravity Forms</a> to use the Gravity Forms PDF Extended Plugin. | <a href="%1$s">Hide Notice</a>'), '?gfpdfe_nag_ignore=1');
		echo '</p></div>';
	}
}

/**
 * Gravity Forms hasn't been installed and user is dismissing the error thrown
 */
add_action('admin_init', 'gfpdfe_nag_ignore');
function gfpdfe_nag_ignore() {
    global $current_user;
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if ( isset($_GET['gfpdfe_nag_ignore']) && $_GET['gfpdfe_nag_ignore'] == 1 ) {
          add_user_meta($user_id, 'gfpdfe_ignore_notice', 'true', true);
    }
}

//Link for Entry Detail View (Provide both View Link and Download)
function detail_pdf_link($form_id, $lead) {
  $lead_id = $lead['id'];
  echo "PDF:  ";
  echo "<a href=\"javascript:;\" onclick=\"var notes_qs = jQuery('#gform_print_notes').is(':checked') ? '&notes=1' : ''; var url='".home_url()."/?gf_pdf=print-entry&fid=".$form_id."&lid=".$lead_id."' + notes_qs; window.open (url,'printwindow');\" class=\"button\"> View</a>";
  echo " <a href=\"javascript:;\" onclick=\"var notes_qs = jQuery('#gform_print_notes').is(':checked') ? '&notes=1' : ''; var url='".home_url()."/?gf_pdf=print-entry&download=1&fid=".$form_id."&lid=".$lead_id."' + notes_qs; window.open (url,'printwindow');\" class=\"button\"> Download</a>";
}

// Made this first... figured i would leave it in.  View link on the Entry list view. 
function pdf_link($form_id, $field_id, $value, $lead) {
  $lead_id = $lead['id'];
  echo "| <a href=\"javascript:;\" onclick=\"var notes_qs = '&notes=1'; var url='".home_url()."/?gf_pdf=print-entry&fid=".$form_id."&lid=".$lead_id."' + notes_qs; window.open (url,'printwindow');\"> View PDF</a>";
}

//Handle Incoming route.   Look for GF_PDF namespace 
function process_exterior_pages(){
  global $wpdb;
  
  if(rgempty("gf_pdf", $_GET))
    return;
    
	$form_id = $_GET['fid'];
	$lead_id = $_GET['lid'];
	$ip = $_GET['ip'];
	$template = (rgempty('template', $_GET)) ? 'pdf-print-entry.php' : rgget('template');
	
	/* check the lead is in the database and the IP address matches (little security booster) */
	$form_entries = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM `".$wpdb->prefix."rg_lead` WHERE form_id = ".$form_id." AND status = 'active' AND id = ".$lead_id." AND ip = '".$ip."'" ) )	;	

  //ensure users are logged in
  if(!is_user_logged_in() && !rgempty('template', $_GET) && $form_entries == 0)
    auth_redirect();

  switch(rgget("gf_pdf")){
    case "print-entry" :
    require_once("render_to_pdf.php");
	/* call the creation class */
	$output = ($_GET['download'] == 1) ? 'download' : 'view';
	PDF_Generator((int) $_GET['fid'], (int) $_GET['lid'], $output, false, $template);
    break;
  }
  exit();
}



?>