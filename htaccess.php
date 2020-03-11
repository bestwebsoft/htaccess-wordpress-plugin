<?php
/*
Plugin Name: Htaccess by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/htaccess/
Description: Protect WordPress website – allow and deny access for certain IP addresses, hostnames, etc.
Author: BestWebSoft
Text Domain: htaccess
Domain Path: /languages
Version: 1.8.2
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2020 BestWebSoft ( https://support.bestwebsoft.com )

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
		global $wp_version, $submenu, $htccss_plugin_info;

		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		$settings = add_menu_page(
			__( 'Htaccess Settings', 'htaccess' ),
			'Htaccess',
			'manage_options',
			'htaccess.php',
			'htccss_settings_page',
			'none'
		);

		add_submenu_page(
			'htaccess.php',
			__( 'Htaccess Settings', 'htaccess' ),
			'Htaccess',
			'manage_options',
			'htaccess.php',
			'htccss_settings_page'
		);

		$editor_page = add_submenu_page(
			'htaccess.php',
			__( 'Editor', 'htaccess' ),
			__( 'Editor', 'htaccess' ),
			'manage_options',
			'htaccess-editor.php',
			'htccss_settings_page'
		);

		add_submenu_page( 'htaccess.php',
			'BWS Panel',
			'BWS Panel',
			'manage_options',
			'htccss-bws-panel',
			'bws_add_menu_render'
		);

		if ( isset( $submenu['htaccess.php'] ) ) {
			$submenu['htaccess.php'][] = array(
				'<span style="color:#d86463"> ' . __( 'Upgrade to Pro', 'htaccess' ) . '</span>',
				'manage_options',
				'https://bestwebsoft.com/products/wordpress/plugins/htaccess/?k=ac1e1061bf4e95ba51406b4cc32f61fa&pn=110&v=' . $htccss_plugin_info["Version"] . '&wp_v=' . $wp_version
			);
		}

		add_action( 'load-' . $settings, 'htccss_add_tabs' );
		add_action( 'load-' . $editor_page, 'htccss_add_tabs' );
	}
}

if ( ! function_exists( 'htccss_plugins_loaded' ) ) {
	function htccss_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'htaccess', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'htccss_init' ) ) {
	function htccss_init() {
		global $htccss_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $htccss_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$htccss_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $htccss_plugin_info, '4.5' );
	}
}

if ( ! function_exists ( 'htccss_plugin_admin_init' ) ) {
	function htccss_plugin_admin_init() {
		global $pagenow, $bws_plugin_info, $htccss_plugin_info, $htccss_options;

		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '110', 'version' => $htccss_plugin_info["Version"] );
		}
		/* Call register settings function */
		if ( isset( $_GET['page'] ) && ( 'htaccess.php' == $_GET['page'] || 'htaccess-editor.php' == $_GET['page'] ) ) {
			register_htccss_settings();
		}

		if ( 'plugins.php' == $pagenow && ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) ) {
			if ( function_exists( 'bws_plugin_banner_go_pro' ) ) {
				if ( empty( $htccss_options ) ) {
					$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
				}
				bws_plugin_banner_go_pro( $htccss_options, $htccss_plugin_info, 'htccss', 'htaccess', 'd97ae872794372d2f58c3f55655bb693', '110', 'htaccess' );
			}
		}
	}
}

if ( ! function_exists ( 'htccss_plugin_activate' ) ) {
	function htccss_plugin_activate( $networkwide ) {
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'htccss_delete_options' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'htccss_delete_options' );
		}

		register_htccss_settings();
	}
}

/**
 * @return array Default plugin options
 * @since 1.7.9
 */
if ( ! function_exists( 'htccss_get_options_default' ) ) {
	function htccss_get_options_default() {
		global $htccss_plugin_info;
		$option_defaults = array(
			'order'						=> 'Order Deny,Allow',
			'allow'						=> '',
			'deny'						=> '',
			'plugin_option_version'		=> $htccss_plugin_info["Version"],
			'allow_xml'					=> htccss_check_xml_access(),
			'display_settings_notice'	=> 1,
			'first_install'				=> strtotime( "now" ),
			'suggest_feature_banner'	=> 1,
			'htaccess_backup'			=> 0,
			'amount_of_allow_forms'		=> 1,
			'amount_of_deny_forms'		=> 1,
		);
		return $option_defaults;
	}
}

/* register settings function */
if ( ! function_exists( 'register_htccss_settings' ) ) {
	function register_htccss_settings() {
		global $htccss_options, $htccss_plugin_info, $htccss_active_plugins, $htccss_auto_added, $wpdb;
		/**
		 * contains IPs, which have been added to .htaccess
		 * in cooperation with other plugins
		 * @since 1.7.2
		 */
		$htccss_auto_added = array( 'allow' => '', 'deny' => '' );

		$is_multisite = is_multisite();		

		/* Install the option defaults */
		if ( $is_multisite ) {
			if ( ! get_site_option( 'htccss_options' ) ) {
				$option_defaults = htccss_get_options_default();
				add_site_option( 'htccss_options', $option_defaults );
			}
		} else {
			if ( ! get_option( 'htccss_options' ) ) {
				$option_defaults = htccss_get_options_default();
				add_option( 'htccss_options', $option_defaults );
			}
		}
		/* Get options from the database */
		$htccss_options = $is_multisite ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );

		/**
		 * search for compatible plugins
		 * @since 1.7.2
		 */

		/* an array of compatible plugins */
		$plugins = array(
			'limit-attempts-pro/limit-attempts-pro.php',
			'limit-attempts/limit-attempts.php'
		);
		$htccss_active_plugins = array();
		if ( $is_multisite ) {
			$blogids  = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			$old_blog = $wpdb->blogid;
			foreach ( $plugins as $plugin ) {
				if ( is_plugin_active_for_network( $plugin ) ) {
					$htccss_active_plugins[] = $plugin;
				} else {
					/* search for active compatible plugins on blogs */
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						if ( is_plugin_active( $plugin ) && ! in_array( $plugin, $htccss_active_plugins ) ) {
							$htccss_active_plugins[] = $plugin;
						}
					}
					switch_to_blog( $old_blog );
				}
			}
		} else {
			foreach ( $plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$htccss_active_plugins[] = $plugin;
					break;
				}
			}
		}
		/* Array merge incase this version has added new options */
		if ( ! isset( $htccss_options['plugin_option_version'] ) || $htccss_options['plugin_option_version'] != $htccss_plugin_info["Version"] ) {
			$option_defaults = htccss_get_options_default();
			$option_defaults['display_settings_notice'] = 0;
			$htccss_options = array_merge( $option_defaults, $htccss_options );
			$htccss_options['plugin_option_version'] = $htccss_plugin_info["Version"];
			/* show pro features */
			$htccss_options['hide_premium_options'] = array();
			htccss_get_htaccess();
			/**
			 * add blocked and blacklisted IPs from lists of Limit Attempts (Free or Pro) by BestWebSoft plugin to .htaccess
			 * @since 1.7.2
			 */
			if ( ! empty( $htccss_active_plugins ) ) {
				if ( $is_multisite ) {
					$blogids  = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
					$old_blog = $wpdb->blogid;
					foreach ( $blogids as $blog_id ) {
						switch_to_blog( $blog_id );
						foreach ( $htccss_active_plugins as $plugin ) {
							$option = htccss_get_option( $plugin );
							if ( $option && 1 == $option['block_by_htaccess'] ) {
								htccss_check_orders();
							}
						}
					}
					switch_to_blog( $old_blog );
				} else {
					foreach ( $htccss_active_plugins as $plugin ) {
						$option = htccss_get_option( $plugin );
						if ( $option && 1 == $option['block_by_htaccess'] ) {
							htccss_check_orders();
						}
					}
				}
			}

			if ( is_multisite() ) {
				switch_to_blog( 1 );
				register_uninstall_hook( __FILE__, 'htccss_delete_options' );
				restore_current_blog();
			} else {
				register_uninstall_hook( __FILE__, 'htccss_delete_options' );
			}

			htccss_update_options();
			htccss_generate_htaccess();
		}
	}
}

