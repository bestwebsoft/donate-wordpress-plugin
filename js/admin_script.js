( function( $ ) {
	/* Check radio button via clicking on span under it */
	/*Local/URL upload box*/
	function dnt_upload_checker( payment ) {
		$( 'input[name="dnt_custom_local_' + payment + '"]' ).click( function() {
			$( 'input[name="dnt_button_custom_choice_' + payment + '"]' ).filter( '[value="local"]' ).attr( 'checked', true );
		});
		$( 'input[name="dnt_custom_url_' + payment + '"]' ).click( function() {
			$( 'input[name="dnt_button_custom_choice_' + payment + '"]' ).filter( '[value="url"]' ).attr( 'checked', true );
		});
	}

	$( document ).ready( function() {
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