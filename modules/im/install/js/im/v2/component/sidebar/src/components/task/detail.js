import type {ImModelSidebarTaskItem} from 'im.v2.model';
import {SidebarDetailBlock} from 'im.v2.const';
import {SidebarCollectionFormatter} from '../../classes/sidebar-collection-formatter';
import {TaskMenu} from '../../classes/context-menu/task/task-menu';
import {SidebarDetail} from '../detail';
import {DetailEmptyState} from '../detail-empty-state';
import {DateGroup} from '../date-group';
import {TaskItem} from './task-item';
import '../../css/task/detail.css';

// @vue/component
export const TaskDetail = {
	name: 'TaskDetail',
	components: {TaskItem, DateGroup, SidebarDetail, DetailEmptyState},
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
		tasks(): ImModelSidebarTaskItem[]
		{
			return this.$store.getters['sidebar/tasks/get'](this.chatId);
		},
		formattedCollection(): Array
		{
			return this.collectionFormatter.format(this.tasks);
		},
		isEmptyState(): boolean
		{
			return this.formattedCollection.length === 0;
		},
	},
	created()
	{
		this.collectionFormatter = new SidebarCollectionFormatter();
		this.contextMenu = new TaskMenu();
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
			class="bx-im-sidebar-task-detail__scope"
		>
			<template v-for="dateGroup in formattedCollection">
				<DateGroup :dateText="dateGroup.dateGroupTitle" />
				<TaskItem
					v-for="task in dateGroup.items"
					:task="task"
					@contextMenuClick="onContextMenuClick"
				/>
			</template>
			<DetailEmptyState
				v-if="!slotProps.isLoading && isEmptyState"
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASKS_EMPTY')"
				:iconType="SidebarDetailBlock.task"
			/>
		</SidebarDetail>
	`
};