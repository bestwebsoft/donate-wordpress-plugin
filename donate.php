<?php
/*
Plugin Name: Donate by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/donate/
Description: Add PayPal and 2CO donate buttons to receive charity payments.
Author: BestWebSoft
Text Domain: donate-button
Domain Path: /languages
Version: 2.1.6
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
*/

/*
	© Copyright 2019  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Create pages for the plugin */
if ( ! function_exists ( 'dnt_add_admin_menu' ) ) {
	function dnt_add_admin_menu() {
		$settings = add_menu_page( __( 'Donate Settings', 'donate-button' ), 'Donate', 'manage_options', 'donate.php', 'dnt_admin_settings', 'none' );
		add_submenu_page( 'donate.php', __( 'Donate Settings', 'donate-button' ), __( 'Settings', 'donate-button' ), 'manage_options', 'donate.php', 'dnt_admin_settings' );
		add_submenu_page( 'donate.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'donate-bws-panel', 'bws_add_menu_render' );
		add_action( 'load-' . $settings, 'dnt_add_tabs' );
	}
}

if ( ! function_exists( 'dnt_plugins_loaded' ) ) {
	function dnt_plugins_loaded() {
		/* Internationalization */
		load_plugin_textdomain( 'donate-button', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists( 'dnt_init' ) ) {
	function dnt_init() {
		global $dnt_plugin_info;
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $dnt_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$dnt_plugin_info = get_plugin_data( __FILE__ );
		}
		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $dnt_plugin_info, '3.9' );
		/* Get/Register and check settings for plugin */
		dnt_register_settings();
	}
}

if ( ! function_exists( 'dnt_admin_init' ) ) {
	function dnt_admin_init() {
		global $bws_plugin_info, $dnt_plugin_info, $bws_shortcode_list;

		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array( 'id' => '103', 'version' => $dnt_plugin_info["Version"] );
		}

		/* add gallery to global $bws_shortcode_list  */
		$bws_shortcode_list['dnt'] = array( 'name' => 'Donate', 'js_function' => 'dnt_shortcode_init' );
	}
}

/* Plugin activate */
if ( ! function_exists( 'dnt_plugin_activate' ) ) {
	function dnt_plugin_activate() {
		/* registering uninstall hook */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'dnt_delete_options' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'dnt_delete_options' );
		}
	}
}

/* Register default settings */
if ( ! function_exists ( 'dnt_register_settings' ) ) {
	function dnt_register_settings() {
		/* Database array for payment */
		global $dnt_options, $dnt_plugin_info, $dnt_option_defaults;

		$dnt_option_defaults = array(
			'paypal_options' => array(
				'paypal_purpose'     => '',
				'paypal_account'     => 0,
				'paypal_amount'      => '1.00',
				'paypal_currency'    => 'USD',
				'item_source_paypal' => '',
				'img'                => ''
			),
			'co_options' => array(
				'co_account'     => 0,
				'co_quantity'    => 1,
				'product_id'     => '',
				'item_source_co' => '',
				'img'            => '',
			),
			'plugin_option_version'   => $dnt_plugin_info["Version"],
			'display_settings_notice' => 1,
			'suggest_feature_banner'  => 1,
		);
		if ( ! get_option( 'dnt_options' ) ) {
			add_option( 'dnt_options', $dnt_option_defaults );
		}

		$dnt_options = get_option( 'dnt_options' );

		if ( ! isset( $dnt_options['plugin_option_version'] ) || $dnt_options['plugin_option_version'] != $dnt_plugin_info["Version"] ) {
			dnt_plugin_activate();
			$dnt_option_defaults['display_settings_notice'] = 0;
			$dnt_options = array_replace_recursive( $dnt_option_defaults, $dnt_options );
			$dnt_options['plugin_option_version'] = $dnt_plugin_info["Version"];
			update_option( 'dnt_options', $dnt_options );
		}
	}
}

/* PayPal API */
if ( ! function_exists( 'dnt_draw_paypal_form' ) ) {
	function dnt_draw_paypal_form() {
		$dnt_options = get_option( 'dnt_options', array() ); ?>
		<input type='hidden' name='business' value="<?php echo $dnt_options['paypal_options']['paypal_account']; ?>" />
		<input type='hidden' name='item_name' value="<?php echo $dnt_options['paypal_options']['paypal_purpose']; ?>" />
		<input type='hidden' name='amount' value="<?php echo $dnt_options['paypal_options']['paypal_amount']; ?>" />
		<input type="hidden" name='currency_code' value="<?php echo $dnt_options['paypal_options']['paypal_currency']; ?>" />
		<input type='hidden' name='cmd' value='_donations' />
	<?php }
}

/* 2CO API */
if ( ! function_exists( 'dnt_draw_co_form' ) ) {
	function dnt_draw_co_form() {
		$dnt_options = get_option( 'dnt_options', array() ); ?>
		<input type='hidden' name='sid' value="<?php echo $dnt_options['co_options']['co_account']; ?>" />
		<input type='hidden' name='quantity' value="<?php echo $dnt_options['co_options']['co_quantity']; ?>" />
		<input type='hidden' name='product_id' value="<?php echo $dnt_options['co_options']['product_id']; ?>" />
	<?php }
}

/* Add CSS and JS for plugin */
if ( ! function_exists ( 'dnt_wp_enqueue_scripts' ) ) {
	function dnt_wp_enqueue_scripts() {
		wp_enqueue_style( 'dnt_style', plugins_url( 'css/style.css', __FILE__ ) );
	}
}

/* Add CSS and JS for plugin */
if ( ! function_exists ( 'dnt_wp_footer' ) ) {
	function dnt_wp_footer() {
		if ( wp_script_is( 'dnt_script', 'registered' ) ) {
			wp_enqueue_script( 'dnt_script' );
		} elseif ( defined( 'BWS_ENQUEUE_ALL_SCRIPTS' ) ) {
			wp_enqueue_script( 'dnt_script', plugins_url( 'js/script.js', __FILE__ ) , array( 'jquery' ), false, true );
		}
	}
}

if ( ! function_exists( 'dnt_pagination_callback' ) ) {
	function dnt_pagination_callback( $content ) {
		$content .= "$( '.dnt-options-box' ).hide();";
		return $content;
	}
}

