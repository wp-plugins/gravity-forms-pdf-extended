=== Plugin Name ===
Contributors: blueliquiddesigns
Donate link: http://www.gravityformspdfextended.com
Tags: gravity, forms, pdf, automation, attachment
Requires at least: 3.4.1
Tested up to: 3.5
Stable tag: 2.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gravity Forms PDF Extended allows you to save/view/download a PDF from the front- and back-end, and automate PDF creation on form submission. 

== Description ==

Gravity Forms PDF Extended is a plugin for Wordpress and Gravity Forms that allows your web server to create PDFs when a user submits a Gravity Form. Not only that, you can then easily attach the document to an email.

At its core the plugin uses the power of the DOMPDF library to convert HTML and CSS into PDFs.

**Features**

* Save PDF File on user submission of a Gravity Form so it can be attached to a notification
* Customise the PDF template without affecting the core Gravity Form Plugin
* Multiple PDF Templates
* Custom PDF Name
* Output individual form fields in the template - like MERGETAGS.
* View and download a PDF via the administrator interface
* Simple function to output PDF via template / plugin
* Works with Gravity Forms Signature Add-On
* Installs a sample form using the new MERGETAGS-style template to help customisation

**Server Requirements**

1. PHP 5.0+ (5.3 recommended)
2. MBString extension
3. DOM extension (bundled with PHP 5)
4. If you want images in your PDF you'll also need the GD Library

**Software Requirements**

1. [Purchase and install Gravity Forms](https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=235154)
2. Wordpress 3.0+
3. Gravity Forms 1.6.9+

**Documentation and Support**

To view the Development Documentation head to [http://www.gravityformspdfextended.com/documentation/](http://www.gravityformspdfextended.com/documentation/). If you need support with the plugin please post a topic in our [support forums](http://gravityformspdfextended.com/support/gravity-forms-pdf-extended/).

== Installation ==

1. Upload this plugin to your website and activate it
2. Create a form in Gravity Forms and configure notifications
3. Get the Form ID and follow the steps below in [the configuration section](http://gravityformspdfextended.com/documentation/2.0.0/1/configuration/)
4. Modify the PDF template file ([see the advanced templating section in the documentation](http://gravityformspdfextended.com/documentation/2.0.0/9/advanced-configuration/)), pdf-print-entry.php or example-template.php, inside your active theme's PDF_EXTENDED_TEMPLATES/ folder.


== Frequently Asked Questions ==

All FAQs can be [viewed on the Gravity Forms PDF Extended website](http://gravityformspdfextended.com/faq/category/developers/).  

== Screenshots ==

1. View PDF from the Gravity Forms entries list.
2. View or download the PDF from a Gravity Forms entry.

== Changelog ==

= 2.0.1 =
* Fixed Signature bug when checking if image file exists using URL instead of filesystem path
* Fixed PHP Constants Notice 

= 2.0.0 =
* Moved templates to active theme folder to prevent custom themes being removed on upgrade
* Allow PDFs to be saved using a custom name
* Fixed WP_Error bug when image/css file cannot be found
* Upgraded to latest version of DOMPDF
* Removed auto-load form bug which would see multiple instances of the example form loaded
* Created a number of constants to allow easier developer modification
* Plugin/Support moved to dedicated website.
* Pro/Business package offers the ability to write fields on an existing PDF.

= 1.2.3 =
* Fixed $wpdb->prepare error

= 1.2.2 =
* Fixed bug with tempalte shipping method MERGETAGS
* Fixed bug where attachment wasn't being sent
* Fixed problem when all_url_fopen was turned off on server and failed to retreive remote images. Now uses WP_HTTP class.

= 1.2.1 =
* Fixed path to custom css file included in PDF template 

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

== Upgrade Notice ==

= 2.0.0 =
New Features: Added custom PDF names and moved templates to active theme's folder (no longer overridden after updating). Also fixed a number of bugs in the problem. Remember to backup your custom templates before upgrading! 
