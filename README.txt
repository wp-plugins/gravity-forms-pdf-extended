=== Plugin Name ===
Contributors: blueliquiddesigns
Donate link: http://www.blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/
Tags: gravity, forms, pdf, automation, attachment
Requires at least: 3.4.1
Tested up to: 3.4.1
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. 

== Description ==

Expanding on the good work of 'rposborne', who created the original [Gravity Forms PDF Plugin](http://wordpress.org/extend/plugins/gravity-forms-pdf/), the extended version overhauls the rendering process so that developers now have more control over the creation of PDFs.

**Features**

* Save PDF File on user submission of a Gravity Form so it can be attached to a notification
* Customise the PDF template without affecting the core Gravity Form Plugin
* Multiple PDF Templates
* Output individual form fields in the template - like MERGETAGS.
* View and download a PDF via the administrator interface
* Simple function to output PDF via template / plugin
* Works with Gravity Forms Signature Add-On
* Installs a sample form using the new MERGETAGS-style template to help the setup

**Tutorial**
[Head to Blue Liquid Designs](http://www.blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/) - the developer of the extended Gravity Forms PDF plugin - and view everything you need to know installing, configuring and using the plugin.

**Demo**

You can see it in action [on the Blue Liquid Designs website](http://www.blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/#tab-demo). 

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload this plugin to your website and activate it
2. Modify the PDF template file, pdf-print-entry.php, inside the gravity-forms-pdf-extended plugin folder to suit your requirements. 
3. Create a form you want to PDF and configure notifications
4. To create the PDF on the fly and add as an email attachment read the tutorial: http://blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/

== Frequently Asked Questions ==

= I get the error message: Fatal error: Call to undefined method DOMText::getAttribute() on line ###. =
This is generally caused by invalid HTML. [See The Template section](http://www.blueliquiddesigns.com.au/index.php/gravity-forms-pdf-extended-plugin/#gf-the-template) for an easy method to debug the issue.

= I added an image to the template file and got the error 'Image not readable or empty'. = 
Make sure you use an absolute path to the file e.g. http://www.your-site.com/my-image.jpg. Also, check that the 'temp' folder in ../gravity-forms-pdf-extended/dompdf/ is writable by your web server.

= I want to have multiple PDF template files. = 
Copy the *pdf-print-entry.php* file (located in the plugin directory) and pass the new template name to the PDF_Generator() function inside the gform_pdf_create() function. 

= I want users to be able to download the PDF from the server. = 
By deleting the .htaccess file in the 'output' folder you'll be able to access the PDFs through a web browser. Use the get_pdf_filename() function to get the PDF's name. 

== Screenshots ==

1. View PDF From the Form List
2. View or download the PDF from a form entry.

== Changelog ==

Remember to always make a backup of your plugin before upgrading otherwise you'll loose your custom PDF template file.

= 1.2.0 =
* Template files moved to the plugin's template folder
* Sample Form installed so developers have a working example to modify
* Fixed bug when using WordPress in another directory to the site

= 1.1.0 =
* Now compatible with Gravity Forms Signature Add-On
* Moved the field data functions out side of the Gravity Forms core so users can freely style their form information (located in pdf-entry-detail.php)
* Simplified the field data output
* Fixed bug when using product information

= 1.0.0 =
* First release. 
