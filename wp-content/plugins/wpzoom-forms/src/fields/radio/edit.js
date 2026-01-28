import isEmpty from 'lodash/isEmpty';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { useState, Fragment, useEffect } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl, Flex, FlexBlock, FlexItem, Card, CardBody, CardHeader, Button, Modal } from '@wordpress/components';

const Edit = props => {
	const blockProps = useBlockProps( { className: 'unstyled-list' } );
	const { attributes, setAttributes, clientId } = props;
	const { id, name, options, defaultValue, label, showLabel, required } = attributes;

	const [isOpen, setOpen] = useState(false);
	const [bulkOptions, setBulkOptions] = useState('');

	const openModal = () => setOpen(true);
	const closeModal = () => setOpen(false);

	const optionAdd = () => {
		const opts = [ ...options ];
		opts.push( sprintf( __( 'Item #%s', 'wpzoom-forms' ), options.length + 1 ) );
		setAttributes( { options: opts } );
	};

	const optionRemove = ( index ) => {
		const opts = [ ...options ];
		opts.splice( index, 1 );
		setAttributes( { options: opts } );
	};

	const optionChange = ( name, index ) => {
		const opts = [ ...options ];
		opts[ index ] = name;
		setAttributes( { options: opts } );
	};

	useEffect( () => {
		if ( ! id ) {
			setAttributes( { id: 'input_' + clientId.substr( 0, 8 ) } );
		}
	}, [] );

	useEffect(() => {
		if (options) {
			let _items = '';
			options.forEach((item) => {
				_items += item + '\n';
			});
			setBulkOptions(_items);
		}
	}, [options]);

	const onBulkAddItems = () => {
		const _options = bulkOptions.split('\n').filter(Boolean);
		setAttributes({ options: _options });
		setOpen(false);
	};

	const [ uniqueId ] = useState( id );

	const handleNameChange = newValue => {
		const newId = newValue.replace(/\s/g, '_').toLowerCase();
		setAttributes( { name: newValue, id: 'input_' + clientId.substr( 0, 8 ) } );		
	};

	return <>
		<InspectorControls>
			<PanelBody title={ __( 'Options', 'wpzoom-forms' ) }>
				<TextControl
					label={ __( 'Name', 'wpzoom-forms' ) }
					value={ name }
					placeholder={ __( 'e.g. My Radio Field', 'wpzoom-forms' ) }
					onChange={ handleNameChange }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>

				<Card size="small">
					<CardHeader>
						{ __( 'Items', 'wpzoom-forms' ) }

						<Button
							icon="insert"
							label={ __( 'Add Item', 'wpzoom-forms' ) }
							onClick={ optionAdd.bind( this ) }
						/>
					</CardHeader>
					<CardBody>
						{ options.map( ( option, index ) => (
							<Fragment key={ index }>
								<Flex>
									<FlexBlock>
										<TextControl
											value={ options[ index ] }
											onChange={ value => optionChange( value, index ) }
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
									</FlexBlock>

									{ options.length > 1 && <FlexItem>
										<Button
											icon="no-alt"
											label={ __( 'Delete Item', 'wpzoom-forms' ) }
											onClick={ () => optionRemove( index ) }
											__next40pxDefaultSize
											__nextHasNoMarginBottom
										/>
									</FlexItem> }
								</Flex>
							</Fragment>
						) ) }
					</CardBody>
				</Card>

				<Button
						icon="admin-settings"
						label={__('Add Bulk Options', 'wpzoom-forms')}
						onClick={openModal}
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					>
						{__('Add Bulk Options', 'wpzoom-forms')}
					</Button>

					{ isOpen && (
						<Modal
							title={__('Add Bulk Options', 'wpzoom-forms')}
							onRequestClose={closeModal}
							shouldCloseOnClickOutside={true}
						>
						<div className="wpzoom-forms-extra-options" style={{ maxWidth: 720 + 'px', maxHeight: 525 + 'px' } } >
							<div className="form-group">
								<div className="wrap-content">
									<TextareaControl
										label={__('Bulk Options', 'wpzoom-forms')}
										help={__('Each line break is a new option.', 'wpzoom-forms')}
										className="bulk-add-enter-options"
										rows="5"
										value={ bulkOptions }
										onChange={value => setBulkOptions(value)}
										__next40pxDefaultSize
										__nextHasNoMarginBottom
									/>
								</div>
							</div>
							<div className="form-group">
								<Button variant="secondary" onClick={() => setOpen(false)} __next40pxDefaultSize __nextHasNoMarginBottom>
									{ __('Cancel', 'wpzoom-forms')}
								</Button>
								{ ! isEmpty( bulkOptions ) && (
									<Button variant="primary" onClick={ () => { onBulkAddItems(); } } __next40pxDefaultSize __nextHasNoMarginBottom>
										{__('Bulk Add', 'wpzoom-forms')}
									</Button>
								)}
							</div>
						</div>
						</Modal>
					)}

				<SelectControl
					label={ __( 'Default Value', 'wpzoom-forms' ) }
					value={ defaultValue }
					options={ options.map( ( option, index ) => ( { label: option, value: option } ) ) }
					onChange={ value => setAttributes( { defaultValue: value } ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>

				<ToggleControl
					label={ __( 'Show Label', 'wpzoom-forms' ) }
					checked={ !! showLabel }
					onChange={ value => setAttributes( { showLabel: !! value } ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>

				{ showLabel && <TextControl
					label={ __( 'Label', 'wpzoom-forms' ) }
					value={ label }
					onChange={ value => setAttributes( { label: value } ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/> }

				<ToggleControl
					label={ __( 'Required', 'wpzoom-forms' ) }
					checked={ !! required }
					onChange={ value => setAttributes( { required: !! value } ) }
					__next40pxDefaultSize
					__nextHasNoMarginBottom
				/>
			</PanelBody>
		</InspectorControls>

		<Fragment>
			{ showLabel && <>
				<RichText
					tagName="label"
					placeholder={ __( 'Label', 'wpzoom-forms' ) }
					value={ label }
					htmlFor={ uniqueId }
					onChange={ value => setAttributes( { label: value } ) }
				/>
				{ required && <sup className="wp-block-wpzoom-forms-required">{ __( '*', 'wpzoom-forms' ) }</sup> }
			</> }

			<ul { ...blockProps }>
				{ options.map( ( option, index ) =>
					<li key={ index }>
						<label>
							<input
								type="radio"
								name={ uniqueId }
								id={ uniqueId }
								value={ option }
								checked={ option == defaultValue }
								onChange={ e => {} }
								required={ !! required }
							/>
							{ option }
						</label>
					</li>
				) }
			</ul>
		</Fragment>
	</>;
};

export default Edit;