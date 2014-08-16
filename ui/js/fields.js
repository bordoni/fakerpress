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

		$( '.field-date-selection' ).each(function(){
			var $select = $(this);
			$select.select2({ width: 200 });
		});
	});
}( jQuery ) );

// Date Fields
( function( $ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-datepicker' ).datepicker( {
			constrainInput: false
		} );

		var $min = $( '.field-min-date' ),
			$max = $( '.field-max-date' ),
			$intervals = $('.fakerpress-range-group').each(function(){
				var $interval = $(this).on({
					'change': function(e){
						var $this = $( this ),
							$minField = $interval.find( '.field-min-date' ),
							$maxField = $interval.find( '.field-max-date' ),
							min = $this.find(':selected').data('min'),
							max = $this.find(':selected').data('max');

						$minField.datepicker( 'option', 'maxDate', min ).val( min );
						$maxField.datepicker( 'option', 'minDate', max ).val( max );
					}
				})
			})
		$min.on({
			'change': function(e){
				$( this ).next( $max ).datepicker( 'option', 'minDate', $( this ).val() );
			}
		}),
		$max.on({
			'change': function(e){
				$( this ).next( $min ).datepicker( 'option', 'maxDate', $( this ).val() );
			}
		})
	});
}( jQuery ) );

// Terms Fields
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-select2-terms' ).each(function(){
			var $select = $(this);

			$select.select2({
				width: 400,
				multiple: true,
				data: {results:[]},
				initSelection : function (element, callback) {
					callback(element.data( 'selected' ));
				},
				allowClear: true,
				ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
					dataType: 'json',
					type: 'POST',
					url: window.ajaxurl,
					data: function (term, page) {
						return {
							action: 'fakerpress.search_terms',
							search: term, // search term
							page_limit: 10,
							page: page,
							post_type: null
						};
					},
					results: function ( data ) { // parse the results into the format expected by Select2.
						$.each( data.results, function( k, result ){
							result.text = _.template('<%= tax %>: <%= term %>')( { tax: data.taxonomies[result.taxonomy].labels.singular_name, term: result.name } );
							result.id = result.term_id;
						} );
						return data;
					}
				},
			});
		});
	});
}( jQuery, _ ) );
