import {Type, Tag, Loc, Dom, bind, onCustomEvent} from 'main.core';
import 'ui.design-tokens';

import './css/style.css';

export const SwitcherSize = Object.freeze({
	medium: 'medium',
	small: 'small',
	extraSmall: 'extra-small',
});

export const SwitcherColor = Object.freeze({
	primary: 'primary',
	green: 'green',
});

export type SwitcherOptions = {
	attributeName: string;
	node: HTMLElement;
	id: string;
	checked: boolean;
	inputName: string;
	handlers: Object;
	size: string;
	color: string;
	disabled: boolean;
}

export class Switcher {
	#classNameSize = {
		[SwitcherSize.extraSmall]: 'ui-switcher-size-xs',
		[SwitcherSize.small]: 'ui-switcher-size-sm',
		[SwitcherSize.medium]: '',
	}
	#classNameColor = {
		[SwitcherColor.primary]: '',
		[SwitcherColor.green]: 'ui-switcher-color-green',
	}

	node: HTMLElement | null = null;
	checked: boolean = false;
	id: string = '';
	#disabled: boolean = false;
	#inputName: string = '';
	#loading: boolean;
	events: Object;
	#classNameOff: string = 'ui-switcher-off';
	#classNameLock: string = 'ui-switcher-lock';
	#attributeName: string = 'data-switcher';

	static #attributeInitName: string = 'data-switcher-init';
	static list = [];
	static className = 'ui-switcher';

	/**
	 * Switcher.
	 *
	 * @param {object} [options] - Options.
	 * @param {string} [options.attributeName] - Name of switcher attribute.
	 * @param {Element} [options.node] - Node.
	 * @param {string} [options.id] - ID.
	 * @param {123} [options.checked] - Checked.
	 * @param {string} [options.inputName] - Input name.
	 * @constructor
	 */
	constructor(options: SwitcherOptions)
	{
		this.init(options);
		Switcher.list.push(this);
	}

	static getById(id: string | number): Switcher | null
	{
		return Switcher.list.find((item) => item.id === id) || null;
	}

	static initByClassName(): void
	{
		const nodes = document.getElementsByClassName(Switcher.className);
		Array.from(nodes).forEach(function (node) {
			if (node.getAttribute(Switcher.#attributeInitName))
			{
				return;
			}
			new Switcher({node: node});
		});
	}

	static getList(): Switcher[]
	{
		return Switcher.list;
	}

	init(options: SwitcherOptions = {}): void
	{
		this.#attributeName = Type.isString(options.attributeName) ? options.attributeName : this.#attributeName;
		this.handlers = Type.isPlainObject(options.handlers) ? options.handlers : {};
		this.#inputName = Type.isString(options.inputName) ? options.inputName : '';
		this.#loading = false;
		this.events = {
			toggled: 'toggled',
			checked: 'checked',
			unchecked: 'unchecked',
			lock: 'lock',
			unlock: 'unlock',
		};

		if (options.node)
		{
			if (!Type.isDomNode(options.node))
			{
				throw new Error('Parameter `node` DOM Node expected.');
			}

			this.node = options.node;
			let data = this.node.getAttribute(this.#attributeName);
			try
			{
				data = JSON.parse(data) || {};
			}
			catch (e)
			{
				data = {};
			}

			if (data.id)
			{
				this.id = data.id;
			}

			this.checked = Boolean(data.checked);
			this.#inputName = data.inputName;
			if(Type.isString(data.color) && Object.values(SwitcherColor).includes(data.color))
			{
				options.color = data.color;
			}
			if(Type.isString(data.size) && Object.values(SwitcherSize).includes(data.size))
			{
				options.size = data.size;
			}
		}
		else
		{
			this.node = document.createElement('span');
		}

		if (this.#classNameSize[options.size])
		{
			Dom.addClass(this.node, this.#classNameSize[options.size]);
		}
		if (this.#classNameColor[options.color])
		{
			Dom.addClass(this.node, this.#classNameColor[options.color]);
		}

		if (Type.isString(options.id) || Type.isNumber(options.id))
		{
			this.id = options.id;
		}
		else if (!this.id)
		{
			this.id = Math.random();
		}

		if (Type.isString(options.inputName))
		{
			this.#inputName = options.inputName;
		}
		this.checked = Type.isBoolean(options.checked) ? options.checked : this.checked;
		this.#disabled = Type.isBoolean(options.disabled) ? options.disabled : this.#disabled;

		this.#initNode();
		this.check(this.checked, false);
		this.disable(this.#disabled, false);
	}

	#initNode(): void
	{
		if (this.node.getAttribute(Switcher.#attributeInitName))
		{
			return;
		}
		this.node.setAttribute(Switcher.#attributeInitName, 'y');

		Dom.addClass(this.node, Switcher.className);
		this.node.innerHTML =
			'<span class="ui-switcher-cursor"></span>\n' +
			'<span class="ui-switcher-enabled">' + Loc.getMessage('UI_SWITCHER_ON') + '</span>\n' +
			'<span class="ui-switcher-disabled">' + Loc.getMessage('UI_SWITCHER_OFF') + '</span>\n';

		if (this.#inputName)
		{
			this.inputNode = Tag.render`
				<input type="hidden" name="${this.#inputName}" />
			`;

			Dom.append(this.inputNode, this.node);
		}

		bind(this.node, 'click', this.toggle.bind(this));
	}

	disable(disabled: boolean, fireEvents: boolean): void
	{
		if (this.isLoading())
		{
			return;
		}

		this.#disabled = disabled;

		fireEvents = fireEvents !== false;

		if (disabled)
		{
			Dom.addClass(this.node, this.#classNameLock);
			fireEvents ? this.#fireEvent(this.events.lock) : null;
		}
		else
		{
			Dom.removeClass(this.node, this.#classNameLock);
			fireEvents ? this.#fireEvent(this.events.unlock) : null;
		}
	}

	check(checked: boolean, fireEvents: boolean): void
	{
		if (this.isLoading())
		{
			return;
		}

		this.checked = !!checked;
		if (this.inputNode)
		{
			this.inputNode.value = this.checked ? 'Y' : 'N';
		}

		fireEvents = fireEvents !== false;

		if (this.checked)
		{
			Dom.removeClass(this.node, this.#classNameOff);
			fireEvents ? this.#fireEvent(this.events.unchecked) : null;
		}
		else
		{
			Dom.addClass(this.node, this.#classNameOff);
			fireEvents ? this.#fireEvent(this.events.checked) : null;
		}

		if (fireEvents)
		{
			this.#fireEvent(this.events.toggled)
		}
	}

	isDisabled()
	{
		return this.#disabled;
	}

	isChecked(): boolean
	{
		return this.checked;
	}

	toggle(): void
	{
		if (this.isDisabled())
		{
			return;
		}

		this.check(!this.isChecked());
	}

	setLoading(mode: boolean): void
	{
		this.#loading = Boolean(mode);

		const cursor = this.getNode().querySelector('.ui-switcher-cursor');

		if (this.#loading)
		{
			const svg = Tag.render`
				<svg viewBox="25 25 50 50">
					<circle
						class="ui-sidepanel-wrapper-loader-path"
						cx="50"
						cy="50"
						r="19"
						fill="none"
						stroke-width="5"
						stroke-miterlimit="10"
					>
					</circle>
				</svg>
			`
			Dom.append(svg, cursor);
		}
		else
		{
			cursor.innerHTML = '';
		}
	}

	isLoading(): boolean
	{
		return this.#loading;
	}

	#fireEvent(eventName: string): void
	{
		onCustomEvent(this, eventName);
		if (this.handlers[eventName])
		{
			this.handlers[eventName].call(this);
		}
	}

	renderTo(targetNode: HTMLElement): HTMLElement
	{
		if (!Type.isDomNode(targetNode))
		{
			throw new Error('Target node must be HTMLElement');
		}

		return Dom.append(this.getNode(), targetNode);
	}

	getNode(): HTMLElement
	{
		return this.node;
	}

	getAttributeName(): string
	{
		return this.#attributeName;
	}

	getInputName(): string
	{
		return this.#inputName;
	}
}
