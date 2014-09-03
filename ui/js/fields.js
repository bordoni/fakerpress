// Simple Select2 Fields
( function( $, _ ){
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
		})
		.on( 'change', function( event ) {
			var data = $( this ).data( 'value' );

			if ( event.added ){
				if ( _.isArray( data ) ) {
					data.push( event.added );
				} else {
					data = [ event.added ];
				}
			} else {
				if ( _.isArray( data ) ) {
					data = _.without( data, event.removed );
				} else {
					data = [];
				}
			}
			$( this ).data( 'value', data ).attr( 'data-value', JSON.stringify( data ) );
		} );

		$( '.field-date-selection' ).each(function(){
			var $select = $(this);
			$select.select2({ width: 200 });
		});
	});
}( window.jQuery, window._ ) );

// Tagged Select2 Fields
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-select2-tags' ).each(function(){
			var $select = $(this);

			$select.select2({
				multiple: true,
				width: 400,
				tags: $select.data('tags'),
				tokenSeparators: [',']
			});
		});
	});
}( window.jQuery, window._ ) );

//Author fields 
( function( $ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-select2-author' ).each(function(){
			var $select = $(this);

			$select.select2({
				width: 400,
				multiple: true,
				allowClear: true,
				escapeMarkup: function (m) { return m; },
				formatSelection: function ( author ){
					return _.template('<abbr title="<%= ID %>: <%= data.user_email %>"><%= roles %>: <%= data.display_name %></abbr>')( author )
				},
				formatResult: function ( author ){
					return _.template('<abbr title="<%= ID %>: <%= data.user_email %>"><%= roles %>: <%= data.display_name %></abbr>')( author )
				},
				ajax: {
					dataType: 'json',
					type: 'POST',
					url: window.ajaxurl,
					data: function ( author, page ) {
						return {
							action: 'fakerpress.search_authors',
							search: author, // search author
							page_limit: 10,
							page: page,
						};
					},
					results: function ( data ) { // parse the results into the format expected by Select2.
						$.each( data.results, function( k, result ){
							result.id = result.data.ID;
							result.text = result.data.display_name;
						} );
						return data;
					}
				}
			});
		});
	});
}( jQuery ) );

//Quantity Range Fields
( function( $ ){
	'use strict';
	$(document).ready(function(){
		$( '.fakerpress_qty_range' ).each(function(){
			var $minField = $( '.qty-range-min' ),
				$maxField = $( '.qty-range-max' ),
				$container = $(this);

				$minField.on({
					'change keyup': function(e){
						if ( $.isNumeric( $(this).val() ) && $(this).val() > 0 ) {
							$maxField.removeAttr( 'disabled' );
							if( $maxField.val() && $(this).val() >= $maxField.val() )
								$(this).val( '' );

						} else {
							$(this).val( '' );
						}

					}
				}),
				$maxField.on({
					'change': function(e){
						if ( $.isNumeric( $(this).val() ) && $(this).val() > $minField.val() ) {
						} else {
							$maxField.val( '' );
						}
					}
				})
		})
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

// Post Query for Select2
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-select2-posts' ).each(function(){
			var $select = $(this);
			$select.select2({
				width: 400,
				multiple: true,
				data: {results:[]},
				allowClear: true,
				escapeMarkup: function (m) { return m; },
				formatSelection: function ( post ){
					return _.template('<abbr title="<%= post_title %>"><%= post_type.labels.singular_name %>: <%= ID %></abbr>')( post )
				},
				formatResult: function ( post ){
					return _.template('<abbr title="<%= post_title %>"><%= post_type.labels.singular_name %>: <%= ID %></abbr>')( post )
				},
				ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
					dataType: 'json',
					type: 'POST',
					url: window.ajaxurl,
					data: function (search, page) {
						return {
							action: 'fakerpress.query_posts',
							query: {
								s: search,
								posts_per_page: 10,
								paged: page,
								post_type: _.pluck( _.where( $( '.field-post_type.select2-offscreen' ).data( 'value' ), { hierarchical: true } ) , 'id' )
							}
						};
					},
					results: function ( data ) { // parse the results into the format expected by Select2.
						$.each( data.results, function( k, result ){
							result.id = result.ID;
						} );
						return data;
					}
				},
			});
		});
	});
}( jQuery, _ ) );

// Check if Post Type is hierarchical
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		$( '.field-post_type' ).on( 'change', function( event ){
			var $field = $(this),
				$field_PostType = $field.parents( '.form-table' ).find( '.field-container-post_parent' );

			if ( ! _.isUndefined( event.added ) && event.added.hierarchical === true && $field_PostType.length === 0 ){
				$field.parents( '.field-container-post_type' ).after( $field.data( 'post_parent' ) );
			} else if ( ! _.isUndefined( event.removed ) && event.removed.hierarchical === true && $field_PostType.length !== 0 ){
				$field.data( 'post_parent', $field_PostType.detach() );
			}
		} ).each(function(){
			var $field = $(this);

			if ( ! $field.hasClass( 'select2-offscreen' ) ){
				return;
			}
			$field.data( 'post_parent', $field.parents( '.form-table' ).find( '.field-container-post_parent' ).detach() );
		});
	});
}( window.jQuery, window._ ) );

// Check for checkbox dependecies
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		var checkDependency = function( event ){
			var $box, $dependecyField;
			if ( _.isNumber( event ) ){
				$box = $( this );
				$dependecyField = $( $box.data('fkDepends') );
			} else {
				$dependecyField = $( this );
				$box = $dependecyField.data( 'fkDependent' );
			}

			var	condition = $box.data('fkCondition'),
				$placeholder = $dependecyField.data( 'fkPlaceholder' );

			if ( ! $placeholder ){
				$placeholder = $( "<div>" ).attr( 'id', _.uniqueId( 'fk-dependent-placeholder-' ) );
				$dependecyField.data( 'fkPlaceholder', $placeholder );
			}
			$dependecyField.data( 'fkDependent', $box );

			if ( _.isNumber( event ) ){
				$dependecyField.on( 'change', checkDependency );
			}

			if ( $dependecyField.is(':checked') != condition ){
				$box.after( $placeholder ).detach();
			} else if ( $placeholder.is(':visible') ) {
				$placeholder.replaceWith( $dependecyField.data( 'fkDependent' ) );
			}
		};

		$( '.fk-field-dependent' ).each( checkDependency );
	});
}( window.jQuery, window._ ) );

