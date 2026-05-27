import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { api } from '../api';
import { cls } from '../utils';

const BLANK = {
	id:     '',
	name:   'Blank Form',
	desc:   'Start from scratch with an empty canvas.',
	icon:   '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16M4 12h16"/></svg>',
	isPro:  false,
	schema: { fields: [] },
};

const UPGRADE_URL  = 'https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=template-picker-upsell';
const AI_UPSELL_URL = 'https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=ai-generator-upsell-template-picker';
const ALL_TEMPLATES_URL = 'https://zoomforms.co/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=view-all-templates-template-picker';

/**
 * Initial screen shown when the builder is opened with no form id.
 * Two-column layout: list of templates on the left, a live preview on
 * the right. Selecting a template calls POST /forms?template=<id> and
 * navigates to the builder URL of the freshly created form.
 */
export default function TemplatePicker({ presetTemplate }) {
	const [ templates,  setTemplates ]  = useState( null );
	const [ selectedId, setSelectedId ] = useState( null );
	const [ creating,   setCreating ]   = useState( false );
	const [ error,      setError ]      = useState( '' );

	useEffect( () => {
		api.getTemplates()
			.then( ( list ) => {
				setTemplates( list );
				setSelectedId( ( list && list.length ) ? list[0].id : '' );
			} )
			.catch( ( e ) => setError( e.message || __( 'Could not load templates', 'wpzoom-forms' ) ) );
	}, [] );

	// If we arrived with ?template=<id>, create immediately (legacy direct-link flow).
	useEffect( () => {
		if ( ! presetTemplate ) return;
		create( presetTemplate );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ presetTemplate ] );

	const create = ( templateId ) => {
		setCreating( true );
		setError( '' );
		api.createForm( templateId ? { template: templateId } : {} )
			.then( ( form ) => {
				const url = window.wpzfBuilder.newFormUrl + '&id=' + form.id + '&new=1';
				window.location.replace( url );
			} )
			.catch( ( e ) => {
				setCreating( false );
				setError( e.message || __( 'Could not create form', 'wpzoom-forms' ) );
			} );
	};

	if ( error && ! templates ) {
		return <div className="wpzf-picker wpzf-picker--error"><p>{ error }</p></div>;
	}
	if ( templates === null || creating ) {
		return (
			<div className="wpzf-picker wpzf-picker--loading">
				<div className="wpzf-spinner" aria-label={ __( 'Loading…', 'wpzoom-forms' ) } />
				{ creating && <p style={ { marginTop: 16, color: '#6b7280' } }>{ __( 'Creating your form…', 'wpzoom-forms' ) }</p> }
			</div>
		);
	}

	const items = [ BLANK, ...templates ];
	const selected = items.find( ( t ) => t.id === selectedId ) || items[0];
	const proCount = templates.filter( ( t ) => t.isPro ).length;

	return (
		<div className="wpzf-picker">
			<header className="wpzf-picker__header">
				<a className="wpzf-topbar__exit" href={ window.wpzfBuilder.formsListUrl } title={ __( 'Back to forms', 'wpzoom-forms' ) } aria-label={ __( 'Back to forms', 'wpzoom-forms' ) }>
					<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M15.4 7.4L14 6l-6 6 6 6 1.4-1.4L10.8 12z"/></svg>
				</a>
				<div className="wpzf-picker__heading">
					<h1>{ __( 'Choose a template', 'wpzoom-forms' ) }</h1>
					<p>{ __( 'Pick a template to preview it, then click "Use This Template" to start building.', 'wpzoom-forms' ) }</p>
				</div>
				<div className="wpzf-picker__ai-upsell">
					<span className="wpzf-picker__ai-upsell-icon">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10.6144 17.7956 11.492 15.7854C12.2731 13.9966 13.6789 12.5726 15.4325 11.7942L17.8482 10.7219C18.6162 10.381 18.6162 9.26368 17.8482 8.92277L15.5079 7.88394C13.7092 7.08552 12.2782 5.60881 11.5105 3.75894L10.6215 1.61673C10.2916.821765 9.19319.821767 8.8633 1.61673L7.97427 3.75892C7.20657 5.60881 5.77553 7.08552 3.97685 7.88394L1.63658 8.92277C.868537 9.26368.868536 10.381 1.63658 10.7219L4.0523 11.7942C5.80589 12.5726 7.21171 13.9966 7.99275 15.7854L8.8704 17.7956C9.20776 18.5682 10.277 18.5682 10.6144 17.7956ZM19.4014 22.6899 19.6482 22.1242C20.0882 21.1156 20.8807 20.3125 21.8695 19.8732L22.6299 19.5353C23.0412 19.3526 23.0412 18.7549 22.6299 18.5722L21.9121 18.2532C20.8978 17.8026 20.0911 16.9698 19.6586 15.9269L19.4052 15.3156C19.2285 14.8896 18.6395 14.8896 18.4628 15.3156L18.2094 15.9269C17.777 16.9698 16.9703 17.8026 15.956 18.2532L15.2381 18.5722C14.8269 18.7549 14.8269 19.3526 15.2381 19.5353L15.9985 19.8732C16.9874 20.3125 17.7798 21.1156 18.2198 22.1242L18.4667 22.6899C18.6473 23.104 19.2207 23.104 19.4014 22.6899Z"/></svg>
					</span>
					<div className="wpzf-picker__ai-upsell-text">
						<strong>{ __( 'Generate Forms with AI', 'wpzoom-forms' ) }</strong>
						<span>{ __( 'Describe your form in simple words and let AI create it for you instantly.', 'wpzoom-forms' ) }</span>
					</div>
					<a className="wpzf-btn wpzf-btn--primary" href={ AI_UPSELL_URL } target="_blank" rel="noreferrer">{ __( 'Upgrade to PRO', 'wpzoom-forms' ) }</a>
				</div>
			</header>

			{ error && <div className="wpzf-picker__error">{ error }</div> }

			<div className="wpzf-picker__split">
				<aside className="wpzf-picker__list">
					{ items.map( ( t ) => (
						<button
							key={ t.id || 'blank' }
							className={ cls( 'wpzf-picker__item', selected.id === t.id && 'is-selected', t.isPro && 'is-pro' ) }
							onClick={ () => setSelectedId( t.id ) }
							onDoubleClick={ () => ! t.isPro && create( t.id ) }
						>
							<span className="wpzf-picker__item-icon" dangerouslySetInnerHTML={ { __html: t.icon || '' } } />
							<span className="wpzf-picker__item-text">
								<span className="wpzf-picker__item-name">
									{ t.name }
									{ t.isPro && <span className="wpzf-picker__pro-badge">PRO</span> }
								</span>
								<span className="wpzf-picker__item-desc">{ t.desc }</span>
							</span>
						</button>
					) ) }

					<a className="wpzf-picker__item wpzf-picker__item--cta" href={ ALL_TEMPLATES_URL } target="_blank" rel="noreferrer">
						<span className="wpzf-picker__item-icon">
							<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 -960 960 960" fill="currentColor"><path d="M440-140H212.31Q182-140 161-161q-21-21-21-51.31v-535.38Q140-778 161-799q21-21 51.31-21H440v680Zm80-380v-300h227.69Q778-820 799-799q21 21 21 51.31V-520H520Zm0 380v-300h300v227.69Q820-182 799-161q-21 21-51.31 21H520Z"/></svg>
						</span>
						<span className="wpzf-picker__item-text">
							<span className="wpzf-picker__item-name">{ __( 'View All 30+ Templates', 'wpzoom-forms' ) }</span>
							<span className="wpzf-picker__item-desc">{ __( 'Explore our complete collection of professional form templates.', 'wpzoom-forms' ) }</span>
						</span>
					</a>
				</aside>

				<section className="wpzf-picker__preview">
					<div className="wpzf-picker__preview-header">
						<div>
							<h2>
								{ selected.name }
								{ selected.isPro && <span className="wpzf-picker__pro-badge">PRO</span> }
							</h2>
							<p>{ selected.desc }</p>
						</div>
						{ selected.isPro ? (
							<a className="wpzf-btn wpzf-btn--primary" href={ UPGRADE_URL } target="_blank" rel="noreferrer">
								{ __( 'Upgrade to PRO', 'wpzoom-forms' ) }
							</a>
						) : (
							<button className="wpzf-btn wpzf-btn--primary" onClick={ () => create( selected.id ) }>
								{ selected.id ? __( 'Use This Template', 'wpzoom-forms' ) : __( 'Start Blank', 'wpzoom-forms' ) }
							</button>
						) }
					</div>
					<div className="wpzf-picker__preview-canvas">
						{ selected.isPro
							? <ProTemplatePlaceholder template={ selected } proCount={ proCount } />
							: <TemplatePreview schema={ selected.schema } />
						}
					</div>
				</section>
			</div>
		</div>
	);
}

