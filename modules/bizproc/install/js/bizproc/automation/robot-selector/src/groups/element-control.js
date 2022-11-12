import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class ElementControl extends Group
{
	getId(): string
	{
		return 'elementControl';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ELEMENT_CONTROL');
	}

	getIcon(): string
	{
		return GroupIcon.ELEMENT_CONTROL;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_ELEMENT_CONTROL');
	}
}