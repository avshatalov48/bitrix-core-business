import {Cache, Tag, Type, Dom} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {fetchEventsFromOptions} from 'landing.ui.component.internal';

import './css/style.css';

type IconButtonOptions = {
	onClick?: () => void,
	// eslint-disable-next-line no-use-before-define
	type: $Values<typeof IconButton.Types>,
	title?: string,
	data?: any,
	style?: CSSStyleDeclaration,
	iconSize?: string,
};

export class IconButton extends EventEmitter
{
	static Types = {
		remove: 'remove',
		drag: 'drag',
		edit: 'edit',
		font: 'font',
		link: 'link'
	};

	constructor(options: IconButtonOptions)
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Component.IconButton');
		this.subscribeFromOptions(fetchEventsFromOptions(options));
		this.options = {...options};
		this.cache = new Cache.MemoryCache();

		this.onClick = this.onClick.bind(this);
	}

	getData(): any
	{
		return this.options.data;
	}

	onClick(event: MouseEvent)
	{
		event.preventDefault();
		this.emit('onClick');
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			const layout = Tag.render`
				<div 
					class="landing-ui-button-icon-${this.options.type}"
					onclick="${this.onClick}"
					title="${Type.isStringFilled(this.options.title) ? this.options.title : ''}"
				></div>
			`;

			if (Type.isPlainObject(this.options.style))
			{
				Dom.style(layout, this.options.style);
			}

			if (Type.isStringFilled(this.options.iconSize))
			{
				Dom.style(layout, 'background-size', this.options.iconSize);
			}

			return layout;
		});
	}
}
