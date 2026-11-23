/**
 * WPZOOM AI Connect, Plugins screen: the "Before you go" survey when the plugin's Deactivate link is clicked.
 * Without this script the link deactivates as usual.
 */
( function () {
function start() {
	var cfg = window.wzaiDeactivate;
	var modal = document.getElementById( 'wzai-deact' );
	if ( ! cfg || ! modal ) {
		return;
	}
	var row = document.querySelector( 'tr[data-plugin="' + cfg.plugin + '"]' );
	var link = row && row.querySelector( '.deactivate a' );
	if ( ! link ) {
		return;
	}
	var form = modal.querySelector( '#wzai-deact-form' );
	var submit = modal.querySelector( '[data-wzai-submit]' );
	var skip = modal.querySelector( '[data-wzai-skip]' );
	var score = '';
	var opener = null;

	skip.setAttribute( 'href', link.getAttribute( 'href' ) );

	function open( event ) {
		event.preventDefault();
		opener = document.activeElement;
		modal.hidden = false;
		document.body.classList.add( 'wzai-deact-open' );
		var first = form.querySelector( 'input' );
		if ( first ) {
			first.focus();
		}
	}

	function close() {
		modal.hidden = true;
		document.body.classList.remove( 'wzai-deact-open' );
		if ( opener ) {
			opener.focus();
		}
	}

	function deactivate() {
		window.location.href = link.getAttribute( 'href' );
	}

	link.addEventListener( 'click', open );

	modal.addEventListener( 'click', function ( event ) {
		if ( event.target === modal || event.target.closest( '[data-wzai-close]' ) ) {
			close();
		}
	} );
	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key && ! modal.hidden ) {
			close();
		}
	} );

	// A reason shows its "tell us more" box (and, for setup trouble, where to get help).
	form.addEventListener( 'change', function ( event ) {
		var box = event.target.closest( '.wzai-deact__choice' );
		if ( ! box || 'checkbox' !== event.target.type ) {
			return;
		}
		box.querySelectorAll( '.wzai-deact__text, .wzai-deact__hint' ).forEach( function ( el ) {
			el.hidden = ! event.target.checked;
		} );
		var text = box.querySelector( '.wzai-deact__text' );
		if ( text && event.target.checked ) {
			text.focus();
		}
		submit.disabled = ! form.querySelector( 'input[name="reasons[]"]:checked' );
	} );

	modal.querySelectorAll( '.wzai-deact__score' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			score = button.getAttribute( 'data-score' );
			modal.querySelectorAll( '.wzai-deact__score' ).forEach( function ( b ) {
				b.setAttribute( 'aria-checked', b === button ? 'true' : 'false' );
			} );
		} );
	} );

	submit.addEventListener( 'click', function () {
		var data = new FormData();
		data.append( 'action', 'wpzoom_ai_deactivation_feedback' );
		data.append( 'nonce', cfg.nonce );
		form.querySelectorAll( 'input[name="reasons[]"]:checked' ).forEach( function ( input ) {
			data.append( 'reasons[]', input.value );
			var text = input.closest( '.wzai-deact__choice' ).querySelector( '.wzai-deact__text' );
			if ( text && text.value.trim() ) {
				data.append( text.name, text.value.trim() );
			}
		} );
		data.append( 'nps_score', score );
		if ( modal.querySelector( '#wzai-deact-consent' ).checked ) {
			data.append( 'consent', '1' );
		}
		submit.disabled = true;
		skip.setAttribute( 'aria-disabled', 'true' );
		submit.textContent = cfg.sending;

		// Deactivate whatever happens, at the latest after 6 seconds.
		var done = false;
		function finish() {
			if ( ! done ) {
				done = true;
				deactivate();
			}
		}
		window.setTimeout( finish, 6000 );
		window.fetch( cfg.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } ).then( finish, finish );
	} );
}

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', start );
} else {
	start();
}
}() );