/* Add CSS and JS for plugin */
if ( ! function_exists ( 'dnt_admin_enqueue_scripts' ) ) {
	function dnt_admin_enqueue_scripts() {
		global $hook_suffix;
		wp_enqueue_style( 'dnt_icon', plugins_url( 'css/icon.css', __FILE__ ) );
		if ( 'widgets.php' == $hook_suffix || ( isset( $_GET['page'] ) && 'donate.php' == $_GET['page'] ) ) {
			wp_enqueue_style( 'dnt_style', plugins_url( 'css/style.css', __FILE__ ) );

			bws_enqueue_settings_scripts();

			if ( isset( $_GET['page'] ) && 'donate.php' == $_GET['page'] ) {
				wp_enqueue_script( 'dnt_admin_script', plugins_url( 'js/admin_script.js', __FILE__ ) , array( 'jquery' ) );

				if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] ) {
					bws_plugins_include_codemirror();
				}
			}
		}
	}
}

/* Create Pay Options Box */
if ( ! function_exists( 'dnt_options_box' ) ) {
	function dnt_options_box() { ?>
		<div class='dnt-box'>
			<div class='dnt-title'><?php _e( 'Please choose the donation payment system', 'donate-button' ); ?></div>
			<div class='dnt-content'>
				<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window' >
					<input type='image' id='dnt_paypal_button' class='dnt-paypal-button'  src="<?php echo plugins_url( 'images/paypal.jpg', __FILE__ ) ?>" alt='paypal' title='PayPal checkout' />
					<?php dnt_draw_paypal_form(); ?>
				</form>
				<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window' >
					<input type='image' id='dnt_co_button' class='dnt-co-button' src="<?php echo plugins_url( 'images/co.jpg', __FILE__ ) ?>" alt='2CO' title='2CO checkout' />
					<?php dnt_draw_co_form(); ?>
				</form>
			</div>
		</div>
	<?php }
}

/* Register Donate_Widget widget */
if ( ! function_exists( 'dnt_register_widget' ) ) {
	function dnt_register_widget() {
		register_widget( 'Donate_Widget' );
	}
}

