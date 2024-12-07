import { Type, Loc, type JsonObject } from 'main.core';
import { BuilderModel, type ActionTree, type GetterTree, type MutationTree } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { UserStatusManager } from 'im.v2.lib.user-status';
import { Color } from 'im.v2.const';
import { formatFieldsWithConfig } from 'im.v2.model';

import { BotsModel } from './nested-modules/bots';
import { userFieldsConfig } from './format/field-config';

import type { User as ImModelUser } from '../type/user';

type UsersState = {
	collection: {[userId: string]: ImModelUser},
	absentList: string[],
};

export class UsersModel extends BuilderModel
{
	getName(): string
	{
		return 'users';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return {
			bots: BotsModel,
		};
	}

	getState(): UsersState
	{
		return {
			collection: {},
			absentList: [],
			absentCheckInterval: null,
		};
	}

	getElementState(params = {}): ImModelUser
	{
		const { id = 0 } = params;

		return {
			id,
			name: '',
			firstName: '',
			lastName: '',
			avatar: '',
			color: Color.base,
			workPosition: '',
			gender: 'M',
			isAdmin: false,
			extranet: false,
			network: false,
			bot: false,
			connector: false,
			externalAuthId: 'default',
			status: '',
			idle: false,
			lastActivityDate: false,
			mobileLastDate: false,
			birthday: false,
			isBirthday: false,
			absent: false,
			isAbsent: false,
			departments: [],
			phones: {
				workPhone: '',
				personalMobile: '',
				personalPhone: '',
				innerPhone: '',
			},
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function users/get */
			get: (state) => (userId, getTemporary = false) => {
				const user = state.collection[userId];

				if (!getTemporary && !user)
				{
					return null;
				}

				if (getTemporary && !user)
				{
					return this.getElementState({ id: userId });
				}

				return user;
			},
			/** @function users/getBlank */
			getBlank: () => (params) => {
				return this.getElementState(params);
			},
			/** @function users/getList */
			getList: (state) => (userList) => {
				const result = [];

				if (!Array.isArray(userList))
				{
					return null;
				}

				userList.forEach((id) => {
					if (state.collection[id])
					{
						result.push(state.collection[id]);
					}
					else
					{
						result.push(this.getElementState({ id }));
					}
				});

				return result;
			},
			/** @function users/hasBirthday */
			hasBirthday: (state) => (rawUserId) => {
				const userId = Number.parseInt(rawUserId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return false;
				}

				return user.isBirthday;
			},
			/** @function users/hasVacation */
			hasVacation: (state) => (rawUserId) => {
				const userId = Number.parseInt(rawUserId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return false;
				}

				return user.isAbsent;
			},
			/** @function users/getLastOnline */
			getLastOnline: (state) => (rawUserId) => {
				const userId = Number.parseInt(rawUserId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return '';
				}

				return Utils.user.getLastDateText(user);
			},
			/** @function users/getPosition */
			getPosition: (state) => (rawUserId) => {
				const userId = Number.parseInt(rawUserId, 10);
				const user: ImModelUser = state.collection[userId];
				const isSupportBot = Core.getStore().getters['users/bots/isSupport'](userId);

				if (userId <= 0 || !user || isSupportBot)
				{
					return '';
				}

				if (user.workPosition)
				{
					return user.workPosition;
				}

				if (user.bot === true)
				{
					return Loc.getMessage('IM_MODEL_USERS_CHAT_BOT');
				}

				return Loc.getMessage('IM_MODEL_USERS_DEFAULT_NAME');
			},
		};
	}

	getActions(): ActionTree
	{
		return {
			/** @function users/set */
			set: (store, rawPayload) => {
				let payload = rawPayload;
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map((user) => {
					return this.formatFields(user);
				}).forEach((user) => {
					const existingUser = store.state.collection[user.id];
					if (existingUser)
					{
						store.commit('update', {
							id: user.id,
							fields: user,
						});
					}
					else
					{
						store.commit('add', {
							id: user.id,
							fields: { ...this.getElementState(), ...user },
						});
					}
				});
			},
			/** @function users/add */
			add: (store, rawPayload) => {
				let payload = rawPayload;
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map((user) => {
					return this.formatFields(user);
				}).forEach((user) => {
					const existingUser = store.state.collection[user.id];
					if (!existingUser)
					{
						store.commit('add', {
							id: user.id,
							fields: { ...this.getElementState(), ...user },
						});
					}
				});
			},
			/** @function users/update */
			update: (store, rawPayload) => {
				const payload = rawPayload;
				payload.id = Number.parseInt(payload.id, 10);

				const user = store.state.collection[payload.id];
				if (!user)
				{
					return;
				}

				const fields = { ...payload.fields, id: payload.id };

				store.commit('update', {
					id: payload.id,
					fields: this.formatFields(fields),
				});
			},
			/** @function users/delete */
			delete: (store, payload) => {
				store.commit('delete', payload.id);
			},
			/** @function users/setStatus */
			setStatus: (store, payload: {status: string}) => {
				store.commit('update', {
					id: Core.getUserId(),
					fields: this.formatFields(payload),
				});
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			add: (state, payload) => {
				// eslint-disable-next-line no-param-reassign
				state.collection[payload.id] = payload.fields;

				UserStatusManager.getInstance().onUserUpdate(payload.fields);
			},
			update: (state, payload) => {
				// eslint-disable-next-line no-param-reassign
				state.collection[payload.id] = { ...state.collection[payload.id], ...payload.fields };

				UserStatusManager.getInstance().onUserUpdate(payload.fields);
			},
			delete: (state, payload) => {
				// eslint-disable-next-line no-param-reassign
				delete state.collection[payload.id];
			},
		};
	}

	formatFields(fields: JsonObject): JsonObject
	{
		const preparedFields: ImModelUser = formatFieldsWithConfig(fields, userFieldsConfig);
		const isBot = preparedFields.bot === true;
		if (isBot)
		{
			Core.getStore().dispatch('users/bots/set', {
				userId: preparedFields.id,
				botData: fields.botData || fields.bot_data,
			});
		}

		return preparedFields;
	}

	addToAbsentList(id)
	{
		const state = this.store.state.users;
		if (!state.absentList.includes(id))
		{
			state.absentList.push(id);
		}
	}

	startAbsentCheckInterval()
	{
		const state = this.store.state.users;
		if (state.absentCheckInterval)
		{
			return;
		}

		const TIME_TO_NEXT_DAY = 1000 * 60 * 60 * 24;
		state.absentCheckInterval = setTimeout(() => {
			setInterval(() => {
				state.absentList.forEach((userId) => {
					const user = state.collection[userId];
					if (!user)
					{
						return;
					}
					const currentTime = Date.now();
					const absentEnd = new Date(user.absent).getTime();

					if (absentEnd <= currentTime)
					{
						state.absentList = state.absentList.filter((element) => {
							return element !== userId;
						});
						user.isAbsent = false;
					}
				});
			}, TIME_TO_NEXT_DAY);
		}, Utils.date.getTimeToNextMidnight());
	}
}
