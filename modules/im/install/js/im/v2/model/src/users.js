import {Type, Loc} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {Utils} from 'im.v2.lib.utils';
import {UserStatus, BotType, Color} from 'im.v2.const';

export class UsersModel extends BuilderModel
{
	getName()
	{
		return 'users';
	}

	getState()
	{
		return {
			collection: {},
			onlineList: [],
			mobileOnlineList: [],
			absentList: [],
			botList: {}
		};
	}

	getElementState(params = {})
	{
		const {id = 0} = params;

		return {
			id,
			name: '',
			firstName: '',
			lastName: '',
			avatar: '',
			color: Color.base,
			workPosition: '',
			gender: 'M',
			extranet: false,
			network: false,
			bot: false,
			connector: false,
			externalAuthId: 'default',
			status: '',
			idle: false,
			lastActivityDate: false,
			mobileLastDate: false,
			isOnline: false,
			isMobileOnline: false,
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
			}
		};
	}

	getGetters()
	{
		return {
			get: state => (userId, getTemporary = false) =>
			{
				userId = Number.parseInt(userId, 10);

				if (userId <= 0)
				{
					if (getTemporary)
					{
						userId = 0;
					}
					else
					{
						return null;
					}
				}

				const user = state.collection[userId];

				if (!getTemporary && !user)
				{
					return null;
				}
				else if (getTemporary && !user)
				{
					return this.getElementState({id: userId});
				}

				return user;
			},
			getBlank: () => params =>
			{
				return this.getElementState(params);
			},
			getList: state => (userList) => {
				const result = [];

				if (!Array.isArray(userList))
				{
					return null;
				}

				userList.forEach(id => {
					if (state.collection[id])
					{
						result.push(state.collection[id]);
					}
					else
					{
						result.push(this.getElementState({id}));
					}
				});

				return result;
			},
			hasBirthday: state => userId => {
				userId = Number.parseInt(userId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return false;
				}

				return user.isBirthday;
			},
			hasVacation: state => userId => {
				userId = Number.parseInt(userId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return false;
				}

				return user.isAbsent;
			},
			getStatus: state => userId => {
				userId = Number.parseInt(userId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return false;
				}

				if (!user.isOnline)
				{
					return '';
				}

				if (user.isMobileOnline)
				{
					return UserStatus.mobileOnline;
				}
				else if (user.idle)
				{
					// away by time
					return UserStatus.idle;
				}
				else
				{
					// manually selected status (online, away, dnd, break)
					return user.status;
				}
			},
			getLastOnline: state => userId => {
				userId = Number.parseInt(userId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return '';
				}

				return Utils.user.getLastDateText(user);
			},
			getPosition: state => userId => {
				userId = Number.parseInt(userId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user)
				{
					return '';
				}

				if (user.workPosition)
				{
					return user.workPosition;
				}

				return Loc.getMessage('IM_MODEL_USERS_DEFAULT_NAME');
			},
			getBotType: state => userId => {
				userId = Number.parseInt(userId, 10);

				const user = state.collection[userId];
				if (userId <= 0 || !user || !user.bot || !state.botList[userId])
				{
					return '';
				}

				const botType = state.botList[userId].type;

				if (!BotType[botType])
				{
					return BotType.bot;
				}

				return botType;
			}
		};
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map(user => {
					return this.validate(user);
				}).forEach(user => {
					const existingUser = store.state.collection[user.id];
					if (existingUser)
					{
						store.commit('update', {
							id: user.id,
							fields: user
						});
					}
					else
					{
						store.commit('add', {
							id: user.id,
							fields: {...this.getElementState(), ...user}
						});
					}
				});
			},
			add: (store, payload) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map(user => {
					return this.validate(user);
				}).forEach(user => {
					const existingUser = store.state.collection[user.id];
					if (!existingUser)
					{
						store.commit('add', {
							id: user.id,
							fields: {...this.getElementState(), ...user}
						});
					}
				});
			},
			update: (store, payload) =>
			{
				payload.id = Number.parseInt(payload.id, 10);

				const user = store.state.collection[payload.id];
				if (!user)
				{
					return false;
				}

				store.commit('update', {
					id: payload.id,
					fields: this.validate(payload.fields)
				});
			},
			delete: (store, payload) =>
			{
				store.commit('delete', payload.id);
			},
			setBotList: (store, payload) =>
			{
				store.commit('setBotList', payload);
			},
			setStatus: (store, payload: {status: string}) =>
			{
				store.commit('update', {
					id: Core.getUserId(),
					fields: this.validate(payload)
				});
			}
		};
	}

	getMutations()
	{
		return {
			add: (state, payload) =>
			{
				state.collection[payload.id] = payload.fields;
				const user = state.collection[payload.id];

				if (Utils.user.isOnline(user.lastActivityDate))
				{
					user.isOnline = true;
					this.addToOnlineList(user.id);
				}

				if (Utils.user.isMobileOnline(user.lastActivityDate, user.mobileLastDate))
				{
					user.isMobileOnline = true;
					this.addToMobileOnlineList(user.id);
				}

				if (user.birthday && Utils.user.isBirthdayToday(user.birthday))
				{
					user.isBirthday = true;
					setTimeout(() => {
						user.isBirthday = false;
					}, Utils.date.getTimeToNextMidnight());
				}

				if (user.absent)
				{
					user.isAbsent = true;
					this.addToAbsentList(user.id);
				}

				this.startOnlineCheckInterval();
				this.startAbsentCheckInterval();
			},
			update: (state, payload) =>
			{
				const user = state.collection[payload.id];
				if (Utils.user.isOnline(payload.fields.lastActivityDate))
				{
					user.isOnline = true;
					this.addToOnlineList(payload.fields.id);
				}

				if (Utils.user.isMobileOnline(payload.fields.lastActivityDate, payload.fields.mobileLastDate))
				{
					user.isMobileOnline = true;
					this.addToMobileOnlineList(payload.fields.id);
				}

				if (payload.fields.absent === false)
				{
					state.absentList = state.absentList.filter(element => {
						return element !== payload.id;
					});
					state.collection[payload.id].isAbsent = false;
				}
				else if (Type.isDate(payload.fields.absent))
				{
					state.collection[payload.id].isAbsent = true;
					this.addToAbsentList(payload.id);
				}

				state.collection[payload.id] = {...state.collection[payload.id], ...payload.fields};
			},
			delete: (state, payload) =>
			{
				delete state.collection[payload.id];
			},
			setBotList: (state, payload) =>
			{
				state.botList = payload;
			}
		};
	}

	validate(fields)
	{
		const result = {};

		if (Type.isNumber(fields.id) || Type.isString(fields.id))
		{
			result.id = Number.parseInt(fields.id, 10);
		}

		if (Type.isStringFilled(fields.first_name))
		{
			fields.firstName = fields.first_name;
		}
		if (Type.isStringFilled(fields.last_name))
		{
			fields.lastName = fields.last_name;
		}
		if (Type.isStringFilled(fields.firstName))
		{
			result.firstName = Utils.text.htmlspecialcharsback(fields.firstName);
		}
		if (Type.isStringFilled(fields.lastName))
		{
			result.lastName = Utils.text.htmlspecialcharsback(fields.lastName);
		}
		if (Type.isStringFilled(fields.name))
		{
			fields.name = Utils.text.htmlspecialcharsback(fields.name);
			result.name = fields.name;
		}

		if (Type.isStringFilled(fields.color))
		{
			result.color = fields.color;
		}

		if (Type.isStringFilled(fields.avatar))
		{
			result.avatar = this.prepareAvatar(fields.avatar);
		}

		if (Type.isStringFilled(fields.work_position))
		{
			fields.workPosition = fields.work_position;
		}
		if (Type.isStringFilled(fields.workPosition))
		{
			result.workPosition = Utils.text.htmlspecialcharsback(fields.workPosition);
		}

		if (Type.isStringFilled(fields.gender))
		{
			result.gender = fields.gender === 'F'? 'F': 'M';
		}

		if (Type.isStringFilled(fields.birthday))
		{
			result.birthday = fields.birthday;
		}

		if (Type.isBoolean(fields.extranet))
		{
			result.extranet = fields.extranet;
		}

		if (Type.isBoolean(fields.network))
		{
			result.network = fields.network;
		}

		if (Type.isBoolean(fields.bot))
		{
			result.bot = fields.bot;
		}

		if (Type.isBoolean(fields.connector))
		{
			result.connector = fields.connector;
		}

		if (Type.isStringFilled(fields.external_auth_id))
		{
			fields.externalAuthId = fields.external_auth_id;
		}
		if (Type.isStringFilled(fields.externalAuthId))
		{
			result.externalAuthId = fields.externalAuthId;
		}

		if (Type.isStringFilled(fields.status))
		{
			result.status = fields.status;
		}

		if (!Type.isUndefined(fields.idle))
		{
			result.idle = Utils.date.cast(fields.idle, false);
		}
		if (!Type.isUndefined(fields.last_activity_date))
		{
			fields.lastActivityDate = fields.last_activity_date;
		}
		if (!Type.isUndefined(fields.lastActivityDate))
		{
			result.lastActivityDate = Utils.date.cast(fields.lastActivityDate, false);
		}
		if (!Type.isUndefined(fields.mobile_last_date))
		{
			fields.mobileLastDate = fields.mobile_last_date;
		}
		if (!Type.isUndefined(fields.mobileLastDate))
		{
			result.mobileLastDate = Utils.date.cast(fields.mobileLastDate, false);
		}

		if (!Type.isUndefined(fields.absent))
		{
			result.absent = Utils.date.cast(fields.absent, false);
		}

		if (Array.isArray(fields.departments))
		{
			result.departments = [];
			fields.departments.forEach(departmentId =>
			{
				departmentId = Number.parseInt(departmentId, 10);
				if (departmentId > 0)
				{
					result.departments.push(departmentId);
				}
			});
		}

		if (Type.isPlainObject(fields.phones))
		{
			result.phones = this.preparePhones(fields.phones);
		}

		return result;
	}

	prepareAvatar(avatar: string): string
	{
		let result = '';

		if (!avatar || avatar.endsWith('/js/im/images/blank.gif'))
		{
			result = '';
		}
		else if (avatar.startsWith('http'))
		{
			result = avatar;
		}
		else
		{
			result = Core.getHost() + avatar;
		}

		if (result)
		{
			result = encodeURI(result);
		}

		return result;
	}

	preparePhones(phones): Object
	{
		const result = {};

		if (!Type.isUndefined(phones.work_phone))
		{
			phones.workPhone = phones.work_phone;
		}
		if (Type.isStringFilled(phones.workPhone) || Type.isNumber(phones.workPhone))
		{
			result.workPhone =phones.workPhone.toString();
		}

		if (!Type.isUndefined(phones.personal_mobile))
		{
			phones.personalMobile = phones.personal_mobile;
		}
		if (Type.isStringFilled(phones.personalMobile) || Type.isNumber(phones.personalMobile))
		{
			result.personalMobile = phones.personalMobile.toString();
		}

		if (!Type.isUndefined(phones.personal_phone))
		{
			phones.personalPhone = phones.personal_phone;
		}
		if (Type.isStringFilled(phones.personalPhone) || Type.isNumber(phones.personalPhone))
		{
			result.personalPhone = phones.personalPhone.toString();
		}

		if (!Type.isUndefined(phones.inner_phone))
		{
			phones.innerPhone = phones.inner_phone;
		}
		if (Type.isStringFilled(phones.innerPhone) || Type.isNumber(phones.innerPhone))
		{
			result.innerPhone = phones.innerPhone.toString();
		}

		return result;
	}

	addToOnlineList(id)
	{
		const state = this.store.state.users;
		if (!state.onlineList.includes(id))
		{
			state.onlineList.push(id);
		}
	}

	addToMobileOnlineList(id)
	{
		const state = this.store.state.users;
		if (!state.mobileOnlineList.includes(id))
		{
			state.mobileOnlineList.push(id);
		}
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
		if (this.absentCheckInterval)
		{
			return true;
		}

		const TIME_TO_NEXT_DAY = 1000*60*60*24;
		this.absentCheckInterval = setTimeout(() => {
			setInterval(() => {
				const state = this.store.state.users;
				state.absentList.forEach(userId => {
					const user = state.collection[userId];
					if (!user)
					{
						return;
					}
					const currentTime = Date.now();
					const absentEnd = new Date(user.absent).getTime();

					if (absentEnd <= currentTime)
					{
						state.absentList = state.absentList.filter(element => {
							return element !== userId;
						});
						user.isAbsent = false;
					}
				});
			}, TIME_TO_NEXT_DAY);
		}, Utils.date.getTimeToNextMidnight());
	}

	startOnlineCheckInterval()
	{
		if (this.onlineCheckInterval)
		{
			return true;
		}

		const ONE_MINUTE = 60000;
		this.onlineCheckInterval = setInterval(() => {
			const state = this.store.state.users;

			state.onlineList.forEach(userId => {
				const user = state.collection[userId];
				if (!user)
				{
					return;
				}

				if (Utils.user.isOnline(user.lastActivityDate))
				{
					user.isOnline = true;
				}
				else
				{
					user.isOnline = false;
					state.onlineList = state.onlineList.filter(element => element !== userId);
				}
			});

			state.mobileOnlineList.forEach(userId => {
				const user = state.collection[userId];
				if (!user)
				{
					return;
				}

				if (Utils.user.isMobileOnline(user.lastActivityDate, user.mobileLastDate))
				{
					user.isMobileOnline = true;
				}
				else
				{
					user.isMobileOnline = false;
					state.mobileOnlineList = state.mobileOnlineList.filter(element => element !== userId);
				}
			});
		}, ONE_MINUTE);
	}
}