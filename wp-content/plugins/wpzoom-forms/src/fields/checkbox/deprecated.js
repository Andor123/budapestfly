
/**
 * WordPress dependencies
 */

import { useBlockProps, RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

const v1 = {
	attributes:   {
		id:            {
			type:      "string",
			default:   ""
		},
		name:          {
			type:      "string",
			default:   ""
		},
		defaultValue:  {
			type:      "boolean",
			default:   false
		},
		label:         {
			type:      "string",
			default:   "Check"
		},
		showLabel:     {
			type:      "boolean",
			default:   true
		},
		required:      {
			type:      "boolean",
			source:    "attribute",
			attribute: "required",
			selector:  "input",
			default:   false
		}
	},
	"supports":     {
		"align":         true,
		"html":          false,
		"shadow":        true,
		"color":         {
			"background": true,
			"gradients":  true,
			"text":       true
		},
		"typography":    {
			"fontSize":   true,
			"lineHeight": true,
			"__experimentalFontFamily": true,
			"__experimentalFontWeight": true,
			"__experimentalFontStyle": true,
			"__experimentalTextTransform": true,
			"__experimentalTextDecoration": true,
			"__experimentalLetterSpacing": true
		},
		"dimensions":    {
			"minHeight":  true
		},
		"spacing":       {
			"margin":     true,
			"padding":    true
		},
		"__experimentalBorder": {
			"color":      true,
			"radius":     true,
			"style":      true,
			"width":      true
		}
	},
	save( { attributes } ) {
		const blockProps = useBlockProps.save();
	    const { id, name, defaultValue, label, showLabel, required } = attributes;
        return (
            <>
                <input
                    type="checkbox"
                    name={ id }
                    id={ id }
                    checked={ true == defaultValue }
                    onChange={ e => {} }
                    required={ !! required }
                    { ...blockProps }
                />

                { showLabel && <label htmlFor={ id }>
                    <RichText.Content
                        tagName="span"
                        value={ label }
                    />
                    { required && <sup className="wp-block-wpzoom-forms-required">{ __( '*', 'wpzoom-forms' ) }</sup> }
                </label> }
            </>
        );
	},
};

const deprecated = [ v1 ];

export default deprecated;