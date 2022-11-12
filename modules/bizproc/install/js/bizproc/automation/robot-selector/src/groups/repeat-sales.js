import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class RepeatSales extends Group
{
	getId(): string
	{
		return 'repeatSales';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_REPEAT_SALES');
	}

	getIcon(): string
	{
		return GroupIcon.SALES;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_REPEAT_SALES');
	}
}