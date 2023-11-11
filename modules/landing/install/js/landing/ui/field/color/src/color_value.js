import {Dom, Type} from 'main.core';

import isRgbString from './internal/is-rgb-string';
import isHex from './internal/is-hex';
import isHslString from './internal/is-hsl-string';
import hexToHsl from './internal/hex-to-hsl';
import hslToHex from './internal/hsl-to-hex';
import rgbStringToHsla from './internal/rgb-string-to-hsl';
import hslStringToHsl from './internal/hsl-string-to-hsl';
import {isCssVar, parseCssVar} from "./internal/css-var";
import {ColorValueOptions, defaultColorValueOptions} from "./types/color_value_options";
import {IColorValue} from './types/i_color_value';

export default class ColorValue implements IColorValue
{
	value: ColorValueOptions;
	/**
	 * For preserve differences between hsl->rgb and rgb->hsl conversions we can save hex
	 * @type {?string}
	 */
	hex: ?string;
	/**
	 * if set css variable value - save them in '--var-name' format
	 * @type {?string}
	 */
	cssVar: ?string;

	constructor(value: ColorValueOptions | ColorValue | string)
	{
		this.value = defaultColorValueOptions;
		this.hex = null;
		this.cssVar = null;
		this.setValue(value);
	}

	getName(): string
	{
		if (this.hex)
		{
			return this.getHex() + '_' + this.getOpacity();
		}
		const {h, s, l} = this.getHsl();
		return `${h}-${s}-${l}-${this.getOpacity()}`;
	}

	setValue(value: ColorValueOptions | ColorValue | string): ColorValue
	{
		if (Type.isObject(value))
		{
			if (value instanceof ColorValue)
			{
				this.value = value.getHsla();
				this.cssVar = value.getCssVar();
				this.hex = value.getHexOriginal();
			}
			else
			{
				this.value = {...this.value, ...value};
			}
		}

		if (Type.isString(value))
		{
			if (isHslString(value))
			{
				this.value = hslStringToHsl(value);
			}
			else if (isHex(value))
			{
				this.value = {...hexToHsl(value), a: defaultColorValueOptions.a};
				this.hex = value;
			}
			else if (isRgbString(value))
			{
				this.value = rgbStringToHsla(value);
			}
			else if (isCssVar(value))
			{
				const cssVar = parseCssVar(value);
				const cssPrimaryVarName = '--primary';
				if (cssVar !== null)
				{
					this.cssVar = cssVar.name;
					if ('opacity' in cssVar)
					{
						this.cssVar = cssPrimaryVarName;
						this.setValue(Dom.style(document.documentElement, this.cssVar));
						this.setOpacity(cssVar.opacity);
					}
					else
					{
						this.setValue(Dom.style(document.documentElement, this.cssVar));
					}
				}
			}
		}

		this.value.h = Math.round(this.value.h);
		this.value.s = Math.round(this.value.s);
		this.value.l = Math.round(this.value.l);
		this.value.a = this.value.a.toFixed(2);
		const offsetFromCorrectValue = Math.round((this.value.a * 100) % 5);
		if (offsetFromCorrectValue < 3)
		{
			this.value.a = (this.value.a * 100 - offsetFromCorrectValue) / 100;
		}
		else
		{
			this.value.a = (this.value.a * 100 - offsetFromCorrectValue + 5) / 100;
		}

		return this;
	}

	setOpacity(opacity: number): ColorValue
	{
		this.setValue({a: opacity});

		return this;
	}

	lighten(percent: number): ColorValue
	{
		this.value.l = Math.min(this.value.l + percent, 100);
		this.hex = null;

		return this;
	}

	darken(percent: number): ColorValue
	{
		this.value.l = Math.max(this.value.l - percent, 0);
		this.hex = null;

		return this;
	}

	saturate(percent: number): ColorValue
	{
		this.value.s = Math.min(this.value.s + percent, 100);
		this.hex = null;

		return this;
	}

