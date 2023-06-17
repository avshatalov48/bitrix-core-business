import {BuilderModel} from 'ui.vue3.vuex';
import {Type, Text} from 'main.core';

import {Core} from 'im.v2.application.core';
import {Utils} from 'im.v2.lib.utils';
import {NotificationTypesCodes} from 'im.v2.const';

export class NotificationsModel extends BuilderModel
{
	getName(): string
	{
		return 'notifications';
	}

	getState(): Object
	{
		return {
			collection: new Map(),
			searchCollection: new Map(),
			unreadCounter: 0,
		};
	}

	getElementState(): Object
	{
		return {
			id: 0,
			authorId: 0,
			date: new Date(),
			title: '',
			text: '',
			params: {},
			replaces: [],
			notifyButtons: [],
			sectionCode: NotificationTypesCodes.simple,
			read: false,
			settingName: 'im|default',
		};
	}

	getGetters(): Object
	{
		return {
			getSortedCollection: state =>
			{
				return [...state.collection.values()].sort(this.sortByType);
			},
			getSearchResultCollection: state =>
			{
				return [...state.searchCollection.values()].sort(this.sortByType);
			},
			getConfirmsCount: state =>
			{
				return [...state.collection.values()].filter(notification => {
					return notification.sectionCode === NotificationTypesCodes.confirm;
				}).length;
			},
			getById: state => (notificationId) =>
			{
				if (Type.isString(notificationId))
				{
					notificationId = Number.parseInt(notificationId, 10);
				}

				const existingItem = state.collection.get(notificationId);
				if (!existingItem)
				{
					return false;
				}

				return existingItem;
			},
			getCounter: (state): number =>
			{
				return state.unreadCounter;
			}
		};
	}

