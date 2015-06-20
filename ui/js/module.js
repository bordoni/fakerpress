if ( 'undefined' === typeof window.fakerpress ){
	window.fakerpress = {};
}

( function( $, _ ){
	'use strict';

	// Setup the Selectors
	window.fakerpress._module_generator = '.fp-module-generator';

	$( document ).ready( function(){
		var $forms = $( window.fakerpress._module_generator ).each( function() {
			var $form = $( this ),
				$submit_container = $form.find( '.submit' );

			$form.on( 'submit', function ( event ) {

				event.preventDefault();
				return;
			} );
		} );
	} );
}( jQuery, _ ) );