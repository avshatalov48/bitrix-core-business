"use strict";
import {Type, Event, Loc, Dom, Tag} from 'main.core';
import {Util} from 'calendar.util';
import {TimeSelector} from "./timeselector";
import {EventEmitter, BaseEvent} from 'main.core.events';

export class DateTimeControl extends EventEmitter
{
	constructor(uid, options = {showTimezone: true})
	{
		super();
		this.setEventNamespace('BX.Calendar.Controls.DateTimeControl');

		this.showTimezone = options.showTimezone;

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
			this.DOM.fromDate = this.DOM.outerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-datetime" value="" type="text" autocomplete="off" style="width: 120px;"/>
			`);

			this.DOM.fromTime = this.DOM.outerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-datetime-menu" value="" type="text" autocomplete="off"/>
			`);

			this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field-block calendar-field-block-between" />`);

			this.DOM.toTime = this.DOM.outerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-datetime-menu" value="" type="text" autocomplete="off"/>
			`);

			this.DOM.toDate = this.DOM.outerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-datetime" value="" type="text" autocomplete="off" style="width: 120px;"/>
			`);

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
			timezoneFrom: this.DOM.fromTz ? this.DOM.fromTz.value : null,
			timezoneTo: this.DOM.toTz ? this.DOM.toTz.value : null
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

		Event.bind(this.DOM.fullDay, 'click', this.handleFullDayChange.bind(this));


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
			BX.calendar({node: target.parentNode, field: target, bTime: false});
			BX.onCustomEvent(window, 'onCalendarControlChildPopupShown');

			if (BX.calendar.get().popup)
			{
				BX.removeCustomEvent(BX.calendar.get().popup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
				BX.addCustomEvent(BX.calendar.get().popup, 'onPopupClose', DateTimeControl.inputCalendarClosePopupHandler);
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

		// if (fullDay && this.calendar.util.getUserOption('timezoneName')
		// 	&& (!this.DOM.fromTz.value || !this.DOM.toTz.value))
		// {
		// 	this.DOM.fromTz.value = this.DOM.toTz.value = this.DOM.defTimezone.value = this.calendar.util.getUserOption('timezoneName');
		// }

		if (fullDay)
		{
			Dom.addClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
		}
		else
		{
			Dom.removeClass(this.DOM.dateTimeWrap, 'calendar-options-item-datetime-hide-time');
		}
		this.handleValueChange();
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
}