/* add help tab */
if ( ! function_exists( 'htccss_add_tabs' ) ) {
	function htccss_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id'		=> 'htccss',
			'section'	=> '200538709'
		);
		bws_help_tab( $screen, $args );
	}
}

if ( ! function_exists( 'htccss_plugin_action_links' ) ) {
	function htccss_plugin_action_links( $links, $file ) {
		if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && is_admin() ) ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
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
			if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && is_admin() ) ) {
				$links[] = '<a href="admin.php?page=htaccess.php">' . __( 'Settings', 'htaccess' ) . '</a>';
			}
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538709" target="_blank">' . __( 'FAQ', 'htaccess' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com" target="_blank">' . __( 'Support', 'htaccess' ) . '</a>';
		}
		return $links;
	}
}

/**
 * Check if string is a number and it less than specified number
 * @param    string      $value     the string that we have to check
 * @param    int         $max       the maximum number that can take the $value
 * @return   boolean
 */
if ( ! function_exists( 'htcsss_is_less' ) ) {
	function htcsss_is_less( $value, $max ) {
		return is_numeric( $value ) && intval( $value ) == $value && $max >= $value;
	}
}

/**
 * Check if string is an IP address
 * @param    string      $value     the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_ip' ) ) {
	function htccss_is_ip( $value ) {
		return
			/* IP v4 or v6*/
			filter_var( $value, FILTER_VALIDATE_IP ) ||
			/* numbers from 0 to 255 - an IP range */
			htcsss_is_less( $value, 255 ) ||
			/* IP v4 range */
			preg_match( '/(^((25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])\.){1,3}$)|(^((25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])\.){1,3}\*{1}$)|(^((25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])\.){2}(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9]){1}$)/', $value ) ||
			/* slash is in string */
			( preg_match_all( "/^(.*?)\/(.*?)$/", $value, $matches ) &&

				(	/* IP v6 CIDR */
					( filter_var( $matches[1][0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) && htcsss_is_less( $matches[2][0], 128 ) ) ||
					/* IP v4 CIDR */
					( filter_var( $matches[1][0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) && ( htcsss_is_less( $matches[2][0], 32 ) || filter_var( $matches[2][0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) )
				)
			);
	}
}

/**
 * Check if string is a hostname
 * @param    string      $value     the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_host' ) ) {
	function htccss_is_host( $value ) {
		return
			! is_numeric( $value ) &&
			! preg_match( "/^\d[\.\d]*$/", $value ) && /* string must not contains numbers and dots only */
			preg_match( "/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $value ) &&
			preg_match("/^.{1,253}$/", $value ) && /* hostnames used can be as long as 253 bytes */
			preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $value ); /* the first label (removing '.' and anything after it from the hostname) can only be up to 63 bytes */
	}
}

/**
 * Check if string is an environment variable
 * @param    string      $value     the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_env' ) ) {
	function htccss_is_env ( $value ) {
		return preg_match( "/^(env={1}([a-zA-Z0-9]|[a-zA-Z0-9\-]|[a-zA-Z0-9_])+)$/", $value );
	}
}

/**
 * Check entered value
 * @param    string    $value    the string that we have to check
 * @return   boolean
 */
if ( ! function_exists( 'htccss_is_wrong' ) ) {
	function htccss_is_wrong( $value ) {
		return
			! empty( $value ) &&
			preg_match( "/[a-z]|[A-Z]|[0-9]/", $value ) &&
			( htccss_is_ip( $value ) || htccss_is_env( $value ) || htccss_is_host( $value ) );
	}
}

/**
 * Filters option before saving to database
 * @param     string    $list_order     <textarea>`s value form pligin`s page
 * @return    string                    escaped string with list of IPs.
 */
if ( ! function_exists( 'htccss_esc_directive' ) ) {
	function htccss_esc_directive( $list_order ) {
		$list_order = trim( esc_textarea( $list_order ) );
		$list_order = preg_replace( "/(Allow from)|(Deny from)/i", '', $list_order );

		if ( empty( $list_order ) ) {
			return '';
		}
		/* split the string by any number of commas, colons, whitespaces, \r, \t, \n */
		$array_order = preg_split( "/[\t\n\r\s\,]+/", $list_order, -1, PREG_SPLIT_NO_EMPTY );
		$array_order = array_unique( $array_order );
		$array_order = array_filter( $array_order, 'htccss_is_wrong' );

		return empty( $array_order ) ? '' : strtolower( implode( "\n", $array_order ) );
	}
}

/* Function for display htaccess settings page in the admin area */
if ( ! function_exists( 'htccss_settings_page' ) ) {
	function htccss_settings_page() { 
		global $htccss_options;

		htccss_get_htaccess(); ?>
		<div id="htccss_wrap" class="wrap">
			<?php if ( 'htaccess.php' == $_GET['page'] ) {
				if ( ! class_exists( 'Bws_Settings_Tabs' ) )
					require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
				require_once( dirname( __FILE__ ) . '/includes/class-htccss-settings.php' );
				$page = new Htccss_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
				<h1><?php _e( 'Htaccess Settings', 'htaccess' ); ?></h1>
                <div class="error inline">
					<p><strong><?php _e( "Note", 'htaccess' ); ?></strong>: <?php printf( __( "Making changes to .htaccess file can crash your website. Double check all changes before saving them and read our %s.", 'htaccess' ), '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538709" target="_blank">' . __( 'FAQ', 'htaccess' ) . '</a>' ); ?></p>
				</div>
				<noscript>
					<div class="error inline"><p><strong><?php _e( "Please enable JavaScript in your browser.", 'htaccess' ); ?></strong></p></div>
				</noscript>
				<?php $page->display_content();
			} else { ?>
				<h1><?php _e( 'Htaccess Editor', 'htaccess' ); ?></h1>
                <div class="error inline">
					<p><strong><?php _e( "Note", 'htaccess' ); ?></strong>: <?php printf( __( "Making changes to .htaccess file can crash your website. Double check all changes before saving them and read our %s.", 'htaccess' ), '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538709" target="_blank">' . __( 'FAQ', 'htaccess' ) . '</a>' ); ?></p>
				</div>
				<?php
					if ( isset( $_POST['htccss_restore_backup_button'] ) && ! is_file( ABSPATH . 'htaccess-backup.txt') ) {
						$message = '<div class="error inline">
										<p>
											<strong>'
												. __( "You do not have a backup file", 'htaccess' ) .
											'</strong>
										</p>
									</div>';
						echo $message;
					} elseif ( isset( $_POST['htccss_restore_backup_button'] ) && is_file( ABSPATH . 'htaccess-backup.txt') ) {
						$message = '<div class="updated fade inline">
										<p>
											<strong>'
												. __( "The .htaccess file has been successfully restored from a backup.", 'htaccess' ) .
											'</strong>
										</p>
									</div>';
						echo $message;
					}
					if ( isset( $_POST['htccss_submit_button_custom'] ) ) {
						$message = '<div class="updated fade inline">
										<p>
											<strong>'
												. __( "Settings saved.", 'htaccess' ) .
											'</strong>
										</p>
									</div>';
						echo $message;
					}
				?>
				<form method="post">
					<table class="form-table">
						<?php $content = htccss_customise_htaccess(); ?>
						<tr valign="top">
							<th scope="row">.htaccess</th>
							<td>
								<textarea name="htccss_customise" id="htccss_customise" class="htccss_textarea"><?php echo $content; ?></textarea>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Create htaccess-backup.txt', 'htaccess' ); ?></th>
							<td>
								<input type="checkbox" name="htaccess_backup" value="1" <?php checked( $htccss_options['htaccess_backup'] );  ?> /> <span class="bws_info"><?php _e( 'Enable to create backup file.', 'htaccess' ); ?></span>
							</td>
						</tr>
					</table>
					<input type="hidden" name="htccss_form_custom" value="submit" />
					<p class="submit">
						<input name="htccss_submit_button_custom" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'htaccess' ); ?>" />
						<input type="submit" class="button" name="htccss_restore_backup_button" value="<?php _e( 'Restore .htaccess to Backup', 'htaccess' ); ?>">
					</p>
					<?php wp_nonce_field( plugin_basename( __FILE__ ), 'htccss_nonce_name' ); ?>
				</form>
			<?php } ?>
		</div>
	<?php }
}

