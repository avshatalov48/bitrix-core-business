import {ConfirmDeleteDialog} from "calendar.controls";
import {Util} from "calendar.util";
import {EntryManager} from "./entrymanager";
import {Type} from 'main.core';

export {EntryManager};

export class Entry
{
	FULL_DAY_LENGTH = 86400;
	constructor(options = {})
	{
		this.prepareData(options.data);
		this.parts = [];

		if (options.userIndex)
		{
			this.setUserIndex(options.userIndex);
		}
		//this.uid = this.calendar.entryController.getUniqueId(data, this);
	}

	prepareData(data)
	{
		this.data = data;
		this.id = this.data.ID || 0;

		if (!this.data.DT_SKIP_TIME)
		{
			this.data.DT_SKIP_TIME = this.data.SKIP_TIME ? 'Y' : 'N';
		}

		this.fullDay = this.data.DT_SKIP_TIME === 'Y';
		this.parentId = this.data.PARENT_ID || 0;
		this.accessibility = this.data.ACCESSIBILITY || 'busy';
		this.important = this.data.IMPORTANCE === 'high';
		this.private = !!this.data.PRIVATE_EVENT;
		this.setSectionId(this.data.SECT_ID);
		this.name = this.data.NAME;

		this.userTimezoneOffsetFrom = parseInt(this.data['~USER_OFFSET_FROM']) || 0;
		this.userTimezoneOffsetTo = parseInt(this.data['~USER_OFFSET_TO']) || this.userTimezoneOffsetFrom;

		if (!this.data.DT_LENGTH)
		{
			this.data.DT_LENGTH = this.data.DURATION || 0;
		}
		if (this.fullDay && !this.data.DT_LENGTH)
		{
			this.data.DT_LENGTH = this.FULL_DAY_LENGTH;
		}

		if (!Type.isString(this.data.DATE_FROM) && !Type.isString(this.data.DATE_TO)
			&& Type.isDate(this.data.dateFrom) && Type.isDate(this.data.dateTo))
		{
			this.from = this.data.dateFrom;
			this.to = this.data.dateTo;

			this.data.DT_LENGTH = Math.round((this.to.getTime() - this.from.getTime()) / 1000);
			this.data.DURATION = this.data.DT_LENGTH;

			if (this.fullDay)
			{
				this.data.DATE_FROM = Util.formatDate(this.from.getTime());
				this.data.DATE_TO = Util.formatDate(this.to.getTime());
			}
			else
			{
				this.from = new Date(this.from.getTime() - (parseInt(this.data['~USER_OFFSET_FROM']) || 0) * 1000);
				this.to = new Date(this.from.getTime() + (this.data.DT_LENGTH - (this.fullDay ? 1 : 0)) * 1000);
			}
		}
		else
		{
			if (this.isTask())
			{
				this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
				this.to = BX.parseDate(this.data.DATE_TO) || this.from;
			}
			else
			{
				this.from = BX.parseDate(this.data.DATE_FROM) || new Date();
				// if (this.data.DT_SKIP_TIME !== "Y")
				// {
				// 	this.from = new Date(this.from.getTime() - (parseInt(this.data['~USER_OFFSET_FROM']) || 0) * 1000);
				// }
				this.to = new Date(this.from.getTime() + (this.data.DT_LENGTH - (this.fullDay ? 1 : 0)) * 1000);
			}
		}

		if (this.fullDay)
		{
			this.from.setHours(0, 0, 0, 0);
			this.to.setHours(0, 0, 0, 0);
		}

		if (!this.data.ATTENDEES_CODES && !this.isTask())
		{
			if (this.data.CAL_TYPE === 'user' && this.data.OWNER_ID)
			{
				this.data.ATTENDEES_CODES = ['U' + this.data.OWNER_ID];
			}
			else if (this.data.CREATED_BY)
			{
				this.data.ATTENDEES_CODES = ['U' + this.data.CREATED_BY];
			}
		}

		this.startDayCode = this.from;
		this.endDayCode = this.to;

		if (!Type.isArray(this.data.REMIND) && Type.isArray(this.data.remind))
		{
			this.data.REMIND = [];
			this.data.remind.forEach(function(value)
			{
				this.data.REMIND.push({type: 'min', count: value});
			}, this);
			delete this.data.remind;
		}
	}

	getAttendeesCodes()
	{
		return this.data.ATTENDEES_CODES || [];
	}

	getAttendeesEntityList()
	{
		return this.data.attendeesEntityList || [];
	}

