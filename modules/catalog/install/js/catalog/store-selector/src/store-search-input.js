import {ajax, Browser, Cache, Dom, Event, Loc, Tag, Text, Type} from 'main.core';
import {Dialog} from 'ui.entity-selector';
import './component.css';
import {StoreSelector} from 'catalog.store-selector';
import 'ui.notification';

export class StoreSearchInput
{
	selector: StoreSelector;
	cache = new Cache.MemoryCache();

	constructor(id, options = {})
	{
		this.id = id || Text.getRandom();
		this.selector = options.selector;
		if (!(this.selector instanceof StoreSelector))
		{
			throw new Error('Store selector instance not found.');
		}

		this.isEnabledDetailLink = options.isEnabledDetailLink;
		this.inputName = options.inputName || '';
	}

	getId()
	{
		return this.id;
	}

	toggleIcon(icon, value)
	{
		if (Type.isDomNode(icon))
		{
			Dom.style(icon, 'display', value);
		}
	}

	getNameBlock(): HTMLElement
	{
		return this.cache.remember('nameBlock', () => {
			return Tag.render`
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
					${this.getNameInput()}
					${this.getHiddenNameInput()}
				</div>
			`;
		});
	}

	getNameInput(): HTMLInputElement
	{
		return this.cache.remember('nameInput', () => {
			return Tag.render`
				<input type="text"
					class="ui-ctl-element ui-ctl-textbox"
					autocomplete="off"
					value="${Text.encode(this.selector.getStoreTitle())}"
					placeholder="${Text.encode(this.getPlaceholder())}"
					title="${Text.encode(this.selector.getStoreTitle())}"
					onchange="${this.handleNameInputHiddenChange.bind(this)}"
				>
			`;
		});
	}

	getHiddenNameInput(): HTMLInputElement
	{
		return this.cache.remember('hiddenNameInput', () => {
			return Tag.render`
				<input
				 	type="hidden"
					name="${Text.encode(this.inputName)}"
					value="${Text.encode(this.selector.getStoreTitle())}"
				>
			`;
		});
	}

	handleNameInputHiddenChange(event: UIEvent)
	{
		this.getHiddenNameInput().value = event.target.value;
	}

	getClearIcon(): HTMLElement
	{
		return this.cache.remember('closeIcon', () => {
			return Tag.render`
				<button
					class="ui-ctl-after ui-ctl-icon-clear"
					onclick="${this.handleClearIconClick.bind(this)}"
				></button>
			`;
		});
	}

	getArrowIcon(): HTMLElement
	{
		return this.cache.remember('arrowIcon', () => {
			return Tag.render`
				<a
					href="${this.selector.getDetailPath()}"
					target="_blank"
					class="ui-ctl-after ui-ctl-icon-forward"
				></button>
			`;
		});
	}

	getSearchIcon(): HTMLElement
	{
		return this.cache.remember('searchIcon', () => {
			return Tag.render`
				<button
					class="ui-ctl-after ui-ctl-icon-search"
					onclick="${this.handleSearchIconClick.bind(this)}"
				></button>
			`;
		});
	}

	clearInputCache()
	{
		this.cache.delete('dialog');
		this.cache.delete('nameBlock');
		this.cache.delete('nameInput');
		this.cache.delete('hiddenNameInput');
	}

	clearDialogCache()
	{
		this.cache.delete('dialog');
	}

	layout(): HTMLElement
	{
		this.clearInputCache();
		const block = Tag.render`<div class="ui-ctl ui-ctl-w100 ui-ctl-after-icon"></div>`;

		block.appendChild(this.getSearchIcon());
		this.toggleIcon(this.getSearchIcon(), 'none');

		block.appendChild(this.getClearIcon());
		this.toggleIcon(this.getClearIcon(), 'none');

		if (this.showDetailLink() && Type.isStringFilled(this.selector.getStoreTitle()))
		{
			this.toggleIcon(this.getArrowIcon(), 'block');
			block.appendChild(this.getArrowIcon());
		}
		else
		{
			this.toggleIcon(this.getSearchIcon(), 'block');
		}

		Event.bind(this.getNameInput(), 'click', this.handleNameInputClick.bind(this));
		Event.bind(this.getNameInput(), 'input', this.handleNameInput.bind(this));
		Event.bind(this.getNameInput(), 'blur', this.handleNameInputBlur.bind(this));
		Event.bind(this.getNameInput(), 'keydown', this.handleNameInputKeyDown.bind(this));

		block.appendChild(this.getNameBlock());
		return block;
	}

	handleNameInputClick(event: UIEvent)
	{
		this.searchInDialog(event.target.value);
		this.handleIconsSwitchingOnNameInput(event);
	}

	handleNameInput(event: UIEvent)
	{
		this.searchInDialog(event.target.value);
		this.handleIconsSwitchingOnNameInput(event);
	}

	showDetailLink(): string
	{
		return this.isEnabledDetailLink;
	}

