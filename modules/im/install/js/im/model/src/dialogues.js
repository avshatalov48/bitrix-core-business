/**
 * Bitrix Messenger
 * Dialogues model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {StorageLimit} from "im.const";
import {Utils} from "im.lib.utils";

export class DialoguesModel extends VuexBuilderModel
{
	getName()
	{
		return 'dialogues';
	}

	getState()
	{
		return {
			host: this.getVariable('host', location.protocol+'//'+location.host),
			collection: {},
			saveDialogList: [],
			saveChatList: [],
		}
	}

	getStateSaveException()
	{
		return {
			host: null
		}
	}

	getElementStateSaveException()
	{
		return {
			writingList: null,
			quoteId: null
		}
	}

	getElementState()
	{
		return {
			dialogId: '0',
			chatId: 0,
			counter: 0,
			userCounter: 0,
			messageCount: 0,
			unreadId: 0,
			lastMessageId: 0,
			managerList: [],
			readedList: [],
			writingList: [],
			muteList: [],
			textareaMessage: "",
			quoteId: 0,
			editId: 0,
			init: false,

			name: "",
			owner: 0,
			extranet: false,
			avatar: "",
			color: "#17A3EA",
			type: "chat",
			entityType: "",
			entityId: "",
			entityData1: "",
			entityData2: "",
			entityData3: "",
			dateCreate: new Date(),
			restrictions: {
				avatar: true,
				extend: true,
				leave: true,
				leaveOwner: true,
				rename: true,
				send: true,
				userList: true,
				mute: true,
				call: true,
			},
			public: {
				code: '',
				link: ''
			}
		};
	}

	getGetters()
	{
		return {
			get: state => dialogId =>
			{
				if (!state.collection[dialogId])
				{
					return null;
				}

				return state.collection[dialogId];
			},
			getByChatId: state => chatId =>
			{
				chatId = parseInt(chatId);

				for (let dialogId in state.collection)
				{
					if (!state.collection.hasOwnProperty(dialogId))
					{
						continue;
					}

					if (state.collection[dialogId].chatId === chatId)
					{
						return state.collection[dialogId];
					}
				}

				return null;
			},
			getBlank: state => params =>
			{
				return this.getElementState();
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
			canSaveChat: state => chatId =>
			{
				if (/^\d+$/.test(chatId))
				{
					chatId = parseInt(chatId);
				}
				return state.saveChatList.includes(parseInt(chatId));
			},
			canSaveDialog: state => dialogId =>
			{
				return state.saveDialogList.includes(dialogId.toString());
			},
			isPrivateDialog: state => dialogId =>
			{
				dialogId = dialogId.toString();
				return state.collection[dialogId.toString()] && state.collection[dialogId].type === 'private';
			},
		}
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(dialog => {
						return Object.assign(
							{},
							this.validate(Object.assign({}, dialog), {host: store.state.host}),
							{init: true}
						);
					});
				}
				else
				{
					let result = [];
					result.push(Object.assign(
						{},
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
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}
				store.commit('update', {
					dialogId : payload.dialogId,
					fields : this.validate(Object.assign({}, payload.fields), {host: store.state.host})
				});

				return true;
			},

			delete: (store, payload) =>
			{
				store.commit('delete', payload.dialogId);
				return true;
			},

			updateWriting: (store, payload) =>
			{
				if (
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}

				let index = store.state.collection[payload.dialogId].writingList.findIndex(el => el.userId === payload.userId);
				if (payload.action)
				{
					if (index >= 0)
					{
						return true;
					}
					else
					{
						let writingList = [].concat(store.state.collection[payload.dialogId].writingList);
						writingList.unshift({
							userId: payload.userId,
							userName: payload.userName,
						});

						store.commit('update', {
							actionName: 'updateWriting/1',
							dialogId : payload.dialogId,
							fields : this.validate({writingList}, {host: store.state.host})
						});
					}
				}
				else
				{
					if (index >= 0)
					{
						let writingList = store.state.collection[payload.dialogId].writingList.filter(el => el.userId !== payload.userId);
						store.commit('update', {
							actionName: 'updateWriting/2',
							dialogId : payload.dialogId,
							fields : this.validate({writingList}, {host: store.state.host})
						});

						return true;
					}
					else
					{
						return true;
					}
				}

				return false;
			},

			updateReaded: (store, payload) =>
			{
				if (
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}

				let readedList = store.state.collection[payload.dialogId].readedList.filter(el => el.userId !== payload.userId);

				if (payload.action)
				{
					readedList.push({
						userId: payload.userId,
						userName: payload.userName || '',
						messageId: payload.messageId,
						date: payload.date || (new Date()),
					});
				}

				store.commit('update', {
					actionName: 'updateReaded',
					dialogId : payload.dialogId,
					fields : this.validate({readedList}, {host: store.state.host})
				});

				return false;
			},

			increaseCounter: (store, payload) =>
			{
				if (
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}

				let counter = store.state.collection[payload.dialogId].counter;
				if (counter === 100)
				{
					return true;
				}

				let increasedCounter = counter + payload.count;
				if (increasedCounter > 100)
				{
					increasedCounter = 100;
				}

				const userId = store.rootState.application?.common.userId;
				const dialogMuted = userId && store.state.collection[payload.dialogId].muteList.includes(userId);

				store.commit('update', {
					actionName: 'increaseCounter',
					dialogId : payload.dialogId,
					dialogMuted,
					fields: {
						counter: increasedCounter,
						previousCounter: counter
					}
				});

				return false;
			},

			decreaseCounter: (store, payload) =>
			{
				if (
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}

				let counter = store.state.collection[payload.dialogId].counter;
				if (counter === 100)
				{
					return true;
				}

				let decreasedCounter = counter - payload.count;
				if (decreasedCounter < 0)
				{
					decreasedCounter = 0;
				}

				let unreadId = payload.unreadId > store.state.collection[payload.dialogId].unreadId? payload.unreadId: store.state.collection[payload.dialogId].unreadId;

				if (
					store.state.collection[payload.dialogId].unreadId !== unreadId
					|| store.state.collection[payload.dialogId].counter !== decreasedCounter
				)
				{
					const previousCounter = store.state.collection[payload.dialogId].counter;
					if (decreasedCounter === 0)
					{
						unreadId = 0;
					}

					const userId = store.rootState.application?.common.userId;
					const dialogMuted = userId && store.state.collection[payload.dialogId].muteList.includes(userId);

					store.commit('update', {
						actionName: 'decreaseCounter',
						dialogId : payload.dialogId,
						dialogMuted,
						fields : {
							counter: decreasedCounter,
							previousCounter,
							unreadId
						}
					});
				}

				return false;
			},

			increaseMessageCounter: (store, payload) =>
			{
				if (
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}

				const currentCounter = store.state.collection[payload.dialogId].messageCount;

				store.commit('update', {
					actionName: 'increaseMessageCount',
					dialogId : payload.dialogId,
					fields : {
						messageCount: currentCounter + payload.count,
					}
				});
			},

			saveDialog: (store, payload) =>
			{
				if (
					typeof store.state.collection[payload.dialogId] === 'undefined'
					|| store.state.collection[payload.dialogId].init === false
				)
				{
					return true;
				}

				store.commit('saveDialog', {
					dialogId : payload.dialogId,
					chatId : payload.chatId
				});

				return false;
			},
		};
	}

	getMutations()
	{
		return {
			initCollection: (state, payload) =>
			{
				this.initCollection(state, payload);
			},
			saveDialog: (state, payload) =>
			{
				// TODO if payload.dialogId is IMOL, skip update this flag
				if (!(payload.chatId > 0 && payload.dialogId.length > 0))
				{
					return false;
				}

				let saveDialogList = state.saveDialogList.filter(function(element) {
					return element !== payload.dialogId;
				});

				saveDialogList.unshift(payload.dialogId);

				saveDialogList = saveDialogList.slice(0, StorageLimit.dialogues);

				if (state.saveDialogList.join(',') === saveDialogList.join(','))
				{
					return true;
				}

				state.saveDialogList = saveDialogList;


				let saveChatList = state.saveChatList.filter(function(element) {
					return element !== payload.chatId;
				});

				saveChatList.unshift(payload.chatId);

				state.saveChatList = saveChatList.slice(0, StorageLimit.dialogues);

				this.saveState(state);
			},
			set: (state, payload) =>
			{
				for (let element of payload)
				{
					this.initCollection(state, {dialogId: element.dialogId});

					state.collection[element.dialogId] = Object.assign(
						this.getElementState(),
						state.collection[element.dialogId],
						element
					);
				}

				// TODO if payload.dialogId is IMOL, skip update cache
				this.saveState(state);
			},
			update: (state, payload) =>
			{
				this.initCollection(state, payload);

				state.collection[payload.dialogId] = Object.assign(
					state.collection[payload.dialogId],
					payload.fields
				);

				// TODO if payload.dialogId is IMOL, skip update cache
				this.saveState(state);
			},
			delete: (state, payload) =>
			{
				delete state.collection[payload.dialogId];

				// TODO if payload.dialogId is IMOL, skip update cache
				this.saveState(state);
			}
		};
	}

	initCollection(state, payload)
	{
		if (typeof state.collection[payload.dialogId] !== 'undefined')
		{
			return true
		}

		Vue.set(state.collection, payload.dialogId, this.getElementState());

		if (payload.fields)
		{
			state.collection[payload.dialogId] = Object.assign(
				state.collection[payload.dialogId],
				this.validate(Object.assign({}, payload.fields), {host: state.host})
			);
		}

		return true;
	}

	getSaveTimeout()
	{
		return 100;
	}

	saveState(state = {})
	{
		if (!this.isSaveAvailable())
		{
			return true;
		}

		super.saveState(() =>
		{
			let storedState = {
				collection: {},
				saveDialogList: [].concat(state.saveDialogList),
				saveChatList: [].concat(state.saveChatList),
			};

			state.saveDialogList.forEach(dialogId => {
				if (!state.collection[dialogId])
					return false;

				storedState.collection[dialogId] = Object.assign(
					this.getElementState(),
					this.cloneState(state.collection[dialogId], this.getElementStateSaveException())
				);
			});

			return storedState;
		});
	}

	validate(fields, options = {})
	{
		const result = {};

		options.host = options.host || this.getState().host;

		if (typeof fields.dialog_id !== 'undefined')
		{
			fields.dialogId = fields.dialog_id;
		}
		if (typeof fields.dialogId === "number" || typeof fields.dialogId === "string")
		{
			result.dialogId = fields.dialogId.toString();
		}

		if (typeof fields.chat_id !== 'undefined')
		{
			fields.chatId = fields.chat_id;
		}
		else if (typeof fields.id !== 'undefined')
		{
			fields.chatId = fields.id;
		}
		if (typeof fields.chatId === "number" || typeof fields.chatId === "string")
		{
			result.chatId = parseInt(fields.chatId);
		}
		if (typeof fields.quoteId === "number")
		{
			result.quoteId = parseInt(fields.quoteId);
		}
		if (typeof fields.editId === "number")
		{
			result.editId = parseInt(fields.editId);
		}

		if (typeof fields.counter === "number" || typeof fields.counter === "string")
		{
			result.counter = parseInt(fields.counter);
		}

		if (typeof fields.user_counter === "number" || typeof fields.user_counter === "string")
		{
			result.userCounter = parseInt(fields.user_counter);
		}
		if (typeof fields.userCounter === "number" || typeof fields.userCounter === "string")
		{
			result.userCounter = parseInt(fields.userCounter);
		}

		if (typeof fields.message_count === "number" || typeof fields.message_count === "string")
		{
			result.messageCount = parseInt(fields.message_count);
		}
		if (typeof fields.messageCount === "number" || typeof fields.messageCount === "string")
		{
			result.messageCount = parseInt(fields.messageCount);
		}

		if (typeof fields.unread_id !== 'undefined')
		{
			fields.unreadId = fields.unread_id;
		}
		if (typeof fields.unreadId === "number" || typeof fields.unreadId === "string")
		{
			result.unreadId = parseInt(fields.unreadId);
		}

		if (typeof fields.last_message_id !== 'undefined')
		{
			fields.lastMessageId = fields.last_message_id;
		}
		if (typeof fields.lastMessageId === "number" || typeof fields.lastMessageId === "string")
		{
			result.lastMessageId = parseInt(fields.lastMessageId);
		}

		if (typeof fields.readed_list !== 'undefined')
		{
			fields.readedList = fields.readed_list;
		}
		if (typeof fields.readedList !== 'undefined')
		{
			result.readedList = [];

			if (fields.readedList instanceof Array)
			{
				fields.readedList.forEach(element =>
				{
					let record = {};
					if (typeof element.user_id !== 'undefined')
					{
						element.userId = element.user_id;
					}
					if (typeof element.user_name !== 'undefined')
					{
						element.userName = element.user_name;
					}
					if (typeof element.message_id !== 'undefined')
					{
						element.messageId = element.message_id;
					}

					if (!element.userId || !element.userName || !element.messageId)
					{
						return false;
					}

					record.userId = parseInt(element.userId);
					record.userName = element.userName.toString();
					record.messageId = parseInt(element.messageId);

					record.date = Utils.date.cast(element.date);

					result.readedList.push(record);
				})
			}
		}

		if (typeof fields.writing_list !== 'undefined')
		{
			fields.writingList = fields.writing_list;
		}
		if (typeof fields.writingList !== 'undefined')
		{
			result.writingList = [];

			if (fields.writingList instanceof Array)
			{
				fields.writingList.forEach(element =>
				{
					let record = {};

					if (!element.userId)
					{
						return false;
					}

					record.userId = parseInt(element.userId);
					record.userName = Utils.text.htmlspecialcharsback(element.userName);

					result.writingList.push(record);
				})
			}
		}

		if (typeof fields.manager_list !== 'undefined')
		{
			fields.managerList = fields.manager_list;
		}
		if (typeof fields.managerList !== 'undefined')
		{
			result.managerList = [];

			if (fields.managerList instanceof Array)
			{
				fields.managerList.forEach(userId =>
				{
					userId = parseInt(userId);
					if (userId > 0)
					{
						result.managerList.push(userId);
					}
				});
			}
		}

		if (typeof fields.mute_list !== 'undefined')
		{
			fields.muteList = fields.mute_list;
		}
		if (typeof fields.muteList !== 'undefined')
		{
			result.muteList = [];

			if (fields.muteList instanceof Array)
			{
				fields.muteList.forEach(userId =>
				{
					userId = parseInt(userId);
					if (userId > 0)
					{
						result.muteList.push(userId);
					}
				});
			}
			else if (typeof fields.muteList === 'object')
			{
				Object.entries(fields.muteList).forEach(entry => {
					if (entry[1] === true)
					{
						const userId = parseInt(entry[0]);
						if (userId > 0)
						{
							result.muteList.push(userId);
						}
					}
				});
			}
		}

		if (typeof fields.textareaMessage !== 'undefined')
		{
			result.textareaMessage = fields.textareaMessage.toString();
		}

		if (typeof fields.title !== 'undefined')
		{
			fields.name = fields.title;
		}
		if (typeof fields.name === "string" || typeof fields.name === "number")
		{
			result.name = Utils.text.htmlspecialcharsback(fields.name.toString());
		}

		if (typeof fields.owner !== 'undefined')
		{
			fields.ownerId = fields.owner;
		}
		if (typeof fields.ownerId === "number" || typeof fields.ownerId === "string")
		{
			result.ownerId = parseInt(fields.ownerId);
		}

		if (typeof fields.extranet === "boolean")
		{
			result.extranet = fields.extranet;
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

		if (typeof fields.color === "string")
		{
			result.color = fields.color.toString();
		}

		if (typeof fields.type === "string")
		{
			result.type = fields.type.toString();
		}

		if (typeof fields.entity_type !== 'undefined')
		{
			fields.entityType = fields.entity_type;
		}
		if (typeof fields.entityType === "string")
		{
			result.entityType = fields.entityType.toString();
		}
		if (typeof fields.entity_id !== 'undefined')
		{
			fields.entityId = fields.entity_id;
		}
		if (typeof fields.entityId === "string" || typeof fields.entityId === "number")
		{
			result.entityId = fields.entityId.toString();
		}

		if (typeof fields.entity_data_1 !== 'undefined')
		{
			fields.entityData1 = fields.entity_data_1;
		}
		if (typeof fields.entityData1 === "string")
		{
			result.entityData1 = fields.entityData1.toString();
		}

		if (typeof fields.entity_data_2 !== 'undefined')
		{
			fields.entityData2 = fields.entity_data_2;
		}
		if (typeof fields.entityData2 === "string")
		{
			result.entityData2 = fields.entityData2.toString();
		}

		if (typeof fields.entity_data_3 !== 'undefined')
		{
			fields.entityData3 = fields.entity_data_3;
		}
		if (typeof fields.entityData3 === "string")
		{
			result.entityData3 = fields.entityData3.toString();
		}

		if (typeof fields.date_create !== 'undefined')
		{
			fields.dateCreate = fields.date_create;
		}

		if (typeof fields.dateCreate !== "undefined")
		{
			result.dateCreate = Utils.date.cast(fields.dateCreate);
		}

		if (typeof fields.dateLastOpen !== "undefined")
		{
			result.dateLastOpen = Utils.date.cast(fields.dateLastOpen);
		}

		if (typeof fields.restrictions === 'object' && fields.restrictions)
		{
			result.restrictions = {};

			if (typeof fields.restrictions.avatar === 'boolean')
			{
				result.restrictions.avatar = fields.restrictions.avatar;
			}

			if (typeof fields.restrictions.extend === 'boolean')
			{
				result.restrictions.extend = fields.restrictions.extend;
			}

			if (typeof fields.restrictions.leave === 'boolean')
			{
				result.restrictions.leave = fields.restrictions.leave;
			}

			if (typeof fields.restrictions.leave_owner === 'boolean')
			{
				result.restrictions.leaveOwner = fields.restrictions.leave_owner;
			}

			if (typeof fields.restrictions.rename === 'boolean')
			{
				result.restrictions.rename = fields.restrictions.rename;
			}

			if (typeof fields.restrictions.send === 'boolean')
			{
				result.restrictions.send = fields.restrictions.send;
			}

			if (typeof fields.restrictions.user_list === 'boolean')
			{
				result.restrictions.userList = fields.restrictions.user_list;
			}

			if (typeof fields.restrictions.mute === 'boolean')
			{
				result.restrictions.mute = fields.restrictions.mute;
			}

			if (typeof fields.restrictions.call === 'boolean')
			{
				result.restrictions.call = fields.restrictions.call;
			}
		}

		if (typeof fields.public === 'object' && fields.public)
		{
			result.public = {};

			if (typeof fields.public.code === 'string')
			{
				result.public.code = fields.public.code;
			}

			if (typeof fields.public.link === 'string')
			{
				result.public.link = fields.public.link;
			}
		}

		return result;
	}
}