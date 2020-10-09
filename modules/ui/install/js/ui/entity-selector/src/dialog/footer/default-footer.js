import { Cache, Tag, Type } from 'main.core';
import BaseFooter from './base-footer';
import type Dialog from '../dialog';

export default class DefaultFooter extends BaseFooter
{
	content: HTMLElement = null;
	cache = new Cache.MemoryCache();

	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.setContent(this.getOption('content'));
	}

	getContainer()
	{
		return this.cache.remember('container', () => {
			return Tag.render`
				<div class="ui-selector-footer-default">
					${this.getContent() ? this.getContent() : '' }
				</div>
			`;
		});
	}

	getContent(): HTMLElement | HTMLElement[] | string | null
	{
		return this.content;
	}

	setContent(content: string | HTMLElement)
	{
		if (Type.isStringFilled(content) || Type.isDomNode(content) || Type.isArrayFilled(content))
		{
			this.content = content;
		}
	}

	render(): HTMLElement
	{
		return this.getContainer();
	}
}