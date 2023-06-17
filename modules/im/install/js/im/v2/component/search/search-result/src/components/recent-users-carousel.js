import {CarouselUser} from './carousel-user';
import '../css/recent-users-carousel.css';
import type {SearchItem} from '../classes/search-item';

const recentUsersLimit = 6;

// @vue/component
export const RecentUsersCarousel = {
	name: 'RecentUsersCarousel',
	components: {CarouselUser},
	props:
	{
		items: {
			type: Object, // Map<string, SearchItem>
			required: true
		},
		selectMode: {
			type: Boolean,
			default: false
		},
		selectedItems: {
			type: Array,
			default: () => []
		}
	},
	emits: ['clickItem'],
	computed:
	{
		users(): SearchItem[]
		{
			const itemsFromMap = [...this.items.values()];

			return itemsFromMap.slice(0, recentUsersLimit);
		},
	},
	methods:
	{
		isSelected(item: SearchItem): boolean
		{
			return this.selectedItems.includes(item.getEntityFullId());
		}
	},
	template: `
		<div class="bx-im-recent-users-carousel__container bx-im-recent-users-carousel__scope">
			<div class="bx-im-recent-users-carousel__title-container">
				<span class="bx-im-recent-users-carousel__section-title">
					{{ $Bitrix.Loc.getMessage('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</span>
			</div>
			<div class="bx-im-recent-users-carousel__users-container">
				<CarouselUser 
					v-for="user in users"
					:key="user.getId()"
					:item="user"
					:selectMode="selectMode"
					:isSelected="isSelected(user)"
					@clickItem="$emit('clickItem', $event)"
				/>
			</div>
		</div>
	`
};