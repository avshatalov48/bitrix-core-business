import { EventEmitter } from 'main.core.events';

import { EntityCreator } from 'im.v2.lib.entity-creator';
import { EventType, Layout, SidebarDetailBlock, ChatActionType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { Button as MessengerButton, ButtonColor, ButtonSize } from 'im.v2.component.elements';

import { TaskMenu } from '../../../../classes/context-menu/task/task-menu';
import { DetailEmptyState } from '../../../elements/detail-empty-state/detail-empty-state';
import { TaskItem } from '../../task/task-item';

import '../css/task.css';

import type { ImModelSidebarTaskItem, ImModelChat } from 'im.v2.model';

// @vue/component
export const TaskPreview = {
	name: 'TaskPreview',
	components: { DetailEmptyState, TaskItem, MessengerButton },
	props:
	{
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
		showAddButton(): boolean
		{
			return PermissionManager.getInstance().canPerformAction(ChatActionType.createTask, this.dialogId);
		},
		dialog(): ImModelChat
		{
			return this.$store.getters['chats/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		isCopilotLayout(): boolean
		{
			const { name: currentLayoutName } = this.$store.getters['application/getLayout'];

			return currentLayoutName === Layout.copilot.name;
		},
		addButtonColor(): ButtonColor
		{
			if (this.isCopilotLayout)
			{
				return this.ButtonColor.Copilot;
			}

			return this.ButtonColor.PrimaryLight;
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
			return (new EntityCreator(this.chatId));
		},
		onAddClick()
		{
			void this.getEntityCreator().createTaskForChat();
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-sidebar-task-preview__scope">
			<div class="bx-im-sidebar-task-preview__container">
				<div 
					class="bx-im-sidebar-task-preview__header_container"
					:class="[firstTask ? '--active': '']"
					@click="onOpenDetail"
				>
					<div class="bx-im-sidebar-task-preview__title">
						<span class="bx-im-sidebar-task-preview__title-text">
							{{ loc('IM_SIDEBAR_TASK_DETAIL_TITLE') }}
						</span>
						<div v-if="firstTask" class="bx-im-sidebar__forward-icon"></div>
					</div>
					<transition name="add-button">
						<MessengerButton
							v-if="showAddButton"
							:text="loc('IM_SIDEBAR_ADD_BUTTON_TEXT')"
							:size="ButtonSize.S"
							:color="addButtonColor"
							:isRounded="true"
							:isUppercase="false"
							icon="plus"
							@click="onAddClick"
							class="bx-im-sidebar-task-preview__title-button"
						/>
					</transition>
				</div>
				<TaskItem 
					v-if="firstTask"
					:contextDialogId="dialogId"
					:task="firstTask" @contextMenuClick="onContextMenuClick"
				/>
				<DetailEmptyState 
					v-else 
					:title="loc('IM_SIDEBAR_TASKS_EMPTY')"
					:iconType="SidebarDetailBlock.task"
				/>
			</div>
		</div>
	`,
};
