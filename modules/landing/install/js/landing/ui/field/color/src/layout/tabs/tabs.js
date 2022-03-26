import {Cache, Dom, Event, Tag, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

import BaseControl from '../../control/base_control/base_control';
import './css/tabs.css';

export default class Tabs extends EventEmitter
{
	tabs: Tab[];
	multiple: boolean;
	isBig: boolean;

	constructor()
	{
		super();
		this.setEventNamespace('BX.Landing.UI.Field.Color.Tabs');

		this.tabs = [];
		this.cache = new Cache.MemoryCache();
		this.multiple = true;
		this.isBig = false;

		this.onToggle = this.onToggle.bind(this);
	}

	setMultiple(multiple: boolean): Tabs
	{
		this.multiple = multiple;

		return this;
	}

	setBig(big: boolean): Tabs
	{
		this.isBig = big;
		this.multiple = false;

		return this;
	}

	appendTab(id: string, title: string, items: BaseControl | Tab | [BaseControl | Tab]): Tabs
	{
		const tab = new Tab({
			id: id,
			title: title,
			items: Type.isArray(items) ? items : [items],
		});
		this.tabs.push(tab);
		this.bindEvents(tab);
		this.cache.delete('layout');

		return this;
	}

	prependTab(id: string, title: string, items: BaseControl | Tab | [BaseControl | Tab]): Tabs
	{
		const tab = new Tab({
			id: id,
			title: title,
			items: Type.isArray(items) || [items],
		});
		this.tabs.unshift(tab);
		this.bindEvents(tab);
		this.cache.delete('layout');

		return this;
	}

	bindEvents(tab)
	{
		tab.subscribe('onToggle', this.onToggle);
		tab.subscribe('onShow', this.onToggle);
		tab.subscribe('onHide', this.onToggle);
	}

	onToggle(event: BaseEvent)
	{
		this.emit('onToggle', event);
	}

	showTab(id): Tabs
	{
		if (!this.multiple)
		{
			this.tabs.forEach((tab) => {
				tab.hide();
			});
		}

		const tab = this.getTabById(id);
		if (tab)
		{
			tab.show();
		}

		return this;
	}

	getTabById(id: string): Tab
	{
		return this.tabs.find((tab) => {
			return tab.id === id;
		});
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			const additional = this.isBig ? ' landing-ui-field-color-tabs--big' : '';
			const layout = Tag.render`<div class="landing-ui-field-color-tabs${additional}"></div>`;

			if (this.isBig)
			{
				const head = Tag.render`
					<div class="landing-ui-field-color-tabs-head landing-ui-field-color-tabs-head--big"></div>
				`;
				const content = Tag.render`
					<div class="landing-ui-field-color-tabs-content landing-ui-field-color-tabs-content--big"></div>
				`;

				this.tabs.forEach(tab => {
					Dom.append(tab.getTitle(), head);
					Dom.append(tab.getLayout(), content);
				});

				Dom.append(head, layout);
				Dom.append(content, layout);
			}
			else
			{
				this.tabs.forEach(tab => {
					const tabLayout = Tag.render`<div class="landing-ui-field-color-tabs-tab">
						${tab.getTitle()}${tab.getLayout()}
					</div>`;
					Dom.append(tabLayout, layout);
				});
			}

			// events
			this.tabs.forEach(tab => {
				Event.bind(tab.getTitle(), 'click', () => {
					if (!this.multiple)
					{
						this.tabs.forEach((tab) => {
							tab.hide();
						});
					}

					tab.toggle();
				});
			});

			return layout;
		});
	}
}

export type TabOptions = {
	id: string,
	title: string,
	items: BaseControl[]
}

export class Tab extends EventEmitter
{
	id: string;
	title: string;
	items: BaseControl[];

	static SHOW_CLASS: string = 'show';

	constructor(options: TabOptions)
	{
		super();

		this.id = options.id;
		this.title = options.title;
		this.items = options.items;
		this.cache = new Cache.MemoryCache();
	}

	getId(): string
	{
		return this.id;
	}

	getTitle(): string
	{
		return this.cache.remember('title', () => {
			return Tag.render`
				<span class="landing-ui-field-color-tabs-tab-toggler">
					<span class="landing-ui-field-color-tabs-tab-toggler-icon"></span>
					<span class="landing-ui-field-color-tabs-tab-toggler-name">${this.title}</span>
				</span>
			`;
		});
	}

	getLayout(): HTMLElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-color-tabs-tab-content">
					${this.items.map(item => item.getLayout())}
				</div>
			`;
		});
	}

	toggle(): Tab
	{
		Dom.toggleClass(this.getLayout(), Tab.SHOW_CLASS);
		Dom.toggleClass(this.getTitle(), Tab.SHOW_CLASS);
		this.emit('onToggle', {tab: this.title});

		return this;
	}

	show(): Tab
	{
		Dom.addClass(this.getLayout(), Tab.SHOW_CLASS);
		Dom.addClass(this.getTitle(), Tab.SHOW_CLASS);
		this.emit('onShow', {tab: this.title});

		return this;
	}

	hide(): Tab
	{
		Dom.removeClass(this.getLayout(), Tab.SHOW_CLASS);
		Dom.removeClass(this.getTitle(), Tab.SHOW_CLASS);
		this.emit('onHide', {tab: this.title});

		return this;
	}
}
