import { Util } from 'calendar.util';
import { CategoryManager } from './categorymanager';

export {
	CategoryManager
}

export class Category
{
	constructor(data)
	{
		this.updateData(data);
		this.calendarContext = Util.getCalendarContext();
		this.rooms = [];
	}

	updateData(data)
	{
		this.data = data || {};
		this.id = parseInt(data.ID, 10);
		this.name = data.NAME;
	}

	addRoom(room)
	{
		this.rooms.push(room);
	}

	getId()
	{
		return this.id;
	}

	setCheckboxStatus(checkboxStatus)
	{
		this.checkboxStatus = checkboxStatus;
	}
}