import {SidebarDetailBlock, SidebarFileTypes} from 'im.v2.const';
import {SidebarCollectionFormatter} from '../../classes/sidebar-collection-formatter';
import {FileMenu} from '../../classes/context-menu/file/file-menu';
import {SidebarDetail} from '../detail';
import {DateGroup} from '../date-group';
import {DetailEmptyState} from '../detail-empty-state';
import {AudioDetailItem} from './item/audio-detail-item';
import '../../css/file/audio-detail.css';

import type {ImModelSidebarFileItem} from 'im.v2.model';

// @vue/component
export const AudioDetail = {
	name: 'AudioDetail',
	components: {DetailEmptyState, AudioDetailItem, DateGroup, SidebarDetail},
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
			return this.$store.getters['sidebar/files/get'](this.chatId, SidebarFileTypes.audio);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.files);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		},
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
			class="bx-im-sidebar-file-audio-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<AudioDetailItem
					v-for="file in dateGroup.items"
					:id="file.id"
					:fileItem="file"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_FILES_EMPTY')"
				:iconType="SidebarDetailBlock.audio"
			/>
		</SidebarDetail>
	`
};