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

			args.ajax = { // instead of writing the function to execute the request we use Select2's convenient helper
				dataType: 'json',
				type: 'POST',
				url: window.ajaxurl,
				results: function ( data ) { // parse the results into the format expected by Select2.
					$.each( data.results, function( k, result ){
						result.id = result.ID;
					} );
					return data;
				}
			};

			// Now we set the data for the source we are looking
			if ( 'WP_Query' === source ){
				args.formatSelection = function ( post ){
					return _.template('<abbr title="<%= post_title %>"><%= post_type.labels.singular_name %>: <%= ID %></abbr>')( post )
				};
				args.formatResult = function ( post ){
					return _.template('<abbr title="<%= post_title %>"><%= post_type.labels.singular_name %>: <%= ID %></abbr>')( post )
				};

				args.ajax.data = function( search, page ) {
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
				}
			} else {
				args.ajax.data = function( search, page ) {
					var $container = $select.parents( '.fp-table-taxonomy' ).eq( 0 );
					return {
						action: 'fakerpress.select2-' + source,
						search: search,
						page: page,
						page_limit: 25,
						taxonomies: $container.find( '.fp-taxonomies' ).select2( 'val' ),
						exclude: $( this ).select2( 'val' )
					};
				}

				args.formatSelection = function ( result ){
					return _.template( '<%= taxonomy %>: <%= name %>' )( result );
				};
				args.formatResult = function ( result ){
					return _.template( '<%= taxonomy %>: <%= name %>' )( result );
				};
			}
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
	window.fakerpress.fieldset = {
		items: [
			{
				name: 'meta',

				$: {},
				selector: {
					container: '.fp-type-meta-container',

					item: '.fp-table-meta',

					type_container: '.fp-meta_type-container',
					type: '.fp-meta_type',

					name_container: '.fp-meta_name-container',
					name: '.fp-meta_name',

					conf_container: '.fp-meta_conf-container'
				},

				update: function( $item ) {
					var fieldset = this,
						$type = $item.find( fieldset.selector.type ),
						type = $type.select2( 'val' ),

						$name = $item.find( fieldset.selector.name ),
						name = $name.val(),

						$name_container = $item.find( fieldset.selector.name_container ),
						$type_container = $item.find( fieldset.selector.type_container ),
						$conf_container = $item.find( fieldset.selector.conf_container ),

						$template;

					// Before constructing the Type Object check if it's a jQuery element (Select2 bug)
					if ( type instanceof jQuery ){
						type = type.val();
					}

					// Find templates relevant to the current container
					$template = $( '.fp-template-' + type ).filter( '[data-rel="' + fieldset.$.container.attr( 'id' ) + '"]' ).filter( '[data-callable]' );

					if ( ! type || 0 === $template.length ){
						$name_container.addClass( 'fp-last-child' );
						$conf_container.hide();
					} else {
						$name_container.removeClass( 'fp-last-child' );
						$conf_container.show();
					}
				},

				is_removeable: function() {
					if ( 1 === this.$.items.length ){
						var $item = this.$.items.eq( 0 );

						if ( _.isEmpty( $item.find( this.selector.type ).select2( 'val' ) ) ) {
							return true;
						} else {
							return false;
						}
					}

					return false;
				},

				setup: function() {
					var fieldset = this;

					fieldset.$.container.on( 'change', fieldset.selector.type, [], function( event ){
						var $field = $( this ),
							$item = $field.parents( fieldset.selector.item ),
							$template = $( '.fp-template-' + event.added.id ).filter( '[data-rel="' + fieldset.$.container.attr( 'id' ) + '"]' ).filter( '[data-callable]' ),
							template = $template.html(),

							$conf_container = $item.find( fieldset.selector.conf_container ),
							$place = $conf_container.find( window.fakerpress.fieldset.selector.wrap );

						$place.empty();

						// Only place if there is a template
						if ( 0 !== $template.length ){
							$place.append( template );
						}

						// Update all the required information
						window.fakerpress.fieldset.update( fieldset );
					} );
				},

				reset: function( $item ) {

				}
			},
			{
				name: 'taxonomy',

				$: {},
				selector: {
					container: '.fp-type-taxonomy-container',

					item: '.fp-table-taxonomy',

					taxonomies_container: '.fp-taxonomies-container',
					taxonomies: '.fp-taxonomies',

					terms_container: '.fp-terms-container',
					terms: '.fp-terms',

					weight_container: '.fp-weight-container',
					weight: '.fp-weight'
				},

				update: function( $item ) {

				},

				is_removeable: function() {
					if ( 1 === this.$.items.length ){
						var $item = this.$.items.eq( 0 );

						if (
							_.isEmpty( $item.find( this.selector.taxonomies ).select2( 'val' ) ) &&
							_.isEmpty( $item.find( this.selector.terms ).val() )
						) {
							return true;
						} else {
							return false;
						}
					}

					return false;
				},

				setup: function() {
					var fieldset = this;
				},

				reset: function( $item ) {

				}
			},
		],

		// Global Elements
		selector: {
			wrap: '.fp-field-wrap',
			duplicate: '.fp-action-duplicate',
			remove: '.fp-action-remove',
			order: '.fp-action-order',
			label: '.fp-field-label',
			internal_label: '.fp-internal-label',
			field: '.fp-field',
			field_container: '.fp-field-container'
		},

		setup: function( fieldset ) {
			var $tbody = $( 'form' ).children( '.form-table' ).children( 'tbody' );

			// Reset the Elements (safety)
			fieldset.$ = {};

			// Based on the Argument set the container
			fieldset.$.container = $tbody.children( fieldset.selector.container );

			// Get the Field Wrapper
			fieldset.$.wrap = fieldset.$.container.children( window.fakerpress.fieldset.selector.wrap );

			// Setup the Duplicate action
			fieldset.$.container.on( 'click', this.selector.duplicate, [], function( event ){
				var $button = $( this ),
					$item = $button.parents( fieldset.selector.item ),
					$clone = $item.clone();

				$clone
					.find( '.fp-type-date' ).removeClass( 'hasDatepicker' )
					.end().find( '.fp-type-dropdown' ).removeClass( 'select2-offscreen' )
					.filter( '.select2-container' ).remove();

				window.fakerpress.fieldset.reset( fieldset, $clone );

				// Append the new Meta to the Wrap
				fieldset.$.wrap.append( $clone );

				// Update all the required information
				window.fakerpress.fieldset.update( fieldset );
			} );

			// Setup the Remove action
			fieldset.$.container.on( 'click', this.selector.remove, [], function( event ){
				var $button = $( this ),
					$item = $button.parents( fieldset.selector.item );

				// Prevent remove when there is just one meta
				if ( 1 === fieldset.$.items.length ){
					window.fakerpress.fieldset.reset( fieldset, fieldset.$.items.eq( 0 ) );
				} else {
					// Remove the Meta where the remove was clicked
					$item.remove();
				}

				// Update all the required information
				window.fakerpress.fieldset.update( fieldset );
			} );

			fieldset.setup();
		},

		reset: function( fieldset, $item ) {
			var $fields = $item.find( 'tbody' ).find( window.fakerpress.fieldset.selector.field );

			$fields.each( function() {
				var $field = $( this );

				// Reset normal fields
				$field.val( '' );

				// Resets the Select2
				if ( $field.hasClass( 'select2-offscreen' ) ){
					$field.select2( 'val', '' );
				}
			} );

			fieldset.reset( $item );
		},

		update: function( fieldset ) {
			// Setup a base list of items
			fieldset.$.items = fieldset.$.wrap.children( fieldset.selector.item );

			// If there is just one meta, disable the remove button
			if ( fieldset.is_removeable() ) {
				fieldset.$.items.eq( 0 )
					.find( window.fakerpress.fieldset.selector.remove ).prop( 'disabled', true )
					.end().find( fieldset.selector.name ).prop( 'required', false );
			} else {
				fieldset.$.items
					.find( window.fakerpress.fieldset.selector.remove ).prop( 'disabled', false )
					.end().find( fieldset.selector.name ).prop( 'required', true );
			}

			// Regenerate Order Index
			fieldset.$.items.each( function( index ){
				var	$item = $( this ),
					$index = $item.find( window.fakerpress.fieldset.selector.order ),
					$fields = $item.find( window.fakerpress.fieldset.selector.field );

				// Change the index first
				$index.val( index + 1 );

				$fields.each( function(  ){
					var $field = $( this ),
						$label = $field.next( window.fakerpress.fieldset.selector.label ),
						$internal_label = $field.next( window.fakerpress.fieldset.selector.internal_label ),

						__name = $field.data( 'name' ),
						__id = $field.data( 'id' ),
						id = [],
						name = [];

					// If didn't find a label inside of the parent element
					if ( 0 === $label.length ){
						$label = $field.parents( window.fakerpress.fieldset.selector.field_container ).eq(0).find( window.fakerpress.fieldset.selector.label );
					}

					_.each( __id, function( value, key, list ) {
						id.push( value );
						if ( 'meta' === value || 'taxonomy' === value ){
							id.push( index );
						}
					} );

					_.each( __name, function( value, key, list ) {
						name.push( value );
						if ( 'meta' === value || 'taxonomy' === value ){
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

				fieldset.update( $item );
			} );

			$.each( window.fakerpress.fields, function( key, callback ){
				callback( window.jQuery, window._ );
			} );
		}
	};


	$( document ).ready( function() {
		$.each( window.fakerpress.fieldset.items, function( index, fieldset ) {
			window.fakerpress.fieldset.setup( fieldset );
			fieldset.$.container.each( function( _index, container ) {
				// We need this to avoid having a `this` variable been a HTML element
				window.fakerpress.fieldset.update( fieldset );
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



*/