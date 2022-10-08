import {EventEmitter} from 'main.core.events';
import {Tag, Cache, Text, Dom} from 'main.core';
import type {TabOptions} from './types/tabs-options';
import typeof {Content} from '../content/content';

import './css/tab.css';

export class Tab extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: TabOptions)
	{
		super();
		this.setOptions(options);
		this.setEventNamespace('BX.UI.SignUp.Tabs.Tab');
		this.subscribeFromOptions(options.events);
	}

	setOptions(options: TabOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): TabOptions
	{
		return this.cache.get('options', {});
	}

	getIconNode(): HTMLSpanElement
	{
		return this.cache.remember('iconNode', () => {
			return Tag.render`
				<span style="background-image: url('${this.getOptions().icon}');"></span>
			`;
		});
	}

	getHeaderLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div 
					class="ui-sign-up-tabs-tab-header" 
					data-id="${Text.encode(this.getOptions().id)}"
					onclick="${this.onHeaderClick.bind(this)}"
				>
					<div class="ui-sign-up-tabs-tab-header-icon">
						${this.getIconNode()}	
					</div>
					<div class="ui-sign-up-tabs-tab-header-text">
						<span>${this.getOptions().header}</span>
					</div>
				</div>
			`;
		});
	}

	onHeaderClick(event: MouseEvent)
	{
		event.preventDefault();
		this.emit('onHeaderClick');
	}

	getContent(): Content
	{
		return this.getOptions().content;
	}

	activate()
	{
		Dom.addClass(this.getHeaderLayout(), 'ui-sign-up-tabs-tab-header-active');
		Dom.style(this.getIconNode(), {
			'background-image': `url('${this.getOptions().activeIcon}')`
		});
	}

	deactivate()
	{
		Dom.removeClass(this.getHeaderLayout(), 'ui-sign-up-tabs-tab-header-active');
		Dom.style(this.getIconNode(), {
			'background-image': `url('${this.getOptions().icon}')`
		});
	}

	isActive(): boolean
	{
		return Dom.hasClass(this.getHeaderLayout(), 'ui-sign-up-tabs-tab-header-active');
	}
}