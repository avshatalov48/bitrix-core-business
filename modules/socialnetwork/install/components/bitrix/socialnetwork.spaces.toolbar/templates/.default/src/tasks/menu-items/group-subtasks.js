import { Loc, Dom } from 'main.core';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';
import { EventEmitter } from 'main.core.events';

type Params = {
	shouldSubtasksBeGrouped: boolean,
	userId: number,
}

export const GroupSubtasksItem: MenuItem = (view: TasksSettingsMenu, params: Params) => {
	let shouldSubtasksBeGrouped = params.shouldSubtasksBeGrouped;

	return new MenuItem({
		dataset: { id: `spaces-tasks-${view.getViewId()}-settings-group-subtasks` },
		text: Loc.getMessage('SN_SPACES_TASKS_GROUP_SUBTASKS'),
		className: (shouldSubtasksBeGrouped ? 'menu-popup-item-accept' : 'menu-popup-item-none'),
		onclick: (event, item) => {
			BX.ajax.runComponentAction('bitrix:tasks.interface.filter', 'toggleGroupByTasks', {
				mode: 'class',
				data: {
					userId: params.userId,
				},
			}).then((response) => {
				if (response.status !== 'success')
				{
					return;
				}

				Dom.toggleClass(item.layout.item, ['menu-popup-item-accept', 'menu-popup-item-none']);

				if (BX.Main.gridManager)
				{
					shouldSubtasksBeGrouped = !shouldSubtasksBeGrouped;
					const gridInstance = BX.Main.gridManager.data[0].instance;
					gridInstance.reloadTable();
					EventEmitter.emit('BX.Tasks.Filter.group', [gridInstance, 'groupBySubTasks', shouldSubtasksBeGrouped]);
				}
				else
				{
					window.location.reload();
				}
			}, (error) => {
				// eslint-disable-next-line no-console
				console.log(error);
			});
		},
	});
};