	getAttendees()
	{
		if (!this.attendeeList && Type.isArray(this.data['ATTENDEE_LIST']))
		{
			this.attendeeList = [];
			const userIndex = this.getUserIndex();
			this.data['ATTENDEE_LIST'].forEach((user) => {
				if (userIndex[user.id])
				{
					let attendee = BX.clone(userIndex[user.id]);
					attendee.STATUS = user.status;
					attendee.ENTRY_ID = user.entryId || false;
					this.attendeeList.push(attendee);
				}
			});
		}
		return this.attendeeList || [];
	}

	setUserIndex(userIndex)
	{
		this.userIndex = userIndex;
	}

	getUserIndex()
	{
		return this.userIndex || EntryManager.getUserIndex();
	}

	cleanParts()
	{
		this.parts = [];
	}

	startPart(part)
	{
		part.partIndex = this.parts.length;
		this.parts.push(part);
		return this.parts[part.partIndex];
	}

	registerPartNode(part, params)
	{
		part.params = params;
	}

	checkPartIsRegistered(part)
	{
		return Type.isPlainObject(part.params);
	}

	getPart(partIndex)
	{
		return this.parts[partIndex] || false;
	}

	getWrap(partIndex)
	{
		return this.parts[partIndex || 0].params.wrapNode;
	}

	getSectionName()
	{
		//return this.calendar.sectionController.getSection(this.sectionId).name || '';
	}

	getDescription(callback)
	{
		if (this.data.DESCRIPTION && this.data['~DESCRIPTION'] && Type.isFunction(callback))
		{
			setTimeout(function()
			{
				callback(this.data['~DESCRIPTION']);
			}.bind(this), 50);
		}
	}

	applyViewRange(viewRange)
	{
		let
			viewRangeStart = viewRange.start.getTime(),
			viewRangeEnd = viewRange.end.getTime(),
			fromTime = this.from.getTime(),
			toTime = this.to.getTime();

		if (toTime < viewRangeStart || fromTime > viewRangeEnd)
			return false;

		if (fromTime < viewRangeStart)
		{
			this.displayFrom = viewRange.start;
			this.startDayCode = this.displayFrom;
		}

		if (toTime > viewRangeEnd)
		{
			this.displayTo = viewRange.end;
			this.endDayCode = this.displayTo;
		}
		return true;
	}

	isPersonal()
	{
		//return (this.data.CAL_TYPE === 'user' && this.data.OWNER_ID == this.calendar.util.userId);
	}

	isMeeting()
	{
		return !!this.data.IS_MEETING;
	}

	isResourcebooking()
	{
		return this.data.EVENT_TYPE === '#resourcebooking#';
	}

	isTask()
	{
		return this.data['~TYPE'] === 'tasks';
	}

	isFullDay()
	{
		return this.fullDay;
	}

	isLongWithTime()
	{
		return !this.fullDay && Util.getDayCode(this.from) !== Util.getDayCode(this.to);
	}

	isExpired()
	{
		return this.to.getTime() < new Date().getTime();
	}

	hasEmailAttendees()
	{
		if (this.emailAttendeesCache === undefined)
		{
			const userIndex = EntryManager.getUserIndex();
			for (let i = 0; i < this.data['ATTENDEE_LIST'].length; i++)
			{
				let user = this.data['ATTENDEE_LIST'][i];
				if ((user.status === 'Y' || user.status === 'Q')
					&& userIndex[user.id]
					&& userIndex[user.id].EMAIL_USER
				)
				{
					this.emailAttendeesCache = true;
					break;
				}
			}
		}
		return this.emailAttendeesCache;
	}

	ownerIsEmailUser()
	{
		if (this.ownerIsEmailUserCache === undefined)
		{
			const userIndex = EntryManager.getUserIndex();
			this.ownerIsEmailUserCache = userIndex[parseInt(this.data.MEETING_HOST)]
				&& userIndex[parseInt(this.data.MEETING_HOST)].EMAIL_USER;
		}
		return this.ownerIsEmailUserCache;
	}

	isSelected()
	{
		return !!this.selected;
	}

	isCrm()
	{
		return !!this.data.UF_CRM_CAL_EVENT;
	}

	isFirstReccurentEntry()
	{
		return (this.data.DATE_FROM_TS_UTC === Math.floor(BX.parseDate(this.data['~DATE_FROM']).getTime() / 1000) * 1000
			||
			BX.parseDate(this.data['DATE_FROM']).getTime() === BX.parseDate(this.data['~DATE_FROM']).getTime()
		) && !this.data.RECURRENCE_ID;
	}

