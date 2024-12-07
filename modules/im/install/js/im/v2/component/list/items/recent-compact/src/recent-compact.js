import { Core } from 'im.v2.application.core';
import { ChatType, Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { RecentService } from 'im.v2.provider.service';
import { RecentMenu } from 'im.v2.lib.menu';
import { Messenger } from 'im.public';
import 'im.v2.css.tokens';

import { RecentItem } from './components/recent-item';
import { ActiveCall } from './components/active-call';
import { EmptyState } from './components/empty-state';

import './css/recent-list.css';

import type { JsonObject } from 'main.core';
import type { ImModelRecentItem, ImModelCallItem } from 'im.v2.model';

// @vue/component
export const RecentList = {
	name: 'RecentList',
	components: { RecentItem, ActiveCall, EmptyState },
	emits: ['chatClick'],
	data(): JsonObject
	{
		return {};
	},
	computed:
	{
		collection(): ImModelRecentItem[]
		{
			return this.getRecentService().getCollection();
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
	},
	async created()
	{
		this.contextMenuManager = new RecentMenu();

		this.managePreloadedList();

		await this.getRecentService().loadFirstPage();
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods:
	{
		onClick(item)
		{
			Messenger.openChat(item.dialogId);
		},
		onRightClick(item, event)
		{
			if (Utils.key.isCombination(event, 'Alt+Shift'))
			{
				return;
			}

			const context = {
				...item,
				compactMode: true,
			};

			this.contextMenuManager.openMenu(context, event.currentTarget);

			event.preventDefault();
		},
		managePreloadedList()
		{
			const { preloadedList } = Core.getApplicationData();
			if (!preloadedList)
			{
				return;
			}

			this.getRecentService().setPreloadedData(preloadedList);
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
		<div class="bx-im-messenger__scope bx-im-list-recent-compact__container">
			<div v-if="activeCalls.length > 0" class="bx-im-list-recent-compact__calls_container">
				<ActiveCall
					v-for="activeCall in activeCalls"
					:key="activeCall.dialogId"
					:item="activeCall"
					@click="onClick"
				/>
			</div>
			<div class="bx-im-list-recent-compact__scroll-container">
				<div v-if="pinnedItems.length > 0" class="bx-im-list-recent-compact__pinned_container">
					<RecentItem
						v-for="item in pinnedItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>
				<div class="bx-im-list-recent-compact__general_container">
					<RecentItem
						v-for="item in generalItems"
						:key="item.dialogId"
						:item="item"
						@click="onClick(item)"
						@click.right="onRightClick(item, $event)"
					/>
				</div>	
				<EmptyState v-if="collection.length === 0" />
			</div>
		</div>
	`,
};
