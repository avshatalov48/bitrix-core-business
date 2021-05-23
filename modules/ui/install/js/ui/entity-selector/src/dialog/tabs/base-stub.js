import { Cache, Dom, Tag, Type } from 'main.core';
import type Tab from '../tabs/tab';

export default class BaseStub
{
	tab: Tab = null;
	autoShow: boolean = true;
	cache = new Cache.MemoryCache();
	content: HTMLElement = null;

	constructor(tab: Tab, options: { [option: string]: any })
	{
		this.options = Type.isPlainObject(options) ? options : {};
		this.tab = tab;
		this.autoShow = this.getOption('autoShow', true);
	}

	/**
	 * @abstract
	 */
	render(): HTMLElement
	{
		throw new Error('You must implement render() method.');
	}

	getTab(): Tab
	{
		return this.tab;
	}

	getOuterContainer()
	{
		return this.cache.remember('outer-container', () => {
			return Tag.render`
				<div class="ui-selector-tab-stub">${this.render()}</div>
			`;
		});
	}

	isAutoShow(): boolean
	{
		return this.autoShow;
	}

	show(): void
	{
		Dom.append(this.getOuterContainer(), this.getTab().getContainer());
		/*requestAnimationFrame(() => {
			Dom.addClass(this.getOuterContainer(), 'ui-selector-tab-stub--show');
		});*/
	}

	hide(): void
	{
		// Dom.removeClass(this.getOuterContainer(), 'ui-selector-tab-stub--show');
		Dom.remove(this.getOuterContainer());
	}

	getOptions(): { [option: string]: any }
	{
		return this.options;
	}

	getOption(option: string, defaultValue?: any): any
	{
		if (!Type.isUndefined(this.options[option]))
		{
			return this.options[option];
		}
		else if (!Type.isUndefined(defaultValue))
		{
			return defaultValue;
		}

		return null;
	}
}