import { Type, Dom } from 'main.core';

declare type ButtonOptions = {
	id?: string,
	text?: string,
	className?: string,
	events?: { [event: string]: (event) => {} }
};

/**
 * @memberOf BX.Main.Popup
 * @deprecated use BX.UI.Button
 */
export default class Button
{
	constructor(params: ButtonOptions)
	{
		this.popupWindow = null;

		this.params = params || {};

		this.text = this.params.text || '';
		this.id = this.params.id || '';
		this.className = this.params.className || '';
		this.events = this.params.events || {};

		this.contextEvents = {};
		for (let eventName in this.events)
		{
			if (Type.isFunction(this.events[eventName]))
			{
				this.contextEvents[eventName] = this.events[eventName].bind(this);
			}
		}

		this.buttonNode = Dom.create(
			'span',
			{
				props: {
					className: 'popup-window-button' + (this.className.length > 0 ? ' ' + this.className : ''),
					id: this.id
				},
				events: this.contextEvents,
				text: this.text
			}
		);
	}

	render(): Element
	{
		return this.buttonNode;
	}

	getId(): string
	{
		return this.id;
	}

	getContainer(): Element
	{
		return this.buttonNode;
	}

	getName(): string
	{
		return this.text;
	}

	setName(name: string)
	{
		this.text = name || '';
		if (this.buttonNode)
		{
			Dom.clean(this.buttonNode);
			Dom.adjust(this.buttonNode, { text: this.text });
		}
	}

	setClassName(className: string)
	{
		if (this.buttonNode)
		{
			if (Type.isString(this.className) && (this.className !== ''))
			{
				Dom.removeClass(this.buttonNode, this.className);
			}

			Dom.addClass(this.buttonNode, className);
		}

		this.className = className;
	}

	addClassName(className: string)
	{
		if (this.buttonNode)
		{
			Dom.addClass(this.buttonNode, className);
			this.className = this.buttonNode.className;
		}
	}

	removeClassName(className: string)
	{
		if (this.buttonNode)
		{
			Dom.removeClass(this.buttonNode, className);
			this.className = this.buttonNode.className;
		}
	}
}