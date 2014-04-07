=== Htaccess ===
Contributors: bestwebsoft
Donate link: https://www.2checkout.com/checkout/purchase?sid=1430388&quantity=10&product_id=13
Tags: htaccess, allow, deny, allow from, deny from, control access, client hostname, IP address
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin Htaccess allows controlling access to your website using the directives Allow and Deny.

== Description ==

The plugin Htaccess allows controlling access to your website using the directives Allow and Deny. Access can be controlled based on the client's hostname, IP address, or other characteristics of the client's request.

<a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank">FAQ</a>

<a href="http://bestwebsoft.com/plugin/htaccess-plugin/" target="_blank">Support</a>

= Features =

* Actions: Allows to edit the directive block of .htaccess file.

= Recommended Plugins =

The author of the Htaccess also recommends the following plugins:

* <a href="http://wordpress.org/plugins/updater/">Updater</a> - This plugin updates WordPress core and the plugins to the recent versions. You can also use the auto mode or manual mode for updating and set email notifications.
There is also a premium version of the plugin <a href="http://bestwebsoft.com/plugin/updater-pro/?k=0cb0bcac78260ef018993d8da560f1c7">Updater Pro</a> with more useful features available. It can make backup of all your files and database before updating. Also it can forbid some plugins or WordPress Core update.

= Translation =

* Russian (ru_RU)

If you create your own language pack or update an existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> for <a href="http://bestwebsoft.com/" target="_blank">BWS</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files  <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

== Installation ==

1. Upload `htaccess` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Plugin settings are located in 'BWS Plugin', 'Htaccess'.

== Frequently Asked Questions ==

= How will the plugin work with the existing .htaccess file?=

If the file exists and there is a Directive block in it, the plugin will add the settings of this block to the settings page and after saving the changes it will update only the Directive block in the existing .htaccess file.

= What should I do if the .htaccess file does not exist? =

The plugin will store the settings in the database and add all the necessary conditions of the directive block to the settings of WordPress automatically.

= How will the plugin work if after saving the changes there will appear a .htaccess file in the root directory of the site? = 

The plugin will get the data of the Directive block from .htaccess file automatically regardless of the previously configured settings.

= What should I do if after making changes in the .htaccess file with the help of the plugin my site stops working? =

The.htaccess is located in the site root. With your FTP program or via Сpanel go to the site root, open the .htaccess file and delete the necessary strings manually.
Please make use of the following information: http://codex.wordpress.org/FTP_Clients

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<a href="http://support.bestwebsoft.com" target="_blank">http://support.bestwebsoft.com</a>). If no, please provide the following data along with your problem's description:
1. the link to the page where the problem occurs
2. the name of the plugin and its version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste into the message your system status report. Please read more here: <a href="https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/edit" target="_blank">Instuction on System Status</a>

= How to use the other language files with the Htaccess? = 

Here is an example for German language files.

1. In order to use another language for WordPress it is necessary to set the WP version to the required language and in configuration wp file - `wp-config.php` in the line `define('WPLANG', '');` write `define('WPLANG', 'de_DE');`. If everything is done properly the admin panel will be in German.

2. Make sure that there are files `de_DE.po` and `de_DE.mo` in the plugin (the folder languages in the root of the plugin).

3. If there are no such files it will be necessary to copy other files from this folder (for example, for Russian or Italian language) and rename them (you should write `de_DE` instead of `ru_RU` in the both files).

4. The files are edited with the help of the program Poedit - http://www.poedit.net/download.php - please load this program, install it, open the file with the help of this program (the required language file) and for each line in English you should write translation in German.

5. If everything has been done properly all the lines will be in German in the admin panel and on frontend.

== Screenshots ==

1. Plugin settings page.

== Changelog ==

= V1.2 - 04.04.2013 =
* Update : Screenshots are updated.
* Update : BWS plugins section is updated.
* Budfix : Plugin optimization is done. 

= V1.1 =
* NEW: The ability to change the Directive block of the existing .htaccess file was added. Plugin optimization is done.

== Upgrade Notice ==

= V1.2 =
Screenshots are updated. BWS plugins section is updated. Plugin optimization is done.

= V1.1 =
The ability to change the Directive block of the existing .htaccess file was added.
