import {Type} from 'main.core';
import {BuilderModel} from 'ui.vue3.vuex';

import {Core} from 'im.old-chat-embedding.application.core';
import {DialogType, MessageStatus, RecentCallStatus, RecentSettings} from 'im.old-chat-embedding.const';
import {Utils} from 'im.old-chat-embedding.lib.utils';

import type {RecentItem as ImModelRecentItem} from './type/recent-item';
import type {Dialog as ImModelDialog} from './type/dialog';

export class RecentModel extends BuilderModel
{
	getName()
	{
		return 'recent';
	}

	getState()
	{
		return {
			collection: {},
			recentCollection: new Set(),
			unreadCollection: new Set(),
			unloadedChatCounters: {},
			activeCalls: [],
			options: {
				showBirthday: true,
				showInvited: true,
				showLastMessage: true
			}
		};
	}

	getElementState()
	{
		return {
			dialogId: '0',
			message: {
				id: 0,
				senderId: 0,
				date: new Date(),
				status: MessageStatus.received,
				sending: false,
				text: '',
				params: {
					withFile: false,
					withAttach: false,
				}
			},
			draft: {
				text: '',
				date: null
			},
			unread: false,
			pinned: false,
			liked: false,
			invitation: {
				isActive: false,
				originator: 0,
				canResend: false
			},
			options: {}
		};
	}

	getActiveCallDefaultState()
	{
		return {
			dialogId: 0,
			name: '',
			call: {},
			state: RecentCallStatus.waiting
		};
	}

	getGetters()
	{
		return {
			getRecentCollection: (state: RecentState): ImModelRecentItem[] =>
			{
				return [...state.recentCollection].map(id => {
					return state.collection[id];
				});
			},
			getUnreadCollection: (state: RecentState): ImModelRecentItem[] =>
			{
				return [...state.unreadCollection].map(id => {
					return state.collection[id];
				});
			},
			getSortedCollection: (state: RecentState): ImModelRecentItem[] =>
			{
				const collectionAsArray = Object.values(state.collection).filter(item => {
					const isBirthdayPlaceholder = item.options.birthdayPlaceholder;
					const isInvitedUser = item.options.defaultUserRecord;

					return !isBirthdayPlaceholder && !isInvitedUser && item.message.id;
				});

				return [...collectionAsArray].sort((a, b) => {
					return b.message.date - a.message.date;
				});
			},
			get: (state: RecentState) => (dialogId: string): ImModelRecentItem | null =>
			{
				if (Type.isNumber(dialogId))
				{
					dialogId = dialogId.toString();
				}

				if (state.collection[dialogId])
				{
					return state.collection[dialogId];
				}

				return null;
			},

			needsBirthdayPlaceholder: (state: RecentState) => (dialogId): boolean =>
			{
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return false;
				}

				const dialog = this.store.getters['dialogues/get'](dialogId);
				if (!dialog || dialog.type !== DialogType.user)
				{
					return false;
				}
				const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);
				if (!hasBirthday)
				{
					return false;
				}

				const hasMessage = Utils.text.isTempMessage(currentItem.message.id) || currentItem.message.id > 0;
				const hasTodayMessage = hasMessage && Utils.date.isToday(currentItem.message.date);

				return state.options.showBirthday && !hasTodayMessage && dialog.counter === 0;
			},

			needsVacationPlaceholder: (state: RecentState) => (dialogId): boolean =>
			{
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return false;
				}

				const dialog = this.store.getters['dialogues/get'](dialogId);
				if (!dialog || dialog.type !== DialogType.user)
				{
					return false;
				}
				const hasVacation = this.store.getters['users/hasVacation'](dialogId);
				if (!hasVacation)
				{
					return false;
				}

				const hasMessage = Utils.text.isTempMessage(currentItem.message.id) || currentItem.message.id > 0;
				const hasTodayMessage = hasMessage && Utils.date.isToday(currentItem.message.date);

