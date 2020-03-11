<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Htccss_Settings_Tabs' ) ) {
	class Htccss_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $htccss_options, $htccss_plugin_info;

			$tabs = array(
				'settings'		=> array( 'label' => __( 'Settings', 'htaccess' ) ),
				'misc'			=> array( 'label' => __( 'Misc', 'htaccess' ) ),
				'license'		=> array( 'label' => __( 'License Key', 'htaccess' ) )
			);

			parent::__construct( array(
				'plugin_basename'	=> $plugin_basename,
				'plugins_info'		=> $htccss_plugin_info,
				'prefix'			=> 'htccss',
				'default_options'	=> htccss_get_options_default(),
				'options'			=> $htccss_options,
				'is_network_options'=> is_network_admin(),
				'tabs'				=> $tabs,
				'wp_slug'			=> 'htaccess',
				'pro_page'           => 'admin.php?page=htaccess-pro.php',
				'bws_license_plugin' => 'htaccess-pro/htaccess-pro.php',
				'link_key'           => 'ac1e1061bf4e95ba51406b4cc32f61fa',
				'link_pn'            => '110'
			) );

			add_filter( get_parent_class( $this ) . '_additional_restore_options', array( $this, 'additional_restore_options' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param void
		 * @return array The action results
		 */
		public function save_options() {
			global $htccss_options;
			$message = $notice = $error = '';

			/* Form string of IPs for writing in '$htccss_options['allow']' and '$htccss_options['deny']'. Start */
			$first_allow = $second_allow = $third_allow = $fourth_allow = $first_deny = $second_deny = $third_deny = $fourth_deny = array();
			$allowed_ip = $denied_ip = $all_allowed_ips = $all_denied_ips = '';
			foreach ( $_POST as $key => $value ) {
				if ( preg_match( '(htccss_allow_1_)', $key ) && ( '' != $value ) ) {
					$first_allow[] = $key;
				} elseif ( preg_match( '(htccss_allow_2_)',$key ) && ( '' != $value ) ) {
					$second_allow[] = $key;
				} elseif ( preg_match( '(htccss_allow_3_)', $key ) && ( '' != $value ) ) {
					$third_allow[] = $key;
				} elseif ( preg_match( '(htccss_allow_4_)', $key ) && ( '' != $value ) ) {
					$fourth_allow[] = $key;
				} elseif ( preg_match( '(htccss_deny_1_)', $key ) && ( '' != $value ) ) {
					$first_deny[] = $key;
				} elseif ( preg_match( '(htccss_deny_2_)', $key ) && ( '' != $value ) ) {
					$second_deny[] = $key;
				} elseif ( preg_match( '(htccss_deny_3_)', $key ) && ( '' != $value ) ) {
					$third_deny[] = $key;
				} elseif ( preg_match( '(htccss_deny_4_)', $key ) && ( '' != $value ) ) {
					$fourth_deny[] = $key;
				}
			}
			/* Сheck if all the fields are filled in */
			$flag_allow = false;
			if ( count( $first_allow ) == count( $second_allow ) && count( $second_allow ) == count( $third_allow ) && count( $third_allow ) == count( $fourth_allow ) ) {
				$count_allowed_ips = count( $first_allow );
			} else {
				$count_allowed_ips = min( count( $first_allow ), count( $second_allow ), count( $third_allow ), count( $fourth_allow ) );
				$flag_allow = true;
			}
			$flag_deny = false;
			if ( count( $first_deny ) == count( $second_deny ) && count( $second_deny ) == count( $third_deny ) && count( $third_deny ) == count( $fourth_deny ) ) {
				$count_denied_ips = count( $first_deny );
			} else {
				$count_denied_ips = min( count( $first_deny ), count( $second_deny ), count( $third_deny ), count( $fourth_deny ) );
				$flag_deny = true;
			}
			/* End chek */
			for ( $j = 0; $j < $count_allowed_ips; $j++ ) {
				if ( ! empty( $_POST[ $first_allow[ $j ] ] ) ) {
					$allowed_ip = $_POST[ $first_allow[ $j ] ] . '.' . $_POST[ $second_allow[ $j ] ] . '.' . $_POST[ $third_allow[ $j ] ] . '.' . $_POST[ $fourth_allow[ $j ] ];
				} else {
					$allowed_ip = '';
				}
				$all_allowed_ips .= $allowed_ip . ' ';
			}
			for ( $j = 0; $j < $count_denied_ips; $j++ ) {
				if ( ! empty( $_POST[ $first_deny[ $j ] ] ) ) {
					$denied_ip = $_POST[ $first_deny[ $j ] ] . '.' . $_POST[ $second_deny[ $j ] ] . '.' . $_POST[ $third_deny[ $j ] ] . '.' . $_POST[ $fourth_deny[ $j ] ];
				} else {
					$denied_ip = '';
				}
				$all_denied_ips .= $denied_ip . ' ';
			}
			/* End */
			/* This filter is needed for prevent removing domain names, netmasks, etc. from $htccss_options['allow'] and $htccss_options['deny']. Start */
			$domains_allow = $domains_deny = '';
			if ( ! empty( $this->options['allow'] ) ) {
				$domains_allow = array();
				$allow_array = preg_split( "/[\t\n\r\s\,]+/", trim( $htccss_options['allow'] ), -1, PREG_SPLIT_NO_EMPTY );
				foreach ( $allow_array as $key => $value ) {
					if ( preg_match('/[^\d.]+/', $value) ) {
						$domains_allow[] = $value;
					}
				}
				$domains_allow = implode(' ', $domains_allow);
			}
			if ( ! empty( $this->options['deny'] ) ) {
				$domains_deny = array();
				$deny_array = preg_split( "/[\t\n\r\s\,]+/", trim( $this->options['deny'] ), -1, PREG_SPLIT_NO_EMPTY );
				foreach ( $deny_array as $key => $value ) {
					if ( preg_match('/[^\d.]+/', $value) ) {
						$domains_deny[] = $value;
					}
				}
				$domains_deny = implode(' ', $domains_deny);
			}
			/* End */
			$this->options['order']			= isset( $_POST['htccss_order'] ) && in_array( $_POST['htccss_order'], array( 'Order Allow,Deny', 'Order Deny,Allow' ) ) ? $_REQUEST['htccss_order'] : 'Order Deny,Allow';	
			$this->options['allow']			= htccss_esc_directive( $all_allowed_ips . ' ' . $domains_allow );
			$this->options['deny']				= htccss_esc_directive( $all_denied_ips . ' ' . $domains_deny );
			$this->options['allow_xml']		= isset( $_REQUEST['htccss_allow_xml'] ) ? 1 : 0;
			$this->options = array_map( 'stripslashes_deep', $this->options );
			if ( ! empty( $count_allowed_ips ) ) {
				$this->options['amount_of_allow_forms'] = $count_allowed_ips;
			} else {
				$this->options['amount_of_allow_forms'] = 1;
			}
			if ( ! empty( $count_denied_ips ) ) {
				$this->options['amount_of_deny_forms'] = $count_denied_ips;
			} else {
				$this->options['amount_of_deny_forms'] = 1;
			}

			/* Verification of the validity of the IP addresses entered. Start */
			$all_allowed_ips = trim( $all_allowed_ips );
			$all_denied_ips = trim( $all_denied_ips );
			if ( $flag_allow ) {
				$error_allow_text = '<p><strong>' . __( 'Notice: ', 'htaccess' ) . '</strong>' . __( 'You have entered an incorrect value for "Allow from" field. Settings are not saved.', 'htaccess' ) . '</p>';
				$error = $error_allow_text;
			} elseif ( ! empty( $all_allowed_ips ) ) {
				$htccss_allow_arr = preg_split("/[\s,]+/", $all_allowed_ips, -1, PREG_SPLIT_NO_EMPTY );
				foreach ( $htccss_allow_arr as $key => $value ) {
					if ( ! filter_var( $value, FILTER_VALIDATE_IP ) || $flag_allow ) {
						$error_allow_text = '<p><strong>' . __( 'Notice: ', 'htaccess' ) . '</strong>' . __( 'You have entered an incorrect value for "Allow from" field. Settings are not saved.', 'htaccess' ) . '</p>';
						$error = $error_allow_text;
						break;
					}
				}
			}
			if ( $flag_deny ) {
				$error = '<p><strong>' . __( 'Notice: ', 'htaccess' ) . '</strong>' . __( 'You have entered an incorrect value for "Deny from" field. Settings are not saved.', 'htaccess' ) . '</p>';
				if ( isset( $error_allow_text ) ) {
					$error = '<p><strong>' . __( 'Notice: ', 'htaccess' ) . '</strong>' . __( 'You have entered an incorrect value for "Allow from" and "Deny from" fields. Settings are not saved.', 'htaccess' ) . '</p>';
				}
			} elseif ( ! empty( $all_denied_ips ) ) {
				$htccss_deny_arr = preg_split("/[\s,]+/", $all_denied_ips, -1, PREG_SPLIT_NO_EMPTY );
				foreach ( $htccss_deny_arr as $key => $value ) {
					if ( ! filter_var( $value, FILTER_VALIDATE_IP ) || $flag_deny ) {
						$error = '<p><strong>' . __( 'Notice: ', 'htaccess' ) . '</strong>' . __( 'You have entered an incorrect value for "Deny from" field. Settings are not saved.', 'htaccess' ) . '</p>';
						if ( isset( $error_allow_text ) ) {
							$error = '<p><strong>' . __( 'Notice: ', 'htaccess' ) . '</strong>' . __( 'You have entered an incorrect value for "Allow from" and "Deny from" fields. Settings are not saved.', 'htaccess' ) . '</p>';
						}
						break;
					}
				}
			}
			/* End */
			if ( "" == $error ) {
				/* Update options in the database */
				if ( $this->is_multisite ) {
					update_site_option( 'htccss_options', $this->options );
				} else {
					update_option( 'htccss_options', $this->options );
				}
				$message = __( 'Settings saved.', 'htaccess' );
				$htccss_options = $this->options;
				htccss_generate_htaccess();
			} else {
				htccss_get_htaccess();
			}

			return compact( 'message', 'notice', 'error' );
		}

		public function tab_settings() {
			global $htccss_auto_added, $htccss_active_plugins; ?>
			<h3 class="bws_tab_label"><?php _e( 'Htaccess Settings', 'htaccess' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php _e( 'Directives Order', 'htaccess' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="htccss_order" value="Order Allow,Deny" <?php checked( 'Order Allow,Deny', $this->options['order'] ); ?>/> Allow,Deny</label><br />
							<label><input type="radio" name="htccss_order" value="Order Deny,Allow" <?php checked( 'Order Deny,Allow', $this->options['order'] ); ?>/> Deny,Allow</label>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Allow from', 'htaccess' ); ?></th>
					<td>
						<div class="htccss_allow_container">
							<div class="htccss_allow_form" style="display: none;">
								<input type="text" name="htccss_allow_1_" class="htccss_ip" data-numb="1" />
								<span class="htccss_dot">.</span>
								<input type="text" name="htccss_allow_2_" class="htccss_ip" data-numb="2" />
								<span class="htccss_dot">.</span>
								<input type="text" name="htccss_allow_3_" class="htccss_ip" data-numb="3" />
								<span class="htccss_dot">.</span>
								<input type="text" name="htccss_allow_4_" class="htccss_ip" data-numb="4" maxlength="3" />
								<span class="dashicons dashicons-trash htccss_trash_allow"></span>
							</div>
                            <?php /* Create variables to fill out form filds. Start */
                            $count_allowed_ips = $this->options['amount_of_allow_forms'];
                            $first_allow = $second_allow = $third_allow = $fourth_allow = $first_deny = $second_deny = $third_deny = $fourth_deny = array();
                            if ( ! empty( $this->options['allow'] ) ) {

                                $allow_array = preg_split( "/[\t\n\r\s\,]+/", trim( $this->options['allow'] ), -1, PREG_SPLIT_NO_EMPTY );

                                foreach ( $allow_array as $key => $value ) {
                                    if ( preg_match('/[^\d.]+/', $value) ) {
                                        continue;
                                    }
                                    $allow_single = preg_split( "/[\.,]+/", trim( $value ), -1, PREG_SPLIT_NO_EMPTY );
                                    $first_allow[] = $allow_single[0];
                                    $second_allow[] = $allow_single[1];
                                    $third_allow[] = $allow_single[2];
                                    $fourth_allow[] = $allow_single[3];
                                }
                            }
                            if ( ! empty( $this->options['deny'] ) ) {

                                $deny_array  = preg_split( "/[\t\n\r\s\,]+/", trim( $this->options['deny'] ), -1, PREG_SPLIT_NO_EMPTY );

                                foreach ( $deny_array as $key => $value ) {
                                    if ( preg_match('/[^\d.]+/', $value) ) {
                                        continue;
                                    }
                                    $deny_single = preg_split( "/[\.,]+/", trim( $value ), -1, PREG_SPLIT_NO_EMPTY );
                                    $first_deny[] = $deny_single[0];
                                    $second_deny[] = $deny_single[1];
                                    $third_deny[] = $deny_single[2];
                                    $fourth_deny[] = $deny_single[3];
                                }
                            }
                            for ( $i = 0; $i < $count_allowed_ips; $i++ ) {
                                if ( isset( $first_allow[ $i ] ) ) {
                                    $allow_1 = $first_allow[ $i ];
                                    $allow_2 = $second_allow[ $i ];
                                    $allow_3 = $third_allow[ $i ];
                                    $allow_4 = $fourth_allow[ $i ];
                                } else {
                                    $allow_1 = $allow_2 = $allow_3 = $allow_4 = '';
                                }
                                /* End */ ?>
                                <div class="htccss_allow_form">
                                    <input type="text" name="htccss_allow_1_<?php echo $i; ?>" class="htccss_ip" data-numb="1" value="<?php echo $allow_1; ?>" />
                                    <span class="htccss_dot">.</span>
                                    <input type="text" name="htccss_allow_2_<?php echo $i; ?>" class="htccss_ip" data-numb="2" value="<?php echo $allow_2; ?>" />
                                    <span class="htccss_dot">.</span>
                                    <input type="text" name="htccss_allow_3_<?php echo $i; ?>" class="htccss_ip" data-numb="3" value="<?php echo $allow_3; ?>" />
                                    <span class="htccss_dot">.</span>
                                    <input type="text" name="htccss_allow_4_<?php echo $i; ?>" class="htccss_ip" data-numb="4" value="<?php echo $allow_4; ?>" maxlength="3" />
                                    <span class="dashicons dashicons-trash htccss_trash_allow"></span>
                                </div>
                            <?php } ?>
						</div>
						<input type="button" name="htccss_add_allow_ip" class="htccss_add_allow_ip_button button button-secondary" value="<?php _e( 'Add IP address', 'htaccess' ); ?>" />
						<div class="bws_info"><?php _e( "Info about the arguments to the Allow directive", 'htaccess' ) ?>: <a href="https://bestwebsoft.com/controlling-access-to-your-website-using-the-htaccess/#Allow_Directive" target="_blank"><?php _e( "Controlling access to your website using the .htaccess", 'htaccess' ); ?></a></div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Deny from', 'htaccess' ); ?></th>
					<td>
						<div class="htccss_deny_container">
							<div class="htccss_deny_form" style="display: none;">
								<input type="text" name="htccss_deny_1_" class="htccss_ip" data-numb="1" />
								<span class="htccss_dot">.</span>
								<input type="text" name="htccss_deny_2_" class="htccss_ip" data-numb="2" />
								<span class="htccss_dot">.</span>
								<input type="text" name="htccss_deny_3_" class="htccss_ip" data-numb="3" />
								<span class="htccss_dot">.</span>
								<input type="text" name="htccss_deny_4_" class="htccss_ip" data-numb="4" maxlength="3" />
								<span class="dashicons dashicons-trash htccss_trash_deny"></span>
							</div>
                            <?php $count_denied_ips = $this->options['amount_of_deny_forms'];
                            for ( $i = 0; $i < $count_denied_ips; $i++ ) {
                                if ( isset( $first_deny[ $i ] ) ) {
                                    $deny_1 = $first_deny[ $i ];
                                    $deny_2 = $second_deny[ $i ];
                                    $deny_3 = $third_deny[ $i ];
                                    $deny_4 = $fourth_deny[ $i ];
                                } else {
                                    $deny_1 = $deny_2 = $deny_3 = $deny_4 = '';
                                } ?>
                                <div class="htccss_deny_form">
                                    <input type="text" name="htccss_deny_1_<?php echo $i; ?>" class="htccss_ip" data-numb="1" value="<?php echo $deny_1; ?>" />
                                    <span class="htccss_dot">.</span>
                                    <input type="text" name="htccss_deny_2_<?php echo $i; ?>" class="htccss_ip" data-numb="2" value="<?php echo $deny_2; ?>" />
                                    <span class="htccss_dot">.</span>
                                    <input type="text" name="htccss_deny_3_<?php echo $i; ?>" class="htccss_ip" data-numb="3" value="<?php echo $deny_3; ?>" />
                                    <span class="htccss_dot">.</span>
                                    <input type="text" name="htccss_deny_4_<?php echo $i; ?>" class="htccss_ip" data-numb="4" value="<?php echo $deny_4; ?>" maxlength="3" />
                                    <span class="dashicons dashicons-trash htccss_trash_deny"></span>
                                </div>
                            <?php } ?>
						</div>
						<input type="button" name="htccss_add_deny_ip_button" class="htccss_add_deny_ip_button button button-secondary" value="<?php _e( 'Add IP address', 'htaccess' ); ?>" />
						<div class="bws_info"><?php _e( "Info about the arguments to the Deny directive", 'htaccess' ) ?>: <a href="https://bestwebsoft.com/controlling-access-to-your-website-using-the-htaccess/#Deny_Directive" target="_blank"><?php _e( "Controlling access to your website using the .htaccess", 'htaccess' ); ?></a></div>
					</td>
				</tr>
				<?php if ( $htccss_active_plugins ) {
					$plugins		= array();
					foreach ( $htccss_active_plugins as $plugin ) {
						$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
						$args		= explode( '/', $plugin );
						/**
						 * don`t display link on plugin settings page for multisite because
						 * it is unknown for which blog the plugin has been activated
						 */
						$plugins[] = $this->is_multisite ? $plugin_data['Name'] : "<a href=\"admin.php?page={$args[1]}\">{$plugin_data['Name']}</a>";
					}
					if ( ! empty( $htccss_auto_added['allow'] ) ) { ?>
						<tr valign="top">
							<th scope="row"><?php _e( 'Allow from (automatically added)', 'htaccess' ); ?></th>
							<td>
								<textarea disabled="disabled" class="bws_no_bind_notice"><?php echo $htccss_auto_added['allow']; ?></textarea>
								<?php if ( empty( $htccss_auto_added['deny'] ) ) { ?>
									<div class="bws_info"><?php echo __( 'You can edit the content of directives that have been automatically added to', 'htaccess' ) . ' ' . sprintf( _n( 'plugin settings page %s', 'settings pages of next plugins: %s', count( $plugins ), 'htaccess' ) . '.', implode( ', ', $plugins ) ); ?></div>
								<?php }?>
							</td>
						</tr>
					<?php }
					if ( ! empty( $htccss_auto_added['deny'] ) ) { ?>
						<tr valign="top">
							<th scope="row"><?php _e( 'Deny from (automatically added)', 'htaccess' ); ?></th>
							<td>
								<textarea disabled="disabled" class="bws_no_bind_notice"><?php echo $htccss_auto_added['deny']; ?></textarea>
								<div class="bws_info"><?php echo __( 'You can edit the content of directives that have been automatically added to', 'htaccess' ) . ' ' . sprintf( _n( 'plugin settings page %s', 'settings pages of next plugins: %s', count( $plugins ), 'htaccess' ) . '.', implode( ', ', $plugins ) ); ?></div>
							</td>
						</tr>
					<?php }
				} ?>
			 </table>
			<?php if ( ! $this->hide_pro_tabs ) { ?>
                <div class="bws_pro_version_bloc">
                    <div class="bws_pro_version_table_bloc">
                        <button type="submit" name="bws_hide_premium_options"
                                class="notice-dismiss bws_hide_premium_options"
                                title="<?php _e( 'Close', 'htaccess' ); ?>"></button>
                        <div class="bws_table_bg"></div>
                        <table class="form-table bws_pro_version">
                            <tr valign="top">
								<th scope="row"><?php _e( 'Disable Access to xmlrpc.php', 'htaccess' ); ?></th>
								<td><fieldset>
									<label>
										<input disabled="disabled" type="checkbox" name="htccss_xmlrpc" value="1" />
										<span class="bws_info">
											<?php echo __( 'Disable the WordPress, Movable Type, MetaWeblog and Blogger XML-RPC publishing protocols.', 'htaccess' ) . ' <a target="_blank" href="https://bestwebsoft.com/what-is-xml-rpc/">' . __( "Read more about XML-RPC", 'htaccess' ) . '</a>'; ?>
										</span>
									</label>
									<div>
										<label>
											<input disabled="disabled" type="radio" name="htccss_xmlrpc_access" value="disabled" /> <?php _e( 'Deny Access', 'htaccess' ); ?>											
										</label>
										<br />
										<label>
											<input disabled="disabled" type="radio" name="htccss_xmlrpc_access" value="rewrite" /> <?php _e( 'Redirect to the main page', 'htaccess' ); ?>
										</label>
									</div>
								</fieldset></td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Hotlinking Protection', 'htaccess' ); ?></th>
								<td>
									<label>
										<input disabled="disabled" type="checkbox" name="htccss_hotlink_deny" class="bws_option_affect" value="1" />
										<span class="bws_info"><?php _e( 'Enable to activate the hotlinking protection (recommended).', 'htaccess' ); ?></span>										
									</label>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php _e( 'Allow Hotlinking for', 'htaccess' ); ?></th>
								<td>
									<textarea disabled="disabled" id="htccss_hotlink_alow" name="htccss_hotlink_alow"></textarea>
									<div class="bws_info">
										<?php _e( 'Allowed hosts should be entered comma separated.', 'htaccess' ); ?>
										<p><?php _e( 'The hosts you entered will be added to .htaccess file in the form of the string', 'htaccess' ); ?>:</p>
										<code>RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?.*<strong>allow</strong>\.<strong>host</strong>/.*$ [NC]</code>
									</div>
								</td>
							</tr>
                        </table>
                    </div>
					<?php $this->bws_pro_block_links(); ?>
                </div>
			<?php }
			if ( $this->is_multisite && ! is_subdomain_install() ) { ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Allow Access to XML Files', 'htaccess' ); ?>
						</th>
						<td>
							<label><input type="checkbox" name="htccss_allow_xml" value="1"<?php echo 1 == $this->options['allow_xml'] ? ' checked="checked"' : ''; ?> /></label>
							<span class="bws_info"><?php printf( __( 'It is necessary to get the access to sitemap files of all network`s blogs via link like %s', 'htaccess' ), 'http://example.com/blog-folder/blog-sitemap.xml' ); ?></span>
							<?php echo bws_add_help_box(
								__( 'The following string will be added to your .htaccess file', 'htaccess' ) . ': <code>RewriteRule ([^/]+\.xml)$ $1 [L]</code>'
							); ?>
						</td>
					</tr>
				</table>
			<?php }
		}

		/**
		 * Additional actions on 'Restore Settings'.
		 * @access public
		 */
		public function additional_restore_options( $options ) {
			/* Important! We need to restore to default checkbox options only and do not touch all order directives with IPs */

			$default_options['order'] = $this->options['order'];
			$default_options['allow'] = $this->options['allow'];
			$default_options['deny'] = $this->options['deny'];

			$default_options['amount_of_allow_forms'] = $this->options['amount_of_allow_forms'];
			$default_options['amount_of_deny_forms'] = $this->options['amount_of_deny_forms'];
									
			return $options;
		}
	}
}
