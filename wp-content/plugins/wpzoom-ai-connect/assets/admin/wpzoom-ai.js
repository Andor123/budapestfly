/* WPZOOM AI Connect screen: old #links open their tab, remember the chosen AI, show its guide, copy buttons. */
( function () {
	var root = document.getElementById( 'wzai' );
	if ( ! root ) {
		return;
	}
	var l10n = window.wzaiL10n || { copied: 'Copied', copy: 'Copy' };
	var KEY = 'wzai-client';
	var CLIENTS = [ 'claude', 'chatgpt', 'other' ];

	// Links from before the tabs pointed at a section of one long page (#wzai-guide…): open the tab that holds it.
	var hash = window.location.hash.slice( 1 );
	if ( hash && ! document.getElementById( hash ) && l10n.tabs && l10n.tabs[ hash ] ) {
		window.location.replace( l10n.tabs[ hash ] );
		return;
	}

	function choose( client, remember ) {
		root.querySelectorAll( '.wzai-choice' ).forEach( function ( b ) {
			b.setAttribute( 'aria-pressed', b.getAttribute( 'data-client' ) === client ? 'true' : 'false' );
		} );
		root.querySelectorAll( '[data-guide]' ).forEach( function ( g ) {
			g.hidden = g.getAttribute( 'data-guide' ) !== client;
		} );
		var open = root.querySelector( '[data-open-ai]' );
		if ( open ) {
			var targets = { claude: [ 'https://claude.ai/new', 'Open Claude' ], chatgpt: [ 'https://chatgpt.com/', 'Open ChatGPT' ], other: [ l10n.guide_url || '#wzai-guide', 'Open your AI app' ] };
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
	if ( saved && CLIENTS.indexOf( saved ) !== -1 ) {
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

	// "Share feedback": a modal with one question, sent through admin-ajax to WPZOOM's collector.
	var fb = document.getElementById( 'wzai-feedback' );
	if ( fb && l10n.feedback ) {
		var fbForm = fb.querySelector( '[data-wzai-feedback-form]' );
		var fbText = fb.querySelector( 'textarea' );
		var fbSend = fb.querySelector( '[data-wzai-feedback-send]' );
		var fbClose = fb.querySelector( '[data-wzai-feedback-close]' );
		var fbError = fb.querySelector( '.wzai-modal__error' );
		var fbDone = fb.querySelector( '.wzai-modal__done' );
		var fbOpener = null;
		var fbCancel = fbClose.textContent;
		var openFeedback = function () {
			fbOpener = document.activeElement;
			fb.hidden = false;
			fbText.focus();
		};
		var closeFeedback = function () {
			fb.hidden = true;
			if ( ! fbDone.hidden ) {
				// Ready for another message next time.
				fbForm.reset();
				fbForm.hidden = false;
				fbDone.hidden = true;
				fbSend.hidden = false;
				fbSend.disabled = true;
				fbClose.textContent = fbCancel;
			}
			if ( fbOpener ) {
				fbOpener.focus();
			}
		};
		root.querySelectorAll( '[data-wzai-feedback-open]' ).forEach( function ( b ) {
			b.addEventListener( 'click', openFeedback );
		} );
		fbClose.addEventListener( 'click', closeFeedback );
		fb.addEventListener( 'click', function ( event ) {
			if ( event.target === fb ) {
				closeFeedback();
			}
		} );
		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! fb.hidden ) {
				closeFeedback();
			}
		} );
		fbText.addEventListener( 'input', function () {
			fbSend.disabled = ! fbText.value.trim();
		} );
		fbSend.addEventListener( 'click', function () {
			var data = new FormData( fbForm );
			data.append( 'action', 'wpzoom_ai_feedback' );
			data.append( 'nonce', l10n.feedback.nonce );
			fbSend.disabled = true;
			fbSend.textContent = l10n.feedback.sending;
			fbError.hidden = true;
			window.fetch( l10n.feedback.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data } ).then( function ( r ) {
				if ( r.ok ) {
					fbForm.hidden = true;
					fbDone.hidden = false;
					fbSend.hidden = true;
					fbClose.textContent = l10n.close || 'Close';
					fbClose.focus();
					return;
				}
				throw r.status;
			} ).catch( function ( status ) {
				fbError.textContent = 429 === status ? l10n.feedback.soon : l10n.feedback.failed;
				fbError.hidden = false;
				fbSend.disabled = false;
			} ).then( function () {
				fbSend.textContent = l10n.feedback.send;
			} );
		} );
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
