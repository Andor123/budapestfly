import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Button } from '@wordpress/components';
import FieldSettings from './FieldSettings.jsx';
import FormSettings from './FormSettings.jsx';
import NotificationsTab from './NotificationsTab.jsx';

/**
 * Short, factual tips about features of the plugin. One is picked at random
 * and shown in the empty state of the field inspector.
 */
const WPZF_TIPS = [
	__( 'You can customize the form button colors in the Styling options of the block on the page where the form is embedded.', 'wpzoom-forms' ),
	__( 'Switch the form theme under Form Settings — pick from styles like Default, Minimal, and Modern to match your site.', 'wpzoom-forms' ),
	__( 'Set the form to Wide or Full width using the block alignment toolbar on the page where it is embedded.', 'wpzoom-forms' ),
	__( 'Embed any form anywhere with the shortcode [wpzf_form id="123"] — just use the form ID.', 'wpzoom-forms' ),
	__( 'Enable the Honeypot option in Form Settings to block spam bots without bothering real visitors.', 'wpzoom-forms' ),
	__( 'Protect your forms from spam with reCAPTCHA or Cloudflare Turnstile in the plugin settings.', 'wpzoom-forms' ),
	__( 'Each field has a Custom CSS box — use the "selector" keyword to target just that field.', 'wpzoom-forms' ),
	__( 'Customize the success and failure messages visitors see after submitting in Form Settings.', 'wpzoom-forms' ),
	__( 'Change where labels sit (above or beside fields) and the form layout under Form Settings.', 'wpzoom-forms' ),
	__( 'All submissions are saved in WordPress, so you never lose a lead even if an email fails to send.', 'wpzoom-forms' ),
	__( 'Set who receives notification emails in the Notifications tab.', 'wpzoom-forms' ),
	__( 'Enable "Use as reply-to address" on an Email field so you can reply straight to the person who submitted the form.', 'wpzoom-forms' ),
	__( 'Speed things up with keyboard shortcuts: press Cmd/Ctrl+S to save, or Delete/Backspace to remove the selected field.', 'wpzoom-forms' ),
];

export default function Inspector({
	activeTab,
	selectedField,
	form,
	onSelectFieldsTab,
	onUpdateField,
	onUpdateSettings,
	onUpdateNotifications,
	onTab,
	onShowGuide,
}) {
	// Pick a tip once per mount so it stays stable while the user works.
	const [ tip ] = useState( () => WPZF_TIPS[ Math.floor( Math.random() * WPZF_TIPS.length ) ] );

	return (
		<aside className="wpzf-sidebar wpzf-sidebar--right">
			{ activeTab === 'fields' && selectedField && (
				<FieldSettings
					field={ selectedField }
					onChange={ onUpdateField }
					onShowGuide={ onShowGuide }
				/>
			) }

			{ activeTab === 'fields' && ! selectedField && (
				<div className="wpzf-inspector-empty">
					<svg width="48" height="48" viewBox="0 0 24 24" fill="#ccc"><path d="M12 2l9 4.9V17L12 22 3 17V6.9L12 2z"/></svg>
					<h3>{ __( 'Select a field', 'wpzoom-forms' ) }</h3>
					<p>{ __( 'Click any field in the form preview to edit its properties here.', 'wpzoom-forms' ) }</p>
					<Button
						variant="secondary"
						onClick={ () => onTab( 'form-settings' ) }
					>
						{ __( 'Edit Form Settings', 'wpzoom-forms' ) }
					</Button>

					<div className="wpzf-inspector-tip">
						<svg className="wpzf-inspector-tip__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M9 21h6M10 21v-2.5M14 21v-2.5M12 3a6 6 0 0 0-3.5 10.9c.6.45 1 1.16 1 1.94V16h5v-.16c0-.78.4-1.49 1-1.94A6 6 0 0 0 12 3Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round"/>
						</svg>
						<div className="wpzf-inspector-tip__body">
							<span className="wpzf-inspector-tip__label">{ __( 'Did you know?', 'wpzoom-forms' ) }</span>
							<p className="wpzf-inspector-tip__text">{ tip }</p>
						</div>
					</div>
				</div>
			) }

			{ activeTab === 'form-settings' && (
				<FormSettings settings={ form.schema.settings } onChange={ onUpdateSettings } onShowGuide={ onShowGuide } />
			) }

			{ activeTab === 'notifications' && (
				<NotificationsTab notifications={ form.notifications } onChange={ onUpdateNotifications } onShowGuide={ onShowGuide } />
			) }
		</aside>
	);
}
