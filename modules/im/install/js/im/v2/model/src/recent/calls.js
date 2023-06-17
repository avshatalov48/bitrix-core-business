import {Type} from 'main.core';
import {BuilderModel, GetterTree, ActionTree, MutationTree} from 'ui.vue3.vuex';

import {RecentCallStatus} from 'im.v2.const';

import type {ImModelCallItem} from 'im.v2.model';

type CallsState = {
	collection: {
		[dialogId: string]: ImModelCallItem
	}
};

export class CallsModel extends BuilderModel
{
	getState()
	{
		return {
			collection: {}
		};
	}

	getElementState()
	{
		return {
			dialogId: 0,
			name: '',
			call: {},
			state: RecentCallStatus.waiting
		};
	}

	getGetters(): GetterTree
	{
		return {
			get: (state: CallsState): ImModelCallItem[] =>
			{
				return Object.values(state.collection);
			}
		};
	}

	getActions(): ActionTree
	{
		return {
			addActiveCall: (store, payload: ImModelCallItem) =>
			{
				const existingCall = Object.values(store.state.collection).find((item: ImModelCallItem) => {
					return item.dialogId === payload.dialogId || item.call.id === payload.call.id;
				});

				if (existingCall)
				{
					store.commit('updateActiveCall', {
						dialogId: existingCall.dialogId,
						fields: this.validateActiveCall(payload)
					});

					return true;
				}

				store.commit('addActiveCall', this.prepareActiveCall(payload));
			},
			updateActiveCall: (store, payload) =>
			{
				const existingCall = store.state.collection[payload.dialogId];
				if (!existingCall)
				{
					return;
				}

				store.commit('updateActiveCall', {
					dialogId: existingCall.dialogId,
					fields: this.validateActiveCall(payload.fields)
				});
			},
			deleteActiveCall: (store, payload) =>
			{
				const existingCall = store.state.collection[payload.dialogId];
				if (!existingCall)
				{
					return;
				}

				store.commit('deleteActiveCall', {
					dialogId: existingCall.dialogId
				});
			}
		};
	}

	getMutations(): MutationTree
	{
		return {
			addActiveCall: (state: CallsState, payload: ImModelCallItem) => {
				state.collection[payload.dialogId] = payload;
			},
			updateActiveCall: (state: CallsState, payload) => {
				state.collection[payload.dialogId] = {
					...state.collection[payload.dialogId],
					...payload.fields
				};
			},
			deleteActiveCall: (state: CallsState, payload) => {
				delete state.collection[payload.dialogId];
			},
		};
	}

	prepareActiveCall(call)
	{
		return {...this.getElementState(), ...this.validateActiveCall(call)};
	}

	validateActiveCall(fields)
	{
		const result = {};

		if (Type.isStringFilled(fields.dialogId) || Type.isNumber(fields.dialogId))
		{
			result.dialogId = fields.dialogId;
		}

		if (Type.isStringFilled(fields.name))
		{
			result.name = fields.name;
		}

		if (Type.isObjectLike(fields.call))
		{
			result.call = fields.call;

			if (fields.call?.associatedEntity?.avatar === '/bitrix/js/im/images/blank.gif')
			{
				result.call.associatedEntity.avatar = '';
			}
		}

		if (RecentCallStatus[fields.state])
		{
			result.state = fields.state;
		}

		return result;
	}
}