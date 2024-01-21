import { Loc } from 'main.core';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';

export const ReadAllItem: MenuItem = (view: TasksSettingsMenu, emptySpace: false) => new MenuItem({
	dataset: {
		id: `spaces-tasks-${view.getViewId()}-settings-read-all`,
	},
	text: Loc.getMessage('SN_SPACES_TASKS_SETTINGS_READ_ALL'),
	className: emptySpace ? 'menu-popup-item-none' : '',
	onclick: () => {
		view.emit('realAll');
	},
});
