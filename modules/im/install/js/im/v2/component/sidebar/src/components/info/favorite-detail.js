import type {ImModelSidebarFavoriteItem} from 'im.v2.model';
import {SidebarDetailBlock} from 'im.v2.const';
import {FavoriteMenu} from '../../classes/context-menu/favorite/favorite-menu';
import {SidebarCollectionFormatter} from '../../classes/sidebar-collection-formatter';
import {SidebarDetail} from '../detail';
import {FavoriteItem} from './favorite-item';
import {DateGroup} from '../date-group';
import {DetailEmptyState} from '../detail-empty-state';
import '../../css/info/favorite-detail.css';

// @vue/component
export const FavoriteDetail = {
	name: 'FavoriteDetail',
	components: {FavoriteItem, DateGroup, DetailEmptyState, SidebarDetail},
	props: {
		dialogId: {
			type: String,
			required: true
		},
		chatId: {
			type: Number,
			required: true
		},
		service: {
			type: Object,
			required: true
		}
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
		isEmptyState()
		{
			return this.formattedCollection.length === 0;
		}
	},
	created()
	{
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new FavoriteMenu();
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
		onScroll()
		{
			this.contextMenu.destroy();
		}
	},
	template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-favorite-detail__scope bx-im-sidebar-favorite-detail__container"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<FavoriteItem 
					v-for="favorite in dateGroup.items" 
					:favorite="favorite"
					:chatId="chatId"
					:dialogId="dialogId"
					@contextMenuClick="onContextMenuClick" 
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FAVORITES_EMPTY')"
				:iconType="SidebarDetailBlock.favorite"
			/>
		</SidebarDetail>
	`
};