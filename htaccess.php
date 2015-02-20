<?php
/*
Plugin Name: Htaccess
Plugin URI: http://bestwebsoft.com/products/
Description: The plugin Htaccess allows controlling access to your website using the directives Allow and Deny. Access can be controlled based on the client's hostname, IP address, or other characteristics of the client's request.
Author: BestWebSoft
Version: 1.6.3
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'add_htccss_admin_menu' ) ) {
	function add_htccss_admin_menu() {
		global $bstwbsftwppdtplgns_options, $bstwbsftwppdtplgns_added_menu;
		$bws_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) . "bws_menu/bws_menu.php" );
		$bws_menu_version = $bws_menu_info["Version"];
		$base = plugin_basename( __FILE__ );

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( is_multisite() ) {
				if ( ! get_site_option( 'bstwbsftwppdtplgns_options' ) )
					add_site_option( 'bstwbsftwppdtplgns_options', array() );
				$bstwbsftwppdtplgns_options = get_site_option( 'bstwbsftwppdtplgns_options' );
			} else {
				if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
					add_option( 'bstwbsftwppdtplgns_options', array() );
				$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
			}
		}

		if ( isset( $bstwbsftwppdtplgns_options['bws_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			unset( $bstwbsftwppdtplgns_options['bws_menu_version'] );
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] < $bws_menu_version ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			if ( is_multisite() )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_added_menu ) ) {
			$plugin_with_newer_menu = $base;
			foreach ( $bstwbsftwppdtplgns_options['bws_menu']['version'] as $key => $value ) {
				if ( $bws_menu_version < $value && is_plugin_active( $base ) ) {
					$plugin_with_newer_menu = $key;
				}
			}
			$plugin_with_newer_menu = explode( '/', $plugin_with_newer_menu );
			$wp_content_dir = defined( 'WP_CONTENT_DIR' ) ? basename( WP_CONTENT_DIR ) : 'wp-content';
			if ( file_exists( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' ) )
				require_once( ABSPATH . $wp_content_dir . '/plugins/' . $plugin_with_newer_menu[0] . '/bws_menu/bws_menu.php' );
			else
				require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );	
			$bstwbsftwppdtplgns_added_menu = true;			
		}

		add_menu_page( 'BWS Plugins', 'BWS Plugins', 'manage_options', 'bws_plugins', 'bws_add_menu_render', plugins_url( "images/px.png", __FILE__ ), 1001 );
		add_submenu_page( 'bws_plugins', 'Htaccess ' . __( 'Settings', 'htaccess' ), 'Htaccess', 'manage_options', "htaccess.php", 'htccss_settings_page' );

	}
}


if ( ! function_exists ( 'htccss_plugin_init' ) ) {
	function htccss_plugin_init() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'htaccess', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		/* Check version on WordPress */
		htccss_version_check();		
	}
}

if ( ! function_exists ( 'htccss_plugin_admin_init' ) ) {
	function htccss_plugin_admin_init() {
 		global $bws_plugin_info, $htccss_plugin_info;

 		$htccss_plugin_info = get_plugin_data( __FILE__, false );

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '110', 'version' => $htccss_plugin_info["Version"] );

		/* Call register settings function */
		if ( isset( $_GET['page'] ) && "htaccess.php" == $_GET['page'] )
			register_htccss_settings();
	}
}

/* Function check if plugin is compatible with current WP version  */
if ( ! function_exists ( 'htccss_version_check' ) ) {
	function htccss_version_check() {
		global $wp_version, $htccss_plugin_info;
		$require_wp		=	"3.5"; /* Wordpress at least requires version */
		$plugin			=	plugin_basename( __FILE__ );
	 	if ( version_compare( $wp_version, $require_wp, "<" ) ) {
	 		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			if ( is_plugin_active( $plugin ) ) {
				$admin_url = ( function_exists( 'get_admin_url' ) ) ? get_admin_url( null, 'plugins.php' ) : esc_url( '/wp-admin/plugins.php' );
				if ( ! $htccss_plugin_info )
					$htccss_plugin_info = get_plugin_data( __FILE__, false );
				deactivate_plugins( $plugin );
				wp_die( "<strong>" . $htccss_plugin_info['Name'] . " </strong> " . __( 'requires', 'htaccess' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'htaccess') . "<br /><br />" . __( 'Back to the WordPress', 'htaccess') . " <a href='" . $admin_url . "'>" . __( 'Plugins page', 'htaccess') . "</a>." );
			}
		}
	}
}

/* register settings function */
if ( ! function_exists( 'register_htccss_settings' ) ) {
	function register_htccss_settings() {
		global $htccss_options, $htccss_plugin_info;

		$htccss_option_defaults = array(
			'order'					=> 'Order Allow,Deny',
			'allow'					=> '',
			'deny'					=> '',
			'plugin_option_version' => $htccss_plugin_info["Version"]
		);

		/* Install the option defaults */
		if ( ! get_option( 'htccss_options' ) )
			add_option( 'htccss_options', $htccss_option_defaults );

		/* Get options from the database */
		$htccss_options = get_option( 'htccss_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $htccss_options['plugin_option_version'] ) || $htccss_options['plugin_option_version'] != $htccss_plugin_info["Version"] ) {
			$htccss_options = array_merge( $htccss_option_defaults, $htccss_options );
			$htccss_options['plugin_option_version'] = $htccss_plugin_info["Version"];
			update_option( 'htccss_options', $htccss_options );
		}
	}
}

if ( ! function_exists( 'htccss_plugin_action_links' ) ) {
	function htccss_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;		
			if ( ! $this_plugin )
				$this_plugin = plugin_basename(__FILE__);
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=htaccess.php">' . __( 'Settings', 'htaccess' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}
/* End function htccss_plugin_action_links */

if ( ! function_exists( 'htccss_register_plugin_links' ) ) {
	function htccss_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[] = '<a href="admin.php?page=htaccess.php">' . __( 'Settings', 'htaccess' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank">' . __( 'FAQ', 'htaccess' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'htaccess' ) . '</a>';
		}
		return $links;
	}
}

