( ( $, _ ) => {
	'use strict';

	const { __ } = wp.i18n;
	const fp = {};

	fp.selectors = {
		moduleGenerator: '.fp-module-generator',
	};

	/**
	 * Get the REST API base URL for FakerPress.
	 *
	 * @return {string} The REST API base URL.
	 */
	fp.getRestUrl = () => {
		// Use localized data first, fallback to wpApiSettings
		const restUrl = window.fakerpressRestApi?.root || window.wpApiSettings?.root || window.wp?.api?.settings?.root || '/wp-json/';
		return `${restUrl}fakerpress/v1/`;
	};

	/**
	 * Get the REST API endpoint URL for a specific module.
	 *
	 * @param {string} module - The module name (posts, users, terms, comments).
	 * @return {string} The complete endpoint URL.
	 */
	fp.getEndpointUrl = ( module ) => {
		const baseUrl = fp.getRestUrl();
		return `${baseUrl}${module}/generate`;
	};

	/**
	 * Get the REST API nonce for authentication.
	 *
	 * @return {string} The REST nonce.
	 */
	fp.getRestNonce = () => {
		// Use localized data first, fallback to wpApiSettings
		return window.fakerpressRestApi?.nonce || window.wpApiSettings?.nonce || window.wp?.api?.settings?.nonce || '';
	};

	/**
	 * Log a message to the UI with proper styling.
	 *
	 * @param {jQuery} $element - The element to append the notice to.
	 * @param {string} html - The HTML template string.
	 * @param {Object} data - The data to interpolate into the template.
	 * @param {string} attrClass - Additional CSS classes for the notice.
	 * @return {jQuery} The element with the appended notice.
	 */
	fp.log = ( $element, html, data, attrClass = 'notice is-dismissible' ) => {
		const finalAttrClass = [ 'notice', 'is-dismissible', attrClass ].join( ' ' ).split( ' ' ).filter( ( value, index, self ) => self.indexOf( value ) === index ).join( ' ' );
		
		// Handle both simple messages and complex HTML with links
		let noticeContent;
		if ( typeof html === 'string' && html.includes( '<%= ' ) ) {
			// Template string - interpolate data
			noticeContent = _.template( html )( data );
		} else {
			// Simple string or already processed HTML
			noticeContent = html;
		}
		
		const templateVars = {
			attrClass: finalAttrClass,
			html: noticeContent,
		};
		
		const noticeTemplate = '<div class="<%= attrClass %>"><p><%= html %></p><button type="button" class="notice-dismiss"></button></div>';
		const noticeHtml = _.template( noticeTemplate )( templateVars );
		const $notice = $( noticeHtml );

		$notice.on( 'click.wp-dismiss-notice', '.notice-dismiss', ( event ) => {
			event.preventDefault();
			$notice.fadeTo( 100, 0, function() {
				$( this ).slideUp( 100, function() {
					$( this ).remove();
				} );
			} );
		} );

		return $element.append( $notice );
	};

	/**
	 * Format the success message with links to generated items.
	 *
	 * @param {Object} responseData - The response data from the REST API.
	 * @param {string} baseMessage - The base success message.
	 * @return {string} The formatted HTML message with links.
	 */
	fp.formatSuccessMessage = ( responseData, baseMessage ) => {
		let message = baseMessage;
		
		// Add links if available
		if ( responseData.links && responseData.links.length > 0 ) {
			// The links array contains HTML strings (e.g., '<a href="...">123</a>')
			// not objects, so we can join them directly
			const linksList = responseData.links.join( ', ' );
			message += '<br><strong>' + __( 'Generated items:', 'fakerpress' ) + '</strong> ' + linksList;
		}
		
		// Add timing information if available
		if ( responseData.time ) {
			message += '<br><em>' + __( 'Generation time: %s seconds', 'fakerpress' ).replace( '%s', responseData.time ) + '</em>';
		}
		
		return message;
	};

	/**
	 * Generate fake data using the REST API.
	 *
	 * @param {jQuery} $form - The form element.
	 * @param {Object} requestData - Optional request data override.
	 */
	fp.moduleGenerate = ( $form, requestData ) => {
		// Get the module endpoint from form data attribute
		const moduleEndpoint = $form.data( 'endpoint' );
		if ( ! moduleEndpoint ) {
			console.error( 'No endpoint specified in form data-endpoint attribute' );
			return;
		}

		// Parse form data if not provided
		if ( typeof requestData === 'undefined' ) {
			requestData = fp.parseFormData( $form );
		}

		const $submitContainer = $form.find( '.fp-submit' );
		const $spinner = $submitContainer.find( '.spinner' );
		const $button = $submitContainer.find( '.button-primary' );
		const $response = $submitContainer.find( '.fp-response' );

		// Check if this is a batched request (has offset/total parameters)
		const isBatchedRequest = requestData && ( requestData.offset !== undefined || requestData.total !== undefined );

		// Only check spinner for initial requests, not batched continuations
		if ( ! isBatchedRequest && $spinner.hasClass( 'is-active' ) ) {
			return;
		}

		// Only set spinner and disable button for initial requests
		if ( ! isBatchedRequest ) {
			$spinner.addClass( 'is-active' );
			$button.prop( 'disabled', true );
		}

		// Get the endpoint URL
		const endpointUrl = fp.getEndpointUrl( moduleEndpoint );
		const restNonce = fp.getRestNonce();

		// Flag to track if error has been handled
		let errorHandled = false;

		// Prepare the data for the request
		let postData = requestData.fakerpress || {};
		
		// Add batching parameters if they exist
		if ( requestData.offset !== undefined ) {
			postData.offset = requestData.offset;
		}
		if ( requestData.total !== undefined ) {
			postData.total = requestData.total;
		}

		$.ajax( {
			url: endpointUrl,
			type: 'POST',
			dataType: 'json',
			data: postData,
			beforeSend: ( xhr ) => {
				if ( restNonce ) {
					xhr.setRequestHeader( 'X-WP-Nonce', restNonce );
				}
			},
			complete: ( jqXHR, status ) => {
				// Don't remove spinner here - let the success/error callbacks handle it
				// This ensures spinner stays active during batching
				
				// Only handle errors in complete if they haven't been handled in the error callback
				if ( status !== 'success' && ! errorHandled ) {
					// Always remove spinner and enable button on errors
					$spinner.removeClass( 'is-active' );
					$button.prop( 'disabled', false );

					let errorMessage = __( 'An error occurred', 'fakerpress' );
					
					// Try to extract error message from response
					if ( jqXHR.responseJSON ) {
						if ( jqXHR.responseJSON.message ) {
							errorMessage = jqXHR.responseJSON.message;
						} else if ( jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
							errorMessage = jqXHR.responseJSON.data.message;
						}
					} else if ( jqXHR.responseText ) {
						try {
							const parsed = JSON.parse( jqXHR.responseText );
							if ( parsed.message ) {
								errorMessage = parsed.message;
							} else if ( parsed.data && parsed.data.message ) {
								errorMessage = parsed.data.message;
							}
						} catch ( e ) {
							// If not JSON, use the raw text
							errorMessage = jqXHR.responseText;
						}
					}

					fp.log( $response, '<%= message %>', { message: errorMessage }, 'notice-error' );
				}
			},
			success: ( data, textStatus, jqXHR ) => {
				if ( data === null ) {
					$spinner.removeClass( 'is-active' );
					$button.prop( 'disabled', false );
					fp.log( $response, '<%= message %>', { message: __( 'No data received from server', 'fakerpress' ) }, 'notice-error' );
				} else if ( data.success ) {
					// Handle successful response
					const responseData = data.data || {};
					const generated = responseData.generated || 0;
					const baseMessage = data.message || __( 'Generated %d items', 'fakerpress' ).replace( '%d', generated );

					// Check if we need to continue with pagination/batching
					if ( responseData.is_capped && responseData.offset < responseData.total ) {
						requestData.offset = responseData.offset;
						requestData.total = responseData.total;

						// Continue with next batch - keep spinner active and button disabled
						fp.moduleGenerate( $form, requestData );
					} else {
						// Batching is complete, remove spinner and enable button
						$spinner.removeClass( 'is-active' );
						$button.prop( 'disabled', false );
					}

					// Format the success message with links
					const formattedMessage = fp.formatSuccessMessage( responseData, baseMessage );

					fp.log( 
						$response, 
						formattedMessage,
						{}, 
						'notice-success' 
					);
				} else {
					$spinner.removeClass( 'is-active' );
					$button.prop( 'disabled', false );
					
					let errorMessage = __( 'Generation failed', 'fakerpress' );
					
					// Extract error message from failed response
					if ( data.message ) {
						errorMessage = data.message;
					} else if ( data.data && data.data.message ) {
						errorMessage = data.data.message;
					}

					fp.log( $response, '<%= message %>', { message: errorMessage }, 'notice-error' );
				}
			},
			error: ( jqXHR, textStatus, errorThrown ) => {
				// Mark error as handled to prevent duplicate messages in complete callback
				errorHandled = true;
				
				$spinner.removeClass( 'is-active' );
				$button.prop( 'disabled', false );

				let errorMessage = __( 'An error occurred', 'fakerpress' );
				
				// Try to extract meaningful error message
				if ( jqXHR.responseJSON ) {
					if ( jqXHR.responseJSON.message ) {
						errorMessage = jqXHR.responseJSON.message;
					} else if ( jqXHR.responseJSON.data && jqXHR.responseJSON.data.message ) {
						errorMessage = jqXHR.responseJSON.data.message;
					}
				} else if ( jqXHR.responseText ) {
					try {
						const parsed = JSON.parse( jqXHR.responseText );
						if ( parsed.message ) {
							errorMessage = parsed.message;
						} else if ( parsed.data && parsed.data.message ) {
							errorMessage = parsed.data.message;
						}
					} catch ( e ) {
						// If parsing fails, check for common HTTP errors
						if ( jqXHR.status === 403 ) {
							errorMessage = __( 'Permission denied. You do not have sufficient permissions to perform this action.', 'fakerpress' );
						} else if ( jqXHR.status === 404 ) {
							errorMessage = __( 'REST API endpoint not found. Please check if the FakerPress REST API is properly configured.', 'fakerpress' );
						} else if ( jqXHR.status === 500 ) {
							errorMessage = __( 'Internal server error occurred. Please check the server logs for more details.', 'fakerpress' );
						} else if ( jqXHR.status === 0 ) {
							errorMessage = __( 'Network error. Please check your internet connection.', 'fakerpress' );
						} else if ( errorThrown ) {
							errorMessage = __( 'Error: %s', 'fakerpress' ).replace( '%s', errorThrown );
						} else {
							errorMessage = __( 'HTTP Error %d', 'fakerpress' ).replace( '%d', jqXHR.status );
						}
					}
				} else if ( errorThrown ) {
					errorMessage = __( 'Error: %s', 'fakerpress' ).replace( '%s', errorThrown );
				}

				fp.log( $response, '<%= message %>', { message: errorMessage }, 'notice-error' );
			},
		} );
	};

	/**
	 * Parse form data into an object suitable for REST API.
	 *
	 * @param {jQuery} $form - The form element.
	 * @return {Object} The parsed form data.
	 */
	fp.parseFormData = ( $form ) => {
		const formData = {};
		const serializedData = $form.serializeArray();
		// Convert serialized array to object
		serializedData.forEach( ( field ) => {
			const { name, value } = field;
			
			// Skip WordPress nonce fields since we use REST API authentication
			if ( name === '_wpnonce' || name === '_wp_http_referer' || name.includes( 'nonce' ) ) {
				return;
			}
			
			// Parse nested array notation (e.g., fakerpress[qty][min], fakerpress[taxonomy][0][qty][max])
			if ( name.includes( '[' ) && name.includes( ']' ) ) {
				fp.setNestedValue( formData, name, value );
			} else {
				formData[ name ] = value;
			}
		} );

		// Convert numeric strings to numbers where appropriate
		fp.convertNumericValues( formData );

		console.log( 'Form Data', formData );

		return formData;
	};

	/**
	 * Set a nested value in an object using array notation.
	 *
	 * @param {Object} obj - The target object.
	 * @param {string} path - The path in array notation (e.g., 'fakerpress[qty][min]').
	 * @param {string} value - The value to set.
	 */
	fp.setNestedValue = ( obj, path, value ) => {
		// Skip empty values entirely
		if ( value === '' || value === null || value === undefined ) {
			return;
		}
		
		// Parse the path: fakerpress[qty][min] -> ['fakerpress', 'qty', 'min']
		const keys = path.split( /[\[\]]/ ).filter( key => key !== '' );
		
		let current = obj;
		
		// Navigate/create the nested structure
		for ( let i = 0; i < keys.length - 1; i++ ) {
			const key = keys[ i ];
			
			// If the key is numeric, we're dealing with an array
			if ( /^\d+$/.test( key ) ) {
				const numKey = parseInt( key, 10 );
				if ( ! Array.isArray( current ) ) {
					// Convert to array if it's not already
					current = [];
				}
				if ( ! current[ numKey ] ) {
					current[ numKey ] = {};
				}
				current = current[ numKey ];
			} else {
				// Regular object key
				if ( ! current[ key ] ) {
					// Check if the next key is numeric to decide if we need an array or object
					const nextKey = keys[ i + 1 ];
					current[ key ] = /^\d+$/.test( nextKey ) ? [] : {};
				}
				current = current[ key ];
			}
		}
		
		// Set the final value
		const finalKey = keys[ keys.length - 1 ];
		if ( /^\d+$/.test( finalKey ) ) {
			const numKey = parseInt( finalKey, 10 );
			if ( ! Array.isArray( current ) ) {
				current = [];
			}
			current[ numKey ] = value;
		} else {
			current[ finalKey ] = value;
		}
	};

	/**
	 * Check if a string represents a numeric value.
	 *
	 * @param {string} str - The string to check.
	 * @return {boolean} True if the string is numeric.
	 */
	fp.isNumeric = ( str ) => {
		if ( typeof str !== 'string' || str.trim() === '' ) {
			return false;
		}
		
		// Check for date patterns (YYYY-MM-DD, MM/DD/YYYY, etc.)
		if ( /^\d{4}-\d{2}-\d{2}$/.test( str ) || /^\d{2}\/\d{2}\/\d{4}$/.test( str ) ) {
			return false;
		}
		
		// Use parseFloat and check if it's a valid number
		const num = parseFloat( str );
		return ! isNaN( num ) && isFinite( num ) && str.trim() === num.toString();
	};

	/**
	 * Recursively convert numeric strings to numbers in an object.
	 *
	 * @param {Object} obj - The object to process.
	 */
	fp.convertNumericValues = ( obj ) => {
		if ( Array.isArray( obj ) ) {
			obj.forEach( item => {
				if ( typeof item === 'object' && item !== null ) {
					fp.convertNumericValues( item );
				}
			} );
		} else if ( typeof obj === 'object' && obj !== null ) {
			Object.keys( obj ).forEach( key => {
				const value = obj[ key ];
				
				if ( typeof value === 'string' && fp.isNumeric( value ) ) {
					// Convert to number (parseFloat handles both integers and floats)
					const numValue = parseFloat( value );
					// If it's a whole number, convert to integer
					obj[ key ] = Number.isInteger( numValue ) ? parseInt( value, 10 ) : numValue;
				} else if ( typeof value === 'object' && value !== null ) {
					fp.convertNumericValues( value );
				}
			} );
		}
	};

	// Document Ready Actions
	$( () => {
		const $forms = $( fp.selectors.moduleGenerator );
		
		$forms.each( function() {
			const $form = $( this );

			$form.on( 'submit', ( event ) => {
				fp.moduleGenerate( $form );

				console.log( 'Form', $form );
				event.preventDefault();
			} );
		} );
	} );
} )( jQuery, _ );
