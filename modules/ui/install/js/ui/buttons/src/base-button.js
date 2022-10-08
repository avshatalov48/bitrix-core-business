import type IButton from './ibutton';
import ButtonTag from './button/button-tag';
import { Type, Tag, Dom, Event } from 'main.core';
import { type BaseButtonOptions } from './base-button-options';
import './ui.buttons.css';

export default class BaseButton implements IButton
{
	constructor(options: BaseButtonOptions)
	{
		options = Type.isPlainObject(options) ? options : {};
		this.options = Object.assign(this.getDefaultOptions(), options);

		/**
		 * 'buttonNode', 'textNode' and counterNode options use only in ButtonManager.createFromNode
		 */
		this.button = Type.isDomNode(this.options.buttonNode) ? this.options.buttonNode : null;
		this.textNode = Type.isDomNode(this.options.textNode) ? this.options.textNode : null;
		this.counterNode = Type.isDomNode(this.options.counterNode) ? this.options.counterNode : null;

		this.text = '';
		this.counter = null;
		this.events = {};
		this.link = '';
		this.maxWidth = null;

		this.tag = this.isEnumValue(this.options.tag, ButtonTag) ? this.options.tag : ButtonTag.BUTTON;
		if (Type.isStringFilled(this.options.link))
		{
			this.tag = ButtonTag.LINK;
		}

		this.baseClass = Type.isStringFilled(this.options.baseClass) ? this.options.baseClass : '';
		this.disabled = false;

		this.handleEvent = this.handleEvent.bind(this);

		this.init(); // needs to initialize private properties in derived classes.

		if (this.options.disabled === true)
		{
			this.setDisabled();
		}

		this.setText(this.options.text);
		this.setCounter(this.options.counter);
		this.setProps(this.options.props);
		this.setDataSet(this.options.dataset);
		this.addClass(this.options.className);
		this.setLink(this.options.link);
		this.setMaxWidth(this.options.maxWidth);

		this.bindEvent('click', this.options.onclick);
		this.bindEvents(this.options.events);
	}

	/**
	 * @protected
	 */
	init(): void
	{
		// needs to initialize private properties in derived classes.
	}

	/**
	 * @protected
	 */
	getDefaultOptions(): Object
	{
		return {};
	}

	/**
	 * @public
	 * @return {HTMLElement}
	 */
	render(): HTMLElement
	{
		return this.getContainer();
	}

	/**
	 * @public
	 * @param {HTMLElement} node
	 * @return {?HTMLElement}
	 */
	renderTo(node: HTMLElement): HTMLElement | null
	{
		if (Type.isDomNode(node))
		{
			return node.appendChild(this.getContainer());
		}

		return null;
	}

	/**
	 * @public
	 * @return {HTMLElement}
	 */
	getContainer(): HTMLElement
	{
		if (this.button !== null)
		{
			return this.button;
		}

		switch (this.getTag())
		{
			case ButtonTag.BUTTON:
			default:
				this.button = Tag.render`<button class="${this.getBaseClass()}"></button>`;
				break;
			case ButtonTag.INPUT:
				this.button = Tag.render`<input class="${this.getBaseClass()}" type="button">`;
				break;
			case ButtonTag.LINK:
				this.button = Tag.render`<a class="${this.getBaseClass()}" href=""></a>`;
				break;
			case ButtonTag.SUBMIT:
				this.button = Tag.render`<input class="${this.getBaseClass()}" type="submit">`;
				break;
			case ButtonTag.DIV:
				this.button = Tag.render`<div class="${this.getBaseClass()}"></div>`;
				break;
			case ButtonTag.SPAN:
				this.button = Tag.render`<span class="${this.getBaseClass()}"></span>`;
				break;
		}

		return this.button;
	}

	/**
	 * @protected
	 * @return {string}
	 */
	getBaseClass(): string
	{
		return this.baseClass;
	}

	/**
	 * @public
	 * @param {string} text
	 * @return {this}
	 */
	setText(text: string): this
	{
		if (Type.isString(text))
		{
			this.text = text;

			if (this.isInputType())
			{
				this.getContainer().value = text;
			}
			else if (text.length > 0)
			{
				if (this.textNode === null)
				{
					this.textNode = Tag.render`<span class="ui-btn-text"></span>`;
				}

				if (!this.textNode.parentNode)
				{
					Dom.prepend(this.textNode, this.getContainer());
				}

				this.textNode.textContent = text;
			}
			else
			{
				if (this.textNode !== null)
				{
					Dom.remove(this.textNode);
				}
			}
		}

		return this;
	}

	/**
	 * @public
	 * @return {string}
	 */
	getText(): string
	{
		return this.text;
	}

	/**
	 *
	 * @param {number | string} counter
	 * @return {this}
	 */
	setCounter(counter: number | string): this
	{
		if ([0, '0', '', null, false].includes(counter))
		{
			if (this.counterNode !== null)
			{
				Dom.remove(this.counterNode);
				this.counterNode = null;
			}

			this.counter = null;
		}
		else if ((Type.isNumber(counter) && counter > 0) || Type.isStringFilled(counter))
		{
			if (this.isInputType())
			{
				throw new Error('BX.UI.Button: an input button cannot have a counter.');
			}

			if (this.counterNode === null)
			{
				this.counterNode = Tag.render`<span class="ui-btn-counter"></span>`;
				Dom.append(this.counterNode, this.getContainer());
			}

			this.counter = counter;
			this.counterNode.textContent = counter;
		}

		return this;
	}

	/**
	 *
	 * @return {number | string | null}
	 */
	getCounter(): number | string | null
	{
		return this.counter;
	}

