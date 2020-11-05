<?php
/*
Plugin Name: Donate by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/donate/
Description: Add PayPal and 2CO donate buttons to receive charity payments.
Author: BestWebSoft
Text Domain: donate-button
Domain Path: /languages
Version: 2.1.7
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
*/

/*
	© Copyright 2020  BestWebSoft  ( https://support.bestwebsoft.com )

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
		$settings = add_menu_page( __( 'Donate Settings', 'donate-button' ), 'Donate', 'manage_options', 'donate.php', 'dnt_settings_page', 'none' );
		add_submenu_page( 'donate.php', __( 'Donate Settings', 'donate-button' ), __( 'Settings', 'donate-button' ), 'manage_options', 'donate.php', 'dnt_settings_page' );
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
        global $dnt_plugin_info, $dnt_options;

        if ( ! get_option( 'dnt_options' ) ) {
            $dnt_option_defaults = dnt_get_options_default();
            add_option( 'dnt_options', $dnt_option_defaults );
        }

        $dnt_options = get_option( 'dnt_options' );

        if ( ! isset( $dnt_options['plugin_option_version'] ) || $dnt_options['plugin_option_version'] != $dnt_plugin_info["Version"] ) {
            dnt_plugin_activate();
            $dnt_option_defaults = dnt_get_options_default();
            $dnt_option_defaults['display_settings_notice'] = 0;
            $dnt_options = array_replace_recursive( $dnt_option_defaults, $dnt_options );
            $dnt_options['plugin_option_version'] = $dnt_plugin_info["Version"];
            update_option( 'dnt_options', $dnt_options );
        }
    }
}

if ( ! function_exists( 'dnt_get_options_default' ) ) {
    function dnt_get_options_default() {
        global $dnt_plugin_info;

        $dnt_option_defaults = array(
            'paypal_options' => array(
                'paypal_display'                => 1,
                'paypal_purpose'                => '',
                'paypal_account'                => '',
                'paypal_amount'                 => '1.00',
                'paypal_currency'               => 'USD',
                'item_source_paypal'            => '',
                'img'                           => '',
                'paypal_image_option'           => 'default',
            ),
            'co_options' => array(
                'co_display'                => 1,
                'co_account'                => 0,
                'co_quantity'               => 1,
                'product_id'                => '',
                'item_source_co'            => '',
                'img'                       => '',
                'co_image_option'           => 'default',
            ),
            'plugin_option_version'   => $dnt_plugin_info["Version"],
            'display_settings_notice' => 1,
            'suggest_feature_banner'  => 1,
        );

        return $dnt_option_defaults;
    }
}
/**
 * Settings page
 */
if ( ! function_exists( 'dnt_settings_page' ) ) {
    function dnt_settings_page() {
        require_once( dirname( __FILE__ ) . '/includes/class-dnt-settings.php' );
        if ( ! class_exists( 'Bws_Settings_Tabs' ) )
            require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
        $page = new Dnt_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
        <div class="wrap">
            <h1 class="dnt-title"><?php _e( 'Donate Settings', 'donate-button' ); ?></h1>
            <?php $page->display_content(); ?>
        </div>
    <?php }
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
        <input type="hidden" name="charset" value="utf-8">
	<?php }
}

/* 2CO API */
if ( ! function_exists( 'dnt_draw_co_form' ) ) {
	function dnt_draw_co_form() {
		$dnt_options = get_option( 'dnt_options', array() ); ?>
		<input type='hidden' name='sid' value="<?php echo $dnt_options['co_options']['co_account']; ?>" />
		<input type='hidden' name='quantity' value="<?php echo $dnt_options['co_options']['co_quantity']; ?>" />
		<input type='hidden' name='product_id' value="<?php echo $dnt_options['co_options']['product_id']; ?>" />
        <input type="hidden" name="charset" value="utf-8">
	<?php }
}

if ( ! function_exists ( 'dnt_wp_enqueue_scripts' ) ) {
	function dnt_wp_enqueue_scripts() {
		wp_enqueue_style( 'dnt_style', plugins_url( 'css/style.css', __FILE__ ) );
	}
}

if ( ! function_exists( 'dnt_pagination_callback' ) ) {
	function dnt_pagination_callback( $content ) {
		$content .= "$( '.dnt-options-box' ).hide();";
		return $content;
	}
}

