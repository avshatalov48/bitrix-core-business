import { BuilderModel, GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import type { InputActionType } from 'im.v2.lib.input-action';

export type InputActionState = {
	collection: {
		[dialogId: string]: ChatInputActions,
	},
};

export type ChatInputActions = InputActionUserRecord[];

type InputActionUserRecord = {
	type: InputActionType,
	userId: number,
	userName: string,
	statusMessageCode: string | null,
	duration: number | null,
};

type InputActionPayload = {
	type: InputActionType,
	dialogId: string,
	userId: number,
	userName?: string,
	statusMessageCode: string | null,
	duration: number | null,
};

/* eslint-disable no-param-reassign */
export class InputActionsModel extends BuilderModel
{
	getState(): InputActionState
	{
		return {
			collection: {},
		};
	}

	getGetters(): GetterTree
	{
		return {
			/** @function chats/inputActions/getByDialogId */
			getByDialogId: (state: InputActionState) => (dialogId: string): ?ChatInputActions => {
				const chatActionList = state.collection[dialogId];
				if (!chatActionList || chatActionList.length === 0)
				{
					return null;
				}

				return chatActionList;
			},
			/** @function chats/inputActions/isChatActive */
			isChatActive: (state: InputActionState) => (dialogId: string): boolean => {
				const chatActionList = state.collection[dialogId];
				if (!chatActionList)
				{
					return false;
				}

				return chatActionList.length > 0;
			},
			/** @function chats/inputActions/isActionActive */
			isActionActive: (state: InputActionState) => (payload: InputActionPayload): boolean => {
				const { dialogId, userId } = payload;
				if (!state.collection[dialogId])
				{
					return false;
				}

				const chatActionList = state.collection[dialogId];

				return this.isAlreadyActive(chatActionList, userId);
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function chats/inputActions/start */
			start: (store, payload: InputActionPayload) => {
				const { dialogId, userId } = payload;
				if (!store.state.collection[dialogId])
				{
					store.commit('initCollection', dialogId);
				}

				const chatActionList = store.state.collection[dialogId];
				const isAlreadyActive = this.isAlreadyActive(chatActionList, userId);
				if (isAlreadyActive)
				{
					return;
				}

				store.commit('start', payload);
			},
			/** @function chats/inputActions/stop */
			stop: (store, payload: InputActionPayload) => {
				const { dialogId, userId } = payload;
				const chatActionList = store.state.collection[dialogId];
				if (!chatActionList)
				{
					return;
				}

				const isAlreadyActive = this.isAlreadyActive(chatActionList, userId);
				if (!isAlreadyActive)
				{
					return;
				}

				store.commit('stop', payload);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			start: (state: InputActionState, payload: InputActionPayload) => {
				const { dialogId, type, userId, userName, duration, statusMessageCode } = payload;

				const chatActionList = state.collection[dialogId];
				chatActionList.push({
					type,
					userId,
					userName,
					duration,
					statusMessageCode,
				});
			},
			stop: (state: InputActionState, payload: InputActionPayload) => {
				const { dialogId, userId } = payload;

				const chatActionList = state.collection[dialogId];
				state.collection[dialogId] = chatActionList.filter((userRecord) => {
					return userRecord.userId !== userId;
				});
			},
			initCollection: (state: InputActionState, dialogId: string) => {
				state.collection[dialogId] = [];
			},
		};
	}

	isAlreadyActive(list: InputActionUserRecord[], userId: number): boolean
	{
		return list.some((userRecord) => userRecord.userId === userId);
	}
}
