// @flow

import {Dom, Tag, Type} from 'main.core';
import CounterColor from './cnt-color';
import CounterSize from './cnt-size';

type CounterOptions = {
	value: number;
	maxValue: number;
	color: CounterColor;
	border: boolean;
	size: string;
};

export default class Counter
{
	static Color = CounterColor;
	static Size = CounterSize;

	constructor(options: CounterOptions)
	{
		this.options = Type.isPlainObject(options) ? options : {};

		this.container = null;
		this.counterContainer = null;
		this.animate = Type.isBoolean(this.options.animate) ? this.options.animate : false;
		this.value = Type.isNumber(this.options.value) ? this.options.value : 0;
		this.maxValue = Type.isNumber(this.options.maxValue) ? this.options.maxValue : 99;
		this.size = Type.isString(this.options.size) ? this.options.size : BX.UI.Counter.Size.MEDIUM;
		this.color = Type.isString(this.options.color) ? this.options.color : BX.UI.Counter.Color.PRIMARY;
		this.border = Type.isBoolean(this.options.border) ? this.options.border : false;
	}

	//region Parameters
	setValue(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.value = (value < 0) ? 0 : value;
		}

		return this;
	}

	getValue(): number
	{
		if (this.value <= this.maxValue)
		{
			return this.value;
		}
		else
		{
			return this.maxValue + "+";
		}
	}

	setMaxValue(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.value = (value < 0) ? 0 : value;
		}

		return this;
	}

	getMaxValue(): number
	{
		return this.maxValue;
	}

	isBorder(): boolean
	{
		return this.border;
	}

	setColor(color: CounterColor): this
	{
		if (Type.isStringFilled(color))
		{
			if (this.container === null)
			{
				this.createContainer();
			}

			Dom.removeClass(this.container, this.color);
			this.color = color;
			Dom.addClass(this.container, this.color);
		}

		return this;
	}

	setSize(size: CounterSize): this
	{
		if (Type.isStringFilled(size))
		{
			BX.removeClass(this.container, this.size);
			this.size = size;
			BX.addClass(this.container, this.size);
		}

		return this;
	}

	setAnimate(animate: boolean): this
	{
		if (Type.isBoolean(animate))
		{
			this.animate = animate;
		}

		return this;
	}

	setBorder(border: boolean): this
	{
		if (!Type.isBoolean(border))
		{
			console.warn('Parameter "border" is not boolean');
			return this;
		}

		this.border = border;
		const borderedCounterClassname = this.#getBorderClassname(border);

		if (border)
		{
			Dom.addClass(this.container, borderedCounterClassname);
		} else
		{
			Dom.removeClass(this.container, borderedCounterClassname);
		}

		return this;
	}

	#getBorderClassname(border: boolean): string
	{
		if (border)
		{
			return 'ui-counter-border';
		}
		else
		{
			return '';
		}
	}

	//endregion

	// region Counter
	update(value)
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (this.animate == true)
		{
			this.updateAnimated(value);
		}
		else if (this.animate == false)
		{
			this.setValue(value);
			Dom.adjust(this.counterContainer, {
				text: this.getValue()
			});
		}

	}

	updateAnimated(value)
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (value > this.value && this.value < this.maxValue)
		{
			Dom.addClass(this.counterContainer, "ui-counter-plus");
		}
		else if (value < this.value && this.value < this.maxValue)
		{
			Dom.addClass(this.counterContainer, "ui-counter-minus");
		}

		setTimeout(function ()
			{
				this.setValue(value);
				Dom.adjust(this.counterContainer, {
					text: this.getValue()
				});
			}.bind(this),
			250);

		setTimeout(function ()
			{
				Dom.removeClass(this.counterContainer, "ui-counter-plus");
				Dom.removeClass(this.counterContainer, "ui-counter-minus");
			}.bind(this),
			500);
	}

	show()
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		Dom.addClass(this.container, "ui-counter-show");
		Dom.removeClass(this.container, "ui-counter-hide");
	}

	hide()
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		Dom.addClass(this.container, "ui-counter-hide");
		Dom.removeClass(this.container, "ui-counter-show");
	}

	getCounterContainer()
	{
		if (this.counterContainer === null)
		{
			this.counterContainer = Tag.render`
				<div class="ui-counter-inner">${this.getValue()}</div>
			`;
		}

		return this.counterContainer;
	}

	createContainer(): HTMLElement
	{
		if (this.container === null)
		{
			this.container = Tag.render`
				<div class="ui-counter">${this.getCounterContainer()}</div>
			`;

			this.setSize(this.size);
			this.setColor(this.color);
			this.setBorder(this.border);
		}

		return this.container;
	}

	//endregion

	getContainer(): Element
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		return this.container;
	}

	renderTo(node: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(node))
		{
			return node.appendChild(this.getContainer());
		}

		return null;
	}

	destroy(): void
	{
		Dom.remove(this.container);
		this.container = null;
		this.finished = false;
		this.textAfterContainer = null;
		this.textBeforeContainer = null;
		this.bar = null;
		this.svg = null;

		for (const property in this)
		{
			if (this.hasOwnProperty(property))
			{
				delete this[property];
			}
		}

		Object.setPrototypeOf(this, null);
	}
}
