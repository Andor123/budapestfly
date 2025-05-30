import Button from '@elementor/ui/Button';
import FormControl from '@elementor/ui/FormControl';
import Typography from '@elementor/ui/Typography';
import { styled } from '@elementor/ui/styles';
import { eventNames, mixpanelService, useStorage } from '@site-mailer/globals';
import { __ } from '@wordpress/i18n';
import { WORDPRESS_REVIEW_LINK } from '../constants';
import { useSettings } from '../hooks/use-settings';

const ReviewForm = ( { close } ) => {
	const { rating } = useSettings();
	const { save, get } = useStorage();

	const handleSubmit = async () => {
		mixpanelService.sendEvent( eventNames.review.publicRedirectClicked, {
			rating: parseInt( rating ),
			timestamp: new Date().toISOString(),
		} );

		await save( {
			site_mailer_review_data: {
				...get.data.site_mailer_review_data,
				repo_review_clicked: true,
			},
		} );

		close();
		window.open( WORDPRESS_REVIEW_LINK, '_blank' );
	};

	return (
		<FormControl fullWidth>
			<Typography variant="body1" marginBottom={ 1 }>
				{ __( 'It would mean a lot if you left us a quick review, so others can discover it too.', 'site-mailer' ) }
			</Typography>
			<StyledButton color="secondary" variant="contained" onClick={ handleSubmit }>{ __( 'Leave a review', 'site-mailer' ) }</StyledButton>
		</FormControl>
	);
};

export default ReviewForm;

const StyledButton = styled( Button )`
	min-width: 90px;
	align-self: flex-end;
`;
