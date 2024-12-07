import { BaseEvent } from 'main.core.events';

import { ChatType, Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { RecentService } from 'im.v2.provider.service';
import { RecentMenu } from 'im.v2.lib.menu';
import { DraftManager } from 'im.v2.lib.draft';
import { CreateChatManager } from 'im.v2.lib.create-chat';
import { ListLoadingState as LoadingState } from 'im.v2.component.elements';

import { RecentItem } from './components/recent-item/recent-item';
import { ActiveCall } from './components/active-call';
import { CreateChat } from './components/create-chat';
import { EmptyState } from './components/empty-state';

import { BroadcastManager } from './classes/broadcast-manager';
import { LikeManager } from './classes/like-manager';

import './css/recent-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelCallItem } from 'im.v2.model';

// @vue/component
export const RecentList = {
	name: 'RecentList',
	components: { LoadingState, RecentItem, ActiveCall, CreateChat, EmptyState },
	emits: ['chatClick'],
	data(): JsonObject
	{
		return {
			isLoading: false,
			isLoadingNextPage: false,
			listIsScrolled: false,
			isCreatingChat: false,
		};
	},
	computed:
	{
		collection(): ImModelRecentItem[]
		{
			return this.getRecentService().getCollection();
		},
		isEmptyCollection(): boolean
		{
			return this.collection.length === 0;
		},
		preparedItems(): ImModelRecentItem[]
		{
			const filteredCollection = this.collection.filter((item) => {
				let result = true;
				if (!this.showBirthdays && item.isBirthdayPlaceholder)
				{
					result = false;
				}

				if (item.isFakeElement && !this.isFakeItemNeeded(item))
				{
					result = false;
				}

				return result;
			});

			return [...filteredCollection].sort((a, b) => {
				const firstDate = this.$store.getters['recent/getSortDate'](a.dialogId);
				const secondDate = this.$store.getters['recent/getSortDate'](b.dialogId);

				return secondDate - firstDate;
			});
		},
		activeCalls(): ImModelCallItem[]
		{
			return this.$store.getters['recent/calls/get'];
		},
		pinnedItems(): ImModelRecentItem[]
		{
			return this.preparedItems.filter((item) => {
				return item.pinned === true;
			});
		},
		generalItems(): ImModelRecentItem[]
		{
			return this.preparedItems.filter((item) => {
				return item.pinned === false;
			});
		},
		showBirthdays(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showBirthday);
		},
		showInvited(): boolean
		{
			return this.$store.getters['application/settings/get'](Settings.recent.showInvited);
		},
		firstPageLoaded(): boolean
		{
			return this.getRecentService().firstPageIsLoaded;
		},
	},
	async created()
	{
		this.contextMenuManager = new RecentMenu();

		this.initBroadcastManager();
		this.initLikeManager();
		this.initCreateChatManager();

		this.isLoading = true;
		await this.getRecentService().loadFirstPage({ ignorePreloadedItems: true });
		this.isLoading = false;

		void DraftManager.getInstance().initDraftHistory();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
		this.destroyBroadcastManager();
		this.destroyLikeManager();
		this.destroyCreateChatManager();
	},
	methods:
	{
		async onScroll(event: Event)
		{
			this.listIsScrolled = event.target.scrollTop > 0;

			this.contextMenuManager.close();
			if (!Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad)
			{
				return;
			}

			this.isLoadingNextPage = true;
			await this.getRecentService().loadNextPage();
			this.isLoadingNextPage = false;
		},
		onClick(item)
		{
			this.$emit('chatClick', item.dialogId);
		},
		onRightClick(item, event)
		{
			if (Utils.key.isCombination(event, 'Alt+Shift'))
			{
				return;
			}

			const context = {
				...item,
				compactMode: false,
			};

			this.contextMenuManager.openMenu(context, event.currentTarget);

			event.preventDefault();
		},
		onCallClick({ item, $event })
		{
			this.onClick(item, $event);
		},
		initBroadcastManager()
		{
			this.onRecentListUpdate = (event) => {
				this.getRecentService().setPreloadedData(event.data);
			};
			this.broadcastManager = BroadcastManager.getInstance();
			this.broadcastManager.subscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
		},
		destroyBroadcastManager()
		{
			this.broadcastManager = BroadcastManager.getInstance();
			this.broadcastManager.unsubscribe(BroadcastManager.events.recentListUpdate, this.onRecentListUpdate);
		},
		initLikeManager()
		{
			this.likeManager = new LikeManager();
			this.likeManager.init();
		},
		destroyLikeManager()
		{
			this.likeManager.destroy();
		},
		initCreateChatManager()
		{
			if (CreateChatManager.getInstance().isCreating())
			{
				this.isCreatingChat = true;
			}

			this.onCreationStatusChange = (event: BaseEvent<boolean>) => {
				this.isCreatingChat = event.getData();
			};
			CreateChatManager.getInstance().subscribe(
				CreateChatManager.events.creationStatusChange,
				this.onCreationStatusChange,
			);
		},
		destroyCreateChatManager()
		{
			CreateChatManager.getInstance().unsubscribe(
				CreateChatManager.events.creationStatusChange,
				this.onCreationStatusChange,
			);
		},
		isFakeItemNeeded(item: ImModelRecentItem): boolean
		{
			const dialog = this.$store.getters['chats/get'](item.dialogId, true);
			const isUser = dialog.type === ChatType.user;
			const hasBirthday = isUser && this.showBirthdays && this.$store.getters['users/hasBirthday'](item.dialogId);

			return this.showInvited || hasBirthday;
		},
		getRecentService(): RecentService
		{
			if (!this.service)
			{
				this.service = RecentService.getInstance();
			}

			return this.service;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-recent__container">
			<div v-if="activeCalls.length > 0" class="bx-im-list-recent__calls_container" :class="{'--with-shadow': listIsScrolled}">
				<ActiveCall
					v-for="activeCall in activeCalls"
					:key="activeCall.dialogId"
					:item="activeCall"
					@click="onCallClick"
				/>
			</div>
			<CreateChat v-if="isCreatingChat"></CreateChat>
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-recent__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div v-if="pinnedItems.length > 0" class="bx-im-list-recent__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-recent__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>	
				<LoadingState v-if="isLoadingNextPage" />
			</div>
		</div>
	`,
};