/* Function for customise htaccess file */
if ( ! function_exists( 'htccss_customise_htaccess' ) ) {
	function htccss_customise_htaccess() {
		global $htccss_options;
		$htaccess_content = '';
		$htaccess_path					= ABSPATH . '.htaccess';
		$htaccess_backup_file_path		= ABSPATH . 'htaccess-backup.txt';
		if ( file_exists( $htaccess_path ) ) {
			$htaccess_content = file_get_contents( $htaccess_path );
		}
		if ( file_exists( $htaccess_backup_file_path ) ) {
			$htaccess_backup_file_content = file_get_contents( $htaccess_backup_file_path );
		}

		if ( ( isset( $_POST['htccss_submit_button_custom'] ) || isset( $_POST['htccss_restore_backup_button'] ) ) && check_admin_referer( plugin_basename( __FILE__ ), 'htccss_nonce_name' ) ) {

			if ( isset( $_POST['htccss_restore_backup_button'] ) && isset( $htaccess_backup_file_content ) ) {
				$fp = fopen( $htaccess_path, "w+" );
				fwrite( $fp, $htaccess_backup_file_content );
				fclose( $fp );
				$htaccess_content = file_get_contents( $htaccess_path );
			}
			if ( isset( $_POST['htaccess_backup'] ) && ! empty( $_POST['htccss_submit_button_custom'] ) ) {
				$fp = fopen( $htaccess_backup_file_path, "w+" );
				fwrite( $fp, $htaccess_content );
				fclose( $fp );
			}
			if ( ! empty( $_POST['htccss_submit_button_custom'] ) ) {
				$htccss_options['htaccess_backup'] = isset( $_POST['htaccess_backup'] ) ? 1 : 0;
			}
			if ( isset( $_REQUEST['htccss_form_custom'] ) ) {
				if ( isset( $_REQUEST['htccss_customise'] ) && ! empty( $_POST['htccss_submit_button_custom'] ) ) {
					$htaccess_content = trim( stripslashes( $_REQUEST['htccss_customise'] ) );
					file_put_contents( $htaccess_path, $htaccess_content );
					htccss_get_htaccess();
					update_option( 'htccss_options', $htccss_options );
				}
			}
		}

		return $htaccess_content;
	}
}

/* check for access to XML files */
if ( ! function_exists( 'htccss_check_xml_access' ) ) {
	function htccss_check_xml_access() {
		$check = 0;
		if ( is_multisite() && ! is_subdomain_install() ) {
			if ( ! function_exists( 'get_home_path' ) ) {
				require_once ( ABSPATH . 'wp-admin/includes/file.php' );
			}
			$htaccess_file = get_home_path() . '.htaccess';
			$reg_exp = preg_quote( "RewriteRule ([^/]+\.xml)$ $1 [L]" );
			$check   = file_exists( $htaccess_file ) && preg_match( "|{$reg_exp}|", file_get_contents( $htaccess_file ) ) ? 1 : 0;
		}
		return $check;
	}
}

/**
 * Convert array of IPs to string.
 * Function is needed to easy viewing of "Deny" and "Allow" options
 * on plugin settings page
 * @param      array    $array
 * @return     string
 */
if ( ! function_exists( 'htccss_implode' ) ) {
	function htccss_implode( $array ) {
		foreach( $array as $key => $item ) {
			$array[ $key ] = preg_replace( "/\s/", "\n", trim( $item ) );
		}
		$array = array_unique( $array );
		return implode( "\n", $array );
	}
}

/**
 * Get data for current directive
 * @since   1.7.2
 * @uses    during generation of the .htaccess file
 * @see     htccss_generate_htaccess()
 * @param   string    $option      list of IPs
 * @param   string    $directive   "Allow from" or "Deny from"
 * @return  string    Alllow/Deny directive
 */
if ( ! function_exists( 'htccss_get_order_content' ) ) {
	function htccss_get_order_content( $option, $directive ) {

		$args = preg_split( "/[\t\n\r\s\,]+/", $option, -1, PREG_SPLIT_NO_EMPTY );

		if ( empty( $args ) ) {
			return '';
		}
		/* split the arrays to form directives */
		$args_strings = array();
		if ( 400 < count( $args ) ) {
			$array_chunk = array_chunk( $args, 400 );
			foreach ( $array_chunk as $value ) {
				$args_strings[] = implode( ' ', $value );
			}
		} else {
			$args_strings[] = implode( ' ', $args );
		}
		return empty( $args_strings ) ? '' : htccss_prepare_directive( $args_strings, $directive );
	}
}

/**
 * Forming directive
 * @param     array     $array      list of IP or CIDR
 * @param     string    $directive  'Allow from' or 'Deny From'
 */
if ( ! function_exists( 'htccss_prepare_directive' ) ) {
	function htccss_prepare_directive( $array, $directive ) {
		$result = '';
		foreach( $array as $item ) {
			$string  = trim( $item );
			$result .= empty( $string ) ? '' : $directive . ' ' . $string . "\n";
		}
		return $result;
	}
}

