import {Event, Tag, Text, Dom, Loc} from 'main.core';
import {Popup, PopupManager} from 'main.popup';
import {BaseEvent} from "main.core.events";
import BaseControl from "../base_control/base_control";
import Colorpicker from "../colorpicker/colorpicker";
import Preset from '../../layout/preset/preset';

import 'ui.fonts.opensans';
import './css/gradient.css';
import GradientValue from "../../gradient_value";
import ColorValue from '../../color_value';

export default class Gradient extends BaseControl
{
	static DISABLE_CLASS = 'disable';

	popupId: string;
	popupTargetContainer: ?HTMLElement;

	preset: ?Preset;
	colorpickerFrom: Colorpicker;
	colorpickerTo: Colorpicker;

	+ROTATE_STEP = 45;

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Gradient');
		this.popupId = 'gradient_popup_' + Text.getRandom();
		this.popupTargetContainer = options.contentRoot;

		this.colorpickerFrom = new Colorpicker(options);
		this.colorpickerFrom.subscribe('onChange', (event) => {
			this.onColorChange(event.getData().color, null);
		});

		this.colorpickerTo = new Colorpicker(options);
		this.colorpickerTo.subscribe('onChange', (event) => {
			this.onColorChange(null, event.getData().color);
		});

		Event.bind(this.getPopupButton(), 'click', this.onPopupOpen.bind(this));
		Event.bind(this.getRotateButton(), 'click', this.onRotate.bind(this));
		Event.bind(this.getSwitchTypeButton(), 'click', this.onSwitchType.bind(this));
		Event.bind(this.getSwapButton(), 'click', this.onSwap.bind(this));

