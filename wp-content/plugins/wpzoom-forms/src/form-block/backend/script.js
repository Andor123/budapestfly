import { 
	InspectorControls,
	PanelColorSettings 
} from '@wordpress/block-editor';

import { 
	registerBlockType, 
	updateCategory 
} from '@wordpress/blocks';

import { 
	Disabled, 
	PanelBody, 
	Placeholder, 
	Button, 
	SelectControl, 
	RangeControl, 
	__experimentalHStack as HStack 
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

import SearchableSelectControl from './searchable-select';

const wpzoomFormsIcon = (
<svg width="40" height="40" viewBox="0 0 250 300" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M250 0H50V50H0V300H250V0Z" fill="#083EA7"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M100 50H200V100H100V50ZM100 150V100H50V250H100V200H150V150H100Z" fill="#1FDE91"/>
</svg>
);

updateCategory( 'wpzoom-blocks', {
	icon: (
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 2C0 0.895431 0.895431 0 2 0H14C15.1046 0 16 0.895431 16 2V14C16 15.1046 15.1046 16 14 16H2C0.895431 16 0 15.1046 0 14V2Z" fill="#164777"/>
        <path d="M5.276 12.084H6.032L8.156 7.224L10.268 12.084H11.024L13.148 5.316H13.988V4.104H10.628V5.316H11.708L10.508 9.468L8.552 4.872H7.832L5.876 9.468L4.592 5.316H5.636V4.104H2.276V5.316H3.116L5.276 12.084Z" fill="white"/>
        </svg>

	)
} );

registerBlockType( 'wpzoom-forms/form-block', {
	title:       __( 'Contact Form by WPZOOM', 'wpzoom-blocks' ),
	description: __( 'A contact form block for accepting submissions from users.', 'wpzoom-blocks' ),
    keywords: [
        __( 'contact', 'wpzoom-blocks' ),
        __( 'form', 'wpzoom-blocks' ),
        __( 'survey', 'wpzoom-blocks' ),
    ],
	icon:        wpzoomFormsIcon,
	category:    'wpzoom-blocks',
	supports:    { align: true, html: false },
	example:     {},
	edit:        props => {
		const { attributes, setAttributes } = props;
		const { formId, align, formBgColor, formBrdWidth, formBrdStyle, formBrdRadius, formBrdColor, fieldBgColor, fieldBrdStyle, fieldBrdWidth, fieldBrdRadius, fieldBrdColor, fieldTextColor, labelTextColor, btnBrdRadius, btnBrdStyle, btnTextColor, btnBrdWidth, btnBrdColor, btnBgColor  } = attributes;
		const _formId = formId && String( formId ).trim() != '' ? String( formId ) : '-1';
		const posts = useSelect( select => select( 'core' ).getEntityRecords( 'postType', 'wpzf-form', { order: 'asc', orderby: 'title', per_page: -1 } ), [] );
		const forms = posts && posts.length > 0 ? posts.map( x => { return { key: String( x.id ), name: x.title.raw } } ) : [];
		const theForm = forms.find( x => x.key == _formId );

		const formSelect = (
			<SearchableSelectControl
				label={ __( 'Form', 'wpzoom-forms' ) }
				selectPlaceholder={ forms.length < 1 ? __( 'No forms exist...', 'wpzoom-forms' ) : __( 'Select a form...', 'wpzoom-forms' ) }
				searchPlaceholder={ __( 'Search...', 'wpzoom-forms' ) }
				noResultsLabel={ __( 'Nothing found...', 'wpzoom-forms' ) }
				options={ forms }
				value={ typeof theForm !== 'undefined' ? theForm : '' }
				onChange={ ( value ) => setAttributes( { formId: String( value.selectedItem.key ) } ) }
			/>
		);

		const formEditLink = (
			<HStack
				expanded={ true }
				alignment="right"
			>
				<Button
					variant="link"
					text={ __( 'Edit form', 'wpzoom-forms' ) }
					icon={ <svg viewBox="0 0 24 24"><path d="M20.1 5.1L16.9 2 6.2 12.7l-1.3 4.4 4.5-1.3L20.1 5.1zM4 20.8h8v-1.5H4v1.5z"></path></svg> }
					iconSize={ 20 }
					href={ wpzf_formblock.admin_url + 'post.php?post=' + _formId + '&action=edit' }
					style={ { textDecoration: 'none' } }
				/>
			</HStack>
		);

		return (
			<>
				<InspectorControls group="settings">
					<PanelBody title={ __( 'Options', 'wpzoom-forms' ) }>
						{ forms.length > 0 ? formSelect : <Disabled>{ formSelect }</Disabled> }
						{ '-1' !== _formId && formEditLink }
					</PanelBody>
				</InspectorControls>
				<InspectorControls group="styles">
					<PanelBody title={ __( 'Fields', 'wpzoom-forms' ) }>
						<SelectControl
							label={ __( 'Border Style', 'wpzoom-forms' ) }
							value={ fieldBrdStyle }
							options={ [
								{ label: __( 'Default', 'wpzoom-forms' ), value: 'default' },
								{ label: __( 'Solid', 'wpzoom-forms' ), value: 'solid' },
								{ label: __( 'Dashed', 'wpzoom-forms' ), value: 'dashed' },
								{ label: __( 'Dotted', 'wpzoom-forms' ), value: 'dotted' },
								{ label: __( 'None', 'wpzoom-forms' ), value: 'none' },
							] }
							onChange={ ( value ) => setAttributes( { fieldBrdStyle: value } )}
						/>
						<RangeControl
							disabled={ fieldBrdStyle == 'default' }
							label={__('Border Width', 'wpzoom-forms')}
							value={ fieldBrdWidth }
							onChange={ (value) => setAttributes( { fieldBrdWidth: value } ) }
							min={0}
							max={20}
						/>
						<RangeControl
							disabled={ fieldBrdStyle == 'default' }
							label={ __( 'Border Radius', 'wpzoom-forms' ) }
							value={ fieldBrdRadius }
							onChange={ ( value ) => setAttributes( { fieldBrdRadius: value } ) }
							min={0}
							max={50}
						/>
						<PanelColorSettings
							title={ __( 'Color Settings', 'wpzoom-formsn' ) }
							initialOpen={ false }
							colorSettings={ [
								{
									value: fieldTextColor,
									onChange: ( value ) => setAttributes( { fieldTextColor: value } ),
									label: __( 'Text Color', 'wpzoom-forms' ),
								},
								{
									value: fieldBrdColor,
									onChange: ( value ) => setAttributes( { fieldBrdColor: value } ),
									label: __( 'Border Color', 'wpzoom-forms' ),
								},
								{
									value: fieldBgColor,
									onChange: (value) => setAttributes( { fieldBgColor: value } ),
									label: __( 'Background Color', 'wpzoom-forms' ),
								},
							] }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Labels', 'wpzoom-forms' ) }>
						<PanelColorSettings
							title={ __( 'Color Settings', 'wpzoom-formsn' ) }
							initialOpen={ false }
							colorSettings={ [
								{
									value: labelTextColor,
									onChange: ( value ) => setAttributes( { labelTextColor: value } ),
									label: __( 'Color', 'wpzoom-forms' ),
								},
							] }
							/>
					</PanelBody>
					<PanelBody title={ __( 'Button', 'wpzoom-forms' ) }>
						<SelectControl
							label={ __( 'Border Style', 'wpzoom-forms' ) }
							value={ btnBrdStyle }
							options={ [
								{ label: __( 'Default', 'wpzoom-forms' ), value: 'default' },
								{ label: __( 'Solid', 'wpzoom-forms' ), value: 'solid' },
								{ label: __( 'Dashed', 'wpzoom-forms' ), value: 'dashed' },
								{ label: __( 'Dotted', 'wpzoom-forms' ), value: 'dotted' },
								{ label: __( 'None', 'wpzoom-forms' ), value: 'none' },
							] }
							onChange={ ( value ) => setAttributes( { btnBrdStyle: value } )}
						/>
						<RangeControl
							disabled={ btnBrdStyle == 'default' }
							label={ __( 'Border Width', 'wpzoom-forms' ) }
							value={ btnBrdWidth }
							onChange={ (value) => setAttributes( { btnBrdWidth: value } ) }
							min={0}
							max={20}
						/>
						<RangeControl
							disabled={ btnBrdStyle == 'default' }
							label={ __( 'Border Radius', 'wpzoom-forms' ) }
							value={ btnBrdRadius }
							onChange={ ( value ) => setAttributes( { btnBrdRadius: value } ) }
							min={0}
							max={50}
						/>
						<PanelColorSettings
							title={ __( 'Color Settings', 'wpzoom-formsn' ) }
							initialOpen={ false }
							colorSettings={ [
								{
									value: btnTextColor,
									onChange: ( value ) => setAttributes( { btnTextColor: value } ),
									label: __( 'Text Color', 'wpzoom-forms' ),
								},
								{
									value: btnBrdColor,
									onChange: ( value ) => setAttributes( { btnBrdColor: value } ),
									label: __( 'Border Color', 'wpzoom-forms' ),
								},
								{
									value: btnBgColor,
									onChange: (value) => setAttributes( { btnBgColor: value } ),
									label: __( 'Background Color', 'wpzoom-forms' ),
								},
							] }
						/>
					</PanelBody>
				</InspectorControls>


				<Fragment>
					{ '-1' != _formId
						? <ServerSideRender
							block="wpzoom-forms/form-block"
							attributes={ attributes }
						  />
						: <Placeholder
							icon={ wpzoomFormsIcon }
							label={ __( 'Contact Form by WPZOOM', 'wpzoom-forms' ) }
						  >
							{ forms.length > 0 ? formSelect : <Disabled>{ formSelect }</Disabled> }
						  </Placeholder>
					}
				</Fragment>
			</>
		);
	}
} );