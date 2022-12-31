import { Type, Text, Tag, Dom, ajax as Ajax, Cache, Loc, Runtime, Reflection } from 'main.core';
import { EventEmitter, BaseEvent } from 'main.core.events';
import { Popup } from 'main.popup';
import { Loader } from 'main.loader';

import Item from '../item/item';
import Tab from './tabs/tab';
import Entity from '../entity/entity';
import TagSelector from '../tag-selector/tag-selector';
import Navigation from './navigation';
import SliderIntegration from './integration/slider-integration';
import Animation from '../common/animation';
import BaseHeader from './header/base-header';
import DefaultHeader from './header/default-header';
import BaseFooter from './footer/base-footer';
import DefaultFooter from './footer/default-footer';

import RecentTab from './tabs/recent-tab';
import SearchTab from './tabs/search-tab';

import type ItemNode from '../item/item-node';
import type { TabOptions } from './tabs/tab-options';
import type { DialogOptions } from './dialog-options';
import type { ItemOptions } from '../item/item-options';
import type { EntityOptions } from '../entity/entity-options';
import type { ItemId } from '../item/item-id';
import type { PopupOptions } from 'main.popup';
import type { HeaderOptions, HeaderContent } from './header/header-content';
import type { FooterOptions, FooterContent } from './footer/footer-content';
import type { ItemNodeOptions } from '../item/item-node-options';

class LoadState
{
	static UNSENT: string = 'UNSENT';
	static LOADING: string = 'LOADING';
	static DONE: string = 'DONE';
}

class TagSelectorMode
{
	static INSIDE: string = 'INSIDE';
	static OUTSIDE: string = 'OUTSIDE';
}

const instances = new Map();

/**
 * @memberof BX.UI.EntitySelector
 */
export default class Dialog extends EventEmitter
{
	id: string = null;
	items: Map<string, Map<string, Item>> = new Map();
	tabs: Map<string, Entity> = new Map();
	entities: Map<string, Entity> = new Map();
	targetNode: HTMLElement = null;
	popup: Popup = null;
	cache = new Cache.MemoryCache();
	multiple: boolean = true;
	hideOnSelect: boolean = null;
	hideOnDeselect: boolean = null;
	clearSearchOnSelect: boolean = true;
	context: string = null;
	selectedItems: Set<Item> = new Set();
	preselectedItems: ItemId[] = [];
	undeselectedItems: ItemId[] = [];
	dropdownMode: boolean = false;

	frozen: boolean = false;
	frozenProps: { [propName: string]: any } = {};

	hideByEsc: boolean = true;
	autoHide: boolean = true;
	autoHideHandler: Function = null;
	offsetTop: number = 5;
	offsetLeft: number = 0;
	cacheable: boolean = true;

	width: number = 565;
	height: number = 420;

	maxLabelWidth: number = 160;
	minLabelWidth: number = 45;

	showAvatars: boolean = true;
	compactView: boolean = false;

	activeTab: Tab = null;
	recentTab: Tab = null;
	searchTab: Tab = null;

	rendered: boolean = false;

	loadState: LoadState = LoadState.UNSENT;
	loader: ?Loader = null;

	tagSelector: ?TagSelector = null;
	tagSelectorMode: ?TagSelectorMode = null;
	tagSelectorHeight: ?number = null;

	saveRecentItemsWithDebounce: Function = Runtime.debounce(this.saveRecentItems, 2000, this);
	recentItemsToSave = [];

	navigation: Navigation = null;
	header: BaseHeader = null;
	footer: BaseFooter = null;
	popupOptions: PopupOptions = {};

	focusOnFirst: boolean = true;
	focusedNode: ItemNode = null;

	clearUnavailableItems: boolean = false;
	overlappingObserver: MutationObserver = null;

	static getById(id: string): ?Dialog
	{
		return instances.get(id) || null;
	}

	static getInstances(): Dialog[]
	{
		return Array.from(instances.values());
	}

	constructor(dialogOptions: DialogOptions)
	{
		super();
		this.setEventNamespace('BX.UI.EntitySelector.Dialog');

		const options: DialogOptions = Type.isPlainObject(dialogOptions) ? dialogOptions : {};
		this.id = Type.isStringFilled(options.id) ? options.id : `ui-selector-${Text.getRandom().toLowerCase()}`;
		this.multiple = Type.isBoolean(options.multiple) ? options.multiple : true;
		this.context = Type.isStringFilled(options.context) ? options.context : null;
		this.clearUnavailableItems = options.clearUnavailableItems === true;
		this.compactView = options.compactView === true;
		this.dropdownMode = Type.isBoolean(options.dropdownMode) ? options.dropdownMode : false;

		if (Type.isArray(options.entities))
		{
			options.entities.forEach((entity) => {
				this.addEntity(entity);
			});
		}

		if (options.tagSelector instanceof TagSelector)
		{
			this.tagSelectorMode = TagSelectorMode.OUTSIDE;
			this.setTagSelector(options.tagSelector);
		}
		else if (options.enableSearch === true)
		{
			const defaultOptions = {
				placeholder: Loc.getMessage('UI_TAG_SELECTOR_SEARCH_PLACEHOLDER'),
				maxHeight: 99,
				textBoxWidth: 105
			};
			const customOptions = Type.isPlainObject(options.tagSelectorOptions) ? options.tagSelectorOptions : {};
			const mandatoryOptions = {
				dialogOptions: null,
				showTextBox: true,
				showAddButton: false,
				showCreateButton: false,
				multiple: this.isMultiple()
			};

			const tagSelectorOptions = Object.assign(defaultOptions, customOptions, mandatoryOptions);
			const tagSelector = new TagSelector(tagSelectorOptions);
			this.tagSelectorMode = TagSelectorMode.INSIDE;
			this.setTagSelector(tagSelector);
		}

		this.setTargetNode(options.targetNode);
		this.setHideOnSelect(options.hideOnSelect);
		this.setHideOnDeselect(options.hideOnDeselect);
		this.setClearSearchOnSelect(options.clearSearchOnSelect);
		this.setWidth(options.width);
		void this.setHeight(options.height);
		this.setAutoHide(options.autoHide);
		this.setAutoHideHandler(options.autoHideHandler);
		this.setHideByEsc(options.hideByEsc);
		this.setOffsetLeft(options.offsetLeft);
		this.setOffsetTop(options.offsetTop);
		this.setCacheable(options.cacheable);
		this.setFocusOnFirst(options.focusOnFirst);
		this.setShowAvatars(options.showAvatars);

		this.recentTab = new RecentTab(this, options.recentTabOptions);
		this.searchTab = new SearchTab(this, options.searchTabOptions, options.searchOptions);

		this.addTab(this.recentTab);
		this.addTab(this.searchTab);

		this.setPreselectedItems(options.preselectedItems);
		this.setUndeselectedItems(options.undeselectedItems);

		this.setOptions(options);

		const preload = options.preload === true || this.getPreselectedItems().length > 0;
		if (preload)
		{
			this.load();
		}

		if (Type.isPlainObject(options.popupOptions))
		{
			const allowedOptions = ['overlay', 'bindOptions', 'targetContainer', 'zIndexOptions'];
			const popupOptions = {};

			Object.keys(options.popupOptions).forEach((option: string) => {
				if (allowedOptions.includes(option))
				{
					popupOptions[option] = options.popupOptions[option];
				}
			});

			this.popupOptions = popupOptions;
		}

		this.navigation = new Navigation(this);

		(new SliderIntegration(this));

		this.subscribe('ItemNode:onFocus', this.handleItemNodeFocus.bind(this));
		this.subscribe('ItemNode:onUnfocus', this.handleItemNodeUnfocus.bind(this));

		this.subscribeFromOptions(options.events);

		instances.set(this.id, this);
	}

