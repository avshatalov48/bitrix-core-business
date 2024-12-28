import { Utils } from 'im.v2.lib.utils';
import { ListLoadingState as LoadingState } from 'im.v2.component.elements';
import { RecentItem } from 'im.v2.component.list.items.recent';

import { EmptyState } from './components/empty-state';
import { CollabService } from './classes/collab-service';
import { CollabRecentMenu } from './classes/context-menu-manager';

import './css/collab-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelMessage } from 'im.v2.model';

// @vue/component
export const CollabList = {
	name: 'CollabList',
	components: { EmptyState, LoadingState, RecentItem },
	emits: ['chatClick'],
	data(): JsonObject
	{
		return {
			isLoading: false,
			isLoadingNextPage: false,
			firstPageLoaded: false,
		};
	},
	computed:
	{
		collection(): ImModelRecentItem[]
		{
			return this.$store.getters['recent/getCollabCollection'];
		},
		preparedItems(): ImModelRecentItem[]
		{
			return [...this.collection].sort((a, b) => {
				const firstMessage: ImModelMessage = this.$store.getters['messages/getById'](a.messageId);
				const secondMessage: ImModelMessage = this.$store.getters['messages/getById'](b.messageId);

				return secondMessage.date - firstMessage.date;
			});
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
		isEmptyCollection(): boolean
		{
			return this.collection.length === 0;
		},
	},
	created()
	{
		this.contextMenuManager = new CollabRecentMenu();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	async activated()
	{
		this.isLoading = true;
		await this.getRecentService().loadFirstPage();
		this.firstPageLoaded = true;
		this.isLoading = false;
	},
	methods:
	{
		async onScroll(event: Event)
		{
			this.contextMenuManager.close();
			if (!Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad)
			{
				return;
			}

			this.isLoadingNextPage = true;
			await this.getRecentService().loadNextPage();
			this.isLoadingNextPage = false;
		},
		onClick(item: ImModelRecentItem)
		{
			this.$emit('chatClick', item.dialogId);
		},
		onRightClick(item: ImModelRecentItem, event: PointerEvent)
		{
			event.preventDefault();
			this.contextMenuManager.openMenu(item, event.currentTarget);
		},
		getRecentService(): CollabService
		{
			if (!this.service)
			{
				this.service = new CollabService();
			}

			return this.service;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-collab__container">
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-collab__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div v-if="pinnedItems.length > 0" class="bx-im-list-collab__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-collab__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<LoadingState v-if="isLoadingNextPage" />
			</div>
		</div>
	`,
};