/* Function for display htaccess settings page in the admin area */
if ( ! function_exists( 'htccss_settings_page' ) ) {
	function htccss_settings_page() {		
		global $htccss_admin_fields_enable, $htccss_options, $htccss_plugin_info, $wp_version;
		$error = $message = "";
		
		if ( ! isset( $_GET['action'] ) ) {
			/* Save data for settings page */
			if ( isset( $_REQUEST['htccss_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'htccss_nonce_name' ) ) {

				if ( 'Order Allow,Deny' != trim( $_REQUEST['htccss_order'] ) && 'Order Deny,Allow' != trim( $_REQUEST['htccss_order'] ) )
					$error = __( "Wrong 'Order fields'. You can enter:", 'htaccess' ) . ' <strong>Order Allow,Deny</strong> ' . __( "or", 'htaccess' ) . ' <strong>Order Deny,Allow</strong>';
				else
					$htccss_options['order'] = trim( $_REQUEST['htccss_order'] );

				$htccss_options['allow'] = trim( trim( preg_replace( '/Allow from /i', '', stripslashes( esc_html( $_REQUEST['htccss_allow'] ) ) ) ), "\n" );

				$htccss_options['deny'] = trim( trim( preg_replace( '/Allow from /i', '', stripslashes( esc_html( $_REQUEST['htccss_deny'] ) ) ) ), "\n" );

				if ( "" == $error ) {
					/* Update options in the database */
					update_option( 'htccss_options', $htccss_options );
					$message = __( "Settings saved.", 'htaccess' );
					htccss_generate_htaccess();
				}
			} else
				htccss_get_htaccess();
		}
		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			global $bstwbsftwppdtplgns_options;
			$all_plugins = get_plugins();
			$bws_license_key = ( isset( $_POST['bws_license_key'] ) ) ? trim( esc_html( $_POST['bws_license_key'] ) ) : "";

			if ( isset( $_POST['bws_license_submit'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'bws_license_nonce_name' ) ) {
				if ( '' != $bws_license_key ) { 
					if ( strlen( $bws_license_key ) != 18 ) {
						$error = __( "Wrong license key", 'htaccess' );
					} else {
						$bws_license_plugin = stripslashes( esc_html( $_POST['bws_license_plugin'] ) );				
						if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] ) && $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] < ( time() + (24 * 60 * 60) ) ) {
							$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] + 1;
						} else {
							$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = 1;
							$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] = time();
						}	

						/* download Pro */				
						if ( ! array_key_exists( $bws_license_plugin, $all_plugins ) ) {
							$current = get_site_transient( 'update_plugins' );
							if ( is_array( $all_plugins ) && !empty( $all_plugins ) && isset( $current ) && is_array( $current->response ) ) {
								$to_send = array();
								$to_send["plugins"][ $bws_license_plugin ] = array();
								$to_send["plugins"][ $bws_license_plugin ]["bws_license_key"] = $bws_license_key;
								$to_send["plugins"][ $bws_license_plugin ]["bws_illegal_client"] = true;
								$options = array(
									'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3 ),
									'body' => array( 'plugins' => serialize( $to_send ) ),
									'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ) );
								$raw_response = wp_remote_post( 'http://bestwebsoft.com/wp-content/plugins/paid-products/plugins/update-check/1.0/', $options );

								if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
									$error = __( "Something went wrong. Try again later. If the error will appear again, please, contact us <a href=http://support.bestwebsoft.com>BestWebSoft</a>. We are sorry for inconvenience.", 'htaccess' );
								} else {
									$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
									
									if ( is_array( $response ) && !empty( $response ) ) {
										foreach ( $response as $key => $value ) {
											if ( "wrong_license_key" == $value->package ) {
												$error = __( "Wrong license key", 'htaccess' ); 
											} elseif ( "wrong_domain" == $value->package ) {
												$error = __( "This license key is bind to another site", 'htaccess' );
											} elseif ( "you_are_banned" == $value->package ) {
												$error = __( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'htaccess' );
											} elseif ( "time_out" == $value->package ) {
												$error = __( 'This license key is valid, but your license has expired.', 'htaccess' );
											} 
										}
										if ( '' == $error ) {																	
											$bstwbsftwppdtplgns_options[ $bws_license_plugin ] = $bws_license_key;

											$url = 'http://bestwebsoft.com/wp-content/plugins/paid-products/plugins/downloads/?bws_first_download=' . $bws_license_plugin . '&bws_license_key=' . $bws_license_key . '&download_from=5';
											$uploadDir = wp_upload_dir();
											$zip_name = explode( '/', $bws_license_plugin );
											$received_content = file_get_contents( $url );
											if ( ! $received_content ) {
												$error = __( "Failed to download the zip archive. Please, upload the plugin manually", 'htaccess' );
											} else {
												if ( is_writable( $uploadDir["path"] ) ) {
													$file_put_contents = $uploadDir["path"] . "/" . $zip_name[0] . ".zip";
												    if ( file_put_contents( $file_put_contents, $received_content ) ) {
												    	@chmod( $file_put_contents, octdec( 755 ) );
												    	if ( class_exists( 'ZipArchive' ) ) {
															$zip = new ZipArchive();
															if ( $zip->open( $file_put_contents ) === TRUE ) {
																$zip->extractTo( WP_PLUGIN_DIR );
																$zip->close();
															} else {
																$error = __( "Failed to open the zip archive. Please, upload the plugin manually", 'htaccess' );
															}
														} elseif ( class_exists( 'Phar' ) ) {
															$phar = new PharData( $file_put_contents );
															$phar->extractTo( WP_PLUGIN_DIR );
														} else {
															$error = __( "Your server does not support either ZipArchive or Phar. Please, upload the plugin manually", 'htaccess' );
														}
														@unlink( $file_put_contents );
													} else {
														$error = __( "Failed to download the zip archive. Please, upload the plugin manually", 'htaccess' );
													}
												} else {
													$error = __( "UploadDir is not writable. Please, upload the plugin manually", 'htaccess' );
												}
											}

											/* activate Pro */
											if ( file_exists( WP_PLUGIN_DIR . '/' . $zip_name[0] ) ) {	
												if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
													/* if multisite and free plugin is network activated */
													$active_plugins = get_site_option( 'active_sitewide_plugins' );
													$active_plugins[ $bws_license_plugin ] = time();
													update_site_option( 'active_sitewide_plugins', $active_plugins );
												} else {
													/* activate on a single blog */
													$active_plugins = get_option( 'active_plugins' );
													array_push( $active_plugins, $bws_license_plugin );
													update_option( 'active_plugins', $active_plugins );
												}
												$pro_plugin_is_activated = true;
											} elseif ( '' == $error ) {
												$error = __( "Failed to download the zip archive. Please, upload the plugin manually", 'htaccess' );
											}																				
										}
									} else {
										$error = __( "Something went wrong. Try again later or upload the plugin manually. We are sorry for inconvienience.", 'htaccess' ); 
					 				}
					 			}
				 			}
						} else {
							/* activate Pro */
							$network_wide = false;
							if ( is_multisite() ) {
								if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) )
									$network_wide = true;
							}
							activate_plugin( $bws_license_plugin, NULL, $network_wide );
							$pro_plugin_is_activated = true;					
						}
						if ( is_multisite() )
							update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
						else
							update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			 		}
			 	} else {
		 			$error = __( "Please, enter Your license key", 'htaccess' );
		 		}
		 	}
		} /* Display form on the setting page */ ?> 
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Htaccess Settings', 'htaccess' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=htaccess.php"><?php _e( 'Settings', 'htaccess' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/products/htaccess/faq/" target="_blank"><?php _e( 'FAQ', 'htaccess' ); ?></a>
				<a class="nav-tab bws_go_pro_tab<?php if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=htaccess.php&amp;action=go_pro"><?php _e( 'Go PRO', 'htaccess' ); ?></a>
			</h2>
			<div class="updated fade" <?php if ( ! isset( $_REQUEST['htccss_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<?php if ( ! isset( $_GET['action'] ) ) { ?>
				<div class="error">
					<p><strong><?php _e( "Notice:", 'htaccess' ); ?></strong> <?php _e( "It is very important to be extremely attentive when making changes to .htaccess file. If after making changes your site stops functioning, please see", 'htaccess' ); ?> <a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank" title=""><?php _e( 'FAQ', 'htaccess' ); ?></a></p>
					<p><?php _e( 'The changes will be applied immediately after saving the changes, if you are not sure - do not click the "Save changes" button.', 'htaccess' ); ?></p>
				</div>
				<div id="htccss_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'htaccess' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'htaccess' ); ?></p></div>
				<form id="htccss_settings_form" method="post" action="admin.php?page=htaccess.php">
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Order fields', 'htaccess' ); ?></th>
							<td>
								<label><input type="radio" name="htccss_order" value="Order Allow,Deny" <?php if ( 'Order Allow,Deny' == $htccss_options['order'] ) echo "checked=\"checked\" "; ?>/> Order Allow,Deny</label><br />
								<label><input type="radio" name="htccss_order" value="Order Deny,Allow" <?php if ( 'Order Deny,Allow' == $htccss_options['order'] ) echo "checked=\"checked\" "; ?>/> Order Deny,Allow</label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Allow from', 'htaccess' ); ?></th>
							<td>
								<textarea name="htccss_allow"><?php echo $htccss_options['allow']; ?></textarea><br />
								<span class="htaccess_info"><?php _e( "Info about the arguments to the Allow directive", 'htaccess' ) ?>: <a href="http://bestwebsoft.com/controlling-access-to-your-website-using-the-htaccess/#Allow_Directive"><?php _e( "Controlling access to your website using the .htaccess", 'htaccess' ); ?></a></span>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Deny from', 'htaccess' ); ?></th>
							<td>
								<textarea name="htccss_deny"><?php echo $htccss_options['deny']; ?></textarea><br />
								<span class="htaccess_info"><?php _e( "Info about the arguments to the Deny directive", 'htaccess' ) ?>: <a href="http://bestwebsoft.com/controlling-access-to-your-website-using-the-htaccess/#Deny_Directive"><?php _e( "Controlling access to your website using the .htaccess", 'htaccess' ); ?></a></span>
							</td>
						</tr>
					</table>
					<div class="bws_pro_version_bloc">
						<div class="bws_pro_version_table_bloc">	
							<div class="bws_table_bg"></div>											
							<table class="form-table bws_pro_version">
								<tr valign="top">
									<th scope="row"><?php _e( 'Deny access to xmlrpc.php', 'htaccess' ); ?></th>
									<td>
										<label><input type="checkbox" name="htccsspr_xmlrpc" value="1" disabled="disabled"> </label>
										<div class="htccss-help-box">
										</div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Disable hotlinking', 'htaccess' ); ?></th>
									<td>
										<label><input type="checkbox" name="htccsspr_hotlink_deny" value="1" disabled="disabled" /> </label>
										<div class="htccss-help-box">
										</div>
									</td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php _e( 'Allow hotlinking for', 'htaccess' ); ?></th>
									<td>
										<textarea name="htccsspr_hotlink_alow" disabled="disabled"></textarea></br>
										<span class="htaccess_info"><?php _e( 'Allowed hosts should be entered comma separated', 'htaccess' ); ?></span></br>
									</td>
								</tr>
							</table>
							<table>
								<tr valign="top">
									<th scope="row" colspan="2">
										* <?php _e( 'If you upgrade to Pro version all your settings will be saved.', 'htaccess' ); ?>
									</th>
								</tr>			
							</table>
						</div>
						<div class="bws_pro_version_tooltip">
							<div class="bws_info">
								<?php _e( 'Unlock premium options by upgrading to a PRO version.', 'htaccess' ); ?> 
								<a href="http://bestwebsoft.com/products/htaccess/?k=ac1e1061bf4e95ba51406b4cc32f61fa&pn=110&v=<?php echo $htccss_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Htaccess Plugin"><?php _e( 'Learn More', 'htaccess' ); ?></a>			
							</div>
							<div class="bws_pro_links">
								<a class="bws_button" href="http://bestwebsoft.com/products/htaccess/buy/?k=ac1e1061bf4e95ba51406b4cc32f61fa&pn=110&v=<?php echo $htccss_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Htaccess Pro Plugin">
									<?php _e( 'Go', 'htaccess' ); ?> <strong>PRO</strong>
								</a>
							</div>	
							<div class="htccss-clear"></div>					
						</div>
					</div>		
					<input type="hidden" name="htccss_form_submit" value="submit" />
					<p class="submit">
						<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ) ?>" />
					</p>
					<?php wp_nonce_field( plugin_basename(__FILE__), 'htccss_nonce_name' ); ?>
				</form>				
				<div class="bws-plugin-reviews">
					<div class="bws-plugin-reviews-rate">
						<?php _e( 'If you enjoy our plugin, please give it 5 stars on WordPress', 'htaccess' ); ?>:
						<a href="http://wordpress.org/support/view/plugin-reviews/htaccess" target="_blank" title="Htaccess reviews"><?php _e( 'Rate the plugin', 'htaccess' ); ?></a>
					</div>
					<div class="bws-plugin-reviews-support">
						<?php _e( 'If there is something wrong about it, please contact us', 'htaccess' ); ?>:
						<a href="http://support.bestwebsoft.com">http://support.bestwebsoft.com</a>
					</div>
				</div>
			<?php } elseif ( 'go_pro' == $_GET['action'] ) { ?>
				<?php if ( isset( $pro_plugin_is_activated ) && true === $pro_plugin_is_activated ) { ?>
					<script type="text/javascript">
						window.setTimeout( function() {
						    window.location.href = 'admin.php?page=htaccess-pro.php';
						}, 5000 );
					</script>				
					<p><?php _e( "Congratulations! The PRO version of the plugin is successfully download and activated.", 'htaccess' ); ?></p>
					<p>
						<?php _e( "Please, go to", 'htaccess' ); ?> <a href="admin.php?page=htaccess-pro.php"><?php _e( 'the setting page', 'htaccess' ); ?></a> 
						(<?php _e( "You will be redirected automatically in 5 seconds.", 'htaccess' ); ?>)
					</p>
				<?php } else { ?>
					<form method="post" action="admin.php?page=htaccess.php&amp;action=go_pro">
						<p>
							<?php _e( 'You can download and activate', 'htaccess' ); ?> 
							<a href="http://bestwebsoft.com/products/htaccess/?k=ac1e1061bf4e95ba51406b4cc32f61fa&pn=110&v=<?php echo $htccss_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>" target="_blank" title="Htaccess Pro">PRO</a> 
							<?php _e( 'version of this plugin by entering Your license key.', 'htaccess' ); ?><br />
							<span style="color: #888888;font-size: 10px;">
								<?php _e( 'You can find your license key on your personal page Client area, by clicking on the link', 'htaccess' ); ?> 
								<a href="http://bestwebsoft.com/wp-login.php">http://bestwebsoft.com/wp-login.php</a> 
								<?php _e( '(your username is the email you specify when purchasing the product).', 'htaccess' ); ?>
							</span>
						</p>
						<?php if ( isset( $bstwbsftwppdtplgns_options['go_pro']['htaccess-pro/htaccess-pro.php']['count'] ) &&
							'5' < $bstwbsftwppdtplgns_options['go_pro']['htaccess-pro/htaccess-pro.php']['count'] &&
							$bstwbsftwppdtplgns_options['go_pro']['htaccess-pro/htaccess-pro.php']['time'] < ( time() + ( 24 * 60 * 60 ) ) ) { ?>
							<p>
								<input disabled="disabled" type="text" name="bws_license_key" value="<?php echo $bws_license_key; ?>" />
								<input disabled="disabled" type="submit" class="button-primary" value="<?php _e( 'Activate', 'htaccess' ); ?>" />
							</p>
							<p>
								<?php _e( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'htaccess' ); ?>
							</p>
						<?php } else { ?>
							<p>
								<input type="text" name="bws_license_key" value="<?php echo $bws_license_key; ?>" />
								<input type="hidden" name="bws_license_plugin" value="htaccess-pro/htaccess-pro.php" />
								<input type="hidden" name="bws_license_submit" value="submit" />
								<input type="submit" class="button-primary" value="<?php _e( 'Go!', 'htaccess' ); ?>" />
								<?php wp_nonce_field( plugin_basename(__FILE__), 'bws_license_nonce_name' ); ?>
							</p>
						<?php } ?>
					</form>				
				<?php }
			} ?>
		</div>
	<?php }
}

