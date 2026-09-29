/* global wp, jQuery */
( function ( $ ) {
	'use strict';

	// =========================================================================
	// SLOT-BASED IMAGE PICKER  (visual layout preview per section)
	// =========================================================================

	/**
	 * Opens the WP media picker for a single layout slot.
	 * Updates the slot's hidden input, thumbnail, and state class.
	 */
	function openSlotPicker( $slot ) {
		var label = $slot.data( 'label' ) || 'afbeelding';

		var frame = wp.media( {
			title   : 'Selecteer: ' + label,
			button  : { text: 'Gebruik deze afbeelding' },
			multiple: false,
		} );

		frame.on( 'select', function () {
			var att = frame.state().get( 'selection' ).first().toJSON();

			// Pick the largest available thumbnail (not full-res, for speed)
			var thumb = ( att.sizes && att.sizes.medium_large ) ? att.sizes.medium_large.url
			          : ( att.sizes && att.sizes.medium )       ? att.sizes.medium.url
			          : ( att.sizes && att.sizes.large )        ? att.sizes.large.url
			          : att.url;

			$slot.find( 'input[type="hidden"]' ).val( att.id );
			$slot.addClass( 'has-image' );

			// Insert or update the image element
			if ( ! $slot.find( '.dtc-cases-slot__img' ).length ) {
				$slot.prepend(
					'<button type="button" class="dtc-cases-slot__remove" aria-label="Verwijder">&times;</button>' +
					'<img class="dtc-cases-slot__img" src="' + thumb + '" alt="" />'
				);
			} else {
				$slot.find( '.dtc-cases-slot__img' ).attr( 'src', thumb );
			}
		} );

		frame.open();
	}

	// Click anywhere on a slot to pick / change its image
	$( document ).on( 'click', '.dtc-cases-slot', function ( e ) {
		// Don't intercept clicks on the remove button
		if ( $( e.target ).closest( '.dtc-cases-slot__remove' ).length ) {
			return;
		}
		e.preventDefault();
		openSlotPicker( $( this ) );
	} );

	// ×-button: clear slot image
	$( document ).on( 'click', '.dtc-cases-slot__remove', function ( e ) {
		e.stopPropagation();
		var $slot = $( this ).closest( '.dtc-cases-slot' );
		$slot.find( 'input[type="hidden"]' ).val( 0 );
		$slot.removeClass( 'has-image' );
		$slot.find( '.dtc-cases-slot__img' ).remove();
		$( this ).remove();
	} );


	// =========================================================================
	// SORTABLE — drag-and-drop for the generic image container
	// (used by any remaining dtc_cases_images_field() calls)
	// =========================================================================

	var DRAG_HANDLE =
		'<span class="dtc-cases-drag-handle" title="Sleep om te herordenen">' +
			'<svg width="10" height="14" viewBox="0 0 10 14" fill="currentColor" aria-hidden="true">' +
				'<circle cx="2.5" cy="2"  r="1.5"/>' +
				'<circle cx="7.5" cy="2"  r="1.5"/>' +
				'<circle cx="2.5" cy="7"  r="1.5"/>' +
				'<circle cx="7.5" cy="7"  r="1.5"/>' +
				'<circle cx="2.5" cy="12" r="1.5"/>' +
				'<circle cx="7.5" cy="12" r="1.5"/>' +
			'</svg>' +
		'</span>';

	function initSortable( $container ) {
		if ( $container.hasClass( 'ui-sortable' ) ) {
			return;
		}
		$container.sortable( {
			items               : '> .dtc-cases-img-item',
			// No restricted handle — entire card is draggable so it's obvious
			tolerance           : 'pointer',
			placeholder         : 'dtc-cases-img-placeholder',
			forcePlaceholderSize: true,
			start: function ( e, ui ) {
				ui.placeholder.css( {
					width : ui.item.outerWidth(),
					height: ui.item.outerHeight(),
				} );
				$container.addClass( 'dtc-cases-sortable-active' );
			},
			stop: function () {
				$container.removeClass( 'dtc-cases-sortable-active' );
			},
		} );
	}

	$( '.dtc-cases-images-container' ).each( function () {
		initSortable( $( this ) );
	} );


	// =========================================================================
	// MULTIPLE-IMAGE UPLOADER  (generic container)
	// =========================================================================

	$( document ).on( 'click', '.dtc-cases-upload-multiple', function ( e ) {
		e.preventDefault();

		var $btn       = $( this );
		var $container = $btn.siblings( '.dtc-cases-images-container' );
		var fieldName  = $container.data( 'name' );

		var frame = wp.media( {
			title   : 'Select Images',
			button  : { text: 'Add to Case' },
			multiple: true,
		} );

		frame.on( 'select', function () {
			frame.state().get( 'selection' ).each( function ( attachment ) {
				var att   = attachment.toJSON();
				var thumb = ( att.sizes && att.sizes.thumbnail )
					? att.sizes.thumbnail.url
					: att.url;

				var $item = $(
					'<div class="dtc-cases-img-item" data-id="' + att.id + '">' +
						DRAG_HANDLE +
						'<img src="' + thumb + '" alt="" />' +
						'<button type="button" class="dtc-cases-remove-image" aria-label="Remove">&times;</button>' +
						'<input type="hidden" name="' + fieldName + '[]" value="' + att.id + '" />' +
					'</div>'
				);
				$container.append( $item );
			} );
			initSortable( $container );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.dtc-cases-remove-image', function ( e ) {
		e.preventDefault();
		$( this ).closest( '.dtc-cases-img-item' ).remove();
	} );


	// =========================================================================
	// SINGLE-IMAGE UPLOADER  (contact photo)
	// =========================================================================

	$( document ).on( 'click', '.dtc-cases-upload-single', function ( e ) {
		e.preventDefault();

		var $btn     = $( this );
		var $wrap    = $btn.closest( '.dtc-cases-single-image-wrap' );
		var $input   = $wrap.find( 'input[type="hidden"]' );
		var $preview = $wrap.find( '.dtc-cases-single-preview' );
		var $remove  = $wrap.find( '.dtc-cases-remove-single' );

		var frame = wp.media( {
			title   : 'Select Contact Photo',
			button  : { text: 'Use This Image' },
			multiple: false,
		} );

		frame.on( 'select', function () {
			var att   = frame.state().get( 'selection' ).first().toJSON();
			var thumb = ( att.sizes && att.sizes.thumbnail )
				? att.sizes.thumbnail.url
				: att.url;

			$input.val( att.id );
			$preview.html( '<img src="' + thumb + '" alt="" />' );

			if ( ! $remove.length ) {
				$btn.after( '<button type="button" class="button dtc-cases-remove-single">Remove</button>' );
			} else {
				$remove.show();
			}
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.dtc-cases-remove-single', function ( e ) {
		e.preventDefault();
		var $wrap = $( this ).closest( '.dtc-cases-single-image-wrap' );
		$wrap.find( 'input[type="hidden"]' ).val( '' );
		$wrap.find( '.dtc-cases-single-preview' ).html( '' );
		$( this ).remove();
	} );

} )( jQuery );
