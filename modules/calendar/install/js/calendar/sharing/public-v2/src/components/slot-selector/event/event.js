import Base from "../base";
import { Util } from "calendar.util";
import {Dom, Loc, Tag, Browser} from "main.core";
import {Popup} from "main.popup";
import WidgetDate from "../widget-date";
import {EventEmitter} from "main.core.events";
import {DateTimeFormat} from "main.date";
import {BottomSheet} from "ui.bottomsheet";


type State = 'created' | 'not-created' | 'declined' ;

type EventData = {
	from: Date,
	to: Date,
	timezone: string,
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
};


export default class Event extends Base
{
	#event;
	#owner;
	#currentTimezone;
	#icsFile;
	#widgetDate;
	#layout;
	#value;
	#state;
	#inDeletedSlider;
	#isView;
	#showBackCalendarButtons;

	constructor(options: EventOptions)
	{
		super({isHiddenOnStart: options.isHiddenOnStart});
		this.#state = options.state;
		this.#isView = options.isView;
		this.#event = options.event;
		this.#owner = options.owner;
		this.#currentTimezone = options.timezone ?? Intl.DateTimeFormat().resolvedOptions().timeZone;
		this.#widgetDate = this.#getWidgetDate();
		this.#layout = {
			back: null,
			widgetDate: null,
			eventName: null,
			icon: null,
			stateTitle: null,
			additionalBlock: null,
			bottomButton: null
		};
		this.#value = {
			from: null,
			to: null,
			timezone: null,
			canceledTimestamp: null,
			canceledUserName: null,
			eventName: null,
			canceledByManager: options.canceledByManager,
			eventLinkHash: options.eventLinkHash,
			eventId: options.eventId,
		};
		this.#icsFile = null;
		this.#inDeletedSlider = options.inDeletedSlider === true;
		this.#showBackCalendarButtons = options.showBackCalendarButtons;

		if (this.#event)
		{
			this.#initEventData();
		}

		if (options.action === 'cancel')
		{
			setTimeout(this.showCancelEventPopup.bind(this), 0);
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

	#getWidgetDate(): WidgetDate
	{
		if (!this.#widgetDate)
		{
			this.#widgetDate = new WidgetDate();
		}

		return this.#widgetDate;
	}

	#initEventData()
	{
		this.#value.from = Util.getTimezoneDateFromTimestampUTC(parseInt(this.#event.timestampFromUTC) * 1000, this.#currentTimezone);
		this.#value.to = Util.getTimezoneDateFromTimestampUTC(parseInt(this.#event.timestampToUTC) * 1000, this.#currentTimezone);
		this.#value.timezone = this.#currentTimezone;
		this.#value.eventName = this.#getEventName();
		this.#value.canceledTimestamp = this.#event.canceledTimestamp;
		this.#value.canceledUserName = this.#event.externalUserName;
		this.updateFormLayout();
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
			this.#updateState(data.state);
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

		this.updateFormLayout();
	}

	updateFormLayout()
	{
		this.#getWidgetDate().updateValue(this.#value);
		this.#getEventNameNode().innerText = this.#value.eventName;
	}

	getContent(): HTMLElement
	{
		return Tag.render`
			<div class="calendar-sharing__form-result">
				${this.#getNodeBackWrapper()}
				<div class="calendar-sharing__calendar-block --form --center">
					${this.#getNodeIcon()}
					${this.#getEventNameNode()}
					${this.#getStateTitleNode()}
				</div>
				
				<div class="calendar-sharing__calendar-block --form --center">
					${this.#getNodeWidgetDate()}
				</div>
				
				<div class="calendar-sharing__calendar-block --form --center">
					${this.#getAdditionalBlockNode()}
				</div>
				
				<div class="calendar-sharing__calendar-block --top-auto">
					${this.#getBottomButtonNode()}
				</div>
			</div>
		`;
	}

	#updateState(state: State)
	{
		this.#state = state;
		Dom.removeClass(this.#getNodeIcon(), ['--accept', '--decline']);
		Dom.addClass(this.#getNodeIcon(), this.#getIconClassByState(state));

		this.#getStateTitleNode().innerText = this.#getStateTitleTextByState(state);

		let oldNode = this.#getAdditionalBlockNode();
		let newNode = this.#createAdditionalBlockContentByState(state);
		this.#layout.additionalBlock = newNode;
		Dom.replace(oldNode, newNode);

		oldNode = this.#getBottomButtonNode();
		newNode = this.#createBottomButtonByState(state);
		this.#layout.bottomButton = newNode;
		Dom.replace(oldNode, newNode);
	}

	#getNodeIcon()
	{
		if (!this.#layout.icon)
		{
			this.#layout.icon = this.#createIconByState(this.#state);
		}

		return this.#layout.icon;
	}

	#getNodeBackWrapper(): HTMLElement
	{
		if (!this.#layout.back)
		{
			if (this.#showBackCalendarButtons && Browser.isMobile())
			{
				this.#layout.back = Tag.render`
					<div class="calendar-sharing__calendar-bar">
						<div class="calendar-sharing__calendar-back" onclick="${this.#onReturnButtonClick.bind(this)}"></div>
					</div>
				`;
			}
			else
			{
				this.#layout.back = Tag.render`<div class="calendar-sharing__calendar-bar --no-margin"></div>`;
			}
		}

		return this.#layout.back;
	}

	#createIconByState(state: State)
	{
		let result = Tag.render`
			<div class="calendar-sharing__form-result_icon"></div>
		`;
		Dom.addClass(result, this.#getIconClassByState(state));

		return result;
	}

	#getIconClassByState(state: State)
	{
		let result = '';
		switch (state)
		{
			case "created":
				result = '--accept';
				break;
			case "not-created":
				result = '--decline';
				break;
			case "declined":
				result = '--decline';
				break;
		}

		return result;
	}

	#getEventNameNode(): HTMLElement
	{
		if (!this.#layout.eventName)
		{
			this.#layout.eventName = Tag.render`
				<div class="calendar-pub-ui__typography-title --center --line-height-normal">
					${this.#value.eventName}
				</div>
			`;
		}

		return this.#layout.eventName;
	}

	#getStateTitleNode()
	{
		if (!this.#layout.stateTitle)
		{
			this.#layout.stateTitle = Tag.render`
				<div class="calendar-pub-ui__typography-s --center"></div>
			`;
			this.#layout.stateTitle.innerText = this.#getStateTitleTextByState(this.#state);
		}

		return this.#layout.stateTitle;
	}

	#getStateTitleTextByState(state: State)
	{
		let result = '';
		switch (state)
		{
			case "created":
				if (!this.#isView)
				{
					result = Loc.getMessage('CALENDAR_SHARING_MEETING_CREATED');
				}
				break;
			case "not-created":
				result = Loc.getMessage('CALENDAR_SHARING_MEETING_NOT_CREATED');
				break;
			case "declined":
				result = Loc.getMessage('CALENDAR_SHARING_MEETING_CANCELED');
				break;
		}

		return result;
	}

	#getAdditionalBlockNode()
	{
		if (!this.#layout.additionalBlock)
		{
			this.#layout.additionalBlock = this.#createAdditionalBlockContentByState(this.#state);
		}

		return this.#layout.additionalBlock;
	}

	#createAdditionalBlockContentByState(state: State)
	{
		let result = '';
		switch (state)
		{
			case "created":
				result = Tag.render`
					<div onclick="${this.showCancelEventPopup.bind(this)}" class="calendar-pub__form-status --decline">
						<div class="calendar-pub__form-status_text">
							${Loc.getMessage('CALENDAR_SHARING_DECLINE_MEETING')}
						</div>
					</div>
				`;
				break;
			case "not-created":
				result = Tag.render`
					<div></div>
				`;
				break;
			case "declined":
				const date = Util.getTimezoneDateFromTimestampUTC(parseInt(this.#value.canceledTimestamp) * 1000, this.#currentTimezone);
				if (this.#value.canceledByManager)
				{
					this.#value.canceledUserName = this.#owner.name + ' ' + this.#owner.lastName;
				}

				if (this.#value.canceledTimestamp && this.#value.canceledUserName && date)
				{
					result = Tag.render`
						<div class="calendar-pub__form-status">
							<div class="calendar-pub__form-status_text">
								${Loc.getMessage('CALENDAR_SHARING_WHO_CANCELED')}: ${this.#value.canceledUserName}<br> ${DateTimeFormat.format('j F ' + Util.getTimeFormatShort(), date.getTime() / 1000)}
							</div>
						</div>
					`;
				}
				else
				{
					result = Tag.render`
						<div></div>
					`;
				}

				break;
		}

		return result;
	}

	#getBottomButtonNode()
	{
		if (!this.#layout.bottomButton)
		{
			this.#layout.bottomButton = this.#createBottomButtonByState(this.#state);
		}

		return this.#layout.bottomButton;
	}

	#createBottomButtonByState(state: State)
	{
		if (this.#inDeletedSlider)
		{
			return '';
		}

		let result = '';
		switch (state)
		{
			case "created":
				result = Tag.render`
					<div onclick="${this.#onDownloadButtonClick.bind(this)}" class="calendar-pub-ui__btn --light-border --m">
						<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_ADD_TO_CALENDAR')}</div>
					</div>
				`;
				break;
			case "not-created":
			case "declined":
				if (this.#showBackCalendarButtons)
				{
					result = Tag.render`
						<div onclick="${this.#onReturnButtonClick.bind(this)}" class="calendar-pub-ui__btn --light-border --m">
							<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_RETURN_TO_SLOT_LIST')}</div>
						</div>
					`;
				}
				else
				{
					result = Tag.render`<div></div>`;
				}
				break;
		}

		return result;
	}

	getPopup()
	{
		if (!this.popup)
		{
			const popupContent = Tag.render`
				<div>
					<div class="calendar-pub__cookies-title">${Loc.getMessage('CALENDAR_SHARING_POPUP_MEETING_CANCELED')}</div>
					<div class="calendar-pub__cookies-info">${Loc.getMessage('CALENDAR_SHARING_POPUP_MEETING_CANCELED_INFO')}</div>
					<div class="calendar-pub__cookies-buttons ${Browser.isMobile() ? '--center' : '--flex-end'}">
						<div onclick="${this.closeCancelEventPopup.bind(this)}" class="calendar-pub-ui__btn --inline --m --light-border">
							<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_POPUP_LEAVE')}</div>
						</div>
						<div onclick="${this.handleDeleteButtonClick.bind(this)}" class="calendar-pub-ui__btn --inline --m --secondary">
							<div class="calendar-pub-ui__btn-text">${Loc.getMessage('CALENDAR_SHARING_POPUP_CANCEL')}</div>
						</div>
					</div>
				</div>
			`;

			if (Browser.isMobile())
			{
				this.popup = new BottomSheet({
					className: 'calendar-pub__state',
					content: popupContent,
					padding: '20px 25px'
				});
			}
			else
			{
				this.popup = new Popup({
					className: 'calendar-pub__popup',
					contentBackground: 'transparent',
					width: 380,
					animation: 'fading-slide',
					content: popupContent,
					overlay: true
				});
			}
		}

		return this.popup;
	}

	showCancelEventPopup()
	{
		this.getPopup().show();
	}

	closeCancelEventPopup()
	{
		this.getPopup().close();
	}

	async handleDeleteButtonClick()
	{
		this.closeCancelEventPopup();
		const isSuccess = await this.deleteEvent();
		if (isSuccess)
		{
			this.#value.canceledTimestamp = new Date().getTime() / 1000;
			this.#updateState('declined');
			EventEmitter.emit('onDeleteEvent');
		}
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
				}
			});
		}
		catch (e)
		{
			response = e;
		}

		return response.errors.length === 0;
	}

	async startVideoconference()
	{
		const response = await BX.ajax.runAction('calendar.api.sharingajax.getConferenceLink', {
			data: {
				eventLinkHash: this.#value.eventLinkHash,
			}
		});

		const conferenceLink = response.data?.conferenceLink || null;

		if (conferenceLink)
		{
			window.location.href = conferenceLink;
		}
	}

	async #onDownloadButtonClick()
	{
		Dom.addClass(this, '--wait');
		await this.downloadIcsFile();
		Dom.removeClass(this, '--wait');
	}

	async downloadIcsFile()
	{
		if (!this.#icsFile)
		{
			const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
				data: {
					eventLinkHash: this.#value.eventLinkHash
				},
			});
			this.icsFile = response.data;
		}

		Util.downloadIcsFile(this.icsFile, 'event');
	}

	#onReturnButtonClick()
	{
		EventEmitter.emit('onCreateAnotherEventButtonClick');
	}

	#getNodeWidgetDate(): HTMLElement
	{
		if (!this.#layout.widgetDate)
		{
			this.#layout.widgetDate = this.#widgetDate.render();
		}

		return this.#layout.widgetDate;
	}

	#getEventName()
	{
		return Loc.getMessage('CALENDAR_SHARING_EVENT_NAME', {
			'#OWNER_NAME#' : this.#owner.name + ' ' + this.#owner.lastName,
		});
	}
}
