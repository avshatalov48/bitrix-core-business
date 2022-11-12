import { Item } from './item';

import '../css/item-list.css';

export const ItemList = {
	name: 'ui-entity-selector-item-list',
	components: {
		Item,
	},
	props: {
		items: {
			Type: Array,
			required: true,
		}
	},
	template: `
		<div class="ui-entity-catalog__content --help-block" v-if="items.length <= 0">
			<slot name="empty-group-stub">
				<div class="ui-entity-catalog__empty-content">
					<div class="ui-entity-catalog__empty-content_icon">
						<img src="/bitrix/js/ui/entity-catalog/images/ui-entity-catalog--search-icon.svg" alt="Choose a grouping">
					</div>
					<div class="ui-entity-catalog__empty-content_text">
						<slot name="empty-group-stub-title"/>
					</div>
				</div>
			</slot>
		</div>
		<div class="ui-entity-catalog__content" v-else>
			<div class="ui-entity-catalog__options">
				<Item 
					:item-data="item"
					:key="item.id"
					v-for="item in items"
				>
					<template #item="itemSlotProps">
						<slot name="item" v-bind:itemData="itemSlotProps.itemData"/>
					</template>
				</Item>
			</div>
		</div>
	`,
}