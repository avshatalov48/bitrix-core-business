/**
 * Bitrix Messenger
 * Notifications model (Vuex Builder model)
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2021 Bitrix
 */

import {Vue} from 'ui.vue';
import {VuexBuilderModel} from 'ui.vue.vuex';
import {Utils} from 'im.lib.utils';
import {Text, Type, Reflection} from 'main.core';
import {NotificationTypesCodes} from 'im.const';

class NotificationsModel extends VuexBuilderModel
{
	getName()
	{
		return 'notifications';
	}

	getState()
	{
		return {
			collection: [],
			searchCollection: [],
			chat_id: 0,
			total: 0,
			host: this.getVariable('host', location.protocol+'//'+location.host),
			unreadCounter: 0,
			schema: {}
		}
	}

	getElementState()
	{
		return {
			id: 0,
			authorId: 0,
			date: new Date(),
			text: '',
			sectionCode: NotificationTypesCodes.simple,
			textConverted: '',
			title: '',
			unread: false,
			display: true,
			settingName: 'im|default',
		};
	}

	getGetters()
	{
		return {
			get: state => () =>
			{
				return state.collection;
			},
			getById: state => (notificationId) =>
			{
				if (Type.isString(notificationId))
				{
					notificationId = parseInt(notificationId);
				}

				const existingItem = this.findItemInArr(state.collection, notificationId);
				if (!existingItem.element)
				{
					return false;
				}

				return existingItem.element;
			},
			getSearchItemById: state => (notificationId) =>
			{
				if (Type.isString(notificationId))
				{
					notificationId = parseInt(notificationId);
				}

				const existingItem = this.findItemInArr(state.searchCollection, notificationId);
				if (!existingItem.element)
				{
					return false;
				}

				return existingItem.element;
			},
			getBlank: state => params =>
			{
				return this.getElementState();
			},
		}
	}

	getActions()
	{
		return {
			set: (store, payload) =>
			{
				const result = {
					notification: [],
				};

				if (payload.notification instanceof Array)
				{
					result.notification = payload.notification.map(notification => this.prepareNotification(notification, { host: store.state.host }));
				}

				if (Type.isNumber(payload.total) || Type.isString(payload.total))
				{
					result.total = parseInt(payload.total);
				}

				store.commit('set', result);
			},
			setSearchResults: (store, payload) =>
			{
				const result = {
					notification: [],
				};

				if (!(payload.notification instanceof Array))
				{
					return false;
				}

				// we don't need validation for the local results
				if (payload.type === 'local')
				{
					result.notification = payload.notification;
				}
				else
				{
					result.notification = payload.notification.map(notification => this.prepareNotification(notification, { host: store.state.host }));
				}

				store.commit('setSearchResults', {
					data: result
				});
			},
			deleteSearchResults: (store, payload) =>
			{
				store.commit('deleteSearchResults');
			},
			setCounter: (store, payload) =>
			{
				if (Type.isNumber(payload.unreadTotal) || Type.isString(payload.unreadTotal))
				{
					const unreadCounter = parseInt(payload.unreadTotal);
					store.commit('setCounter', unreadCounter);
				}
			},
			setTotal: (store, payload) =>
			{
				if (Type.isNumber(payload.total) || Type.isString(payload.total))
				{
					store.commit('setTotal', payload.total);
				}
			},
			add: (store, payload) =>
			{
				const addItem = this.prepareNotification(payload.data, { host: store.state.host });
				addItem.unread = true;

				const existingItem = this.findItemInArr(store.state.collection, addItem.id);
				if (!existingItem.element)
				{
					store.commit('add', {
						data: addItem,
					});

					store.commit('setTotal', store.state.total + 1);
				}
				else
				{
					store.commit('update', {
						index: existingItem.index,
						fields: Object.assign({}, payload.fields),
					});
				}
			},
			updatePlaceholders: (store, payload) =>
			{
				if (payload.items instanceof Array)
				{
					payload.items = payload.items.map(notification => this.prepareNotification(notification));
				}
				else
				{
					return false;
				}

				store.commit('updatePlaceholders', payload);

				return true;
			},
			clearPlaceholders: (store, payload) =>
			{
				store.commit('clearPlaceholders', payload);
			},
			update: (store, payload) =>
			{
				const existingItem = this.findItemInArr(store.state.collection, payload.id);
				if (existingItem.element)
				{
					store.commit('update', {
						index: existingItem.index,
						fields: Object.assign({}, payload.fields),
					});
				}

				if (payload.searchMode)
				{
					const existingItemInSearchCollection = this.findItemInArr(store.state.searchCollection, payload.id);
					if (existingItemInSearchCollection.element)
					{
						store.commit('update', {
							searchCollection: true,
							index: existingItemInSearchCollection.index,
							fields: Object.assign({}, payload.fields),
						});
					}
				}
			},
			read: (store, payload) =>
			{
				for (const notificationId of payload.ids)
				{
					const existingItem = this.findItemInArr(store.state.collection, notificationId);
					if (!existingItem.element)
					{
						return false;
					}

					store.commit('read', {
						index: existingItem.index,
						action: !payload.action,
					});
				}
			},
			readAll: (store, payload) =>
			{
				store.commit('readAll');
			},
			delete: (store, payload) =>
			{
				const existingItem = this.findItemInArr(store.state.collection, payload.id);
				if (existingItem.element)
				{
					store.commit('delete', {
						searchCollection: false,
						index: existingItem.index,
					});

					store.commit('setTotal', store.state.total - 1);
				}

				if (payload.searchMode)
				{
					const existingItemInSearchCollection = this.findItemInArr(store.state.searchCollection, payload.id);
					if (existingItemInSearchCollection.element)
					{
						store.commit('delete', {
							searchCollection: true,
							index: existingItemInSearchCollection.index,
						});
					}
				}
			},
			deleteAll: (store, payload) =>
			{
				store.commit('deleteAll');
			},
			setSchema: (store, payload) =>
			{
				store.commit('setSchema', {
					data: payload.data,
				});
			},
		}
	}

