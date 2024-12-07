import { Type, Runtime } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { mapWritableState } from 'ui.vue3.pinia';
import 'ui.icons';

import { MainGroups } from './main-groups';
import { MainContent } from './main-content';
import { TitleBarFilter } from './titlebar-filter';
import { Search } from './search';

import { useGlobalState } from '../stores/global-state';

import { GroupData } from '@/type/group';
import type { ItemData } from '@/type/item';

import '../css/application.css';

export const Application = {
	name: 'ui-entity-catalog-application',
	components: {
		MainGroups,
		MainContent,
		TitleBarFilter,
		Search,
	},
	props: {
		recentGroupData: {
			type: GroupData,
			required: false,
		},
		groups: {
			type: Array,
			required: true,
		},
		items: {
			type: Array,
			required: true,
		},
		showEmptyGroups: {
			type: Boolean,
			default: false,
		},
		showRecentGroup: {
			type: Boolean,
			default: true,
		},
		filterOptions: {
			type: Object,
			default: {
				filterItems: [],
				multiple: false,
			},
		},
	},
	data(): Object
	{
		let selectedGroup = null;
		for (const groupList of this.groups)
		{
			selectedGroup = groupList.find(group => group.selected);
			if (selectedGroup)
			{
				break;
			}
		}
		if (Type.isNil(selectedGroup) && this.recentGroupData?.selected)
		{
			selectedGroup = {id: 'recent', ...(this.recentGroupData ?? {})};
		}

		return {
			selectedGroup,
			selectedGroupId: selectedGroup?.id ?? null,
			shownItems: [],
			shownGroups: this.getDisplayedGroup(),
			lastSearchString: '',
			filters: [],
		};
	},
	computed: {
		itemsBySelectedGroupId(): Array<ItemData>
		{
			const items = this.items.filter((item) => item.groupIds.some(id => id === this.selectedGroupId));

			return this.selectedGroup?.compare ? items.sort(this.selectedGroup.compare) : items;
		},
		...mapWritableState(useGlobalState, {
			searchQuery: 'searchQuery',
			searching: 'searchApplied',
			filtersApplied: 'filtersApplied',
			globalGroup: 'currentGroup',
			shouldShowWelcomeStub: 'shouldShowWelcomeStub',
		}),
	},
	watch: {
		selectedGroup()
		{
			this.shouldShowWelcomeStub = false;
			this.globalGroup = this.selectedGroup;
		},
		selectedGroupId()
		{
			if (this.searching)
			{
				return;
			}

			this.shownItems = this.itemsBySelectedGroupId;
			this.applyFilters();
		},
	},
	created()
	{
		this.shownItems = this.itemsBySelectedGroupId;
	},
	methods: {
		getDisplayedGroup(): Array<Array<GroupData>>
		{
			if (this.showEmptyGroups)
			{
				return Runtime.clone(this.groups);
			}

			const groupIdsWithItems = new Set();
			this.items.forEach((item: ItemData) => {
				item.groupIds.forEach((groupId: String | Number) => {
					groupIdsWithItems.add(groupId)
				});
			});

			return (
				this
					.groups
					.map((groupList: Array<GroupData>) => groupList.filter((group: GroupData) => groupIdsWithItems.has(group.id)
					))
					.filter(groupList => groupList.length > 0)
			);
		},
		handleGroupSelected(group: ?GroupData)
		{
			this.searching = false;
			this.$refs.search?.clearSearch();

			this.selectedGroupId = group ? group.id : null;
			this.selectedGroup = group ?? null;
		},
		onSearch(event: BaseEvent)
		{
			const queryString = event.getData().queryString.toLowerCase();
			this.lastSearchString = queryString;
			this.searchQuery = queryString || '';

			if (!Type.isStringFilled(queryString))
			{
				this.searching = false;
				this.shownItems = [];

				return;
			}

			this.searching = true;
			this.selectedGroup = null;
			this.selectedGroupId = null;

			this.shownItems = this.items.filter((item) => (
				String(item.title).toLowerCase().includes(queryString)
				|| String(item.description).toLowerCase().includes(queryString)
				|| item.tags?.some(tag => tag === queryString)
			));

			this.applyFilters();
		},
		onApplyFilterClick(event: BaseEvent)
		{
			this.filters = event.getData();
			if (this.searching)
			{
				this.onSearch(new BaseEvent({data: {queryString: this.lastSearchString}}));

				return;
			}

			this.shownItems = this.itemsBySelectedGroupId;
			this.applyFilters();
		},
		applyFilters()
		{
			this.filtersApplied = Object.values(this.filters).length > 0;
			for (const filterId in this.filters)
			{
				this.shownItems = this.shownItems.filter(this.filters[filterId].action);
			}
		},
		getFilterNode(): ?Element
		{
			return (this.$root.$app
				.getPopup()
				.getTitleContainer()
				.querySelector('[data-role="titlebar-filter"]')
			);
		},
		getSearchNode(): ?Element
		{
			return (this.$root.$app
				.getPopup()
				.getTitleContainer()
				.querySelector('[data-role="titlebar-search"]')
			);
		},
		stopPropagation(event)
		{
			event.stopPropagation();
		},
	},
	template: `
		<div class="ui-entity-catalog__main">
			<MainGroups
				:recent-group-data="this.recentGroupData"
				:groups="this.shownGroups"
				:show-recent-group="showRecentGroup"
				:searching="searching"
				@group-selected="handleGroupSelected"
			>
				<template #group-list-header>
					<slot name="group-list-header"/>
				</template>
				<template #group="groupSlotProps">
					<slot
						name="group"
						v-bind:groupData="groupSlotProps.groupData"
						v-bind:handleClick="groupSlotProps.handleClick"
					/>
				</template>
				<template #group-list-footer>
					<slot name="group-list-footer"/>
				</template>
			</MainGroups>
			<MainContent
				:items="itemsBySelectedGroupId"
				:items-to-show="shownItems"
				:group="selectedGroup"
				:searching="searching"
			>
				<template #main-content-header>
					<slot name="main-content-header"/>
				</template>
				<template #main-content-no-selected-group-stub>
					<slot name="main-content-no-selected-group-stub"/>
				</template>
				<template #main-content-welcome-stub>
					<slot name="main-content-welcome-stub"/>
				</template>
				<template #main-content-filter-stub v-if="$slots['main-content-filter-stub']">
					<slot name="main-content-filter-stub"/>
				</template>
				<template #main-content-filter-stub-title v-if="$slots['main-content-filter-stub-title']">
					<slot name="main-content-filter-stub-title"/>
				</template>
				<template #main-content-search-stub>
					<slot name="main-content-search-stub"></slot>
				</template>
				<template #main-content-search-not-found-stub>
					<slot name="main-content-search-not-found-stub"/>
				</template>
				<template #main-content-empty-group-stub>
					<slot name="main-content-empty-group-stub"/>
				</template>
				<template #main-content-empty-group-stub-title>
					<slot name="main-content-empty-group-stub-title"/>
				</template>
				<template #item="itemSlotProps">
					<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
				</template>
				<template #main-content-footer>
					<slot name="main-content-footer"/>
				</template>
			</MainContent>
			<Teleport v-if="getFilterNode()" :to="getFilterNode()">
				<TitleBarFilter
					:filters="filterOptions.filterItems"
					:multiple="filterOptions.multiple"
					@onApplyFilters="onApplyFilterClick"
					@mousedown="stopPropagation"
				/>
			</Teleport>
			<Teleport v-if="getSearchNode()" :to="getSearchNode()">
				<Search @onSearch="onSearch" ref="search" @mousedown="stopPropagation"/>
			</Teleport>
		</div>
	`,
}