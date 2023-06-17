import { Tag, Type } from 'main.core';
import { Form } from './form/index';
import { EmptyState } from './empty-state/index';
import { SlotList } from './slot-list/index';
import { Event } from './event/index';
import { EventEmitter } from "main.core.events";
import { AccessDenied } from "./empty-state/index";

type SlotSelectorOptions = {
	selectedTimezoneId: number,
	owner: any,
	link: any,
	sharingUser: any,
	hasContactData: boolean,
	calendarSettings: any,
	eventLinkHash: any,
	event: any,
	action: string,
	showBackCalendarButtons: any,
};
export default class SlotSelector
{
	#layout;
	#components;
	#selectedTimezoneId;
	#owner;
	#link;
	#sharingUser;
	#isFromCrm;
	#hasContactData;
	#calendarSettings;
	#eventLinkHash;
	#event;
	#action;
	#showBackCalendarButtons;

	BLOCK_NAME_FORM = 'form';
	BLOCK_NAME_SLOT_LIST = 'slot-list';
	BLOCK_NAME_EMPTY_STATE = 'empty-state';
	BLOCK_NAME_ACCESS_DENIED = 'access-denied';
	BLOCK_NAME_EVENT = 'event';

	constructor(options: SlotSelectorOptions)
	{
		this.#selectedTimezoneId = options.selectedTimezoneId;
		this.#owner = options.owner;
		this.#link = options.link;
		this.#sharingUser = options.sharingUser;
		this.#eventLinkHash = options.eventLinkHash;
		this.#event = options.event;

		this.#layout = {
			wrapper: null,
			empty: null,
			title: null,
			slots: null,
			slotSelector: null,
		}
		this.#components = {
			form: null,
			slotList: null,
			emptyState: null,
			event: null,
			accessDenied: null,
		};
		this.#isFromCrm = this.#link.type === 'crm_deal';
		this.#hasContactData = options.hasContactData;
		this.#calendarSettings = options.calendarSettings;
		this.#showBackCalendarButtons = options.showBackCalendarButtons;
		this.#action = options.action;

		this.#bindEvents();
		// EventEmitter.subscribe('selectorStateChange', this.showForm.bind(this));
		// EventEmitter.subscribe('hideForm', this.hideForm.bind(this));
	}

	#bindEvents()
	{
		EventEmitter.subscribe('confirmedSelectSlot', (event) => {
			const data = event.data;
			const value = data.value;
			this.#components.form.updateFormValue({
				from: value.from,
				to: value.to,
				timezone: this.#selectedTimezoneId,
			});
			this.openForm();
		});

		EventEmitter.subscribe('switchSlots', (event) => {
			const slots = event.data.slots ?? [];
			if (slots.length > 0)
			{
				EventEmitter.emit('updateSlotsList', event);
				this.openSlotList();
			}
			else
			{
				this.openEmptyState();
			}
		});

		EventEmitter.subscribe('updateTimezone', (event) => {
			const data = event.data;
			this.#selectedTimezoneId = data.timezone;
		});

		EventEmitter.subscribe('onSaveEvent', (event) => {
			const eventData = event.data;
			this.#components.form.cleanDescription();
			this.#components.event.updateValue(eventData);
			this.openEvent();
		});
	}

	openForm()
	{
		this.#components.form?.clearInputErrors();
		this.openBlock(this.BLOCK_NAME_FORM);
	}

	openSlotList()
	{
		this.openBlock(this.BLOCK_NAME_SLOT_LIST);
	}

	openEmptyState()
	{
		this.openBlock(this.BLOCK_NAME_EMPTY_STATE);
	}

	openAccessDenied()
	{
		this.openBlock(this.BLOCK_NAME_ACCESS_DENIED);
	}

	openEvent()
	{
		this.openBlock(this.BLOCK_NAME_EVENT);
	}

	openBlock(blockName)
	{
		EventEmitter.emit('selectorTypeChange', blockName);
	}

	render(): HTMLElement
	{
		if (!this.#components.form)
		{
			this.#components.form = new Form({
				isHiddenOnStart: true,
				owner: this.#owner,
				link: this.#link,
				sharingUser: this.#sharingUser,
				isFromCrm: this.#isFromCrm,
				hasContactData: this.#hasContactData,
				isPhoneFeatureEnabled: this.#calendarSettings.phoneFeatureEnabled,
				isMailFeatureEnabled: this.#calendarSettings.mailFeatureEnabled
			});
		}
		if (!this.#components.emptyState)
		{
			this.#components.emptyState = new EmptyState({isHiddenOnStart: true});
		}
		if (!this.#components.accessDenied)
		{
			this.#components.accessDenied = new AccessDenied({isHiddenOnStart: true});
		}
		if (!this.#components.slotList)
		{
			this.#components.slotList = new SlotList({isHiddenOnStart: false});
		}
		if (!this.#components.event)
		{
			let state = 'created';

			if (this.#link.active === false || this.#event?.meetingStatus === 'N' || this.#event?.deleted === 'Y')
			{
				state = 'declined';
			}

			let canceledByManager = false;
			if (this.#event?.meetingStatus === 'N')
			{
				canceledByManager = true;
			}

			this.#components.event = new Event({
				isHiddenOnStart: false,
				owner: this.#owner,
				event: this.#event,
				eventLinkHash: this.#eventLinkHash,
				state: state,
				eventId: this.#event.id,
				isView: Type.isString(this.#eventLinkHash),
				canceledByManager: canceledByManager,
				showBackCalendarButtons: this.#showBackCalendarButtons,
				action: this.#action,
			});
		}

		return Tag.render`
			<div class="calendar-pub__slots">
				${this.#components.slotList.render()}
				${this.#components.form.render()}
				${this.#components.emptyState.render()}
				${this.#components.event.render()}
				${this.#components.accessDenied.render()}
			</div>
		`;
	}
}
