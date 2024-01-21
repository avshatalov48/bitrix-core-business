import { Loc } from 'main.core';
import { MenuItem } from 'main.popup';
import { ScrumCompleteSettings } from '../scrum/scrum-complete-settings';

export const BurnDownItem: MenuItem = (view: ScrumCompleteSettings) => new MenuItem({
	dataset: {
		id: `spaces-tasks-${view.getViewId()}-settings-burn-down`,
	},
	text: Loc.getMessage('SN_SPACES_TASKS_SCRUM_SETTINGS_BURN_DOWN'),
	onclick: () => {
		view.close();
		view.emit('showBurnDown');
	},
});
