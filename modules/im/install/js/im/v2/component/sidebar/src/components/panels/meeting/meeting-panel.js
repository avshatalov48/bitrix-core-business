import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { Loader } from 'im.v2.component.elements';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { EventEmitter } from 'main.core.events';

import { MeetingItem } from './meeting-item';
import { DateGroup } from '../../elements/date-group';
import { DetailHeader } from '../../elements/detail-header';
import { DetailEmptyState } from '../../elements/detail-empty-state';
import { MeetingMenu } from '../../../classes/context-menu/meeting/meeting-menu';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';

import './css/meeting-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelSidebarMeetingItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const MeetingPanel = {
	name: 'MeetingPanel',
	components: { MeetingItem, DateGroup, DetailEmptyState, DetailHeader, Loader },
	props: {
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
		meetings(): ImModelSidebarMeetingItem[]
		{
			return this.$store.getters['sidebar/meetings/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.meetings);
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
		this.contextMenu = new MeetingMenu();
	},
	beforeUnmount()
	{
		this.collectionFormatter.destroy();
		this.contextMenu.destroy();
	},
	methods:
	{
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId,
			};

			this.contextMenu.openMenu(item, target);
		},
		onBackClick()
		{
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.meeting });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const hasNextPage = this.$store.getters['sidebar/meetings/hasNextPage'](this.chatId);

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
		onAddClick()
		{
			(new EntityCreator(this.chatId)).createMeetingForChat();
		},
	},
	template: `
		<div class="bx-im-sidebar-meeting-detail__scope">
			<DetailHeader
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETING_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="true"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-meeting-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-meeting-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<MeetingItem
						v-for="meeting in dateGroup.items"
						:meeting="meeting"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETINGS_EMPTY')"
					:iconType="SidebarDetailBlock.meeting"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