				return !hasTodayMessage && dialog.counter === 0;
			},

			getMessageDate: (state: RecentState) => (dialogId): Date | null =>
			{
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return null;
				}

				if (Type.isDate(currentItem.draft.date) && currentItem.draft.date > currentItem.message.date)
				{
					return currentItem.draft.date;
				}

				const needsBirthdayPlaceholder = this.store.getters['recent/needsBirthdayPlaceholder'](currentItem.dialogId);
				if (needsBirthdayPlaceholder)
				{
					return Utils.date.getStartOfTheDay();
				}

				return currentItem.message.date;
			},

			hasActiveCall: (state: RecentState): boolean =>
			{
				return state.activeCalls.some(item => item.state === RecentCallStatus.joined);
			},

			getOption: (state: RecentState) => (optionName: string): boolean =>
			{
				if (!RecentSettings[optionName])
				{
					return false;
				}

				return state.options[optionName];
			},

			getTotalCounter: (state: RecentState): number =>
			{
				let loadedChatsCounter = 0;
				[...state.recentCollection].forEach(dialogId => {
					const dialog: ImModelDialog = this.store.getters['dialogues/get'](dialogId, true);
					const recentItem: ImModelRecentItem = state.collection[dialogId];

					const isMuted = dialog.muteList.includes(Core.getUserId());
					if (isMuted)
					{
						return;
					}
					const isMarked = recentItem.unread;
					if (dialog.counter === 0 && isMarked)
					{
						loadedChatsCounter++;
						return;
					}
					loadedChatsCounter += dialog.counter;
				});

				let unloadedChatsCounter = 0;
				Object.values(state.unloadedChatCounters).forEach(counter => {
					unloadedChatsCounter += counter;
				});

				return loadedChatsCounter + unloadedChatsCounter;
			}
		};
	}

	getActions()
	{
		return {
			setRecent: (store, payload: Array | Object) =>
			{
				this.store.dispatch('recent/set', payload).then(itemIds => {
					store.commit('setRecentCollection', itemIds);
				});

				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				const zeroedCountersForNewItems = {};
				payload.forEach(item => {
					zeroedCountersForNewItems[item.chat_id] = 0;
				});
				this.store.dispatch('recent/setUnloadedChatCounters', zeroedCountersForNewItems);
			},
			setUnread: (store, payload: Array | Object) =>
			{
				this.store.dispatch('recent/set', payload).then(itemIds => {
					store.commit('setUnreadCollection', itemIds);
				});
			},
			set: (store, payload: Array | Object) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				const itemsToUpdate = [];
				const itemsToAdd = [];
				payload.map(element => {
					return this.validate(element);
				}).forEach(element => {
					const existingItem = store.state.collection[element.dialogId];
					if (existingItem)
					{
						itemsToUpdate.push({dialogId: existingItem.dialogId, fields: {...element}});
					}
					else
					{
						itemsToAdd.push({...this.getElementState(), ...element});
					}
				});

				if (itemsToAdd.length > 0)
				{
					store.commit('add', itemsToAdd);
				}
				if (itemsToUpdate.length > 0)
				{
					store.commit('update', itemsToUpdate);
				}

				return [...itemsToAdd, ...itemsToUpdate].map(item => item.dialogId);
			},

			update: (store, payload: {id: string | number, fields: Object}) =>
			{
				const {id, fields} = payload;
				const existingItem: ImModelRecentItem = store.state.collection[id];
				if (!existingItem)
				{
					return false;
				}

				if (fields.message)
				{
					fields.message = {...existingItem.message, ...fields.message};
				}
				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: this.validate(fields)
				});
			},

			unread: (store, payload: {id: string | number, action: boolean}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: {unread: payload.action}
				});
			},

			pin: (store, payload: {id: string | number, action: boolean}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: {pinned: payload.action}
				});
			},

			like: (store, payload: {id: string | number, messageId: number, liked: boolean}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				const isLastMessage = existingItem.message.id === Number.parseInt(payload.messageId, 10);
				const isExactMessageLiked = !Type.isUndefined(payload.messageId) && payload.liked === true;
				if (isExactMessageLiked && !isLastMessage)
				{
					return false;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: {liked: payload.liked === true}
				});
			},

			draft: (store, payload: {id: string | number, text: string}) =>
			{
				const dialog = this.store.getters['dialogues/get'](payload.id);
				if (!dialog)
				{
					return false;
				}

				let existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					if (payload.text === '')
					{
						return false;
					}
					const newItem = {
						dialogId: payload.id.toString(),
					};
					store.commit('add', {...this.getElementState(), ...newItem});
					store.commit('setRecentCollection', [newItem.dialogId]);
					existingItem = store.state.collection[payload.id];
				}

				const fields = this.validate({draft: {text: payload.text.toString()}});
				if (fields.draft.text === existingItem.draft.text)
				{
					return false;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields
				});
			},

			delete: (store, payload: {id: string | number}) =>
			{
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return false;
				}

				store.commit('delete', {
					id: existingItem.dialogId
				});
				store.commit('deleteFromRecentCollection', existingItem.dialogId);
			},

			addActiveCall: (store, payload) =>
			{
				const existingIndex = store.state.activeCalls.findIndex(item => {
					return item.dialogId === payload.dialogId || item.call.id === payload.call.id;
				});

				if (existingIndex > -1)
				{
					store.commit('updateActiveCall', {
						index: existingIndex,
						fields: this.validateActiveCall(payload)
					});

					return true;
				}

				store.commit('addActiveCall', this.prepareActiveCall(payload));
			},

			updateActiveCall: (store, payload) =>
			{
				const existingIndex = store.state.activeCalls.findIndex(item => {
					return item.dialogId === payload.dialogId;
				});

				store.commit('updateActiveCall', {
					index: existingIndex,
					fields: this.validateActiveCall(payload.fields)
				});
			},

			deleteActiveCall: (store, payload) =>
			{
				const existingIndex = store.state.activeCalls.findIndex(item => {
					return item.dialogId === payload.dialogId;
				});

				if (existingIndex === -1)
				{
					return false;
				}

				store.commit('deleteActiveCall', {
					index: existingIndex
				});
			},

			setOptions: (store, payload) =>
			{
				if (!Type.isPlainObject(payload))
				{
					return false;
				}

				payload = this.validateOptions(payload);
				Object.entries(payload).forEach(([option, value]) => {
					store.commit('setOptions', {
						option,
						value
					});
				});
			},

			clearUnread: (store) =>
			{
				store.commit('clearUnread');
			},

			setUnloadedChatCounters: (store, payload: {[chatId: string]: number}) =>
			{
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedChatCounters', payload);
			}
		};
	}

	getMutations()
	{
		return {
			setRecentCollection: (state: RecentState, payload: string[]) => {
				payload.forEach(dialogId => {
					state.recentCollection.add(dialogId);
				});
			},
			deleteFromRecentCollection: (state: RecentState, payload: string) => {
				state.recentCollection.delete(payload);
			},
			setUnreadCollection: (state: RecentState, payload: string[]) => {
				payload.forEach(dialogId => {
					state.unreadCollection.add(dialogId);
				});
			},
			add: (state: RecentState, payload: Object[] | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				payload.forEach(item => {
					state.collection[item.dialogId] = item;
				});
			},

			update: (state: RecentState, payload: Object[] | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				payload.forEach(({dialogId, fields}) => {
					// if we already got chat - we should not update it with default user chat (unless it's an accepted invitation)
					const defaultUserElement = fields.options && fields.options.defaultUserRecord && !fields.invitation;
					if (defaultUserElement)
					{
						return false;
					}

					const currentElement = state.collection[dialogId];
					fields.message = {...currentElement.message, ...fields.message};
					fields.options = {...currentElement.options, ...fields.options};
					state.collection[dialogId] = {
						...currentElement,
						...fields
					};
				});
			},

			delete: (state: RecentState, payload: {id: string}) => {
				delete state.collection[payload.id];
			},

			addActiveCall: (state: RecentState, payload) => {
				state.activeCalls.push(payload);
			},

			updateActiveCall: (state: RecentState, payload) => {
				state.activeCalls[payload.index] = {
					...state.activeCalls[payload.index],
					...payload.fields
				};
			},

			deleteActiveCall: (state: RecentState, payload) => {
				state.activeCalls.splice(payload.index, 1);
			},

			setOptions: (state: RecentState, payload) => {
				state.options[payload.option] = payload.value;
			},

			clearUnread: (state: RecentState) => {
				Object.keys(state.collection).forEach(key => {
					state.collection[key].unread = false;
				});
			},

			setUnloadedChatCounters: (state: RecentState, payload: {[chatId: string]: number}) => {
				Object.entries(payload).forEach(([chatId, counter]) => {
					if (counter === 0)
					{
						delete state.unloadedChatCounters[chatId];
						return;
					}
					state.unloadedChatCounters[chatId] = counter;
				});
			}
		};
	}

	validate(fields: Object)
	{
		const result = {
			options: {}
		};

		if (Type.isNumber(fields.id))
		{
			result.dialogId = fields.id.toString();
		}
		if (Type.isStringFilled(fields.id))
		{
			result.dialogId = fields.id;
		}

		if (Type.isNumber(fields.dialogId))
		{
			result.dialogId = fields.dialogId.toString();
		}
		if (Type.isStringFilled(fields.dialogId))
		{
			result.dialogId = fields.dialogId;
		}

		if (Type.isPlainObject(fields.message))
		{
			result.message = this.prepareMessage(fields);
		}

		if (Type.isPlainObject(fields.draft))
		{
			result.draft = this.prepareDraft(fields);
		}

		if (Type.isBoolean(fields.unread))
		{
			result.unread = fields.unread;
		}

		if (Type.isBoolean(fields.pinned))
		{
			result.pinned = fields.pinned;
		}

		if (Type.isBoolean(fields.liked))
		{
			result.liked = fields.liked;
		}

		if (Type.isPlainObject(fields.invited))
		{
			result.invitation = {
				isActive: true,
				originator: fields.invited.originator_id,
				canResend: fields.invited.can_resend
			};
			result.options.defaultUserRecord = true;
		}
		else if (fields.invited === false)
		{
			result.invitation = {
				isActive: false,
				originator: 0,
				canResend: false
			};
			result.options.defaultUserRecord = true;
		}

		if (Type.isPlainObject(fields.options))
		{
			if (!result.options)
			{
				result.options = {};
			}

			if (Type.isBoolean(fields.options.default_user_record))
			{
				fields.options.defaultUserRecord = fields.options.default_user_record;
			}

			if (Type.isBoolean(fields.options.defaultUserRecord))
			{
				result.options.defaultUserRecord = fields.options.defaultUserRecord;
			}

			if (Type.isBoolean(fields.options.birthdayPlaceholder))
			{
				result.options.birthdayPlaceholder = fields.options.birthdayPlaceholder;
			}
		}

		return result;
	}

	prepareMessage(fields: Object): Object
	{
		const {message} = this.getElementState();
		if (Type.isNumber(fields.message.id) || Utils.text.isUuidV4(fields.message.id) || Type.isStringFilled(fields.message.id))
		{
			message.id = fields.message.id;
		}
		if (Type.isString(fields.message.text))
		{
			message.text = fields.message.text;
		}

		if (
			Type.isStringFilled(fields.message.attach)
			|| Type.isBoolean(fields.message.attach)
			|| Type.isArray(fields.message.attach)
		)
		{
			message.params.withAttach = fields.message.attach;
		}
		else if (
			Type.isStringFilled(fields.message.params?.withAttach)
			|| Type.isBoolean(fields.message.params?.withAttach)
			|| Type.isArray(fields.message.params?.withAttach)
		)
		{
			message.params.withAttach = fields.message.params.withAttach;
		}

		if (Type.isBoolean(fields.message.file) || Type.isPlainObject(fields.message.file))
		{
			message.params.withFile = fields.message.file;
		}
		else if (Type.isBoolean(fields.message.params?.withFile) || Type.isPlainObject(fields.message.params?.withFile))
		{
			message.params.withFile = fields.message.params.withFile;
		}

		if (Type.isDate(fields.message.date) || Type.isString(fields.message.date))
		{
			message.date = Utils.date.cast(fields.message.date);
		}

		if (Type.isNumber(fields.message.author_id))
		{
			message.senderId = fields.message.author_id;
		}
		else if (Type.isNumber(fields.message.authorId))
		{
			message.senderId = fields.message.authorId;
		}
		else if (Type.isNumber(fields.message.senderId))
		{
			message.senderId = fields.message.senderId;
		}

		if (Type.isStringFilled(fields.message.status))
		{
			message.status = fields.message.status;
		}
		if (Type.isBoolean(fields.message.sending))
		{
			message.sending = fields.message.sending;
		}

		return message;
	}

	prepareDraft(fields: Object): Object
	{
		const {draft} = this.getElementState();

		if (Type.isString(fields.draft.text))
		{
			draft.text = fields.draft.text;
		}

		if (Type.isStringFilled(draft.text))
		{
			draft.date = new Date();
		}
		else
		{
			draft.date = null;
		}

		return draft;
	}

	prepareActiveCall(call)
	{
		return {...this.getActiveCallDefaultState(), ...this.validateActiveCall(call)};
	}

	validateActiveCall(fields)
	{
		const result = {};

		if (Type.isStringFilled(fields.dialogId) || Type.isNumber(fields.dialogId))
		{
			result.dialogId = fields.dialogId;
		}

		if (Type.isStringFilled(fields.name))
		{
			result.name = fields.name;
		}

		if (Type.isObjectLike(fields.call))
		{
			result.call = fields.call;

			if (fields.call?.associatedEntity?.avatar === '/bitrix/js/im/images/blank.gif')
			{
				result.call.associatedEntity.avatar = '';
			}
		}

		if (RecentCallStatus[fields.state])
		{
			result.state = fields.state;
		}

		return result;
	}

	validateOptions(fields)
	{
		const result = {};

		if (Type.isBoolean(fields.showBirthday))
		{
			result.showBirthday = fields.showBirthday;
		}

		if (Type.isBoolean(fields.showInvited))
		{
			result.showInvited = fields.showInvited;
		}

		if (Type.isBoolean(fields.showLastMessage))
		{
			result.showLastMessage = fields.showLastMessage;
		}

		return result;
	}
}

type RecentState = {
	collection: {[dialogId: string]: ImModelRecentItem},
	recentCollection: Set<string>,
	unreadCollection: Set<string>,
	markedCollection: Set<number>,
	unloadedChatCounters: {[chatId: string]: number},
	activeCalls: ActiveCall[],
	options: {
		showBirthday: boolean,
		showInvited: boolean,
		showLastMessage: boolean
	}
};

type ActiveCall = {
	dialogId: string,
	name: string,
	call: Object,
	state: $Values<typeof RecentCallStatus>
};