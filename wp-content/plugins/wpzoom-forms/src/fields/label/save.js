import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const Save = ({ attributes }) => {
	const blockProps = useBlockProps.save();
	const { name, forInput, required } = attributes;

	// Create label props object
	const labelProps = {
		...blockProps,
		htmlFor: forInput || ''
	};

	// Only add data-required attribute if required is true
	if (required === true) {
		labelProps['data-required'] = true;
	}

	return (
		<>
			<label {...labelProps}>
				{name}
			</label>
			{required && (
				<sup className="wp-block-wpzoom-forms-required">*</sup>
			)}
		</>
	);
};

export default Save;