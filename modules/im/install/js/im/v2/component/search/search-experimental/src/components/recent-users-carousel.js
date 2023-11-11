import { Core } from 'im.v2.application.core';

import { CarouselUser } from './carousel-user';

import '../css/recent-users-carousel.css';

import type { ImModelUser, ImModelRecentItem } from 'im.v2.model';

const recentUsersLimit = 6;

// @vue/component
export const RecentUsersCarousel = {
	name: 'RecentUsersCarousel',
	components: { CarouselUser },
	emits: ['clickItem'],
	computed:
	{
		users(): ImModelUser[]
		{
			const recentUsers = [];

			this.$store.getters['recent/getSortedCollection'].forEach((recentItem: ImModelRecentItem) => {
				if (this.isChat(recentItem.dialogId))
				{
					return;
				}
				const user: ImModelUser = this.$store.getters['users/get'](recentItem.dialogId, true);

				recentUsers.push(user);
			});

			return recentUsers.filter((user: ImModelUser) => {
				return !user.bot && user.id !== Core.getUserId();
			}).slice(0, recentUsersLimit);
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
				<CarouselUser 
					v-for="user in users"
					:key="user.id"
					:userId="user.id"
					@clickItem="$emit('clickItem', $event)"
				/>
			</div>
		</div>
	`,
};
