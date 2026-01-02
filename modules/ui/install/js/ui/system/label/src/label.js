import { Dom, Tag } from 'main.core';

import { LabelStyle } from './label-style';
import { LabelSize } from './label-size';
import { LabelIcon } from './label-icon';

import './style.css';

export type LabelOptions = {
	size: ?LabelSize;
	value: string;
	style: ?LabelStyle;
	border: ?boolean;
};

export class Label
{
	#size: ?string;
	#value: ?string;
	#style: ?string;
	#border: ?boolean;
	#icon: ?string;

	#wrapper: ?HTMLElement = null;

	constructor(options: LabelOptions = {})
	{
		this.setSize(options.size ?? LabelSize.MD);
		this.setStyle(options.style ?? LabelStyle.FILLED);
		this.#value = options.value ?? '';
		this.#border = options.border === true;
		this.#icon = options.icon ?? null;
	}

	render(): HTMLElement
	{
		this.#wrapper = Tag.render`
			<div class="${this.#getClassname()}" title="${this.#value}">
				<div class="ui-system-label__inner">
					<div class="ui-system-label__value">
						${this.#value}
					</div>
				</div>
			</div>
		`;

		return this.#wrapper;
	}

	/*
	* @deprecated used only in vue extension
	* */
	renderOnNode(node: HTMLElement): void
	{
		// eslint-disable-next-line no-param-reassign
		node.className = '';
		// eslint-disable-next-line no-param-reassign
		node.innerHTML = '';

		// eslint-disable-next-line no-param-reassign
		node.className = this.#getClassname();

		Dom.attr(node, 'title', this.#value);

		const nodeInner = Tag.render`
			<div class="ui-system-label__inner">
				<div class="ui-system-label__value">
					${this.#value}
				</div>
			</div>
		`;

		Dom.append(nodeInner, node);

		this.#wrapper = node;
	}

	getStyle(): string
	{
		return this.#style;
	}

	setStyle(style: LabelStyle): void
	{
		if (this.#validateStyle(style) === false)
		{
			return;
		}

		if (this.#wrapper)
		{
			Dom.removeClass(this.#wrapper, `--style-${this.#style}`);
			Dom.addClass(this.#wrapper, `--style-${style}`);
		}

		this.#style = style;
	}

	setSize(size: LabelSize): void
	{
		if (this.#validateSize(size) === false)
		{
			return;
		}

		if (this.#wrapper)
		{
			Dom.removeClass(this.#wrapper, `--size-${this.#size}`);
			Dom.addClass(this.#wrapper, `--size-${size}`);
		}

		this.#size = size;
	}

	getSize(): string
	{
		return this.#size;
	}

	getValue(): ?string
	{
		return this.#value;
	}

	setValue(value: string): void
	{
		this.#value = value;

		if (this.#wrapper)
		{
			const valueElement = this.#wrapper.querySelector('.ui-system-label__value');

			if (valueElement)
			{
				valueElement.innerText = value;
			}

			Dom.attr(this.#wrapper, 'title', this.#value);
		}
	}

	setIcon(icon: LabelIcon): void
	{
		Dom.removeClass(this.#wrapper, [`--icon-${this.#icon}`, '--icon-mode']);

		if (icon)
		{
			this.#icon = icon;
			Dom.addClass(this.#wrapper, [`--icon-${this.#icon}`, '--icon-mode']);
		}
		else
		{
			this.#icon = null;
		}
	}

	setBordered(flag: boolean = true): void
	{
		this.#border = flag === true;

		if (!this.#wrapper)
		{
			return;
		}

		if (this.#border)
		{
			Dom.addClass(this.#wrapper, '--bordered');
		}
		else
		{
			Dom.removeClass(this.#wrapper, '--bordered');
		}
	}

	destroy(): void
	{
		Dom.remove(this.#wrapper);
		this.#wrapper = null;
	}

	#getClassname(): string
	{
		const classes = [
			'ui-system-label',
			`--size-${this.#size}`,
			`--style-${this.#style}`,
		];

		if (this.#border)
		{
			classes.push('--bordered');
		}

		if (this.#icon)
		{
			classes.push(`--icon-mode --icon-${this.#icon}`);
		}

		return classes.join(' ');
	}

	#validateSize(size: string): boolean
	{
		const isValid = Object.values(LabelSize).includes(size);

		if (isValid === false)
		{
			console.warn('UI.System.Label: invalid size', size);
		}

		return isValid;
	}

	#validateStyle(style: string): boolean
	{
		const isValid = Object.values(LabelStyle).includes(style);

		if (isValid === false)
		{
			console.warn('UI.System.Label: invalid style', style);
		}

		return isValid;
	}
}
