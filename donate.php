<?php
/*
Plugin Name: Donate by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: Create custom buttons for payment systems
Author: BestWebSoft
Version: 2.0.4
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
*/

/*
	© Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

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
		bws_add_general_menu( plugin_basename( __FILE__ ) );
		add_submenu_page( 'bws_plugins', 'Donate', 'Donate', 'manage_options', "donate.php", 'dnt_admin_settings' );
	}
}

if ( ! function_exists( 'dnt_init' ) ) {
	function dnt_init() {
		global $dnt_plugin_info;
		/* Internationalization */
		load_plugin_textdomain( 'donate', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );		
  		
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_functions.php' );
		
		if ( empty( $dnt_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$dnt_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_version_check( plugin_basename( __FILE__ ), $dnt_plugin_info, "3.0" );

		/* Get/Register and check settings for plugin */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && "donate.php" == $_GET['page'] ) )
			dnt_register_settings();
	}
}

if ( ! function_exists( 'dnt_admin_init' ) ) {
	function dnt_admin_init() {
		global $bws_plugin_info, $dnt_plugin_info;

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '103', 'version' => $dnt_plugin_info["Version"] );
	}
}

/* Register default settings */
if ( ! function_exists ( 'dnt_register_settings' ) ) {
	function dnt_register_settings() {
		/* Database array for payment */
		global $dnt_options, $dnt_plugin_info;

		if ( ! $dnt_plugin_info ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$dnt_plugin_info = get_plugin_data( __FILE__ );
		}

		$default_pay_options = array(
			'paypal_options' => array(
				'style'					=>	1,
				'style_load'			=>	0,
				'choice_check'			=>	0,
				'payment_option'		=>	0,
				'image_paypal'			=>	1,
				'paypal_purpose'		=>	'',
				'paypal_account'		=>	0,
				'image_paypal_id'		=>	'',
				'item_source_paypal'	=>	'',
				'img'					=>	'',
				'paypal_amount'			=>	'1.00'
			),
			'co_options' => array(
				'style'					=>	1,
				'choice_check'			=>	0,
				'style_load'			=>	0,
				'choice_check'			=>	0,
				'image_co'				=>	1,
				'co_account'			=>	0,
				'co_quantity'			=>	1,
				'image_co_id'			=>	'',
				'item_source_co'		=>	'',
				'product_id'			=>	'',
				'img'					=>	'',
			),
			'donate_options' => array(
				'check_donate'			=>	1,
				'image_donate'			=>	1
			),
			'plugin_option_version' => $dnt_plugin_info["Version"]
		);
		if ( ! get_option( 'dnt_options' ) )
			add_option( 'dnt_options', $default_pay_options, '', 'yes' );
		$dnt_options = get_option( 'dnt_options' );

		if ( ! isset( $dnt_options['plugin_option_version'] ) || $dnt_options['plugin_option_version'] != $dnt_plugin_info["Version"] ) {
			$dnt_options = array_merge( $default_pay_options, $dnt_options );
			$dnt_options['plugin_option_version'] = $dnt_plugin_info["Version"];
			update_option( 'dnt_options', $dnt_options );
		}
	}
}

/* PayPal API */
if ( ! function_exists ( 'dnt_draw_paypal_form' ) ) {
	function dnt_draw_paypal_form() {
		$dnt_options = get_option( 'dnt_options', array() ); ?>
		<input type='hidden' name='business' value="<?php echo $dnt_options['paypal_options']['paypal_account']; ?>" />
		<input type='hidden' name='item_name' value="<?php echo $dnt_options['paypal_options']['paypal_purpose']; ?>" />
		<input type='hidden' name='amount' value="<?php echo $dnt_options['paypal_options']['paypal_amount']; ?>">
		<input type='hidden' name='cmd' value='_donations' />
	<?php }
}

/* 2CO API */
if ( ! function_exists ( 'dnt_draw_co_form' ) ) {
	function dnt_draw_co_form() {
		$dnt_options = get_option( 'dnt_options', array() ); ?>
		<input type='hidden' name='sid' value="<?php echo $dnt_options['co_options']['co_account']; ?>" />
		<input type='hidden' name='quantity' value="<?php echo $dnt_options['co_options']['co_quantity']; ?>" />
		<input type='hidden' name='product_id' value="<?php echo $dnt_options['co_options']['product_id']; ?>" />
	<?php }
}

/* Add CSS and JS for plugin */
if ( ! function_exists ( 'dnt_plugin_stylesheet' ) ) {
	function dnt_plugin_stylesheet() {
		global $wp_version, $hook_suffix;	
		if ( ! is_admin() || ( isset( $_GET['page'] ) && "donate.php" == $_GET['page'] ) || ( isset( $hook_suffix ) && "widgets.php" == $hook_suffix ) ) {
			if ( 3.8 > $wp_version )
				wp_enqueue_style( 'dnt_style', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );
			else
				wp_enqueue_style( 'dnt_style', plugins_url( 'css/style.css', __FILE__ ) );

			wp_enqueue_script( 'dnt_script', plugins_url( '/js/script.js', __FILE__ ) , array( 'jquery' ) );
		}
	}
}

