=== Htaccess by BestWebSoft ===
Contributors: bestwebsoft
Donate link: http://bestwebsoft.com/donate/
Tags: access, allow, allow directive, allow from, client hostname, control access, deny, deny directive, deny from, directive, directive block, hatccess, htaccess, htacess, htaces, htacces, hteccess, htecess, htecces, ip-address, order, order fields, website access
Requires at least: 3.5
Tested up to: 4.2.2
Stable tag: 1.6.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The plugin Htaccess allows controlling access to your website using the directives Allow and Deny.

== Description ==

The plugin Htaccess allows to controll access to your website. Access can be controlled based on the client's hostname, IP address, or other characteristics of the client's request. It is very simple and has just two the directives like Allow and Deny.

http://www.youtube.com/watch?v=-Y-qw8cF9yk

<a href="http://www.youtube.com/watch?v=_V9FiMPwvtA" target="_blank">Video instruction on Installation</a>

<a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank">FAQ</a>
<a href="http://support.bestwebsoft.com" target="_blank">Support</a>

<a href="http://bestwebsoft.com/products/htaccess/?k=a483ae73b932f20e3ab795724abefe53" target="_blank">Upgrade to Pro Version</a>

= Features =

* Actions: Allows to edit the directive block of .htaccess file.

= Recommended Plugins =

The author of the Htaccess also recommends the following plugins:

* <a href="http://wordpress.org/plugins/updater/">Updater</a> - This plugin updates WordPress core and the plugins to the recent versions. You can also use the auto mode or manual mode for updating and set email notifications.
There is also a premium version of the plugin <a href="http://bestwebsoft.com/products/updater/?k=0cb0bcac78260ef018993d8da560f1c7">Updater Pro</a> with more useful features available. It can make backup of all your files and database before updating. Also it can forbid some plugins or WordPress Core update.

= Translation =

* Polish (pl_PL) (thanks to <a href="mailto:dabek1812@gmail.com">Damian Dąbrowski</a>)
* Russian (ru_RU)
* Ukrainian (uk)

If you create your own language pack or update an existing one, you can send <a href="http://codex.wordpress.org/Translating_WordPress" target="_blank">the text of PO and MO files</a> for <a href="http://bestwebsoft.com/" target="_blank">BWS</a> and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO files  <a href="http://www.poedit.net/download.php" target="_blank">Poedit</a>.

== Installation ==

1. Upload `htaccess` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Plugin settings are located in 'BWS Plugin', 'Htaccess'.

<a href="https://docs.google.com/document/d/1PElhNK0lFTcVXJYNBsaXdezac__eFSqXtUKYIYvuABQ/edit" target="_blank">View a PDF version of Step-by-step Instruction on Htaccess Installation</a>.

http://www.youtube.com/watch?v=_V9FiMPwvtA

== Frequently Asked Questions ==

= How will the plugin work with the existing .htaccess file? =

If the file exists and there is a Directive block in it, the plugin will add the settings of this block to the settings page and after saving the changes it will update only the Directive block in the existing .htaccess file.

= What should I do if the .htaccess file does not exist? =

The plugin will store the settings in the database and add all the necessary conditions of the directive block to the settings of WordPress automatically.

= How will the plugin work if after saving the changes there will appear a .htaccess file in the root directory of the site? = 

The plugin will get the data of the Directive block from .htaccess file automatically regardless of the previously configured settings.

= What should I do if after making changes in the .htaccess file with the help of the plugin my site stops working? =

The.htaccess is located in the site root. With your FTP program or via Сpanel go to the site root, open the .htaccess file and delete the necessary strings manually.
Please make use of the following information: http://codex.wordpress.org/FTP_Clients

= What is content theft (hotlinking), and how do I protect myself against it? =

To find out about hotlinking and the ways you can prevent it, please check our <a href=http://bestwebsoft.com/how-to-prevent-hotlinking target="_blank">article dedicated to the topic</a>. In this article, you will find all the necessary data that will give you a heads up and help you avoid hotlinking on your website.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<a href="http://support.bestwebsoft.com" target="_blank">http://support.bestwebsoft.com</a>). If no, please provide the following data along with your problem's description:

1. the link to the page where the problem occurs
2. the name of the plugin and its version. If you are using a pro version - your order number.
3. the version of your WordPress installation
4. copy and paste into the message your system status report. Please read more here: <a href="https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/edit" target="_blank">Instuction on System Status</a>

== Screenshots ==

1. Plugin settings page.

== Changelog ==

= V1.6.7 - 11.06.2015 =
* Bugfix : We fixed the error with Order Deny Alow lines overflow in the .htaccess file.

= V1.6.6 - 11.05.2015 =
* NEW : The Polish language file is added to the plugin.
* Update : We updated all functionality for wordpress 4.2.2.

= V1.6.5 - 01.04.2015 =
* Bugfix : An error that occurs when Order Deny,Alow entries are manually added to .htaccess file was fixed.
* Update : BWS plugins section is updated.

= V1.6.4 - 02.03.2015 =
* Bugfix : We fixed plugin errors when working on multisite
* Bugfix : We fixed the error deleting IP addresses when working with the Limit Attempts plugin

= V1.6.3 - 20.02.2015 =
* Update : We updated all functionality for wordpress 4.1.1
* Update : BWS plugins section is updated.

= V1.6.2 - 28.01.2015 =
* Update : We updated all functionality for Limit Attempts Pro plugin.

= V1.6.1 - 12.01.2015 =
* Update : BWS plugins section is updated.
* Update : We updated all functionality for wordpress 4.1.

= V1.6 - 06.10.2014 =
* Bugfix : Bug with access rights to an .htaccess file was fixed.

= V1.5 - 07.08.2014 =
* Bugfix : Security Exploit was fixed.

= V1.4 - 28.07.2014 =
* Update : We updated all functionality for Limit Attempts plugin.

= V1.3 - 14.05.2014 =
* NEW : The Ukrainian language file is added to the plugin.
* Update : We updated all functionality for wordpress 3.9.1.

= V1.2 - 04.04.2013 =
* Update : Screenshots are updated.
* Update : BWS plugins section is updated.
* Budfix : Plugin optimization is done. 

= V1.1 =
* NEW: The ability to change the Directive block of the existing .htaccess file was added. Plugin optimization is done.

== Upgrade Notice ==

= V1.6.7 =
We fixed the error with Order Deny Alow lines overflow in the .htaccess file.

= V1.6.6 =
The Polish language file is added to the plugin. We updated all functionality for wordpress 4.2.2.

= V1.6.5 =
An error that occurs when Order Deny,Alow entries are manually added to .htaccess file was fixed. BWS plugins section is updated.

= V1.6.4 =
We fixed the error deleting IP addresses when working with the Limit Attempts plugin. We fixed plugin errors when working on multisite.

= V1.6.3 =
 We updated all functionality for wordpress 4.1.1. BWS plugins section is updated.

= V1.6.2 =
We updated all functionality for Limit Attempts Pro plugin.

= V1.6.1 =
BWS plugins section is updated. We updated all functionality for wordpress 4.1.

= V1.6 =
Bug with access rights to an .htaccess file was fixed.

= V1.5 =
Security Exploit was fixed.

= V1.4 =
We updated all functionality for Limit Attempts plugin.

= V1.3 =
The Ukrainian language file is added to the plugin. We updated all functionality for wordpress 3.9.1.

= V1.2 =
Screenshots are updated. BWS plugins section is updated. Plugin optimization is done.

= V1.1 =
The ability to change the Directive block of the existing .htaccess file was added.
