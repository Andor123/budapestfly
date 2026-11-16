/* WPZOOM AI Connect welcome screen: remember the chosen AI, show its guide, copy buttons. */
( function () {
	var root = document.getElementById( 'wzai' );
	if ( ! root ) {
		return;
	}
	var l10n = window.wzaiL10n || { copied: 'Copied', copy: 'Copy' };
	var KEY = 'wzai-client';

	function choose( client, remember ) {
		root.querySelectorAll( '.wzai-choice' ).forEach( function ( b ) {
			b.setAttribute( 'aria-pressed', b.getAttribute( 'data-client' ) === client ? 'true' : 'false' );
		} );
		root.querySelectorAll( '[data-guide]' ).forEach( function ( g ) {
			g.hidden = g.getAttribute( 'data-guide' ) !== client;
		} );
		var open = root.querySelector( '[data-open-ai]' );
		if ( open ) {
			var targets = { claude: [ 'https://claude.ai/new', 'Open Claude' ], chatgpt: [ 'https://chatgpt.com/', 'Open ChatGPT' ], other: [ '#wzai-guide', 'Open your AI app' ] };
			var t = targets[ client ] || targets.claude;
			open.setAttribute( 'href', t[0] );
			open.textContent = ( window.wzaiL10n && window.wzaiL10n[ 'open_' + client ] ) || t[1];
			if ( 'other' === client ) { open.removeAttribute( 'target' ); } else { open.setAttribute( 'target', '_blank' ); }
		}
		if ( remember ) {
			try { window.localStorage.setItem( KEY, client ); } catch ( e ) { /* private mode */ }
		}
	}

	var saved = null;
	try { saved = window.localStorage.getItem( KEY ); } catch ( e ) { /* private mode */ }
	if ( saved && root.querySelector( '.wzai-choice[data-client="' + saved + '"]' ) ) {
		choose( saved, false );
	}
	root.querySelectorAll( '.wzai-choice' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () { choose( b.getAttribute( 'data-client' ), true ); } );
	} );

	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}
		var ta = document.createElement( 'textarea' );
		ta.value = text;
		ta.setAttribute( 'readonly', '' );
		ta.style.position = 'fixed';
		ta.style.top = '-1000px';
		document.body.appendChild( ta );
		ta.select();
		try { document.execCommand( 'copy' ); } catch ( e ) { /* ignore */ }
		document.body.removeChild( ta );
		return Promise.resolve();
	}

	root.querySelectorAll( '[data-copy]' ).forEach( function ( b ) {
		b.addEventListener( 'click', function () {
			copyText( b.getAttribute( 'data-copy' ) ).then( function () {
				var isText = b.classList.contains( 'wzai-copy__btn' );
				var prompt = b.closest( '.wzai-prompt' );
				if ( isText ) {
					b.textContent = l10n.copied;
				}
				if ( prompt ) {
					prompt.classList.add( 'wzai-prompt--copied' );
				}
				window.setTimeout( function () {
					if ( isText ) {
						b.textContent = l10n.copy;
					}
					if ( prompt ) {
						prompt.classList.remove( 'wzai-prompt--copied' );
					}
				}, 1400 );
			} );
		} );
	} );
}() );
