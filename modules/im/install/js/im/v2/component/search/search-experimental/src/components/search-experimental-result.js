import { Loader } from 'im.v2.component.elements';

import { EmptyState } from './empty-state';
import { SearchExperimentalItem } from './search-experimental-item';

// @vue/component
export const SearchExperimentalResult = {
	name: 'SearchExperimentalResult',
	components: { SearchExperimentalItem, EmptyState, Loader },
	props: {
		dialogIds: {
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
	},
	emits: ['clickItem'],
	computed:
	{
		isEmptyState(): boolean
		{
			return this.dialogIds.length === 0;
		},
	},
	template: `
		<div class="bx-im-search-experimental-result__scope">
			<SearchExperimentalItem
				v-for="dialogId in dialogIds"
				:key="dialogId"
				:dialogId="dialogId"
				:withDate="true"
				:query="query"
				@clickItem="$emit('clickItem', $event)"
			/>
			<EmptyState v-if="isEmptyState" />
		</div>
	`,
};
