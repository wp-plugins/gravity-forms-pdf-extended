<?php

/**
 * Plugin: Gravity Forms PDF Extended
 * File: install-update-manager.php
 * 
 * This file handles the installation and update code that ensures the plugin will be supported.
 */

/**
 * Check to see if Gravity Forms version is supported
 */
 
class GFPDF_InstallUpdater
{
	public function install() 
	{
		if(strlen(get_option('gf_pdf_extended_installed')) == 0)
		{
			update_option('gf_pdf_extended_version', PDF_EXTENDED_VERSION);	
			update_option('gf_pdf_extended_deploy', 'yes');
			self::pdf_extended_activate(true);
		}
	}
	
	public function gform_pdf_is_gravityforms_supported() {
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
	public function pdf_extended_activate($deploy = false)
	{	
		/**
		 * If deploying new software files we'll remove the old ones first.
		 */
		 if($deploy && PDF_DEPLOY === true)
		 {
			if(file_exists(PDF_TEMPLATE_LOCATION.'default-template.php')) { unlink(PDF_TEMPLATE_LOCATION.'default-template.php'); }
			if(file_exists(PDF_TEMPLATE_LOCATION.'default-template-no-style.php')) { unlink(PDF_TEMPLATE_LOCATION.'default-template-no-style.php'); }
			if(file_exists(PDF_TEMPLATE_LOCATION.'default-template-two-rows.php')) { unlink(PDF_TEMPLATE_LOCATION.'default-template-two-rows.php');}
			if(file_exists(PDF_TEMPLATE_LOCATION.'example-template.php')) { unlink(PDF_TEMPLATE_LOCATION.'example-template.php'); }
			if(file_exists(PDF_TEMPLATE_LOCATION.'template.css')) { unlink(PDF_TEMPLATE_LOCATION.'template.css'); }
		 }
	
		/* preload example pdf form into database if it isn't installed already */
		/**
		 * Version used if made an update to example template
		 * if( ((get_option('gf_pdf_extended_sample') != 'installed')) || ((!RGFormsModel::get_form_id('Gravity Forms PDF Extended Custom Template Sample')) &&  (get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION))  )*/
		 if(!RGFormsModel::get_form_id('Gravity Forms PDF Extended Custom Template Sample') && (get_option('gf_pdf_extended_sample') != 'installed') )
		{
			if(GFExport::import_file(PDF_PLUGIN_DIR . 'example-form.xml'))
			{			
				/* update system to ensure everything is installed correctly */
				update_option('gf_pdf_extended_sample', 'installed');
			}
		}
		
		include PDF_PLUGIN_DIR.'render_to_pdf.php';
		
		/* create new directory in active themes folder*/	
		if(!is_dir(PDF_TEMPLATE_LOCATION))
		{
			if(mkdir(PDF_TEMPLATE_LOCATION) === false)
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}
		}
	
		if(!is_dir(PDF_SAVE_LOCATION))
		{
			/* create new directory in active themes folder*/	
			if(mkdir(PDF_SAVE_LOCATION) === false)
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}
		}
		
