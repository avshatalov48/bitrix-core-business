/**
 * Bitrix Messenger
 * Call Application model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';
import {ConferenceUserState} from 'im.const';

export class CallModel extends VuexBuilderModel
{
	getName()
	{
		return 'call';
	}

	getState()
	{
		return {
			users: {}
		}
	}

	getElementState(params = {})
	{
		return {
			id: params.id ? params.id : 0,
			state: ConferenceUserState.Idle,
			talking: false,
			pinned: false,
			cameraState: false,
			microphoneState: false,
			screenState: false,
			floorRequestState: false
		};
	}

	getGetters()
	{
		return {
			getUser: state => (userId) =>
			{
				userId = parseInt(userId, 10);

				if (!state.users[userId])
				{
					return this.getElementState({id: userId});
				}

				return state.users[userId];
			},
			getBlankUser: state => (userId) =>
			{
				userId = parseInt(userId, 10);

				return this.getElementState({id: userId});
			}
		}
	}

	getActions()
	{
		return {
			updateUser: (store, payload) =>
			{
				payload.id = parseInt(payload.id, 10);

				payload.fields = Object.assign(
					{},
					this.validate(payload.fields),
				);

				store.commit('updateUser', payload);
			},
			unpinUser: (store, payload) =>
			{
				store.commit('unpinUser');
			},
		}
	}

	getMutations()
	{
		return {
			updateUser: (state, payload) =>
			{
				if (!state.users[payload.id])
				{
					Vue.set(state.users, payload.id, Object.assign(this.getElementState(), payload.fields, {id: payload.id}));
				}
				else
				{
					state.users[payload.id] = Object.assign(state.users[payload.id], payload.fields);
				}
			},
			unpinUser: (state, payload) =>
			{
				const pinnedUser = Object.values(state.users).find(user => user.pinned === true);

				if (pinnedUser)
				{
					state.users[pinnedUser.id].pinned = false;
				}
			},
		}
	}

	validate(payload)
	{
		const result = {};

		if (Type.isNumber(payload.id) || Type.isString(payload.id))
		{
			result.id = parseInt(payload.id, 10);
		}

		if (ConferenceUserState[payload.state])
		{
			result.state = payload.state;
		}

		if (Type.isBoolean(payload.talking))
		{
			result.talking = payload.talking;
		}

		if (Type.isBoolean(payload.pinned))
		{
			result.pinned = payload.pinned;
		}

		if (Type.isBoolean(payload.cameraState))
		{
			result.cameraState = payload.cameraState;
		}

		if (Type.isBoolean(payload.microphoneState))
		{
			result.microphoneState = payload.microphoneState;
		}

		if (Type.isBoolean(payload.screenState))
		{
			result.screenState = payload.screenState;
		}

		if (Type.isBoolean(payload.floorRequestState))
		{
			result.floorRequestState = payload.floorRequestState;
		}

		return result;
	}

	getStateSaveException()
	{
		return {
			users: false
		}
	}
}