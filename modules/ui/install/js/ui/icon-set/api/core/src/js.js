import { Type, Tag, Dom } from 'main.core';
import 'ui.design-tokens.air';

import { Actions, Main, ContactCenter, Outline, CRM, Social, Animated, Editor, Special, Solid, Disk, DiskCompact, SmallOutline } from './icon';
import { IconHoverMode } from './icon-hover-mode';

export type IconOptions = {
	icon: string,
	size?: number,
	color?: string,
	hoverMode?: IconHoverMode,
	responsive?: boolean,
};

export class Icon
{
	icon: string;
	size: number;
	color: string;
	iconElement: HTMLElement | null;
	#hoverMode: ?string = null;
	#responsive: boolean = false;

	static isValid(params: IconOptions = {}): boolean
	{
		return Icon.validateParams(params) === null;
	}

	static validateParams(params: IconOptions): ?string
	{
		if (!params.icon)
		{
			return 'IconSet: property "icon" not set.';
		}

		if (!Type.isUndefined(params.size) && !Type.isNumber(params.size))
		{
			return 'IconSet: "size" is not a number.';
		}

		if (params.color && !Type.isString(params.color))
		{
			return 'IconSet: "color" is not a string.';
		}

		const sets = [
			Actions,
			Main,
			ContactCenter,
			Outline,
			CRM,
			Social,
			Animated,
			Editor,
			Special,
			Solid,
			Disk,
			DiskCompact,
			SmallOutline,
		];

		const iconExists = sets.some((set) => Object.values(set).includes(params.icon));
		if (!iconExists)
		{
			return 'IconSet: "icon" is not exist.';
		}

		return null;
	}

	constructor(params: IconOptions = {}) {
		const error = Icon.validateParams(params);
		if (error)
		{
			throw new Error(error);
		}

		this.icon = params.icon;
		this.size = params.size > 0 ? params.size : null;
		this.color = params.color || null;
		this.#hoverMode = params.hoverMode ?? null;
		this.#responsive = params.responsive ?? false;

		this.iconElement = null;
	}

	validateParams(params: IconOptions): void
	{
		if (!params.icon)
		{
			throw new Error('IconSet: property "icon" not set.');
		}

		if (!this.#checkIconExistence(params.icon))
		{
			throw new Error('IconSet: "icon" is not exist.');
		}

		if (!Type.isUndefined(params.size) && !Type.isNumber(params.size))
		{
			throw new TypeError('IconSet: "size" is not a number.');
		}

		if (params.color && !Type.isString(params.color))
		{
			throw new TypeError('IconSet: "color" is not a string.');
		}

		if (!Type.isUndefined(params.responsive) && !Type.isBoolean(params.responsive))
		{
			throw new TypeError('IconSet: "responsive" is not a boolean.');
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
		const className = `ui-icon-set --${this.icon}`;

		this.iconElement = Tag.render`<div class="${className}"></div>`;

		if (this.#responsive)
		{
			Dom.style(this.iconElement, '--ui-icon-set__icon-size', '100%');
		}
		else if (this.size)
		{
			Dom.style(this.iconElement, '--ui-icon-set__icon-size', `${this.size}px`);
		}

		if (this.color)
		{
			Dom.style(this.iconElement, '--ui-icon-set__icon-color', this.color);
		}

		if (this.#hoverMode)
		{
			this.setHoverMode(this.#hoverMode);
		}

		if (this.#isIconWithFixedColor())
		{
			Dom.addClass(this.iconElement, '--fixed-color');
		}
		else
		{
			Dom.removeClass(this.iconElement, '--fixed-color');
		}

		return this.iconElement;
	}

	/**
	 *
	 * @param color
	 */
	setColor(color: string): void
	{
		Dom.style(this.iconElement, '--ui-icon-set__icon-color', color);
	}

	setHoverMode(hoverMode: IconHoverMode | null): void
	{
		const prevHoverMode = this.#hoverMode;
		this.#hoverMode = hoverMode;

		if (!this.iconElement)
		{
			return;
		}

		Dom.removeClass(this.iconElement, this.#getHoverModeClassnameModifier(prevHoverMode));
		Dom.addClass(this.iconElement, this.#getHoverModeClassnameModifier(hoverMode));
	}

	setResponsive(responsive: boolean): void
	{
		this.#responsive = responsive;

		if (!this.iconElement)
		{
			return;
		}

		if (this.#responsive)
		{
			Dom.style(this.iconElement, '--ui-icon-set__icon-size', '100%');
			Dom.addClass(this.iconElement, '--responsive');
		}
		else
		{
			Dom.style(this.iconElement, '--ui-icon-set__icon-size', null);
			Dom.removeClass(this.iconElement, '--responsive');
		}
	}

	#getHoverModeClassnameModifier(hoverMode: IconHoverMode): string
	{
		const hoverModeModifiers = {
			[IconHoverMode.DEFAULT]: '--hoverable-default',
			[IconHoverMode.ALT]: '--hoverable-alt',
		};

		return hoverModeModifiers[hoverMode] ?? '';
	}

	#checkIconExistence(iconName: string): boolean
	{
		const sets = [Actions, Main, ContactCenter, Outline, CRM, Social, Animated, Editor, Special, Solid];

		return sets.some((set) => {
			return Object.values(set).includes(iconName);
		});
	}

	#isIconWithFixedColor(): boolean
	{
		return Object.values(Disk).includes(this.icon) || Object.values(DiskCompact).includes(this.icon);
	}
}
