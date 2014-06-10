<?php
/*
Plugin Name: Htaccess
Plugin URI:  http://bestwebsoft.com/plugin/
Description: The plugin Htaccess allows controlling access to your website using the directives Allow and Deny. Access can be controlled based on the client's hostname, IP address, or other characteristics of the client's request.
Author: BestWebSoft
Version: 1.3
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2014  BestWebSoft  ( http://support.bestwebsoft.com )

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
		global $bstwbsftwppdtplgns_options, $wpmu, $bstwbsftwppdtplgns_added_menu;
		$bws_menu_info = get_plugin_data( plugin_dir_path( __FILE__ ) . "bws_menu/bws_menu.php" );
		$bws_menu_version = $bws_menu_info["Version"];
		$base = plugin_basename( __FILE__ );

		if ( ! isset( $bstwbsftwppdtplgns_options ) ) {
			if ( 1 == $wpmu ) {
				if ( ! get_site_option( 'bstwbsftwppdtplgns_options' ) )
					add_site_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_site_option( 'bstwbsftwppdtplgns_options' );
			} else {
				if ( ! get_option( 'bstwbsftwppdtplgns_options' ) )
					add_option( 'bstwbsftwppdtplgns_options', array(), '', 'yes' );
				$bstwbsftwppdtplgns_options = get_option( 'bstwbsftwppdtplgns_options' );
			}
		}

		if ( isset( $bstwbsftwppdtplgns_options['bws_menu_version'] ) ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			unset( $bstwbsftwppdtplgns_options['bws_menu_version'] );
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
			require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );
		} else if ( ! isset( $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] ) || $bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] < $bws_menu_version ) {
			$bstwbsftwppdtplgns_options['bws_menu']['version'][ $base ] = $bws_menu_version;
			update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options, '', 'yes' );
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
		add_submenu_page( 'bws_plugins', __( 'Htaccess Settings', 'htaccess' ), __( 'Htaccess', 'htaccess' ), 'manage_options', "htaccess.php", 'htccss_settings_page' );

	}
}


if ( ! function_exists ( 'htccss_plugin_init' ) ) {
	function htccss_plugin_init() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'htaccess', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'htccss_plugin_admin_init' ) ) {
	function htccss_plugin_admin_init() {
 		global $bws_plugin_info, $htccss_plugin_info;

 		$htccss_plugin_info = get_plugin_data( __FILE__, false );

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '110', 'version' => $htccss_plugin_info["Version"] );

		/* Check version on WordPress */
		htccss_version_check();

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
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "<strong>" . $htccss_plugin_info['Name'] . " </strong> " . __( 'requires', 'htaccess' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'htaccess') . "<br /><br />" . __( 'Back to the WordPress', 'htaccess') . " <a href='" . get_admin_url( null, 'plugins.php' ) . "'>" . __( 'Plugins page', 'htaccess') . "</a>." );
			}
		}
	}
}

