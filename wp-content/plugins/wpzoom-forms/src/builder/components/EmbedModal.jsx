import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';
import { copy } from '@wordpress/icons';

export default function EmbedModal({ shortcode, onClose }) {
	const [ copied, setCopied ] = useState( false );

	const doCopy = () => {
		const done = () => {
			setCopied( true );
			setTimeout( () => setCopied( false ), 1500 );
		};
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( shortcode ).then( done, done );
		} else {
			// Fallback: select-and-copy via a temp textarea.
			const ta = document.createElement( 'textarea' );
			ta.value = shortcode;
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild( ta );
			ta.select();
			try { document.execCommand( 'copy' ); } catch ( _ ) {}
			document.body.removeChild( ta );
			done();
		}
	};

	return (
		<Modal
			title={ __( 'Embed your form', 'wpzoom-forms' ) }
			onRequestClose={ onClose }
			size="small"
		>
			<p>{ __( 'Paste this shortcode into any post or page to display the form:', 'wpzoom-forms' ) }</p>
			<div className="wpzf-embed-code">
				<code>{ shortcode }</code>
				<Button
					variant="secondary"
					icon={ copied ? null : copy }
					onClick={ doCopy }
				>
					{ copied ? __( 'Copied!', 'wpzoom-forms' ) : __( 'Copy', 'wpzoom-forms' ) }
				</Button>
			</div>
			<p className="wpzf-hint">{ __( 'You can also insert the form via the WPZOOM Forms block in the Gutenberg editor.', 'wpzoom-forms' ) }</p>
		</Modal>
	);
}
