import {Event, Runtime, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import 'main.polyfill.intersectionobserver';
import {mapState} from 'ui.vue3.vuex';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';

import {NotificationTypesCodes, EventType} from 'im.v2.const';
import {NotificationService} from 'im.v2.provider.service';
import {Logger} from 'im.v2.lib.logger';
import {ChatInfoPopup, UserListPopup, Loader} from 'im.v2.component.elements';

import {NotificationItem} from './components/notification-item';
import {NotificationPlaceholder} from './components/notification-placeholder';
import {NotificationSearchPanel} from './components/notification-search-panel';
import {NotificationScrollButton} from './components/notification-scroll-button';
import {NotificationSearchService} from './classes/notification-search-service';
import {NotificationReadService} from './classes/notification-read-service';

import './css/notification-content.css';
import type {ImModelNotification} from 'im.v2.model';

// @vue/component
export const NotificationContent = {
	name: 'NotificationContent',
	components:
	{
		NotificationItem,
		NotificationSearchPanel,
		NotificationPlaceholder,
		NotificationScrollButton,
		ChatInfoPopup,
		UserListPopup,
		Loader
	},
	directives:
	{
		'notifications-item-observer':
		{
			mounted(element, binding)
			{
				binding.instance.observer.observe(element);
			},
			beforeUnmount(element, binding)
			{
				binding.instance.observer.unobserve(element);
			}
		}
	},
	data: function()
	{
		return {
			isInitialLoading: false,
			isNextPageLoading: false,
			notificationsOnScreen: new Set(),

			windowFocused: false,
			showSearchPanel: false,
			showSearchResult: false,

			popupBindElement: null,
			showChatInfoPopup: false,
			chatInfoDialogId: null,
			showUserListPopup: false,
			userListIds: null,

			schema: {}
		};
	},
	computed:
	{
		NotificationTypesCodes: () => NotificationTypesCodes,
		notificationCollection(): ImModelNotification[]
		{
			return this.$store.getters['notifications/getSortedCollection'];
		},
		searchResultCollection(): ImModelNotification[]
		{
			return this.$store.getters['notifications/getSearchResultCollection'];
		},
		notifications(): ImModelNotification[]
		{
			if (this.showSearchResult)
			{
				return this.searchResultCollection;
			}

			return this.notificationCollection;
		},
		isReadAllAvailable(): boolean
		{
			if (this.showSearchResult)
			{
				return false;
			}

			return this.unreadCounter > 0;
		},
		isEmptyState(): boolean
		{
			return this.notifications.length === 0 && !this.isInitialLoading && !this.isNextPageLoading;
		},
		emptyStateIcon(): string
		{
			return this.showSearchResult
				? 'bx-im-content-notification__not-found-icon'
				: 'bx-im-content-notification__empty-state-icon'
			;
		},
		emptyStateTitle(): string
		{
			return this.showSearchResult
				? this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_RESULTS_NOT_FOUND')
				: this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_NO_ITEMS')
			;
		},
		...mapState({
			unreadCounter: state => state.notifications.unreadCounter,
		})
	},
	watch:
	{
		showSearchPanel(newValue: boolean, oldValue: boolean)
		{
			if (newValue === false && oldValue === true)
			{
				this.showSearchResult = false;
				this.$store.dispatch('notifications/clearSearchResult');
			}
		}
	},
	created()
	{
		this.notificationService = new NotificationService();
		this.notificationSearchService = new NotificationSearchService();
		this.notificationReadService = new NotificationReadService();
		this.searchOnServerDelayed = Runtime.debounce(this.searchOnServer, 1500, this);

		Event.bind(window, 'focus', this.onWindowFocus);
		Event.bind(window, 'blur', this.onWindowBlur);

		this.initObserver();
	},
	mounted()
	{
		this.isInitialLoading = true;
		this.windowFocused = document.hasFocus();
		this.notificationService.loadFirstPage().then(response => {
			this.schema = response;
			this.isInitialLoading = false;
		});

		EventEmitter.subscribe(EventType.mention.openChatInfo, this.onOpenChatInfo);
	},
	beforeUnmount()
	{
		this.notificationService.destroy();
		this.notificationSearchService.destroy();
		this.notificationReadService.destroy();
		EventEmitter.unsubscribe(EventType.mention.openChatInfo, this.onOpenChatInfo);
		Event.unbind(window, 'focus', this.onWindowFocus);
		Event.unbind(window, 'blur', this.onWindowBlur);
	},
	methods:
	{
		initObserver()
		{
			this.observer = new IntersectionObserver(entries => {
				entries.forEach(entry => {
					const notificationId = Number.parseInt(entry.target.dataset.id, 10);
					if (!entry.isIntersecting)
					{
						this.notificationsOnScreen.delete(notificationId);

						return;
					}

					if (
						entry.intersectionRatio >= 0.7
						|| (entry.intersectionRatio > 0 && entry.intersectionRect.height > entry.rootBounds.height / 2)
					)
					{
						this.read(notificationId);
						this.notificationsOnScreen.add(notificationId);
					}
					else
					{
						this.notificationsOnScreen.delete(notificationId);
					}
				});
			}, {
				root: this.$refs['listNotifications'],
				threshold: Array.from({length: 101}).fill(0).map((zero, index) => index * 0.01)
			});
		},
		read(notificationIds: number | number[])
		{
			if (!this.windowFocused)
			{
				return;
			}

			if (Type.isNumber(notificationIds))
			{
				notificationIds = [notificationIds];
			}

			this.notificationReadService.addToReadQueue(notificationIds);
			this.notificationReadService.read();
		},
		oneScreenRemaining(event): boolean
		{
			return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
		},
		searchOnServer(event)
		{
			this.notificationSearchService.loadFirstPage(event).then(result => {
				this.isNextPageLoading = false;
				this.setSearchResult(result);
			});
		},
		setSearchResult(items: Array)
		{
			this.$store.dispatch('notifications/setSearchResult', {
				notifications: items,
			});
		},

		//events
		onScrollButtonClick(offset)
		{
			this.$refs['listNotifications'].scroll({
				top: offset,
				behavior: 'smooth'
			});
		},
		onScroll(event)
		{
			this.showChatInfoPopup = false;
			this.showUserListPopup = false;

			if (this.showSearchResult)
			{
				this.onScrollSearchResult(event);
			}
			else
			{
				this.onScrollNotifications(event);
			}
		},
		onClickReadAll()
		{
			const messageBox = new MessageBox({
				message: this.$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_READ_ALL_WARNING_POPUP'),
				buttons: MessageBoxButtons.YES_CANCEL,
				onYes: () => {
					this.notificationReadService.readAll();
					messageBox.close();
				},
				onCancel: () => {
					messageBox.close();
				}
			});
			messageBox.show();
		},
		onScrollNotifications(event)
		{
			if (
				!this.oneScreenRemaining(event)
				|| !this.notificationService.hasMoreItemsToLoad
				|| this.isInitialLoading
				|| this.isNextPageLoading
			)
			{
				return;
			}

			this.isNextPageLoading = true;
			this.notificationService.loadNextPage().then(() => {
				this.isNextPageLoading = false;
			});
		},
		onScrollSearchResult(event)
		{
			if (
				!this.oneScreenRemaining(event)
				|| !this.notificationSearchService.hasMoreItemsToLoad
				|| this.isInitialLoading
				|| this.isNextPageLoading
			)
			{
				return;
			}

			this.isNextPageLoading = true;
			this.notificationSearchService.loadNextPage().then(result => {
				this.isNextPageLoading = false;
				this.setSearchResult(result);
			});
		},
		onDoubleClick(notificationId: number)
		{
			if (this.showSearchResult)
			{
				return;
			}

			this.notificationReadService.changeReadStatus(notificationId);
		},
		onConfirmButtonsClick(button: { id: string, value: string})
		{
			const {id, value} = button;
			const notificationId = Number.parseInt(id, 10);

			this.notificationsOnScreen.delete(notificationId);
			this.notificationService.sendConfirmAction(notificationId, value);
		},
		onDeleteClick(notificationId: number)
		{
			this.notificationsOnScreen.delete(notificationId);
			this.notificationService.delete(notificationId);
		},
		onOpenChatInfo(event: BaseEvent)
		{
			const {dialogId, event: $event} = event.getData();
			this.popupBindElement = $event.target;
			this.chatInfoDialogId = dialogId;
			this.showChatInfoPopup = true;
		},
		onMoreUsersClick(event)
		{
			Logger.warn('onMoreUsersClick', event);
			this.popupBindElement = event.event.target;
			this.userListIds = event.users;
			this.showUserListPopup = true;
		},
		onSearch(event)
		{
			if (event.searchQuery.length < 3 && event.searchType === '' && event.searchDate === '')
			{
				this.showSearchResult = false;

				return;
			}

			this.showSearchResult = true;
			const localResult = this.notificationSearchService.searchInModel(event);
			this.$store.dispatch('notifications/clearSearchResult');
			this.$store.dispatch('notifications/setSearchResult', {notifications: localResult, skipValidation: true});

			this.isNextPageLoading = true;
			this.searchOnServerDelayed(event);
		},
		onSendQuickAnswer(event)
		{
			this.notificationService.sendQuickAnswer(event);
		},
		onWindowFocus()
		{
			this.windowFocused = true;
			this.read([...this.notificationsOnScreen]);
		},
		onWindowBlur()
		{
			this.windowFocused = false;
		}
	},
	template: `
	<div class="bx-im-content-notification__container">
		<div class="bx-im-content-notification__header-container">
			<div class="bx-im-content-notification__header">
				<div class="bx-im-content-notification__header-panel-container">
					<div class="bx-im-content-notification__panel-title_icon"></div>
					<div class="bx-im-content-notification__panel_text">
						{{ $Bitrix.Loc.getMessage('IM_NOTIFICATIONS_HEADER') }}
					</div>
				</div>
				<div v-if="notificationCollection.length > 0" class="bx-im-content-notification__header-buttons-container">
					<transition name="notifications-read-all-fade">
						<div
							v-if="isReadAllAvailable"
							class="bx-im-content-notification__header_button bx-im-content-notification__header_read-all-button"
							@click="onClickReadAll"
							:title="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_READ_ALL_BUTTON')"
						></div>
					</transition>
					<div
						class="bx-im-content-notification__header_button bx-im-content-notification__header_filter-button"
						:class="[showSearchPanel ? '--active' : '']"
						@click="showSearchPanel = !showSearchPanel"
						:title="$Bitrix.Loc.getMessage('IM_NOTIFICATIONS_SEARCH_FILTER_OPEN_BUTTON')"
					></div>
				</div>
			</div>
			<NotificationSearchPanel v-if="showSearchPanel" :schema="schema" @search="onSearch" />
		</div>
		<div class="bx-im-content-notification__elements-container">
			<div class="bx-im-content-notification__elements" @scroll.passive="onScroll" ref="listNotifications">
				<NotificationItem
					v-for="notification in notifications"
					:key="notification.id"
					:data-id="notification.id"
					:notification="notification"
					@dblclick="onDoubleClick"
					@confirmButtonsClick="onConfirmButtonsClick"
					@deleteClick="onDeleteClick"
					@moreUsersClick="onMoreUsersClick"
					@sendQuickAnswer="onSendQuickAnswer"
					v-notifications-item-observer
				/>
				<div v-if="isEmptyState" class="bx-im-content-notification__empty-state-container">
					<div :class="emptyStateIcon"></div>
					<span class="bx-im-content-notification__empty-state-title">
						{{ emptyStateTitle }}
					</span>
				</div>
				<NotificationPlaceholder v-if="isInitialLoading" />
				<div v-if="isNextPageLoading" class="bx-im-content-notification__loader-container">
					<Loader />
				</div>
			</div>
			<NotificationScrollButton
				v-if="!isInitialLoading || !isNextPageLoading"
				:unreadCounter="unreadCounter"
				:notificationsOnScreen="notificationsOnScreen"
				@scrollButtonClick="onScrollButtonClick"
			/>
			<ChatInfoPopup
				v-if="showChatInfoPopup"
				:dialogId="chatInfoDialogId"
				:bindElement="popupBindElement"
				:showPopup="showChatInfoPopup"
				@close="showChatInfoPopup = false"
			/>
			<UserListPopup
				v-if="showUserListPopup"
				:userIds="userListIds"
				:bindElement="popupBindElement"
				:showPopup="showUserListPopup"
				@close="showUserListPopup = false"
			/>
		</div>
	</div>
`
};