import { DraftManager } from 'im.v2.lib.draft';
import { Utils } from 'im.v2.lib.utils';
import { ListLoadingState as LoadingState } from 'im.v2.component.elements.list-loading-state';
import { RecentItem } from 'im.v2.component.list.items.recent';

import { EmptyState } from './components/empty-state';
import { TaskService } from './classes/task-service';
import { TaskRecentMenu } from './classes/context-menu-manager';

import './css/task-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem } from 'im.v2.model';

// @vue/component
export const TaskList = {
	name: 'TaskList',
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
	computed: {
		collection(): ImModelRecentItem[]
		{
			return this.$store.getters['recent/getTaskCollection'];
		},
		preparedItems(): ImModelRecentItem[]
		{
			return [...this.collection].sort((a, b) => {
				const firstDate: Date = this.$store.getters['recent/getSortDate'](a.dialogId);
				const secondDate: Date = this.$store.getters['recent/getSortDate'](b.dialogId);

				return secondDate - firstDate;
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
		this.contextMenuManager = new TaskRecentMenu();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	async activated()
	{
		this.isLoading = true;
		await this.getRecentService().loadFirstPage();
		if (!this.firstPageLoaded)
		{
			void DraftManager.getInstance().initDraftHistory();
		}
		this.firstPageLoaded = true;
		this.isLoading = false;
	},
	methods: {
		async onScroll(event: Event)
		{
			this.contextMenuManager.close();
			if (!Utils.dom.isOneScreenRemaining(event.target) || !this.getRecentService().hasMoreItemsToLoad())
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

			const context = {
				dialogId: item.dialogId,
				recentItem: item,
			};
			this.contextMenuManager.openMenu(context, event.currentTarget);
		},
		getRecentService(): TaskService
		{
			if (!this.service)
			{
				this.service = new TaskService();
			}

			return this.service;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-task__container">
			<LoadingState v-if="isLoading && !firstPageLoaded" />
			<div v-else @scroll="onScroll" class="bx-im-list-task__scroll-container">
				<EmptyState v-if="isEmptyCollection" />
				<div v-if="pinnedItems.length > 0" class="bx-im-list-task__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-task__general_container">
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
