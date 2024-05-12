import { EventEmitter } from 'main.core.events';

import { Loader } from 'im.v2.component.elements';
import { EntityCreator } from 'im.v2.lib.entity-creator';
import { EventType, SidebarDetailBlock } from 'im.v2.const';

import { TaskItem } from './task-item';
import { Task } from '../../../classes/panels/task';
import { DateGroup } from '../../elements/date-group/date-group';
import { DetailHeader } from '../../elements/detail-header/detail-header';
import { DetailEmptyState } from '../../elements/detail-empty-state/detail-empty-state';
import { TaskMenu } from '../../../classes/context-menu/task/task-menu';
import { SidebarCollectionFormatter } from '../../../classes/sidebar-collection-formatter';

import './css/task-panel.css';

import type { JsonObject } from 'main.core';
import type { ImModelChat, ImModelSidebarTaskItem } from 'im.v2.model';

// @vue/component
export const TaskPanel = {
	name: 'TaskPanel',
	components: { TaskItem, DateGroup, DetailHeader, DetailEmptyState, Loader },
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
		this.contextMenu = new TaskMenu();
		this.service = new Task({ dialogId: this.dialogId });
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
			EventEmitter.emit(EventType.sidebar.close, { panel: SidebarDetailBlock.task });
		},
		needToLoadNextPage(event: Event): boolean
		{
			const target = event.target;
			const isAtThreshold = target.scrollTop + target.clientHeight >= target.scrollHeight - target.clientHeight;
			const hasNextPage = this.$store.getters['sidebar/tasks/hasNextPage'](this.chatId);

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
			(new EntityCreator(this.chatId)).createTaskForChat();
		},
	},
	template: `
		<div class="bx-im-sidebar-task-detail__scope">
			<DetailHeader
				:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASK_DETAIL_TITLE')"
				:secondLevel="secondLevel"
				:withAddButton="true"
				@addClick="onAddClick"
				@back="onBackClick"
			/>
			<div class="bx-im-sidebar-task-detail__container bx-im-sidebar-detail__container" @scroll="onScroll">
				<div v-for="dateGroup in formattedCollection" class="bx-im-sidebar-task-detail__date-group_container">
					<DateGroup :dateText="dateGroup.dateGroupTitle" />
					<TaskItem
						v-for="task in dateGroup.items"
						:task="task"
						@contextMenuClick="onContextMenuClick"
					/>
				</div>
				<DetailEmptyState
					v-if="!isLoading && isEmptyState"
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASKS_EMPTY')"
					:iconType="SidebarDetailBlock.task"
				/>
				<Loader v-if="isLoading" class="bx-im-sidebar-detail__loader-container" />
			</div>
		</div>
	`,
};
