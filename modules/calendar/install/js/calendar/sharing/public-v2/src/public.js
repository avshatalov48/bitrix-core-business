import { Tag, Type, Dom } from 'main.core';
import Welcome from './components/welcome';
import { Calendar } from './components/calendar/index';
import { SlotSelector } from './components/slot-selector/index';
import { EventEmitter } from 'main.core.events';

import 'ui.design-tokens';
import './style.css';

type PublicOptions = {
	target: HTMLElement,
	link: any,
	parentLink: any,
	event: any,
	action: string,
	currentLang: string,
	owner: any,
}

export default class PublicV2
{
	#layout;
	#owner;
	#welcomePage;
	#calendar;
	#slotsBlock;

	#linkMembers;
	#eventMembers;

	constructor(options: PublicOptions)
	{
		this.#owner = options.owner || null;
		this.target = Type.isDomNode(options.target) ? options.target : null;
		this.#layout = {
			wrapper: null,
			animate: null,
		};
		this.#welcomePage = null;
		this.#calendar = null;
		this.#slotsBlock = null;

		this.#linkMembers = (options.parentLink || options.link).members;
		this.#eventMembers = options.link.members ?? options.event.members;

		this.#init();
		this.#bindEvents();

		this.showPageWelcome(options);

		if (options.link.type === 'event')
		{
			if (options.parentLink && options.parentLink.active === true)
			{
				this.#renderFreeSlots(options);
				this.#welcomePage.handleWelcomePageButtonClick();
				this.#slotsBlock.openEvent();
			}
			else if (options.event)
			{
				this.#renderSlotsSelector(options);
				this.#welcomePage.handleWelcomePageButtonClick();
				this.#welcomePage.hideButton();
				this.#welcomePage.setAccessDenied();
				this.#slotsBlock.openEvent();
			}
			else
			{
				this.#renderSlotsSelector(options);
				this.#welcomePage.handleWelcomePageButtonClick();
				this.#welcomePage.hideButton();
				this.#slotsBlock.openAccessDenied();
			}
		}
		else if (options.link.active === true)
		{
			this.#renderFreeSlots(options);
		}
		else
		{
			this.#renderSlotsSelector(options);
			this.#welcomePage.handleWelcomePageButtonClick();
			this.#welcomePage.hideButton();
			this.#slotsBlock.openAccessDenied();
		}

		if (options.action === 'opened')
		{
			this.#welcomePage.handleWelcomePageButtonClick();
		}

		// this.showFreeSlots();
	}

	#bindEvents()
	{
		EventEmitter.subscribe('showSlotSelector', this.showFreeSlots.bind(this));
		EventEmitter.subscribe('hideSlotSelector', this.hideFreeSlots.bind(this));
	}

	showPageWelcome(options)
	{
		if (!options.owner)
		{
			return;
		}

		this.#welcomePage = new Welcome({
			owner: options.owner,
			link: options.link,
			currentLang: options.currentLang,
			members: this.#linkMembers,
		});

		Dom.append(this.#welcomePage.render(), this.#getNodeWrapper());
	}

	#renderFreeSlots(options)
	{
		this.#calendar = new Calendar({
			userIds: options.link.userIds,
			accessibility: options.userAccessibility,
			timezoneList: options.timezoneList,
			calendarSettings: options.calendarSettings,
			rule: options.link.rule,
		});

		let eventLinkHash = null;
		if (options.link.type === 'event')
		{
			eventLinkHash = options.link.hash;
		}

		this.#slotsBlock = new SlotSelector({
			selectedTimezoneId: this.#calendar.getSelectedTimezoneId(),
			owner: this.#owner,
			link: options.parentLink || options.link,
			members: this.#eventMembers,
			sharingUser: options.sharingUser,
			hasContactData: options.hasContactData,
			calendarSettings: options.calendarSettings,
			event: options.event,
			showBackCalendarButtons: true,
			eventLinkHash,
			action: options.action,
		});

		const firstNodeWrapper = Tag.render`
			<div class="calendar-pub__block --plus">
				${this.#calendar.render()}
			</div>
		`;

		this.#layout.animate = Tag.render`
			<div class="calendar-pub__block-animate">
				${firstNodeWrapper}
				<div class="calendar-pub__block">
					${this.#slotsBlock.render()}
				</div>
			</div>
		`;

		EventEmitter.subscribe('selectorTypeChange', (ev) => {
			if (ev.data === 'form' || ev.data === 'event')
			{
				Dom.addClass(firstNodeWrapper, '--hidden');
			}
			else
			{
				Dom.removeClass(firstNodeWrapper, '--hidden');
			}
		});

		Dom.append(this.#layout.animate, this.#getNodeWrapper());

		if (options.link.type !== 'event')
		{
			this.#calendar.selectFirstAvailableDay();
		}
	}

	#renderSlotsSelector(options)
	{
		let eventLinkHash = null;
		if (options.link.type === 'event')
		{
			eventLinkHash = options.link.hash;
		}

		this.#slotsBlock = new SlotSelector({
			selectedTimezoneId: null,
			owner: this.#owner,
			link: options.link,
			sharingUser: options.sharingUser,
			hasContactData: options.hasContactData,
			calendarSettings: options.calendarSettings,
			event: options.event,
			showBackCalendarButtons: false,
			action: options.action,
			eventLinkHash,
		});

		this.#layout.animate = Tag.render`
			<div class="calendar-pub__block-animate">
				<div class="calendar-pub__block">
					${this.#slotsBlock.render()}
				</div>
			</div>
		`;

		Dom.append(this.#layout.animate, this.#getNodeWrapper());
	}

	showFreeSlots()
	{
		Dom.removeClass(this.#getNodeWrapper(), '--hide');
	}

	hideFreeSlots()
	{
		Dom.addClass(this.#getNodeWrapper(), '--hide');
	}

	#getNodeWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-pub__wrapper calendar-pub__state --hide"></div>
			`;

			if (Type.isArrayFilled(this.#linkMembers) || Type.isArrayFilled(this.#eventMembers))
			{
				Dom.addClass(this.#layout.wrapper, '--large');
			}
		}

		return this.#layout.wrapper;
	}

	#render()
	{
		if (!this.target)
		{
			console.warn('BX.Calendar.Sharing: "target" is not defined');

			return;
		}

		if (this.target.parentNode)
		{
			Dom.append(this.#getNodeWrapper(), this.target.parentNode);
			Dom.remove(this.target);
		}
	}

	#init(): void
	{
		this.#render();
	}
}
