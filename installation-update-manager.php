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
			
			self::pdf_extended_activate();
		}
	}
	
	/**
	 * Install everything required
	 */
	public function pdf_extended_activate()
	{	
	    /*
		 * Initialise the Wordpress Filesystem API
		 */
		if(PDF_Common::initialise_WP_filesystem_API(array('gfpdf_deploy'), 'pdf-extended-filesystem') === false)
		{
			return false;	
		}	
		
		/*
		 * If we got here we should have $wp_filesystem available
		 */
		global $wp_filesystem;		
	
		/**
		 * If PDF_TEMPLATE_LOCATION already exists then we will remove the old template files so we can redeploy the new ones
		 */
		 if(PDF_DEPLOY === true && $wp_filesystem->exists(PDF_TEMPLATE_LOCATION))
		 {
			 /* read all file names into array and unlink from active theme template folder */
			 foreach(glob(PDF_PLUGIN_DIR.'templates/*.php') as $file) {
				 	$path_parts = pathinfo($file);					
						if($wp_filesystem->exists(PDF_TEMPLATE_LOCATION.$path_parts['basename']))
						{
							$wp_filesystem->delete(PDF_TEMPLATE_LOCATION.$path_parts['basename']);
						}
			 }			
			if($wp_filesystem->exists(PDF_TEMPLATE_LOCATION.'template.css')) { $wp_filesystem->delete(PDF_TEMPLATE_LOCATION.'template.css'); }
		 }

		/* unzip the mPDF file */
		if($wp_filesystem->exists(PDF_PLUGIN_DIR . 'mPDF.zip'))
		{
			if($results = unzip_file( PDF_PLUGIN_DIR . 'mPDF.zip', PDF_PLUGIN_DIR ) !== true)
			{
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_unzip_mpdf_err")); 	
				return true;				
			}
			/*
			 * Remove the original archive
			 */
			 $wp_filesystem->delete(PDF_PLUGIN_DIR . 'mPDF.zip');
		}		
		
		/* create new directory in active themes folder*/	
		if(!$wp_filesystem->is_dir(PDF_TEMPLATE_LOCATION))
		{
			if($wp_filesystem->mkdir(PDF_TEMPLATE_LOCATION) === false)
			{
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return true;
			}
		}
	
		if(!$wp_filesystem->is_dir(PDF_SAVE_LOCATION))
		{
			/* create new directory in active themes folder*/	
			if($wp_filesystem->mkdir(PDF_SAVE_LOCATION) === false)
			{
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return true;
			}
		}
		
		if(!$wp_filesystem->is_dir(PDF_FONT_LOCATION))
		{
			/* create new directory in active themes folder*/	
			if($wp_filesystem->mkdir(PDF_FONT_LOCATION) === false)
			{
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return true;
			}
		}		
		
		/*
		 * Copy entire template folder over to PDF_TEMPLATE_LOCATION
		 */
		 self::pdf_extended_copy_directory( PDF_PLUGIN_DIR . 'templates', PDF_TEMPLATE_LOCATION, false );

		if(!$wp_filesystem->exists(PDF_TEMPLATE_LOCATION.'configuration.php'))
		{ 
			/* copy template files to new directory */
			if(!$wp_filesystem->copy(PDF_PLUGIN_DIR .'configuration.php', PDF_TEMPLATE_LOCATION.'configuration.php'))
			{ 
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return true;
			}
		}
		
		if(!$wp_filesystem->exists(PDF_TEMPLATE_LOCATION.'template.css'))
		{ 
			/* copy template files to new directory */
			if(!$wp_filesystem->copy(PDF_PLUGIN_DIR .'styles/template.css', PDF_TEMPLATE_LOCATION.'template.css'))
			{ 
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return true;
			}
		}	
	
		if(!$wp_filesystem->exists(PDF_SAVE_LOCATION.'.htaccess'))
		{		
			if(!$wp_filesystem->put_contents(PDF_SAVE_LOCATION.'.htaccess', 'deny from all'))
			{
				add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_template_dir_err")); 	
				return true;
			}	
		}	
		
		if(self::install_fonts() !== true)
		{
			return true;	
		}				 
		
		/* 
		 * Update system to ensure everything is installed correctly.
		 */
		update_option('gf_pdf_extended_installed', 'installed');			
		update_option('gf_pdf_extended_deploy', 'yes');
		
		return true;	
	}
	
	public static function initialise_fonts()
	{
	    /*
		 * Initialise the Wordpress Filesystem API
		 */
		if(PDF_Common::initialise_WP_filesystem_API(array('gfpdf_deploy'), 'pdf-extended-filesystem') === false)
		{
			return false;	
		}	
		
		if(self::install_fonts() === true)
		{
			add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_font_install_success")); 
		}		
		return true;
	}
	
	private static function install_fonts()
	{

		global $wp_filesystem;	
		$write_to_file = '<?php 
		
			if(!defined("PDF_EXTENDED_VERSION"))
			{
				return;	
			}
		
		';
		
		/*
		 * Search the font folder for .ttf files. If found, move them to the mPDF font folder 
		 * and write the configuration file
		 */

		 /* read all file names into array and unlink from active theme template folder */
		 foreach(glob(PDF_TEMPLATE_LOCATION.'fonts/*.[tT][tT][fF]') as $file) {

			 	$path_parts = pathinfo($file);	
				
				/*
				 * Check if the files already exist in the mPDF font folder
				 */					
				 if(!$wp_filesystem->exists(PDF_PLUGIN_DIR . 'mPDF/ttfonts/' . $path_parts['basename']))
				 {
					/*
					 * copy ttf file to the mPDF font folder
					 */
					if($wp_filesystem->copy($file, PDF_PLUGIN_DIR . 'mPDF/ttfonts/' . $path_parts['basename']) === false)
					{ 
						add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_font_err")); 	
						return false;
					}	
				 }
				
				/*
				 * Generate configuration information in preparation to write to file
				 */ 							
				$write_to_file .= '
					$this->fontdata[\''.$path_parts['filename'].'\'] = array(
								\'R\' => \''.$path_parts['basename'].'\'
					);';
					
		 }					 

		 /*
		  * Remove the old configuration file and put the contents of $write_to_file in a font configuration file
		  */
		  $wp_filesystem->delete(PDF_TEMPLATE_LOCATION.'fonts/config.php');
		  if($wp_filesystem->put_contents(PDF_TEMPLATE_LOCATION.'fonts/config.php', $write_to_file) === false)
		  {
			  	add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_font_config_err")); 	
				return false;  
		  }			
		 
		 return true;
	}
	
	public function gf_pdf_font_install_success()
	{
		echo '<div id="message" class="updated"><p>';
		echo 'The font files have been successfully installed. A font can be used by adding it\'s file name (without .ttf) in a CSS font-family declaration.';
		echo '</p></div>';
	}	

	public function gf_pdf_font_err()
	{
		echo '<div id="message" class="error"><p>';
		echo 'There was a problem installing the font files. Manually copy your fonts to the mPDF/ttfonts/ folder.';
		echo '</p></div>';
	}	
	
	public function gf_pdf_font_config_err()
	{
		echo '<div id="message" class="error"><p>';
		echo 'Could not create font configuration file. Try initialise again.';
		echo '</p></div>';
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
	 * PDF Extended has been updated but the new template files haven't been deployed yet
	 */
	public function gf_pdf_not_deployed()
	{		
		if( (PDF_DEPLOY === true) && !rgpost('update') )
		{
			if(rgget("page") == 'gf_settings' && rgget('addon') == 'PDF')
			{
				echo '<div id="message" class="error"><p>';
				echo 'You\'ve updated Gravity Forms PDF Extended but are yet to re-initialise the plugin. After initialising, please review the latest updates to ensure your custom templates remain compatible with the latest version.';
				echo '</p></div>';
				
			}
			else
			{
				echo '<div id="message" class="error"><p>';
				echo 'You\'ve updated Gravity Forms PDF Extended but are yet to re-initialise the plugin. Please go to the <a href="'.PDF_SETTINGS_URL.'">plugin\'s settings page</a> to initialise.';
				echo '</p></div>';
			}
		}
	}	
	
	/**
	 * PDF Extended has been freshly installed
	 */
	public function gf_pdf_not_deployed_free()
	{		
		if( (PDF_DEPLOY === true) && !rgpost('update') )
		{
			if(rgget("page") == 'gf_settings' && rgget('addon') == 'PDF')
			{
				echo '<div id="message" class="updated"><p>';
				echo 'Welcome to Gravity Forms PDF Extended. Before you can use the plugin correctly you need to initilise it.';
				echo '</p></div>';
				
			}
			else
			{
				echo '<div id="message" class="updated"><p>';
				echo 'Welcome to Gravity Forms PDF Extended. Before you can use the plugin correctly you need to initilise it. Please go to the <a href="'.PDF_SETTINGS_URL.'">plugin\'s settings page</a> to initialise.';
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
			echo 'Gravity Forms PDF Extended only works with Gravity Forms version '.GF_PDF_EXTENDED_SUPPORTED_VERSION.' and higher. Please <a href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154" target="ejejcsingle">upgrade your copy of Gravity Forms</a> to use this plugin.';
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
	
	public static function gf_pdf_unzip_mpdf_err()
	{
			echo '<div id="message" class="error"><p>';
			echo 'Could not unzip mPDF.zip (located in the plugin folder). Unzip the file manually, place the extracted mPDF folder in the plugin folder and run the initialisation again.';
			echo '</p></div>';		
	}
	
	/**
	 * Cannot remove old default template files
	 */
	public function gf_pdf_deployment_unlink_error()
	{
			echo '<div id="message" class="error"><p>';
			echo 'We could not remove the default template files from the Gravity Forms PDF Extended folder in your active theme\'s directory. Please manually remove all files starting with \'default-\' and the template.css file.';
			echo '</p></div>';
	
	}		
	
	/**
	 * Cannot create new template folder in active theme directory
	 */
	public function gf_pdf_template_move_err()
	{
			echo '<div id="message" class="error"><p>';
			echo 'We could not copy the contents of '.PDF_PLUGIN_DIR.'templates/ to your newly-created PDF_EXTENDED_TEMPLATES folder. Please manually copy the files to the aforementioned directory..';
			echo '</p></div>';
	
	}
	
	/*
	 * When switching themes copy over current active theme's PDF_EXTENDED_TEMPLATES (if it exists) to new theme folder
	 */
	public function gf_pdf_on_switch_theme($old_theme_name, $old_theme_object) {
		
		/*
		 * We will store the old pdf dir and new pdf directory and prompt the user to copy the PDF_EXTENDED_TEMPLATES folder
		 */		
		 	 $previous_theme_directory = $old_theme_object->get_stylesheet_directory();
		 			 			
			 $current_theme_array = wp_get_theme(); 
			 $current_theme_directory = $current_theme_array->get_stylesheet_directory();

			 /*
			  * Add the save folder name to the end of the paths
			  */ 
			 $old_pdf_path = $previous_theme_directory . '/' . PDF_SAVE_FOLDER;
			 $new_pdf_path = $current_theme_directory . '/' . PDF_SAVE_FOLDER;
		 	
			 update_option('gfpdfe_switch_theme', array('old' => $old_pdf_path, 'new' => $new_pdf_path));
	}
	
	/*
	 * Check if a theme switch has been made recently 
	 * If it has then prompt the user to move the files
	 */
	public static function check_theme_switch()
	{
		$theme_switch = get_option('gfpdfe_switch_theme');
		if(isset($theme_switch['old']) && isset($theme_switch['new']))
		{
			/*
			 * Add admin notification hook to move the files
			 */	
			 add_action('admin_notices', array("GFPDF_InstallUpdater", "do_theme_switch_notice")); 	
			 return true;
		}
		return false;		
	}
	
	/*
	 * Prompt user to keep the plugin working
	 */
	public static function do_theme_switch_notice()
	{		
		/*
		 * Check we aren't in the middle of doing the sync
		 */
		 if(isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'gfpdfe_sync_now'))
		 {
			return; 
		 }
		 
			echo '<div id="message" class="error"><p>';
			echo 'Gravity Forms PDF Extended needs to keep the PDF_EXTENDED_TEMPLATE folder in sync with your current active theme. <a href="'. wp_nonce_url(PDF_SETTINGS_URL, 'gfpdfe_sync_now') . '" class="button">Sync Now</a>';
			echo '</p></div>';		
		 
	}
	
	public static function gf_pdf_theme_sync_success()
	{
			echo '<div id="message" class="updated"><p>';
			echo 'PDF_EXTENDED_TEMPLATE folder successfully synced.';
			echo '</p></div>';			
	}
	
	/*
	 * The after_switch_theme hook is too early in the initialisation to use request_filesystem_credentials()
	 * so we have to call this function at a later inteval
	 */
	public function do_theme_switch($previous_pdf_path, $current_pdf_path)
	{
		/*
		 * Prepare for calling the WP Filesystem
		 * It only allows post data to be added so we have to manually assign them
		 */
		$_POST['previous_pdf_path'] = $previous_pdf_path;
		$_POST['current_pdf_path'] = $current_pdf_path;
		
	    /*
		 * Initialise the Wordpress Filesystem API
		 */
		if(PDF_Common::initialise_WP_filesystem_API(array('previous_pdf_path', 'current_pdf_path'), 'gfpdfe_sync_now') === false)
		{
			return false;	
		}				
		
		/*
		 * If we got here we should have $wp_filesystem available
		 */
		global $wp_filesystem;			 
		 
		 if($wp_filesystem->is_dir($previous_pdf_path))
		 {
			 self::pdf_extended_copy_directory( $previous_pdf_path, $current_pdf_path, true, true );
		 }		
		 
		/*
		 * Remove the options key that triggers the switch theme function
		 */ 
		 delete_option('gfpdfe_switch_theme');
		 add_action('gfpdfe_notices', array("GFPDF_InstallUpdater", "gf_pdf_theme_sync_success")); 	
		 
		 /*
		  * Show success message to user
		  */
		 return true;
	}
	
	/*
	 * Allows you to copy entire folder structures to new location
	 */
	
	public function pdf_extended_copy_directory( $source, $destination, $copy_base = true, $delete_destination = false ) 
	{
		global $wp_filesystem;		
		
		if ( $wp_filesystem->is_dir( $source ) ) 
		{			
			if($delete_destination === true)
			{
				/*
				 * To ensure everything stays in sync we will remove the destination file structure
				 */
				 $wp_filesystem->delete($destination, true);
			}
			 
			if($copy_base === true)
			{
				$wp_filesystem->mkdir( $destination );
			}
			$directory = dir( $source );
			while ( FALSE !== ( $readdirectory = $directory->read() ) ) 
			{
				
				if ( $readdirectory === '.' || $readdirectory === '..' ) 
				{
					continue;
				}
				
				$PathDir = $source . '/' . $readdirectory; 
				
				if ( $wp_filesystem->is_dir( $PathDir ) ) 
				{
					self::pdf_extended_copy_directory( $PathDir, $destination . '/' . $readdirectory );
					continue;
				}
				$wp_filesystem->copy( $PathDir, $destination . '/' . $readdirectory );
			}
	 
			$directory->close();
		}
		else 
		{
			$wp_filesystem->copy( $source, $destination );
		}	
	}

}
