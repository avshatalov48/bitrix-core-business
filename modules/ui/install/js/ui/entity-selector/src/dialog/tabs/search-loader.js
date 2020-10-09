import { Cache, Tag, Loc, Dom } from 'main.core';
import { Loader } from 'main.loader';
import type Tab from './tab';

export default class SearchLoader
{
	tab: Tab = null;
	loader: Loader = null;
	cache = new Cache.MemoryCache();

	constructor(tab: Tab)
	{
		this.tab = tab;
	}

	getTab(): Tab
	{
		return this.tab;
	}

	getLoader(): Loader
	{
		if (this.loader === null)
		{
			this.loader = new Loader({
				target: this.getIconContainer(),
				size: 32
			});
		}

		return this.loader;
	}

	getContainer()
	{
		return this.cache.remember('container', () => {
			return Tag.render`
				<div class="ui-selector-search-loader">
					${this.getBoxContainer()}
					${this.getSpacerContainer()}
				</div>
			`;
		});
	}

	getBoxContainer()
	{
		return this.cache.remember('box-container', () => {
			return Tag.render`
				<div class="ui-selector-search-loader-box">
					${this.getIconContainer()}
					${this.getTextContainer()}
				</div>`;
		});
	}

	getIconContainer()
	{
		return this.cache.remember('icon', () => {
			return Tag.render`<div class="ui-selector-search-loader-icon"></div>`;
		});
	}

	getTextContainer()
	{
		return this.cache.remember('text', () => {
			return Tag.render`
				<div class="ui-selector-search-loader-text">${
					Loc.getMessage('UI_SELECTOR_SEARCH_LOADER_TEXT')
				}</div>
			`;
		});
	}

	getSpacerContainer()
	{
		return this.cache.remember('spacer', () => {
			return Tag.render`<div class="ui-selector-search-loader-spacer"></div>`;
		});
	}

	show()
	{
		if (!this.getContainer().parentNode)
		{
			Dom.append(this.getContainer(), this.getTab().getContainer());
		}

		this.getLoader().show();

		Dom.addClass(this.getContainer(), 'ui-selector-search-loader--show');
		requestAnimationFrame(() => {
			Dom.addClass(this.getContainer(), 'ui-selector-search-loader--animate');
		});
	}

	hide()
	{
		if (this.loader === null)
		{
			return;
		}

		Dom.removeClass(
			this.getContainer(),
			['ui-selector-search-loader--show', 'ui-selector-search-loader--animate']
		);

		this.getLoader().hide();
	}
}