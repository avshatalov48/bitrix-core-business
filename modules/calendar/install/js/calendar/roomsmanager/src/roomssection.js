import { Util } from 'calendar.util';
import { CalendarSection } from 'calendar.sectionmanager';

export class RoomsSection extends CalendarSection
{
	constructor(data)
	{
		super(data);
		this.updateData(data);
		this.calendarContext = Util.getCalendarContext();
		// this.roomsManager = this.calendarContext.roomsManager;
	}

	updateData(data)
	{
		this.data = data || {};
		this.type = data.CAL_TYPE || '';
		this.necessity = data.NECESSITY || 'N';
		this.capacity = parseInt(data.CAPACITY) || 0;
		this.ownerId = parseInt(data.OWNER_ID) || 0;
		this.id = parseInt(data.ID);
		this.location_id = parseInt(data.LOCATION_ID);
		this.color = this.data.COLOR;
		this.name = this.data.NAME;
		this.categoryId = parseInt(this.data.CATEGORY_ID);
		this.reserved = this.data.reserved || false;
	}

	belongsToView()
	{
		return true;
	}
}