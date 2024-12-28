import { Type, type JsonObject } from 'main.core';
import { BuilderModel } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { ChatType, FakeDraftMessagePrefix, Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { ChannelManager } from 'im.v2.lib.channel';
import { formatFieldsWithConfig, convertObjectKeysToCamelCase } from 'im.v2.model';

import { recentFieldsConfig } from './format/field-config';
import { CallsModel } from './nested-modules/calls';

import type { GetterTree, ActionTree, MutationTree } from 'ui.vue3.vuex';
import type { ImModelMessage, ImModelChat } from 'im.v2.model';

import type { RecentItem as ImModelRecentItem } from '../type/recent-item';

type RecentState = {
	collection: {[dialogId: string]: ImModelRecentItem},
	recentCollection: Set<string>,
	unreadCollection: Set<string>,
	copilotCollection: Set<string>,
	channelCollection: Set<string>,
	collabCollection: Set<string>,
};

type SetDraftPayload = {
	id: string | number,
	text: string,
	collectionName: string,
	addMethodName: string
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
		};
	}

	getState(): RecentState
	{
		return {
			collection: {},
			recentCollection: new Set(),
			unreadCollection: new Set(),
			copilotCollection: new Set(),
			channelCollection: new Set(),
			collabCollection: new Set(),
		};
	}

	getElementState(): ImModelRecentItem
	{
		return {
			dialogId: '0',
			messageId: 0,
			draft: {
				text: '',
				date: null,
			},
			unread: false,
			pinned: false,
			liked: false,
			invitation: {
				isActive: false,
				originator: 0,
				canResend: false,
			},
			isFakeElement: false,
			isBirthdayPlaceholder: false,
			lastActivityDate: null,
		};
	}

	// eslint-disable-next-line max-lines-per-function
	getGetters(): GetterTree
	{
		return {
			/** @function recent/getRecentCollection */
			getRecentCollection: (state: RecentState): ImModelRecentItem[] => {
				return [...state.recentCollection].filter((dialogId) => {
					const dialog = this.store.getters['chats/get'](dialogId);

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
			/** @function recent/getCopilotCollection */
			getCopilotCollection: (state: RecentState): ImModelRecentItem[] => {
				return [...state.copilotCollection].filter((dialogId) => {
					const dialog = this.store.getters['chats/get'](dialogId);

					return Boolean(dialog);
				}).map((id) => {
					return state.collection[id];
				});
			},
			/** @function recent/getChannelCollection */
			getChannelCollection: (state: RecentState): ImModelRecentItem[] => {
				return [...state.channelCollection].filter((dialogId) => {
					const dialog = this.store.getters['chats/get'](dialogId);

					return Boolean(dialog);
				}).map((id) => {
					return state.collection[id];
				});
			},
			/** @function recent/getCollabCollection */
			getCollabCollection: (state: RecentState): ImModelRecentItem[] => {
				return [...state.collabCollection].filter((dialogId) => {
					const dialog = this.store.getters['chats/get'](dialogId);

					return Boolean(dialog);
				}).map((id) => {
					return state.collection[id];
				});
			},
			/** @function recent/getSortedCollection */
			getSortedCollection: (state: RecentState): ImModelRecentItem[] => {
				const recentCollectionAsArray = [...state.recentCollection].map((dialogId) => {
					return state.collection[dialogId];
				});

				const filteredCollection = recentCollectionAsArray.filter((item) => {
					return !item.isBirthdayPlaceholder && !item.isFakeElement && item.messageId;
				});

				return [...filteredCollection].sort((a, b) => {
					const messageA: ImModelMessage = this.#getMessage(a.messageId);
					const messageB: ImModelMessage = this.#getMessage(b.messageId);

					return messageB.date - messageA.date;
				});
			},
			/** @function recent/get */
			get: (state: RecentState) => (dialogId: string): ImModelRecentItem | null => {
				if (!state.collection[dialogId])
				{
					return null;
				}

				return state.collection[dialogId];
			},
			/** @function recent/getMessage */
			getMessage: (state: RecentState) => (dialogId: string): ImModelMessage | null => {
				const element = state.collection[dialogId];
				if (!element)
				{
					return null;
				}

				return this.#getMessage(element.messageId);
			},
			/** @function recent/needsBirthdayPlaceholder */
			needsBirthdayPlaceholder: (state: RecentState) => (dialogId): boolean => {
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return false;
				}

				const dialog = this.store.getters['chats/get'](dialogId);
				if (!dialog || dialog.type !== ChatType.user)
				{
					return false;
				}
				const hasBirthday = this.store.getters['users/hasBirthday'](dialogId);
				if (!hasBirthday)
				{
					return false;
				}

				const showBirthday = this.store.getters['application/settings/get'](Settings.recent.showBirthday);
				const hasTodayMessage = this.#hasTodayMessage(currentItem.messageId);

				return showBirthday && !hasTodayMessage && dialog.counter === 0;
			},
			/** @function recent/needsVacationPlaceholder */
			needsVacationPlaceholder: (state: RecentState) => (dialogId): boolean => {
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return false;
				}

				const dialog = this.store.getters['chats/get'](dialogId);
				if (!dialog || dialog.type !== ChatType.user)
				{
					return false;
				}
				const hasVacation = this.store.getters['users/hasVacation'](dialogId);
				if (!hasVacation)
				{
					return false;
				}

				const hasTodayMessage = this.#hasTodayMessage(currentItem.messageId);

				return !hasTodayMessage && dialog.counter === 0;
			},
			/** @function recent/getSortDate */
			getSortDate: (state: RecentState) => (dialogId): Date | null => {
				const currentItem = state.collection[dialogId];
				if (!currentItem)
				{
					return null;
				}

				const message: ImModelMessage = this.#getMessage(currentItem.messageId);

				if (Type.isDate(currentItem.draft.date) && currentItem.draft.date > message.date)
				{
					return currentItem.draft.date;
				}

				const needsBirthdayPlaceholder = this.store.getters['recent/needsBirthdayPlaceholder'](currentItem.dialogId);
				if (needsBirthdayPlaceholder)
				{
					return Utils.date.getStartOfTheDay();
				}

				const lastActivity = currentItem.lastActivityDate;
				const needToUseActivityDate = Type.isDate(lastActivity) && lastActivity > message.date;
				if (ChannelManager.isChannel(currentItem.dialogId) && needToUseActivityDate)
				{
					return lastActivity;
				}

				return message.date;
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
				const itemIds = await Core.getStore().dispatch('recent/store', payload);

				store.commit('setRecentCollection', itemIds);

				this.#updateUnloadedRecentCounters(payload);
			},
			/** @function recent/setUnread */
			setUnread: async (store, payload: Array | Object) => {
				const itemIds = await this.store.dispatch('recent/store', payload);
				store.commit('setUnreadCollection', itemIds);
			},
			/** @function recent/setCopilot */
			setCopilot: async (store, payload: Array | Object) => {
				const itemIds = await this.store.dispatch('recent/store', payload);
				store.commit('setCopilotCollection', itemIds);

				this.#updateUnloadedCopilotCounters(payload);
			},
			/** @function recent/setChannel */
			setChannel: async (store, payload: Array | Object) => {
				const itemIds = await this.store.dispatch('recent/store', payload);
				store.commit('setChannelCollection', itemIds);
			},
			/** @function recent/setCollab */
			setCollab: async (store, payload: Array | Object) => {
				const itemIds = await this.store.dispatch('recent/store', payload);
				store.commit('setCollabCollection', itemIds);

				this.#updateUnloadedCollabCounters(payload);
			},
			/** @function recent/clearChannelCollection */
			clearChannelCollection: (store) => {
				store.commit('clearChannelCollection');
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
					return this.#formatFields(element);
				}).forEach((element) => {
					const preparedElement = { ...element };
					const existingItem = store.state.collection[element.dialogId];
					if (existingItem)
					{
						itemsToUpdate.push({ dialogId: existingItem.dialogId, fields: preparedElement });
					}
					else
					{
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
			update: (store, payload: { id: string | number, fields: Object }) => {
				const { id, fields } = payload;
				const existingItem: ImModelRecentItem = store.state.collection[id];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: this.#formatFields(fields),
				});
			},
			/** @function recent/unread */
			unread: (store, payload: { id: string | number, action: boolean }) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: { unread: payload.action },
				});
			},
			/** @function recent/pin */
			pin: (store, payload: { id: string | number, action: boolean }) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				store.commit('update', {
					dialogId: existingItem.dialogId,
					fields: { pinned: payload.action },
				});
			},
			/** @function recent/like */
			like: (store, payload: { id: string | number, messageId: number, liked: boolean }) => {
				const existingItem: ImModelRecentItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				const isLastMessage = existingItem.messageId === Number.parseInt(payload.messageId, 10);
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
			/** @function recent/setRecentDraft */
			setRecentDraft: (store, payload: { id: string | number, text: string }) => {
				Core.getStore().dispatch('recent/setDraft', {
					id: payload.id,
					text: payload.text,
					collectionName: 'recentCollection',
					addMethodName: 'setRecentCollection',
				});
			},
			/** @function recent/setCopilotDraft */
			setCopilotDraft: (store, payload: { id: string | number, text: string }) => {
				Core.getStore().dispatch('recent/setDraft', {
					id: payload.id,
					text: payload.text,
					collectionName: 'copilotCollection',
					addMethodName: 'setCopilotCollection',
				});
			},
			/** @function recent/setDraft */
			setDraft: (store, payload: SetDraftPayload) => {
				const isRemovingDraft = !Type.isStringFilled(payload.text);
				if (isRemovingDraft && this.#shouldDeleteItemWithDraft(payload))
				{
					void Core.getStore().dispatch('recent/delete', { id: payload.id });

					return;
				}

				let existingItem = store.state.collection[payload.id];
				if (!existingItem && !isRemovingDraft)
				{
					store.commit('add', { ...this.getElementState(), ...this.#prepareFakeItemWithDraft(payload) });
					existingItem = store.state.collection[payload.id];
				}

				const existingCollectionItem = store.state[payload.collectionName].has(payload.id);
				if (!existingCollectionItem)
				{
					if (isRemovingDraft)
					{
						return;
					}
					store.commit(payload.addMethodName, [payload.id.toString()]);
				}

				const fields = this.#formatFields({ draft: { text: payload.text.toString() } });
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
			delete: (store, payload: { id: string | number }) => {
				const existingItem = store.state.collection[payload.id];
				if (!existingItem)
				{
					return;
				}

				store.commit('deleteFromRecentCollection', existingItem.dialogId);
				store.commit('deleteFromCopilotCollection', existingItem.dialogId);
				store.commit('deleteFromChannelCollection', existingItem.dialogId);
				store.commit('deleteFromCollabCollection', existingItem.dialogId);
				const canDelete = this.#canDelete(existingItem.dialogId);

				if (!canDelete)
				{
					return;
				}

				store.commit('delete', {
					id: existingItem.dialogId,
				});
			},
			/** @function recent/clearUnread */
			clearUnread: (store) => {
				store.commit('clearUnread');
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
			setCopilotCollection: (state: RecentState, payload: string[]) => {
				payload.forEach((dialogId) => {
					state.copilotCollection.add(dialogId);
				});
			},
			deleteFromCopilotCollection: (state: RecentState, payload: string) => {
				state.copilotCollection.delete(payload);
			},
			deleteFromChannelCollection: (state: RecentState, payload: string) => {
				state.channelCollection.delete(payload);
			},
			setChannelCollection: (state: RecentState, payload: string[]) => {
				payload.forEach((dialogId) => {
					state.channelCollection.add(dialogId);
				});
			},
			clearChannelCollection: (state: RecentState) => {
				state.channelCollection = new Set();
			},
			setCollabCollection: (state: RecentState, payload: string[]) => {
				payload.forEach((dialogId) => {
					state.collabCollection.add(dialogId);
				});
			},
			deleteFromCollabCollection: (state: RecentState, payload: string) => {
				state.collabCollection.delete(payload);
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
					// if we already got chat - we should not update it with fake user chat
					// (unless it's an accepted invitation or fake user with real message)
					const elementIsInRecent = state.recentCollection.has(dialogId);
					const isFakeElement = fields.isFakeElement && Utils.text.isTempMessage(fields.messageId);
					if (elementIsInRecent && isFakeElement && !fields.invitation)
					{
						return;
					}

					const currentElement = state.collection[dialogId];
					state.collection[dialogId] = { ...currentElement, ...fields };
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
		};
	}

	#formatFields(rawFields: JsonObject): Partial<ImModelRecentItem>
	{
		const options = Type.isPlainObject(rawFields.options) ? rawFields.options : {};
		const fields = { ...rawFields, ...options };

		return formatFieldsWithConfig(fields, recentFieldsConfig);
	}

	#updateUnloadedRecentCounters(payload: Array | Object)
	{
		this.#updateUnloadedCounters(payload, 'counters/setUnloadedChatCounters');
	}

	#updateUnloadedCopilotCounters(payload: Array | Object)
	{
		this.#updateUnloadedCounters(payload, 'counters/setUnloadedCopilotCounters');
	}

	#updateUnloadedCollabCounters(payload: Array | Object)
	{
		this.#updateUnloadedCounters(payload, 'counters/setUnloadedCollabCounters');
	}

	#updateUnloadedCounters(payload: Array | Object, updateMethod: string)
	{
		if (!Array.isArray(payload) && Type.isPlainObject(payload))
		{
			payload = [payload];
		}
		const zeroedCountersForNewItems = {};
		const preparedItems = payload.map((item) => convertObjectKeysToCamelCase(item));

		preparedItems.forEach((item) => {
			zeroedCountersForNewItems[item.chatId] = 0;
		});
		void Core.getStore().dispatch(updateMethod, zeroedCountersForNewItems);
	}

	#getMessage(messageId: number | string): ImModelMessage
	{
		return Core.getStore().getters['messages/getById'](messageId);
	}

	#getDialog(dialogId: string): ImModelChat
	{
		return Core.getStore().getters['chats/get'](dialogId);
	}

	#hasTodayMessage(messageId: number | string): boolean
	{
		const message: ImModelMessage = this.#getMessage(messageId);
		const hasMessage = Utils.text.isUuidV4(message.id) || message.id > 0;

		return hasMessage && Utils.date.isToday(message.date);
	}

	#canDelete(dialogId: string): boolean
	{
		const NOT_DELETABLE_TYPES = [ChatType.openChannel];
		const { type } = this.#getDialog(dialogId);

		return !NOT_DELETABLE_TYPES.includes(type);
	}

	#prepareFakeItemWithDraft(payload: SetDraftPayload): Partial<ImModelRecentItem>
	{
		const messageId = this.#createFakeMessageForDraft(payload.id);

		return this.#formatFields({
			dialogId: payload.id.toString(),
			draft: {
				text: payload.text.toString(),
			},
			messageId,
		});
	}

	#createFakeMessageForDraft(dialogId: string): string
	{
		const messageId = `${FakeDraftMessagePrefix}-${dialogId}`;
		void Core.getStore().dispatch('messages/store', { id: messageId, date: new Date() });

		return messageId;
	}

	#shouldDeleteItemWithDraft(payload: SetDraftPayload): boolean
	{
		const existingItem = Core.getStore().state.recent.collection[payload.id];

		return existingItem
			&& !Type.isStringFilled(payload.text)
			&& existingItem.messageId.toString().startsWith(FakeDraftMessagePrefix)
		;
	}
}
