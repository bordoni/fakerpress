( function ( $ ) {
	'use strict';
	// Stub: conditional field logic was never implemented. It only reads
	// each element's `data-condition` and does nothing with it yet.
	$.fn.conditional = function () {
		this.each( function ( index, element ) {
			$( element ).data( 'condition' );
		} );
	};

	$( document ).ready( function () {
		$( '.fp-conditional' ).conditional();
	} );
} )( window.jQuery );
