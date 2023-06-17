import {EventEmitter} from 'main.core.events';
import {reactionType as Reaction} from 'ui.reactions-select';

import {DialogScrollThreshold, EventType} from 'im.v2.const';

import {ReactionItem} from './item';
import {ReactionService} from '../classes/reaction-service';

import '../css/list.css';

import type {ImModelReactions, ImModelMessage} from 'im.v2.model';

type ReactionType = $Values<typeof Reaction>;

// @vue/component
export const ReactionList = {
	components: {ReactionItem},
	props:
	{
		messageId: {
			type: Number,
			required: true
		},
	},
	data()
	{
		return {
			mounted: false
		};
	},
	computed:
	{
		Reaction: () => Reaction,
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
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
		}
	},
	watch:
	{
		showReactionsContainer(newValue, oldValue)
		{
			if (!oldValue && newValue)
			{
				EventEmitter.emit(EventType.dialog.scrollToBottom, {
					chatId: this.message.chatId,
					threshold: DialogScrollThreshold.nearTheBottom
				});
			}
		}
	},
	mounted()
	{
		this.mounted = true;
	},
	methods:
	{
		onReactionSelect(reaction: ReactionType, event: {animateItemFunction: () => void})
		{
			const {animateItemFunction} = event;
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
		}
	},
	template: `
		<div v-if="showReactionsContainer" class="bx-im-reaction-list__container">
			<template v-for="reactionType in Reaction">
				<ReactionItem
					v-if="reactionCounters[reactionType] > 0"
					:key="reactionType"
					:messageId="messageId"
					:type="reactionType"
					:counter="reactionCounters[reactionType]"
					:users="getReactionUsers(reactionType)"
					:selected="ownReactions.has(reactionType)"
					:animate="mounted"
					@click="onReactionSelect(reactionType, $event)"
				/>
			</template>
		</div>
	`
};