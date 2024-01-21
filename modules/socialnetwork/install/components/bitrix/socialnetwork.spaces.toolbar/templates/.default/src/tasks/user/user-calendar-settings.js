import { TasksSettingsMenu } from '../tasks-settings-menu';

export class UserCalendarSettings extends TasksSettingsMenu
{
	getViewId(): string
	{
		return 'user-calendar';
	}
}
