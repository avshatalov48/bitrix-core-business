import { Dom, Tag, Type } from 'main.core';
import 'ui.design-tokens.air';
import CounterColor from './cnt-color';
import CounterSize from './cnt-size';

import './air.css';
import { CounterStyle } from './cnt-style';

export type CounterOptions = {
	useAirDesign: boolean;
	style: CounterStyle;
	value: number;
	maxValue: number;
	color: CounterColor;
	secondaryColor: CounterColor;
	border: boolean;
	size: string;
	isDouble: boolean;
	usePercentSymbol?: boolean;
	hideIfZero?: boolean;
	node?: HTMLElement;
	id?: string;
};

export default class Counter
{
	static Color = CounterColor;
	static Size = CounterSize;
	static Style = CounterStyle;
	static BaseClassname = 'ui-counter';

	#usePercentSymbol: boolean = false;
	#useAirDesign: boolean = false;
	#style: CounterStyle = CounterStyle.FILLED;
	#hideIfZero: boolean = false;
	#node: HTMLElement;
	#id: ?string = undefined;

	static initFromCounterNode(node: HTMLElement): ?Counter
	{
		if (Dom.hasClass(node, Counter.BaseClassname) === false)
		{
			return null;
		}

		const options: CounterOptions = {};

		options.useAirDesign = Dom.hasClass(node, '--air');
		options.style = Object.values(Counter.Style).find((value) => Dom.hasClass(node, value)) ?? Counter.Color.PRIMARY;
		options.color = Object.values(Counter.Color).find((value) => Dom.hasClass(node, value)) ?? Counter.Style.FILLED;
		options.size = Object.values(Counter.Size).find((value) => Dom.hasClass(node, value)) ?? Counter.Size.MEDIUM;
		options.value = parseInt(Dom.attr(node, 'data-value'), 10);
		options.hideIfZero = Dom.hasClass(node, '--hide-zero');
		options.node = node;
		options.id = node.id;

		return new Counter(options);
	}

	static updateCounterNodeValue(node: HTMLElement, value: number): void
	{
		const counter = Counter.initFromCounterNode(node);

		if (counter && Type.isNumber(value))
		{
			counter.update(value);
		}
	}

	constructor(options: CounterOptions)
	{
		this.options = Type.isPlainObject(options) ? options : {};

		this.#useAirDesign = this.options.useAirDesign === true;
		this.#node = options.node ?? null;
		this.container = null;
		this.counterContainer = null;
		this.animate = Type.isBoolean(this.options.animate) ? this.options.animate : false;
		this.isDouble = Type.isBoolean(this.options.isDouble) ? this.options.isDouble : false;
		this.value = Type.isNumber(this.options.value) ? this.options.value : 0;
		this.maxValue = Type.isNumber(this.options.maxValue) ? this.options.maxValue : 99;
		this.size = Type.isString(this.options.size) ? this.options.size : BX.UI.Counter.Size.MEDIUM;
		this.color = Type.isString(this.options.color) ? this.options.color : BX.UI.Counter.Color.PRIMARY;
		this.secondaryColor = Type.isString(this.options.secondaryColor)
			? this.options.secondaryColor
			: BX.UI.Counter.Color.PRIMARY
		;
		this.border = Type.isBoolean(this.options.border) ? this.options.border : false;
		this.#usePercentSymbol = this.options?.usePercentSymbol === true;
		this.#style = this.options.style ?? CounterStyle.FILLED;
		this.#hideIfZero = this.options.hideIfZero === true;
		this.#id = this.options.id;
	}

