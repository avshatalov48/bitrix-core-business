import {Util} from 'calendar.util';
import { Event, Type } from 'main.core';

export class CalendarSection
{
	constructor(data)
	{
		this.updateData(data);
		this.calendarContext = Util.getCalendarContext();
		this.sectionManager = this.calendarContext.sectionManager;
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
			hiddenSections = BX.util.deleteFromArray(hiddenSections, BX.util.array_search(this.id, hiddenSections));
			this.calendarContext.sectionManager.setHiddenSections(hiddenSections);

			BX.userOptions.save('calendar', 'hidden_sections', 'hidden_sections', hiddenSections);
		}
	}

	hide()
	{
		if (this.isShown())
		{
			var hiddenSections = this.calendarContext.sectionManager.getHiddenSections();
			hiddenSections.push(this.id);
			this.calendarContext.sectionManager.setHiddenSections(hiddenSections);
			BX.userOptions.save('calendar', 'hidden_sections', 'hidden_sections', hiddenSections);
		}
	}

	remove()
	{
		if (confirm(BX.message('EC_SEC_DELETE_CONFIRM')))
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
						const sectionManager = Util.getCalendarContext().sectionManager;
						let reload = true;
						let section;

						for (let i = 0; i < sectionManager.sections.length; i++)
						{
							section = sectionManager.sections[i];
							if (section.id !== this.id && section.belongsToView())
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
					},
					(response) => {
						// this.calendar.displayError(response.errors);
					}
				);
		}
	}

	hideGoogle()
	{
		if (confirm(BX.message('EC_CAL_GOOGLE_HIDE_CONFIRM')))
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
					// Success
					BX.delegate(function (response)
					{
						this.calendar.reload();
					}, this),
					// Failure
					BX.delegate(function (response)
					{
						this.calendar.displayError(response.errors);
					}, this)
				);
		}
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
		if (!window.jsOutlookUtils)
		{
			BX.loadScript('/bitrix/js/calendar/outlook.js', BX.delegate(function ()
			{
				try
				{
					eval(this.data.OUTLOOK_JS);
				}
				catch (e)
				{
				}
			}, this));
		}
		else
		{
			try
			{
				eval(this.data.OUTLOOK_JS);
			}
			catch (e)
			{
			}
		}
	}

	canDo(action)
	{
		//action: access|add|edit|edit_section|view_full|view_time|view_title
		if (this.isVirtual() && ['access','add','edit'].includes(action))
		{
			return false;
		}

		if (action === 'view_event')
		{
			action = 'view_time';
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
		return this.data.GAPI_CALENDAR_ID;
	}

	isCalDav()
	{
		return !this.isPseudo() && this.data.CAL_DAV_CAL && this.data.CAL_DAV_CON;
	}

	isCompanyCalendar()
	{
		return !this.isPseudo() && this.type !== 'user' && this.type !== 'group' && !this.ownerId;
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

	belongsToUser(userId)
	{
		return this.type === 'user'
			&& this.ownerId === parseInt(userId)
			&& this.data.ACTIVE !== 'N';
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
}