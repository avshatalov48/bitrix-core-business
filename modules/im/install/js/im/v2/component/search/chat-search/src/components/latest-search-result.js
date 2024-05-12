import { Loader } from 'im.v2.component.elements';

import { SearchItem } from './search-item';
import { RecentUsersCarousel } from './recent-users-carousel';

import '../css/latest-search-result.css';

// @vue/component
export const LatestSearchResult = {
	name: 'LatestSearchResult',
	components: { RecentUsersCarousel, SearchItem, Loader },
	props: {
		items: {
			type: Array,
			default: () => [],
		},
		isLoading: {
			type: Boolean,
			default: false,
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
		searchItems(): {dialogId: string, dateMessage: string}[]
		{
			return this.items;
		},
		title(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT');
		},
	},
	methods:
	{
		isSelected(dialogId: string): boolean
		{
			return this.selectedItems.includes(dialogId.toString());
		},
	},
	template: `
		<div class="bx-im-latest-search-result__scope">
			<RecentUsersCarousel
				:selectMode="selectMode"
				:selectedItems="selectedItems"
				:showMyNotes="showMyNotes"
				@clickItem="$emit('clickItem', $event)"
				@openContextMenu="$emit('openContextMenu', $event)"
			/>
			<div class="bx-im-latest-search-result__title">{{ title }}</div>
			<SearchItem
				v-for="item in searchItems"
				:key="item.dialogId"
				:dialogId="item.dialogId"
				:selected="isSelected(item.dialogId)"
				@clickItem="$emit('clickItem', $event)"
				@openContextMenu="$emit('openContextMenu', $event)"
			/>
			<Loader v-if="isLoading" class="bx-im-latest-search-result__loader" />
		</div>
	`,
};
