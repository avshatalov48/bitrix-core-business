import { Loc, Tag, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Popup, PopupOptions } from 'main.popup';
import { BitrixVue } from 'ui.vue3';
import { createPinia } from 'ui.vue3.pinia';
import { Hint } from 'ui.vue3.components.hint';
import { feedback } from './directives/feedback';

import { Application } from './components/application';
import { Button } from './components/button';

import 'ui.forms';

import './css/popup.css';

import type { GroupData } from './types/group';
import type { ItemData } from './types/item';
import type { FilterData } from './types/filter';

export type {
	GroupData,
	ItemData,
	FilterData,
};

import { EmptyContent } from './components/stubs/empty-content';

import { useGlobalState } from './stores/global-state';

export const Stubs = {
	EmptyContent,
};

export const States = {
	useGlobalState,
};

export class EntityCatalog extends EventEmitter
{
	static DEFAULT_POPUP_WIDTH = 881;
	static DEFAULT_POPUP_HEIGHT = 621;
	static DEFAULT_POPUP_COLOR = '#edeef0';

	static SLOT_GROUP_LIST_HEADER = 'group-list-header';
	static SLOT_GROUP = 'group';
	static SLOT_GROUP_LIST_FOOTER = 'group-list-footer';
	static SLOT_MAIN_CONTENT_HEADER = 'main-content-header';
	static SLOT_MAIN_CONTENT_FOOTER = 'main-content-footer';
	static SLOT_MAIN_CONTENT_FILTERS_STUB = 'main-content-filter-stub';
	static SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE = 'main-content-filter-stub-title';
	static SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND = 'search-not-found';
	static SLOT_MAIN_CONTENT_WELCOME_STUB = 'main-content-welcome-stub';
	static SLOT_MAIN_CONTENT_NO_SELECTED_GROUP_STUB = 'main-content-no-selected-group-stub';
	static SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB = 'main-content-empty-group-stub';
	static SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB_TITLE = 'main-content-empty-group-stub-title';
	static SLOT_MAIN_CONTENT_ITEM = 'main-content-item';
	static SLOT_MAIN_CONTENT_SEARCH_STUB = 'main-content-search-stub';

	#popup: ?Popup;
	#popupOptions: PopupOptions;
	#popupTitle: string;
	#customTitleBar: Element = null;

