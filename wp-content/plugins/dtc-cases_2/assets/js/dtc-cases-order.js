/* global dtcCasesOrder, jQuery */
( function ( $ ) {
	'use strict';

	var $tbody  = $( '#the-list' );
	var $notice = $( '#dtc-order-notice' );
	var saveTimer;

	if ( ! $tbody.length ) {
		return;
	}

	// ── Inject status notice above the table ─────────────────────────────────
	$( '<div id="dtc-order-notice" class="dtc-order-notice"></div>' )
		.insertBefore( $( '.wp-list-table' ) );
	$notice = $( '#dtc-order-notice' );

	// ── Make the list table body sortable ────────────────────────────────────
	$tbody.sortable( {
		items:       '> tr',
		handle:      '.dtc-drag-handle',
		axis:        'y',
		cursor:      'grabbing',
		placeholder: 'dtc-row-placeholder',
		tolerance:   'pointer',
		start: function ( e, ui ) {
			// Keep row width stable while dragging.
			ui.placeholder.height( ui.item.outerHeight() );
			ui.item.css( 'opacity', '0.75' );
		},
		stop: function ( e, ui ) {
			ui.item.css( 'opacity', '' );
			restripeRows();
		},
		update: function () {
			clearTimeout( saveTimer );
			saveTimer = setTimeout( saveOrder, 400 );
		},
	} );

	// ── Helpers ───────────────────────────────────────────────────────────────
	function restripeRows() {
		$tbody.find( '> tr' ).each( function ( i ) {
			$( this ).toggleClass( 'alternate', i % 2 === 1 );
		} );
	}

	function saveOrder() {
		var order = [];
		$tbody.find( '> tr' ).each( function () {
			var id = parseInt( $( this ).attr( 'id' ).replace( 'post-', '' ), 10 );
			if ( id ) {
				order.push( id );
			}
		} );

		setStatus( 'saving', dtcCasesOrder.saving );

		$.post( dtcCasesOrder.ajaxurl, {
			action: 'dtc_cases_save_order',
			nonce:  dtcCasesOrder.nonce,
			order:  order,
		} )
		.done( function ( res ) {
			setStatus( res.success ? 'saved' : 'error',
				res.success ? dtcCasesOrder.saved : dtcCasesOrder.error );
		} )
		.fail( function () {
			setStatus( 'error', dtcCasesOrder.error );
		} );
	}

	function setStatus( state, msg ) {
		$notice
			.text( msg )
			.removeClass( 'is-saving is-saved is-error' )
			.addClass( 'is-' + state )
			.show();

		if ( 'saved' === state ) {
			clearTimeout( $notice.data( 'hideTimer' ) );
			$notice.data( 'hideTimer', setTimeout( function () {
				$notice.fadeOut( 400 );
			}, 2500 ) );
		}
	}

} )( jQuery );