/* Additional links on the plugin page */
if ( ! function_exists ( 'dnt_register_plugin_links' ) ) {
	function dnt_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=donate.php">' . __( 'Settings', 'donate' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/donate-button/faq/" target="_blank">' . __( 'FAQ', 'donate' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'donate' ) . '</a>';
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
			if ( ! $this_plugin )
				$this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=donate.php">' . __( 'Settings', 'donate' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

/* Create Pay Options Box */
if ( ! function_exists ( 'dnt_options_box' ) ) {
	function dnt_options_box() { ?>
		<div id='dnt_options_box' class='dnt_noscript_box'>
			<div id='dnt_options_title'><?php _e( 'Please choose the payment system to make a donation', 'donate' ); ?></div>
			<div class='dnt_clear_both'></div>
			<div id='dnt_options'>
				<div class='dnt_paypal_image'>
					<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window' >
						<input type='image' id='dnt_paypal_button' src="<?php echo plugins_url( 'images/paypal.jpg', __FILE__ ) ?>" alt='paypal' title='PayPal checkout' />
						<?php dnt_draw_paypal_form(); ?>
					</form>
				</div>
				<div class='dnt_co_image'>
					<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window' >
						<input type='image' id='dnt_co_button' src="<?php echo plugins_url( 'images/co.jpg', __FILE__ ) ?>" alt='2CO' title='2CO checkout' />
						<?php dnt_draw_co_form(); ?>
					</form>
				</div>
			</div>
			<div class='dnt_clear_both'></div>
		</div>
	<?php }
}

/* Register Donate_Widget widget */
if ( ! function_exists ( 'dnt_register_widget' ) ) {
	function dnt_register_widget() {
		register_widget( 'Donate_Widget' );
	}
}

/* Add widget */
class Donate_Widget extends WP_Widget {
	/* Register widget with WordPress */
	function __construct() {
		parent::__construct(
			'donate_widget',
			'Donate ' . __( 'Widget', 'donate' ),
			array( 'description' => 'Donate ' . __( 'Widget', 'donate' ), )
		);
	}

	/* Front-end display of widget */
	public function widget( $args, $instance ) {
		global $dnt_options;
		if ( 'hide' == $instance['dnt_widget_button_options_co'] && 'hide' == $instance['dnt_widget_button_options_paypal'] ) {
			/* Do not show widget in front-end */
		} else {
			echo $args['before_widget']; ?>
			<h3 class='widget-title'><?php echo $instance['dnt_widget_title']; ?></h3>
			<ul>
				<li>
					<?php if ( ( 'donate' != $instance['dnt_widget_button_system'] ) && ( ( 'default' == $instance['dnt_widget_button_options_co'] ) || ( 'small' == $instance['dnt_widget_button_options_co'] ) || ( 'custom' == $instance['dnt_widget_button_options_co'] ) || ( 'credits' == $instance['dnt_widget_button_options_co'] ) ) ) { ?>
						<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window'>
							<?php if ( 'default' == $instance['dnt_widget_button_options_co'] ) { ?>
								<input type='image' class='dnt_co_button' src="<?php echo plugins_url( 'images/co-default.png', __FILE__ ); ?>" alt='co-default' />
							<?php } elseif ( 'small' == $instance['dnt_widget_button_options_co'] ) { ?>
								<input type='image' class='dnt_co_button' src="<?php echo plugins_url( 'images/co-small.png', __FILE__ ); ?>" alt='co-small' />
							<?php } elseif ( 'credits' == $instance['dnt_widget_button_options_co'] ) { ?>
								<input type='image' class='dnt_co_button' src="<?php echo plugins_url( 'images/co-credits.png', __FILE__ ); ?>" alt='co-credits' />
							<?php }	elseif ( 'custom' == $instance['dnt_widget_button_options_co'] ) { ?>
								<input type='image' src='<?php echo $dnt_options['co_options']['img']; ?>' alt='custom-button-co' />
							<?php }
							dnt_draw_co_form(); ?>
						</form>
					<?php }
					if ( ( 'donate' != $instance['dnt_widget_button_system'] ) && ( ( 'default' == $instance['dnt_widget_button_options_paypal'] ) || ( 'small' == $instance['dnt_widget_button_options_paypal'] ) || ( 'custom' == $instance['dnt_widget_button_options_paypal'] ) || ( 'credits' == $instance['dnt_widget_button_options_paypal'] ) ) ) { ?>
						<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window'>
							<?php if ( 'default' == $instance['dnt_widget_button_options_paypal'] ) { ?>
								<input type='image' class='dnt_paypal_button' src="<?php echo plugins_url( 'images/paypal-default.png', __FILE__ ); ?>" alt='paypal-default' />
							<?php } elseif ( 'small' == $instance['dnt_widget_button_options_paypal'] ) { ?>
								<input type='image' class='dnt_paypal_button' src="<?php echo plugins_url( 'images/paypal-small.png', __FILE__ ); ?>" alt='paypal-small' />
							<?php } elseif ( 'credits' == $instance['dnt_widget_button_options_paypal'] ) { ?>
								<input type='image' class='dnt_paypal_button' src="<?php echo plugins_url( 'images/paypal-credits.png', __FILE__ ); ?>" alt='paypal-credits' />
							<?php } elseif ( 'custom' == $instance['dnt_widget_button_options_paypal'] ) { ?>
								<input type='image' src='<?php echo $dnt_options['paypal_options']['img']; ?>' alt='custom-button-paypal' />
							<?php }
							dnt_draw_paypal_form(); ?>
						</form>
					<?php }
					elseif ( 'donate' == $instance['dnt_widget_button_system'] ) { ?>
						<div class='dnt_donate_button dnt_noscript_button'><img src="<?php echo plugins_url( 'images/donate-button.png', __FILE__ ); ?>" alt='donate-button' />
							<?php dnt_options_box(); ?>
						</div>
					<?php } ?>
				</li>
			</ul>
			<?php echo $args['after_widget'];
		}
	}

	/* Back-end widget form */
	public function form( $instance ) {
		$default_widget_args = array(
			'dnt_widget_button_system'			=>	'donate',
			'dnt_widget_button_options_paypal'	=>	'default',
			'dnt_widget_button_options_co'		=>	'default',
			'dnt_widget_title'					=>	''
		);
		$instance = wp_parse_args( ( array ) $instance, $default_widget_args ); ?>
		<div class='dnt_widget_settings_donate'>
			<p>
				<label>
					<?php _e( 'Title:', 'donate' ); ?>
					<input type='text' <?php echo $this->get_field_id( 'dnt_widget_title' ); ?> name="<?php echo $this->get_field_name( 'dnt_widget_title' ); ?>" value="<?php echo $instance['dnt_widget_title']; ?>" class='dnt_widget_title' />
				</label>
				<label class='dnt_lbl'>
					<input type='checkbox' name="<?php echo $this->get_field_name( 'dnt_widget_button_system' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_donate' ); ?>" class='dnt_widget_checkbox_donate' <?php if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "checked='checked'"; } ?> value='donate' /> <?php _e( 'One button', 'donate' ); ?>
				</label>
			</p>
			<p>
				<ul class='category-tabs'>
					<li class='tabs'><a id='dnt_paypal_widget_tab'><?php _e( 'PayPal', 'donate' ); ?></a></li>
					<li><a id='dnt_co_widget_tab'><?php _e( '2CO', 'donate' ); ?></a></li>
				</ul>
				<div class='dnt_tabs-panel-paypal'>
					<label>
						<input type='radio' class='dnt_small_paypal' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_button_small_paypal' ); ?>" <?php if ( 'small' == $instance['dnt_widget_button_options_paypal'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='small' /> <?php _e( 'Small button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_credits_paypal' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_button_credits_paypal' ); ?>" <?php if ( 'credits' == $instance['dnt_widget_button_options_paypal'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='credits' /> <?php _e( 'Credit cards button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_default_paypal' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" class="dnt_widget_checkers" id="<?php echo $this->get_field_id( 'dnt_widget_button_style_paypal' ); ?>" <?php if ( 'default' == $instance['dnt_widget_button_options_paypal'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='default' /> <?php _e( 'Default button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_custom_paypal' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" class="dnt_widget_checkers" id="<?php echo $this->get_field_id( 'dnt_widget_button_custom_paypal' ); ?>" <?php if ( 'custom' == $instance['dnt_widget_button_options_paypal'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='custom' /> <?php _e( 'Custom button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_default_paypal' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_paypal' ); ?>" class="dnt_widget_checkers" id="<?php echo $this->get_field_id( 'dnt_widget_button_style_paypal' ); ?>" <?php if ( 'hide' == $instance['dnt_widget_button_options_paypal'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='hide' /> <?php _e( 'Don&apos;t show', 'donate' ); ?>
					</label>
				</div>
				<div class='dnt_tabs-panel-co dnt_hidden'>
					<label>
						<input type='radio' class='dnt_small_co' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_button_small_co' ); ?>" <?php if ( 'small' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='small' /> <?php _e( 'Small button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_credits_co' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_button_credits_co' ); ?>" <?php if ( 'credits' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='credits' /> <?php _e( 'Credit cards button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_default_co' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" id="<?php echo $this->get_field_id( 'dnt_widget_button_style_co' ); ?>" <?php if ( 'default' == $instance['dnt_widget_button_options_co'] && 'donate' != $instance['dnt_widget_button_system'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='default' /> <?php _e( 'Default button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_custom_co' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" class="dnt_widget_checkers" id="<?php echo $this->get_field_id( 'dnt_widget_button_custom_co' ); ?>" <?php if ( 'custom' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='custom' /> <?php _e( 'Custom button', 'donate' ); ?>
					</label>
					<label>
						<input type='radio' class='dnt_custom_co' name="<?php echo $this->get_field_name( 'dnt_widget_button_options_co' ); ?>" class="dnt_widget_checkers" id="<?php echo $this->get_field_id( 'dnt_widget_button_custom_co' ); ?>" <?php if ( 'hide' == $instance['dnt_widget_button_options_co'] ) { echo "checked='checked'"; } else if ( 'donate' == $instance['dnt_widget_button_system'] ) { echo "disabled='disabled'"; } ?> value='hide' /> <?php _e( 'Don&apos;t show', 'donate' ); ?>
					</label>
				</div>
				<?php if ( 'donate' == $instance['dnt_widget_button_system'] ) { ?>
					<div class='dnt_disabled dnt_display'></div>
				<?php } else { ?>
					<div class='dnt_disabled dnt_hidden'></div>
				<?php } ?>
			</p>
		</div>
	<?php }

	/* Save Widget form values */
	public function update( $new_instance, $old_instance ) {
		$instance										=	$old_instance;
		$instance['dnt_widget_title']					=	$new_instance['dnt_widget_title'];
		$instance['dnt_widget_button_system']			=	$new_instance['dnt_widget_button_system'];
		$instance['dnt_widget_button_options_co']		=	$new_instance['dnt_widget_button_options_co'];
		$instance['dnt_widget_button_options_paypal']	=	$new_instance['dnt_widget_button_options_paypal'];
		if ( 'donate' != $instance["dnt_widget_button_system"] && NULL == $instance['dnt_widget_button_options_paypal'] )
			$instance['dnt_widget_button_options_paypal'] = 'default';
		if ( 'donate' != $instance["dnt_widget_button_system"] && NULL == $instance['dnt_widget_button_options_co'] )
			$instance['dnt_widget_button_options_co'] = 'default';
		return $instance;
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
			'png'	=>	'image/png',
			'jpe'	=>	'image/jpeg',
			'jpeg'	=>	'image/jpeg',
			'jpg'	=>	'image/jpeg',
			'gif'	=>	'image/gif',
			'bmp'	=>	'image/bmp',
			'ico'	=>	'image/vnd.microsoft.icon',
			'tiff'	=>	'image/tiff',
			'tif'	=>	'image/tiff',
			'svg'	=>	'image/svg+xml',
			'svgz'	=>	'image/svg+xml'
		);
		if ( ! file_exists( $uploaddir ) ) {
			/*Create dir with absolute path */
			@mkdir( $uploaddir, 0755 );
		}
		if ( isset ( $_POST['dnt_button_choice_' . $payment] ) ) {
			if ( 'custom' == $_POST['dnt_button_choice_' . $payment] ) {
				$dnt_options[$payment . '_options']['choice_check'] = 0;
				$dnt_options[$payment . '_options']['style'] = 2;
				${"shortcode_$payment"} = '[donate payment=' . " " . $payment . 'type=custom]';
				if ( ( isset ( $_POST['dnt_button_custom_choice_' . $payment] ) ) && ( 'local' == $_POST['dnt_button_custom_choice_' . $payment] ) ) {
					$dnt_options[$payment . '_options']['style_load'] = 1;
					/* For custom local upload */
					if ( is_uploaded_file( $_FILES['dnt_custom_local_' . $payment]['tmp_name'] ) ) {
						${"get_size_uploaded_file_$payment"} = getimagesize( $_FILES['dnt_custom_local_' . $payment]['tmp_name'] );
						/* If uploaded file not image */
						if ( in_array( ${"get_size_uploaded_file_$payment"}['mime'], $dnt_mime_types ) ) {
							$current_image_width	=	${"get_size_uploaded_file_$payment"}[0];
							$current_image_height	=	${"get_size_uploaded_file_$payment"}[1];
							if ( ( $current_image_width <= $max_width ) && ( $current_image_height <= $max_height ) && ( $current_image_width >= $min_width ) && ( $current_image_height >= $min_height ) ) {
								/* File name */
								${"uploadfile_$payment"} = "id-" . time() . "-" . basename( $_FILES['dnt_custom_local_' . $payment]['name'] );
								${"source_$payment"} = $uploaddir . ${"uploadfile_$payment"};
								/* Copy file from temp to needed dir */
								if ( copy( $_FILES['dnt_custom_local_' . $payment]['tmp_name'], ${"source_$payment"} ) ) {
									/* Excerpt local dir */
									$uploaddir = substr( $uploaddir, strlen( ABSPATH . 'wp-content' ) );
									$dnt_options['path'] = $uploaddir;
									$dnt_options[$payment . '_options']['item_source_' . $payment][] = ${"uploadfile_$payment"};
									for ( $k = 0; $k <= count( $dnt_options[$payment . '_options']['item_source_' . $payment], COUNT_RECURSIVE ) - 1; $k++ ) {
										${"shortcode_id_$payment"} = $k + 1;
									}
									$dnt_options[$payment . '_options']['image_' . $payment . '_id'] = ${"shortcode_id_$payment"};
								} else {
									$dnt_error['upload_error'] = __( 'Unable to move the file', 'donate' );
								}
							} else {
								if ( ( $current_image_width < $min_width ) || ( $current_image_height < $min_height ) )
									$dnt_error[$payment . '_upload'] = __( 'Uploaded file smaller than 16x16', 'donate' );
								else
									$dnt_error[$payment . '_upload'] = __( 'Uploaded file bigger then 170x70', 'donate' );
							}
						} else {
							$dnt_error['mime_type'] = __( 'You can upload only image files', 'donate' ) . '(.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)';
						}
					}
				} elseif ( ( isset ( $_POST['dnt_button_custom_choice_' . $payment ] ) ) && ( 'url' == $_POST['dnt_button_custom_choice_' . $payment ] ) ) {
					$dnt_options[$payment . '_options']['style_load'] = 2;
					/* URL upload */
					$dnt_headers = @get_headers( $_POST['dnt_custom_url_' . $payment] );
					if ( null != $_POST['dnt_custom_url_' . $payment] && preg_match( "#^https?:(.*).(png|jpg|jpeg|gif|bmp|ico|tif|tiff|jpe|svg|svgz)$#i", $_POST['dnt_custom_url_' . $payment] ) && preg_match("|200|", $dnt_headers[0] ) ) {
						if ( is_callable( 'curl_init' ) ) {
							/* Url from form element value */
							${"url_$payment"} = curl_init( $_POST['dnt_custom_url_' . $payment ] );
							if ( isset( $_POST['dnt_custom_url_' . $payment] ) ) {
								${"get_size_uploaded_file_$payment"} = getimagesize( $_POST['dnt_custom_url_' . $payment ] );
								if ( in_array ( ${"get_size_uploaded_file_$payment"}['mime'], $dnt_mime_types ) ) {
									$current_image_width	=	${"get_size_uploaded_file_$payment"}[0];
									$current_image_height	=	${"get_size_uploaded_file_$payment"}[1];
									if ( ( $current_image_width <= $max_width ) && ( $current_image_height <= $max_height ) && ( $current_image_width >= $min_width ) && ( $current_image_height >= $min_height ) ) {
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
										$dnt_options[$payment . '_options']['item_source_' . $payment][] = ${"url_path_$payment"};
										$dnt_options['path'] = $uploaddir;
										for ( $k = 0; $k <= count( $dnt_options[$payment . '_options']['item_source_' . $payment], COUNT_RECURSIVE ) - 1; $k++ ) {
											${"shortcode_id_$payment"} = $k + 1;
										}
										$dnt_options[ $payment . '_options']['image_' . $payment . '_id'] = ${"shortcode_id_$payment"};
									} else {
										if ( ( $current_image_width < $min_width ) || ( $current_image_height < $min_height ) )
											$dnt_error[ $payment . '_upload'] = __( 'Uploaded file smaller than 16x16', 'donate' );
										else
											$dnt_error[ $payment . '_upload'] = __( 'Uploaded file bigger then 170x70', 'donate' );
									}
								} else {
									$dnt_error['mime_type'] = __( 'You can upload only image files', 'donate' ) . '(.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)';
								}
							}
						} else {
							$dnt_error['curl'] = __( 'Please enable curl on server' , 'donate' );
						}
					}
				}
			}
		}
	}
}

/* Display all custom buttons */
if ( ! function_exists ( 'dnt_display_custom_buttons' ) ) {
	function dnt_display_custom_buttons( $payment ) {
		global $dnt_options, $style_paypal, $style_co, $image_source_paypal, $image_source_co, $shortcode_paypal, $shortcode_co;
		if ( null != $dnt_options[$payment . '_options']['item_source_' . $payment ] ) { ?>
			<div class='postbox'>
				<div class='dnt_handlediv handlediv'></div>
				<h3 class='dnt_buttons_block'><span><?php _e( 'Display all custom buttons', 'donate' ); ?></span></h3>
				<div class='inside'>
					<table id='dnt_imagex_box'>
						<?php for ( $i = 0; $i <= count( $dnt_options[$payment . '_options']['item_source_' . $payment], COUNT_RECURSIVE ) - 1; $i++ ) {
							${"image_check_val_$payment"} = content_url() . $dnt_options['path'] . $dnt_options[$payment . '_options']['item_source_' . $payment][$i];
							if ( ${"style_$payment"} == '2' ) {
								if ( ( isset ( $_POST['check_image_' . $payment ] ) ) && ( $_POST['check_image_' . $payment] == ${"image_check_val_$payment"} ) && ( ! isset ( $_POST['dnt_button_custom_choice_' . $payment] ) ) ) {
									$dnt_options[$payment . '_options']['image_' . $payment] = 4;
									${"shortcode_$payment"} = '[donate payment=' . $payment . " " . 'type=custom id=' . $dnt_options[$payment . '_options']['image_' . $payment . '_id'] . ']';
									${"image_source_$payment"} = ${"image_check_val_$payment"};
									$dnt_options[$payment . '_options']['img'] = ${"image_check_val_$payment"};
								} elseif ( isset ( $_POST['dnt_button_custom_choice_' . $payment] ) ) {
									$dnt_options[$payment . '_options']['img'] = ${"image_check_val_$payment"};
									${"shortcode_$payment"} = '[donate payment=' . $payment . " " . 'type=custom id=' . $dnt_options[$payment . '_options']['image_' . $payment . '_id'] . ']';
									${"image_source_$payment"} = ${"image_check_val_$payment"};
									$dnt_options[$payment . '_options']['image_' . $payment] = 4;
								}
								$dnt_options['paypal_options']['style_load'] = 0;
								$dnt_options['co_options']['style_load'] = 0;
							} ?>
							<tr>
								<td>
									<img src="<?php echo content_url() . $dnt_options['path'] . $dnt_options[$payment . '_options']['item_source_' . $payment][$i] ?>" alt='custom-image' />
								</td>
								<td>
									<label><input class='dnt_custom_buttons_block' type='radio' name="<?php echo 'check_image_' . $payment ?>" <?php if ( $dnt_options[$payment . '_options']['img'] == ${"image_check_val_$payment"} ) { ?>checked='checked' <?php } ?> value="<?php echo ${"image_check_val_$payment"}; ?>" /> <?php _e( 'Use button','donate' ); ?></label>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		<?php }
		update_option( 'dnt_options', $dnt_options );
	}
}

/* Output images */
if ( ! function_exists ( 'dnt_output_images' ) ) {
	function dnt_output_images( $payment ) {
		global $dnt_options, $image_source_co, $image_source_paypal, $image_alt_co, $shortcode_paypal, $shortcode_co, $image_alt_paypal;
		if ( '1' == $dnt_options[$payment . '_options']['image_' . $payment] ) {
			${"shortcode_$payment"}		=	'[donate payment=' . $payment . ' type=default]';
			${"image_source_$payment"}	=	plugins_url( 'images/' . $payment . '-default.png', __FILE__ );
			${"image_alt_$payment"}		=	$payment . '-default';
		} elseif ( '2' == $dnt_options[$payment . '_options']['image_' . $payment] ) {
			${"shortcode_$payment"}		=	'[donate payment=' . $payment . " " . 'type=default-small]';
			${"image_source_$payment"}	=	plugins_url( 'images/' . $payment . '-small.png', __FILE__ );
			${"image_alt_$payment"}		=	$payment . '-small';
		} elseif ( '3' == $dnt_options[$payment . '_options']['image_' . $payment] ) {
			${"shortcode_$payment"}		=	'[donate payment=' . $payment . " " . 'type=default-credits]';
			${"image_source_$payment"}	=	plugins_url( 'images/' . $payment . '-credits.png', __FILE__ );
			${"image_alt_$payment"}		=	$payment . '-credits';
		} elseif ( '4' == $dnt_options[$payment . '_options']['image_' . $payment] ) {
			${"shortcode_$payment"}		=	'[donate payment=' .$payment . " " . 'type=custom id=' . $dnt_options[$payment . '_options']['image_' . $payment . '_id'] . ']';
			${"image_source_$payment"}	=	$dnt_options[$payment . '_options']['img'];
			${"image_alt_$payment"}		=	$payment . '-custom-local';
		}
	}
}

/* Display output block */
if ( ! function_exists ( 'dnt_display_output_block' ) ) {
	function dnt_display_output_block( $payment ) {
		global $dnt_options, $image_source_co, $image_source_paypal, $image_alt_co, $shortcode_paypal, $shortcode_co, $image_alt_paypal, $image_source_donate, $shortcode_donate, $image_alt_donate;
		if ( '1' == $dnt_options['donate_options']['image_donate'] ) { ?>
			<div id='dnt_donate_box' class='dnt_display_box'>
				<h3 class='dnt_buttons_block'><?php _e( 'Output', 'donate' ); ?></h3>
				<div class='dnt_inside_block'>
					<div class='dnt_img_box'><img src='<?php echo $image_source_donate ?>' alt=<?php echo $image_alt_donate ?> /></div>
					<div class='dnt_notice_box'><?php _e( 'If you would like to add the button to your website, just copy and paste this shortcode to your post page or widget:', 'donate' ); ?></div>
					<div class='dnt_shortcode_box'><?php echo $shortcode_donate ?></div>
				</div>
			</div>
		<?php }	else { ?>
			<div class='dnt_display_box'>
				<h3 class='dnt_buttons_block'><?php _e( 'Output', 'donate' ); ?></h3>
				<div class='dnt_inside_block'>
					<div class='dnt_img_box'><img src='<?php echo ${"image_source_$payment"} ?>' alt=<?php echo ${"image_alt_$payment"} ?> /></div>
					<div class='dnt_notice_box'><?php _e( 'If you would like to add the button to your website, just copy and paste this shortcode to your post page or widget:', 'donate' ); ?></div>
					<div class='dnt_shortcode_box'><?php echo ${"shortcode_$payment"} ?></div>
				</div>
			</div>
		<?php }
		update_option( 'dnt_options', $dnt_options );
	}
}

/* Add content for donate Menu */
if ( ! function_exists ( 'dnt_admin_settings' ) ) {
	function dnt_admin_settings() {
		global $dnt_error, $dnt_options, $image_source_donate, $shortcode_donate, $shortcode_paypal, $image_alt_donate, $style_paypal, $style_co,
				$image_source_co, $image_source_paypal, $image_alt_co, $shortcode_co, $image_alt_paypal, $dnt_plugin_info;

		$message = $choice_check = $dnt_tab_co = $dnt_tab_paypal = $dnt_tab_active_paypal = $dnt_tab_active_co = $shortcode_donate	=	$image_alt_donate = '';
		/* PayPal save options */
		if ( isset( $_POST['option_form'] ) && check_admin_referer( plugin_basename( __FILE__ ), 'dnt_check_field' ) ) {
			if ( null != $_POST['dnt_paypal_account'] ) {
				if ( ! is_email( $_POST['dnt_paypal_account'] ) ) {
					$dnt_error['account_paypal'] = __( 'Email validation error, email must be written like example@gmail.com', 'donate' );
				} else {
					$dnt_options['paypal_options']['paypal_account'] = $_POST['dnt_paypal_account'];
				}
			} else {
				$dnt_options['paypal_options']['paypal_account'] = '';
				$dnt_error['account_paypal'] = __( 'Account name is required field, please write your account name in PayPal tab', 'donate' );
			}

			if ( ( isset ( $_POST['dnt_paypal_amount'] ) ) && ( null != $_POST['dnt_paypal_amount'] ) ) {
				$dnt_options['paypal_options']['paypal_amount'] = number_format( floatval( $_POST['dnt_paypal_amount'] ), 2, ".", '' );
				if ( "0.00" == $dnt_options['paypal_options']['paypal_amount'] )
					$dnt_options['paypal_options']['paypal_amount'] = '1.00';
			} else {
				$dnt_options['paypal_options']['paypal_amount'] = '1.00';
			}
			
			if ( ( isset ( $_POST['dnt_paypal_purpose'] ) ) && ( null != $_POST['dnt_paypal_purpose'] ) ) {
				$dnt_options['paypal_options']['paypal_purpose'] = stripslashes( esc_html( $_POST['dnt_paypal_purpose'] ) );
			} else {
				$dnt_options['paypal_options']['paypal_purpose'] = '';
			}
			if ( ( isset( $_POST['dnt_button_choice_paypal'] ) ) && ( 'default' == $_POST['dnt_button_choice_paypal'] ) ) {
				$dnt_options['paypal_options']['style']			=	1;
				$dnt_options['paypal_options']['image_paypal']	=	1;
				if ( ( isset( $_POST['dnt_check_choice_paypal'] ) ) && ( 'small' == $_POST['dnt_check_choice_paypal'] ) ) {
					$dnt_options['paypal_options']['choice_check']	=	1;
					$dnt_options['paypal_options']['image_paypal']	=	2;
				} elseif ( ( isset( $_POST['dnt_check_choice_paypal'] ) ) && ( 'credits' == $_POST['dnt_check_choice_paypal'] ) ) {
					$dnt_options['paypal_options']['choice_check']	=	2;
					$dnt_options['paypal_options']['image_paypal']	=	3;
				} else {
					$dnt_options['paypal_options']['choice_check']	=	0;
					$dnt_options['paypal_options']['image_paypal']	=	1;
					$shortcode_paypal = '[donate payment=paypal type=default]';
				}
			}
			dnt_save_custom_images( 'paypal' );
			/* For One button */
			if ( ( isset( $_POST['dnt_button_donate'] ) ) && ( 'donate' == $_POST['dnt_button_donate'] ) ) {
				$dnt_options['donate_options']['check_donate']	=	1;
				$dnt_options['donate_options']['image_donate']	=	1;
			} else {
				$dnt_options['donate_options']['check_donate']	=	0;
				$dnt_options['donate_options']['image_donate']	=	0;
			}
			/* 2CO save options */
			if ( ( isset ( $_POST['dnt_co_account'] ) ) && ( null != $_POST['dnt_co_account'] ) && ( preg_match( '/^\d+$/' ,$_POST['dnt_co_account'] ) ) ) {
				$dnt_options['co_options']['co_account'] = $_POST['dnt_co_account'];
			} else {
				$dnt_options['co_options']['co_account'] = '';
				$dnt_error['account_co'] = __( 'Account name is required field, please write your account name in 2CO tab', 'donate' );
			}
			if ( ( null != $_POST['dnt_co_account'] ) && ( ! preg_match( '/^\d+$/' ,$_POST['dnt_co_account'] ) ) ) {
				$dnt_error['type_account'] = __( '2CO Account error:You type string, numeric expected.', 'donate' );
			}
			if ( ( null != $_POST['dnt_quantity_donate'] ) && ( ! preg_match( '/^\d+$/' ,$_POST['dnt_quantity_donate'] ) ) ) {
				$dnt_error['type_quantity'] = __( '2CO Quantity error:You type string, numeric expected.', 'donate' );
			}
			if ( ( null != $_POST['dnt_product_id'] ) && ( ! preg_match( '/^\d+$/' ,$_POST['dnt_product_id'] ) ) ) {
				$dnt_error['type_product'] = __( '2CO Product ID error:You type string, numeric expected.', 'donate' );
			}
			if ( ( isset ( $_POST['dnt_quantity_donate'] ) ) && ( null != $_POST['dnt_quantity_donate'] ) && ( preg_match( '/^\d+$/' ,$_POST['dnt_quantity_donate'] ) ) ) {
				$dnt_options['co_options']['co_quantity'] = $_POST['dnt_quantity_donate'];
			} else {
				$dnt_options['co_options']['co_quantity'] = '';
				$dnt_error['co_quantity'] = __( 'Quantity is required field, please write quantity of products in 2CO tab', 'donate' );
			}
			if ( ( isset ( $_POST['dnt_product_id'] ) ) && ( null != $_POST['dnt_product_id'] ) && ( preg_match( '/^\d+$/' ,$_POST['dnt_product_id'] ) ) ) {
				$dnt_options['co_options']['product_id'] = $_POST['dnt_product_id'];
			} else {
				$dnt_options['co_options']['product_id'] = '';
				$dnt_error['product_id'] = __( 'Product ID is required field, please write product ID in 2CO tab', 'donate' );
			}
			if ( ( isset( $_POST['dnt_button_choice_co'] ) ) && ( 'default' == $_POST['dnt_button_choice_co'] ) ) {
				$dnt_options['co_options']['image_co']	=	1;
				$dnt_options['co_options']['style']		=	1;
				if ( isset( $_POST['dnt_check_choice_co'] ) ) {
					if ( 'small' == $_POST['dnt_check_choice_co'] ) {
						$dnt_options['co_options']['choice_check']	=	1;
						$dnt_options['co_options']['image_co']		=	2;
					} elseif ( 'credits' == $_POST['dnt_check_choice_co'] ) {
						$dnt_options['co_options']['choice_check']	=	2;
						$dnt_options['co_options']['image_co']		=	3;
					}
				} else {
					$dnt_options['co_options']['choice_check']	=	0;
					$dnt_options['co_options']['image_co']		=	1;
				}
			}
			dnt_save_custom_images( 'co' );

			/* Display active payment tab */
			/* If value changed add classes for active tab */
			if ( '1' == $_POST['dnt_tab_co'] ) {
				$dnt_tab_co				=	"class='dnt_display'";
				$dnt_tab_paypal			=	"class='dnt_hidden'";
				$dnt_tab_active_co		=	'nav-tab-active dnt_display_tab';
				$dnt_tab_active_paypal	=	'';
			} elseif ( '1' == $_POST['dnt_tab_paypal'] ) {
				$dnt_tab_paypal			=	"class='dnt_display'";
				$dnt_tab_co				=	"class='dnt_hidden'";
				$dnt_tab_active_paypal	=	'nav-tab-active dnt_display_tab';
				$dnt_tab_active_co		=	'';
			}
		}
		/* Get options for PayPal */
		$style_paypal		=	$dnt_options['paypal_options']['style'];
		$style_load_paypal	=	$dnt_options['paypal_options']['style_load'];
		$choice_check		=	$dnt_options['paypal_options']['choice_check'];
		$payment_option		=	$dnt_options['paypal_options']['payment_option'];

		/* Get options for 2CO */
		$style_co			=	$dnt_options['co_options']['style'];
		$style_load_co		=	$dnt_options['co_options']['style_load'];
		$choice_check_co	=	$dnt_options['co_options']['choice_check'];

		/* Get options for one button */		
		$check_donate		=	$dnt_options['donate_options']['check_donate'];

		dnt_output_images( 'paypal' );
		/* For One Button */
		if ( '1' == $dnt_options['donate_options']['image_donate'] ) {
			$shortcode_donate		=	'[donate count=2 type=default]';
			$image_source_donate	=	plugins_url( 'images/donate-button.png', __FILE__ );
			$image_alt_donate		=	'donate-default';
		}
		dnt_output_images( 'co' ); ?>
		<!--Interface-->
		<!--Errors-->
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php _e( 'Donate Settings', 'donate' ); ?></h2>
			<div id="dnt_settings_notice" class="updated fade" style="display:none"><p><strong><?php _e( "Notice:", 'donate' ); ?></strong> <?php _e( "The plugin's settings have been changed. In order to save them please don't forget to click the 'Save Changes' button.", 'donate' ); ?></p></div>
			<div class="error">
				<?php if ( ( isset ( $dnt_error['account_paypal'] ) ) && ( null != $dnt_error['account_paypal'] ) && ( '1' == $_POST['dnt_tab_paypal'] ) ) { ?>
					<p><strong><?php echo $dnt_error['account_paypal']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['account_co'] ) ) && ( null != $dnt_error['account_co'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['account_co']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['paypal_upload'] ) ) && ( null != $dnt_error['paypal_upload'] ) && ( '1' == $_POST['dnt_tab_paypal'] ) ) { ?>
					<p><strong><?php echo $dnt_error['paypal_upload']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['co_upload'] ) ) && ( null != $dnt_error['co_upload'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['co_upload']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['co_quantity'] ) ) && ( null != $dnt_error['co_quantity'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['co_quantity']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['product_id'] ) ) && ( null != $dnt_error['product_id'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['product_id']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['type_account'] ) ) && ( null != $dnt_error['type_account'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['type_account']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['type_quantity'] ) ) && ( null != $dnt_error['type_quantity'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['type_quantity']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['type_product'] ) ) && ( null != $dnt_error['type_product'] ) && ( '1' == $_POST['dnt_tab_co'] ) ) { ?>
					<p><strong><?php echo $dnt_error['type_product']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['upload_error'] ) ) && ( null != $dnt_error['upload_error'] ) ) { ?>
					<p><strong><?php echo $dnt_error['upload_error']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['curl'] ) ) && ( null != $dnt_error['curl'] ) ) { ?>
					<p><strong><?php echo $dnt_error['curl']; ?></strong></p>
				<?php }
				if ( ( isset ( $dnt_error['mime_type'] ) ) && ( null != $dnt_error['mime_type'] ) ) { ?>
					<p><strong><?php echo $dnt_error['mime_type']; ?></strong></p>
				<?php } elseif ( null == $dnt_error ) {
					$message = __( 'Changes saved', 'donate' );
				} ?>
			</div>
			<?php if ( ( null != $message ) && ( isset( $_POST['option_form'] ) ) ) { ?>
				<div class="updated fade">
					<p><strong><?php echo $message; ?></strong></p>
				</div>
			<?php } ?>
			<table id='dnt_settings_box'>
				<tr>
					<td>
						<form id="dnt_settings_form" enctype='multipart/form-data' method='post' action='admin.php?page=donate.php'>
							<table id='dnt_donate'>
								<tr>
									<td>
										<label><input type='checkbox' name='dnt_button_donate' id='dnt_button_donate' value='donate' <?php if ( 1 == $dnt_options['donate_options']['check_donate'] ) { ?> checked='checked' <?php } ?> /> <?php _e( 'One button for both systems', 'donate' ); ?></label>
									</td>
								</tr>
								<tr>
									<td colspan='2'>
										<p class='dnt_notice'><?php _e( 'Please fill in the required fields for each payment system', 'donate' ); ?></p>
									</td>
								</tr>
								<tr>
									<td colspan='2'>
										<h2 class='nav-tab-wrapper dnt_hidden'>
											<a class='nav-tab <?php echo $dnt_tab_active_paypal; ?>'><span class='dnt_paypal_text'>PayPal</span></a>
											<a class='nav-tab <?php echo $dnt_tab_active_co; ?>'><span class='dnt_co_text'>2CO</span></a>
											<a class='nav-tab' href="http://bestwebsoft.com/products/donate/faq/" target='_blank'><?php _e( 'FAQ', 'donate' ); ?></a>
										</h2>
									</td>
								</tr>
							</table>
							<table id='dnt_noscript' class="form-table">
								<tr id='dnt_shortcode_options_paypal' <?php echo $dnt_tab_paypal; ?>>
									<td>
										<!--PayPal-->
										<table class="form-table">
											<tr>
												<th class='dnt_row dnt_account_row' scope="row">
													<?php _e( 'Your paypal account email address:', 'donate' ); ?>
												</td>
												<td class='dnt_account_row'>
													<input type='text' name='dnt_paypal_account' id='dnt_paypal_account' value="<?php if ( null != $dnt_options['paypal_options']['paypal_account'] ) echo $dnt_options['paypal_options']['paypal_account']; ?>" />
												</td>
											</tr>
											<tr>
												<th class='dnt_row dnt_account_row' scope="row">
													<?php _e( 'Your donation purpose:', 'donate' ); ?>
												</td>
												<td class='dnt_account_row'>
													<input type='text' id='dnt_paypal_purpose' name='dnt_paypal_purpose' value="<?php if ( null != $dnt_options['paypal_options']['paypal_purpose'] ) echo $dnt_options['paypal_options']['paypal_purpose']; ?>" />
												</td>
											</tr>
											<tr>
												<th class='dnt_row dnt_account_row' scope="row">
													<?php _e( 'Amount:', 'donate' ); ?>
												</td>
												<td class='dnt_account_row'>
													<input type='text' id='dnt_paypal_amount' name='dnt_paypal_amount' value="<?php if ( null != $dnt_options['paypal_options']['paypal_amount'] ) echo $dnt_options['paypal_options']['paypal_amount']; ?>" />
												</td>
											</tr>
											<tr>
												<th class='dnt_row' scope="row">
													<?php _e( 'Button type:', 'donate' ); ?>
												</td>
												<td>
													<label class='dnt_checkers'>
														<input id='dnt_default_paypal' class='dnt_elements' type='radio' value='default' name='dnt_button_choice_paypal' <?php if ( '1' == $style_paypal ) echo 'checked="checked"'; ?> /> <?php _e( 'Default', 'donate' ); ?>
													</label>
													<ul>
													<li>
														<label class='dnt_elements dnt_elements_disabled_paypal'>
															<input id='dnt_small_paypal' class='dnt_elements dnt_elements_disabled_paypal' type='checkbox' value='small' name='dnt_check_choice_paypal' <?php if ( '1' == $choice_check ) echo 'checked="checked"'; ?> /> <?php _e( 'Use small image', 'donate' ); ?>
														</label>
													</li>
													<li>
														<label class='dnt_elements dnt_elements_disabled_paypal'>
															<input id='dnt_credits_paypal' class='dnt_elements dnt_elements_disabled_paypal' type='checkbox' value='credits' name='dnt_check_choice_paypal' <?php if ( '2' == $choice_check ) echo 'checked="checked"'; ?> /> <?php _e( 'Use credit card image', 'donate' ); ?>
														</label>
													</li>
													</ul>
												</td>
											</tr>
											<tr>
												<td></td>
												<td>
													<label class='dnt_checkers'>
														<input id='dnt_custom_paypal' class='dnt_elements' type='radio' value='custom' name='dnt_button_choice_paypal' <?php if ( '2' == $style_paypal ) echo 'checked="checked"'; ?> /> <?php _e( 'Custom button', 'donate' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<td></td>
												<td>
													<table class='dnt_local_upload_paypal'>
														<tr>
															<td>
																<div class='dnt_local_box'>
																	<label class='dnt_checker_loc dnt_elements'><input class='dnt_elements' id='dnt_local_paypal' type='radio' value='local' name='dnt_button_custom_choice_paypal' /> <?php _e( 'Use image from file', 'donate' ); ?></label>
																	<br />
																	<input type='file' name='dnt_custom_local_paypal' class='dnt_elements' value='browse' />
																</div>
																<p class='dnt_size_notice'><?php _e( 'The size of the image you upload must be no more than 170x70 and no smaller than 16x16', 'donate' ); ?></p>
																<p class='dnt_size_notice'><?php _e( 'You can upload only image files', 'donate' ); ?> (.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)</p>
															</td>
														</tr>
														<tr>
															<td>
																<div class='dnt_url_box'>
																	<label class='dnt_checker_url dnt_elements'><input id='dnt_url_paypal' class='dnt_elements' type='radio' value='url' name='dnt_button_custom_choice_paypal' /> <?php _e( 'Use image from URL', 'donate' ); ?></label>
																	<br />
																	<input id='dnt_custom_url_paypal' class='dnt_elements' type='text' name='dnt_custom_url_paypal' />
																</div>
																<p class='dnt_size_notice'><?php _e( 'The size of the image you upload must be no more than 170x70 and no smaller than 16x16', 'donate' ); ?></p>
																<p class='dnt_size_notice'><?php _e( 'You can upload only image files', 'donate' ); ?> (.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)</p>
															</td>
														</tr>
														<tr>
															<td class='dnt_custom_buttons'>
																<?php dnt_display_custom_buttons( 'paypal' ); ?>
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<input type='hidden' id='dnt_tab_paypal' name='dnt_tab_paypal' value='1' />
										</table>
									</td>
									<td>
										<div class='dnt_output_block_paypal'>
											<?php dnt_display_output_block( 'paypal' ); ?>
										</div>
									</td>
								</tr>
								<tr id='dnt_shortcode_options_co' <?php echo $dnt_tab_co; ?>>
									<td>
										<!--2CO-->
										<table class="form-table">
											<tr>
												<th class='dnt_row dnt_account_row' scope="row">
													<?php _e( 'Your 2CO account ID:', 'donate' ); ?>
												</td>
												<td class='dnt_account_row'>
													<input type='number' name='dnt_co_account' id='dnt_co_account' value="<?php if ( null != $dnt_options['co_options']['co_account'] ) echo $dnt_options['co_options']['co_account']; ?>" />
												</td>
											</tr>
											<tr>
												<th class='dnt_row dnt_account_row' scope="row">
													<?php _e( 'Quantity:', 'donate' ); ?>
												</td>
												<td class='dnt_account_row'>
													<input id='dnt_quantity_donate' type='number' name='dnt_quantity_donate' value="<?php if ( null != $dnt_options['co_options']['co_quantity'] ) echo $dnt_options['co_options']['co_quantity']; ?>" />
												</td>
											</tr>
											<tr>
												<th class='dnt_row dnt_account_row' scope="row">
													<?php _e( 'Product ID:', 'donate' ); ?>
												</td>
												<td class='dnt_account_row'>
													<input type='number' name='dnt_product_id' id='dnt_product_id' value="<?php if ( null != $dnt_options['co_options']['product_id'] ) echo $dnt_options['co_options']['product_id']; ?>" />
												</td>
											</tr>
											<tr>
												<th class='dnt_row' scope="row">
													<?php _e( 'Button type:', 'donate' ); ?>
												</td>
												<td>
													<label class='dnt_checkers'>
														<input id='dnt_default_co' class='dnt_elements' type='radio' value='default' name='dnt_button_choice_co' <?php if ( '1' == $style_co ) echo 'checked="checked"'; ?> /> <?php _e( 'Default button', 'donate' ); ?>
													</label>
													<ul>
														<li>
															<label class='dnt_checkers dnt_elements_disabled_co'>
																<input id='dnt_small_co' class='dnt_elements dnt_elements_disabled_co' type='checkbox' value='small' name='dnt_check_choice_co' <?php if ( '1' == $choice_check_co ) echo 'checked="checked"'; ?> /> <?php _e( 'Use small image', 'donate' ); ?>
															</label>
														</li>
														<li>
															<label class='dnt_checkers dnt_elements_disabled_co'>
																<input id='dnt_credits_co' class='dnt_elements dnt_elements_disabled_co' type='checkbox' value='credits' name='dnt_check_choice_co' <?php if ( '2' == $choice_check_co ) echo 'checked="checked"'; ?> /> <?php _e( 'Use credit card image', 'donate' ); ?>
															</label>
														</li>
													</ul>
												</td>
											</tr>
											<tr>
												<td></td>
												<td>
													<label class='dnt_checkers'>
														<input id='dnt_custom_co' class='dnt_elements' type='radio' value='custom' name='dnt_button_choice_co' <?php if ( '2' == $style_co ) echo 'checked="checked"'; ?> /> <?php _e( 'Custom button', 'donate' ); ?>
													</label>
												</td>
											</tr>
											<tr>
												<td></td>
												<td>
													<table class='dnt_local_upload_co'>
														<tr>
															<td>
																<div class='dnt_local_box'>
																	<label class='dnt_checker_loc dnt_elements'><input id='dnt_local_co' class='dnt_elements' type='radio' value='local' name='dnt_button_custom_choice_co' /> <?php _e( 'Use image from file', 'donate' ); ?></label>
																	<br />
																	<input type='file' name='dnt_custom_local_co' class='dnt_elements' />
																</div>
																<p class='dnt_size_notice'><?php _e( 'The size of the image you upload must be no more than 170x70 and no smaller than 16x16', 'donate' ); ?></p>
																<p class='dnt_size_notice'><?php _e( 'You can upload only image files', 'donate' ); ?> (.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)</p>
															</td>
														</tr>
														<tr>
															<td>
																<div class='dnt_url_box'>
																	<label class='dnt_checker_url dnt_elements'><input id='dnt_url_co' class='dnt_elements' type='radio' value='url' name='dnt_button_custom_choice_co' /> <?php _e( 'Use image from URL', 'donate' ); ?></label>
																	<br />
																	<input type='text' name='dnt_custom_url_co' id='dnt_custom_url_co' class='dnt_elements' />
																</div>
																<p class='dnt_size_notice'><?php _e( 'The size of the image you upload must be no more than 170x70 and no smaller than 16x16', 'donate' ); ?></p>
																<p class='dnt_size_notice'><?php _e( 'You can upload only image files', 'donate' ); ?> (.png, .jpg, .jpeg, .gif, .bmp, .ico, .tif, .tiff, .jpe, .svg, .svgz)</p>
															</td>
														</tr>
														<tr>
															<td class='dnt_custom_buttons'>
																<?php dnt_display_custom_buttons( 'co' ); ?>
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<input type='hidden' id='dnt_tab_co' name='dnt_tab_co' value='0' />
										</table>
									</td>
									<td>
										<div class='dnt_output_block_co'>
											<?php dnt_display_output_block( 'co' ); ?>
										</div>
									</td>
								</tr>
							</table>
							<p class="submit">
								<input type='submit' name='option_form' value='<?php _e( "Save changes", "donate" ); ?>' class='button-primary' />
								<?php wp_nonce_field( plugin_basename( __FILE__ ), 'dnt_check_field' ) ?>	
							</p>
						</form>					
					</td>
				</tr>
			</table>
			<?php bws_plugin_reviews_block( $dnt_plugin_info['Name'], 'donate-button' ); ?>
		</div>
	<?php }
}

