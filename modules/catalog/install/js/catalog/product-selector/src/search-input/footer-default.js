import { DefaultFooter, Dialog } from 'ui.entity-selector';
import { Dom, Loc, Tag, Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Loader } from 'main.loader';
import { ProductSelector } from 'catalog.product-selector';

export class ProductSearchInputDefaultFooter extends DefaultFooter
{
	#loader: Loader = null;

	constructor(dialog: Dialog, options: { [option: string]: any })
	{
		super(dialog, options);

		this.getDialog().subscribe('onSearch', this.handleOnSearch.bind(this));
	}

	getContent(): HTMLElement
	{
		let phrase = '';

		const isViewCreateButton = this.options.allowCreateItem === true || this.options.allowEditItem === false;

		if (this.isViewEditButton() && isViewCreateButton)
		{
			phrase = Tag.render`
				<div>${Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_1')}</div>
			`;

			const createButton = phrase.querySelector('create-button');
			Dom.replace(createButton, this.#getLabelContainer());

			const changeButton = phrase.querySelector('change-button');
			Dom.replace(changeButton, this.#getSaveContainer());
		}
		else if (this.isViewEditButton())
		{
			phrase = this.#getSaveContainer();
		}
		else
		{
			phrase = this.#getLabelContainer();
		}

		return Tag.render`
			<div class="ui-selector-search-footer-box">
				${phrase}
				${this.#getHintContainer()}
				${this.getLoaderContainer()}
			</div>
		`;
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

		this.getQueryContainer().textContent = ` ${query}`;
	}

	isViewEditButton(): boolean
	{
		return this.options.allowEditItem === true;
	}

	getQueryContainer(): HTMLElement
	{
		return this.cache.remember('name-container', () => {
			return Tag.render`
				<span class="ui-selector-search-footer-query"></span>
			`;
		});
	}

	#getSaveContainer(): HTMLElement
	{
		return this.cache.remember('save-container', () => {
			const className = 'ui-selector-footer-link';

			const messageId = (this.options.inputName === ProductSelector.INPUT_FIELD_BARCODE)
				? 'CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_BARCODE_CHANGE'
				: 'CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CHANGE'
			;

			return Tag.render`
				<span class="${className}" onclick="${this.#onClickSaveChanges.bind(this)}">
					${Loc.getMessage(messageId)}
				</span>
			`;
		});
	}

	#getLoader(): Loader
	{
		if (Type.isNil(this.#loader))
		{
			this.#loader = new Loader({
				target: this.getLoaderContainer(),
				size: 17,
				color: 'rgba(82, 92, 105, 0.9)',
			});
		}

		return this.#loader;
	}

	#showLoader(): void
	{
		void this.#getLoader().show();
	}

	#hideLoader(): void
	{
		void this.#getLoader().hide();
	}

	#getLabelContainer(): HTMLElement
	{
		return this.cache.remember('label', () => {
			return Tag.render`
				<span>
					<span
						onclick="${this.#handleClick.bind(this)}"
						class="ui-selector-footer-link  ui-selector-footer-link-add"
					>
						${
							this.getOption(
								'creationLabel',
								Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_FOOTER_CREATE'),
							)
						}
					</span>
					${this.getQueryContainer()}
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

	#getHintContainer(): ?HTMLElement
	{
		return this.cache.remember('hint', () => {
			let message = null;
			if (!this.options.allowEditItem && !this.options.allowCreateItem)
			{
				message = Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_FOOTER_ALL_HINT', {
					'#ADMIN_HINT#': this.#getErrorAdminHint(),
				});
			}
			else if (!this.options.allowEditItem)
			{
				message = Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_FOOTER_EDIT_HINT', {
					'#ADMIN_HINT#': this.#getErrorAdminHint(),
				});
			}
			else if (!this.options.allowCreateItem)
			{
				message = Loc.getMessage('CATALOG_SELECTOR_SEARCH_POPUP_DISABLED_FOOTER_ADD_HINT', {
					'#ADMIN_HINT#': this.#getErrorAdminHint(),
				});
			}

			if (!message)
			{
				return null;
			}

			const hintNode = Tag.render`<span class="ui-btn ui-btn-icon-lock ui-btn-link"></span>`;
			hintNode.dataset.hint = message;
			hintNode.dataset.hintNoIcon = true;

			BX.UI.Hint.initNode(hintNode);

			return Tag.render`<div class="product-search-selector-disabled-footer-hint">${hintNode}</div>`;
		});
	}

	#onClickSaveChanges(): void
	{
		if (!this.options.allowEditItem)
		{
			return;
		}

		const dialog = this.getDialog();

		dialog.emit('ChangeItem:onClick', { query: dialog.getSearchTab().getLastSearchQuery().query });
		dialog.clearSearch();
		dialog.hide();
	}

	#createItem(event: UIEvent): void
	{
		if (!this.options.allowCreateItem)
		{
			return;
		}

		const tagSelector = this.getDialog().getTagSelector();
		if (tagSelector && tagSelector.isLocked())
		{
			return;
		}

		const finalize = () => {
			this.#hideLoader();
			if (this.getDialog().getTagSelector())
			{
				this.getDialog().getTagSelector().unlock();
				this.getDialog().focusSearch();
			}
		};

		event.preventDefault();
		this.#showLoader();

		if (tagSelector)
		{
			tagSelector.lock();
		}

		this.getDialog()
			.emitAsync('Search:onItemCreateAsync', {
				searchQuery: this.getDialog().getActiveTab().getLastSearchQuery(),
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

	#handleClick(event: UIEvent): void
	{
		this.#createItem(event);
	}

	#getErrorAdminHint(): string
	{
		return this.options.errorAdminHint || '';
	}
}
