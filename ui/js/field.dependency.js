( function( $, _ ){
	'use strict';
	$.fn.conditional = function( _opt ) {
		var $elements = this,
			defaults = {
				conditional: function(  ) {
					return false;
				}
			},
			opt = $.extend( defaults, _opt );

		$elements.each( function( index, element ) {
			var $element = $( element ),
				$condition = $element.data( 'condition' );

		} );

	};

	$(document).ready(function(){
		$( '.fp-conditional' ).conditional();
	});
}( window.jQuery, window._ ) );