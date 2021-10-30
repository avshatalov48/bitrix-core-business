import {Type, Text} from 'main.core';

import {GradientValueOptions, defaultColorValueOptions} from "./types/color_value_options";
import ColorValue from "./color_value";
import {IColorValue} from './types/i_color_value';
import Primary from './layout/primary/primary';

import isGradientString, {
	matcherGradient,
	matcherGradientAngle,
	matcherGradientColors,
} from './internal/is-gradient-string';

export default class GradientValue implements IColorValue
{
	static TYPE_RADIAL = 'radial';
	static TYPE_LINEAR = 'linear';
	static DEFAULT_ANGLE = 180;
	static DEFAULT_TYPE = 'linear';

	value: GradientValueOptions;

	constructor(value: GradientValueOptions | GradientValue | string)
	{
		this.value = {
			from: new ColorValue('#ffffff'),
			to: new ColorValue(Primary.CSS_VAR),
			angle: GradientValue.DEFAULT_ANGLE,
			type: GradientValue.DEFAULT_TYPE,
		};
		this.setValue(value);
	}

	getName(): string
	{
		return this.value.from.getName() + '_' + this.value.to.getName() + '_' + this.getAngle() + '_' + this.getType();
	}

	// todo: parse grad string?
	setValue(value: GradientValueOptions | GradientValue | string): GradientValue
	{
		if (Type.isObject(value))
		{
			if (value instanceof GradientValue)
			{
				this.value.from = new ColorValue(value.getFrom());
				this.value.to = new ColorValue(value.getTo());
				this.value.angle = value.getAngle();
				this.value.type = value.getType();
			}
			else
			{
				if ('from' in value)
				{
					this.value.from = new ColorValue(value.from);
				}
				if ('to' in value)
				{
					this.value.to = new ColorValue(value.to);
				}
				if ('angle' in value)
				{
					this.value.angle = Text.toNumber(value.angle);
				}
				if ('type' in value)
				{
					this.value.type = value.type;
				}
			}
		}

		else if (Type.isString(value) && isGradientString(value))
		{
			this.parseGradientString(value);
		}

		return this;
	}

	setOpacity(opacity: number): GradientValue
	{
		this.value.from.setOpacity(opacity);
		this.value.to.setOpacity(opacity);

		return this;
	}

	parseGradientString(value: string): viod
	{
		const typeMatches = value.trim().match(matcherGradient);
		if (!!typeMatches)
		{
			this.setValue({type: typeMatches[1]});
		}

		const angleMatches = value.trim().match(matcherGradientAngle);
		if (!!angleMatches)
		{
			this.setValue({angle: angleMatches[2]});
		}


		const colorMatches = value.trim().match(matcherGradientColors);
		if (colorMatches && colorMatches.length > 0)
		{
			this.setValue({from: new ColorValue(colorMatches[0])});
			this.setValue({to: new ColorValue(colorMatches[colorMatches.length - 1])});
		}
	}

	getFrom(): ColorValue
	{
		return this.value.from;
	}

	getTo(): ColorValue
	{
		return this.value.to;
	}

	getAngle(): number
	{
		return this.value.angle;
	}

	setAngle(angle: number)
	{
		if (Type.isNumber(angle))
		{
			this.value.angle = Math.min(Math.max(angle, 0), 360);
		}
		return this;
	}

	getType(): string
	{
		return this.value.type;
	}

	setType(type: string)
	{
		if (type === GradientValue.TYPE_RADIAL || type === GradientValue.TYPE_LINEAR)
		{
			this.value.type = type;
		}
		return this;
	}

	getOpacity(): number
	{
		return (this.value.from.getOpacity() + this.value.to.getOpacity()) / 2 ?? defaultColorValueOptions.a;
	}

	getStyleString(): string
	{
		const angle = this.value.angle;
		const type = this.value.type;
		const fromString = this.value.from.getStyleString();
		const toString = this.value.to.getStyleString();

		return type === 'linear'
			? `linear-gradient(${angle}deg, ${fromString} 0%, ${toString} 100%)`
			: `radial-gradient(circle farthest-side at 50% 50%, ${fromString} 0%, ${toString} 100%)`;
	}

	getStyleStringForOpacity(): string
	{
		return `radial-gradient(at top left, ${this.value.from.getHex()}, transparent)`
			+ `, radial-gradient(at bottom left, ${this.value.to.getHex()}, transparent)`
	}

	static compare(value1: GradientValue, value2: GradientValue, full:boolean = true): boolean
	{
		const base = (
				ColorValue.compare(value1.getFrom(), value2.getFrom())
				&& ColorValue.compare(value1.getTo(), value2.getTo())
			) || (
				ColorValue.compare(value1.getTo(), value2.getFrom())
				&& ColorValue.compare(value1.getFrom(), value2.getTo())
			);
		const ext = full
			? (value1.getAngle() === value2.getAngle() && value1.getType() === value2.getType())
			: true;

		return  base && ext;
	}
}
