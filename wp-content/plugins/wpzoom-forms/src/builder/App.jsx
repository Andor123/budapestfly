import { useEffect, useReducer, useCallback, useMemo, useState } from '@wordpress/element';
import { useDispatch } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import { __ } from '@wordpress/i18n';
import { api } from './api';
import { clone, makeField, moveItem } from './utils';

import Topbar from './components/Topbar.jsx';
import LeftSidebar from './components/LeftSidebar.jsx';
import Canvas from './components/Canvas.jsx';
import Inspector from './components/Inspector.jsx';
import Notices from './components/Notices.jsx';
import EmbedModal from './components/EmbedModal.jsx';
import TemplatePicker from './components/TemplatePicker.jsx';
import WelcomeGuide from './components/WelcomeGuide.jsx';

const TABS = {
	FIELDS:        'fields',
	FORM_SETTINGS: 'form-settings',
	NOTIFICATIONS: 'notifications',
};

const initialState = {
	loading:       true,
	saving:        false,
	dirty:         false,
	form:          null,           // server payload: { id, title, schema, notifications, ... }
	selectedField: null,           // field id or null
	activeTab:     TABS.FIELDS,
	notices:       [],
	showEmbed:     false,
};

function reducer( state, action ) {
	switch ( action.type ) {
		case 'LOAD_START':   return { ...state, loading: true };
		case 'LOAD_OK':      return { ...state, loading: false, form: action.form, dirty: false };
		case 'LOAD_ERR':     return { ...state, loading: false, notices: [ ...state.notices, { type: 'error', text: action.text } ] };

		case 'SAVE_START':   return { ...state, saving: true };
		case 'SAVE_OK':      return { ...state, saving: false, dirty: false, form: action.form, notices: [ ...state.notices, { type: 'success', text: __( 'Form saved', 'wpzoom-forms' ) } ] };
		case 'SAVE_ERR':     return { ...state, saving: false, notices: [ ...state.notices, { type: 'error', text: action.text } ] };

		case 'SET_TITLE': {
			const f = clone( state.form );
			f.title = action.title;
			return { ...state, form: f, dirty: true };
		}

		case 'ADD_FIELD': {
			const f = clone( state.form );
			if ( action.index !== undefined ) {
				f.schema.fields.splice( action.index, 0, action.field );
			} else {
				f.schema.fields.push( action.field );
			}
			return { ...state, form: f, dirty: true, selectedField: action.field.id, activeTab: TABS.FIELDS };
		}

		case 'UPDATE_FIELD': {
			const f = clone( state.form );
			const idx = f.schema.fields.findIndex( fld => fld.id === action.id );
			if ( idx === -1 ) return state;
			f.schema.fields[ idx ] = { ...f.schema.fields[ idx ], ...action.patch };
			return { ...state, form: f, dirty: true };
		}

		case 'DELETE_FIELD': {
			const f = clone( state.form );
			f.schema.fields = f.schema.fields.filter( fld => fld.id !== action.id );
			const sel = state.selectedField === action.id ? null : state.selectedField;
			return { ...state, form: f, dirty: true, selectedField: sel };
		}

		case 'DUPLICATE_FIELD': {
			const f = clone( state.form );
			const idx = f.schema.fields.findIndex( fld => fld.id === action.id );
			if ( idx === -1 ) return state;
			const dup = clone( f.schema.fields[ idx ] );
			dup.id = 'f_' + Math.random().toString( 36 ).slice( 2, 10 );
			f.schema.fields.splice( idx + 1, 0, dup );
			return { ...state, form: f, dirty: true, selectedField: dup.id };
		}

		case 'REORDER_FIELDS': {
			const f = clone( state.form );
			f.schema.fields = moveItem( f.schema.fields, action.from, action.to );
			return { ...state, form: f, dirty: true };
		}

		case 'SELECT_FIELD':   return { ...state, selectedField: action.id, activeTab: TABS.FIELDS };
		case 'SET_TAB':        return { ...state, activeTab: action.tab, selectedField: null };

		case 'UPDATE_SETTINGS': {
			const f = clone( state.form );
			f.schema.settings = { ...f.schema.settings, ...action.patch };
			return { ...state, form: f, dirty: true };
		}

		case 'UPDATE_NOTIFICATIONS': {
			const f = clone( state.form );
			f.notifications = { ...f.notifications, ...action.patch };
			return { ...state, form: f, dirty: true };
		}

		case 'DISMISS_NOTICE':
			return { ...state, notices: state.notices.filter( ( _, i ) => i !== action.index ) };

		case 'TOGGLE_EMBED': return { ...state, showEmbed: !! action.show };

		default: return state;
	}
}

