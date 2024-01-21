import { Dom, Loc, Tag, Event } from 'main.core';
import SlotItem from './slot-item';
import { EventEmitter } from 'main.core.events';
import Base from '../base';
import { Util } from 'calendar.util';

type SlotListOptions = {
	isHiddenOnStart: boolean,
	ownerOffset: number,
}

export default class SlotList extends Base
{
	#layout;
	#slots;
	#selectedSlot;
	#timezoneNoticeWasUnderstood;
	#ownerTimezoneOffsetUtc;
	#selectedTimezoneOffsetUtc;

	constructor(options: SlotListOptions)
	{
		super({ isHiddenOnStart: options.isHiddenOnStart });
		this.#layout = {
			title: null,
			list: null,
			timezoneNotice: null,
			timezoneNoticeOffset: null,
		};
		this.#slots = [];

		this.#timezoneNoticeWasUnderstood = false;
		this.#ownerTimezoneOffsetUtc = -options.ownerOffset;
		this.#selectedTimezoneOffsetUtc = new Date().getTimezoneOffset();

		this.#bindEvents();
	}

	#bindEvents()
	{
		EventEmitter.subscribe('updateSlotsList', (event) => {
			this.#slots = event.data.slots;
			this.updateSlotsList();
		});
		EventEmitter.subscribe('selectSlot', (event) => {
			const newSelectedSlot = event.data;
			if (this.#selectedSlot !== newSelectedSlot)
			{
				this.#selectedSlot?.unSelect();
			}

			this.#selectedSlot = newSelectedSlot;
		});
		EventEmitter.subscribe('updateTimezone', (event) => {
			this.#selectedTimezoneOffsetUtc = Util.getTimeZoneOffset(event.getData().timezone);

			this.#hideTimezoneNotice();
			if (this.#shouldShowTimezoneNotice())
			{
				this.#showTimezoneNotice();
			}
		});
	}

	getType()
	{
		return 'slot-list';
	}

	getContent(): HTMLElement
	{
		return this.#getNodeSlotList();
	}

	updateSlotsList()
	{
		Dom.clean(this.#getNodeList());

		const slotListNode = this.#getNodeListItems();

		Dom.append(slotListNode, this.#getNodeList());
		Dom.removeClass(this.#getNodeList(), '--shadow-top');
		Dom.removeClass(this.#getNodeList(), '--shadow-bottom');
	}

	#getNodeSlotList(): HTMLElement
	{
		if (!this.#layout.slotSelector)
		{
			this.#layout.slotSelector = Tag.render`
				<div class="calendar-pub__slot-list-wrap">
					${this.#getNodeTitle()}
					${this.#getNodeList()}
				</div>
			`;

			if (this.#shouldShowTimezoneNotice())
			{
				this.#showTimezoneNotice();
			}
		}

		return this.#layout.slotSelector;
	}

	#getNodeTitle(): HTMLElement
	{
		if (!this.#layout.title)
		{
			this.#layout.title = Tag.render`
				<div class="calendar-sharing__calendar-bar">
					<div class="calendar-pub-ui__typography-m">${Loc.getMessage('CALENDAR_SHARING_SLOTS_FREE')}</div>
				</div>
			`;
		}

		return this.#layout.title;
	}

	#getNodeTimezoneNotice()
	{
		if (!this.#layout.timezoneNotice)
		{
			this.#layout.timezoneNotice = Tag.render`
				<div class="calendar-pub-timezone-notice calendar-pub-ui__typography-s">
					${this.#getNodeTimezoneNoticeText()}
					<div class="calendar-pub-timezone-notice-offset">
						${Loc.getMessage('CALENDAR_SHARING_TIMEZONE_NOTICE_OFFSET')}
					</div>
					${this.#getNodeTimezoneNoticeButton()}
				</div>
			`;
			this.#hideTimezoneNotice();
		}

		return this.#layout.timezoneNotice;
	}

	#getNodeTimezoneNoticeText()
	{
		if (!this.#layout.timezoneNoticeText)
		{
			this.#layout.timezoneNoticeText = Tag.render`
				<div>
					${Loc.getMessage('CALENDAR_SHARING_TIMEZONE_NOTICE')}
				</div>
			`;
		}

		return this.#layout.timezoneNoticeText;
	}

	#getNodeTimezoneNoticeButton()
	{
		const button = Tag.render`
			<div class="calendar-pub-ui__btn --m">
				<div class="calendar-pub-ui__btn-text">
					${Loc.getMessage('CALENDAR_SHARING_UNDERSTAND')}
				</div>
			</div>
		`;

		Event.bind(button, 'click', () => {
			this.#hideTimezoneNotice();
			this.#timezoneNoticeWasUnderstood = true;
		});

		return button;
	}

	#shouldShowTimezoneNotice()
	{
		const timezoneIsVeryDifferent = Math.abs(this.#ownerTimezoneOffsetUtc - this.#selectedTimezoneOffsetUtc) >= 180;

		return !this.#timezoneNoticeWasUnderstood && timezoneIsVeryDifferent;
	}

	#showTimezoneNotice()
	{
		const offset = this.#ownerTimezoneOffsetUtc - this.#selectedTimezoneOffsetUtc;
		this.#layout.timezoneNoticeText.innerText = this.#getTimezoneNoticeText(offset);
		Dom.style(this.#layout.timezoneNotice, 'display', '');
	}

	#getTimezoneNoticeText(offset)
	{
		const sign = (offset < 0) ? '+' : '-';
		return Loc.getMessage('CALENDAR_SHARING_TIMEZONE_NOTICE', {
			'#OFFSET#': `${sign}${Util.formatDuration(Math.abs(offset))}`,
		});
	}

	#hideTimezoneNotice()
	{
		Dom.style(this.#layout.timezoneNotice, 'display', 'none');
	}

	#getNodeList(): HTMLElement
	{
		if (!this.#layout.slots)
		{
			this.#layout.slots = Tag.render`
				<div class="calendar-sharing__calendar-block --overflow-hidden --shadow">
					${this.#getNodeListItems()}
				</div>
			`;
		}

		return this.#layout.slots;
	}

	#getNodeListItems(): HTMLElement
	{
		const currentDaySlots = this.#slots
			.map((slot) => new SlotItem({
				value: {
					from: slot.timeFrom,
					to: slot.timeTo,
				},
			}));

		const result = Tag.render`
			<div class="calendar-sharing__slots">
				${this.#getNodeTimezoneNotice()}
				${currentDaySlots.map((slotItem) => slotItem.render())}
			</div>
		`;

		Event.bind(result, 'scroll', () => {
			if (result.scrollTop > 0)
			{
				Dom.addClass(this.#getNodeList(), '--shadow-top');
			}
			else
			{
				Dom.removeClass(this.#getNodeList(), '--shadow-top');
			}

			if (result.scrollHeight > result.offsetHeight
				&& Math.ceil(result.offsetHeight + result.scrollTop) < result.scrollHeight)
			{
				Dom.addClass(this.#getNodeList(), '--shadow-bottom');
			}
			else
			{
				Dom.removeClass(this.#getNodeList(), '--shadow-bottom');
			}
		});

		setTimeout(() => {
			if (result.scrollHeight > result.offsetHeight)
			{
				Dom.addClass(this.#getNodeList(), '--shadow-bottom');
			}
		});

		return result;
	}
}
