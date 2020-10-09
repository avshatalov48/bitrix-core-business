import { Runtime, ajax as Ajax, Type, Loc } from 'main.core';

import Tab from './tab';
import SearchEngine from '../../search/search-engine';
import MatchResult from '../../search/match-result';
import SearchQuery from '../../search/search-query';
import SearchLoader from './search-loader';
import Item from '../../item/item';

import type { BaseEvent } from 'main.core.events';
import type { ItemOptions } from '../../item/item-options';
import type { TabOptions } from './tab-options';
import type { SearchOptions } from '../search-options';
import type Entity from '../../entity/entity';
import type ItemNode from '../../item/item-node';

export default class SearchTab extends Tab
{
	lastSearchQuery: ?SearchQuery = null;
	loadWithDebounce = Runtime.debounce(this.load, 500, this);
	queryCache = new Set();
	queryXhr = null;
	searchLoader: SearchLoader = new SearchLoader(this);

	allowCreateItem: boolean = false;
	draftItem: ?Item = null;
	draftItemOptions: ItemOptions = {};
	draftItemRender: ?Function = null;

	constructor(tabOptions: TabOptions, searchOptions: SearchOptions)
	{
		const defaults = {
			title: Loc.getMessage('UI_SELECTOR_SEARCH_TAB_TITLE'),
			visible: false,
			stub: true,
			stubOptions: {
				autoShow: false,
				title: Loc.getMessage('UI_SELECTOR_SEARCH_STUB_TITLE'),
				subtitle: Loc.getMessage('UI_SELECTOR_SEARCH_STUB_SUBTITLE')
			}
		};

		const options = Object.assign({}, defaults, tabOptions);
		options.id = 'search';
		options.stubOptions.autoShow = false;

		super(options);

		this.handleOnBeforeItemSelect = this.handleOnBeforeItemSelect.bind(this);
		searchOptions = Type.isPlainObject(searchOptions) ? searchOptions : {};

		this.setAllowCreateItem(searchOptions.allowCreateItem);
		this.setDraftItemOptions(searchOptions.draftItemOptions);
		this.setDraftItemRender(searchOptions.draftItemRender);
	}

	search(query: string)
	{
		this.getSearchLoader().hide();

		const searchQuery = new SearchQuery(query);
		const dynamicEntities = this.getDynamicEntities(searchQuery);
		searchQuery.setDynamicSearchEntities(dynamicEntities);

		if (searchQuery.isEmpty())
		{
			return;
		}

		this.lastSearchQuery = searchQuery;

		let matchResults = [];
		this.getDialog().getItems().forEach(items => {
			matchResults = matchResults.concat(SearchEngine.matchItems(items, searchQuery));
		});

		this.clearResults();

		if (this.canCreateItem())
		{
			this.getRootNode().addItem(this.getDraftItem());

			if (this.getDraftItemRender() !== null)
			{
				this.getDraftItemRender()(this.getDraftItem(), searchQuery);
			}
			else
			{
				this.getDraftItem().setTitle(searchQuery.getQuery());
			}
		}

		this.appendResults(matchResults);

		if (this.getDialog().shouldFocusOnFirst())
		{
			this.getDialog().focusOnFirstNode();
		}

		if (this.shouldLoad(searchQuery))
		{
			this.loadWithDebounce(searchQuery);
			if (!this.isEmptyResult())
			{
				this.getStub().hide();
			}
		}
		else
		{
			this.toggleEmptyResult();
		}
	}

	getLastSearchQuery(): ?SearchQuery
	{
		return this.lastSearchQuery;
	}