	desaturate(percent: number): ColorValue
	{
		this.value.s = Math.max(this.value.s - percent, 0);
		this.hex = null;

		return this;
	}

	adjustHue(degree: number): ColorValue
	{
		this.value.h = (this.value.h + degree) % 360;

		return this;
	}

	getHsl(): {h: number, s: number, l: number}
	{
		return {
			h: this.value.h,
			s: this.value.s,
			l: this.value.l,
		};
	}

	getHsla(): ColorValueOptions
	{
		const a = this.value.a || 1;
		return {
			h: this.value.h,
			s: this.value.s,
			l: this.value.l,
			a,
		};
	}

	/**
	 * Return original hex-string or convert value to hex (w.o. alpha)
	 * @returns {string}
	 */
	getHex(): string
	{
		return this.hex || hslToHex(this.value);
	}

	/**
	 * Return hex only if value created from hex-string
	 */
	getHexOriginal(): string
	{
		return this.hex;
	}

	getOpacity(): number
	{
		return this.value.a ?? defaultColorValueOptions.a;
	}

	getCssVar(): ?string
	{
		return this.cssVar;
	}

	/**
	 * Get style string for set inline css var.
	 * Set hsla value or primary css var with opacity in format --var-name-opacity_12_3
	 * @returns {string}
	 */
	getStyleString(): string
	{
		if (this.cssVar === null)
		{
			if (this.hex && this.getOpacity() === defaultColorValueOptions.a)
			{
				return this.hex
			}

			const {h, s, l, a} = this.value;

			return `hsla(${h}, ${s}%, ${l}%, ${a})`;
		}
		else
		{
			let fullCssVar = this.cssVar;
			if (this.value.a !== defaultColorValueOptions.a)
			{
				fullCssVar = fullCssVar + '-opacity-' + String(this.value.a).replace('.', '_');
			}
			return `var(${fullCssVar})`;
		}
	}

	getStyleStringForOpacity(): string
	{
		const {h, s, l} = this.value;

		return `linear-gradient(to right, hsla(${h}, ${s}%, ${l}%, 0) 0%, hsla(${h}, ${s}%, ${l}%, 1) 100%)`;
	}

	static compare(color1: ColorValue, color2: ColorValue): boolean
	{
		return color1.getHsla().h === color2.getHsla().h
			&& color1.getHsla().s === color2.getHsla().s
			&& color1.getHsla().l === color2.getHsla().l
			&& color1.getHsla().a === color2.getHsla().a
			&& color1.cssVar === color2.cssVar;
	}

	static getMedian(color1: ColorValue, color2: ColorValue): ColorValue
	{
		return new ColorValue({
			h: (color1.getHsla().h + color2.getHsla().h) / 2,
			s: (color1.getHsla().s + color2.getHsla().s) / 2,
			l: (color1.getHsla().l + color2.getHsla().l) / 2,
			a: (color1.getHsla().a + color2.getHsla().a) / 2,
		});
	}

	/**
	 * Special formula for contrast. Not only color invert!
	 * @returns {string}
	 */
	getContrast(): ColorValue
	{
		let k = 60;
		// math h range to 0-2pi radian and add modifier by sinus
		let rad = this.getHsl().h * Math.PI / 180;
		k += (Math.sin(rad) * 10) + 5;	// 10 & 5 is approximate coefficients
		// lighten by started light
		let deltaL = k - (45 * this.getHsl().l / 100);

		return new ColorValue(this.value).setValue({l: (this.getHsl().l + deltaL) % 100});
	}

	/**
	 * Special formula for lighten, good for dark and light colors
	 */
	getLighten(): ColorValue
	{
		let {h, s, l} = this.getHsl();

		if (s > 0)
		{
			s += (l - 50) / 100 * 60;
			s = Math.min(100, Math.max(0, l));
		}

		l += 10 + 20 * l / 100;
		l = Math.min(100, l);

		return new ColorValue({h, s, l});
	}
}
