import { Tag, Type, Dom } from 'main.core';
import type { ItemBadgeOptions } from './item-badge-options';

export default class ItemBadge
{
	title: string = '';
	textColor: ?string = null;
	bgColor: ?string = null;
	container: ?HTMLElement = null;

	constructor(badgeOptions: ItemBadgeOptions)
	{
		const options: ItemBadgeOptions = Type.isPlainObject(badgeOptions) ? badgeOptions : {};

		this.setTitle(options.title);
		this.setTextColor(options.textColor);
		this.setBgColor(options.bgColor);
	}

	getTitle(): string
	{
		return this.title;
	}

	setTitle(title: string): this
	{
		if (Type.isStringFilled(title))
		{
			this.title = title;
		}

		return this;
	}

	getTextColor(): ?string
	{
		return this.textColor;
	}

	setTextColor(textColor: ?string): this
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
		}

		return this;
	}

	getBgColor(): ?string
	{
		return this.bgColor;
	}

	setBgColor(bgColor: ?string): this
	{
		if (Type.isString(bgColor) || bgColor === null)
		{
			this.bgColor = bgColor;
		}

		return this;
	}

	render(): HTMLElement
	{
		const container = Tag.render`<span class="ui-selector-item-badge"></span>`;

		container.textContent = this.getTitle();
		Dom.style(container, 'color', this.getTextColor());
		Dom.style(container, 'background-color', this.getBgColor());

		return container;
	}
}