import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';
import {ChatTypes} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';

const WRITING_STATUS_TIME = 35000;

export class DialoguesModel extends BuilderModel
{
	getName()
	{
		return 'dialogues';
	}

	getState()
	{
		return {
			collection: {},
			writingStatusTimers: {},
			chatOptions: {}
		};
	}

	getElementState()
	{
		return {
			dialogId: '0',
			chatId: 0,
			type: ChatTypes.chat,
			name: '',
			avatar: '',
			color: '#17A3EA',
			extranet: false,
			counter: 0,
			userCounter: 0,
			messageCounter: 0,
			unreadId: 0,
			lastMessageId: 0,
			managerList: [],
			readList: [],
			writingList: [],
			muteList: [],
			textareaMessage: '',
			quoteId: 0,
			editId: 0,
			owner: 0,
			entityType: '',
			entityId: '',
			dateCreate: null,
			public: {
				code: '',
				link: ''
			}
		};
	}

	getGetters()
	{
		return {
			get: state => (dialogId, getBlank = false) =>
			{
				if (!state.collection[dialogId] && getBlank)
				{
					return this.getElementState();
				}
				else if (!state.collection[dialogId] && !getBlank)
				{
					return null;
				}

				return state.collection[dialogId];
			},
			getByChatId: state => chatId =>
			{
				chatId = Number.parseInt(chatId, 10);
				return Object.values(state.collection).find(item => {
					return item.chatId === chatId;
				});
			},
			getBlank: () =>
			{
				return this.getElementState();
			},
			getChatOption: state => (chatType, option) =>
			{
				if (!state.chatOptions[chatType])
				{
					chatType = 'default';
				}

				return state.chatOptions[chatType][option];
			},
			getQuoteId: state => dialogId =>
			{
				if (!state.collection[dialogId])
				{
					return 0;
				}

				return state.collection[dialogId].quoteId;
			},
			getEditId: state => dialogId =>
			{
				if (!state.collection[dialogId])
				{
					return 0;
				}

				return state.collection[dialogId].editId;
			},
			areUnreadMessagesLoaded: state => dialogId =>
			{
				const dialog = state.collection[dialogId];
				if (!dialog || dialog.lastMessageId === 0)
				{
					return true;
				}

				const messagesCollection = this.store.getters['messages/get'](dialog.chatId);
				if (messagesCollection.length === 0)
				{
					return true;
				}

				let lastElementId = 0;
				for (let index = messagesCollection.length - 1; index >= 0; index--)
				{
					const lastElement = messagesCollection[index];
					if (Type.isNumber(lastElement.id))
					{
						lastElementId = lastElement.id;
						break;
					}
				}

				return lastElementId >= dialog.lastMessageId;
			}
		};
	}

	getActions()
	{
		return {
			set: (store, payload: Array | Object) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map(element => {
					return this.validate(element);
				}).forEach(element => {
					const existingItem = store.state.collection[element.dialogId];
					if (existingItem)
					{
						store.commit('update', {
							dialogId: element.dialogId,
							fields: element
						});
					}
					else
					{
						store.commit('add', {
							dialogId: element.dialogId,
							fields: {...this.getElementState(), ...element}
						});
					}
				});
			},

			add: (store, payload: Array | Object) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				payload.map(element => {
					return this.validate(element);
				}).forEach(element => {
					const existingItem = store.state.collection[element.dialogId];
					if (!existingItem)
					{
						store.commit('add', {
							dialogId: element.dialogId,
							fields: {...this.getElementState(), ...element}
						});
					}
				});
			},