if ( ! function_exists ( 'htccss_get_htaccess' ) ) {
	function htccss_get_htaccess() {
		global $htccss_options;

		if ( empty( $htccss_options ) )
			$htccss_options = get_option( 'htccss_options' );

		if ( ! function_exists( 'get_home_path' ) )
			require_once ( ABSPATH . 'wp-admin/includes/file.php' );

		$htaccess_file = get_home_path() . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$handle = fopen( $htaccess_file, "r" );
			if ( $handle ) {
				$htccss_allow_old_array = array();
				$htccss_deny_old_array = array();
				$htccss_order_line = '';
				while ( ! feof( $handle ) ) {
					$current_line = fgets( $handle );
					if ( false !== stripos( $current_line, 'Order ' ) ) {
						$htccss_order_line = trim( $current_line );
					} else if ( false !== stripos( $current_line, 'Allow' ) || false !== stripos( $current_line, 'Deny' ) ) {
						if ( false !== stripos( $current_line, 'Allow' ) ) {
							$htccss_allow_old_array[] = trim( str_ireplace( 'Allow from ', '', $current_line ), "\n" );
						} else if ( false !== stripos( $current_line, 'Deny' ) ) {
							$htccss_deny_old_array[] = trim( str_ireplace( 'Deny from ', '', $current_line ), "\n" );
						}
					}
				}
				if ( ! empty( $htccss_order_line ) ) {
					$htccss_options['order'] = $htccss_order_line;
					$htccss_options['allow'] = implode( "\n", $htccss_allow_old_array );
					$htccss_options['deny'] = implode( "\n", $htccss_deny_old_array );
				}
			}
		}
	}
}

