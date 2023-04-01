import {Util} from 'calendar.util';
import { Event, Type } from 'main.core';
import {SectionManager} from "calendar.sectionmanager";

export class CalendarSection
{
	constructor(data)
	{
		this.updateData(data);
		this.calendarContext = Util.getCalendarContext();
	}

	getId(): number
	{
		return this.id;
	}

	updateData(data)
	{
		this.data = data || {};
		this.type = data.CAL_TYPE || '';
		this.ownerId = parseInt(data.OWNER_ID) || 0;
		this.id = parseInt(data.ID);
		this.color = this.data.COLOR;
		this.name = this.data.NAME;
	}

	isShown(): boolean
	{
		return this.calendarContext.sectionManager.sectionIsShown(this.id);
	}

	show(): void
	{
		if (!this.isShown())
		{
			let hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
			hiddenSections = hiddenSections.filter((sectionId) => {return sectionId !== this.id;}, this);
			this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
			this.calendarContext.sectionManager.saveHiddenSections();
		}
	}

	hide(): void
	{
		if (this.isShown())
		{
			const hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
			hiddenSections.push(this.id);
			this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
			this.calendarContext.sectionManager.saveHiddenSections();
		}
	}

	remove()
	{
		const EventAlias = Util.getBX().Event;
		EventAlias.EventEmitter.emit(
			'BX.Calendar.Section:delete',
			new EventAlias.BaseEvent({data: {sectionId: this.id}})
		);

		BX.ajax.runAction('calendar.api.calendarajax.deleteCalendarSection', {
			data: {
				id: this.id
			}
		})
		.then(
			(response) => {
				return this.updateListAfterDelete();
			},
			(response) => {
				// this.calendar.displayError(response.errors);
			}
		);
	}

	hideSyncSection()
	{
		this.hide();
		BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);
		Util.getBX().Event.EventEmitter.emit(
			'BX.Calendar.Section:delete',
			new Event.BaseEvent({data: {sectionId: this.id}})
		);

