/**
 * Support button: expand and collapse the panel of donation links.
 */
( function () {
	'use strict';

	var widget = document.querySelector( '[data-dp-support]' );
	if ( ! widget ) {
		return;
	}

	var toggle = widget.querySelector( '.dp-support__toggle' );
	var panel = widget.querySelector( '.dp-support__panel' );
	if ( ! toggle || ! panel ) {
		return;
	}

	function setOpen( open ) {
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		panel.hidden = ! open;
	}

	setOpen( false );

	toggle.addEventListener( 'click', function () {
		setOpen( toggle.getAttribute( 'aria-expanded' ) !== 'true' );
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! widget.contains( event.target ) ) {
			setOpen( false );
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' ) {
			setOpen( false );
		}
	} );
} )();
