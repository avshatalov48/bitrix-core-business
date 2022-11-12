import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class DigitalWorkplace extends Group
{
	getId(): string
	{
		return 'digitalWorkplace';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DIGITAL_WORKPLACE');
	}

	getIcon(): string
	{
		return GroupIcon.AUTOMATION;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_DIGITAL_WORKPLACE');
	}
}