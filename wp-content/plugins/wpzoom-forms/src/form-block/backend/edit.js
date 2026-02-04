/**
 * WordPress dependencies.
 */
import { __ } from "@wordpress/i18n";
import ServerSideRender from '@wordpress/server-side-render';
import {
	Disabled,
	PanelBody,
	Placeholder,
	Button,
	SelectControl,
	RangeControl,
	ComboboxControl,
	__experimentalHStack as HStack
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { Fragment, useEffect } from '@wordpress/element';
import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';

/**
 * Internal dependencies.
 */
import FieldsControls from './controls/fields';
import ButtonControls from './controls/button';


const wpzoomFormsIcon = (
	<svg width="40" height="40" viewBox="0 0 250 300" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path fill-rule="evenodd" clip-rule="evenodd" d="M250 0H50V50H0V300H250V0Z" fill="#083EA7" />
		<path fill-rule="evenodd" clip-rule="evenodd" d="M100 50H200V100H100V50ZM100 150V100H50V250H100V200H150V150H100Z" fill="#1FDE91" />
	</svg>
);

/**
 * Edit component for the Form Block.
 */
export default function Edit({ attributes, setAttributes, clientId }) {
	const { formId } = attributes;


	const _formId = formId && String(formId).trim() != '' ? String(formId) : '-1';
	const posts = useSelect(select => select('core').getEntityRecords('postType', 'wpzf-form', {
		order: 'asc',
		orderby: 'title',
		per_page: -1,
		status: 'publish' // Only show published forms, exclude draft, trash, etc.
	}), []);
	const forms = posts && posts.length > 0 ? posts.map(x => { return { key: String(x.id), name: x.title.raw } }) : [];
	const theForm = forms.find(x => x.key == _formId);

	// Auto-select form if there's only one available and no form is currently selected
	useEffect(() => {
		if (forms.length === 1 && _formId === '-1') {
			setAttributes({ formId: forms[0].key });
		}
	}, [forms, _formId, setAttributes]);


	const formSelect = (
		<ComboboxControl
			label={__('Form', 'wpzoom-forms')}
			placeholder={forms.length < 1 ? __('No forms exist...', 'wpzoom-forms') : __('Select a form...', 'wpzoom-forms')}
			options={forms.map(form => ({ label: form.name, value: form.key }))}
			value={typeof theForm !== 'undefined' ? theForm.key : ''}
			onChange={(value) => setAttributes({ formId: String(value) })}
			allowReset={false}
			__next40pxDefaultSize
		/>
	);

	const formEditLink = (
		<HStack
			expanded={true}
			alignment="right"
		>
			<Button
				variant="link"
				text={__('Edit form', 'wpzoom-forms')}
				icon={<svg viewBox="0 0 24 24"><path d="M20.1 5.1L16.9 2 6.2 12.7l-1.3 4.4 4.5-1.3L20.1 5.1zM4 20.8h8v-1.5H4v1.5z"></path></svg>}
				iconSize={20}
				href={wpzf_formblock.admin_url + 'post.php?post=' + _formId + '&action=edit'}
				style={{ textDecoration: 'none' }}
			/>
		</HStack>
	);

	return (
		<>

			<InspectorControls group="settings">
				<PanelBody title={__('Options', 'wpzoom-forms')}>
					{forms.length > 0 ? formSelect : <Disabled>{formSelect}</Disabled>}
					{'-1' !== _formId && formEditLink}
				</PanelBody>
			</InspectorControls>
			<InspectorControls group="styles">

				<FieldsControls
					attributes={attributes}
					setAttributes={setAttributes}
					clientId={clientId}
				/>

				<ButtonControls
					attributes={attributes}
					setAttributes={setAttributes}
					clientId={clientId}
				/>
			</InspectorControls>


			<Fragment>
				{'-1' != _formId
					? <ServerSideRender
						block="wpzoom-forms/form-block"
						attributes={attributes}
					/>
					: <Placeholder
						icon={wpzoomFormsIcon}
						label={__('Contact Form by WPZOOM', 'wpzoom-forms')}
					>
						{forms.length > 0 ? formSelect : <Disabled>{formSelect}</Disabled>}
					</Placeholder>
				}
			</Fragment>
		</>
	);
}
