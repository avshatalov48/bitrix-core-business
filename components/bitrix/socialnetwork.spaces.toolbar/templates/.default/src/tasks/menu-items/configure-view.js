import { Loc, Runtime } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';

export const ConfigureViewItem: MenuItem = (view: TasksSettingsMenu) => new MenuItem({
	dataset: { id: `spaces-tasks-${view.getViewId()}-settings-configure-view` },
	text: Loc.getMessage('SN_SPACES_TASKS_CONFIGURE_VIEW'),
	onclick: () => {
		Runtime.loadExtension('ui.dialogs.checkbox-list').then(() => {
			view.close();
			EventEmitter.emit('tasks-kanban-settings-fields-view');
		});
	},
});