if ( ! function_exists ( 'htccss_get_htaccess' ) ) {
	function htccss_get_htaccess() {
		global $htccss_options, $htccss_auto_added;
		if ( ! is_array( $htccss_auto_added ) ) {
			$htccss_auto_added = array( 'allow' => '', 'deny' => '' );
		}
		if ( empty( $htccss_options ) ) {
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}
		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$htaccess_file = get_home_path() . '.htaccess';

		if ( ! file_exists( $htaccess_file ) ) {
			return false;
		}
		$handle = fopen( $htaccess_file, "r" );

		if ( $handle ) {
			$allow_array = $deny_array =
			$auto_allow_array = $auto_deny_array = array();
			$order_start = $order_end =
			$skip = $is_manual = $is_auto = $order_directives = false;
			/*
			 * Get all "Deny from" and "Allow from" directives except those
			 * which placed inside group directives
			 * <Files>, <FilesMatch>,
			 * <Directory>, <DirectoryMatch>
			 * <Proxy>, <ProxyMatch>
			 * <Location>, <LocationMatch> or <Limit>
			 */
			while ( ! feof( $handle ) ) {
				$string = fgets( $handle );
				/*
				 * Skip comments that have not been generated by plugin
				 */
				if( preg_match( "/(# htccss_order_start #)/i", $string ) ) {
					$order_start = true;
				} elseif( preg_match( "/(# htccss_order_end #)/i", $string ) ) {
					$order_start = false;
					$order_end = true;
				} elseif ( preg_match( "/(?=(^(.*?)\#(.*?)$))(?=(^((?!((htccss)|(htcss))).)*$))/i", $string ) ) {
					continue;
				} elseif( preg_match( "/(## htccss_allow_manually_start ##)|(## htccss_deny_manually_start ##)/i", $string ) ) {
					$is_manual = true;
				} elseif( preg_match( "/(## htccss_allow_manually_start ##)|(## htccss_deny_manually_start ##)/i", $string ) ) {
					$is_manual = false;
				} elseif( preg_match( "/(## htccss_allow_automatically_start ##)|(## htccss_deny_automatically_start ##)/i", $string ) ) {
					$is_auto = true;
				} elseif( preg_match( "/(## htccss_allow_automatically_end ##)|(## htccss_deny_automatically_end ##)/i", $string ) ) {
					$is_auto = false;
				} elseif ( preg_match( "/<(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) { /* open tag */
					$skip = true;
				} elseif ( preg_match( "/<\/(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) { /* close tag */
					$skip = false;
				} elseif ( preg_match( "/^Order.+(Allow|Deny)$/i", $string ) && ! $order_directives && ! $skip ) { /* first founded ORDER will be saved in plugin`s settings */
					$htccss_options['order'] = trim( $string );
					$order_directives = true;
				} elseif ( preg_match( "/Allow from[\s\S]+/i", $string ) && ! $skip ) {
					if ( $is_auto || $order_end )
						$auto_allow_array[] = trim( str_ireplace( 'Allow from ', '', $string ) );
					else
						$allow_array[] = trim( str_ireplace( 'Allow from ', '', $string ) );
				} elseif ( preg_match( "/Deny from[\s\S]+/i", $string ) && ! $skip ) {
					if ( $is_auto || $order_end )
						$auto_deny_array[] = trim( str_ireplace( 'Deny from ', '', $string ) );
					else
						$deny_array[] = trim( str_ireplace( 'Deny from ', '', $string ) );
				}
			}

			fclose( $handle );

			$htccss_options['allow']	= htccss_implode( $allow_array );
			$htccss_options['deny']		= htccss_implode( $deny_array );
			$htccss_options['allow_xml'] = htccss_check_xml_access();
			$htccss_auto_added['allow'] = htccss_implode( $auto_allow_array );
			$htccss_auto_added['deny']	= htccss_implode( $auto_deny_array );
		}
	}
}

if ( ! function_exists ( 'htccss_mod_rewrite_rules' ) ) {
	function htccss_mod_rewrite_rules( $rules ) {
		global $htccss_options, $htccss_auto_added;
		$home_path = get_home_path();
		if ( ! file_exists( $home_path . '.htaccess' ) ) {

			$allow_array = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_options['allow'] ), -1, PREG_SPLIT_NO_EMPTY );
			$deny_array  = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_options['deny'] ), -1, PREG_SPLIT_NO_EMPTY );

			if ( is_array( $htccss_auto_added ) ) {
			$auto_allow_array = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_auto_added['allow'] ), -1, PREG_SPLIT_NO_EMPTY );
			$auto_deny_array  = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_auto_added['deny'] ), -1, PREG_SPLIT_NO_EMPTY );
			$allow_array      = array_merge( $allow_array, $auto_allow_array );
			$deny_array       = array_merge( $deny_array, $auto_deny_array );
		}
			if ( false == stripos( $rules, 'Order ' ) && ( ! empty( $allow_array ) || ! empty( $deny_array ) ) ) {
				$allow = stripos( $htccss_options['order'], 'Allow' );
				$deny  = stripos( $htccss_options['order'], 'Deny' );
				$allow_first = $allow < $deny;

				$allow_content = empty( $allow_array ) ? 'Allow from all' . "\n" : htccss_prepare_directive( $allow_array, 'Allow from' );
				$deny_content  = empty( $deny_array ) ? '' : htccss_prepare_directive( $deny_array, 'Deny from' );

				$content  = $htccss_options['order'] . "\n";
				$content .=
						$allow_first
					?
						$allow_content . $deny_content
					:
						$deny_content . $allow_content;

				$rules = $content . $rules ;
			}
		}
		return $rules;
	}
}

/**
 * Remove plugin`s directives from the .htaccess file
 * @uses   during plugin uninstallation
 * @since  1.7.2
 * @param  void
 * @return void
 */

if ( ! function_exists( 'htccss_clear_htaccess' ) ) {
	function htccss_clear_htaccess( $remove_directives = false ) {
		$htaccess_file = get_home_path() . '.htaccess';

		if ( ! file_exists( $htaccess_file ) ) {
			return false;
		}
		$handle = fopen( $htaccess_file, "r+" );

		if ( $handle ) {
			flock( $handle, LOCK_EX );
			/* get content of the .htaccess file */
			$content = stream_get_contents( $handle );

			/* remove plugin`s directives */
			if ( $remove_directives ) {
				$content = trim( preg_replace( "/([\n]+)?# htccss_order_start([\s\S]+)htccss_order_end #/", "", $content ) );
			}

			/* remove access to XML files for network */
			$reg_exp = preg_quote( "RewriteRule ([^/]+\.xml)$ $1 [L]" );
			$content = preg_replace( "|([\n]+)?{$reg_exp}|", "", $content );

			fseek( $handle, 0 );
			$bytes = fwrite( $handle, $content );
			if ( $bytes ) {
				ftruncate( $handle, ftell( $handle ) );
			}
			fflush( $handle );
			flock( $handle, LOCK_UN );
			fclose( $handle );
		}
	}
}

if ( ! function_exists ( 'htccss_create_htaccess' ) ) {
	function htccss_create_htaccess( $htaccess_file ) {
		if ( ! $htaccess_file || ! is_writable( dirname( $htaccess_file ) ) || ! touch( $htaccess_file ) ) {
			return false;
		} else {
			return true;
		}
	}
}

if ( ! function_exists ( 'htccss_string_unique_ip' ) ) {
	function htccss_string_unique_ip( $string ) {
		if ( ! empty( $string ) ) {
			$string = str_replace( "\n", " ", $string );
			$string = str_replace( '::ffff:', '', $string );
			$strin_arr = explode( " ", $string );
			$strin_arr = array_unique( $strin_arr );
			$string = implode( "\n", $strin_arr );
		}
		return $string;
	}
}

