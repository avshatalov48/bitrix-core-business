import {Type, Loc, Dom, Event, Tag, Browser, BookingUtil, MenuManager} from "../resourcebooking";
import {ViewControlAbstract} from "../viewcontrolabstract";

export class TimeSelector extends ViewControlAbstract
{
	constructor(params)
	{
		super(params);
		this.DOM = {
			outerWrap: params.outerWrap,
			wrap: null
		};

		this.data = params.data || {};
		this.setDataConfig();

		this.timeFrom = this.data.timeFrom || params.timeFrom || 7;
		if (params.timeFrom !== undefined)
		{
			this.timeFrom = params.timeFrom;
		}
		this.timeTo = this.data.timeTo || 20;
		if (params.timeTo !== undefined)
		{
			this.timeTo = params.timeTo;
		}
		this.SLOTS_ROW_AMOUNT = 6;
		this.id = 'time-selector-' + Math.round(Math.random() * 1000);
		this.popupSelectId = this.id + '-select-popup';

		this.previewMode = params.previewMode === undefined;
		this.changeValueCallback = params.changeValueCallback;
		this.timezone = params.timezone;
		this.timezoneOffset = params.timezoneOffset;
		this.timezoneOffsetLabel = params.timezoneOffsetLabel;
		this.timeMidday = 12;
		this.timeEvening = 17;
		this.displayed = true;
	}

	setDataConfig()
	{
		let
			style = this.data.style === 'select' ? 'select' : 'slots', // select|slots
			showOnlyFree = this.data.showOnlyFree !== 'N',
			showFinishTime = this.data.showFinishTime === 'Y',
			scale = parseInt(this.data.scale || 30),
			configWasChanged = this.style !== style || this.showOnlyFree !== showOnlyFree || this.showFinishTime !== showFinishTime || this.scale !== scale;

		this.style = style;
		this.showOnlyFree = showOnlyFree;
		this.showFinishTime = showFinishTime;
		this.scale = scale;

		return configWasChanged;
	}

	display()
	{
		this.DOM.wrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block"></div>`);
		this.DOM.innerWrap = this.DOM.wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-inner"></div>`);
		if (this.data.label)
		{
			this.DOM.labelWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {
				props: {className: 'calendar-resbook-webform-block-title'},
				text: this.data.label + '*'
			}));

			if (this.timezone)
			{
				this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-title-timezone"></div>`);
				Dom.adjust(this.DOM.timezoneLabelWrap, {html: Loc.getMessage('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)});
			}
		}

		this.displayControl();
		this.setValue(this.getValue());
		this.shown = true;
	}

	refresh(data, params)
	{
		params = params || {};
		this.setSlotIndex(params.slotIndex);
		this.currentDate = params.currentDate || new Date();
		this.data = data;

		if (!this.isShown())
		{
			this.setDataConfig();
			this.display();
		}
		else
		{
			if (this.DOM.labelWrap && this.data.label)
			{
				Dom.adjust(this.DOM.labelWrap, {text: this.data.label + '*'});
			}

			if (this.timezone)
			{
				if (!this.DOM.timezoneLabelWrap
					|| !this.DOM.labelWrap.contains(this.DOM.timezoneLabelWrap))
				{
					this.DOM.timezoneLabelWrap = this.DOM.labelWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-title-timezone"></div>`);

				}
				Dom.adjust(this.DOM.timezoneLabelWrap, {html: Loc.getMessage('USER_TYPE_RESOURCE_TIMEZONE').replace('#TIMEZONE#', this.timezone + ' ' + this.timezoneOffsetLabel)});
			}

			if (this.setDataConfig() || params.slotIndex || params.selectedValue)
			{
				Dom.remove(this.DOM.controlWrap);
				this.displayControl();
			}
		}

		this.setCurrentValue(params.selectedValue || this.getValue());
	}

	setSlotIndex(slotIndex)
	{
		if (Type.isPlainObject(slotIndex))
		{
			this.availableSlotIndex = slotIndex;
		}
	}

	setCurrentValue(timeValue)
	{
		if (timeValue && (this.previewMode || this.availableSlotIndex[timeValue]))
		{
			this.setValue(timeValue);
		}
		else
		{
			this.setValue(null);
		}
	}

	showEmptyWarning()
	{
		if (this.DOM.labelWrap)
		{
			this.DOM.labelWrap.style.display = 'none';
		}

		if (!this.DOM.warningWrap)
		{
			this.DOM.warningTextNode = Tag.render`<span class="calendar-resbook-webform-block-notice-date"/>`;

			this.DOM.warningWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {
				props: {className: 'calendar-resbook-webform-block-notice'},
				children: [
					Tag.render`<span class="calendar-resbook-webform-block-notice-icon"/>`,
					this.DOM.warningTextNode,
					Dom.create("span", {
						props: {className: 'calendar-resbook-webform-block-notice-detail'},
						text: Loc.getMessage('WEBF_RES_BOOKING_BUSY_DAY_WARNING')
					})
				]
			}));
		}

