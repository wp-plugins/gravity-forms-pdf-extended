<?php

/**
 * Class: PDFGenerator
 * Plugin: Gravity Forms PDF Extended
 * Usage: assign options from user configuration file, automatically attach PDFs to specified Gravity Forms, and view PDF from admin area.
 */
 
 class PDFGenerator
 {
	
	/*
	 * Set default values for forms not assigned a PDF 
	 */
	public static $default = array(
		'template' 		=> 'default-template.php',
		'pdf_size' 		=> 'a4',
		'orientation' 	=> 'portrait',
		'rtf'			=> false,
		'security' 		=> false
	);
	
	public static $allowed_privileges = array('copy', 'print', 'modify', 'annot-forms', 'fill-forms', 'extract', 'assemble', 'print-highres');
	
	public $configuration = array();
	
	public static $gf_compatibility;
	
	public static $pre_1_7_notifications = array('Admin Notification', 'User Notification');
	
	/*
	 * Switch to verify if configuration file exists.
	 * If not, user is using old functions.php method and we 
	 * don't want to interfere with it.
	 */ 
	public $disabled = false;	
	
	/*
	 * The index holds the form_id and configuration key in $this->configuration 
	 * so each form knows 
	 */
	public $index = array();
	
	public function __construct()
	{
		/*
		 * Detect Gravity Forms version and use appropriate code
		 */	
		 PDFGenerator::$gf_compatibility = 'post 1.7';
		 if(GFCommon::$version < 1.7)
		 {
				PDFGenerator::$gf_compatibility = 'pre 1.7'; 
		 }
		 
		 /* 
		  * Do configuration pre-processing
		  */
		  
		  /*
		   * Check if user configuration file exists
		   * If not disable $configuration and $index.
		   */ 		   
		  if(!file_exists(PDF_TEMPLATE_LOCATION.'configuration.php'))
		  {
			  $this->disabled = true;
			  return;
		  }
		  else
		  {
				/*
				 * Include the configuration file and set up the configuration variable.
				 */  
				 require(PDF_TEMPLATE_LOCATION.'configuration.php');				
				 /*
				  * $gf_pdf_config included from configuration.php file
				  */				 
				 $this->configuration = (isset($gf_pdf_config)) ? $gf_pdf_config : array();
		  }
		  
		  $this->pdf_config();
	}
	
	/*
	 * Run through user configuration and set PDF options
	 */		
	private function pdf_config()
	{
		if(sizeof($this->configuration) == 0)
		{
			return;
		}
		
		$this->set_form_pdfs();		
	}
	
	
	/*
	 * Set the configuration index so it's faster to access template configuration information
	 */			
	private function set_form_pdfs()
	{
		foreach($this->configuration as $key => $config)
		{			
			if(!is_array($config['form_id']))
			{
				$this->assign_index($config['form_id'], $key);
			}
			else
			{
				foreach($config['form_id'] as $id)
				{
					$this->assign_index($id, $key);
				}
			}
			
		}
	}	
	
	/*
	 * Check to see if ID is valid
	 * If so, assign ID => key to index 
	 */	
	protected function assign_index($id, $key)
	{
		$id = (int) $id;
		if($id !== 0)
		{
			/*
			 * Assign the outter array with the form ID and the value as the configuration key
			 */
			$this->index[$id][] = $key;
		}		
	}
	
	/*
	 * Searches the index for the configuration key
	 * Return: form PDF configuration
	 */ 
	public function get_config($id)
	{
		if(!isset($this->index[$id]))
		{
			return false;	
		}
		return $this->index[$id];		
	}
	
	/*
	 * Searches the index for the configuration key and once found return the real configuration
	 * Return: form PDF configuration
	 */ 
	public function get_config_data($form_id)
	{
		if(!isset($this->index[$form_id]))
		{
			return false;	
		}

		$index = $this->index[$form_id];
		
		/* 
		 * Because it is the default template we can assume multiple indexes don't exist as this feature 
		 * is something used to assign different templates to notifications in the same form
		 */		
		return $this->configuration[$index[0]];
	}	
	
	/*
	 * Search for the template from a given form id
	 * Return: the first template found for the form
	 * TODO: return all PDFs
	 */ 
	public function get_template($form_id)
	{
		$template = '';
		
		if(isset($this->index[$form_id]))
		{
			/*
			 * Check if PDF template is avaliable
			 */ 
			 if(isset($this->configuration[$this->index[$form_id][0]]['template']))
			 {
					$user_template = (isset($_GET['template'])) ? $_GET['template'] : '';
					$match = false;

					foreach($this->index[$form_id] as $index)
					{
						if($this->configuration[$index]['template'] === $user_template)
						{
							$match = true;			
						}
					}
					
					$template = ($match === true) ? $user_template : $this->configuration[$this->index[$form_id][0]]['template'];
			 }
			
			 if(strlen($template) == 0)
			 {
				$template = PDFGenerator::$default['template'];
			 }
			 return $template;
		}
		
		if( (strlen($template) == 0) && (GFPDF_SET_DEFAULT_TEMPLATE === true))
		{			
			/*
			 * If no PDF template exists then we will use $gf_pdf_default_configuration if it exists.
			 * If not, we will set the default			 
			 */ 
			 global $gf_pdf_default_configuration;
			 
			/*
			 * Check if a default configuration is defined
			 */			
			 
			 if(is_array($gf_pdf_default_configuration) && sizeof($gf_pdf_default_configuration) > 0 && isset($gf_pdf_default_configuration['template']))			 
			 {
				return $gf_pdf_default_configuration['template'];	 
			 }
			 else
			 {			 
				return PDFGenerator::$default['template'];
			 }
		}			
		else
		{
			return false;	
		}

	}	
	
	public function get_pdf_name($index, $form_id = false, $lead_id = false)
	{
		return PDF_Common::validate_pdf_name($this->configuration[$index]['filename'], $form_id, $lead_id);		
	}
	
	public function validate_privileges($privs)
	{ 
		if(!is_array($privs))
		{
			return array();
		}

		$new_privs = array_filter($privs, array($this, 'array_filter_privilages'));
		
		return $new_privs;
	}
	
	private function array_filter_privilages($i)
	{
		if(in_array($i, PDFGenerator::$allowed_privileges))
		{
			return true;
		}
		return false;		
	}
	 
 }
