// @flow

import {Dom, Tag, Type} from 'main.core';
import ProgressBarColor from './progressbar-color';
import ProgressBarSize from './progressbar-size';
import ProgressBarStatus from './progressbar-status';

type ProgressBarOptions = {
	value: number;
	maxValue: number;
	color: ProgressBarColor;
	size: ProgressBarSize | number;
	width: number;
	textBefore: string;
	textAfter: string;
	colorTrack: string;
	colorBar: string;
	statusType: string;
	lineSize: number;
	fill: boolean;
	finished: boolean;
	rotation: boolean;
};

export class ProgressBar {
	static Color = ProgressBarColor;
	static Size = ProgressBarSize;
	static Status = ProgressBarStatus;

	constructor(options = ProgressBarOptions)
	{
		this.options = Type.isPlainObject(options) ? options : {};

		this.value = Type.isNumber(this.options.value) ? this.options.value : 0;
		this.maxValue = Type.isNumber(this.options.maxValue) ? this.options.maxValue : 100;
		this.bar = null;
		this.container = null;
		this.status = null;
		this.finished = false;
		this.fill = Type.isBoolean(this.options.fill) ? this.options.fill : false;
		this.column = Type.isBoolean(this.options.column) ? this.options.column : false;
		this.statusPercent = "0%";
		this.statusCounter = "0 / 0";
		this.textBefore = Type.isString(this.options.textBefore) ? this.options.textBefore : null;
		this.textBeforeContainer = null;
		this.textAfter = Type.isString(this.options.textAfter) ? this.options.textAfter : null;
		this.textAfterContainer = null;
		this.statusType = Type.isString(this.options.statusType) ? this.options.statusType : BX.UI.ProgressBar.Status.NONE;
		this.size = (Type.isStringFilled(this.options.size) || Type.isNumber(this.options.size)) ? this.options.size : BX.UI.ProgressBar.Size.MEDIUM;
		this.colorTrack = Type.isString(this.options.colorTrack) ? this.options.colorTrack : null;
		this.colorBar = Type.isString(this.options.colorBar) ? this.options.colorBar : null;
		this.color = Type.isString(this.options.color) ? this.options.color : BX.UI.ProgressBar.Color.PRIMARY;

		// this.setStatusType(options.statusType);
		// this.setColorTrack(options.colorTrack);
		// this.setColorBar(options.colorBar);
	}

	//region Parameters
	setValue(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.value = (value > this.maxValue) ? this.maxValue : value;
		}

