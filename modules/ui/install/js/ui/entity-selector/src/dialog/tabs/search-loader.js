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

	getContainer(): HTMLElement
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

	getBoxContainer(): HTMLElement
	{
		return this.cache.remember('box-container', () => {
			return Tag.render`
				<div class="ui-selector-search-loader-box">
					${this.getIconContainer()}
					${this.getTextContainer()}
				</div>`;
		});
	}

	getIconContainer(): HTMLElement
	{
		return this.cache.remember('icon', () => {
			return Tag.render`<div class="ui-selector-search-loader-icon"></div>`;
		});
	}

	getTextContainer(): HTMLElement
	{
		return this.cache.remember('text', () => {
			return Tag.render`
				<div class="ui-selector-search-loader-text">${
					Loc.getMessage('UI_SELECTOR_SEARCH_LOADER_TEXT')
				}</div>
			`;
		});
	}

	getSpacerContainer(): HTMLElement
	{
		return this.cache.remember('spacer', () => {
			return Tag.render`<div class="ui-selector-search-loader-spacer"></div>`;
		});
	}

	show(): void
	{
		if (!this.getContainer().parentNode)
		{
			Dom.append(this.getContainer(), this.getTab().getContainer());
		}

		void this.getLoader().show();

		Dom.addClass(this.getContainer(), 'ui-selector-search-loader--show');
		requestAnimationFrame(() => {
			Dom.addClass(this.getContainer(), 'ui-selector-search-loader--animate');
		});
	}

	hide(): void
	{
		if (this.loader === null)
		{
			return;
		}

		Dom.removeClass(
			this.getContainer(),
			['ui-selector-search-loader--show', 'ui-selector-search-loader--animate']
		);

		void this.getLoader().hide();
	}

	isShown(): boolean
	{
		return this.loader !== null && this.loader.isShown();
	}
}