if ( ! function_exists ( 'htccss_generate_htaccess' ) ) {
	function htccss_generate_htaccess() {
		global $htccss_options, $htccss_auto_added;
		if ( ! is_array( $htccss_auto_added ) ) {
			$htccss_auto_added = array( 'allow' => '', 'deny' => '' );
		}
		$htaccess_file = get_home_path() . '.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			if ( ! htccss_create_htaccess( $htaccess_file ) ) {
				return false;
			}
		}
		$handle = fopen( $htaccess_file, "r+" );

		if ( $handle ) {
			/*
			 * Attempt to get a lock. If the filesystem supports locking, this will block until the lock is acquired.
			 * It is required for Windows
			 */
			flock( $handle, LOCK_EX );

			/* Get content of .htaccess without "Deny from" and "Allow from" directives */

			$content = trim( stream_get_contents( $handle ) );
			if ( ! empty( $content ) ) {
				/* get SetEnv directives that were early added to the top of .htaccess and remove them from the content of .htaccess */
				if ( preg_match( "/# htccss_set_env_start #([\s\S]+)# htccss_set_env_end #/", $content, $matches ) ) {
					$setenv_directives = trim( $matches[1] ) . "\n";
					$content = preg_replace( "/# htccss_set_env_start([\s\S]+)htccss_set_env_end #/", "", $content );
				} else {
					$setenv_directives = '';
				}

				$order_block = ( preg_match( "/# htccss_order_start([\s\S]+)htccss_order_end #/", $content ) );
				if ( $order_block ) {
					if ( preg_match( "/## htccss_allow_automatically_start ##([\s\S]+)## htccss_allow_automatically_end ##/", $content, $matches_auto_allow ) ) {
						if ( ! empty( $matches_auto_allow[1] ) ) {
							$htccss_auto_added['allow'] = trim( str_ireplace( 'Allow from ', '', $htccss_auto_added['allow'] ) );
						}
					}

					if ( preg_match( "/## htccss_deny_automatically_start ##([\s\S]+)## htccss_deny_automatically_end ##/", $content, $matches_auto_deny ) ) {
						if ( ! empty( $matches_auto_deny[1] ) ) {
							$htccss_auto_added['deny'] = trim( str_ireplace( 'Deny from ', '', $htccss_auto_added['deny'] ) );
						}
					}
				}
				/* remove plugin`s directives */
				$content = preg_replace( "/# htccss_order_start([\s\S]+)htccss_order_end #/", "", $content );
				/*
				 * Remove other "Deny from" and "Allow from" directives except those
				 * which placed inside group directives
				 * <Files>, <FilesMatch>,
				 * <Directory>, <DirectoryMatch>
				 * <Proxy>, <ProxyMatch>
				 * <Location>, <LocationMatch> or <Limit>
				 */
				$content_array = explode( "\n", $content );
				$env_vars = $setenv_temp = array();
				if ( is_array( $content_array ) && ! empty( $content_array ) ) {
					$skip = false;
					foreach( $content_array as $key => $string ) {
						if ( preg_match( "/^\#(.*?)$/", $string ) ) { /* skip comments */
							continue;
						} elseif ( preg_match( "/<(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) {/* open tag */
							$skip = true;
						} elseif ( preg_match( "/<\/(Files)|(Directory)|(Proxy)|(Location)|(Limit)[\s\S]+>/i", $string ) ) { /* close tag */
							$skip = false;
						} elseif ( preg_match( "/^Order.+(Allow|Deny)$/i", $string ) && ! $skip ) {
							unset( $content_array[ $key ] );
						} elseif ( preg_match( "/((Allow from)|(Deny from))[\s\S]+/i", $string ) && ! $skip ) {
							/* if directive contains some environment variables and we found some SetEnv directives early */
							if ( preg_match_all( "/env=(.*?)(\S+)/", $string, $matches ) && ! empty( $setenv_temp ) ) {
								foreach ( $matches[2] as $var_name ) {
									foreach ( $setenv_temp as $key_temp => $item ) {
										if ( preg_match( "|" . preg_quote( $var_name ) . "|", $item['setenv_string'] ) ) {
											/* add SetEnv directives to the top part of .htaccess */
											if ( ! preg_match( "|" . preg_quote( $item['setenv_string'] ) . "|", $setenv_directives ) )
												$setenv_directives .= "{$item['setenv_string']}\n";
											/* remove it from the bottom part of .htaccess */
											unset( $content_array[ $item['setenv_key'] ] );
											/* remove it from the temporary array */
											unset( $setenv_temp[ $key_temp ] );
										}
									}
								}
							} else {
								if ( $order_block ) {
									if ( preg_match( "/Allow from[\s\S]+/i", $string ) && ! $skip ) {
										$htccss_auto_added['allow'] .= " " . trim( str_ireplace( 'Allow from ', '', $string ) );
									} elseif ( preg_match( "/Deny from[\s\S]+/i", $string ) && ! $skip ) {
										$htccss_auto_added['deny'] .= " " . trim( str_ireplace( 'Deny from ', '', $string ) );
									}
								}
							}
							unset( $content_array[ $key ] );
						} elseif ( preg_match( "/^[\s]*(SetEnv)|(SetEnvIf)|(SetEnvIfNoCase)|(SetEnvIfExpr)[\s\S]+/i", $string ) ) {
							/* add directive to the temporary array */
							$setenv_temp[] = array( 'setenv_key' => $key, 'setenv_string' => $string );
						}
					}
					$content = implode( "\n", $content_array );
					$content = trim( $content );
				}
			}

			$setenv_directives = empty( $setenv_directives ) ? '' : "# htccss_set_env_start #\n{$setenv_directives}# htccss_set_env_end #\n";

			$htccss_options['allow'] = htccss_string_unique_ip( $htccss_options['allow'] );
			$htccss_options['deny'] = htccss_string_unique_ip( $htccss_options['deny'] );
			$htccss_auto_added['allow'] = htccss_string_unique_ip( $htccss_auto_added['allow'] );
			$htccss_auto_added['deny'] = htccss_string_unique_ip( $htccss_auto_added['deny'] );
			/* get "Deny from" and "Allow from" directives from plugin`s settings */
			$allow_content		= htccss_get_order_content( $htccss_options['allow'], 'Allow from' );
			$deny_content		= htccss_get_order_content( $htccss_options['deny'], 'Deny from' );
			$auto_allow_content	= htccss_get_order_content( $htccss_auto_added['allow'], 'Allow from' );
			$auto_deny_content	= htccss_get_order_content( $htccss_auto_added['deny'], 'Deny from' );

			$content_directives = '';

			/* add directives to the content of .htaccess */
			if (
				! empty( $allow_content ) ||
				! empty( $deny_content ) ||
				! empty( $auto_allow_content ) ||
				! empty( $auto_deny_content )
			) {

				$allow = stripos( $htccss_options['order'], 'Allow' );
				$deny  = stripos( $htccss_options['order'], 'Deny' );

				if (
					$allow < $deny &&
					empty( $htccss_options['allow'] ) &&
					empty( $htccss_auto_added['allow'] ) &&
					( ! empty( $htccss_options['deny'] ) || ! empty( $htccss_auto_added['deny'] ) )
				) {
					$allow_first = false;
					$htccss_options['order'] = "Order Deny,Allow";
				} else {
					$allow_first = $allow < $deny;
				}

				$order_content_array = array();

				if ( $allow_content ) {
					$order_content_array['allow'][] = "## htccss_allow_manually_start ##\n{$allow_content}## htccss_allow_manually_end ##\n\n";
				}

				if ( $deny_content ) {
					$order_content_array['deny'][] = "## htccss_deny_manually_start ##\n{$deny_content}## htccss_deny_manually_end ##\n\n";
				}

				if ( $auto_allow_content ) {
					$order_content_array['allow'][] = "## htccss_allow_automatically_start ##\n{$auto_allow_content}## htccss_allow_automatically_end ##\n\n";
				}

				if ( $auto_deny_content ) {
					$order_content_array['deny'][] = "## htccss_deny_automatically_start ##\n{$auto_deny_content}## htccss_deny_automatically_end ##\n\n";
				}

				if ( ! $allow_first ) {
					krsort( $order_content_array );
				}

				$order_allow_deny_content = "# htccss_order_start #\n{$htccss_options['order']}\n";

				foreach ( $order_content_array as $order_key => $order_value ) {
					foreach ( $order_value as $sub_order_key => $sub_order_value ) {
						$order_allow_deny_content .= $sub_order_value;
					}
				}

				$order_allow_deny_content .= "# htccss_order_end #\n";

				$content_directives = "{$setenv_directives}\n{$order_allow_deny_content}";
			}

			/* allow access to XML files */
			if ( is_multisite() && ! is_subdomain_install() ) {
				$content = htccss_allow_xml( $content );
			}
			$content = $content_directives . "\n" . $content;

			fseek( $handle, 0 );
			$bytes = fwrite( $handle, $content );
			if ( $bytes ) {
				ftruncate( $handle, ftell( $handle ) );
			}
			fflush( $handle );
			flock( $handle, LOCK_UN );
			fclose( $handle );
		}
		/* give htaccess_file 644 access rights */
		@chmod( $htaccess_file, 0644 );
	}
}

