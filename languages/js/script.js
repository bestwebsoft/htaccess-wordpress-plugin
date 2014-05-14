(function($) {
	$(document).ready( function() {
		/* add notice about changing in the settings page */
		$( '#htccss_settings_form textarea, #htccss_settings_form input' ).bind( "change", function() {
			if ( $( this ).attr( 'type' ) != 'submit' ) {
				$( '.updated.fade' ).css( 'display', 'none' );
				$( '#htccss_settings_notice' ).css( 'display', 'block' );
			};
		});
	});
})(jQuery);