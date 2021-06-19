import {Util} from 'calendar.util';
import { Event, Type, Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { CalendarSection } from './calendarsection';

export class CalendarTaskSection extends CalendarSection
{
	constructor(data = {}, {type, userId, ownerId})
	{
		const defaultColor = '#ff5b55';
		let defaultName = Loc.getMessage('EC_SEC_MY_TASK_DEFAULT');

		if(type === 'user' && userId !== ownerId)
		{
			defaultName = Loc.getMessage('EC_SEC_USER_TASK_DEFAULT');
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
				edit_section:true,
				view_full:true,
				view_time:true,
				view_title:true
			}
		});
	}

	isPseudo(): boolean
	{
		return true;
	}

	updateData(data)
	{
		super.updateData(data);
		this.id = data.ID;
	}
}