if ( ! function_exists( 'htccss_allow_xml' ) ) {
	function htccss_allow_xml( $content ) {
		global $htccss_options;
		$reg_exp = preg_quote( "RewriteRule ([^/]+\.xml)$ $1 [L]" );
		if ( 1 == $htccss_options['allow_xml'] && ! preg_match( "|{$reg_exp}|", $content ) ) {
			if ( preg_match( "/RewriteBase.*(?=\n)/", $content, $matches ) ) {
				$content_array = explode( $matches[0], $content );
				$content_array[1] = "\nRewriteRule ([^/]+\.xml)$ $1 [L]{$content_array[1]}";
				$content = implode( $matches[0], $content_array );
			}
		} elseif ( 0 == $htccss_options['allow_xml'] ) {
			$content = preg_replace( "|\n{$reg_exp}|", '', $content );
		}
		return $content;
	}
}

if ( ! function_exists ( 'htccss_admin_head' ) ) {
	function htccss_admin_head() {
		global $htccss_plugin_info;

		/* css for displaing an icon */
		wp_enqueue_style( 'htccss_icon_stylesheet', plugins_url( 'css/icon.css', __FILE__ ) );

		if ( isset( $_REQUEST['page'] ) && ( 'htaccess.php' == $_REQUEST['page'] || 'htaccess-editor.php' == $_REQUEST['page'] ) ) {
			wp_enqueue_script( 'admin_script', plugins_url( 'js/admin-script.js', __FILE__ ), array( 'jquery' ), $htccss_plugin_info['Version'] );
			wp_enqueue_style( 'htccss_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			bws_enqueue_settings_scripts();
		}
	}
}

if ( ! function_exists( 'htccss_plugin_banner' ) ) {
	function htccss_plugin_banner() {
		global $hook_suffix, $htccss_plugin_info;
		if ( 'plugins.php' == $hook_suffix ) {
			if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
				bws_plugin_banner_to_settings( $htccss_plugin_info, 'htccss_options', 'htaccess', 'admin.php?page=htaccess.php' );
			}
			if ( is_multisite() && ! is_network_admin() && is_admin() ) { ?>
				<div class="update-nag"><strong><?php _e( 'Notice:', 'htaccess' ); ?></strong>
					<?php if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
						_e( 'Due to the peculiarities of the multisite work, Htaccess plugin has only', 'htaccess' ); ?> <a target="_blank" href="<?php echo network_admin_url( 'admin.php?page=htaccess.php' ); ?>"><?php _e( 'Network settings page', 'htaccess' ); ?></a>
					<?php } else {
						_e( 'Due to the peculiarities of the multisite work, Htaccess plugin has the network settings page only and it should be Network Activated. Please', 'htaccess' ); ?> <a target="_blank" href="<?php echo network_admin_url( 'plugins.php' ); ?>"><?php _e( 'Activate Htaccess for Network', 'htaccess' ); ?></a>
					<?php } ?>
				</div>
			<?php }
		}
		if ( isset( $_REQUEST['page'] ) && 'htaccess.php' == $_REQUEST['page'] ) {
			bws_plugin_suggest_feature_banner( $htccss_plugin_info, 'htccss_options', 'htaccess' );
		}
	}
}

/**
 * Fetch lists of IPs that have been added to the .htaccess file
 * manually ( via form on plugin`s settings page )
 * and automatically ( form lists of IPs of the Limit Attempts plugin )
 * @uses   during plugin update or activation
 * @see    register_htccss_settings()
 * @since  1.7.2
 * @param  boolean     $add_auto_allow   true if they need to add "Allow" directives to the "allow_automatically" section of the .htaccess file
 * @return void
 */
