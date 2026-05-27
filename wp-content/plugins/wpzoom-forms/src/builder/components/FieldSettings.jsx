import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	TextControl,
	TextareaControl,
	SelectControl,
	ToggleControl,
	Button,
	Modal,
	TabPanel,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { plus, reset, help } from '@wordpress/icons';
import { api } from '../api';

const HAS_LABEL    = [ 'text', 'name', 'email', 'tel', 'url', 'number', 'textarea', 'date', 'select', 'radio', 'checkboxes', 'checkbox', 'hidden' ];
const HAS_PLACEHOLDER = [ 'text', 'name', 'email', 'tel', 'url', 'number', 'textarea', 'date', 'select' ];
const HAS_REQUIRED = [ 'text', 'name', 'email', 'tel', 'url', 'number', 'textarea', 'date', 'select', 'radio', 'checkboxes', 'checkbox' ];
const HAS_HELP     = [ 'text', 'name', 'email', 'tel', 'url', 'number', 'textarea', 'date', 'select', 'radio', 'checkboxes', 'checkbox' ];
const HAS_DEFAULT  = [ 'text', 'name', 'email', 'tel', 'url', 'number', 'date', 'hidden' ];
const HAS_OPTIONS  = [ 'select', 'radio', 'checkboxes' ];
const HAS_CONDITIONAL_LOGIC = [ 'text', 'name', 'email', 'tel', 'url', 'textarea', 'select', 'checkboxes', 'checkbox', 'radio', 'date' ];

const CONDITIONAL_LOGIC_URL = 'https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpzoom-forms-free&utm_medium=conditional-logic-upsell&utm_campaign=plugin-upsell';

const WIDTH_OPTIONS = [
	{ value: 'full',       label: __( 'Full', 'wpzoom-forms' ) },
	{ value: 'half',       label: '1/2' },
	{ value: 'third',      label: '1/3' },
	{ value: 'two-thirds', label: '2/3' },
];

const DATE_FORMATS = [
	{ value: 'Y-m-d',  label: 'YYYY-MM-DD (2026-05-17)' },
	{ value: 'F j, Y', label: 'Month D, YYYY (May 17, 2026)' },
	{ value: 'm/d/Y',  label: 'MM/DD/YYYY (05/17/2026)' },
	{ value: 'd/m/Y',  label: 'DD/MM/YYYY (17/05/2026)' },
];

const TABS = [
	{ name: 'general',  title: __( 'General',  'wpzoom-forms' ) },
	{ name: 'advanced', title: __( 'Advanced', 'wpzoom-forms' ) },
];

