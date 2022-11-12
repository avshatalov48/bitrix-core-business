import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class ClientCategory extends Group
{
	getId(): string
	{
		return 'client_category';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_CLIENT_CATEGORY');
	}

	getIcon(): string
	{
		return GroupIcon.COMMUNICATION;
	}
}