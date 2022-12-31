import { Loc } from 'main.core';
import { CalendarSection } from './calendarsection';

export class CalendarTaskSection extends CalendarSection
{
	constructor(data = {}, {type, userId, ownerId})
	{
		const defaultColor = '#ff5b55';
		let belongToUser = false;
		let defaultName = Loc.getMessage('EC_SEC_USER_TASK_DEFAULT');

		if (type === 'user' && userId === ownerId)
		{
			defaultName = Loc.getMessage('EC_SEC_MY_TASK_DEFAULT');
			belongToUser = true;
		}
		else if(type === 'group')
		{
			defaultName = Loc.getMessage('EC_SEC_GROUP_TASK_DEFAULT');
		}

		super({
			ID: 'tasks',
			NAME: data.name || defaultName,
			COLOR: data.color || defaultColor,
			PERM: {
				edit_section: true,
				view_full: true,
				view_time: true,
				view_title: true
			}
		});

		this.isUserTaskSection = belongToUser;
	}

	isPseudo(): boolean
	{
		return true;
	}

	taskSectionBelongToUser()
	{
		return this.isUserTaskSection;
	}

	updateData(data)
	{
		super.updateData(data);
		this.id = data.ID;
	}
}