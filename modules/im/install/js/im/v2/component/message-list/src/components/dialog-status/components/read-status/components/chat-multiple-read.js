import { RichLoc } from 'ui.vue3.components.rich-loc';

import { AdditionalUsers } from './additional-users';

import type { ImModelChat } from 'im.v2.model';

// @vue/component
export const ChatMultipleRead = {
	name: 'ChatMultipleRead',
	components: { AdditionalUsers, RichLoc },
	props:
	{
		dialogId: {
			required: true,
			type: String,
		},
	},
	data(): Record
	{
		return {
			showAdditionalUsers: false,
		};
	},
	computed:
	{
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		formattedStatus(): string
		{
			const { countOfViewers, firstViewer } = this.dialog.lastMessageViews;

			return this.loc('IM_MESSAGE_LIST_STATUS_READ_CHAT_PLURAL_MSGVER_2', {
				'#USERS#': firstViewer.userName,
				'#COUNT#': countOfViewers - 1,
			});
		},
	},
	methods:
	{
		onMoreUsersClick(): void
		{
			this.showAdditionalUsers = true;
		},
		onUsersPopupClose(): void
		{
			this.showAdditionalUsers = false;
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<div class="bx-im-dialog-chat-status__text">
			<RichLoc :text="formattedStatus" placeholder="[link]">
				<template #link="{ text }">
					<span class="bx-im-dialog-chat-status__user-count" @click="onMoreUsersClick" ref="moreUsers">
						{{ text }}
					</span>
				</template>
			</RichLoc>
		</div>
		<AdditionalUsers
			v-if="showAdditionalUsers"
			:dialogId="dialogId"
			:bindElement="$refs['moreUsers'] || {}"
			@close="onUsersPopupClose"
		/>
	`,
};
