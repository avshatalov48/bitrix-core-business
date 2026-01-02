import { Loc } from 'main.core';

import { Group } from './group';
import type { GroupData } from 'ui.entity-catalog';
import GroupIcon from './group-icon';

export class NewEntitiesGroup extends Group
{
	getId(): string
	{
		return 'new';
	}

	getName(): string
	{
		return Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_NEW_ROBOT_AND_TRIGGER_GROUP');
	}

	getIcon(): string
	{
		return GroupIcon.NEW_ENTITY;
	}

	isHeaderGroup(): boolean
	{
		return true;
	}
}