	// region Parameters
	setValue(value: number): this
	{
		this.#setPositiveValue(value);

		Dom.attr(this.getContainer(), 'data-value', value);
		Dom.removeClass(this.getContainer(), '--one-digit');

		if (this.value < 10 && this.#usePercentSymbol === false)
		{
			Dom.addClass(this.getContainer(), '--one-digit');
		}

		return this;
	}

	getValue(): number
	{
		if (this.#usePercentSymbol)
		{
			return this.value;
		}

		if (this.value <= this.maxValue)
		{
			return this.value;
		}

		return `${this.maxValue}+`;
	}

	getRealValue(): number
	{
		return this.value;
	}

	setMaxValue(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.maxValue = (value < 0) ? 0 : value;
		}

		return this;
	}

	getMaxValue(): number
	{
		return this.maxValue;
	}

	getId(): string
	{
		return this.#id;
	}

	isBorder(): boolean
	{
		return this.border;
	}

	setAirDesign(flag: boolean = true): this
	{
		this.#useAirDesign = flag === true;

		if (!this.container)
		{
			return;
		}

		if (this.#useAirDesign)
		{
			Dom.addClass(this.container, '--air');
		}
		else
		{
			Dom.removeClass(this.container, '--air');
		}
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

	setStyle(style: CounterStyle): this
	{
		if (this.container && this.#useAirDesign)
		{
			Dom.removeClass(this.container, this.#style);
			Dom.addClass(this.container, style);
		}

		this.#style = style;
	}

	setSize(size: CounterSize): this
	{
		if (Type.isStringFilled(size))
		{
			Dom.removeClass(this.container, this.size);
			this.size = size;
			Dom.addClass(this.container, this.size);
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

	createSecondaryContainer()
	{
		if (this.isDouble)
		{
			this.secondaryContainer = Tag.render`
				<div class="ui-counter-secondary"></div>
			`;
		}
		Dom.append(this.secondaryContainer, this.container);
	}

	setSecondaryColor()
	{
		if (this.secondaryContainer === null)
		{
			this.createSecondaryContainer();
		}
		Dom.removeClass(this.secondaryContainer, this.secondaryColor);
		Dom.addClass(this.secondaryContainer, this.secondaryColor);
	}

	setBorder(border: boolean): this
	{
		if (!Type.isBoolean(border))
		{
			console.warn('Parameter "border" is not boolean');

			return this;
		}

		this.border = border;
		const borderedCounterClassname = this.#getBorderClassname(true);

		if (border)
		{
			Dom.addClass(this.container, borderedCounterClassname);
		}
		else
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

		return '';
	}

	// endregion

	// region Counter
	update(value)
	{
		if (this.container === null)
		{
			this.createContainer(this.#node);
		}

		if (Boolean(this.animate) === true && this.#useAirDesign === false)
		{
			this.updateAnimated(value);
		}
		else if (Boolean(this.animate) === false)
		{
			this.setValue(value);

			if (this.#useAirDesign)
			{
				const oldCounterContainer = this.counterContainer;
				this.counterContainer = null;
				this.counterContainer = this.getCounterContainer();
				Dom.replace(oldCounterContainer, this.counterContainer);
			}
			else
			{
				const percentSymbol = this.#usePercentSymbol ? '%' : '';

				Dom.adjust(this.counterContainer, {
					text: `${this.getValue()}${percentSymbol}`,
				});
			}
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
			Dom.addClass(this.counterContainer, 'ui-counter-plus');
		}
		else if (value < this.value && this.value < this.maxValue)
		{
			Dom.addClass(this.counterContainer, 'ui-counter-minus');
		}

		setTimeout(() => {
			this.setValue(value);
			Dom.adjust(this.counterContainer, {
				text: this.getValue(),
			});
		}, 250);

		setTimeout(() => {
			Dom.removeClass(this.counterContainer, 'ui-counter-plus');
			Dom.removeClass(this.counterContainer, 'ui-counter-minus');
		}, 500);
	}

	show()
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		Dom.addClass(this.container, 'ui-counter-show');
		Dom.removeClass(this.container, 'ui-counter-hide');
	}

	hide()
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		Dom.addClass(this.container, 'ui-counter-hide');
		Dom.removeClass(this.container, 'ui-counter-show');
	}

	getCounterContainer(): HTMLElement
	{
		if (this.counterContainer === null && this.#useAirDesign)
		{
			this.counterContainer = this.#createAirCounterContainer();
		}
		else if (this.counterContainer === null)
		{
			const percentSymbol = this.#usePercentSymbol ? '%' : '';

			this.counterContainer = Tag.render`
				<div class="ui-counter-inner">${this.getValue()}${percentSymbol}</div>
			`;
		}

		return this.counterContainer;
	}

	#createAirCounterContainer(): HTMLElement
	{
		let symbol = '';
		let value = this.value;

		if (this.#usePercentSymbol)
		{
			symbol = '%';
		}
		else if (this.value > this.maxValue)
		{
			value = this.value > this.maxValue ? this.maxValue : this.value;
			symbol = '+';
		}

		const valueContainer = Tag.render`<span class="ui-counter__value">${value}</span>`;
		const symbolContainer = Tag.render`<span class="ui-counter__symbol">${symbol}</span>`;

		return Tag.render`
			<div class="ui-counter-inner">
				${valueContainer}
				${symbolContainer}
			</div>
		`;
	}

	// node params used only for vue3 component
	createContainer(node: HTMLElement = null): HTMLElement
	{
		if (this.container === null)
		{
			if (node)
			{
				this.container = node;
				this.container.className = 'ui-counter ui-counter__scope';
				Dom.clean(this.container);
				Dom.append(this.getCounterContainer(), this.container);
			}
			else
			{
				this.container = Tag.render`
					<div class="ui-counter ui-counter__scope">${this.getCounterContainer()}</div>
				`;
			}

			if (this.#hideIfZero)
			{
				Dom.addClass(this.container, '--hide-zero');
			}

			if (this.#id)
			{
				Dom.attr(this.container, 'id', this.#id);
			}

			this.setAirDesign(this.#useAirDesign);
			this.setSize(this.size);
			this.setColor(this.color);
			this.setStyle(this.#style);
			this.setBorder(this.border);
			this.setValue(this.value);
			this.createSecondaryContainer();
			this.setSecondaryColor();
		}

		return this.container;
	}

	// endregion

	getContainer(): Element
	{
		if (this.container === null)
		{
			this.createContainer(this.#node);
		}

		return this.container;
	}

	renderTo(node: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(node))
		{
			Dom.append(this.getContainer(), node);

			return this.getContainer();
		}

		return null;
	}

	/** @deprecated used only for vue3 component */
	renderOnNode(node: HTMLElement): void
	{
		this.createContainer(node);
	}

	render(): HTMLElement
	{
		return this.getContainer();
	}

	destroy(): void
	{
		Dom.remove(this.container);
		this.container = null;
		this.secondaryContainer = null;
		this.finished = false;
		this.textAfterContainer = null;
		this.textBeforeContainer = null;
		this.bar = null;
		this.svg = null;

		Object.keys(this).forEach((property) => {
			if (Object.prototype.hasOwnProperty.call(this, property))
			{
				delete this[property];
			}
		});

		Object.setPrototypeOf(this, null);
	}

	#setPositiveValue(value: number): void
	{
		if (Type.isNumber(value))
		{
			this.value = (value < 0) ? 0 : value;
		}
	}
}
