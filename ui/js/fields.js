if ( 'undefined' === typeof window.fakerpress ){
	window.fakerpress = {};
}
window.fakerpress.fields = {};
window.fakerpress.ready_class = 'fp-js-ready';
window.fakerpress.plugin = 'fakerpress';
window.fakerpress.abbr = 'fp';
window.fakerpress.fieldName = function( pieces ){
	return this.plugin + '[' + pieces.join( '][' ) + ']';
};

window.fakerpress.fieldId = function( pieces ){
	return this.plugin + '-field-' + pieces.join( '-' );
};

window.fakerpress.searchId = function ( e ) {
	var id = null;

	if ( 'undefined' !== typeof e.id ){
		id = e.id;
	} else if ( 'undefined' !== typeof e.ID ){
		id = e.ID;
	} else if ( 'undefined' !== typeof e.value ){
		id = e.value;
	}
	return e == undefined ? null : id;
};


// Date Fields
window.fakerpress.fields.dates = function( $, _ ){
	'use strict';
	var datepicker_args = {
			after: ['attr'],
			constrainInput: false,
			dateFormat: 'yy-mm-dd',
		};

	$( '.fp-type-date' ).not( '.hasDatepicker' ).datepicker( datepicker_args );
	$( '.fp-type-interval-wrap' ).each( function(){
		var $container = $( this ),
			$interval = $container.find( '.fp-type-dropdown' ),
			$min = $container.find( '[data-type="min"]' ),
			$max = $container.find( '[data-type="max"]' );

		if ( $container.hasClass( window.fakerpress.ready_class ) ){
			return;
		}
		$container.addClass( window.fakerpress.ready_class );

		$min.on({
			'change': function(e){
				$min.parents( '.fp-field-wrap' ).find( '[data-type="max"]' ).datepicker( 'option', 'minDate', $( this ).val() ).datepicker( 'refresh' );
				$( '.fp-type-date.hasDatepicker' ).datepicker( 'refresh' );
			}
		});

		$max.on({
			'change': function(e){
				$max.parents( '.fp-field-wrap' ).find( '[data-type="min"]' ).datepicker( 'option', 'maxDate', $( this ).val() ).datepicker( 'refresh' );
				$( '.fp-type-date.hasDatepicker' ).datepicker( 'refresh' );
			}
		});

		$interval.on({
			'change': function(e){
				var $selected = $interval.find(':selected'),
					min = $selected.attr('min'),
					max = $selected.attr('max');

				$min.datepicker( 'setDate', min );
				$max.datepicker( 'setDate', max );
			}
		}).trigger( 'change' );
	} );
};

