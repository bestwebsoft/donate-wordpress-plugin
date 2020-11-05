<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Dnt_Settings_Tabs' ) ) {
    class Dnt_Settings_Tabs extends Bws_Settings_Tabs {
        private $paypal_currency;

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
            global $dnt_options, $dnt_plugin_info;

            $tabs = array(
                'settings'		=> array( 'label' => __( 'Settings', 'donate-button' ) ),
                'misc'			=> array( 'label' => __( 'Misc', 'donate-button' ) ),
                'custom_code'	=> array( 'label' => __( 'Custom Code', 'donate-button' ) ),
            );
            parent::__construct( array(
                'plugin_basename'	=> $plugin_basename,
                'plugins_info'		=> $dnt_plugin_info,
                'prefix'			=> 'dnt',
                'default_options'	=> dnt_get_options_default(),
                'options'			=> $dnt_options,
                'tabs'				=> $tabs,
                'wp_slug'			=> 'donate-button'
            ) );

            add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
            add_action( get_parent_class( $this ) . '_display_second_postbox', array( $this, 'display_second_postbox' ) );

            $this->paypal_currency = array(
                'AUD' => 'Australian Dollar',
                'BRL' => 'Brazilian Real',
                'CAD' => 'Canadian Dollar',
                'CHF' => 'Swiss Franc',
                'CZK' => 'Czech Koruna',
                'DKK' => 'Danish Krone',
                'EUR' => 'Euro',
                'GBP' => 'British Pound',
                'HKD' => 'Hong Kong Dollar',
                'HUF' => 'Hungarian Forint',
                'ILS' => 'Israeli New Shekel',
                'JPY' => 'Japanese Yen',
                'MXN' => 'Mexican Peso',
                'MYR' => 'Malaysian Ringgit',
                'NOK' => 'Norwegian Krone',
                'NZD' => 'New Zealand Dollar',
                'PHP' => 'Philippine Peso',
                'PLN' => 'Polish Zloty',
                'RUB' => 'Russian Ruble',
                'SEK' => 'Swedish Krona',
                'SGD' => 'Singapore Dollar',
                'THB' => 'Thai Baht',
                'TWD' => 'New Taiwan Dollar',
                'USD' => 'U.S. Dollar',
            );
        }

        /**
         * Save plugin options to the database
         * @access public
         * @param  void
         * @return array    The action results
         */
        public function save_options() {
            $message = $notice = $error = '';

            /* Creating a massive with possible currencies */

            /* PayPal save options */
            $this->options['paypal_options']['paypal_display']       = isset( $_POST['dnt_paypal_display'] ) ? 1 : 0;
            $this->options['paypal_options']['paypal_image_option']  = ( isset( $_REQUEST['dnt_paypal_display_option'] ) && 'custom' == $_REQUEST['dnt_paypal_display_option'] && ! empty( $_REQUEST['dnt_paypal_image_custom'] ) ) ? 'custom' : 'default';

            if ( is_email( $_POST['dnt_paypal_account'] ) ) {
                $this->options['paypal_options']['paypal_account'] = sanitize_email( $_POST['dnt_paypal_account'] );
            } else {
                $error .= __( 'Paypal Account error: Please enter a valid email address', 'donate-button' ) . '<br />';
                $this->options['paypal_options']['paypal_account'] = '';
            }

            if ( isset( $_POST['dnt_paypal_amount'] ) ) {
                $this->options['paypal_options']['paypal_amount'] = number_format( floatval( $_POST['dnt_paypal_amount'] ), 2, ".", '' );
                if ( 0 > $this->options['paypal_options']['paypal_amount'] ) {
                    $error .= __( 'The amount must be a positive number.', 'donate-button' ) . '<br />';
                }
            } else {
                $this->options['paypal_options']['paypal_amount'] = '1.00';
            }

            $this->options['paypal_options']['paypal_currency'] = array_key_exists( $_POST['dnt_paypal_currency'], $this->paypal_currency ) ? $_POST['dnt_paypal_currency'] : 'USD';

            if ( ! empty( $_POST['dnt_paypal_purpose'] ) ) {
                $this->options['paypal_options']['paypal_purpose'] = sanitize_text_field( $_POST['dnt_paypal_purpose'] );
            } else {
                $this->options['paypal_options']['paypal_purpose'] = '';
            }

                /* 2CO save options */
            $this->options['co_options']['co_display']         = isset( $_POST['dnt_co_display'] ) ? 1 : 0;
            $this->options['co_options']['co_image_option']   = ( isset( $_REQUEST['dnt_co_display_option'] ) && 'custom' == $_REQUEST['dnt_co_display_option'] && ! empty( $_REQUEST['dnt_co_image_custom'] ) ) ? 'custom' : 'default';
            $this->options['co_options']['co_account'] = absint( $_POST['dnt_co_account'] );
            $this->options['co_options']['co_quantity'] = ( ! empty( $_POST['dnt_quantity_donate'] ) ) ? absint( $_POST['dnt_quantity_donate'] ) : $this->options['co_options']['co_quantity'] = '';

            $donate_system = array( 'paypal', 'co' );
            foreach ( $donate_system  AS $value ) {
                if ( isset( $_REQUEST['dnt_' . $value . '_image_custom'] ) && 'custom' == $_REQUEST['dnt_' . $value . '_display_option'] ) {
                    if ( ! empty( $_REQUEST['dnt_' . $value . '_image_custom'] ) ) {
                        $max_image_width	= 170;
                        $max_image_height	= 70;
                        $min_image_width    = 16;
                        $min_image_height   = 16;
                        $valid_types 		= array( 'jpg', 'jpeg', 'png' );
                        $attachment_id = intval( $_REQUEST['dnt_' . $value . '_image_custom'] );
                        $metadata = wp_get_attachment_metadata( $attachment_id );
                        $filename = pathinfo( $metadata['file'] );

                        if ( in_array( $filename['extension'], $valid_types ) ) {
                            if ( ( $metadata['width'] <= $max_image_width ) && ( $metadata['height'] <= $max_image_height ) && ( $metadata['width'] >= $min_image_width ) && ( $metadata['height'] >= $min_image_height ) ) {
                                $this->options[$value . '_options']['img'] = $attachment_id;
                            } else {
                                if ( $metadata['width'] < $min_image_width || $metadata['height'] < $min_image_height ) {
                                    $this->options[$value . '_options'][$value . '_image_option'] = 'default';
                                    $error = __( 'Uploaded file smaller than 16x16', 'donate-button' ) . '<br />';
                                } else {
                                    $this->options[$value . '_options'][$value . '_image_option'] = 'default';
                                    $error = __( 'Uploaded file bigger then 170x70', 'donate-button' ) . '<br />';
                                }
                            }
                        } else {
                            $this->options[$value . '_options'][$value . '_image_option'] = 'default';
                            $error	= __( "Error: Invalid file type", 'donate-button' ) . '<br />';
                        }
                    } else {
                        $this->options[$value . '_options']['img'] = '';
                    }
                }
            }

            if ( empty( $error ) ) {
                $message = __( 'Changes saved', 'donate-button' );
                update_option( 'dnt_options', $this->options );
            }
            return compact( 'message', 'notice', 'error' );
        }

        public function tab_settings() { ?>
            <h3 class="bws_tab_label"><?php _e( 'Donate Settings', 'donate-button' ); ?></h3>
            <?php $this->help_phrase(); ?>
            <hr>
            <div class="bws_tab_sub_label"><?php _e( 'General', 'donate-button' ); ?></div>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Buttons', 'donate-button' ); ?></th>
                    <td>
                        <label><input type='checkbox' name="dnt_paypal_display" value="1"<?php checked( $this->options['paypal_options']['paypal_display'] ); ?> class='bws_option_affect' data-affect-show='.dnt-paypal-display' ><?php _e( 'PayPal', 'donate-button' ); ?></label>
                        <div class="bws_info">
                            <?php printf( __( 'Enable to display a PayPal button.', 'donate-button' ), 0 ); ?>
                        </div><br />
                        <label><input type='checkbox' name="dnt_co_display" value="1"<?php checked( $this->options['co_options']['co_display'] ); ?> class="bws_option_affect" data-affect-show=".dnt-2co-display"  ><?php _e( '2CO', 'donate-button' ); ?></label>
                        <div class="bws_info">
                            <?php printf( __( 'Enable to display a 2Checkout button.', 'donate-button' ), 0 ); ?>
                        </div>
                    </td>
                </tr>
            </table>
            <!--PayPal-->
            <div class="dnt-paypal-display dnt-paypal-block">
                <div class="bws_tab_sub_label"><?php _e( 'PayPal', 'donate-button' ); ?></div>
                <table class="dnt_settings_form form-table" >
                    <tr>
                        <th scope="row"><?php _e( 'PayPal Email Address', 'donate-button' ); ?></th>
                        <td>
                            <label><input type='text' name='dnt_paypal_account' id='dnt_paypal_account' value="<?php echo $this->options['paypal_options']['paypal_account']; ?>" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Donation Purpose', 'donate-button' ); ?></th>
                        <td>
                            <label><input type='text' id='dnt_paypal_purpose' name='dnt_paypal_purpose' value="<?php if ( null != $this->options['paypal_options']['paypal_purpose'] ) echo $this->options['paypal_options']['paypal_purpose']; ?>" /></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Amount', 'donate-button' ); ?></th>
                        <td>
                            <label><input type='number' min="0" step="0.01" id='dnt_paypal_amount' name='dnt_paypal_amount' value="<?php if ( null != $this->options['paypal_options']['paypal_amount'] ) echo $this->options['paypal_options']['paypal_amount']; ?>" /></label>
                            <div class="bws_info">
                                <?php printf( __( 'Set "%d" to allow users to set donation amount.', 'donate-button' ), 0 ); ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Currency', 'donate-button' ); ?></th>
                        <td>
                            <select name="dnt_paypal_currency">
                                <?php foreach ( $this->paypal_currency as $key => $currency ) {
                                    echo '<option value="' . $key . '"';
                                    selected( $key, $this->options['paypal_options']['paypal_currency'] );
                                    echo '">' . $key . ' - ' . $currency . '</option>';
                                } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php _e( 'Button Image', 'donate-button' ); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="bws_option_affect" type="radio"  data-affect-hide=".dnt_paypal_option_custom" name="dnt_paypal_display_option" value="default" <?php checked( 'default', $this->options['paypal_options']['paypal_image_option'] ); ?> />
                                    <?php _e( 'Default', 'donate-button' ); ?>
                                </label><br />
                                <label>
                                    <input class="bws_option_affect" type="radio" data-affect-show=".dnt_paypal_option_custom"  name="dnt_paypal_display_option" value="custom" <?php checked( 'custom', $this->options['paypal_options']['paypal_image_option'] ); ?> />
                                    <?php _e( 'Custom', 'donate-button' ); ?>
                                </label><br />
                                <div class="dnt_paypal_option_custom">
                                    <div class="wp-media-buttons">
                                        <a href="#" class="button insert-media add_media hide-if-no-js"><span class="wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'donate-button' ); ?></a><br>
                                        <span class="bws_info">
                                            <?php printf( __( 'The size of the image you upload must be no more than %s and no smaller than %s.', 'donate-button' ), '170x70', '16x16' ); ?><br>
                                            <?php printf( __( 'You can upload only image types: %s.', 'donate-button' ), '"png", "jpg", "jpeg"' ); ?>
                                        </span>
                                    </div>
                                    <br />
                                    <div class="dnt-image">
                                        <?php if ( ! empty( $this->options['paypal_options']['img'] ) ) {
                                            /**
                                             * Update
                                             * @deprecated 2.1.7
                                             * @todo Update after 22.07.2021
                                             */
                                            $url = is_int( $this->options['paypal_options']['img'] ) ? wp_get_attachment_url( $this->options['paypal_options']['img'] ) : $this->options['paypal_options']['img'];
                                            /* end todo */
                                            echo '<img src="' . $url . '" /><span class="dnt-delete-image"><span class="dashicons dashicons-no-alt"></span></span>';
                                        } ?>
                                    </div>
                                    <input class="dnt-image-id hide-if-js" type="text" name="dnt_paypal_image_custom" value="<?php if ( ! empty( $this->options['paypal_options']['img'] ) ) echo $this->options['paypal_options']['img']; ?>" />
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            <input type='hidden' id='dnt_tab_paypal' name='dnt_tab_paypal' value='1' />
            <div class="clear"></div>
            <!--2CO-->
            <div class="dnt-2co-display dnt-co2-block">
                <div class="bws_tab_sub_label"><?php _e( '2CO', 'donate-button' ); ?></div>
                <table class="dnt_settings_form form-table">
                    <tr>
                        <th scope="row"><?php _e( '2CO Account ID', 'donate-button' ); ?></th>
                        <td>
                            <input type='number' min="0" name='dnt_co_account' value="<?php if ( null != $this->options['co_options']['co_account'] ) echo $this->options['co_options']['co_account']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Quantity', 'donate-button' ); ?></th>
                        <td>
                            <input type='number' min="0" name='dnt_quantity_donate' value="<?php if ( null != $this->options['co_options']['co_quantity'] ) echo $this->options['co_options']['co_quantity']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Product ID', 'donate-button' ); ?></th>
                        <td>
                            <input type='number' min="0" name='dnt_product_id' value="<?php if ( null != $this->options['co_options']['product_id'] ) echo $this->options['co_options']['product_id']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <?php _e( 'Button Image', 'donate-button' ); ?>
                        </th>
                        <td>
                            <fieldset>
                                <label>
                                    <input class="bws_option_affect" type="radio"  data-affect-hide=".dnt_2co_option_custom" name="dnt_co_display_option" value="default" <?php checked( 'default', $this->options['co_options']['co_image_option'] ); ?> />
                                    <?php _e( 'Default', 'donate-button' ); ?>
                                </label><br />
                                <label>
                                    <input class="bws_option_affect" type="radio" data-affect-show=".dnt_2co_option_custom"  name="dnt_co_display_option" value="custom" <?php checked( 'custom', $this->options['co_options']['co_image_option'] ); ?> />
                                    <?php _e( 'Custom', 'donate-button' ); ?>
                                </label><br />
                                <div class="dnt_2co_option_custom">
                                    <div class="wp-media-buttons">
                                        <a href="#" class="button insert-media add_media hide-if-no-js"><span class="wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'donate-button' ); ?></a><br>
                                        <span class="bws_info">
                                            <?php printf( __( 'The size of the image you upload must be no more than %s and no smaller than %s.', 'donate-button' ), '170x70', '16x16' ); ?><br>
                                            <?php printf( __( 'You can upload only image types: %s.', 'donate-button' ), '"png", "jpg", "jpeg"' ); ?>
                                        </span>
                                    </div>
                                    <br />
                                    <div class="dnt-image">
                                        <?php if ( ! empty( $this->options['co_options']['img'] ) ) {
                                            /**
                                             * Update
                                             * @deprecated 2.1.7
                                             * @todo Update after 22.07.2021
                                             */
                                            $url = is_int( $this->options['co_options']['img'] ) ? wp_get_attachment_url( $this->options['co_options']['img'] ) : $this->options['co_options']['img'];
                                            /* end todo */
                                            echo '<img src="' . $url . '" /><span class="dnt-delete-image"><span class="dashicons dashicons-no-alt"></span></span>';
                                        } ?>
                                    </div>
                                    <input class="dnt-image-id hide-if-js" type="text" name="dnt_co_image_custom" value="<?php if ( ! empty( $this->options['co_options']['img'] ) ) echo $this->options['co_options']['img']; ?>" />
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            <input type='hidden' id='dnt_tab_co' name='dnt_tab_co' value='0' />
            <div class="clear"></div> <?php
        }

        /**
         * Display custom metabox
         * @access public
         * @param  void
         * @return array    The action results
         */
        public function display_metabox() { ?>
            <div class="postbox">
                <h3 class="hndle">
                    <?php _e( 'Donate Shortcode', 'donate-button' ); ?>
                </h3>
                <div class="inside">
                    <p><?php _e( 'Add Donate button to a widget.', 'donate-button' ); ?> <br> <a href="widgets.php"><?php _e( 'Navigate to Widgets', 'donate-button' ); ?></a></p>
                </div>
                <div class="inside">
                    <?php _e( "Add Donate button for both systems to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add Paypal default button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=paypal type=default]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add Paypal small button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=paypal type=default-small]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add Paypal credits button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=paypal type=default-credits]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add Paypal custom button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=paypal type=custom]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add 2CO default button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=co type=default]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add 2CO small button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=co type=default-small]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add 2CO credits button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=co type=default-credits]" ); ?>
                </div>
                <div class="inside">
                    <?php _e( "Add 2CO custom button to your posts or pages using the following shortcode:", 'donate-button' ); ?>
                    <?php bws_shortcode_output( "[donate payment=co type=custom]" ); ?>
                </div>
            </div>
        <?php }

        public function display_second_postbox() { ?>
            <div class="bws_tab postbox dnt-preview-box" >
				<h3 class="hndle">
					<?php _e( 'Preview', 'donate-button' ); ?>
				</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e( 'One button for both systems', 'donate-button' ); ?></th>
                        <td>
                            <div class='dnt-img-box'><img src='<?php echo plugins_url( '../images/donate-button.png', __FILE__ ); ?>' alt='donate-default' /></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Default button', 'donate-button' ); ?></th>
                        <td>
                            <span class='dnt-img-box'><img src='<?php echo plugins_url( '../images/paypal-default.png', __FILE__ ); ?>' alt='donate-default' /></span>
                            <span class='dnt-img-box'><img src='<?php echo plugins_url( '../images/co-default.png', __FILE__ ); ?>' alt='donate-default' /></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Small button', 'donate-button' ); ?></th>
                        <td>
                            <span class='dnt-img-box'><img src='<?php echo plugins_url( '../images/paypal-small.png', __FILE__ ); ?>' alt='donate-default' /></span>
                            <span class='dnt-img-box'><img src='<?php echo plugins_url( '../images/co-small.png', __FILE__ ); ?>' alt='donate-default' /></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Credit cards button', 'donate-button' ); ?></th>
                        <td>
                            <span class='dnt-img-box'><img src='<?php echo plugins_url( '../images/paypal-credits.png', __FILE__ ); ?>' alt='donate-default' /></span>
                            <span class='dnt-img-box'><img src='<?php echo plugins_url( '../images/co-credits.png', __FILE__ ); ?>' alt='donate-default' /></span>
                        </td>
                    </tr>
                </table>
			</div>
       <?php }
    }
}