export default function FieldSettings({ field, onChange, onShowGuide }) {
	const set = ( patch ) => onChange( patch );

	return (
		<div className="wpzf-inspector">
			<div className="wpzf-inspector__header">
				<h3>{ __( 'Field Settings', 'wpzoom-forms' ) }</h3>
				<div className="wpzf-inspector__header-right">
					<span className="wpzf-inspector__type">{ field.type }</span>
					<Button icon={ help } size="small" label={ __( 'Show Welcome Guide', 'wpzoom-forms' ) } showTooltip onClick={ onShowGuide } />
				</div>
			</div>

			<TabPanel
				className="wpzf-inspector__tabs"
				tabs={ TABS }
			>
				{ ( tab ) => (
					<div className="wpzf-inspector__body">
						{ tab.name === 'general' && (
							<>
								{ HAS_LABEL.includes( field.type ) && (
									<TextControl
										label={ __( 'Label', 'wpzoom-forms' ) }
										value={ field.label || '' }
										onChange={ ( v ) => set( { label: v } ) }
									/>
								) }

								{ field.type === 'heading' && (
									<>
										<TextControl
											label={ __( 'Text', 'wpzoom-forms' ) }
											value={ field.text || '' }
											onChange={ ( v ) => set( { text: v } ) }
										/>
										<SelectControl
											label={ __( 'Heading Level', 'wpzoom-forms' ) }
											value={ field.level || 'h3' }
											options={ [
												{ value: 'h2', label: 'H2' },
												{ value: 'h3', label: 'H3' },
												{ value: 'h4', label: 'H4' },
												{ value: 'h5', label: 'H5' },
											] }
											onChange={ ( v ) => set( { level: v } ) }
										/>
									</>
								) }

								{ field.type === 'paragraph' && (
									<TextareaControl
										label={ __( 'Text', 'wpzoom-forms' ) }
										help={ __( 'Basic HTML is allowed, e.g. <strong>, <em>, <a>, <br>, <span>, <code>.', 'wpzoom-forms' ) }
										rows={ 4 }
										value={ field.text || '' }
										onChange={ ( v ) => set( { text: v } ) }
									/>
								) }

								{ HAS_PLACEHOLDER.includes( field.type ) && (
									<TextControl
										label={ __( 'Placeholder', 'wpzoom-forms' ) }
										value={ field.placeholder || '' }
										onChange={ ( v ) => set( { placeholder: v } ) }
									/>
								) }

								{ HAS_DEFAULT.includes( field.type ) && (
									<TextControl
										label={ __( 'Default Value', 'wpzoom-forms' ) }
										value={ field.defaultValue || '' }
										onChange={ ( v ) => set( { defaultValue: v } ) }
									/>
								) }

								{ field.type === 'textarea' && (
									<TextControl
										label={ __( 'Rows', 'wpzoom-forms' ) }
										type="number"
										min={ 1 }
										max={ 20 }
										value={ String( field.rows || 4 ) }
										onChange={ ( v ) => set( { rows: parseInt( v, 10 ) || 4 } ) }
									/>
								) }

								{ field.type === 'number' && (
									<>
										<TextControl
											label={ __( 'Min', 'wpzoom-forms' ) }
											type="number"
											value={ field.min == null ? '' : String( field.min ) }
											onChange={ ( v ) => set( { min: v === '' ? null : parseFloat( v ) } ) }
										/>
										<TextControl
											label={ __( 'Max', 'wpzoom-forms' ) }
											type="number"
											value={ field.max == null ? '' : String( field.max ) }
											onChange={ ( v ) => set( { max: v === '' ? null : parseFloat( v ) } ) }
										/>
										<TextControl
											label={ __( 'Step', 'wpzoom-forms' ) }
											type="number"
											step="any"
											value={ field.step == null ? '' : String( field.step ) }
											onChange={ ( v ) => set( { step: v === '' ? null : parseFloat( v ) } ) }
										/>
									</>
								) }

								{ field.type === 'date' && (
									<>
										<ToggleGroupControl
											label={ __( 'Mode', 'wpzoom-forms' ) }
											value={ field.mode || 'single' }
											onChange={ ( v ) => set( { mode: v } ) }
											isBlock
											__nextHasNoMarginBottom={ false }
										>
											<ToggleGroupControlOption value="single"   label={ __( 'Single',   'wpzoom-forms' ) } />
											<ToggleGroupControlOption value="multiple" label={ __( 'Multiple', 'wpzoom-forms' ) } />
											<ToggleGroupControlOption value="range"    label={ __( 'Range',    'wpzoom-forms' ) } />
										</ToggleGroupControl>
										<SelectControl
											label={ __( 'Date Format', 'wpzoom-forms' ) }
											value={ field.format || 'Y-m-d' }
											options={ DATE_FORMATS }
											onChange={ ( v ) => set( { format: v } ) }
											__next40pxDefaultSize
										/>
									</>
								) }

								{ field.type === 'checkbox' && (
									<TextControl
										label={ __( 'Checkbox Label', 'wpzoom-forms' ) }
										value={ field.checkboxText || '' }
										onChange={ ( v ) => set( { checkboxText: v } ) }
									/>
								) }

								{ field.type === 'email' && (
									<ToggleControl
										label={ __( 'Use as reply-to address', 'wpzoom-forms' ) }
										checked={ !! field.isReplyTo }
										onChange={ ( v ) => set( { isReplyTo: v } ) }
									/>
								) }

								{ field.type === 'text' && (
									<ToggleControl
										label={ __( 'Use as email subject', 'wpzoom-forms' ) }
										checked={ !! field.isSubject }
										onChange={ ( v ) => set( { isSubject: v } ) }
									/>
								) }

								{ HAS_OPTIONS.includes( field.type ) && (
									<OptionsEditor
										options={ field.options || [] }
										onChange={ ( options ) => set( { options } ) }
										fieldType={ field.type }
										defaultValue={ field.defaultValue }
										onChangeDefault={ ( v ) => set( { defaultValue: v } ) }
									/>
								) }

								{ HAS_REQUIRED.includes( field.type ) && (
									<ToggleControl
										label={ __( 'Required', 'wpzoom-forms' ) }
										checked={ !! field.required }
										onChange={ ( v ) => set( { required: v } ) }
									/>
								) }

								{ HAS_HELP.includes( field.type ) && (
									<TextareaControl
										label={ __( 'Help text', 'wpzoom-forms' ) }
										rows={ 2 }
										value={ field.help || '' }
										onChange={ ( v ) => set( { help: v } ) }
									/>
								) }

								<ToggleGroupControl
									label={ __( 'Width', 'wpzoom-forms' ) }
									value={ field.width || 'full' }
									onChange={ ( v ) => set( { width: v } ) }
									isBlock
									__nextHasNoMarginBottom={ false }
								>
									{ WIDTH_OPTIONS.map( ( w ) => (
										<ToggleGroupControlOption key={ w.value } value={ w.value } label={ w.label } />
									) ) }
								</ToggleGroupControl>
							</>
						) }

						{ tab.name === 'advanced' && (
							<>
								<TextControl
									label={ __( 'CSS Class', 'wpzoom-forms' ) }
									value={ field.cssClass || '' }
									onChange={ ( v ) => set( { cssClass: v } ) }
								/>

								{ field.type !== 'hidden' && (
									<TextareaControl
										label={ __( 'Additional CSS', 'wpzoom-forms' ) }
										rows={ 5 }
										value={ field.customCSS || '' }
										onChange={ ( v ) => set( { customCSS: v } ) }
										help={ __( 'Use "selector" to target this field\'s wrapper. Example: selector { border-radius: 8px; }', 'wpzoom-forms' ) }
										className="wpzf-custom-css-textarea"
									/>
								) }

								<TextControl
									label={ __( 'Field ID', 'wpzoom-forms' ) }
									value={ field.id }
									disabled
								/>

								{ HAS_CONDITIONAL_LOGIC.includes( field.type ) && (
									<div className="wpzf-conditional-logic-upsell">
										<div className="wpzf-conditional-logic-upsell__header">
											<span className="wpzf-conditional-logic-upsell__title">
												{ __( 'Conditional Logic', 'wpzoom-forms' ) }
											</span>
											<span className="wpzf-conditional-logic-upsell__badge">PRO</span>
										</div>
										<div className="wpzf-conditional-logic-upsell__body">
											<p>{ __( 'Show or hide this field based on the value of another field.', 'wpzoom-forms' ) }</p>
											<Button
												href={ CONDITIONAL_LOGIC_URL }
												target="_blank"
												rel="noopener noreferrer"
												variant="primary"
											>
												{ __( 'Unlock this feature', 'wpzoom-forms' ) }
											</Button>
						
										</div>
									</div>
								) }
							</>
						) }
					</div>
				) }
			</TabPanel>
		</div>
	);
}

