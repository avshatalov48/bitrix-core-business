// @flow

import {Dom, Tag, Type} from 'main.core';
import ProgressRoundColor from './progressround-color';
import ProgressRoundStatus from './progressround-status';

type ProgressRoundOptions = {
	value: number;
	maxValue: number;
	color: ProgressRoundColor;
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

export default class ProgressRound // extends BX.UI.ProgressRound
{
	static Color = ProgressRoundColor;
	static Status = ProgressRoundStatus;

	constructor(options: ProgressRoundOptions)
	{
		this.options = Type.isPlainObject(options) ? options : {};

		this.value = Type.isNumber(this.options.value) ? this.options.value : 0;
		this.maxValue = Type.isNumber(this.options.maxValue) ? this.options.maxValue : 100;
		this.bar = null;
		this.container = null;
		this.width = Type.isNumber(this.options.width) ? this.options.width : 100;
		this.lineSize = Type.isNumber(this.options.lineSize) ? this.options.lineSize : 5;
		this.status = null;
		this.statusType = Type.isString(this.options.statusType) ? this.options.statusType : BX.UI.ProgressRound.Status.NONE;
		this.statusPercent = "0%";
		this.statusCounter = "0 / 0";
		this.textBefore = Type.isString(this.options.textBefore) ? this.options.textBefore : null;
		this.textBeforeContainer = null;
		this.textAfter = Type.isString(this.options.textAfter) ? this.options.textAfter : null;
		this.textAfterContainer = null;
		this.fill = false;
		this.finished = false;
		this.rotation = Type.isBoolean(this.options.rotation) ? this.options.rotation : false;
		this.colorTrack = Type.isString(this.options.colorTrack) ? this.options.colorTrack : null;
		this.colorBar = Type.isString(this.options.colorBar) ? this.options.colorBar : null;
		this.color = Type.isString(this.options.color) ? this.options.color : BX.UI.ProgressRound.Color.PRIMARY;
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

	setWidth(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.width = value;
		}

		return this;
	}

	getWidth(): number
	{
		return this.width;
	}

	setLineSize(value: number): this
	{
		if (Type.isNumber(value))
		{
			this.lineSize = (value > (this.width / 2)) ? (this.width / 2) : value;
		}

		return this;
	}

	getLineSize(): number
	{
		return this.lineSize;
	}

	setColor(color: ProgressRoundColor): this
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
			color = "--ui-current-round-color:" + color + ";"
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
			color = "--ui-current-round-bg-track-color:" + color + ";"
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

		let currentAttribute = this.container.getAttribute('style'),
			customColorsValue = (!currentAttribute) ? value : currentAttribute + value;
		this.container.setAttribute('style', customColorsValue)
	}

	setFill(fill: boolean): this
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (Type.isBoolean(fill))
		{
			this.fill = fill;

			if (fill === true)
			{
				Dom.addClass(this.container, "ui-progressround-bg");
			}
			else
			{
				Dom.removeClass(this.container, "ui-progressround-bg");
			}
		}

		return this;
	}

	setRotation(rotation: boolean): this
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		if (Type.isBoolean(rotation))
		{
			this.rotation = rotation;

			if (rotation === true)
			{
				Dom.addClass(this.container, "ui-progressround-rotation");
			}
			else
			{
				Dom.removeClass(this.container, "ui-progressround-rotation");
			}
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
				<div class="ui-progressround-text-before">${text}</div>
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
				<div class="ui-progressround-text-after">${text}</div>
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
		if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER)
		{
			Dom.adjust(this.status, {
				text: this.getStatusCounter()
			});
		}
		else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT)
		{
			Dom.adjust(this.status, {
				text: this.getStatusPercent()
			});
		}
		else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE)
		{
			Dom.adjust(this.status, {
				text: this.getStatusPercent()
			});
		}
		else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER)
		{
			Dom.adjust(this.status, {
				text: this.getStatusCounter()
			});
		}
	}

	getStatus()
	{
		if (!this.status)
		{
			if (this.getStatusType() === BX.UI.ProgressRound.Status.COUNTER)
			{
				this.status = Tag.render`
					<div class="ui-progressround-status">${this.getStatusCounter()}</div>
				`;
			}
			else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLE)
			{
				this.status = Tag.render`
					<div class="ui-progressround-status-percent-incircle">${this.getStatusPercent()}</div>
				`;
			}
			else if (this.getStatusType() === BX.UI.ProgressRound.Status.INCIRCLECOUNTER)
			{
				this.status = Tag.render`
					<div class="ui-progressround-status-incircle">${this.getStatusCounter()}</div>
				`;
			}
			else if (this.getStatusType() === BX.UI.ProgressRound.Status.PERCENT)
			{
				this.status = Tag.render`
					<div class="ui-progressround-status-percent">${this.getStatusPercent()}</div>
				`;
			}
			else
			{
				this.status = Dom.create("span", {});
			}
		}

		return this.status;
	}

	getStatusPercent(): string | number
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

		return this.statusPercent + "%";
	}

	getStatusCounter(): string | number
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

	// region ProgressRound
	createContainer(): HTMLElement
	{
		if (this.container === null)
		{
			this.container = Dom.create("div", {
				props: {className: "ui-progressround"},
				children: [
					this.getTextAfter(),
					this.getTextBefore(),
					Dom.create("div", {
						props: {className: "ui-progressround-track"},
						children: [
							this.getStatus(),
							this.getBar()
						]
					})
				]
			});

			this.setStatusType(this.statusType);
			this.setColor(this.color);
			this.setRotation(this.rotation);
			this.setFill(this.fill);
			this.setColorTrack(this.colorTrack);
			this.setColorBar(this.colorBar);
		}
	}

	getCircleFerence()
	{
		return (this.width / 2 - this.lineSize / 2) * 2 * 3.14;
	}

	getCircleProgress()
	{
		return this.getCircleFerence() - (this.getCircleFerence() / this.maxValue * this.value);
	}

	getBar(): HTMLElement
	{
		var factRadius = this.width / 2 - (this.lineSize / 2);

		this.svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
		this.svg.setAttributeNS(null, 'class', 'ui-progressround-track-bar');
		this.svg.setAttributeNS(null, 'viewport', '0 0 ' + this.width + ' ' + this.width);
		this.svg.setAttributeNS(null, 'width', this.width);
		this.svg.setAttributeNS(null, 'height', this.width);

		this.progressBg = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
		this.progressBg.setAttributeNS(null, 'r', factRadius);
		this.progressBg.setAttributeNS(null, 'cx', (this.width / 2));
		this.progressBg.setAttributeNS(null, 'cy', (this.width / 2));
		this.progressBg.setAttributeNS(null, 'stroke-width', this.lineSize);
		this.progressBg.setAttributeNS(null, 'class', 'ui-progressround-track-bar-bg');

		this.svg.appendChild(this.progressBg);

		this.progressMove = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
		this.progressMove.setAttributeNS(null, 'r', factRadius);
		this.progressMove.setAttributeNS(null, 'cx', (this.width / 2));
		this.progressMove.setAttributeNS(null, 'cy', (this.width / 2));
		this.progressMove.setAttributeNS(null, 'stroke-width', this.lineSize);
		this.progressMove.setAttributeNS(null, 'stroke-dasharray', this.getCircleFerence());
		this.progressMove.setAttributeNS(null, 'stroke-dashoffset', this.getCircleFerence());
		this.progressMove.setAttributeNS(null, 'class', 'ui-progressround-track-bar-progress');

		this.svg.appendChild(this.progressMove);

		return this.svg;
	}

	animateProgressBar()
	{
		this.svg.setAttributeNS(null, 'class', 'task-report-circle-bar task-report-circle-bar-animate');

		var progressDashoffset = (this.maxValue === 0) ? this.getCircleFerence() : this.getCircleProgress();

		this.progressMove.setAttributeNS(null, 'stroke-dashoffset', progressDashoffset);
	}

	update(value)
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		this.setValue(value);

		if (value >= this.maxValue)
		{
			setTimeout(function () {
				Dom.addClass(this.container, "ui-progressround-finished");
			}.bind(this), 300);
			this.finished = true;
		}
		else
		{
			Dom.removeClass(this.container, "ui-progressround-finished");
			this.finished = false;
		}

		this.setStatus();

		if (this.svg === null)
		{
			this.getBar();
		}

		this.animateProgressBar();
	}

	//endregion

	getContainer(): Element
	{
		if (this.container === null)
		{
			this.createContainer();
		}

		this.animateProgressBar()

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