// Select2 Fields
window.fakerpress.fields.dropdown = function( $, _ ){
	'use strict';
	var $elements = $( '.fp-type-dropdown' ).not( '.select2-offscreen, .select2-container' );
	$elements.each(function(){
		var $select = $(this),
			args = {
				width: 420
			};

		if ( $select.is( '[multiple]' ) ){
			args.multiple = true;

			if ( ! $select.is( '[data-tags]' ) ){
				args.data = function(){
					return { 'results': $select.data( 'options' ) };
				};
			}

			if ( ! _.isArray( $select.data( 'separator' ) ) ){
				args.tokenSeparators = [ $select.data( 'separator' ) ];
			} else {
				args.tokenSeparators = $select.data( 'separator' );
			}
			args.separator = $select.data( 'separator' );

			// Define the regular Exp based on
			args.regexSeparatorElements = [ '^(' ];
			args.regexSplitElements = [ '(?:' ];
			$.each( args.tokenSeparators, function ( i, token ){
				args.regexSeparatorElements.push( '[^' + token + ']+' );
				args.regexSplitElements.push( '[' + token + ']' );
			} );
			args.regexSeparatorElements.push( ')$' );
			args.regexSplitElements.push( ')' );

			args.regexSeparatorString = args.regexSeparatorElements.join( '' )
			args.regexSplitString = args.regexSplitElements.join( '' )

			args.regexToken = new RegExp( args.regexSeparatorString, 'ig');
			args.regexSplit = new RegExp( args.regexSplitString, 'ig');

			args.id = window.fakerpress.searchId;

		} else {
			args.width = 200;
		}

		args.matcher = function( term, text ) {
			var result = text.toUpperCase().indexOf( term.toUpperCase() ) == 0;

			if ( ! result && 'undefined' !== typeof args.tags ){
				var possible = _.where( args.tags, { text: text } );
				if ( args.tags.length > 0  && _.isObject( possible ) ){
					var test_value = window.fakerpress.searchId( possible[0] );
					result = test_value.toUpperCase().indexOf( term.toUpperCase() ) == 0;
				}
			}

			return result;
		};

		if ( $select.is( '[data-tags]' ) ){
			args.tags = $select.data( 'options' );

			args.initSelection = function ( element, callback ) {
				var data = [];
				$( element.val().split( args.regexSplit ) ).each( function () {
					var obj = { id: this, text: this };
					if ( args.tags.length > 0  && _.isObject( args.tags[0] ) ){
						var _obj = _.where( args.tags, { value: this } );
						if ( _obj.length > 0 ){
							obj = _obj[0];
							obj = {
								id: obj.value,
								text: obj.text,
							};
						}
					}

					data.push( obj );

				} );
				callback( data );
			};

			args.createSearchChoice = function(term, data) {
				if ( term.match( args.regexToken ) ){
					return { id: term, text: term };
				}
			};

			if ( 0 === args.tags.length ){
				args.formatNoMatches = function(  ){
					return $select.attr( 'placeholder' );
				};
			}
		}


		if ( $select.is( '[data-source]' ) ){
			var source = $select.data( 'source' );

			args.data = { results: [] };
			args.allowClear = true;

			args.escapeMarkup = function (m) {
				return m;
			};

			args.formatSelection = function ( post ){
				return _.template('<abbr title="<%= post_title %>"><%= post_type.labels.singular_name %>: <%= ID %></abbr>')( post )
			};
			args.formatResult = function ( post ){
				return _.template('<abbr title="<%= post_title %>"><%= post_type.labels.singular_name %>: <%= ID %></abbr>')( post )
			};

			args.ajax = { // instead of writing the function to execute the request we use Select2's convenient helper
				dataType: 'json',
				type: 'POST',
				url: window.ajaxurl,
				data: function (search, page) {
					var post_types = _.intersection( $( '#fakerpress-field-post_types' ).val().split( ',' ), _.pluck( _.where( $( '#fakerpress-field-post_types' ).data( 'options' ), { hierarchical: true } ) , 'id' ) );

					return {
						action: 'fakerpress.select2-' + source,
						query: {
							s: search,
							posts_per_page: 10,
							paged: page,
							post_type: post_types
						}
					};
				},
				results: function ( data ) { // parse the results into the format expected by Select2.
					$.each( data.results, function( k, result ){
						result.id = result.ID;
					} );
					return data;
				}
			};

		}

		$select.select2( args );
	})
	.on( 'change', function( event ) {
		var $select = $(this),
			data = $( this ).data( 'value' );

		if ( ! $select.is( '[multiple]' ) ){
			return;
		}
		if ( ! $select.is( '[data-source]' ) ){
			return;
		}

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
		$select.data( 'value', data ).attr( 'data-value', JSON.stringify( data ) );
	} );
};