/* ───────────────── Options editor ───────────────── */

function slugify( s ) {
	return String( s ).toLowerCase().trim().replace( /\s+/g, '-' ).replace( /[^a-z0-9-]/g, '' );
}

function OptionsEditor({ options, onChange, fieldType, defaultValue, onChangeDefault }) {
	const [ bulk, setBulk ] = useState( false );
	const isMulti = fieldType === 'checkboxes';

	const isDefault = ( val ) => {
		if ( isMulti ) {
			return Array.isArray( defaultValue ) ? defaultValue.includes( val ) : false;
		}
		return defaultValue === val;
	};

	const toggleDefault = ( val ) => {
		if ( isMulti ) {
			const cur = Array.isArray( defaultValue ) ? defaultValue : [];
			onChangeDefault( cur.includes( val ) ? cur.filter( ( v ) => v !== val ) : [ ...cur, val ] );
		} else {
			onChangeDefault( isDefault( val ) ? '' : val );
		}
	};

	const update = ( i, patch ) => {
		const next = options.slice();
		next[ i ] = { ...next[ i ], ...patch };
		onChange( next );
	};
	const remove      = ( i ) => onChange( options.filter( ( _, idx ) => idx !== i ) );
	const insertAfter = ( i ) => {
		const next = options.slice();
		const n    = next.length + 1;
		next.splice( i + 1, 0, { label: 'Option ' + n, value: 'option-' + n } );
		onChange( next );
	};

	// HTML5 drag-and-drop reorder.
	const [ dragIdx, setDragIdx ] = useState( null );
	const onDragStart = ( e, idx ) => {
		setDragIdx( idx );
		e.dataTransfer.effectAllowed = 'move';
		try { e.dataTransfer.setData( 'text/plain', String( idx ) ); } catch ( _ ) {}
	};
	const onDragOver = ( e ) => { e.preventDefault(); };
	const onDrop = ( e, idx ) => {
		e.preventDefault();
		if ( dragIdx === null || dragIdx === idx ) return;
		const next = options.slice();
		const [ item ] = next.splice( dragIdx, 1 );
		next.splice( idx, 0, item );
		onChange( next );
		setDragIdx( null );
	};
	const onDragEnd = () => setDragIdx( null );

	return (
		<div className="wpzf-row">
			<div className="wpzf-row__label-row">
				<label className="wpzf-row__label">{ __( 'Options', 'wpzoom-forms' ) }</label>
				<Button onClick={ () => setBulk( true ) }>
					{ __( 'Bulk Edit', 'wpzoom-forms' ) }
				</Button>
			</div>
			<div className="wpzf-options">
				{ options.map( ( o, i ) => (
					<div
						className={ 'wpzf-option' + ( dragIdx === i ? ' is-dragging' : '' ) }
						key={ i }
						draggable
						onDragStart={ ( e ) => onDragStart( e, i ) }
						onDragOver={ onDragOver }
						onDrop={ ( e ) => onDrop( e, i ) }
						onDragEnd={ onDragEnd }
					>
						<span className="wpzf-option__handle" title={ __( 'Drag to reorder', 'wpzoom-forms' ) } aria-hidden="true">
							<svg width="10" height="14" viewBox="0 0 10 14" fill="currentColor"><circle cx="2" cy="2" r="1.2"/><circle cx="8" cy="2" r="1.2"/><circle cx="2" cy="7" r="1.2"/><circle cx="8" cy="7" r="1.2"/><circle cx="2" cy="12" r="1.2"/><circle cx="8" cy="12" r="1.2"/></svg>
						</span>
						<input
							type={ isMulti ? 'checkbox' : 'radio' }
							className="wpzf-option__default"
							checked={ isDefault( o.value ) }
							onChange={ () => toggleDefault( o.value ) }
							title={ __( 'Set as default', 'wpzoom-forms' ) }
						/>
						<input
							type="text"
							value={ o.label }
							placeholder={ __( 'Label', 'wpzoom-forms' ) }
							onChange={ ( e ) => {
								const label = e.target.value;
								const wasAutoValue = o.value === slugify( o.label );
								update( i, wasAutoValue ? { label, value: slugify( label ) } : { label } );
							} }
						/>
						<Button
							icon={ reset }
							iconSize={ 18 }
							label={ __( 'Remove option', 'wpzoom-forms' ) }
							onClick={ () => remove( i ) }
							size="compact"
							disabled={ options.length <= 1 }
						/>
						<Button
							icon={ plus }
							iconSize={ 18 }
							label={ __( 'Add option below', 'wpzoom-forms' ) }
							onClick={ () => insertAfter( i ) }
							size="compact"
						/>
					</div>
				) ) }
			</div>

			{ bulk && (
				<BulkOptionsModal
					initial={ options }
					onApply={ ( next ) => { onChange( next ); setBulk( false ); } }
					onClose={ () => setBulk( false ) }
				/>
			) }
		</div>
	);
}

