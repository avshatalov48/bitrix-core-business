import { MenuItem } from 'main.popup';
import { BurnDownItem } from '../menu-items/burn-down';
import { TasksSettingsMenu } from '../tasks-settings-menu';

export class ScrumCompleteSettings extends TasksSettingsMenu
{
	getViewId(): string
	{
		return 'scrum-complete';
	}

	getMenuItems(): MenuItem[]
	{
		return [
			BurnDownItem(this),
		];
	}
}
