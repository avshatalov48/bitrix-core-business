import { Loader } from 'im.v2.component.elements';

import { EmptyState } from './empty-state';
import { SearchItem } from './search-item';

// @vue/component
export const SearchResult = {
	name: 'SearchResult',
	components: { SearchItem, EmptyState, Loader },
	props:
	{
		items: {
			type: Array,
			default: () => [],
		},
		isLoading: {
			type: Boolean,
			default: false,
		},
		query: {
			type: String,
			default: '',
		},
		selectMode: {
			type: Boolean,
			default: false,
		},
		selectedItems: {
			type: Array,
			required: false,
			default: () => [],
		},
		showMyNotes: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['clickItem', 'openContextMenu'],
	computed:
	{
		searchResult(): {dialogId: string, dateMessage: ''}[]
		{
			return this.items;
		},
		isEmptyState(): boolean
		{
			return this.items.length === 0;
		},
	},
	methods:
	{
		isSelected(item: {dialogId: string, dateMessage: ''}): boolean
		{
			return this.selectedItems.includes(item.dialogId);
		},
	},
	template: `
		<div class="bx-im-search-result__scope">
			<SearchItem
				v-for="item in items"
				:key="item.dialogId"
				:dialogId="item.dialogId"
				:dateMessage="item.dateMessage"
				:withDate="true"
				:selectMode="selectMode"
				:isSelected="isSelected(item)"
				:query="query"
				@clickItem="$emit('clickItem', $event)"
				@openContextMenu="$emit('openContextMenu', $event)"
			/>
			<EmptyState v-if="isEmptyState" />
		</div>
	`,
};