		if (this.DOM.warningWrap)
		{
			Dom.adjust(this.DOM.warningTextNode, {text: BookingUtil.formatDate(Loc.getMessage('WEBF_RES_BUSY_DAY_DATE_FORMAT'), this.currentDate)});
			this.DOM.warningWrap.style.display = '';

			this.noSlotsAvailable = true;
		}
	}

	hideEmptyWarning()
	{
		this.noSlotsAvailable = false;
		if (this.DOM.labelWrap)
		{
			this.DOM.labelWrap.style.display = '';
		}
		if (this.DOM.warningWrap)
		{
			this.DOM.warningWrap.style.display = 'none';
		}
	}

	displayControl()
	{
		let slotsInfo = this.getSlotsInfo();
		this.slots = slotsInfo.slots;

		if (!slotsInfo.freeSlotsCount)
		{
			this.showEmptyWarning();
		}
		else
		{
			this.hideEmptyWarning();
			if (this.style === 'select')
			{
				this.createSelectControl();
			}
			else if (this.style === 'slots')
			{
				this.createSlotsControl();
			}
		}
	}

	hide()
	{
		if (this.DOM.innerWrap)
		{
			this.DOM.innerWrap.style.display = 'none';
		}
	}

	show()
	{
		if (this.DOM.innerWrap)
		{
			this.DOM.innerWrap.style.display = '';
		}
	}

	createSlotsControl()
	{
		if (this.DOM.controlWrap)
		{
			Dom.remove(this.DOM.controlWrap);
		}

		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(
			Dom.create("div", {
				props: {className: 'calendar-resbook-webform-block-time'},
				events: {click: this.handleClick.bind(this)}
			}));

		if (!this.showFinishTime && !BookingUtil.isAmPmMode())
		{
			Dom.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-sm');
		}
		else if (!this.showFinishTime && BookingUtil.isAmPmMode())
		{
			Dom.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-md');
		}
		else if (BookingUtil.isAmPmMode())
		{
			Dom.addClass(this.DOM.controlWrap, 'calendar-resbook-webform-block-time-lg');
		}

		this.DOM.controlStaticWrap = this.DOM.controlWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-time-static-wrap"></div>`);
		this.DOM.controlInnerWrap = this.DOM.controlStaticWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-time-inner-wrap"></div>`);

		let
			itemsInColumn,
			maxColumnNumber = 3,
			parts = {},
			itemNumber = 0,
			innerWrap;

		// FilterSlots
		this.slots.forEach(function(slot)
		{
			if (!parts[slot.partOfTheDay])
			{
				parts[slot.partOfTheDay] = {
					items: []
				};
			}

			parts[slot.partOfTheDay].items.push(slot);
		});

		this.slots.forEach(function(slot)
		{
			if (!parts[slot.partOfTheDay].wrap)
			{
				itemNumber = 0;
				itemsInColumn = 6;
				parts[slot.partOfTheDay].wrap = Dom.create("div", {
					props: {className: 'calendar-resbook-webform-block-col'},
					html: '<span class="calendar-resbook-webform-block-col-title">'
						+ Loc.getMessage('WEBF_RES_PART_OF_THE_DAY_' + slot.partOfTheDay.toUpperCase())
						+ '</span>'
				});

				parts[slot.partOfTheDay].itemsWrap = parts[slot.partOfTheDay].wrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-col-list"></div>`);

				if (parts[slot.partOfTheDay].items.length > maxColumnNumber * itemsInColumn)
				{
					itemsInColumn = Math.ceil(parts[slot.partOfTheDay].items.length / maxColumnNumber);
				}
			}

			if (itemNumber % itemsInColumn === 0)
			{
				innerWrap = parts[slot.partOfTheDay].itemsWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-col-list-inner"></div>`);
			}

			if (innerWrap && (!slot.booked || !this.showOnlyFree))
			{
				innerWrap.appendChild(Dom.create("div", {
					attrs: {
						'data-bx-resbook-time-meta': 'slot' + (slot.booked ? '-off' : ''),
						'data-bx-resbook-slot': slot.time.toString(),
						className: 'calendar-resbook-webform-block-col-item'
							+ (slot.selected ? ' calendar-resbook-webform-block-col-item-select' : '')
							+ (slot.booked ? ' calendar-resbook-webform-block-col-item-off' : '')
					},
					html: '<div class="calendar-resbook-webform-block-col-item-inner">' + '<span class="calendar-resbook-webform-block-col-time">' + slot.fromTime + '</span>' + (this.showFinishTime ? '- <span class="calendar-resbook-webform-block-col-time calendar-resbook-webform-block-col-time-end">' + slot.toTime + '</span>' : ''
					) + '</div>'
				}));
				itemNumber++;
			}

			parts[slot.partOfTheDay].itemsAmount = itemNumber;
		}, this);

		let k;
		for (k in parts)
		{
			if (parts.hasOwnProperty(k) && parts[k].itemsAmount > 0)
			{
				this.DOM.controlInnerWrap.appendChild(parts[k].wrap);
			}
		}

		this.initCustomScrollForSlots();
	}

	createSelectControl()
	{
		if (this.DOM.controlWrap)
		{
			Dom.remove(this.DOM.controlWrap);
		}

		this.DOM.controlWrap = this.DOM.innerWrap.appendChild(Dom.create("div", {
			props: {className: 'calendar-resbook-webform-block-field'},
			events: {click: this.handleClick.bind(this)}
		}));

		this.DOM.timeSelectWrap = this.DOM.controlWrap.appendChild(Dom.create("div", {
			props: {className: 'calendar-resbook-webform-block-strip'}
		}));
		this.DOM.valueInput = this.DOM.timeSelectWrap.appendChild(Dom.create("input", {
			attrs: {
				type: 'hidden',
				value: ''
			}
		}));

		this.DOM.previousArrow = this.DOM.timeSelectWrap.appendChild(Dom.create("span", {
			attrs: {
				className: 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-prev',
				'data-bx-resbook-time-meta': 'previous'
			}
		}));

		this.DOM.stateWrap = this.DOM.timeSelectWrap.appendChild(Dom.create("span", {
			attrs: {
				className: 'calendar-resbook-webform-block-strip-text',
				'data-bx-resbook-time-meta': 'select'
			}
		}));
		this.DOM.stateWrap = this.DOM.stateWrap.appendChild(Dom.create("span", {props: {className: 'calendar-resbook-webform-block-strip-date'}}));

		this.DOM.nextArrow = this.DOM.timeSelectWrap.appendChild(Dom.create("span", {
			attrs: {
				className: 'calendar-resbook-webform-block-strip-arrow calendar-resbook-webform-block-strip-arrow-next',
				'data-bx-resbook-time-meta': 'next'
			}
		}));

		this.setValue(this.getValue());
	}

	setValue(value)
	{
		let slot = this.getSlotByTime(value);
		if (slot)
		{
			if (this.style === 'select' && Type.isDomNode(this.DOM.stateWrap))
			{
				Dom.adjust(this.DOM.stateWrap, {text: this.getTimeTextBySlot(slot)});
			}
			else if (this.style === 'slots')
			{
				this.setSelected(this.getSlotNode(slot.time));
			}
			this.value = slot.time;
		}
		else
		{
			this.value = null;
		}

		if (!this.previewMode && Type.isFunction(this.changeValueCallback))
		{
			this.changeValueCallback(this.value);
		}
	}

	getValue()
	{
		if (!this.value && (this.previewMode || this.style === 'select'))
		{
			this.value = this.slots[0].time;
		}
		return this.value;
	}

	hasAvailableSlots()
	{
		return !this.noSlotsAvailable;
	}

	getTimeTextBySlot(slot)
	{
		return slot.fromTime + (this.showFinishTime ? ' - ' + slot.toTime : '');
	}

	getSlotByTime(time)
	{
		return Type.isArray(this.slots) ? this.slots.find(function(slot){return parseInt(slot.time) === parseInt(time);}) : null;
	}

	handleClick(e)
	{
		let target = e.target || e.srcElement;
		if (target.hasAttribute('data-bx-resbook-time-meta') ||
			(target = target.closest('[data-bx-resbook-time-meta]')))
		{
			let meta = target.getAttribute('data-bx-resbook-time-meta');
			if (this.style === 'select')
			{
				if (meta === 'previous')
				{
					this.setValue(this.getValue() - this.scale);
				}
				else if (meta === 'next')
				{
					this.setValue(this.getValue() + this.scale);
				}
				else if (meta === 'select')
				{
					this.openSelectPopup();
				}
			}
			else if (meta === 'slot')
			{
				this.setValue(parseInt(target.getAttribute('data-bx-resbook-slot')));
			}
		}
	}

	getSlotsInfo()
	{
		let
			slots = [], slot,
			freeSlotsCount = 0,
			finishTime, hourFrom, minFrom,
			hourTo, minTo,
			part = 'morning',
			num = 0,
			time = this.timeFrom * 60;

		while (time < this.timeTo * 60)
		{
			if (time >= this.timeEvening * 60)
			{
				part = 'evening';
			}
			else if (time >= this.timeMidday * 60)
			{
				part = 'afternoon';
			}

			hourFrom = Math.floor(time / 60);
			minFrom = (time) - hourFrom * 60;
			finishTime = time + this.scale;
			hourTo = Math.floor(finishTime / 60);
			minTo = (finishTime) - hourTo * 60;

			slot = {
				time: time,
				fromTime: BookingUtil.formatTime(hourFrom, minFrom),
				toTime: BookingUtil.formatTime(hourTo, minTo),
				partOfTheDay: part
			};

			if (this.previewMode)
			{
				if (!num)
				{
					slot.selected = true;
				}
				else if (Math.round(Math.random() * 10) <= 3)
				{
					slot.booked = true;
				}
			}
			else if(this.availableSlotIndex)
			{
				slot.booked = !this.availableSlotIndex[time];
			}

			if (!slot.booked)
			{
				freeSlotsCount++;
			}

			slots.push(slot);
			time += this.scale;
			num++;
		}

		return {
			slots: slots,
			freeSlotsCount: freeSlotsCount
		};
	}

	initCustomScrollForSlots()
	{
		let arrowWrap = this.DOM.controlWrap.appendChild(Tag.render`<div class="calendar-resbook-webform-block-arrow-container" />`);

			this.DOM.leftArrow = arrowWrap.appendChild(Dom.create("span",
			{
				props : {className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-prev'},
				events: {click: this.handlePreletrowClick.bind(this)}
			}));
		this.DOM.rightArrow = arrowWrap.appendChild(Dom.create("span",
			{
				props : { className : 'calendar-resbook-webform-block-arrow calendar-resbook-webform-block-arrow-next'},
				events: {click: this.handleNextArrowClick.bind(this)}
			}));

		this.outerWidth = parseInt(this.DOM.controlStaticWrap.offsetWidth);
		this.innerWidth = parseInt(this.DOM.controlInnerWrap.offsetWidth);

		if ('onwheel' in document)
			Event.bind(this.DOM.controlStaticWrap, "wheel", this.mousewheelScrollHandler.bind(this));
		else
			Event.bind(this.DOM.controlStaticWrap, "mousewheel", this.mousewheelScrollHandler.bind(this));

		this.checkSlotsScroll();
	}

	handleNextArrowClick()
	{
		this.DOM.controlStaticWrap.scrollLeft = this.DOM.controlStaticWrap.scrollLeft + 100;
		this.checkSlotsScroll();
	}

	handlePreletrowClick()
	{
		this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft - 100, 0);
		this.checkSlotsScroll();
	}

	mousewheelScrollHandler(e)
	{
		e = e || window.event;
		let delta = e.deltaY || e.detail || e.wheelDelta;
		if (Math.abs(delta) > 0)
		{
			if (!Browser.isMac())
			{
				delta = delta * 5;
			}
			this.DOM.controlStaticWrap.scrollLeft = Math.max(this.DOM.controlStaticWrap.scrollLeft + delta, 0);
			this.checkSlotsScroll();
			if(e.stopPropagation)
			{
				e.preventDefault();
				e.stopPropagation();
			}
			return false;
		}
	}

	checkSlotsScroll()
	{
		if (this.outerWidth <= this.innerWidth)
		{
			this.DOM.leftArrow.style.display = this.DOM.controlStaticWrap.scrollLeft ? '' : 'none';
			if (this.innerWidth - this.outerWidth - 4 <= this.DOM.controlStaticWrap.scrollLeft)
			{
				this.DOM.rightArrow.style.display = 'none';
			}
			else
			{
				this.DOM.rightArrow.style.display = '';
			}
		}
	}

	openSelectPopup()
	{
		if (this.isSelectPopupShown())
		{
			return this.closeSelectPopup();
		}

		this.popup = MenuManager.create(
			this.popupSelectId,
			this.DOM.stateWrap,
			this.getTimeSelectItems(),
			{
				className: "calendar-resbook-time-select-popup"	,
				angle: true,
				closeByEsc : true,
				autoHide : true,
				offsetTop: 5,
				offsetLeft: 10,
				cacheable: false
			}
		);

		this.popup.show(true);
	}

	closeSelectPopup()
	{
		if (this.isSelectPopupShown())
		{
			this.popup.close();
			Event.unbind(document, 'click', this.handleClick.bind(this));
		}
	}

	isSelectPopupShown()
	{
		return this.popup && this.popup.popupWindow &&
			this.popup.popupWindow.isShown && this.popup.popupWindow.isShown();
	}

	getTimeSelectItems()
	{
		let menuItems = [];
		this.slots.forEach(function(slot)
		{
			if (this.showOnlyFree && slot.booked)
			{
				return;
			}
			let className = 'menu-popup-no-icon';
			if (slot.booked)
			{
				className += ' menu-item-booked';
			}
			if (slot.selected)
			{
				className += ' menu-item-selected';
			}

			menuItems.push(
				{
					className: className,
					text: this.getTimeTextBySlot(slot),
					dataset: {
						value: slot.time,
						booked: !!slot.booked
					},
					onclick: this.menuItemClick.bind(this)
				}
			);
		}, this);
		return menuItems;
	}

	menuItemClick(e, menuItem)
	{
		if (menuItem && menuItem.dataset && menuItem.dataset.value)
		{
			if (!menuItem.dataset.booked)
			{
				this.setValue(menuItem.dataset.value);
			}
		}
		this.closeSelectPopup();
	}

	getSlotNode(time)
	{
		let i, slotNodes = this.DOM.controlInnerWrap.querySelectorAll('.calendar-resbook-webform-block-col-item');
		for (i = 0; i < slotNodes.length; i++)
		{
			if (parseInt(slotNodes[i].getAttribute('data-bx-resbook-slot')) === parseInt(time))
			{
				return slotNodes[i];
			}
		}
		return null;
	}

	setSelected(slotNode)
	{
		if (Type.isDomNode(slotNode))
		{
			if (this.currentSelected)
			{
				Dom.removeClass(this.currentSelected, 'calendar-resbook-webform-block-col-item-select');
			}
			this.currentSelected = slotNode;
			Dom.addClass(slotNode, 'calendar-resbook-webform-block-col-item-select');
		}
	}
}