	show(): void
	{
		this.load();
		this.getPopup().show();
	}

	hide(): void
	{
		this.getPopup().close();
	}

	destroy(): void
	{
		if (this.destroying)
		{
			return;
		}

		this.destroying = true;
		this.emit('onDestroy');

		this.disconnectTabOverlapping();
		instances.delete(this.getId());
		if (this.isRendered())
		{
			this.getPopup().destroy();
		}

		for (const property in this)
		{
			if (this.hasOwnProperty(property))
			{
				delete this[property];
			}
		}

		Object.setPrototypeOf(this, null);
	}

	isOpen(): boolean
	{
		return this.popup && this.popup.isShown();
	}

	adjustPosition(): void
	{
		if (this.isRendered())
		{
			this.getPopup().adjustPosition();
		}
	}

	search(queryString: string): void
	{
		const query = Type.isStringFilled(queryString) ? queryString.trim() : '';

		const event = new BaseEvent({ data: { query } });
		this.emit('onBeforeSearch', event);
		if (event.isDefaultPrevented())
		{
			return;
		}

		if (!Type.isStringFilled(query))
		{
			this.selectFirstTab();
			if (this.getSearchTab())
			{
				this.getSearchTab().clearResults();
			}
		}
		else if (this.getSearchTab())
		{
			this.selectTab(this.getSearchTab().getId());
			this.getSearchTab().search(query);
		}

		this.emit('onSearch', { query });
	}

	addItem(options: ItemOptions): Item
	{
		if (!Type.isPlainObject(options))
		{
			throw new Error('EntitySelector.addItem: wrong item options.');
		}

		let item = this.getItem(options);
		if (!item)
		{
			item = new Item(options);

			const undeselectable = this.getUndeselectedItems().some((itemId: ItemId) => {
				return itemId[0] === item.getEntityId() && String(itemId[1]) === String(item.getId());
			});

			if (undeselectable)
			{
				item.setDeselectable(false);
			}

			item.setDialog(this);

			const entity = this.getEntity(item.getEntityId());
			if (entity === null)
			{
				this.addEntity({ id: item.getEntityId() });
			}

			let entityItems = this.items.get(item.getEntityId());
			if (!entityItems)
			{
				entityItems = new Map();
				this.items.set(item.getEntityId(), entityItems);
			}

			entityItems.set(String(item.getId()), item);

			if (item.isSelected())
			{
				this.handleItemSelect(item);
			}
		}

		let tabs = [];
		if (Type.isArray(options.tabs))
		{
			tabs = options.tabs;
		}
		else if (Type.isStringFilled(options.tabs))
		{
			tabs = [options.tabs];
		}

		const children = Type.isArray(options.children) ? options.children : [];

		tabs.forEach((tabId) => {
			const tab = this.getTab(tabId);
			if (tab)
			{
				const itemNode = tab.getRootNode().addItem(item, options.nodeOptions);
				itemNode.addChildren(children);
			}
		});

		return item;
	}

	removeItem(item: Item | ItemOptions): ?Item
	{
		item = this.getItem(item);
		if (item)
		{
			this.handleItemDeselect(item);

			item.getNodes().forEach((node: ItemNode) => {
				node.getParentNode().removeChild(node);
			});

			const entityItems = this.getEntityItemsInternal(item.getEntityId());
			if (entityItems)
			{
				entityItems.delete(String(item.getId()));
				if (entityItems.size === 0)
				{
					this.items.delete(item.getEntityId());
				}
			}
		}

		return item;
	}

	removeItems(): void
	{
		this.getItemsInternal().forEach((items: Map<string, Item>) => {
			items.forEach((item: Item) => {
				this.removeItem(item);
			});
		});
	}

	getItem(item: ItemId | Item | ItemOptions): ?Item
	{
		let id = null;
		let entityId = null;

		if (Type.isArray(item) && item.length === 2)
		{
			[entityId, id] = item;
		}
		else if (item instanceof Item)
		{
			id = item.getId();
			entityId = item.getEntityId();
		}
		else if (Type.isObjectLike(item))
		{
			({ id, entityId } = item);
		}

		const entityItems = this.getEntityItemsInternal(entityId);
		if (entityItems)
		{
			return entityItems.get(String(id)) || null;
		}

		return null;
	}

	getSelectedItems(): Item[]
	{
		return Array.from(this.selectedItems);
	}

	getItems(): Item[]
	{
		const items = [];
		this.getItemsInternal().forEach((entityItems: Map<string, Item>) => {
			Array.prototype.push.apply(items, Array.from(entityItems.values()));
		});

		return items;
	}

	/**
	 * @internal
	 */
	getItemsInternal(): Map<string, Map<string, Item>>
	{
		return this.items;
	}

	getEntityItems(entityId: string): Item[]
	{
		const items = this.getEntityItemsInternal(entityId);

		return items === null ? [] : Array.from(items.values());
	}

	/**
	 * @internal
	 */
	getEntityItemsInternal(entityId: string): Map<string, Item> | null
	{
		return this.items.get(entityId) || null;
	}

