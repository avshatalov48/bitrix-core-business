import {Type, Tag, Dom} from "main.core";
import {Set} from "./icon";

export type IconOptions = {
	icon: string,
	size?: number,
	color?: string,
};

export class Icon {
	icon: string;
	size: number;
	color: string;
	iconElement: HTMLElement | null;

	constructor(params: IconOptions = {}) {
		this.validateParams(params);

		this.icon = params.icon;
		this.size = params.size || null;
		this.color = params.color || null;

		this.iconElement = null;
	}

	validateParams(params: IconOptions): void
	{
		if (!params.icon)
		{
			throw new Error('IconSet: property "icon" not set.');
		}

		if (!Object.values(Set).includes(params.icon))
		{
			throw new Error('IconSet: "icon" is not exist.');
		}

		if (params.size && !Type.isNumber(params.size))
		{
			throw new Error('IconSet: "size" is not a number.');
		}

		if (params.color && !Type.isString(params.color))
		{
			throw new Error('IconSet: "color" is not a string.');
		}
	}

	renderTo(node: HTMLElement): void
	{
		if (!Type.isElementNode(node))
		{
			throw new Error('IconSet: node is not a htmlElement.');
		}

		Dom.append(this.render(), node);
	}

	render(): Node
	{
		let className = 'ui-icon-set' +  ` --${this.icon}`;

		this.iconElement = Tag.render`<div class="${className}"></div>`;

		if (this.size)
		{
			Dom.style(this.iconElement, '--ui-icon-set__icon-size', `${this.size}px`);
		}

		if (this.color)
		{
			Dom.style(this.iconElement, `--ui-icon-set__icon-color`, `${this.color}`);
		}

		return this.iconElement;
	}

	/**
	 *
	 * @param color
	 */
	setColor(color: string): void
	{
		Dom.style(this.iconElement, `--ui-icon-set__icon-color`, `${color}`);
	}
}