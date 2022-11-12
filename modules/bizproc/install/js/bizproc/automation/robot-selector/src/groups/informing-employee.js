import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class InformingEmployee extends Group
{
	getId(): string
	{
		return 'informingEmployee';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_INFORMING_EMPLOYEE');
	}

	getIcon(): string
	{
		return GroupIcon.INFORMING;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_INFORMING_EMPLOYEE');
	}
}