	isRecursive()
	{
		return !!this.data.RRULE;
	}

	getMeetingHost()
	{
		return parseInt(this.data.MEETING_HOST);
	}

	getMeetingNotify()
	{
		return this.data.MEETING.NOTIFY;
	}

	getRrule()
	{
		return this.data.RRULE;
	}

	getRRuleDescription()
	{
		return this.data['~RRULE_DESCRIPTION'];
	}

	hasRecurrenceId()
	{
		return this.data.RECURRENCE_ID;
	}

	wasEverRecursive()
	{
		return this.data.RRULE || this.data.RECURRENCE_ID;
	}

	deselect()
	{
		this.selected = false;
	}

	select()
	{
		this.selected = true;
	}

	deleteParts()
	{
		if (Type.isArray(this.parts))
		{
			this.parts.forEach(function(part){
				if (part.params)
				{
					if (part.params.wrapNode)
					{
						part.params.wrapNode.style.opacity = 0;
					}
				}
			}, this);

			setTimeout(function(){
				this.parts.forEach(function(part){
					if (part.params)
					{
						if (part.params.wrapNode)
						{
							BX.remove(part.params.wrapNode);
						}
					}
				}, this);
			}.bind(this), 300);
		}
	}

	getUniqueId()
	{
		let sid = this.data.PARENT_ID || this.data.PARENT_ID;
		if (this.isRecursive())
			sid += '|' + this.data.DT_FROM_TS;

		if (this.data['~TYPE'] === 'tasks')
			sid += '|' + 'task';

		return sid;
	}

	getCurrentStatus()
	{
		let
			userId = Util.getCurrentUserId(),
			status = false,
			i, user;

		if (this.isMeeting())
		{
			if (userId === parseInt(this.data.CREATED_BY))
			{
				status = this.data.MEETING_STATUS || 'Q';
			}
			else if (userId === parseInt(this.data.MEETING_HOST))
			{
				status = 'H';
				//status = this.data.MEETING_STATUS || 'H';
			}
			else if (Type.isArray(this.data['ATTENDEE_LIST']))
			{
				for (i = 0; i < this.data['ATTENDEE_LIST'].length; i++)
				{
					user = this.data['ATTENDEE_LIST'][i];
					if (parseInt(user.id) === userId)
					{
						status = user.status;
						break;
					}
				}
			}
		}

		return Util.getMeetingStatusList().includes(status) ? status : false;
	}

	setCurrentStatus(status)
	{
		if (this.isMeeting() && Util.getMeetingStatusList().includes(status))
		{
			this.data.MEETING_STATUS = status;

			const userId = Util.getCurrentUserId();
			if (Type.isArray(this.data['ATTENDEE_LIST']))
			{
				for (let i = 0; i < this.data['ATTENDEE_LIST'].length; i++)
				{
					if (parseInt(this.data['ATTENDEE_LIST'][i].id) === userId)
					{
						this.data['ATTENDEE_LIST'][i].status = status;
						this.attendeeList = null;
						break;
					}
				}
			}
		}
	}

	getReminders()
	{
		let res = [];
		if (this.data && this.data.REMIND)
		{
			this.data.REMIND.forEach(function (remind)
			{
				switch(remind.type)
				{
					case 'min':
						res.push(remind.count);
						break;
					case 'hour':
						res.push(parseInt(remind.count) * 60);
						break;
					case 'day':
						res.push(parseInt(remind.count) * 60 * 24);
						break;
					case 'daybefore':
						res.push(remind);
						break;
					case 'date':
						if (!Type.isDate(remind.value))
						{
							remind.value = Util.parseDate(remind.value);
						}

						if (Type.isDate(remind.value))
						{
							res.push(remind);
						}
						break;
				}
			});
		}
		return res;
	}

	getLengthInDays()
	{
		let
			from = new Date(this.from.getFullYear(), this.from.getMonth(), this.from.getDate(), 0, 0, 0),
			to = new Date(this.to.getFullYear(), this.to.getMonth(), this.to.getDate(), 0, 0, 0);

		return Math.round((to.getTime() - from.getTime()) / Util.getDayLength()) + 1;
	}

	getName()
	{
		return this.name || this.defaultNewName;
	}

	getColor()
	{
		return this.data.COLOR;
	}

	getType()
	{
		return this.data.CAL_TYPE;
	}