if ( ! function_exists( 'htccss_check_orders' ) ) {
	function htccss_check_orders( $add_auto_allow = false ) {
		global $wpdb, $htccss_options, $htccss_auto_added;
		if ( empty( $htccss_options ) )
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		htccss_get_htaccess();
		$prefix  = $wpdb->prefix . 'lmtttmpts_';
		$ip_list = $deny = $allow = $auto_deny = $auto_allow = array();

		/**
		 * Check list of IPs from "Deny" direction
		 */
		$blocked_ips = $wpdb->get_col(
			"SELECT `ip` FROM `{$prefix}failed_attempts` WHERE `block` = true
			 UNION
			 SELECT `ip` FROM `{$prefix}blacklist`"
		);
		if ( ! empty( $blocked_ips ) ) {

			$ip_list	= htccss_prepare_data( $blocked_ips );													/* get list of IPs ranges that are in blacklist or blocked list of the Limit Attempts plugin */
			$deny		= preg_split( "/[\t\n\r\s\,]+/", $htccss_options['deny'], -1, PREG_SPLIT_NO_EMPTY );	/* get list of IPs (as array) from plugin settings */
			$deny		= array_diff( $deny, $ip_list );														/* get list of IPs that are not in blacklist or blocked list of the Limit Attempts plugin */
			$deny		= array_filter( $deny );																/* remove empty values */

			$auto_deny	= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['deny'], -1, PREG_SPLIT_NO_EMPTY );	/* get list of IPs (as array) that have been added to htaccess automatically */
			$deny_unique = array_diff( $ip_list, $auto_deny );													/* get list of IPs that have not been added to the .htaccess file automatically yet */
			$auto_deny	= array_merge( $auto_deny, $deny_unique );												/* get list of IPs that they have to being added to the .htaccess file to the "deny_automatically" section */
			$auto_deny	= array_filter( $auto_deny );															/* remove empty values */

			$htccss_options['deny']		= implode( "\n", $deny );
			$htccss_auto_added['deny']	= implode( "\n", $auto_deny );
		}

		/**
		 * Check list of IPs from "Allow" direction:
		 * this part might be useful if they need to add
		 * list of whitelisted IPs of the Limit Attempts plugin to the .htaccess file
		 */
		$whitelisted_ips = $wpdb->get_col( "SELECT `ip` FROM `{$prefix}whitelist`" );
		if ( ! empty( $whitelisted_ips ) ) {

			$ip_list	= htccss_prepare_data( $whitelisted_ips );												/* get list of IPs that are in whitelist list of Limit Attempts plugin */
			$allow		= preg_split( "/[\t\n\r\s\,]+/", $htccss_options['allow'], -1, PREG_SPLIT_NO_EMPTY );	/* get list of IPs (as array) from plugin settings */
			$in_htccss	= array_merge( $deny, $allow, $auto_deny );												/* get list of IPs that they have to being added to the .htaccess file */
			$allow		= array_diff( $in_htccss, $ip_list );													/* get list of IPs that are not in whitelist of the Limit Attempts plugin */
			$allow		= array_diff( $allow, array_merge( $auto_deny, $deny ) );								/* get list of IPs that are not in "Deny" directives */
			$allow		= array_filter( $allow );																/* remove empty values */
			$htccss_options['allow'] = implode( "\n", $allow );

			if ( $add_auto_allow ) {
				$auto_allow   = preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['allow'], -1, PREG_SPLIT_NO_EMPTY );	/* get list of IPs (as array) that have been added to htaccess automatically */
				$allow_unique = array_diff( $ip_list, $auto_allow );													/* get list of IPs that have not been added to the .htaccess file automatically yet */
				$auto_allow   = array_merge( $auto_allow, $allow_unique );												/* get list of IPs that they have to being added to the .htaccess file to the "allow_automatically" section */
				$auto_allow   = array_filter( $auto_allow );															/* remove empty values */
				$htccss_auto_added['allow'] = implode( "\n", $auto_allow );
			}
		}
		if ( ( ! empty( $htccss_options['deny'] ) || ! empty( $htccss_auto_added['deny'] ) ) && empty( $htccss_options['allow'] ) && empty( $htccss_auto_added['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_copy_all' ) ) {
	function htccss_lmtttmpts_copy_all( $add_auto_allow = false ) {
		global $wpdb, $htccss_options, $htccss_auto_added;
		if ( empty( $htccss_options ) ) {
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}
		htccss_get_htaccess();
		$prefix = $wpdb->prefix . 'lmtttmpts_';
		$deny	= $allow = array();
		$flag	= false;

		/* add blocked IPs to "Deny" directive */
		$blocked_ips = $wpdb->get_col(
			"SELECT `ip` FROM `{$prefix}failed_attempts` WHERE `block` = true
			 UNION
			 SELECT `ip` FROM `{$prefix}blacklist`"
		);
		if ( ! empty( $blocked_ips ) ) {
			$flag    = true;
			$deny    = preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added[ 'deny' ], -1, PREG_SPLIT_NO_EMPTY );
			$ip_list = htccss_prepare_data( (array)$blocked_ips );
			if ( empty( $deny ) ) {
				$htccss_auto_added['deny'] = implode( "\n", $ip_list );
				$deny = $ip_list;
			} else {
				/* list of IPs that has not been added in the "Deny" directive yet */
				$deny_unique = array_diff( $ip_list, $deny );
				$htccss_auto_added['deny'] .= "\n" . implode( "\n", $deny_unique );
				$deny = array_merge( $deny, $deny_unique );
			}
		}

		/* add whitelisted IPs to "Allow" directive */
		if ( $add_auto_allow ) {
			$whitelisted_ips = $wpdb->get_col( "SELECT `ip` FROM `{$prefix}whitelist`" );
			if ( ! empty( $whitelisted_ips ) ) {
				$flag		= true;
				$ip_list	= htccss_prepare_data( ( array )$whitelisted_ips );
				$in_htccss	= $deny;
				$allow		= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['allow'], -1, PREG_SPLIT_NO_EMPTY );
				$auto_allow	= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['allow'], -1, PREG_SPLIT_NO_EMPTY );
				$in_htccss	= array_merge( $in_htccss, $allow );

				if ( empty( $in_htccss ) ) {
					$htccss_auto_added['allow'] = implode( "\n", $ip_list );
				} else {
					/* list of IPs that has not been added in the "Deny" or "Allow" directives yet */
					$allow_unique = array_diff( $ip_list, $in_htccss );
					$htccss_auto_added['allow'] .= "\n" . implode( "\n", $allow_unique );
				}
			}
		}
		if ( $flag ) {
			htccss_update_options();
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_all' ) ) {
	function htccss_lmtttmpts_delete_all() {
		global $wpdb, $htccss_options, $htccss_auto_added;

		if ( empty( $htccss_options ) ) {
			$htccss_options = is_multisite() ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}

		htccss_get_htaccess();
		$prefix = $wpdb->prefix . 'lmtttmpts_';
		$flag	= false;
		$ip_tables = array(
			'blocked' => $wpdb->get_col(
					"SELECT `ip` FROM `{$prefix}failed_attempts` WHERE `block` = true
					 UNION
					 SELECT `ip` FROM `{$prefix}blacklist`"
				),
			'whitelisted' => $wpdb->get_col( "SELECT `ip` FROM `{$prefix}whitelist`" )
		);

		if ( ! empty( $ip_tables ) ) {
			foreach( $ip_tables as $key => $value ) {
				if ( ! empty( $value ) ) {
					$flag		= true;
					$option		= 'whitelisted' == $key ? 'allow' : 'deny';
					$ip_list	= htccss_prepare_data( (array)$value );
					$in_htccss	= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added[ $option ], -1, PREG_SPLIT_NO_EMPTY );
					if ( ! empty( $in_htccss ) ) {
						$to_htccss = array_diff( $in_htccss, $ip_list );
						$htccss_auto_added[ $option ] = implode( "\n", $to_htccss );
					}
				}
			}
		}

		if ( $flag ) {
			htccss_update_options();
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_block' ) ) {
	function htccss_lmtttmpts_block( $ip ) {
		global $htccss_options, $htccss_auto_added;
		if ( empty( $htccss_options ) ) {
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$ip_list	= htccss_prepare_data( ( array )$ip );
		$in_htccss	= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['deny'], -1, PREG_SPLIT_NO_EMPTY );
		if ( empty( $in_htccss ) ) {
			$htccss_auto_added['deny'] = implode( "\n", $ip_list );
		} else {
			$new_ip = array_diff( $ip_list, $in_htccss );
			$htccss_auto_added['deny'] .= "\n" . implode( "\n", $new_ip );
		}
		htccss_update_options();
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_reset_block' ) ) {
	function htccss_lmtttmpts_reset_block( $ip ) {
		global $htccss_options, $htccss_auto_added;
		if ( empty( $htccss_options ) ) {
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$in_htccss = preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['deny'], -1, PREG_SPLIT_NO_EMPTY );
		if ( ! empty( $in_htccss ) ) {
			$ip_list = htccss_prepare_data( ( array )$ip );
			$new_ip  = array_diff( $in_htccss, $ip_list );
			$htccss_auto_added['deny'] = implode( "\n", $new_ip );
			htccss_update_options();
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_from_whitelist' ) ) {
	function htccss_lmtttmpts_delete_from_whitelist( $ip ) {
		global $htccss_options, $htccss_auto_added;
		if ( empty( $htccss_options ) ) {
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$in_htccss = preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['allow'], -1, PREG_SPLIT_NO_EMPTY );
		if ( ! empty( $in_htccss ) ) {
			$ip_list = htccss_prepare_data( ( array )$ip );
			$new_ip  = array_diff( $in_htccss, $ip_list );
			$htccss_auto_added['allow'] = implode( "\n", $new_ip );
			htccss_update_options();
			htccss_generate_htaccess();
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_add_to_whitelist' ) ) {
	function htccss_lmtttmpts_add_to_whitelist( $ip ) {
		global $htccss_options, $htccss_auto_added;
		if ( empty( $htccss_options ) ) {
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );
		}
		require_once ( ABSPATH . 'wp-admin/includes/file.php' );
		htccss_get_htaccess();
		$ip_list	= htccss_prepare_data( ( array )$ip );
		$deny		= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added[ 'deny' ], -1, PREG_SPLIT_NO_EMPTY );
		$allow		= preg_split( "/[\t\n\r\s\,]+/", $htccss_auto_added['allow'], -1, PREG_SPLIT_NO_EMPTY );
		$in_htccss = array_merge( $deny, $allow );
		if ( empty( $in_htccss ) ) {
			$htccss_auto_added['allow'] = implode( "\n", $ip_list );
		} else {
			$new_ip = array_diff( $ip_list, $in_htccss );
			$htccss_auto_added['allow'] .= "\n" . implode( "\n", $new_ip );
		}
		htccss_update_options();
		htccss_generate_htaccess();
	}
}

/**
 * Prepare list of IPs before .htaccess editing
 * @param     array     $ip_list    List with IPs
 * @return    array     $args       List with IPs
 */
if ( ! function_exists( 'htccss_prepare_data' ) ) {
	function htccss_prepare_data( $ip_list ) {
		if ( empty( $ip_list ) )
			return false;

		$args = array();

		foreach( $ip_list as $ip ) {
			if (
				/* single IP */
				filter_var( $ip, FILTER_VALIDATE_IP ) ||
				/* CIDR */
				preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\/(3[0-2]|[1-2][0-9]|[0-9])$/', $ip )
			) {
				if ( ! in_array( $ip, $args ) ) {
					$args[] = $ip;
				}
			} elseif (
				/* short mask like 10., 192.168. or 128.45.25. */
				preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){0,2}\.$/', $ip )
			) {
				$dot_entry = substr_count( $ip, '.' );
				switch ( $dot_entry ) {
					case 3: /* in case if mask like xxx.xxx.xxx. */
						$cidr = $ip . '0/24';
						break;
					case 2: /* in case if mask like xxx.xxx. */
						$cidr = $ip . '0.0/16';
						break;
					case 1: /* in case if mask like xxx. */
						$cidr = $ip . '0.0.0/8';
						break;
					default:
						$cidr = '';
						break;
				}
				if ( ! empty( $cidr ) && ! in_array( $cidr, $args ) ) {
					$args[] = $cidr;
				}
			} elseif (
				/* range like 128.45.25.0-188.5.5.5 */
				preg_match( '/^(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}\-(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])(\.(25[0-5]|2[0-4][0-9]|[1][0-9]{2}|[1-9][0-9]|[0-9])){3}$/', $ip )
			) {
				$ips		= explode( '-', $ip );
				$ip_from_int = sprintf( '%u', ip2long( $ips[0] ) );
				$ip_to_int	= sprintf( '%u', ip2long( $ips[1] ) );
				if ( $ip_from_int <= $ip_to_int ) {
					$num		= $ip_to_int - $ip_from_int + 1;
					$bin		= decbin( $num );
					$chunk		= str_split( $bin );
					$chunk		= array_reverse( $chunk );
					$mask		= 32 - count( $chunk ) + 1;
					while ( $mask <= 32 ) {
						if ( $chunk[ 32 - $mask ] != 0 ) {
							$start_ip = isset( $end_ip ) ? long2ip( $end_ip ) : long2ip( $ip_from_int );
							$end_ip   = ip2long( $start_ip ) + pow( 2, 32 - $mask );
							$cidr     = $start_ip . '/' . $mask;
							if ( ! in_array( $cidr, $args ) )
								$args[] = $cidr;
						}
						$mask ++;
					}
					if ( isset( $end_ip ) ) {
						unset( $end_ip );
					}
				}
			}
		}
		return $args;
	}
}

/**
 * Get options of the specified plugin
 * @since  1.7.2
 * @uses   during plugin update or activation
 * @see    register_htccss_settings()
 * @param  void
 * @return void
 */
if ( ! function_exists( 'htccss_get_option' ) ) {
	function htccss_get_option( $plugin ) {
		switch( $plugin ) {
			case 'limit-attempts/limit-attempts.php':
				return get_option( 'lmtttmpts_options' );
			case 'limit-attempts-pro/limit-attempts-pro.php':
				return get_option( 'lmtttmptspr_options' );
			default:
				return false;
		}

	}
}

/**
 * Update plugin options
 * @since  1.7.2
 * @param  void
 * @return void
 */
if ( ! function_exists( 'htccss_update_options' ) ) {
	function htccss_update_options() {
		global $htccss_auto_added, $htccss_options;
		$htccss_auto_added['deny']	= preg_replace( "/\n{2,}/", "\n", $htccss_auto_added['deny'] );
		$htccss_auto_added['allow'] = preg_replace( "/\n{2,}/", "\n", $htccss_auto_added['allow'] );
		if ( preg_match( "/^\n*$/", $htccss_auto_added['deny'] ) ) {
			$htccss_auto_added['deny'] = "";
		}
		if ( preg_match( "/^\n*$/", $htccss_auto_added['allow'] ) ) {
			$htccss_auto_added['allow'] = "";
		}
		if ( ( ! empty( $htccss_options['deny'] ) || ! empty( $htccss_auto_added['deny'] ) ) && empty( $htccss_options['allow'] ) && empty( $htccss_auto_added['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
	}
}

/* Function for delete delete options */
if ( ! function_exists ( 'htccss_delete_options' ) ) {
	function htccss_delete_options() {
		global $wpdb;

		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$all_plugins = get_plugins();

		if ( ! array_key_exists( 'htaccess-pro/htaccess-pro.php', $all_plugins ) ) {
			if ( is_multisite() ) {
				$old_blog = $wpdb->blogid;
				/* Get all blog ids */
				$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
				foreach ( $blogids as $blog_id ) {
					switch_to_blog( $blog_id );
					delete_option( 'htccss_options' );
				}
				switch_to_blog( $old_blog );
				delete_site_option( 'htccss_options' );
			} else {
				delete_option( 'htccss_options' );
			}

			htccss_clear_htaccess( true );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'htccss_plugin_activate' );

if ( function_exists( 'is_multisite' ) ) {
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', 'add_htccss_admin_menu' );
	} else {
		add_action( 'admin_menu', 'add_htccss_admin_menu' );
	}
}

add_action( 'init', 'htccss_init' );
add_action( 'admin_init', 'htccss_plugin_admin_init' );
add_action( 'plugins_loaded', 'htccss_plugins_loaded' );

add_action( 'admin_enqueue_scripts', 'htccss_admin_head' );
add_action( 'admin_notices', 'htccss_plugin_banner' );
add_action( 'network_admin_notices', 'htccss_plugin_banner');
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