/* ───────────────── Bulk options modal ───────────────── */

function BulkOptionsModal({ initial, onApply, onClose }) {
	const [ text, setText ]   = useState( ( initial || [] ).map( ( o ) => o.label ).join( '\n' ) );
	const [ lists, setLists ] = useState( null );
	const [ loadErr, setLoadErr ] = useState( '' );

	useEffect( () => {
		api.getOptionLists().then( setLists ).catch( ( e ) => setLoadErr( e.message || '' ) );
	}, [] );

	const apply = () => {
		const next = text.split( /\r?\n/ ).map( ( l ) => l.trim() ).filter( Boolean ).map( ( label ) => ( { label, value: slugify( label ) } ) );
		onApply( next );
	};

	const useList = ( items ) => {
		const existing = text.trim();
		setText( ( existing ? existing + '\n' : '' ) + items.join( '\n' ) );
	};

	return (
		<Modal
			title={ __( 'Bulk Edit Options', 'wpzoom-forms' ) }
			onRequestClose={ onClose }
			size="medium"
		>
			<TextareaControl
				help={ __( 'Each line is a new option. Values are generated from labels.', 'wpzoom-forms' ) }
				rows={ 12 }
				value={ text }
				onChange={ setText }
				placeholder={ __( 'Option one\nOption two\nOption three', 'wpzoom-forms' ) }
			/>
			{ ! loadErr && lists && lists.length > 0 && (
				<div className="wpzf-bulk__lists">
					<span className="wpzf-bulk__lists-label">{ __( 'Predefined lists:', 'wpzoom-forms' ) }</span>
					{ lists.map( ( l ) => (
						<Button key={ l.id } variant="secondary" size="compact" onClick={ () => useList( l.items ) }>
							{ l.label }
						</Button>
					) ) }
				</div>
			) }
			<div className="wpzf-modal__footer">
				<Button variant="tertiary" onClick={ onClose }>{ __( 'Cancel', 'wpzoom-forms' ) }</Button>
				<Button variant="primary" onClick={ apply }>{ __( 'Apply', 'wpzoom-forms' ) }</Button>
			</div>
		</Modal>
	);
}