	setAllowCreateItem(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.allowCreateItem = flag;

			if (this.getDialog())
			{
				if (flag)
				{
					this.createDraftItem();
				}
				else
				{
					this.removeDraftItem();
				}
			}
		}
	}

	setDraftItemOptions(draftItemOptions: ItemOptions): void
	{
		if (Type.isPlainObject(draftItemOptions))
		{
			this.draftItemOptions = draftItemOptions;
		}
	}

	setDraftItemRender(fn: ?Function): void
	{
		if (Type.isFunction(fn) || fn === null)
		{
			this.draftItemRender = fn;
		}
	}

	getDraftItemRender(): ?Function
	{
		return this.draftItemRender;
	}

	getDraftItemOptions(): ItemOptions
	{
		return this.draftItemOptions;
	}

	/**
	 * @internal
	 */
	createDraftItem()
	{
		if (this.draftItem === null)
		{
			const itemOptions = Object.assign(
				{},
				this.getDraftItemOptions(),
				{
					id: 'draft-item',
					entityId: 'search-tab',
					searchable: false,
					saveable: false,
					avatar:
						'data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2224%22%20height%3D%2224%22%2' +
						'0fill%3D%22none%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%3Cpath%20fill-rule%' +
						'3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20d%3D%22M12%2024c6.627%200%2012-5.373%201' +
						'2-12S18.627%200%2012%200%200%205.373%200%2012s5.373%2012%2012%2012z%22%20fill%3D%22%23' +
						'2FC6F6%22/%3E%3Cpath%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20d%3D%22' +
						'M13%207h-2v4H7v2h4v4h2v-4h4v-2h-4V7z%22%20fill%3D%22%23fff%22/%3E%3C/svg%3E'
				}
			);

			this.draftItem = this.getDialog().addItem(itemOptions);
			this.getDialog().subscribe('Item:onBeforeSelect', this.handleOnBeforeItemSelect);
		}

		return this.draftItem;
	}

	/**
	 * @internal
	 */
	removeDraftItem()
	{
		if (this.draftItem === null)
		{
			return;
		}

		this.getDialog().removeItem(this.draftItem);
		this.getDialog().unsubscribe('Item:onBeforeSelect', this.handleOnBeforeItemSelect);
		this.draftItem = null;
	}

	canCreateItem(): boolean
	{
		return this.allowCreateItem;
	}

	getDraftItem(): ?Item
	{
		if (!this.canCreateItem())
		{
			return null;
		}

		return this.createDraftItem();
	}

	appendResults(matchResults: MatchResult[]): void
	{
		matchResults.sort((a: MatchResult, b: MatchResult) => {
			const contextSortA = a.getItem().getContextSort();
			const contextSortB = b.getItem().getContextSort();

			if (contextSortA !== null && contextSortB === null)
			{
				return -1;
			}
			else if (contextSortA === null && contextSortB !== null)
			{
				return 1;
			}
			else if (contextSortA !== null && contextSortB !== null)
			{
				return contextSortB - contextSortA;
			}
			else
			{
				const globalSortA = a.getItem().getGlobalSort();
				const globalSortB = b.getItem().getGlobalSort();

				if (globalSortA !== null && globalSortB === null)
				{
					return -1;
				}
				else if (globalSortA === null && globalSortB !== null)
				{
					return 1;
				}
				else if (globalSortA !== null && globalSortB !== null)
				{
					return globalSortB - globalSortA;
				}

				return 0;
			}
		});

		this.getRootNode().disableRender();

		matchResults.forEach((matchResult: MatchResult) => {
			const item = matchResult.getItem();
			if (!this.getRootNode().hasItem(item))
			{
				const node = this.getRootNode().addItem(item);
				node.setHighlights(matchResult.getMatchFields());
			}
		});

		this.getRootNode().enableRender();
		this.render();
	}

	getDynamicEntities(searchQuery: SearchQuery): string[]
	{
		const result = [];

		this.getDialog().getEntities().forEach((entity: Entity) => {
			if (entity.isSearchable())
			{
				const hasCacheLimit = entity.getSearchCacheLimits().some((pattern: RegExp) => {
					return pattern.test(searchQuery.getQuery());
				});

				if (hasCacheLimit)
				{
					result.push(entity.getId());
				}
			}
		});

		return result;
	}

	isQueryCacheable(searchQuery: SearchQuery): boolean
	{
		return searchQuery.isCacheable() && !searchQuery.hasDynamicSearch();
	}

	isQueryLoaded(searchQuery: SearchQuery): boolean
	{
		let found = false;
		this.queryCache.forEach(query => {
			if (found === false && searchQuery.getQuery().startsWith(query))
			{
				found = true;
			}
		});

		return found;
	}

	addCacheQuery(searchQuery: SearchQuery): void
	{
		if (this.isQueryCacheable(searchQuery))
		{
			this.queryCache.add(searchQuery.getQuery());
		}
	}

	removeCacheQuery(searchQuery: SearchQuery): void
	{
		this.queryCache.delete(searchQuery.getQuery());
	}

	shouldLoad(searchQuery: SearchQuery): boolean
	{
		if (!this.isQueryCacheable(searchQuery))
		{
			return true;
		}

		if (!this.getDialog().hasDynamicSearch())
		{
			return false;
		}

		return !this.isQueryLoaded(searchQuery);
	}

	load(searchQuery: SearchQuery): void
	{
		if (!this.shouldLoad(searchQuery))
		{
			return;
		}
		/*if (this.queryXhr)
		{
			this.queryXhr.abort();
		}*/

		this.addCacheQuery(searchQuery);

		this.getStub().hide();
		this.getSearchLoader().show();

		Ajax.runAction('ui.entityselector.doSearch', {
				json: {
					dialog: this.getDialog(),
					searchQuery
				},
				onrequeststart: (xhr) => {
					this.queryXhr = xhr;
				},
				getParameters: {
					context: this.getDialog().getContext()
				}
			})
			.then(response => {

				this.getSearchLoader().hide();

				if (!response || !response.data || !response.data.dialog || !response.data.dialog.items)
				{
					this.removeCacheQuery(searchQuery);
					this.toggleEmptyResult();
					return;
				}

				if (response.data.searchQuery && response.data.searchQuery.cacheable === false)
				{
					this.removeCacheQuery(searchQuery);
				}

				if (Type.isArrayFilled(response.data.dialog.items))
				{
					const items = new Set();
					response.data.dialog.items.forEach((itemOptions: ItemOptions) => {
						delete itemOptions.tabs;
						delete itemOptions.children;

						const item = this.getDialog().addItem(itemOptions);
						items.add(item);
					});

					const isTabEmpty = this.isEmptyResult();

					const matchResults = SearchEngine.matchItems(items, this.getLastSearchQuery());
					this.appendResults(matchResults);

					if (isTabEmpty && this.getDialog().shouldFocusOnFirst())
					{
						this.getDialog().focusOnFirstNode();
					}
				}

				this.toggleEmptyResult();
			})
			.catch((error) => {
				this.removeCacheQuery(searchQuery);
				this.getSearchLoader().hide();
				console.error(error);
			});
	}

	getSearchLoader(): SearchLoader
	{
		return this.searchLoader;
	}

	clearResults(): void
	{
		this.getRootNode().removeChildren();
	}

	isEmptyResult(): boolean
	{
		return !this.getRootNode().hasChildren();
	}

	toggleEmptyResult(): void
	{
		if (this.isEmptyResult())
		{
			this.getStub().show();
		}
		else
		{
			this.getStub().hide();
		}
	}

	handleOnBeforeItemSelect(event: BaseEvent): void
	{
		const { item } = event.getData();
		if (item !== this.getDraftItem())
		{
			return;
		}

		const showLoader = () => {
			if (this.getDraftItem())
			{
				this.getDraftItem().getNodes().forEach((node: ItemNode) => {
					node.showLoader();
				});
			}
		};

		const hideLoader = () => {
			if (this.getDraftItem())
			{
				this.getDraftItem().getNodes().forEach((node: ItemNode) => {
					node.hideLoader();
				});
			}
		};

		const finalize = () => {
			hideLoader();
			if (this.getDialog().getTagSelector())
			{
				this.getDialog().getTagSelector().unlock();
				this.getDialog().focusSearch();
			}
		};

		event.preventDefault();
		showLoader();
		this.getDialog().getTagSelector().lock();

		this.getDialog()
			.emitAsync('Search:onItemCreateAsync', {
				draftItem: this.getDraftItem(),
				searchQuery: this.getLastSearchQuery()
			})
			.then(() => {
				this.clearResults();
				this.getDialog().clearSearch();
				if (this.getDialog().getActiveTab() === this)
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
}