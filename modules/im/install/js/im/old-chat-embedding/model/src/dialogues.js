import {Text, Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {Core} from 'im.old-chat-embedding.application.core';
import {ChatOption, Color, DialogType} from 'im.old-chat-embedding.const';
import {Utils} from 'im.old-chat-embedding.lib.utils';

import type {Dialog as ImModelDialog} from './type/dialog';

const WRITING_STATUS_TIME = 35000;

type DialogState = {
	collection: {[dialogId: string]: ImModelDialog},
	writingStatusTimers: {[timerId: string]: number},
	chatOptions: {[chatType: string]: {[option: string]: boolean}}
};

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
			type: DialogType.chat,
			name: '',
			description: '',
			avatar: '',
			color: Color.base,
			extranet: false,
			counter: 0,
			userCounter: 0,
			lastReadId: 0,
			markedId: 0,
			lastMessageId: 0,
			lastMessageViews: {
				countOfViewers: 0,
				firstViewer: null,
				messageId: 0
			},
			savedPositionMessageId: 0,
			managerList: [],
			writingList: [],
			muteList: [],
			textareaMessage: '',
			quoteId: 0,
			owner: 0,
			entityType: '',
			entityId: '',
			dateCreate: null,
			public: {
				code: '',
				link: ''
			},
			inited: false,
			loading: false,
			hasPrevPage: false,
			hasNextPage: false,
			diskFolderId: 0,
		};
	}

	getGetters()
	{
		return {
			get: (state: DialogState) => (dialogId: string, getBlank: boolean = false) =>
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
			getByChatId: (state: DialogState) => (chatId: number | string) =>
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
			getChatOption: (state: DialogState) => (chatType: string, option: string) =>
			{
				if (!state.chatOptions[chatType])
				{
					chatType = 'default';
				}

				return state.chatOptions[chatType][option];
			},
			getQuoteId: (state: DialogState) => (dialogId: string) =>
			{
				if (!state.collection[dialogId])
				{
					return 0;
				}

				return state.collection[dialogId].quoteId;
			},
			isUser: (state: DialogState) => (dialogId: string) =>
			{
				if (!state.collection[dialogId])
				{
					return false;
				}

				return state.collection[dialogId].type === DialogType.user;
			},
			canLeave: (state: DialogState) => (dialogId: string) =>
			{
				if (!state.collection[dialogId])
				{
					return false;
				}

				const dialog = state.collection[dialogId];

				const isExternalTelephonyCall = dialog.type === DialogType.call;
				const isUser = dialog.type === DialogType.user;
				if (isExternalTelephonyCall || isUser)
				{
					return false;
				}

				const currentUserId = Core.getUserId();
				const optionToCheck = dialog.owner === currentUserId ? ChatOption.leaveOwner : ChatOption.leave;

				return this.store.getters['dialogues/getChatOption'](dialog.type, optionToCheck);
			},
			canMute: (state: DialogState) => (dialogId: string) =>
			{
				if (!state.collection[dialogId])
				{
					return false;
				}

				const dialog = state.collection[dialogId];

				const isUser = dialog.type === DialogType.user;
				const isAnnouncement = dialog.type === DialogType.announcement;
				if (isUser || isAnnouncement)
				{
					return null;
				}

				return this.store.getters['dialogues/getChatOption'](dialog.type, ChatOption.mute);
			},
			getLastReadId: (state: DialogState) => (dialogId: string): number =>
			{
				if (!state.collection[dialogId])
				{
					return 0;
				}

				const {lastReadId, lastMessageId} = state.collection[dialogId];

				return lastReadId === lastMessageId ? 0 : lastReadId;
			},
			getInitialMessageId: (state: DialogState) => (dialogId: string): number =>
			{
				if (!state.collection[dialogId])
				{
					return 0;
				}

				const {lastReadId, markedId} = state.collection[dialogId];
				if (markedId === 0)
				{
					return lastReadId;
				}

				return Math.min(lastReadId, markedId);
			},
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

				store.commit('delete', {dialogId: payload.dialogId});
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

			clearCounters: (store) =>
			{
				store.commit('clearCounters');
			},

			mute: (store, payload: {dialogId: string}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const currentUserId = Core.getUserId();
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

				const currentUserId = Core.getUserId();
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
			},

			setLastMessageViews: (store, payload: {
				dialogId: string,
				fields: {userId: number, userName: string, date: string, messageId: number}
			}) =>
			{
				const {dialogId, fields: {userId, userName, date, messageId}} = payload;
				const existingItem: ImModelDialog = store.state.collection[dialogId];
				if (!existingItem)
				{
					return false;
				}

				const newLastMessageViews = {
					countOfViewers: 1,
					messageId,
					firstViewer: {
						userId,
						userName,
						date: Utils.date.cast(date)
					}
				};
				store.commit('update', {
					actionName: 'setLastMessageViews',
					dialogId: dialogId,
					fields: {
						lastMessageViews: newLastMessageViews
					}
				});
			},

			clearLastMessageViews: (store, payload: {dialogId: string}) =>
			{
				const existingItem = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const {lastMessageViews: defaultLastMessageViews} = this.getElementState();
				store.commit('update', {
					actionName: 'clearLastMessageViews',
					dialogId: payload.dialogId,
					fields: {
						lastMessageViews: defaultLastMessageViews
					}
				});
			},

			incrementLastMessageViews: (store, payload: {dialogId: string}) =>
			{
				const existingItem: ImModelDialog = store.state.collection[payload.dialogId];
				if (!existingItem)
				{
					return false;
				}

				const newCounter = existingItem.lastMessageViews.countOfViewers + 1;
				store.commit('update', {
					actionName: 'incrementLastMessageViews',
					dialogId: payload.dialogId,
					fields: {
						lastMessageViews: {...existingItem.lastMessageViews, countOfViewers: newCounter}
					}
				});
			}
		};
	}

	getMutations()
	{
		return {
			add: (state: DialogState, payload) =>
			{
				state.collection[payload.dialogId] = payload.fields;
			},
			update: (state: DialogState, payload) =>
			{
				state.collection[payload.dialogId] = {...state.collection[payload.dialogId], ...payload.fields};
			},
			delete: (state: DialogState, payload) =>
			{
				delete state.collection[payload.dialogId];
			},
			setChatOptions: (state: DialogState, payload) =>
			{
				state.chatOptions = payload;
			},
			clearCounters: (state: DialogState) =>
			{
				Object.keys(state.collection).forEach(key => {
					state.collection[key].counter = 0;
					state.collection[key].markedId = 0;
				});
			},
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

		if (!Type.isUndefined(fields.last_id))
		{
			fields.lastId = fields.last_id;
		}
		if (Type.isNumber(fields.lastId))
		{
			result.lastReadId = fields.lastId;
		}

		if (!Type.isUndefined(fields.marked_id))
		{
			fields.markedId = fields.marked_id;
		}
		if (Type.isNumber(fields.markedId))
		{
			result.markedId = fields.markedId;
		}

		if (!Type.isUndefined(fields.last_message_id))
		{
			fields.lastMessageId = fields.last_message_id;
		}
		if (Type.isNumber(fields.lastMessageId) || Type.isStringFilled(fields.lastMessageId))
		{
			result.lastMessageId = Number.parseInt(fields.lastMessageId, 10);
		}

		if (Type.isPlainObject(fields.last_message_views))
		{
			fields.lastMessageViews = fields.last_message_views;
		}
		if (Type.isPlainObject(fields.lastMessageViews))
		{
			result.lastMessageViews = this.prepareLastMessageViews(fields.lastMessageViews);
		}

		if (Type.isBoolean(fields.hasPrevPage))
		{
			result.hasPrevPage = fields.hasPrevPage;
		}

		if (Type.isBoolean(fields.hasNextPage))
		{
			result.hasNextPage = fields.hasNextPage;
		}

		if (Type.isNumber(fields.savedPositionMessageId))
		{
			result.savedPositionMessageId = fields.savedPositionMessageId;
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
			result.name = Text.decode(fields.name.toString());
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

		if (Type.isBoolean(fields.inited))
		{
			result.inited = fields.inited;
		}

		if (Type.isBoolean(fields.loading))
		{
			result.loading = fields.loading;
		}

		if (Type.isString(fields.description))
		{
			result.description = fields.description;
		}

		if (Type.isNumber(fields.disk_folder_id))
		{
			result.diskFolderId = fields.disk_folder_id;
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

	prepareWritingList(writingList: Object[]): Array<{userId: number, userName: string}>
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

	prepareLastMessageViews(rawLastMessageViews): {countOfViewers: number, firstViewers: Object[], messageId: number}
	{
		const {
			count_of_viewers: countOfViewers,
			first_viewers: rawFirstViewers,
			message_id: messageId
		} = rawLastMessageViews;

		let firstViewer;
		rawFirstViewers.forEach(rawFirstViewer => {
			if (rawFirstViewer.user_id === Core.getUserId())
			{
				return;
			}

			firstViewer = {
				userId: rawFirstViewer.user_id,
				userName: rawFirstViewer.user_name,
				date: Utils.date.cast(rawFirstViewer.date)
			};
		});

		if (countOfViewers > 0 && !firstViewer)
		{
			throw new Error('Dialogues model: no first viewer for message');
		}

		return {
			countOfViewers,
			firstViewer,
			messageId
		};
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