if ( ! function_exists ( 'htccss_mod_rewrite_rules' ) ) {
	function htccss_mod_rewrite_rules( $rules ) {
		global $htccss_options;
		$home_path = get_home_path();
		if ( ! file_exists( $home_path . '.htaccess' ) ) {
			$htccss_allow_array = explode( "\n", trim( $htccss_options['allow'], "\n" ) );
			$htccss_deny_array = explode( "\n", trim( $htccss_options['deny'], "\n" ) );
			$order_allow_deny_content = '';
			if ( false === stripos( $rules, 'Order ' ) && ( '' != $htccss_options['allow'] || '' != $htccss_options['deny'] ) ) {
				$order_allow_deny_content .= $htccss_options['order'] . "\n";
				if ( $allow = stripos( $htccss_options['order'], 'Allow' ) < $deny = stripos( $htccss_options['order'], 'Deny' ) ) {
					if ( '' != $htccss_options['allow'] ) {
						foreach ( $htccss_allow_array as $htccss_allow )
							$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
					} elseif ( '' != $htccss_options['deny'] )
						$order_allow_deny_content .= 'Allow from all' . "\n";

					if ( '' != $htccss_options['deny'] ) {
						foreach ( $htccss_deny_array as $htccss_deny )
							$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
					}
				} else {
					if ( '' != $htccss_options['deny'] ) {
						foreach ( $htccss_deny_array as $htccss_deny )
							$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
					}
					if ( '' != $htccss_options['allow'] ) {
						foreach ( $htccss_allow_array as $htccss_allow )
							$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
					}
				}
				$rules = $order_allow_deny_content . $rules ;
			}
		}
		return $rules;
	}
}

