import { Loc } from 'main.core';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { MenuItem } from 'main.popup';

export const SyncItem: MenuItem = (view: TasksSettingsMenu, syncScript: string) => {
	return {
		dataset: {
			id: `spaces-tasks-${view.getViewId()}-settings-sync`,
		},
		text: Loc.getMessage('SN_SPACES_TASKS_SYNC_LIST'),
		className: 'menu-popup-item-none',
		items: [
			{
				text: Loc.getMessage('SN_SPACES_TASKS_SYNC_WITH_OUTLOOK'),
				className: 'sn-spaces-tasks-icon-outlook',
				// eslint-disable-next-line no-eval
				onclick: () => eval(syncScript),
			},
		],
	};
};
