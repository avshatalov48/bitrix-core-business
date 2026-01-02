import { Loc } from 'main.core';
import { Group } from './group';
import GroupIcon from './group-icon';

export class RecentGroup extends Group
{
	getId(): string
	{
		return 'recent';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_RECENT_ROBOT_GROUP');
	}

	getIcon(): string
	{
		return GroupIcon.RECENT_ENTITY;
	}

	isHeaderGroup(): boolean
	{
		return true;
	}
}