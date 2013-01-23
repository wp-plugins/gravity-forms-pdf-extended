<?php

/*
Plugin Name: Gravity Forms PDF Extended
Plugin URI: http://www.gravityformspdfextended.com
Description: Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. Our Business Plus package also allows you to overlay field onto an existing PDF.
Version: 2.0.0
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

if(!defined('PDF_PLUGIN_DIR'))
{
	define('PDF_PLUGIN_DIR', ABSPATH. 'wp-content/plugins/gravity-forms-pdf-extended/');
}

if(!defined('GF_PDF_SUPPORTED_VERSION'))
{
	define('GF_PDF_SUPPORTED_VERSION', '1.6.0');
}

add_action('admin_init',  'gfe_admin_init', 9);
add_action("gform_entry_created", "gform_pdf_example_create", 15, 2);
add_filter("gform_admin_notification_attachments", 'gform_add_example_attachment', 10, 3);
add_filter("gform_user_notification_attachments", 'gform_add_example_attachment', 10, 3);

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
		require PDF_PLUGIN_DIR.'render_to_pdf.php';
        /* generate and save the PDF using a custom name*/
		$filename = PDF_Generator($form_id, $user_id, 'save', true, 'example-template.php');	
	}
}

/* Emails our sample PDF to the site administrator */
function gform_add_example_attachment($attachments, $lead, $form) {
 
	$form_id = $lead['form_id'];
	$user_id = $lead['id'];
	$attachments = (is_array($attachments)) ? $attachments : array();
	$folder_id = $form_id.$user_id.'/';
 
	if($form['title'] == 'Gravity Forms PDF Extended Custom Template Sample')
	{
		/* include PDF converter plugin */
		include PDF_PLUGIN_DIR.'render_to_pdf.php';
		$attachment_file = PDF_SAVE_LOCATION. $folder_id . get_pdf_filename($form_id, $user_id);
		$attachments[] = $attachment_file;
		return $attachments;
	}  
}

/**
 * Check to see if Gravity Forms is actually installed
 */
function gfe_admin_init()
{

	if(!class_exists("RGForms"))
	{ 
		/* throw error to the admin notice bar */
		add_action('admin_notices', 'gf_pdf_not_installed'); 
		return;
	}
	
	if(!gform_pdf_is_gravityforms_supported())
	{
		add_action('admin_notices', 'gf_pdf_not_supported'); 	
		return;
	}
	
	if(get_option('gf_pdf_extended_sample') != 'installed')
	{
		pdf_extended_activate();
	}
}

/**
 * Check to see if Gravity Forms version is supported
 */
function gform_pdf_is_gravityforms_supported(){
	if(class_exists("GFCommon"))
	{
		$is_correct_version = version_compare(GFCommon::$version, GF_PDF_SUPPORTED_VERSION, ">=");
		return $is_correct_version;
	}
	else 
	{
		return false;
	}
}

/**
 * Install an example form to show off the new template system
 */
