import {DefaultFooter, Dialog} from 'ui.entity-selector';
import {ajax, Browser, Dom, Loc, Runtime, Tag, Type, Validation} from 'main.core';
import {BaseEvent, EventEmitter} from "main.core.events";
import type {TabOptions, Tab} from "ui.entity-selector";
import {Loader} from "main.loader";
import {ProductSelector} from "catalog.product-selector";

export default class ProductSearchSelectorFooter extends DefaultFooter
{
	loader: Loader = null;

	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.getDialog().subscribe('onSearch', this.handleOnSearch.bind(this));
	}

	getContent(): HTMLElement
	{
		let phrase = '';

		if (this.options.allowCreateItem === false)
		{
			phrase = this.getSaveContainer();
		}
		else
		{
			phrase = Tag.render`
				<div>${Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER')}</div>
			`;

			const createButton = phrase.querySelector('create-button');
			Dom.replace(createButton, this.getLabelContainer());

			const changeButton = phrase.querySelector('change-button');
			Dom.replace(changeButton, this.getSaveContainer());
		}

		return Tag.render`
			<div class="ui-selector-search-footer-box">
				${phrase}
				${this.getLoaderContainer()}
			</div>
		`;
	}
	getLoader(): Loader
	{
		if (Type.isNil(this.loader))
		{
			this.loader = new Loader({
				target: this.getLoaderContainer(),
				size: 17,
				color: 'rgba(82, 92, 105, 0.9)'
			});
		}

		return this.loader;
	}

	showLoader(): void
	{
		void this.getLoader().show();
	}

	hideLoader(): void
	{
		void this.getLoader().hide();
	}

	setLabel(label: string)
	{
		if (Type.isString(label))
		{
			this.getLabelContainer().textContent = label;
		}
	}

	getLabelContainer(): HTMLElement
	{
		return this.cache.remember('label', () => {
			return Tag.render`
				<span>
					<span onclick="${this.handleClick.bind(this)}" class="ui-selector-footer-link  ui-selector-footer-link-add">
						${
							this.getOption('creationLabel', Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE'))
						}
					</span>
					${this.getQueryContainer()}
				</span>
			`;
		});
	}

	getQueryContainer(): HTMLElement
	{
		return this.cache.remember('name-container', () => {
			return Tag.render`
				<span class="ui-selector-search-footer-query"></span>
			`;
		});
	}

	getSaveContainer(): HTMLElement
	{
		return this.cache.remember('save-container', () => {
			const className = `ui-selector-footer-link`;

			const messageId =
				(this.options.inputName === ProductSelector.INPUT_FIELD_BARCODE)
					? 'CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE_CHANGE'
					: 'CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CHANGE'
			;

			return Tag.render`
			<span class="${className}" onclick="${this.onClickSaveChanges.bind(this)}">
				${Loc.getMessage(messageId)}
			</span>
		`;
		});
	}

	getLoaderContainer(): HTMLElement
	{
		return this.cache.remember('loader', () => {
			return Tag.render`
				<div class="ui-selector-search-footer-loader"></div>
			`;
		});
	}

	onClickSaveChanges()
	{
		const lastQuery = this.getDialog().getActiveTab().getLastSearchQuery();
		this.getDialog().emit('ChangeItem:onClick', { query: lastQuery.query });
		this.getDialog().clearSearch();
		this.getDialog().hide();
	}

	createItem(): void
	{
		const tagSelector = this.getDialog().getTagSelector();
		if (tagSelector && tagSelector.isLocked())
		{
			return;
		}

		const finalize = () => {
			this.hideLoader();
			if (this.getDialog().getTagSelector())
			{
				this.getDialog().getTagSelector().unlock();
				this.getDialog().focusSearch();
			}
		};

		event.preventDefault();
		this.showLoader();

		if (tagSelector)
		{
			tagSelector.lock();
		}

		this.getDialog()
			.emitAsync('Search:onItemCreateAsync', {
				searchQuery: this.getDialog().getActiveTab().getLastSearchQuery()
			})
			.then(() => {
				this.getTab().clearResults();
				this.getDialog().clearSearch();
				if (this.getDialog().getActiveTab() === this.getTab())
				{
					this.getDialog().selectFirstTab();
				}

				finalize();
			})
			.catch(() => {
				finalize();
			})
		;
	}

	handleClick(): void
	{
		this.createItem();
	}

	handleOnSearch(event: BaseEvent): void
	{
		const { query } = event.getData();

		if (this.options.currentValue === query || query === '')
		{
			this.hide();
		}
		else
		{
			this.show();
		}

		if (this.options.allowCreateItem !== false)
		{
			this.getQueryContainer().textContent = query;
		}
	}
}