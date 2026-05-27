import { Guide } from '@wordpress/components';
import { useSelect, useDispatch, dispatch } from '@wordpress/data';
import { store as preferencesStore } from '@wordpress/preferences';
import { __ } from '@wordpress/i18n';

import step1 from '../images/step-1.png';
import step2 from '../images/step-2.png';
import step3 from '../images/step-3.png';
import step4 from '../images/step-4.png';

// Scope shared with wpzoom-forms-pro so the preference persists across both.
const PREF_SCOPE = 'wpzoom-forms';
const PREF_KEY   = 'builderWelcomeGuide';

// Set default before any render so first-time users see the guide automatically.
dispatch( preferencesStore ).setDefaults( PREF_SCOPE, { [ PREF_KEY ]: true } );

export default function WelcomeGuide() {
	const { set } = useDispatch( preferencesStore );

	const isActive = useSelect(
		( select ) => select( preferencesStore ).get( PREF_SCOPE, PREF_KEY ),
		[]
	);

	if ( ! isActive ) return null;

	const close = () => set( PREF_SCOPE, PREF_KEY, false );

	return (
		<Guide
			className="wpzf-welcome-guide"
			onFinish={ close }
			pages={ [
				{
					image: (
						<img width="312" src={ step1 } alt="" />
					),
					content: (
						<div className="wpzf-welcome-guide__page">
							<h2 className="wpzf-welcome-guide__heading">
								{ __( 'Welcome to the Form Builder', 'wpzoom-forms' ) }
							</h2>
							<p className="wpzf-welcome-guide__text">
								{ __( 'Create powerful forms in a focused, full-screen environment designed specifically around the way you build forms.', 'wpzoom-forms' ) }
							</p>
						</div>
					),
				},
				{
					image: (
						<img width="312" src={ step2 } alt="" />
					),
					content: (
						<div className="wpzf-welcome-guide__page">
							<h2 className="wpzf-welcome-guide__heading">
								{ __( 'Drag, Drop & Reorder', 'wpzoom-forms' ) }
							</h2>
							<p className="wpzf-welcome-guide__text">
								{ __( 'Pull any field from the left panel onto the canvas. Drag to reorder, click to configure, or duplicate any field in one click.', 'wpzoom-forms' ) }
							</p>
						</div>
					),
				},
				{
					image: (
						<img width="312" src={ step3 } alt="" />
					),
					content: (
						<div className="wpzf-welcome-guide__page">
							<h2 className="wpzf-welcome-guide__heading">
								{ __( 'Configure Your Form', 'wpzoom-forms' ) }
							</h2>
							<p className="wpzf-welcome-guide__text">
								{ __( 'Use the Settings panel to change the submit button text, customize form layouts, adjust styles... and more.', 'wpzoom-forms' ) }
			
							</p>
						</div>
					),
				},
				{
					image: (
						<img width="312" src={ step4 } alt="" />
					),
					content: (
						<div className="wpzf-welcome-guide__page">
							<h2 className="wpzf-welcome-guide__heading">
								{ __( 'Embed Your Form Anywhere', 'wpzoom-forms' ) }
							</h2>
							<p className="wpzf-welcome-guide__text">
								{ __( 'Copy the shortcode and paste it into any page, post, or page builder. Your form is live instantly, wherever you need it.', 'wpzoom-forms' ) }
							</p>
						</div>
					),
				},
			] }
		/>
	);
}
