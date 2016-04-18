( function( $ ) {
	/* Check radio button via clicking on span under it */
	/*Local/URL upload box*/
	function dnt_upload_checker( payment ) {
		$( 'input[name="dnt_custom_local_' + payment + '"]' ).click( function() {
			$( 'input[name="dnt_button_custom_choice_' + payment + '"]' ).filter('[value="local"]').attr( 'checked', true );
		});
		$( 'input[name="dnt_custom_url_' + payment + '"]' ).click( function() {
			$( 'input[name="dnt_button_custom_choice_' + payment + '"]' ).filter('[value="url"]').attr( 'checked', true );
		});
	}
	
	$( document ).ready( function() {
		/* FRONTEND */		
		$( '.dnt_options_box' ).addClass( 'dnt_hidden' );
		/* Display_pay_options */
		$( '.dnt_donate_button' ).click( function() {
			if ( $( this ).children( '.dnt_options_box' ).hasClass( 'dnt_hidden' ) ) {
				$( this ).children( '.dnt_options_box' ).removeClass( 'dnt_hidden' );
			} else {
				$( this ).children( '.dnt_options_box' ).addClass( 'dnt_hidden' );
			}
		} );
		/* New window */
		$( '.dnt_co_button, #dnt_co_button' ).click( function() {
			window.open( '', 'co_window' );
		});
		$( '.dnt_paypal_button, #dnt_paypal_button' ).click( function() {
			window.open( '', 'paypal_window' );
		});

		/* ADMIN */
	
		/* Display active payment tab */
		if ( $( '.dnt_co_text' ).hasClass( 'nav-tab-active' ) ) {
			$( '#dnt_shortcode_options_paypal, .dnt_output_block_paypal' ).hide();
		} else if ( $( '.dnt_paypal_text' ).hasClass( 'nav-tab-active' ) ) {
			$( '#dnt_shortcode_options_co, .dnt_output_block_co' ).hide();
		} else {
			$( '.dnt_paypal_text' ).addClass( 'nav-tab-active' );
			$( '#dnt_shortcode_options_co, .dnt_output_block_co' ).hide();
		}

		$( '.dnt_paypal_text' ).click( function() {
			$( '#dnt_shortcode_options_paypal, .dnt_output_block_paypal' ).show();
			$( '#dnt_shortcode_options_co, .dnt_output_block_co' ).hide();
			$( '#dnt_tab_paypal' ).val( '1' );
			$( '#dnt_tab_co' ).val( '0' );
			$( '.dnt_tabs .nav-tab' ).removeClass( 'nav-tab-active' );
			$( this ).addClass( 'nav-tab-active' );
		});
		$( '.dnt_co_text' ).click( function() {
			$( '#dnt_shortcode_options_paypal, .dnt_output_block_paypal' ).hide();
			$( '#dnt_shortcode_options_co, .dnt_output_block_co' ).show();
			$( '#dnt_tab_co' ).val( '1' );
			$( '#dnt_tab_paypal' ).val( '0' );
			$( '.dnt_tabs .nav-tab' ).removeClass( 'nav-tab-active' );
			$( this ).addClass( 'nav-tab-active' );
		});
			
		dnt_upload_checker( 'paypal' );
		dnt_upload_checker( 'co' );				
	});
})( jQuery );