if ( ! function_exists ( 'htccss_generate_htaccess' ) ) {
	function htccss_generate_htaccess() {
		global $htccss_options;
		$home_path = get_home_path();
		$htaccess_file = $home_path . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$handle = fopen( $htaccess_file, "r" );
			if ( $handle ) {
				$previous_line = $content = '';
				$order_flag				=	false;
				$write_order_flag		=	false;
				$htccss_allow_array		=	explode( "\n", trim( $htccss_options['allow'], "\n" ) );
				$htccss_deny_array		=	explode( "\n", trim( $htccss_options['deny'], "\n" ) );
				$htccss_allow_old_array	=	array();
				$htccss_deny_old_array	=	array();
				$htccss_order_line		=	'';
				if ( ! empty( $htccss_allow_array ) || ! empty( $htccss_deny_array ) ) {
					while ( ! feof( $handle ) ) {
						$current_line = fgets( $handle );
						if ( false !== stripos( $current_line, 'Order ' ) ) {
							$htccss_order_line = trim( $current_line, "\n" );
							$order_flag = true;
						} else if ( $order_flag && ( false !== stripos( $current_line, 'Allow' ) || false !== stripos( $current_line, 'Deny' ) ) ) {
							/**/
						} else {
							if ( $order_flag && ! $write_order_flag ) {
								$write_order_flag = true;
								$order_allow_deny_content = '';
								$order_allow_deny_content .= $htccss_options['order'] . "\n";
								if ( $allow = stripos( $htccss_options['order'], 'Allow' ) < $deny = stripos( $htccss_options['order'], 'Deny' ) ) {								
									if ( '' != $htccss_options['allow'] ) {
										foreach ( $htccss_allow_array as $htccss_allow )
											$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
									} elseif ( '' != $htccss_options['deny'] )
										$order_allow_deny_content .= 'Allow from all' . "\n";

									if ( '' != $htccss_options['deny'] ) {
										foreach ( $htccss_deny_array as $htccss_deny )
											$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
									}
								} else {
									if ( '' != $htccss_options['deny'] ) {
										foreach ( $htccss_deny_array as $htccss_deny )
											$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
									}
									if ( '' != $htccss_options['allow'] ) {
										foreach ( $htccss_allow_array as $htccss_allow )
											$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
									}
								}
								$content .= $order_allow_deny_content;
							}
							$content .= trim( $current_line, "\n" ) . "\n";
						}
						$previous_line = $current_line;
					}
					if ( ! $order_flag ) {
						$order_allow_deny_content = '';
						if ( '' != $htccss_options['allow'] || '' != $htccss_options['deny'] ) {
							$order_allow_deny_content .= $htccss_options['order'] . "\n";
							if ( $allow = stripos( $htccss_options['order'], 'Allow' ) < $deny = stripos( $htccss_options['order'], 'Deny' ) ) {
								if ( '' != $htccss_options['allow'] ) {
									foreach ( $htccss_allow_array as $htccss_allow )
										$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
								} elseif ( '' != $htccss_options['deny'] )
									$order_allow_deny_content .= 'Allow from all' . "\n";

								if ( '' != $htccss_options['deny'] ) {
									foreach ( $htccss_deny_array as $htccss_deny )
										$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
								}
							} else {
								if ( '' != $htccss_options['deny'] ) {
									foreach ( $htccss_deny_array as $htccss_deny )
										$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
								}
								if ( '' != $htccss_options['allow'] ) {
									foreach ( $htccss_allow_array as $htccss_allow )
										$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
								}
							}
							$content = $order_allow_deny_content . "\n" . $content;
						}
					}
					$temp_file = tempnam( '/tmp','allow_' );
					$fp = fopen( $temp_file, 'w' );
					fwrite( $fp, $content );
					fclose( $fp );
					rename( $temp_file, $htaccess_file );
				}
			}
			/* give htaccess_file 644 access rights */
			@chmod( $htaccess_file, 0644 );
		}
	}
}

