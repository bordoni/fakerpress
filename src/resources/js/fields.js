( function( $, _, fp ){
	'use strict';

	const isNumeric = ( n ) => {
		return ! isNaN( parseFloat( n ) ) && isFinite( n );
	};

	fp.fields = {};
	fp.ready_class = 'fp-js-ready';
	fp.plugin = 'fakerpress';
	fp.abbr = 'fp';

	fp.fieldName = function( pieces ){
		pieces = pieces.map( function( piece ) {
			return new String( piece );
		} );

		return this.plugin + '[' + pieces.join( '][' ) + ']';
	};

	fp.fieldId = function( pieces ){
		return this.plugin + '-field-' + pieces.join( '-' );
	};

	fp.searchId = function ( e ) {
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
	fp.fields.dates = function( $, _ ){
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

			if ( $container.hasClass( fp.ready_class ) ){
				return;
			}
			$container.addClass( fp.ready_class );

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
	fp.fields.dropdown = function( $, _ ){
		'use strict';
		var $elements = $( '.fp-type-dropdown' ).not( '.select2-offscreen, .select2-container' ),
			config = {
				wp_query_title: []
			};

		config.wp_query_title.push( "ID: &quot;<%= ID %>&quot;" );
		config.wp_query_title.push( "Title: &quot;<%= post_title %>&quot;" );
		config.wp_query_title.push( "Post Type: &quot;<%= post_type.labels.singular_name %>&quot;" );
		config.wp_query_title.push( "Author: &quot;<%= post_author %>&quot;" );

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

				args.id = fp.searchId;

			} else {
				args.width = 200;
			}

			args.matcher = function( term, text ) {
				var result = text.toUpperCase().indexOf( term.toUpperCase() ) == 0;

				if ( ! result && 'undefined' !== typeof args.tags ){
					var possible = _.where( args.tags, { text: text } );
					if ( args.tags.length > 0  && _.isObject( possible ) ){
						var test_value = fp.searchId( possible[0] );
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
				var nonce = $select.data( 'nonce' );

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
						return _.template('<abbr title="' + config.wp_query_title.join( '&#13;&#10;' ) + '"><%= post_type.labels.singular_name %>: <%= ID %>')( post )
					};
					args.formatResult = function ( post ){
						return _.template('<abbr title="' + config.wp_query_title.join( '&#13;&#10;' ) + '"><%= post_type.labels.singular_name %>: <%= ID %> <b>[</b> <em><%= post_title %></em> <b>]</b></abbr>')( post )
					};

					args.ajax.data = function( search, page ) {
						var post_types = _.intersection( $( '#fakerpress-field-post_types' ).val().split( ',' ), _.pluck( _.where( $( '#fakerpress-field-post_types' ).data( 'options' ), { hierarchical: true } ) , 'id' ) );

						return {
							action: 'fakerpress.select2-' + source,
							nonce: nonce,
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
							nonce: nonce,
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
		} )
		.on( 'select2-open', ( event ) => {
			const $target = $( event.target );
			const select2 = $target.data( 'select2' );
			const width = select2.dropdown.width();

			select2.dropdown.width( width+2 ).css( 'margin-left', '-1px' );

		} );
	};

	// Quantity Range Fields
	fp.fields.range = function( $, _ ){
		'use strict';

		$( '.fp-type-range-wrap' ).each(function(){
			var $container = $( this );
			var $minField = $container.find( '.fp-type-number[data-type="min"]' );
			var $maxField = $container.find( '.fp-type-number[data-type="max"]' );

			if ( $container.hasClass( fp.ready_class ) ){
				return;
			}
			$container.addClass( fp.ready_class );

			$minField.on( {
				'change keyup': function(e){
					var minValue = parseInt( $minField.val(), 10 );
					var maxValue = parseInt( $maxField.val(), 10 );

					if ( isNumeric( minValue ) ) {
						$maxField.prop( 'disabled', false );

						// When we have max value we don't allow min to be bigger than max
						if ( maxValue && isNumeric( maxValue ) && minValue > maxValue ){
							$minField.val( maxValue );
						}
					} else {
						$maxField.prop( 'disabled', true );
						$minField.val( '' );
					}
				}
			} ).trigger( 'change' );

			$maxField.on( {
				'change keyup': function(e){
					var minValue = parseInt( $minField.val(), 10 );
					var maxValue = parseInt( $maxField.val(), 10 );

					if ( isNumeric( maxValue ) && minValue > maxValue ) {
						$minField.val( maxValue );
					}
				}
			} ).trigger( 'change' );
		} );
	};

	fp.fieldset = {
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
					// Render again the Configuration
					this.render( $item );
				},

				render: function( $item ) {
					var fieldset = this,
						$type = $item.find( fieldset.selector.type ),
						type = $type.select2( 'val' ),

						$name = $item.find( fieldset.selector.name ),
						name = $name.val(),

						$name_container = $item.find( fieldset.selector.name_container ),
						$type_container = $item.find( fieldset.selector.type_container ),
						$conf_container = $item.find( fieldset.selector.conf_container ),

						$place = $conf_container.find( fp.fieldset.selector.wrap ),
						config = $conf_container.data( 'config' ),

						$template,
						template;

					// Before constructing the Type Object check if it's a jQuery element (Select2 bug)
					if ( type instanceof jQuery ){
						type = type.val();
					}

					// Find templates relevant to the current container
					$template = $( '.fp-template-' + type ).filter( '[data-rel="' + fieldset.$.container.attr( 'id' ) + '"]' ).filter( '[data-callable]' );
					template = $template.html();

					// Only place if there is a template
					if ( template && type !== $conf_container.data( 'type' ) ) {
						$conf_container.data( 'type', type );
						$place.empty().append( template );

						this.configure( $conf_container );
					}

					// Make Styles Match the what needs to be done
					if ( ! type ){
						$name_container.addClass( 'fp-last-child' );
						$conf_container.hide();
					} else {
						$name_container.removeClass( 'fp-last-child' );
						$conf_container.show();
					}
				},

				/**
				 * A way to setup the configuration fields for Meta
				 *
				 * @param  jQuery   $conf  The configuration Container
				 *
				 * @return null
				 */
				configure: function( $conf ) {
					var fieldset = this;
					var config = $conf.data( 'config' );
					var $fields = $conf.find( fp.fieldset.selector.field );

					// Reset the Configuration, only happens once!
					// $conf.removeAttr( 'data-config', false ).data( 'config', {} );

					// Loop fields
					$fields.each( function() {
						var $field = $( this );
						var $fieldset = $field.parents( fieldset.selector.item ).eq( 0 );
						var $label = $field.next( fp.fieldset.selector.label );
						var $internal_label = $field.next( fp.fieldset.selector.internal_label );

						var name = $field.data( 'name' );
						var index = parseInt( $fieldset.find( fp.fieldset.selector.order ).val(), 10 ) - 1;
						var key = name[ index ];
						var __name = $field.data( 'name' );
						var __id = $field.data( 'id' );
						var id = [];
						var name = [];

						// If didn't find a label inside of the parent element
						if ( 0 === $label.length ){
							$label = $field.parents( fp.fieldset.selector.field_container ).eq( 0 ).find( fp.fieldset.selector.label );
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
							$field.attr( 'id', fp.fieldId( id ) );
							$label.attr( 'for', fp.fieldId( id ) );
							$internal_label.attr( 'for', fp.fieldId( id ) );
						}

						if ( 0 !== name.length ){
							$field.attr( 'name', fp.fieldName( name ) );
						}

						if ( 'undefined' === typeof config || 'undefined' === typeof config[ key ] ) {
							return;
						}

						$field.val( config[ key ] );
					} );
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
						var $field = $( this );

						// Render again the Configuration
						fieldset.render( $field );

						// Update all the required information
						fp.fieldset.update( fieldset, false );
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
			fieldset.$.wrap = fieldset.$.container.children( fp.fieldset.selector.wrap );

			// Setup the Duplicate action
			fieldset.$.container.on( 'click', this.selector.duplicate, [], function( event ){
				var $button = $( this ),
					$item = $button.parents( fieldset.selector.item ),
					$clone = $item.clone();

				$clone
					.find( '.fp-type-date' ).removeClass( 'hasDatepicker' )
					.end().find( '.fp-type-dropdown' ).removeClass( 'select2-offscreen' )
					.filter( '.select2-container' ).remove();

				fp.fieldset.reset( fieldset, $clone );

				// Append the new Meta to the Wrap
				fieldset.$.wrap.append( $clone );

				// Update all the required information
				fp.fieldset.update( fieldset );
			} );

			// Setup the Remove action
			fieldset.$.container.on( 'click', this.selector.remove, [], function( event ){
				var $button = $( this ),
					$item = $button.parents( fieldset.selector.item );

				// Prevent remove when there is just one meta
				if ( 1 === fieldset.$.items.length ){
					fp.fieldset.reset( fieldset, fieldset.$.items.eq( 0 ) );
				} else {
					// Remove the Meta where the remove was clicked
					$item.remove();
				}

				// Update all the required information
				fp.fieldset.update( fieldset );
			} );

			fieldset.setup();

			// Update all the required information
			this.update( fieldset, false );
		},

		reset: function( fieldset, $item ) {
			var $fields = $item.find( 'tbody' ).find( fp.fieldset.selector.field );

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
					.find( fp.fieldset.selector.remove ).prop( 'disabled', true )
					.end().find( fieldset.selector.name ).prop( 'required', false );
			} else {
				fieldset.$.items
					.find( fp.fieldset.selector.remove ).prop( 'disabled', false )
					.end().find( fieldset.selector.name ).prop( 'required', true );
			}

			// Regenerate Order Index
			fieldset.$.items.each( function( index ){
				var	$item = $( this ),
					$index = $item.find( fp.fieldset.selector.order ),
					$fields = $item.find( fp.fieldset.selector.field );

				// Change the index first
				$index.val( index + 1 );

				$fields.filter( 'input, textarea, select' ).each( function(  ){
					var $field = $( this ),
						$label = $field.next( fp.fieldset.selector.label ),
						$internal_label = $field.next( fp.fieldset.selector.internal_label ),

						__name = $field.data( 'name' ),
						__id = $field.data( 'id' ),
						id = [],
						name = [];

					// If didn't find a label inside of the parent element
					if ( 0 === $label.length ){
						$label = $field.parents( fp.fieldset.selector.field_container ).eq(0).find( fp.fieldset.selector.label );
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
						$field.attr( 'id', fp.fieldId( id ) );
						$label.attr( 'for', fp.fieldId( id ) );
						$internal_label.attr( 'for', fp.fieldId( id ) );
					}

					if ( 0 !== name.length ){
						$field.attr( 'name', fp.fieldName( name ) );
					}
				} );

				fieldset.update( $item );
			} );

			$.each( fp.fields, function( key, callback ){
				callback( window.jQuery, window._ );
			} );
		}
	};

	$( document ).ready( function() {
		$.each( fp.fieldset.items, function( index, fieldset ) {
			fp.fieldset.setup( fieldset );
			fieldset.$.container.each( function( _index, container ) {
				// We need this to avoid having a `this` variable been a HTML element
				fp.fieldset.update( fieldset );
			} );
		} );

		$.each( fp.fields, function( key, callback ){
			callback( window.jQuery, window._ );
		} );
	} );

}( window.jQuery, window._, window.fakerpress ) );
