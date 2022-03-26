import {Type, Loc} from 'main.core';
import {BaseEvent} from "main.core.events";

import isHex from '../internal/is-hex';
import isRgbString from '../internal/is-rgb-string';
import isHslString from '../internal/is-hsl-string';
import isGradientString from '../internal/is-gradient-string';
import {isCssVar} from '../internal/css-var';

import Color from "./color";
import Gradient from "../control/gradient/gradient";
import Preset from '../layout/preset/preset';

import GradientValue from "../gradient_value";
import {IColorValue} from '../types/i_color_value';
import Opacity from '../control/opacity/opacity';

export default class BgColor extends Color
{
	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColor');
		this.property = ['background-image', 'background-color'];
		this.variableName = '--bg';
		this.className = 'g-bg';

		this.activeControl = null;

		this.gradient = new Gradient(options);
		this.gradient.subscribe('onChange', this.onGradientChange.bind(this));
		this.tabs.prependTab('Gradient', Loc.getMessage('LANDING_FIELD_COLOR-TAB_GRADIENT'), this.gradient);

		this.setGradientPreset(this.colorSet.getPreset());
		this.colorSet.subscribe('onPresetChange', (event) => {
			this.setGradientPreset(event.getData().preset);
		});

		this.tabs.subscribe('onToggle', this.onTabsToggle.bind(this));
	}

	setGradientPreset(preset: Preset)
	{
		const gradientPreset = preset.getGradientPreset();
		this.gradient.setPreset(gradientPreset);
		gradientPreset.subscribe('onChange', () => {
			this.activeControl = this.gradient;
			this.onChange();
		});

		const value = this.getValue();
		if (value !== null && value instanceof GradientValue)
		{
			if (this.gradient.getPreset().isPresetValue(value))
			{
				this.colorSet.getPreset().unsetActive();
				this.gradient.getPreset().setActiveValue(value);
				this.gradient.unsetColorpickerActive();
			}
		}
	}

	onColorSetChange(event: BaseEvent)
	{
		this.activeControl = this.colorSet;
		this.gradient.unsetActive();

		super.onColorSetChange(event);
	}

	onGradientChange(event: BaseEvent)
	{
		this.activeControl = this.gradient;
		this.colorSet.unsetActive();

		const gradValue = event.getData().gradient;
		if (gradValue !== null)
		{
			this.opacity.setValue(
				gradValue.setOpacity(this.opacity.getValue().getOpacity()),
			);
		}

		this.onChange();
	}

	onOverlayOpacityChange()
	{
		this.onChange();
	}

	onTabsToggle()
	{
		this.gradient.getPopup().close();
	}

	unsetActive()
	{
		this.colorSet.unsetActive();
		this.gradient.unsetActive();
		this.primary.unsetActive();
	}

	setValue(value: ?string): void
	{
		this.colorSet.setValue(null);
		this.gradient.setValue(null);
		this.unsetActive();

		this.activeControl = null;

		if (Type.isNull(value))
		{
			// todo: set NULL for gradient or opacity?
		}
		else if (
			isRgbString(value)
			|| isHex(value)
			|| isHslString(value)
			|| isCssVar(value)
		)
		{
			super.setValue(value);

			this.activeControl = this.colorSet;
		}
		else if (isGradientString(value))
		{
			this.activeControl = this.gradient;

			const gradientValue = new GradientValue(value);
			this.gradient.setValue(gradientValue);
			this.opacity.setValue(gradientValue);

			const presets = this.colorSet.getPresetsCollection();
			const activePreset = presets.getGlobalActiveId()
				? presets.getPresetById(presets.getGlobalActiveId())
				: presets.getPresetByItemValue(gradientValue);
			if (activePreset !== null && activePreset !== this.colorSet.getPreset())
			{
				this.colorSet.setPreset(activePreset);
				this.setGradientPreset(activePreset);
			}

			this.tabs.showTab('Gradient');
			if (gradientValue.getOpacity() < 1)
			{
				this.tabs.showTab('Opacity');
			}
		}
	}

	getValue(): ?IColorValue
	{
		return this.cache.remember('value', () => {
			if (this.activeControl === null)
			{
				return null;
			}
			else if (this.activeControl === this.gradient)
			{
				const gradValue = this.gradient.getValue();
				return (gradValue === null)
					? gradValue
					: gradValue.setOpacity(this.opacity.getValue().getOpacity());
			}
			else
			{
				return super.getValue();
			}
		});
	}
}