/* Add widget */
if ( ! class_exists( 'Donate_Widget' ) ) {
	class Donate_Widget extends WP_Widget {
		/* Register widget with WordPress */
		function __construct() {
			parent::__construct(
				'donate_widget',
				'Donate ' . __( 'Widget', 'donate-button' ),
				array( 'description' => 'Donate ' . __( 'Widget', 'donate-button' ), )
			);
		}

		/* Front-end display of widget */
		public function widget( $args, $instance ) {
			global $dnt_options;
			if ( isset( $instance['dnt_widget_button_options_co'] ) && 'hide' == $instance['dnt_widget_button_options_co']
				&& isset( $instance['dnt_widget_button_options_paypal'] )  && 'hide' == $instance['dnt_widget_button_options_paypal'] ) {
				/* Do not show widget in front-end */
			} else {
				echo $args['before_widget'];

				echo $args['before_title'];
				echo ( ! empty( $instance['dnt_widget_title'] ) ) ? apply_filters( 'widget_title', $instance['dnt_widget_title'], $instance, $this->id_base ) : '';
				echo $args['after_title']; ?>
				<ul class="dnt-widdget-list">
					<li>
						<?php if ( 'donate' != $instance['dnt_widget_button_system'] && in_array( $instance['dnt_widget_button_options_co'], array( 'default', 'small', 'custom', 'credits' ) ) ) { ?>
							<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window'>
								<?php if ( 'custom' == $instance['dnt_widget_button_options_co'] ) { ?>
									<input type='image' class='dnt-co-button' src='<?php echo $dnt_options['co_options']['img']; ?>' alt='custom-button-co' />
								<?php } else { ?>
									<input type='image' class='dnt-co-button' src="<?php echo plugins_url( 'images/co-' . $instance['dnt_widget_button_options_co'] . '.png', __FILE__ ); ?>" alt='co-button' />
								<?php }
								dnt_draw_co_form(); ?>
							</form>
						<?php }
						if ( 'donate' != $instance['dnt_widget_button_system'] && in_array( $instance['dnt_widget_button_options_paypal'], array( 'default', 'small', 'custom', 'credits' ) ) ) { ?>
							<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window'>
								<?php if ( 'custom' == $instance['dnt_widget_button_options_paypal'] ) { ?>
									<input type='image' class='dnt-paypal-button' src='<?php echo $dnt_options['paypal_options']['img']; ?>' alt='custom-button-paypal' />
								<?php } else { ?>
									<input type='image' class='dnt-paypal-button' src="<?php echo plugins_url( 'images/paypal-' . $instance['dnt_widget_button_options_paypal'] . '.png', __FILE__ ); ?>" alt='paypal-button' />
								<?php }
								dnt_draw_paypal_form(); ?>
							</form>
						<?php } elseif ( 'donate' == $instance['dnt_widget_button_system'] ) { ?>
							<div class='dnt-button'><img src="<?php echo plugins_url( 'images/donate-button.png', __FILE__ ); ?>" alt='donate-button' />
								<?php dnt_options_box(); ?>
							</div>
						<?php } ?>
					</li>
				</ul>
				<?php echo $args['after_widget'];

				if ( ! wp_script_is( 'dnt_script', 'registered' ) ) {
					wp_register_script( 'dnt_script', plugins_url( 'js/script.js', __FILE__ ) , array( 'jquery' ), false, true );
				}
			}
		}

		/* Back-end widget form */
		public function form( $instance ) {
			global $dnt_options;
			$default_widget_args = array(
				'dnt_widget_button_system'         => 'donate',
				'dnt_widget_button_options_paypal' => 'default',
				'dnt_widget_button_options_co'     => 'default',
				'dnt_widget_title'                 => '',
			);
			$instance = wp_parse_args( ( array ) $instance, $default_widget_args ); ?>
			<script type="text/javascript">
				/* we added script here (not in separete file) because we need to do js after widget update */
				(function( $ ) {
					$( document ).ready(function() {
						$( '.dnt-tabs-panel-co' ).addClass( 'hidden' ).removeClass( 'hide-if-js' );
						$( '#dnt_paypal_widget_tab, #dnt_co_widget_tab' ).on( 'click', function() {
							var parent = $( this ).parents( '.dnt-settings-donate' ).filter( ':first' );
							$( parent ).find( 'li' ).removeClass( 'tabs' );
							$( this ).parent().addClass( 'tabs' );

							$( parent ).find( '.dnt-tabs-panel-paypal, .dnt-tabs-panel-co' ).addClass( 'hidden' ).removeClass( 'hide-if-js' );
							if ( 'dnt_paypal_widget_tab' == $( this ).attr( 'id' ) ) {
								$( parent ).find( '.dnt-tabs-panel-paypal' ).removeClass( 'hidden' );
							} else {
								$( parent ).find( '.dnt-tabs-panel-co' ).removeClass( 'hidden' );
							}
						} );
						/* Widget disabling/enabling checkboxes */
						$( '.dnt_checkbox_donate' ).on( 'click', function() {
							if ( $( this ).is( ':checked' ) ) {
								$( '.dnt-tabs-panel-paypal input[type="radio"], .dnt-tabs-panel-co input[type="radio"]' ).attr( 'disabled', 'disabled' );
							} else {
								$( '.dnt-tabs-panel-paypal input[type="radio"], .dnt-tabs-panel-co input[type="radio"]' ).removeAttr( 'disabled' );
							}
						} );
					});
				})( jQuery );
			</script>
			<div class='dnt-settings-donate'>
				<p>
					<label>
						<?php _e( 'Title:', 'donate-button' ); ?>
						<input type='text' <?php echo $this->get_field_id( 'dnt_widget_title' ); ?> name="<?php echo $this->get_field_name( 'dnt_widget_title' ); ?>" value="<?php echo $instance['dnt_widget_title']; ?>" class='dnt-widget-title' />
					</label>
					<label>
						<input class='dnt_checkbox_donate' type='checkbox' name="<?php echo $this->get_field_name( 'dnt_widget_button_system' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_donate' ); ?>" <?php if ( 'donate' == $instance['dnt_widget_button_system'] ) echo "checked='checked'"; ?> value='donate' /> <?php _e( 'One button for both systems', 'donate-button' ); ?>
					</label>
				</p>
				<p>
					<ul class='category-tabs hide-if-no-js'>
						<li class='tabs'><a id='dnt_paypal_widget_tab'><?php _e( 'PayPal', 'donate-button' ); ?></a></li>
						<li><a id='dnt_co_widget_tab'><?php _e( '2CO', 'donate-button' ); ?></a></li>
					</ul>
					<ul class='category-tabs hide-if-js'><li class='tabs'><?php _e( 'PayPal', 'donate-button' ); ?></li></ul>
					<div class='dnt-tabs-panel-paypal'>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" <?php if ( 'small' == $instance['dnt_widget_button_options_paypal'] ) echo "checked='checked'"; elseif ( 'donate' == $instance['dnt_widget_button_system'] ) echo "disabled='disabled'"; ?> value='small' /> <?php _e( 'Small button', 'donate-button' ); ?>
						</label>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" <?php if ( 'credits' == $instance['dnt_widget_button_options_paypal'] ) echo "checked='checked'"; else if ( 'donate' == $instance['dnt_widget_button_system'] ) echo "disabled='disabled'"; ?> value='credits' /> <?php _e( 'Credit cards button', 'donate-button' ); ?>
						</label>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" <?php if ( 'default' == $instance['dnt_widget_button_options_paypal'] ) echo "checked='checked'"; else if ( 'donate' == $instance['dnt_widget_button_system'] ) echo "disabled='disabled'"; ?> value='default' /> <?php _e( 'Default button', 'donate-button' ); ?>
						</label>
						<?php if ( ! empty( $dnt_options['paypal_options']['img'] ) ) { ?>
							<label>
								<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" <?php if ( 'custom' == $instance['dnt_widget_button_options_paypal'] ) echo "checked='checked'"; else if ( 'donate' == $instance['dnt_widget_button_system'] ) echo "disabled='disabled'"; ?> value='custom' /> <?php _e( 'Custom button', 'donate-button' ); ?>
							</label>
						<?php } ?>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" <?php if ( 'hide' == $instance['dnt_widget_button_options_paypal'] ) echo "checked='checked'"; else if ( 'donate' == $instance['dnt_widget_button_system'] ) echo "disabled='disabled'"; ?> value='hide' /> <?php _e( "Don't show", 'donate-button' ); ?>
						</label>
					</div>
					<ul class='category-tabs hide-if-js'><li class='tabs'><?php _e( '2CO', 'donate-button' ); ?></li></ul>
					<div class='dnt-tabs-panel-co hide-if-js'>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" <?php if ( 'small' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='small' /> <?php _e( 'Small button', 'donate-button' ); ?>
						</label>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" <?php if ( 'credits' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='credits' /> <?php _e( 'Credit cards button', 'donate-button' ); ?>
						</label>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" <?php if ( 'default' == $instance['dnt_widget_button_options_co'] && 'donate' != $instance['dnt_widget_button_system'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='default' /> <?php _e( 'Default button', 'donate-button' ); ?>
						</label>
						<?php if ( ! empty( $dnt_options['co_options']['img'] ) ) { ?>
							<label>
								<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" <?php if ( 'custom' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='custom' /> <?php _e( 'Custom button', 'donate-button' ); ?>
							</label>
						<?php } ?>
						<label>
							<input type='radio' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" <?php if ( 'hide' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='hide' /> <?php _e( "Don't show", 'donate-button' ); ?>
						</label>
					</div>
				</p>
			</div>
		<?php }

		/* Save Widget form values */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['dnt_widget_title'] = isset( $new_instance['dnt_widget_title'] ) ? sanitize_text_field( $new_instance['dnt_widget_title'] ) : '';
			$instance['dnt_widget_button_system'] = isset( $new_instance['dnt_widget_button_system'] ) ? $new_instance['dnt_widget_button_system'] : '';
			$instance['dnt_widget_button_options_co'] = isset( $new_instance['dnt_widget_button_options_co'] ) ? $new_instance['dnt_widget_button_options_co'] : 'default';
			$instance['dnt_widget_button_options_paypal'] = isset( $new_instance['dnt_widget_button_options_paypal'] ) ? $new_instance['dnt_widget_button_options_paypal'] : 'default';

			if ( 'donate' == $instance["dnt_widget_button_system"] ) {
				$instance['dnt_widget_button_options_paypal'] = 'default';
				$instance['dnt_widget_button_options_co'] = 'default';
			}
			return $instance;
		}
	}
}

