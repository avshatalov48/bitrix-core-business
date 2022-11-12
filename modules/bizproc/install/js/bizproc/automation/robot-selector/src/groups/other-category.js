import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class OtherCategory extends Group
{
	getId(): string
	{
		return 'other_category';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_OTHER_CATEGORY');
	}

	getIcon(): string
	{
		return GroupIcon.ANDROID;
	}
}