function ProTemplatePlaceholder({ template, proCount }) {
	return (
		<div className="wpzf-picker__pro-placeholder">
			<div className="wpzf-picker__pro-placeholder-icon" dangerouslySetInnerHTML={ { __html: template.icon || '' } } />
			<h3>{ template.name }</h3>
			<p>{ template.desc }</p>
			<p className="wpzf-picker__pro-placeholder-hint">
				{ proCount > 0
					? __( 'This template plus dozens more are unlocked in WPZOOM Forms PRO — along with AI form generation, conditional logic, file uploads, Mailchimp integration and more.', 'wpzoom-forms' )
					: __( 'This template is available in WPZOOM Forms PRO.', 'wpzoom-forms' ) }
			</p>
			<a className="wpzf-btn wpzf-btn--primary" href={ UPGRADE_URL } target="_blank" rel="noreferrer">{ __( 'Upgrade to PRO', 'wpzoom-forms' ) }</a>
		</div>
	);
}

/**
 * Renders a faithful, read-only preview of a form schema. Mirrors the public
 * markup so the preview matches what the user will see on the live site.
 */
function TemplatePreview({ schema }) {
	if ( ! schema || ! schema.fields || schema.fields.length === 0 ) {
		return (
			<div className="wpzf-picker__empty">
				<p>{ __( 'A blank canvas. Drag in any field from the palette once you start.', 'wpzoom-forms' ) }</p>
			</div>
		);
	}

	const submitLabel = ( schema.settings && schema.settings.submitLabel ) || 'Submit';
	const submitAlign = ( schema.settings && schema.settings.submitAlign ) || 'left';

	return (
		<form className="wpzf-preview-form" onSubmit={ ( e ) => e.preventDefault() }>
			<div className="wpzf-preview-form__fields">
				{ schema.fields.map( ( f ) => (
					<div key={ f.id } className={ `wpzf-preview-form__row wpzf-preview-form__row--w-${ f.width || 'full' }` }>
						<PreviewField field={ f } />
					</div>
				) ) }
			</div>
			<div className={ `wpzf-preview-form__submit wpzf-preview-form__submit--${ submitAlign }` }>
				<button type="button" className="wpzf-preview-form__btn" disabled>{ submitLabel }</button>
			</div>
		</form>
	);
}

