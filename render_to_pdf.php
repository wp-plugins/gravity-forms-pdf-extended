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

/**
 * Outputs a PDF entry from a Gravity Form
 * var $form_id integer: The form id
 * var $lead_id integer: The entry id
 * var $output string: either view, save or download
 * save will save a copy of the PDF to the server using the PDF_SAVE_LOCATION constant
 * var $return boolean: if set to true it will return the path of the saved PDF
 * var $template string: if you want to use multiple PDF templates - name of the template file
 * var $pdfname string: allows you to pass a custom PDF name to the generator e.g. 'Application Form.pdf' (ensure .pdf is appended to the filename)
 * var $fpdf boolean: custom hook to allow the FPDF engine to generate PDFs instead of DOMPDF. Premium Paid Feature.
 */
 if (!function_exists('PDF_Generator')) {
	function PDF_Generator($form_id, $lead_id, $arguments = 'view', $return = false, $template = 'default-template.php', $pdfname = '', $fpdf = false)
	{
		/* Because we merged the create and attach functions we need to measure to only run this function once per session per lead id. */
		static $pdf_creator = array();	
		
		/* PDF_Generator was becoming too cluttered so store all the variables in an array */
		if(is_array($arguments))
		{
			$output			= (strlen($arguments['output']) > 0) ? $arguments['output'] : 'save';			
			$return			= ($arguments['return']) ? $arguments['return'] : false;
			$fpdf 			= ($arguments['fpdf']) ? $arguments['fpdf'] : false;
			$template 		= (strlen($arguments['template']) > 0) ? $arguments['template'] : 'default-template.php';
			$pdfname 		= (strlen($arguments['pdfname']) > 0) ? $arguments['pdfname'] : '';
		}
		else
		{
			/* maintain backwards compatibility */			
			$output = $arguments;	
		}
		
		$filename = (strlen($pdfname) > 0) ? $pdfname : get_pdf_filename($form_id, $lead_id); 

		/* check if the PDF exists and if this function has already run this season */	
		if(in_array($lead_id, $pdf_creator) && file_exists(PDF_SAVE_LOCATION.$id.'/'. $filename))
		{
			/* don't generate a new PDF, use the existing one */
			return true;	
		}
		$pdf_creator[] = $lead_id;

		$entry = load_entry_data($form_id, $lead_id, $template, $fpdf);	
		$id = $form_id . $lead_id;
	
		if(strlen($entry) > 0)
		{
			PDF_processing($entry, $filename, $id, $output);
		}
		/* return the filename so we can use it */
		if($return)
		{
			return PDF_SAVE_LOCATION.$id.'/'. $filename;
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
	function load_entry_data($form_id, $lead_id, $template, $fpdf)
	{
		/* set up contstants for gravity forms to use so we can override the security on the printed version */
		define('GF_FORM_ID', $form_id);
		define('GF_LEAD_ID', $lead_id);	
		
		/* if FPDF is true we include the template file instead of return it */
		if(rgget("fpdf") || $fpdf)
		{		
			if(file_exists(PDF_TEMPLATE_LOCATION.$template))
			{
				include PDF_TEMPLATE_LOCATION.$template;
				return;	
			}
			else
			{
				include PDF_PLUGIN_DIR."templates/" . $template;
				return;
			}	
		}
		
		
		if(file_exists(PDF_TEMPLATE_LOCATION.$template))
		{	
			return GetRequire(PDF_TEMPLATE_LOCATION.$template);
		}
		else
		{
			return GetRequire(PDF_PLUGIN_DIR."templates/" . $template);
		}		
	}
 }

/**
 * Creates the PDF and does a specific output (see PDF_Generator function above for $output variable types)
 */
 if (!function_exists('PDF_processing')) {
	function PDF_processing($entry, $filename, $id, $output = 'view')
	{
		//Parse the default print view from Gravity forms so we can play with it.
		$DOM = new DOMDocument;
		$DOM->loadHTML($entry);
		
		/* Problem pulling the site style so manually set */
		$imgs = $DOM->getElementsByTagName('img');
		foreach($imgs as $img){
		  $src = $img->getAttribute('src');
		  if(strpos($src, site_url()) !== 0){
			$img->setAttribute('src', site_url()."$src");
		  }
		}

		/**
		 * Depreciated Code v2.0.1 
		 * Removes <div id="print_preview_hdr"></div> tag from template
		 * Removed in v2.0.2
		 */
		$xpath = new DOMXPath($DOM);
		$nlist = $xpath->query("//div[@id='print_preview_hdr']"); 
		/* FIX THIS - OBJECT PASSED... */

		if($nlist->item(0) != null)
		{
			$node = $nlist->item(0);
			$node->parentNode->removeChild($node);
		}
		$entry = $DOM->saveHTML();
	
		//Load the DOMPDF Engine to render the PDF
		require_once(PDF_PLUGIN_DIR ."/dompdf/dompdf_config.inc.php");
		$dompdf = new DOMPDF();
		$dompdf -> load_html($entry);
		/*$dompdf -> set_base_path(site_url());*/
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
				savePDF($dompdf, $filename, $id);
			break;
		}
	}
 }

/**
 * Creates the PDF and does a specific output (see PDF_Generator function above for $output variable types)
 * var $dompdf Object
 */
  if (!function_exists('savePDF')) {
	function savePDF($dompdf, $filename, $id) 
	{
		$pdf = $dompdf->output();
		
		/* create unique folder for PDFs */
		if(!is_dir(PDF_SAVE_LOCATION.$id))
		{
			if(!mkdir(PDF_SAVE_LOCATION.$id))
			{
				print 'Could not save PDF';
				return;
			}
		}
		
		if(!file_put_contents(PDF_SAVE_LOCATION.$id.'/'. $filename, $pdf))
		{
			print 'Could not save PDF';	
		}
	}
  }


?>