/**
 * Bitrix Messenger
 * Messages model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */


import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import { MutationType, RecentSection as Section, StorageLimit, EventType } from 'im.const';
import {Utils} from "im.lib.utils";
import { Logger } from "im.lib.logger";

import {EventEmitter} from 'main.core.events';

const IntersectionType = {
	empty: 'empty',
	equal: 'equal',
	none: 'none',
	found: 'found',
	foundReverse: 'foundReverse',
};

export class MessagesModel extends VuexBuilderModel
{
	getName()
	{
		return 'messages';
	}

	getState()
	{
		return {
			created: 0,
			collection: {},
			mutationType: {},
			saveMessageList: {},
			saveFileList: {},
			saveUserList: {},
			host: this.getVariable('host', location.protocol+'//'+location.host),
		}
	}

	getElementState()
	{
		return {
			templateId: 0,
			templateType: 'message',
			placeholderType: 0,

			id: 0,
			chatId: 0,
			authorId: 0,
			date: new Date(),
			text: "",
			textConverted: "",
			params: {
				TYPE : 'default',
				COMPONENT_ID : 'bx-im-view-message',
			},

			push: false,
			unread: false,
			sending: false,
			error: false,
			retry: false,
			blink: false,
		};
	}

	getGetters()
	{
		return {

			getMutationType: state => chatId =>
			{
				if (!state.mutationType[chatId])
				{
					return {initialType: MutationType.none, appliedType: MutationType.none};
				}

				return state.mutationType[chatId];
			},
			getLastId: state => chatId =>
			{
				if (!state.collection[chatId] || state.collection[chatId].length <= 0)
				{
					return null;
				}

				let lastId = 0;

				for (let i = 0; i < state.collection[chatId].length; i++)
				{
					let element = state.collection[chatId][i];
					if (
						element.push
						|| element.sending
						|| element.id.toString().startsWith('temporary')
					)
					{
						continue;
					}

					if (lastId < element.id)
					{
						lastId = element.id;
					}
				}

				return lastId? lastId: null;
			},
			getMessage: state => (chatId, messageId) =>
			{
				if (!state.collection[chatId] || state.collection[chatId].length <= 0)
				{
					return null;
				}

				for (let index = state.collection[chatId].length-1; index >= 0; index--)
				{
					if (state.collection[chatId][index].id === messageId)
					{
						return state.collection[chatId][index];
					}
				}

				return null;
			},
			get: state => chatId =>
			{
				if (!state.collection[chatId] || state.collection[chatId].length <= 0)
				{
					return [];
				}

				return state.collection[chatId];
			},
			getBlank: state => params =>
			{
				return this.getElementState();
			},
			getSaveFileList: state => params =>
			{
				return state.saveFileList;
			},
			getSaveUserList: state => params =>
			{
				return state.saveUserList;
			},
		}
	}