	getActions(): Object
	{
		return {
			initialSet: (store, payload) =>
			{
				if (Type.isNumber(payload.total_unread_count))
				{
					store.commit('setCounter', payload.total_unread_count);
				}

				if (!Type.isArrayFilled(payload.notifications))
				{
					return;
				}

				const itemsToUpdate = [];
				const itemsToAdd = [];

				const currentUserId = Core.getUserId();
				payload.notifications.map(element => {
					return NotificationsModel.validate(element, currentUserId);
				}).forEach(element => {
					const existingItem = store.state.collection.get(element.id);
					if (existingItem)
					{
						itemsToUpdate.push({id: existingItem.id, fields: {...element}});
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
			},
			set: (store, payload) =>
			{
				if (!Array.isArray(payload) && Type.isPlainObject(payload))
				{
					payload = [payload];
				}

				const itemsToUpdate = [];
				const itemsToAdd = [];
				const currentUserId = Core.getUserId();
				payload.map(element => {
					return NotificationsModel.validate(element, currentUserId);
				}).forEach(element => {
					const existingItem = store.state.collection.get(element.id);
					if (existingItem)
						{
						itemsToUpdate.push({id: existingItem.id, fields: {...element}});
					}
					else
					{
						itemsToAdd.push({...this.getElementState(), ...element});
					}
				});

				if (itemsToAdd.length > 0)
				{
					store.commit('add', itemsToAdd);
					itemsToAdd.forEach(() => {
						store.commit('increaseCounter');
					});
				}
				if (itemsToUpdate.length > 0)
				{
					store.commit('update', itemsToUpdate);
				}
			},
			setSearchResult: (store, payload) =>
			{
				const itemsToUpdate = [];
				const itemsToAdd = [];

				let {notifications} = payload;

				const skipValidation = !!payload.skipValidation;
				if (!skipValidation)
				{
					const currentUserId = Core.getUserId();
					notifications = notifications.map(element => {
						return NotificationsModel.validate(element, currentUserId);
					});
				}

				notifications.forEach(element => {
					const existingItem = store.state.searchCollection.get(element.id);
					if (existingItem)
					{
						itemsToUpdate.push({id: existingItem.id, fields: {...element}});
					}
					else
					{
						itemsToAdd.push({...this.getElementState(), ...element});
					}
				});

				if (itemsToAdd.length > 0)
				{
					store.commit('addSearchResult', itemsToAdd);
				}
				if (itemsToUpdate.length > 0)
				{
					store.commit('updateSearchResult', itemsToUpdate);
				}
			},
			read: (store, payload) =>
			{
				payload.ids.forEach(notificationId => {
					const existingItem = store.state.collection.get(notificationId);
					if (!existingItem || existingItem.read === payload.read)
					{
						return false;
					}

					if (payload.read)
					{
						store.commit('decreaseCounter');
					}
					else
					{
						store.commit('increaseCounter');
					}

					store.commit('read', {
						id: existingItem.id,
						read: payload.read,
					});
				});
			},
			readAll: (store) =>
			{
				store.commit('readAll');
				store.commit('setCounter', 0);
			},
			delete: (store, payload) =>
			{
				const existingItem = store.state.collection.get(payload.id);
				if (!existingItem)
				{
					return;
				}

				if (existingItem.read === false)
				{
					store.commit('decreaseCounter');
				}

				store.commit('delete', {id: existingItem.id});
			},
			clearSearchResult: (store) =>
			{
				store.commit('clearSearchResult');
			},
			setCounter: (store, payload) =>
			{
				store.commit('setCounter', payload);
			}
		};
	}

	getMutations()
	{
		return {
			add: (state, payload) =>
			{
				payload.forEach(item => {
					state.collection.set(item.id, item);
				});
			},
			addSearchResult: (state, payload) =>
			{
				payload.forEach(item => {
					state.searchCollection.set(item.id, item);
				});
			},
			update: (state, payload) =>
			{
				payload.forEach(item => {
					state.collection.set(item.id, {
						...state.collection.get(item.id),
						...item.fields
					});
				});
			},
			updateSearchResult: (state, payload) =>
			{
				payload.forEach(item => {
					state.searchCollection.set(item.id, {
						...state.searchCollection.get(item.id),
						...item.fields
					});
				});
			},
			delete: (state, payload) =>
			{
				state.collection.delete(payload.id);
			},
			read: (state, payload) =>
			{
				state.collection.set(payload.id, {
					...state.collection.get(payload.id),
					read: payload.read
				});
			},
			readAll: (state) =>
			{
				[...state.collection.values()].forEach(item => {
					if (!item.read)
					{
						item.read = true;
					}
				});
			},
			setCounter: (state, payload) =>
			{
				state.unreadCounter = Number.parseInt(payload, 10);
			},
			decreaseCounter: (state) =>
			{
				if (state.unreadCounter > 0)
				{
					state.unreadCounter--;
				}
			},
			increaseCounter: (state) =>
			{
				state.unreadCounter++;
			},
			clearSearchResult: (state) =>
			{
				state.searchCollection.clear();
			}
		};
	}

	static validate(fields: Object)
	{
		const result = {};

		if (Type.isString(fields.id) || Type.isNumber(fields.id))
		{
			result.id = fields.id;
		}

		if (Type.isNumber(fields.author_id))
		{
			result.authorId = fields.author_id;
		}
		else if (Type.isNumber(fields.userId))
		{
			result.authorId = fields.userId;
		}

		if (!Type.isNil(fields.date))
		{
			result.date = Utils.date.cast(fields.date);
		}

		if (Type.isString(fields.notify_title))
		{
			result.title = fields.notify_title;
		}
		else if (Type.isString(fields.title))
		{
			result.title = fields.title;
		}

		if (Type.isString(fields.text) || Type.isNumber(fields.text))
		{
			result.text = Text.decode(fields.text.toString());
		}

		if (Type.isObjectLike(fields.params))
		{
			result.params = fields.params;
		}

		if (Type.isArray(fields.replaces))
		{
			result.replaces = fields.replaces;
		}

		if (!Type.isNil(fields.notify_buttons))
		{
			result.notifyButtons = JSON.parse(fields.notify_buttons);
		}
		else if (!Type.isNil(fields.buttons))
		{
			result.notifyButtons = fields.buttons.map(button => {
				return {
					COMMAND: 'notifyConfirm',
					COMMAND_PARAMS: `${result.id}|${button.VALUE}`,
					TEXT: `${button.TITLE}`,
					TYPE: 'BUTTON',
					DISPLAY: 'LINE',
					BG_COLOR: (button.VALUE === 'Y' ? '#8bc84b' : '#ef4b57'),
					TEXT_COLOR: '#fff',
				};
			});
		}

		if (fields.notify_type === NotificationTypesCodes.confirm || fields.type === NotificationTypesCodes.confirm)
		{
			result.sectionCode = NotificationTypesCodes.confirm;
		}
		else
		{
			result.sectionCode = NotificationTypesCodes.simple;
		}

		if (!Type.isNil(fields.notify_read))
		{
			result.read = fields.notify_read === 'Y';
		}
		else if (!Type.isNil(fields.read))
		{
			result.read = fields.read === 'Y';
		}

		if (Type.isString(fields.setting_name))
		{
			result.settingName = fields.setting_name;
		}
		else if (Type.isString(fields.settingName))
		{
			result.settingName = fields.settingName;
		}

		return result;
	}

	sortByType(a, b)
	{
		if (a.sectionCode === NotificationTypesCodes.confirm && b.sectionCode !== NotificationTypesCodes.confirm)
		{
			return -1;
		}
		else if (a.sectionCode !== NotificationTypesCodes.confirm && b.sectionCode === NotificationTypesCodes.confirm)
		{
			return 1;
		}
		else
		{
			return b.id - a.id;
		}
	}
}