/* Save custom images */
if ( ! function_exists ( 'dnt_save_custom_images' ) ) {
	function dnt_save_custom_images( $payment ) {
		global $dnt_error, $dnt_options;
		$uploaddir		=	WP_CONTENT_DIR . "/donate-uploads/";
		$max_width		=	170;
		$max_height		=	70;
		$min_width		=	16;
		$min_height		=	16;
		$dnt_mime_types	=	array(
			'png'  => 'image/png',
			'jpe'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg'  => 'image/jpeg',
			'gif'  => 'image/gif',
			'bmp'  => 'image/bmp',
			'ico'  => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif'  => 'image/tiff',
			'svg'  => 'image/svg+xml',
			'svgz' => 'image/svg+xml'
		);
		if ( ! file_exists( $uploaddir ) ) {
			/*Create dir with absolute path */
			@mkdir( $uploaddir, 0755 );
		}

		if ( isset ( $_POST['dnt_button_custom_choice_' . $payment ] ) && 'local' == $_POST['dnt_button_custom_choice_' . $payment ] ) {
			/* For custom local upload */
			if ( is_uploaded_file( $_FILES['dnt_custom_local_' . $payment ]['tmp_name'] ) ) {
				$getimagesize = getimagesize( $_FILES['dnt_custom_local_' . $payment ]['tmp_name'] );
				/* If uploaded file not image */
				if ( in_array( $getimagesize['mime'], $dnt_mime_types ) ) {
					$current_image_width = $getimagesize[0];
					$current_image_height = $getimagesize[1];
					if ( $current_image_width <= $max_width && $current_image_height <= $max_height && $current_image_width >= $min_width && $current_image_height >= $min_height ) {
						/* File name */
						${"uploadfile_$payment"} = "id-" . time() . "-" . sanitize_file_name( basename( $_FILES['dnt_custom_local_' . $payment ]['name'] ) );
						${"source_$payment"} = $uploaddir . ${"uploadfile_$payment"};
						/* Copy file from temp to needed dir */
						if ( copy( $_FILES['dnt_custom_local_' . $payment]['tmp_name'], ${"source_$payment"} ) ) {
							/* Excerpt local dir */
							$uploaddir = substr( $uploaddir, strlen( ABSPATH . 'wp-content' ) );
							$dnt_options['path'] = $uploaddir;
							$dnt_options[ $payment . '_options']['item_source_' . $payment][] = ${"uploadfile_$payment"};
							if ( empty( $dnt_options[ $payment . '_options']['img'] ) ) {
								$dnt_options[ $payment . '_options']['img'] = content_url() . $dnt_options['path'] . ${"uploadfile_$payment"};
							}

							update_option( 'dnt_options', $dnt_options );
						} else {
							$dnt_error['upload_error'] = __( 'Unable to move the file', 'donate-button' );
						}
					} else {
						if ( $current_image_width < $min_width || $current_image_height < $min_height ) {
							$dnt_error[ $payment . '_upload'] = __( 'Uploaded file smaller than 16x16', 'donate-button' );
						} else {
							$dnt_error[ $payment . '_upload'] = __( 'Uploaded file bigger then 170x70', 'donate-button' );
						}
					}
				} else {
					$dnt_error['mime_type'] = __( 'You can upload only image files', 'donate-button' ) . '(.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)';
				}
			}
		} elseif ( isset( $_POST['dnt_button_custom_choice_' . $payment ] ) && 'url' == $_POST['dnt_button_custom_choice_' . $payment ] ) {
			/* URL upload */
			$dnt_headers = @get_headers( $_POST['dnt_custom_url_' . $payment] );
			if ( null != $_POST['dnt_custom_url_' . $payment] && preg_match( "#^https?:(.*).(png|jpg|jpeg|gif|bmp|ico|tif|tiff|jpe|svg|svgz)$#i", $_POST['dnt_custom_url_' . $payment] ) && preg_match( "|200|", $dnt_headers[0] ) ) {
				if ( is_callable( 'curl_init' ) ) {
					/* Url from form element value */
					${"url_$payment"} = curl_init( $_POST['dnt_custom_url_' . $payment ] );
					if ( isset( $_POST['dnt_custom_url_' . $payment] ) ) {
						$getimagesize = getimagesize( $_POST['dnt_custom_url_' . $payment ] );
						if ( in_array( $getimagesize['mime'], $dnt_mime_types ) ) {
							$current_image_width	=	$getimagesize[0];
							$current_image_height	=	$getimagesize[1];
							if ( $current_image_width <= $max_width && $current_image_height <= $max_height && $current_image_width >= $min_width && $current_image_height >= $min_height ) {
								${"url_path_$payment"} = "image id-" . time();
								${"source_$payment"} = $uploaddir . ${"url_path_$payment"};
								/* Path where download with write permissions */
								${"url_write_path_$payment"} = fopen( ${"source_$payment"}, 'w' );
								/* Write file to directory */
								curl_setopt( ${"url_$payment"}, CURLOPT_FILE, ${"url_write_path_$payment"} );
								curl_exec( ${"url_$payment"} );
								curl_close( ${"url_$payment"} );
								fclose( ${"url_write_path_$payment"} );
								$uploaddir = substr( $uploaddir, strlen( ABSPATH . 'wp-content' ) );
								$dnt_options[ $payment . '_options']['item_source_' . $payment ][] = ${"url_path_$payment"};
								$dnt_options['path'] = $uploaddir;
								if ( empty( $dnt_options[ $payment . '_options']['img'] ) ) {
									$dnt_options[ $payment . '_options']['img'] = content_url() . $dnt_options['path'] . ${"url_path_$payment"};
								}

								update_option( 'dnt_options', $dnt_options );
							} else {
								if ( $current_image_width < $min_width || $current_image_height < $min_height ) {
									$dnt_error[ $payment . '_upload'] = __( 'Uploaded file smaller than 16x16', 'donate-button' );
								} else {
									$dnt_error[ $payment . '_upload'] = __( 'Uploaded file bigger then 170x70', 'donate-button' );
								}
							}
						} else {
							$dnt_error['mime_type'] = __( 'You can upload only image files', 'donate-button' ) . '(.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)';
						}
					}
				} else {
					$dnt_error['curl'] = __( 'Please enable cURL on server' , 'donate-button' );
				}
			} else {
				$dnt_error['mime_type'] = __( 'Please enter the correct address and image format', 'donate-button' );
			}
		}
	}
}

