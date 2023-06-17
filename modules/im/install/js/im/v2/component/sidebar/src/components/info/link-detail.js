import type {ImModelSidebarLinkItem} from 'im.v2.model';
import {SidebarDetailBlock} from 'im.v2.const';
import {SidebarCollectionFormatter} from '../../classes/sidebar-collection-formatter';
import {LinkMenu} from '../../classes/context-menu/link/link-menu';
import {SidebarDetail} from '../detail';
import {LinkItem} from './link-item';
import {DateGroup} from '../date-group';
import {DetailEmptyState} from '../detail-empty-state';
import '../../css/info/link-detail.css';

// @vue/component
export const LinkDetail = {
	name: 'LinkDetail',
	components: {LinkItem, SidebarDetail, DateGroup, DetailEmptyState},
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
		},
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		links(): ImModelSidebarLinkItem[]
		{
			return this.$store.getters['sidebar/links/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.links);
		},
		isEmptyState()
		{
			return this.formattedCollection.length === 0;
		}
	},
	created()
	{
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new LinkMenu();
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
				source: event.source,
			};

			this.contextMenu.openMenu(item, event.target);
		},
		onScroll()
		{
			this.contextMenu.destroy();
		},
	},
	template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-link-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<template v-for="link in dateGroup.items">
					<LinkItem :link="link" @contextMenuClick="onContextMenuClick" />
				</template>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_EMPTY')"
				:iconType="SidebarDetailBlock.link"
			/>
		</SidebarDetail>
	`
};