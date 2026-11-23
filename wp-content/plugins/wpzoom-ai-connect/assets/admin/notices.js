/**
 * WPZOOM AI Connect admin notices: record a choice without leaving the screen.
 * Without this script the same links still work, through a page reload.
 */
( function () {
	function record( url ) {
		if ( url && window.fetch ) {
			window.fetch( url + '&js=1', { credentials: 'same-origin' } );
		}
	}
	function hide( notice ) {
		notice.style.display = 'none';
	}
	document.addEventListener( 'click', function ( event ) {
		var notice = event.target.closest && event.target.closest( '.wzai-admin-notice' );
		if ( ! notice ) {
			return;
		}
		// WordPress's own close button (is-dismissible): dismiss the setup help, snooze the review notice.
		if ( event.target.closest( '.notice-dismiss' ) ) {
			record( notice.getAttribute( 'data-wzai-close' ) );
			return;
		}
		var link = event.target.closest( 'a[data-wzai-choice]' );
		if ( ! link ) {
			return;
		}
		if ( '_blank' === link.getAttribute( 'target' ) ) {
			// "Leave a review" opens wordpress.org in a new tab through admin-post, which records it.
			hide( notice );
			return;
		}
		event.preventDefault();
		record( link.href );
		hide( notice );
	} );
} )();