	/**
	 * @private
	 */
	validateItemIds(itemIds: ItemId[]): ItemId[]
	{
		if (!Type.isArrayFilled(itemIds))
		{
			return [];
		}

		const result = [];
		itemIds.forEach((itemId: ItemId) => {
			if (!Type.isArray(itemId) || itemId.length !== 2)
			{
				return;
			}

			const [entityId, id] = itemId;

			if (Type.isStringFilled(entityId) && (Type.isStringFilled(id) || Type.isNumber(id)))
			{
				result.push(itemId);
			}
		});

		return result;
	}

	addTab(tab: Tab | TabOptions): Tab
	{
		if (Type.isPlainObject(tab))
		{
			tab = new Tab(this, tab);
		}

		if (!(tab instanceof Tab))
		{
			throw new Error('EntitySelector: a tab must be an instance of EntitySelector.Tab.');
		}

		if (this.getTab(tab.getId()))
		{
			console.error(`EntitySelector: the "${tab.getId()}" tab is already existed.`);
			return tab;
		}

		tab.setDialog(this);
		this.tabs.set(tab.getId(), tab);

		if (this.isRendered())
		{
			this.insertTab(tab);
		}

		return tab;
	}

	getTabs(): Tab[]
	{
		return Array.from(this.tabs.values());
	}

	getTab(id: string): ?Tab
	{
		return this.tabs.get(id) || null;
	}

	getRecentTab(): RecentTab
	{
		return this.recentTab;
	}

	getSearchTab(): SearchTab
	{
		return this.searchTab;
	}

