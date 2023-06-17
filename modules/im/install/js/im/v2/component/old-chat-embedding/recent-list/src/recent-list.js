import 'ui.design-tokens';

import {EventEmitter} from 'main.core.events';
import {mapState} from 'ui.vue3.vuex';

import {DialogType, EventType, RecentSettings, OpenTarget} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';
import {RecentService} from 'im.v2.provider.service';
import {RecentLoadingState} from 'im.v2.component.old-chat-embedding.elements';
import {RecentMenu} from 'im.v2.lib.old-chat-embedding.menu';

import {RecentItem} from './components/recent-item';
import {ActiveCall} from './components/active-call';

import {SettingsManager} from './settings-manager';
import {BroadcastManager} from './broadcast-manager';
import {CallManager} from './call-manager';
import {DraftManager} from './draft-manager';
import {EventHandler} from './event-handler';

import './css/recent-list.css';

// @vue/component
export const RecentList = {
	name: 'RecentList',
	components: {LoadingState: RecentLoadingState, RecentItem, ActiveCall},
	directives:
	{
		'recent-list-observer':
		{
			mounted(element, binding)
			{
				binding.instance.observer.observe(element);
			}
		}
	},
	props: {
		compactMode: {
			type: Boolean,
			default: false
		}
	},
	data()
	{
		return {
			isLoading: false,
			visibleElements: new Set()
		};
	},
	computed:
	{
		collection()
		{
			return this.$store.getters['recent/getRecentCollection'];
		},
		sections()
		{
			return [this.pinnedItems, this.generalItems];
		},
		preparedItems()
		{
			const filteredCollection = this.collection.filter(item => {
				if (!this.showBirthdays && item.options.birthdayPlaceholder)
				{
					return false;
				}

				const dialog = this.$store.getters['dialogues/get'](item.dialogId, true);
				const isUser = dialog.type === DialogType.user;
				const hasBirthday = isUser && this.showBirthdays && this.$store.getters['users/hasBirthday'](item.dialogId);
				if (!this.showInvited && item.options.defaultUserRecord && !hasBirthday)
				{
					return false;
				}

				return true;
			});

			return [...filteredCollection].sort((a, b) => {
				const firstDate = this.$store.getters['recent/getMessageDate'](a.dialogId);
				const secondDate = this.$store.getters['recent/getMessageDate'](b.dialogId);

				return secondDate - firstDate;
			});
		},
		pinnedItems()
		{
			return this.preparedItems.filter(item => {
				return item.pinned === true;
			});
		},
		generalItems()
		{
			return this.preparedItems.filter(item => {
				return item.pinned === false;
			});
		},
		isDarkTheme()
		{
			return this.application.options.darkTheme;
		},
		showBirthdays()
		{
			return this.$store.getters['recent/getOption'](RecentSettings.showBirthday);
		},
		showInvited()
		{
			return this.$store.getters['recent/getOption'](RecentSettings.showInvited);
		},
		transitionType()
		{
			if (this.compactMode)
			{
				return '';
			}

			if (this.isLoading)
			{
				return '';
			}

			return 'bx-messenger-recent-transition';
		},
		...mapState({
			activeCalls: state => state.recent.activeCalls,
			application: state => state.application
		})
	},
	created()
	{
		this.recentService = RecentService.getInstance();
		this.contextMenuManager = new RecentMenu(this.$Bitrix);

		CallManager.init(this.$Bitrix);
		EventHandler.init(this.$Bitrix);
		SettingsManager.init(this.$Bitrix);
		this.initBroadcastManager();
		this.initObserver();

		this.managePreloadedList();
		this.manageChatOptions();
	},
	mounted()
	{
		this.isLoading = true;
		this.recentService.loadFirstPage().then(() => {
			this.isLoading = false;
			DraftManager.init(this.$Bitrix);
		});
		this.initBirthdayCheck();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
		this.clearBirthdayCheck();
		this.destroyBroadcastManager();
	},
	methods:
	{
		onScroll(event)
		{
			this.contextMenuManager.close();
			if (!this.oneScreenRemaining(event) || !this.recentService.hasMoreItemsToLoad)
			{
				return false;
			}

			this.isLoading = true;
			this.recentService.loadNextPage().then(() => {
				this.isLoading = false;
			});
		},

		onClick(item, event)
		{
			const target = !this.compactMode || event.altKey? OpenTarget.current: OpenTarget.auto;

			EventEmitter.emit(EventType.dialog.open, {
				...item,
				chat: this.$store.getters['dialogues/get'](item.dialogId, true),
				user: this.$store.getters['users/get'](item.dialogId, true),
				target
			});
		},

		onRightClick(item, event)
		{
			if (event.altKey && event.shiftKey)
			{
				return;
			}

			const target = !this.compactMode || event.altKey? OpenTarget.current: OpenTarget.auto;
			const context = {
				...item,
				compactMode: this.compactMode,
				target
			};

			this.contextMenuManager.openMenu(context, event.currentTarget);

			event.preventDefault();
		},

		onCallClick({item, $event})
		{
			this.onClick(item, $event);
		},

		onCallRightClick({item, $event})
		{
			this.onRightClick(item, $event);
		},

		oneScreenRemaining(event)
		{
			return event.target.scrollTop + event.target.clientHeight >= event.target.scrollHeight - event.target.clientHeight;
		},

		initObserver()
		{
			this.observer = new IntersectionObserver(((entries) => {
				entries.forEach(entry => {
					if (entry.isIntersecting && entry.intersectionRatio === 1)
					{
						this.visibleElements.add(entry.target.dataset.id);
					}
					else if (!entry.isIntersecting)
					{
						this.visibleElements.delete(entry.target.dataset.id);
					}
				});
			}), {threshold: [0, 1]});
		},

		initBroadcastManager()
		{
			this.onRecentListUpdate = (event) => {
				this.recentService.setPreloadedData(event.data);
			};
			this.broadcastManager = BroadcastManager.getInstance();
			this.broadcastManager.subscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
		},

		destroyBroadcastManager()
		{
			this.broadcastManager = BroadcastManager.getInstance();
			this.broadcastManager.unsubscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
		},

		initBirthdayCheck()
		{
			const fourHours = 60000*60*4;
			const day = 60000*60*24;
			this.birthdayCheckTimeout = setTimeout(() => {
				this.recentService.loadFirstPage({ignorePreloadedItems: true});
				this.birthdayCheckInterval = setInterval(() => {
					this.recentService.loadFirstPage({ignorePreloadedItems: true});
				}, day);
			}, Utils.date.getTimeToNextMidnight() + fourHours);
		},

		clearBirthdayCheck()
		{
			clearTimeout(this.birthdayCheckTimeout);
			clearInterval(this.birthdayCheckInterval);
		},

		managePreloadedList()
		{
			const {preloadedList} = this.$Bitrix.Application.get().params;
			if (!preloadedList)
			{
				return false;
			}

			this.recentService.setPreloadedData(preloadedList);
			this.broadcastManager.sendRecentList(preloadedList);
		},

		manageChatOptions()
		{
			const {chatOptions} = this.$Bitrix.Application.get().params;
			if (!chatOptions)
			{
				return false;
			}

			this.$store.dispatch('dialogues/setChatOptions', chatOptions);
		}
	},
	template: `
		<div @scroll="onScroll" class="bx-messenger-recent-list" :class="{'bx-messenger-recent-compact': compactMode}" >
			<transition-group :name="transitionType">
				<ActiveCall
					v-for="activeCall in activeCalls"
					:key="'call-' + activeCall.dialogId"
					:item="activeCall"
					:compactMode="compactMode"
					@click="onCallClick"
					@click.right="onCallRightClick"
				/>
				<template v-for="section in sections">
					<RecentItem
						v-for="item in section"
						:key="item.dialogId"
						:item="item"
						:compactMode="compactMode"
						:isVisibleOnScreen="visibleElements.has(item.dialogId)"
						v-recent-list-observer
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</template>
			</transition-group>
			<LoadingState v-if="isLoading" :compactMode="compactMode" />
			<template v-if="collection.length === 0">
				<div class="bx-im-recent-empty">{{ $Bitrix.Loc.getMessage('IM_RECENT_EMPTY') }}</div>
			</template>
		</div>
	`
};