/* Add shortcode */
if ( ! function_exists ( 'dnt_user_shortcode' ) ) {
	function dnt_user_shortcode( $atts ) {
		global $dnt_options;;
		extract( shortcode_atts( array(
			'type'		=>	'',
			'count'		=>	'',
			'payment'	=>	'',
			'id'		=>	''
		), $atts ) );

		/* Display buttons what we need */
		if ( ( isset( $atts['payment'] ) ) && ( 'paypal' == $atts['payment'] ) ) {
			$dnt_shortcode_return = "<form action='https://www.paypal.com/cgi-bin/webscr' method='post' target='paypal_window'>";
				if ( 'default' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt_paypal_button' src=" . plugins_url( 'images/paypal-default.png', __FILE__ ) ." alt='paypal button' />";
				} elseif ( 'default-small' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt_paypal_button' src=" . plugins_url( 'images/paypal-small.png', __FILE__ ) . " alt='small_button_LOL' />";
				} elseif ( 'default-credits' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt_paypal_button' src=" . plugins_url( 'images/paypal-credits.png', __FILE__ ) . " alt='paypal-credits' />";
				} elseif ( ( 'custom' == $atts['type'] ) && ( $atts['id'] == $dnt_options['paypal_options']['image_paypal_id'] ) ) {
					$dnt_shortcode_return .= '<input type="image" src="' . $dnt_options['paypal_options']['img'] . '"alt="custom-button-paypal" />';
				}
				ob_start();
				dnt_draw_paypal_form();
				$out_paypal_form = ob_get_contents();
				ob_end_clean();
				$dnt_shortcode_return .= $out_paypal_form;
				$dnt_shortcode_return .= "</form>";
		}
		if ( ( isset( $atts['count'] ) ) && ( ( 2 == $atts['count'] ) && ( 'default' == $atts['type'] ) ) ) {
			$dnt_shortcode_return = "<div class='dnt_donate_button dnt_noscript_button'><img src=" . plugins_url( 'images/donate-button.png', __FILE__ ) . " alt='donate button' />";
			ob_start();
			dnt_options_box();
			$out_options_box = ob_get_contents();
			ob_end_clean();
			$dnt_shortcode_return .= $out_options_box;
			$dnt_shortcode_return .= "</div>";
		}
		if ( ( isset( $atts['payment'] ) ) && ( 'co' == $atts['payment'] ) ) {
			$dnt_shortcode_return = "<form action='https://www.2checkout.com/checkout/purchase' method='post' target='co_window'>";
				if ( 'default' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt_co_button' src=" . plugins_url( 'images/co-default.png', __FILE__ ) . " alt='co button' />";
				} elseif ( 'default-small' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt_co_button' src=" . plugins_url( 'images/co-small.png', __FILE__ ) . " alt='co small button' />";
				} elseif ( 'default-credits' == $atts['type'] ) {
					$dnt_shortcode_return .= "<input type='image' class='dnt_co_button' src=" . plugins_url( 'images/co-credits.png', __FILE__ ) . " alt='paypal-credits' />";
				} elseif ( ( 'custom' == $atts['type'] ) && ( $atts['id'] == $dnt_options['co_options']['image_co_id'] ) ) {
					$dnt_shortcode_return .= '<input type="image" src="' . $dnt_options['co_options']['img'] . '"alt="custom-button-co" />';
				}
				ob_start();
				dnt_draw_co_form();
				$out_co_form = ob_get_contents();
				ob_end_clean();
				$dnt_shortcode_return .= $out_co_form;
			$dnt_shortcode_return .= "</form>";
		}
		return $dnt_shortcode_return;
	}
}

/* Delete options db ( Uninstall ) */
if ( ! function_exists ( 'dnt_delete_options' ) ) {
	function dnt_delete_options() {
		delete_option( 'dnt_options' );
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
	}
}

add_action( 'admin_menu', 'dnt_add_admin_menu' );
add_action( 'admin_init', 'dnt_admin_init' );
add_action( 'init', 'dnt_init' );
add_action( 'admin_enqueue_scripts', 'dnt_plugin_stylesheet' );
add_action( 'wp_enqueue_scripts', 'dnt_plugin_stylesheet' );
add_action( 'widgets_init', 'dnt_register_widget' );

add_shortcode( 'donate', 'dnt_user_shortcode' );
/* Links in admin menu */
add_filter( 'plugin_row_meta', 'dnt_register_plugin_links', 10, 2 );
add_filter( 'plugin_action_links', 'dnt_plugin_action_links', 10, 2 );
add_filter( 'widget_text', 'do_shortcode' );

register_uninstall_hook( __FILE__, 'dnt_delete_options' );
?>