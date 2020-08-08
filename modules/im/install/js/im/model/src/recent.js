/**
 * Bitrix Messenger
 * Recent model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {VuexBuilderModel} from 'ui.vue.vuex';

export class RecentModel extends VuexBuilderModel
{
	getName()
	{
		return 'recent';
	}

	getState()
	{
		return {
			host: this.getVariable('host', location.protocol+'//'+location.host),
			collection: {
				pinned: [],
				general: []
			}
		}
	}

	getElementState(params = {})
	{
		return {
			id: 0,
			templateId: '',
			template: 'item',
			chatType: 'chat',
			sectionCode: 'general',
			avatar: '',
			color: '#048bd0',
			title: '',
			message: {
				id: 0,
				text: '',
				date: new Date()
			},
			counter: 0,
			pinned: false,
			chatId: 0,
			userId: 0
		};
	}

	getGetters()
	{
		return {
			get: state => dialogId =>
			{
				return this.findItem(state.collection, dialogId);
			}
		};
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				let result = {};

				if (payload.pinned instanceof Array)
				{
					result.pinned = payload.pinned.map(
						recentItem => this.prepareItem(recentItem, { host: store.state.host, sectionCode: 'pinned' })
					);
				}
				else if (typeof payload.pinned !== 'undefined')
				{
					let pinned = [];
					pinned.push(this.prepareItem(payload.pinned, { host: store.state.host, sectionCode: 'pinned' }));

					result.pinned = pinned;
				}

				if (payload.general instanceof Array)
				{
					result.general = payload.general.map(
						recentItem => this.prepareItem(recentItem, { host: store.state.host })
					);
				}
				else if (typeof payload.general !== 'undefined')
				{
					let general = [];
					general.push(this.prepareItem(payload.general, { host: store.state.host }));

					result.general = general;
				}

				store.commit('set', result);
			},

			updatePlaceholders: (store, payload) =>
			{
				if (!(payload.items instanceof Array))
				{
					return false;
				}

				payload.items = payload.items.map(element => this.prepareItem(element));

				payload.items.forEach((element, index) => {
					let placeholderId = 'placeholder' + (payload.firstMessage + index);
					let existingPlaceholder = this.findItem(
						store.state.collection,
						placeholderId,
						'templateId'
					);

					let existingItem = this.findItem(store.state.collection, element.id);

					if (existingItem.element)
					{
						store.commit('update', {
							index: existingItem.index,
							fields: Object.assign({}, element),
							section: 'general'
						});

						store.commit('delete', {
							index: existingPlaceholder.index,
							section: 'general'
						});
					}
					else
					{
						store.commit('update', {
							index: existingPlaceholder.index,
							fields: Object.assign({}, element),
							section: 'general'
						});
					}
				});
			},

			update: (store, payload) =>
			{
				if (
					typeof payload !== 'object' ||
					payload instanceof Array ||
					!payload.id ||
					!payload.fields
				)
				{
					return false;
				}

				if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify')
				{
					payload.id = parseInt(payload.id);
				}

				let existingItem = this.findItem(store.state.collection, payload.id);

				if (!existingItem.element)
				{
					return false;
				}

				store.commit('update', {
					index: existingItem.index,
					fields: Object.assign({}, this.validate(payload.fields)),
					section: existingItem.element.sectionCode
				});
			},

			pin: (store, payload) =>
			{
				if (
					typeof payload !== 'object' ||
					payload instanceof Array ||
					!payload.id ||
					typeof payload.action !== 'boolean'
				)
				{
					return false;
				}

				if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify')
				{
					payload.id = parseInt(payload.id);
				}

				let existingItem = this.findItem(store.state.collection, payload.id, undefined, payload.action? 'general': 'pinned');

				if (!existingItem.element)
				{
					return true;
				}

				if (payload.action)
				{
					store.state.collection.pinned.push(
						Object.assign({}, existingItem.element, {
							sectionCode: 'pinned',
							pinned: true
						})
					);
					store.state.collection.pinned.sort(this.sortListByMessageDate);

					store.commit('delete', {
						index: existingItem.index,
						section: 'general'
					});
				}
				else
				{
					store.state.collection.general.push(
						Object.assign({}, existingItem.element, {
							sectionCode: 'general',
							pinned: false
						})
					);
					store.state.collection.general.sort(this.sortListByMessageDate);

					store.commit('delete', {
						index: existingItem.index,
						section: 'pinned'
					});
				}
			},

			clearPlaceholders: (store, payload) =>
			{
				store.state.collection.general = store.state.collection.general.filter(element => {
					return !element.id.toString().startsWith('placeholder');
				});
			},

			delete: (store, payload) =>
			{
				if (
					typeof payload !== 'object' ||
					payload instanceof Array ||
					!payload.id
				)
				{
					return false;
				}

				if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify')
				{
					payload.id = parseInt(payload.id);
				}

				let existingItem = this.findItem(store.state.collection, payload.id);

				if (!existingItem.element)
				{
					return false;
				}

				store.commit('delete', {
					index: existingItem.index,
					section: existingItem.element.sectionCode
				});
			}
		}
	}

	getMutations()
	{
		return {
			set: (state, payload) => {
				if (payload.general instanceof Array)
				{
					payload.general.forEach(element => {
						let {index, alreadyExists} = this.initCollection(state, element, 'general');

						if (alreadyExists)
						{
							state.collection.general[index] = Object.assign(
								{},
								state.collection.general[index],
								element
							);
						}
					});
				}
				if (payload.pinned instanceof Array)
				{
					payload.pinned.forEach(element => {
						let {index, alreadyExists} = this.initCollection(state, element, 'pinned');
						if (alreadyExists)
						{
							state.collection.pinned[index] = Object.assign(
								{},
								state.collection.pinned[index],
								element
							);
						}
					});
				}
			},

			update: (state, payload) => {
				if (
					!payload ||
					payload instanceof Array ||
					typeof payload.fields !== 'object' ||
					typeof payload.index !== 'number' ||
					typeof payload.section !== 'string'
				)
				{
					return false;
				}

				state.collection[payload.section][payload.index] = Object.assign(
					{},
					state.collection[payload.section][payload.index],
					payload.fields
				);

				state.collection[payload.section].sort(this.sortListByMessageDate);
			},

			delete: (state, payload) => {
				if (
					!payload ||
					payload instanceof Array ||
					typeof payload.index !== 'number' ||
					typeof payload.section !== 'string'
				)
				{
					return false;
				}

				state.collection[payload.section].splice(payload.index, 1);
			}
		}
	}

	initCollection(state, payload, section)
	{
		let existingItem = this.findItem(state.collection, payload.id, undefined, section);

		if (existingItem.element)
		{
			return {index: existingItem.index, alreadyExists: true};
		}

		let newLength = state.collection[section].push(Object.assign(
			{},
			this.getElementState(),
			payload
		));

		return {index: newLength - 1, alreadyExists: false};
	}

	validate(fields, options = {})
	{
		const result = {};

		if (typeof fields.id === "number" || typeof fields.id === "string")
		{
			result.id = fields.id;
		}

		if (typeof fields.templateId === 'string')
		{
			result.templateId = fields.templateId;
		}

		if (typeof fields.template === 'string')
		{
			result.template = fields.template;
		}

		if (typeof fields.type === "string")
		{
			if (fields.type === 'chat')
			{
				if (fields.chat.type === 'open')
				{
					result.chatType = 'open';
				}
				else if (fields.chat.type === 'chat')
				{
					result.chatType = 'chat';
				}
			}
			else if (fields.type === 'user')
			{
				result.chatType = 'user';
			}
			else if (fields.type === 'notification')
			{
				result.chatType = 'notification';
				fields.title = 'Notifications';
			}
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

		if (typeof fields.color === 'string')
		{
			result.color = fields.color;
		}

		if (typeof fields.title === "string")
		{
			result.title = fields.title;
		}

		if (
			typeof fields.message === "object" &&
			!(fields.message instanceof Array) &&
			fields.message !== null)
		{
			result.message = fields.message;
		}

		if (typeof fields.counter === 'number')
		{
			result.counter = fields.counter;
		}

		if (typeof fields.pinned === 'boolean')
		{
			result.pinned = fields.pinned;
		}

		if (typeof fields.chatId === 'number')
		{
			result.chatId = fields.chatId;
		}

		if (typeof fields.userId === 'number')
		{
			result.userId = fields.userId;
		}

		return result;
	}

	sortListByMessageDate(a, b)
	{
		if (a.message && b.message)
		{
			let timestampA = new Date(a.message.date).getTime();
			let timestampB = new Date(b.message.date).getTime();

			return timestampB - timestampA;
		}
	}

	prepareItem(item, options = {})
	{
		let result = this.validate(Object.assign({}, item));

		return Object.assign({}, this.getElementState(), result, options);
	}

	findItem(store, value, key = 'id', section = 'general')
	{
		let result = {};
		if (typeof store[section] === undefined)
		{
			return result;
		}

		let elementIndex = store[section].findIndex((element, index) => {
			return element[key] === value;
		});

		if (elementIndex !== -1)
		{
			result.index = elementIndex;
			result.element = store[section][elementIndex];

			return result;
		}

		return result;
	}
}