/* Display all custom buttons */
if ( ! function_exists ( 'dnt_display_custom_buttons' ) ) {
	function dnt_display_custom_buttons( $payment ) {
		global $dnt_options; ?>
		<?php if ( ! empty( $dnt_options[ $payment . '_options']['item_source_' . $payment ] ) ) { ?>
			<tr>
				<th scope="row"><?php _e( 'Choose Custom Button Image', 'donate-button' ); ?></td>
				<td>
					<fieldset>
						<?php foreach ( $dnt_options[ $payment . '_options']['item_source_' . $payment ]  as $key => $value ) {
							$current_image = content_url() . $dnt_options['path'] . $dnt_options[ $payment . '_options']['item_source_' . $payment][ $key ]; ?>
							<label>
								<input type='radio' name="dnt_check_image_<?php echo $payment ?>" <?php if ( $dnt_options[$payment . '_options']['img'] == $current_image ) echo "checked='checked'"; ?> value="<?php echo $current_image; ?>" />
								<img src="<?php echo $current_image; ?>" alt='' />
							</label><br>
						<?php } ?>
					</fieldset>
				</td>
			</tr>
		<?php } ?>
		<tr>
			<th scope="row">
				<?php _e( 'Custom Button Image', 'donate-button' ); ?>
				<div class="bws_help_box dashicons dashicons-editor-help">
					<div class="bws_hidden_help_text" style="min-width: 180px;">
						<?php printf( __( 'The size of the image you upload must be no more than %s and no smaller than %s.', 'donate-button' ), '170x70', '16x16' ); ?><br>
						<?php _e( 'You can upload only image files', 'donate-button' ); ?> (.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)
					</div>
				</div>
			</th>
			<td>
				<fieldset>
					<div class='dnt_local_box'>
						<label>
							<input type='radio' value='local' name='dnt_button_custom_choice_<?php echo $payment; ?>' />
							<?php _e( 'Upload an image', 'donate-button' ); ?>
						</label><br />
						<input type='file' name='dnt_custom_local_<?php echo $payment; ?>' value='browse' />
					</div>
					<div class='dnt_url_box'>
						<label class='dnt_checker_url'>
							<input type='radio' value='url' name='dnt_button_custom_choice_<?php echo $payment; ?>' />
							<?php _e( 'Paste image URL', 'donate-button' ); ?>
						</label><br />
						<input type='text' name='dnt_custom_url_<?php echo $payment; ?>' />
					</div>
				</fieldset>
			</td>
		</tr>
	<?php }
}

/* Display output block */
if ( ! function_exists ( 'dnt_display_output_block' ) ) {
	function dnt_display_output_block() {
		global $dnt_options; ?>
		<div class='dnt_display_box'>
			<h2><?php _e( 'Output', 'donate-button' ); ?></h2>
			<div class='dnt-inside-block'>
				<?php _e( 'One button for both systems', 'donate-button' ); ?>
				<div class='dnt-img-box'><img src='<?php echo plugins_url( 'images/donate-button.png', __FILE__ ); ?>' alt='donate-default' /></div>
			</div>
			<?php foreach ( array( 'paypal' => __( 'PayPal', 'donate-button' ), 'co' => __( '2CO', 'donate-button' ) ) as $payment => $payment_name ) {
				foreach ( array( 'default' => __( 'Default button', 'donate-button' ), 'default-small' => __( 'Small button', 'donate-button' ), 'default-credits' => __( 'Credit cards button', 'donate-button' ) ) as $type => $type_name ) { ?>
					<div class='dnt-inside-block dnt_output_block_<?php echo $payment; ?>'>
						<?php echo $payment_name; ?> - <?php echo $type_name; ?>
						<div class='dnt-img-box'><img src='<?php echo plugins_url( 'images/' . $payment . '-' . str_replace( 'default-', '', $type ) . '.png', __FILE__ ); ?>' alt='<?php echo $payment . '-' . $type; ?>' /></div>
					</div>
				<?php }
				if ( ! empty( $dnt_options[ $payment . '_options']['img'] ) ) { ?>
					<div class='dnt-inside-block dnt_output_block_<?php echo $payment; ?>'>
						<?php echo $payment_name; ?> - <?php _e( 'Custom button', 'donate-button' ); ?>
						<div class='dnt-img-box'><img src='<?php echo $dnt_options[ $payment . '_options']['img']; ?>' alt='<?php echo $payment . '-' . $type; ?>' /></div>
					</div>
				<?php }
			} ?>
		</div>
	<?php }
}