function PreviewField({ field }) {
	const required = field.required ? <span className="wpzf-required"> *</span> : null;
	const label    = field.label ? <label className="wpzf-preview-form__label">{ field.label }{ required }</label> : null;

	switch ( field.type ) {
		case 'heading': {
			const Tag = field.level || 'h3';
			return <Tag className="wpzf-preview-form__heading">{ field.text }</Tag>;
		}
		case 'paragraph':
			// Paragraph text may contain inline HTML (e.g. <strong>, <a>) which the
			// frontend renders via wp_kses_post(). Mirror that here so the preview
			// shows the formatting instead of raw tags.
			return <div className="wpzf-preview-form__paragraph" dangerouslySetInnerHTML={ { __html: field.text || '' } } />;
		case 'divider':
			return <hr className="wpzf-preview-form__divider" />;
		case 'textarea':
			return <>{ label }<textarea className="wpzf-preview-form__input" rows={ field.rows || 4 } placeholder={ field.placeholder || ' ' } disabled /></>;
		case 'select':
			return <>{ label }
				<select className="wpzf-preview-form__input" disabled>
					<option>{ field.placeholder || '— Select —' }</option>
					{ ( field.options || [] ).map( ( o, i ) => <option key={ i }>{ o.label }</option> ) }
				</select>
			</>;
		case 'radio':
		case 'checkboxes':
			return <>{ label }
				<div className="wpzf-preview-form__choices">
					{ ( field.options || [] ).map( ( o, i ) => (
						<label key={ i } className="wpzf-preview-form__choice">
							<input type={ field.type === 'radio' ? 'radio' : 'checkbox' } disabled />
							<span>{ o.label }</span>
						</label>
					) ) }
				</div>
			</>;
		case 'checkbox':
			return <label className="wpzf-preview-form__choice wpzf-preview-form__choice--single">
				<input type="checkbox" disabled />
				<span>{ field.checkboxText || field.label }{ required }</span>
			</label>;
		default:
			return <>{ label }<input type="text" className="wpzf-preview-form__input" placeholder={ field.placeholder || ' ' } disabled /></>;
	}
}
