import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class ClientData extends Group
{
	getId(): string
	{
		return 'clientData';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_CLIENT_DATA');
	}

	getIcon(): string
	{
		return GroupIcon.CLIENT_DATA;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_CLIENT_DATA');
	}
}