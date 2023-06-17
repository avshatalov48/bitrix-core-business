/**
 * Bitrix im
 * Notifications vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

import 'ui.design-tokens';

import { BitrixVue } from 'ui.vue';
import { Vuex } from 'ui.vue.vuex';
import { Logger } from 'im.lib.logger';
import { Utils as MessengerUtils } from 'im.lib.utils';
import { Popup } from 'im.view.popup';
import { MountingPortal } from 'ui.vue.portal';
import { Animation } from "im.lib.animation";

import { MenuManager } from 'main.popup';
import { Type } from 'main.core';
import 'ui.forms';

import { NotificationItem } from './component/notification-item';
import { NotificationSearchResult } from './component/notification-search-result';
import './notifications.css';
import { EventType, RestMethod, RestMethodHandler, NotificationTypesCodes } from 'im.const';
import { NotificationCore } from './mixin/notificationCore';
import { Timer } from 'im.lib.timer';
import { EventEmitter } from "main.core.events";

const ObserverType = Object.freeze({
	read: 'read',
	none: 'none',
});

/**
 * @notice Do not mutate or clone this component! It is under development.
 */
BitrixVue.component('bx-im-component-notifications',
{
	components:
	{
		NotificationItem,
		MountingPortal,
		Popup,
		NotificationSearchResult
	},
	directives:
	{
		'bx-im-directive-notifications-observer':
		{
			inserted(element, bindings, vnode)
			{
				if (bindings.value === ObserverType.none)
				{
					return false;
				}

				if (!vnode.context.observers[bindings.value])
				{
					vnode.context.observers[bindings.value] = vnode.context.getObserver({
						type: bindings.value
					});
				}
				vnode.context.observers[bindings.value].observe(element);

				return true;
			},
			unbind(element, bindings, vnode)
			{
				if (bindings.value === ObserverType.none)
				{
					return true;
				}

				if (vnode.context.observers[bindings.value])
				{
					vnode.context.observers[bindings.value].unobserve(element);
				}

				return true;
			}
		}
	},
	mixins: [
		NotificationCore
	],
	props:
	{
		darkTheme: { default: undefined },
	},
	data: function()
	{
		return {
			initialDataReceived: false,
			perPage: 50,
			isLoadingInitialData: false,
			isLoadingNewPage: false,
			pagesRequested: 0,
			pagesLoaded: 0,
			lastId: 0,
			lastType: NotificationTypesCodes.confirm,

			ObserverType: ObserverType,
			notificationsOnScreen: [],
			notificationsToRead: [],
			notificationsToDelete: [],
			changeReadStatusBlockTimeout: {},
			firstUnreadNotificationOnInit: null,

			contentPopupType: '',
			contentPopupValue: '',
			popupInstance: null,
			popupIdSelector: '',
			contextPopupInstance: null,

			searchQuery: '',
			searchType: '',
			searchDate: '',
			showSearch: false,

			callViewState: false,
		};
	},
	computed:
	{
		NotificationTypesCodes: () => NotificationTypesCodes,
		remainingPages()
		{
			return Math.ceil(
				(this.total - this.notification.length) / this.perPage
			);
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('IM_NOTIFICATIONS_', this);
		},
		visibleNotifications()
		{
			return this.notification.filter((notificationItem) => {
				return notificationItem.display;
			});
		},
		highestNotificationId()
		{
			return this.notification.reduce((highestId, currentNotification) => {
				return currentNotification.id > highestId ? currentNotification.id : highestId
			}, 0);
		},
		isNeedToReadAll()
		{
			return this.unreadCounter > 0;
		},
		panelStyles()
		{
			if (this.callViewState === BX.Call.Controller.ViewState.Folded && !this.showSearch)
			{
				return {
					paddingBottom: '60px' // height of .bx-messenger-videocall-panel-folded
				};
			}

			return {};
		},
		filterBoxStyles()
		{
			if (this.callViewState === BX.Call.Controller.ViewState.Folded && this.showSearch)
			{
				return {
					paddingTop: '70px' // height of .bx-messenger-videocall-panel-folded + 10px for space
				};
			}

			return {};
		},
		firstUnreadNotification()
		{
			let unreadNotification = null;
			const maxNotificationIndex = this.notification.length - 1;

			for (let i = 0; i <= maxNotificationIndex; i++)
			{
				if (this.notification[i].unread && this.notification[i].sectionCode !== NotificationTypesCodes.placeholder)
				{
					unreadNotification = this.notification[i];
					break;
				}
			}

			return unreadNotification;
		},
		firstUnreadNotificationBelowVisible()
		{
			const minIdOnScreen = Math.max(...this.notificationsOnScreen);

			let unreadId = null;
			const maxNotificationIndex = this.notification.length - 1;

			for (let i = 0; i <= maxNotificationIndex; i++)
			{
				if (
					this.notification[i].unread
					&& minIdOnScreen > this.notification[i].id
					&& this.notification[i].sectionCode === NotificationTypesCodes.simple
				)
				{
					unreadId = this.notification[i].id;
					break;
				}
			}

			return unreadId;
		},
		isUnreadNotificationVisible()
		{
			const unreadOnScreen = Array.from(this.notificationsOnScreen).filter(idOnScreen => {
				const notificationOnScreen = this.$store.getters['notifications/getById'](idOnScreen);

				return notificationOnScreen ? notificationOnScreen.unread : false;
			});

			return unreadOnScreen.length > 0
		},
		showScrollButton()
		{
			if (!this.initialDataReceived)
			{
				return false;
			}

			if (this.unreadCounter <= 0 || !BXIM.settings.notifyAutoRead)
			{
				return false;
			}

			if (this.notificationsOnScreen.length === 0)
			{
				return false;
			}

			if (this.isUnreadNotificationVisible)
			{
				return false;
			}

			return true;
		},

		hasUnreadBelowVisible()
		{
			let unreadCounterBeforeVisible = 0;

			for (let i = 0; i <= this.notification.length - 1; i++)
			{
				if (this.notification[i].unread && this.notification[i].sectionCode !== NotificationTypesCodes.placeholder)
				{
					++unreadCounterBeforeVisible;
				}

				// In this case we decide that there is no more unread notifications below visible notifications,
				// so we show arrow up on scroll button.
				if (
					this.notificationsOnScreen.includes(this.notification[i].id)
					&& this.unreadCounter === unreadCounterBeforeVisible
				)
				{
					return false;
				}
			}

			return true;
		},

		arrowButtonClass()
		{
			let arrowUp = !this.hasUnreadBelowVisible;

			return {
				'bx-im-notifications-scroll-button-arrow-down': !arrowUp,
				'bx-im-notifications-scroll-button-arrow-up': arrowUp,
				'bx-im-notifications-scroll-button-arrow': true,
			}
		},
		filterTypes()
		{
			const originalSchema = Object.assign({}, this.schema);

			// get rid of some subcategories
			const modulesToReduceListItems = [
				'timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender', 'blog', 'vote', 'socialnetwork',
				'imopenlines', 'photogallery', 'intranet', 'forum'
			];
			modulesToReduceListItems.forEach(moduleId => {
				if (originalSchema.hasOwnProperty(moduleId))
				{
					delete originalSchema[moduleId].LIST;
				}
			});

			// rename some groups
			if (originalSchema.hasOwnProperty('calendar'))
			{
				originalSchema['calendar'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_CALENDAR'];
			}
			if (originalSchema.hasOwnProperty('sender'))
			{
				originalSchema['sender'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SENDER'];
			}
			if (originalSchema.hasOwnProperty('blog'))
			{
				originalSchema['blog'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_BLOG'];
			}
			if (originalSchema.hasOwnProperty('socialnetwork'))
			{
				originalSchema['socialnetwork'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_SOCIALNETWORK'];
			}
			if (originalSchema.hasOwnProperty('intranet'))
			{
				originalSchema['intranet'].NAME = this.localize['IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_INTRANET'];
			}

			// we need only this modules in this order!
			const modulesToShowInFilter = [
				'tasks', 'calendar', 'crm', 'timeman', 'mail', 'disk', 'bizproc', 'voximplant', 'sender',
				'blog', 'vote', 'socialnetwork', 'imopenlines', 'photogallery', 'intranet', 'forum'
			];
			const notificationFilterTypes = [];
			modulesToShowInFilter.forEach(moduleId => {
				if (originalSchema.hasOwnProperty(moduleId))
				{
					notificationFilterTypes.push(originalSchema[moduleId]);
				}
			});

			return notificationFilterTypes;
		},
		...Vuex.mapState({
			notification: state => state.notifications.collection,
			total: state => state.notifications.total,
			unreadCounter: state => state.notifications.unreadCounter,
			schema: state => state.notifications.schema,
		})
	},
	created()
	{
		this.drawPlaceholders().then(() => {
			this.getInitialData();
		});

		EventEmitter.subscribe(EventType.notification.updateState, this.onUpdateState);
		window.addEventListener('focus', this.onWindowFocus);
		window.addEventListener('blur', this.onWindowBlur);

		if (BXIM && BX.Call)
		{
			this.callViewState = BXIM.callController.callViewState;

			BXIM.callController.subscribe(BX.Call.Controller.Events.onViewStateChanged, this.onCallViewStateChange);
		}

		this.timer = new Timer();
		this.readNotificationsQueue = new Set();
		this.readNotificationsNodes = {};
		this.observers = {};

		this.readVisibleNotificationsDelayed = MessengerUtils.debounce(this.readVisibleNotifications, 50, this);
	},
	mounted()
	{
		this.windowFocused = document.hasFocus();
	},
	beforeDestroy()
	{
		this.observers = {};
		window.removeEventListener('focus', this.onWindowFocus);
		window.removeEventListener('blur', this.onWindowBlur);
		EventEmitter.unsubscribe(EventType.notification.updateState, this.onUpdateState);
		if (BXIM && BX.Call)
		{
			BXIM.callController.unsubscribe(BX.Call.Controller.Events.onViewStateChanged, this.onCallViewStateChange);
		}
	},
	methods:
	{
		getFirstUnreadNotificationOnInit()
		{
			if (this.unreadCounter <= 0)
			{
				return null;
			}

			let unreadId = null;
			const maxNotificationIndex = this.notification.length - 1;

			for (let i = 0; i <= maxNotificationIndex; i++)
			{
				if (this.notification[i].unread)
				{
					unreadId = this.notification[i].id;
					break;
				}
			}

			return unreadId;
		},
		onCallViewStateChange({data})
		{
			this.callViewState = data.callViewState;
		},
		onUpdateState(event)
		{
			const lastNotificationId = event.data.lastId;
			if (
				!this.isLoadingInitialData
				&& this.highestNotificationId > 0
				&& lastNotificationId !== this.highestNotificationId
			)
			{
				this.getInitialData();
			}
		},
		readVisibleNotifications()
		{
			//todo: replace legacy chat API
			if (!this.windowFocused || !BXIM.settings.notifyAutoRead)
			{
				Logger.warn('reading is disabled!');

				return false;
			}

			this.readNotificationsQueue.forEach(notificationId => {
				if (this.readNotificationsNodes[notificationId])
				{
					delete this.readNotificationsNodes[notificationId];
				}
				this.readNotifications(parseInt(notificationId, 10));
			});
			this.readNotificationsQueue.clear();
		},
		getInitialData()
		{
			this.isLoadingInitialData = true;
			const queryParams = {
				[RestMethodHandler.imNotifyGet]: [RestMethod.imNotifyGet, {
					'LIMIT': this.perPage,
					'CONVERT_TEXT': 'Y'
				}],
				[RestMethodHandler.imNotifySchemaGet]: [RestMethod.imNotifySchemaGet, {}],
			};

			this.getRestClient().callBatch(queryParams, (response) => {
				Logger.warn('im.notify.get: initial result', response[RestMethodHandler.imNotifyGet].data());
				this.processInitialData(response[RestMethodHandler.imNotifyGet].data());
				this.processSchemaData(response[RestMethodHandler.imNotifySchemaGet].data());
				this.pagesLoaded++;
				this.isLoadingInitialData = false;
				this.firstUnreadNotificationOnInit = this.getFirstUnreadNotificationOnInit();
			}, false, false);
		},
		processInitialData(data)
		{
			//if we got empty data - clear all placeholders
			if (!data.notifications || data.notifications.length === 0)
			{
				this.$store.dispatch('notifications/clearPlaceholders');

				this.$store.dispatch('notifications/setTotal', {
					total: this.notification.length,
				});

				return false;
			}

			this.lastId = this.getLastItemId(data.notifications);
			this.lastType = this.getLastItemType(data.notifications);

			this.$store.dispatch('notifications/clearPlaceholders');
			this.$store.dispatch('notifications/setCounter', {
				unreadTotal: data.total_unread_count
			});
			this.$store.dispatch('notifications/set', {
				notification: data.notifications,
				total: data.total_count,
			});
			this.$store.dispatch('users/set', data.users);
			this.updateRecentList(data.total_unread_count, true);

			this.initialDataReceived = true;
		},
		processSchemaData(data)
		{
			this.$store.dispatch('notifications/setSchema', {data: data});
		},
		drawPlaceholders()
		{
			const placeholders = this.generatePlaceholders(this.perPage);

			return this.$store.dispatch('notifications/set', {notification: placeholders});
		},
		loadNextPage()
		{
			Logger.warn(`Loading more notifications!`);

			const queryParams = {
				'LIMIT': this.perPage,
				'LAST_ID': this.lastId,
				'LAST_TYPE': this.lastType,
				'CONVERT_TEXT': 'Y'
			};

			this.getRestClient().callMethod('im.notify.get', queryParams).then(result => {
				Logger.warn('im.notify.get: new page results', result.data());

				const newUsers = result.data().users;
				const newItems = result.data().notifications;

				//if we got empty data - clear all placeholders
				if (!newItems || newItems.length === 0)
				{
					this.$store.dispatch('notifications/clearPlaceholders');

					this.$store.dispatch('notifications/setTotal', {
						total: this.notification.length,
					});

					return false;
				}

				this.lastId = this.getLastItemId(newItems);
				this.lastType = this.getLastItemType(newItems);

				this.$store.dispatch('users/set', newUsers);

				//change temp data in models to real data, we need new items, first item to update and section
				return this.$store.dispatch('notifications/updatePlaceholders', {
						items: newItems,
						firstItem: this.pagesLoaded * this.perPage,
					});
			}).then(() => {
				this.pagesLoaded++;
				Logger.warn('Page loaded. Total loaded - ', this.pagesLoaded);

				return this.onAfterLoadNextPageRequest();
			}).catch(result => {
				Logger.warn('Request history error', result);
			});
		},
		onAfterLoadNextPageRequest()
		{
			Logger.warn('onAfterLoadNextPageRequest');
			if (this.pagesRequested > 0)
			{
				Logger.warn('We have delayed requests -', this.pagesRequested);
				this.pagesRequested--;

				return this.loadNextPage();
			}
			else
			{
				Logger.warn('No more delayed requests, clearing placeholders');
				this.$store.dispatch('notifications/clearPlaceholders');
				this.isLoadingNewPage = false;

				return true;
			}
		},
		changeReadStatus(item)
		{
			this.$store.dispatch('notifications/read', { ids: [item.id], action: item.unread });
			// change the unread counter
			const originalCounterBeforeUpdate = this.unreadCounter;
			const counterValue = item.unread ? this.unreadCounter - 1 : this.unreadCounter + 1;
			this.updateRecentList(counterValue);
			this.$store.dispatch('notifications/setCounter', {
				unreadTotal: counterValue
			});

			clearTimeout(this.changeReadStatusBlockTimeout[item.id]);
			this.changeReadStatusBlockTimeout[item.id] = setTimeout(() => {
				this.getRestClient().callMethod('im.notify.read', {
						id: item.id,
						action: item.unread ? 'Y' : 'N',
						only_current: 'Y'
					})
					.then(() => {
						Logger.warn(`Notification ${item.id} unread status set to ${!item.unread}`);
					})
					.catch((error) => {
						console.error(error);
						this.$store.dispatch('notifications/read', { ids: [item.id], action: !item.unread });
						// restore the unread counter in case of an error
						this.updateRecentList(originalCounterBeforeUpdate)
						this.$store.dispatch('notifications/setCounter', {
							unreadTotal: originalCounterBeforeUpdate
						});
					});
			}, 1500);
		},
		delete(item)
		{
			const itemId = +item.id;
			this.notificationsToDelete.push(itemId);
			const notification = this.$store.getters['notifications/getById'](itemId);
			this.$store.dispatch('notifications/update', {
				id: itemId,
				fields: { display: false }
			});
			// change the unread counter
			const originalCounterBeforeUpdate = this.unreadCounter;
			const counterValue = notification.unread ? this.unreadCounter - 1 : this.unreadCounter;
			this.updateRecentList(counterValue, true)
			this.$store.dispatch('notifications/setCounter', {
				unreadTotal: counterValue
			});

			this.timer.stop('deleteNotificationServer', 'notifications', true);

			this.timer.start('deleteNotificationServer', 'notifications', .5, () => {
				const idsToDelete = this.notificationsToDelete;
				this.notificationsToDelete = [];

				this.getRestClient().callMethod('im.notify.delete', { id: idsToDelete })
					.then(() => {
						idsToDelete.forEach(id => {
							this.$store.dispatch('notifications/delete', { id: id });
						});

					})
					.catch((error) => {
						console.error(error)
						idsToDelete.forEach(id => {
							this.$store.dispatch('notifications/update', {
								id: id,
								fields: { display: true }
							});
						});

						// restore the unread counter in case of an error
						this.updateRecentList(originalCounterBeforeUpdate, true)
						this.$store.dispatch('notifications/setCounter', {
							unreadTotal: originalCounterBeforeUpdate
						});
					});
			});
		},
		getObserver(config)
		{
			if (
				typeof window.IntersectionObserver === 'undefined'
				|| config.type === ObserverType.none
			)
			{
				return {
					observe: () => {},
					unobserve: () => {}
				};
			}

			const observerCallback = (entries) => {
				entries.forEach(entry => {
					let sendReadEvent = false;
					const entryNotificationId = parseInt(entry.target.dataset.id, 10);
					if (entry.isIntersecting)
					{
						//on Windows with interface scaling intersectionRatio will never be 1
						if (entry.intersectionRatio >= 0.99)
						{
							sendReadEvent = true;
							this.notificationsOnScreen.push(entryNotificationId);
						}
						else if (
							entry.intersectionRatio > 0
							&& entry.intersectionRect.height > entry.rootBounds.height / 2
						)
						{
							sendReadEvent = true;
							this.notificationsOnScreen.push(entryNotificationId);
						}
						else
						{
							this.notificationsOnScreen = this.notificationsOnScreen.filter(notificationId => notificationId !== entryNotificationId);
						}
					}
					else
					{
						this.notificationsOnScreen = this.notificationsOnScreen.filter(notificationId => notificationId !== entryNotificationId);
					}

					if (sendReadEvent)
					{
						this.readNotificationsQueue.add(entryNotificationId);
						this.readNotificationsNodes[entryNotificationId] = entry.target;
					}
					else
					{
						this.readNotificationsQueue.delete(entryNotificationId);
						delete this.readNotificationsNodes[entryNotificationId];
					}

					this.readVisibleNotificationsDelayed();
				});
			};

			const observerOptions = {
				root: this.$refs['listNotifications'],
				threshold: new Array(101).fill(0).map((zero, index) => index * 0.01)
			};

			return new IntersectionObserver(observerCallback, observerOptions);

		},

		//events
		onScroll(event)
		{
			if (!this.isReadyToLoadNewPage(event))
			{
				return;
			}

			if (this.remainingPages === 0 || !this.initialDataReceived)
			{
				return;
			}

			if (this.isLoadingNewPage)
			{
				this.drawPlaceholders().then(() => {
					this.pagesRequested++;
					Logger.warn('Already loading! Draw placeholders and add request, total - ', this.pagesRequested);
				});
			}
			else //if (!this.isLoadingNewPage)
			{
				Logger.warn('Starting new request');

				this.isLoadingNewPage = true;

				this.drawPlaceholders().then(() => {
					this.loadNextPage();
				});
			}
		},
		onWindowFocus()
		{
			this.windowFocused = true;
			this.readVisibleNotifications();
		},
		onWindowBlur()
		{
			this.windowFocused = false;
		},
		onDoubleClick(event)
		{
			this.changeReadStatus(event.item);
		},
		onButtonsClick(event)
		{
			const params = this.getConfirmRequestParams(event);
			const itemId = +params.NOTIFY_ID;

			this.$store.dispatch('notifications/update', {
				id: itemId,
				fields: { display: false }
			});
			// change the unread counter
			const counterValueBeforeUpdate = this.unreadCounter;
			const counterValue = this.unreadCounter - 1;
			this.updateRecentList(counterValue, true);
			this.$store.dispatch('notifications/setCounter', {
				unreadTotal: counterValue
			});

			this.getRestClient().callMethod('im.notify.confirm', params)
				.then(() => {
					this.$store.dispatch('notifications/delete', {
						id: itemId,
					});
				})
				.catch(() => {
					this.$store.dispatch('notifications/update', {
						id: itemId,
						fields: { display: true }
					});
					// restore the unread counter in case of an error
					this.updateRecentList(counterValueBeforeUpdate, true);
					this.$store.dispatch('notifications/setCounter', {
						unreadTotal: counterValueBeforeUpdate
					});
				});
		},
		onDeleteClick(event)
		{
			this.delete(event.item);

			//we need to load more, if we are on the first page and we have more elements.
			if (!this.isLoadingNewPage && this.remainingPages > 0 && this.notification.length === this.perPage - 1)
			{
				this.isLoadingNewPage = true;

				this.drawPlaceholders().then(() => {
					this.loadNextPage();
				});
			}
		},
		onRightClick(event)
		{
			if (this.contextPopupInstance !== null)
			{
				this.closeContextMenuPopup();
			}

			const items = this.getContextMenu(event.item);

			this.contextPopupInstance = MenuManager.create({
				id: 'bx-messenger-context-popup-external-data',
				bindElement: event.event,
				items: items,
				events: {
					onPopupClose: () => this.contextPopupInstance.destroy(),
					onPopupDestroy: () => this.contextPopupInstance = null
				},
			});

			this.contextPopupInstance.show();
		},

		onDateFilterClick(event)
		{
			if (typeof (BX) !== 'undefined' && BX.calendar && BX.calendar.get().popup)
			{
				BX.calendar.get().popup.close();
			}

			BX.calendar({
				node: event.target,
				field: event.target,
				bTime: false,
				callback_after: () => {
					this.searchDate = event.target.value;
				}
			});

			return false;
		},

		getContextMenu(notification)
		{
			const unreadMenuItemText = notification.unread ?
				this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_SET_READ'] :
				this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_SET_UNREAD'];

			const blockMenuItemText = Type.isUndefined(BXIM.settingsNotifyBlocked[notification.settingName]) ?
				this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_DONT_NOTIFY'] :
				this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_NOTIFY'];

			return [
				{
					text: unreadMenuItemText,
					onclick: (event, item) => {
						this.changeReadStatus(notification);
						this.closeContextMenuPopup();
					}},
				{
					text: this.localize['IM_NOTIFICATIONS_CONTEXT_POPUP_DELETE_NOTIFICATION'],
					onclick: (event, item) => {
						this.delete(notification);
						this.closeContextMenuPopup();
					}},
				{
					text: blockMenuItemText,
					onclick: (event, item) => {
						console.log(notification)
						this.closeContextMenuPopup();
					}
				},
			];
		},
		closeContextMenuPopup()
		{
			this.contextPopupInstance.destroy();
			this.contextPopupInstance = null;
		},
		getConfirmRequestParams(event)
		{
			if (event.params)
			{
				const options = event.params.params.split('|');

				return {
					'NOTIFY_ID': options[0],
					'NOTIFY_VALUE': options[1],
				};
			}

			return null;
		},
		readNotifications(notificationId)
		{
			const notification = this.$store.getters['notifications/getById'](notificationId);
			if (notification.unread === false)
			{
				return false;
			}

			this.notificationsToRead.push(notificationId);
			// read on front
			this.$store.dispatch('notifications/read', { ids: [notificationId], action: true });

			// change the unread counter
			const counterValueBeforeUpdate = this.unreadCounter;
			const counterValue = this.unreadCounter - 1;
			this.$store.dispatch('notifications/setCounter', { unreadTotal: counterValue });
			// update recent counter
			this.updateRecentList(counterValue);

			this.timer.stop('readNotificationServer', 'notifications', true);

			this.timer.start('readNotificationServer', 'notifications', .5, () => {
				const idsToRead = this.notificationsToRead;
				this.notificationsToRead = [];

				// we can read all notifications from some ID, only if we have not received new notifications
				// (otherwise we will read notifications at the top that we are not actually seeing)
				let canReadFromId = false;
				if (this.firstUnreadNotificationOnInit !== null)
				{
					canReadFromId = Math.max(...idsToRead) <= this.firstUnreadNotificationOnInit;
				}

				let restMethod = 'im.notify.read.list';
				let requestParams = {
					ids: idsToRead,
					action: 'Y'
				};

				if (canReadFromId)
				{
					const readFromId = Math.min(...idsToRead);
					restMethod = 'im.notify.read';
					requestParams = {
						id: readFromId,
						action: 'Y'
					};
				}

				this.getRestClient().callMethod(restMethod, requestParams).then(() => {
					Logger.warn('I have read the notifications', requestParams);
				}).catch(() => {
					this.$store.dispatch('notifications/read', { ids: idsToRead, action: false });
					// restore the unread counter in case of an error
					this.$store.dispatch('notifications/setCounter', { unreadTotal: counterValueBeforeUpdate });
					this.updateRecentList(counterValueBeforeUpdate);
				});
			});
		},
		getLastItemType(collection)
		{
			return this.getItemType(collection[collection.length - 1]);
		},
		getItemType(item)
		{
			if (item.notify_type === NotificationTypesCodes.confirm)
			{
				return NotificationTypesCodes.confirm;
			}
			else
			{
				return NotificationTypesCodes.simple;
			}
		},
		getLatest()
		{
			let latestNotification = {
				id: 0
			};

			for (const notification of this.notification)
			{
				if (notification.id > latestNotification.id)
				{
					latestNotification = notification;
				}
			}

			return latestNotification;
		},
		//todo: refactor this method for the new chat
		showConfirmPopupOnReadAll()
		{
			const readAll = this.readAll.bind(this);

			BXIM.openConfirm(this.localize['IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP'], [
				new BX.PopupWindowButton({
					text: this.localize['IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP_YES'],
					className: 'popup-window-button-accept',
					events: {
						click: function() {
							readAll();
							this.popupWindow.close();
						}
					}
				}),
				new BX.PopupWindowButton({
					text: this.localize['IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP_CANCEL'],
					className: 'popup-window-button',
					events: {
						click: function() {
							this.popupWindow.close();
						}
					}
				})
			]);
		},
		readAll()
		{
			if (this.notification.lastId <= 0)
			{
				return;
			}

			if (!this.isNeedToReadAll)
			{
				return false;
			}

			this.$store.dispatch('notifications/readAll');

			this.getRestClient().callMethod('im.notify.read', {
				id: 0,
				action: 'Y'
			}).catch((result) => {
				this.getInitialData();
				console.error(result);
			});
		},
		updateRecentList(counterValue, setPreview = false)
		{
			const fields = {
				counter: counterValue
			};

			if (setPreview)
			{
				const latestNotification = this.getLatest();
				fields.message = {
					id: latestNotification.id,
					text: latestNotification.text,
					date: latestNotification.date
				};
			}

			this.$store.dispatch('recent/update', {
				id: 'notify',
				fields: fields
			});
		},
		onScrollButtonClick(event)
		{
			if (this.isLoadingNewPage || !this.initialDataReceived)
			{
				return false;
			}

			let notificationIdToScroll = null;
			if (this.firstUnreadNotificationBelowVisible !== null)
			{
				notificationIdToScroll = this.firstUnreadNotificationBelowVisible;
			}
			else if (!this.hasUnreadBelowVisible)
			{
				notificationIdToScroll = this.firstUnreadNotification.id
			}

			let firstUnreadNotificationNode = null
			if (notificationIdToScroll !== null)
			{
				const selector = `.bx-im-notifications-item[data-id="${notificationIdToScroll}"]`;
				firstUnreadNotificationNode = document.querySelector(selector);
			}

			if (firstUnreadNotificationNode)
			{
				this.animatedScrollToPosition({
					start: this.$refs['listNotifications'].scrollTop,
					end: firstUnreadNotificationNode.offsetTop
				});
			}
			else
			{
				const latestNotification = this.notification[this.notification.length - 1];
				const selector = `.bx-im-notifications-item[data-id="${latestNotification.id}"]`;
				const latestNotificationNode = document.querySelector(selector);

				this.animatedScrollToPosition({
					start: this.$refs['listNotifications'].scrollTop,
					end: latestNotificationNode.offsetTop
				});
			}
		},
		animatedScrollToPosition(params = {})
		{
			if (this.animateScrollId)
			{
				Animation.cancel(this.animateScrollId);
				this.scrollAnimating = false;
			}
			if (typeof params === 'function')
			{
				params = {callback: params};
			}

			const container = this.$refs.listNotifications;

			let {
				start = container.scrollTop,
				end = container.scrollHeight - container.clientHeight,
				increment = 20,
				callback,
				duration = 500
			} = params;

			if (container && (end - start) > container.offsetHeight * 3)
			{
				start = end - container.offsetHeight * 3;
			}

			this.scrollAnimating = true;

			this.animateScrollId = Animation.start({
				start,
				end,
				increment,
				duration,

				element: container,
				elementProperty: 'scrollTop',

				callback: () =>
				{
					this.animateScrollId = null;
					this.scrollAnimating = false;
					if (callback && typeof callback === 'function')
					{
						callback();
					}
				},
			});
		},
	},
	//language=Vue
	template: `
		<div class="bx-messenger-next-notify">
			<div class="bx-messenger-panel-next-wrapper" :style="panelStyles">
				<div class="bx-messenger-panel-next">
					<div>
						<span 
							class="bx-messenger-panel-avatar bx-im-notifications-image-system bx-im-notifications-header-image"
						></span>
						<span class="bx-messenger-panel-title bx-messenger-panel-title-middle" style="flex-shrink: 0;">
							{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_HEADER') }}
						</span>
					</div>
					<div v-if="notification.length > 0" class="bx-im-notifications-header-buttons">
						<transition name="notifications-read-all-fade">
							<div v-if="isNeedToReadAll" class="bx-im-notifications-header-read-all">
								<span
									class='bx-messenger-panel-button bx-im-notifications-header-read-all-icon'
									@click="showConfirmPopupOnReadAll"
									:title="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_READ_ALL_BUTTON')"
								></span>
							</div>
						</transition>
						<div class="bx-im-notifications-header-filter">
							<span
								:class="['bx-messenger-panel-button bx-messenger-panel-history bx-im-notifications-header-filter-icon', (showSearch? 'bx-im-notifications-header-filter-active': '')]"
								@click="showSearch = !showSearch"
								:title="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_OPEN_BUTTON')"
							></span>
						</div>
					</div>
				</div>
				<div v-if="showSearch" class="bx-im-notifications-header-filter-box" :style="filterBoxStyles">
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-xs ui-ctl-w25">
						<div class="ui-ctl-after ui-ctl-icon-angle"></div>
						<select class="ui-ctl-element" v-model="searchType">
							<option value="">
								{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TYPE_PLACEHOLDER') }}
							</option>
							<template v-for="group in filterTypes">
								<template v-if="group.LIST">
									<optgroup :label="group.NAME">
										<option v-for="option in group.LIST" :value="option.ID">
											{{ option.NAME }}
										</option>
									</optgroup>
								</template>
								<template v-else>   
									<option :value="group.MODULE_ID">
										{{ group.NAME }}
									</option>
								</template>
							</template>
							
						</select>
					</div>
					<div class="ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-xs ui-ctl-w50"> 
						<button class="ui-ctl-after ui-ctl-icon-clear" @click.prevent="searchQuery=''"></button>
						<input
							autofocus
							type="text" 
							class="ui-ctl-element" 
							v-model="searchQuery" 
							:placeholder="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_TEXT_PLACEHOLDER')"
						>
					</div>
					<div class="ui-ctl ui-ctl-after-icon ui-ctl-before-icon ui-ctl-xs ui-ctl-w25">
						<div class="ui-ctl-before ui-ctl-icon-calendar"></div>
						<input 
							type="text" 
							class="ui-ctl-element ui-ctl-textbox" 
							v-model="searchDate"
							@focus.prevent.stop="onDateFilterClick"
							@click.prevent.stop="onDateFilterClick"
							:placeholder="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_DATE_PLACEHOLDER')"
							readonly
						>
						<button class="ui-ctl-after ui-ctl-icon-clear" @click.prevent="searchDate=''"></button>
					</div>
				</div>
			</div>
			<div 
				v-if="showSearch && (searchQuery.length >= 3 || searchType !== '' || searchDate !== '')" 
				class="bx-messenger-list-notifications-wrap"
			>
				<NotificationSearchResult :searchQuery="searchQuery" :searchType="searchType" :searchDate="searchDate"/>
			</div>
			<div v-else class="bx-messenger-list-notifications-wrap">
				<div :class="[ darkTheme ? 'bx-messenger-dark' : '', 'bx-messenger-list-notifications']" @scroll.passive="onScroll" ref="listNotifications">
					<notification-item
						v-for="listItem in visibleNotifications"
						:key="listItem.id"
						:data-id="listItem.id"
						:rawListItem="listItem"
						@dblclick="onDoubleClick"
						@buttonsClick="onButtonsClick"
						@deleteClick="onDeleteClick"
						@contentClick="onContentClick"
						v-bx-im-directive-notifications-observer="
							listItem.sectionCode !== NotificationTypesCodes.placeholder
							? ObserverType.read 
							: ObserverType.none
						"
					/>
					<div
						v-if="notification.length <= 0"
						style="padding-top: 210px; margin-bottom: 20px;"
						class="bx-messenger-box-empty bx-notifier-content-empty"
					>
						{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_NO_ITEMS') }}
					</div>
				</div>
				<!-- Scroll button -->
				<transition name="bx-im-notifications-scroll-button">
					<div v-show="showScrollButton" class="bx-im-notifications-scroll-button-box" @click="onScrollButtonClick">
						<div class="bx-im-notifications-scroll-button">
							<div class="bx-im-notifications-scroll-button-counter">
								<div class="bx-im-notifications-scroll-button-counter-digit">{{ unreadCounter }}</div>
							</div>
							<div :class="arrowButtonClass"></div>
						</div>
					</div>
				</transition>
				
				<mounting-portal :mount-to="popupIdSelector" append v-if="popupInstance">
					<popup :type="contentPopupType" :value="contentPopupValue" :popupInstance="popupInstance"/>
				</mounting-portal>
			</div>
		</div>
	`
});