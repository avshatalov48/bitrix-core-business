import 'ui.design-tokens';

import { RecentLoadingState } from 'im.v2.component.elements';
import { CopilotDraftManager } from 'im.v2.lib.draft';

import { CopilotItem } from './components/copilot-item';
import { CopilotRecentService } from './classes/copilot-service';
import { CopilotRecentMenu } from './classes/context-menu-manager';

import './css/copilot-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem } from 'im.v2.model';

// @vue/component
export const CopilotList = {
	name: 'CopilotList',
	components: { LoadingState: RecentLoadingState, CopilotItem },
	emits: ['chatClick'],
	data(): JsonObject
	{
		return {
			isLoading: false,
			isCreatingChat: false,
		};
	},
	computed:
	{
		collection(): ImModelRecentItem[]
		{
			return this.getRecentService().getCollection();
		},
		sortedItems(): ImModelRecentItem[]
		{
			return [...this.collection].sort((a, b) => {
				const firstDate = this.$store.getters['recent/getMessageDate'](a.dialogId);
				const secondDate = this.$store.getters['recent/getMessageDate'](b.dialogId);

				return secondDate - firstDate;
			});
		},
		pinnedItems(): ImModelRecentItem[]
		{
			return this.sortedItems.filter((item) => {
				return item.pinned === true;
			});
		},
		generalItems(): ImModelRecentItem[]
		{
			return this.sortedItems.filter((item) => {
				return item.pinned === false;
			});
		},
	},
	async created()
	{
		this.contextMenuManager = new CopilotRecentMenu();

		this.isLoading = true;
		await this.getRecentService().loadFirstPage();
		this.isLoading = false;
		CopilotDraftManager.getInstance().initDraftHistory();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods:
	{
		async onScroll(event)
		{
			this.contextMenuManager.close();
			if (!this.oneScreenRemaining(event) || !this.getRecentService().hasMoreItemsToLoad)
			{
				return;
			}

			this.isLoading = true;
			await this.getRecentService().loadNextPage();
			this.isLoading = false;
		},
		onClick(item, event)
		{
			this.$emit('chatClick', item.dialogId);
		},
		onRightClick(item, event)
		{
			event.preventDefault();
			this.contextMenuManager.openMenu(item, event.currentTarget);
		},
		oneScreenRemaining(event): boolean
		{
			const bottomPointOfVisibleContent = event.target.scrollTop + event.target.clientHeight;
			const containerHeight = event.target.scrollHeight;
			const oneScreenHeight = event.target.clientHeight;

			return bottomPointOfVisibleContent >= containerHeight - oneScreenHeight;
		},
		getRecentService(): CopilotRecentService
		{
			if (!this.service)
			{
				this.service = new CopilotRecentService();
			}

			return this.service;
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-copilot__scope bx-im-list-copilot__container">
			<div @scroll="onScroll" class="bx-im-list-copilot__scroll-container">
				<div v-if="pinnedItems.length > 0" class="bx-im-list-copilot__pinned_container">
					<CopilotItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-copilot__general_container">
					<CopilotItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item, $event)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>	
				<div v-if="isLoading" class="bx-im-list-copilot__loading"></div>
				<div v-else-if="collection.length === 0" class="bx-im-list-copilot__empty">
					<div class="bx-im-list-copilot__empty_icon"></div>
					<div class="bx-im-list-copilot__empty_text">{{ loc('IM_LIST_COPILOT_EMPTY') }}</div>
				</div>
			</div>
		</div>
	`,
};
