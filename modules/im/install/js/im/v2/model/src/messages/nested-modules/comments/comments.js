import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { formatFieldsWithConfig } from 'im.v2.model';

import { commentFieldsConfig } from './format/field-config';

import type { JsonObject } from 'main.core';
import type { ActionTree, MutationTree, GetterTree } from 'ui.vue3.vuex';
import type { ImModelCommentInfo, ImModelMessage } from 'im.v2.model';
import type { RawCommentInfo } from 'im.v2.provider.service';

const LAST_USERS_TO_SHOW = 3;

type CommentsState = {
	collection: {
		[messageId: string]: ImModelCommentInfo
	},
	layout: {
		opened: boolean,
		channelDialogId: string,
		postId: number,
	}
};

export class CommentsModel extends BuilderModel
{
	getState(): CommentsState
	{
		return {
			collection: {},
			layout: {
				opened: false,
				channelDialogId: '',
				postId: 0,
			},
		};
	}

	getElementState(): ImModelCommentInfo
	{
		return {
			chatId: 0,
			lastUserIds: [],
			messageCount: 0,
			messageId: 0,
			isUserSubscribed: false,
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function messages/comments/getByMessageId */
			getByMessageId: (state: CommentsState) => (messageId: number): ?ImModelCommentInfo => {
				return state.collection[messageId] ?? this.getElementState();
			},
			/** @function messages/comments/getMessageIdByChatId */
			getMessageIdByChatId: (state: CommentsState) => (chatId: number): ?number => {
				const collection = Object.values(state.collection);
				const foundItem = collection.find((item) => {
					return item.chatId === chatId;
				});

				return foundItem?.messageId;
			},
			/** @function messages/comments/isUserSubscribed */
			isUserSubscribed: (state: CommentsState) => (messageId: number): boolean => {
				const element = state.collection[messageId];
				if (!element && this.#isMessageAuthor(messageId))
				{
					return true;
				}

				return element?.isUserSubscribed ?? false;
			},
			/** @function messages/comments/areOpened */
			areOpened: (state: CommentsState): boolean => {
				return state.layout.opened;
			},
			/** @function messages/comments/areOpenedForChannel */
			areOpenedForChannel: (state: CommentsState) => (channelDialogId: string): boolean => {
				return state.layout.channelDialogId === channelDialogId;
			},
			/** @function messages/comments/areOpenedForChannelPost */
			areOpenedForChannelPost: (state: CommentsState) => (postId: number): boolean => {
				return state.layout.postId === postId;
			},
			/** @function messages/comments/getOpenedChannelId */
			getOpenedChannelId: (state: CommentsState): string => {
				return state.layout.channelDialogId ?? '';
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function messages/comments/set */
			set: (store, rawPayload: RawCommentInfo[] | RawCommentInfo) => {
				let payload = rawPayload;
				if (!payload)
				{
					return;
				}

				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload = payload.map((item: RawCommentInfo) => {
					const currentItem: ImModelCommentInfo = store.state.collection[item.messageId];
					if (currentItem)
					{
						return { ...currentItem, ...this.#formatFields(item) };
					}

					return {
						...this.getElementState(),
						isUserSubscribed: this.#isMessageAuthor(item.messageId),
						...this.#formatFields(item),
					};
				});

				store.commit('set', payload);
			},
			/** @function messages/comments/setLastUser */
			setLastUser: (store, payload: { messageId: number, newUserId: number }) => {
				const { messageId, newUserId } = payload;
				const currentItem = store.state.collection[messageId];
				if (!currentItem || newUserId === 0)
				{
					return;
				}

				store.commit('setLastUser', payload);
			},
			/** @function messages/comments/subscribe */
			subscribe: (store, messageId: number) => {
				Core.getStore().dispatch('messages/comments/set', {
					messageId,
					isUserSubscribed: true,
				});
			},
			/** @function messages/comments/unsubscribe */
			unsubscribe: (store, messageId: number) => {
				Core.getStore().dispatch('messages/comments/set', {
					messageId,
					isUserSubscribed: false,
				});
			},
			/** @function messages/comments/setOpened */
			setOpened: (store, payload: { channelDialogId: string }) => {
				store.commit('setOpened', payload);
			},
			/** @function messages/comments/setClosed */
			setClosed: (store) => {
				store.commit('setClosed');
			},
		};
	}

	/* eslint-disable no-param-reassign */
	getMutations(): MutationTree
	{
		return {
			set: (state: CommentsState, payload: RawCommentInfo[]) => {
				payload.forEach((item) => {
					state.collection[item.messageId] = item;
				});
			},
			setLastUser: (state: CommentsState, payload: { messageId: number, newUserId: number }) => {
				const { messageId, newUserId } = payload;
				const { lastUserIds: currentUsers } = state.collection[messageId];
				if (currentUsers.includes(newUserId))
				{
					return;
				}

				if (currentUsers.length < LAST_USERS_TO_SHOW)
				{
					currentUsers.unshift(newUserId);

					return;
				}

				currentUsers.pop();
				currentUsers.unshift(newUserId);
			},
			setOpened: (state: CommentsState, payload: { channelDialogId: string, commentsPostId: number }) => {
				const { channelDialogId, commentsPostId } = payload;

				state.layout = {
					opened: true,
					channelDialogId,
					postId: commentsPostId,
				};
			},
			setClosed: (state: CommentsState) => {
				state.layout = {
					opened: false,
					channelDialogId: '',
					commentsPostId: 0,
				};
			},
		};
	}

	#formatFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, commentFieldsConfig);
	}

	#isMessageAuthor(messageId: number): boolean
	{
		const message: ImModelMessage = Core.getStore().getters['messages/getById'](messageId);

		return message?.authorId === Core.getUserId();
	}
}
