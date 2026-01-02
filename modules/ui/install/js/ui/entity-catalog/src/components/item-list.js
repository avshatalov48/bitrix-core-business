import { Item } from './item';
import type { ItemData } from '@/type/item';

import '../css/item-list.css';

export const ItemList = {
	name: 'ui-entity-selector-item-list',
	components: {
		Item,
	},
	props: {
		items: {
			/** @type Array<ItemData> */
			Type: Array,
			required: true,
		}
	},
	template: `
		<div class="ui-entity-catalog__content">
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