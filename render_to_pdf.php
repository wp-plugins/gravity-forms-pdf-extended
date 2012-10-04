<?php
// Handy trick to let us run the default print view and give us the processed string... it's all we want really. 
if (!function_exists('GetRequire')) {
	function GetRequire($sFilename) {
	  ob_start();
	  require($sFilename);
	  $sResult = ob_get_contents();
	  ob_end_clean();
	  return $sResult;
	  return ob_get_clean();
	}
}

if(!defined('PDF_SAVE_LOCATION'))
{
	define('PDF_SAVE_LOCATION', dirname(__FILE__)."/output/");
}

/**
 * Outputs a PDF entry from a Gravity Form
 * var $form_id integer: The form id
 * var $lead_id integer: The entry id
 * var $output string: either view, save or download
 * save will save a copy of the PDF to the server using the PDF_SAVE_LOCATION constant
 * var $return boolean: if set to true it will return the path of the saved PDF
 * var $template string: if you want to use multiple PDF templates - name of the template file
 */
 if (!function_exists('PDF_Generator')) {
	function PDF_Generator($form_id, $lead_id, $output = 'view', $return = false, $template = 'pdf-print-entry.php')
	{
		$filename = get_pdf_filename($form_id, $lead_id); 
		$entry = load_entry_data($form_id, $lead_id, $template);	
		PDF_processing($entry, $filename, $output);
		/* return the filename so we can use it */
		if($return)
		{
			return PDF_SAVE_LOCATION. $filename;
		}
	}
 }

/**
 * Get the name of the PDF based on the Form and the submission
 */
 if (!function_exists('get_pdf_filename')) {
	function get_pdf_filename($form_id, $lead_id)
	{
		return "form-$form_id-entry-$lead_id.pdf";
	}
 }

/**
 * Loads the Gravity Form output script (actually the print preview)
 */
 if (!function_exists('load_entry_data')) {
	function load_entry_data($form_id, $lead_id, $template)
	{
		/* set up contstants for gravity forms to use so we can override the security on the printed version */
		define('GF_FORM_ID', $form_id);
		define('GF_LEAD_ID', $lead_id);	
		return GetRequire(dirname(__FILE__)."/templates/" . $template);
	}
 }

/**
 * Creates the PDF and does a specific output (see PDF_Generator function above for $output variable types)
 */
 if (!function_exists('PDF_processing')) {
	function PDF_processing($entry, $filename, $output = 'view')
	{
		//Parse the default print view from Gravity forms so we can play with it.
		$DOM = new DOMDocument;
		$DOM->loadHTML($entry);
		
		//Make Stylesheets/Images Absolute
		/*$stylesheets = $DOM->getElementsByTagName('link');
		
		foreach($stylesheets as $stylesheet){
		  $href = $stylesheet->getAttribute('href');
		  if(strpos($src, site_url()) !== 0){
			$stylesheet->setAttribute('href', site_url()."$href");
		  }
		}*/
		
		/* Problem pulling the site style so manually set */
		
		$imgs = $DOM->getElementsByTagName('img');
		foreach($imgs as $img){
		  $src = $img->getAttribute('src');
		  if(strpos($src, site_url()) !== 0){
			$img->setAttribute('src', site_url()."$src");
		  }
		}
		
		//Remove Ugly Header
		$xpath = new DOMXPath($DOM);
		$nlist = $xpath->query("//div[@id='print_preview_hdr']");
		$node = $nlist->item(0);
		$node->parentNode->removeChild($node);
		$entry = $DOM->saveHTML();
		
		//Load the DOMPDF Engine to render the PDF
		require_once("dompdf/dompdf_config.inc.php");
		$dompdf = new DOMPDF();
		$dompdf -> load_html($entry);
		$dompdf -> set_base_path(site_url());
		$dompdf -> render();	
		
		switch($output)
		{
			case 'download':
				 $dompdf -> stream($filename, array("Attachment" => true));
				 exit(0);
			break;
			
			case 'view':
				 $dompdf -> stream($filename, array("Attachment" => false));
				 exit(0);
			break;
			
			case 'save':
				savePDF($dompdf, $filename);
			break;
		}
	}
 }

/**
 * Creates the PDF and does a specific output (see PDF_Generator function above for $output variable types)
 * var $dompdf Object
 */
  if (!function_exists('savePDF')) {
	function savePDF($dompdf, $filename) 
	{
		$pdf = $dompdf->output();
		if(!file_put_contents(PDF_SAVE_LOCATION. $filename, $pdf))
		{
			print 'Could not save PDF';	
		}
	}
  }


?>