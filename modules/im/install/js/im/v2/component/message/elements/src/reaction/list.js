import { EventEmitter } from 'main.core.events';
import { reactionType as Reaction } from 'ui.reactions-select';

import { DialogScrollThreshold, EventType, ChatType, ChatActionType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChannelManager } from 'im.v2.lib.channel';

import { ReactionItem } from './components/item';
import { ReactionService } from './classes/reaction-service';

import './list.css';

import type { JsonObject } from 'main.core';
import type { ImModelReactions, ImModelMessage, ImModelChat } from 'im.v2.model';

type ReactionType = $Values<typeof Reaction>;

// @vue/component
export const ReactionList = {
	name: 'ReactionList',
	components: { ReactionItem },
	props:
	{
		messageId: {
			type: [String, Number],
			required: true,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
	},
	data(): JsonObject
	{
		return {
			mounted: false,
		};
	},
	computed:
	{
		Reaction: () => Reaction,
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/getByChatId'](this.message.chatId);
		},
		reactionsData(): ImModelReactions
		{
			return this.$store.getters['messages/reactions/getByMessageId'](this.messageId);
		},
		reactionCounters(): {[ReactionType]: number}
		{
			return this.reactionsData?.reactionCounters ?? {};
		},
		ownReactions(): Set<ReactionType>
		{
			return this.reactionsData?.ownReactions ?? new Set();
		},
		showReactionsContainer(): boolean
		{
			return Object.keys(this.reactionCounters).length > 0;
		},
		isChannel(): boolean
		{
			return ChannelManager.isChannel(this.dialog.dialogId);
		},
		showAvatars(): boolean
		{
			return !this.isChannel;
		},
	},
	watch:
	{
		showReactionsContainer(newValue, oldValue)
		{
			if (!oldValue && newValue)
			{
				EventEmitter.emit(EventType.dialog.scrollToBottom, {
					chatId: this.message.chatId,
					threshold: DialogScrollThreshold.nearTheBottom,
					animation: false,
				});
			}
		},
	},
	mounted()
	{
		this.mounted = true;
	},
	methods:
	{
		onReactionSelect(reaction: ReactionType, event: {animateItemFunction: () => void})
		{
			const permissionManager = PermissionManager.getInstance();
			if (!permissionManager.canPerformAction(ChatActionType.setReaction, this.dialog.dialogId))
			{
				return;
			}

			const { animateItemFunction } = event;
			if (this.ownReactions?.has(reaction))
			{
				this.getReactionService().removeReaction(this.messageId, reaction);

				return;
			}

			this.getReactionService().setReaction(this.messageId, reaction);
			animateItemFunction();
		},
		getReactionUsers(reaction: ReactionType): number[]
		{
			const users = this.reactionsData.reactionUsers[reaction];
			if (!users)
			{
				return [];
			}

			return [...users];
		},
		getReactionService(): ReactionService
		{
			if (!this.reactionService)
			{
				this.reactionService = new ReactionService();
			}

			return this.reactionService;
		},
	},
	template: `
		<div v-if="showReactionsContainer" class="bx-im-reaction-list__container bx-im-reaction-list__scope">
			<template v-for="reactionType in Reaction">
				<ReactionItem
					v-if="reactionCounters[reactionType] > 0"
					:key="reactionType + messageId"
					:messageId="messageId"
					:type="reactionType"
					:counter="reactionCounters[reactionType]"
					:users="getReactionUsers(reactionType)"
					:selected="ownReactions.has(reactionType)"
					:animate="mounted"
					:showAvatars="showAvatars"
					:contextDialogId="contextDialogId"
					@click="onReactionSelect(reactionType, $event)"
				/>
			</template>
		</div>
	`,
};
