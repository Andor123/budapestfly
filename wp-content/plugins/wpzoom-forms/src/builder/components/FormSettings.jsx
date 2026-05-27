import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	TextControl,
	ToggleControl,
	Button,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';
import { help } from '@wordpress/icons';
import ProModal from './ProModal.jsx';

const FORM_STYLES = [
	{
		value:  'default',
		label:  __( 'Default', 'wpzoom-forms' ),
		colors: [ '#374151', '#6b7280', '#d1d5db', '#f9fafb', '#111827' ],
		isPro:  false,
	},
	{
		value:  'minimal',
		label:  __( 'Minimal', 'wpzoom-forms' ),
		colors: [ '#1f2937', '#6b7280', '#d1d5db', '#ffffff', '#9ca3af' ],
		isPro:  false,
	},
	{
		value:  'modern',
		label:  __( 'Modern', 'wpzoom-forms' ),
		colors: [ '#4f46e5', '#7c3aed', '#0f172a', '#f8fafc', '#e2e8f0' ],
		isPro:  false,
	},
	{
		value:  'bold',
		label:  __( 'Bold', 'wpzoom-forms' ),
		colors: [ '#111827', '#f97316', '#374151', '#e5e7eb', '#fff7ed' ],
		isPro:  false,
	},
	{
		value:  'rounded',
		label:  __( 'Rounded', 'wpzoom-forms' ),
		colors: [ '#6d28d9', '#ede9fe', '#7c3aed', '#ffffff', '#f5f3ff' ],
		isPro:  false,
	},
	{
		value:  'flat',
		label:  __( 'Flat', 'wpzoom-forms' ),
		colors: [ '#374151', '#f1f5f9', '#e2e8f0', '#ffffff', '#64748b' ],
		isPro:  false,
	},
	{
		value:  'soft',
		label:  __( 'Soft', 'wpzoom-forms' ),
		colors: [ '#8b5cf6', '#a78bfa', '#faf5ff', '#c4b5fd', '#ede9fe' ],
		isPro:  true,
	},
	{
		value:  'ink',
		label:  __( 'Ink', 'wpzoom-forms' ),
		colors: [ '#111827', '#374151', '#ffffff', '#f9fafb', '#6b7280' ],
		isPro:  true,
	},
	{
		value:  'outline',
		label:  __( 'Outline', 'wpzoom-forms' ),
		colors: [ '#2563eb', '#ffffff', '#d1d5db', '#111827', '#f9fafb' ],
		isPro:  true,
	},
	{
		value:    'custom',
		label:    __( 'Custom Style', 'wpzoom-forms' ),
		isCustom: true,
		isPro:    true,
	},
];

function StylePicker( { value, onChange } ) {
	const [ proLabel, setProLabel ] = useState( '' );

	return (
		<div className="wpzf-row">
			<label className="wpzf-row__label">{ __( 'Form Style', 'wpzoom-forms' ) }</label>
			<div className="wpzf-style-picker">
				{ FORM_STYLES.map( ( style ) => (
					<button
						key={ style.value }
						type="button"
						className={ [
							'wpzf-style-picker__item',
							value === style.value ? 'is-selected' : '',
							style.isPro ? 'is-pro' : '',
						].filter( Boolean ).join( ' ' ) }
						onClick={ () => {
							if ( style.isPro ) { setProLabel( style.label ); }
							else { onChange( style.value ); }
						} }
						title={ style.label + ( style.isPro ? ' (PRO)' : '' ) }
					>
						{ style.isCustom ? (
							<div className="wpzf-style-picker__custom-preview">
								<svg xmlns="http://www.w3.org/2000/svg" height="22" viewBox="0 -960 960 960" width="22" fill="currentColor"><path d="M120-160v-117q0-16 6-30.5t17-25.5l335-335-58-56 58-56 76 76 124-124q5-5 12.5-8t15.5-3q8 0 15 3t13 8l94 94q5 6 8 13t3 15q0 8-3 15.5t-8 12.5L705-555l76 78-57 57-56-58-335 335q-11 11-25.5 17t-30.5 6H160q-17 0-28.5-11.5T120-160Zm80-40h78l332-334-76-76-334 332v78Z"/></svg>
							</div>
						) : (
							<div className="wpzf-style-picker__swatches">
								{ style.colors.map( ( color, i ) => (
									<span key={ i } className="wpzf-style-picker__swatch" style={ { background: color } } />
								) ) }
							</div>
						) }
						<span className="wpzf-style-picker__name">{ style.label }</span>
						{ style.isPro && <span className="wpzf-style-picker__pro-badge">PRO</span> }
					</button>
				) ) }
			</div>
			{ proLabel && (
				<ProModal
					fieldLabel={ proLabel + ' ' + __( 'style', 'wpzoom-forms' ) }
					onClose={ () => setProLabel( '' ) }
				/>
			) }
		</div>
	);
}

