import { useEffect } from '@wordpress/element';

export default function Notices({ items, onDismiss }) {
	useEffect( () => {
		const timers = items.map( ( n, i ) => {
			if ( n.type === 'success' ) {
				return setTimeout( () => onDismiss( i ), 3000 );
			}
			return null;
		} );
		return () => { timers.forEach( ( t ) => t && clearTimeout( t ) ); };
	}, [ items, onDismiss ] );

	if ( ! items.length ) return null;
	return (
		<div className="wpzf-notices">
			{ items.map( ( n, i ) => (
				<div key={ i } className={ 'wpzf-notice-toast wpzf-notice-toast--' + n.type }>
					<span>{ n.text }</span>
					<button onClick={ () => onDismiss( i ) }>×</button>
				</div>
			) ) }
		</div>
	);
}
