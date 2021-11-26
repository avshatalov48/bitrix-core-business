import { Dom, Tag, Type } from 'main.core';
import BaseFooter from './base-footer';
import type Dialog from '../dialog';
import type Tab from '../tabs/tab';
import type { FooterOptions } from './footer-content';

export default class DefaultFooter extends BaseFooter
{
	content: HTMLElement = null;

	constructor(context: Dialog | Tab, options: FooterOptions)
	{
		super(context, options);

		this.setContent(this.getOption('content'));
	}

	render()
	{
		const container = Tag.render`
			<div>
				${this.getContent() ? this.getContent() : '' }
			</div>
		`;

		const className = this.getOption('containerClass', 'ui-selector-footer-default');
		const containerStyles = this.getOption('containerStyles', {});

		Dom.addClass(container, className);
		Dom.style(container, containerStyles);

		return container;
	}

	getContent(): HTMLElement | HTMLElement[] | string | null
	{
		return this.content;
	}

	setContent(content: string | HTMLElement | HTMLElement[])
	{
		if (Type.isStringFilled(content) || Type.isDomNode(content) || Type.isArrayFilled(content))
		{
			this.content = content;
		}
	}
}