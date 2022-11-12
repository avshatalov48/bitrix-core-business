import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class Ads extends Group
{
	getId(): string
	{
		return 'ads';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ADS');
	}

	getIcon(): string
	{
		return GroupIcon.ADS;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_ADS');
	}
}