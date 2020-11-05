( function( $ ) {
	$(document).ready( function() {
		$( '.dnt-box' ).hide();
		/* Display_pay_options */
		$( document ).on( 'click', '.dnt-button > img', function() {
			$( this ).hide();
			$( this ).closest( '.dnt-button' ).children( '.dnt-box' ).show();
		} );
		$( document ).on( 'click', '#dnt_co_button, #dnt_paypal_button', function() {
			$( this ).closest( '.dnt-button' ).children( '.dnt-box' ).hide();
			$( '.dnt-button > img' ).show();
		} );
		/* New window */
		$( document ).on( 'click', '.dnt-co-button, #dnt_co_button', function() {
			window.open( '', 'co_window' );
		});
		$( document ).on( 'click', '.dnt-paypal-button, #dnt_paypal_button', function() {
			window.open( '', 'paypal_window' );
		});
	});
})( jQuery );