export default function FormSettings({ settings, onChange, onShowGuide }) {
	return (
		<div className="wpzf-inspector">
			<div className="wpzf-inspector__header">
				<h3>{ __( 'Form Settings', 'wpzoom-forms' ) }</h3>
				<Button icon={ help } size="small" label={ __( 'Show Welcome Guide', 'wpzoom-forms' ) } showTooltip onClick={ onShowGuide } />
			</div>
			<div className="wpzf-inspector__body">
				<TextControl
					label={ __( 'Submit Button Text', 'wpzoom-forms' ) }
					value={ settings.submitLabel || '' }
					onChange={ ( v ) => onChange( { submitLabel: v } ) }
				/>

				<ToggleGroupControl
					label={ __( 'Submit Button Alignment', 'wpzoom-forms' ) }
					value={ settings.submitAlign }
					onChange={ ( v ) => onChange( { submitAlign: v } ) }
					isBlock
					__nextHasNoMarginBottom={ false }
				>
					{ [ 'left', 'center', 'right', 'full' ].map( ( v ) => (
						<ToggleGroupControlOption key={ v } value={ v } label={ v.charAt( 0 ).toUpperCase() + v.slice( 1 ) } />
					) ) }
				</ToggleGroupControl>

				<ToggleGroupControl
					label={ __( 'Labels Position', 'wpzoom-forms' ) }
					value={ settings.labelsPosition }
					onChange={ ( v ) => onChange( { labelsPosition: v } ) }
					isBlock
					__nextHasNoMarginBottom={ false }
				>
					{ [ 'top', 'left', 'hidden' ].map( ( v ) => (
						<ToggleGroupControlOption key={ v } value={ v } label={ v.charAt( 0 ).toUpperCase() + v.slice( 1 ) } />
					) ) }
				</ToggleGroupControl>

				<ToggleGroupControl
					label={ __( 'Form Layout', 'wpzoom-forms' ) }
					value={ settings.formLayout || 'default' }
					onChange={ ( v ) => onChange( { formLayout: v } ) }
					isBlock
					__nextHasNoMarginBottom={ false }
				>
					<ToggleGroupControlOption value="default"    label={ __( 'Default',    'wpzoom-forms' ) } />
					<ToggleGroupControlOption value="horizontal" label={ __( 'Horizontal', 'wpzoom-forms' ) } />
					<ToggleGroupControlOption value="compact"    label={ __( 'Compact',    'wpzoom-forms' ) } />
				</ToggleGroupControl>

				<StylePicker
					value={ settings.theme }
					onChange={ ( v ) => onChange( { theme: v } ) }
				/>

				<ToggleControl
					label={ __( 'Show required-field asterisk (*)', 'wpzoom-forms' ) }
					checked={ !! settings.showRequiredMark }
					onChange={ ( v ) => onChange( { showRequiredMark: v } ) }
				/>

				<ToggleControl
					label={ __( 'Enable spam honeypot field', 'wpzoom-forms' ) }
					checked={ !! settings.honeypot }
					onChange={ ( v ) => onChange( { honeypot: v } ) }
				/>
			</div>
		</div>
	);
}