export default function App({ formId, presetTemplate }) {
	const [ state, dispatch ] = useReducer( reducer, initialState );
	const { set: setPref } = useDispatch( preferencesStore );
	const showGuide = useCallback( () => setPref( 'wpzoom-forms', 'builderWelcomeGuide', true ), [ setPref ] );

	// Load on mount — only when we have a form id.
	useEffect( () => {
		if ( ! formId ) return;
		let cancelled = false;
		dispatch( { type: 'LOAD_START' } );
		api.getForm( formId ).then( ( form ) => {
			if ( cancelled ) return;
			dispatch( { type: 'LOAD_OK', form } );
		} ).catch( ( err ) => {
			if ( cancelled ) return;
			dispatch( { type: 'LOAD_ERR', text: err.message || __( 'Could not load form', 'wpzoom-forms' ) } );
		} );
		return () => { cancelled = true; };
	}, [ formId ] );

	const save = useCallback( () => {
		if ( ! state.form ) return;
		dispatch( { type: 'SAVE_START' } );
		api.updateForm( formId, {
			title: state.form.title,
			schema: state.form.schema,
			notifications: state.form.notifications,
		} ).then( ( form ) => {
			dispatch( { type: 'SAVE_OK', form } );
		} ).catch( ( err ) => {
			dispatch( { type: 'SAVE_ERR', text: err.message || __( 'Could not save', 'wpzoom-forms' ) } );
		} );
	}, [ formId, state.form ] );

	// Cmd/Ctrl+S to save
	useEffect( () => {
		const handler = ( e ) => {
			if ( ( e.metaKey || e.ctrlKey ) && e.key === 's' ) {
				e.preventDefault();
				if ( state.dirty && ! state.saving ) save();
			}
		};
		window.addEventListener( 'keydown', handler );
		return () => window.removeEventListener( 'keydown', handler );
	}, [ state.dirty, state.saving, save ] );

	// Warn on leave if dirty
	useEffect( () => {
		const handler = ( e ) => {
			if ( state.dirty ) {
				e.preventDefault();
				e.returnValue = '';
			}
		};
		window.addEventListener( 'beforeunload', handler );
		return () => window.removeEventListener( 'beforeunload', handler );
	}, [ state.dirty ] );

	const selectedFieldObj = useMemo( () => {
		if ( ! state.selectedField || ! state.form ) return null;
		return state.form.schema.fields.find( f => f.id === state.selectedField ) || null;
	}, [ state.selectedField, state.form ] );

	// No form id → show the template picker.
	if ( ! formId ) {
		return (
			<div className="wpzf-builder wpzf-builder--picker">
				<TemplatePicker presetTemplate={ presetTemplate } />
			</div>
		);
	}

	if ( state.loading ) {
		return (
			<div className="wpzf-builder wpzf-builder--loading">
				<div className="wpzf-spinner" aria-label={ __( 'Loading…', 'wpzoom-forms' ) } />
			</div>
		);
	}

	if ( ! state.form ) {
		return <div className="wpzf-builder wpzf-builder--error">{ __( 'Could not load form.', 'wpzoom-forms' ) }</div>;
	}

	const addField    = ( type )        => dispatch( { type: 'ADD_FIELD', field: makeField( type ) } );
	const insertField = ( type, index ) => dispatch( { type: 'ADD_FIELD', field: makeField( type ), index } );

	return (
		<div className="wpzf-builder">
			<Topbar
				title={ state.form.title }
				dirty={ state.dirty }
				saving={ state.saving }
				submissions={ state.form.submissions }
				onTitleChange={ ( title ) => dispatch( { type: 'SET_TITLE', title } ) }
				onSave={ save }
				onEmbed={ () => dispatch( { type: 'TOGGLE_EMBED', show: true } ) }
				activeTab={ state.activeTab }
				onTab={ ( tab ) => dispatch( { type: 'SET_TAB', tab } ) }
				submissionsUrl={ window.wpzfBuilder.submissionsListUrl + '&wpzf_form=' + formId }
				exitUrl={ window.wpzfBuilder.formsListUrl }
			/>

			<div className="wpzf-builder__body">
				<LeftSidebar
					onAddField={ addField }
				/>

				<Canvas
					fields={ state.form.schema.fields }
					settings={ state.form.schema.settings }
					selectedId={ state.selectedField }
					onSelect={ ( id ) => dispatch( { type: 'SELECT_FIELD', id } ) }
					onReorder={ ( from, to ) => dispatch( { type: 'REORDER_FIELDS', from, to } ) }
					onDelete={ ( id ) => dispatch( { type: 'DELETE_FIELD', id } ) }
					onDuplicate={ ( id ) => dispatch( { type: 'DUPLICATE_FIELD', id } ) }
					onAddField={ addField }
					onInsertField={ insertField }
				/>

				<Inspector
					activeTab={ state.activeTab }
					selectedField={ selectedFieldObj }
					form={ state.form }
	
					onUpdateField={ ( patch ) => dispatch( { type: 'UPDATE_FIELD', id: state.selectedField, patch } ) }
					onUpdateSettings={ ( patch ) => dispatch( { type: 'UPDATE_SETTINGS', patch } ) }
					onUpdateNotifications={ ( patch ) => dispatch( { type: 'UPDATE_NOTIFICATIONS', patch } ) }
					onTab={ ( tab ) => dispatch( { type: 'SET_TAB', tab } ) }
					onShowGuide={ showGuide }
				/>
			</div>

			<Notices items={ state.notices } onDismiss={ ( i ) => dispatch( { type: 'DISMISS_NOTICE', index: i } ) } />

			{ state.showEmbed && (
				<EmbedModal
					shortcode={ `[wpzf_form id="${ formId }"]` }
					onClose={ () => dispatch( { type: 'TOGGLE_EMBED', show: false } ) }
				/>
			) }

			<WelcomeGuide />
		</div>
	);
}
