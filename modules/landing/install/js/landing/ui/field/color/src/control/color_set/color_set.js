import {Dom, Tag} from 'main.core';
import {BaseEvent} from "main.core.events";

import BaseControl from '../base_control/base_control';
import ColorValue from "../../color_value";
import Colorpicker from "../colorpicker/colorpicker";
import Preset from '../../layout/preset/preset';
import PresetCollection from '../../layout/preset/preset_collection';
import Reset from '../../layout/reset/reset';
import Zeroing from '../../layout/zeroing/zeroing';

import Generator from '../../layout/preset/generator';
import './css/color_set.css';

export default class ColorSet extends BaseControl
{
	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.ColorSet');

		this.reset = new Reset(options);
		this.reset.subscribe('onReset', () => {
			this.emit('onReset');
		});

		this.zeroing = new Zeroing();
		this.zeroing.subscribe('onChange', (event) => {
			this.unsetActive();
			this.setValue(event.getData().color);
			// todo: need reload computed props and reinit

			this.onChange(event);
		});

		this.blackAndWhitePreset = new Preset(Generator.getBlackAndWhitePreset());
		this.blackAndWhitePreset.subscribe('onChange', (event) => {
			this.preset.unsetActive();
			this.onPresetItemChange(event);
		});

		this.colorpicker = new Colorpicker(options);
		this.colorpicker.subscribe('onChange', (event) => {
			this.preset.unsetActive();
			this.blackAndWhitePreset.unsetActive();

			this.onChange(event);
		});

		this.presets = new PresetCollection(options);
		this.presets.subscribe('onChange', (event) => {
			this.setPreset(event.getData().preset);
		});
		this.presets.addDefaultPresets();
		const preset = this.presets.getActivePreset() || this.presets.getDefaultPreset();
		if (preset)
		{
			this.setPreset(preset);
			// todo: what if not preset?
		}
	}

	buildLayout(): HTMLDivElement
	{
		Dom.append(this.reset.getLayout(), this.presets.getTitleContainer());
		Dom.prepend(this.zeroing.getLayout(), this.blackAndWhitePreset.getLayout());

		return Tag.render`
			<div class="landing-ui-field-color-colorset">
				<div class="landing-ui-field-color-colorset-top">
					${this.presets.getLayout()}
				</div>
				${this.getPresetContainer()}
				<div class="landing-ui-field-color-colorset-bottom">
					${this.blackAndWhitePreset.getLayout()}
					${this.colorpicker.getLayout()}
				</div>
			</div>
		`;
	}

	getTitleLayout(): HTMLDivElement
	{
		return this.cache.remember('titleLayout', () => {
			return this.getLayout().querySelector('.landing-ui-field-color-colorset-title');
		});
	}

	getPresetContainer(): HTMLDivElement
	{
		return this.cache.remember('presetContainer', () => {
			return Tag.render`<div class="landing-ui-field-color-colorset-preset-container"></div>`;
		});
	}

	setPreset(preset: Preset)
	{
		this.preset = preset;
		this.preset.unsetActive();
		if (this.getValue() !== null && this.preset.isPresetValue(this.getValue()))
		{
			this.unsetActive();
			this.preset.setActiveValue(this.getValue());
		}
		else
		{
			this.unsetActive();
			this.colorpicker.setValue(this.getValue());
		}
		this.preset.subscribe('onChange', (event) => {
			this.blackAndWhitePreset.unsetActive();
			this.onPresetItemChange(event);
		});

		Dom.clean(this.getPresetContainer());
		Dom.append(preset.getLayout(), this.getPresetContainer());

		this.emit('onPresetChange', {preset: preset});
	}

	getPreset(): ?Preset
	{
		return this.preset;
	}

	onPresetItemChange(event: BaseEvent)
	{
		this.colorpicker.setValue(event.getData().color);
		this.colorpicker.unsetActive();
		this.onChange(event);
	}

	onChange(event: ?BaseEvent)
	{
		this.cache.set('value', event.getData().color);
		this.emit('onChange', event);
	}

	getValue(): ?ColorValue
	{
		return this.cache.remember('value', () => {
			return this.colorpicker.getValue();
		});
	}

	setValue(value: ?ColorValue)
	{
		super.setValue(value);
		this.colorpicker.setValue(value);

		const activePreset =
			this.presets.getActiveId()
				? this.presets.getPresetById(this.presets.getActiveId())
				: this.presets.getPresetByItemValue(value);
		if (activePreset !== null)
		{
			this.setPreset(activePreset);
			this.presets.setActiveItem(activePreset.getId());
		}
		if (value !== null && this.blackAndWhitePreset.isPresetValue(value))
		{
			this.unsetActive();
			this.blackAndWhitePreset.setActiveValue(value);
		}
	}

	unsetActive(): void
	{
		this.preset.unsetActive();
		this.blackAndWhitePreset.unsetActive();
		this.colorpicker.unsetActive();
	}

	isActive(): boolean
	{
		return this.preset.isActive() || this.blackAndWhitePreset.isActive() || this.colorpicker.isActive();
	}
}
