import { Core } from 'im.v2.application.core';
import { MessageAvatar, AvatarSize } from 'im.v2.component.elements';
import { ActionByRole, ChatType, UserType } from 'im.v2.const';
import { CopilotManager } from 'im.v2.lib.copilot';
import { PermissionManager } from 'im.v2.lib.permission';

import { MessageSelectButton } from './message-select-button';

import type { ImModelChat, ImModelUser } from 'im.v2.model';
import type { AuthorGroupItem } from '../../classes/collection-manager/collection-manager';

// @vue/component
export const AuthorGroup = {
	name: 'AuthorGroup',
	components: { MessageAvatar, MessageSelectButton },
	props:
	{
		item: {
			type: Object,
			required: true,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
		withAvatarMenu: {
			type: Boolean,
			default: true,
		},
	},
	emits: ['avatarClick'],
	computed:
	{
		AvatarSize: () => AvatarSize,
		contextDialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.contextDialogId, true);
		},
		contextUser(): ImModelUser
		{
			return this.$store.getters['users/get'](this.contextDialogId, true);
		},
		isUser(): boolean
		{
			return this.contextDialog.type === ChatType.user;
		},
		isBulkActionsMode(): boolean
		{
			return this.$store.getters['messages/select/getBulkActionsMode'];
		},
		authorGroup(): AuthorGroupItem
		{
			return this.item;
		},
		authorDialogId(): string
		{
			return this.authorGroup.avatar.avatarId;
		},
		firstMessageIdInAuthorGroup(): number
		{
			// this is potentially dangerous.
			// for now, we always have the same avatar in one authorGroup
			// in future it can be different: several support answers (with different avatars) in one authorGroup
			return this.authorGroup.messages[0].id;
		},
		avatarMenuAvailable(): boolean
		{
			if (!this.withAvatarMenu)
			{
				return false;
			}

			const authorUser: ImModelUser = this.$store.getters['users/get'](this.authorDialogId);
			if (!authorUser)
			{
				return false;
			}

			const copilotManager = new CopilotManager();
			if (copilotManager.isCopilotBot(this.authorDialogId))
			{
				return false;
			}

			const isCurrentUser = authorUser.id === Core.getUserId();
			if (isCurrentUser)
			{
				return false;
			}

			const isBotChat = this.isUser && this.contextUser.type === UserType.bot;
			if (isBotChat)
			{
				return false;
			}

			const permissionManager = PermissionManager.getInstance();

			return permissionManager.canPerformActionByRole(ActionByRole.openAvatarMenu, this.contextDialogId);
		},
		containerClasses(): string[]
		{
			const classes = [`--${this.authorGroup.messageType}`];
			if (!this.avatarMenuAvailable)
			{
				classes.push('--no-menu');
			}

			if (this.isBulkActionsMode)
			{
				classes.push('--is-bulk-actions-mode');
			}

			if (this.authorGroup.avatar.isNeeded)
			{
				classes.push('--has-avatar');
			}

			return classes;
		},
	},
	methods:
	{
		isAvatarNeeded(index: number): boolean
		{
			const lastIndexMessageInGroup = this.authorGroup.messages.length - 1;

			return this.authorGroup.avatar.isNeeded && index === lastIndexMessageInGroup;
		},
		onAvatarClick(event: PointerEvent): void
		{
			if (!this.avatarMenuAvailable)
			{
				return;
			}

			this.$emit('avatarClick', {
				dialogId: this.authorGroup.avatar.avatarId,
				$event: event,
			});
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-message-list-author-group__container" :class="containerClasses">
			<template v-for="(message, index) in authorGroup.messages">
				<Transition name="bx-im-select-button-transition">
					<MessageSelectButton v-if="isBulkActionsMode" :message="message" />
				</Transition>
				<div v-if="isAvatarNeeded(index)" class="bx-im-message-list-author-group__avatar">
					<MessageAvatar
						:messageId="firstMessageIdInAuthorGroup"
						:authorId="authorGroup.avatar.avatarId"
						:size="AvatarSize.L"
						@click="onAvatarClick"
					/>
				</div>
				<slot name="message" :message="message" :index="index"></slot>
			</template>
		</div>
	`,
};
