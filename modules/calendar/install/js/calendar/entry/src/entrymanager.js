import {Entry} from "calendar.entry";
import {CalendarSectionManager} from "calendar.calendarsection";
import {Util} from "calendar.util";
import {Loc, Type, Event} from "main.core";
import {ConfirmStatusDialog, ConfirmEditDialog} from "calendar.controls";
import {CompactEventForm} from "calendar.compacteventform";

export class EntryManager {
	static newEntryName = '';

	static getNewEntry(options)
	{
		let newEntryData = {};
		let dateTime = EntryManager.getNewEntryTime(new Date());

		newEntryData.ID = null;
		newEntryData.NAME = EntryManager.getNewEntryName();
		newEntryData.dateFrom = dateTime.from;
		newEntryData.dateTo = dateTime.to;
		newEntryData.SECT_ID = CalendarSectionManager.getNewEntrySectionId();
		newEntryData.REMIND = [{type: 'min', count: 15}];
		newEntryData.ATTENDEES_CODES = ['U' + Util.getCurrentUserId()];
		//newEntryData.TIMEZONE_FROM = userSettings.timezoneName || userSettings.timezoneDefaultName || null;

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
					userId: options.userId
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
			BX.addCustomEvent('BX.Calendar.Entry:beforeDelete', ()=>{
				if (Util.getBX().SidePanel.Instance)
				{
					Util.getBX().SidePanel.Instance.close();
				}
			});
			BX.addCustomEvent('BX.Calendar.Entry:delete', ()=>{
				Util.getBX().reload();
			});
			entry.delete();
		}
	}

	static setMeetingStatus(entry, status, params = {})
	{
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
			function (response)
			{
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
			}.bind(this)
		);
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

	static getCompactViewForm()
	{
		if (!EntryManager.compactEntryForm)
		{
			EntryManager.compactEntryForm = new CompactEventForm();
		}
		return EntryManager.compactEntryForm;
	}

	static openCompactViewForm(options = {})
	{
		EntryManager.getCompactViewForm().showInViewMode(options);
	}

	static openCompactEditForm(options = {})
	{
		EntryManager.getCompactViewForm().showInEditMode(options);
	}

	static getEntryInstance(entry, userIndex)
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
				entryInstance = EntryManager.getNewEntry();
			}
		}

		return entryInstance;
	}
}