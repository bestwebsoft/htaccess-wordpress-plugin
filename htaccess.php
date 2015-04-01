<?php
/*
Plugin Name: Htaccess by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: The plugin Htaccess allows controlling access to your website using the directives Allow and Deny. Access can be controlled based on the client's hostname, IP address, or other characteristics of the client's request.
Author: BestWebSoft
Version: 1.6.5
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
		if ( is_multisite() && ! is_network_admin() )
			return;
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', 'Htaccess ' . __( 'Settings', 'htaccess' ), 'Htaccess', 'manage_options', "htaccess.php", 'htccss_settings_page' );
	}
}

if ( ! function_exists ( 'htccss_plugin_init' ) ) {
	function htccss_plugin_init() {
		global $htccss_plugin_info;
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'htaccess', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );
		
		if ( empty( $htccss_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$htccss_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_version_check( plugin_basename( __FILE__ ), $htccss_plugin_info, "3.5" );
	}
}

if ( ! function_exists ( 'htccss_plugin_admin_init' ) ) {
	function htccss_plugin_admin_init() {
 		global $bws_plugin_info, $htccss_plugin_info;

 		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '110', 'version' => $htccss_plugin_info["Version"] );

		/* Call register settings function */
		if ( isset( $_GET['page'] ) && "htaccess.php" == $_GET['page'] )
			register_htccss_settings();
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
		if ( is_multisite() ) {
			if ( ! get_site_option( 'htccss_options' ) )
				add_site_option( 'htccss_options', $htccss_option_defaults );
		} else {
			if ( ! get_option( 'htccss_options' ) )			
				add_option( 'htccss_options', $htccss_option_defaults );
		} 
		/* Get options from the database */
		$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) : get_option( 'htccss_options' );	

		/* Array merge incase this version has added new options */
		if ( ! isset( $htccss_options['plugin_option_version'] ) || $htccss_options['plugin_option_version'] != $htccss_plugin_info["Version"] ) {
			$htccss_options = array_merge( $htccss_option_defaults, $htccss_options );
			$htccss_options['plugin_option_version'] = $htccss_plugin_info["Version"];
			if ( is_multisite() )
				update_site_option( 'htccss_options', $htccss_options );
			else
				update_option( 'htccss_options', $htccss_options );
		}
	}
}

if ( ! function_exists( 'htccss_plugin_action_links' ) ) {
	function htccss_plugin_action_links( $links, $file ) {
		if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && is_admin() ) ) {
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
			if ( ( is_multisite() && is_network_admin() ) || ( ! is_multisite() && is_admin() ) )
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
		$all_plugins = get_plugins();
		$plugin_basename = plugin_basename(__FILE__);
		if ( ! isset( $_GET['action'] ) ) {
			/* Save data for settings page */
			if ( isset( $_REQUEST['htccss_form_submit'] ) && check_admin_referer( $plugin_basename, 'htccss_nonce_name' ) ) {

				if ( 'Order Allow,Deny' != trim( $_REQUEST['htccss_order'] ) && 'Order Deny,Allow' != trim( $_REQUEST['htccss_order'] ) )
					$error = __( "Wrong 'Order fields'. You can enter:", 'htaccess' ) . ' <strong>Order Allow,Deny</strong> ' . __( "or", 'htaccess' ) . ' <strong>Order Deny,Allow</strong>';
				else
					$htccss_options['order'] = trim( $_REQUEST['htccss_order'] );

				$htccss_options['allow'] = trim( trim( preg_replace( '/Allow from /i', '', stripslashes( esc_html( $_REQUEST['htccss_allow'] ) ) ) ), "\n" );
				$htccss_options['deny'] = trim( trim( preg_replace( '/Allow from /i', '', stripslashes( esc_html( $_REQUEST['htccss_deny'] ) ) ) ), "\n" );

				if ( "" == $error ) {
					/* Update options in the database */
					if ( is_multisite() )
						update_site_option( 'htccss_options', $htccss_options );
					else
						update_option( 'htccss_options', $htccss_options );

					$message = __( "Settings saved.", 'htaccess' );
					htccss_generate_htaccess();
				}
			} else {
				htccss_get_htaccess();
			}
		}
		/* GO PRO */
		if ( isset( $_GET['action'] ) && 'go_pro' == $_GET['action'] ) {
			$go_pro_result = bws_go_pro_tab_check( $plugin_basename );
			if ( ! empty( $go_pro_result['error'] ) )
				$error = $go_pro_result['error'];
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
									<th scope="row"><?php _e( 'Access to xmlrpc.php', 'htaccess' ); ?></th>
									<td>
										<label><input type="checkbox" name="htccsspr_xmlrpc" value="1" disabled="disabled"> </label>
										<div class="htccss-help-box"></div><br />
										<span class="htaccess_info htaccess_info_link"><?php _e( "Learn more", 'htaccess' ) ?>: <a target="_blank" href="http://bestwebsoft.com/what-is-xml-rpc/"><?php _e( "What is XML-RPC?", 'htaccess' ); ?></a></span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php _e( 'Disable hotlinking', 'htaccess' ); ?></th>
									<td>
										<label><input type="checkbox" name="htccsspr_hotlink_deny" value="1" disabled="disabled" /> </label>
										<div class="htccss-help-box"></div><br />
										<span class="htaccess_info htaccess_info_link"><?php _e( "Learn more", 'htaccess' ) ?>: <a target="_blank" href="http://bestwebsoft.com/how-to-prevent-hotlinking/"><?php _e( "How to Prevent Hotlinking?", 'htaccess' ); ?></a></span>
									</td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php _e( 'Allow hotlinking for', 'htaccess' ); ?></th>
									<td>
										<textarea name="htccsspr_hotlink_alow"  disabled="disabled"></textarea></br>
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
					<p class="submit">
						<input type="hidden" name="htccss_form_submit" value="submit" />
						<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'htaccess' ); ?>" />
					</p>
					<?php wp_nonce_field( $plugin_basename, 'htccss_nonce_name' ); ?>
				</form>
				<?php bws_plugin_reviews_block( $htccss_plugin_info['Name'], 'htaccess' );
			} elseif ( 'go_pro' == $_GET['action'] ) { 
				bws_go_pro_tab( $htccss_plugin_info, $plugin_basename, 'htaccess.php', 'htaccess-pro.php', 'htaccess-pro/htaccess-pro.php', 'htaccess', 'ac1e1061bf4e95ba51406b4cc32f61fa', '110', isset( $go_pro_result['pro_plugin_is_activated'] ) );
			} ?>
		</div>
	<?php }
}

