import { Type } from 'main.core';
import { mapState } from 'ui.vue3.pinia';
import { GroupData } from '@/types/group';
import { ItemListAdvice } from './item-list-advice';
import { ItemList} from './item-list';
import { EmptyContent } from './stubs/empty-content';

import { useGlobalState } from '../stores/global-state';

import '../css/main-content.css';

export const MainContent = {
	name: 'ui-entity-catalog-main-content',
	components: {
		ItemListAdvice,
		ItemList,
		EmptyContent,
	},
	props: {
		items: {
			type: Array,
			required: true
		},
		itemsToShow: {
			type: Array,
		},
		group: {
			type: GroupData,
			required: true,
		},
		searching: {
			type: Boolean,
			default: false,
		},
	},
	computed: {
		...mapState(useGlobalState, ['filtersApplied', 'shouldShowWelcomeStub']),
		showAdvice(): boolean
		{
			return this.group && Type.isStringFilled(this.group.adviceTitle) && !this.searching;
		},
		hasItems(): boolean
		{
			return this.group && this.items.length > 0;
		},
		showWelcomeStub(): boolean
		{
			return this.showNoSelectedGroupStub && this.shouldShowWelcomeStub;
		},
		showNoSelectedGroupStub(): boolean
		{
			return !this.group && !this.searching;
		},
		showFiltersStub()
		{
			const hasFilterStubTitle = !!this.$slots['main-content-filter-stub-title'];

			return hasFilterStubTitle && this.hasItems && this.filtersApplied && (this.itemsToShow.length <= 0);
		},
		showSearchStub(): boolean
		{
			return (!this.group || this.hasItems) && this.searching && (this.itemsToShow.length <= 0);
		},
		showEmptyGroupStub(): boolean
		{
			return this.group && this.itemsToShow.length === 0;
		},
		showSeparator(): boolean
		{
			return this.showAdvice && (this.items.length <= 0);
		},
	},
	beforeUpdate()
	{
		this.$refs.content.scrollTop = 0;
	},
	template: `
		<div class="ui-entity-catalog__main-content">
			<div class="ui-entity-catalog__main-content-head">
				<slot name="main-content-header"/>
			</div>
			<ItemListAdvice v-if="showAdvice" :groupData="group" />

			<hr class="ui-entity-catalog__main-separator" v-if="showSeparator">

			<div class="ui-entity-catalog__main-content-body" ref="content">
				<slot name="main-content-welcome-stub" v-if="showWelcomeStub"/>
				<slot name="main-content-no-selected-group-stub" v-else-if="showNoSelectedGroupStub"/>
				<slot name="main-content-filter-stub" v-if="showFiltersStub">
					<EmptyContent>
						<slot name="main-content-filter-stub-title"/>
					</EmptyContent>
				</slot>
				<slot name="main-content-search-stub" v-else-if="showSearchStub">
					<EmptyContent>
						<slot name="main-content-search-not-found-stub"/>
					</EmptyContent>
				</slot>
				<slot name="main-content-empty-group-stub" v-else-if="showEmptyGroupStub">
					<EmptyContent>
						<slot name="main-content-empty-group-stub-title"/>
					</EmptyContent> 
				</slot>
				<ItemList v-else :items="itemsToShow">
					<template #item="itemSlotProps">
						<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
					</template>
				</ItemList>
				<div class="ui-entity-catalog__main-content-footer">
					<slot name="main-content-footer"/>
				</div>
			</div>
		</div>
	`,
};