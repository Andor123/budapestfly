import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import FieldsIcons from '../icons';
import ProModal from './ProModal';

const FIELD_GROUPS = [
	{
		id: 'standard',
		label: __( 'Standard Fields', 'wpzoom-forms' ),
		types: [ 'text', 'name', 'email', 'tel', 'url', 'textarea' ],
	},
	{
		id: 'choice',
		label: __( 'Choice Fields', 'wpzoom-forms' ),
		types: [ 'select', 'radio', 'checkboxes', 'checkbox' ],
	},
	{
		id: 'advanced',
		label: __( 'Advanced', 'wpzoom-forms' ),
		types: [ 'date' ],
	},
	{
		id: 'layout',
		label: __( 'Layout', 'wpzoom-forms' ),
		types: [ 'heading', 'paragraph', 'divider' ],
	},
];

const TYPE_LABELS = {
	text:       __( 'Text', 'wpzoom-forms' ),
	name:       __( 'Name', 'wpzoom-forms' ),
	email:      __( 'Email', 'wpzoom-forms' ),
	tel:        __( 'Phone', 'wpzoom-forms' ),
	url:        __( 'Website', 'wpzoom-forms' ),
	number:     __( 'Number', 'wpzoom-forms' ),
	textarea:   __( 'Message', 'wpzoom-forms' ),
	select:     __( 'Select', 'wpzoom-forms' ),
	radio:      __( 'Radio', 'wpzoom-forms' ),
	checkboxes: __( 'Multichoice', 'wpzoom-forms' ),
	checkbox:   __( 'Checkbox', 'wpzoom-forms' ),
	date:       __( 'Date', 'wpzoom-forms' ),
	heading:    __( 'Heading', 'wpzoom-forms' ),
	paragraph:  __( 'Paragraph', 'wpzoom-forms' ),
	divider:    __( 'Divider', 'wpzoom-forms' ),
};

const TYPE_ICONS = {
	text: 'text', name: 'user', email: 'email', tel: 'phone',
	url: 'link', number: 'number', textarea: 'message',
	select: 'select', radio: 'radio', checkboxes: 'checkboxes', checkbox: 'checkbox',
	date: 'date',
	heading: 'heading', paragraph: 'paragraph', divider: 'divider',
};

const PRO_FIELDS = [
	{ type: 'number', label: __( 'Number', 'wpzoom-forms' ), icon: 'number' },
	{ type: 'hidden', label: __( 'Hidden', 'wpzoom-forms' ), icon: 'hidden' },
	{ type: 'upload', label: __( 'Upload', 'wpzoom-forms' ), icon: 'upload' },
	{ type: 'time',   label: __( 'Time', 'wpzoom-forms' ),   icon: 'time' },
	{ type: 'gdpr',   label: __( 'GDPR', 'wpzoom-forms' ),   icon: 'gdpr' },
];

export default function LeftSidebar({ onAddField }) {
	const [ proField, setProField ] = useState( null );

	return (
		<aside className="wpzf-sidebar wpzf-sidebar--left">
			<div className="wpzf-palette">
				{ FIELD_GROUPS.map( ( group ) => (
					<div className="wpzf-palette__group" key={ group.id }>
						<h3 className="wpzf-palette__title">{ group.label }</h3>
						<div className="wpzf-palette__grid">
							{ group.types.map( ( type ) => (
								<button
									key={ type }
									className="wpzf-palette__item"
									draggable
									onDragStart={ ( e ) => {
										e.dataTransfer.setData( 'wpzf/new-field', type );
										e.dataTransfer.effectAllowed = 'copy';
									} }
									onClick={ () => onAddField( type ) }
									title={ TYPE_LABELS[ type ] }
								>
									<span className="wpzf-palette__icon">{ FieldsIcons[ TYPE_ICONS[ type ] ] }</span>
									<span className="wpzf-palette__label">{ TYPE_LABELS[ type ] }</span>
								</button>
							) ) }
						</div>
					</div>
				) ) }

				<div className="wpzf-palette__group wpzf-palette__group--premium">
					<h3 className="wpzf-palette__title">{ __( 'Premium Fields', 'wpzoom-forms' ) }</h3>
					<div className="wpzf-palette__grid">
						{ PRO_FIELDS.map( ( field ) => (
							<button
								key={ field.type }
								className="wpzf-palette__item wpzf-palette__item--pro"
								draggable={ false }
								onClick={ () => setProField( field.label ) }
								title={ sprintf( /* translators: %s: field type label */ __( '%s (Pro)', 'wpzoom-forms' ), field.label ) }
							>
								<span className="wpzf-palette__icon">{ FieldsIcons[ field.icon ] }</span>
								<span className="wpzf-palette__label">{ field.label }</span>
								<span className="wpzf-palette__pro-badge">PRO</span>
							</button>
						) ) }
					</div>
				</div>
			</div>

			{ proField && (
				<ProModal
					fieldLabel={ sprintf( /* translators: %s: field type label */ __( '%s Field', 'wpzoom-forms' ), proField ) }

					onClose={ () => setProField( null ) }
				/>
			) }
		</aside>
	);
}
