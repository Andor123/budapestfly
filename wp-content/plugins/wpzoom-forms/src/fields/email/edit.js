import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { Fragment, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl, SelectControl } from '@wordpress/components';

const Edit = props => {
	const blockProps = useBlockProps();
	const { attributes, setAttributes, clientId } = props;
	const { id, name, placeholder, label, showLabel, required, replyto } = attributes;

	useEffect( () => {
		if ( ! id ) {
			setAttributes( { id: 'input_' + clientId.substr( 0, 8 ) } );
		}
	}, [] );

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Options', 'wpzoom-forms' ) }>
				<TextControl
					label={ __( 'Name', 'wpzoom-forms' ) }
					value={ name }
					placeholder={ __( 'e.g. My Email Field', 'wpzoom-forms' ) }
					onChange={ value => setAttributes( { name: value } ) }
					__next40pxDefaultSize
				/>

				<TextControl
					label={ __( 'Placeholder', 'wpzoom-forms' ) }
					value={ placeholder }
					onChange={ value => setAttributes( { placeholder: value } ) }
					__next40pxDefaultSize
				/>

				<ToggleControl
					label={ __( 'Show Label', 'wpzoom-forms' ) }
					checked={ !! showLabel }
					onChange={ value => setAttributes( { showLabel: !! value } ) }
					__next40pxDefaultSize
				/>

				{ showLabel && <TextControl
					label={ __( 'Label', 'wpzoom-forms' ) }
					value={ label }
					onChange={ value => setAttributes( { label: value } ) }
					__next40pxDefaultSize
				/> }

				<ToggleControl
					label={ __( 'Required', 'wpzoom-forms' ) }
					checked={ !! required }
					onChange={ value => setAttributes( { required: !! value } ) }
					__next40pxDefaultSize
				/>

				<ToggleControl
					label={ __( 'Is Reply-To Address', 'wpzoom-forms' ) }
					help={ __( 'Whether this email field should be used as the reply-to address in the form (useful for contact forms).', 'wpzoom-forms' ) }
					checked={ !! replyto }
					onChange={ value => setAttributes( { replyto: !! value } ) }
					__next40pxDefaultSize
				/>
			</PanelBody>
		</InspectorControls>

		<Fragment>
			{ showLabel && <>
				<RichText
					tagName="label"
					placeholder={ __( 'Label', 'wpzoom-forms' ) }
					value={ label }
					htmlFor={ id }
					onChange={ value => setAttributes( { label: value } ) }
				/>
				{ required && <sup className="wp-block-wpzoom-forms-required">{ __( '*', 'wpzoom-forms' ) }</sup> }
			</> }

			<input
				type="email"
				name={ id }
				id={ id }
				placeholder={ placeholder }
				required={ !! required }
				data-replyto={ !! replyto }
				{ ...blockProps }
			/>
		</Fragment>
	</>;
};

export default Edit;