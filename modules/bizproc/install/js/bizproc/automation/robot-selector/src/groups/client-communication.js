import { Group } from './group';
import GroupIcon from './group-icon';
import { Loc } from 'main.core';

export class ClientCommunication extends Group
{
	getId(): string
	{
		return 'clientCommunication';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_CLIENT_COMMUNICATION');
	}

	getIcon(): string
	{
		return GroupIcon.COMMUNICATION;
	}

	getAdviceTitle(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_DESCRIPTION_CLIENT_COMMUNICATION');
	}
}