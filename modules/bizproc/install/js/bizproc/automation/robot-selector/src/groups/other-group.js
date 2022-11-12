import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class OtherGroup extends Group
{
	getId(): string
	{
		return 'other';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_OTHER');
	}

	getIcon(): string
	{
		return GroupIcon.ANDROID;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_OTHER');
	}
}