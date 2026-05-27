import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import FieldsIcons from '../icons';
import ProModal from './ProModal';

const FIELD_GROUPS = [
	{
		id: 'standard',
		label: 'Standard Fields',
		types: [ 'text', 'name', 'email', 'tel', 'url', 'textarea' ],
	},
	{
		id: 'choice',
		label: 'Choice Fields',
		types: [ 'select', 'radio', 'checkboxes', 'checkbox' ],
	},
	{
		id: 'advanced',
		label: 'Advanced',
		types: [ 'date' ],
	},
	{
		id: 'layout',
		label: 'Layout',
		types: [ 'heading', 'paragraph', 'divider' ],
	},
];

const TYPE_LABELS = {
	text:       'Text',
	name:       'Name',
	email:      'Email',
	tel:        'Phone',
	url:        'Website',
	number:     'Number',
	textarea:   'Message',
	select:     'Select',
	radio:      'Radio',
	checkboxes: 'Multichoice',
	checkbox:   'Checkbox',
	date:       'Date',
	heading:    'Heading',
	paragraph:  'Paragraph',
	divider:    'Divider',
};

const TYPE_ICONS = {
	text: 'text', name: 'user', email: 'email', tel: 'phone',
	url: 'link', number: 'number', textarea: 'message',
	select: 'select', radio: 'radio', checkboxes: 'checkboxes', checkbox: 'checkbox',
	date: 'date',
	heading: 'heading', paragraph: 'paragraph', divider: 'divider',
};

const PRO_FIELDS = [
	{ type: 'number', label: 'Number', icon: 'number' },
	{ type: 'hidden', label: 'Hidden', icon: 'hidden' },
	{ type: 'upload', label: 'Upload', icon: 'upload' },
	{ type: 'time',   label: 'Time',   icon: 'time' },
	{ type: 'gdpr',   label: 'GDPR',   icon: 'gdpr' },
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
					<h3 className="wpzf-palette__title">Premium Fields</h3>
					<div className="wpzf-palette__grid">
						{ PRO_FIELDS.map( ( field ) => (
							<button
								key={ field.type }
								className="wpzf-palette__item wpzf-palette__item--pro"
								draggable={ false }
								onClick={ () => setProField( field.label ) }
								title={ field.label + ' (Pro)' }
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
					fieldLabel={ proField + ' Field' }

					onClose={ () => setProField( null ) }
				/>
			) }
		</aside>
	);
}
