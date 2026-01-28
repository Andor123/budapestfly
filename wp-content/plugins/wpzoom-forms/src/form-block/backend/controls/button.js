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

const ButtonControls = ({ attributes, setAttributes }) => {
    const { btnTextColor, btnBrdColor, btnBgColor, btnBrdStyle, btnBrdWidth, btnBrdRadius } = attributes;

    const colorSettings = [
        {
            value: btnTextColor,
            onChange: (value) => setAttributes({ btnTextColor: value }),
            isShownByDefault: true,
            label: __("Text Color", "wpzoom-blocks"),
            resetAllFilter: () => setAttributes({ btnTextColor: '' })
        },
        {
            value: btnBrdColor,
            onChange: (value) => setAttributes({ btnBrdColor: value }),
            isShownByDefault: true,
            label: __("Border Color", "wpzoom-blocks"),
            resetAllFilter: () => setAttributes({ btnBrdColor: '' }),
        },
        {
            value: btnBgColor,
            onChange: (value) => setAttributes({ btnBgColor: value }),
            isShownByDefault: true,
            label: __("Background Color", "wpzoom-blocks"),
            resetAllFilter: () => setAttributes({ btnBgColor: '' }),
        },
    ];

    const colorGradientSettings = useMultipleOriginColorsAndGradients();


    return (
        <ToolsPanel
            label={__("Button", "wpzoom-blocks")}
            resetAll={() => setAttributes(
                {
                    btnTextColor: '',
                    btnBrdColor: '',
                    btnBgColor: '',
                    btnBrdStyle: 'default',
                    btnBrdWidth: 0,
                    btnBrdRadius: 0,
                })}
            className="wpzoom-forms-button__controls"
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
                            key={`button-color-${label}`}
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
                hasValue={() => btnBrdStyle !== 'default' || !!btnBrdWidth}
                label={__("Border", "wpzoom-blocks")}
                onDeselect={() => setAttributes({ btnBrdStyle: undefined, btnBrdWidth: undefined })}
                onSelect={() => setAttributes({ btnBrdStyle: 'solid', btnBrdWidth: 1 })}
                style={{ marginTop: '16px' }}
            >
                <RangeControl
                    label={__('Border Width', 'wpzoom-forms')}
                    value={btnBrdWidth}
                    onChange={(value) => setAttributes({ btnBrdWidth: value })}
                    min={0}
                    max={10}
                    __next40pxDefaultSize
                />
                <ToggleGroupControl
                    label={__('Border Style', 'wpzoom-forms')}
                    value={btnBrdStyle || 'solid'}
                    onChange={(value) => setAttributes({ btnBrdStyle: value })}
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
                hasValue={() => !!btnBrdRadius}
                label={__("Border Radius", "wpzoom-blocks")}
                onDeselect={() => setAttributes({ btnBrdRadius: undefined })}
                onSelect={() => setAttributes({ btnBrdRadius: undefined })}
                style={{ marginTop: '16px' }}
            >
                <RangeControl
                    label={__('Border Radius', 'wpzoom-forms')}
                    value={btnBrdRadius}
                    onChange={(value) => setAttributes({ btnBrdRadius: value })}
                    min={0}
                    max={50}
                    __next40pxDefaultSize
                />
            </ToolsPanelItem>

        </ToolsPanel>
    );
};

export default ButtonControls;