function pdf_extended_activate()
{
	if(!class_exists("RGForms"))
	{ 
		/* throw error to the admin notice bar */
		echo '<span style="font-family: Arial, sans-serif; font-size: 12px;">Gravity Forms needs to be installed to use this plugin. <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">Purchase a copy of Gravity Forms now</a>.</span>';
		exit;
	}
	
	if(!gform_pdf_is_gravityforms_supported())
	{
		echo '<span style="font-family: Arial, sans-serif; font-size: 12px;">You need at least version 1.6.0 of Gravity Forms to use this plugin. <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">Upgrade Gravity Forms now</a>.</span>';
		exit;	
	}	

	/* preload example pdf form into database if it isn't installed already */
	if(!RGFormsModel::get_form_id('Gravity Forms PDF Extended Custom Template Sample'))
	{
		GFExport::import_file(PDF_PLUGIN_DIR . 'example-form.xml');
	}
	
	include PDF_PLUGIN_DIR.'render_to_pdf.php';
	
	/* create new directory in active themes folder*/	
	if(!is_dir(PDF_TEMPLATE_LOCATION))
	{
		if(mkdir(PDF_TEMPLATE_LOCATION) === false)
		{
			add_action('admin_notices', 'gf_pdf_template_dir_err'); 	
			return;
		}
	}

	if(!is_dir(PDF_SAVE_LOCATION))
	{
		/* create new directory in active themes folder*/	
		if(mkdir(PDF_SAVE_LOCATION) === false)
		{
			add_action('admin_notices', 'gf_pdf_template_dir_err'); 	
			return;
		}
	}
	
	if(!file_exists(PDF_TEMPLATE_LOCATION.'pdf-print-entry.php'))
	{
		/* copy template files to new directory */
		if(!copy(PDF_PLUGIN_DIR . 'templates/pdf-print-entry.php', PDF_TEMPLATE_LOCATION.'pdf-print-entry.php'))
		{
			add_action('admin_notices', 'gf_pdf_template_move_err'); 	
			return;
		}	
	}
	
	if(!file_exists(PDF_TEMPLATE_LOCATION.'example-template.php'))
	{
		/* copy template files to new directory */
		if(!copy(PDF_PLUGIN_DIR .'templates/example-template.php', PDF_TEMPLATE_LOCATION.'example-template.php'))
		{
			add_action('admin_notices', 'gf_pdf_template_move_err'); 	
			return;
		}
	}
	
	if(!file_exists(PDF_TEMPLATE_LOCATION.'template.css'))
	{
		/* copy template files to new directory */
		if(!copy(PDF_PLUGIN_DIR .'template.css', PDF_TEMPLATE_LOCATION.'template.css'))
		{
			add_action('admin_notices', 'gf_pdf_template_move_err'); 	
			return;
		}
	}	

	if(!file_exists(PDF_SAVE_LOCATION.'.htaccess'))
	{		
		if(!file_put_contents(PDF_SAVE_LOCATION.'.htaccess', 'deny from all'))
		{
			add_action('admin_notices', 'gf_pdf_template_move_err'); 	
			return;
		}	
	}
	
	/* update system to ensure everything is installed correctly */
	update_option('gf_pdf_extended_sample', 'installed');
}

/**
 * Gravity Forms hasn't been installed so throw error.
 * We make sure the user hasn't already dismissed the error
 */
function gf_pdf_not_installed()
{
	echo '<div id="message" class="error"><p>';
	echo 'You need to install <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">Gravity Forms</a> to use the Gravity Forms PDF Extended Plugin.';
	echo '</p></div>';
}

/**
 * The Gravity Forms version isn't compatible. Prompt user to upgrade
 */
function gf_pdf_not_supported()
{
		echo '<div id="message" class="error"><p>';
		echo 'Gravity Forms PDF Extended only works with Gravity Forms version '.GF_PDF_SUPPORTED_VERSION.' and higher. Please <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">upgrade your copy of Gravity Forms</a> to use this plugin.';
		echo '</p></div>';	
}
							

/**
 * Cannot create new template folder in active theme directory
 */
function gf_pdf_template_dir_err()
{
		echo '<div id="message" class="error"><p>';
		echo 'We could not create a template folder in your active theme\'s directory. Please created a folder called <strong>\'PDF_EXTENDED_TEMPLATES\'</strong> and place it in '.get_stylesheet_directory().'/. Then copy the contents of '.ABSPATH .'/wp-content/plugins/gravity-forms-pdf-extended/templates/ to your newly-created PDF_EXTENDED_TEMPLATES folder. You should also make this directory writable.';
		echo '</p></div>';

}

/**
 * Cannot create new template folder in active theme directory
 */
function gf_pdf_template_move_err()
{
		echo '<div id="message" class="error"><p>';
		echo 'We could not copy the contents of '.ABSPATH .'/wp-content/plugins/gravity-forms-pdf-extended/templates/ to your newly-created PDF_EXTENDED_TEMPLATES folder. Please manually copy the files to the aforementioned directory. You should also make this directory writable.';
		echo '</p></div>';

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
	$form_entries = $wpdb->get_var( $wpdb->prepare("SELECT count(*) FROM `".$wpdb->prefix."rg_lead` WHERE form_id = ".$form_id." AND status = 'active' AND id = ".$lead_id." AND ip = '".$ip."'", array() ) )	;	

  //ensure users are logged in
  if(!is_user_logged_in() && !rgempty('template', $_GET) && $form_entries == 0)
    auth_redirect();

  switch(rgget("gf_pdf")){
    case "print-entry" :
	/* include the pdf processing file */
	require PDF_PLUGIN_DIR.'render_to_pdf.php';
	
	/* call the creation class */
	$output = ($_GET['download'] == 1) ? 'download' : 'view';
	PDF_Generator((int) $_GET['fid'], (int) $_GET['lid'], $output, false, $template);
    break;
  }
  exit();
}

?>