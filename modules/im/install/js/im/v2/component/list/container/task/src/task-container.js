import { TaskList } from 'im.v2.component.list.items.task';
import { Layout } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';

import './css/task-container.css';

// @vue/component
export const TaskListContainer = {
	name: 'TaskListContainer',
	components: { TaskList },
	emits: ['selectEntity'],
	created()
	{
		Logger.warn('List: Task container created');
	},
	methods: {
		onChatClick(dialogId: string): void
		{
			this.$emit('selectEntity', { layoutName: Layout.taskComments, entityId: dialogId });
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-list-container-task__container">
			<div class="bx-im-list-container-task__header_container">
				<div class="bx-im-list-container-task__header_title">
					{{ loc('IM_LIST_CONTAINER_TASK_HEADER_TITLE') }}
				</div>
			</div>
			<div class="bx-im-list-container-task__elements_container">
				<div class="bx-im-list-container-task__elements">
					<TaskList @chatClick="onChatClick" />
				</div>
			</div>
		</div>
	`,
};
