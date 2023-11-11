import { SidebarDetailBlock } from 'im.v2.const';

import { SidebarCollectionFormatter } from '../../classes/sidebar-collection-formatter';
import { MeetingMenu } from '../../classes/context-menu/meeting/meeting-menu';
import { SidebarDetail } from '../detail';
import { DateGroup } from '../date-group';
import { MeetingItem } from './meeting-item';
import { DetailEmptyState } from '../detail-empty-state';

import '../../css/meeting/detail.css';

import type { ImModelSidebarTaskItem } from 'im.v2.model';

// @vue/component
export const MeetingDetail = {
	name: 'MeetingDetail',
	components: { MeetingItem, DateGroup, SidebarDetail, DetailEmptyState },
	props: {
		dialogId: {
			type: String,
			required: true,
		},
		chatId: {
			type: Number,
			required: true,
		},
		service: {
			type: Object,
			required: true,
		},
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		meetings(): ImModelSidebarTaskItem[]
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
		onScroll()
		{
			this.contextMenu.destroy();
		},
		onContextMenuClick(event, target)
		{
			const item = {
				...event,
				dialogId: this.dialogId,
			};

			this.contextMenu.openMenu(item, target);
		},
	},
	template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-meeting-detail__scope"
		>
			<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-meeting-detail__date-group_container">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<MeetingItem
					v-for="meeting in dateGroup.items"
					:meeting="meeting"
					@contextMenuClick="onContextMenuClick"
				/>
			</div>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_MEETINGS_EMPTY')"
				:iconType="SidebarDetailBlock.meeting"
			/>
		</SidebarDetail>
	`,
};
