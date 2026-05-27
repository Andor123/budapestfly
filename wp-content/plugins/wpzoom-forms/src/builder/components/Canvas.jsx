import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Modal, Button } from '@wordpress/components';
import { cls } from '../utils';
import FieldCard from './FieldCard.jsx';

export default function Canvas({
	fields,
	settings,
	selectedId,
	onSelect,
	onReorder,
	onDelete,
	onDuplicate,
	onAddField,
	onInsertField,
}) {
	const [ dragIdx, setDragIdx ] = useState( null );
	const [ overIdx, setOverIdx ] = useState( null );
	const [ overHalf, setOverHalf ] = useState( null ); // 'top' | 'bottom'
	const [ overEnd, setOverEnd ] = useState( false );
	const [ pendingDeleteId, setPendingDeleteId ] = useState( null );

	// Backspace/Delete opens the confirmation modal for the selected field.
	useEffect( () => {
		const onKey = ( e ) => {
			if ( e.key !== 'Backspace' && e.key !== 'Delete' ) return;
			if ( ! selectedId ) return;
			const tag = document.activeElement?.tagName;
			if ( tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT' ) return;
			if ( document.activeElement?.isContentEditable ) return;
			if ( document.activeElement?.closest( '.components-modal__frame' ) ) return;
			e.preventDefault();
			setPendingDeleteId( selectedId );
		};
		document.addEventListener( 'keydown', onKey );
		return () => document.removeEventListener( 'keydown', onKey );
	}, [ selectedId ] );

	// Clear all drag state when any drag ends — covers both canvas reorders
	// and palette drags that are cancelled or dropped outside the canvas.
	useEffect( () => {
		const clear = () => { setDragIdx( null ); setOverIdx( null ); setOverHalf( null ); setOverEnd( false ); };
		document.addEventListener( 'dragend', clear );
		return () => document.removeEventListener( 'dragend', clear );
	}, [] );

	const onDragStart = ( e, idx ) => {
		setDragIdx( idx );
		e.dataTransfer.effectAllowed = 'move';
		try { e.dataTransfer.setData( 'text/plain', String( idx ) ); } catch ( _ ) {}
	};

	const onDragOver = ( e, idx ) => {
		e.preventDefault();
		const rect = e.currentTarget.getBoundingClientRect();
		const half = e.clientY < rect.top + rect.height / 2 ? 'top' : 'bottom';
		setOverIdx( idx );
		setOverHalf( half );
		setOverEnd( false );
	};

	// Compute moveItem `to` index from drag source, target, and which half was hovered.
	const reorderTarget = ( from, toIdx, half ) => {
		if ( half === 'top' ) return from < toIdx ? toIdx - 1 : toIdx;
		return from < toIdx ? toIdx : toIdx + 1;
	};

	const onDrop = ( e, idx ) => {
		e.preventDefault();
		const palType = e.dataTransfer.getData( 'wpzf/new-field' );
		const half = overHalf || 'bottom';
		if ( palType ) {
			onInsertField( palType, half === 'top' ? idx : idx + 1 );
		} else if ( dragIdx !== null && dragIdx !== idx ) {
			onReorder( dragIdx, reorderTarget( dragIdx, idx, half ) );
		}
		setDragIdx( null );
		setOverIdx( null );
		setOverHalf( null );
	};

	const onDropEnd = ( e ) => {
		e.preventDefault();
		const palType = e.dataTransfer.getData( 'wpzf/new-field' );
		if ( palType ) {
			onInsertField( palType, fields.length );
		} else if ( dragIdx !== null ) {
			onReorder( dragIdx, fields.length );
		}
		setDragIdx( null );
		setOverEnd( false );
		setOverHalf( null );
	};

	const empty = fields.length === 0;

	return (
		<main className="wpzf-canvas">
			<div className="wpzf-canvas__inner">
				<div className={ cls( 'wpzf-preview', 'wpzf-form', settings.theme && settings.theme !== 'default' && 'wpzf-theme-' + settings.theme, 'wpzf-labels-' + settings.labelsPosition, settings.formLayout && settings.formLayout !== 'default' && 'wpzf-layout-' + settings.formLayout ) }>

					{ empty && (
						<div
							className={ cls( 'wpzf-empty-state', overEnd && 'is-over' ) }
							onDragOver={ ( e ) => { e.preventDefault(); setOverEnd( true ); } }
							onDragLeave={ () => setOverEnd( false ) }
							onDrop={ ( e ) => {
								e.preventDefault();
								const palType = e.dataTransfer.getData( 'wpzf/new-field' );
								if ( palType ) onInsertField( palType, 0 );
								setOverEnd( false );
							} }
						>
							<h2>{ __( 'Drop your first field', 'wpzoom-forms' ) }</h2>
							<p>{ __( 'Drag a field from the left panel, or click any field type to add it.', 'wpzoom-forms' ) }</p>
							<div className="wpzf-empty-state__shortcuts">
								<button onClick={ () => onAddField( 'name' ) }>+ { __( 'Name', 'wpzoom-forms' ) }</button>
								<button onClick={ () => onAddField( 'email' ) }>+ { __( 'Email', 'wpzoom-forms' ) }</button>
								<button onClick={ () => onAddField( 'textarea' ) }>+ { __( 'Message', 'wpzoom-forms' ) }</button>
							</div>
						</div>
					) }

					{ ! empty && (
						<div className="wpzf-canvas-fields">
							{ fields.map( ( field, idx ) => (
								<div
									key={ field.id }
									className={ cls(
										'wpzf-canvas-row',
										'wpzf-canvas-row--width-' + field.width,
										dragIdx === idx && 'is-dragging',
										overIdx === idx && dragIdx !== idx && (
											overHalf === 'top' ? 'is-drop-target--top' : 'is-drop-target--bottom'
										)
									) }
									draggable
									onDragStart={ ( e ) => onDragStart( e, idx ) }
									onDragOver={ ( e ) => onDragOver( e, idx ) }
									onDrop={ ( e ) => onDrop( e, idx ) }
								>
									<FieldCard
										field={ field }
										settings={ settings }
										selected={ selectedId === field.id }
										onSelect={ () => onSelect( field.id ) }
										onDelete={ () => onDelete( field.id ) }
										onDuplicate={ () => onDuplicate( field.id ) }
									/>
								</div>
							) ) }

							<div
								className={ cls( 'wpzf-canvas-drop-end', overEnd && 'is-over' ) }
								onDragOver={ ( e ) => { e.preventDefault(); setOverEnd( true ); setOverIdx( null ); } }
								onDragLeave={ () => setOverEnd( false ) }
								onDrop={ onDropEnd }
							/>
						</div>
					) }

					{ ! empty && (
						<div className={ cls( 'wpzf-preview-submit', 'wpzf-submit', 'wpzf-preview-submit--align-' + settings.submitAlign, 'wpzf-submit--align-' + settings.submitAlign ) }>
							<button type="button" className="wpzf-preview-submit__btn wpzf-submit__btn">{ settings.submitLabel || __( 'Submit', 'wpzoom-forms' ) }</button>
						</div>
					) }

				</div>
			</div>

			{ pendingDeleteId && (
				<Modal
					title={ __( 'Remove field', 'wpzoom-forms' ) }
					onRequestClose={ () => setPendingDeleteId( null ) }
					size="small"
				>
					<p>{ __( 'Are you sure you want to remove this field? This action cannot be undone.', 'wpzoom-forms' ) }</p>
					<div className="wpzf-modal__footer">
						<Button variant="tertiary" onClick={ () => setPendingDeleteId( null ) }>
							{ __( 'Cancel', 'wpzoom-forms' ) }
						</Button>
						<Button variant="primary" isDestructive onClick={ () => { onDelete( pendingDeleteId ); setPendingDeleteId( null ); } }>
							{ __( 'Delete', 'wpzoom-forms' ) }
						</Button>
					</div>
				</Modal>
			) }
		</main>
	);
}
