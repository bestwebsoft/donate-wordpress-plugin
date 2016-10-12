( function( $ ) {
	$( document ).ready( function() {
		$( '.dnt_options_box' ).addClass( 'dnt_hidden' );
		/* Display_pay_options */
		$( document ).on( 'click', '.dnt_donate_button', function() {
			if ( $( this ).children( '.dnt_options_box' ).hasClass( 'dnt_hidden' ) ) {
				$( this ).children( '.dnt_options_box' ).removeClass( 'dnt_hidden' );
			} else {
				$( this ).children( '.dnt_options_box' ).addClass( 'dnt_hidden' );
			}
		} );
		/* New window */
		$( document ).on( 'click', '.dnt_co_button, #dnt_co_button', function() {
			window.open( '', 'co_window' );
		});
		$( document ).on( 'click', '.dnt_paypal_button, #dnt_paypal_button', function() {
			window.open( '', 'paypal_window' );
		});
	});
})( jQuery );