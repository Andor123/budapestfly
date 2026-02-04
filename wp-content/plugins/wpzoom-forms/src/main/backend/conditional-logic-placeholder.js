/**
 * Conditional Logic placeholder for the free version.
 * Shows a panel with an "Unlock this feature" link when a form field block is selected.
 * The actual conditional logic is available in WPZOOM Forms Pro.
 */

import { createHigherOrderComponent } from "@wordpress/compose";
import { addFilter } from "@wordpress/hooks";
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { __experimentalToolsPanel as ToolsPanel, Button, Notice } from '@wordpress/components';
import { arrowRight } from '@wordpress/icons';

const ALLOWED_FIELD_BLOCKS = [
	'wpzoom-forms/text-plain-field',
	'wpzoom-forms/text-name-field',
	'wpzoom-forms/text-email-field',
	'wpzoom-forms/text-website-field',
	'wpzoom-forms/text-phone-field',
	'wpzoom-forms/textarea-field',
	'wpzoom-forms/select-field',
	'wpzoom-forms/multi-checkbox-field',
	'wpzoom-forms/checkbox-field',
	'wpzoom-forms/radio-field',
	'wpzoom-forms/datepicker-field'
];

const CONDITIONAL_LOGIC_PRO_URL = 'https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpzoom-forms-free&utm_medium=conditional-logic-upsell&utm_campaign=plugin-upsell';

/**
 * Add conditional logic placeholder inspector controls to the block editor
 */
addFilter(
	"editor.BlockEdit",
	"wpzoom-forms/conditional-logic-placeholder",
	createHigherOrderComponent((BlockEdit) => {
		return (props) => {
			const { name } = props;

			if (!ALLOWED_FIELD_BLOCKS.includes(name)) {
				return <BlockEdit {...props} />;
			}

			return (
				<>
					<BlockEdit key="edit" {...props} />
					<InspectorControls>
						<ToolsPanel
							label={
								<>
									{__("Conditional Logic", "wpzoom-forms")}
									<span
										className="wpzoom-forms-conditional-logic-pro-badge"
										style={{
											display: 'inline-block',
											backgroundColor: '#eff6ff',
											color: '#2563eb',
											fontSize: '9px',
											padding: '3px 6px',
											borderRadius: '3px',
											marginLeft: '8px',
											lineHeight: '1.4',
											textTransform: 'uppercase',
											letterSpacing: '0.5px',
											border: '1px solid #dbeafe'
										}}
									>
										{__('Pro', 'wpzoom-forms')}
									</span>
								</>
							}
							dropdownMenuProps={{
								popoverProps: {
									placement: "left-start",
									offset: 259,
								},
							}}
						>
							<div style={{ 
									gridColumn: '1 / -1',
									padding: '12px',
									backgroundColor: '#eff6ff',
									borderRadius: '3px',
									border: '1px solid #dbeafe'
								}}
							>
								<p style={{ margin: '0 0 12px 0' }}>
									{__('Show or hide this field based on the value of another field.', 'wpzoom-forms')}
								</p>
								
								<Button
									href={CONDITIONAL_LOGIC_PRO_URL}
									target="_blank"
									rel="noopener noreferrer"
									variant="primary"
									className="wpzoom-forms-unlock-feature-link"
									icon={arrowRight}
									iconPosition="right"
									iconSize={20}
								>
									{__('Unlock this feature', 'wpzoom-forms')}
								</Button>
							</div>
						</ToolsPanel>
					</InspectorControls>
				</>
			);
		};
	}, "withConditionalLogicPlaceholder"),
);
