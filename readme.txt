=== Htaccess by BestWebSoft ===
Contributors: bestwebsoft
Donate link: https://bestwebsoft.com/donate/
Tags: access, allow directive, control access, deny directive, directive block, htaccess, htaccess plugin, website access, protection, lockdown, safety, website security
Requires at least: 3.8
Tested up to: 4.7.3
Stable tag: 1.7.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect WordPress website – allow and deny access for certain IP addresses, hostnames, etc.

== Description ==

Htaccess plugin is a simple and useful tool which helps to control the access to your WordPress website. Allow or deny access based on a hostname, IP address, IP range, and others. Disable hotlinking and access to xmlrpc.php.

Easily secure your WordPress website!

https://www.youtube.com/watch?v=-Y-qw8cF9yk

= Free Features =

* Set the order fields:
	* Allow, Deny
	* Deny, Allow
* Set the argument info to the directive form:
	* Allow
	* Deny
* Compatible with latest WordPress version
* Incredibly simple settings for fast setup without modifying code
* Detailed step-by-step documentation and videos
* Multilingual and RTL ready

> **Pro Features**
>
> All features from Free version included plus:
>
> * Set the access to the xmlrpc.php:
> 	* Access deny
> 	* Redirect to the main page
> * Enable/disable hotlinking
> * Allow hotlinking based on hostnames
> * Configure all subsites on the network
> * Get answer to your support question within one business day ([Support Policy](https://bestwebsoft.com/support-policy/))
>
> [Upgrade to Pro Now](https://bestwebsoft.com/products/htaccess/?k=a483ae73b932f20e3ab795724abefe53)

If you have a feature suggestion or idea you'd like to see in the plugin, we'd love to hear about it! [Suggest a Feature](https://support.bestwebsoft.com/hc/en-us/requests/new)

= Documentation & Videos =

* [[Doc] Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)
* [[Doc] Purchase](https://docs.google.com/document/d/1EUdBVvnm7IHZ6y0DNyldZypUQKpB8UVPToSc_LdOYQI/)
* [[Video] Installation Instruction](https://www.youtube.com/watch?v=_V9FiMPwvtA)

= Help & Support =

Visit our Help Center if you have any questions, our friendly Support Team is happy to help — <https://support.bestwebsoft.com/>

= Translation =

* Polish (pl_PL) (thanks to [Damian Dąbrowski](mailto:dabek1812@gmail.com))
* Russian (ru_RU)
* Ukrainian (uk)

Some of these translations are not complete. We are constantly adding new features which should be translated. If you would like to create your own language pack or update the existing one, you can send [the text of PO and MO files](https://codex.wordpress.org/Translating_WordPress) to [BestWebSoft](https://support.bestwebsoft.com/hc/en-us/requests/new) and we'll add it to the plugin. You can download the latest version of the program for work with PO and MO [files Poedit](https://www.poedit.net/download.php).

= Recommended Plugins =

* [Updater](https://bestwebsoft.com/products/wordpress/plugins/updater/?k=0cb0bcac78260ef018993d8da560f1c7) - Automatically check and update WordPress core with all installed plugins to the latest versions. Manual mode, email notifications and backups of all your files and database before updating.
* [Limit Attempts](https://bestwebsoft.com/products/wordpress/plugins/limit-attempts/?k=60cc47e7c0e54ddfb0963d3bba201808) - Protect WordPress website against brute force attacks. Limit rate of login attempts.

== Installation ==

1. Upload `htaccess` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Plugin settings are located in 'BWS Panel', 'Htaccess'.

[View a PDF version of Step-by-step Instruction on Htaccess Installation](https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/)

https://www.youtube.com/watch?v=_V9FiMPwvtA

== Frequently Asked Questions ==

= How will the plugin work with the existing .htaccess file? =

If the file exists and there is a Directive block in it, the plugin will add the settings of this block to the settings page and after saving the changes it will update only the Directive block in the existing .htaccess file.

= What should I do if the .htaccess file does not exist? =

The plugin will store the settings in the database and add all the necessary conditions of the directive block to the settings of WordPress automatically.

= How will the plugin work if after saving the changes there will appear a .htaccess file in the root directory of the site? =

The plugin will get the data of the Directive block from .htaccess file automatically regardless of the previously configured settings.

= What should I do if after making changes in the .htaccess file with the help of the plugin my site stops working? =

The .htaccess is located in the site root. With your FTP program or via Сpanel go to the site root, open the .htaccess file and delete the necessary strings manually.
Please make use of the following information: <https://codex.wordpress.org/FTP_Clients>

= What is content theft (hotlinking), and how do I protect myself against it? =

To find out about hotlinking and the ways you can prevent it, please check our <a href="https://bestwebsoft.com/how-to-prevent-hotlinking" target="_blank">article dedicated to the topic</a>. In this article, you will find all the necessary data that will give you a heads up and help you avoid hotlinking on your website.

= How can I update the list of IPs after updating the plugin to V1.7.2? =

In Htaccess by BestWebSoft V1.7.2 we moved all IPs that have been added to .htaccess automatically via plugin`s interaction hooks to "automatically added" plugin options. Since this update, they won't be stored in the database but in the ".htaccess" file only. This was made in order to reduce the size of the database.

If you use some plugins that interact with the Htaccess by BestWebSoft plugin (e.g. Limit Attempts or Limit Attempts Pro by BestWebSoft), please go the Htaccess settings page and make sure that all IPs from IP lists of this plugin have been moved to "Deny from (automatically added)" and "Allow from (automatically added)" correctly. If some IPs from that lists are still in the "Deny from" and "Allow from" options, please remove them manually and save changes.

= I have some problems with the plugin's work. What Information should I provide to receive proper support? =

Please make sure that the problem hasn't been discussed yet on our forum (<https://support.bestwebsoft.com>). If no, please provide the following data along with your problem's description:
- The link to the page where the problem occurs
- The name of the plugin and its version. If you are using a pro version - your order number.
- The version of your WordPress installation
- Copy and paste into the message your system status report. Please read more here: [Instruction on System Status](https://docs.google.com/document/d/1Wi2X8RdRGXk9kMszQy1xItJrpN0ncXgioH935MaBKtc/)

== Screenshots ==

1. Plugin settings page.
2. Plugin network settings page.

== Changelog ==

= V1.7.6 - 14.04.2017 =
* Bugfix : Multiple Cross-Site Scripting (XSS) vulnerability was fixed.

= V1.7.5 - 21.02.2017 =
* Update : We updated all functionality for wordpress 4.7.2.
* Update : Function to parse .htaccess file was updated.

= V1.7.4 - 15.08.2016 =
* Update : All functionality for WordPress 4.6 was updated.

= V1.7.3 - 28.06.2016 =
* Update : The Polish language file is updated.

= V1.7.2 - 05.05.2016 =
* Update : All IPs that have been added to .htaccess automatically via plugin`s interaction hooks moved to "automatically added" options and won't be stored in the database. If you also use Limit Attempts or Limit Attempts Pro by BestWebSoft plugins, please pay attention to the fact that you may need to update list of IPs on the Htaccess by BestWebSoft settings page. For more info see FAQ.
* Update : All functionality for wordpress 4.5.1 has been updated.
* Bugfix : The bug with converting of IPv4 ranges like xxx.xxx.xxx.xxx-yyy.yyy.yyy.yyy to the CIDR has been fixed.

= V1.7.1 - 19.02.2016 =
* Update : Functionality for saving plugin settings has been updated.
* Update : Compatibility with the Limit Attempts plugin has been updated.
* Bugfix : The bug with adding plugin directions to .htaccess has been fixed.
* Bugfix : The bug with plugin menu duplicating has been fixed.

= V1.7.0 - 05.10.2015 =
* Update : We updated all functionality for wordpress 4.3.1.
* Update : Auxiliary notices were added. Tooltips displaying was updated.

= V1.6.9 - 20.08.2015 =
* NEW : Ability to allow access to the xml files ( for network, which based on sub-directories ).

= V1.6.8 - 14.07.2015 =
* NEW : Ability to restore settings to defaults.

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

= V1.7.6 =
* Bugs fixed.

= V1.7.5 =
* The compatibility with new WordPress version updated.

= V1.7.4 =
* The compatibility with new WordPress version updated.

= V1.7.3 =
The Polish language file is updated.

= V1.7.2 =
All IPs that have been added to .htaccess automatically via plugin`s interaction hooks moved to "automatically added" options and won't be stored in the database. If you also use Limit Attempts or Limit Attempts Pro by BestWebSoft plugins, please pay attention to the fact that you may need to update list of IPs on the Htaccess by BestWebSoft settings page. For more info see FAQ. All functionality for wordpress 4.5.1 has been updated. The bug with converting of IPv4 ranges like xxx.xxx.xxx.xxx-yyy.yyy.yyy.yyy to the CIDR has been fixed.

= V1.7.1 =
Functionality for saving plugin settings has been updated. Compatibility with the Limit Attempts plugin has been updated. The bug with adding plugin directions to .htaccess has been fixed. The bug with plugin menu duplicating has been fixed.

= V1.7.0 =
We updated all functionality for wordpress 4.3.1. Auxiliary notices were added. Tooltips displaying was updated.

= V1.6.9 =
Ability to allow access to the xml files ( for network, which based on sub-directories ).

= V1.6.8 =
Ability to restore settings to defaults.

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