/* Add content for donate Menu */
if ( ! function_exists ( 'dnt_admin_settings' ) ) {
	function dnt_admin_settings() {
		global $dnt_error, $dnt_options, $dnt_plugin_info, $dnt_option_defaults;

		$message = $dnt_tab_active_paypal = $dnt_tab_active_co = '';
		$plugin_basename = plugin_basename( __FILE__ );

		/* Creating a masive with possible currencies */
		$paypal_currency = array(
			array( 'AUD', 'Australian Dollar' ),
			array( 'BRL', 'Brazilian Real' ),
			array( 'CAD', 'Canadian Dollar' ),
			array( 'CHF', 'Swiss Franc' ),
			array( 'CZK', 'Czech Koruna' ),
			array( 'DKK', 'Danish Krone' ),
			array( 'EUR', 'Euro' ),
			array( 'GBP', 'British Pound' ),
			array( 'HKD', 'Hong Kong Dollar' ),
			array( 'HUF', 'Hungarian Forint' ),
			array( 'ILS', 'Israeli New Shekel' ),
			array( 'JPY', 'Japanese Yen' ),
			array( 'MXN', 'Mexican Peso' ),
			array( 'MYR', 'Malaysian Ringgit' ),
			array( 'NOK', 'Norwegian Krone' ),
			array( 'NZD', 'New Zealand Dollar' ),
			array( 'PHP', 'Philippine Peso' ),
			array( 'PLN', 'Polish Zloty' ),
			array( 'RUB', 'Russian Ruble' ),
			array( 'SEK', 'Swedish Krona' ),
			array( 'SGD', 'Singapore Dollar' ),
			array( 'THB', 'Thai Baht' ),
			array( 'TWD', 'New Taiwan Dollar' ),
			array( 'USD', 'U.S. Dollar' ),
		);

		/* PayPal save options */
		if ( isset( $_POST['dnt_form_submit'] ) && check_admin_referer( $plugin_basename, 'dnt_check_field' ) ) {
			if ( ! empty( $_POST['dnt_paypal_account'] ) ) {
				if ( ! is_email( $_POST['dnt_paypal_account'] ) ) {
					$dnt_error['account_paypal'] = sprintf( __( 'Email validation error, email must be written like %s', 'donate-button' ), 'example@gmail.com' );
				} else {
					$dnt_options['paypal_options']['paypal_account'] = $_POST['dnt_paypal_account'];
				}
			} else {
				$dnt_options['paypal_options']['paypal_account'] = '';
				if ( '1' == $_POST['dnt_tab_paypal'] ) {
					$dnt_error['account_paypal'] = __( 'Account name is required field, please write your account name in PayPal tab', 'donate-button' );
				}
			}

			if ( isset( $_POST['dnt_paypal_amount'] ) ) {
				$dnt_options['paypal_options']['paypal_amount'] = number_format( floatval( $_POST['dnt_paypal_amount'] ), 2, ".", '' );
				if ( 0 > $dnt_options['paypal_options']['paypal_amount'] ) {
					$dnt_error['paypal_amount'] = __( 'The amount must be a positive number.', 'donate-button' );
				}
			} else {
				$dnt_options['paypal_options']['paypal_amount'] = '1.00';
			}

			if ( ! empty( $_POST['dnt_paypal_currency'] ) && preg_match( "/^[A-Z]{3}$/", $_POST['dnt_paypal_currency'] ) ) {
				$dnt_options['paypal_options']['paypal_currency'] = esc_attr( $_POST['dnt_paypal_currency'] );
			}

			if ( ! empty( $_POST['dnt_paypal_purpose'] ) ) {
				$dnt_options['paypal_options']['paypal_purpose'] = stripslashes( esc_html( $_POST['dnt_paypal_purpose'] ) );
			} else {
				$dnt_options['paypal_options']['paypal_purpose'] = '';
			}

			if ( ! empty( $_POST['dnt_check_image_paypal'] ) ) {
				$dnt_options['paypal_options']['img'] = $_POST['dnt_check_image_paypal'];
			}

			dnt_save_custom_images( 'paypal' );

			/* 2CO save options */
			if ( ! empty( $_POST['dnt_co_account'] ) ) {
				if ( ! preg_match( '/^\d+$/' ,$_POST['dnt_co_account'] ) ) {
					$dnt_error['type_account'] = __( '2CO Account error: You type string, numeric expected.', 'donate-button' );
				} else {
					$dnt_options['co_options']['co_account'] = $_POST['dnt_co_account'];
				}
			} else {
				$dnt_options['co_options']['co_account'] = '';
				if ( '1' == $_POST['dnt_tab_co'] ) {
					$dnt_error['account_co'] = __( '2CO account ID is required field, please write your account name in 2CO tab', 'donate-button' );
				}
			}

			if ( ! empty( $_POST['dnt_quantity_donate'] ) ) {
				if ( ! preg_match( '/^\d+$/' ,$_POST['dnt_quantity_donate'] ) ) {
					$dnt_error['type_quantity'] = __( '2CO Quantity error: You type string, numeric expected.', 'donate-button' );
				} else {
					$dnt_options['co_options']['co_quantity'] = $_POST['dnt_quantity_donate'];
				}
			} else {
				$dnt_options['co_options']['co_quantity'] = '';
				if ( '1' == $_POST['dnt_tab_co'] ) {
					$dnt_error['co_quantity'] = __( 'Quantity is required field, please write quantity of products in 2CO tab', 'donate-button' );
				}
			}

			if ( ! empty( $_POST['dnt_product_id'] ) ) {
				if ( ! preg_match( '/^\d+$/' ,$_POST['dnt_product_id'] ) ) {
					$dnt_error['type_product'] = __( '2CO Product ID error: You type string, numeric expected.', 'donate-button' );
				} else {
					$dnt_options['co_options']['product_id'] = $_POST['dnt_product_id'];
				}
			} else {
				$dnt_options['co_options']['product_id'] = '';
				if ( '1' == $_POST['dnt_tab_co'] ) {
					$dnt_error['product_id'] = __( 'Product ID is required field, please write product ID in 2CO tab', 'donate-button' );
				}
			}

			if ( ! empty( $_POST['dnt_check_image_co'] ) ) {
				$dnt_options['co_options']['img'] = $_POST['dnt_check_image_co'];
			}

			dnt_save_custom_images( 'co' );

			/* Display active payment tab */
			/* If value changed add classes for active tab */
			if ( '1' == $_POST['dnt_tab_co'] ) {
				$dnt_tab_active_co = 'nav-tab-active';
			} elseif ( '1' == $_POST['dnt_tab_paypal'] ) {
				$dnt_tab_active_paypal = 'nav-tab-active';
			}

			if ( empty( $dnt_error ) ) {
				$message = __( 'Changes saved', 'donate-button' );
				update_option( 'dnt_options', $dnt_options );
			}
		}

		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$dnt_options = $dnt_option_defaults;
			update_option( 'dnt_options', $dnt_options );
			$message = __( 'All plugin settings were restored.', 'donate-button' );
		} ?>
		<div class="wrap dnt_wrap">
			<h1><?php _e( 'Donate Settings', 'donate-button' ); ?></h1>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab<?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>"  href="admin.php?page=donate.php"><?php _e( 'Settings', 'donate-button' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'custom_code' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=donate.php&amp;action=custom_code"><?php _e( 'Custom code', 'donate-button' ); ?></a>
			</h2>
			<?php bws_show_settings_notice(); ?>
			<?php if ( ! empty( $dnt_error ) ) { ?>
				<div class="error below-h2">
					<?php foreach ( $dnt_error as $error ) { ?>
						<p><strong><?php echo $error; ?></strong></p>
					<?php } ?>
				</div>
			<?php }
			if ( ! empty( $message ) ) { ?>
				<div class="updated fade below-h2"><p><strong><?php echo $message; ?></strong></p></div>
			<?php }
			if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else { ?>
					<form class="bws_form dnt-form-table" enctype='multipart/form-data' method='post' action='admin.php?page=donate.php'>
						<br/>
						<div><?php printf(
							__( "If you would like to add the button to your page or post, please use %s button", 'donate-button' ),
							'<span class="bwsicons bwsicons-shortcode"></span>' ); ?>
							<div class="bws_help_box bws_help_box_right dashicons dashicons-editor-help">
								<div class="bws_hidden_help_text" style="min-width: 180px;">
									<?php printf(
										__( "You can add the button to your page or post by clicking on %s button in the content edit block using the Visual mode. If the button isn't displayed, please use the shortcode %s, where you can specify payment one of %s, and type one of %s", 'donate-button' ),
										'<code><span class="bwsicons bwsicons-shortcode"></span></code>',
										'<code>[donate payment=* type=*]</code>',
										'`paypal`, `co`',
										'`default-small`, `default-credits`, `custom`'
									); ?>
								</div>
							</div>
							<br>
							<?php _e( 'You can also add a widget', 'donate-button' ); ?>: <strong>Donate Widget</strong>
						</div>
						<p><strong><?php _e( 'Please fill in the required fields for each payment system', 'donate-button' ); ?></strong></p>
						<h2 class='nav-tab-wrapper hide-if-no-js dnt_tabs'>
							<a class='nav-tab <?php echo $dnt_tab_active_paypal; ?> dnt_paypal_text'><?php _e( 'PayPal', 'donate-button' ); ?></a>
							<a class='nav-tab <?php echo $dnt_tab_active_co; ?> dnt_co_text'><?php _e( '2CO', 'donate-button' ); ?></a>
						</h2>
						<!--PayPal-->
						<h2 class="hide-if-js"><?php _e( 'PayPal', 'donate-button' ); ?></h2>
						<div id='dnt_shortcode_options_paypal'>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Your PayPal Account Email Address', 'donate-button' ); ?></td>
									<td>
										<input type='text' name='dnt_paypal_account' id='dnt_paypal_account' value="<?php if ( null != $dnt_options['paypal_options']['paypal_account'] ) echo $dnt_options['paypal_options']['paypal_account']; ?>" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Your Donation Purpose', 'donate-button' ); ?></td>
									<td>
										<input type='text' id='dnt_paypal_purpose' name='dnt_paypal_purpose' value="<?php if ( null != $dnt_options['paypal_options']['paypal_purpose'] ) echo $dnt_options['paypal_options']['paypal_purpose']; ?>" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Amount', 'donate-button' ); ?></td>
									<td>
										<input type='number' min="0" step="0.01" id='dnt_paypal_amount' name='dnt_paypal_amount' value="<?php if ( null != $dnt_options['paypal_options']['paypal_amount'] ) echo $dnt_options['paypal_options']['paypal_amount']; ?>" />
										<div class="bws_info">
											<?php printf( __( 'Set "%d" to allow users to set donation amount', 'donate-button' ), 0 ); ?>
										</div>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Currency', 'donate-button' ); ?></td>
									<td>
										<select name="dnt_paypal_currency">
											<?php foreach ( $paypal_currency as $currency ) {
												echo '<option value="' . $currency[0] . '"';
												selected( $currency[0], $dnt_options['paypal_options']['paypal_currency'] );
												echo '">' . $currency[0] . ' - ' . $currency[1] . '</option>';
											} ?>
										</select><br/>
									</td>
								</tr>
								<?php dnt_display_custom_buttons( 'paypal' ); ?>
							</table>
							<input type='hidden' id='dnt_tab_paypal' name='dnt_tab_paypal' value='1' />
						</div>
						<!--2CO-->
						<h2 class="hide-if-js"><?php _e( '2CO', 'donate-button' ); ?></h2>
						<div id='dnt_shortcode_options_co'>
							<table class="form-table">
								<tr>
									<th scope="row"><?php _e( 'Your 2CO Account ID', 'donate-button' ); ?></td>
									<td>
										<input type='number' name='dnt_co_account' value="<?php if ( null != $dnt_options['co_options']['co_account'] ) echo $dnt_options['co_options']['co_account']; ?>" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Quantity', 'donate-button' ); ?></td>
									<td>
										<input type='number' name='dnt_quantity_donate' value="<?php if ( null != $dnt_options['co_options']['co_quantity'] ) echo $dnt_options['co_options']['co_quantity']; ?>" />
									</td>
								</tr>
								<tr>
									<th scope="row"><?php _e( 'Product ID', 'donate-button' ); ?></td>
									<td>
										<input type='number' name='dnt_product_id' value="<?php if ( null != $dnt_options['co_options']['product_id'] ) echo $dnt_options['co_options']['product_id']; ?>" />
									</td>
								</tr>
								<?php dnt_display_custom_buttons( 'co' ); ?>
							</table>
							<input type='hidden' id='dnt_tab_co' name='dnt_tab_co' value='0' />
						</div>
						<p class="submit">
							<input id="bws-submit-button" type='submit' name='dnt_form_submit' value='<?php _e( "Save changes", 'donate-button' ); ?>' class='button-primary' />
							<?php wp_nonce_field( $plugin_basename, 'dnt_check_field' ) ?>
						</p>
					</form>
					<div class='dnt-output-block'>
						<?php dnt_display_output_block(); ?>
					</div>
					<div class="clear"></div>
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			} else {
				bws_custom_code_tab();
			}
			bws_plugin_reviews_block( $dnt_plugin_info['Name'], 'donate-button' ); ?>
		</div>
	<?php }
}

