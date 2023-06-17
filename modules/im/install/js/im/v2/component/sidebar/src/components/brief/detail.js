import {SidebarFileTypes, SidebarDetailBlock} from 'im.v2.const';
import type {ImModelSidebarFileItem} from 'im.v2.model';
import {SidebarCollectionFormatter} from '../../classes/sidebar-collection-formatter';
import {FileMenu} from '../../classes/context-menu/file/file-menu';
import {DateGroup} from '../date-group';
import {BriefItem} from './brief-item';
import {SidebarDetail} from '../detail';
import {DetailEmptyState} from '../detail-empty-state';
import '../../css/brief/detail.css';

// @vue/component
export const BriefDetail = {
	name: 'BriefDetail',
	components: {DateGroup, BriefItem, SidebarDetail, DetailEmptyState},
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
		files(): ImModelSidebarFileItem[]
		{
			return this.$store.getters['sidebar/files/get'](this.chatId, SidebarFileTypes.brief);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.files);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		}
	},
	created()
	{
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new FileMenu();
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
				dialogId: this.dialogId
			};

			this.contextMenu.openMenu(item, target);
		}
	},
	template: `
		<SidebarDetail
			:dialogId="dialogId"
			:chatId="chatId"
			:service="service"
			@scroll="onScroll"
			v-slot="slotProps"
			class="bx-im-sidebar-brief-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle"/>
				<BriefItem
					v-for="file in dateGroup.items"
					:brief="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_BRIEFS_EMPTY')"
				:iconType="SidebarDetailBlock.brief"
			/>
		</SidebarDetail>
	`
};