	getMutations()
	{
		return {
			set: (state, payload) =>
			{
				state.total = payload.hasOwnProperty('total') ? payload.total : state.total;

				if (!payload.hasOwnProperty('notification') || !Type.isArray(payload.notification))
				{
					return;
				}

				for (const element of payload.notification)
				{
					const existingItem = this.findItemInArr(state.collection, element.id);

					if (!existingItem.element)
					{
						state.collection.push(element);
					}
					else
					{
						// we trust unread status of existing item to prevent notifications blinking while init loading.
						if (element.unread !== state.collection[existingItem.index].unread)
						{
							element.unread = state.collection[existingItem.index].unread;
							state.unreadCounter = (element.unread === true ? state.unreadCounter + 1 : state.unreadCounter - 1);
						}

						state.collection[existingItem.index] = Object.assign(
							state.collection[existingItem.index],
							element
						);
					}
				}

				state.collection.sort(this.sortByType);
			},
			setSearchResults: (state, payload) =>
			{
				for (const element of payload.data.notification)
				{
					const existingItem = this.findItemInArr(state.searchCollection, element.id);

					if (!existingItem.element)
					{
						state.searchCollection.push(element);
					}
					else
					{
						state.searchCollection[existingItem.index] = Object.assign(
							state.searchCollection[existingItem.index],
							element
						);
					}
				}
			},
			deleteAll: (state, payload) =>
			{
				state.collection = [];
			},
			deleteSearchResults: (state, payload) =>
			{
				state.searchCollection = [];
			},
			add: (state, payload) =>
			{
				let firstNotificationIndex = null;
				if (payload.data.sectionCode === NotificationTypesCodes.confirm)
				{
					//new confirms should always add to the beginning of the collection
					state.collection.unshift(payload.data);
				}
				else //if (payload.data.sectionCode === NotificationTypesCodes.simple)
				{
					for (let index = 0; state.collection.length > index; index++)
					{
						if (state.collection[index].sectionCode === NotificationTypesCodes.simple)
						{
							firstNotificationIndex = index;
							break;
						}
					}

					//if we didn't find any simple notification and its index, then add new one to the end.
					if (firstNotificationIndex === null)
					{
						state.collection.push(payload.data);
					}
					else //otherwise, put it right before first simple notification.
					{
						state.collection.splice(firstNotificationIndex, 0, payload.data);
					}
				}

				state.collection.sort(this.sortByType);
			},
			update: (state, payload) =>
			{
				const collectionName = payload.searchCollection ? 'searchCollection' : 'collection';

				Vue.set(state[collectionName], payload.index, Object.assign(
					{},
					state[collectionName][payload.index],
					payload.fields
				));

			},
			delete: (state, payload) =>
			{
				const collectionName = payload.searchCollection ? 'searchCollection' : 'collection';
				state[collectionName].splice(payload.index, 1);
			},
			read: (state, payload) =>
			{
				state.collection[payload.index].unread = payload.action;
			},
			readAll: (state, payload) =>
			{
				for (let index = 0; state.collection.length > index; index++)
				{
					state.collection[index].unread = false;
				}
			},
			updatePlaceholders: (state, payload) =>
			{
				const collectionName = payload.searchCollection ? 'searchCollection' : 'collection';

				payload.items.forEach((element, index) => {
					const placeholderId = `placeholder${payload.firstItem + index}`;
					const existingPlaceholderIndex = state[collectionName].findIndex(notification => {
						return notification.id === placeholderId;
					});

					const existingMessageIndex = state[collectionName].findIndex(notification => {
						return notification.id === element.id;
					});

					if (existingMessageIndex >= 0)
					{
						state[collectionName][existingMessageIndex] = Object.assign(
							state[collectionName][existingMessageIndex],
							element
						);
						state[collectionName].splice(existingPlaceholderIndex, 1);
					}
					else
					{
						state[collectionName].splice(
							existingPlaceholderIndex,
							1,
							Object.assign({}, element)
						);
					}
				});

				state[collectionName].sort(this.sortByType);
			},
			clearPlaceholders: (state, payload) =>
			{
				state.collection = state.collection.filter(element => {
					return !element.id.toString().startsWith('placeholder');
				});

				state.searchCollection = state.searchCollection.filter(element => {
					return !element.id.toString().startsWith('placeholder');
				});
			},
			setCounter: (state, payload) =>
			{
				state.unreadCounter = payload;
			},
			setTotal: (state, payload) =>
			{
				state.total = payload;
			},
			setSchema: (state, payload) =>
			{
				state.schema = payload.data;
			}
		}
	}

