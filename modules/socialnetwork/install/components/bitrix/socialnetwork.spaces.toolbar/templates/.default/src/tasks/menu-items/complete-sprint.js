import { Loc } from 'main.core';
import { MenuItem } from 'main.popup';
import { ScrumActiveSettings } from '../scrum/scrum-active-settings';

export const CompleteSprintItem: MenuItem = (view: ScrumActiveSettings) => new MenuItem({
	dataset: {
		id: `spaces-tasks-${view.getViewId()}-settings-complete-sprint`,
	},
	text: Loc.getMessage('SN_SPACES_TASKS_SCRUM_SETTINGS_COMPLETE_SPRINT'),
	disabled: !view.canCompleteSprint(),
	onclick: () => {
		view.close();
		view.emit('completeSprint');
	},
});
