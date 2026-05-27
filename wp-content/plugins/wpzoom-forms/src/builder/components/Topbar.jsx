import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextControl } from '@wordpress/components';
import { edit, code, settings, chevronLeft } from '@wordpress/icons';

const BellIcon = () => (
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="24px" width="24px">
		<path fill-rule="evenodd" d="M17 11.5c0 1.353.17 2.368.976 3 .266.209.602.376 1.024.5v1H5v-1c.422-.124.757-.291 1.024-.5.806-.632.976-1.647.976-3V9c0-2.8 2.2-5 5-5s5 2.2 5 5v2.5ZM15.5 9v2.5c0 .93.066 1.98.515 2.897l.053.103H7.932a4.018 4.018 0 0 0 .053-.103c.449-.917.515-1.967.515-2.897V9c0-1.972 1.528-3.5 3.5-3.5s3.5 1.528 3.5 3.5Zm-5.492 9.008c0-.176.023-.346.065-.508h3.854A1.996 1.996 0 0 1 12 20c-1.1 0-1.992-.892-1.992-1.992Z"></path>
	</svg>
);

const WPZform = (
	<svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fillRule="evenodd" clipRule="evenodd" d="M7.33 2h13.34v20H4V5.33h3.33V2zm10 3.33h-6.66v3.34H7.33v10h3.34v-3.34H14V12h-3.33V8.67h6.66V5.33z" /></svg>
)

export default function Topbar({
	title,
	dirty,
	saving,
	submissions,
	activeTab,
	onTitleChange,
	onSave,
	onEmbed,
	onTab,
	submissionsUrl,
	exitUrl,
}) {
	const [ editing, setEditing ] = useState( false );

	return (
		<header className="wpzf-topbar">
			<div className="wpzf-topbar__left">
				<Button
					variant="primary"
					className="wpzf-topbar__exit"
					href={ exitUrl }
					label={ __( 'Back to forms', 'wpzoom-forms' ) }
					icon={WPZform}
					iconSize={ 28 }
					showTooltip
				/>
				<Button
					size="compact"
					href={ submissionsUrl }
					label={ __( 'View Submissions', 'wpzoom-forms' ) }
					showTooltip
				>
					<span className="wpzf-badge">{ submissions || 0 }</span>
					{ __( 'Submissions', 'wpzoom-forms' ) }
				</Button>
			</div>

			<div className="wpzf-topbar__center">
				{ editing ? (
					<div className="wpzf-topbar__title-wrap">
						<TextControl
							label={ __( 'Form title', 'wpzoom-forms' ) }
							size="compact"
							hideLabelFromVision
							value={ title }
							onChange={ onTitleChange }
							onBlur={ () => setEditing( false ) }
							onFocus={ ( e ) => e.target.select() }
							onKeyDown={ ( e ) => {
								if ( e.key === 'Enter' || e.key === 'Escape' ) setEditing( false );
							} }
							autoFocus
							__nextHasNoMarginBottom
						/>
					</div>
				) : (
					<Button
						icon={ edit }
						size="compact"
						iconSize={ 20 }
						iconPosition="right"
						className="wpzf-topbar__title"
						onClick={ () => setEditing( true ) }
						label={ __( 'Rename form', 'wpzoom-forms' ) }
						showTooltip
					>
						{ title || __( 'Untitled form', 'wpzoom-forms' ) }
					</Button>
				) }
			</div>

			<div className="wpzf-topbar__right">
				<Button
					icon={ code }
					size="compact"
					onClick={ onEmbed }
				>
					{ __( 'Embed', 'wpzoom-forms' ) }
				</Button>

				<div className="wpzf-topbar__divider" aria-hidden="true" />

				<Button
					icon={ BellIcon }
					size="compact"
					isPressed={ activeTab === 'notifications' }
					label={ __( 'Notifications', 'wpzoom-forms' ) }
					showTooltip
					onClick={ () => onTab( activeTab === 'notifications' ? 'fields' : 'notifications' ) }
				>
					{ __( 'Notifications', 'wpzoom-forms' ) }
				</Button>

				<Button
					icon={ settings }
					size="compact"
					isPressed={ activeTab === 'form-settings' }
					label={ __( 'Form Settings', 'wpzoom-forms' ) }
					showTooltip
					onClick={ () => onTab( activeTab === 'form-settings' ? 'fields' : 'form-settings' ) }
				>
					{ __( 'Settings', 'wpzoom-forms' ) }
				</Button>

				<div className="wpzf-topbar__divider" aria-hidden="true" />

				<Button
					variant="primary"
					size="compact"
					onClick={ onSave }
					disabled={ saving || ! dirty }
					className={ dirty ? 'is-dirty' : undefined }
				>
					{ saving ? __( 'Saving…', 'wpzoom-forms' ) : ( dirty ? __( 'Save', 'wpzoom-forms' ) : __( 'Saved', 'wpzoom-forms' ) ) }
				</Button>

			</div>
		</header>
	);
}
