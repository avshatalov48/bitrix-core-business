import { Type } from 'main.core';
import { GroupData } from '@/types/group';
import { ItemListAdvice } from './item-list-advice';
import { ItemList} from './item-list';

import '../css/main-content.css';

export const MainContent = {
	name: 'ui-entity-catalog-main-content',
	components: {
		ItemListAdvice,
		ItemList,
	},
	props: {
		items: {
			type: Array,
			required: true
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
		showAdvice(): boolean
		{
			return this.group && Type.isStringFilled(this.group.adviceTitle) && !this.searching;
		},
		showNoSelectedGroupStub(): boolean
		{
			return !this.group && !this.searching;
		},
		showSearchStub(): boolean
		{
			return this.searching && (this.items.length <= 0);
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
				<slot name="main-content-no-selected-group-stub" v-if="showNoSelectedGroupStub"/>
				<slot name="main-content-search-stub" v-else-if="showSearchStub">
					<div class="ui-entity-catalog__content --help-block">
						<div class="ui-entity-catalog__empty-content">
							<div class="ui-entity-catalog__empty-content_icon">
								<img src="/bitrix/js/ui/entity-catalog/images/ui-entity-catalog--search-icon.svg" alt="Choose a grouping">
							</div>
							<div class="ui-entity-catalog__empty-content_text">
								<slot name="main-content-search-not-found-stub"/>
							</div>
						</div>
					</div>
				</slot>
				<ItemList v-else :items="items">
					<template #empty-group-stub>
						<slot name="main-content-empty-group-stub"/>
					</template>
					<template #empty-group-stub-title>
						<slot name="main-content-empty-group-stub-title"/>
					</template>
					<template #item="itemSlotProps">
						<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
					</template>
				</ItemList>
			</div>
		</div>
	`,
};