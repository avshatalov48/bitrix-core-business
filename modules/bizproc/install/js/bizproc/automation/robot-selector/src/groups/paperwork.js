import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class Paperwork extends Group
{
	getId(): string
	{
		return 'paperwork';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_PAPERWORK');
	}

	getIcon(): string
	{
		return GroupIcon.PAPERWORK;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_PAPERWORK');
	}
}