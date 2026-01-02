import { getUsersFromRecentItems } from 'im.v2.lib.search';

import { MyNotes } from './my-notes';
import { CarouselUser } from './carousel-user';

import '../css/recent-users-carousel.css';

const SHOW_USERS_LIMIT = 6;

// @vue/component
export const RecentUsersCarousel = {
	name: 'RecentUsersCarousel',
	components: { CarouselUser, MyNotes },
	emits: ['clickItem', 'openContextMenu'],
	computed:
	{
		usersDialogIds(): string[]
		{
			return getUsersFromRecentItems({ withFakeUsers: false }).map(({ dialogId }) => dialogId);
		},
		items(): string[]
		{
			return this.usersDialogIds.slice(0, SHOW_USERS_LIMIT - 1);
		},
	},
	methods:
	{
		isChat(dialogId: string): boolean
		{
			return dialogId.startsWith('chat');
		},
		loc(key: string): string
		{
			return this.$Bitrix.Loc.getMessage(key);
		},
	},
	template: `
		<div class="bx-im-recent-users-carousel__container bx-im-recent-users-carousel__scope">
			<div class="bx-im-recent-users-carousel__title-container">
				<span class="bx-im-recent-users-carousel__section-title">
					{{ loc('IM_SEARCH_SECTION_RECENT_CHATS') }}
				</span>
			</div>
			<div class="bx-im-recent-users-carousel__users-container">
				<MyNotes
					@clickItem="$emit('clickItem', $event)" 
				/>
				<CarouselUser
					v-for="userDialogId in items"
					:key="userDialogId"
					:userDialogId="userDialogId"
					@clickItem="$emit('clickItem', $event)"
					@openContextMenu="$emit('openContextMenu', $event)"
				/>
			</div>
		</div>
	`,
};
