import { EventEmitter } from 'main.core.events';

import { EntityCreator } from 'im.v2.lib.entity-creator';
import { EventType, SidebarDetailBlock } from 'im.v2.const';
import { Button as MessengerButton, ButtonColor, ButtonSize } from 'im.v2.component.elements';

import { TaskMenu } from '../../../classes/context-menu/task/task-menu';
import { DetailEmptyState } from '../../elements/detail-empty-state';
import { TaskItem } from '../task/task-item';

import './css/task.css';

import type { ImModelSidebarTaskItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const TaskPreview = {
	name: 'TaskPreview',
	components: { DetailEmptyState, TaskItem, MessengerButton },
	props:
	{
		isLoading: {
			type: Boolean,
			default: false,
		},
		dialogId: {
			type: String,
			required: true,
		},
	},
	computed:
	{
		SidebarDetailBlock: () => SidebarDetailBlock,
		ButtonSize: () => ButtonSize,
		ButtonColor: () => ButtonColor,
		firstTask(): ?ImModelSidebarTaskItem
		{
			return this.$store.getters['sidebar/tasks/get'](this.chatId)[0];
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
		this.contextMenu = new TaskMenu();
	},
	beforeUnmount()
	{
		this.contextMenu.destroy();
	},
	methods:
	{
		getEntityCreator(): EntityCreator
		{
			if (!this.entityCreator)
			{
				this.entityCreator = new EntityCreator(this.chatId);
			}

			return this.entityCreator;
		},
		onAddClick()
		{
			this.getEntityCreator().createTaskForChat();
		},
		onOpenDetail()
		{
			if (!this.firstTask)
			{
				return;
			}

			EventEmitter.emit(EventType.sidebar.open, {
				panel: SidebarDetailBlock.task,
				dialogId: this.dialogId,
			});
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
		<div class="bx-im-sidebar-task-preview__scope">
			<div v-if="isLoading" class="bx-im-sidebar-task-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-task-preview__container">
				<div 
					class="bx-im-sidebar-task-preview__header_container"
					:class="[firstTask ? '--active': '']"
					@click="onOpenDetail"
				>
					<div class="bx-im-sidebar-task-preview__title">
						<span class="bx-im-sidebar-task-preview__title-text">
							{{ $Bitrix.Loc.getMessage('IM_SIDEBAR_TASK_DETAIL_TITLE') }}
						</span>
						<div v-if="firstTask" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							:text="$Bitrix.Loc.getMessage('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="ButtonColor.PrimaryLight"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddClick"
							class="bx-im-sidebar-task-preview__title-button"
						/>
					</transition>
				</div>
				<TaskItem v-if="firstTask" :task="firstTask" @contextMenuClick="onContextMenuClick"/>
				<DetailEmptyState 
					v-else 
					:title="$Bitrix.Loc.getMessage('IM_SIDEBAR_TASKS_EMPTY')"
					:iconType="SidebarDetailBlock.task"
				/>
			</div>
		</div>
	`,
};