	selectTab(id: string): ?Tab
	{
		const newActiveTab = this.getTab(id);
		if (!newActiveTab || newActiveTab === this.getActiveTab())
		{
			return newActiveTab;
		}

		if (this.getActiveTab())
		{
			this.getActiveTab().deselect();
		}

		this.activeTab = newActiveTab;
		newActiveTab.select();

		if (!newActiveTab.isRendered())
		{
			newActiveTab.render();
		}

		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				this.focusSearch();
			});
		});

		this.clearNodeFocus();
		if (this.shouldFocusOnFirst())
		{
			this.focusOnFirstNode();
		}

		this.adjustHeader();
		this.adjustFooter();

		return newActiveTab;
	}

	/**
	 * @private
	 */
	insertTab(tab: Tab): void
	{
		tab.renderLabel();
		tab.renderContainer();

		Dom.append(tab.getLabelContainer(), this.getLabelsContainer());
		Dom.append(tab.getContainer(), this.getTabContentsContainer());

		if (tab.getHeader())
		{
			Dom.append(tab.getHeader().getContainer(), this.getHeaderContainer());
		}

		if (tab.getFooter())
		{
			Dom.append(tab.getFooter().getContainer(), this.getFooterContainer());
		}
	}

	selectFirstTab(onlyVisible = true): ?Tab
	{
		const tabs = this.getTabs();
		for (let i = 0; i < tabs.length; i++)
		{
			const tab = tabs[i];
			if (onlyVisible === false || tab.isVisible())
			{
				return this.selectTab(tab.getId());
			}
		}

		if (this.isDropdownMode())
		{
			return this.selectTab(this.getRecentTab().getId());
		}

		return null;
	}

	selectLastTab(onlyVisible = true): ?Tab
	{
		const tabs = this.getTabs();
		for (let i = tabs.length - 1; i >= 0; i--)
		{
			const tab = tabs[i];
			if (onlyVisible === false || tab.isVisible())
			{
				return this.selectTab(tab.getId());
			}
		}

		if (this.isDropdownMode())
		{
			return this.selectTab(this.getRecentTab().getId());
		}

		return null;
	}

	getActiveTab(): ?Tab
	{
		return this.activeTab;
	}

	getNextTab(onlyVisible = true): ?Tab
	{
		let nextTab = null;
		let activeFound = false;
		const tabs = this.getTabs();
		for (let i =  0; i < tabs.length; i++)
		{
			const tab = tabs[i];
			if (onlyVisible && !tab.isVisible())
			{
				continue;
			}

			if (tab === this.getActiveTab())
			{
				activeFound = true;
			}
			else if (activeFound)
			{
				nextTab = tab;
				break;
			}
		}

		return nextTab;
	}

	getPreviousTab(onlyVisible = true): ?Tab
	{
		let previousTab = null;
		let activeFound = false;
		const tabs = this.getTabs();
		for (let i = tabs.length - 1; i >= 0; i--)
		{
			const tab = tabs[i];
			if (onlyVisible && !tab.isVisible())
			{
				continue;
			}

			if (tab === this.getActiveTab())
			{
				activeFound = true;
			}
			else if (activeFound)
			{
				previousTab = tab;
				break;
			}
		}

		return previousTab;
	}

	removeTab(id: string): void
	{
		const tab = this.getTab(id);
		if (!tab)
		{
			return;
		}

		tab.getRootNode().removeChildren();

		this.tabs.delete(id);

		Dom.remove(tab.getLabelContainer(), this.getLabelsContainer());
		Dom.remove(tab.getContainer(), this.getTabContentsContainer());

		if (tab.getHeader())
		{
			Dom.remove(tab.getHeader().getContainer(), this.getHeaderContainer());
		}

		if (tab.getFooter())
		{
			Dom.remove(tab.getFooter().getContainer(), this.getFooterContainer());
		}

		this.selectFirstTab();
	}

	addEntity(entity: Entity | EntityOptions): Entity
	{
		if (Type.isPlainObject(entity))
		{
			entity = new Entity(entity);
		}

		if (!(entity instanceof Entity))
		{
			throw new Error('EntitySelector: an entity must be an instance of EntitySelector.Entity.');
		}

		if (this.hasEntity(entity.getId()))
		{
			console.error(`EntitySelector: the "${entity.getId()}" entity is already existed.`);
			return entity;
		}

		this.entities.set(entity.getId(), entity);

		return entity;
	}

	getEntity(id: string): ?Entity
	{
		return this.entities.get(id) || null;
	}

	hasEntity(id: string): boolean
	{
		return this.entities.has(id);
	}

	getEntities(): Entity[]
	{
		return Array.from(this.entities.values());
	}

	removeEntity(id: string): void
	{
		this.removeEntityItems(id);
		this.entities.delete(id);
	}

	removeEntityItems(id: string): void
	{
		const items = this.getEntityItemsInternal(id);
		if (items)
		{
			items.forEach((item: Item) => {
				this.removeItem(item);
			});
		}
	}

	getHeader(): ?BaseHeader
	{
		return this.header;
	}

	getActiveHeader(): ?BaseHeader
	{
		if (!this.getActiveTab())
		{
			return null;
		}

		if (this.getActiveTab().getHeader())
		{
			return this.getActiveTab().getHeader();
		}

		return this.getHeader() && this.getActiveTab().canShowDefaultHeader() ? this.getHeader() : null;
	}

	/**
	 * @internal
	 */
	adjustHeader(): void
	{
		if (!this.getActiveTab())
		{
			return;
		}

		if (this.getActiveTab().getHeader())
		{
			if (this.getHeader())
			{
				this.getHeader().hide();
			}

			this.getActiveTab().getHeader().show();
		}
		else
		{
			if (this.getHeader())
			{
				if (this.getActiveTab().canShowDefaultHeader())
				{
					this.getHeader().show();
				}
				else
				{
					this.getHeader().hide();
				}
			}
		}
	}

	setHeader(headerContent: ?HeaderContent, headerOptions?: HeaderOptions): ?BaseHeader
	{
		/** @var {BaseHeader} */
		let header = null;
		if (headerContent !== null)
		{
			header = this.constructor.createHeader(this, headerContent, headerOptions);
			if (header === null)
			{
				return null;
			}
		}

		if (this.isRendered() && this.getHeader() !== null)
		{
			Dom.remove(this.getHeader().getContainer());
			this.adjustHeader();
		}

		this.header = header;

		if (this.isRendered())
		{
			this.appendHeader(header);
			this.adjustHeader();
		}

		return header;
	}

	/**
	 * @internal
	 */
	appendHeader(header: BaseHeader): void
	{
		if (header instanceof BaseHeader)
		{
			Dom.append(header.getContainer(), this.getHeaderContainer());
		}
	}

	/**
	 * @internal
	 */
	static createHeader(context: Dialog | Tab, headerContent: HeaderContent, headerOptions?: HeaderOptions): ?BaseHeader
	{
		if (
			!Type.isStringFilled(headerContent) &&
			!Type.isArrayFilled(headerContent) &&
			!Type.isDomNode(headerContent) &&
			!Type.isFunction(headerContent)
		)
		{
			return null;
		}

		/** @var {BaseHeader} */
		let header = null;
		const options = Type.isPlainObject(headerOptions) ? headerOptions : {};

		if (Type.isFunction(headerContent) || Type.isString(headerContent))
		{
			const className = Type.isString(headerContent) ? Reflection.getClass(headerContent) : headerContent;
			if (Type.isFunction(className))
			{
				header = new className(context, options);
				if (!(header instanceof BaseHeader))
				{
					console.error('EntitySelector: header is not an instance of BaseHeader.');
					header = null;
				}
			}
		}

		if (headerContent !== null && !header)
		{
			header = new DefaultHeader(context, Object.assign({}, options, { content: headerContent }));
		}

		return header;
	}

	getFooter(): ?BaseFooter
	{
		return this.footer;
	}

	getActiveFooter(): ?BaseFooter
	{
		if (!this.getActiveTab())
		{
			return null;
		}

		if (this.getActiveTab().getFooter())
		{
			return this.getActiveTab().getFooter();
		}

		return this.getFooter() && this.getActiveTab().canShowDefaultFooter() ? this.getFooter() : null;
	}

	/**
	 * @internal
	 */
	adjustFooter(): void
	{
		if (!this.getActiveTab())
		{
			return;
		}

		if (this.getActiveTab().getFooter())
		{
			if (this.getFooter())
			{
				this.getFooter().hide();
			}

			this.getActiveTab().getFooter().show();
		}
		else
		{
			if (this.getFooter())
			{
				if (this.getActiveTab().canShowDefaultFooter())
				{
					this.getFooter().show();
				}
				else
				{
					this.getFooter().hide();
				}
			}
		}
	}

	setFooter(footerContent: ?FooterContent, footerOptions?: FooterOptions): ?BaseFooter
	{
		/** @var {BaseFooter} */
		let footer = null;
		if (footerContent !== null)
		{
			footer = this.constructor.createFooter(this, footerContent, footerOptions);
			if (footer === null)
			{
				return null;
			}
		}

		if (this.isRendered() && this.getFooter() !== null)
		{
			Dom.remove(this.getFooter().getContainer());
			this.adjustFooter();
		}

		this.footer = footer;

		if (this.isRendered())
		{
			this.appendFooter(footer);
			this.adjustFooter();
		}

		return footer;
	}

	/**
	 * @internal
	 */
	appendFooter(footer: BaseFooter): void
	{
		if (footer instanceof BaseFooter)
		{
			Dom.append(footer.getContainer(), this.getFooterContainer());
		}
	}

	/**
	 * @internal
	 */
	static createFooter(context: Dialog | Tab, footerContent: FooterContent, footerOptions?: FooterOptions): ?BaseFooter
	{
		if (
			!Type.isStringFilled(footerContent) &&
			!Type.isArrayFilled(footerContent) &&
			!Type.isDomNode(footerContent) &&
			!Type.isFunction(footerContent)
		)
		{
			return null;
		}

		/** @var {BaseFooter} */
		let footer = null;
		const options = Type.isPlainObject(footerOptions) ? footerOptions : {};

		if (Type.isFunction(footerContent) || Type.isString(footerContent))
		{
			const className = Type.isString(footerContent) ? Reflection.getClass(footerContent) : footerContent;
			if (Type.isFunction(className))
			{
				footer = new className(context, options);
				if (!(footer instanceof BaseFooter))
				{
					console.error('EntitySelector: footer is not an instance of BaseFooter.');
					footer = null;
				}
			}
		}

		if (footerContent !== null && !footer)
		{
			footer = new DefaultFooter(context, Object.assign({}, options, { content: footerContent }));
		}

		return footer;
	}

	getId(): string
	{
		return this.id;
	}

	getContext(): ?string
	{
		return this.context;
	}

	getNavigation(): Navigation
	{
		return this.navigation;
	}

	deselectAll(): void
	{
		this.getSelectedItems().forEach((item: Item) => {
			item.deselect();
		});
	}

	isMultiple(): boolean
	{
		return this.multiple;
	}

	setTargetNode(node: HTMLElement | { left: number, top: number } | null | MouseEvent): void
	{
		if (!Type.isDomNode(node) && !Type.isNull(node) && !Type.isObject(node))
		{
			return;
		}

		this.targetNode = node;

		if (this.isRendered())
		{
			this.getPopup().setBindElement(this.targetNode);
			this.getPopup().adjustPosition();
		}
	}

	getTargetNode(): ?HTMLElement
	{
		if (this.targetNode === null)
		{
			if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE)
			{
				return this.getTagSelector().getOuterContainer();
			}
		}

		return this.targetNode;
	}

	setHideOnSelect(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.hideOnSelect = flag;
		}
	}

	shouldHideOnSelect(): boolean
	{
		if (this.hideOnSelect !== null)
		{
			return this.hideOnSelect;
		}

		return !this.isMultiple();
	}

	setHideOnDeselect(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.hideOnDeselect = flag;
		}
	}

	shouldHideOnDeselect(): boolean
	{
		if (this.hideOnDeselect !== null)
		{
			return this.hideOnDeselect;
		}

		return false;
	}

	setClearSearchOnSelect(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.clearSearchOnSelect = flag;
		}
	}

	shouldClearSearchOnSelect(): boolean
	{
		return this.clearSearchOnSelect;
	}

	setShowAvatars(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.showAvatars = flag;

			if (this.isRendered())
			{
				this.getTabs().forEach((tab: Tab) => {
					tab.renderContainer();
				});
			}
		}
	}

	shouldShowAvatars(): boolean
	{
		return this.showAvatars;
	}

	isCompactView(): boolean
	{
		return this.compactView;
	}

	setAutoHide(enable: boolean): void
	{
		if (Type.isBoolean(enable))
		{
			this.autoHide = enable;
			if (this.isRendered())
			{
				this.getPopup().setAutoHide(enable);
			}
		}
	}

	isAutoHide(): boolean
	{
		return this.autoHide;
	}

	setAutoHideHandler(handler?: (event: MouseEvent, dialog: Dialog) => boolean): void
	{
		if (Type.isFunction(handler) || handler === null)
		{
			this.autoHideHandler = handler;
		}
	}

	setHideByEsc(enable: boolean): void
	{
		if (Type.isBoolean(enable))
		{
			this.hideByEsc = enable;
			if (this.isRendered())
			{
				this.getPopup().setClosingByEsc(enable);
			}
		}
	}

	shouldHideByEsc(): boolean
	{
		return this.hideByEsc;
	}

	getWidth(): number
	{
		return this.width;
	}

	setWidth(width: number): void
	{
		if (Type.isNumber(width) && width > 0)
		{
			this.width = width;
			if (this.isRendered())
			{
				Dom.style(this.getContainer(), 'width', `${width}px`);
			}
		}
	}

	getHeight(): number
	{
		return this.height;
	}

	setHeight(height: number): Promise
	{
		if (Type.isNumber(height) && height > 0)
		{
			this.height = height;
			if (this.isRendered())
			{
				Dom.style(this.getContainer(), 'height', `${height}px`);
				return Animation.handleTransitionEnd(this.getContainer(), 'height');
			}
			else
			{
				return Promise.resolve();
			}
		}
		else
		{
			return Promise.resolve();
		}
	}

	getOffsetLeft(): number
	{
		return this.offsetLeft;
	}

	setOffsetLeft(offset: number): void
	{
		if (Type.isNumber(offset) && offset >= 0)
		{
			this.offsetLeft = offset;
			if (this.isRendered())
			{
				this.getPopup().setOffset({ offsetLeft: offset });
				this.adjustPosition();
			}
		}
	}

	getOffsetTop(): number
	{
		return this.offsetTop;
	}

	setOffsetTop(offset: number): void
	{
		if (Type.isNumber(offset) && offset >= 0)
		{
			this.offsetTop = offset;
			if (this.isRendered())
			{
				this.getPopup().setOffset({ offsetTop: offset });
				this.adjustPosition();
			}
		}
	}

	getZindex(): number
	{
		return this.getPopup().getZindex();
	}

	isCacheable(): boolean
	{
		return this.cacheable;
	}

	setCacheable(cacheable: boolean): void
	{
		if (Type.isBoolean(cacheable))
		{
			this.cacheable = cacheable;
			if (this.isRendered())
			{
				this.getPopup().setCacheable(cacheable);
			}
		}
	}

	shouldFocusOnFirst(): boolean
	{
		return this.focusOnFirst;
	}

	setFocusOnFirst(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.focusOnFirst = flag;
		}
	}

	focusOnFirstNode(): ?ItemNode
	{
		if (this.getActiveTab())
		{
			const itemNode = this.getActiveTab().getRootNode().getFirstChild();
			if (itemNode)
			{
				itemNode.focus();

				return itemNode;
			}
		}

		return null;
	}

	getFocusedNode(): ?ItemNode
	{
		return this.focusedNode;
	}

	clearNodeFocus(): void
	{
		if (this.focusedNode)
		{
			this.focusedNode.unfocus();
			this.focusedNode = null;
		}
	}

	isDropdownMode(): boolean
	{
		return this.dropdownMode;
	}

	setPreselectedItems(itemIds: ItemId[]): void
	{
		this.preselectedItems = this.validateItemIds(itemIds);
	}

	getPreselectedItems(): ItemId[]
	{
		return this.preselectedItems;
	}

	setUndeselectedItems(itemIds: ItemId[]): void
	{
		this.undeselectedItems = this.validateItemIds(itemIds);
	}

	getUndeselectedItems()
	{
		return this.undeselectedItems;
	}

	/**
	 * @private
	 */
	setOptions(dialogOptions: DialogOptions): void
	{
		const options = Type.isPlainObject(dialogOptions) ? dialogOptions : {};

		if (Type.isArray(options.tabs))
		{
			options.tabs.forEach((tab) => {
				this.addTab(tab);
			});
		}

		if (Type.isArray(options.selectedItems))
		{
			options.selectedItems.forEach((itemOptions: ItemOptions) => {
				const options = Object.assign({}, Type.isPlainObject(itemOptions) ? itemOptions : {});
				options.selected = true;
				this.addItem(options);
			});
		}

		if (Type.isArray(options.items))
		{
			options.items.forEach((itemOptions: ItemOptions) => {
				this.addItem(itemOptions);
			});
		}

		this.setHeader(options.header, options.headerOptions);
		this.setFooter(options.footer, options.footerOptions);
	}

	getMaxLabelWidth(): number
	{
		return this.maxLabelWidth;
	}

	getMinLabelWidth(): number
	{
		return this.minLabelWidth;
	}

	getTagSelector(): ?TagSelector
	{
		return this.tagSelector;
	}

	getTagSelectorMode(): ?TagSelectorMode
	{
		return this.tagSelectorMode;
	}

	isTagSelectorInside(): boolean
	{
		return this.getTagSelector() && this.getTagSelectorMode() === TagSelectorMode.INSIDE;
	}

	isTagSelectorOutside(): boolean
	{
		return this.getTagSelector() && this.getTagSelectorMode() === TagSelectorMode.OUTSIDE;
	}

	getTagSelectorQuery(): string
	{
		return this.getTagSelector() ? this.getTagSelector().getTextBoxValue() : '';
	}

	/**
	 * @private
	 */
	setTagSelector(tagSelector: TagSelector): void
	{
		this.tagSelector = tagSelector;

		this.tagSelector.subscribe('onInput', Runtime.debounce(this.handleTagSelectorInput, 200, this));
		this.tagSelector.subscribe('onAddButtonClick', this.handleTagSelectorAddButtonClick.bind(this));
		this.tagSelector.subscribe('onTagRemove', this.handleTagSelectorTagRemove.bind(this));
		this.tagSelector.subscribe('onAfterTagRemove', this.handleTagSelectorAfterTagRemove.bind(this));
		this.tagSelector.subscribe('onAfterTagAdd', this.handleTagSelectorAfterTagAdd.bind(this));
		this.tagSelector.subscribe('onContainerClick', this.handleTagSelectorClick.bind(this));

		this.tagSelector.setDialog(this);
	}

	focusSearch(): void
	{
		if (this.getTagSelector())
		{
			if (this.getActiveTab() !== this.getSearchTab())
			{
				this.getTagSelector().clearTextBox();
			}

			this.getTagSelector().focusTextBox();
		}
	}

	clearSearch(): void
	{
		if (this.getTagSelector())
		{
			this.getTagSelector().clearTextBox();

			if (this.getActiveTab() === this.getSearchTab())
			{
				this.selectFirstTab();
			}
		}
	}

	getLoader(): Loader
	{
		if (this.loader === null)
		{
			this.loader = new Loader({
				target: this.getTabsContainer(),
				size: 100
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
		if (this.loader !== null)
		{
			void this.getLoader().hide();
		}
	}

	destroyLoader(): void
	{
		if (this.loader !== null)
		{
			this.getLoader().destroy();
		}

		this.loader = null;
	}

	getPopup(): Popup
	{
		if (this.popup !== null)
		{
			return this.popup;
		}

		this.getTabs().forEach((tab: Tab) => {
			this.insertTab(tab);
		});

		this.popup = new Popup(Object.assign({
			contentPadding: 0,
			padding: 0,
			offsetTop: this.getOffsetTop(),
			offsetLeft: this.getOffsetLeft(),
			animation: {
				showClassName: 'ui-selector-popup-animation-show',
				closeClassName: 'ui-selector-popup-animation-close',
				closeAnimationType: 'animation'
			},
			bindElement: this.getTargetNode(),
			bindOptions: {
				forceBindPosition: true
			},
			autoHide: this.isAutoHide(),
			autoHideHandler: this.handleAutoHide.bind(this),
			closeByEsc: this.shouldHideByEsc(),
			cacheable: this.isCacheable(),
			events: {
				onFirstShow: this.handlePopupFirstShow.bind(this),
				onAfterShow: this.handlePopupAfterShow.bind(this),
				onAfterClose: this.handlePopupAfterClose.bind(this),
				onDestroy: this.handlePopupDestroy.bind(this)
			},
			content: this.getContainer()
		}, this.popupOptions));

		this.rendered = true;

		this.selectFirstTab();

		return this.popup;
	}

	isRendered(): boolean
	{
		return this.rendered;
	}

	getContainer(): HTMLElement
	{
		return this.cache.remember('container', () => {

			let searchContainer = '';
			if (this.getTagSelectorMode() === TagSelectorMode.INSIDE)
			{
				searchContainer = Tag.render`<div class="ui-selector-search"></div>`;

				this.getTagSelector().renderTo(searchContainer);
			}

			const className = this.isCompactView() ? ' ui-selector-dialog--compact-view' : '';

			return Tag.render`
				<div 
					class="ui-selector-dialog${className}" 
					style="width:${this.getWidth()}px; height:${this.getHeight()}px;"
				>
					${this.getHeaderContainer()}
					${searchContainer}
					${this.getTabsContainer()}
					${this.getFooterContainer()}
				</div>
			`;
		});
	}

	getTabsContainer(): HTMLElement
	{
		return this.cache.remember('tabs-container', () => {
			return Tag.render`
				<div class="ui-selector-tabs">
					${this.getTabContentsContainer()}
					${this.getLabelsContainer()}
				</div>
			`;
		});
	}

	getTabContentsContainer(): HTMLElement
	{
		return this.cache.remember('tab-contents', () => {
			return Tag.render`<div class="ui-selector-tab-contents"></div>`;
		});
	}

	getLabelsContainer(): HTMLElement
	{
		return this.cache.remember('labels-container', () => {
			return Tag.render`
				<div 
					class="ui-selector-tab-labels"
					onmouseenter="${this.handleLabelsMouseEnter.bind(this)}"
					onmouseleave="${this.handleLabelsMouseLeave.bind(this)}"
				></div>
			`;
		});
	}

	getHeaderContainer(): HTMLElement
	{
		return this.cache.remember('header', () => {
			const header = this.getHeader() && this.getHeader().getContainer();

			return Tag.render`
				<div class="ui-selector-header-container">${header ? header : ''}</div>
			`;
		});
	}

	getFooterContainer(): HTMLElement
	{
		return this.cache.remember('footer', () => {
			const footer = this.getFooter() && this.getFooter().getContainer();

			return Tag.render`
				<div class="ui-selector-footer-container">${footer ? footer : ''}</div>
			`;
		});
	}

	freeze(): void
	{
		if (this.isFrozen())
		{
			return;
		}

		this.frozenProps = {
			autoHide: this.isAutoHide(),
			hideByEsc: this.shouldHideByEsc(),
		};

		this.setAutoHide(false);
		this.setHideByEsc(false);

		this.getNavigation().disable();
		Dom.addClass(this.getContainer(), 'ui-selector-dialog--freeze');

		this.frozen = true;
	}

	unfreeze(): void
	{
		if (!this.isFrozen())
		{
			return;
		}

		this.setAutoHide(this.frozenProps.autoHide !== false);
		this.setHideByEsc(this.frozenProps.hideByEsc !== false);

		this.getNavigation().enable();
		Dom.removeClass(this.getContainer(), 'ui-selector-dialog--freeze');

		this.frozen = false;
	}

	isFrozen(): boolean
	{
		return this.frozen;
	}

	hasRecentItems(): Promise
	{
		return new Promise((resolve, reject) => {
			Ajax
				.runAction('ui.entityselector.load', {
					json: {
						dialog: this.getAjaxJson()
					},
					getParameters: {
						context: this.getContext()
					}
				})
				.then((response) => {
					resolve(
						response.data && response.data.dialog && Type.isArrayFilled(response.data.dialog.recentItems)
					);
				})
				.catch((error) => {
					reject(error);
				})
			;
		});
	}

	load(): void
	{
		if (this.loadState !== LoadState.UNSENT || !this.hasDynamicLoad())
		{
			return;
		}

		if (this.getTagSelector())
		{
			this.getTagSelector().lock();
		}

		setTimeout(() => {
			if (this.isLoading())
			{
				this.showLoader();
			}
		}, 400);

		this.loadState = LoadState.LOADING;

		Ajax.runAction('ui.entityselector.load', {
				json: {
					dialog: this.getAjaxJson()
				},
				getParameters: {
					context: this.getContext()
				}
			})
			.then((response) => {
				if (response && response.data && Type.isPlainObject(response.data.dialog))
				{
					this.loadState = LoadState.DONE;

					const entities =
						Type.isArrayFilled(response.data.dialog.entities)
							? response.data.dialog.entities
							: []
					;

					entities.forEach((entityOptions: EntityOptions) => {
						const entity = this.getEntity(entityOptions.id);
						if (entity)
						{
							entity.setDynamicSearch(entityOptions.dynamicSearch);

						}
					});

					this.setOptions(response.data.dialog);

					this.getPreselectedItems().forEach((preselectedItem: ItemId) => {
						const item = this.getItem(preselectedItem);
						if (item)
						{
							item.select(true);
						}
					});

					const recentItems = response.data.dialog.recentItems;
					if (Type.isArray(recentItems))
					{
						const nodeOptionsMap: Map<Item, ItemNodeOptions> = new Map();
						const itemsOptions: ItemOptions[] = response.data.dialog.items;
						if (Type.isArray(itemsOptions))
						{
							itemsOptions.forEach((itemOptions: ItemOptions) => {
								if (itemOptions.nodeOptions)
								{
									const item = this.getItem(itemOptions);
									if (item)
									{
										nodeOptionsMap.set(item, itemOptions.nodeOptions);
									}
								}
							});
						}

						const items = recentItems.map((recentItem: ItemId) => {
							const item = this.getItem(recentItem);

							return [item, nodeOptionsMap.get(item)];
						});

						this.getRecentTab().getRootNode().addItems(items);
					}

					if (!this.getRecentTab().getRootNode().hasChildren() && this.getRecentTab().getStub())
					{
						this.getRecentTab().getStub().show();
					}

					if (this.getTagSelector())
					{
						this.getTagSelector().unlock();
					}

					if (this.isRendered())
					{
						if (this.isDropdownMode() && this.getActiveTab() === this.getRecentTab())
						{
							this.selectFirstTab();
						}
						else if (!this.getActiveTab())
						{
							this.selectFirstTab();
						}
					}

					this.focusSearch();
					this.destroyLoader();

					if (this.shouldFocusOnFirst())
					{
						this.focusOnFirstNode();
					}

					this.emit('onLoad');
				}
			})
			.catch((error) => {
				this.loadState = LoadState.UNSENT;

				if (this.getTagSelector())
				{
					this.getTagSelector().unlock();
				}

				this.focusSearch();
				this.destroyLoader();

				this.emit('onLoadError', { error });

				console.error(error);
			});
	}

	isLoaded(): boolean
	{
		return this.loadState === LoadState.DONE;
	}

	isLoading(): boolean
	{
		return this.loadState === LoadState.LOADING;
	}

	hasDynamicLoad(): boolean
	{
		let hasDynamicLoad = false;
		this.entities.forEach((entity: Entity) => {
			hasDynamicLoad = hasDynamicLoad || entity.hasDynamicLoad();
		});

		return hasDynamicLoad;
	}

	hasDynamicSearch(): boolean
	{
		let hasDynamicSearch = false;
		this.entities.forEach((entity: Entity) => {
			hasDynamicSearch = hasDynamicSearch || (entity.isSearchable() && entity.hasDynamicSearch());
		});

		return hasDynamicSearch;
	}

	saveRecentItem(item: Item): void
	{
		if (this.getContext() === null || !item.isSaveable())
		{
			return;
		}

		this.recentItemsToSave.push(item);
		this.saveRecentItemsWithDebounce();
	}

	/**
	 * @private
	 */
	saveRecentItems(): void
	{
		if (!Type.isArrayFilled(this.recentItemsToSave))
		{
			return;
		}

		Ajax.runAction('ui.entityselector.saveRecentItems', {
				json: {
					dialog: this.getAjaxJson(),
					recentItems: this.recentItemsToSave.map((item: Item) => item.getAjaxJson())
				},
				getParameters: {
					context: this.getContext()
				}
			})
			.then((response) => {

			})
			.catch((error) => {
				console.error(error);
			});

		this.recentItemsToSave = [];
	}

	shouldClearUnavailableItems(): boolean
	{
		return this.clearUnavailableItems;
	}

	/**
	 * @private
	 */
	handleTagSelectorInput(): void
	{
		if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE && !this.isOpen())
		{
			this.show();
		}

		const query = this.getTagSelector().getTextBoxValue();
		this.search(query);
	}

	/**
	 * @private
	 */
	handleTagSelectorAddButtonClick(): void
	{
		this.show();
	}

	/**
	 * @private
	 */
	handleTagSelectorTagRemove(event: BaseEvent): void
	{
		const { tag } = event.getData();

		const item = this.getItem({ id: tag.getId(), entityId: tag.getEntityId() });
		if (item)
		{
			item.deselect();
		}

		this.focusSearch();
	}

	/**
	 * @private
	 */
	handleTagSelectorAfterTagRemove(): void
	{
		this.adjustByTagSelector();
	}

	/**
	 * @private
	 */
	handleTagSelectorAfterTagAdd(): void
	{
		this.adjustByTagSelector();
	}

	/**
	 * @private
	 */
	adjustByTagSelector(): void
	{
		if (this.getTagSelectorMode() === TagSelectorMode.OUTSIDE)
		{
			this.adjustPosition();
		}
		else if (this.getTagSelectorMode() === TagSelectorMode.INSIDE)
		{
			const newTagSelectorHeight = this.getTagSelector().calcHeight();
			if (newTagSelectorHeight > 0)
			{
				const offset = newTagSelectorHeight - (this.tagSelectorHeight || this.getTagSelector().getMinHeight());
				this.tagSelectorHeight = newTagSelectorHeight;
				if (offset !== 0)
				{
					const height = this.getHeight();
					this.setHeight(height + offset).then(() => {
						this.adjustPosition();
					});
				}
			}
		}
	}

	/**
	 * @private
	 */
	handleTagSelectorClick(): void
	{
		this.focusSearch();
	}

	/**
	 * @internal
	 */
	handleItemSelect(item: Item, animate: boolean = true): void
	{
		if (!this.isMultiple())
		{
			this.deselectAll();

			if (this.getSelectedItems().length > 0)
			{
				console.error('EntitySelector: some items are still selected.', this.getSelectedItems());
			}
		}

		if (this.getTagSelector() && (this.isMultiple() || this.isTagSelectorOutside()))
		{
			const tag = item.createTag();
			tag.animate = animate;
			this.getTagSelector().addTag(tag);
		}

		this.selectedItems.add(item);
	}

	/**
	 * @internal
	 */
	handleItemDeselect(item: Item): void
	{
		this.selectedItems.delete(item);

		if (this.getTagSelector())
		{
			this.getTagSelector().removeTag({
				id: item.getId(),
				entityId: item.getEntityId()
			});
		}
	}

	/**
	 * @private
	 */
	handlePopupAfterShow(): void
	{
		this.focusSearch();
		this.adjustByTagSelector();

		this.emit('onShow');
	}

	/**
	 * @private
	 */
	handlePopupFirstShow(): void
	{
		this.emit('onFirstShow');

		requestAnimationFrame(() => {
			requestAnimationFrame(() => {
				Dom.addClass(this.getPopup().getPopupContainer(), 'ui-selector-popup-container');
			});
		});

		this.observeTabOverlapping();
	}

	/**
	 * @private
	 */
	handleAutoHide(event: MouseEvent): void
	{
		const target = event.target;
		const el = this.getPopup().getPopupContainer();
		if (target === el || el.contains(target))
		{
			return false;
		}

		if (
			this.isTagSelectorOutside()
			&& target === this.getTagSelector().getTextBox()
			&& Type.isStringFilled(this.getTagSelector().getTextBoxValue())
		)
		{
			return false;
		}

		if (this.autoHideHandler !== null)
		{
			const result = this.autoHideHandler(event, this);
			if (Type.isBoolean(result))
			{
				return result;
			}
		}

		return true;
	}

	/**
	 * @private
	 */
	observeTabOverlapping(): void
	{
		this.disconnectTabOverlapping();

		this.overlappingObserver = new MutationObserver(() => {
			if (this.getLabelsContainer().offsetWidth > 0)
			{
				const left = parseInt(this.getPopup().getPopupContainer().style.left, 10);
				if (left < this.getMinLabelWidth())
				{
					Dom.style(this.getPopup().getPopupContainer(), 'left', `${this.getMinLabelWidth()}px`);
				}
			}
		});

		this.overlappingObserver.observe(this.getPopup().getPopupContainer(), {
			attributes: true,
			attributeFilter: ['style']
		});
	}

	/**
	 * @private
	 */
	disconnectTabOverlapping(): void
	{
		if (this.overlappingObserver)
		{
			this.overlappingObserver.disconnect();
		}
	}

	/**
	 * @private
	 */
	handlePopupAfterClose(): void
	{
		if (this.isTagSelectorOutside())
		{
			if (this.getActiveTab() && this.getActiveTab() === this.getSearchTab())
			{
				this.selectFirstTab();
			}

			this.getTagSelector().clearTextBox();
			this.getTagSelector().showAddButton();
			this.getTagSelector().hideTextBox();
		}

		this.emit('onHide');
	}

	/**
	 * @private
	 */
	handlePopupDestroy(): void
	{
		this.destroy();
	}

	/**
	 * @private
	 */
	handleLabelsMouseEnter(): void
	{
		const rect = Dom.getRelativePosition(this.getLabelsContainer(), this.getPopup().getTargetContainer());
		const freeSpace = rect.right;

		if (freeSpace > this.getMinLabelWidth())
		{
			Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
			Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');

			Dom.style(this.getLabelsContainer(), 'max-width', `${Math.min(freeSpace, this.getMaxLabelWidth())}px`);
			Animation.handleTransitionEnd(this.getLabelsContainer(), 'max-width').then(() => {
				Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
				Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
			});
		}
		else
		{
			Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');
		}
	}

	/**
	 * @private
	 */
	handleLabelsMouseLeave(): void
	{
		Dom.addClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
		Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-show');
		Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--active');

		Animation.handleTransitionEnd(this.getLabelsContainer(), 'max-width').then(() => {
			Dom.removeClass(this.getLabelsContainer(), 'ui-selector-tab-labels--animate-hide');
		});

		Dom.style(this.getLabelsContainer(), 'max-width', null);
	}

	/**
	 * @private
	 */
	handleItemNodeFocus(event: BaseEvent): void
	{
		const { node } = event.getData();
		if (this.focusedNode === node)
		{
			return;
		}

		this.clearNodeFocus();

		this.focusedNode = node;
	}

	/**
	 * @private
	 */
	handleItemNodeUnfocus(): void
	{
		this.clearNodeFocus();
	}

	getAjaxJson(): { [key: string]: any }
	{
		return {
			id: this.getId(),
			context: this.getContext(),
			entities: this.getEntities(),
			preselectedItems: this.getPreselectedItems(),
			clearUnavailableItems: this.shouldClearUnavailableItems()
		};
	}
}