	/* region Validation */
	validate(fields, options)
	{
		const result = {};

		if (Type.isString(fields.id) || Type.isNumber(fields.id))
		{
			result.id = fields.id;
		}

		if (!Type.isNil(fields.date))
		{
			result.date = Utils.date.cast(fields.date);
		}

		if (Type.isString(fields.text) || Type.isNumber(fields.text))
		{
			result.text = fields.text.toString();
			result.textConverted = NotificationsModel.decodeText(result.text);
		}

		if (Type.isNumber(fields.author_id))
		{
			if (fields.system === true || fields.system === 'Y')
			{
				result.authorId = 0;
			}
			else
			{
				result.authorId = fields.author_id;
			}
		}

		if (Type.isNumber(fields.userId))
		{
			result.authorId = fields.userId;
		}

		if (Type.isObjectLike(fields.params))
		{
			const params = this.validateParams(fields.params);
			if (params)
			{
				result.params = params;
			}
		}

		if (!Type.isNil(fields.notify_buttons))
		{
			result.notifyButtons = JSON.parse(fields.notify_buttons);
		}

		//p&p format
		if (!Type.isNil(fields.buttons))
		{
			result.notifyButtons = fields.buttons.map((button) => {
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
		else if (fields.type === NotificationTypesCodes.placeholder)
		{
			result.sectionCode = NotificationTypesCodes.placeholder;
		}

		if (!Type.isNil(fields.notify_read))
		{
			result.unread = fields.notify_read === 'N';
		}

		//p&p format
		if (!Type.isNil(fields.read))
		{
			result.unread = fields.read === 'N'; //?
		}

		if (Type.isString(fields.setting_name))
		{
			result.settingName = fields.setting_name;
		}

		// rest format
		if (Type.isString(fields.notify_title) && fields.notify_title.length > 0)
		{
			result.title = fields.notify_title;
		}

		// p&p format
		if (Type.isString(fields.title) && fields.title.length > 0)
		{
			result.title = fields.title;
		}

		return result;
	}

	validateParams(params)
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
					if (Type.isString(params[field]) && BX.Vue.isComponent(params[field]))
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
	/* endregion Validation */

	/* region Internal helpers */
	prepareNotification(notification, options = {})
	{
		let result = this.validate(Object.assign({}, notification));

		return Object.assign({}, this.getElementState(), result, options);
	}

	findItemInArr(arr, value, key = 'id')
	{
		const result = {};

		const elementIndex = arr.findIndex((element, index) => {
			return element[key] === value;
		});

		if (elementIndex !== -1)
		{
			result.index = elementIndex;
			result.element = arr[elementIndex];
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
	/* endregion Internal helpers */

	static decodeText(text: string)
	{
		text = Text.decode(text.toString());
		text = Utils.text.decode(text, {skipImages: true});

		const Parser = Reflection.getClass('BX.Messenger.v2.Lib.Parser');
		if (Parser)
		{
			text = Parser.decodeSmileForLegacyCore(text, {enableBigSmile: false});
		}

		if (!Utils.platform.isBitrixDesktop())
		{
			text = text.replace(/<a(.*?)>(.*?)<\/a>/gi, (whole, anchor, innerText) => {
				return `<a ${anchor.replace('target="_blank"', 'target="_self"')} class="bx-im-notifications-item-link">${innerText}</a>`;
			});
		}

		return text;
	}
}

export {NotificationsModel};