	getDialog(): ?Dialog
	{
		return this.cache.remember('dialog', () => {
			const params = {
				id: this.id + '_store',
				height: 300,
				context: 'catalog-store',
				targetNode: this.getNameInput(),
				enableSearch: false,
				multiple: false,
				dropdownMode: true,
				searchTabOptions: {
					stub: true,
					stubOptions: {
						title: Tag.message`${'CATALOG_STORE_SELECTOR_IS_EMPTY_TITLE'}`,
						subtitle: Tag.message`${'CATALOG_STORE_SELECTOR_IS_EMPTY_SUBTITLE'}`,
						arrow: true
					}
				},
				events: {
					'Item:onSelect': this.onStoreSelect.bind(this),
					'onSearch': this.onSearch.bind(this),
					'Search:onItemCreateAsync': this.createStore.bind(this),
				},
				entities: [
					{
						id: 'store',
						options: {
							productId: this.selector.getProductId(),
						},
						searchFields: [
							{ name: 'subtitle', type: 'string', system: true, searchable: false },
						],
						dynamicLoad: true,
						dynamicSearch: true,
					}
				],
				searchOptions: {
					allowCreateItem: true
				},
			};

			return new Dialog(params);
		});
	}

	handleNameInputKeyDown(event: KeyboardEvent): void
	{
		const dialog = this.getDialog();
		if (event.key === 'Enter' && dialog.getActiveTab() === dialog.getSearchTab())
		{
			// prevent a form submit
			event.preventDefault();

			if ((Browser.isMac() && event.metaKey) || event.ctrlKey)
			{
				dialog.getSearchTab().getFooter().createItem();
			}
		}
	}

	handleIconsSwitchingOnNameInput(event: UIEvent): void
	{
		this.toggleIcon(this.getArrowIcon(), 'none');

		if (Type.isStringFilled(event.target.value))
		{
			this.toggleIcon(this.getClearIcon(), 'block');
			this.toggleIcon(this.getSearchIcon(), 'none');
		}
		else
		{
			this.toggleIcon(this.getClearIcon(), 'none');
			this.toggleIcon(this.getSearchIcon(), 'block');
		}
	}

	handleClearIconClick(event: UIEvent)
	{
		this.selector.onClear();

		event.stopPropagation();
		event.preventDefault();
	}

	focusName()
	{
		requestAnimationFrame(() => this.getNameInput().focus());
	}

	searchInDialog(searchQuery: string = '')
	{
		const dialog = this.getDialog();
		if (dialog)
		{
			dialog.show();
			dialog.search(searchQuery);
		}
	}

	handleShowSearchDialog(event: UIEvent)
	{
		this.searchInDialog(event.target.value);
	}

	handleNameInputBlur(event: UIEvent)
	{
		// timeout to toggle clear icon handler while cursor is inside of name input
		setTimeout(() => {
			this.toggleIcon(this.getClearIcon(), 'none');

			if (this.showDetailLink() && Type.isStringFilled(this.selector.getStoreTitle()))
			{
				this.toggleIcon(this.getSearchIcon(), 'none');
				this.toggleIcon(this.getArrowIcon(), 'block');
			}
			else
			{
				this.toggleIcon(this.getArrowIcon(), 'none');
				this.toggleIcon(this.getSearchIcon(), 'block');
			}
		}, 200);
	}

	handleSearchIconClick(event: UIEvent)
	{
		this.searchInDialog();
		this.focusName();

		event.stopPropagation();
		event.preventDefault();
	}

	onSearch(event)
	{
		const { query } = event.getData();
		if (query === '' || query === this.selector.getStoreTitle())
		{
			event.target?.searchTab?.getFooter()?.hide();
		}
		else
		{
			event.target?.searchTab?.getFooter()?.show();
		}
	}

	onStoreSelect(event)
	{
		const item = event.getData().item;
		item.getDialog().getTargetNode().value = item.getTitle();

		if (this.selector)
		{
			this.selector.onStoreSelect(item.getId(), item.getTitle());
		}
		this.toggleIcon(this.getSearchIcon(), 'none');
		this.selector.clearLayout();
		this.selector.layout();

		this.cache.delete('dialog');
	}

	createStore(event): Promise
	{
		const {searchQuery} = event.getData();
		const name = searchQuery.getQuery();

		return new Promise(
			(resolve, reject) => {
				if (!Type.isStringFilled(name))
				{
					reject();
					return;
				}

				const dialog: Dialog = event.getTarget();
				dialog.showLoader();
				ajax.runAction(
						'catalog.storeSelector.createStore',
						{
							json: {name}
						}
					)
					.then(response => {
						dialog.hideLoader();
						const id = Text.toInteger(response.data.id);
						const item = dialog.addItem({
							id,
							entityId: 'store',
							title: name,
							tabs: dialog.getRecentTab().getId(),
						});

						if (item)
						{
							item.select();
						}

						dialog.hide();
						resolve();
					})
					.catch(() => reject())
				;
			});
	}

	getPlaceholder(): string
	{
		return Loc.getMessage('CATALOG_STORE_SELECTOR_BEFORE_SEARCH_TITLE');
	}
}