		return this;
	}

	getValue(): number
	{
		return this.value;
	}

	setMaxValue(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.maxValue = value;
		}

		return this;
	}

	getMaxValue(): number
	{
		return this.maxValue;
	}

	finish()
	{
		this.update(this.maxValue)
	}

	isFinish(): boolean
	{
		return this.finished;
	}

	setColor(color: ProgressBarColor): this
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

	setColorBar(color: string): this
	{
		if (Type.isStringFilled(color))
		{
			this.colorBar = color;
			color = "--ui-current-bar-color:" + color + ";"
			this.#setCustomColors(color)
		}

		return this;
	}

	setColorTrack(color: string): this
	{
		if (Type.isStringFilled(color))
		{
			this.colorTrack = color;
			this.setFill(true);
			color = "--ui-current-bar-bg-track-color:" + color + ";"
			this.#setCustomColors(color)
		}

		return this;
	}

	#setCustomColors(value)
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		this.setFill(false)
		this.setColor(BX.UI.ProgressBar.Color.NONE)

		let currentAttribute = this.container.getAttribute('style'),
			customColorsValue = (!currentAttribute) ? value : currentAttribute + value;
		this.container.setAttribute('style', customColorsValue)
	}

	setSize(size: ProgressBarSize | number): this
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (Type.isStringFilled(size))
		{
			Dom.removeClass(this.container, this.size);
			this.size = size;
			Dom.addClass(this.container, this.size);
		}
		else if (Type.isNumber(size))
		{
			this.container.setAttribute('style', "--ui-current-bar-size:" + size + "px;")
			this.size = size;
		}

		return this;
	}

	setFill(fill: boolean): this
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (fill)
		{
			Dom.addClass(this.container, "ui-progressbar-bg");
		}
		else
		{
			Dom.removeClass(this.container, "ui-progressbar-bg");
		}

		return this;
	}

	setColumn(column: boolean): this
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (column === true)
		{
			Dom.addClass(this.container, "ui-progressbar-column");
		}
		else
		{
			Dom.removeClass(this.container, "ui-progressbar-column");
		}

		return this;
	}

	//endregion

	//region Text
	setTextBefore(text: string): this
	{
		if (Type.isStringFilled(text))
		{
			this.textBefore = text;
			if (!this.textBeforeContainer)
			{
				this.createTextBefore(text);
			}
			else
			{
				Dom.adjust(this.textBeforeContainer, {
					html: text
				});
			}
		}
	}

	createTextBefore(text: string)
	{
		if ((!this.textBeforeContainer) && Type.isStringFilled(text))
		{
			this.textBeforeContainer = Tag.render`
				<div class="ui-progressbar-text-before">${text}</div>
			`;
		}
	}

	getTextBefore()
	{
		if (!this.textBeforeContainer)
		{
			this.createTextBefore(this.textBefore);
		}

		return this.textBeforeContainer;
	}

	setTextAfter(text: string): this
	{
		if (Type.isStringFilled(text))
		{
			this.textAfter = text;
			if (!this.textAfterContainer)
			{
				this.createTextAfter(text);
			}
			else
			{
				Dom.adjust(this.textAfterContainer, {
					html: text
				});
			}
		}
	}

	createTextAfter(text: string)
	{
		if ((!this.textAfterContainer) && Type.isStringFilled(text))
		{
			this.textAfterContainer = Tag.render`
				<div class="ui-progressbar-text-after">${text}</div>
			`;
		}
	}

	getTextAfter()
	{
		if (!this.textAfterContainer)
		{
			this.createTextAfter(this.textAfter);
		}

		return this.textAfterContainer;
	}

	//endregion

	// region Status
	setStatus()
	{
		if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER)
		{
			Dom.adjust(this.status, {
				text: this.getStatusCounter()
			});
		}
		else if (this.getStatusType() === BX.UI.ProgressBar.Status.PERCENT)
		{
			Dom.adjust(this.status.firstChild, {
				text: this.getStatusPercent(),
			});
		}
	}

	getStatus()
	{
		if (!this.status)
		{
			if (this.getStatusType() === BX.UI.ProgressBar.Status.COUNTER)
			{
				this.status = Tag.render`
					<div class="ui-progressbar-status">${this.getStatusCounter()}</div>
				`;
			}
			else if (this.getStatusType() === BX.UI.ProgressBar.Status.PERCENT)
			{
				this.status = Tag.render`
					<div class="ui-progressbar-status-percent">
						<span class="ui-progressbar-status-percent-value">${this.getStatusPercent()}</span>
						<span class="ui-progressbar-status-percent-sign">%</span>
					</div>
				`;
			}
			else
			{
				this.status = Dom.create("span", {});
			}
		}

		return this.status;
	}

	getStatusPercent()
	{
		if (this.maxValue === 0)
		{
			return "0%"
		}

		this.statusPercent = Math.round(this.getValue() / (this.getMaxValue() / 100));
		if (this.statusPercent > 100)
		{
			this.statusPercent = 100;
		}

		return this.statusPercent;
	}

	getStatusCounter()
	{
		if (Math.round(this.getValue()) > Math.round(this.getMaxValue()))
		{
			this.statusCounter = Math.round(this.getMaxValue()) + " / " + Math.round(this.getMaxValue());
		}
		else
		{
			this.statusCounter = Math.round(this.getValue()) + " / " + Math.round(this.getMaxValue());
		}

		return this.statusCounter;
	}

	getStatusType()
	{
		return this.statusType;
	}

	setStatusType(type: string)
	{
		if (Type.isStringFilled(type))
		{
			this.statusType = type;
		}
	}

	//endregion

	// region ProgressBar
	createContainer(): HTMLElement
	{
		if (this.container === null)
		{
			this.container = Dom.create("div", {
				props: {className: "ui-progressbar"},
				children: [
					this.getTextAfter(),
					this.getTextBefore(),
					this.getStatus(),
					BX.create("div", {
						props: {className: "ui-progressbar-track"},
						children: [
							this.getBar()
						]
					})
				]
			});

			this.setColor(this.color)
			this.setColumn(this.column);
			this.setSize(this.size);
			this.setFill(this.fill);
			this.setColorTrack(this.colorTrack);
			this.setColorBar(this.colorBar);
		}
	}

	getBar(): HTMLElement
	{
		if (this.bar === null)
		{
			this.bar = Dom.create("div", {
				props: {className: "ui-progressbar-bar"},
				style: {width: `${this.getStatusPercent()}%`}
			});
		}

		return this.bar;
	}

	update(value: number)
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		this.setValue(value);

		if (value >= this.maxValue)
		{
			setTimeout(function () {
				Dom.addClass(this.container, "ui-progressbar-finished");
			}.bind(this), 300);
			this.finished = true;
		}
		else
		{
			Dom.removeClass(this.container, "ui-progressbar-finished");
			this.finished = false;
		}

		this.setStatus();

		if (this.bar === null)
		{
			this.getBar();
		}

		Dom.adjust(this.bar, {
			style: {width: `${this.getStatusPercent()}%`}
		});
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