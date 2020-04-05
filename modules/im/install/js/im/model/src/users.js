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
			collection: {},
			index: {},
		}
	}

	getElementState(params = {})
	{
		return {
			id: 0,
			name: this.getVariable('defaultName', ''),
			firstName: this.getVariable('defaultName', ''),
			lastName: "",
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
			departments: [],
			absent: false,
			phones: {
				workPhone: "",
				personalMobile: "",
				personalPhone: ""
			},
			init: false
		};
	}

	getGetters()
	{
		return {
			get: state => userId =>
			{
				if (!state.collection[userId])
				{
					return null;
				}

				return state.collection[userId];
			},
			getBlank: state => params =>
			{
				return this.getElementState();
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
				if (
					typeof store.state.collection[payload.id] === 'undefined'
					|| store.state.collection[payload.id].init === false
				)
				{
					return true;
				}

				store.commit('update', {
					userId : payload.id,
					fields : this.validate(Object.assign({}, payload.fields), {host: store.state.host})
				});

				return true;
			},
			delete: (store, payload) =>
			{
				store.commit('delete', payload.id);
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
					if (typeof state.collection[element.id] === 'undefined')
					{
						Vue.set(state.collection, element.id, element);
					}

					state.collection[element.id] = element;
				}
			},
			update: (state, payload) =>
			{
				if (typeof state.collection[payload.id] === 'undefined')
				{
					Vue.set(state.collection, payload.id, this.getElementState());
				}

				state.collection[payload.id] = Object.assign(
					state.collection[payload.id],
					payload.fields
				);
			},
			delete: (state, payload) =>
			{
				delete state.collection[payload.id]
			}
		}
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
			if (!fields.avatar || fields.avatar.startsWith('http'))
			{
				result.avatar = fields.avatar;
			}
			else
			{
				result.avatar = options.host+fields.avatar;
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
			if (fields.idle instanceof Date)
			{
				result.idle = fields.idle;
			}
			else if (typeof fields.idle === "string")
			{
				result.idle = new Date(fields.idle);
			}
			else
			{
				result.idle = false;
			}
		}

		if (typeof fields.last_activity_date !== "undefined")
		{
			fields.lastActivityDate = fields.last_activity_date;
		}
		if (typeof fields.lastActivityDate !== "undefined")
		{
			if (fields.lastActivityDate instanceof Date)
			{
				result.lastActivityDate = fields.lastActivityDate;
			}
			else if (typeof fields.lastActivityDate === "string")
			{
				result.lastActivityDate = new Date(fields.lastActivityDate);
			}
			else
			{
				result.lastActivityDate = false;
			}
		}

		if (typeof fields.mobile_last_date !== "undefined")
		{
			fields.mobileLastDate = fields.mobile_last_date;
		}
		if (typeof fields.mobileLastDate !== "undefined")
		{
			if (fields.mobileLastDate instanceof Date)
			{
				result.mobileLastDate = fields.mobileLastDate;
			}
			else if (typeof fields.mobileLastDate === "string")
			{
				result.mobileLastDate = new Date(fields.mobileLastDate);
			}
			else
			{
				result.mobileLastDate = false;
			}
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

		if (typeof fields.absent !== "undefined")
		{
			if (fields.absent instanceof Date)
			{
				result.absent = fields.absent;
			}
			else if (typeof fields.absent === "string")
			{
				result.absent = new Date(fields.absent);
			}
			else
			{
				result.absent = false;
			}
		}

		if (typeof fields.phones === 'object' && !fields.phones)
		{
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
		}

		return result;
	}
}

export {UsersModel};