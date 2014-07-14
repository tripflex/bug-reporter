jQuery(function($){

	function smyles_clear_bug_form( field_values ){

		$( '#smyles-bug-email' ).removeClass( 'smyles-field-invalid' );
		$( '#smyles-bug-description' ).removeClass( 'smyles-field-invalid' );
		$( '#smyles-bug-details' ).removeClass( 'smyles-field-invalid' );

		if( field_values ){

			$( '#smyles-bug-email' ).val('');
			$( '#smyles-bug-description' ).val('');
			$( '#smyles-bug-details' ).val('');

		}
	}

	function smyles_bug_toggle(){
		$( '#smyles-report-bug' ).toggleClass( 'smyles-open' );
		$( '#smyles-bug-toggle' ).toggleClass( 'smyles-bug-toggle-open' );
	}

	$( '#smyles-bug-toggle' ).click( function () {
		smyles_bug_toggle();
	} );

	$( '#smyles-bug-submit' ).click(function( event ){

		var alert_status;
		var alert_message;

		var email = $( '#smyles-bug-email' ).val();
		var description = $( '#smyles-bug-description' ).val();
		var details = $( '#smyles-bug-details' ).val();
		var nonce = $( '#smyles-bug-nonce' ).val();

		smyles_clear_bug_form( false );
		$( '#smyles-bug-alert' ).html( '' ).removeClass( 'alert-danger alert-success' ).hide();

		if ( ! email ) {
			$( '#smyles-bug-email' ).addClass( 'smyles-field-invalid' ).focus();
			$( '#smyles-bug-alert' ).addClass( 'alert-danger' ).html( 'This field is required!' ).show();
			return false;
		}

		if ( ! description ) {
			$( '#smyles-bug-description' ).addClass( 'smyles-field-invalid' ).focus();
			$( '#smyles-bug-alert' ).addClass( 'alert-danger' ).html( 'This field is required!' ).show();
			return false;
		}

		if ( ! details ){
			$( '#smyles-bug-details' ).addClass( 'smyles-field-invalid' ).focus();
			$( '#smyles-bug-alert' ).addClass( 'alert-danger' ).html( 'This field is required!' ).show();
			return false;
		}

		jQuery.ajax( ajaxurl, {
			type: 'POST',
			dataType: 'JSON',
			data: {
				action: 'smyles_submit_bug',
				nonce: nonce,
				email: email,
				description: description,
				details: details

			}, beforeSend: function () {

				$( '#smyles-bug-form' ).hide();
				$( '#smyles-bug-spin' ).show();

			}, error: function ( request, status, error ) {

			}, success: function ( data ) {

				if( data.status == 'success' ){
					alert_status = 'alert-success';
					alert_message = smyles_bug_report.submit_success;
				} else {
					alert_status = 'alert-danger';
					alert_message = smyles_bug_report.submit_error;
				}

				$( '#smyles-bug-spin' ).hide();
				$( '#smyles-bug-alert' ).addClass( alert_status ).html( alert_message ).show();

			}, complete: function () {

				smyles_clear_bug_form( true );

				setTimeout(function(){
					smyles_bug_toggle();
					$( '#smyles-bug-form' ).show();
				}, 2000);

			}
		} );
	});

});