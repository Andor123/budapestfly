/**
 * WordPress dependencies
 */

import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const Save = ( { attributes } ) => {
	const blockProps = useBlockProps.save();
	const { id, name, defaultValue, label, showLabel, required } = attributes;

	return (
		<div { ...blockProps }>
			<input
				type="checkbox"
				name={ id }
				id={ id }
				checked={ true == defaultValue }
				onChange={ e => {} }
				required={ !! required }
				
			/>

			{ showLabel && <label htmlFor={ id }>
				<RichText.Content
					tagName="span"
					value={ label }
				/>
				{ required && <sup className="wp-block-wpzoom-forms-required">{ __( '*', 'wpzoom-forms' ) }</sup> }
			</label> }
		</div>
	);
};

export default Save;