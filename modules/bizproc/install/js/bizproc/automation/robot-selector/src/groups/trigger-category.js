import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class TriggerCategory extends Group
{
	getId(): string
	{
		return 'trigger_category';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_TRIGGER_CATEGORY');
	}

	getIcon(): string
	{
		return GroupIcon.AUTOMATION;
	}
}