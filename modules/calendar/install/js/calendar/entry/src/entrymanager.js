import { Entry } from 'calendar.entry';
import { SectionManager } from 'calendar.sectionmanager';
import { Util } from 'calendar.util';
import { Event, Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { CompactEventForm } from 'calendar.compacteventform';
import 'ui.notification';
import { RoomsManager } from 'calendar.roomsmanager';

export class EntryManager {
	static newEntryName = '';
	static userIndex = {};
	static delayedActionList = [];
	static DELETE_DELAY_TIMEOUT = 4000;
	static slidersMap = new WeakMap();

	static getNewEntry(options)
	{
		const newEntryData = {};
		const dateTime = EntryManager.getNewEntryTime(new Date());
		const userSettings = Util.getUserSettings();
		const userId = Util.getCurrentUserId();

		newEntryData.ID = null;
		newEntryData.NAME = EntryManager.getNewEntryName();
		newEntryData.dateFrom = dateTime.from;
		newEntryData.dateTo = dateTime.to;
		if(options.type === 'location')
		{
			newEntryData.SECT_ID = RoomsManager.getNewEntrySectionId(options.type, parseInt(options.ownerId));
		}
		else
		{
			newEntryData.SECT_ID = SectionManager.getNewEntrySectionId(options.type, parseInt(options.ownerId));
		}
		newEntryData.REMIND = EntryManager.getNewEntryReminders();

		newEntryData.attendeesEntityList = [{entityId: 'user', id: userId}];
		newEntryData.ATTENDEE_LIST = [{id: Util.getCurrentUserId(), status: "H"}];

		if (options.type === 'user' && userId !== options.ownerId)
		{
			newEntryData.attendeesEntityList.push({entityId: 'user', id: options.ownerId});
			newEntryData.ATTENDEE_LIST = [
				{id: options.ownerId, status: "H"},
				{id: Util.getCurrentUserId(), status: "Y"}
			];
		}
		else if (options.type === 'group')
		{
			newEntryData.attendeesEntityList.push({entityId: 'project', id: options.ownerId});
		}

		newEntryData.TZ_FROM = userSettings.timezoneName || userSettings.timezoneDefaultName || '';
		newEntryData.TZ_TO = userSettings.timezoneName || userSettings.timezoneDefaultName || '';

		return new Entry({data: newEntryData});
	}

	static getNewEntryTime(date, duration)
	{
		date = Util.getUsableDateTime(date);

		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			const displayedViewRange = calendarContext.getDisplayedViewRange();
			if (Type.isDate(displayedViewRange?.start))
			{
				const dateTime = date.getTime();
				if (
					dateTime < displayedViewRange.start.getTime()
					|| dateTime > displayedViewRange.end.getTime()
				)
				{
					const startDate = new Date(displayedViewRange.start.getTime());
					const workTime = calendarContext.util.getWorkTime();
					startDate.setHours(workTime.start, 0, 0,0);
					date = Util.getUsableDateTime(startDate);
				}
			}
		}

		return {
			from : date,
			to : new Date(date.getTime() + (duration || 3600) * 1000)
		}
	}

	static getNewEntryName(): string
	{
		return (EntryManager.newEntryName || '');
	}

	static setNewEntryName(newEntryName)
	{
		EntryManager.newEntryName = newEntryName;
	}

	static showEditEntryNotification(entryId)
	{
		Util.showNotification(
			Loc.getMessage('CALENDAR_SAVE_EVENT_NOTIFICATION'),
			[{
				title: Loc.getMessage('CALENDAR_EVENT_DO_VIEW'),
				events: {
					click: function(event, balloon, action) {

						EntryManager.openViewSlider(entryId);
						balloon.close();
					}
				}
			}]
		)
	}

	static showNewEntryNotification(entryId)
	{
		Util.showNotification(
			Loc.getMessage('CALENDAR_NEW_EVENT_NOTIFICATION'),
			[{
				title: Loc.getMessage('CALENDAR_EVENT_DO_VIEW'),
				events: {
					click: (event, balloon, action) => {

						EntryManager.openViewSlider(entryId);
						balloon.close();
					}
				}
			}]
		)
	}

	static showDeleteEntryNotification(entry)
	{
		if (entry && entry instanceof Entry)
		{
			BX.UI.Notification.Center.notify({
				id: 'calendar' + entry.getUniqueId(),
				content: Loc.getMessage('CALENDAR_DELETE_EVENT_NOTIFICATION'),
				actions: [{
					title: Loc.getMessage('CALENDAR_EVENT_DO_CANCEL'),
					events: {
						click: (event, balloon, action) => {
							entry.cancelDelete();
							balloon.close();
						}
					}
				}]
			});
		}
	}

	static showReleaseLocationNotification()
	{
		BX.UI.Notification.Center.notify({
			content: Loc.getMessage('CALENDAR_RELEASE_LOCATION_NOTIFICATION'),
		});
	}

	static closeDeleteNotificationBalloon(entry)
	{
		if (entry && entry instanceof Entry)
		{
			const balloon = BX.UI.Notification.Center.getBalloonById('calendar' + entry.getUniqueId());
			if (balloon)
			{
				balloon.close();
			}
		}
	}

	static openEditSlider(options = {})
	{
		const bx = Util.getBX();

		if (bx.Calendar && bx.Calendar.SliderLoader)
		{
			new bx.Calendar.SliderLoader(
				options.entry ? 'EDIT' + options.entry.id : 'NEW',
				{
					calendarContext: options.calendarContext,
					entry: options.entry || null,
					type: options.type,
					isLocationCalendar: options.isLocationCalendar || false,
					roomsManager: options.roomsManager || null,
					locationAccess: options.locationAccess || false,
					dayOfWeekMonthFormat: options.dayOfWeekMonthFormat || false,
					locationCapacity: options.locationCapacity || 0,
					ownerId: options.ownerId,
					userId: options.userId,
					formDataValue: options.formDataValue || null
				}
			).show();
		}
	}

	static openViewSlider(eventId = null, options = {})
	{
		if (!Type.isNull(eventId))
		{
			const bx = Util.getBX();
			if (bx.Calendar && bx.Calendar.SliderLoader)
			{
				new bx.Calendar.SliderLoader(eventId, {
					entryDateFrom: options.from,
					timezoneOffset: options.timezoneOffset,
					dayOfWeekMonthFormat: options.dayOfWeekMonthFormat || false,
					calendarContext: options.calendarContext || null,
					link: options.link,
				}).show();
			}
		}
	}

	static deleteEntry(entry, calendarContext = null)
	{
		if (entry instanceof Entry)
		{
			const slider = Util.getBX().SidePanel.Instance.getTopSlider();
			const beforeDeleteHandler = () => {
				if (slider && slider.options.type === 'calendar:slider')
				{
					Util.getBX().SidePanel.Instance.close();
				}
			};
			EventEmitter.subscribe('BX.Calendar.Entry:beforeDelete', beforeDeleteHandler);

			const deleteHandler = () => {
				const calendar = Util.getCalendarContext();
				if (!calendar && !calendarContext)
				{
					return Util.getBX().reload();
				}

				if (calendar)
				{
					calendar.reload();
				}
				else if (calendarContext)
				{
					calendarContext.reload();
				}
				EventEmitter.unsubscribe('BX.Calendar.Entry:delete', deleteHandler);
				EventEmitter.unsubscribe('BX.Calendar.Entry:beforeDelete', beforeDeleteHandler);
			};

			EventEmitter.subscribe('BX.Calendar.Entry:delete', deleteHandler);

			entry.delete();
		}
	}

	static setMeetingStatus(entry, status, params = {})
	{
		return new Promise(resolve => {
			if (!Type.isPlainObject(params))
			{
				params = {};
			}
			params.recursionMode = params.recursionMode || false;

			if (status === 'N' && !params.confirmed)
			{
				if (entry.isRecursive())
				{
					this.showConfirmStatusDialog(entry, resolve);
					return false;
				}
			}

			BX.ajax.runAction('calendar.api.calendarajax.setMeetingStatus', {
				data: {
					entryId: entry.id,
					entryParentId: entry.parentId,
					status: status,
					recursionMode: params.recursionMode,
					currentDateFrom: Util.formatDate(entry.from)
				}
			}).then(
				(response) => {
					BX.Event.EventEmitter.emit(
						'BX.Calendar.Entry:onChangeMeetingStatus',
						new Event.BaseEvent({
							data: {
								entry: entry,
								status: status,
								recursionMode: params.recursionMode,
								currentDateFrom: entry.from,
								counters: response.data.counters
							}
						})
					);

					if (entry instanceof Entry)
					{
						entry.setCurrentStatus(status);
					}

					resolve({
						entry: entry,
						status: status,
						recursionMode: params.recursionMode,
						currentDateFrom: entry.from
					});
				}
			);
		});
	}

	static showConfirmStatusDialog(entry, resolvePromiseCallback = null)
	{
		if (!this.confirmDeclineDialog)
		{
			this.confirmDeclineDialog = this.createConfirmStatusDialog();
		}

		this.confirmDeclineDialog.show();
		this.confirmDeclineDialog.unsubscribeAll('onDecline');
		this.confirmDeclineDialog.subscribe('onDecline', function(event)
		{
			if (event && Type.isFunction(event.getData))
			{
				EntryManager.setMeetingStatus(
					entry,
					'N',
					{recursionMode: event.getData().recursionMode, confirmed: true}
				).then(() => {
					if (Type.isFunction(resolvePromiseCallback))
					{
						resolvePromiseCallback();
					}
				});
			}
		});
	}

	static showConfirmEditDialog(options)
	{
		if (!this.confirmEditDialog)
		{
			this.confirmEditDialog = this.createConfirmEditDialog();
		}
		this.confirmEditDialog.show();

		if (Type.isFunction(options.callback))
		{
			this.confirmEditDialog.unsubscribeAll('onEdit');
			this.confirmEditDialog.subscribe('onEdit', (event) => {
				if (event && Type.isFunction(event.getData))
				{
					options.callback(event.getData());
				}
			});
		}
	}

	static showReInviteUsersDialog(options)
	{
		if (!this.reinviteUsersDialog)
		{
			this.reinviteUsersDialog = this.createReinviteUserDialog();
		}
		this.reinviteUsersDialog.show();

		if (Type.isFunction(options.callback))
		{
			this.reinviteUsersDialog.unsubscribeAll('onSelect');
			this.reinviteUsersDialog.subscribe('onSelect', function(event)
			{
				if (event && Type.isFunction(event.getData))
				{
					options.callback(event.getData());
				}
			});
		}
	}

	static showConfirmedEmailDialog(options = {})
	{
		if (!this.confirmedEmailDialog)
		{
			this.confirmedEmailDialog = this.createConfirmedEmailDialog();
		}
		this.confirmedEmailDialog.show();

		if (Type.isFunction(options.callback))
		{
			this.confirmedEmailDialog.unsubscribeAll('onSelect');
			this.confirmedEmailDialog.subscribe('onSelect', function(event)
			{
				if (event && Type.isFunction(event.getData))
				{
					options.callback(event.getData());
				}
			});
		}
	}

	static showEmailLimitationDialog(options = {})
	{
		if (!this.limitationEmailDialog)
		{
			this.limitationEmailDialog = this.createEmailLimitationDialog();
		}
		this.limitationEmailDialog.subscribe('onClose', ()=>{
			if (Type.isFunction(options.callback))
			{
				options.callback();
			}
		});
		this.limitationEmailDialog.show();
	}

	static getCompactViewForm(create = true)
	{
		if (!EntryManager.compactEntryForm && create)
		{
			EntryManager.compactEntryForm = new CompactEventForm();
		}

		return EntryManager.compactEntryForm;
	}

	static openCompactViewForm(options = {})
	{
		const compactForm = EntryManager.getCompactViewForm();
		if (!compactForm.isShown())
		{
			compactForm.unsubscribeAll('onClose');
			if (Type.isFunction(options.closeCallback))
			{
				compactForm.subscribe('onClose', options.closeCallback);
			}
			compactForm.showInViewMode(options);
		}
	}

	static openCompactEditForm(options = {})
	{
		const compactForm = EntryManager.getCompactViewForm();
		if (!compactForm.isShown())
		{
			compactForm.unsubscribeAll('onClose');
			if (Type.isFunction(options.closeCallback))
			{
				compactForm.subscribe('onClose', options.closeCallback);
			}
			compactForm.showInEditMode(options);
		}
	}

	static getEntryInstance(entry, userIndex, options = {})
	{
		let entryInstance = null;
		if (entry instanceof Entry)
		{
			entryInstance = entry;
		}
		else
		{
			if (Type.isObject(entry) && Type.isObject(entry.data))
			{
				entryInstance = new Entry({data: entry.data, userIndex: userIndex});
			}
			else if (Type.isObject(entry))
			{
				entryInstance = new Entry({data: entry, userIndex: userIndex});
			}
			else
			{
				entryInstance = EntryManager.getNewEntry(options);
			}
		}

		return entryInstance;
	}

	static getUserIndex(options = {})
	{
		return EntryManager.userIndex
	}

	static setUserIndex(userIndex)
	{
		EntryManager.userIndex = userIndex;
	}

	handlePullChanges(params)
	{
		if (!BX.Calendar.Util.checkRequestId(params.requestUid))
		{
			return;
		}

		const compactForm = EntryManager.getCompactViewForm();
		if (
			compactForm
			&& compactForm.isShown()
		)
		{
			compactForm.handlePull(params);
		}

		BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
		{
			const data = EntryManager.slidersMap.get(slider);
			if (
				data
				&& data.entry
				&& data.entry.parentId === parseInt(params?.fields?.PARENT_ID)
			)
			{
				if (
					params.command === 'delete_event'
					&& data.entry.getType() === params?.fields?.CAL_TYPE
				)
				{
					slider.close();
				}
			}
		});

		if (params.command === 'set_meeting_status')
		{
			top.BX.Event.EventEmitter.emit('BX.Calendar:doReloadCounters');
		}
		else if (params.command === 'delete_event' || params.command === 'edit_event')
		{
			if (
				!params.fields
				||
				(params?.fields?.IS_MEETING && params?.fields?.MEETING_STATUS === 'Q')
			)
			{
				top.BX.Event.EventEmitter.emit('BX.Calendar:doReloadCounters');
			}

			if (params?.fields?.CAL_TYPE === 'location' && top.BX.Calendar?.Controls?.Location)
			{
				top.BX.Calendar.Controls.Location.handlePull(params);
			}
		}

		const calendarContext = Util.getCalendarContext();
		const entrySectionId = parseInt(params?.fields?.SECTION_ID);
		let sectionDisplayed = Type.isArray(params.sections)
			&& params.sections.find(section => {
				return section.id === entrySectionId && section.isShown();
			});

		let loadedEntry = params?.fields
			? EntryManager.getEntryInstance(
				calendarContext.getView().getEntryById(EntryManager.getEntryUniqueId(params.fields)),
			)
			: null;

		if ((sectionDisplayed || loadedEntry) && calendarContext)
		{
			calendarContext.reloadDebounce();
		}
	}

	static registerDeleteTimeout(params)
	{
		EntryManager.delayedActionList.push(params);
	}

	static unregisterDeleteTimeout({action, data})
	{
		EntryManager.delayedActionList = EntryManager.delayedActionList.filter((item) => {
			return item.action !== action
				|| item.data.entryId !== data.entryId
				|| item.data.recursionMode !== data.recursionMode
				|| item.data.excludeDate !== data.excludeDate;
		});
	}

	static doDelayedActions()
	{
		let requestList = [];
		return new Promise(resolve => {
			if(!EntryManager.delayedActionList.length)
			{
				resolve();
			}

			EntryManager.delayedActionList.forEach(({action, data, params}) => {

				const requestUid = parseInt(data.requestUid);
				requestList.push(data.requestUid);

				if (params.entry)
				{
					EntryManager.closeDeleteNotificationBalloon(params.entry);
				}

				BX.ajax.runAction(
					`calendar.api.calendarajax.${action}`,
					{data: data}
				).then(
					() => {
						Type.isFunction(params.callback)
						{
							params.callback();
						}

						requestList = requestList.filter(uid => {return uid !== requestUid});
						if (!requestList.length)
						{
							resolve();
						}
					},
					() => {
						requestList = requestList.filter(uid => {return uid !== requestUid});
						if (!requestList.length)
						{
							resolve();
						}
					}
				);

				EntryManager.unregisterDeleteTimeout({action, data, params});
			});
		});
	}

	static getEntryUniqueId(entryData, entry)
	{
		let sid = entryData.PARENT_ID || entryData.ID;
		if (entryData.RRULE)
		{
			sid += '|' + (entry ? Util.formatDate(entry.from) : Util.formatDate(BX.parseDate(entryData.DATE_FROM)));
		}

		if (entryData['~TYPE'] === 'tasks')
		{
			sid += '|' + 'task';
		}
		return sid;
	}

	static registerEntrySlider(entry, control)
	{
		const slider = Util.getBX().SidePanel.Instance.getTopSlider();
		if (slider)
		{
			EntryManager.slidersMap.set(slider, {entry, control});
		}
	}

	static getNewEntryReminders(type = 'withTime')
	{
		const userSettings = Util.getUserSettings();
		if (Type.isObjectLike(userSettings.defaultReminders)
			&& Type.isArray(userSettings.defaultReminders[type])
			&& userSettings.defaultReminders[type].length)
		{
			return userSettings.defaultReminders[type];
		}

		return type === 'withTime'
			? [{type: 'min', count: 15}]
			: [{type: 'daybefore', before: 0, time: 480}];
	}

	static setNewEntryReminders(type = 'withTime', reminders)
	{
		const userSettings = Util.getUserSettings();
		if (Type.isObjectLike(userSettings.defaultReminders))
		{
			userSettings.defaultReminders[type] = reminders;
		}
		Util.setUserSettings(userSettings);
	}

	//this is because extensions cant be loaded in iframe with import
	static createConfirmEditDialog()
	{
		const bx = Util.getBX();
		return new bx.Calendar.Controls.ConfirmEditDialog();
	}

	static createConfirmStatusDialog()
	{
		const bx = Util.getBX();
		return new bx.Calendar.Controls.ConfirmStatusDialog();
	}

	static createReinviteUserDialog()
	{
		const bx = Util.getBX();
		return new bx.Calendar.Controls.ReinviteUserDialog();
	}

	static createConfirmedEmailDialog()
	{
		const bx = Util.getBX();
		return new bx.Calendar.Controls.ConfirmedEmailDialog();
	}

	static createEmailLimitationDialog()
	{
		const bx = Util.getBX();
		return new bx.Calendar.Controls.EmailLimitationDialog();
	}
}