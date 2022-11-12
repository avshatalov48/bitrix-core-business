import { Dom, Tag, Type } from 'main.core';
import BaseHeader from './base-header';
import type Dialog from '../dialog';
import type Tab from '../tabs/tab';
import type { HeaderOptions } from './header-content';

export default class DefaultHeader extends BaseHeader
{
	content: HTMLElement = null;

	constructor(context: Dialog | Tab, options: HeaderOptions)
	{
		super(context, options);

		this.setContent(this.getOption('content'));
	}

	render(): HTMLElement
	{
		const container = Tag.render`
			<div>
				${this.getContent() ? this.getContent() : '' }
			</div>
		`;

		const className = this.getOption('containerClass', 'ui-selector-header-default');
		const containerStyles = this.getOption('containerStyles', {});

		Dom.addClass(container, className);
		Dom.style(container, containerStyles);

		return container;
	}

	getContent(): HTMLElement | HTMLElement[] | string | null
	{
		return this.content;
	}

	setContent(content: string | HTMLElement | HTMLElement[]): void
	{
		if (Type.isStringFilled(content) || Type.isDomNode(content) || Type.isArrayFilled(content))
		{
			this.content = content;
		}
	}
}