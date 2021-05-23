import { Tag, Type } from 'main.core';
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
		return Tag.render`
			<div class="ui-selector-footer-default">
				${this.getContent() ? this.getContent() : '' }
			</div>
		`;
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