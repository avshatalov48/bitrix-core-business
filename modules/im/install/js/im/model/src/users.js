/**
 * Bitrix Messenger
 * User model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {Utils} from "im.utils";

class UsersModel extends VuexBuilderModel
{
	getName()
	{
		return 'users';
	}

	getState()
	{
		return {
			host: this.getVariable('host', location.protocol+'//'+location.host),
			collection: {}
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
			extranet: false,
			network: false,
			bot: false,
			connector: false,
			externalAuthId: "default",
			status: "online",
			idle: false,
			lastActivityDate: false,
			mobileLastDate: false,
			absent: false,
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

					state.collection[element.id] = element;

					this.saveState(state);
				}
			},
			update: (state, payload) =>
			{
				this.initCollection(state, payload);

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
			fields.firstName = fields.first_name;
		}
		if (typeof fields.last_name !== "undefined")
		{
			fields.lastName = fields.last_name;
		}
		if (typeof fields.name === "string" || typeof fields.name === "number")
		{
			result.name = fields.name.toString();

			if (typeof fields.firstName !== "undefined" && !fields.firstName)
			{
				let elementsOfName = fields.name.split(' ');
				if (elementsOfName.length > 1)
				{
					delete elementsOfName[elementsOfName.length-1];
					fields.firstName = elementsOfName.join(' ').trim();
				}
				else
				{
					fields.firstName = result.name;
				}
			}

			if (typeof fields.lastName !== "undefined" && !fields.lastName)
			{
				let elementsOfName = fields.name.split(' ');
				if (elementsOfName.length > 1)
				{
					fields.lastName = elementsOfName[elementsOfName.length-1];
				}
				else
				{
					fields.lastName = '';
				}
			}
		}

		if (typeof fields.firstName === "string" || typeof fields.name === "number")
		{
			result.firstName = fields.firstName.toString();
		}
		if (typeof fields.lastName === "string" || typeof fields.name === "number")
		{
			result.lastName = fields.lastName.toString();
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
}

export {UsersModel};