import {ImModelSidebarTaskItem, ImModelDialog} from 'im.v2.model';
import {EntityCreator} from 'im.v2.lib.entity-creator';
import {Button, ButtonColor, ButtonSize} from 'im.v2.component.elements';
import {SidebarBlock, SidebarDetailBlock} from 'im.v2.const';
import {TaskMenu} from '../../classes/context-menu/task/task-menu';
import {DetailEmptyState} from '../detail-empty-state';
import {TaskItem} from './task-item';
import '../../css/task/preview.css';

// @vue/component
export const TaskPreview = {
	name: 'TaskPreview',
	components: {DetailEmptyState, TaskItem, Button},
	props: {
		isLoading: {
			type: Boolean,
			default: false
		},
		dialogId: {
			type: String,
			required: true
		}
	},
	emits: ['openDetail'],
	data() {
		return {
			showAddButton: false
		};
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
		dialog(): ImModelDialog
		{
			return this.$store.getters['dialogues/get'](this.dialogId, true);
		},
		chatId(): number
		{
			return this.dialog.chatId;
		},
		dialogInited()
		{
			return this.dialog.inited;
		},
		isLoadingState(): boolean
		{
			return !this.dialogInited || this.isLoading;
		}
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

			this.$emit('openDetail', {block: SidebarBlock.task, detailBlock: SidebarDetailBlock.task});
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
		<div class="bx-im-sidebar-task-preview__scope">
			<div v-if="isLoadingState" class="bx-im-sidebar-task-preview__skeleton"></div>
			<div v-else class="bx-im-sidebar-task-preview__container">
				<div 
					class="bx-im-sidebar-task-preview__header_container"
					@mouseover="showAddButton = true"
					@mouseleave="showAddButton = false"
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
						<Button
							v-if="showAddButton"
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
	`
};