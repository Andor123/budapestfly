import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';
import { cls } from '../utils';

/**
 * Renders a field preview inside the canvas. Click selects, hover shows actions.
 * The preview is intentionally non-interactive (pointer-events readonly).
 */
export default function FieldCard({ field, settings, selected, onSelect, onDelete, onDuplicate }) {
	const showLabel = settings.labelsPosition !== 'hidden' && field.type !== 'checkbox' && field.label;
	const reqMark = field.required && settings.showRequiredMark ? <span className="wpzf-required"> *</span> : null;
	const [ confirmOpen, setConfirmOpen ] = useState( false );

	return (
		<div
			className={ cls( 'wpzf-field-card', 'wpzf-field-card--type-' + field.type, selected && 'is-selected' ) }
			onClick={ ( e ) => { e.stopPropagation(); onSelect(); } }
			role="button"
			tabIndex={ 0 }
			onKeyDown={ ( e ) => { if ( e.key === 'Enter' || e.key === ' ' ) { e.preventDefault(); onSelect(); } } }
		>
			<div className="wpzf-field-card__drag" title={ __( 'Drag to reorder', 'wpzoom-forms' ) }>
				<svg width="12" height="18" viewBox="0 0 12 18" fill="currentColor"><circle cx="3" cy="3" r="1.5"/><circle cx="9" cy="3" r="1.5"/><circle cx="3" cy="9" r="1.5"/><circle cx="9" cy="9" r="1.5"/><circle cx="3" cy="15" r="1.5"/><circle cx="9" cy="15" r="1.5"/></svg>
			</div>

			<div className="wpzf-field-card__body">
				{ showLabel && (
					<label className="wpzf-field-card__label wpzf-label">{ field.label }{ reqMark }</label>
				) }
				<FieldPreview field={ field } />
				{ field.help && <div className="wpzf-field-card__help wpzf-help">{ field.help }</div> }
			</div>

			<div className="wpzf-field-card__actions" onClick={ ( e ) => e.stopPropagation() }>
				<button className="wpzf-icon-btn" title={ __( 'Duplicate', 'wpzoom-forms' ) } onClick={ onDuplicate }>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4a2 2 0 00-2 2v14h2V3h12V1zm3 4H8a2 2 0 00-2 2v14a2 2 0 002 2h11a2 2 0 002-2V7a2 2 0 00-2-2zm0 16H8V7h11v14z"/></svg>
				</button>
				<button className="wpzf-icon-btn wpzf-icon-btn--danger" title={ __( 'Delete', 'wpzoom-forms' ) } onClick={ () => setConfirmOpen( true ) }>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 19a2 2 0 002 2h8a2 2 0 002-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>
				</button>
			</div>

			{ confirmOpen && (
				<Modal
					title={ __( 'Remove field', 'wpzoom-forms' ) }
					onRequestClose={ () => setConfirmOpen( false ) }
					size="small"
				>
					<p>{ __( 'Are you sure you want to remove this field? This action cannot be undone.', 'wpzoom-forms' ) }</p>
					<div className="wpzf-modal__footer">
						<Button variant="tertiary" onClick={ () => setConfirmOpen( false ) }>
							{ __( 'Cancel', 'wpzoom-forms' ) }
						</Button>
						<Button variant="primary" isDestructive onClick={ () => { setConfirmOpen( false ); onDelete(); } }>
							{ __( 'Delete', 'wpzoom-forms' ) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
}

function FieldPreview({ field }) {
	switch ( field.type ) {
		case 'text':
		case 'name':
		case 'email':
		case 'tel':
		case 'url':
		case 'number':
		case 'date':
		case 'hidden':
			return <input type="text" readOnly className="wpzf-input" placeholder={ field.placeholder || ' ' } />;
		case 'textarea':
			return <textarea readOnly className="wpzf-input" rows={ field.rows || 4 } placeholder={ field.placeholder || ' ' } />;
		case 'select': {
			const selDefault = field.defaultValue || '';
			return (
				<select className="wpzf-input wpzf-input--select" value={ selDefault } onChange={ () => {} }>
					<option value="">{ field.placeholder || '— Select —' }</option>
					{ ( field.options || [] ).map( ( o, i ) => (
						<option key={ i } value={ o.value }>{ o.label }</option>
					) ) }
				</select>
			);
		}
		case 'radio':
		case 'checkboxes': {
			const isRadio   = field.type === 'radio';
			const defVal    = field.defaultValue;
			const isChecked = ( val ) => isRadio
				? defVal === val
				: Array.isArray( defVal ) && defVal.includes( val );
			return (
				<div className="wpzf-choices">
					{ ( field.options || [] ).map( ( o, i ) => (
						<label key={ i } className="wpzf-choice">
							<input
								type={ isRadio ? 'radio' : 'checkbox' }
								checked={ isChecked( o.value ) }
								onChange={ () => {} }
							/>
							<span>{ o.label }</span>
						</label>
					) ) }
				</div>
			);
		}
		case 'checkbox':
			return (
				<label className="wpzf-choice wpzf-choice--single">
					<input type="checkbox" readOnly />
					<span>{ field.checkboxText || field.label }</span>
				</label>
			);
		case 'heading': {
			const Tag = field.level || 'h3';
			return <Tag className="wpzf-heading">{ field.text || ' ' }</Tag>;
		}
		case 'paragraph':
			return <div className="wpzf-paragraph" dangerouslySetInnerHTML={ { __html: field.text || '&nbsp;' } } />;
		case 'divider':
			return <hr className="wpzf-divider" />;
		default:
			return null;
	}
}
