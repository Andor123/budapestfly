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

const MailchimpIcon = () => (
	<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" className="components-panel__icon" aria-hidden="true" focusable="false"><path d="M11.267 0C6.791-.015-1.82 10.246 1.397 12.964l.79.669a3.88 3.88 0 0 0-.22 1.792c.084.84.518 1.644 1.22 2.266.666.59 1.542.964 2.392.964 1.406 3.24 4.62 5.228 8.386 5.34 4.04.12 7.433-1.776 8.854-5.182.093-.24.488-1.316.488-2.267 0-.956-.54-1.352-.885-1.352-.01-.037-.078-.286-.172-.586-.093-.3-.19-.51-.19-.51.375-.563.382-1.065.332-1.35-.053-.353-.2-.653-.496-.964-.296-.311-.902-.63-1.753-.868l-.446-.124c-.002-.019-.024-1.053-.043-1.497-.014-.32-.042-.822-.197-1.315-.186-.668-.508-1.253-.911-1.627 1.112-1.152 1.806-2.422 1.804-3.511-.003-2.095-2.576-2.729-5.746-1.416l-.672.285A678.22 678.22 0 0 0 12.7.504C12.304.159 11.817.002 11.267 0zm.073.873c.166 0 .322.019.465.058.297.084 1.28 1.224 1.28 1.224s-1.826 1.013-3.52 2.426c-2.28 1.757-4.005 4.311-5.037 7.082-.811.158-1.526.618-1.963 1.253-.261-.218-.748-.64-.834-.804-.698-1.326.761-3.902 1.781-5.357C5.834 3.44 9.37.867 11.34.873zm3.286 3.273c.04-.002.06.05.028.074-.143.11-.299.26-.413.414a.04.04 0 0 0 .031.064c.659.004 1.587.235 2.192.574.041.023.012.103-.034.092-.915-.21-2.414-.369-3.97.01-1.39.34-2.45.863-3.224 1.426-.04.028-.086-.023-.055-.06.896-1.035 1.999-1.935 2.987-2.44.034-.018.07.019.052.052-.079.143-.23.447-.278.678-.007.035.032.063.062.042.615-.42 1.684-.868 2.622-.926zm3.023 3.205l.056.001a.896.896 0 0 1 .456.146c.534.355.61 1.216.638 1.845.015.36.059 1.229.074 1.478.034.571.184.651.487.751.17.057.33.098.563.164.706.198 1.125.4 1.39.658.157.162.23.333.253.497.083.608-.472 1.36-1.942 2.041-1.607.746-3.557.935-4.904.785l-.471-.053c-1.078-.145-1.693 1.247-1.046 2.201.417.615 1.552 1.015 2.688 1.015 2.604 0 4.605-1.111 5.35-2.072a.987.987 0 0 0 .06-.085c.036-.055.006-.085-.04-.054-.608.416-3.31 2.069-6.2 1.571 0 0-.351-.057-.672-.182-.255-.1-.788-.344-.853-.891 2.333.72 3.801.039 3.801.039a.072.072 0 0 0 .042-.072.067.067 0 0 0-.074-.06s-1.911.283-3.718-.378c.197-.64.72-.408 1.51-.345a11.045 11.045 0 0 0 3.647-.394c.818-.234 1.892-.697 2.727-1.356.281.618.38 1.299.38 1.299s.219-.04.4.073c.173.106.299.326.213.895-.176 1.063-.628 1.926-1.387 2.72a5.714 5.714 0 0 1-1.666 1.244c-.34.18-.704.334-1.087.46-2.863.935-5.794-.093-6.739-2.3a3.545 3.545 0 0 1-.189-.522c-.403-1.455-.06-3.2 1.008-4.299.065-.07.132-.153.132-.256 0-.087-.055-.179-.102-.243-.374-.543-1.669-1.466-1.409-3.254.187-1.284 1.31-2.189 2.357-2.135.089.004.177.01.266.015.453.027.85.085 1.223.1.625.028 1.187-.063 1.853-.618.225-.187.405-.35.71-.401.028-.005.092-.028.215-.028zm.022 2.18a.42.42 0 0 0-.06.005c-.335.054-.347.468-.228 1.04.068.32.187.595.32.765.175-.02.343-.022.498 0 .089-.205.104-.557.024-.942-.112-.535-.261-.872-.554-.868zm-3.66 1.546a1.724 1.724 0 0 0-1.016.326c-.16.117-.311.28-.29.378.008.032.031.056.088.063.131.015.592-.217 1.122-.25.374-.023.684.094.923.2.239.104.386.173.443.113.037-.038.026-.11-.031-.204-.118-.192-.36-.387-.618-.497a1.601 1.601 0 0 0-.621-.129zm4.082.81c-.171-.003-.313.186-.317.42-.004.236.131.43.303.432.172.003.314-.185.318-.42.004-.236-.132-.429-.304-.432zm-3.58.172c-.05 0-.102.002-.155.008-.311.05-.483.152-.593.247-.094.082-.152.173-.152.237a.075.075 0 0 0 .075.076c.07 0 .228-.063.228-.063a1.98 1.98 0 0 1 1.001-.104c.157.018.23.027.265-.026.01-.016.022-.049-.01-.1-.063-.103-.311-.269-.66-.275zm2.26.4c-.127 0-.235.051-.283.148-.075.154.035.363.246.466.21.104.443.063.52-.09.075-.155-.035-.364-.246-.467a.542.542 0 0 0-.237-.058zm-11.635.024c.048 0 .098 0 .149.003.73.04 1.806.6 2.052 2.19.217 1.41-.128 2.843-1.449 3.069-.123.02-.248.029-.374.026-1.22-.033-2.539-1.132-2.67-2.435-.145-1.44.591-2.548 1.894-2.811.117-.024.252-.04.398-.042zm-.07.927a1.144 1.144 0 0 0-.847.364c-.38.418-.439.988-.366 1.19.027.073.07.094.1.098.064.008.16-.039.22-.2a1.2 1.2 0 0 0 .017-.052 1.58 1.58 0 0 1 .157-.37.689.689 0 0 1 .955-.199c.266.174.369.5.255.81-.058.161-.154.469-.133.721.043.511.357.717.64.738.274.01.466-.143.515-.256.029-.067.005-.107-.011-.125-.043-.053-.113-.037-.18-.021a.638.638 0 0 1-.16.022.347.347 0 0 1-.294-.148c-.078-.12-.073-.3.013-.504.011-.028.025-.058.04-.092.138-.308.368-.825.11-1.317-.195-.37-.513-.602-.894-.65a1.135 1.135 0 0 0-.138-.01z"></path></svg>
);

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
	const [ proOpen, setProOpen ] = useState( false );

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

				<div className="wpzf-inspector__section">
					<h4 className="wpzf-inspector__section-title">{ __( 'Integrations', 'wpzoom-forms' ) }</h4>

					<div className="wpzf-integration-cards">
						<button
							type="button"
							className="wpzf-integration-card is-pro"
							onClick={ () => setProOpen( true ) }
						>
							<span className="wpzf-integration-card__icon"><MailchimpIcon /></span>
							<span className="wpzf-integration-card__info">
								<strong className="wpzf-integration-card__name">{ __( 'Mailchimp', 'wpzoom-forms' ) }</strong>
								<span className="wpzf-integration-card__desc">{ __( 'Subscribe users to your audience', 'wpzoom-forms' ) }</span>
							</span>
							<span className="wpzf-integration-card__badge is-pro">{ __( 'PRO', 'wpzoom-forms' ) }</span>
						</button>
					</div>
				</div>
			</div>
			{ proOpen && (
				<ProModal
					fieldLabel={ __( 'Mailchimp integration', 'wpzoom-forms' ) }
					onClose={ () => setProOpen( false ) }
				/>
			) }
		</div>
	);
}