if ( ! function_exists ( 'htccss_get_htaccess' ) ) {
	function htccss_get_htaccess() {
		global $htccss_options;

		if ( empty( $htccss_options ) ) {
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );
		}

		if ( ! function_exists( 'get_home_path' ) )
			require_once ( ABSPATH . 'wp-admin/includes/file.php' );

		$htaccess_file = get_home_path() . '.htaccess';
		if ( file_exists( $htaccess_file ) ) {
			$handle = fopen( $htaccess_file, "r" );
			if ( $handle ) {
				$htccss_allow_old_array = array();
				$htccss_deny_old_array = array();
				$htccss_order_line = $previous_line = '';
				$htcss_order_section_end = $htcss_order_section_start = $write_disabled = false;
				while ( ! feof( $handle ) ) {
					$current_line = fgets( $handle );

					/* check the validity of the order line */
					if ( preg_match( "/^<{1}[a-z]+/i", $previous_line ) && false !== stripos( $current_line, 'Order ' ) ) {
						$write_disabled = true;
					} else {
						$write_disabled = false;
					}

					/* Check availability block plug-in file or line order */
					if ( false !== stripos( $current_line, '# htccss_order_end #' ) ) {
						$htcss_order_section_end = true;
					} elseif ( false !== stripos( $current_line, '# htccss_order_start #' ) ) {
						$htcss_order_section_start = true;
					}

					if ( false !== stripos( $current_line, 'Order ' ) && isset( $htccss_first_order_exists ) ) {
						$htccss_second_order_exists = true;
					}
					if ( ! $htcss_order_section_start ) {
						if ( false !== stripos( $current_line, 'Order ' ) && ! isset( $htccss_first_order_exists ) && ! $write_disabled ) {
							$htccss_order_line = trim( $current_line );
							$htccss_first_order_exists = true;

						} elseif ( false !== stripos( $current_line, 'Allow' ) || false !== stripos( $current_line, 'Deny' ) ) {
							if ( false !== stripos( $current_line, 'Allow' ) && ! isset( $htccss_second_order_exists ) ) {
								$htccss_allow_old_array[] = trim( str_ireplace( 'Allow from ', '', $current_line ), "\n" );
							} else if ( false !== stripos( $current_line, 'Deny' ) && ! isset( $htccss_second_order_exists ) ) {
								$htccss_deny_old_array[] = trim( str_ireplace( 'Deny from ', '', $current_line ), "\n" );
							}
						}
					} elseif ( $htcss_order_section_start && ! $htcss_order_section_end ) {
						if ( false !== stripos( $current_line, 'Order ' ) ) {
							$htccss_order_line = trim( $current_line );
							$htccss_first_order_exists = true;

						} elseif ( false !== stripos( $current_line, 'Allow' ) || false !== stripos( $current_line, 'Deny' ) ) {
							if ( false !== stripos( $current_line, 'Allow' ) ) {
								$htccss_allow_old_array[] = trim( str_ireplace( 'Allow from ', '', $current_line ), "\n" );
							} else if ( false !== stripos( $current_line, 'Deny' ) ) {
								$htccss_deny_old_array[] = trim( str_ireplace( 'Deny from ', '', $current_line ), "\n" );
							}
						}
					}
					$previous_line = $current_line;
				}
				if ( ! empty( $htccss_order_line ) ) {
					$htccss_options['order'] = $htccss_order_line;
					$htccss_options['allow'] = implode( "\n", $htccss_allow_old_array );
					$htccss_options['deny'] = implode( "\n", $htccss_deny_old_array );
				} else {
					$htccss_options['allow'] = '';
					$htccss_options['deny'] = '';		
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
			if ( false == stripos( $rules, 'Order ' ) && ( '' != $htccss_options['allow'] || '' != $htccss_options['deny'] ) ) {
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
				$previous_line = $content = $htccss_order_line = '';
				$order_flag = $order_flag_exists = $write_order_flag = $write_disabled = $htcss_order_section_start	= $htcss_order_section_end = false;
				$htccss_allow_array			=	explode( "\n", trim( $htccss_options['allow'], "\n" ) );
				$htccss_deny_array			=	explode( "\n", trim( $htccss_options['deny'], "\n" ) );
				$htccss_allow_old_array = $htccss_deny_old_array = array();
				if ( ! empty( $htccss_allow_array ) || ! empty( $htccss_deny_array ) ) {
					while ( ! feof( $handle ) ) {
						$current_line = fgets( $handle );
						/* check the validity of the order line */
						if ( preg_match( "/^<{1}[a-z]+/i", $previous_line ) && false !== stripos( $current_line, 'Order ' ) ) {
							$write_disabled = true;
						} else {
							$write_disabled = false;
						}
						/* Check how many times the line order */
						if ( $order_flag && false !== stripos( $current_line, 'Order ' ) ) {
							$order_flag_exists = true;
						}
						/* Check availability block plug-in file or line order */
						if ( false !== stripos( $current_line, '# htccss_order_end #' ) ) {
							$htcss_order_section_end = true;
						} elseif ( false !== stripos( $current_line, '# htccss_order_start #' ) ) {
							$htcss_order_section_start = true;
						} elseif ( false !== stripos( $current_line, 'Order ' ) &&  ! $htcss_order_section_start && ! $order_flag && ! $write_disabled ) {
							$htccss_order_line = trim( $current_line, "\n" );
							$order_flag = true;
						} elseif ( ( ( $order_flag && ! $order_flag_exists  ) || ( $htcss_order_section_start && ! $htcss_order_section_end ) )  && ( false !== stripos( $current_line, 'Allow' ) || false !== stripos( $current_line, 'Deny' ) ) ) {
							/**/
						} else {
							if ( ( $order_flag || $htcss_order_section_start ) && ! $write_order_flag ) {
								$write_order_flag = true;
								if ( '' != $htccss_options['allow'] || '' != $htccss_options['deny'] ) {
									if ( ( $allow = stripos( $htccss_options['order'], 'Allow' ) < $deny = stripos( $htccss_options['order'], 'Deny' ) ) && empty( $htccss_options['allow'] ) && ! empty( $htccss_options['deny'] ) )				
										$htccss_options['order'] = "Order Deny,Allow";
									$order_allow_deny_content = '# htccss_order_start #' . "\n";
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
									$order_allow_deny_content .= '# htccss_order_end #' . "\n";
									$content .= $order_allow_deny_content . "\n";
								}
							}
							
							if ( 1 >= strlen($current_line) ) {
								if( ! isset( $htccss_empty_string ) ) {
									$content .= $current_line;
									$htccss_empty_string = true;
								}
							}

							if ( 1 < strlen( $current_line ) ) {
								unset( $htccss_empty_string );
								$content .= trim( $current_line, "\n" ) . "\n";
							}
						}
						
						$previous_line = $current_line;
					}
					if ( ! $order_flag && ! $htcss_order_section_start ) {
						$order_allow_deny_content = '# htccss_order_start #' . "\n";
						if ( ( $allow = stripos( $htccss_options['order'], 'Allow' ) < $deny = stripos( $htccss_options['order'], 'Deny' ) ) && empty( $htccss_options['allow'] ) && ! empty( $htccss_options['deny'] ) )				
							$htccss_options['order'] = "Order Deny,Allow";
						if ( '' != $htccss_options['allow'] || '' != $htccss_options['deny'] ) {
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
							$order_allow_deny_content .= '# htccss_order_end #' . "\n"; 
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
if ( ! function_exists( 'htccss_plugin_banner' ) ) {
	function htccss_plugin_banner() {
		global $hook_suffix;
		if ( 'plugins.php' == $hook_suffix ) {  
			global $htccss_plugin_info;
			bws_plugin_banner( $htccss_plugin_info, 'htccss', 'htaccess', 'd97ae872794372d2f58c3f55655bb693', '110', plugins_url( 'images/banner.png', __FILE__ ) );   
		}
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_copy_all' ) ) {
	function htccss_lmtttmpts_copy_all() {
		global $wpdb, $htccss_options;
		
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );

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
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_all' ) ) {
	function htccss_lmtttmpts_delete_all() {
		global $wpdb, $htccss_options;

		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );

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
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_block' ) ) {
	function htccss_lmtttmpts_block( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );
		
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
		if ( ! empty( $htccss_options['deny'] ) && empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_reset_block' ) ) {
	function htccss_lmtttmpts_reset_block( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );
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
							$htccss_options['deny'] = preg_replace( '/('. str_replace( array( ".", "/" ) , array( "\.", "\/" ), $cidr ) . ')($|\s)/', " ", $htccss_options['deny'] ) ;
						}
					}
					unset( $cidr );
				}
			} elseif ( preg_match( $pattern, $htccss_options['deny'] ) ) {
				$htccss_options['deny'] = preg_replace( '/('. str_replace( array( ".", "/" ) , array( "\.", "\/" ), $ip ) . ')($|\s)/', " ", $htccss_options['deny'] ) ;
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
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_delete_from_whitelist' ) ) {
	function htccss_lmtttmpts_delete_from_whitelist( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );
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
							$htccss_options['allow'] = preg_replace( '/('. str_replace( array( ".", "/" ) , array( "\.", "\/" ), $cidr ) . ')($|\s)/', " ", $htccss_options['allow'] ) ;
						}
					}
					unset( $cidr );
				}
			} elseif ( preg_match( $pattern, $htccss_options['allow'] ) ) {
				$htccss_options['allow'] = preg_replace( '/('. str_replace( array( ".", "/" ) , array( "\.", "\/" ), $ip ) . ')($|\s)/', " ", $htccss_options['allow'] ) ;
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
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
		htccss_generate_htaccess();
	}
}

if ( ! function_exists( 'htccss_lmtttmpts_add_to_whitelist' ) ) {
	function htccss_lmtttmpts_add_to_whitelist( $ip ) {
		global $htccss_options;
		if ( empty( $htccss_options ) )
			$htccss_options = ( is_multisite() ) ? get_site_option( 'htccss_options' ) :  get_option( 'htccss_options' );
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
		if ( empty( $htccss_options['deny'] ) && ! empty( $htccss_options['allow'] ) ) {
			$htccss_options['order'] = 'Order Deny,Allow';
		}
		if ( is_multisite() ) {
			update_site_option( 'htccss_options', $htccss_options );
		} else {
			update_option( 'htccss_options', $htccss_options );
		}
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
		if ( is_multisite() )
			delete_site_option( 'htccss_options' );
		else
			delete_option( 'htccss_options' );
	}
}


if ( function_exists( 'is_multisite' ) ) {
	if ( is_multisite() ) {
		add_action( 'network_admin_menu', 'add_htccss_admin_menu' );
	} else {
		add_action( 'admin_menu', 'add_htccss_admin_menu' );
	}
}
add_action( 'init', 'htccss_plugin_init' );
add_action( 'admin_init', 'htccss_plugin_admin_init' );
add_action( 'admin_enqueue_scripts', 'htccss_admin_head' );
add_action( 'admin_notices', 'htccss_plugin_banner' );
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