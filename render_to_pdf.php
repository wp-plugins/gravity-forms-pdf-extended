<?php
 
 /*
  * File: render_to_pdf.php
  * Status: Depreciated as of GF PDF Extended 3.0.0
  * Left here to ensure backward compatibility
  * File replaced by pdf-render.php
  * Add compatibility functions to ensure backwards compatibility with the software
  */ 
  
/*
 * Added to ensure backwards compatibility with older versions of the software
 */

if(!function_exists('PDF_Generator'))
{
 function PDF_Generator($form_id, $lead_id, $arguments = 'view', $return = false, $template = 'default-template.php', $pdfname = '', $fpdf = false)
 {
		if(is_array($arguments))
		{
			$output			= (isset($arguments['output'])  && strlen($arguments['output']) > 0) ? $arguments['output'] : 'save';			
			$return			= (isset($arguments['return']) && $arguments['return'] === true) ? $arguments['return'] : false;
			$fpdf 			= (isset($arguments['fpdf']) && $arguments['fpdf'] === true) ? $arguments['fpdf'] : false;
			$template 		= (isset($arguments['template']) && strlen($arguments['template']) > 0) ? $arguments['template'] : 'default-template.php';
			$pdfname 		= (isset($arguments['pdfname']) && strlen($arguments['pdfname']) > 0) ? $arguments['pdfname'] : get_pdf_filename($form_id, $lead_id);
		}
		else
		{
			/* maintain backwards compatibility */			
			$output = $arguments;	
		}	
		
		if(strlen($pdfname) == 0)
		{
			$pdfname = get_pdf_filename($form_id, $lead_id);	
		}
		
		$new_arguments = array(
			'output' => $output,
			'return' => $return,
			'template' => $template,
			'pdfname' => $pdfname,
			'fpdf' => $fpdf
		);	
		
		/*
		 * Depreciated - Backwards Compatibility
		 * Set Constants
		 */		
		 if(!defined('GF_FORM_ID'))
		 {
			/* TODO */
			trigger_error('Gravity Forms PDF Extended constants GF_FORM_ID and GF_LEAD_ID depreciated in v3.0.0. Custom template files should be updated with the new code. See http://gravityformspdfextended.com/documentation-v3-x-x/v3-0-0-migration-guide/ for upgrade instructions.');
			define('GF_FORM_ID', $form_id);
			define('GF_LEAD_ID', $lead_id);				 
		 }			
		
		$render = new PDFRender();
		return $render->PDF_Generator($form_id, $lead_id, $new_arguments);
 }
}
 
if(!function_exists('get_pdf_filename'))
{
 function get_pdf_filename($form_id, $lead_id)
 {
 		return PDF_Common::get_pdf_filename($form_id, $lead_id); 
 } 
}

?>