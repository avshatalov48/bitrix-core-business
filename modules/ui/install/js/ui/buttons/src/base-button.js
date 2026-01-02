import { Type, Tag, Dom, Event } from 'main.core';
import 'ui.design-tokens.air';

import { ButtonCounter, type ButtonCounterOptions } from './button-counter';
import { getCounterSize } from './helpers/counter-size';
import { ButtonCounterSize } from './index';
import ButtonTag from './button/button-tag';
import type { BaseButtonOptions } from './base-button-options';
import type IButton from './ibutton';

import './ui.buttons.css';
import './ui.air-buttons.css';

export type SetCounterOptions = {
	counter: ButtonCounterOptions,
	position: 'left' | 'right',
};

export default class BaseButton implements IButton
{
	#useAirDesign: boolean = false;
	#leftCounter: ?ButtonCounter;
	#rightCounter: ?ButtonCounter;
	#leftCounterContainer: ?HTMLElement;
	#rightCounterContainer: ?HTMLElement;

	constructor(options: BaseButtonOptions)
	{
		this.options = Object.assign(this.getDefaultOptions(), Type.isPlainObject(options) ? options : {});

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

		this.init(); // needs to initialize private properties in derived classes.

		if (this.options.disabled === true)
		{
			this.setDisabled();
		}

		this.setAirDesign(this.options.useAirDesign === true);
		this.setText(this.options.text);
		this.setCounter(this.options.counter);
		this.setProps(this.options.props);
		this.setDataSet(this.options.dataset);
		this.addClass(this.options.className);
		this.setLink(this.options.link);
		this.setMaxWidth(this.options.maxWidth);

		if (this.hasAirDesign())
		{
			if (this.options.leftCounter)
			{
				this.setLeftCounter({
					...this.options.leftCounter,
					size: getCounterSize(this.options.size),
				});
			}

			if (this.options.rightCounter)
			{
				this.setRightCounter({
					...this.options.rightCounter,
					size: getCounterSize(this.options.size),
				});
			}
		}

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

	setAirDesign(use: boolean): void
	{
		this.#useAirDesign = use === true;

		Dom.toggleClass(this.getContainer(), '--air', this.#useAirDesign);
	}

	hasAirDesign(): boolean
	{
		return this.#useAirDesign;
	}

	/**
	 * @protected
	 */
	getDefaultOptions(): Object
	{
		return {};
	}

	render(): HTMLElement
	{
		return this.getContainer();
	}

	renderTo(node: HTMLElement): HTMLElement
	{
		Dom.append(this.getContainer(), node);

		return this.getContainer();
	}

	getContainer(): HTMLElement
	{
		this.button ??= {
			[ButtonTag.LINK]: () => Tag.render`<a class="${this.getBaseClass()}" href=""></a>`,
			[ButtonTag.INPUT]: () => Tag.render`<input class="${this.getBaseClass()}" type="button">`,
			[ButtonTag.SUBMIT]: () => Tag.render`<input class="${this.getBaseClass()}" type="submit">`,
			[ButtonTag.DIV]: () => Tag.render`<div class="${this.getBaseClass()}"></div>`,
		}[this.getTag()]?.() ?? Tag.render`<button class="${this.getBaseClass()}"></button>`;

		return this.button;
	}

	/**
	 * @protected
	 */
	getBaseClass(): string
	{
		return this.baseClass;
	}

	setText(text: string): this
	{
		if (!Type.isString(text) && !this.hasAirDesign())
		{
			return this;
		}

		this.text = text || '';

		if (this.isInputType())
		{
			this.getContainer().value = this.text;
		}
		else if (this.text.length > 0 || this.hasAirDesign())
		{
			if (this.textNode === null)
			{
				this.textNode = Tag.render`<span class="ui-btn-text"><span class="ui-btn-text-inner"></span></span>`;
			}

			if (!this.textNode.parentNode)
			{
				Dom.prepend(this.textNode, this.getContainer());
			}

			const textContentNode = this.textNode.querySelector('.ui-btn-text-inner') ?? this.textNode;
			textContentNode.textContent = text;
		}
		else if (this.textNode !== null)
		{
			Dom.remove(this.textNode);
		}

		return this;
	}

	getTextContainer(): HTMLElement
	{
		return this.textNode;
	}

	getText(): string
	{
		return this.text;
	}

	/**
	 * Use for buttons with air option
	 * Use only to create or delete a counter. Update counter value via getLeftCounter() method.
	 *
	 * @param options Object | null Object for creating. null for deleting.
	 */
	setLeftCounter(options: ButtonCounterOptions | null): this
	{
		if (this.hasAirDesign() === false)
		{
			console.warn('Left counter works only with air buttons. Use setLeftCounter or useAirDesign option in constructor.');

			return this;
		}

		if (!options)
		{
			this.#removeLeftCounter();

			return this;
		}

		if (this.#leftCounter)
		{
			return this;
		}

		this.#removeLeftCounter();
		this.#leftCounter = new ButtonCounter({
			...options,
			size: Type.isString(options.size) ? options.size : ButtonCounterSize.MEDIUM,
		});

		if (this.textNode)
		{
			this.#leftCounterContainer = Tag.render`
				<div class="ui-btn-left-counter">
					${this.#leftCounter.render()}
				</div>
			`;

			Dom.prepend(this.#leftCounterContainer, this.textNode);
			Dom.addClass(this.getContainer(), '--with-left-counter');
		}

		return this;
	}

	/**
	 * Use for buttons with air option
	 * Use only to create or delete a counter. Update counter value via getRightCounter() method.
	 *
	 * @param options Object | null Object for creating. null for deleting.
	 */
	setRightCounter(options: ButtonCounterOptions | null): this
	{
		if (this.hasAirDesign() === false)
		{
			console.warn('Right counter works only with air buttons. Use setRightCounter or useAirDesign option in constructor.');

			return this;
		}

		if (!options)
		{
			this.#removeRightCounter();

			return this;
		}

		this.#removeRightCounter();

		this.#rightCounter = new ButtonCounter({
			...options,
			size: Type.isString(options.size) ? options.size : ButtonCounterSize.MEDIUM,
		});

		if (this.textNode)
		{
			this.#rightCounterContainer = Tag.render`
				<div class="ui-btn-right-counter">${this.#rightCounter.render()}</div>
			`;

			Dom.append(this.#rightCounterContainer, this.textNode);
			Dom.addClass(this.getContainer(), '--with-right-counter');
		}

		return this;
	}

	getLeftCounter(): ButtonCounter
	{
		return this.#leftCounter;
	}

	getRightCounter(): ButtonCounter
	{
		return this.#rightCounter;
	}

	#removeLeftCounter(): void
	{
		Dom.remove(this.#leftCounterContainer);
		Dom.removeClass(this.getContainer(), '--with-left-counter');
		this.#leftCounterContainer = null;
		this.#leftCounter = null;
	}

	#removeRightCounter(): void
	{
		Dom.remove(this.#rightCounterContainer);
		Dom.removeClass(this.getContainer(), '--with-right-counter');
		this.#rightCounterContainer = null;
		this.#rightCounter = null;
	}

	/**
	 * use for old buttons (without useAirTheme option)
	 */
	setCounter(counter: number | string): this
	{
		if ([0, '0', '', null, false].includes(counter))
		{
			Dom.remove(this.counterNode);
			this.counterNode = null;
			this.counter = null;
		}
		else if ((Type.isNumber(counter) && counter > 0) || Type.isStringFilled(counter))
		{
			if (this.hasAirDesign())
			{
				console.warn('Use setCounter or counter option only for not air buttons. For fir buttons use setLeftCounter or setRightCounter methods or leftCounter or rightCounter options.');

				return this;
			}

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

	getCounter(): number | string | null
	{
		return this.counter;
	}

	setLink(link: string): this
	{
		if (Type.isStringFilled(link))
		{
			if (this.getTag() !== ButtonTag.LINK)
			{
				throw new Error('BX.UI.Button: only an anchor button tag supports a link.');
			}

			this.getContainer().href = link;
		}

		return this;
	}

	getLink(): string
	{
		return this.getContainer().href;
	}

	setMaxWidth(maxWidth: number): this
	{
		this.maxWidth = maxWidth > 0 ? maxWidth : null;
		Dom.style(this.getContainer(), 'max-width', maxWidth > 0 ? `${maxWidth}px` : null);

		return this;
	}

	getMaxWidth(): number | null
	{
		return this.maxWidth;
	}

	getTag(): ButtonTag
	{
		return this.tag;
	}

	setProps(props: { [propertyName: string]: string }): this
	{
		if (Type.isPlainObject(props))
		{
			Dom.attr(this.getContainer(), props);
		}

		return this;
	}

	getProps(): { [property: string]: string }
	{
		const reserved = this.isInputType() ? ['class', 'type'] : ['class'];

		return [...this.getContainer().attributes]
			.filter(({ name }) => !reserved.includes(name) && !name.startsWith('data-'))
			.reduce((props, { name, value }) => ({
				...props,
				[name]: value,
			}), {})
		;
	}

	setDataSet(props: { [propertyName: string]: string }): this
	{
		if (!Type.isPlainObject(props))
		{
			return this;
		}

		Object.entries(props).forEach(([property, value]) => {
			this.getDataSet()[property] = value;
			if (value === null)
			{
				delete this.getDataSet()[property];
			}
		});

		return this;
	}

	getDataSet(): DOMStringMap
	{
		return this.getContainer().dataset;
	}

	addClass(className: string): this
	{
		Dom.addClass(this.getContainer(), className);

		return this;
	}

	removeClass(className: string): this
	{
		Dom.removeClass(this.getContainer(), className);

		return this;
	}

	setDisabled(disabled: boolean = true): this
	{
		this.disabled = disabled;
		this.setProps({ disabled: disabled ? true : null });

		return this;
	}

	isDisabled(): boolean
	{
		return this.disabled;
	}

	isInputType(): boolean
	{
		return [ButtonTag.SUBMIT, ButtonTag.INPUT].includes(this.getTag());
	}

	bindEvents(events: { [event: string]: (button: this, event: MouseEvent) => {} }): this
	{
		if (Type.isPlainObject(events))
		{
			Object.entries(events).forEach(([name, handler]) => this.bindEvent(name, handler));
		}

		return this;
	}

	unbindEvents(events: string[]): this
	{
		if (Type.isArray(events))
		{
			events.forEach((eventName) => this.unbindEvent(eventName));
		}

		return this;
	}

	bindEvent(eventName: string, fn: (button: this, event: MouseEvent) => {}): this
	{
		if (Type.isStringFilled(eventName) && Type.isFunction(fn))
		{
			this.unbindEvent(eventName);
			this.events[eventName] = fn;
			Event.bind(this.getContainer(), eventName, this.#handleEvent);
		}

		return this;
	}

	unbindEvent(eventName: string): this
	{
		if (this.events[eventName])
		{
			delete this.events[eventName];
			Event.unbind(this.getContainer(), eventName, this.#handleEvent);
		}

		return this;
	}

	#handleEvent = (event: MouseEvent): void => {
		this.events[event.type]?.call(this, this, event);
	};

	/**
	 * @protected
	 */
	isEnumValue(value: any, enumeration: Object): boolean
	{
		return Object.values(enumeration).includes(value);
	}
}
