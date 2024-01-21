import { Core } from 'im.v2.application.core';

import { MyNotes } from './my-notes';
import { CarouselUser } from './carousel-user';

import '../css/recent-users-carousel.css';

import type { ImModelUser, ImModelRecentItem } from 'im.v2.model';

const SHOW_USERS_LIMIT = 6;

// @vue/component
export const RecentUsersCarousel = {
	name: 'RecentUsersCarousel',
	components: { CarouselUser, MyNotes },
	props: {
		withMyNotes: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['clickItem'],
	computed:
	{
		users(): number[]
		{
			const recentUsers = [];

			this.$store.getters['recent/getSortedCollection'].forEach((recentItem: ImModelRecentItem) => {
				if (this.isChat(recentItem.dialogId))
				{
					return;
				}
				const user: ImModelUser = this.$store.getters['users/get'](recentItem.dialogId, true);
				if (user.bot || user.id === Core.getUserId())
				{
					return;
				}

				recentUsers.push(user);
			});

			return recentUsers.map((user: ImModelUser) => user.id);
		},
		items(): number[]
		{
			const limit = this.withMyNotes ? SHOW_USERS_LIMIT - 1 : SHOW_USERS_LIMIT;

			return this.users.slice(0, limit);
		},

		currentUserId(): number
		{
			return Core.getUserId();
		},
	},
	methods:
	{
		isChat(dialogId: string): boolean
		{
			return dialogId.startsWith('chat');
		},
	},
	template: `
		<div class="bx-im-recent-users-carousel__container bx-im-recent-users-carousel__scope">
			<div class="bx-im-recent-users-carousel__title-container">
				<span class="bx-im-recent-users-carousel__section-title">
					{{ $Bitrix.Loc.getMessage('IM_SEARCH_EXPERIMENTAL_SECTION_RECENT_CHATS') }}
				</span>
			</div>
			<div class="bx-im-recent-users-carousel__users-container">
				<MyNotes
					v-if="withMyNotes"
					@clickItem="$emit('clickItem', $event)"
				/>
				<CarouselUser
					v-for="userId in items"
					:key="userId"
					:userId="userId"
					@clickItem="$emit('clickItem', $event)"
				/>
			</div>
		</div>
	`,
};
