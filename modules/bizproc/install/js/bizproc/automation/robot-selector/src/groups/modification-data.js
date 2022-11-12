import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class ModificationData extends Group
{
	getId(): string
	{
		return 'modificationData';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_MODIFICATION_DATA');
	}

	getIcon(): string
	{
		return GroupIcon.STORAGE;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_MODIFICATION_DATA');
	}
}