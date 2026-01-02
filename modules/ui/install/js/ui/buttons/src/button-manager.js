import { Type, Reflection, Dom, Runtime, Tag } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { MenuItem } from 'main.popup';
import { Switcher } from 'ui.switcher';
import { Counter, CounterStyle } from 'ui.cnt';

import BaseButton from './base-button';
import Button from './button/button';
import SplitButton from './split-button/split-button';
import ButtonTag from './button/button-tag';
import ButtonColor from './button/button-color';
import ButtonSize from './button/button-size';
import ButtonState from './button/button-state';
import ButtonStyle from './button/button-style';
import ButtonIcon from './button/button-icon';
import SplitButtonState from './split-button/split-button-state';

import type { SplitButtonOptions } from './split-button/split-button-options';
import type { ButtonOptions } from './button/button-options';
import type { SplitSubButtonOptions } from './split-button/split-sub-button-options';
import SplitSubButtonType from './split-button/split-sub-button-type';
import { AirButtonStyle } from './index';

export default class ButtonManager
{
	/**
	 * @public
	 * @param {HTMLButtonElement | HTMLAnchorElement | HTMLInputElement} node
	 * @return {Button | SplitButton}
	 */
	static createFromNode(
		node: HTMLButtonElement | HTMLAnchorElement | HTMLInputElement | HTMLDivElement
	): Button | SplitButton
	{
		if (!Type.isDomNode(node))
		{
			throw new Error('BX.UI.ButtonManager.createFromNode: "node" must be a DOM node.');
		}

		if (!Dom.hasClass(node, Button.BASE_CLASS) && !Dom.hasClass(node, SplitButton.BASE_CLASS))
		{
			throw new Error('BX.UI.ButtonManager.createFromNode: "node" is not a button.');
		}

		const isSplitButton = Dom.hasClass(node, SplitButton.BASE_CLASS);
		let tag = null;
		let text = null;
		let textNode = null;
		let counterNode = null;
		let switcherNode = null;
		let disabled = false;
		let mainButtonOptions: SplitSubButtonOptions = {};
		let menuButtonOptions: SplitSubButtonOptions = {};

		if (isSplitButton)
		{
			const mainButton = node.querySelector(`.${SplitSubButtonType.MAIN}`);
			const menuButton = node.querySelector(`.${SplitSubButtonType.MENU}`);
			if (!mainButton)
			{
				throw new Error('BX.UI.ButtonManager.createFromNode: a split button doesn\'t have a main button.');
			}

			if (!menuButton)
			{
				throw new Error('BX.UI.ButtonManager.createFromNode: a split button doesn\'t have a menu button.');
			}

			const mainButtonTag = this.#getTag(mainButton);
			if (mainButtonTag === ButtonTag.INPUT || mainButtonTag === ButtonTag.SUBMIT)
			{
				text = mainButton.value;
			}
			else
			{
				[textNode, counterNode] = this.#getTextNode(mainButton);
				text = textNode.textContent;
			}

			disabled = Dom.hasClass(node, SplitButtonState.DISABLED);

			mainButtonOptions = {
				tag: mainButtonTag,
				textNode,
				counterNode,
				buttonNode: mainButton,
				disabled: Dom.hasClass(node, SplitButtonState.MAIN_DISABLED)
			};

			menuButtonOptions = {
				tag: this.#getTag(menuButton),
				buttonNode: menuButton,
				textNode: null,
				counterNode: null,
				disabled: Dom.hasClass(node, SplitButtonState.MENU_DISABLED)
			};

			switcherNode = menuButton.querySelector(`.${Switcher.className}`) || null;
		}
		else
		{
			tag = this.#getTag(node);
			if (tag === null)
			{
				throw new Error('BX.UI.ButtonManager.createFromNode: "node" must be a button, link or input.');
			}

			disabled = Dom.hasClass(node, ButtonState.DISABLED);

			if (tag === ButtonTag.INPUT || tag === ButtonTag.SUBMIT)
			{
				text = node.value;
			}
			else
			{
				[textNode, counterNode] = this.#getTextNode(node);
				text = this.#getTextNodeValue(textNode);
			}
		}

		const useAirDesign = Dom.hasClass(node, '--air');

		const options: ButtonOptions & SplitButtonOptions = {
			useAirDesign,
			id: node.dataset.btnUniqid,
			buttonNode: node,
			textNode: isSplitButton ? null : textNode,
			counterNode: isSplitButton ? null : counterNode,
			counter: this.#getCounter(counterNode),
			tag,
			text,
			disabled,
			mainButton: mainButtonOptions,
			menuButton: menuButtonOptions,
			size: this.#getEnumProp(node, ButtonSize),
			color: this.#getEnumProp(node, ButtonColor),
			state: this.#getEnumProp(node, isSplitButton ? SplitButtonState : ButtonState),
			noCaps: Dom.hasClass(node, ButtonStyle.NO_CAPS),
			round: Dom.hasClass(node, ButtonStyle.ROUND),
			dependOnTheme: Dom.hasClass(node, ButtonStyle.DEPEND_ON_THEME),
			style: this.#getEnumProp(node, AirButtonStyle),
			switcher: isSplitButton ? { node: switcherNode } : null,
		};

		if (Dom.hasClass(node, '--with-collapsed-icon') && this.#getEnumProp(node, ButtonIcon))
		{
			options.collapsedIcon = this.#getEnumProp(node, ButtonIcon);
		}
		else if (this.#getEnumProp(node, ButtonIcon))
		{
			options.icon = this.#getEnumProp(node, ButtonIcon);
		}

		if (useAirDesign)
		{
			options.counterNode = undefined;

			if (this.#getCounter(counterNode))
			{
				options.rightCounter = {
					value: this.#getCounter(counterNode),
					style: this.#getCounterStyle(counterNode),
				};

				options.counterNode = undefined;
				options.counter = undefined;

				if (Dom.hasClass(counterNode?.parentElement, 'ui-btn-right-counter'))
				{
					Dom.remove(counterNode?.parentElement);
				}

				Dom.remove(counterNode);
			}
		}

		const nodeOptions = Dom.attr(node, 'data-json-options') || {};

		if (Dom.hasClass(node, ButtonStyle.DROPDOWN))
		{
			options.dropdown = true;
		}
		else if (nodeOptions.dropdown === false)
		{
			options.dropdown = false;
		}

		if (nodeOptions.onclick)
		{
			options.onclick = this.#convertEventHandler(nodeOptions.onclick);
		}

		if (Type.isPlainObject(nodeOptions.events))
		{
			options.events = nodeOptions.events;
			this.#convertEvents(options.events);
		}

		if (Type.isPlainObject(nodeOptions.menu))
		{
			options.menu = nodeOptions.menu;
			this.#convertMenuEvents(options.menu.items);
		}

		['mainButton', 'menuButton'].forEach(button => {

			if (!Type.isPlainObject(nodeOptions[button]))
			{
				return;
			}

			options[button] = Runtime.merge(options[button], nodeOptions[button]);
			if (options[button].onclick)
			{
				options[button].onclick = this.#convertEventHandler(options[button].onclick);
			}

			this.#convertEvents(options[button].events);

		});

		if (Type.isStringFilled(nodeOptions.menuTarget))
		{
			options.menuTarget = nodeOptions.menuTarget;
		}

		return isSplitButton ? new SplitButton(options) : new Button(options);
	}

	static createByUniqId(id): Button | SplitButton | null
	{
		if (!Type.isStringFilled(id))
		{
			return null;
		}

		const node = document.querySelector(`[data-btn-uniqid="${id}"]`);

		return node ? this.createFromNode(node) : null;
	}

	/**
	 * @private
	 * @param {HTMLElement} node
	 * @return {null|number}
	 */
	static #getTag(node: HTMLElement | HTMLInputElement): ButtonTag | null
	{
		if (node.nodeName === 'A')
		{
			return ButtonTag.LINK;
		}
		else if (node.nodeName === 'BUTTON')
		{
			return ButtonTag.BUTTON;
		}
		else if (node.nodeName === 'INPUT' && node.type === 'button')
		{
			return ButtonTag.INPUT;
		}
		else if (node.nodeName === 'INPUT' && node.type === 'submit')
		{
			return ButtonTag.SUBMIT;
		}

		return null;
	}

	/**
	 * @private
	 * @param {HTMLElement} node
	 */
	static #getTextNode(node: HTMLElement): [HTMLElement, HTMLElement]
	{
		let textNode = node.querySelector('.ui-btn-text');
		const counterNode = node.querySelector('.ui-btn-counter') || node.querySelector('.ui-counter');
		const isAirButton = Dom.hasClass(node, '--air');

		if (!textNode)
		{
			if (counterNode)
			{
				Dom.remove(counterNode);
			}

			if (isAirButton)
			{
				textNode = Tag.render`<span class="ui-btn-text">${this.#getTextNodeValue(textNode)}</span>`;
			}
			else
			{
				textNode = Tag.render`<span class="ui-btn-text">${node.innerHTML.trim()}</span>`;
			}

			Dom.clean(node);
			Dom.append(textNode, node);

			if (counterNode)
			{
				Dom.append(counterNode, node);
			}
		}

		return [textNode, counterNode];
	}

	/**
	 * @private
	 * @param counterNode
	 * @return {null|any}
	 */
	static #getCounter(counterNode: HTMLElement): number | string | null
	{
		if (Type.isDomNode(counterNode) && Dom.hasClass(counterNode, Counter.BaseClassname))
		{
			const textContent = counterNode.querySelector('.ui-counter__value')?.innerText;
			const dataAttributeValue = Dom.attr(counterNode, 'data-value');

			const counter = Number(dataAttributeValue || textContent);

			return Type.isNumber(counter) ? counter : textContent;
		}

		if (Type.isDomNode(counterNode))
		{
			const textContent = counterNode.textContent;
			const counter = Number(textContent);

			return Type.isNumber(counter) ? counter : textContent;
		}

		return null;
	}

	/**
	 * @private
	 * @param {HTMLElement} counterNode
	 * @return {string | null}
	 */
	static #getCounterStyle(counterNode: HTMLElement): string | null
	{
		if (!Type.isDomNode(counterNode))
		{
			return null;
		}

		return this.#getEnumProp(counterNode, CounterStyle);
	}

	/**
	 * @private
	 * @param {HTMLElement} node
	 * @param {object} enumeration
	 * @return {null|*}
	 */
	static #getEnumProp(node: HTMLElement, enumeration: Object)
	{
		for (let key in enumeration)
		{
			if (!enumeration.hasOwnProperty(key))
			{
				continue;
			}

			if (Dom.hasClass(node, enumeration[key]))
			{
				return enumeration[key];
			}
		}

		return null;
	}

	/**
	 * @private
	 * @param handler
	 * @return {Function}
	 */
	static #convertEventHandler(handler): Function
	{
		if (Type.isFunction(handler))
		{
			return handler;
		}

		if (!Type.isObject(handler))
		{
			throw new Error('BX.UI.ButtonManager.createFromNode: Event handler must be described as object or function.');
		}

		if (Type.isStringFilled(handler.code))
		{
			return function() { // handle code can use callback arguments
				eval(handler.code);
			};
		}
		else if (Type.isStringFilled(handler.event))
		{
			return function(...args) {

				let event;
				if (args[0] instanceof BaseEvent)
				{
					event = args[0];
				}
				else
				{
					if (args[0] instanceof BaseButton)
					{
						event = new BaseEvent({ data: { button: args[0], event: args[1] } });
					}
					else if (args[1] instanceof MenuItem)
					{
						event = new BaseEvent({ data: { item: args[1], event: args[0] } });
					}
					else
					{
						event = new BaseEvent({ data: args });
					}
				}

				EventEmitter.emit(handler.event, event);
			};
		}
		else if (Type.isStringFilled(handler.handler))
		{
			return function(...args) {
				const fn = Reflection.getClass(handler.handler);
				if (Type.isFunction(fn))
				{
					let context = this;
					if (Type.isStringFilled(handler.context))
					{
						context = Reflection.getClass(handler.context);
					}

					return fn.apply(context, args);
				}
				else
				{
					console.warn(
						`BX.UI.ButtonManager.createFromNode: be aware, the handler ${handler.handler} is not a function.`
					);
				}

				return null;
			};
		}

		return null;
	}

	/**
	 * @private
	 * @param events
	 */
	static #convertEvents(events)
	{
		if (Type.isPlainObject(events))
		{
			for (let [eventName, eventFn] of Object.entries(events))
			{
				events[eventName] = this.#convertEventHandler(eventFn);
			}
		}
	}

	/**
	 * @private
	 * @param items
	 */
	static #convertMenuEvents(items)
	{
		if (!Type.isArray(items))
		{
			return;
		}

		items.forEach(item => {
			if (item.onclick)
			{
				item.onclick = this.#convertEventHandler(item.onclick);
			}

			if (item.events)
			{
				this.#convertEvents(item.events);
			}

			if (Type.isArray(item.items))
			{
				this.#convertMenuEvents(item.items);
			}
		});
	}

	/**
	 * @deprecated
	 * @param uniqId
	 * @return {null|*}
	 */
	static getByUniqid(uniqId)
	{
		const ToolbarManager = Reflection.getClass('BX.UI.ToolbarManager');
		const toolbar = ToolbarManager?.getDefaultToolbar();

		return toolbar ? toolbar.getButton(uniqId) : null;
	}

	static #getTextNodeValue(target: HTMLElement): string
	{
		if (!target)
		{
			return '';
		}

		if (target.querySelector('.ui-btn-text-inner'))
		{
			return target.querySelector('.ui-btn-text-inner')?.textContent || '';
		}

		const childNodes = target.childNodes;

		for (const node of childNodes)
		{
			if (node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '')
			{
				return node.textContent.trim();
			}
		}

		return '';
	}
}
