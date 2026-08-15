/**
 * Settings page: add and remove donation method rows.
 */
( function () {
	'use strict';

	var wrap = document.getElementById( 'dpsb-methods' );
	var add = document.getElementById( 'dpsb-add' );
	var tpl = document.getElementById( 'dpsb-row-template' );
	if ( ! wrap || ! add || ! tpl ) {
		return;
	}

	var next = wrap.querySelectorAll( '.dpsb-row' ).length;

	add.addEventListener( 'click', function () {
		var html = tpl.innerHTML.replace( /__i__/g, String( next++ ) ).trim();
		var holder = document.createElement( 'div' );
		holder.innerHTML = html;
		var row = holder.firstElementChild;
		if ( row ) {
			wrap.appendChild( row );
			var input = row.querySelector( 'input' );
			if ( input ) {
				input.focus();
			}
		}
	} );

	wrap.addEventListener( 'click', function ( event ) {
		var remove = event.target.closest( '.dpsb-remove' );
		if ( ! remove ) {
			return;
		}
		var row = remove.closest( '.dpsb-row' );
		if ( row ) {
			row.remove();
		}
	} );
} )();
