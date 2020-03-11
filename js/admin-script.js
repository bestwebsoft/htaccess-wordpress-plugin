( function( $ ) {
	$( document ).ready( function() {
		/* Add new input into Allow form */
		var tokenAllow = 0;
		$( '.htccss_add_allow_ip_button' ).on( 'click', function( event ) {
			event.preventDefault();
			tokenAllow += 1;

			/* Clone hidden form */
			var newRowAllow = $( '.htccss_allow_form' ).first().clone();

			/* Remove attribute style="display: none" */
			newRowAllow.removeAttr( 'style' );
			newRowAllow.children( 'input' ).each( function() {

				/* Add to end of attribute 'name' a number - for $_POST */
				var name = $( this ).attr( 'name' );
				$( this ).attr( 'name', name + tokenAllow );
			} );

			/* Insert field before button */
			var htccss_allow_form_input = $( '.htccss_allow_form:last-child input' );
			if ( htccss_allow_form_input.eq(0).val() > 0 &&
				htccss_allow_form_input.eq(1).val() > 0 &&
				htccss_allow_form_input.eq(2).val() > 0 &&
				htccss_allow_form_input.eq(3).val() > 0
			) {
				$( '.htccss_allow_container' ).append( newRowAllow.show() );
			}
		} );

		/* Remove input from Allow form.
		 Remove forms which were created by using 'Add IP addres button' */
		$( '.htccss_add_allow_ip_button' ).on( 'click', function() {
			removeAllowForm();
			focusOnNextField();
			validateUserInputData();
		} );

		/* Remove forms which were present on page */
		removeAllowForm();
		function removeAllowForm() {
			$( '.htccss_trash_allow' ).each( function() {
				$( this ).on( 'click', function( event ) {
					event.preventDefault();
					if ( 1 === $( '.htccss_allow_form:visible' ).length )  {
						$( '.htccss_allow_form :input' ).val( '' );
					} else if ( 1 < $( '.htccss_allow_form:visible' ).length ) {
						$( this ).closest( '.htccss_allow_form' ).remove();
					}
				} );
			} );
		}

		/* Add new input into Deny form */
		var tokenDeny = 0;
		$( '.htccss_add_deny_ip_button' ).on( 'click', function( event ) {
			event.preventDefault();
			tokenDeny += 1;

			/* Clone hidden form */
			var newRowDeny = $( '.htccss_deny_form' ).first().clone();

			/* Remove attribute style="display: none" */
			newRowDeny.removeAttr( 'style' );
			newRowDeny.children( 'input' ).each( function() {

				/* Add to and of attribute 'name' a number - for $_POST */
				var name = $( this ).attr( 'name' );
				$( this ).attr( 'name', name + tokenDeny );
			} );

			/* Insert field before button */
			var htccss_deny_form_input = $( '.htccss_deny_form:last-child input' );
			if ( htccss_deny_form_input.eq(0).val() > 0 &&
				htccss_deny_form_input.eq(1).val() > 0 &&
				htccss_deny_form_input.eq(2).val() > 0 &&
				htccss_deny_form_input.eq(3).val() > 0
			) {
				$( '.htccss_deny_container' ).append( newRowDeny.show() );
			}
		} );

		/* Remove input from Deny form
		Remove forms which were created by using 'Add IP addres button' */
		$( '.htccss_add_deny_ip_button' ).on( 'click', function() {
			removeDenyForm();
			focusOnNextField();
			validateUserInputData();
		} );

		/* Remove forms which were present on page */
		removeDenyForm();
		function removeDenyForm() {
			$( '.htccss_trash_deny' ).each( function() {
				$( this ).on( 'click', function( event ) {
					event.preventDefault();
					if ( 1 === $( '.htccss_deny_form:visible' ).length )  {
						$( '.htccss_deny_form :input' ).val( '' );
					} else if ( 1 < $( '.htccss_deny_form:visible' ).length ) {
						$( this ).closest( '.htccss_deny_form' ).remove();
					}
				} );
			} );
		}

		validateUserInputData();

		/* Check data that user is typed. */
		function validateUserInputData() {
			$( '.htccss_ip' ).each( function() {
				$( this ).on( 'keyup', function() {
					var inputValue = $( this ).val();
					if ( ! $.isNumeric( inputValue ) &&
						'' !== inputValue ||
						inputValue.length > 3 ||
						inputValue > 255
					) {
						$( this ).addClass( 'htccss-invalid-value' );
					} else {
						$( this ).removeClass( 'htccss-invalid-value' );
					}
				} );
			} );
		}

		focusOnNextField();
		function focusOnNextField() {
			$( '.htccss_ip' ).each( function() {
				$( this ).on( 'keyup', function( event ) {

					/* Focus on next to left field after removed all characters from current field. */
					if ( 8 == event.which ) {
						var number = $( this ).attr( 'data-numb' );
						var count = $( this ).val().length;
						if( 0 == count && number > 1 ) {
							var ipForms = $( this ).siblings( '.htccss_ip ' );
							ipForms[ number - 2 ].focus();
						}
					} else if ( ! /(16)|(35)|(36)|(37)|(38)|(39)|(40)/.test( event.which ) && 9 != event.which ) {

						/* Focus on next to right field after entered third character into current field. */
						var number = $( this ).attr( 'data-numb' );
						var count = $( this ).val().length;
						if( count >= 3 && number < 4 ) {
							$( this ).next().next().focus();
						}
					}
				} );
			} );

			/* Focus on next to right field on press Enter */
			$( '.htccss_ip' ).each( function() {
				$( this ).on( 'keypress', function( e ) {
					if ( 13 == e.which ) {
						e.preventDefault();
						$( this ).next().next().focus();
					}
				} );
			} );

			/* Paste whole IP addres */
			$( '.htccss_ip' ).each( function() {
				$( this ).bind( 'paste', function( e ) {
					$( e.target ).bind( 'keyup', function( event ) {
						var inputData = $( this ).val();
						var numbers = String( inputData ).match(/\d+/g);
						if ( $.isArray( numbers ) ) {
							var domElement = $( this );
							for ( var i = 0; i < numbers.length; i++ ) {
								domElement.attr( 'value', numbers[ i ] );
								inputValue=domElement.val();
								if ( ! $.isNumeric( inputValue ) &&
									'' !== inputValue ||
									inputValue.length > 3 ||
									inputValue > 255
								) {
									domElement.addClass( 'htccss-invalid-value' );
								} else {
									domElement.removeClass( 'htccss-invalid-value' );
								}
								if ( domElement.attr( 'data-numb' ) < 4 ) {
									domElement = domElement.next().next();
								} else {
									break;
								}
							}
						}
					} );
				} );
			} );
		}

		/* Forbid submitting if user entered an incorrect IP address */
		$( '#htccss_wrap button[type="submit"]' ).on( 'click', function( event ) {
			var test = false;
			$( '.htccss_ip' ).each( function() {
				if ( $( this ).hasClass( 'htccss-invalid-value' ) ) {
					event.preventDefault();
					test = true;
				}
			} );
			if ( test ) {
				$( '.htccss-invalid-value' ).first().focus();
			}
		} );
	} );

} )( jQuery );