if ( ! function_exists ( 'htccss_admin_head' ) ) {
	function htccss_admin_head() {
		if ( isset( $_REQUEST['page'] ) && 'htaccess.php' == $_REQUEST['page'] ) {
			wp_enqueue_style( 'htccss_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'htccss_script', plugins_url( 'js/script.js', __FILE__ ) );
		}
	}
}
if ( ! function_exists( 'htccss_show_notices' ) ) {
	function htccss_show_notices() {
		global $hook_suffix;
		if ( 'plugins.php' == $hook_suffix ) {  
			global $htccss_plugin_info, $bstwbsftwppdtplgns_cookie_add;
			$banner_array = array(
				array( 'gglnltcs_hide_banner_on_plugin_page', 'bws-google-analytics/bws-google-analytics.php', '1.6.2' ),
				array( 'htccss_hide_banner_on_plugin_page', 'htaccess/htaccess.php', '1.6.3' ),
				array( 'sbscrbr_hide_banner_on_plugin_page', 'subscriber/subscriber.php', '1.1.8' ),
				array( 'lmtttmpts_hide_banner_on_plugin_page', 'limit-attempts/limit-attempts.php', '1.0.2' ),
				array( 'sndr_hide_banner_on_plugin_page', 'sender/sender.php', '0.5' ),
				array( 'srrl_hide_banner_on_plugin_page', 'user-role/user-role.php', '1.4' ),
				array( 'pdtr_hide_banner_on_plugin_page', 'updater/updater.php', '1.12' ),
				array( 'cntctfrmtdb_hide_banner_on_plugin_page', 'contact-form-to-db/contact_form_to_db.php', '1.2' ),
				array( 'cntctfrmmlt_hide_banner_on_plugin_page', 'contact-form-multi/contact-form-multi.php', '1.0.7' ),
				array( 'gglmps_hide_banner_on_plugin_page', 'bws-google-maps/bws-google-maps.php', '1.2' ),
				array( 'fcbkbttn_hide_banner_on_plugin_page', 'facebook-button-plugin/facebook-button-plugin.php', '2.29' ),
				array( 'twttr_hide_banner_on_plugin_page', 'twitter-plugin/twitter.php', '2.34' ),
				array( 'pdfprnt_hide_banner_on_plugin_page', 'pdf-print/pdf-print.php', '1.7.1' ),
				array( 'gglplsn_hide_banner_on_plugin_page', 'google-one/google-plus-one.php', '1.1.4' ),
				array( 'gglstmp_hide_banner_on_plugin_page', 'google-sitemap-plugin/google-sitemap-plugin.php', '2.8.4' ),
				array( 'cntctfrmpr_for_ctfrmtdb_hide_banner_on_plugin_page', 'contact-form-pro/contact_form_pro.php', '1.14' ),
				array( 'cntctfrm_for_ctfrmtdb_hide_banner_on_plugin_page', 'contact-form-plugin/contact_form.php', '3.62' ),
				array( 'cntctfrm_hide_banner_on_plugin_page', 'contact-form-plugin/contact_form.php', '3.47' ),
				array( 'cptch_hide_banner_on_plugin_page', 'captcha/captcha.php', '3.8.4' ),
				array( 'gllr_hide_banner_on_plugin_page', 'gallery-plugin/gallery-plugin.php', '3.9.1' )
			);
			if ( ! $htccss_plugin_info )
				$htccss_plugin_info = get_plugin_data( __FILE__ );
			
			$all_plugins = get_plugins();
			$this_banner = 'htccss_hide_banner_on_plugin_page';
			foreach ( $banner_array as $key => $value ) {
				if ( $this_banner == $value[0] ) {
					global $wp_version;
					if ( ! isset( $bstwbsftwppdtplgns_cookie_add ) ) {
						echo '<script type="text/javascript" src="' . plugins_url( 'js/c_o_o_k_i_e.js', __FILE__ ) . '"></script>';
						$bstwbsftwppdtplgns_cookie_add = true;
					} ?>
					<script type="text/javascript">
						(function($) {
							$(document).ready( function() {
								var hide_message = $.cookie( "htccss_hide_banner_on_plugin_page" );
								if ( hide_message == "true") {
									$( ".htccss_message" ).css( "display", "none" );
								} else {
									$( ".htccss_message" ).css( "display", "block" );
								}
								$( ".htccss_close_icon" ).click( function() {
									$( ".htccss_message" ).css( "display", "none" );
									$.cookie( "htccss_hide_banner_on_plugin_page", "true", { expires: 32 } );
								});
							});
						})(jQuery);
					</script>
					<div class="updated" style="padding: 0; margin: 0; border: none; background: none;">
						<div class="htccss_message bws_banner_on_plugin_page" style="display: none;">
							<img class="htccss_close_icon close_icon" title="" src="<?php echo plugins_url( 'images/close_banner.png', __FILE__ ); ?>" alt=""/>
							<div class="button_div">
								<a class="button" target="_blank" href="http://bestwebsoft.com/products/htaccess/?k=d97ae872794372d2f58c3f55655bb693&pn=110&v=<?php echo $htccss_plugin_info["Version"]; ?>&wp_v=<?php echo $wp_version; ?>"><?php _e( "Learn More", 'htaccess' ); ?></a>
							</div>
							<div class="text"><?php
								_e( "It's time to upgrade your <strong>Htaccess</strong> to <strong>PRO</strong> version", 'htaccess' ); ?>!<br />
								<span><?php _e( 'Extend standard plugin functionality with new great options', 'htaccess' ); ?>.</span>
							</div> 	
							<div class="icon">
								<img title="" src="<?php echo plugins_url( 'images/banner.png', __FILE__ ); ?>" alt=""/>
							</div>
						</div>  
					</div>
					<?php break;
				}
				if ( isset( $all_plugins[ $value[1] ] ) && $all_plugins[ $value[1] ]["Version"] >= $value[2] && is_plugin_active( $value[1] ) && ! isset( $_COOKIE[ $value[0] ] ) ) {
					break;
				}
			}
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_copy_all' ) ) {
	function htccss_lmtttmpts_copy_all() {
		global $wpdb, $htccss_options;
		$htccss_options = get_option( 'htccss_options' );

		htccss_get_htaccess();
		$prefix = $wpdb->prefix . 'lmtttmpts_';
		$blocked = ( $wpdb->get_col(
			"SELECT `ip`
			FROM `" . $prefix . "failed_attempts`
			WHERE `block` = true"
		) );
		foreach ( $blocked as $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ), array( '\.', '\/' ), $ip ) . ')($|\s|\n|\r|\t)/';
			if ( ! preg_match( $pattern , $htccss_options['deny'] ) ) {
				$htccss_options['deny'] .= " " . $ip . " ";
			}
		}
		unset( $ip );
		$blacklist = ( $wpdb->get_col(
			"SELECT `ip`
			FROM `" . $prefix . "blacklist`"
		) );
		foreach ( $blacklist as $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr) . ')($|\s|\n|\r|\t)/';
						if ( ! preg_match( $pattern, $htccss_options['deny'] ) ) {
							$htccss_options['deny'] .= " " . $cidr . " ";
						}
					}
					unset ( $cidr );
				}
			} elseif ( ! preg_match( $pattern, $htccss_options['deny'] ) ) {
				$htccss_options['deny'] .= " " . $ip . " ";
			}
		}
		unset( $ip );
		$whitelist = ( $wpdb->get_col(
			"SELECT `ip`
			FROM `" . $prefix . "whitelist`"
		) );
		foreach ( $whitelist as $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr ) . ')($|\s|\n|\r|\t)/';
						if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
							$htccss_options['deny'] = str_replace( $cidr . " ", " ", $htccss_options['deny'] );
						}
						if ( ! preg_match( $pattern , $htccss_options['allow'] ) ) {
							$htccss_options['allow'] .= " " . $cidr . " ";
						}
					}
					unset( $cidr );
				}
			} elseif ( ! preg_match( $pattern, $htccss_options['allow'] ) ) {
				$htccss_options['allow'] .= " " . $ip . " ";
				if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
					$htccss_options['deny'] = str_replace( $ip . " ", " ", $htccss_options['deny'] );
				}
			}
		}
		unset( $ip );
		$htccss_options['deny'] = preg_replace( "/ {2,}/", " ", $htccss_options['deny'] );
		$htccss_options['allow'] = preg_replace( "/ {2,}/", " ", $htccss_options['allow'] );
		if ( preg_match( '/^\s*$/', $htccss_options['deny'] ) ) {
			$htccss_options['deny'] = "";
		}
		if ( preg_match( '/^\s*$/', $htccss_options['allow'] ) ) {
			$htccss_options['allow'] = "";
		}
		if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_all' ) ) {
	function htccss_lmtttmpts_delete_all() {
		global $wpdb, $htccss_options;
		$htccss_options = get_option( 'htccss_options' );

		htccss_get_htaccess();
		$prefix = $wpdb->prefix . 'lmtttmpts_';
		$blocked = ( $wpdb->get_col(
			"SELECT `ip`
			FROM `" . $prefix . "failed_attempts`
			WHERE `block` = true"
		) );
		foreach ( $blocked as $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip ) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
				$htccss_options['deny'] = str_replace( $ip . " ", " ", $htccss_options['deny'] ) ;
			}
		}
		unset( $ip );
		$blacklist = ( $wpdb->get_col(
			"SELECT `ip`
			FROM `" . $prefix . "blacklist`"
		) );
		foreach ( $blacklist as $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ($cidrs as $cidr) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr) . ')($|\s|\n|\r|\t)/';
						if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
							$htccss_options['deny'] = str_replace( $cidr . " ", " ", $htccss_options['deny'] );
						}
					}
					unset( $cidr );
				}
			} elseif ( preg_match( $pattern, $htccss_options['deny'] ) ) {
				$htccss_options['deny'] = str_replace( $ip . " ", " ", $htccss_options['deny'] );
			}
		}
		unset( $ip );
		$whitelist = ( $wpdb->get_col(
			"SELECT `ip`
			FROM `" . $prefix . "whitelist`"
		) );
		foreach ( $whitelist as $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ), array( '\.', '\/' ), $cidr ) . ')($|\s|\n|\r|\t)/';
						if ( preg_match( $pattern, $htccss_options['allow'] ) ) {
							$htccss_options['allow'] = str_replace( $cidr . " ", " ", $htccss_options['allow'] );
						}
					}
					unset( $cidr );
				}
			} elseif ( preg_match( $pattern, $htccss_options['allow'] ) ) {
				$htccss_options['allow'] = str_replace( $ip . " ", " ", $htccss_options['allow'] );
			}
		}
		unset( $ip );
		$htccss_options['deny'] = preg_replace( "/ {2,}/", " ", $htccss_options['deny'] );
		$htccss_options['allow'] = preg_replace( "/ {2,}/", " ", $htccss_options['allow'] );
		if ( preg_match( '/^\s*$/', $htccss_options['deny'] ) ) {
			$htccss_options['deny'] = "";
		}
		if ( preg_match( '/^\s*$/', $htccss_options['allow'] ) ) {
			$htccss_options['allow'] = "";
		}
		if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_block' ) ) {
	function htccss_lmtttmpts_block( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		
		if ( ! is_array( $ip ) )
			$ip_array[] = $ip;
		else
			$ip_array = $ip;

		foreach ( $ip_array as $key => $ip ) {		
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip ) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr) . ')($|\s|\n|\r|\t)/';
						if ( ! preg_match( $pattern, $htccss_options['deny'] ) ) {
							$htccss_options['deny'] .= " " . $cidr . " ";
						}
					}
					unset( $cidr );
				}
			} elseif ( ! preg_match( $pattern, $htccss_options['deny'] ) ) {
				$htccss_options['deny'] .= " " . $ip . " ";
			}
		}
		$htccss_options['deny'] = preg_replace( "/ {2,}/", " ", $htccss_options['deny'] );
		$htccss_options['allow'] = preg_replace( "/ {2,}/", " ", $htccss_options['allow'] );
		if ( preg_match( '/^\s*$/', $htccss_options['deny'] ) ) {
			$htccss_options['deny'] = "";
		}
		if ( preg_match( '/^\s*$/', $htccss_options['allow'] ) ) {
			$htccss_options['allow'] = "";
		}
		if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_reset_block' ) ) {
	function htccss_lmtttmpts_reset_block( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();

		if ( ! is_array( $ip ) )
			$ip_array[] = $ip;
		else
			$ip_array = $ip;		
		
		foreach ( $ip_array as $key => $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip ) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr ) . ')($|\s|\n|\r|\t)/';
						if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
							$htccss_options['deny'] = str_replace( $cidr . " ", " ", $htccss_options['deny'] );
						}
					}
					unset( $cidr );
				}
			} elseif ( preg_match( $pattern, $htccss_options['deny'] ) ) {
				$htccss_options['deny'] = str_replace( $ip . " ", " ", $htccss_options['deny'] );
			}
		}
		$htccss_options['deny'] = preg_replace( "/ {2,}/", " ", $htccss_options['deny'] );
		$htccss_options['allow'] = preg_replace( "/ {2,}/", " ", $htccss_options['allow'] );
		if ( preg_match( '/^\s*$/', $htccss_options['deny'] ) ) {
			$htccss_options['deny'] = "";
		}
		if ( preg_match( '/^\s*$/', $htccss_options['allow'] ) ) {
			$htccss_options['allow'] = "";
		}
		if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_from_whitelist' ) ) {
	function htccss_lmtttmpts_delete_from_whitelist( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();

		if ( ! is_array( $ip ) )
			$ip_array[] = $ip;
		else
			$ip_array = $ip;
		
		foreach ( $ip_array as $key => $ip ) {			
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip ) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr ) . ')($|\s|\n|\r|\t)/';
						if ( preg_match( $pattern, $htccss_options['allow'] ) ) {
							$htccss_options['allow'] = str_replace( $cidr . " ", " ", $htccss_options['allow'] );
						}
					}
					unset( $cidr );
				}
			} elseif ( preg_match( $pattern, $htccss_options['allow'] ) ) {
				$htccss_options['allow'] = str_replace( $ip . " ", " ", $htccss_options['allow'] );
			}
		}
		$htccss_options['deny'] = preg_replace( "/ {2,}/", " ", $htccss_options['deny'] );
		$htccss_options['allow'] = preg_replace( "/ {2,}/", " ", $htccss_options['allow'] );
		if ( preg_match( '/^\s*$/', $htccss_options['deny'] ) ) {
			$htccss_options['deny'] = "";
		}
		if ( preg_match( '/^\s*$/', $htccss_options['allow'] ) ) {
			$htccss_options['allow'] = "";
		}
		if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_add_to_whitelist' ) ) {
	function htccss_lmtttmpts_add_to_whitelist( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = get_option( 'htccss_options' );
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();

		if ( ! is_array( $ip ) )
			$ip_array[] = $ip;
		else
			$ip_array = $ip;
		
		foreach ( $ip_array as $key => $ip ) {
			$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $ip ) . ')($|\s|\n|\r|\t)/';
			if ( preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip ) ) {
				$ips = explode( '-', $ip ); /*$ips[0] - diapason from, $ips[1] - diapason to*/
				if ( sprintf( '%u', ip2long( $ips[0] ) ) <= sprintf( '%u', ip2long( $ips[1] ) ) ) {
					$cidrs = htccss_range2cidrlist( $ips[0], $ips[1] );
					foreach ( $cidrs as $cidr ) {
						$pattern = '/(^|\s+|\n|\r|\t)(' . str_replace( array( '.', '/' ) , array( '\.', '\/' ), $cidr ) . ')($|\s|\n|\r|\t)/';
						if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
							$htccss_options['deny'] = str_replace( $cidr . " ", " ", $htccss_options['deny'] );
						}
						if ( ! preg_match( $pattern , $htccss_options['allow'] ) ) {
							$htccss_options['allow'] .= " " . $cidr . " ";
						}
					}
					unset( $cidr );
				}
			} elseif ( ! preg_match( $pattern, $htccss_options['allow'] ) ) {
				$htccss_options['allow'] .= " " . $ip . " ";
				if ( preg_match( $pattern, $htccss_options['deny'] ) ) {
					$htccss_options['deny'] = str_replace( $ip . " ", " ", $htccss_options['deny'] );
				}
			}
		}
		$htccss_options['deny'] = preg_replace( "/ {2,}/", " ", $htccss_options['deny'] );
		$htccss_options['allow'] = preg_replace( "/ {2,}/", " ", $htccss_options['allow'] );
		if ( preg_match( '/^\s*$/', $htccss_options['deny'] ) ) {
			$htccss_options['deny'] = "";
		}
		if ( preg_match( '/^\s*$/', $htccss_options['allow'] ) ) {
			$htccss_options['allow'] = "";
		}
		if ( empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		update_option( 'htccss_options', $htccss_options );
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_imask' ) ) {
	function htccss_imask( $this ) {
		/*use base_convert not dechex because dechex is broken and returns 0x80000000 instead of 0xffffffff*/
		return base_convert( ( pow( 2,32 ) - pow( 2, ( 32-$this ) ) ), 10, 16);
	}
}

