import { EventEmitter } from 'main.core.events';

import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';

import { FavoriteMenu } from '../../../classes/context-menu/favorite/favorite-menu';
import { Favorite } from '../../../classes/panels/favorite';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { FavoriteItem } from './favorite-item';
import { DateGroup } from '../../elements/date-group/date-group';
import { DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';

import './css/favorite-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarFavoriteItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const FavoritePanel = {
	name: 'FavoritePanel',
	components: { FavoriteItem, DateGroup, DetailEmptyState, DetailHeader, Loader },
	props:
	{
		dialogId: {
			type: String,
			required: true,
		},
		secondLevel: {
			type: Boolean,
			default: false,
		},
	},
	data(): JsonObject
	{
		return {
			isLoading: false,
		};
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		favorites(): ImModelSidebarFavoriteItem[]
		{
			return this.$store.getters['sidebar/favorites/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.favorites);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
	},
	created()
	{
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new FavoriteMenu();
		this.service = new Favorite({ dialogId: this.dialogId });
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
		this.collectionFormatter.destroy();
	},
	methods:
	{
		onContextMenuClick(event)
		{
			const item = {
				id: event.id,
				messageId: event.messageId,
				dialogId: this.dialogId,
				chatId: this.chatId,
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.favorite });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const hasNextPage = this.$store.getters['sidebar/favorites/hasNextPage'](this.chatId);

			return isAtThreshold && hasNextPage;
		},
		async onScroll(event: Event)
		{
			this.contextMenu.destroy();

			if (this.isLoading || !this.needToLoadNextPage(event))
			{
				return;
			}

			this.isLoading = true;
			await this.service.loadNextPage();
			this.isLoading = false;
		},
	},
	template: `
		<div class="bx-im-sidebar-favorite-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITE_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-favorite-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div 
					v-for="dateGroup in formattedCollection" 
					class="bx-im-sidebar-favorite-detail__date-group_container"
				>
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<FavoriteItem 
						v-for="favorite in dateGroup.items" 
						:favorite="favorite"
						:chatId="chatId"
						:dialogId="dialogId"
						@contextMenuClick="onContextMenuClick" 
					/>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITES_EMPTY')"
					:iconType="SidebarDetailBlock.favorite"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