/* Add shortcode */
if ( ! function_exists ( 'dnt_user_shortcode' ) ) {
	function dnt_user_shortcode( $atts ) {
		global $dnt_options;
		extract( shortcode_atts( array(
			'type'    => 'default',
			'payment' => ''
		), $atts ) );

		/* Display buttons what we need */
		if ( isset( $atts['payment'] ) && 'paypal' == $atts['payment'] ) {
			$dnt_shortcode_return = "<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window'>";
				if ( 'default' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt-paypal-button' src=" . plugins_url( 'images/paypal-default.png', __FILE__ ) ." alt='paypal button' />";
				} elseif ( 'default-small' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt-paypal-button' src=" . plugins_url( 'images/paypal-small.png', __FILE__ ) . " alt='paypal button' />";
				} elseif ( 'default-credits' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt-paypal-button' src=" . plugins_url( 'images/paypal-credits.png', __FILE__ ) . " alt='paypal button' />";
				} elseif ( 'custom' == $atts['type'] && ! empty( $dnt_options['paypal_options']['img'] ) ) {
					$dnt_shortcode_return .= '<input type="image" class="dnt-paypal-button" src="' . $dnt_options['paypal_options']['img'] . '"alt="paypal button" />';
				}
				ob_start();
				dnt_draw_paypal_form();
				$out_paypal_form = ob_get_contents();
				ob_end_clean();
				$dnt_shortcode_return .= $out_paypal_form;
				$dnt_shortcode_return .= "</form>";
		} elseif ( isset( $atts['payment'] ) && 'co' == $atts['payment'] ) {
			$dnt_shortcode_return = "<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window'>";
				if ( 'default' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt-co-button' src=" . plugins_url( 'images/co-default.png', __FILE__ ) . " alt='2co button' />";
				} elseif ( 'default-small' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt-co-button' src=" . plugins_url( 'images/co-small.png', __FILE__ ) . " alt='2co button' />";
				} elseif ( 'default-credits' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt-co-button' src=" . plugins_url( 'images/co-credits.png', __FILE__ ) . " alt='2co button' />";
				} elseif ( 'custom' == $atts['type'] && ! empty( $dnt_options['co_options']['img'] ) ) {
					$dnt_shortcode_return .= '<input type="image" class="dnt-co-button" src="' . $dnt_options['co_options']['img'] . '"alt="2co button" />';
				}
				ob_start();
				dnt_draw_co_form();
				$out_co_form = ob_get_contents();
				ob_end_clean();
				$dnt_shortcode_return .= $out_co_form;
			$dnt_shortcode_return .= '</form>';
		} else {
			$dnt_shortcode_return = "<div class='dnt-button'><img src=" . plugins_url( 'images/donate-button.png', __FILE__ ) . " alt='donate button' />";
			ob_start();
			dnt_options_box();
			$out_options_box = ob_get_contents();
			ob_end_clean();
			$dnt_shortcode_return .= $out_options_box;
			$dnt_shortcode_return .= "</div>";
		}

		if ( ! wp_script_is( 'dnt_script', 'registered' ) ) {
			wp_register_script( 'dnt_script', plugins_url( 'js/script.js', __FILE__ ) , array( 'jquery' ), false, true );
		}

		return $dnt_shortcode_return;
	}
}

