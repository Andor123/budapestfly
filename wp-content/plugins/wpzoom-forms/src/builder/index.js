import { createRoot } from '@wordpress/element';
import App from './App.jsx';
import './style.scss';

document.addEventListener( 'DOMContentLoaded', () => {
	const node = document.getElementById( 'wpzf-builder-root' );
	if ( ! node ) return;
	const formId      = parseInt( node.getAttribute( 'data-form-id' ), 10 ) || 0;
	const presetTpl   = node.getAttribute( 'data-template' ) || '';
	const root = createRoot( node );
	root.render( <App formId={ formId } presetTemplate={ presetTpl } /> );
} );
