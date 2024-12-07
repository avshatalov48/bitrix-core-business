import { Loc, Tag, Text, Type } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Popup, PopupOptions } from 'main.popup';
import { BitrixVue } from 'ui.vue3';
import { createPinia } from 'ui.vue3.pinia';
import { Hint } from "ui.vue3.components.hint";
import { feedback} from "./directives/feedback";

import { Application } from './components/application';
import { Button } from './components/button';

import 'ui.forms';

import './css/popup.css';

import type { GroupData } from './types/group';
import type { ItemData } from './types/item';
import type { FilterData} from './types/filter';

export type {
	GroupData,
	ItemData,
	FilterData,
};

import { EmptyContent } from './components/stubs/empty-content';

import { useGlobalState } from './stores/global-state';

export const Stubs = {
	EmptyContent,
}

export const States = {
	useGlobalState,
}

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
	#recentGroupData: ?GroupData;
	#showEmptyGroups: boolean = false;
	#showRecentGroup: boolean = false;
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

	constructor(props: {
		groups?: Array<Array<GroupData>>,
		items?: Array<ItemData>,
		recentGroupData?: GroupData,
		canDeselectGroups?: boolean,
		showEmptyGroups?: boolean,
		showRecentGroup?: boolean,
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
	})
	{
		super();
		this.setEventNamespace('BX.UI.EntityCatalog');

		this.setGroups(Type.isArray(props.groups) ? props.groups : []);
		this.setItems(Type.isArray(props.items) ? props.items : []);
		this.#recentGroupData = props.recentGroupData;

		if (Type.isBoolean(props.canDeselectGroups))
		{
			this.#groups.forEach((groupList) => {
				groupList.forEach((group) => {
					group.deselectable = props.canDeselectGroups
				});
			});
		}

		this.#showEmptyGroups = Type.isBoolean(props.showEmptyGroups) ? props.showEmptyGroups : false;
		this.#showRecentGroup = Type.isBoolean(props.showRecentGroup) ? props.showRecentGroup : false;
		this.#showSearch = Type.isBoolean(props.showSearch) ? props.showSearch : false;

		if (Type.isPlainObject(props.filterOptions))
		{
			this.#filterOptions = props.filterOptions;
		}

		this.#popupTitle = Type.isString(props.title) ? props.title : '';
		this.#customTitleBar = props.customTitleBar ? props.customTitleBar : null;
		this.#popupOptions = Object.assign(
			this.#getDefaultPopupOptions(),
			Type.isObject(props.popupOptions) ? props.popupOptions : {}
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
				groupList = [groupList]
			}

			return groupList.map(group => ({
				selected: false,
				deselectable: true,
				...group
			}));
		});

		return this;
	}

	getItems(): Array<ItemData>
	{
		return this.#items;
	}

	setItems(items: Array<ItemData>): this
	{
		items = items.map(item => ({
			button: {},
			...item
		}));

		this.#items.length = 0;
		this.#items.push(...items);

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

	#attachTemplate()
	{
		const context = this;

		const rootProps = {
			recentGroupData: this.#recentGroupData,
			groups: this.#groups,
			items: this.#items,
			showEmptyGroups: this.#showEmptyGroups,
			showRecentGroups: this.#showRecentGroup,
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
					feedback
				},
				props: {
					recentGroupData: Object,
					groups: Array,
					items: Array,
					showEmptyGroups: Boolean,
					showRecentGroups: Boolean,
					filterOptions: Object,
				},
				created()
				{
					this.$app = context;
				},
				template: `
					<Application
						:recent-group-data="recentGroupData"
						:groups="groups"
						:items="items"
						:show-empty-groups="showEmptyGroups"
						:show-recent-group="showRecentGroups"
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
			rootProps
		);

		this.#application.use(createPinia()).mount(this.getPopup().getContentContainer());
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
			`
		};
	}

	#handleClose(): void
	{
		this.close();
	}

	close()
	{
		this.#application.unmount();
		this.getPopup().close();
	}
}