		//hideExternalCalendarSection
		BX.ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
			data: {
				sectionStatus: {
					[this.id] : false
				}
			}
		})
		.then(
			(response) => {
				return this.updateListAfterDelete();
			},
			(response) => {
				// this.calendar.displayError(response.errors);
			}
		);
	}

	hideExternalCalendarSection()
	{
		this.hide();
		BX.onCustomEvent(this.calendar, 'BXCalendar:onSectionDelete', [this.id]);
		Util.getBX().Event.EventEmitter.emit(
			'BX.Calendar.Section:delete',
			new Event.BaseEvent({data: {sectionId: this.id}})
		);

		BX.ajax.runAction('calendar.api.calendarajax.hideExternalCalendarSection', {
			data: {
				id: this.id
			}
		})
		.then(
			(response) => {
				return this.updateListAfterDelete();
			},
			(response) => {
				// this.calendar.displayError(response.errors);
			}
		);
	}

	getLink()
	{
		return this.data && this.data.LINK ? this.data.LINK : '';
	}

	canBeConnectedToOutlook()
	{
		return !this.isPseudo() && this.data.OUTLOOK_JS && !(this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON) && !BX.browser.IsMac();
	}

	connectToOutlook()
	{
		BX.ajax.runAction('calendar.api.syncajax.getOutlookLink', {
			data: {
				id: this.id
			}
		})
		.then(
			(response) => {
				const url = response.data.result;

				eval(url);
			},
			(response) => {
				// this.calendar.displayError(response.errors);
			}
		)
	}

	canDo(action)
	{
		//action: access|add|edit|edit_section|view_full|view_time|view_title
		if (this.isVirtual() && ['access','add','edit'].includes(action))
		{
			return false;
		}

		return this.hasPermission(action);
	}

	hasPermission(action)
	{
		if (action === 'view_event')
		{
			action = 'view_time';
		}

		if (!this.data.PERM[action])
		{
			return false;
		}

		return this.data.PERM && this.data.PERM[action];
	}

	isSuperposed()
	{
		return !this.isPseudo() && !!this.data.SUPERPOSED;
	}

	isPseudo()
	{
		return false;
	}

	isVirtual()
	{
		return (this.data.CAL_DAV_CAL && this.data.CAL_DAV_CAL.indexOf('@virtual/events/') !== -1)
			|| (this.data.GAPI_CALENDAR_ID && this.data.GAPI_CALENDAR_ID.indexOf('@group.v.calendar.google.com') !== -1)
			|| (this.data.EXTERNAL_TYPE === 'google_readonly')
			|| (this.data.EXTERNAL_TYPE === 'google_freebusy')
	}

	isGoogle()
	{
		const googleTypes = [
			'google_readonly',
			'google',
			'google_write_read',
			'google_freebusy'
		]

		return !this.isPseudo() && googleTypes.includes(this.data.EXTERNAL_TYPE);
	}

	isCalDav()
	{
		return !this.isPseudo() && this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON;
	}

	isIcloud()
	{
		return !this.isPseudo() && this.data.EXTERNAL_TYPE === 'icloud';
	}

	isOffice365()
	{
		return !this.isPseudo() && this.data.EXTERNAL_TYPE === 'office365';
	}

	isArchive()
	{
		return !this.isPseudo() && this.data.EXTERNAL_TYPE === 'archive';
	}

	isExchange()
	{
		return !this.isPseudo() && this.data['IS_EXCHANGE'];
	}

	isCompanyCalendar()
	{
		return !this.isPseudo() && this.type !== 'user' && this.type !== 'group' && !this.ownerId;
	}

	hasConnection()
	{
		return !this.isPseudo() && this.data.connectionLinks && this.data.connectionLinks.length;
	}

	isLocationRoom()
	{
		return this.type === 'location';
	}

	belongsToView()
	{
		const calendarContext = Util.getCalendarContext();
		return this.type === calendarContext.getCalendarType()
			&& this.ownerId === calendarContext.getOwnerId();
	}

	belongsToOwner()
	{
		return this.belongsToUser(Util.getCalendarContext().getUserId());
	}

	belongsToUser(userId): boolean
	{
		return this.type === 'user'
			&& this.ownerId === parseInt(userId)
			&& this.data.ACTIVE !== 'N';
	}

	getExternalType(): string
	{
		return this.data.EXTERNAL_TYPE
			? this.data.EXTERNAL_TYPE
			: (this.isCalDav() ? 'caldav' : '')
		;
	}

	getConnectionLinks(): object
	{
		return Type.isArray(this.data.connectionLinks)
			? this.data.connectionLinks
			: [];
	}

	externalTypeIsLocal(): boolean
	{
		return this.getExternalType() === SectionManager.EXTERNAL_TYPE_LOCAL;
	}

	isPrimaryForConnection(): boolean
	{
		return !this.externalTypeIsLocal() && this.getConnectionLinks().find(connection => {
			return connection.isPrimary === 'Y';
		});
	}

	isActive()
	{
		return this.data.ACTIVE !== 'N';
	}

	getType()
	{
		return this.type;
	}

	getOwnerId()
	{
		return this.ownerId;
	}

	getConnectionIdList()
	{
		const connectionIdList = [];
		let connectionId =  parseInt(this.data.CAL_DAV_CON, 10);
		if (connectionId)
		{
			connectionIdList.push(connectionId);
		}

		return connectionIdList;
	}


	updateListAfterDelete()
	{
		const sectionManager = Util.getCalendarContext().sectionManager;
		let reload = true;
		let section;

		for (let i = 0; i < sectionManager.sections.length; i++)
		{
			section = sectionManager.sections[i];
			if (
				section.id !== this.id
				&& section.belongsToView()
				&& !section.isGoogle()
				&& !section.isIcloud()
				&& !section.isOffice365()
				&& !section.isCalDav()
				&& !section.isArchive()
			)
			{
				reload = false;
				break;
			}
		}

		const calendar = Util.getCalendarContext();
		if (!calendar || reload)
		{
			return Util.getBX().reload();
		}
		calendar.reload();
	}

}