import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class Goods extends Group
{
	getId(): string
	{
		return 'goods';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_GOODS');
	}

	getIcon(): string
	{
		return GroupIcon.GOODS;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_GOODS');
	}
}