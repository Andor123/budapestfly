import { Guide } from '@wordpress/components';
import { withSelect, dispatch, useDispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { PluginMoreMenuItem } from '@wordpress/edit-post';
import { store as preferencesStore } from '@wordpress/preferences';
import { __ } from '@wordpress/i18n';

function WelcomeGuide( props ) {
	const { toggle } = useDispatch( preferencesStore );
	const [isFirstLoad, setIsFirstLoad] = useState(true);

	useEffect( () => {
		// Set default preference for welcome guide
		dispatch( preferencesStore ).setDefaults( 'wpzoom-forms', { welcomeGuide: true } );

		// Check if this is first time use
		if (isFirstLoad) {
			// Get the option value
			wp.apiFetch({ path: '/wp/v2/settings' }).then(settings => {
				const hasSeenWelcomeGuide = settings.wpzoom_forms_welcome_guide_shown;
				
				if ( !hasSeenWelcomeGuide ) {
					// Show the guide
					dispatch( preferencesStore ).set( 'wpzoom-forms', 'welcomeGuide', true );
					
					// Mark as shown in site options
					wp.apiFetch({
						path: '/wp/v2/settings',
						method: 'POST',
						data: {
							wpzoom_forms_welcome_guide_shown: true
						}
					});
				}
			});
			
			setIsFirstLoad(false);
		}
	}, [isFirstLoad] );

	return <>
		<PluginMoreMenuItem
			onClick={ () => toggle( 'wpzoom-forms', 'welcomeGuide' ) }
		>
			{ __( 'WPZOOM Forms Welcome Guide', 'wpzoom-forms' ) }
		</PluginMoreMenuItem>

		{ props.isActive && <Guide
            className="edit-post-welcome-guide"
			onFinish={ () => toggle( 'wpzoom-forms', 'welcomeGuide' ) }
			pages={ [
				{
					image: (
                        <img width="312" src={require('../images/step-1.png')} />
					),
					content: (
						<div className="wpzoom-forms-welcome-guide-page">
							<h2 class="edit-post-welcome-guide__heading">{ __( 'Welcome to WPZOOM Forms', 'wpzoom-forms' ) }</h2>
							<p class="edit-post-welcome-guide__text">
								{ __( 'Easily build custom forms in WordPress using the intuitive block editor — no coding needed.', 'wpzoom-forms' ) }
							</p>
						</div>
					),
				},
                {
                    image: (
                        <img width="312" src={require('../images/step-2.png')} />
                    ),
                    content: (
                        <div className="wpzoom-forms-welcome-guide-page">
                            <h2 class="edit-post-welcome-guide__heading">{ __( 'Drag & Drop Fields', 'wpzoom-forms' ) }</h2>
                            <p class="edit-post-welcome-guide__text">
                                { __( 'Edit your form visually. Add, remove, or rearrange fields directly in the editor with a simple drag-and-drop interface.', 'wpzoom-forms' ) }
                            </p>
                        </div>
                    ),
                },
                {
                    image: (
                        <img width="312" src={require('../images/step-3.png')} />
                    ),
                    content: (
                        <div className="wpzoom-forms-welcome-guide-page">
                            <h2 class="edit-post-welcome-guide__heading">{ __( 'Embed Forms Anywhere.', 'wpzoom-forms' ) }</h2>
                            <p class="edit-post-welcome-guide__text">
                                { __( 'Insert your forms using a block or shortcode, on pages, posts, or inside page builders.', 'wpzoom-forms' ) }
                            </p>
                        </div>
                    ),
                },
                {
                    image: (
                        <img width="312" src={require('../images/step-4.png')} />
                    ),
                    content: (
                        <div className="wpzoom-forms-welcome-guide-page">
                            <h2 class="edit-post-welcome-guide__heading">{ __( 'Customize Settings.', 'wpzoom-forms' ) }</h2>
                            <p class="edit-post-welcome-guide__text">
                                { __( 'Fine-tune each form and field from the sidebar to match your style and needs.', 'wpzoom-forms' ) }
                            </p>
                        </div>
                    ),
                },
			] }
		/> }
	</>;
}

export default withSelect( select => {
	return {
		isActive: select( preferencesStore ).get( 'wpzoom-forms', 'welcomeGuide' ),
	};
} )( WelcomeGuide );