if ( ! function_exists ( 'dnt_admin_enqueue_scripts' ) ) {
	function dnt_admin_enqueue_scripts() {
		global $hook_suffix;
		wp_enqueue_style( 'dnt_icon', plugins_url( 'css/icon.css', __FILE__ ) );
		if ( 'widgets.php' == $hook_suffix || ( isset( $_GET['page'] ) && 'donate.php' == $_GET['page'] ) ) {
			wp_enqueue_style( 'dnt_style', plugins_url( 'css/style.css', __FILE__ ) );
			if ( isset( $_GET['page'] ) && 'donate.php' == $_GET['page'] ) {
				wp_enqueue_script( 'dnt_admin_script', plugins_url( 'js/admin_script.js', __FILE__ ) , array( 'jquery' ) );wp_enqueue_script( 'dnt_script', plugins_url( 'js/script.js' , __FILE__ ), array( 'jquery' ) );
                wp_enqueue_media();
                wp_localize_script( 'dnt_admin_script', 'dnt_var',
                    array(
                        'nonce'             => wp_create_nonce( 'dnt_ajax_nonce' ),
                        'wp_media_title'    => __( 'Insert Media', 'donate-button' ),
                        'wp_media_button'	=> __( 'Insert', 'donate-button' )
                    )
                );
                bws_plugins_include_codemirror();
                bws_enqueue_settings_scripts();
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
				<form action='https://www.paypal.com/cgi-bin/webscr' data-locale="auto" method='post' target='paypal_window' >
					<input type='image' id='dnt_paypal_button' class='dnt-paypal-button'  src="<?php echo plugins_url( 'images/paypal.jpg', __FILE__ ) ?>" alt='paypal' title='PayPal checkout' />
					<?php dnt_draw_paypal_form(); ?>
                    <input type="hidden" name="charset" value="utf-8">
				</form>
				<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window' >
					<input type='image' id='dnt_co_button' class='dnt-co-button' src="<?php echo plugins_url( 'images/co.jpg', __FILE__ ) ?>" alt='2CO' title='2CO checkout' />
					<?php dnt_draw_co_form(); ?>
                    <input type="hidden" name="charset" value="utf-8">
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
								<?php if ( 'custom' == $instance['dnt_widget_button_options_co'] ) {
                                    /**
                                    * Update
                                    * @deprecated 2.1.7
                                    * @todo Update after 22.07.2021
                                    */
                                    $url = is_int( $dnt_options['co_options']['img'] ) ? wp_get_attachment_url( $dnt_options['co_options']['img'] ) : $dnt_options['co_options']['img'];
                                    /* end todo */
									echo '<input type="image" class="dnt-co-button" src="' . $url . '" alt="custom-button-co" />';
								 } else { ?>
									<input type='image' class='dnt-co-button' src="<?php echo plugins_url( 'images/co-' . $instance['dnt_widget_button_options_co'] . '.png', __FILE__ ); ?>" alt='co-button' />
								<?php }
								dnt_draw_co_form(); ?>
							</form>
						<?php }
						if ( 'donate' != $instance['dnt_widget_button_system'] && in_array( $instance['dnt_widget_button_options_paypal'], array( 'default', 'small', 'custom', 'credits' ) ) ) { ?>
							<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window'>
								<?php if ( 'custom' == $instance['dnt_widget_button_options_paypal'] ) {
                                    /**
                                    * Update
                                    * @deprecated 2.1.7
                                    * @todo Update after 22.07.2021
                                    */
                                    $url = is_int( $dnt_options['paypal_options']['img'] ) ? wp_get_attachment_url( $dnt_options['paypal_options']['img'] ) : $dnt_options['paypal_options']['img'];
                                    /* end todo */
                                    echo '<input type="image" class="dnt-paypal-button" src="' . $url . '" alt="custom-button-paypal" />';
                                } else { ?>
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

				if ( ! wp_script_is( 'dnt_script', 'enqueued' ) ) {
					wp_enqueue_script( 'dnt_script', plugins_url( 'js/script.js', __FILE__ ) , array( 'jquery' ), false, true );
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
                        /**
                         * Update
                         * @deprecated 2.1.7
                         * @todo Update after 22.07.2021
                         */
                        $url = is_int( $dnt_options['paypal_options']['img'] ) ? wp_get_attachment_url( $dnt_options['paypal_options']['img'] ) : $dnt_options['paypal_options']['img'];
                        $dnt_shortcode_return .= '<input type="image" class="dnt-paypal-button" src="' . $url . '"alt="paypal button" />';
                        /* end todo */
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
                        /**
                         * Update
                         * @deprecated 2.1.7
                         * @todo Update after 22.07.2021
                         */
                        $url = is_int( $dnt_options['co_options']['img'] ) ? wp_get_attachment_url( $dnt_options['co_options']['img'] ) : $dnt_options['co_options']['img'];
                        $dnt_shortcode_return .= '<input type="image" class="dnt-co-button" src="' . $url . '"alt="2co button" />';
                        /* end todo */
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

		if ( ! wp_script_is( 'dnt_script', 'enqueued' ) ) {
            wp_enqueue_script( 'dnt_script', plugins_url( 'js/script.js', __FILE__ ) , array( 'jquery' ), false, true );
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
add_filter( 'pgntn_callback', 'dnt_pagination_callback' );
add_action( 'widgets_init', 'dnt_register_widget' );
add_shortcode( 'donate', 'dnt_user_shortcode' );
/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'dnt_shortcode_button_content' );
add_action( 'admin_notices', 'dnt_admin_notices' );
/* Links in admin menu */
add_filter( 'plugin_row_meta', 'dnt_register_plugin_links', 10, 2 );
add_filter( 'plugin_action_links', 'dnt_plugin_action_links', 10, 2 );