// Quantity Range Fields
window.fakerpress.fields.range = function( $, _ ){
	'use strict';

	$( '.fp-type-range-wrap' ).each(function(){
		var $container = $(this),
			$minField = $container.find( '.fp-type-number[data-type="min"]' ),
			$maxField = $container.find( '.fp-type-number[data-type="max"]' );

		if ( $container.hasClass( window.fakerpress.ready_class ) ){
			return;
		}
		$container.addClass( window.fakerpress.ready_class );

		$minField.on({
			'change keyup': function(e){
				if ( $.isNumeric( $(this).val() ) ) {
					$maxField.removeAttr( 'disabled' );

					if ( $maxField.val() && $(this).val() >= $maxField.val() ){
						$(this).val( '' );
					}
				} else {
					$(this).val( '' );
				}

			}
		});
	});
};

( function( $ ){
	$( document ).ready( function(){
		$.each( window.fakerpress.fields, function( key, callback ){
			callback( window.jQuery, window._ );
		} );
	} );
}( jQuery ) );

( function( $, _ ){
	'use strict';
	$( document ).ready( function(){
		var $meta_containers = $( 'form' ).children( '.form-table' ).children( 'tbody' ).children( '.fp-type-meta-container' ).each( function(){
			var $container = $( this ),
				_wrap = '.fp-field-wrap',
				$wrap = $container.children( _wrap ),

				_meta = '.fp-table-meta',
				$metas = $wrap.children( _meta ),

				_meta_type_container = '.fp-meta_type-container',
				_meta_type = '.fp-meta_type',

				_meta_name_container = '.fp-meta_name-container',
				_meta_name = '.fp-meta_name',

				_meta_conf_container = '.fp-meta_conf-container',

				_duplicate = '.fp-action-duplicate',
				_remove = '.fp-action-remove',
				_order = '.fp-action-order',

				_label = '.fp-field-label',
				_internal_label = '.fp-internal-label',

				_fields = '.fp-field';

			$container.on( 'meta', [], function( event ){
				// Update the List of Metas
				$metas = $wrap.children( _meta );
				$container.data( 'metas', $metas );

				// If there is just one meta, disable the remove button
				if ( 1 === $metas.length && ! $metas.eq( 0 ).find( _meta_type ).select2( 'val' ) ){
					$metas.eq( 0 )
						.find( _remove ).prop( 'disabled', true )
						.end().find( _meta_name ).prop( 'required', false );
				} else {
					$metas
						.find( _remove ).prop( 'disabled', false )
						.end().find( _meta_name ).prop( 'required', true );
				}

				// Regenerate Order Index
				$metas.each( function( index ){
					var	$meta = $( this ),
						$index = $meta.find( _order ),

						$type = $meta.find( _meta_type ),
						type = $type.select2( 'val' ),
						$name = $meta.find( _meta_name ),
						name = $name.val(),

						$name_container = $meta.find( _meta_name_container ),
						$type_container = $meta.find( _meta_type_container ),
						$conf_container = $meta.find( _meta_conf_container ),
						$fields = $meta.find( _fields ),

						$template;

					// Before constructing the Type Object check if it's a jQuery element (Select2 bug)
					if ( type instanceof jQuery ){
						type = type.val();
					}
					$template = $( '.fp-template-' + type ).filter( '[data-rel="' + $container.attr( 'id' ) + '"]' ).filter( '[data-callable]' );


					// Change the index first
					$index.val( index + 1 );

					$fields.each( function(  ){
						var $field = $( this ),
							$label = $field.next( _label ),
							$internal_label = $field.next( _internal_label ),
							__name = $field.data( 'name' ),
							__id = $field.data( 'id' ),
							id = [],
							name = [];

						// If didn't find a label inside of the parent element
						if ( 0 === $label.length ){
							$label = $field.parents( _wrap ).eq(0).find( _label );
						}

						_.each( __id, function( value, key, list ) {
							id.push( value );
							if ( 'meta' === value ){
								id.push( index );
							}
						} );

						_.each( __name, function( value, key, list ) {
							name.push( value );
							if ( 'meta' === value ){
								name.push( index );
							}
						} );

						if ( 0 !== id.length ){
							$field.attr( 'id', window.fakerpress.fieldId( id ) );
							$label.attr( 'for', window.fakerpress.fieldId( id ) );
							$internal_label.attr( 'for', window.fakerpress.fieldId( id ) );
						}

						if ( 0 !== name.length ){
							$field.attr( 'name', window.fakerpress.fieldName( name ) );
						}
					} );

					if ( ! type || 0 === $template.length ){
						$name_container.addClass( 'fp-last-child' );
						$conf_container.hide();
					} else {
						$name_container.removeClass( 'fp-last-child' );
						$conf_container.show();
					}
				} );

				$.each( window.fakerpress.fields, function( key, callback ){
					callback( window.jQuery, window._ );
				} );

			} ).trigger( 'meta' );

			$container.on( 'change', _meta_type, [], function( event ){
				var $field = $( this ),
					$meta = $field.parents( _meta ),
					$template = $( '.fp-template-' + event.added.id ).filter( '[data-rel="' + $container.attr( 'id' ) + '"]' ).filter( '[data-callable]' ),
					template = $template.html(),

					$conf_container = $meta.find( _meta_conf_container ),
					$place = $conf_container.find( _wrap );

				$place.empty();

				// Only place if there is a template
				if ( 0 !== $template.length ){
					$place.append( template );
				}

				// Update all the required information
				$container.trigger( 'meta' );
			} );

			$container.on( 'click', _duplicate, [], function( event ){
				var $button = $( this ),
					$meta = $button.parents( _meta ),
					$clone = $meta.clone();

				$clone
					.find( _meta_type ).select2( 'val', '' )
					.end().find( _meta_name ).val( '' )
					.end().find( '.fp-type-date' ).removeClass( 'hasDatepicker' )
					.end().find( '.fp-type-dropdown' ).removeClass( 'select2-offscreen' )
					.filter( '.select2-container' ).remove();

				// Append the new Meta to the Wrap
				$wrap.append( $clone );

				// Update all the required information
				$container.trigger( 'meta' );
			} );

			$container.on( 'click', _remove, [], function( event ){
				var $button = $( this ),
					$meta = $button.parents( _meta ),
					$type = $meta.find( _meta_type ),
					$name = $meta.find( _meta_name );

				// Prevent remove when there is just one meta
				if ( 1 === $metas.length ){
					$type.select2( 'val', '' );
					$name.val( '' );
				} else {
					// Remove the Meta where the remove was clicked
					$meta.remove();
				}

				// Update all the required information
				$container.trigger( 'meta' );
			} );
		} );
	} );
}( window.jQuery, window._ ) );

