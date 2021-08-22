/**
 * Bitrix Messenger
 * Users model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {Utils} from "im.lib.utils";

export class UsersModel extends VuexBuilderModel
{
	getName()
	{
		return 'users';
	}

	getState()
	{
		this.startOnlineCheckInterval();

		return {
			host: this.getVariable('host', location.protocol+'//'+location.host),
			collection: {},
			onlineList: [],
			mobileOnlineList: [],
			absentList: []
		}
	}

	getElementState(params = {})
	{
		let {
			id = 0,
			name = this.getVariable('default.name', ''),
			firstName = this.getVariable('default.name', ''),
			lastName = '',
		} = params;

		return {
			id,
			name,
			firstName,
			lastName,
			workPosition: "",
			color: "#048bd0",
			avatar: "",
			gender: "M",
			birthday: false,
			isBirthday: false,
			extranet: false,
			network: false,
			bot: false,
			connector: false,
			externalAuthId: "default",
			status: "online",
			idle: false,
			lastActivityDate: false,
			mobileLastDate: false,
			isOnline: false,
			isMobileOnline: false,
			absent: false,
			isAbsent: false,
			departments: [],
			phones: {
				workPhone: "",
				personalMobile: "",
				personalPhone: "",
				innerPhone: "",
			},
			init: false
		};
	}

	getGetters()
	{
		return {
			get: state => (userId, getTemporary = false) =>
			{
				userId = parseInt(userId);

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

				if (
					!getTemporary
					&& (!state.collection[userId] || !state.collection[userId].init)
				)
				{
					return null;
				}

				if (!state.collection[userId])
				{
					return this.getElementState({id: userId});
				}

				return state.collection[userId];
			},
			getBlank: state => params =>
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
			}
		}
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(user => {
						return Object.assign(
							{},
							this.getElementState(),
							this.validate(Object.assign({}, user), {host: store.state.host}),
							{init: true}
						);
					});
				}
				else
				{
					let result = [];
					result.push(Object.assign(
						{},
						this.getElementState(),
						this.validate(Object.assign({}, payload), {host: store.state.host}),
						{init: true}
					));
					payload = result;
				}

				store.commit('set', payload);
			},
			update: (store, payload) =>
			{
				payload.id = parseInt(payload.id);

				if (
					typeof store.state.collection[payload.id] === 'undefined'
					|| store.state.collection[payload.id].init === false
				)
				{
					return true;
				}

				store.commit('update', {
					id : payload.id,
					fields : this.validate(Object.assign({}, payload.fields), {host: store.state.host})
				});

				return true;
			},
			delete: (store, payload) =>
			{
				store.commit('delete', payload.id);
				return true;
			},
			saveState: (store, payload) =>
			{
				store.commit('saveState', {});
				return true;
			},
		}
	}

	getMutations()
	{
		return {
			set: (state, payload) =>
			{
				for (let element of payload)
				{
					this.initCollection(state, {id: element.id});

					state.collection[element.id] = Object.assign(state.collection[element.id], element);

					let status = Utils.user.getOnlineStatus(element);
					if (status.isOnline)
					{
						state.collection[element.id].isOnline = true;
						this.addToOnlineList(state, element.id);
					}

					let mobileStatus = Utils.user.isMobileActive(element);
					if (mobileStatus)
					{
						state.collection[element.id].isMobileOnline = true;
						this.addToMobileOnlineList(state, element.id);
					}

					if (element.birthday)
					{
						let today = Utils.date.format(new Date(), "d-m");
						if (element.birthday === today)
						{
							state.collection[element.id].isBirthday = true;

							let timeToNextMidnight = this.getTimeToNextMidnight();
							setTimeout(() => {
								state.collection[element.id].isBirthday = false;
							}, timeToNextMidnight);
						}
					}

					if (element.absent)
					{
						element.isAbsent = true;

						if (!state.absentList.includes(element.id))
						{
							this.addToAbsentList(state, element.id);

							let timeToNextMidnight = this.getTimeToNextMidnight();
							let timeToNextDay = 1000*60*60*24;
							setTimeout(() => {
								setInterval(() => this.startAbsentCheckInterval(state), timeToNextDay);
							}, timeToNextMidnight);
						}
					}

					this.saveState(state);
				}
			},
			update: (state, payload) =>
			{
				this.initCollection(state, payload);

				if (typeof payload.fields.lastActivityDate !== 'undefined' && state.collection[payload.id].lastActivityDate)
				{
					let lastActivityDate = state.collection[payload.id].lastActivityDate.getTime();
					let newActivityDate = payload.fields.lastActivityDate.getTime();
					if (newActivityDate > lastActivityDate)
					{
						let status = Utils.user.getOnlineStatus(payload.fields);
						if (status.isOnline)
						{
							state.collection[payload.id].isOnline = true;
							this.addToOnlineList(state, payload.fields.id);
						}
					}
				}

				if (
					typeof payload.fields.mobileLastDate !== 'undefined'
					&& state.collection[payload.id].mobileLastDate !== payload.fields.mobileLastDate
				)
				{
					let mobileStatus = Utils.user.isMobileActive(payload.fields);
					if (mobileStatus)
					{
						state.collection[payload.id].isMobileOnline = true;
						this.addToMobileOnlineList(state, payload.fields.id);
					}
				}

				state.collection[payload.id] = Object.assign(
					state.collection[payload.id],
					payload.fields
				);

				this.saveState(state);
			},
			delete: (state, payload) =>
			{
				delete state.collection[payload.id];
				this.saveState(state);
			},
			saveState: (state, payload) =>
			{
				this.saveState(state);
			},
		}
	}

	initCollection(state, payload)
	{
		if (typeof state.collection[payload.id] !== 'undefined')
		{
			return true;
		}

		Vue.set(state.collection, payload.id, this.getElementState());

		return true;
	}

	getSaveUserList()
	{
		if (!this.db)
		{
			return [];
		}

		if (!this.store.getters['messages/getSaveUserList'])
		{
			return [];
		}

		let list = this.store.getters['messages/getSaveUserList']();
		if (!list)
		{
			return [];
		}

		return list;
	}

	getSaveTimeout()
	{
		return 250;
	}

	saveState(state)
	{
		if (!this.isSaveAvailable())
		{
			return false;
		}

		super.saveState(() =>
		{
			let list = this.getSaveUserList();
			if (!list)
			{
				return false;
			}

			let storedState = {
				collection: {},
			};

			let exceptionList = {
				absent: true,
				idle: true,
				mobileLastDate: true,
				lastActivityDate: true,
			};

			for (let chatId in list)
			{
				if (!list.hasOwnProperty(chatId))
				{
					continue;
				}

				list[chatId].forEach(userId =>
				{
					if (!state.collection[userId])
					{
						return false;
					}

					storedState.collection[userId] = this.cloneState(state.collection[userId], exceptionList);
				});
			}

			return storedState;
		});
	}

	validate(fields, options = {})
	{
		const result = {};

		options.host = options.host || this.getState().host;

		if (typeof fields.id === "number" || typeof fields.id === "string")
		{
			result.id = parseInt(fields.id);
		}

		if (typeof fields.first_name !== "undefined")
		{
			fields.firstName = Utils.text.htmlspecialcharsback(fields.first_name);
		}
		if (typeof fields.last_name !== "undefined")
		{
			fields.lastName = Utils.text.htmlspecialcharsback(fields.last_name);
		}
		if (typeof fields.name === "string" || typeof fields.name === "number")
		{
			fields.name = Utils.text.htmlspecialcharsback(fields.name.toString());
			result.name = fields.name;
		}

		if (typeof fields.firstName === "string" || typeof fields.firstName === "number")
		{
			result.firstName = Utils.text.htmlspecialcharsback(fields.firstName.toString());
		}
		if (typeof fields.lastName === "string" || typeof fields.lastName === "number")
		{
			result.lastName = Utils.text.htmlspecialcharsback(fields.lastName.toString());
		}

		if (typeof fields.work_position !== "undefined")
		{
			fields.workPosition = fields.work_position;
		}
		if (typeof fields.workPosition === "string" || typeof fields.workPosition === "number")
		{
			result.workPosition = fields.workPosition.toString();
		}

		if (typeof fields.color === "string")
		{
			result.color = fields.color;
		}

		if (typeof fields.avatar === 'string')
		{
			let avatar;

			if (!fields.avatar || fields.avatar.endsWith('/js/im/images/blank.gif'))
			{
				avatar = '';
			}
			else if (fields.avatar.startsWith('http'))
			{
				avatar = fields.avatar;
			}
			else
			{
				avatar = options.host + fields.avatar;
			}

			if (avatar)
			{
				result.avatar = encodeURI(avatar);
			}
		}

		if (typeof fields.gender !== 'undefined')
		{
			result.gender = fields.gender === 'F'? 'F': 'M';
		}

		if (typeof fields.birthday === "string")
		{
			result.birthday = fields.birthday;
		}

		if (typeof fields.extranet === "boolean")
		{
			result.extranet = fields.extranet;
		}

		if (typeof fields.network === "boolean")
		{
			result.network = fields.network;
		}

		if (typeof fields.bot === "boolean")
		{
			result.bot = fields.bot;
		}

		if (typeof fields.connector === "boolean")
		{
			result.connector = fields.connector;
		}

		if (typeof fields.external_auth_id !== "undefined")
		{
			fields.externalAuthId = fields.external_auth_id;
		}
		if (typeof fields.externalAuthId === "string" && fields.externalAuthId)
		{
			result.externalAuthId = fields.externalAuthId;
		}

		if (typeof fields.status === "string")
		{
			result.status = fields.status;
		}

		if (typeof fields.idle !== "undefined")
		{
			result.idle = Utils.date.cast(fields.idle, false);
		}
		if (typeof fields.last_activity_date !== "undefined")
		{
			fields.lastActivityDate = fields.last_activity_date;
		}
		if (typeof fields.lastActivityDate !== "undefined")
		{
			result.lastActivityDate = Utils.date.cast(fields.lastActivityDate, false);
		}
		if (typeof fields.mobile_last_date !== "undefined")
		{
			fields.mobileLastDate = fields.mobile_last_date;
		}
		if (typeof fields.mobileLastDate !== "undefined")
		{
			result.mobileLastDate = Utils.date.cast(fields.mobileLastDate, false);
		}

		if (typeof fields.absent !== "undefined")
		{
			result.absent = Utils.date.cast(fields.absent, false);
		}

		if (typeof fields.departments !== 'undefined')
		{
			result.departments = [];

			if (fields.departments instanceof Array)
			{
				fields.departments.forEach(departmentId =>
				{
					departmentId = parseInt(departmentId);
					if (departmentId > 0)
					{
						result.departments.push(departmentId);
					}
				});
			}
		}

		if (typeof fields.phones === 'object' && fields.phones)
		{
			result.phones = {};

			if (typeof fields.phones.work_phone !== "undefined")
			{
				fields.phones.workPhone = fields.phones.work_phone;
			}
			if (typeof fields.phones.workPhone === 'string' || typeof fields.phones.workPhone === 'number')
			{
				result.phones.workPhone = fields.phones.workPhone.toString();
			}

			if (typeof fields.phones.personal_mobile !== "undefined")
			{
				fields.phones.personalMobile = fields.phones.personal_mobile;
			}
			if (typeof fields.phones.personalMobile === 'string' || typeof fields.phones.personalMobile === 'number')
			{
				result.phones.personalMobile = fields.phones.personalMobile.toString();
			}

			if (typeof fields.phones.personal_phone !== "undefined")
			{
				fields.phones.personalPhone = fields.phones.personal_phone;
			}
			if (typeof fields.phones.personalPhone === 'string' || typeof fields.phones.personalPhone === 'number')
			{
				result.phones.personalPhone = fields.phones.personalPhone.toString();
			}

			if (typeof fields.phones.inner_phone !== "undefined")
			{
				fields.phones.innerPhone = fields.phones.inner_phone;
			}
			if (typeof fields.phones.innerPhone === 'string' || typeof fields.phones.innerPhone === 'number')
			{
				result.phones.innerPhone = fields.phones.innerPhone.toString();
			}
		}

		return result;
	}

	addToOnlineList(state, id)
	{
		if (!state.onlineList.includes(id))
		{
			state.onlineList.push(id);
		}
	}

	addToMobileOnlineList(state, id)
	{
		if (!state.mobileOnlineList.includes(id))
		{
			state.mobileOnlineList.push(id);
		}
	}

	addToAbsentList(state, id)
	{
		if (!state.absentList.includes(id))
		{
			state.absentList.push(id);
		}
	}

	getTimeToNextMidnight()
	{
		let nextMidnight = new Date(new Date().setHours(24,0,0)).getTime();
		return nextMidnight - new Date();
	}

	startAbsentCheckInterval(state)
	{
		for (let userId of state.absentList)
		{
			let user = state.collection[userId];

			if (!user)
			{
				continue;
			}
			let currentTime = new Date().getTime();
			let absentEnd = new Date(state.collection[userId].absent).getTime();

			if (absentEnd <= currentTime)
			{
				state.absentList = state.absentList.filter(element => {
					return element !== userId;
				});
				user.isAbsent = false;
			}
		}
	}

	startOnlineCheckInterval()
	{
		const intervalTime = 60000;

		setInterval(() => {
			for (let userId of this.store.state.users.onlineList)
			{
				let user = this.store.state.users.collection[userId];

				if (!user)
				{
					continue;
				}

				let status = Utils.user.getOnlineStatus(user);
				if (status.isOnline)
				{
					user.isOnline = true;
				}
				else
				{
					user.isOnline = false;
					this.store.state.users.onlineList = this.store.state.users.onlineList.filter(element => {
						return element !== userId
					});
				}
			}

			for (let userId of this.store.state.users.mobileOnlineList)
			{
				let user = this.store.state.users.collection[userId];

				if (!user)
				{
					continue;
				}

				let mobileStatus = Utils.user.isMobileActive(user);
				if (mobileStatus)
				{
					user.isMobileOnline = true;
				}
				else
				{
					user.isMobileOnline = false;
					this.store.state.users.mobileOnlineList = this.store.state.users.mobileOnlineList.filter(element => {
						return element !== userId
					});
				}
			}
		}, intervalTime);
	}
}