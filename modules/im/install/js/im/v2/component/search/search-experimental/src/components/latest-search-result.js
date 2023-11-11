import { Loader } from 'im.v2.component.elements';

import { RecentUsersCarousel } from './recent-users-carousel';
import { SearchExperimentalItem } from './search-experimental-item';

import '../css/latest-search-result.css';

// @vue/component
export const LatestSearchResult = {
	name: 'LatestSearchResult',
	components: { RecentUsersCarousel, SearchExperimentalItem, Loader },
	props: {
		dialogIds: {
			type: Array,
			default: () => [],
		},
		isLoading: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['clickItem'],
	computed:
	{
		title(): string
		{
			return this.$Bitrix.Loc.getMessage('IM_SEARCH_EXPERIMENTAL_SECTION_RECENT');
		},
	},
	template: `
		<div class="bx-im-latest-search-result__scope">
			<RecentUsersCarousel @clickItem="$emit('clickItem', $event)" />
			<div class="bx-im-latest-search-result__title">{{ title }}</div>
			<SearchExperimentalItem
				v-for="dialogId in dialogIds"
				:key="dialogId"
				:dialogId="dialogId"
				@clickItem="$emit('clickItem', $event)"
			/>
			<Loader v-if="isLoading" class="bx-im-latest-search-result__loader" />
		</div>
	`,
};