/*

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

// Author fields
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

// Post Query for Select2
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		$( '.fp-field-select2-posts' ).each(function(){
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

// Check for checkbox dependecies
( function( $, _ ){
	'use strict';
	$(document).ready(function(){
		var checkDependency = function( event ){
			var $box, $dependecyField;
			if ( _.isNumber( event ) ){
				$box = $( this );
				$dependecyField = $( $box.data('fpDepends') );
			} else {
				$dependecyField = $( this );
				$box = $dependecyField.data( 'fpDependent' );
			}

			var	condition = $box.data('fpCondition'),
				$placeholder = $dependecyField.data( 'fpPlaceholder' );

			if ( ! $placeholder ){
				$placeholder = $( "<div>" ).attr( 'id', _.uniqueId( 'fp-dependent-placeholder-' ) );
				$dependecyField.data( 'fpPlaceholder', $placeholder );
			}
			$dependecyField.data( 'fpDependent', $box );

			if ( _.isNumber( event ) ){
				$dependecyField.on( 'change', checkDependency );
			}

			if ( $dependecyField.is(':checked') != condition ){
				$box.after( $placeholder ).detach();
			} else if ( $placeholder.is(':visible') ) {
				$placeholder.replaceWith( $dependecyField.data( 'fpDependent' ) );
			}
		};

		$( '.fp-field-dependent' ).each( checkDependency );
	});
}( window.jQuery, window._ ) );

*/