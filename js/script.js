( function( $ ) {
	$( document ).ready( function() {
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
	});
})( jQuery );