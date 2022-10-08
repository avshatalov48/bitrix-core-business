import 'ui.design-tokens';

import {Cache, Tag, Type, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';

class Colors
{
	static Primary = 'primary';
	static Grey = 'grey';
}

type LinkOptions = {
	text: string | HTMLElement,
	href?: string,
	target?: '_self' | '_blank' | '_parent' | '_top',
	attrs?: {[key: string]: any},
	style?: CSSStyleDeclaration,
	color?: $Values<Colors>,
};

const defaultOptions: LinkOptions = {
	text: '',
	color: Colors.Primary,
	attrs: {},
	style: {},
};

export class Link extends EventEmitter
{
	static Colors = Colors;

	options: LinkOptions;

	constructor(options: LinkOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Component.Link');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.options = {...defaultOptions, ...options};
		this.cache = new Cache.MemoryCache();
	}

	getTag(): string
	{
		return this.cache.remember('tag', () => {
			return Type.isStringFilled(this.options.href) ? 'a' : 'span';
		});
	}

	getLayout(): HTMLSpanElement | HTMLAnchorElement
	{
		return this.cache.remember('layout', () => {
			const tag = this.getTag();
			const element = Tag.render`
				<${tag}
					class="landing-ui-component-link landing-ui-component-link-color-${this.options.color}"
					onclick="${this.onClick.bind(this)}">${this.options.text}</${tag}>
			`;

			if (tag === 'a')
			{
				Dom.attr(element, 'href', this.options.href);
			}

			if (tag === 'a' && Type.isStringFilled(this.options.target))
			{
				Dom.attr(element, 'target', this.options.target);
			}

			Dom.attr(element, this.options.attrs);
			Dom.style(element, this.options.style);

			return element;
		});
	}

	onClick(event: MouseEvent)
	{
		if (this.getTag() === 'span')
		{
			event.preventDefault();
		}

		this.emit('onClick');
	}
}