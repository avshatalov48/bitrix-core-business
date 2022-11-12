import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class Delivery extends Group
{
	getId(): string
	{
		return 'delivery';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DELIVERY');
	}

	getIcon(): string
	{
		return GroupIcon.DELIVERY;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_DELIVERY');
	}
}