if ( ! function_exists( 'htccss_imaxblock' ) ) {
	function htccss_imaxblock( $ibase, $tbit ) {
		while ( $tbit > 0 ) {
			$im = hexdec( htccss_imask( $tbit-1 ) );
			$imand = $ibase & $im;
			if ( $imand != $ibase ) {
				break;
			}
			$tbit--;
		}
		return $tbit;
	}
}

if ( ! function_exists( 'htccss_range2cidrlist' ) ) {
	function htccss_range2cidrlist( $istart, $iend ) {
		/* this function returns an array of cidr lists that map the range given */
		$s = explode( ".", $istart );
		/* PHP ip2long does not handle leading zeros on IP addresses! 172.016 comes back as 172.14, seems to be treated as octal! */
		$start = $dot = "";
		while ( list( $key,$val ) = each( $s ) ) {
			$start = sprintf( "%s%s%d", $start, $dot, $val );
			$dot = ".";
		}
		$end = $dot = "";
		$e = explode( ".",$iend );
		while ( list( $key,$val ) = each( $e ) ) {
			$end = sprintf( "%s%s%d", $end, $dot, $val );
			$dot = ".";
		}
		$start = ip2long( $start );
		$end = ip2long( $end );
		$result = array();
		while ( $end > $start ) {
			$maxsize = htccss_imaxblock( $start,32 );
			$x = log( $end - $start + 1 )/log( 2 );
			$maxdiff = floor( 32 - floor( $x ) );
			$ip = long2ip( $start );
			if ( $maxsize < $maxdiff ) {
				$maxsize = $maxdiff;
			}
			array_push( $result,"$ip/$maxsize" );
			$start += pow( 2, ( 32-$maxsize ) );
		}
		return $result;
	}
}

