import { TasksSettingsMenu } from '../tasks-settings-menu';

export class GroupCalendarSettings extends TasksSettingsMenu
{
	getViewId(): string
	{
		return 'group-calendar';
	}
}