			update: (store, payload: {dialogId: string, fields: Object}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					dialogId: payload.dialogId,
					fields: this.validate(payload.fields)
				});
			},

			delete: (store, payload: {dialogId: string}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				store.commit('delete', payload.dialogId);
			},

			startWriting: (store, payload: {dialogId: string, userId: number, userName: string}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const timerId = `${payload.dialogId}|${payload.userId}`;
				const alreadyWriting = existingItem.writingList.some(el => el.userId === payload.userId);
				if (alreadyWriting)
				{
					clearTimeout(store.state.writingStatusTimers[timerId]);
					store.state.writingStatusTimers[timerId] = this.setWritingStatusTimeout(payload);
					return true;
				}

				const newItem = {userId: payload.userId, userName: payload.userName};
				const newWritingList = [newItem, ...existingItem.writingList];
				store.commit('update', {
					actionName: 'startWriting',
					dialogId: payload.dialogId,
					fields: this.validate({writingList: newWritingList})
				});

				if (!store.state.writingStatusTimers[timerId])
				{
					store.state.writingStatusTimers[timerId] = this.setWritingStatusTimeout(payload);
				}
			},

			stopWriting: (store, payload: {dialogId: string, userId: number}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const alreadyWriting = existingItem.writingList.find(el => el.userId === payload.userId);
				if (!alreadyWriting)
				{
					return false;
				}

				const newWritingList = existingItem.writingList.filter(item => item.userId !== payload.userId);
				store.commit('update', {
					actionName: 'stopWriting',
					dialogId: payload.dialogId,
					fields: this.validate({writingList: newWritingList})
				});

				const timerId = `${payload.dialogId}|${payload.userId}`;
				clearTimeout(store.state.writingStatusTimers[timerId]);
				delete store.state.writingStatusTimers[timerId];
			},

			addToReadList: (store, payload: {
				dialogId: string,
				userId: number,
				userName: string,
				messageId: number,
				date: string
			}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const readList = existingItem.readList.filter(el => el.userId !== payload.userId);

				readList.push({
					userId: payload.userId,
					userName: payload.userName || '',
					messageId: payload.messageId,
					date: payload.date || (new Date()),
				});

				store.commit('update', {
					actionName: 'addToReadList',
					dialogId: payload.dialogId,
					fields: this.validate({readList})
				});
			},

			removeFromReadList: (store, payload: {
				dialogId: string,
				userId: number
			}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const readList = existingItem.readList.filter(el => el.userId !== payload.userId);

				store.commit('update', {
					actionName: 'removeFromReadList',
					dialogId: payload.dialogId,
					fields: this.validate({readList})
				});
			},

			increaseCounter: (store, payload: {dialogId: string, count: number}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				if (existingItem.counter === 100)
				{
					return true;
				}

				let increasedCounter = existingItem.counter + payload.count;
				if (increasedCounter > 100)
				{
					increasedCounter = 100;
				}

				store.commit('update', {
					actionName: 'increaseCounter',
					dialogId: payload.dialogId,
					fields: {
						counter: increasedCounter,
						previousCounter: existingItem.counter
					}
				});
			},

			decreaseCounter: (store, payload: {dialogId: string, count: number}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				if (existingItem.counter === 100)
				{
					return true;
				}

				let decreasedCounter = existingItem.counter - payload.count;
				if (decreasedCounter < 0)
				{
					decreasedCounter = 0;
				}

				store.commit('update', {
					actionName: 'decreaseCounter',
					dialogId: payload.dialogId,
					fields: {
						counter: decreasedCounter,
						previousCounter: existingItem.counter
					}
				});
			},

			increaseMessageCounter: (store, payload: {dialogId: string, count: number}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					actionName: 'increaseMessageCount',
					dialogId: payload.dialogId,
					fields: {
						messageCounter: existingItem.messageCounter + payload.count,
					}
				});
			},

			mute: (store, payload: {dialogId: string}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const currentUserId = this.store.state.application.common.userId;
				if (existingItem.muteList.includes(currentUserId))
				{
					return false;
				}
				const muteList = [...existingItem.muteList, currentUserId];

				store.commit('update', {
					actionName: 'mute',
					dialogId: payload.dialogId,
					fields: this.validate({muteList})
				});
			},

			unmute: (store, payload: {dialogId: string}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const currentUserId = this.store.state.application.common.userId;
				const muteList = existingItem.muteList.filter(item => item !== currentUserId);

				store.commit('update', {
					actionName: 'unmute',
					dialogId: payload.dialogId,
					fields: this.validate({muteList})
				});
			},

			setChatOptions: (store, payload: Object) =>
			{
				store.commit('setChatOptions', this.validateChatOptions(payload));
			}
		};
	}

	getMutations()
	{
		return {
			add: (state, payload) =>
			{
				state.collection[payload.dialogId] = payload.fields;
			},
			update: (state, payload) =>
			{
				state.collection[payload.dialogId] = {...state.collection[payload.dialogId], ...payload.fields};
			},
			delete: (state, payload) =>
			{
				delete state.collection[payload.dialogId];
			},
			setChatOptions: (state, payload) =>
			{
				state.chatOptions = payload;
			}
		};
	}

	setWritingStatusTimeout(payload: {dialogId: string, userId: number})
	{
		return setTimeout(() => {
			this.store.dispatch('dialogues/stopWriting', {
				dialogId: payload.dialogId,
				userId: payload.userId
			});
		}, WRITING_STATUS_TIME);
	}

	validate(fields)
	{
		const result = {};

		if (!Type.isUndefined(fields.dialog_id))
		{
			fields.dialogId = fields.dialog_id;
		}
		if (Type.isNumber(fields.dialogId) || Type.isStringFilled(fields.dialogId))
		{
			result.dialogId = fields.dialogId.toString();
		}

		if (!Type.isUndefined(fields.chat_id))
		{
			fields.chatId = fields.chat_id;
		}
		else if (!Type.isUndefined(fields.id))
		{
			fields.chatId = fields.id;
		}
		if (Type.isNumber(fields.chatId) || Type.isStringFilled(fields.chatId))
		{
			result.chatId = Number.parseInt(fields.chatId, 10);
		}

		if (Type.isStringFilled(fields.type))
		{
			result.type = fields.type.toString();
		}

		if (Type.isNumber(fields.quoteId))
		{
			result.quoteId = Number.parseInt(fields.quoteId, 10);
		}
		if (Type.isNumber(fields.editId))
		{
			result.editId = Number.parseInt(fields.editId, 10);
		}

		if (Type.isNumber(fields.counter) || Type.isStringFilled(fields.counter))
		{
			result.counter = Number.parseInt(fields.counter, 10);
		}

		if (!Type.isUndefined(fields.user_counter))
		{
			result.userCounter = fields.user_counter;
		}
		if (Type.isNumber(fields.userCounter) || Type.isStringFilled(fields.userCounter))
		{
			result.userCounter = Number.parseInt(fields.userCounter, 10);
		}

		if (!Type.isUndefined(fields.message_count))
		{
			result.messageCounter = fields.message_count;
		}
		if (Type.isNumber(fields.messageCounter) || Type.isStringFilled(fields.messageCounter))
		{
			result.messageCounter = Number.parseInt(fields.messageCounter, 10);
		}

		if (!Type.isUndefined(fields.unread_id))
		{
			fields.unreadId = fields.unread_id;
		}
		if (Type.isNumber(fields.unreadId) || Type.isStringFilled(fields.unreadId))
		{
			result.unreadId = Number.parseInt(fields.unreadId, 10);
		}

		if (!Type.isUndefined(fields.last_message_id))
		{
			fields.lastMessageId = fields.last_message_id;
		}
		if (Type.isNumber(fields.lastMessageId) || Type.isStringFilled(fields.lastMessageId))
		{
			result.lastMessageId = Number.parseInt(fields.lastMessageId, 10);
		}

		if (!Type.isUndefined(fields.textareaMessage))
		{
			result.textareaMessage = fields.textareaMessage.toString();
		}

		if (!Type.isUndefined(fields.title))
		{
			fields.name = fields.title;
		}
		if (Type.isNumber(fields.name) || Type.isStringFilled(fields.name))
		{
			result.name = Utils.text.htmlspecialcharsback(fields.name.toString());
		}

		if (!Type.isUndefined(fields.owner))
		{
			fields.ownerId = fields.owner;
		}
		if (Type.isNumber(fields.ownerId) || Type.isStringFilled(fields.ownerId))
		{
			result.owner = Number.parseInt(fields.ownerId, 10);
		}

		if (Type.isString(fields.avatar))
		{
			result.avatar = this.prepareAvatar(fields.avatar);
		}

		if (Type.isStringFilled(fields.color))
		{
			result.color = fields.color;
		}

		if (Type.isBoolean(fields.extranet))
		{
			result.extranet = fields.extranet;
		}

		if (!Type.isUndefined(fields.entity_type))
		{
			fields.entityType = fields.entity_type;
		}
		if (Type.isStringFilled(fields.entityType))
		{
			result.entityType = fields.entityType;
		}
		if (!Type.isUndefined(fields.entity_id))
		{
			fields.entityId = fields.entity_id;
		}
		if (Type.isNumber(fields.entityId) || Type.isStringFilled(fields.entityId))
		{
			result.entityId = fields.entityId.toString();
		}

		if (!Type.isUndefined(fields.date_create))
		{
			fields.dateCreate = fields.date_create;
		}
		if (!Type.isUndefined(fields.dateCreate))
		{
			result.dateCreate = Utils.date.cast(fields.dateCreate);
		}

		if (Type.isPlainObject(fields.public))
		{
			result.public = {};

			if (Type.isStringFilled(fields.public.code))
			{
				result.public.code = fields.public.code;
			}

			if (Type.isStringFilled(fields.public.link))
			{
				result.public.link = fields.public.link;
			}
		}

		if (!Type.isUndefined(fields.readed_list))
		{
			fields.readList = fields.readed_list;
		}
		if (Type.isArray(fields.readList))
		{
			result.readList = this.prepareReadList(fields.readList);
		}

		if (!Type.isUndefined(fields.writing_list))
		{
			fields.writingList = fields.writing_list;
		}
		if (Type.isArray(fields.writingList))
		{
			result.writingList = this.prepareWritingList(fields.writingList);
		}

		if (!Type.isUndefined(fields.manager_list))
		{
			fields.managerList = fields.manager_list;
		}
		if (Type.isArray(fields.managerList))
		{
			result.managerList = [];

			fields.managerList.forEach(userId =>
			{
				userId = Number.parseInt(userId, 10);
				if (userId > 0)
				{
					result.managerList.push(userId);
				}
			});
		}

		if (!Type.isUndefined(fields.mute_list))
		{
			fields.muteList = fields.mute_list;
		}
		if (Type.isArray(fields.muteList) || Type.isPlainObject(fields.muteList))
		{
			result.muteList = this.prepareMuteList(fields.muteList);
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
			result = this.store.state.application.common.host + avatar;
		}

		if (result)
		{
			result = encodeURI(result);
		}

		return result;
	}

	prepareReadList(readList: Object[]): Object[]
	{
		const result = [];

		readList.forEach(element =>
		{
			const item = {};
			if (!Type.isUndefined(element.user_id))
			{
				element.userId = element.user_id;
			}
			if (!Type.isUndefined(element.user_name))
			{
				element.userName = element.user_name;
			}
			if (!Type.isUndefined(element.message_id))
			{
				element.messageId = element.message_id;
			}

			if (!element.userId || !element.userName || !element.messageId)
			{
				return false;
			}

			item.userId = Number.parseInt(element.userId, 10);
			item.userName = element.userName.toString();
			item.messageId = Number.parseInt(element.messageId, 10);

			item.date = Utils.date.cast(element.date);

			result.push(item);
		});

		return result;
	}

	prepareWritingList(writingList: Object[]): Object[]
	{
		const result = [];

		writingList.forEach(element =>
		{
			const item = {};

			if (!element.userId)
			{
				return false;
			}

			item.userId = Number.parseInt(element.userId, 10);
			item.userName = Utils.text.htmlspecialcharsback(element.userName);

			result.push(item);
		});

		return result;
	}

	prepareMuteList(muteList: Object[] | Object): Object[]
	{
		const result = [];

		if (Type.isArray(muteList))
		{
			muteList.forEach(userId =>
			{
				userId = Number.parseInt(userId, 10);
				if (userId > 0)
				{
					result.push(userId);
				}
			});
		}
		else if (Type.isPlainObject(muteList))
		{
			Object.entries(muteList).forEach(([key, value]) => {
				if (!value)
				{
					return;
				}
				const userId = Number.parseInt(key, 10);
				if (userId > 0)
				{
					result.push(userId);
				}
			});
		}

		return result;
	}

	validateChatOptions(options: Object): Object
	{
		const result = {};

		Object.entries(options).forEach(([type, typeOptions]) => {
			const newType = Utils.text.convertSnakeToCamelCase(type.toLowerCase());
			result[newType] = {};
			Object.entries(typeOptions).forEach(([key, value]) => {
				const newKey = Utils.text.convertSnakeToCamelCase(key.toLowerCase());
				result[newType][newKey] = value;
			});
		});

		return result;
	}
}