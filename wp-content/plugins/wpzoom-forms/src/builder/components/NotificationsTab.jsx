import { __ } from '@wordpress/i18n';
import {
	TextControl,
	TextareaControl,
	RadioControl,
	Button,
} from '@wordpress/components';
import { help } from '@wordpress/icons';

const NOTIFICATION_EMAIL_URL = 'https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=builder-notification-email-upsell';

const METHODS = [
	{ value: 'email',    label: __( 'Email Only', 'wpzoom-forms' ) },
	{ value: 'db',       label: __( 'Database Only', 'wpzoom-forms' ) },
	{ value: 'combined', label: __( 'Email + Database', 'wpzoom-forms' ) },
];

export default function NotificationsTab({ notifications, onChange, onShowGuide }) {
	return (
		<div className="wpzf-inspector">
			<div className="wpzf-inspector__header">
				<h3>{ __( 'Notifications & Confirmation', 'wpzoom-forms' ) }</h3>
				<Button icon={ help } size="small" label={ __( 'Show Welcome Guide', 'wpzoom-forms' ) } showTooltip onClick={ onShowGuide } />
			</div>
			<div className="wpzf-inspector__body">
				<RadioControl
					label={ __( 'How to handle submissions', 'wpzoom-forms' ) }
					selected={ notifications.method }
					options={ METHODS }
					onChange={ ( v ) => onChange( { method: v } ) }
				/>

				<div style={{ height: '16px' }} />

				{ ( notifications.method === 'email' || notifications.method === 'combined' ) && (
					<>
						<TextControl
							label={ __( 'Send notifications to', 'wpzoom-forms' ) }
							type="email"
							value={ notifications.email || '' }
							onChange={ ( v ) => onChange( { email: v } ) }
							placeholder="you@example.com"
							__next40pxDefaultSize
						/>
						<TextControl
							label={ __( 'Email subject', 'wpzoom-forms' ) }
							value={ notifications.subject || '' }
							onChange={ ( v ) => onChange( { subject: v } ) }
							__next40pxDefaultSize
						/>
					</>
				) }

				<TextareaControl
					label={ __( 'Success message', 'wpzoom-forms' ) }
					rows={ 2 }
					value={ notifications.successMessage || '' }
					onChange={ ( v ) => onChange( { successMessage: v } ) }
					__next40pxDefaultSize
				/>

				<TextareaControl
					label={ __( 'Failure message', 'wpzoom-forms' ) }
					rows={ 2 }
					value={ notifications.failureMessage || '' }
					onChange={ ( v ) => onChange( { failureMessage: v } ) }
					__next40pxDefaultSize
				/>

				<div className="wpzf-notification-email-upsell">
					<Button
						variant="primary"
						href={ NOTIFICATION_EMAIL_URL }
						target="_blank"
						rel="noopener noreferrer"
						className="wpzf-notification-email-upsell__btn"
						__next40pxDefaultSize
					>
						{ __( 'Customize Notification Email', 'wpzoom-forms' ) }
						<span className="wpzf-notification-email-upsell__badge">PRO</span>
					</Button>
					<p className="wpzf-notification-email-upsell__desc">
						{ __( 'Edit the template for emails sent to you when a new form entry is submitted.', 'wpzoom-forms' ) }
					</p>
				</div>
			</div>
		</div>
	);
}
