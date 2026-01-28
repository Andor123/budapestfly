import { 
	registerBlockType, 
	updateCategory 
} from '@wordpress/blocks';

import { __ } from '@wordpress/i18n';


import Edit from './edit';

const wpzoomFormsIcon = (
<svg width="40" height="40" viewBox="0 0 250 300" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M250 0H50V50H0V300H250V0Z" fill="#083EA7"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M100 50H200V100H100V50ZM100 150V100H50V250H100V200H150V150H100Z" fill="#1FDE91"/>
</svg>
);

updateCategory( 'wpzoom-blocks', {
	icon: (
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M0 2C0 0.895431 0.895431 0 2 0H14C15.1046 0 16 0.895431 16 2V14C16 15.1046 15.1046 16 14 16H2C0.895431 16 0 15.1046 0 14V2Z" fill="#164777"/>
        <path d="M5.276 12.084H6.032L8.156 7.224L10.268 12.084H11.024L13.148 5.316H13.988V4.104H10.628V5.316H11.708L10.508 9.468L8.552 4.872H7.832L5.876 9.468L4.592 5.316H5.636V4.104H2.276V5.316H3.116L5.276 12.084Z" fill="white"/>
        </svg>

	)
} );

registerBlockType( 'wpzoom-forms/form-block', {
	title:       __( 'Contact Form by WPZOOM', 'wpzoom-blocks' ),
	description: __( 'A contact form block for accepting submissions from users.', 'wpzoom-blocks' ),
    keywords: [
        __( 'contact', 'wpzoom-blocks' ),
        __( 'form', 'wpzoom-blocks' ),
        __( 'survey', 'wpzoom-blocks' ),
    ],
	icon:        wpzoomFormsIcon,
	category:    'wpzoom-blocks',
	supports:    { align: true, html: false },
	example:     {},
	edit:        Edit,
} );