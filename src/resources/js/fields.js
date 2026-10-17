/* global jQuery */
( function ( $, _ ) {
	'use strict';

	const isNumeric = ( n ) => {
		return ! isNaN( parseFloat( n ) ) && isFinite( n );
	};

	const fp = {};

	fp.fields = {};
	fp.ready_class = 'fp-js-ready';
	fp.plugin = 'fakerpress';
	fp.abbr = 'fp';

	fp.fieldName = function ( pieces ) {
		pieces = pieces.map( function ( piece ) {
			return new String( piece );
		} );

		return this.plugin + '[' + pieces.join( '][' ) + ']';
	};

	fp.fieldId = function ( pieces ) {
		return this.plugin + '-field-' + pieces.join( '-' );
	};

	fp.searchId = function ( e ) {
		let id = null;

		if ( 'undefined' !== typeof e.id ) {
			id = e.id;
		} else if ( 'undefined' !== typeof e.ID ) {
			id = e.ID;
		} else if ( 'undefined' !== typeof e.value ) {
			id = e.value;
		}
		return e === undefined || e === null ? null : id;
	};

	// Date Fields
	fp.fields.dates = function () {
		'use strict';
		const datepickerArgs = {
			after: [ 'attr' ],
			constrainInput: false,
			dateFormat: 'yy-mm-dd',
		};

		$( '.fp-type-date' )
			.not( '.hasDatepicker' )
			.datepicker( datepickerArgs );
		$( '.fp-type-interval-wrap' ).each( function () {
			const $container = $( this );

			if ( $container.hasClass( fp.ready_class ) ) {
				return;
			}
			$container.addClass( fp.ready_class );

			const $interval = $container.find( '.fp-type-dropdown' );
			const $min = $container.find( '[data-type="min"]' );
			const $max = $container.find( '[data-type="max"]' );

			$min.on( {
				change() {
					$min.parents( '.fp-field-wrap' )
						.find( '[data-type="max"]' )
						.datepicker( 'option', 'minDate', $( this ).val() )
						.datepicker( 'refresh' );
					$( '.fp-type-date.hasDatepicker' ).datepicker( 'refresh' );
				},
			} );

			$max.on( {
				change() {
					$max.parents( '.fp-field-wrap' )
						.find( '[data-type="min"]' )
						.datepicker( 'option', 'maxDate', $( this ).val() )
						.datepicker( 'refresh' );
					$( '.fp-type-date.hasDatepicker' ).datepicker( 'refresh' );
				},
			} );

			$interval
				.on( {
					change() {
						const $selected = $interval.find( ':selected' );
						const min = $selected.attr( 'min' );
						const max = $selected.attr( 'max' );

						$min.datepicker( 'setDate', min );
						$max.datepicker( 'setDate', max );
					},
				} )
				.trigger( 'change' );
		} );
	};

	// Select2 Fields
	fp.fields.dropdown = function () {
		'use strict';
		const $elements = $( '.fp-type-dropdown' ).not(
			'.select2-offscreen, .select2-container'
		);
		const config = {
			wp_query_title: [],
		};

		config.wp_query_title.push( 'ID: &quot;<%= ID %>&quot;' );
		config.wp_query_title.push( 'Title: &quot;<%= post_title %>&quot;' );
		config.wp_query_title.push(
			'Post Type: &quot;<%= post_type.labels.singular_name %>&quot;'
		);
		config.wp_query_title.push( 'Author: &quot;<%= post_author %>&quot;' );

		$elements
			.each( function () {
				const $select = $( this );
				const args = {
					width: 420,
				};

				if ( $select.is( '[multiple]' ) ) {
					args.multiple = true;

					if ( ! $select.is( '[data-tags]' ) ) {
						args.data = function () {
							return { results: $select.data( 'options' ) };
						};
					}

					if ( ! _.isArray( $select.data( 'separator' ) ) ) {
						args.tokenSeparators = [ $select.data( 'separator' ) ];
					} else {
						args.tokenSeparators = $select.data( 'separator' );
					}
					args.separator = $select.data( 'separator' );

					// Define the regular Exp based on
					args.regexSeparatorElements = [ '^(' ];
					args.regexSplitElements = [ '(?:' ];
					$.each( args.tokenSeparators, function ( i, token ) {
						args.regexSeparatorElements.push( '[^' + token + ']+' );
						args.regexSplitElements.push( '[' + token + ']' );
					} );
					args.regexSeparatorElements.push( ')$' );
					args.regexSplitElements.push( ')' );

					args.regexSeparatorString =
						args.regexSeparatorElements.join( '' );
					args.regexSplitString = args.regexSplitElements.join( '' );

					args.regexToken = new RegExp(
						args.regexSeparatorString,
						'ig'
					);
					args.regexSplit = new RegExp( args.regexSplitString, 'ig' );

					args.id = fp.searchId;
				} else {
					args.width = 200;
				}

				args.matcher = function ( term, text ) {
					let result =
						text.toUpperCase().indexOf( term.toUpperCase() ) === 0;

					if ( ! result && 'undefined' !== typeof args.tags ) {
						const possible = _.where( args.tags, { text } );
						if ( args.tags.length > 0 && _.isObject( possible ) ) {
							const testValue = fp.searchId( possible[ 0 ] );
							result =
								testValue
									.toUpperCase()
									.indexOf( term.toUpperCase() ) === 0;
						}
					}

					return result;
				};

				if ( $select.is( '[data-tags]' ) ) {
					args.tags = $select.data( 'options' );

					args.initSelection = function ( element, callback ) {
						const data = [];
						$( element.val().split( args.regexSplit ) ).each(
							function () {
								let obj = { id: this, text: this };
								if (
									args.tags.length > 0 &&
									_.isObject( args.tags[ 0 ] )
								) {
									const _obj = _.where( args.tags, {
										value: this,
									} );
									if ( _obj.length > 0 ) {
										obj = _obj[ 0 ];
										obj = {
											id: obj.value,
											text: obj.text,
										};
									}
								}

								data.push( obj );
							}
						);
						callback( data );
					};

					args.createSearchChoice = function ( term ) {
						if ( term.match( args.regexToken ) ) {
							return { id: term, text: term };
						}
					};

					if ( 0 === args.tags.length ) {
						args.formatNoMatches = function () {
							return $select.attr( 'placeholder' );
						};
					}
				}

				if ( $select.is( '[data-source]' ) ) {
					const source = $select.data( 'source' );
					const nonce = $select.data( 'nonce' );

					args.data = { results: [] };
					args.allowClear = true;

					args.escapeMarkup = function ( m ) {
						return m;
					};

					args.ajax = {
						// instead of writing the function to execute the request we use Select2's convenient helper
						dataType: 'json',
						type: 'POST',
						url: window.ajaxurl,
						results( data ) {
							// parse the results into the format expected by Select2.
							$.each( data.results, function ( k, result ) {
								result.id = result.ID;
							} );
							return data;
						},
					};

					// Now we set the data for the source we are looking
					if ( 'WP_Query' === source ) {
						args.formatSelection = function ( post ) {
							return _.template(
								'<abbr title="' +
									config.wp_query_title.join( '&#13;&#10;' ) +
									'"><%= post_type.labels.singular_name %>: <%= ID %>'
							)( post );
						};
						args.formatResult = function ( post ) {
							return _.template(
								'<abbr title="' +
									config.wp_query_title.join( '&#13;&#10;' ) +
									'"><%= post_type.labels.singular_name %>: <%= ID %> <b>[</b> <em><%= post_title %></em> <b>]</b></abbr>'
							)( post );
						};

						args.ajax.data = function ( search, page ) {
							let postTypes = [];
							const $postTypesField = $(
								'#fakerpress-field-post_types'
							);

							// Check if post_types field exists on the page and has a value
							if (
								$postTypesField.length > 0 &&
								$postTypesField.val()
							) {
								// Use the selected post types from the field
								const fieldValue = $postTypesField.val();
								const fieldOptions =
									$postTypesField.data( 'options' );

								if ( fieldValue && fieldOptions ) {
									postTypes = _.intersection(
										fieldValue.split( ',' ),
										_.pluck(
											_.where( fieldOptions, {
												hierarchical: true,
											} ),
											'id'
										)
									);
								}
							} else {
								// Default to all public post types when field doesn't exist or has no value (e.g., on attachments page)
								// This will be handled server-side to get all public post types
								postTypes = [];
							}

							return {
								action: 'fakerpress.select2-' + source,
								nonce,
								query: {
									s: search,
									posts_per_page: 10,
									paged: page,
									post_type: postTypes,
								},
							};
						};
					} else {
						args.ajax.data = function ( search, page ) {
							const $container = $select
								.parents( '.fp-table-taxonomy' )
								.eq( 0 );
							return {
								action: 'fakerpress.select2-' + source,
								search,
								nonce,
								page,
								page_limit: 25,
								taxonomies: $container
									.find( '.fp-taxonomies' )
									.select2( 'val' ),
								exclude: $( this ).select2( 'val' ),
							};
						};

						args.formatSelection = function ( result ) {
							return _.template( '<%= taxonomy %>: <%= name %>' )(
								result
							);
						};
						args.formatResult = function ( result ) {
							return _.template( '<%= taxonomy %>: <%= name %>' )(
								result
							);
						};
					}
				}

				$select.select2( args );
			} )
			.on( 'change', function ( event ) {
				const $select = $( this );

				if ( ! $select.is( '[multiple]' ) ) {
					return;
				}
				if ( ! $select.is( '[data-source]' ) ) {
					return;
				}

				let data = $select.data( 'value' );

				if ( event.added ) {
					if ( _.isArray( data ) ) {
						data.push( event.added );
					} else {
						data = [ event.added ];
					}
				} else if ( _.isArray( data ) ) {
					data = _.without( data, event.removed );
				} else {
					data = [];
				}
				$select
					.data( 'value', data )
					.attr( 'data-value', JSON.stringify( data ) );
			} )
			.on( 'select2-open', ( event ) => {
				const $target = $( event.target );
				const select2 = $target.data( 'select2' );
				const width = select2.dropdown.width();

				select2.dropdown
					.width( width + 2 )
					.css( 'margin-left', '-1px' );
			} );
	};

	// Quantity Range Fields
	fp.fields.range = function () {
		'use strict';

		$( '.fp-type-range-wrap' ).each( function () {
			const $container = $( this );

			if ( $container.hasClass( fp.ready_class ) ) {
				return;
			}
			$container.addClass( fp.ready_class );

			const $minField = $container.find(
				'.fp-type-number[data-type="min"]'
			);
			const $maxField = $container.find(
				'.fp-type-number[data-type="max"]'
			);

			$minField
				.on( {
					'change keyup'() {
						const minValue = parseInt( $minField.val(), 10 );
						const maxValue = parseInt( $maxField.val(), 10 );

						if ( isNumeric( minValue ) ) {
							$maxField.prop( 'disabled', false );

							// When we have max value we don't allow min to be bigger than max
							if (
								maxValue &&
								isNumeric( maxValue ) &&
								minValue > maxValue
							) {
								$minField.val( maxValue );
							}
						} else {
							$maxField.prop( 'disabled', true );
							$minField.val( '' );
						}
					},
				} )
				.trigger( 'change' );

			$maxField
				.on( {
					'change keyup'() {
						const minValue = parseInt( $minField.val(), 10 );
						const maxValue = parseInt( $maxField.val(), 10 );

						if ( isNumeric( maxValue ) && minValue > maxValue ) {
							$minField.val( maxValue );
						}
					},
				} )
				.trigger( 'change' );
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

					conf_container: '.fp-meta_conf-container',
				},

				update( $item ) {
					// Render again the Configuration
					this.render( $item );
				},

				render( $item ) {
					const fieldset = this;
					const $type = $item.find( fieldset.selector.type );
					let type = $type.select2( 'val' );
					const $nameContainer = $item.find(
						fieldset.selector.name_container
					);
					const $confContainer = $item.find(
						fieldset.selector.conf_container
					);
					const $place = $confContainer.find(
						fp.fieldset.selector.wrap
					);

					// Before constructing the Type Object check if it's a jQuery element (Select2 bug)
					if ( type instanceof jQuery ) {
						type = type.val();
					}

					// Find templates relevant to the current container
					const $template = $( '.fp-template-' + type )
						.filter(
							'[data-rel="' +
								fieldset.$.container.attr( 'id' ) +
								'"]'
						)
						.filter( '[data-callable]' );
					const template = $template.html();

					// Only place if there is a template
					if ( template && type !== $confContainer.data( 'type' ) ) {
						$confContainer.data( 'type', type );
						$place.empty().append( template );

						this.configure( $confContainer );
					}

					// Make Styles Match the what needs to be done
					if ( ! type ) {
						$nameContainer.addClass( 'fp-last-child' );
						$confContainer.hide();
					} else {
						$nameContainer.removeClass( 'fp-last-child' );
						$confContainer.show();
					}
				},

				/**
				 * A way to setup the configuration fields for Meta.
				 *
				 * @param {jQuery} $conf The configuration Container.
				 */
				configure( $conf ) {
					const fieldset = this;
					const config = $conf.data( 'config' );
					const $fields = $conf.find( fp.fieldset.selector.field );

					// Reset the Configuration, only happens once!
					// $conf.removeAttr( 'data-config', false ).data( 'config', {} );

					// Loop fields
					$fields.each( function () {
						const $field = $( this );
						const $fieldset = $field
							.parents( fieldset.selector.item )
							.eq( 0 );
						let $label = $field.next( fp.fieldset.selector.label );
						const $internalLabel = $field.next(
							fp.fieldset.selector.internal_label
						);

						const __name = $field.data( 'name' );
						const index =
							parseInt(
								$fieldset
									.find( fp.fieldset.selector.order )
									.val(),
								10
							) - 1;
						const key = __name[ index ];
						const __id = $field.data( 'id' );
						const id = [];
						const name = [];

						// If didn't find a label inside of the parent element
						if ( 0 === $label.length ) {
							$label = $field
								.parents( fp.fieldset.selector.field_container )
								.eq( 0 )
								.find( fp.fieldset.selector.label );
						}

						_.each( __id, function ( value ) {
							id.push( value );
							if ( 'meta' === value || 'taxonomy' === value ) {
								id.push( index );
							}
						} );

						_.each( __name, function ( value ) {
							name.push( value );
							if ( 'meta' === value || 'taxonomy' === value ) {
								name.push( index );
							}
						} );

						if ( 0 !== id.length ) {
							$field.attr( 'id', fp.fieldId( id ) );
							$label.attr( 'for', fp.fieldId( id ) );
							$internalLabel.attr( 'for', fp.fieldId( id ) );
						}

						if ( 0 !== name.length ) {
							$field.attr( 'name', fp.fieldName( name ) );
						}

						if (
							'undefined' === typeof config ||
							'undefined' === typeof config[ key ]
						) {
							return;
						}

						$field.val( config[ key ] );
					} );
				},

				is_removable() {
					if ( 1 === this.$.items.length ) {
						const $item = this.$.items.eq( 0 );

						if (
							_.isEmpty(
								$item
									.find( this.selector.type )
									.select2( 'val' )
							)
						) {
							return true;
						}
						return false;
					}

					return false;
				},

				setup() {
					const fieldset = this;

					fieldset.$.container.on(
						'change',
						fieldset.selector.type,
						[],
						function () {
							const $field = $( this );

							// Render again the Configuration
							fieldset.render( $field );

							// Update all the required information
							fp.fieldset.update( fieldset, false );
						}
					);
				},

				reset() {},
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
					weight: '.fp-weight',
				},

				update() {},

				is_removable() {
					if ( 1 === this.$.items.length ) {
						const $item = this.$.items.eq( 0 );

						if (
							_.isEmpty(
								$item
									.find( this.selector.taxonomies )
									.select2( 'val' )
							) &&
							_.isEmpty( $item.find( this.selector.terms ).val() )
						) {
							return true;
						}
						return false;
					}

					return false;
				},

				setup() {},

				reset() {},
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
			field_container: '.fp-field-container',
		},

		setup( fieldset ) {
			const $tbody = $( 'form' )
				.children( '.form-table' )
				.children( 'tbody' );

			// Reset the Elements (safety)
			fieldset.$ = {};

			// Based on the Argument set the container
			fieldset.$.container = $tbody.children(
				fieldset.selector.container
			);

			// Get the Field Wrapper
			fieldset.$.wrap = fieldset.$.container.children(
				fp.fieldset.selector.wrap
			);

			// Setup the Duplicate action
			fieldset.$.container.on(
				'click',
				this.selector.duplicate,
				[],
				function () {
					const $button = $( this );
					const $item = $button.parents( fieldset.selector.item );
					const $clone = $item.clone();

					$clone
						.find( '.fp-type-date' )
						.removeClass( 'hasDatepicker' )
						.end()
						.find( '.fp-type-dropdown' )
						.removeClass( 'select2-offscreen' )
						.filter( '.select2-container' )
						.remove();

					fp.fieldset.reset( fieldset, $clone );

					// Append the new Meta to the Wrap
					fieldset.$.wrap.append( $clone );

					// Update all the required information
					fp.fieldset.update( fieldset );
				}
			);

			// Setup the Remove action
			fieldset.$.container.on(
				'click',
				this.selector.remove,
				[],
				function () {
					const $button = $( this );
					const $item = $button.parents( fieldset.selector.item );

					// Prevent remove when there is just one meta
					if ( 1 === fieldset.$.items.length ) {
						fp.fieldset.reset( fieldset, fieldset.$.items.eq( 0 ) );
					} else {
						// Remove the Meta where the remove was clicked
						$item.remove();
					}

					// Update all the required information
					fp.fieldset.update( fieldset );
				}
			);

			fieldset.setup();

			// Update all the required information
			this.update( fieldset, false );
		},

		reset( fieldset, $item ) {
			const $fields = $item
				.find( 'tbody' )
				.find( fp.fieldset.selector.field );

			$fields.each( function () {
				const $field = $( this );

				// Reset normal fields
				$field.val( '' );

				// Resets the Select2
				if ( $field.hasClass( 'select2-offscreen' ) ) {
					$field.select2( 'val', '' );
				}
			} );

			fieldset.reset( $item );
		},

		update( fieldset ) {
			// Setup a base list of items
			fieldset.$.items = fieldset.$.wrap.children(
				fieldset.selector.item
			);

			// If there is just one meta, disable the remove button
			if ( fieldset.is_removable() ) {
				fieldset.$.items
					.eq( 0 )
					.find( fp.fieldset.selector.remove )
					.prop( 'disabled', true )
					.end()
					.find( fieldset.selector.name )
					.prop( 'required', false );
			} else {
				fieldset.$.items
					.find( fp.fieldset.selector.remove )
					.prop( 'disabled', false )
					.end()
					.find( fieldset.selector.name )
					.prop( 'required', true );
			}

			// Regenerate Order Index
			fieldset.$.items.each( function ( index ) {
				const $item = $( this );
				const $index = $item.find( fp.fieldset.selector.order );
				const $fields = $item.find( fp.fieldset.selector.field );

				// Change the index first
				$index.val( index + 1 );

				$fields.filter( 'input, textarea, select' ).each( function () {
					const $field = $( this );
					let $label = $field.next( fp.fieldset.selector.label );
					const $internalLabel = $field.next(
						fp.fieldset.selector.internal_label
					);
					const __name = $field.data( 'name' );
					const __id = $field.data( 'id' );
					const id = [];
					const name = [];

					// If didn't find a label inside of the parent element
					if ( 0 === $label.length ) {
						$label = $field
							.parents( fp.fieldset.selector.field_container )
							.eq( 0 )
							.find( fp.fieldset.selector.label );
					}

					_.each( __id, function ( value ) {
						id.push( value );
						if ( 'meta' === value || 'taxonomy' === value ) {
							id.push( index );
						}
					} );

					_.each( __name, function ( value ) {
						name.push( value );
						if ( 'meta' === value || 'taxonomy' === value ) {
							name.push( index );
						}
					} );

					if ( 0 !== id.length ) {
						$field.attr( 'id', fp.fieldId( id ) );
						$label.attr( 'for', fp.fieldId( id ) );
						$internalLabel.attr( 'for', fp.fieldId( id ) );
					}

					if ( 0 !== name.length ) {
						$field.attr( 'name', fp.fieldName( name ) );
					}
				} );

				fieldset.update( $item );
			} );

			$.each( fp.fields, function ( key, callback ) {
				callback();
			} );
		},
	};

	$( () => {
		$.each( fp.fieldset.items, function ( index, fieldset ) {
			fp.fieldset.setup( fieldset );
			fieldset.$.container.each( function () {
				// We need this to avoid having a `this` variable been a HTML element
				fp.fieldset.update( fieldset );
			} );
		} );

		$.each( fp.fields, function ( key, callback ) {
			callback();
		} );
	} );
} )( window.jQuery, window._ );