	getActions()
	{
		return {
			add: (store, payload) =>
			{
				let result = this.validate(Object.assign({}, payload));
				result.params = Object.assign({}, this.getElementState().params, result.params);
				if (payload.id)
				{
					if (store.state.collection[payload.chatId])
					{
						const countMessages = store.state.collection[payload.chatId].length-1;
						for (let index = countMessages; index >= 0; index--)
						{
							const message = store.state.collection[payload.chatId][index];
							if (message.templateId === payload.id)
							{
								return;
							}
						}
					}

					result.id = payload.id;
				}
				else
				{
					result.id = 'temporary' + (new Date).getTime() + store.state.created;
				}
				result.templateId = result.id;
				result.unread = false;

				store.commit('add', Object.assign({}, this.getElementState(), result));

				if (payload.sending !== false)
				{
					store.dispatch('actionStart', {
						id: result.id,
						chatId: result.chatId,
					});
				}

				return result.id;
			},
			actionStart: (store, payload) =>
			{
				if (/^\d+$/.test(payload.id))
				{
					payload.id = parseInt(payload.id);
				}

				payload.chatId = parseInt(payload.chatId);

				Vue.nextTick(() => {
					store.commit('update', {
						id : payload.id ,
						chatId : payload.chatId,
						fields : {sending: true}
					});
				});
			},
			actionError: (store, payload) =>
			{
				if (/^\d+$/.test(payload.id))
				{
					payload.id = parseInt(payload.id);
				}
				payload.chatId = parseInt(payload.chatId);

				Vue.nextTick(() => {
					store.commit('update', {
						id : payload.id ,
						chatId : payload.chatId,
						fields : {sending: false, error: true, retry: payload.retry !== false}
					});
				});
			},
			actionFinish: (store, payload) =>
			{
				if (/^\d+$/.test(payload.id))
				{
					payload.id = parseInt(payload.id);
				}
				payload.chatId = parseInt(payload.chatId);

				Vue.nextTick(() => {
					store.commit('update', {
						id : payload.id ,
						chatId : payload.chatId,
						fields : {sending: false, error: false, retry: false}
					});
				});
			},
			set: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(message => this.prepareMessage(message, {host: store.state.host}));
				}
				else
				{
					let result = this.prepareMessage(payload, {host: store.state.host});
					(payload = []).push(result);
				}

				store.commit('set', {
					insertType : MutationType.set,
					data : payload
				});

				return 'set is done';
			},
			addPlaceholders: (store, payload) =>
			{
				if (payload.placeholders instanceof Array)
				{
					payload.placeholders = payload.placeholders.map(message => this.prepareMessage(message, { host: store.state.host }));
				}
				else
				{
					return false;
				}

				const insertType = payload.requestMode === 'history'? MutationType.setBefore : MutationType.setAfter;
				if (insertType === MutationType.setBefore)
				{
					payload.placeholders = payload.placeholders.reverse();
				}

				store.commit('set', {
					insertType,
					data : payload.placeholders
				});

				return payload.placeholders[0].id;
			},
			clearPlaceholders: (store, payload) =>
			{
				store.commit('clearPlaceholders', payload);
			},
			updatePlaceholders: (store, payload) =>
			{
				if (payload.data instanceof Array)
				{
					payload.data = payload.data.map(message => this.prepareMessage(message, { host: store.state.host }));
				}
				else
				{
					return false;
				}

				store.commit('updatePlaceholders', payload);

				return true;
			},
			setAfter: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(message => this.prepareMessage(message));
				}
				else
				{
					let result = this.prepareMessage(payload);
					(payload = []).push(result);
				}

				store.commit('set', {
					insertType : MutationType.setAfter,
					data : payload
				});
			},
			setBefore: (store, payload) =>
			{
				if (payload instanceof Array)
				{
					payload = payload.map(message => this.prepareMessage(message));
				}
				else
				{
					let result = this.prepareMessage(payload);
					(payload = []).push(result);
				}

				store.commit('set', {
					insertType : MutationType.setBefore,
					data : payload
				});
			},
			update: (store, payload) =>
			{
				if (/^\d+$/.test(payload.id))
				{
					payload.id = parseInt(payload.id);
				}
				if (/^\d+$/.test(payload.chatId))
				{
					payload.chatId = parseInt(payload.chatId);
				}

				store.commit('initCollection', {chatId: payload.chatId});

				if (!store.state.collection[payload.chatId])
				{
					return false;
				}

				let index = store.state.collection[payload.chatId].findIndex(el => el.id === payload.id);
				if (index < 0)
				{
					return false;
				}

				let result = this.validate(Object.assign({}, payload.fields));

				if (result.params)
				{
					result.params = Object.assign(
						{},
						this.getElementState().params,
						store.state.collection[payload.chatId][index].params,
						result.params
					);
				}

				store.commit('update', {
					id : payload.id,
					chatId : payload.chatId,
					index : index,
					fields : result
				});

				if (payload.fields.blink)
				{
					setTimeout(() => {
						store.commit('update', {
							id : payload.id ,
							chatId : payload.chatId,
							fields : {blink: false}
						});
					}, 1000);
				}

				return true;
			},
			delete: (store, payload) =>
			{
				if (!(payload.id instanceof Array))
				{
					payload.id = [payload.id];
				}

				payload.id = payload.id.map(id => {
					if (/^\d+$/.test(id))
					{
						id = parseInt(id);
					}
					return id;
				});

				store.commit('delete', {
					chatId : payload.chatId,
					elements : payload.id,
				});

				return true;
			},
			clear: (store, payload) =>
			{
				payload.chatId = parseInt(payload.chatId);

				if (payload.keepPlaceholders)
				{
					store.commit('clearMessages', {
						chatId : payload.chatId
					});
				}
				else
				{
					store.commit('clear', {
						chatId : payload.chatId
					});
				}

				return true;
			},
			applyMutationType: (store, payload) =>
			{
				payload.chatId = parseInt(payload.chatId);

				store.commit('applyMutationType', {
					chatId : payload.chatId
				});

				return true;
			},
			readMessages: (store, payload) =>
			{
				payload.readId = parseInt(payload.readId) || 0;
				payload.chatId = parseInt(payload.chatId);

				if (typeof store.state.collection[payload.chatId] === 'undefined')
				{
					return {count: 0}
				}

				let count = 0;
				for (let index = store.state.collection[payload.chatId].length-1; index >= 0; index--)
				{
					let element = store.state.collection[payload.chatId][index];
					if (!element.unread)
						continue;

					if (payload.readId === 0 || element.id <= payload.readId)
					{
						count++;
					}
				}

				store.commit('readMessages', {
					chatId: payload.chatId,
					readId: payload.readId,
				});

				return {count};
			},
			unreadMessages: (store, payload) =>
			{
				payload.unreadId = parseInt(payload.unreadId) || 0;
				payload.chatId = parseInt(payload.chatId);

				if (typeof store.state.collection[payload.chatId] === 'undefined' || !payload.unreadId)
				{
					return {count: 0}
				}

				let count = 0;
				for (let index = store.state.collection[payload.chatId].length-1; index >= 0; index--)
				{
					let element = store.state.collection[payload.chatId][index];
					if (element.unread)
						continue;

					if (element.id >= payload.unreadId)
					{
						count++;
					}
				}

				store.commit('unreadMessages', {
					chatId: payload.chatId,
					unreadId: payload.unreadId,
				});

				return {count};
			},
		}
	}

	getMutations()
	{
		return {
			initCollection: (state, payload) =>
			{
				return this.initCollection(state, payload);
			},
			add: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				state.collection[payload.chatId].push(payload);
				state.saveMessageList[payload.chatId].push(payload.id);

				state.created += 1;

				state.collection[payload.chatId].sort((a, b) => a.id - b.id);
				this.saveState(state, payload.chatId);
				Logger.warn('Messages model: saving state after add');
			},
			clearPlaceholders: (state, payload) =>
			{
				if (!state.collection[payload.chatId])
				{
					return false;
				}

				state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => {
					return !element.id.toString().startsWith('placeholder');
				});
			},
			updatePlaceholders: (state, payload) =>
			{
				const firstPlaceholderId = `placeholder${payload.firstMessage}`;
				const firstPlaceholderIndex = state.collection[payload.chatId].findIndex((message) => {
					return message.id === firstPlaceholderId;
				});
				// Logger.warn('firstPlaceholderIndex', firstPlaceholderIndex);
				if (firstPlaceholderIndex >= 0)
				{
					// Logger.warn('before delete', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
					state.collection[payload.chatId].splice(firstPlaceholderIndex, payload.amount);
					// Logger.warn('after delete', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
					state.collection[payload.chatId].splice(firstPlaceholderIndex, 0, ...payload.data);
					// Logger.warn('after add', state.collection[payload.chatId].length, [...state.collection[payload.chatId]]);
				}

				state.collection[payload.chatId].sort((a, b) => a.id - b.id);
				Logger.warn('Messages model: saving state after updating placeholders');
				this.saveState(state, payload.chatId);
			},
			set: (state, payload) =>
			{
				Logger.warn('Messages model: set mutation', payload);
				let chats = [];
				let chatsSave = [];
				let isPush = false;

				payload.data = MessagesModel.getPayloadWithTempMessages(state, payload);

				const initialType = payload.insertType;

				if (payload.insertType === MutationType.set)
				{
					payload.insertType = MutationType.setAfter;

					let elements = {};
					payload.data.forEach(element => {
						if (!elements[element.chatId])
						{
							elements[element.chatId] = [];
						}
						elements[element.chatId].push(element.id);
					});

					for (let chatId in elements)
					{
						if (!elements.hasOwnProperty(chatId))
							continue;

						this.initCollection(state, {chatId});
						Logger.warn('Messages model: messages before adding from request - ', state.collection[chatId].length);

						if (
							state.saveMessageList[chatId].length > elements[chatId].length
							|| elements[chatId].length < StorageLimit.messages
						)
						{
							state.collection[chatId] = state.collection[chatId].filter(element => elements[chatId].includes(element.id));
							state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(id => elements[chatId].includes(id));
						}

						Logger.warn('Messages model: cache length', state.saveMessageList[chatId].length);
						let intersection = this.manageCacheBeforeSet(
							[...state.saveMessageList[chatId].reverse()],
							elements[chatId]
						);
						Logger.warn('Messages model: set intersection with cache', intersection);

						if (intersection.type === IntersectionType.none)
						{
							if (intersection.foundElements.length > 0)
							{
								state.collection[chatId] = state.collection[chatId].filter(element => !intersection.foundElements.includes(element.id));
								state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(id => !intersection.foundElements.includes(id));
							}

							Logger.warn('Messages model: no intersection - removing cache');
							this.removeIntersectionCacheElements = state.collection[chatId].map(element => element.id);

							state.collection[chatId] = state.collection[chatId].filter(element => !this.removeIntersectionCacheElements.includes(element.id));
							state.saveMessageList[chatId] = state.saveMessageList[chatId].filter(id => !this.removeIntersectionCacheElements.includes(id));
							this.removeIntersectionCacheElements = [];
						}
						else if (intersection.type === IntersectionType.foundReverse)
						{
							Logger.warn('Messages model: found reverse intersection');
							payload.insertType = MutationType.setBefore;
							payload.data = payload.data.reverse();
						}
					}
				}

				Logger.warn('Messages model: adding messages to model', payload.data);
				for (let element of payload.data)
				{
					this.initCollection(state, {chatId: element.chatId});

					let index = state.collection[element.chatId].findIndex(localMessage => {
						if (MessagesModel.isTemporaryMessage(localMessage))
						{
							return localMessage.templateId === element.templateId;
						}

						return localMessage.id === element.id;
					});
					if (index > -1)
					{
						state.collection[element.chatId][index] = Object.assign(
							state.collection[element.chatId][index],
							element
						);
					}
					else if (payload.insertType === MutationType.setBefore)
					{
						state.collection[element.chatId].unshift(element);
					}
					else if (payload.insertType === MutationType.setAfter)
					{
						state.collection[element.chatId].push(element);
					}

					chats.push(element.chatId);

					if (this.store.getters['dialogues/canSaveChat'] && this.store.getters['dialogues/canSaveChat'](element.chatId))
					{
						chatsSave.push(element.chatId);
					}
				}

				chats = [...new Set(chats)];
				chatsSave = [...new Set(chatsSave)];

				isPush = payload.data.every(element => element.push === true);
				Logger.warn('Is it fake push message?', isPush);
				chats.forEach(chatId => {
					state.collection[chatId].sort((a, b) => a.id - b.id);

					if (!isPush)
					{
						//send event that messages are ready and we can start reading etc
						Logger.warn('setting messagesSet = true for chatId = ', chatId);
						setTimeout(() => {
							EventEmitter.emit(EventType.dialog.messagesSet, {chatId});
							EventEmitter.emit(EventType.dialog.readVisibleMessages, {chatId});
						}, 100);
					}
				});

				if (initialType !== MutationType.setBefore)
				{
					chatsSave.forEach(chatId => {
						Logger.warn('Messages model: saving state after set');
						this.saveState(state, chatId);
					});
				}
			},
			update: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				let index = -1;
				if (typeof payload.index !== 'undefined' && state.collection[payload.chatId][payload.index])
				{
					index = payload.index;
				}
				else
				{
					index = state.collection[payload.chatId].findIndex(el => el.id === payload.id);
				}

				if (index >= 0)
				{
					let isSaveState = (
						state.saveMessageList[payload.chatId].includes(state.collection[payload.chatId][index].id)
						|| payload.fields.id && !payload.fields.id.toString().startsWith('temporary') && state.collection[payload.chatId][index].id.toString().startsWith('temporary')
					);

					state.collection[payload.chatId][index] = Object.assign(
						state.collection[payload.chatId][index],
						payload.fields
					);

					if (isSaveState)
					{
						Logger.warn('Messages model: saving state after update');
						this.saveState(state, payload.chatId);
					}
				}
			},
			delete: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => !payload.elements.includes(element.id));

				if (state.saveMessageList[payload.chatId].length > 0)
				{
					for (let id of payload.elements)
					{
						if (state.saveMessageList[payload.chatId].includes(id))
						{
							Logger.warn('Messages model: saving state after delete');
							this.saveState(state, payload.chatId);

							break;
						}
					}
				}
			},
			clear: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				state.collection[payload.chatId] = [];
				state.saveMessageList[payload.chatId] = [];
			},
			clearMessages: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				state.collection[payload.chatId] = state.collection[payload.chatId].filter(element => {
					return element.id.toString().startsWith('placeholder');
				});
				state.saveMessageList[payload.chatId] = [];
			},
			applyMutationType: (state, payload) =>
			{
				if (typeof state.mutationType[payload.chatId] === 'undefined')
				{
					Vue.set(state.mutationType, payload.chatId, {applied: false, initialType: MutationType.none, appliedType: MutationType.none, scrollStickToTop: 0, scrollMessageId: 0});
				}

				state.mutationType[payload.chatId].applied = true;
			},
			readMessages: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				let saveNeeded = false;
				for (let index = state.collection[payload.chatId].length-1; index >= 0; index--)
				{
					let element = state.collection[payload.chatId][index];
					if (!element.unread)
						continue;

					if (payload.readId === 0 || element.id <= payload.readId)
					{
						state.collection[payload.chatId][index] = Object.assign(
							state.collection[payload.chatId][index],
							{unread: false}
						);
						saveNeeded = true;
					}
				}
				if (saveNeeded)
				{
					Logger.warn('Messages model: saving state after reading');
					this.saveState(state, payload.chatId);
				}
			},
			unreadMessages: (state, payload) =>
			{
				this.initCollection(state, {chatId: payload.chatId});

				let saveNeeded = false;
				for (let index = state.collection[payload.chatId].length-1; index >= 0; index--)
				{
					let element = state.collection[payload.chatId][index];
					if (element.unread)
						continue;

					if (element.id >= payload.unreadId)
					{
						state.collection[payload.chatId][index] = Object.assign(
							state.collection[payload.chatId][index],
							{unread: true}
						);
						saveNeeded = true;
					}
				}
				if (saveNeeded)
				{
					Logger.warn('Messages model: saving state after unreading');
					this.saveState(state, payload.chatId);
					this.updateSubordinateStates();
				}
			},
		}
	}

	initCollection(state, payload)
	{
		if (typeof payload.chatId === 'undefined')
		{
			return false;
		}

		if (
			typeof payload.chatId === 'undefined'
			|| typeof state.collection[payload.chatId] !== 'undefined'
		)
		{
			return true;
		}

		Vue.set(state.collection, payload.chatId, payload.messages? [].concat(payload.messages): []);
		Vue.set(state.saveMessageList, payload.chatId, []);
		Vue.set(state.saveFileList, payload.chatId, []);
		Vue.set(state.saveUserList, payload.chatId, []);

		return true;
	}

	prepareMessage(message, options = {})
	{
		let result = this.validate(Object.assign({}, message), options);

		result.params = Object.assign({}, this.getElementState().params, result.params);
		if (!result.templateId)
		{
			result.templateId = result.id;
		}

		return Object.assign({}, this.getElementState(), result);
	}

	manageCacheBeforeSet(cache, elements, recursive = false)
	{
		Logger.warn('manageCacheBeforeSet', cache, elements);
		let result = {
			type: IntersectionType.empty,
			foundElements: [],
			noneElements: []
		};

		if (!cache || cache.length <= 0)
		{
			return result;
		}

		for (let id of elements)
		{
			if (cache.includes(id))
			{
				if (result.type === IntersectionType.empty)
				{
					result.type = IntersectionType.found;
				}
				result.foundElements.push(id);
			}
			else
			{
				if (result.type === IntersectionType.empty)
				{
					result.type = IntersectionType.none;
				}
				result.noneElements.push(id);
			}
		}

		if (
			result.type === IntersectionType.found
			&& cache.length === elements.length
			&& result.foundElements.length === elements.length
		)
		{
			result.type = IntersectionType.equal;
		}
		else if (
			result.type === IntersectionType.none
			&& !recursive
			&& result.foundElements.length > 0
		)
		{
			let reverseResult = this.manageCacheBeforeSet(cache.reverse(), elements.reverse(), true);
			if (reverseResult.type === IntersectionType.found)
			{
				reverseResult.type = IntersectionType.foundReverse;
				return reverseResult;
			}
		}

		return result;
	}

	updateSaveLists(state, chatId)
	{
		if (!this.isSaveAvailable())
		{
			return true;
		}

		if (
			!chatId
			|| !this.store.getters['dialogues/canSaveChat']
			|| !this.store.getters['dialogues/canSaveChat'](chatId)
		)
		{
			return false;
		}

		this.initCollection(state, {chatId: chatId});

		let count = 0;
		let saveMessageList = [];
		let saveFileList = [];
		let saveUserList = [];

		let dialog = this.store.getters['dialogues/getByChatId'](chatId);
		if (dialog && dialog.type === 'private')
		{
			saveUserList.push(parseInt(dialog.dialogId));
		}

		let readCounter = 0;
		for (let index = state.collection[chatId].length-1; index >= 0; index--)
		{
			if (state.collection[chatId][index].id.toString().startsWith('temporary'))
			{
				continue;
			}

			if (!state.collection[chatId][index].unread)
			{
				readCounter++;
			}

			if (count >= StorageLimit.messages && readCounter >= 50)
			{
				break;
			}

			saveMessageList.unshift(state.collection[chatId][index].id);

			count++;
		}

		saveMessageList = saveMessageList.slice(0, StorageLimit.messages);

		state.collection[chatId].filter(element => saveMessageList.includes(element.id)).forEach(element =>
		{
			if (element.authorId > 0)
			{
				saveUserList.push(element.authorId);
			}

			if (element.params.FILE_ID instanceof Array)
			{
				saveFileList = element.params.FILE_ID.concat(saveFileList);
			}
		});

		state.saveMessageList[chatId] = saveMessageList;
		state.saveFileList[chatId] = [...new Set(saveFileList)];
		state.saveUserList[chatId] = [...new Set(saveUserList)];

		return true;
	}

	getSaveTimeout()
	{
		return 150;
	}

	saveState(state, chatId)
	{
		if (!this.updateSaveLists(state, chatId))
		{
			return false;
		}

		super.saveState(() =>
		{
			let storedState = {
				collection: {},
				saveMessageList: {},
				saveUserList: {},
				saveFileList: {},
			};

			for (let chatId in state.saveMessageList)
			{
				if (!state.saveMessageList.hasOwnProperty(chatId))
				{
					continue;
				}

				if (!state.collection[chatId])
				{
					continue;
				}

				if (!storedState.collection[chatId])
				{
					storedState.collection[chatId] = [];
				}

				state.collection[chatId]
					.filter(element => state.saveMessageList[chatId].includes(element.id))
					.forEach(element => {
						if (element.templateType !== 'placeholder')
						{
							storedState.collection[chatId].push(element);
						}
					});
				Logger.warn('Cache after updating', storedState.collection[chatId]);

				storedState.saveMessageList[chatId] = state.saveMessageList[chatId];
				storedState.saveFileList[chatId] = state.saveFileList[chatId];
				storedState.saveUserList[chatId] = state.saveUserList[chatId];
			}

			return storedState;
		});
	}

	updateSubordinateStates()
	{
		this.store.dispatch('users/saveState');
		this.store.dispatch('files/saveState');
	}

	validate(fields, options)
	{
		const result = {};

		if (typeof fields.id === "number")
		{
			result.id = fields.id;
		}
		else if (typeof fields.id === "string")
		{
			if (fields.id.startsWith('temporary') || fields.id.startsWith('placeholder') || Utils.types.isUuidV4(fields.id))
			{
				result.id = fields.id;
			}
			else
			{
				result.id = parseInt(fields.id);
			}
		}

		if (typeof fields.uuid === "string")
		{
			result.templateId = fields.uuid;
		}
		else if (typeof fields.templateId === "number")
		{
			result.templateId = fields.templateId;
		}
		else if (typeof fields.templateId === "string")
		{
			if (fields.templateId.startsWith('temporary') || Utils.types.isUuidV4(fields.templateId))
			{
				result.templateId = fields.templateId;
			}
			else
			{
				result.templateId = parseInt(fields.templateId);
			}
		}

		if (typeof fields.templateType === "string")
		{
			result.templateType = fields.templateType;
		}

		if (typeof fields.placeholderType === "number")
		{
			result.placeholderType = fields.placeholderType;
		}

		if (typeof fields.chat_id !== 'undefined')
		{
			fields.chatId = fields.chat_id;
		}
		if (typeof fields.chatId === "number" || typeof fields.chatId === "string")
		{
			result.chatId = parseInt(fields.chatId);
		}
		if (typeof fields.date !== "undefined")
		{
			result.date = Utils.date.cast(fields.date);
		}

		// previous P&P format
		if (typeof fields.textLegacy === "string" || typeof fields.textLegacy === "number")
		{
			if (typeof fields.text === "string" || typeof fields.text === "number")
			{
				result.text = fields.text.toString();
			}

			result.textConverted = this.convertToHtml({
				text: fields.textLegacy.toString(),
				isConverted: true
			});

			if (typeof fields.text === "string" || typeof fields.text === "number")
			{
				result.text = fields.text;
			}
		}
		else // modern format
		{
			if (typeof fields.text_converted !== 'undefined')
			{
				fields.textConverted = fields.text_converted;
			}
			if (typeof fields.textConverted === "string" || typeof fields.textConverted === "number")
			{
				result.textConverted = fields.textConverted.toString();
			}
			if (typeof fields.text === "string" || typeof fields.text === "number")
			{
				result.text = fields.text.toString();

				let isConverted = typeof result.textConverted !== 'undefined';

				result.textConverted = this.convertToHtml({
					text: isConverted? result.textConverted: result.text,
					isConverted
				});
			}
		}

		if (typeof fields.senderId !== 'undefined')
		{
			fields.authorId = fields.senderId;
		}
		else if (typeof fields.author_id !== 'undefined')
		{
			fields.authorId = fields.author_id;
		}
		if (typeof fields.authorId === "number" || typeof fields.authorId === "string")
		{
			if (fields.system === true || fields.system === 'Y')
			{
				result.authorId = 0;
			}
			else
			{
				result.authorId = parseInt(fields.authorId);
			}
		}

		if (typeof fields.params === "object" && fields.params !== null)
		{
			const params = this.validateParams(fields.params, options);
			if (params)
			{
				result.params = params;
			}
		}

		if (typeof fields.push === "boolean")
		{
			result.push = fields.push;
		}

		if (typeof fields.sending === "boolean")
		{
			result.sending = fields.sending;
		}

		if (typeof fields.unread === "boolean")
		{
			result.unread = fields.unread;
		}

		if (typeof fields.blink === "boolean")
		{
			result.blink = fields.blink;
		}

		if (typeof fields.error === "boolean" || typeof fields.error === "string")
		{
			result.error = fields.error;
		}

		if (typeof fields.retry === "boolean")
		{
			result.retry = fields.retry;
		}

		return result;
	}

	validateParams(params, options)
	{
		const result = {};
		try
		{
			for (let field in params)
			{
				if (!params.hasOwnProperty(field))
				{
					continue;
				}

				if (field === 'COMPONENT_ID')
				{
					if (typeof params[field] === "string" && BX.Vue.isComponent(params[field]))
					{
						result[field] = params[field];
					}
				}
				else if (field === 'LIKE')
				{
					if (params[field] instanceof Array)
					{
						result['REACTION'] = {like: params[field].map(element => parseInt(element))};
					}
				}
				else if (field === 'CHAT_LAST_DATE')
				{
					result[field] = Utils.date.cast(params[field]);
				}
				else if (field === 'AVATAR')
				{
					if (params[field])
					{
						result[field] = params[field].startsWith('http') ? params[field] : options.host + params[field];
					}
				}
				else if (field === 'NAME')
				{
					if (params[field])
					{
						result[field] = params[field];
					}
				}
				else if (field === 'LINK_ACTIVE')
				{
					if (params[field])
					{
						result[field] = params[field].map(function(userId) {
							return parseInt(userId)
						});
					}
				}
				else if (field === 'ATTACH')
				{
					result[field] = params[field];
				}
				else
				{
					result[field] = params[field];
				}
			}
		}
		catch (e) {}

		let hasResultElements = false;
		for (let field in result)
		{
			if (!result.hasOwnProperty(field))
			{
				continue;
			}

			hasResultElements = true;
			break
		}

		return hasResultElements? result: null;
	}

	convertToHtml(params = {})
	{
		let {
			quote = true,
			image = true,
			text = '',
			isConverted = false,
			enableBigSmile = true
		} = params;

		text = text.trim();

		if (!isConverted)
		{
			text = text.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
		}

		if (text.startsWith('/me'))
		{
			text = `<i>${text.substr(4)}</i>`;
		}
		else if (text.startsWith('/loud'))
		{
			text = `<b>${text.substr(6)}</b>`;
		}

		const quoteSign = "&gt;&gt;";
		if (quote && text.indexOf(quoteSign) >= 0)
		{
			let textPrepareFlag = false;
			let textPrepare = text.split(isConverted? "<br />": "\n");
			for (let i = 0; i < textPrepare.length; i++)
			{
				if (textPrepare[i].startsWith(quoteSign))
				{
					textPrepare[i] = textPrepare[i].replace(quoteSign, '<div class="bx-im-message-content-quote"><div class="bx-im-message-content-quote-wrap">');
					while (++i < textPrepare.length && textPrepare[i].startsWith(quoteSign))
					{
						textPrepare[i] = textPrepare[i].replace(quoteSign, '');
					}
					textPrepare[i - 1] += '</div></div><br>';
					textPrepareFlag = true;
				}
			}
			text = textPrepare.join("<br />");
		}

		text = text.replace(/\n/gi, '<br />');

		text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

		//text = this.decodeBbCode(text, false, enableBigSmile);
		text = Utils.text.decodeBbCode(text, enableBigSmile);

		if (quote)
		{
			text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\](?: #(?:(?:chat)?\d+|\d+:\d+)\/\d+)?<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, p4, offset) {
				return (offset > 0? '<br>': '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\"><div class=\"bx-im-message-content-quote-name\"><span class=\"bx-im-message-content-quote-name-text\">" + p1 + "</span><span class=\"bx-im-message-content-quote-name-time\">" + p2 + "</span></div>" + p3 + "</div></div><br />";
			});
			text = text.replace(/------------------------------------------------------<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, function (whole, p1, p2, p3, offset) {
				return (offset > 0? '<br>': '') + "<div class=\"bx-im-message-content-quote\"><div class=\"bx-im-message-content-quote-wrap\">" + p1 + "</div></div><br />";
			});
		}

		if (image)
		{
			let changed = false;
			text = text.replace(/<a(.*?)>(http[s]{0,1}:\/\/.*?)<\/a>/gi, function(whole, aInner, text, offset)
			{
				if(!text.match(/(\.(jpg|jpeg|png|gif|webp)\?|\.(jpg|jpeg|png|gif|webp)$)/i) || text.indexOf("/docs/pub/") > 0 || text.indexOf("logout=yes") > 0)
				{
					return whole;
				}
				else
				{
					changed = true;
					return (offset > 0? '<br />':'')+'<a' +aInner+ ' target="_blank" class="bx-im-element-file-image"><img src="'+text+'" class="bx-im-element-file-image-source-text" onerror="BX.Messenger.Model.MessagesModel.hideErrorImage(this)"></a></span>';
				}
			});
			if (changed)
			{
				text = text.replace(/<\/span>(\n?)<br(\s\/?)>/gi, '</span>').replace(/<br(\s\/?)>(\n?)<br(\s\/?)>(\n?)<span/gi, '<br /><span');
			}
		}

		if (enableBigSmile)
		{
			text = text.replace(
				/^(\s*<img\s+src=[^>]+?data-code=[^>]+?data-definition="UHD"[^>]+?style="width:)(\d+)(px[^>]+?height:)(\d+)(px[^>]+?class="bx-smile"\s*\/?>\s*)$/,
				function doubleSmileSize(match, start, width, middle, height, end) {
					return start + (parseInt(width, 10) * 1.7) + middle + (parseInt(height, 10) * 1.7) + end;
				}
			);
		}

		if (text.substr(-6) == '<br />')
		{
			text = text.substr(0, text.length - 6);
		}
		text = text.replace(/<br><br \/>/gi, '<br />');
		text = text.replace(/<br \/><br>/gi, '<br />');

		return text;
	};

	decodeBbCode(text, textOnly = false, enableBigSmile = true)
	{
		return MessagesModel.decodeBbCode({text, textOnly, enableBigSmile})
	}

	decodeAttach(item)
	{
		if (Array.isArray(item))
		{
			item.forEach(arrayElement => {
				arrayElement = this.decodeAttach(arrayElement);
			});
		}
		else if (typeof item === 'object' && item !== null)
		{
			for (const prop in item)
			{
				if (item.hasOwnProperty(prop))
				{
					item[prop] = this.decodeAttach(item[prop]);
				}
			}
		}
		else
		{
			if (typeof item === 'string')
			{
				item = Utils.text.htmlspecialcharsback(item);
			}
		}

		return item;
	}

	static decodeBbCode(params = {})
	{
		let {text, textOnly = false, enableBigSmile = true} = params;

		let putReplacement = [];
		text = text.replace(/\[PUT(?:=(.+?))?\](.+?)?\[\/PUT\]/gi, function(whole)
		{
			var id = putReplacement.length;
			putReplacement.push(whole);
			return '####REPLACEMENT_PUT_'+id+'####';
		});

		let sendReplacement = [];
		text = text.replace(/\[SEND(?:=(.+?))?\](.+?)?\[\/SEND\]/gi, function(whole)
		{
			var id = sendReplacement.length;
			sendReplacement.push(whole);
			return '####REPLACEMENT_SEND_'+id+'####';
		});

		let codeReplacement = [];
		text = text.replace(/\[CODE\]\n?(.*?)\[\/CODE\]/sig, function(whole, text) {
			let id = codeReplacement.length;
			codeReplacement.push(text);
			return '####REPLACEMENT_CODE_'+id+'####';
		});

		text = text.replace(/\[url=([^\]]+)\](.*?)\[\/url\]/gis, function(whole, link, text)
		{
			let tag = document.createElement('a');
			tag.href = Utils.text.htmlspecialcharsback(link);
			tag.target = '_blank';
			tag.text = Utils.text.htmlspecialcharsback(text);

			let allowList = [
				"http:",
				"https:",
				"ftp:",
				"file:",
				"tel:",
				"callto:",
				"mailto:",
				"skype:",
				"viber:",
			];
			if (allowList.indexOf(tag.protocol) <= -1)
			{
				return whole;
			}

			return tag.outerHTML;
		});

		text = text.replace(/\[url\]([^\]]+)\[\/url\]/gis, function(whole, link)
		{
			link = Utils.text.htmlspecialcharsback(link);

			let tag = document.createElement('a');
			tag.href = link;
			tag.target = '_blank';
			tag.text = link;

			let allowList = [
				"http:",
				"https:",
				"ftp:",
				"file:",
				"tel:",
				"callto:",
				"mailto:",
				"skype:",
				"viber:",
			];
			if (allowList.indexOf(tag.protocol) <= -1)
			{
				return whole;
			}

			return tag.outerHTML;
		});

		text = text.replace(/\[LIKE\]/gi, '<span class="bx-smile bx-im-smile-like"></span>');
		text = text.replace(/\[DISLIKE\]/gi, '<span class="bx-smile bx-im-smile-dislike"></span>');

		text = text.replace(/\[BR\]/gi, '<br/>');
		text = text.replace(/\[([buis])\](.*?)\[(\/[buis])\]/gi, (whole, open, inner, close) => '<'+open+'>'+inner+'<'+close+'>'); // TODO tag USER

		// this code needs to be ported to im/install/js/im/view/message/body/src/body.js:229
		text = text.replace(/\[CHAT=(imol\|)?([0-9]{1,})\](.*?)\[\/CHAT\]/gi, (whole, openlines, chatId, inner) => openlines? inner: '<span class="bx-im-mention" data-type="CHAT" data-value="chat'+chatId+'">'+inner+'</span>'); // TODO tag CHAT

		if (false && Utils.device.isMobile())
		{
			let replacements = [];
			text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/gi, (whole, number, text) => {
				let index = replacements.length;
				replacements.push({number, text});
				return `####REPLACEMENT_MARK_${index}####`;
			});

			text = text.replace(/[+]{0,1}(?:[-\/. ()\[\]~;#,]*[0-9]){10,}[^\n\r<][-\/. ()\[\]~;#,0-9^]*/g, (number) => {
				let pureNumber = number.replace(/\D/g, '');
				return `[CALL=${pureNumber}]${number}[/CALL]`;
			});

			replacements.forEach((item, index) => {
				text = text.replace(`####REPLACEMENT_MARK_${index}####`, `[CALL=${item.number}]${item.text}[/CALL]`)
			});
		}

		text = text.replace(/\[CALL(?:=(.+?))?\](.+?)?\[\/CALL\]/gi, (whole, number, text) => '<span class="bx-im-mention" data-type="CALL" data-value="'+Utils.text.htmlspecialchars(number)+'">'+text+'</span>'); // TODO tag CHAT

		text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/gi, (whole, historyId, text) => text); // TODO tag PCH

		let textElementSize = 0;
		if (enableBigSmile)
		{
			textElementSize = text.replace(/\[icon\=([^\]]*)\]/gi, '').trim().length;
		}

		text = text.replace(/\[icon\=([^\]]*)\]/gi, (whole) =>
		{
			let url = whole.match(/icon\=(\S+[^\s.,> )\];\'\"!?])/i);
			if (url && url[1])
			{
				url = url[1];
			}
			else
			{
				return '';
			}

			let attrs = {'src': url, 'border': 0};

			let size = whole.match(/size\=(\d+)/i);
			if (size && size[1])
			{
				attrs['width'] = size[1];
				attrs['height'] = size[1];
			}
			else
			{
				let width = whole.match(/width\=(\d+)/i);
				if (width && width[1])
				{
					attrs['width'] = width[1];
				}

				let height = whole.match(/height\=(\d+)/i);
				if (height && height[1])
				{
					attrs['height'] = height[1];
				}

				if (attrs['width'] && !attrs['height'])
				{
					attrs['height'] = attrs['width'];
				}
				else if (attrs['height'] && !attrs['width'])
				{
					attrs['width'] = attrs['height'];
				}
				else if (attrs['height'] && attrs['width'])
				{}
				else
				{
					attrs['width'] = 20;
					attrs['height'] = 20;
				}
			}

			attrs['width'] = attrs['width']>100? 100: attrs['width'];
			attrs['height'] = attrs['height']>100? 100: attrs['height'];

			if (enableBigSmile && textElementSize === 0 && attrs['width'] === attrs['height'] && attrs['width'] === 20)
			{
				attrs['width'] = 40;
				attrs['height'] = 40;
			}

			let title = whole.match(/title\=(.*[^\s\]])/i);
			if (title && title[1])
			{
				title = title[1];
				if (title.indexOf('width=') > -1)
				{
					title = title.substr(0, title.indexOf('width='))
				}
				if (title.indexOf('height=') > -1)
				{
					title = title.substr(0, title.indexOf('height='))
				}
				if (title.indexOf('size=') > -1)
				{
					title = title.substr(0, title.indexOf('size='))
				}
				if (title)
				{
					attrs['title'] = Utils.text.htmlspecialchars(title).trim();
					attrs['alt'] = attrs['title'];
				}
			}

			let attributes = '';
			for (let name in attrs)
			{
				if (attrs.hasOwnProperty(name))
				{
					attributes += name+'="'+attrs[name]+'" ';
				}
			}


			return '<img class="bx-smile bx-icon" '+attributes+'>';
		});

		sendReplacement.forEach((value, index) => {
			text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
		});

		text = text.replace(/\[SEND(?:=(?:.+?))?\](?:.+?)?\[\/SEND]/gi, (match) =>
		{
			return match.replace(/\[SEND(?:=(.+))?\](.+?)?\[\/SEND]/gi, (whole, command, text) =>
			{
				let html = '';

				text = text? text: command;
				command = (command? command: text).replace('<br />', '\n');

				if (!textOnly && text)
				{
					text = text.replace(/<([\w]+)[^>]*>(.*?)<\\1>/i, "$2", text);
					text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

					command = command.split('####REPLACEMENT_PUT_').join('####REPLACEMENT_SP_');

					html = '<!--IM_COMMAND_START-->' +
						'<span class="bx-im-message-command-wrap">'+
							'<span class="bx-im-message-command" data-entity="send">'+text+'</span>'+
							'<span class="bx-im-message-command-data">'+command+'</span>'+
						'</span>'+
					'<!--IM_COMMAND_END-->';
				}
				else
				{
					html = text;
				}

				return html;
			});
		});

		putReplacement.forEach((value, index) => {
			text = text.replace('####REPLACEMENT_PUT_'+index+'####', value);
		});

		text = text.replace(/\[PUT(?:=(?:.+?))?\](?:.+?)?\[\/PUT]/gi, (match) =>
		{
			return match.replace(/\[PUT(?:=(.+))?\](.+?)?\[\/PUT]/gi, (whole, command, text) =>
			{
				let html = '';

				text = text? text: command;
				command = (command? command: text).replace('<br />', '\n');

				if (!textOnly && text)
				{
					text = text.replace(/<([\w]+)[^>]*>(.*?)<\/\1>/i, "$2", text);
					text = text.replace(/\[([\w]+)[^\]]*\](.*?)\[\/\1\]/i, "$2", text);

					html = '<!--IM_COMMAND_START-->' +
						'<span class="bx-im-message-command-wrap">'+
							'<span class="bx-im-message-command" data-entity="put">'+text+'</span>'+
							'<span class="bx-im-message-command-data">'+command+'</span>'+
						'</span>'+
					'<!--IM_COMMAND_END-->';
				}
				else
				{
					html = text;
				}

				return html;
			});
		});

		codeReplacement.forEach((code, index) => {
			text = text.replace('####REPLACEMENT_CODE_'+index+'####',
				!textOnly? '<div class="bx-im-message-content-code">'+code+'</div>': code
			)
		});

		if (sendReplacement.length > 0)
		{
			do
			{
				sendReplacement.forEach((value, index) => {
					text = text.replace('####REPLACEMENT_SEND_'+index+'####', value);
				});
			}
			while (text.includes('####REPLACEMENT_SEND_'));
		}

		text = text.split('####REPLACEMENT_SP_').join('####REPLACEMENT_PUT_');

		if (putReplacement.length > 0)
		{
			do
			{
				putReplacement.forEach((value, index) => {
					text = text.replace('####REPLACEMENT_PUT_'+index+'####', value);
				});
			}
			while (text.includes('####REPLACEMENT_PUT_'));
		}

		return text;
	}

	static hideErrorImage(element)
	{
		if (element.parentNode && element.parentNode)
		{
			element.parentNode.innerHTML = '<a href="'+element.src+'" target="_blank">'+element.src+'</a>';
		}
		return true;
	};

	static isTemporaryMessage(element)
	{
		return element.id
			&& (Utils.types.isUuidV4(element.id) || element.id.toString().startsWith('temporary'));
	}

	static getPayloadWithTempMessages(state, payload)
	{
		const payloadData = [...payload.data];

		if (!Utils.platform.isBitrixMobile())
		{
			return payloadData;
		}

		if (!payload.data || payload.data.length <= 0)
		{
			return payloadData;
		}

		// consider that in the payload we have messages only for one chat, so we get the value from the first message.
		const payloadChatId = payload.data[0].chatId;
		if (!state.collection[payloadChatId])
		{
			return payloadData;
		}

		state.collection[payloadChatId].forEach(message => {
			if (
				MessagesModel.isTemporaryMessage(message)
				&& !MessagesModel.existsInPayload(payload, message.templateId)
				&& MessagesModel.doesTaskExist(message)
			)
			{
				payloadData.push(message);
			}
		});

		return payloadData;
	}

	static existsInPayload(payload, templateId)
	{
		return payload.data.find(payloadMessage => payloadMessage.templateId === templateId);
	}

	static doesTaskExist(message)
	{
		if (Array.isArray(message.params.FILE_ID))
		{
			let foundUploadTasks = false;
			message.params.FILE_ID.forEach(fileId => {
				if (!foundUploadTasks)
				{
					foundUploadTasks = window.imDialogUploadTasks.find(task => task.taskId.split('|')[1] === fileId);
				}
			})

			return !!foundUploadTasks;
		}

		if (message.templateId)
		{
			const foundMessageTask = window.imDialogMessagesTasks.find(task => task.taskId.split('|')[1] === message.templateId);

			return !!foundMessageTask;
		}

		return false;
	}
}