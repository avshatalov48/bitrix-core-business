import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';

import type { Store, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelReactions } from 'im.v2.model';

export const Reaction = Object.freeze({
	like: 'like',
	kiss: 'kiss',
	laugh: 'laugh',
	wonder: 'wonder',
	cry: 'cry',
	angry: 'angry',
	facepalm: 'facepalm',
});

type ReactionType = $Values<typeof Reaction>;

type RawReactions = {
	messageId: number,
	reactionCounters: ReactionCounters,
	reactionUsers: ReactionUsers,
	ownReactions: ReactionType[]
};
type ReactionCounters = {[reactionType: string]: number};
type ReactionUsers = {[reactionType: string]: number[]};
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
			collection: {},
		};
	}

	getElementState(): ImModelReactions
	{
		return {
			reactionCounters: {},
			reactionUsers: {},
			ownReactions: new Set(),
		};
	}

	getGetters(): GetterTree
	{
		return {
			getByMessageId: (state: ReactionsState) => (messageId: number): ?ImModelReactions => {
				return state.collection[messageId];
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			set: (store: Store, payload: RawReactionsList) => {
				store.commit('set', this.prepareSetPayload(payload));
			},
			setReaction: (
				store: Store,
				payload: {messageId: number, userId: number, reaction: ReactionType},
			) => {
				if (!Reaction[payload.reaction])
				{
					return;
				}

				if (!store.state.collection[payload.messageId])
				{
					store.commit('initCollection', payload.messageId);
				}

				store.commit('setReaction', payload);
			},
			removeReaction: (
				store: Store,
				payload: {messageId: number, userId: number, reaction: ReactionType},
			) => {
				if (!store.state.collection[payload.messageId] || !Reaction[payload.reaction])
				{
					return;
				}
				store.commit('removeReaction', payload);
			},
			clearCollection: (store: Store) => {
				store.commit('clearCollection');
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			set: (state: ReactionsState, payload: RawReactionsList) => {
				payload.forEach((item) => {
					const newItem = {
						reactionCounters: item.reactionCounters,
						reactionUsers: item.reactionUsers,
					};

					const currentItem = state.collection[item.messageId];
					const newOwnReaction = Boolean(item.ownReactions);
					if (newOwnReaction)
					{
						newItem.ownReactions = item.ownReactions;
					}
					else
					{
						newItem.ownReactions = currentItem ? currentItem.ownReactions : new Set();
					}

					// eslint-disable-next-line no-param-reassign
					state.collection[item.messageId] = newItem;
				});
			},
			setReaction: (
				state: ReactionsState,
				payload: {messageId: number, userId: number, reaction: ReactionType},
			) => {
				const { messageId, userId, reaction } = payload;
				const reactions = state.collection[messageId];
				if (Core.getUserId() === userId)
				{
					this.removeAllCurrentUserReactions(state, messageId);
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
				payload: {messageId: number, userId: number, reaction: ReactionType},
			) => {
				const { messageId, userId, reaction } = payload;
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
			},
			initCollection: (state: ReactionsState, messageId: number) => {
				if (!state.collection[messageId])
				{
					// eslint-disable-next-line no-param-reassign
					state.collection[messageId] = this.getElementState();
				}
			},
			clearCollection: (state: ReactionsState) => {
				// eslint-disable-next-line no-param-reassign
				state.collection = {};
			},
		};
	}

	removeAllCurrentUserReactions(state: ReactionsState, messageId: Number)
	{
		const reactions = state.collection[messageId];
		reactions.ownReactions.forEach((reaction) => {
			reactions.reactionUsers[reaction]?.delete(Core.getUserId());
			reactions.reactionCounters[reaction]--;
			if (reactions.reactionCounters[reaction] === 0)
			{
				delete reactions.reactionCounters[reaction];
			}
		});

		reactions.ownReactions = new Set();
	}

	prepareSetPayload(payload: RawReactionsList): {
		messageId: number,
		reactionCounters: ReactionCounters,
		reactionUsers: ReactionUsers
	}
	{
		return payload.map((item) => {
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
				reactionCounters,
				reactionUsers,
			};

			if (item.ownReactions?.length > 0)
			{
				result.ownReactions = new Set(item.ownReactions);
			}

			return result;
		});
	}
}
