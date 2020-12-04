import {Entry} from "calendar.entry";
import {CalendarSectionManager} from "calendar.calendarsection";
import {Util} from "calendar.util";
import {Loc, Type, Event} from "main.core";
import {EventEmitter} from 'main.core.events';
import {ConfirmStatusDialog, ConfirmEditDialog, ReinviteUserDialog, ConfirmedEmailDialog, EmailLimitationDialog} from "calendar.controls";
import {CompactEventForm} from "calendar.compacteventform";


export class EntryManager {
	static newEntryName = '';
	static userIndex = {};

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
		newEntryData.SECT_ID = CalendarSectionManager.getNewEntrySectionId();
		newEntryData.REMIND = [{type: 'min', count: 15}];

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
		return {
			from : date,
			to : new Date(date.getTime() + (duration || 3600) * 1000)
		}
	}

	static getNewEntryName()
	{
		return EntryManager.newEntryName || Loc.getMessage('CALENDAR_DEFAULT_ENTRY_NAME');
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
					click: function(event, balloon, action) {

						EntryManager.openViewSlider(entryId);
						balloon.close();
					}
				}
			}]
		)
	}

	static openEditSlider(options = {})
	{
		const bx = Util.getBX();

		if (bx.Calendar && bx.Calendar.SliderLoader)
		{
			new bx.Calendar.SliderLoader(
				options.entry ? 'EDIT' + options.entry.id : 'NEW',
				{
					entry: options.entry || null,
					type: options.type,
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
					timezoneOffset: options.timezoneOffset
				}).show();
			}
		}
	}

	static deleteEntry(entry)
	{
		if (entry instanceof Entry)
		{
			EventEmitter.subscribe('BX.Calendar.Entry:beforeDelete', ()=>{
				if (Util.getBX().SidePanel.Instance)
				{
					Util.getBX().SidePanel.Instance.close();
				}
			});
			EventEmitter.subscribe('BX.Calendar.Entry:delete', (optins = {})=> {
				const calendar = Util.getCalendarContext();
				if (calendar)
				{
					calendar.reload();
				}
				else
				{
					Util.getBX().reload();
				}
			});
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
					this.showConfirmStatusDialog(entry);
					return false;
				}
				else if (!confirm(Loc.getMessage('EC_DECLINE_MEETING_CONFIRM')))
				{
					return false;
				}
			}

			BX.ajax.runAction('calendar.api.calendarajax.setMeetingStatus', {
				data: {
					entryId: entry.id,
					entryParentId: entry.parentId,
					status: status,
					recursionMode: params.recursionMode,
					currentDateDrom: Util.formatDate(entry.from)
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
								currentDateDrom: entry.from
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
						currentDateDrom: entry.from
					});
				}
			);
		});
	}

	static showConfirmStatusDialog(entry)
	{
		if (!this.confirmDeclineDialog)
		{
			this.confirmDeclineDialog = new ConfirmStatusDialog();
		}

		this.confirmDeclineDialog.show();
		this.confirmDeclineDialog.unsubscribeAll('onDecline');
		this.confirmDeclineDialog.subscribe('onDecline', function(event)
		{
			if (event instanceof Event.BaseEvent)
			{
				EntryManager.setMeetingStatus(
					entry,
					'N',
					{recursionMode: event.getData().recursionMode, confirmed: true}
				);
			}
		});
	}

	static showConfirmEditDialog(options)
	{
		if (!this.confirmEditDialog)
		{
			this.confirmEditDialog = new ConfirmEditDialog();
		}
		this.confirmEditDialog.show();

		if (Type.isFunction(options.callback))
		{
			this.confirmEditDialog.unsubscribeAll('onEdit');
			this.confirmEditDialog.subscribe('onEdit', function(event)
			{
				if (event instanceof Event.BaseEvent)
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
			this.reinviteUsersDialog = new ReinviteUserDialog();
		}
		this.reinviteUsersDialog.show();

		if (Type.isFunction(options.callback))
		{
			this.reinviteUsersDialog.unsubscribeAll('onSelect');
			this.reinviteUsersDialog.subscribe('onSelect', function(event)
			{
				if (event instanceof Event.BaseEvent)
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
			this.confirmedEmailDialog = new ConfirmedEmailDialog();
		}
		this.confirmedEmailDialog.show();

		if (Type.isFunction(options.callback))
		{
			this.confirmedEmailDialog.unsubscribeAll('onSelect');
			this.confirmedEmailDialog.subscribe('onSelect', function(event)
			{
				if (event instanceof Event.BaseEvent)
				{
					options.callback(event.getData());
				}
			});
		}
	}

	static showEmailLimitationDialog(options = {})
	{
		const confirmedEmailDialog = new EmailLimitationDialog();
		confirmedEmailDialog.subscribe('onClose', ()=>{
			if (Type.isFunction(options.callback))
			{
				options.callback();
			}
		});
		confirmedEmailDialog.show();
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


	static openChatForEntry({entryId, entry})
	{
		if (window.BXIM && entry && entry.data.MEETING && parseInt(entry.data.MEETING.CHAT_ID))
		{
			BXIM.openMessenger('chat' + parseInt(entry.data.MEETING.CHAT_ID));
		}
		else
		{
			BX.ajax.runAction('calendar.api.calendarajax.createEventChat', {
				data: {
					entryId: entryId
				}
			})
			.then((response) => {
					if (window.BXIM && response.data && response.data.chatId > 0)
					{
						BXIM.openMessenger('chat' + response.data.chatId);
					}
				}
			);
		}
	}
}
