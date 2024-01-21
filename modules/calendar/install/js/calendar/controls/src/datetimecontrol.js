"use strict";
import {Type, Event, Loc, Dom, Tag} from 'main.core';
import {Util} from 'calendar.util';
import {TimeSelector} from "./timeselector";
import {EventEmitter, BaseEvent} from 'main.core.events';

export class DateTimeControl extends EventEmitter
{
	DATE_INPUT_WIDTH = 110;
	TIME_INPUT_WIDTH = 90;
	zIndex = 4200;

	from = null;
	to = null;

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
				<input class="calendar-field calendar-field-time" value="" type="text" autocomplete="off" style="width: ${this.TIME_INPUT_WIDTH}px; max-width: ${this.TIME_INPUT_WIDTH}px;"/>
			`);
			if (this.inlineEditMode)
			{
				this.DOM.fromTimeText = this.DOM.leftInnerWrap.appendChild(Tag.render`<span class="calendar-field-value calendar-field-value-time"></span>`);
			}

			this.DOM.betweenSpacer = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field-block calendar-field-block-between" />`);

			this.DOM.rightInnerWrap = this.DOM.outerWrap.appendChild(Tag.render`<div class="calendar-field-block calendar-field-block-right"></div>`);

			this.DOM.toTime = this.DOM.rightInnerWrap.appendChild(Tag.render`
				<input class="calendar-field calendar-field-time" value="" type="text" autocomplete="off" style="width: ${this.TIME_INPUT_WIDTH}px; max-width: ${this.TIME_INPUT_WIDTH}px;"/>
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

		this.DOM.fromTime.value = Util.formatTime(value.from);
		this.DOM.toTime.value = Util.formatTime(value.to);

		const parsedFromTime = Util.parseTime(this.DOM.fromTime.value);
		const parsedToTime = Util.parseTime(this.DOM.toTime.value);
		this.fromMinutes = parsedFromTime.h * 60 + parsedFromTime.m;
		this.toMinutes = parsedToTime.h * 60 + parsedToTime.m;

		this.updateTimePeriod();

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
		this.emit('onSetValue');
	}

	updateTimePeriod()
	{
		this.from = this.getFrom();
		this.to = this.getTo();
		this.fromTimeControl.highlightValue(this.from);
		this.toTimeControl.highlightValue(this.to);
		this.updateToTimeDurationHints();
	}

	getFrom()
	{
		return this.getDateWithTime(this.DOM.fromDate.value, this.fromMinutes);
	}

	getTo()
	{
		return this.getDateWithTime(this.DOM.toDate.value, this.toMinutes);
	}

	getDateWithTime(date, minutes)
	{
		const parsedDate = Util.parseDate(date);
		if (!parsedDate)
		{
			return null;
		}
		return new Date(parsedDate.getTime() + minutes * 60 * 1000);
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

		Event.bind(this.DOM.fromTime, 'input', this.handleTimeInput.bind(this));
		Event.bind(this.DOM.toTime, 'input', this.handleTimeInput.bind(this));

		Event.bind(this.DOM.fromTz, 'change', this.handleValueChange.bind(this));
		Event.bind(this.DOM.toTz, 'change', this.handleValueChange.bind(this));

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
			if (calendarControl.popup_month)
			{
				calendarControl.popup_month.destroy();
				calendarControl.popup_month = null;
			}
			if (calendarControl.popup_year)
			{
				calendarControl.popup_year.destroy();
				calendarControl.popup_year = null;
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

	static inputCalendarClosePopupHandler()
	{
		BX.onCustomEvent(window, 'onCalendarControlChildPopupClosed');
	}

	handleDateFromChange()
	{
		if (!this.getFrom())
		{
			this.DOM.fromDate.value = Util.formatDate(this.from.getTime());
			return;
		}
		this.DOM.fromDate.value = Util.formatDate(this.getFrom());

		const difference = this.getFrom().getTime() - this.from.getTime();

		this.DOM.toDate.value = Util.formatDate(this.to.getTime() + difference);

		this.handleValueChange();
	}

	handleDateToChange()
	{
		if (!this.getTo())
		{
			this.DOM.toDate.value = Util.formatDate(this.to.getTime());
			return;
		}
		this.DOM.toDate.value = Util.formatDate(this.getTo());

		const difference = Math.abs(this.to.getTime() - this.getTo().getTime());
		const yearDuration = 1000 * 60 * 60 * 24 * 300;
		if (difference > yearDuration)
		{
			const duration = this.to.getTime() - this.from.getTime();
			const toDate = Util.parseDate(this.DOM.toDate.value);
			toDate.setHours(this.to.getHours(), this.to.getMinutes(), 0, 0);
			const fromDate = new Date(toDate.getTime() - duration);
			this.DOM.fromDate.value = Util.formatDate(fromDate);
		}

		if (this.getTo() < this.getFrom())
		{
			this.DOM.toDate.value = this.DOM.fromDate.value;
			this.DOM.toTime.value = this.DOM.fromTime.value;
			this.toMinutes = this.getMinutesFromFormattedTime(this.DOM.toTime.value);
		}
		this.handleValueChange();
	}

	handleTimeFromChange(inputValue, dataValue)
	{
		this.handleTimeChange(this.DOM.fromTime);

		if (this.isIncorrectTimeValue(this.DOM.fromTime.value))
		{
			this.DOM.fromTime.value = Util.formatTime(this.from);
		}
		else
		{
			this.fromMinutes = dataValue ?? this.getMinutesFromFormattedTime(this.DOM.fromTime.value);
			this.DOM.fromTime.value = Util.formatTime(this.getFrom());
		}

		if (this.getTo())
		{
			const difference = this.getFrom().getTime() - this.from.getTime();
			this.toMinutes = this.toMinutes + difference / (60 * 1000);
		}

		this.handleValueChange();
	}

	handleTimeToChange(inputValue, dataValue)
	{
		this.handleTimeChange(this.DOM.toTime);

		if (this.isIncorrectTimeValue(this.DOM.toTime.value))
		{
			this.DOM.toTime.value = Util.formatTime(this.to);
		}
		else
		{
			this.toMinutes = dataValue ?? this.getMinutesFromFormattedTime(this.DOM.toTime.value);
			this.DOM.toTime.value = Util.formatTime(this.getTo());
		}

		if (this.getTo() < this.getFrom())
		{
			const difference = this.getTo().getTime() - this.to.getTime();
			this.fromMinutes = this.fromMinutes + difference / (60 * 1000);
			const newFromDate = new Date(this.from.getTime() + difference);
			this.DOM.fromTime.value = Util.formatTime(newFromDate);
			this.DOM.fromDate.value = Util.formatDate(newFromDate);
		}

		this.handleValueChange();
	}

	isIncorrectTimeValue(timeValue)
	{
		if (BX.isAmPmMode())
		{
			return timeValue === '';
		}
		return timeValue === '' || (timeValue[0] !== '0' && Util.parseTime(timeValue).h === 0);
	}

	handleTimeChange(timeSelector)
	{
		if (timeSelector.value === '')
		{
			return;
		}

		let time = this.getMaskedTime(timeSelector.value);
		time = this.beautifyTime(time);
		if (BX.isAmPmMode())
		{
			let amPmSymbol = (timeSelector.value.toLowerCase().match(/[ap]/g) ?? []).pop();
			if (!amPmSymbol)
			{
				const hour = parseInt(this.getMinutesAndHours(time).hours);
				if (8 <= hour && hour <= 11)
				{
					amPmSymbol = 'a';
				}
				else
				{
					amPmSymbol = 'p';
				}
			}
			if (amPmSymbol === 'a')
			{
				time += ' am';
			}
			if (amPmSymbol === 'p')
			{
				time += ' pm';
			}
		}
		timeSelector.value = time;
	}

	handleTimeInput(e)
	{
		e.target.value = this.getMaskedTime(e.target.value, e.data, e.inputType === 'deleteContentBackward');
	}

	getMaskedTime(value, key, backspace = false)
	{
		if (backspace)
		{
			return value;
		}

		let time = '';
		const { hours, minutes } = this.getMinutesAndHours(value, key);
		if (hours && !minutes)
		{
			time = `${hours}`;
			if (value.length - time.length === 1 || value.indexOf(':') !== -1)
			{
				time += ':';
			}
		}
		if (hours && minutes)
		{
			time = `${hours}:${minutes}`;
		}

		if (BX.isAmPmMode() && this.clearTimeString(time) !== '')
		{
			const amPmSymbol = (value.toLowerCase().match(/[ap]/g) ?? []).pop();
			if (amPmSymbol === 'a')
			{
				time = this.beautifyTime(time) + ' am';
			}
			if (amPmSymbol === 'p')
			{
				time = this.beautifyTime(time) + ' pm';
			}
		}

		return time;
	}

	getMinutesAndHours(value, key)
	{
		let time = this.clearTimeString(value,  key);
		let hours, minutes;
		if (time.indexOf(':') !== -1)
		{
			hours = time.match(/[\d]*:/g)[0].slice(0, -1);
			minutes = time.match(/:[\d]*/g)[0].slice(1);
		}
		else
		{
			const digits = (time.match(/\d/g) ?? []).splice(0,4).map(d => parseInt(d));
			if (digits.length === 4 && digits[0] > this.getMaxHours() / 10)
			{
				digits.pop();
			}
			if (digits.length === 1)
			{
				hours = `${digits[0]}`;
			}
			if (digits.length === 2)
			{
				hours = `${digits[0]}${digits[1]}`;
				if (parseInt(hours) > this.getMaxHours())
				{
					hours = `${digits[0]}`;
					minutes = `${digits[1]}`;
				}
			}
			if (digits.length === 3)
			{
				if (BX.isAmPmMode())
				{
					if (digits[0] >= 1)
					{
						hours = `${digits[0]}`;
						minutes = `${digits[1]}${digits[2]}`;
					}
					else
					{
						hours = `${digits[0]}${digits[1]}`;
						minutes = `${digits[2]}`;
					}
				}
				else
				{
					if (parseInt(`${digits[0]}${digits[1]}`) < 24)
					{
						hours = `${digits[0]}${digits[1]}`;
						minutes = `${digits[2]}`;
					}
					else
					{
						hours = `${digits[0]}`;
						minutes = `${digits[1]}${digits[2]}`;
					}
				}
			}
			if (digits.length === 4)
			{
				hours = `${digits[0]}${digits[1]}`;
				minutes = `${digits[2]}${digits[3]}`;
			}
		}

		if (hours)
		{
			hours = this.formatHours(hours);
		}
		if (minutes)
		{
			minutes = this.formatMinutes(minutes);
		}
		return { hours, minutes };
	}

	clearTimeString(str, key)
	{
		let validatedTime = str.replace(/[ap]/g, '').replace(/\D/g, ':'); // remove a and p and replace not digits to :
		validatedTime = validatedTime.replace(/:*/, ''); // remove everything before first digit

		// leave only first :
		const firstColonIndex = validatedTime.indexOf(':');
		validatedTime = validatedTime.substr(0, firstColonIndex + 1) + validatedTime.slice(firstColonIndex + 1).replaceAll(':', '');

		// leave not more than 2 hour digits and 2 minute digits
		if (firstColonIndex !== -1)
		{
			const hours = this.formatHours(validatedTime.match(/[\d]*:/g)[0].slice(0, -1));
			const minutes = validatedTime.match(/:[\d]*/g)[0].slice(1).slice(0, 3);
			if (hours.length === 1 && minutes.length === 3 && !isNaN(parseInt(key)) && this.areTimeDigitsCorrect(`${hours}${minutes}`))
			{
				return `${hours}${minutes}`;
			}
			return `${hours}:${minutes}`;
		}
		return validatedTime.slice(0, 4);
	}

	areTimeDigitsCorrect(time)
	{
		const hh = time.slice(0, 2);
		const mm = time.slice(2);
		return this.formatHours(hh) === hh && this.formatMinutes(mm) === mm;
	}

	formatHours(str)
	{
		const firstDigit = str[0];
		if (parseInt(firstDigit) > this.getMaxHours() / 10)
		{
			return `0${firstDigit}`;
		}
		if (parseInt(str) <= this.getMaxHours())
		{
			return `${firstDigit}${str[1] ?? ''}`;
		}
		return `${firstDigit}`;
	}

	formatMinutes(str)
	{
		const firstDigit = str[0];
		if (firstDigit >= 6)
		{
			return `0${firstDigit}`;
		}
		return `${firstDigit}${str[1] ?? ''}`;
	}

	beautifyTime(time)
	{
		if (this.clearTimeString(time) === '')
		{
			return '';
		}

		if (time.indexOf(':') === -1)
		{
			time += ':00';
		}
		if (time.indexOf(':') === time.length - 1)
		{
			time += '00';
		}

		let { hours, minutes } = this.getMinutesAndHours(time);
		hours = `0${hours}`.slice(-2);
		minutes = `0${minutes}`.slice(-2);

		return `${hours}:${minutes}`;
	}

	getMaxHours()
	{
		return BX.isAmPmMode() ? 12 : 24;
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
		this.setValue({ from: this.getFrom(), to: this.getTo() });
		this.emit('onChange', new BaseEvent({data: {value: this.getValue()}}));
	}

	updateToTimeDurationHints()
	{
		this.toTimeControl.updateDurationHints(
			this.DOM.fromTime.value,
			this.DOM.toTime.value,
			this.DOM.fromDate.value,
			this.DOM.toDate.value
		);
	}

	getFullDayValue()
	{
		return !!this.DOM.fullDay.checked;
	}

	getMinutesFromFormattedTime(time)
	{
		const parsedTime = Util.parseTime(time);
		return parsedTime.h * 60 + parsedTime.m;
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