/* register settings function */
if ( ! function_exists( 'register_htccss_settings' ) ) {
	function register_htccss_settings() {
		global $wpmu, $htccss_options, $htccss_plugin_info;

		$htccss_option_defaults = array(
			'order'					=> 'Order Allow,Deny',
			'allow'					=> '',
			'deny'					=> '',
			'plugin_option_version' => $htccss_plugin_info["Version"]
		);

		/* Install the option defaults */
		if ( 1 == $wpmu ) {
			if ( ! get_site_option( 'htccss_options' ) )
				add_site_option( 'htccss_options', $htccss_option_defaults, '', 'yes' );
		} else {
			if ( ! get_option( 'htccss_options' ) )
				add_option( 'htccss_options', $htccss_option_defaults, '', 'yes' );
		}

		/* Get options from the database */
		if ( 1 == $wpmu )
			$htccss_options = get_site_option( 'htccss_options' );
		else
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
		/* Static so we don't call plugin_basename on every plugin row. */
		static $this_plugin;
		if ( ! $this_plugin )
			$this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="admin.php?page=htaccess.php">' . __( 'Settings', 'htaccess' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}
/* End function htccss_plugin_action_links */

if ( ! function_exists( 'htccss_register_plugin_links' ) ) {
	function htccss_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
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
		global $htccss_admin_fields_enable, $htccss_options;
		$error = "";
		/* Save data for settings page */
		if ( isset( $_REQUEST['htccss_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'htccss_nonce_name' ) ) {

			if ( 'Order Allow,Deny' != trim( $_REQUEST['htccss_order'] ) && 'Order Deny,Allow' != trim( $_REQUEST['htccss_order'] ) )
				$error = __( "Wrong 'Order fields'. You can enter:", 'htaccess' ) . ' <strong>Order Allow,Deny</strong> ' . __( "or", 'htaccess' ) . ' <strong>Order Deny,Allow</strong>';
			else
				$htccss_options['order']	= trim( $_REQUEST['htccss_order'] );

			$htccss_options['allow'] = trim( trim( preg_replace( '/Allow from /i', '', $_REQUEST['htccss_allow'] ) ), "\n" );

			$htccss_options['deny'] = trim( trim( preg_replace( '/Allow from /i', '', $_REQUEST['htccss_deny'] ) ), "\n" );

			if ( "" == $error ) {
				/* Update options in the database */
				update_option( 'htccss_options', $htccss_options, '', 'yes' );
				$message = __( "Settings saved.", 'htaccess' );
				htccss_generate_htaccess();
			}
		} else {
			htccss_get_htaccess();
		}
		/* Display form on the setting page */
		?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Htaccess Settings', 'htaccess' ); ?></h2>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab nav-tab-active" href="admin.php?page=htaccess.php"><?php _e( 'Settings', 'htaccess' ); ?></a>
				<a class="nav-tab" href="http://bestwebsoft.com/plugin/htaccess/#faq" target="_blank"><?php _e( 'FAQ', 'htaccess' ); ?></a>
			</h2>			
			<div class="error">
				<p><strong><?php _e( "Notice:", 'htaccess' ); ?></strong> <?php _e( "It is very important to be extremely attentive when making changes to .htaccess file. If after making changes your site stops functioning, please see", 'htaccess' ); ?> <a href="http://wordpress.org/plugins/htaccess/faq/" target="_blank" title=""><?php _e( 'FAQ', 'htaccess' ); ?></a></p>
				<p><?php _e( 'The changes will be applied immediately after saving the changes, if you are not sure - do not click the "Save changes" button.', 'htaccess' ); ?></p>
			</div>
			<div id="htccss_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'htaccess' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'htaccess' ); ?></p></div>
			<div class="updated fade" <?php if ( ! isset( $_REQUEST['htccss_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
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
		</div>
	<?php }
}

if ( ! function_exists ( 'htccss_get_htaccess' ) ) {
	function htccss_get_htaccess() {
		global $htccss_options;
		$htaccess_file = get_home_path() . '.htaccess';
		/* if ( ( ! file_exists($home_path . '.htaccess') && is_writable($home_path) ) || is_writable($home_path . '.htaccess') ) */
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
					}
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
									}
									if ( '' != $htccss_options['deny'] ) {
										foreach ( $htccss_deny_array as $htccss_deny )
											$order_allow_deny_content .= 'Deny from ' . trim( $htccss_deny, "\n" ) . "\n";
									}
								} else {
									if ( '' != $htccss_options['deny'] ) {
										foreach( $htccss_deny_array as $htccss_deny )
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
								if ( '' != $htccss_options['deny'] ) {
									foreach ( $htccss_allow_array as $htccss_allow )
										$order_allow_deny_content .= 'Allow from ' . trim( $htccss_allow, "\n" ) . "\n";
								}
								if ( '' != $htccss_options['allow'] ) {
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
		} else {
			/**/
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

/* Function for delete delete options */
if ( ! function_exists ( 'htccss_delete_options' ) ) {
	function htccss_delete_options() {
		delete_option( 'htccss_options' );
		delete_site_option( 'htccss_options' );
	}
}

add_action( 'admin_menu', 'add_htccss_admin_menu' );
add_action( 'init', 'htccss_plugin_init' );
add_action( 'admin_init', 'htccss_plugin_admin_init' );
add_action( 'admin_enqueue_scripts', 'htccss_admin_head' );

/* Adds "Settings" link to the plugin action page */
add_filter( 'plugin_action_links', 'htccss_plugin_action_links', 10, 2 );
/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'htccss_register_plugin_links', 10, 2 );
add_filter( 'mod_rewrite_rules', 'htccss_mod_rewrite_rules' );

register_uninstall_hook( __FILE__, 'htccss_delete_options' );
?>