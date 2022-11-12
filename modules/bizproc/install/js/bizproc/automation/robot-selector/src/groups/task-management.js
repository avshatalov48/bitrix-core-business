import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class TaskManagement extends Group
{
	getId(): string
	{
		return 'taskManagement';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_TASK_MANAGEMENT');
	}

	getIcon(): string
	{
		return GroupIcon.TASK;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_TASK_MANAGEMENT');
	}
}