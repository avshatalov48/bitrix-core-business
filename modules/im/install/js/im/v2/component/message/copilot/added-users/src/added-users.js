import { Messenger } from 'im.public';
import { BaseMessage } from 'im.v2.component.message.base';
import { UserListPopup } from 'im.v2.component.elements';

import './css/copilot-added-users-message.css';

import type { JsonObject } from 'main.core';
import type { ImModelMessage } from 'im.v2.model';

// @vue/component
export const ChatCopilotAddedUsersMessage = {
	name: 'ChatCopilotAddedUsersMessage',
	components: { BaseMessage, UserListPopup },
	props: {
		item: {
			type: Object,
			required: true,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			showMoreUsers: false,
		};
	},
	computed:
	{
		message(): ImModelMessage
		{
			return this.item;
		},
		addedUsers(): {first: number, restUsers: number[]}
		{
			const addedUsers = this.message.componentParams.addedUsers;
			const firstAddedUser = addedUsers.shift();

			return {
				first: firstAddedUser,
				restUsers: addedUsers,
			};
		},
		firstAddedUserName(): string
		{
			return this.$store.getters['users/get'](this.addedUsers.first).name;
		},
		andMoreAddedUsers(): string
		{
			if (this.addedUsers.restUsers.length === 0)
			{
				return '';
			}

			return this.loc('IM_MESSAGE_COPILOT_ADDED_USERS_DESCRIPTION_MORE', {
				'#NAME#': '',
				'#COUNT#': this.addedUsers.restUsers.length,
			});
		},
		preparedDescription(): string
		{
			return this.loc('IM_MESSAGE_COPILOT_ADDED_USERS_DESCRIPTION_MENTION_MSGVER_1', {
				'#BR#': '\n',
			});
		},
	},
	methods:
	{
		loc(phraseCode: string, replacements: {[p: string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
		onFirstAddedUserClick()
		{
			Messenger.openChat(this.addedUsers.first.toString());
		},
		onMoreUsersClick()
		{
			this.showMoreUsers = true;
		},
	},
	template: `
		<BaseMessage
			:dialogId="dialogId"
			:item="item"
			:withContextMenu="false"
			:withReactions="false"
			:withBackground="false"
		>
			<div class="bx-im-message-copilot-added-users__container">
				<div class="bx-im-message-copilot-added-users__image"></div>
				<div class="bx-im-message-copilot-added-users__content">
					<div class="bx-im-message-copilot-added-users__title">
						{{ loc('IM_MESSAGE_COPILOT_ADDED_USERS_TITLE') }}
					</div>
					<div
						class="bx-im-message-copilot-added-users__description"
						:title="preparedDescription"
					>
						{{ preparedDescription }}
					</div>
					<div class="bx-im-message-copilot-added-users__users"> 
						<span class="bx-im-message-copilot-added-users__user" @click="onFirstAddedUserClick">
							{{ firstAddedUserName }}
						</span>
						<span
							v-if="andMoreAddedUsers"
							class="bx-im-message-copilot-added-users__user"
							@click="onMoreUsersClick"
							ref="addedUsersLink"
						>
							{{ andMoreAddedUsers }}
						</span>
					</div>
				</div>
			</div>
			<UserListPopup
				:showPopup="showMoreUsers"
				:userIds="addedUsers.restUsers"
				:contextDialogId="dialogId"
				:bindElement="$refs.addedUsersLink || {}"
				:withAngle="false"
				:forceTop="true"
				@close="showMoreUsers = false"
			/>
		</BaseMessage>
	`,
};