	getOwnerId()
	{
		return this.data.OWNER_ID;
	}

	delete(params)
	{
		params = Type.isPlainObject(params) ? params : {};
		let recursionMode = params.recursionMode || false;

		if (this.wasEverRecursive() && !params.confirmed)
		{
			return this.showConfirmDeleteDialog({entry: this});
		}
		else
		{
			if (!params.confirmed
				&& !confirm(BX.message('EC_DELETE_EVENT_CONFIRM'))
			)
			{
				return false;
			}

			// Broadcast event
			BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{entryId: this.id, recursionMode: recursionMode}]);

			this.deleteParts();

			BX.ajax.runAction('calendar.api.calendarajax.deleteCalendarEntry', {
				data: {
					entryId: this.id,
					recursionMode: params.recursionMode || false
				}
			}).then(
				function (response)
				{
					BX.onCustomEvent('BX.Calendar.Entry:delete', [{entryId: this.id, recursionMode: recursionMode}]);
				}.bind(this)
			);
		}
	}

	deleteThis()
	{
		let recursionMode = 'this';
		if (this.isRecursive())
		{
			BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{entryId: this.id, recursionMode: recursionMode}]);
			BX.ajax.runAction('calendar.api.calendarajax.excludeRecursionDate', {
				data: {
					entryId: this.id,
					excludeDate: this.data.DATE_FROM
				}
			}).then(
				// Success
				function (response)
				{
					BX.onCustomEvent('BX.Calendar.Entry:delete', [{entryId: this.id, recursionMode: recursionMode}]);
				}.bind(this)
			);
		}
		else if (this.hasRecurrenceId())
		{
			this.delete({confirmed: true, recursionMode: 'this'});
		}
	}

	deleteNext()
	{
		let recursionMode = 'next';
		if (this.isRecursive() && this.isFirstReccurentEntry())
		{
			this.deleteAll();
		}
		else
		{
			BX.onCustomEvent('BX.Calendar.Entry:beforeDelete', [{entryId: this.id, recursionMode: recursionMode}]);
			BX.ajax.runAction('calendar.api.calendarajax.changeRecurciveEntryUntil', {
				data: {
					entryId: this.id,
					untilDate: Util.formatDate(this.from.getTime() - Util.getDayLength())
				}
			}).then(
				// Success
				function (response)
				{
					BX.onCustomEvent('BX.Calendar.Entry:delete', [{entryId: this.id, recursionMode: recursionMode}]);
				}.bind(this)
			);
		}
	}

	deleteAll()
	{
		return this.delete({confirmed: true, recursionMode: 'all'});
	}

	showConfirmDeleteDialog(params)
	{
		if (!this.confirmDeleteDialog)
		{
			this.confirmDeleteDialog = new ConfirmDeleteDialog({entry: params.entry});
		}
		this.confirmDeleteDialog.show();
	}

	save()
	{
	}

	getLocation()
	{
		return this.data.LOCATION;
	}

	setTimezone(timezoneFrom, timezoneTo = null)
	{
		if(Type.isString(timezoneFrom))
		{
			this.data.TZ_FROM = timezoneFrom;
			if (Type.isNull(timezoneTo))
			{
				this.data.TZ_TO = timezoneFrom;
			}
		}
		if(Type.isString(timezoneTo))
		{
			this.data.TZ_TO = timezoneTo;
		}
	}

	getTimezoneFrom()
	{
		return this.data.TZ_FROM || '';
	}

	getTimezoneTo()
	{
		return this.data.TZ_TO || '';
	}

	setSectionId(value)
	{
		this.data.SECT_ID = this.sectionId = this.isTask() ? 'tasks' : parseInt(value);
	}

	setDateTimeValue({from, to})
	{
		if (Type.isDate(from) && Type.isDate(to))
		{
			this.from = this.data.dateFrom = from;
			this.to = this.data.dateTo = to;

			this.data.DT_LENGTH = Math.round((this.to.getTime() - this.from.getTime()) / 1000);
			this.data.DURATION = this.data.DT_LENGTH;

			if (this.fullDay)
			{
				this.data.DATE_FROM = Util.formatDate(this.from.getTime());
				this.data.DATE_TO = Util.formatDate(this.to.getTime());
			}
			else
			{
				this.data.DATE_FROM = Util.formatDateTime(this.from.getTime());
				this.data.DATE_TO = Util.formatDateTime(this.to.getTime());
			}
		}
	}
}
