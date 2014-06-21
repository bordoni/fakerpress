// Taxonomies Fields
( function( $ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-select2-simple' ).each(function(){
			var $select = $(this);

			$select.select2({
				multiple: true,
				width: 400,
				data: function(){
					return { 'results': $select.data( 'possibilities' ) };
				}
			});
		});
	});
}( jQuery ) );