	/**
	 *
	 * @param {string} link
	 * @return {this}
	 */
	setLink(link: string): this
	{
		if (Type.isString(link))
		{
			if (this.getTag() !== ButtonTag.LINK)
			{
				throw new Error('BX.UI.Button: only an anchor button tag supports a link.');
			}

			this.getContainer().href = link;
		}

		return this;
	}

	/**
	 *
	 * @return {string}
	 */
	getLink(): string
	{
		return this.getContainer().href;
	}

	setMaxWidth(maxWidth: number): this
	{
		if (Type.isNumber(maxWidth) && maxWidth > 0)
		{
			this.maxWidth = maxWidth;
			this.getContainer().style.maxWidth = `${maxWidth}px`;
		}
		else if (maxWidth === null)
		{
			this.getContainer().style.removeProperty('max-width');
			this.maxWidth = null;
		}

		return this;
	}

	getMaxWidth(): number | null
	{
		return this.maxWidth;
	}

	/**
	 * @public
	 * @return {ButtonTag}
	 */
	getTag(): ButtonTag
	{
		return this.tag;
	}

	/**
	 * @public
	 * @param {object.<string, string>} props
	 * @return {this}
	 */
	setProps(props: { [propertyName: string]: string }): this
	{
		if (!Type.isPlainObject(props))
		{
			return this;
		}

		for (let propName in props)
		{
			const propValue = props[propName];
			Dom.attr(this.getContainer(), propName, propValue);
		}

		return this;
	}

	/**
	 * @public
	 * @return {object.<string, string>}
	 */
	getProps(): { [propertyName: string]: string }
	{
		const attrs = this.getContainer().attributes;
		const result = {};
		const reserved = this.isInputType() ? ['class', 'type'] : ['class'];

		for (let i = 0; i < attrs.length; i++)
		{
			const { name, value } = attrs[i];
			if (reserved.includes(name) || name.startsWith('data-'))
			{
				continue;
			}

			result[name] = value;
		}

		return result;
	}

	/**
	 * @public
	 * @param {object.<string, string>} props
	 * @return {this}
	 */
	setDataSet(props: { [propertyName: string]: string }): this
	{
		if (!Type.isPlainObject(props))
		{
			return this;
		}

		for (let propName in props)
		{
			const propValue = props[propName];
			if (propValue === null)
			{
				delete this.getDataSet()[propName];
			}
			else
			{
				this.getDataSet()[propName] = propValue;
			}
		}

		return this;
	}

	/**
	 * @public
	 * @return {DOMStringMap}
	 */
	getDataSet(): DOMStringMap
	{
		return this.getContainer().dataset;
	}

	/**
	 * @public
	 * @param {string} className
	 * @return {this}
	 */
	addClass(className: string): this
	{
		if (Type.isStringFilled(className))
		{
			Dom.addClass(this.getContainer(), className);
		}

		return this;
	}

	/**
	 * @public
	 * @param {string} className
	 * @return {this}
	 */
	removeClass(className: string): this
	{
		if (Type.isStringFilled(className))
		{
			Dom.removeClass(this.getContainer(), className);
		}

		return this;
	}

	/**
	 * @public
	 * @param {boolean} [flag=true]
	 * @return {this}
	 */
	setDisabled(flag?: boolean): this
	{
		if (flag === false)
		{
			this.disabled = false;
			this.setProps({ disabled: null });
		}
		else
		{
			this.disabled = true;
			this.setProps({ disabled: true });
		}

		return this;
	}

	/**
	 *
	 * @return {boolean}
	 */
	isDisabled(): boolean
	{
		return this.disabled;
	}

	/**
	 * @public
	 * @return {boolean}
	 */
	isInputType(): boolean
	{
		return this.getTag() === ButtonTag.SUBMIT || this.getTag() === ButtonTag.INPUT;
	}

	/**
	 * @public
	 * @param {object.<string, function>} events
	 * @return {this}
	 */
	bindEvents(events: { [event: string]: (button: this, event: MouseEvent) => {} }): this
	{
		if (Type.isPlainObject(events))
		{
			for (let eventName in events)
			{
				const fn = events[eventName];
				this.bindEvent(eventName, fn);
			}
		}

		return this;
	}

	/**
	 * @public
	 * @param {string[]} events
	 * @return {this}
	 */
	unbindEvents(events: string[]): this
	{
		if (Type.isArray(events))
		{
			events.forEach(eventName => {
				this.unbindEvent(eventName);
			});
		}

		return this;
	}

	/**
	 * @public
	 * @param {string} eventName
	 * @param {function} fn
	 * @return {this}
	 */
	bindEvent(eventName: string, fn: (button: this, event: MouseEvent) => {}): this
	{
		if (Type.isStringFilled(eventName) && Type.isFunction(fn))
		{
			this.unbindEvent(eventName);
			this.events[eventName] = fn;
			Event.bind(this.getContainer(), eventName, this.handleEvent);
		}

		return this;
	}

	/**
	 * @public
	 * @param {string} eventName
	 * @return {this}
	 */
	unbindEvent(eventName: string): this
	{
		if (this.events[eventName])
		{
			delete this.events[eventName];
			Event.unbind(this.getContainer(), eventName, this.handleEvent);
		}

		return this;
	}

	/**
	 * @private
	 * @param {MouseEvent} event
	 */
	handleEvent(event)
	{
		const eventName = event.type;
		if (this.events[eventName])
		{
			const fn = this.events[eventName];
			fn.call(this, this, event);
		}
	}

	/**
	 * @protected
	 */
	isEnumValue(value, enumeration): boolean
	{
		for (let code in enumeration)
		{
			if (enumeration[code] === value)
			{
				return true;
			}
		}

		return false;
	}
}