import {Dom, Tag, Text, Type} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import Opacity from './internal/opacity/opacity';

import './css/style.css';

export class ColorPickerField extends BaseField
{
	static id: number = 0;

	constructor(options)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.ColorPickerField');
		this.setLayoutClass('landing-ui-field-colorpicker');

		Dom.append(this.getColorLayout(), this.input);

		this.setValue(this.options.value);
	}

	getUid(): string
	{
		return this.cache.remember('uid', () => {
			ColorPickerField.id += 1;
			return `${Text.getRandom()}${ColorPickerField.id}`;
		});
	}

	getColorLabelInner()
	{
		return this.cache.remember('colorLabelInner', () => {
			return Tag.render`
				<span class="landing-ui-field-colorpicker-label-inner"></span>
			`;
		});
	}

	getColorLabel(): HTMLElement
	{
		return this.cache.remember('colorLabel', () => {
			return Tag.render`
				<label 
					class="landing-ui-field-colorpicker-label"
					for="${this.getUid()}"
					title="${Loc.getMessage('LANDING_COLORPICKER_FIELD_CHANGE_COLOR_TITLE')}"
				>
					${this.getColorLabelInner()}
				</label>
			`;
		});
	}

	getColorInput(): HTMLInputElement
	{
		return this.cache.remember('colorInput', () => {
			return Tag.render`
				<input 
					type="color" 
					class="landing-ui-field-colorpicker-input"
					id="${this.getUid()}"
					oninput="${this.onInputChange.bind(this)}"
					onchange="${this.onInputChange.bind(this)}"
				>
			`;
		});
	}

	onInputChange()
	{
		this.setValue(this.getColorInput().value, false, true);
	}

	getColorLayout(): HTMLDivElement
	{
		return this.cache.remember('colorLayout', () => {
			return Tag.render`
				<div class="landing-ui-field-colorpicker-layout">
					${this.getColorLabel()}
					${this.getColorInput()}
					${this.getOpacityField().getLayout()}
				</div>
			`;
		});
	}

	getOpacityField(): BX.Landing.UI.Field.Range
	{
		return this.cache.remember('opacityField', () => {
			return new Opacity({
				onChange: () => {
					const parsedValue = ColorPickerField.parseHex(this.getColorInput().value);
					parsedValue[3] = this.getOpacityField().getValue();
					Dom.style(this.getColorLabelInner(), {
						backgroundColor: ColorPickerField.toRgba(...parsedValue),
					});
					this.emit('onChange');
				},
			});
		});

		// return this.cache.remember('opacityField', () => {
		// 	const createOpacityItems = () => {
		// 		return Array.from({length: 101}, (item, index) => {
		// 			return {name: `${index}%`, value: `${(100 - index) / 100}`};
		// 		});
		// 	};
		//
		// 	return new window.top.BX.Landing.UI.Field.Range({
		// 		title: Loc.getMessage('LANDING_COLORPICKER_FIELD_OPACITY_TITLE'),
		// 		items: createOpacityItems(),
		// 		onChange: () => {
		// 			this.emit('onChange');
		// 		},
		// 	});
		// });
	}

	static prepareHex(hex: string): string
	{
		if (Type.isStringFilled(hex))
		{
			const preparedHex = hex.replace('#', '');
			if (preparedHex.length === 3)
			{
				return `#${preparedHex.split('').reduce((acc, item) => {
					return `${acc}${item}${item}`;
				}, '')}`;
			}
		}

		return hex;
	}

	static parseHex(hex)
	{
		hex = ColorPickerField.fillHex(hex);
		let parts = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})?$/i.exec(hex);
		if (!parts)
		{
			parts = [0, 0, 0, 1];
		}
		else
		{
			parts = [
				parseInt(parts[1], 16),
				parseInt(parts[2], 16),
				parseInt(parts[3], 16),
				parseInt(100 * (parseInt(parts[4] || 'ff', 16) / 255)) / 100,
			];
		}

		return parts;
	}

	static fillHex(hex, fillAlpha)
	{
		if (hex.length === 4 || (fillAlpha && hex.length === 5))
		{
			hex = hex.replace(/([a-f0-9])/gi, '$1$1');
		}

		if (fillAlpha && hex.length === 7)
		{
			hex += 'ff';
		}

		return hex;
	}

	static toHex(...args)
	{
		args[3] = typeof args[3] === 'undefined' ? 1 : args[3];
		args[3] = parseInt(255 * args[3]);

		return `#${args.map((part) => {
			part = part.toString(16);
			return part.length === 1 ? `0${part}` : part;
		}).join('')}`;
	}

	static hexToRgba(hex)
	{
		return `rgba(${this.parseHex(hex).join(', ')})`;
	}

	static toRgba(...args)
	{
		return `rgba(${args.join(', ')})`;
	}

	setValue(value: string, preventEvent = false, skipOpacity = false)
	{
		const parsedValue = ColorPickerField.parseHex(value);
		const hex = ColorPickerField.toHex(...parsedValue);

		if (value.length === 7)
		{
			parsedValue[3] = this.getOpacityField().getValue();
		}

		Dom.style(this.getColorLabelInner(), {
			backgroundColor: ColorPickerField.toRgba(parsedValue),
		});

		this.getColorInput().value = hex.slice(0, 7);
		this.getOpacityField().setValue({
			parsedColor: parsedValue,
			skipOpacity,
		});

		if (!preventEvent)
		{
			this.emit('onChange');
		}
	}

	getValue(): string
	{
		const parsedHex = ColorPickerField.parseHex(this.getColorInput().value);
		parsedHex[3] = this.getOpacityField().getValue();

		return ColorPickerField.toHex(...parsedHex);
	}
}