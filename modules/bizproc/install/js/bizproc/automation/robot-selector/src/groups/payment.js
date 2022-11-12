import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class Payment extends Group
{
	getId(): string
	{
		return 'payment';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_PAYMENT');
	}

	getIcon(): string
	{
		return GroupIcon.PAYMENT;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_PAYMENT');
	}
}