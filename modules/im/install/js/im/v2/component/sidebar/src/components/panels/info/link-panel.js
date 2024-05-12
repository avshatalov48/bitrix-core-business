import { EventEmitter } from 'main.core.events';

import { Loader } from 'im.v2.component.elements';
import { EventType, SidebarDetailBlock } from 'im.v2.const';

import { LinkItem } from './link-item';
import { Link } from '../../../classes/panels/link';
import { DateGroup } from '../../elements/date-group/date-group';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';
import { LinkMenu } from '../../../classes/context-menu/link/link-menu';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';

import './css/link-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelSidebarLinkItem } from 'im.v2.model';

// @vue/component
export const LinkPanel = {
	name: 'LinkPanel',
	components: { DetailHeader, LinkItem, DateGroup, DetailEmptyState, Loader },
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
		links(): ImModelSidebarLinkItem[]
		{
			return this.$store.getters['sidebar/links/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.links);
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
		this.contextMenu = new LinkMenu();
		this.service = new Link({ dialogId: this.dialogId });
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
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.link });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;

			const hasNextPage = this.$store.getters['sidebar/links/hasNextPage'](this.chatId);

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
		<div class="bx-im-sidebar-link-detail__scope">
			<DetailHeader
				:dialogId="dialogId"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_LINK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-link-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<template v-for="link in dateGroup.items">
						<LinkItem :link="link" @contextMenuClick="onContextMenuClick" />
					</template>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_LINKS_EMPTY')"
					:iconType="SidebarDetailBlock.link"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
