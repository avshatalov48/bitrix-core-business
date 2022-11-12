import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class AdsCategory extends Group
{
	getId(): string
	{
		return 'ads_category';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ADS_CATEGORY');
	}

	getIcon(): string
	{
		return GroupIcon.ADS;
	}
}