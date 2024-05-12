import { Member } from '../../layout/members-list';
import Base from '../base';
import { Util } from 'calendar.util';
import { Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import EventLayout from './event-layout';

type State = 'created' | 'not-created' | 'declined' ;

type EventData = {
	from: Date,
	to: Date,
	timezone: string,
	isFullDay: boolean,
	eventLinkHash: string,
	eventName: string,
	state: State,
	isView: boolean,
	eventId: boolean,
	userName: string,
}

type EventOptions = {
	isHiddenOnStart: boolean,
	owner: any,
	event: any,
	eventLinkHash: any,
	state: State,
	isView: boolean,
	eventId: number,
	timezone: string,
	inDeletedSlider: boolean,
	action: string,
	canceledByManager: boolean,
	showBackCalendarButtons: boolean,
	members: Member[],
};

export default class Event extends Base
{
	#event;
	#owner;
	#currentTimezone;
	#icsFile;
	#layout;
	#value;
	#state;
	#inDeletedSlider;
	#isView;
	#showBackCalendarButtons;

	#eventLayout: EventLayout;

	constructor(options: EventOptions)
	{
		super({ isHiddenOnStart: options.isHiddenOnStart });

		this.#state = options.state;
		this.#isView = options.isView;
		this.#event = options.event;
		this.#owner = options.owner;
		this.#currentTimezone = options.timezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone;
		this.#layout = {
			back: null,
			widgetDate: null,
			eventName: null,
			icon: null,
			stateTitle: null,
			additionalBlock: null,
			bottomButton: null,
		};
		this.#value = {
			from: null,
			to: null,
			timezone: null,
			isFullDay: false,
			canceledTimestamp: null,
			canceledUserName: null,
			eventName: null,
			canceledByManager: options.canceledByManager,
			eventLinkHash: options.eventLinkHash,
			eventId: options.eventId,
			members: options.members,
		};
		this.#icsFile = null;
		this.#inDeletedSlider = options.inDeletedSlider === true;
		this.#showBackCalendarButtons = options.showBackCalendarButtons;

		if (this.#event)
		{
			this.#initEventData();
		}

		this.#eventLayout = new EventLayout(this.#getLayoutProps());

		if (options.action === 'cancel')
		{
			setTimeout(() => this.#eventLayout.showCancelEventPopup(), 0);
		}

		if (options.action === 'ics')
		{
			this.downloadIcsFile();
		}

		if (options.action === 'videoconference')
		{
			this.startVideoconference();
		}
	}

	getType(): string
	{
		return 'event';
	}

	#initEventData()
	{
		this.#value.from = Util.getTimezoneDateFromTimestampUTC(
			parseInt(this.#event.timestampFromUTC, 10) * 1000,
			this.#currentTimezone,
		);
		this.#value.to = Util.getTimezoneDateFromTimestampUTC(
			parseInt(this.#event.timestampToUTC, 10) * 1000,
			this.#currentTimezone,
		);
		this.#value.timezone = this.#currentTimezone;
		this.#value.isFullDay = this.#event.isFullDay;
		this.#value.eventName = this.#getEventName();
		this.#value.canceledTimestamp = this.#event.canceledTimestamp;
		this.#value.canceledUserName = this.#event.externalUserName;
	}

	updateValue(data: EventData)
	{
		if (data.from)
		{
			this.#value.from = data.from;
		}

		if (data.to)
		{
			this.#value.to = data.to;
		}

		if (data.timezone)
		{
			this.#value.timezone = data.timezone;
		}

		if (Type.isBoolean(data.isFullDay))
		{
			this.#value.isFullDay = data.isFullDay;
		}

		if (data.eventLinkHash)
		{
			this.#value.eventLinkHash = data.eventLinkHash;
		}

		if (data.eventName)
		{
			this.#value.eventName = data.eventName;
		}

		if (data.state)
		{
			this.#state = data.state;
		}

		if (data.isView)
		{
			this.#isView = false;
		}

		if (data.eventId)
		{
			this.#value.eventId = data.eventId;
		}

		if (data.userName)
		{
			this.#value.canceledUserName = data.userName;
		}

		if (this.#value.canceledByManager === true)
		{
			this.#value.canceledByManager = false;
		}

		this.#eventLayout.update(this.#getLayoutProps());
	}

	getContent(): HTMLElement
	{
		return this.#eventLayout.render();
	}

	#getLayoutProps()
	{
		return {
			eventName: this.#value.eventName,
			from: this.#value.from,
			to: this.#value.to,
			timezone: this.#value.timezone,
			isFullDay: this.#value.isFullDay,
			members: this.#value.members,

			title: this.#getStateTitleTextByState(this.#state),

			iconClassName: this.#getIconClassByState(this.#state),

			onDeleteEvent: this.#state === 'created' ? this.deleteEvent.bind(this) : '',
			cancelledInfo: this.#getCancelledInfo(),

			showBackCalendarButton: this.#showBackCalendarButtons,
			bottomButtons: this.#getBottomButtons(),
		};
	}

	#getCancelledInfo(): any
	{
		if (this.#state === 'declined')
		{
			const cancelledDate = Util.getTimezoneDateFromTimestampUTC(
				parseInt(this.#value.canceledTimestamp, 10) * 1000,
				this.#currentTimezone,
			);
			if (this.#value.canceledByManager)
			{
				this.#value.canceledUserName = `${this.#owner.name} ${this.#owner.lastName}`;
			}

			if (this.#value.canceledTimestamp && this.#value.canceledUserName && cancelledDate)
			{
				return {
					date: cancelledDate,
					name: this.#value.canceledUserName,
				};
			}
		}

		return null;
	}

	#getBottomButtons()
	{
		const bottomButtons = {};

		if (this.#state === 'created')
		{
			bottomButtons.onStartVideoconference = this.startVideoconference.bind(this);
			bottomButtons.onDownloadIcs = this.downloadIcsFile.bind(this);
		}

		if (['not-created', 'declined'].includes(this.#state) && this.#showBackCalendarButtons)
		{
			bottomButtons.onReturnToCalendar = this.#onReturnButtonClick.bind(this);
		}

		return bottomButtons;
	}

	#getIconClassByState(state: State)
	{
		let result = '';
		switch (state)
		{
			case 'created':
				result = '--accept';
				break;
			case 'not-created':
				result = '--decline';
				break;
			case 'declined':
				result = '--decline';
				break;
			default:
				break;
		}

		return result;
	}

	#getStateTitleTextByState(state: State)
	{
		let result = '';
		switch (state)
		{
			case 'created':
				if (!this.#isView)
				{
					result = Loc.getMessage('CALENDAR_SHARING_MEETING_CREATED');
				}
				break;
			case 'not-created':
				result = Loc.getMessage('CALENDAR_SHARING_MEETING_NOT_CREATED');
				break;
			case 'declined':
				result = Loc.getMessage('CALENDAR_SHARING_MEETING_CANCELED');
				break;
			default:
				break;
		}

		return result;
	}

	async deleteEvent()
	{
		let response = null;
		try
		{
			response = await BX.ajax.runAction('calendar.api.sharingajax.deleteEvent', {
				data: {
					eventId: this.#value.eventId,
					eventLinkHash: this.#value.eventLinkHash,
				},
			});
		}
		catch (e)
		{
			response = e;
		}

		if (response.errors.length === 0)
		{
			this.#value.canceledTimestamp = Date.now() / 1000;
			this.#state = 'declined';
			this.#eventLayout.update(this.#getLayoutProps());
			EventEmitter.emit('onDeleteEvent');
		}

		return response.errors.length === 0;
	}

	async startVideoconference()
	{
		let response = null;
		try
		{
			response = await BX.ajax.runAction('calendar.api.sharingajax.getConferenceLink', {
				data: {
					eventLinkHash: this.#value.eventLinkHash,
				},
			});
		}
		catch (error)
		{
			console.error(error);
		}

		const conferenceLink = response?.data?.conferenceLink;

		if (conferenceLink)
		{
			window.location.href = conferenceLink;
		}
	}

	async downloadIcsFile()
	{
		if (!this.#icsFile)
		{
			const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
				data: {
					eventLinkHash: this.#value.eventLinkHash,
				},
			});
			this.#icsFile = response.data;
		}

		Util.downloadIcsFile(this.#icsFile, 'event');
	}

	#onReturnButtonClick()
	{
		EventEmitter.emit('onCreateAnotherEventButtonClick');
	}

	#getEventName()
	{
		return Loc.getMessage('CALENDAR_SHARING_EVENT_NAME', {
			'#OWNER_NAME#': `${this.#owner.name} ${this.#owner.lastName}`,
		});
	}
}
