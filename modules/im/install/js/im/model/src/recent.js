/**
 * Bitrix Messenger
 * Recent model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {VuexBuilderModel} from 'ui.vue.vuex';
import {Type} from 'main.core';
import {ChatTypes, MessageStatus, RecentSection as Section, TemplateTypes} from "im.const";
import {Utils} from "im.lib.utils";

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
			collection: []
		}
	}

	getElementState(): RecentItem
	{
		return {
			id: 0,
			templateId: '',
			template: TemplateTypes.item,
			chatType: ChatTypes.chat,
			sectionCode: Section.general,
			avatar: '',
			color: '#048bd0',
			title: '',
			lines: {id: 0, status: 0},
			message: {
				id: 0,
				text: '',
				date: new Date(),
				senderId: 0,
				status: MessageStatus.received
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
			get: state => (dialogId: string): {index: number, element: RecentItem} | boolean =>
			{
				if (Type.isNumber(dialogId))
				{
					dialogId = dialogId.toString();
				}

				let currentItem = this.findItem(dialogId);
				if (currentItem)
				{
					return currentItem;
				}

				return false;
			}
		};
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				let result = [];

				if (payload instanceof Array)
				{
					result = payload.map(
						recentItem => this.prepareItem(recentItem, { host: store.state.host })
					);
				}

				if (result.length === 0)
				{
					return false;
				}

				result.forEach(element => {
					const existingItem = this.findItem(element.id);
					if (existingItem)
					{
						store.commit('update', {
							index: existingItem.index,
							fields: element
						});
					}
					else
					{
						store.commit('add', {
							fields: element
						});
					}
				});
				store.state.collection.sort(this.sortListByMessageDate);
			},

			addPlaceholders: (store, payload: []) =>
			{
				payload.forEach(element => {
					store.commit('addPlaceholder', {
						fields: element
					});
				});
			},

			updatePlaceholders: (store, payload: {items: [], firstMessage: number}) =>
			{
				payload.items = payload.items.map(element => this.prepareItem(element));

				payload.items.forEach((element, index) => {
					const placeholderId = 'placeholder' + (payload.firstMessage + index);
					const existingPlaceholder = this.findItem(placeholderId, 'templateId');

					const existingItem = this.findItem(element.id);
					if (existingItem)
					{
						store.commit('update', {
							index: existingItem.index,
							fields: element
						});
						store.commit('delete', {
							index: existingPlaceholder.index,
						});
					}
					else
					{
						store.commit('update', {
							index: existingPlaceholder.index,
							fields: element
						});
					}
				});
			},

			update: (store, payload: {id: string | number, fields: Object}) =>
			{
				if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify')
				{
					payload.id = parseInt(payload.id);
				}

				const existingItem = this.findItem(payload.id);
				if (!existingItem)
				{
					return false;
				}

				payload.fields = this.validate(Object.assign({}, payload.fields));

				store.commit('update', {
					index: existingItem.index,
					fields: payload.fields
				});
				store.state.collection.sort(this.sortListByMessageDate);
			},

			pin: (store, payload: {id: string | number, action: boolean}) =>
			{
				if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify')
				{
					payload.id = parseInt(payload.id);
				}


				let existingItem = this.findItem(payload.id);

				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					index: existingItem.index,
					fields: Object.assign({}, existingItem.element, {
						pinned: payload.action
					})
				});

				store.state.collection.sort(this.sortListByMessageDate);
			},

			clearPlaceholders: (store) =>
			{
				store.commit('clearPlaceholders');
			},

			delete: (store, payload: {id: string | number}) =>
			{
				if (typeof payload.id === 'string' && !payload.id.startsWith('chat') && payload.id !== 'notify')
				{
					payload.id = parseInt(payload.id);
				}

				const existingItem = this.findItem(payload.id);
				if (!existingItem)
				{
					return false;
				}

				store.commit('delete', {
					index: existingItem.index
				});
				store.state.collection.sort(this.sortListByMessageDate);
			}
		}
	}

	getMutations()
	{
		return {
			add: (state, payload: {fields: Object}) => {
				state.collection.push(Object.assign(
					{},
					this.getElementState(),
					payload.fields
				));
			},

			update: (state, payload: {index: number, fields: Object}) => {
				state.collection.splice(payload.index, 1, Object.assign(
					{},
					state.collection[payload.index],
					payload.fields
				));
			},

			delete: (state, payload: {index: number}) => {
				state.collection.splice(payload.index, 1);
			},

			addPlaceholder: (state, payload: {fields: Object}) => {
				state.collection.push(Object.assign(
					{},
					this.getElementState(),
					payload.fields
				));
			},

			clearPlaceholders: (state) => {
				state.collection = state.collection.filter(element => {
					return !element.id.toString().startsWith('placeholder');
				});
			}
		}
	}

	validate(fields: rawRecentItem, options = {}): RecentItem
	{
		const result = {};

		if (Type.isNumber(fields.id))
		{
			result.id = fields.id.toString();
		}
		if (Type.isStringFilled(fields.id))
		{
			result.id = fields.id;
		}

		if (Type.isString(fields.templateId))
		{
			result.templateId = fields.templateId;
		}

		if (Type.isString(fields.template))
		{
			result.template = fields.template;
		}

		if (Type.isString(fields.type))
		{
			if (fields.type === ChatTypes.chat)
			{
				if (fields.chat.type === ChatTypes.open)
				{
					result.chatType = ChatTypes.open;
				}
				else if (fields.chat.type === ChatTypes.chat)
				{
					result.chatType = ChatTypes.chat;
				}
			}
			else if (fields.type === ChatTypes.user)
			{
				result.chatType = ChatTypes.user;
			}
			else if (fields.type === ChatTypes.notification)
			{
				result.chatType = ChatTypes.notification;
				fields.title = 'Notifications';
			}
			else
			{
				result.chatType = ChatTypes.chat;
			}
		}

		if (Type.isString(fields.avatar))
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

		if (Type.isString(fields.color))
		{
			result.color = fields.color;
		}

		if (Type.isString(fields.title))
		{
			result.title = fields.title;
		}

		if (Type.isPlainObject(fields.message))
		{
			const message = {};
			if (Type.isNumber(fields.message.id))
			{
				message.id = fields.message.id;
			}
			if (Type.isString(fields.message.text))
			{
				const options = {}
				if (fields.message.withAttach)
				{
					options.WITH_ATTACH = true;
				}
				else if (fields.message.withFile)
				{
					options.WITH_FILE = true;
				}

				message.text = Utils.text.purify(fields.message.text, options);
			}
			if (Type.isDate(fields.message.date) || Type.isString(fields.message.date))
			{
				message.date = fields.message.date;
			}
			if (Type.isNumber(fields.message.author_id))
			{
				message.senderId = fields.message.author_id;
			}
			if (Type.isNumber(fields.message.senderId))
			{
				message.senderId = fields.message.senderId;
			}
			if (Type.isStringFilled(fields.message.status))
			{
				message.status = fields.message.status;
			}

			result.message = message;
		}

		if (Type.isNumber(fields.counter))
		{
			result.counter = fields.counter;
		}

		if (Type.isBoolean(fields.pinned))
		{
			result.pinned = fields.pinned;
		}

		if (Type.isNumber(fields.chatId))
		{
			result.chatId = fields.chatId;
		}

		if (Type.isNumber(fields.userId))
		{
			result.userId = fields.userId;
		}

		return result;
	}

	sortListByMessageDate(a: RecentItem, b: RecentItem)
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

	findItem(value, key = 'id'): {index: number, element: RecentItem} | boolean
	{
		let result = {};

		if (key === 'id' && Type.isNumber(value))
		{
			value = value.toString();
		}

		let elementIndex = this.store.state.recent.collection.findIndex((element, index) => {
			return element[key] === value;
		});

		if (elementIndex !== -1)
		{
			result.index = elementIndex;
			result.element = this.store.state.recent.collection[elementIndex];

			return result;
		}

		return false;
	}
}

//raw input object for validation
type rawRecentItem = {
	id?: number | string,
	templateId?: string,
	template?: TemplateTypes.item | TemplateTypes.placeholder,
	type?: ChatTypes.chat | ChatTypes.user | ChatTypes.notification,
	chat?: {
		type?: string
	},
	avatar?: string,
	color?: string,
	title?: string,
	message?: RawRecentItemMessage,
	counter?: number,
	pinned?: boolean,
	chatId?: number,
	userId?: number
}

type RawRecentItemMessage = {
	id?: number,
	text?: string,
	date?: Date,
	senderId?: number,
	author_id?: number, //senderId alias
	status?: MessageStatus.received | MessageStatus.delivered
}

//item in collection
type RecentItem = {
	id?: number,
	templateId?: string,
	template?: TemplateTypes.item | TemplateTypes.placeholder,
	chatType?: ChatTypes.chat | ChatTypes.open | ChatTypes.user | ChatTypes.notification,
	sectionCode?: Section.general | Section.pinned,
	avatar?: string,
	color?: string,
	title?: string,
	message?: RecentItemMessage,
	counter?: number,
	pinned?: boolean,
	chatId?: number,
	userId?: number
}

type RecentItemMessage = {
	id?: number,
	text?: string,
	date?: Date | string,
	senderId?: number,
	status?: MessageStatus.received | MessageStatus.delivered
}