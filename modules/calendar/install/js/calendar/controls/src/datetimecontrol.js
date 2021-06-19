"use strict";
import {Type, Event, Loc, Dom, Tag} from 'main.core';
import {Util} from 'calendar.util';
import {TimeSelector} from "./timeselector";
import {EventEmitter, BaseEvent} from 'main.core.events';

export class DateTimeControl extends EventEmitter
{
	DATE_INPUT_WIDTH = 110;
	TIME_INPUT_WIDTH = 70;
	MODIFIED_TIME_INPUT_WIDTH = 80;
	zIndex = 4200;

	constructor(uid, options = {showTimezone: true})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.DateTimeControl');

		this.showTimezone = options.showTimezone;
		this.inlineEditMode = !!options.inlineEditMode;
		this.currentInlineEditMode = options.currentInlineEditMode || 'view';

		this.UID = uid || 'date-time-' + Math.round(Math.random() * 100000);

		this.DOM = {
			outerWrap: options.outerWrap || null,
			outerContent: options.outerContent || null
		};

		this.create();
	}

	create()
	{
		if (Type.isDomNode(this.DOM.outerWrap))
		{
			if (this.inlineEditMode)
			{
				Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
			}

			this.DOM.leftInnerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field-block calendar-field-block-left"></div>`);

			this.DOM.fromDate = this.DOM.leftInnerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-datetime" value="" type="text" autocomplete="off" style="width: ${this.DATE_INPUT_WIDTH}px;"/>
			`);
			if (this.inlineEditMode)
			{
				this.DOM.fromDateText = this.DOM.leftInnerWrap.appendChild(Tag.render`<span class="calendar-field-value calendar-field-value-date"></span>`);
			}

			this.DOM.fromTime = this.DOM.leftInnerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-time" value="" type="text" autocomplete="off" style="width: ${this.TIME_INPUT_WIDTH}px;"/>
			`);
			if (this.inlineEditMode)
			{
				this.DOM.fromTimeText = this.DOM.leftInnerWrap.appendChild(Tag.render`<span class="calendar-field-value calendar-field-value-time"></span>`);
			}

			this.DOM.betweenSpacer = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field-block calendar-field-block-between" />`);

			this.DOM.rightInnerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field-block calendar-field-block-right"></div>`);

			this.DOM.toTime = this.DOM.rightInnerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-time" value="" type="text" autocomplete="off" style="width: ${this.TIME_INPUT_WIDTH}px;"/>
			`);
			if (this.inlineEditMode)
			{
				this.DOM.toTimeText = this.DOM.rightInnerWrap.appendChild(Tag.render`<span class="calendar-field-value calendar-field-value-time"></span>`);
			}

			this.DOM.toDate = this.DOM.rightInnerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-datetime" value="" type="text" autocomplete="off" style="width: ${this.DATE_INPUT_WIDTH}px;"/>`);

			if (this.inlineEditMode)
			{
				this.DOM.toDateText = this.DOM.rightInnerWrap.appendChild(Tag.render`<span class="calendar-field-value calendar-field-value-date"></span>`);
			}

			this.fromTimeControl = new TimeSelector({
				input: this.DOM.fromTime,
				onChangeCallback: this.handleTimeFromChange.bind(this)
			});

			this.toTimeControl = new TimeSelector({
				input: this.DOM.toTime,
				onChangeCallback: this.handleTimeToChange.bind(this)
			});

			let fullDayWrap = this.DOM.outerWrap.appendChild(Tag.render`
				<span class="calendar-event-full-day"></span>
			`);
			this.DOM.fullDay = fullDayWrap.appendChild(Tag.render`
				<input value="Y" type="checkbox" id="{this.UID}"/>
			`);
			fullDayWrap.appendChild(Tag.render`<label for="{this.UID}">${Loc.getMessage('EC_ALL_DAY')}</label>`);
		}

		//this.DOM.defTimezoneWrap = BX(this.UID + '_timezone_default_wrap');
		//this.DOM.defTimezone = BX(this.UID + '_timezone_default');

		if (this.showTimezone)
		{
			// this.DOM.fromTz = BX(this.UID + '_timezone_from');
			// this.DOM.toTz = BX(this.UID + '_timezone_to');
			// this.DOM.tzButton = BX(this.UID + '_timezone_btn');
			// this.DOM.tzOuterCont = BX(this.UID + '_timezone_wrap');
			// this.DOM.tzCont = BX(this.UID + '_timezone_inner_wrap');
			// BX(this.UID + '_timezone_hint').title = BX.message('EC_EVENT_TZ_HINT');
			// BX(this.UID + '_timezone_default_hint').title = BX.message('EC_EVENT_TZ_DEF_HINT');
		}

		this.bindEventHandlers();
	}

	setValue(value = {})
	{
		this.DOM.fromDate.value = Util.formatDate(value.from);
		this.DOM.toDate.value = Util.formatDate(value.to);

		this.lastDateValue = value.from;

		this.fromTimeControl.setValue(value.from);
		this.toTimeControl.setValue(value.to);

		this.DOM.fromTime.value = Util.formatTime(value.from);
		this.DOM.toTime.value = Util.formatTime(value.to);

		if (this.inlineEditMode)
		{
			this.DOM.fromDateText.innerHTML = Util.formatDateUsable(value.from, true, true);
			this.DOM.toDateText.innerHTML = Util.formatDateUsable(value.to, true, true);

			// Hide right part if it's the same date
			this.DOM.toDateText.style.display = this.DOM.fromDate.value === this.DOM.toDate.value ? 'none' : '';

			if (value.fullDay)
			{
				if (this.DOM.fromDate.value === this.DOM.toDate.value)
				{
					this.DOM.toTimeText.innerHTML = Loc.getMessage('EC_ALL_DAY');
					this.DOM.toTimeText.style.display = '';
					this.DOM.fromTimeText.style.display = 'none';
					this.DOM.fromTimeText.innerHTML = '';
				}
				else
				{
					this.DOM.betweenSpacer.style.display = '';
					this.DOM.fromTimeText.style.display = 'none';
					this.DOM.toTimeText.style.display = 'none';
				}
			}
			else
			{
				this.DOM.fromTimeText.innerHTML = this.DOM.fromTime.value;
				this.DOM.toTimeText.innerHTML = this.DOM.toTime.value;
				this.DOM.betweenSpacer.style.display = '';
				this.DOM.fromTimeText.style.display = '';
				this.DOM.toTimeText.style.display = '';
			}
		}

		if (value.fullDay !== undefined)
		{
			this.DOM.fullDay.checked = value.fullDay;
		}

		if (this.showTimezone)
		{
			value.timezoneFrom = value.timezoneFrom || value.timezoneName;
			value.timezoneTo = value.timezoneTo || value.timezoneName;

			if (value.timezoneFrom !== undefined && Type.isDomNode(this.DOM.fromTz))
			{
				this.DOM.fromTz.value = value.timezoneFrom;
			}
			if(value.timezoneTo !== undefined && Type.isDomNode(this.DOM.toTz))
			{
				this.DOM.toTz.value = value.timezoneTo;
			}

			if (value.timezoneName !== undefined
				&& (value.timezoneName !== value.timezoneFrom
					|| value.timezoneName !== value.timezoneTo))
			{
				this.switchTimezone(true);
			}
		}
		this.value = value;

		this.handleFullDayChange();
	}

	getValue()
	{
		let value = {
			fullDay: this.DOM.fullDay.checked,
			fromDate: this.DOM.fromDate.value,
			toDate: this.DOM.toDate.value,
			fromTime: this.DOM.fromTime.value,
			toTime: this.DOM.toTime.value,
			timezoneFrom: this.DOM.fromTz ? this.DOM.fromTz.value : (this.value.timezoneFrom || this.value.timezoneName || null),
			timezoneTo: this.DOM.toTz ? this.DOM.toTz.value : (this.value.timezoneTo || this.value.timezoneName || null)
		};

		value.from = Util.parseDate(value.fromDate);
		if (Type.isDate(value.from))
		{
			value.to = Util.parseDate(value.toDate);
			if (!Type.isDate(value.to))
			{
				value.to = value.from;
			}

			if (value.fullDay)
			{
				value.from.setHours(0, 0, 0);
				value.to.setHours(0, 0, 0);
			}
			else
			{
				let
					fromTime = Util.parseTime(value.fromTime),
					toTime = Util.parseTime(value.toTime) || fromTime;

				if (fromTime && toTime)
				{
					value.from.setHours(fromTime.h, fromTime.m, 0);
					value.to.setHours(toTime.h, toTime.m, 0);
				}
			}
		}

		return value;
	}

	bindEventHandlers()
	{
		Event.bind(this.DOM.fromDate, 'click', DateTimeControl.showInputCalendar);
		Event.bind(this.DOM.fromDate, 'change', this.handleDateFromChange.bind(this));

		Event.bind(this.DOM.toDate, 'click', DateTimeControl.showInputCalendar);
		Event.bind(this.DOM.toDate, 'change', this.handleDateToChange.bind(this));

		Event.bind(this.DOM.fullDay, 'click', () => {
			this.handleFullDayChange();
			this.handleValueChange();
		});

		if (this.inlineEditMode)
		{
			Event.bind(this.DOM.outerWrap, 'click', this.changeInlineEditMode.bind(this));
		}

		if (Type.isDomNode(this.DOM.defTimezone))
		{
			Event.bind(this.DOM.defTimezone, 'change', BX.delegate(function()
			{
				//this.calendar.util.setUserOption('timezoneName', this.DOM.defTimezone.value);
				if (this.bindFromToDefaultTimezones)
				{
					this.DOM.fromTz.value = this.DOM.toTz.value = this.DOM.defTimezone.value;
				}
			}, this));
		}

		if (this.showTimezone)
		{
			if (Type.isDomNode(this.DOM.tzButton))
			{
				Event.bind(this.DOM.tzButton, 'click', this.switchTimezone.bind(this));
			}

			Event.bind(this.DOM.fromTz, 'change', function()
			{
				if (this.bindTimezones)
				{
					this.DOM.toTz.value = this.DOM.fromTz.value;
				}
				this.bindFromToDefaultTimezones = false;
			}.bind(this));

			Event.bind(this.DOM.toTz, 'change', function()
			{
				this.bindTimezones = false;
				this.bindFromToDefaultTimezones = false;
			}.bind(this));

			this.bindTimezones = this.DOM.fromTz.value === this.DOM.toTz.value;
			this.bindFromToDefaultTimezones = this.bindTimezones
				&& this.DOM.fromTz.value === this.DOM.toTz.value
				&& this.DOM.fromTz.value === this.DOM.defTimezone.value;
		}
	}

	static showInputCalendar(e)
	{
		let target = e.target || e.srcElement;
		if (Type.isDomNode(target) && target.nodeName.toLowerCase() === 'input')
		{
			const calendarControl = BX.calendar.get();
			if (calendarControl.popup)
			{
				// Workaround hack for BX.calendar - it works as singleton and we trying to reinit it
				calendarControl.popup.destroy();
				calendarControl.popup = null;
				calendarControl._current_layer = null;
				calendarControl._layers = {};
			}
			calendarControl.Show({node: target.parentNode, field: target, bTime: false});
			BX.onCustomEvent(window, 'onCalendarControlChildPopupShown');

			const calendarPopup = calendarControl.popup;
			if (calendarPopup)
			{
				BX.removeCustomEvent(calendarPopup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
				BX.addCustomEvent(calendarPopup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
			}
		}
	}

	static inputCalendarClosePopupHandler(e)
	{
		BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	}

	handleDateFromChange()
	{
		let
			fromTime = Util.parseTime(this.DOM.fromTime.value),
			toTime = Util.parseTime(this.DOM.toTime.value),
			fromDate = Util.parseDate(this.DOM.fromDate.value),
			toDate = Util.parseDate(this.DOM.toDate.value);

		if (this.lastDateValue)
		{
			if (this.DOM.fullDay.checked && this.lastDateValue)
			{
				this.lastDateValue.setHours(0, 0, 0);
			}
			else
			{
				if (fromDate && fromTime)
				{
					fromDate.setHours(fromTime.h, fromTime.m, 0);
				}

				if (toDate && toTime)
				{
					toDate.setHours(toTime.h, toTime.m, 0);
				}
			}

			if (fromDate && this.lastDateValue)
			{
				toDate = new Date(fromDate.getTime()
					+ ((toDate.getTime() - this.lastDateValue.getTime()) || 3600000));

				if (toDate)
				{
					this.DOM.toDate.value = Util.formatDate(toDate);
				}
			}
		}
		this.lastDateValue = fromDate;

		this.handleValueChange();
	}

	handleTimeFromChange()
	{
		let
			fromTime = Util.parseTime(this.DOM.fromTime.value),
			toTime = Util.parseTime(this.DOM.toTime.value),
			fromDate = Util.parseDate(this.DOM.fromDate.value),
			toDate = Util.parseDate(this.DOM.toDate.value);

		if (fromDate && fromTime)
		{
			fromDate.setHours(fromTime.h, fromTime.m, 0);
		}

		if (toDate && toTime)
		{
			toDate.setHours(toTime.h, toTime.m, 0);
		}

		if (this.lastDateValue)
		{
			let newToDate = new Date(
				Util.getTimeRounded(fromDate) +
				Util.getTimeRounded(toDate)
				- Util.getTimeRounded(this.lastDateValue)
			);
			this.DOM.toTime.value = Util.formatTime(newToDate);
			this.DOM.toDate.value = Util.formatDate(newToDate);
		}

		this.lastDateValue = fromDate;
		this.handleValueChange();
	}

	handleDateToChange()
	{
		this.handleValueChange();
	}

	handleTimeToChange()
	{
		this.handleValueChange();
	}

	handleFullDayChange()
	{
		let fullDay = this.getFullDayValue();

		if (fullDay)
		{
			if (Type.isDomNode(this.DOM.dateTimeWrap))
			{
				Dom.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
			}

			if (Type.isDomNode(this.DOM.outerWrap))
			{
				Dom.addClass(this.DOM.outerWrap, 'calendar-options-item-datetime-hide-time');
			}
		}
		else
		{
			if (Type.isDomNode(this.DOM.dateTimeWrap))
			{
				Dom.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
			}
			if (Type.isDomNode(this.DOM.outerWrap))
			{
				Dom.removeClass(this.DOM.outerWrap, 'calendar-options-item-datetime-hide-time');
			}
		}
	}

	handleValueChange()
	{
		this.emit('onChange', new BaseEvent({data: {value: this.getValue()}}));
	}

	getFullDayValue()
	{
		return !!this.DOM.fullDay.checked;
	}

	switchTimezone(showTimezone)
	{
		if (!Type.isBoolean(showTimezone))
		{
			showTimezone = BX.hasClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
		}

		if (showTimezone)
		{
			Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
			Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
		}
		else
		{
			Dom.addClass(this.DOM.tzCont, 'calendar-options-timezone-collapse');
			Dom.removeClass(this.DOM.tzCont, 'calendar-options-timezone-expand');
		}
	}

	changeInlineEditMode()
	{
		if (!this.viewMode)
		{
			this.setInlineEditMode('edit');
		}
	}

	setViewMode(viewMode)
	{
		this.viewMode = viewMode;
		if (this.viewMode && this.currentInlineEditMode === 'edit')
		{
			this.setInlineEditMode('view');
		}
	}

	setInlineEditMode(currentInlineEditMode)
	{
		if (this.inlineEditMode)
		{
			this.currentInlineEditMode = currentInlineEditMode;
			if (this.currentInlineEditMode === 'edit')
			{
				Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-edit');
				Dom.removeClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
			}
			else
			{
				Dom.removeClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-edit');
				Dom.addClass(this.DOM.outerWrap, 'calendar-datetime-inline-mode-view');
			}
		}
	}
}
