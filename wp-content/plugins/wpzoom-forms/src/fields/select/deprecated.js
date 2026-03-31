import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const SaveLegacySelectDefaultValue = ( { attributes } ) => {
	const blockProps = useBlockProps.save();
	const { id, options, defaultValue, label, showLabel, multiple, required } = attributes;

	return <>
		{ showLabel && <label htmlFor={ id }>
			<RichText.Content
				tagName="span"
				value={ label }
			/>
			{ required && <sup className="wp-block-wpzoom-forms-required">{ __( '*', 'wpzoom-forms' ) }</sup> }
		</label> }

		<select
			name={ multiple ? `${id}[]` : id }
			id={ id }
			required={ !! required }
			multiple={ !! multiple }
			defaultValue={ defaultValue }
			{ ...blockProps }
		>
			{ options.map( ( option, index ) => <option key={ index } value={ option }>{ option }</option> ) }
		</select>
	</>;
};

export default [
	{
		attributes: {
			id: {
				type: 'string',
				default: ''
			},
			name: {
				type: 'string',
				default: 'Select an option'
			},
			options: {
				type: 'array',
				default: [ 'Item #1' ]
			},
			defaultValue: {
				type: 'string',
				source: 'attribute',
				attribute: 'defaultvalue',
				selector: 'select',
				default: 'Item #1'
			},
			label: {
				type: 'string',
				default: 'Select an option'
			},
			showLabel: {
				type: 'boolean',
				default: true
			},
			multiple: {
				type: 'boolean',
				source: 'attribute',
				attribute: 'multiple',
				selector: 'select',
				default: false
			},
			required: {
				type: 'boolean',
				source: 'attribute',
				attribute: 'required',
				selector: 'select',
				default: false
			}
		},
		save: SaveLegacySelectDefaultValue,
		migrate: ( attributes ) => ( {
			...attributes,
			defaultValue: Array.isArray( attributes.options ) && attributes.options.includes( attributes.defaultValue )
				? attributes.defaultValue
				: ( attributes.options?.[0] || '' ),
		} ),
	},
];
