import { Type, Text } from 'main.core';
import { LabelColor } from 'ui.label';

import { Utils } from 'im.v2.lib.utils';
import { ImModelSidebarTaskItem } from 'im.v2.model';
import { ChatAvatar, AvatarSize } from 'im.v2.component.elements';
import { highlightText } from 'im.v2.lib.text-highlighter';

import './css/task-item.css';

// @vue/component
export const TaskItem = {
	name: 'TaskItem',
	components: { ChatAvatar, AvatarSize },
	props: {
		task: {
			type: Object,
			required: true,
		},
		contextDialogId: {
			type: String,
			required: true,
		},
		searchQuery: {
			type: String,
			default: '',
		},
	},
	emits: ['contextMenuClick'],
	data(): { showContextButton: boolean } {
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
			if (this.searchQuery.length === 0)
			{
				return Text.encode(this.taskItem.task.title);
			}

			return highlightText(Text.encode(this.taskItem.task.title), this.searchQuery);
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
		},
	},
	methods:
	{
		onTaskClick()
		{
			BX.SidePanel.Instance.open(this.taskItem.task.source, { cacheable: false });
		},
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				task: this.taskItem,
				source: this.taskItem.task.source,
				messageId: this.taskItem.messageId,
			}, event.currentTarget);
		},
	},
	template: `
		<div 
			class="bx-im-sidebar-task-item__container bx-im-sidebar-task-item__scope" 
			:class="taskBackgroundColorClass"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-task-item__content" @click="onTaskClick">
				<div class="bx-im-sidebar-task-item__header-text" :title="taskTitle" v-html="taskTitle"></div>
				<div class="bx-im-sidebar-task-item__detail-container">
					<ChatAvatar 
						:size="AvatarSize.XS"
						:avatarDialogId="taskAuthorDialogId"
						:contextDialogId="contextDialogId"
					/>
					<div class="bx-im-sidebar-task-item__forward-small-icon bx-im-sidebar__forward-small-icon"></div>
					<ChatAvatar 
						:avatarDialogId="taskResponsibleDialogId" 
						:contextDialogId="contextDialogId" 
						:size="AvatarSize.XS" 
					/>
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
	`,
};
