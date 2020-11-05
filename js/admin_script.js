( function( $ ) {
	$(document).ready( function() {
		$( '.add_media' ).on( 'click', function() {
			var currentParent = $( this ).parents( 'td' );
			if ( this.window === undefined ) {
				this.window = wp.media({
					title: dnt_var.wp_media_title,
					library: { type: 'image' },
					multiple: false,
					button: { text: dnt_var.wp_media_button }
				});

				var self = this; /* Needed to retrieve our variable in the anonymous function below */
				this.window.on( 'select', function() {
					var all = self.window.state().get( 'selection' ).toJSON();
					currentParent.find( '.dnt-image' ).html( '<img src="' + all[0].url + '" /><span class="dnt-delete-image"><span class="dashicons dashicons-no-alt"></span></span>' );
					currentParent.find( '.dnt-image-id' ).val( all[0].id );
				});
			}
			this.window.open();
			return false;
		});
		$( '.dnt_settings_form' ).on( 'click', '.dnt-delete-image', function(){
			$( this ).parent().next().val( '' );
			$( this ).parent().html( '' );
		});
	});
})( jQuery );