		if(!file_exists(PDF_TEMPLATE_LOCATION.'default-template.php'))
		{
			/* copy template files to new directory */
			if(!copy(PDF_PLUGIN_DIR . 'templates/default-template.php', PDF_TEMPLATE_LOCATION.'default-template.php'))
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}	
		}
		
		if(!file_exists(PDF_TEMPLATE_LOCATION.'default-template-two-rows.php'))
		{
			/* copy template files to new directory */
			if(!copy(PDF_PLUGIN_DIR . 'templates/default-template-two-rows.php', PDF_TEMPLATE_LOCATION.'default-template-two-rows.php'))
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}	
		}
		
		if(!file_exists(PDF_TEMPLATE_LOCATION.'default-template-no-style.php'))
		{
			/* copy template files to new directory */
			if(!copy(PDF_PLUGIN_DIR . 'templates/default-template-no-style.php', PDF_TEMPLATE_LOCATION.'default-template-no-style.php'))
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}	
		}		
		
		if(!file_exists(PDF_TEMPLATE_LOCATION.'example-template.php'))
		{
			/* copy template files to new directory */
			if(!copy(PDF_PLUGIN_DIR .'templates/example-template.php', PDF_TEMPLATE_LOCATION.'example-template.php'))
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}
		}
		
		if(!file_exists(PDF_TEMPLATE_LOCATION.'template.css'))
		{ 
			/* copy template files to new directory */
			if(!copy(PDF_PLUGIN_DIR .'styles/template.css', PDF_TEMPLATE_LOCATION.'template.css'))
			{ 
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}
		}	
	
		if(!file_exists(PDF_SAVE_LOCATION.'.htaccess'))
		{		
			if(!file_put_contents(PDF_SAVE_LOCATION.'.htaccess', 'deny from all'))
			{
				add_action('admin_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return;
			}	
		}	
		
		if(get_option('gf_pdf_extended_version') != PDF_EXTENDED_VERSION)
		{
			update_option('gf_pdf_extended_deploy', 'no');
		}			 
		
		/* update system to ensure everything is installed correctly */
		update_option('gf_pdf_extended_installed', 'installed');	
		if($deploy)
		{			
			update_option('gf_pdf_extended_deploy', 'yes');
		}		
	}
	
	/**
	 * Gravity Forms hasn't been installed so throw error.
	 * We make sure the user hasn't already dismissed the error
	 */
	public function gf_pdf_not_installed()
	{
		echo '<div id="message" class="error"><p>';
		echo 'You need to install <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">Gravity Forms</a> to use the Gravity Forms PDF Extended Plugin.';
		echo '</p></div>';
	}
	
	/**
	 * Gravity Forms is now v1.7. Warn user they need to upgrade their functions.php code
	 */
	public function gf_pdf_now_1_7()
	{
		if(get_option('gf_pdf_1_7_alert') != 'done')
		{
			echo '<div id="message" class="updated"><p>';
			echo 'Gravity Forms PDF Extended needs to be modified to be compatible with Gravity Forms v1.7. <a href="http://gravityformspdfextended.com/gravity-forms-pdf-extended-2-2-0-release-notes/">Read the v2.2.0 release notes for upgrade instructions</a>.';
			echo '</p></div>';
			update_option('gf_pdf_1_7_alert', 'done');
		}
	}	
	
	/**
	 * PDF Extended has been updated but the new template files haven't been deployed yet
	 */
	public function gf_pdf_not_deployed()
	{
		if(PDF_DEPLOY === true)
		{
			if(rgget("page") == 'gf_settings' && rgget('addon') == 'PDF')
			{
				echo '<div id="message" class="error"><p>';
				echo 'You\'ve updated Gravity Forms PDF Extended but are yet to deploy the new template files. Please review the latest updates before deploying v'.PDF_EXTENDED_VERSION.' template files.';
				echo '</p></div>';
				
			}
			else
			{
				echo '<div id="message" class="error"><p>';
				echo 'You\'ve updated Gravity Forms PDF Extended but are yet to deploy the new template files. Please go to the <a href="'.PDF_SETTINGS_URL.'">plugin\'s settings page</a> to redeploy the new files.';
				echo '</p></div>';
			}
		}
	}	
	
	/**
	 * The Gravity Forms version isn't compatible. Prompt user to upgrade
	 */
	public function gf_pdf_not_supported()
	{
			echo '<div id="message" class="error"><p>';
			echo 'Gravity Forms PDF Extended only works with Gravity Forms version '.GF_PDF_SUPPORTED_VERSION.' and higher. Please <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">upgrade your copy of Gravity Forms</a> to use this plugin.';
			echo '</p></div>';	
	}
								
	
	/**
	 * Cannot create new template folder in active theme directory
	 */
	public function gf_pdf_template_dir_err()
	{
			echo '<div id="message" class="error"><p>';
			echo 'We could not create a template folder in your active theme\'s directory. Please created a folder called <strong>\''. PDF_SAVE_FOLDER .'\'</strong> in '.get_stylesheet_directory().'/. Then copy the contents of '.PDF_PLUGIN_DIR.'templates/ to your newly-created PDF_EXTENDED_TEMPLATES folder, as well as styles/template.css. You should also make this directory writable.';
			echo '</p></div>';
	
	}
	
	/**
	 * Cannot remove old default template files
	 */
	public function gf_pdf_deployment_unlink_error()
	{
			echo '<div id="message" class="error"><p>';
			echo 'We could not remove the default template files from the Gravity Forms PDF Extended folder in your active theme\'s directory. Please ensure this directory is writable by your web server before trying again.';
			echo '</p></div>';
	
	}	
	
	
	
	/**
	 * Cannot create new template folder in active theme directory
	 */
	public function gf_pdf_template_move_err()
	{
			echo '<div id="message" class="error"><p>';
			echo 'We could not copy the contents of '.PDF_PLUGIN_DIR.'templates/ to your newly-created PDF_EXTENDED_TEMPLATES folder. Please manually copy the files to the aforementioned directory. You should also make this directory writable.';
			echo '</p></div>';
	
	}
	
	/*
	 * When switching themes copy over current active theme's PDF_EXTENDED_TEMPLATES (if it exists) to new theme folder
	 */
	public function gf_pdf_on_switch_theme($old_theme) {
		 $current_themes = get_themes();
		 $new_theme = get_current_theme();
		 
		 $old_theme_info = $current_themes[$old_theme];
		 $new_theme_info = $current_themes[$new_theme];
		 
		 if(!defined('PDF_TEMPLATE_LOCATION'))
		 {
			include 'render_to_pdf.php'; 
		 }
		 
		 $old_pdf_dir = str_replace( $new_theme_info->template, $old_theme_info->template, PDF_TEMPLATE_LOCATION);
		 $new_pdf_dir = PDF_TEMPLATE_LOCATION;
		 
		 if(is_dir($old_pdf_dir))
		 {
			 self::pdf_extended_copy_directory( $old_pdf_dir, $new_pdf_dir );
		 }
	 
	}
	
	/*
	 * Allows you to copy entire folder structures to new location
	 */
	
	public function pdf_extended_copy_directory( $source, $destination ) 
	{
		if ( is_dir( $source ) ) 
		{
			@mkdir( $destination );
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) 
			{
				if ( $readdirectory == '.' || $readdirectory == '..' ) 
				{
					continue;
				}
				$PathDir = $source . '/' . $readdirectory; 
				if ( is_dir( $PathDir ) ) 
				{
					self::pdf_extended_copy_directory( $PathDir, $destination . '/' . $readdirectory );
					continue;
				}
				copy( $PathDir, $destination . '/' . $readdirectory );
			}
	 
			$directory->close();
		}
		else 
		{
			copy( $source, $destination );
		}
	}
}


?>