		this.preset = null;
	}

	onColorChange(fromValue: ?ColorValue, toValue: ?ColorValue)
	{
		if (fromValue === null && toValue === null)
		{
			return;
		}

		const valueToSet = this.getValue() || new GradientValue();
		const fromValueToSet = fromValue || valueToSet.getFrom() || (new GradientValue()).getFrom();
		const toValueToSet = toValue || valueToSet.getTo() || (new GradientValue()).getTo();
		valueToSet.setValue({
			from: fromValueToSet,
			to: toValueToSet,
		});

		this.setValue(valueToSet);
		this.preset.unsetActive();
		this.onChange();
	}

	onPopupOpen()
	{
		this.getPopup().toggle();
	}

	onRotate(event: MouseEvent)
	{
		// todo: not set colorpicker active
		if (!Gradient.isButtonEnable(event.target))
		{
			return;
		}

		const value = this.getValue();
		if (value !== null)
		{
			value.setValue({
				angle: ((value.getAngle() + this.ROTATE_STEP) % 360),
			});
			this.setValue(value);
			this.onChange();
		}
		this.getPopup().close();
	}

	onSwitchType(event: MouseEvent)
	{
		// todo: not set colorpicker active
		if (!Gradient.isButtonEnable(event.target))
		{
			return;
		}

		const value = this.getValue();
		if (value !== null)
		{
			if (value.getType() === GradientValue.TYPE_LINEAR)
			{
				value.setValue({type: GradientValue.TYPE_RADIAL});
				Gradient.disableButton(this.getRotateButton());
			}
			else
			{
				value.setValue({type: GradientValue.TYPE_LINEAR});
				Gradient.enableButton(this.getRotateButton());
			}
			this.setValue(value);
			this.onChange();
		}
		this.getPopup().close();
	}

	onSwap(event: MouseEvent)
	{
		// todo: not set colorpicker active
		if (!Gradient.isButtonEnable(event.target))
		{
			return;
		}

		const value = this.getValue();
		if (value !== null)
		{
			value.setValue({
				to: value.getFrom(),
				from: value.getTo(),
			});
			this.setValue(value);
			this.onChange();
		}
		this.getPopup().close();
	}

	static disableButton(button: HTMLDivElement)
	{
		Dom.addClass(button, Gradient.DISABLE_CLASS);
	}

	static enableButton(button: HTMLDivElement)
	{
		Dom.removeClass(button, Gradient.DISABLE_CLASS);
	}

	static isButtonEnable(button: HTMLDivElement)
	{
		return !Dom.hasClass(button, Gradient.DISABLE_CLASS);
	}

	correctColorpickerColors()
	{
		const value = this.getValue();
		if (value !== null)
		{
			const angle = value.getAngle();
			const hexFrom = this.colorpickerFrom.getHexPreviewObject();
			const hexTo = this.colorpickerTo.getHexPreviewObject();
			const colorFrom = value.getFrom();
			const colorTo = value.getTo();
			if (value.getType() === GradientValue.TYPE_LINEAR)
			{
				if (angle === 270 || angle === 90)
				{
					const median = ColorValue.getMedian(colorFrom, colorTo).getContrast().getHex();
					hexFrom.adjustColors(median, 'transparent');
					hexTo.adjustColors(median, 'transparent');
				}
				else if (angle >= 135 && angle <= 225)
				{
					hexFrom.adjustColors(colorFrom.getContrast().getHex(), 'transparent');
					hexTo.adjustColors(colorTo.getContrast().getHex(), 'transparent');
				}
				else
				{
					hexFrom.adjustColors(colorTo.getContrast().getHex(), 'transparent');
					hexTo.adjustColors(colorFrom.getContrast().getHex(), 'transparent');
				}
			}
			else if (value.getType() === GradientValue.TYPE_RADIAL)
			{
				hexFrom.adjustColors(colorTo.getContrast().getHex(), 'transparent');
				hexTo.adjustColors(colorTo.getContrast().getHex(), 'transparent');
			}
		}
	}

	getPopup(): Popup
	{
		return this.cache.remember('popup', () => {
			return PopupManager.create({
				id: this.popupId,
				className: 'landing-ui-field-color-gradient-preset-popup',
				autoHide: true,
				bindElement: this.getPopupButton(),
				bindOptions: {
					forceTop: true,
					forceLeft: true,
				},
				offsetLeft: 15,
				angle: {offset: -5},
				padding: 0,
				contentPadding: 7,
				content: this.getPopupContent(),
				closeByEsc: true,
				targetContainer: this.popupTargetContainer,
			});
		});
	}

	getPopupContent(): HTMLDivElement
	{
		return this.cache.remember('popupContainer', () => {
			return Tag.render`
				<div class="landing-ui-field-color-gradient-preset-popup-container">
					${this.getRotateButton()}
					${this.getSwapButton()}
				</div>
			`;
		});
	}

	buildLayout(): HTMLDivElement
	{
		if (this.preset)
		{
			Dom.clean(this.getPresetContainer());
			Dom.append(this.preset.getLayout(), this.getPresetContainer());
		}
		return Tag.render`
			<div class="landing-ui-field-color-gradient">
				${this.getPresetContainer()}
				<div class="landing-ui-field-color-gradient-container">
					<div class="landing-ui-field-color-gradient-from">${this.colorpickerFrom.getLayout()}</div>
					${this.getPopupButton()}
					<div class="landing-ui-field-color-gradient-to">${this.colorpickerTo.getLayout()}</div>
				</div>
				<div class="landing-ui-field-color-gradient-switch-type-container">
					${this.getSwitchTypeButton()}
				</div>
			</div>
		`;
	}

	getContainerLayout(): HTMLDivElement
	{
		// todo: do better after change vyorstka
		return this.getLayout().querySelector('.landing-ui-field-color-gradient-container');
	}

	getPresetContainer(): HTMLDivElement
	{
		return this.cache.remember('presetContainer', () => {
			return Tag.render`<div class="landing-ui-field-color-gradient-preset-container"></div>`;
		});
	}

	getPopupButton(): HTMLDivElement
	{
		return this.cache.remember('popupButton', () => {
			return Tag.render`<span class="landing-ui-field-color-gradient-open-popup"></span>`;
		});
	}

	getSwitchTypeButton(): HTMLDivElement
	{
		return this.cache.remember('switchTypeButton', () => {
			return Tag.render`
				<span
					class="landing-ui-field-color-gradient-switch-type"
					title="${Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_SWITCH_TYPE')}"
				></span>`;
		});
	}

	getRotateButton(): HTMLDivElement
	{
		return this.cache.remember('rotateButton', () => {
			return Tag.render`
				<span
					class="landing-ui-field-color-gradient-rotate"
					title="${Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_ROTATE')}"
				></span>`;
		});
	}

	getSwapButton(): HTMLDivElement
	{
		return this.cache.remember('swapButton', () => {
			return Tag.render`
				<span
					class="landing-ui-field-color-gradient-swap"
					title="${Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_SWAP')}"
				></span>`;
		});
	}

	setPreset(preset: Preset)
	{
		this.preset = preset;
		this.preset.unsetActive();
		this.preset.subscribe('onChange', (event) => {
			this.setValue(event.getData().color);
			this.unsetColorpickerActive();
			this.onChange(event);
		});

		Dom.clean(this.getPresetContainer());
		Dom.append(preset.getLayout(), this.getPresetContainer());
	}

	getPreset(): ?Preset
	{
		return this.preset;
	}

	getValue(): ?GradientValue
	{
		return this.cache.remember('value', () => {
			if (
				this.colorpickerFrom.getValue() === null
				|| this.colorpickerTo.getValue() === null
			)
			{
				return null;
			}

			let rotate = this.getRotateButton().dataset.rotate;
			rotate = rotate ? Text.toNumber(rotate) : 0;
			const type = this.getSwitchTypeButton().dataset.type || GradientValue.TYPE_LINEAR;

			return new GradientValue({
				from: this.colorpickerFrom.getValue(),
				to: this.colorpickerTo.getValue(),
				angle: rotate,
				type: type,
			});
		});
	}

	setValue(value: ?GradientValue)
	{
		super.setValue(value);

		if (value === null)
		{
			this.colorpickerFrom.setValue(null);
			this.colorpickerTo.setValue(null);

			this.unsetActive();

			Dom.style(this.getContainerLayout(), 'background', (new GradientValue).getStyleString());

			Gradient.disableButton(this.getRotateButton());
			Gradient.disableButton(this.getSwitchTypeButton());
			Gradient.disableButton(this.getSwapButton());
		}
		else
		{
			// todo: how set default type and rotation?
			this.colorpickerFrom.setValue(value.getFrom());
			this.colorpickerTo.setValue(value.getTo());
			this.correctColorpickerColors();

			this.getRotateButton().dataset.rotate = value.getAngle();
			this.getSwitchTypeButton().dataset.type = value.getType();

			Dom.style(this.getRotateButton(), 'transform', `rotate(${value.getAngle()}deg)`);
			Dom.style(this.getContainerLayout(), 'background', this.getValue().getStyleString());

			Gradient.enableButton(this.getSwitchTypeButton());
			Gradient.enableButton(this.getSwapButton());
			if (value.getType() === GradientValue.TYPE_RADIAL)
			{
				Gradient.disableButton(this.getRotateButton())
				this.getSwitchTypeButton().innerText = Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_DO_LINEAR');
			}
			else
			{
				Gradient.enableButton(this.getRotateButton());
				this.getSwitchTypeButton().innerText = Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_DO_RADIAL');
			}

			this.setActive();
		}
	}

	onChange(event: ?BaseEvent)
	{
		this.cache.delete('value');
		this.emit('onChange', {gradient: this.getValue()});
	}

	setActive(): void
	{
		const value = this.getValue();
		if (this.preset.isPresetValue(value))
		{
			this.preset.setActiveValue(value);
			this.unsetColorpickerActive();
		}
		else
		{
			this.preset.unsetActive();
			this.setColorpickerActive();
		}
	}

	unsetActive(): void
	{
		this.preset.unsetActive();
		this.unsetColorpickerActive();
	}

	setColorpickerActive(): void
	{
		Dom.addClass(this.getContainerLayout(), Gradient.ACTIVE_CLASS);
	}

	unsetColorpickerActive(): void
	{
		this.colorpickerFrom.unsetActive();
		this.colorpickerTo.unsetActive();
		Dom.removeClass(this.getContainerLayout(), Gradient.ACTIVE_CLASS);
	}
}
