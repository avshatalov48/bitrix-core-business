import {BuilderModel} from 'ui.vue3.vuex';
import {reactionType as Reaction} from 'ui.reactions-select';

import {Core} from 'im.v2.application.core';

import type {Store, GetterTree, ActionTree, MutationTree} from 'ui.vue3.vuex';
import type {ImModelReactions} from 'im.v2.model';

type ReactionType = $Values<typeof Reaction>;

type RawReactions = {
	messageId: number,
	reactionCounters: {[reactionType: string]: number},
	reactionUsers: {[reactionType: string]: number[]},
	ownReactions: ReactionType[]
};
type RawReactionsList = RawReactions[];

type ReactionsState = {
	collection: {
		[messageId: string]: ImModelReactions
	}
};

const USERS_TO_SHOW = 5;

export class ReactionsModel extends BuilderModel
{
	getState(): ReactionsState
	{
		return {
			collection: {}
		};
	}

	getElementState()
	{
		return {
			reactionCounters: {},
			reactionUsers: {},
			ownReactions: new Set()
		};
	}

	getGetters(): GetterTree
	{
		return {
			getByMessageId: (state: ReactionsState) => (messageId: number): ?ImModelReactions =>
			{
				return state.collection[messageId];
			}
		};
	}

	getActions(): ActionTree
	{
		return {
			set: (store: Store, payload: RawReactionsList) =>
			{
				store.commit('set', this.prepareSetPayload(payload));
			},
			setReaction: (
				store: Store,
				payload: {messageId: number, userId: number, reaction: ReactionType}
			) =>
			{
				if (!Reaction[payload.reaction])
				{
					return;
				}
				if (!store.state.collection[payload.messageId])
				{
					store.state.collection[payload.messageId] = this.getElementState();
				}

				store.commit('setReaction', payload);
			},
			removeReaction: (
				store: Store,
				payload: {messageId: number, userId: number, reaction: ReactionType}
			) =>
			{
				if (!store.state.collection[payload.messageId] || !Reaction[payload.reaction])
				{
					return;
				}
				store.commit('removeReaction', payload);
			}
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: ReactionsState, payload: RawReactionsList) =>
			{
				payload.forEach(item => {
					const newItem = {
						reactionCounters: item.reactionCounters,
						reactionUsers: item.reactionUsers
					};

					const currentItem = state.collection[item.messageId];
					const newOwnReaction = !!item.ownReactions;
					if (newOwnReaction)
					{
						newItem.ownReactions = item.ownReactions;
					}
					else
					{
						newItem.ownReactions = currentItem ? currentItem.ownReactions : new Set();
					}

					state.collection[item.messageId] = newItem;
				});
			},
			setReaction: (
				state: ReactionsState,
				payload: {messageId: number, userId: number, reaction: ReactionType}
			) =>
			{
				const {messageId, userId, reaction} = payload;
				const reactions = state.collection[messageId];
				if (Core.getUserId() === userId)
				{
					this.removeAllCurrentUserReactions(reactions);
					reactions.ownReactions.add(reaction);
				}

				if (!reactions.reactionCounters[reaction])
				{
					reactions.reactionCounters[reaction] = 0;
				}
				const currentCounter = reactions.reactionCounters[reaction];
				if (currentCounter + 1 <= USERS_TO_SHOW)
				{
					if (!reactions.reactionUsers[reaction])
					{
						reactions.reactionUsers[reaction] = new Set();
					}
					reactions.reactionUsers[reaction].add(userId);
				}

				reactions.reactionCounters[reaction]++;
			},
			removeReaction: (
				state: ReactionsState,
				payload: {messageId: number, userId: number, reaction: ReactionType}
			) =>
			{
				const {messageId, userId, reaction} = payload;
				const reactions = state.collection[messageId];

				if (Core.getUserId() === userId)
				{
					reactions.ownReactions.delete(reaction);
				}

				reactions.reactionUsers[reaction]?.delete(userId);
				reactions.reactionCounters[reaction]--;
				if (reactions.reactionCounters[reaction] === 0)
				{
					delete reactions.reactionCounters[reaction];
				}
			}
		};
	}

	removeAllCurrentUserReactions(reactions: ImModelReactions)
	{
		reactions.ownReactions.forEach(reaction => {
			reactions.reactionUsers[reaction]?.delete(Core.getUserId());
			reactions.reactionCounters[reaction]--;
			if (reactions.reactionCounters[reaction] === 0)
			{
				delete reactions.reactionCounters[reaction];
			}
		});

		reactions.ownReactions = new Set();
	}

	prepareSetPayload(payload: RawReactionsList)
	{
		return payload.map(item => {
			const reactionUsers = {};
			Object.entries(item.reactionUsers).forEach(([reaction, users]) => {
				reactionUsers[reaction] = new Set(users);
			});

			const reactionCounters = {};
			Object.entries(item.reactionCounters).forEach(([reaction, counter]) => {
				reactionCounters[reaction] = counter;
			});

			const result = {
				messageId: item.messageId,
				reactionCounters: reactionCounters,
				reactionUsers: reactionUsers
			};

			if (item.ownReactions?.length > 0)
			{
				result.ownReactions = new Set(item.ownReactions);
			}

			return result;
		});
	}
}