/* Function for delete delete options */
if ( ! function_exists ( 'htccss_delete_options' ) ) {
	function htccss_delete_options() {
		delete_option( 'htccss_options' );
	}
}

add_action( 'admin_menu', 'add_htccss_admin_menu' );
add_action( 'init', 'htccss_plugin_init' );
add_action( 'admin_init', 'htccss_plugin_admin_init' );
add_action( 'admin_enqueue_scripts', 'htccss_admin_head' );
add_action( 'admin_notices', 'htccss_show_notices' );
/* Adds "Settings" link to the plugin action page */
add_filter( 'plugin_action_links', 'htccss_plugin_action_links', 10, 2 );
/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'htccss_register_plugin_links', 10, 2 );
add_filter( 'mod_rewrite_rules', 'htccss_mod_rewrite_rules' );
/* Adding hooks for interaction with Limit Attempts plugin */
add_action( 'lmtttmpts_htaccess_hook_for_copy_all', 'htccss_lmtttmpts_copy_all' );
add_action( 'lmtttmpts_htaccess_hook_for_delete_all', 'htccss_lmtttmpts_delete_all' );
add_action( 'lmtttmpts_htaccess_hook_for_block', 'htccss_lmtttmpts_block' );
add_action( 'lmtttmpts_htaccess_hook_for_reset_block', 'htccss_lmtttmpts_reset_block' );
add_action( 'lmtttmpts_htaccess_hook_for_delete_from_whitelist', 'htccss_lmtttmpts_delete_from_whitelist' );
add_action( 'lmtttmpts_htaccess_hook_for_add_to_whitelist', 'htccss_lmtttmpts_add_to_whitelist' );

register_uninstall_hook( __FILE__, 'htccss_delete_options' );
?>