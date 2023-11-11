import { Type } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { DialogType, MessageStatus, Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import { CallsModel } from './recent/calls';
import { RecentSearchModel } from './recent/search';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';

import type { RecentItem as ImModelRecentItem } from './type/recent-item';
import type { Dialog as ImModelDialog } from './type/dialog';

type RecentState = {
	collection: {[dialogId: string]: ImModelRecentItem},
	recentCollection: Set<string>,
	unreadCollection: Set<string>,
	unloadedChatCounters: {[chatId: string]: number},
	unloadedLinesCounters: {[chatId: string]: number}
};

export class RecentModel extends BuilderModel
{
	getName(): string
	{
		return 'recent';
	}

	getNestedModules(): { [moduleName: string]: BuilderModel }
	{
		return {
			calls: CallsModel,
			search: RecentSearchModel,
		};
	}

	getState(): RecentState
	{
		return {
			collection: {},
			recentCollection: new Set(),
			unreadCollection: new Set(),
			unloadedChatCounters: {},
			unloadedLinesCounters: {},
		};
	}

	getElementState(): ImModelRecentItem
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
				},
			},
			draft: {
				text: '',
				date: null,
			},
			unread: false,
			pinned: false,
			liked: false,
			dateUpdate: null,
			invitation: {
				isActive: false,
				originator: 0,
				canResend: false,
			},
			options: {},
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function recent/getRecentCollection */
			getRecentCollection: (state: RecentState): ImModelRecentItem[] => {
				return [...state.recentCollection].filter((dialogId) => {
					const dialog = this.store.getters['dialogues/get'](dialogId);

					return Boolean(dialog);
				}).map((id) => {
					return state.collection[id];
				});
			},
			/** @function recent/getUnreadCollection */
			getUnreadCollection: (state: RecentState): ImModelRecentItem[] => {
				return [...state.unreadCollection].map((id) => {
					return state.collection[id];
				});
			},
			/** @function recent/getSortedCollection */
			getSortedCollection: (state: RecentState): ImModelRecentItem[] => {
				const collectionAsArray = Object.values(state.collection).filter((item) => {
					const isBirthdayPlaceholder = item.options.birthdayPlaceholder;
					const isInvitedUser = item.options.defaultUserRecord;

					return !isBirthdayPlaceholder && !isInvitedUser && item.message.id;
				});

				return [...collectionAsArray].sort((a, b) => {
					return b.message.date - a.message.date;
				});
			},
			/** @function recent/get */
			get: (state: RecentState) => (rawDialogId: string): ImModelRecentItem | null => {
				let dialogId = rawDialogId;
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
			/** @function recent/needsBirthdayPlaceholder */
			needsBirthdayPlaceholder: (state: RecentState) => (dialogId): boolean => {
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

				const showBirthday = this.store.getters['application/settings/get'](Settings.recent.showBirthday);

				return showBirthday && !hasTodayMessage && dialog.counter === 0;
			},
			/** @function recent/needsVacationPlaceholder */
			needsVacationPlaceholder: (state: RecentState) => (dialogId): boolean => {
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
			/** @function recent/getMessageDate */
			getMessageDate: (state: RecentState) => (dialogId): Date | null => {
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
			/** @function recent/getTotalChatCounter */
			getTotalChatCounter: (state: RecentState): number => {
				let loadedChatsCounter = 0;
				[...state.recentCollection].forEach((dialogId) => {
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
				Object.values(state.unloadedChatCounters).forEach((counter) => {
					unloadedChatsCounter += counter;
				});

				return loadedChatsCounter + unloadedChatsCounter;
			},
			/** @function recent/getTotalLinesCounter */
			getTotalLinesCounter: (state: RecentState): number => {
				let unloadedLinesCounter = 0;
				Object.values(state.unloadedLinesCounters).forEach((counter) => {
					unloadedLinesCounter += counter;
				});

				return unloadedLinesCounter;
			},
			/** @function recent/getSpecificLinesCounter */
			getSpecificLinesCounter: (state: RecentState) => (chatId: number): number => {
				if (!state.unloadedLinesCounters[chatId])
				{
					return 0;
				}

				return state.unloadedLinesCounters[chatId];
			},
		};
	}

	/* eslint-disable no-param-reassign */
	/* eslint-disable-next-line max-lines-per-function */
	getActions(): ActionTree
	{
		return {
			/** @function recent/setRecent */
			setRecent: async (store, payload: Array | Object) => {
				const itemIds = await this.store.dispatch('recent/store', payload);
				store.commit('setRecentCollection', itemIds);

				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				const zeroedCountersForNewItems = {};
				payload.forEach((item) => {
					zeroedCountersForNewItems[item.chat_id] = 0;
				});
				this.store.dispatch('recent/setUnloadedChatCounters', zeroedCountersForNewItems);
			},
			/** @function recent/setUnread */
			setUnread: async (store, payload: Array | Object) => {
				const itemIds = await this.store.dispatch('recent/store', payload);
				store.commit('setUnreadCollection', itemIds);
			},
			/** @function recent/store */
			store: (store, payload: Array | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				const itemsToUpdate = [];
				const itemsToAdd = [];
				payload.map((element) => {
					return this.validate(element);
				}).forEach((element) => {
					const preparedElement = { ...element };
					const existingItem = store.state.collection[element.dialogId];
					if (existingItem)
					{
						itemsToUpdate.push({ dialogId: existingItem.dialogId, fields: preparedElement });
					}
					else
					{
						const { message: defaultMessage } = this.getElementState();
						preparedElement.message = { ...defaultMessage, ...preparedElement.message };
						itemsToAdd.push({ ...this.getElementState(), ...preparedElement });
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

				return [...itemsToAdd, ...itemsToUpdate].map((item) => item.dialogId);
			},
			/** @function recent/update */
			update: (store, payload: {id: string | number, fields: Object}) => {
				const { id, fields } = payload;
				const existingItem: ImModelRecentItem = store.state.collection[id];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: this.validate(fields),
				});
			},
			/** @function recent/unread */
			unread: (store, payload: {id: string | number, action: boolean, dateUpdate: Date}) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: {
						unread: payload.action,
						dateUpdate: payload.dateUpdate,
					},
				});
			},
			/** @function recent/pin */
			pin: (store, payload: {id: string | number, action: boolean, dateUpdate: Date}) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: {
						pinned: payload.action,
						dateUpdate: payload.dateUpdate,
					},
				});
			},
			/** @function recent/like */
			like: (store, payload: {id: string | number, messageId: number, liked: boolean}) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				const isLastMessage = existingItem.message.id === Number.parseInt(payload.messageId, 10);
				const isExactMessageLiked = !Type.isUndefined(payload.messageId) && payload.liked === true;
				if (isExactMessageLiked && !isLastMessage)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: { liked: payload.liked === true },
				});
			},
			/** @function recent/draft */
			draft: (store, payload: {id: string | number, text: string}) => {
				let existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					if (payload.text === '')
					{
						return;
					}
					const newItem = {
						dialogId: payload.id.toString(),
					};
					store.commit('add', { ...this.getElementState(), ...newItem });
					existingItem = store.state.collection[payload.id];
				}

				const existingRecentCollectionItem = store.state.recentCollection.has(payload.id);
				if (!existingRecentCollectionItem)
				{
					if (payload.text === '')
					{
						return;
					}
					store.commit('setRecentCollection', [payload.id.toString()]);
				}

				const fields = this.validate({ draft: { text: payload.text.toString() } });
				if (fields.draft.text === existingItem.draft.text)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields,
				});
			},
			/** @function recent/delete */
			delete: (store, payload: {id: string | number}) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				store.commit('delete', {
					id: existingItem.dialogId,
				});
				store.commit('deleteFromRecentCollection', existingItem.dialogId);
			},
			/** @function recent/clearUnread */
			clearUnread: (store) => {
				store.commit('clearUnread');
			},
			/** @function recent/setUnloadedChatCounters */
			setUnloadedChatCounters: (store, payload: {[chatId: string]: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedChatCounters', payload);
			},
			/** @function recent/setUnloadedLinesCounters */
			setUnloadedLinesCounters: (store, payload: {[chatId: string]: number}) => {
				if (!Type.isPlainObject(payload))
				{
					return;
				}

				store.commit('setUnloadedLinesCounters', payload);
			},
		};
	}

	getMutations(): MutationTree
	{
		return {
			setRecentCollection: (state: RecentState, payload: string[]) => {
				payload.forEach((dialogId) => {
					state.recentCollection.add(dialogId);
				});
			},
			deleteFromRecentCollection: (state: RecentState, payload: string) => {
				state.recentCollection.delete(payload);
			},
			setUnreadCollection: (state: RecentState, payload: string[]) => {
				payload.forEach((dialogId) => {
					state.unreadCollection.add(dialogId);
				});
			},
			add: (state: RecentState, payload: Object[] | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				payload.forEach((item) => {
					state.collection[item.dialogId] = item;
				});
			},

			update: (state: RecentState, payload: Object[] | Object) => {
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}
				payload.forEach(({ dialogId, fields }) => {
					// if we already got chat - we should not update it with default user chat
					// (unless it's an accepted invitation)
					const elementIsInRecent = state.recentCollection.has(dialogId);
					const defaultUserElement = fields.options?.defaultUserRecord && !fields.invitation;
					if (defaultUserElement && elementIsInRecent)
					{
						return;
					}

					const currentElement = state.collection[dialogId];
					fields.message = { ...currentElement.message, ...fields.message };
					fields.options = { ...currentElement.options, ...fields.options };
					state.collection[dialogId] = {
						...currentElement,
						...fields,
					};
				});
			},

			delete: (state: RecentState, payload: {id: string}) => {
				delete state.collection[payload.id];
			},

			clearUnread: (state: RecentState) => {
				Object.keys(state.collection).forEach((key) => {
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
			},

			setUnloadedLinesCounters: (state: RecentState, payload: {[chatId: string]: number}) => {
				Object.entries(payload).forEach(([chatId, counter]) => {
					if (counter === 0)
					{
						delete state.unloadedLinesCounters[chatId];

						return;
					}
					state.unloadedLinesCounters[chatId] = counter;
				});
			},
		};
	}

	validate(fields: Object)
	{
		const result = {
			options: {},
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

		if (Type.isStringFilled(fields.date_update) || Type.isStringFilled(fields.dateUpdate))
		{
			const date = fields.date_update || fields.dateUpdate;
			result.dateUpdate = Utils.date.cast(date);
		}
		else if (Type.isDate(fields.dateUpdate))
		{
			result.dateUpdate = fields.dateUpdate;
		}

		if (Type.isPlainObject(fields.invited))
		{
			result.invitation = {
				isActive: true,
				originator: fields.invited.originator_id,
				canResend: fields.invited.can_resend,
			};
			result.options.defaultUserRecord = true;
		}
		else if (fields.invited === false)
		{
			result.invitation = {
				isActive: false,
				originator: 0,
				canResend: false,
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
		const message = {};
		const params = {};

		if (
			Type.isNumber(fields.message.id)
			|| Type.isStringFilled(fields.message.id)
			|| Utils.text.isUuidV4(fields.message.id)
		)
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
			params.withAttach = fields.message.attach;
		}
		else if (
			Type.isStringFilled(fields.message.params?.withAttach)
			|| Type.isBoolean(fields.message.params?.withAttach)
			|| Type.isArray(fields.message.params?.withAttach)
		)
		{
			params.withAttach = fields.message.params.withAttach;
		}

		if (Type.isBoolean(fields.message.file) || Type.isPlainObject(fields.message.file))
		{
			params.withFile = fields.message.file;
		}
		else if (Type.isBoolean(fields.message.params?.withFile) || Type.isPlainObject(fields.message.params?.withFile))
		{
			params.withFile = fields.message.params.withFile;
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

		if (Object.keys(params).length > 0)
		{
			message.params = params;
		}

		return message;
	}

	prepareDraft(fields: Object): Object
	{
		const { draft } = this.getElementState();

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
}