	#groups: Array<Array<GroupData>> = [];
	#items: Array<Item> = [];
	#showEmptyGroups: boolean = false;
	#showSearch: boolean = false;
	#filterOptions: {
		filterItems: Array<FilterData>,
		multiple: boolean,
	} = {
		filterItems: [],
		multiple: false,
	};
	#application;
	#slots: object;
	#customComponents: object;

	// backward compatability
	#recentGroupData: ?GroupData;
	#showRecentGroup: boolean = false;
	#vueInstance: any;

	constructor(props: {
		groups?: Array<Array<GroupData>>,
		items?: Array<ItemData>,
		canDeselectGroups?: boolean,
		showEmptyGroups?: boolean,
		showSearch?: boolean,
		filterOptions?: {
			filterItems: Array<FilterData>,
			multiple: boolean,
		},
		popupOptions?: PopupOptions,
		customTitleBar?: string,
		title?: string,
		slots?: object,
		events?: { [eventName: string]: (event: BaseEvent) => void },
		customComponents?: object,

		// backward compatability
		recentGroupData: ?GroupData,
		showRecentGroup: boolean,
	})
	{
		super();
		this.setEventNamespace('BX.UI.EntityCatalog');

		this.setGroups(Type.isArray(props.groups) ? props.groups : []);
		this.setItems(Type.isArray(props.items) ? props.items : []);

		// backward compatibility
		this.#recentGroupData = props.recentGroupData ?? null;
		this.#showRecentGroup = Type.isBoolean(props.showRecentGroup) ? props.showRecentGroup : false;

		if (Type.isBoolean(props.canDeselectGroups))
		{
			this.#groups.forEach((groupList) => {
				groupList.forEach((group) => {
					group.deselectable = props.canDeselectGroups;
				});
			});
		}

		this.#showEmptyGroups = Type.isBoolean(props.showEmptyGroups) ? props.showEmptyGroups : false;
		this.#showSearch = Type.isBoolean(props.showSearch) ? props.showSearch : false;

		if (Type.isPlainObject(props.filterOptions))
		{
			this.#filterOptions = props.filterOptions;
		}

		this.#popupTitle = Type.isString(props.title) ? props.title : '';
		this.#customTitleBar = props.customTitleBar ? props.customTitleBar : null;
		this.#popupOptions = Object.assign(
			this.#getDefaultPopupOptions(),
			Type.isObject(props.popupOptions) ? props.popupOptions : {},
		);
		this.#slots = props.slots ?? {};
		this.#customComponents = props.customComponents ?? {};

		this.subscribeFromOptions(props.events);
	}

	setGroups(groups: Array<Array<GroupData> | GroupData>): this
	{
		this.#groups = groups.map((groupList) => {
			if (!Type.isArray(groupList))
			{
				groupList = [groupList];
			}
			return groupList.map(group => ({ selected: false, deselectable: true, ...group }));
		});

		if (!this.#vueInstance || !this.#vueInstance.localGroups)
		{
			return this;
		}

		if (this.isGroupsStructureChanged(this.#groups))
		{
			try
			{
				this.#vueInstance.refreshGroups(this.#groups);
			}
			catch (e)
			{
				console.error(e);
				this.#vueInstance.localGroups = this.#groups;
			}
		}
		else
		{
			const countersMap = {};
			for (const list of this.#groups)
			{
				for (const g of list)
				{
					countersMap[String(g.id)] = g.customData?.counterValue ?? null;
				}
			}

			this._updateCountersInGroupLists(this.#vueInstance.localGroups, countersMap);
		}

		return this;
	}

	isGroupsStructureChanged(newGroups): boolean
	{
		if (!Array.isArray(this.#groups) || !Array.isArray(newGroups))
		{
			return true;
		}

		if (this.#groups.length !== newGroups.length)
		{
			return true;
		}

		for (let i = 0; i < newGroups.length; i++)
		{
			const oldList = this.#groups[i] || [];
			const newList = newGroups[i] || [];
			if (oldList.length !== newList.length)
			{
				return true;
			}
			for (let j = 0; j < newList.length; j++)
			{
				if (String(oldList[j]?.id) !== String(newList[j]?.id))
				{
					return true;
				}
			}
		}
		return false;
	}

	_updateCountersInGroupLists(groupLists, countersMap = {})
	{
		if (!Array.isArray(groupLists)) return;
		for (const list of groupLists)
		{
			for (const group of list)
			{
				const id = String(group.id);
				if (Object.prototype.hasOwnProperty.call(countersMap, id))
				{
					const custom = group.customData ?? {};
					group.customData = Object.assign({}, custom, { counterValue: countersMap[id] });
				}
			}
		}
	}

	updateGroupCounter(groupId, counterValue): this
	{
		const idStr = String(groupId);
		const countersMap = { [idStr]: counterValue };

		this._updateCountersInGroupLists(this.#groups, countersMap);

		if (!this.#vueInstance)
		{
			return this;
		}

		try
		{
			this._updateCountersInGroupLists(this.#vueInstance.localGroups, countersMap);
		}
		catch (e)
		{
			if (typeof this.#vueInstance.refreshGroups === 'function')
			{
				this.#vueInstance.refreshGroups(this.#groups);
			}
		}

		return this;
	}

	getItems(): Array<ItemData>
	{
		return this.#items;
	}

	setItems(items: Array<ItemData> = []): this
	{
		this.#items.length = 0;
		this.#items.push(...items);

		if (!this.#vueInstance || typeof this.#vueInstance.refreshItems !== 'function')
		{
			return this;
		}

		try
		{
			this.#vueInstance.refreshItems(this.#items);
		}
		catch (e)
		{
			console.error(e);
		}

		return this;
	}

	updateItemById(id, patch = {}): this
	{
		if (this.#vueInstance && typeof this.#vueInstance.updateItemById === 'function')
		{
			try
			{
				this.#vueInstance.updateItemById(id, patch);
				return this;
			}
			catch (e)
			{
				console.error(e);
			}
		}

		const itemForUpdate = this.#items.find(item => String(item.id) === String(id));
		if (itemForUpdate)
		{
			Object.assign(itemForUpdate, patch);
		}
		else
		{
			this.#items.push(Object.assign({ id }, patch));
		}

		return this;
	}

	show()
	{
		this.#attachTemplate();
		this.getPopup().show();
	}

	isShown(): boolean
	{
		return this.#popup && this.#popup.isShown();
	}

	#resolveGroupsForTemplate(): Array<Array<GroupData>>
	{
		if (!this.#recentGroupData || !this.#showRecentGroup)
		{
			return this.#groups;
		}

		// clone groups shallowly to avoid mutating original arrays
		const groupsClone = this.#groups.map(list => list.slice());

		const recent = {
			isHeaderGroup: true,
			id: (this.#recentGroupData.id ?? 'recent'),
			name: this.#recentGroupData.name ?? '',
			icon: this.#recentGroupData.icon ?? '',
			tags: this.#recentGroupData.tags ?? [],
			adviceTitle: this.#recentGroupData.adviceTitle,
			adviceAvatar: this.#recentGroupData.adviceAvatar,
			selected: !!this.#recentGroupData.selected,
			disabled: !!this.#recentGroupData.disabled,
			deselectable: this.#recentGroupData.deselectable ?? true,
			compare: this.#recentGroupData.compare,
			customData: Object.assign(
				{},
				this.#recentGroupData.customData ?? {},
				{
					// prefer canonical name `counterValue`, fallback to legacy `newEntitiesCount`
					counterValue: (this.#recentGroupData.customData?.counterValue ?? this.#recentGroupData.customData?.newEntitiesCount)
				}
			),
		};

		if (groupsClone.length > 0 && Array.isArray(groupsClone[0]) && groupsClone[0].some(g => g.isHeaderGroup))
		{
			groupsClone[0].unshift(recent);
			return groupsClone;
		}

		return [[recent], ...groupsClone];
	}

	#attachTemplate()
	{
		const container = this.getPopup().getContentContainer();

		if (this.#application && typeof this.#application.unmount === 'function')
		{
			try
			{
				this.#application.unmount();
			}
			catch (e)
			{
				console.error(e);
			}
			this.#application = null;
			this.#vueInstance = null;
		}

		const context = this;

		const groupsToPass = this.#resolveGroupsForTemplate();

		const rootProps = {
			groups: groupsToPass,
			items: this.#items,
			showEmptyGroups: this.#showEmptyGroups,
			filterOptions: this.#filterOptions,
		};

		this.#application = BitrixVue.createApp(
			{
				name: 'ui-entity-catalog',
				components: Object.assign(this.#customComponents, {
					Application,
					Hint,
					Button,
				}),
				directives: {
					feedback,
				},
				props: {
					groups: Array,
					items: Array,
					showEmptyGroups: Boolean,
					filterOptions: Object,
				},
				created()
				{
					this.$app = context;
				},
				data(): { localGroups: Array<Array<GroupData>>, localItems: Array<Item> }
				{
					return {
						localGroups: this.groups,
						localItems: this.items,
					};
				},
				methods: {
					onItemsRendered()
					{
						this.$app.emit('onItemsRendered');
					},
					refreshGroups(groups: Array<GroupData>)
					{
						this.localGroups = groups;
					},
					refreshItems(newItems = [])
					{
						try
						{
							const existingMap = new Map(this.localItems.map(it => [String(it.id), it]));

							newItems.forEach(newIt => {
								const id = String(newIt.id);
								if (existingMap.has(id))
								{
									Object.assign(existingMap.get(id), newIt);
								}
								else
								{
									this.localItems.push(newIt);
									existingMap.set(id, this.localItems[this.localItems.length - 1]);
								}
							});

							const newIds = new Set(newItems.map(it => String(it.id)));
							for (let i = this.localItems.length - 1; i >= 0; i--)
							{
								if (!newIds.has(String(this.localItems[i].id)))
								{
									this.localItems.splice(i, 1);
								}
							}
						}
						catch (e)
						{
							console.error(e);
							this.localItems.splice(0, this.localItems.length, ...newItems);
						}
					},

					updateItemById(id, patch = {})
					{
						try
						{
							const it = this.localItems.find(x => String(x.id) === String(id));
							if (it)
							{
								Object.assign(it, patch);
							}
							else
							{
								this.localItems.push(Object.assign({ id }, patch));
							}
						}
						catch (e)
						{
							console.error(e);
						}
					},
				},
				template: `
					<Application
						ref="application"
						@itemsRendered="onItemsRendered"
						:groups="localGroups"
						:items="localItems"
						:show-empty-groups="showEmptyGroups"
						:filter-options="filterOptions"
					>
						<template #group-list-header>
							${this.#slots[EntityCatalog.SLOT_GROUP_LIST_HEADER] ?? ''}
						</template>
						<template #group="groupSlotProps">
							${this.#slots[EntityCatalog.SLOT_GROUP] ?? ''}
						</template>
						<template #group-list-footer>
							${this.#slots[EntityCatalog.SLOT_GROUP_LIST_FOOTER] ?? ''}
						</template>

						<template #main-content-header>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_HEADER] ?? ''}
						</template>
						<template #main-content-footer>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_FOOTER] ?? ''}
						</template>
						<template #main-content-filter-stub v-if="${!!this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB]}">
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB]}
						</template>
						<template #main-content-filter-stub-title v-if="${!!this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]}">
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]}
						</template>
						<template #main-content-search-not-found-stub>
							${
								this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND]
								?? Loc.getMessage('UI_JS_ENTITY_CATALOG_GROUP_LIST_ITEM_LIST_SEARCH_STUB_DEFAULT_TITLE')
							}
						</template>
						<template v-if="${Boolean(this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_STUB])}" #main-content-search-stub>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_STUB]}
						</template>
						<template #main-content-welcome-stub>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB] ?? ''}
						</template>
						<template #main-content-no-selected-group-stub>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_NO_SELECTED_GROUP_STUB] ?? ''}
						</template>
						<template #main-content-empty-group-stub>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB] ?? ''}
						</template>
						<template #main-content-empty-group-stub-title>
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB_TITLE] ?? ''}
						</template>
						<template #item="itemSlotProps">
							${this.#slots[EntityCatalog.SLOT_MAIN_CONTENT_ITEM] ?? ''}
						</template>
					</Application>
				`,
			},
			rootProps,
		);

		this.#vueInstance = this.#application.use(createPinia()).mount(container);
	}

	getPopup(): Popup
	{
		if (Type.isNil(this.#popup))
		{
			this.#popup = new Popup(this.#popupOptions);

			this.#popup.setResizeMode(true);
		}

		return this.#popup;
	}

	selectGroup(groupId: string | number): this
	{
		if (this.#vueInstance && this.#vueInstance.$refs.application)
		{
			const group = this.#vueInstance.localGroups.flat().find(g => String(g.id) === String(groupId));

			if (group)
			{
				this.#vueInstance.$refs.application.handleGroupSelected(group);
			}
		}

		return this;
	}

	#getDefaultPopupOptions(): PopupOptions
	{
		return {
			className: 'ui-catalog-popup ui-entity-catalog__scope',
			titleBar: this.#getPopupTitleBar(),
			noAllPaddings: true,
			closeByEsc: true,
			contentBackground: EntityCatalog.DEFAULT_POPUP_COLOR,
			draggable: true,
			width: EntityCatalog.DEFAULT_POPUP_WIDTH,
			height: EntityCatalog.DEFAULT_POPUP_HEIGHT,
			minWidth: EntityCatalog.DEFAULT_POPUP_WIDTH,
			minHeight: EntityCatalog.DEFAULT_POPUP_HEIGHT,
			autoHide: false,
		};
	}

	#getPopupTitleBar(): Object
	{
		const titleBar =
			this.#customTitleBar
				? this.#customTitleBar
				: Tag.render`<div>${Text.encode(this.#popupTitle)}</div>`
		;

		return {
			content: Tag.render`
				<div class="popup-window-titlebar-text ui-entity-catalog-popup-titlebar">
					${titleBar}
					
					${this.#showSearch ? `<div class="ui-entity-catalog__titlebar_search" data-role="titlebar-search"></div>` : ''}
					${this.#filterOptions.filterItems.length > 0 ? '<div data-role="titlebar-filter"></div>' : ''}
					<span
						class="popup-window-close-icon popup-window-titlebar-close-icon"
						onclick="${this.#handleClose.bind(this)}"
						></span>
				</div>
			`,
		};
	}

	#handleClose(): void
	{
		this.close();
	}

	close()
	{
		try
		{
			if (this.#application && typeof this.#application.unmount === 'function')
			{
				this.#application.unmount();
			}

			if (this.#popup)
			{
				this.#popup.close();
			}
		}
		catch (e)
		{
			console.error(e);
		}

		this.#application = null;
		this.#vueInstance = null;
		this.#popup = null;
	}
}