import { Type, Dom } from 'main.core';
import type { ItemBadgeOptions } from './item-badge-options';
import TextNode from '../common/text-node';
import type { TextNodeOptions } from '../common/text-node-options';

export default class ItemBadge
{
	title: ?TextNode = null;
	textColor: ?string = null;
	bgColor: ?string = null;
	containers: WeakMap<HTMLElement, HTMLElement> = new WeakMap();

	constructor(badgeOptions: ItemBadgeOptions)
	{
		const options: ItemBadgeOptions = Type.isPlainObject(badgeOptions) ? badgeOptions : {};

		this.setTitle(options.title);
		this.setTextColor(options.textColor);
		this.setBgColor(options.bgColor);
	}

	getTitle(): string
	{
		const titleNode = this.getTitleNode();

		return titleNode !== null && !titleNode.isNullable() ? titleNode.getText() : '';
	}

	getTitleNode(): ?TextNode
	{
		return this.title;
	}

	setTitle(title: ?string | TextNodeOptions): void
	{
		if (Type.isStringFilled(title) || Type.isPlainObject(title) || title === null)
		{
			this.title = title === null ? null : new TextNode(title);
		}
	}

	getTextColor(): ?string
	{
		return this.textColor;
	}

	setTextColor(textColor: ?string): void
	{
		if (Type.isString(textColor) || textColor === null)
		{
			this.textColor = textColor;
		}
	}

	getBgColor(): ?string
	{
		return this.bgColor;
	}

	setBgColor(bgColor: ?string): void
	{
		if (Type.isString(bgColor) || bgColor === null)
		{
			this.bgColor = bgColor;
		}
	}

	getContainer(target: HTMLElement): HTMLElement
	{
		let container = this.containers.get(target);
		if (!container)
		{
			container = document.createElement('span');
			container.className = 'ui-selector-item-badge';

			this.containers.set(target, container);
		}

		return container;
	}

	renderTo(target: HTMLElement): void
	{
		const container = this.getContainer(target);

		const titleNode = this.getTitleNode();
		if (titleNode)
		{
			this.getTitleNode().renderTo(container);
		}
		else
		{
			container.textContent = '';
		}

		Dom.style(container, 'color', this.getTextColor());
		Dom.style(container, 'background-color', this.getBgColor())
		Dom.append(container, target);
	}

	toJSON()
	{
		return {
			title: this.getTitleNode(),
			textColor: this.getTextColor(),
			bgColor: this.getBgColor()
		}
	}
}