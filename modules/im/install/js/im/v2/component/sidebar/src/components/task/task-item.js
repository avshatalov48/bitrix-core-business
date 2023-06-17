import {ImModelSidebarTaskItem} from 'im.v2.model';
import {Avatar, AvatarSize} from 'im.v2.component.elements';
import {Type} from 'main.core';
import {LabelColor} from 'ui.label';
import '../../css/task/task-item.css';
import {Utils} from 'im.v2.lib.utils';

// @vue/component
export const TaskItem = {
	name: 'TaskItem',
	components: {Avatar, AvatarSize},
	props: {
		task: {
			type: Object,
			required: true
		}
	},
	emits: ['contextMenuClick'],
	data() {
		return {
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		taskItem(): ImModelSidebarTaskItem
		{
			return this.task;
		},
		taskTitle(): string
		{
			return this.taskItem.task.title;
		},
		taskAuthorDialogId(): string
		{
			return this.taskItem.task.creatorId.toString();
		},
		taskResponsibleDialogId(): string
		{
			return this.taskItem.task.responsibleId.toString();
		},
		taskDeadlineText(): string
		{
			const statusToShow = Type.isStringFilled(this.taskItem.task.state)
				? this.taskItem.task.state
				: this.taskItem.task.statusTitle
			;

			return Utils.text.convertHtmlEntities(statusToShow);
		},
		taskBackgroundColorClass(): string
		{
			if (this.taskItem.task.status === 5)
			{
				return '--completed';
			}

			return '';
		},
		statusColorClass(): string
		{
			if (!this.taskItem.task.color || !LabelColor[this.taskItem.task.color.toUpperCase()])
			{
				return '';
			}

			return `ui-label-${this.taskItem.task.color.toLowerCase()}`;
		}
	},
	methods:
	{
		onTaskClick()
		{
			BX.SidePanel.Instance.open(this.taskItem.task.source, {cacheable: false});
		},
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				task: this.taskItem,
				source: this.taskItem.task.source,
				messageId: this.taskItem.messageId,
			}, event.currentTarget);
		}
	},
	template: `
		<div 
			class="bx-im-sidebar-task-item__container bx-im-sidebar-task-item__scope" 
			:class="taskBackgroundColorClass"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-task-item__content" @click="onTaskClick">
				<div class="bx-im-sidebar-task-item__header-text">
					{{ taskTitle }}
				</div>
				<div class="bx-im-sidebar-task-item__detail-container">
					<Avatar :size="AvatarSize.XS" :dialogId="taskAuthorDialogId" />
					<div class="bx-im-sidebar-task-item__forward-small-icon bx-im-sidebar__forward-small-icon"></div>
					<Avatar :size="AvatarSize.XS" :dialogId="taskResponsibleDialogId" />
					<div class="bx-im-sidebar-task-item__status-text" :class="statusColorClass">
						{{taskDeadlineText}}
					</div>
				</div>
			</div>
			<button 
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon" 
				@click="onContextMenuClick"
			></button>
		</div>
	`
};