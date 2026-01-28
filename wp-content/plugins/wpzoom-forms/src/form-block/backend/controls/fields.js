/**
 * Effects controls component with Dropdown interface
 */
import { __ } from "@wordpress/i18n";
import {
	__experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
	__experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
} from "@wordpress/block-editor";
import { __experimentalToolsPanel as ToolsPanel, __experimentalToolsPanelItem as ToolsPanelItem, RangeControl, __experimentalToggleGroupControl as ToggleGroupControl, __experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon } from "@wordpress/components";
import { lineSolid, lineDashed, lineDotted } from "@wordpress/icons";

const FieldsControls = ({ attributes, setAttributes }) => {
	const { fieldBgColor, fieldBrdStyle, fieldBrdWidth, fieldBrdRadius, fieldBrdColor, fieldTextColor, labelTextColor, } = attributes;

	const colorSettings = [
		{
			value: fieldTextColor,
			onChange: (value) => setAttributes({ fieldTextColor: value }),
			isShownByDefault: true,
			label: __("Text Color", "wpzoom-blocks"),
			resetAllFilter: () => setAttributes({ fieldTextColor: undefined })
		},
		{
			value: labelTextColor,
			onChange: (value) => setAttributes({ labelTextColor: value }),
			isShownByDefault: true,
			label: __("Label Color", "wpzoom-blocks"),
			resetAllFilter: () => setAttributes({ labelTextColor: undefined }),
		},
		{
			value: fieldBrdColor,
			onChange: (value) => setAttributes({ fieldBrdColor: value }),
			isShownByDefault: true,
			label: __("Border Color", "wpzoom-blocks"),
			resetAllFilter: () => setAttributes({ fieldBrdColor: undefined }),
		},
		{
			value: fieldBgColor,
			onChange: (value) => setAttributes({ fieldBgColor: value }),
			isShownByDefault: true,
			label: __("Background Color", "wpzoom-blocks"),
			resetAllFilter: () => setAttributes({ fieldBgColor: undefined }),
		},
	];

	const colorGradientSettings = useMultipleOriginColorsAndGradients();


	return (
		<ToolsPanel
			label={__("Fields", "wpzoom-blocks")}
			resetAll={() => setAttributes(
				{
					fieldTextColor: '',
					fieldBrdColor: '',
					fieldBgColor: '',
					labelTextColor: '',
					fieldBrdStyle: 'default',
					fieldBrdWidth: 0,
					fieldBrdRadius: 0,
				})}
			className="wpzoom-forms-fields__controls"
			dropdownMenuProps={{
				popoverProps: {
					placement: "left-start",
					offset: 259,
				},
			}}
			style={{ gap: '0' }}
		>

			{colorGradientSettings.hasColorsOrGradients
				&& colorSettings.map(
					({ onChange, label, isShownByDefault, value, resetAllFilter }) => (
						<ColorGradientSettingsDropdown
							key={`fields-color-${label}`}
							__experimentalIsRenderedInSidebar
							settings={[
								{
									colorValue: value,
									label,
									onColorChange: onChange,
									resetAllFilter,
									isShownByDefault,
									enableAlpha: true,
									clearable: true,
								},
							]}
							{...colorGradientSettings}
						/>
					),
				)
			}

			<ToolsPanelItem
				hasValue={() => fieldBrdStyle !== 'default' || !!fieldBrdWidth}
				label={__("Border", "wpzoom-blocks")}
				onDeselect={() => setAttributes({ fieldBrdStyle: undefined, fieldBrdWidth: undefined })}
				onSelect={() => setAttributes({ fieldBrdStyle: 'solid', fieldBrdWidth: 1 })}
				style={{ marginTop: '16px' }}
			>
				<RangeControl
					label={__('Border Width', 'wpzoom-forms')}
					value={fieldBrdWidth}
					onChange={(value) => setAttributes({ fieldBrdWidth: value })}
					min={0}
					max={10}
					__next40pxDefaultSize
				/>
				<ToggleGroupControl
					label={__('Border Style', 'wpzoom-forms')}
					value={fieldBrdStyle || 'solid'}
					onChange={(value) => setAttributes({ fieldBrdStyle: value })}
					isAdaptiveWidth={true}
					__next40pxDefaultSize
				>
					<ToggleGroupControlOptionIcon
						value="solid"
						icon={lineSolid}
						label={__('Solid', 'wpzoom-forms')}
					/>
					<ToggleGroupControlOptionIcon
						value="dashed"
						icon={lineDashed}
						label={__('Dashed', 'wpzoom-forms')}
					/>
					<ToggleGroupControlOptionIcon
						value="dotted"
						icon={lineDotted}
						label={__('Dotted', 'wpzoom-forms')}
					/>
				</ToggleGroupControl>
			</ToolsPanelItem>

			<ToolsPanelItem
				hasValue={() => !!fieldBrdRadius}
				label={__("Border Radius", "wpzoom-blocks")}
				onDeselect={() => setAttributes({ fieldBrdRadius: undefined })}
				onSelect={() => setAttributes({ fieldBrdRadius: undefined })}
				style={{ marginTop: '16px' }}
			>
				<RangeControl
					label={__('Border Radius', 'wpzoom-forms')}
					value={fieldBrdRadius}
					onChange={(value) => setAttributes({ fieldBrdRadius: value })}
					min={0}
					max={50}
					__next40pxDefaultSize
				/>
			</ToolsPanelItem>

		</ToolsPanel>
	);
};

export default FieldsControls;
