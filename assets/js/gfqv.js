jQuery( function ( $ ) {

	var $triggers = $( document.querySelectorAll( '.quick-look' ) ); // This is faster.
	var $form_data = $( document.querySelectorAll( '.gfqv-entry-data' ) );
	var close_button = $( document.getElementById( 'gfqv-close' ) );
	var container = $( document.getElementById( 'gfqv-container' ) );

	$triggers.on( 'click', 'a', function ( e ) {
		e.preventDefault();

		var $this = $( this );
		var entry_id = $this.data( 'entry' );

		$.ajax( {
			url: ajaxurl, // This is defined by default in WP Admin
			data: {
				action: 'gfqv_get_entry_data',
				entry_id: entry_id
			},
			dataType: 'JSON',
			method: 'POST'
		} ).done( function ( response ) {

			container.addClass( 'open' );

			var i = 1;
			$.each( $form_data, function ( index, value ) {

				if ( 'undefined' === typeof response[i] ) {
					return;
				}

				$( this ).find( '.entry-data' ).text( response[i] );
				i ++;
			} );

		} ).error( function ( error ) {
			console.log( error );
		} );

	} );

	close_button.click( function ( e ) {
		e.preventDefault();
		container.removeClass( 'open' );
	} );

	$( document ).keyup( function ( e ) {
		if ( 27 === e.keyCode ) {
			container.removeClass( 'open' );
		}
	} );

} );