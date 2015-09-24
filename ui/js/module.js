if ( 'undefined' === typeof window.fakerpress ){
	window.fakerpress = {};
}

( function( $, _ ){
	'use strict';
	// Creates a Shortcut
	var fp = window.fakerpress;

	// Setup the Selectors
	fp._module_generator = '.fp-module-generator';

	fp.log = function ( $element, html, data, classes ){
		if ( 'undefined' === typeof classes ){
			classes = 'notice is-dismissible';
		} else {
			classes = 'notice is-dismissible ' + classes;
		}

		var htmlTemplate = _.template( html ),
			$notice = $( _.template( '<div class="<%= classes %>"><p><%= html %></p><button type="button" class="notice-dismiss"></button></div>', { classes: classes, html: htmlTemplate( data ) } ) );

		$notice.on( 'click.wp-dismiss-notice', '.notice-dismiss', function( event ) {
			event.preventDefault();
			$notice.fadeTo( 100 , 0, function() {
				$(this).slideUp( 100, function() {
					$(this).remove();
				});
			});
		});

		return $element.append( $notice );
	};

	fp.moduleGenerate = function ( $form, _POST ){
		if ( 'undefined' === typeof _POST ){
			_POST = Qs.parse( $form.serialize() );
		}

		// Always Hard set the Action
		_POST.action = 'fakerpress.module_generate';

		var $submit_container = $form.find( '.fp-submit' ),
			$spinner = $submit_container.find( '.spinner' ),
			$button = $submit_container.find( '.button-primary' ),
			$response = $submit_container.find( '.fp-response' );

		if ( $spinner.hasClass( 'is-active' ) ){
			return;
		}
		$spinner.addClass( 'is-active' );
		$button.prop( 'disabled', true );

		$.ajax({
			dataType: 'json',
			type: 'POST',
			url: window.ajaxurl,
			data: _POST,
			complete: function( jqXHR, status ){
				if ( 'success' !== status ){
					$spinner.removeClass( 'is-active' );
					$button.prop( 'disabled', false );

					fp.log( $response, '<%= message %>', { message: jqXHR.responseText }, 'notice-warning' );
				}
			},
			success: function( data, textStatus, jqXHR ) {
				$spinner.removeClass( 'is-active' );

				if ( data.status ){
					if ( data.is_capped && data.offset < data.total ){
						_POST.offset = data.offset;
						_POST.total = data.total;

						fp.moduleGenerate( $form, _POST );
					} else {
						$button.prop( 'disabled', false );
					}

					fp.log( $response, 'Faked <%= total %>: <%= message %>', { message: data.message, total: data.results.length }, 'notice-success' );
				} else {
					$button.prop( 'disabled', false );
					fp.log( $response, '<%= message %>', data, 'notice-warning' );
				}
			}
		});
	};

	// Document Ready Actions
	$( document ).ready( function(){
		var $forms = $( fp._module_generator ).each( function() {
			var $form = $( this );

			$form.on( 'submit', function ( event ) {
				fp.moduleGenerate( $form );

				event.preventDefault();
				return;
			} );
		} );
	} );
}( jQuery, _ ) );