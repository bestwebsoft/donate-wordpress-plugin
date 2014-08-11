( function( $ ) {
	/* Check radio button via clicking on span under it */
	/*Local/URL upload box*/
	function dnt_upload_checker( payment ) {
		$( '#dnt_shortcode_options_' +payment + ' .dnt_local_box' ).click( function() {
			$( '#dnt_local_' +payment ).attr( "checked", "checked" );
			$( '#dnt_url_' + payment ).removeAttr( "checked");
		} );
		$( '#dnt_shortcode_options_' + payment + ' .dnt_url_box' ).click( function() {
			$( '#dnt_url_' + payment ).attr( "checked", "checked" );
			$( '#dnt_local_' + payment ).removeAttr( "checked");
		} );
	}
	
	/*Check small/credits*/
	function dtn_checker( payment ) {
		$( '#dnt_small_' + payment ).click( function() {
			if ( $( "#dnt_small_" + payment ).attr( "checked" ) != "checked" ) {
				$( this ).removeAttr( 'checked' );
			} else {
				$( '#dnt_small_' + payment ).attr( 'checked', 'checked' );
				$( '#dnt_credits_' + payment ).removeAttr( 'checked' );
			}
		} );
		$( '#dnt_credits_' + payment ).click( function() {
			if ( $( "#dnt_credits_" + payment ).attr( "checked" ) != "checked" ) {
				$( this ).removeAttr( 'checked' );
			} else {
				$( '#dnt_credits_' + payment ).attr( 'checked', 'checked' );
				$( '#dnt_small_' + payment ).removeAttr( 'checked' );
			}
		} );
	}
	
	/*Regular Exp*/
	function dnt_valid( arg ) {
		$( arg ).blur( function() {
			if ( ! ( $( this ).val().match( /^\d+$/ ) ) ) {
				$( arg ).removeClass( 'default_border' ).removeClass( 'dnt_green_border' ).addClass( 'dnt_red_border' );
			} else {
				$( arg ).removeClass( 'dnt_red_border' ).removeClass( 'default_border' ).addClass( 'dnt_green_border' );
			}
		} );
		$( arg ).focus( function() {
			$( arg ).toggleClass( 'default_border' );
		} );
	}
	
	$( document ).ready( function() {
		/*For JavaScript*/
		$( 'form #dnt_shortcode_options_co, #dnt_shortcode_options_donate' ).addClass( 'dnt_hidden' );
		$( '#dnt_shortcode_options_co .dnt_output_block_co, #dnt_shortcode_options_paypal .dnt_output_block_paypal' ).addClass( 'dnt_position' );
		$( '#dnt_options_box' ).addClass( 'dnt_hidden' );
		$( '.dnt_paypal_image' ).parent().parent().removeClass( 'dnt_noscript_box' ).addClass( 'dnt_hidden' ).parent().removeClass( 'dnt_noscript_button' );
		/*Display_pay_options*/
		$( '.dnt_donate_button' ).click( function() {
			if ( $( this ).children( '#dnt_options_box' ).hasClass( 'dnt_hidden' ) ) {
				$( this ).children( '#dnt_options_box' ).removeClass( 'dnt_hidden' ).addClass( 'dnt_display' );
			} else {
				$( this ).children( '#dnt_options_box' ).removeClass( 'dnt_display' ).addClass( 'dnt_hidden' );
			}
		} );
		/*Tabs*/
		$( '#dnt_donate .nav-tab-wrapper' ).addClass( 'dnt_display' );
		$( '#dnt_settings_box .nav-tab-wrapper' ).children( '.nav-tab' ).first().toggleClass( 'nav-tab-active' );
		$( '#dnt_settings_box .nav-tab' ).click( function() {
			if ( ! $( this ).hasClass( 'nav-tab-active' ) && $( this ).index() != 2 ) {
				$( '#dnt_settings_box .nav-tab' ).removeClass( 'nav-tab-active' );
				$( this ).toggleClass( 'nav-tab-active' );
			}
		} );
		
		$( '.dnt_paypal_text' ).click( function() {
			$( '#dnt_shortcode_options_paypal' ).show();
			$( '#dnt_shortcode_options_co' ).hide();
		} );
		$( '.dnt_co_text' ).click( function() {
			$( '#dnt_shortcode_options_paypal' ).hide();
			$( '#dnt_shortcode_options_co' ).show();
		} );
		
		/*For One button donate (hide others)*/
		if ( $( '#dnt_button_donate' ).attr( 'checked' ) == 'checked' ) {
			$( '#dnt_noscript' ).removeClass( 'dnt_black_color' ).addClass( 'dnt_grey_color' ).find( '.dnt_elements' ).attr( 'disabled', 'disabled' );
		} else {
			$( '#dnt_noscript' ).removeClass( 'dnt_grey_color' ).addClass( 'dnt_black_color' ).find( '.dnt_elements' ).removeAttr( 'disabled' );
		}
		$( '#dnt_button_donate' ).click( function () {
			if ( $( ".dnt_elements" ).attr( 'disabled' ) == 'disabled' ) {
				$( '#dnt_noscript' ).removeClass( 'dnt_grey_color' ).addClass( 'dnt_black_color' ).find( '.dnt_elements' ).removeAttr( 'disabled' );
			} else {
				$( '#dnt_noscript' ).removeClass( 'dnt_black_color' ).addClass( 'dnt_grey_color' ).find( '.dnt_elements' ).attr( 'disabled', 'disabled' );
			}
		} );
		
		dtn_checker( 'paypal' );
		dtn_checker( 'co' );
		
		$( '.dnt_local_upload_paypal' ).hide();
		$( '.dnt_local_upload_co' ).hide();

		/*Show/Hide custom block*/
		$( '#dnt_custom_paypal' ).click( function() {
			$( '.dnt_local_upload_paypal' ).show();
			$( '.dnt_elements_disabled_paypal' ).hide();
		} );
		$( '#dnt_custom_co' ).click( function() {
			$( '.dnt_local_upload_co' ).show();
			$( '.dnt_elements_disabled_co' ).hide();
		} );
		
		$( '#dnt_default_paypal' ).click( function() {
			$( '.dnt_local_upload_paypal' ).hide();
			$( '.dnt_elements_disabled_paypal' ).show();
		} );
		$( '#dnt_default_co' ).click( function() {
			$( '.dnt_local_upload_co' ).hide();
			$( '.dnt_elements_disabled_co' ).show();
		} );
		
		if ( $( '#dnt_custom_paypal' ).attr( "checked" ) == "checked" ) {
			$( '.dnt_local_upload_paypal' ).show();
			$( '.dnt_elements_disabled_paypal' ).hide();
		}
		if ( $( '#dnt_custom_co' ).attr( "checked" ) == "checked" ) {
			$( '.dnt_local_upload_co' ).show();
			$( '.dnt_elements_disabled_co' ).hide();
		}
		
		if ( ( $( '#dnt_custom_paypal' ).attr( "checked" ) == "checked" ) || ( $( '#dnt_button_donate' ).attr( "checked" ) == "checked" ) ) {
			$( '#dnt_shortcode_options_paypal .postbox' ).hide();
		}
		if ( ( $( '#dnt_custom_paypal' ).attr( "checked" ) == "checked" ) && ( $( '#dnt_button_donate' ).attr( "checked" ) != "checked" ) ) {
			$( '#dnt_shortcode_options_paypal .postbox' ).show();
		}
		
		/*Block hide/show*/
		$( '.dnt_handlediv' ).click( function() {
			if ( $( '.dnt_custom_buttons .inside' ).is( ':visible' ) ) {
				$( '.dnt_custom_buttons .inside' ).removeClass( 'dnt_display' ).addClass( 'dnt_hidden' );
			} else {
				$( '.dnt_custom_buttons .inside' ).removeClass( 'dnt_hidden' ).addClass( 'dnt_display' );
			}
		} );
		
		dnt_upload_checker( 'paypal' );
		dnt_upload_checker( 'co' );

		/*Widget settings*/
		$( 'a#dnt_paypal_widget_tab' ).live( 'click', function() {
			if ( $( this ).parent().hasClass( 'tabs' ) ) {
				$( this ).parent().toggleClass( 'tabs' );
			}
			$( this ).parent().toggleClass( 'tabs' );
			$( this ).parent().next().children( 'a#dnt_co_widget_tab' ).parent().removeClass( 'tabs' );
			$( this ).parent().parent().next().removeClass( 'dnt_hidden' ).addClass( 'dnt_display' );
			$( this ).parent().parent().next().next().removeClass( 'dnt_display' ).addClass( 'dnt_hidden' );
		} );
		$( 'a#dnt_co_widget_tab' ).live( 'click', function() {
			if ( $( this ).parent().hasClass( 'tabs' ) ) {
				$( this ).parent().toggleClass( 'tabs' );
			}
			$( this ).parent().toggleClass( 'tabs' );
			$( this ).parent().prev().children( 'a#dnt_paypal_widget_tab' ).parent().removeClass( 'tabs' );
			$( this ).parent().parent().next().removeClass( 'dnt_display' ).addClass( 'dnt_hidden' );
			$( this ).parent().parent().next().next().removeClass( 'dnt_hidden' ).addClass( 'dnt_display' );
		} );
		
		if ( $( '.error' ).children( 'p' ).length == 0 ) {
			$( '.error' ).remove();
		}
		if ( $( '#dnt_shortcode_options_co .dnt_custom_buttons' ).children( 'div' ).length == 0 ) {
			$( '#dnt_shortcode_options_co .dnt_custom_buttons' ).addClass( 'dnt_none_border' );
		}
		if ( $( '#dnt_shortcode_options_paypal .dnt_custom_buttons' ).children( 'div' ).length == 0 ) {
			$( '#dnt_shortcode_options_paypal .dnt_custom_buttons' ).addClass( 'dnt_none_border' );
		}
		
		$( '#dnt_shortcode_options_paypal .dnt_local_box, #dnt_shortcode_options_paypal .dnt_url_box' ).click( function() {
			$( '#dnt_shortcode_options_paypal .dnt_custom_buttons_block').removeAttr( 'checked' )
		} );
		$( '#dnt_shortcode_options_co .dnt_local_box, #dnt_shortcode_options_co .dnt_url_box' ).click( function() {
			$( '#dnt_shortcode_options_co .dnt_custom_buttons_block' ).removeAttr( 'checked' )
		} );
		
		/*New window*/
		$( '.dnt_co_button, #dnt_co_button' ).click( function() {
			window.open( '', 'co_window' );
		} );
		$( '.dnt_paypal_button, #dnt_paypal_button' ).click( function() {
			window.open( '', 'paypal_window' );
		} );
		
		/*Widget disabling/enabling checkboxes*/
		$( '.dnt_widget_checkbox_donate' ).live( 'click', function() {
			if ( $( this ).attr( 'checked' ) == 'checked' ) {
				$('.dnt_tabs-panel-paypal input[type="radio"]').each(function(){ $(this).attr( 'disabled', 'disabled' ); });
				$('.dnt_tabs-panel-co input[type="radio"]').each(function(){ $(this).attr( 'disabled', 'disabled' ); });
			} else {
				$('.dnt_tabs-panel-paypal input[type="radio"]').each(function(){ $(this).removeAttr( 'disabled' ); });
				$('.dnt_tabs-panel-co input[type="radio"]').each(function(){ $(this).removeAttr( 'disabled' ); });
			}
		} );

		dnt_valid( '#dnt_co_account' );
		dnt_valid( '#dnt_quantity_donate' );
		dnt_valid( '#dnt_product_id' );
		
		/*Display active payment tab*/
		$( '.dnt_co_text' ).click( function() {
			if ( $( '#dnt_shortcode_options_co' ).hasClass( 'dnt_display' ) ) {
				/*Change hidden value*/
				$( '#dnt_tab_co' ).val( '1' );
				$( '#dnt_tab_paypal' ).val( '0' );
			}
		} );
		$( '.dnt_paypal_text' ).click( function() {
			if ( $( '#dnt_shortcode_options_paypal' ).hasClass( 'dnt_display' ) ) {
				$( '#dnt_tab_paypal' ).val( '1' );
				$( '#dnt_tab_co' ).val( '0' );
			}
		} );
		
		if ( $( '.dnt_co_text' ).parent().hasClass( 'dnt_display_tab' ) ) {
			$( '.dnt_paypal_text' ).parent().removeClass( 'nav-tab-active' );
			$( '.dnt_co_text' ).parent().addClass( 'nav-tab-active' );
			$( '#dnt_shortcode_options_co' ).show();
			$( '#dnt_shortcode_options_paypal' ).hide();
		} else if ( $( '.dnt_paypal_text' ).parent().hasClass( 'dnt_display_tab' ) ) {
			$( '.dnt_co_text' ).parent().removeClass( 'nav-tab-active' );
			$( '.dnt_paypal_text' ).parent().addClass( 'nav-tab-active' );
			$( '#dnt_shortcode_options_paypal' ).show();
			$( '#dnt_shortcode_options_co' ).hide();
		}
		
		/*Imitate click on tab*/
		$( '.button-primary' ).click( function() {
			if ( $( '#dnt_shortcode_options_co' ).hasClass( 'dnt_display' ) ) {
				$( '.dnt_co_text' ).trigger( 'click' );
			} else if ( $( '#dnt_shortcode_options_paypal' ).hasClass( 'dnt_display' ) ) {
				$( '.dnt_paypal_text' ).trigger( 'click' );
			}
		} );
		
		/*	$( '.wp-menu-image' ).find( 'img' ).remove(); */

		/* add notice about changing in the settings page */
		$( '#dnt_settings_form input' ).bind( "change click select", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#dnt_settings_notice' ).css( 'display', 'block' );
			};
		});
	} );
} )( jQuery );