/* add shortcode content  */
if ( ! function_exists( 'dnt_shortcode_button_content' ) ) {
	function dnt_shortcode_button_content( $content ) {
		global $dnt_options; ?>
		<div id="dnt" style="display:none;">
			<fieldset class='dnt-settings-donate'>
				<label>
					<input id="dnt_button_system" type='checkbox' name="dnt_button_system" checked='checked' value="donate" /> <?php _e( 'One button for both systems', 'donate-button' ); ?>
				</label>
				<br>
				<label>
					<select name="dnt_paypal" disabled="disabled">
						<option value='default-small'><?php _e( 'Small button', 'donate-button' ); ?></option>
						<option value='default-credits'><?php _e( 'Credit cards button', 'donate-button' ); ?></option>
						<option value='default' selected="selected"><?php _e( 'Default button', 'donate-button' ); ?></option>
						<?php if ( ! empty( $dnt_options['paypal_options']['img'] ) ) { ?>
							<option value='custom'><?php _e( 'Custom button', 'donate-button' ); ?></option>
						<?php } ?>
						<option value='hide'><?php _e( "Don't show", 'donate-button' ); ?></option>
					</select>
					<?php _e( 'PayPal', 'donate-button' ); ?>
				</label>
				<br>
				<label>
					<select name="dnt_co" disabled="disabled">
						<option value='default-small'><?php _e( 'Small button', 'donate-button' ); ?></option>
						<option value='default-credits'><?php _e( 'Credit cards button', 'donate-button' ); ?></option>
						<option value='default' selected="selected"><?php _e( 'Default button', 'donate-button' ); ?></option>
						<?php if ( ! empty( $dnt_options['co_options']['img'] ) ) { ?>
							<option value='custom'><?php _e( 'Custom button', 'donate-button' ); ?></option>
						<?php } ?>
						<option value='hide'><?php _e( "Don't show", 'donate-button' ); ?></option>
					</select>
					<?php _e( '2CO', 'donate-button' ); ?>
				</label>
			</fieldset>
			<input class="bws_default_shortcode" type="hidden" name="default" value="[donate]" />
			<script type="text/javascript">
				function dnt_shortcode_init() {
					(function( $ ) {
						$( '.mce-reset #dnt_button_system, .mce-reset select[name="dnt_co"], .mce-reset select[name="dnt_paypal"]' ).on( 'change', function() {
							var shortcode = '';

							if ( $( '.mce-reset #dnt_button_system' ).is( ':checked' ) ) {
								shortcode = '[donate]';
								$( '.mce-reset select[name="dnt_co"], .mce-reset select[name="dnt_paypal"]' ).val( 'default' ).attr( 'disabled', 'disabled' );
							} else {
								$( '.mce-reset select[name="dnt_co"], .mce-reset select[name="dnt_paypal"]' ).removeAttr( 'disabled' );
								var co = $( '.mce-reset select[name="dnt_co"]' ).val();
								var paypal = $( '.mce-reset select[name="dnt_paypal"]' ).val();
								if ( paypal != 'hide' ) {
									shortcode = '[donate payment=paypal type=' + paypal + ']';
								}
								if ( co != 'hide' ) {
									shortcode = shortcode + ' [donate payment=co type=' + co + ']';
								}
							}

							$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
						} );
					})( jQuery );
				}
			</script>
			<div class="clear"></div>
		</div>
	<?php }
}

/* Additional links on the plugin page */
if ( ! function_exists ( 'dnt_register_plugin_links' ) ) {
	function dnt_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=donate.php">' . __( 'Settings', 'donate-button' ) . '</a>';
			}
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538699" target="_blank">' . __( 'FAQ', 'donate-button' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'donate-button' ) . '</a>';
		}
		return $links;
	}
}

/* Adds "Settings" link to the plugin action page */
if ( ! function_exists ( 'dnt_plugin_action_links' ) ) {
	function dnt_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row */
			static $this_plugin;
			if ( ! $this_plugin ) {
				$this_plugin = plugin_basename( __FILE__ );
			}
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=donate.php">' . __( 'Settings', 'donate-button' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists ( 'dnt_admin_notices' ) ) {
	function dnt_admin_notices() {
		global $hook_suffix, $dnt_plugin_info;
		if ( 'plugins.php' == $hook_suffix ) {
			bws_plugin_banner_to_settings( $dnt_plugin_info, 'dnt_options', 'donate-button', 'admin.php?page=donate.php' );
		}
		if ( isset( $_GET['page'] ) && 'donate.php' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $dnt_plugin_info, 'dnt_options', 'donate-button' );
		}
	}
}

/* add help tab  */
if ( ! function_exists( 'dnt_add_tabs' ) ) {
	function dnt_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id'      => 'dnt',
			'section' => '200538699'
		);
		bws_help_tab( $screen, $args );
	}
}

/* Delete options db ( Uninstall ) */
if ( ! function_exists ( 'dnt_delete_options' ) ) {
	function dnt_delete_options() {
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'dnt_options' );
				delete_option( 'widget_donate_widget' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'dnt_options' );
			delete_option( 'widget_donate_widget' );
		}

		$del_dir = WP_CONTENT_DIR . "/donate-uploads/";
		/* Get all file names */
		$files = glob( WP_CONTENT_DIR . "/donate-uploads/*" );
		/*iterate files*/
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				/* Delete file */
				@unlink( $file );
			}
		}
		@rmdir( $del_dir );

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'dnt_plugin_activate' );
add_action( 'admin_menu', 'dnt_add_admin_menu' );
add_action( 'init', 'dnt_init' );
add_action( 'admin_init', 'dnt_admin_init' );
add_action( 'plugins_loaded', 'dnt_plugins_loaded' );
add_action( 'admin_enqueue_scripts', 'dnt_admin_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', 'dnt_wp_enqueue_scripts' );
add_action( 'wp_footer', 'dnt_wp_footer' );
add_filter( 'pgntn_callback', 'dnt_pagination_callback' );
add_action( 'widgets_init', 'dnt_register_widget' );
add_shortcode( 'donate', 'dnt_user_shortcode' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'dnt_shortcode_button_content' );
add_action( 'admin_notices', 'dnt_admin_notices' );
/* Links in admin menu */
add_filter( 'plugin_row_meta', 'dnt_register_plugin_links', 10, 2 );
add_filter( 'plugin_action_links', 'dnt_plugin_action_links', 10, 2 );