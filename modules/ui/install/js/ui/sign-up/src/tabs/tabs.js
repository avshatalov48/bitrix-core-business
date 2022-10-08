import {Cache, Tag, Dom, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Tab} from './tab';
import type {TabsOptions} from './types/tabs-options';

import './css/tabs.css';

export class Tabs extends EventEmitter
{
	cache = new Cache.MemoryCache();

	constructor(options: TabsOptions = {})
	{
		super();
		this.setEventNamespace('BX.UI.SignUp.Tabs');
		this.subscribeFromOptions(options.events);
		this.setOptions(options);

		this.onTabHeaderClick = this.onTabHeaderClick.bind(this);

		const {defaultState} = this.getOptions();

		if (Type.isStringFilled(defaultState))
		{
			const currentTab: Tab = this.getTabs().find((tab) => {
				return tab.getOptions().id === defaultState;
			});

			if (currentTab)
			{
				this.setCurrentTab(currentTab);
				currentTab.activate();
			}
			else
			{
				const [firstTab: Tab] = this.getTabs();
				this.setCurrentTab(firstTab);
				firstTab.activate();
			}
		}
		else
		{
			const [firstTab: Tab] = this.getTabs();
			this.setCurrentTab(firstTab);
			firstTab.activate();
		}
	}

	getCurrentTab(): Tab
	{
		return this.cache.get('currentTab');
	}

	setCurrentTab(tab: Tab)
	{
		this.cache.set('currentTab', tab);
	}

	setOptions(options: TabsOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): TabsOptions
	{
		return this.cache.get('options', {});
	}

	getTabs(): Array<Tab>
	{
		return this.cache.remember('tabs', () => {
			return this.getOptions().tabs.map((options) => {
				return new Tab({
					...options,
					events: {
						onHeaderClick: this.onTabHeaderClick
					},
				});
			});
		});
	}

	onTabHeaderClick(event: BaseEvent)
	{
		const targetTab = event.getTarget();
		this.setCurrentTab(targetTab);

		this.getTabs().forEach((tab) => {
			tab.deactivate();
		});

		targetTab.activate();

		Dom.replace(this.getBodyLayout().firstElementChild, targetTab.getContent().getLayout())
	}

	getHeaderLayout(): HTMLDivElement
	{
		return this.cache.remember('headerLayout', () => {
			return Tag.render`
				<div class="ui-sign-up-tabs-header">
					${this.getTabs().map((tab) => tab.getHeaderLayout())}
				</div>
			`;
		});
	}

	getBodyLayout(): HTMLDivElement
	{
		return this.cache.remember('bodyLayout', () => {
			return Tag.render`
				<div class="ui-sign-up-tabs-body">
					${this.getCurrentTab().getContent().getLayout()}
				</div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-sign-up-tabs">
					${this.getHeaderLayout()}
					${this.getBodyLayout()}
				</div>
			`;
		});
	}
}