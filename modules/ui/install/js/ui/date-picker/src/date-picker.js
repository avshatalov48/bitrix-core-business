import { Dom, Extension, Tag, Type, Event } from 'main.core';
import { type BaseCache, MemoryCache } from 'main.core.cache';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { DateTimeFormat } from 'main.date';
import { Popup, type PopupOptions } from 'main.popup';

import { type BasePicker } from './base-picker';

import {
	type DatePickerSelectionMode,
	type DayColorOptions,
	type DateLike,
	type DatePickerOptions,
	type DatePickerType,
	type DayColor,
	type DayMark,
	type DayMarkOptions,
	type DateLikeMatcher,
	type DateMatcher,
} from './date-picker-options';

import { DayPicker } from './day-picker';
import { DatePickerEvent } from './date-picker-event';
import { addDate } from './helpers/add-date';
import { addToRange } from './helpers/add-to-range';
import { ceilDate } from './helpers/ceil-date';
import { cloneDate } from './helpers/clone-date';

import { createDate } from './helpers/create-date';
import { createUtcDate } from './helpers/create-utc-date';
import { floorDate } from './helpers/floor-date';
import { getDate, type DateComponents } from './helpers/get-date';
import { getFocusableBoundaryElements } from './helpers/get-focusable-boundary-elements';
import { isDateLike } from './helpers/is-date-like';
import { isDatesEqual } from './helpers/is-dates-equal';
import { setTime } from './helpers/set-time';
import { isDateMatch } from './helpers/is-date-match';
import { KeyboardNavigation } from './keyboard-navigation';
import { MonthPicker } from './month-picker';
import { TimePickerWheel } from './time-picker-wheel';
import { TimePickerGrid } from './time-picker-grid';
import { YearPicker } from './year-picker';

import './css/date-picker.css';

let singleOpenDatePicker: DatePicker = null;

/**
 * @namespace BX.UI.DatePicker
 */
export class DatePicker extends EventEmitter
{
	#viewDate: Date = null;
	#startDate: Date = null;
	#selectedDates: Date[] = [];
	#focusDate: Date = null;

	#type: DatePickerType = 'date';
	#currentView: 'day' | 'year' | 'month' | 'time' = null;
	#selectionMode: DatePickerSelectionMode = 'single';
	#views: Map = new Map();

	#firstWeekDay: number = 1;
	#showWeekDays: boolean = true;
	#showWeekNumbers: boolean = false;
	#showOutsideDays: boolean = true;
	#numberOfMonths: number = 1;

	#maxDays: number = Infinity;
	#minDays: number = 0;
	#fullYear: boolean = false;

	#weekends: number[] = [0, 6];
	#holidays: Array<[number, number]> = [];
	#workdays: Array<[number, number]> = [];
	#enableTime: boolean = false;
	#allowSeconds: boolean = false;
	#amPmMode: boolean = false;
	#minuteStep: number = 5;
	#defaultTime: string = '00:00:00';
	#defaultTimeSpan: number = 60;
	#timePickerStyle: 'wheel' | 'grid' = 'grid';
	#cutZeroTime: boolean = true;

	#targetNode: HTMLElement = null;
	#inputField: HTMLInputElement | HTMLTextAreaElement = null;
	#rangeStartInput: HTMLInputElement | HTMLTextAreaElement = null;
	#rangeEndInput: HTMLInputElement | HTMLTextAreaElement = null;
	#useInputEvents: boolean = true;
	#dateSeparator: string = ', ';

	#popup: Popup = null;
	#popupOptions: PopupOptions = {};
	#hideByEsc: boolean = true;
	#autoHide: boolean = true;
	#cacheable: boolean = true;
	#singleOpening: boolean = true;

	#refs: BaseCache<HTMLElement | Function> = new MemoryCache();
	#rendered: boolean = false;
	#inline: boolean = false;
	#autoFocus: boolean = true;

	#dateFormat: string = null;
	#timeFormat: string = null;

	#toggleSelected: boolean = null;
	#hideOnSelect: boolean = true;
	#locale: boolean = null;
	#hideHeader: boolean = false;

	#dayColors: DayColor[] = [];
	#dayMarks: DayMark[] = [];

	#keyboardNavigation: KeyboardNavigation = null;
	#destroying: boolean = false;

	constructor(pickerOptions: DatePickerOptions)
	{
		super();
		this.setEventNamespace('BX.UI.DatePicker');

		const settings = Extension.getSettings('ui.date-picker');
		const options: DatePickerOptions = Type.isPlainObject(pickerOptions) ? pickerOptions : {};

		this.#setType(options.type);
		this.#setSelectionMode(options.selectionMode);

		this.#locale = Type.isStringFilled(options.locale) ? options.locale : settings.get('locale', 'en');

		this.#enableTime = Type.isBoolean(options.enableTime) ? options.enableTime : this.#enableTime;
		if (this.isMultipleMode())
		{
			this.#enableTime = false;
		}

		this.#allowSeconds = Type.isBoolean(options.allowSeconds) ? options.allowSeconds : this.#allowSeconds;
		this.#amPmMode = Type.isBoolean(options.amPmMode) ? options.amPmMode : DateTimeFormat.isAmPmMode();
		this.#cutZeroTime = Type.isBoolean(options.cutZeroTime) ? options.cutZeroTime : this.#cutZeroTime;
		this.#dateFormat = Type.isStringFilled(options.dateFormat) ? options.dateFormat : this.#getDefaultDateFormat();

		this.setDefaultTime(options.defaultTime);
		this.setDefaultTimeSpan(options.defaultTimeSpan);

		this.#timeFormat = (
			Type.isStringFilled(options.timeFormat)
				? options.timeFormat
				: DateTimeFormat.getFormat(this.#allowSeconds ? 'LONG_TIME_FORMAT' : 'SHORT_TIME_FORMAT')
		);

		this.#minuteStep = (
			Type.isNumber(options.minuteStep) && [1, 5, 10, 15, 30].includes(options.minuteStep)
				? options.minuteStep
				: this.#minuteStep
		);

		this.#timePickerStyle = options.timePickerStyle === 'wheel' ? 'wheel' : this.#timePickerStyle;

		this.#viewDate = this.getToday();

		this.#useInputEvents = Type.isBoolean(options.useInputEvents) ? options.useInputEvents : this.#useInputEvents;
		this.setAutoFocus(options.autoFocus);
		this.setInputField(options.inputField);
		this.setRangeStartInput(options.rangeStartInput);
		this.setRangeEndInput(options.rangeEndInput);
		this.setDateSeparator(options.dateSeparator);

		this.selectDates(options.selectedDates, { emitEvents: false });

		this.#startDate = isDateLike(options.startDate) ? this.createDate(options.startDate) : null;
		const viewDate = this.getDefaultViewDate();
		this.setViewDate(viewDate);

		this.#inline = options.inline === true;

		let firstWeekDay = settings.get('firstWeekDay', this.#firstWeekDay);
		firstWeekDay = Type.isNumber(options.firstWeekDay) ? options.firstWeekDay : firstWeekDay;
		this.#firstWeekDay = Math.min(Math.max(0, firstWeekDay), 6);

		this.#numberOfMonths = Type.isNumber(options.numberOfMonths) ? options.numberOfMonths : this.#numberOfMonths;
		this.#fullYear = options.fullYear === true;
		if (this.#fullYear)
		{
			this.#enableTime = false;
			this.#numberOfMonths = 12;
			this.setViewDate(createUtcDate(viewDate.getUTCFullYear(), 0, 1));
		}

		this.#showWeekDays = Type.isBoolean(options.showWeekDays) ? options.showWeekDays : this.#showWeekDays;
		this.#showWeekNumbers = Type.isBoolean(options.showWeekNumbers) ? options.showWeekNumbers : this.#showWeekNumbers;

		const defaultWeekends = settings.get('weekends', []);
		this.#weekends = (
			Type.isArray(options.weekends)
				? options.weekends
				: (Type.isArrayFilled(defaultWeekends) ? defaultWeekends : this.#weekends)
		);

		const defaultHolidays = settings.get('holidays', []);
		this.#holidays = Type.isArray(options.holidays) ? options.holidays : defaultHolidays;

		const defaultWorkdays = settings.get('workdays', []);
		this.#workdays = Type.isArray(options.workdays) ? options.workdays : defaultWorkdays;

		this.#showOutsideDays = this.#numberOfMonths > 1 ? false : this.#showOutsideDays;
		this.#showOutsideDays = Type.isBoolean(options.showOutsideDays) ? options.showOutsideDays : this.#showOutsideDays;

		this.#popupOptions = Type.isPlainObject(options.popupOptions) ? options.popupOptions : this.#popupOptions;

		this.setMinDays(options.minDays);
		this.setMaxDays(options.maxDays);
		this.setHideOnSelect(options.hideOnSelect);
		this.setTargetNode(options.targetNode);
		this.setToggleSelected(options.toggleSelected);
		this.setAutoHide(options.autoHide);
		this.setHideByEsc(options.hideByEsc);
		this.setCacheable(options.cacheable);
		this.setSingleOpening(options.singleOpening);
		this.setDayColors(options.dayColors);
		this.setDayMarks(options.dayMarks);
		this.setHideHeader(options.hideHeader);

		this.subscribeFromOptions(options.events);
		this.#keyboardNavigation = new KeyboardNavigation(this);
	}

	setViewDate(date: DateLike)
	{
		let newDate = this.createDate(date);
		if (newDate === null)
		{
			return;
		}

		newDate = setTime(newDate, 0, 0, 0);

		this.#viewDate = newDate;

		if (this.isDateOutOfView(this.getFocusDate()))
		{
			this.setFocusDate(null, { adjustViewDate: false, render: false });
		}

		if (this.isRendered())
		{
			this.getPicker().render();
		}
	}

	getViewDate(): Date
	{
		return this.#viewDate;
	}

	getDefaultViewDate(): Date
	{
		return this.getSelectedDate() || this.#startDate || this.getToday();
	}

	adjustViewDate(date: Date): void
	{
		if (this.isSingleMode())
		{
			if (this.getNumberOfMonths() === 1)
			{
				if (!isDatesEqual(date, this.getViewDate(), 'month'))
				{
					this.setViewDate(createUtcDate(date.getUTCFullYear(), date.getUTCMonth()));
				}
			}
			else
			{
				const { year, month } = this.getViewDateParts();
				const firstMonth = createUtcDate(year, month);
				const lastMonth = ceilDate(createUtcDate(year, month + this.getNumberOfMonths() - 1), 'month');
				if (date < firstMonth || date >= lastMonth)
				{
					this.setViewDate(createUtcDate(date.getUTCFullYear(), date.getUTCMonth()));
				}
			}
		}
		else
		{
			const dayPicker: DayPicker = this.getPicker('day');
			const months = dayPicker.getMonths();
			const firstDay = months[0].weeks[0][0].date;
			const lastDay = months.at(-1).weeks.at(-1).at(-1).date;
			if (date < firstDay || date > lastDay)
			{
				this.setViewDate(createUtcDate(date.getUTCFullYear(), date.getUTCMonth()));
			}
		}
	}

	getViewDateParts(): DateComponents
	{
		return getDate(this.#viewDate);
	}

	selectDate(date: DateLike, options = {}): boolean
	{
		if (this.isRangeMode())
		{
			throw new Error('DatePicker: to select a range use selectRange method.');
		}

		if (!isDateLike(date))
		{
			return false;
		}

		const selectedDate = this.createDate(date);
		if (this.isDateSelected(selectedDate, 'datetime'))
		{
			return false;
		}

		const updateTime = this.isDateSelected(selectedDate, 'day');
		if (!updateTime && this.isMultipleMode() && this.#selectedDates.length >= this.getMaxDays())
		{
			return false;
		}

		const { emitEvents, render, updateInputs } = {
			emitEvents: true,
			render: true,
			updateInputs: true,
			...options,
		};

		if (emitEvents && !this.#canSelectDate(selectedDate))
		{
			return false;
		}

		if (this.isMultipleMode())
		{
			if (updateTime)
			{
				const index = this.#selectedDates.findIndex((currentDate: Date) => {
					return isDatesEqual(currentDate, selectedDate, 'day');
				});

				// replace existing date
				if (index !== -1)
				{
					this.#selectedDates.splice(index, 1, selectedDate);
				}
			}
			else
			{
				const index = this.#selectedDates.findIndex((currentDate: Date) => {
					return currentDate > selectedDate;
				});

				if (index === -1)
				{
					this.#selectedDates.push(selectedDate);
				}
				else if (index === 0)
				{
					this.#selectedDates.unshift(selectedDate);
				}
				else
				{
					this.#selectedDates.splice(index, 0, selectedDate);
				}
			}
		}
		else
		{
			const currentDate = this.#selectedDates[0] || null;
			if (emitEvents && currentDate !== null)
			{
				if (!this.#canDeselectDate(currentDate))
				{
					return false;
				}

				this.deselectDate(currentDate, { emitEvents: false, render: false });
				this.emit(DatePickerEvent.DESELECT, { date: currentDate });
			}

			this.#selectedDates = [selectedDate];
		}

		this.adjustViewDate(selectedDate);
		if (this.isRendered() && render)
		{
			this.getPicker().render();
		}

		if (updateInputs)
		{
			this.updateInputFields();
		}

		if (emitEvents)
		{
			this.emit(DatePickerEvent.SELECT, { date: selectedDate });
			this.emit(DatePickerEvent.SELECT_CHANGE);
		}

		return true;
	}

	selectDates(dates: DateLike[], options = {}): void
	{
		if (!Type.isArrayFilled(dates))
		{
			return;
		}

		if (this.isRangeMode())
		{
			const [start, end] = dates;
			this.selectRange(start, end, options);
		}
		else
		{
			dates.forEach((date: DateLike): void => {
				this.selectDate(date, options);
			});
		}
	}

	selectRange(start: DateLike, end: DateLike = null, options = {}): boolean
	{
		if (!this.isRangeMode())
		{
			throw new Error('DatePicker: to select a date use selectDate method.');
		}

		if (!isDateLike(start) || (end !== null && !isDateLike(end)))
		{
			return false;
		}

		let newStart = this.createDate(start);
		let newEnd = end === null ? null : this.createDate(end);
		if (newStart === null && newEnd === null)
		{
			return false;
		}

		if (newStart !== null && newEnd !== null && newStart > newEnd)
		{
			[newStart, newEnd] = [newEnd, newStart];
		}

		const currentStart = this.#selectedDates[0] || null;
		const currentEnd = this.#selectedDates[1] || null;

		if (
			isDatesEqual(newStart, currentStart, 'datetime')
			&& (
				(newEnd === null && currentEnd === null) || isDatesEqual(newEnd, currentEnd, 'datetime')
			)
		)
		{
			return false;
		}

		const { emitEvents, updateInputs } = { emitEvents: true, updateInputs: true, ...options };
		const deselectStart = (
			currentStart !== null
			&& emitEvents
			&& !isDatesEqual(newStart, currentStart, 'datetime')
			&& !isDatesEqual(newEnd, currentStart, 'datetime')
		);

		const deselectEnd = (
			currentEnd !== null
			&& emitEvents
			&& !isDatesEqual(newStart, currentEnd, 'datetime')
			&& !isDatesEqual(newEnd, currentEnd, 'datetime')
		);

		const selectStart = !this.isDateSelected(newStart, 'datetime');
		const selectEnd = (
			newEnd !== null
			&& (
				!this.isDateSelected(newEnd, 'datetime')
				|| (currentEnd === null && isDatesEqual(newEnd, newStart, 'datetime'))
			)
		);

		if (deselectStart && !this.#canDeselectDate(currentStart))
		{
			return false;
		}

		if (deselectEnd && !this.#canDeselectDate(currentEnd))
		{
			return false;
		}

		if (selectStart && !this.#canSelectDate(newStart))
		{
			return false;
		}

		if (selectEnd && !this.#canSelectDate(newEnd))
		{
			return false;
		}

		if (deselectStart)
		{
			this.deselectDate(currentStart, { emitEvents: false, render: false });
			this.emit(DatePickerEvent.DESELECT, { date: currentStart });
		}

		if (deselectEnd)
		{
			this.deselectDate(currentEnd, { emitEvents: false, render: false });
			this.emit(DatePickerEvent.DESELECT, { date: currentEnd });
		}

		this.#selectedDates = newEnd === null ? [newStart] : [newStart, newEnd];

		this.adjustViewDate(newStart);
		if (this.isRendered())
		{
			this.getPicker().render();
		}

		if (updateInputs)
		{
			this.updateInputFields();
		}

		if (emitEvents)
		{
			if (selectStart)
			{
				this.emit(DatePickerEvent.SELECT, { date: newStart });
			}

			if (selectEnd)
			{
				this.emit(DatePickerEvent.SELECT, { date: newEnd });
			}

			this.emit(DatePickerEvent.SELECT_CHANGE);
		}

		return true;
	}

	deselectDate(date: DateLike, options = {}): boolean
	{
		if (!isDateLike(date))
		{
			return false;
		}

		const dateToDeselect = this.createDate(date);
		const { emitEvents, render, updateInputs } = {
			emitEvents: true,
			render: true,
			updateInputs: true,
			...options,
		};

		if (emitEvents && !this.#canDeselectDate(dateToDeselect))
		{
			return false;
		}

		if (this.isMultipleMode() && this.#selectedDates.length <= this.getMinDays())
		{
			return false;
		}

		const index = this.#selectedDates.findIndex((selectedDate) => {
			return isDatesEqual(dateToDeselect, selectedDate);
		});

		if (index === -1)
		{
			return false;
		}

		this.#selectedDates.splice(index, 1);

		if (emitEvents)
		{
			this.emit(DatePickerEvent.DESELECT, { date: dateToDeselect });
			this.emit(DatePickerEvent.SELECT_CHANGE);
		}

		if (this.isRendered() && render)
		{
			this.getPicker().render();
		}

		if (updateInputs)
		{
			this.updateInputFields();
		}

		return true;
	}

	deselectAll(options = {}): boolean
	{
		const dates = [...this.#selectedDates];
		dates.forEach((date: Date) => {
			this.deselectDate(date, options);
		});

		return this.#selectedDates.length === 0;
	}

	#canSelectDate(date: Date): boolean
	{
		const event = new BaseEvent({ data: { date } });
		this.emit(DatePickerEvent.BEFORE_SELECT, event);

		return !event.isDefaultPrevented();
	}

	#canDeselectDate(date: Date): boolean
	{
		const event = new BaseEvent({ data: { date } });
		this.emit(DatePickerEvent.BEFORE_DESELECT, event);

		return !event.isDefaultPrevented();
	}

	getSelectedDates(): Date[]
	{
		return this.#selectedDates;
	}

	getSelectedDate(): Date | null
	{
		return this.#selectedDates[0] || null;
	}

	getRangeStart(): Date | null
	{
		return this.#selectedDates[0] || null;
	}

	getRangeEnd(): Date | null
	{
		return this.#selectedDates[1] || null;
	}

	isDateSelected(date: Date, precision: 'day' | 'datetime' | 'month' | 'year' = 'day'): boolean
	{
		return this.#selectedDates.some((selectedDate: Date): boolean => {
			return isDatesEqual(date, selectedDate, precision);
		});
	}

	setFocusDate(date: DateLike, options = {}): void
	{
		if (!isDateLike(date) && date !== null)
		{
			return;
		}

		this.#focusDate = date === null ? null : this.createDate(date);

		const { render, adjustViewDate } = { render: true, adjustViewDate: true, ...options };

		if (adjustViewDate && this.isDateOutOfView(this.#focusDate))
		{
			this.setViewDate(createUtcDate(this.#focusDate.getUTCFullYear(), this.#focusDate.getUTCMonth()));
		}

		if (this.isRendered() && render)
		{
			this.getPicker().render();
		}
	}

	getFocusDate(): Date | null
	{
		return this.#focusDate;
	}

	getInitialFocusDate(mode: 'datetime' | 'range-start' | 'range-end' = 'datetime'): Date
	{
		const focusDate = this.getFocusDate();
		if (focusDate !== null)
		{
			return focusDate;
		}

		if (mode === 'range-start')
		{
			const { year, month, day } = this.getViewDateParts();

			return this.getRangeStart() || createUtcDate(year, month, day);
		}

		if (mode === 'range-end')
		{
			const { year, month, day } = this.getViewDateParts();

			return this.getRangeEnd() || createUtcDate(year, month, day);
		}

		const selectedDates = this.getSelectedDates();
		if (Type.isArrayFilled(selectedDates))
		{
			const date = selectedDates.find((selectedDate: Date) => {
				return !this.isDateOutOfView(selectedDate);
			});

			if (Type.isDate(date))
			{
				return date;
			}
		}

		return this.getViewDate();
	}

	isDateOutOfView(date: Date | null): boolean
	{
		if (date === null)
		{
			return false;
		}

		let isOutOfView = false;
		const { year: currentViewYear } = this.getViewDateParts();
		const { year: focusYear } = getDate(date);
		if (this.getCurrentView() === 'day')
		{
			const dayPicker: DayPicker = this.getPicker('day');
			const firstDay = dayPicker.getFirstDay();
			const lastDay = dayPicker.getLastDay();

			const focusDate = createUtcDate(
				date.getUTCFullYear(),
				date.getUTCMonth(),
				date.getUTCDate(),
			);

			isOutOfView = focusDate < firstDay || focusDate >= lastDay;
		}
		else if (this.getCurrentView() === 'month')
		{
			isOutOfView = currentViewYear !== focusYear;
		}
		else if (this.getCurrentView() === 'year')
		{
			const yearPicker: YearPicker = this.getPicker('year');
			const firstYear = yearPicker.getFirstYear();
			const lastYear = yearPicker.getLastYear();

			isOutOfView = focusYear < firstYear || focusYear > lastYear;
		}

		return isOutOfView;
	}

	setCurrentView(view: string): void
	{
		if (this.#currentView === view)
		{
			return;
		}

		const picker = this.getPicker(view);
		if (picker === null)
		{
			return;
		}

		Dom.style(this.getPicker()?.getContainer(), 'display', 'none');
		Dom.attr(this.getPicker()?.getContainer(), 'inert', true);
		this.getPicker()?.onHide();

		this.#currentView = view;
		this.setFocusDate(null, { render: false });

		if (!picker.isRendered())
		{
			picker.renderTo(this.getViewsContainer());
		}

		this.focus();

		Dom.style(picker.getContainer(), 'display', null);
		Dom.attr(picker.getContainer(), 'inert', null);

		picker.onShow();
		picker.render();
	}

	getCurrentView(): 'day' | 'year' | 'month' | 'time'
	{
		return this.#currentView;
	}

	getPicker(pickerId?: string): BasePicker | null
	{
		const currentPickerId = Type.isStringFilled(pickerId) ? pickerId : this.#currentView;
		let view = this.#views.get(currentPickerId) || null;
		if (view === null)
		{
			view = this.#createPicker(currentPickerId);
			if (view !== null)
			{
				this.#views.set(currentPickerId, view);
			}
		}

		return view;
	}

	#setType(type: DatePickerType)
	{
		if (['date', 'year', 'month', 'time'].includes(type))
		{
			this.#type = type;
		}
	}

	getType(): DatePickerType
	{
		return this.#type;
	}

	getFirstWeekDay(): number
	{
		return this.#firstWeekDay;
	}

	getNumberOfMonths(): number
	{
		return this.#numberOfMonths;
	}

	shouldShowWeekDays(): boolean
	{
		return this.#showWeekDays;
	}

	shouldShowWeekNumbers(): boolean
	{
		return this.#showWeekNumbers;
	}

	shouldShowOutsideDays(): boolean
	{
		return this.#showOutsideDays;
	}

	getWeekends(): number[]
	{
		return this.#weekends;
	}

	isWeekend(date: Date): boolean
	{
		return this.#weekends.includes(date.getUTCDay());
	}

	isHoliday(date: Date): boolean
	{
		return this.#holidays.some(([day, month]) => {
			return date.getUTCDate() === day && date.getUTCMonth() === month;
		});
	}

	isWorkday(date: Date): boolean
	{
		return this.#workdays.some(([day, month]) => {
			return date.getUTCDate() === day && date.getUTCMonth() === month;
		});
	}

	isDayOff(date: Date): boolean
	{
		return !this.isWorkday(date) && (this.isWeekend(date) || this.isHoliday(date));
	}

	isTimeEnabled(): boolean
	{
		return this.#enableTime;
	}

	setDefaultTime(time: string): void
	{
		if (Type.isStringFilled(time) && /([01]{1,2}\d|2[0-3]):[0-5]\d(:[0-5]\d)?/.test(time))
		{
			this.#defaultTime = time;
		}
	}

	getDefaultTime(): string
	{
		return this.#defaultTime;
	}

	setDefaultTimeSpan(minutes: number): void
	{
		if (Type.isNumber(minutes) && minutes >= 0)
		{
			this.#defaultTimeSpan = minutes;
		}
	}

	getDefaultTimeSpan(): string
	{
		return this.#defaultTimeSpan;
	}

	getDefaultTimeParts(): { hours: number, minutes: number, seconds: number }
	{
		const parts = this.getDefaultTime().split(':');

		return {
			hours: Number(parts[0] || 0),
			minutes: Number(parts[1] || 0),
			seconds: Number(parts[2] || 0),
		};
	}

	getTimePickerStyle(): 'wheel' | 'grid'
	{
		return this.#timePickerStyle;
	}

	shouldCutZeroTime(): boolean
	{
		return this.#cutZeroTime;
	}

	shouldAllowSeconds(): boolean
	{
		return this.#allowSeconds;
	}

	setToggleSelected(flag: boolean | null): void
	{
		if (Type.isBoolean(flag) || Type.isNull(flag))
		{
			this.#toggleSelected = flag;
		}
	}

	shouldToggleSelected(): boolean
	{
		if (this.#toggleSelected !== null)
		{
			return this.#toggleSelected;
		}

		return this.isMultipleMode();
	}

	setMaxDays(days: number): void
	{
		if (Type.isNumber(days) && days > 0)
		{
			this.#maxDays = days;
		}
	}

	getMaxDays(): number
	{
		return this.#maxDays;
	}

	setMinDays(days: number)
	{
		if (Type.isNumber(days) && days > 0)
		{
			this.#minDays = days;
		}
	}

	getMinDays(): number
	{
		return this.#minDays;
	}

	isFullYear(): boolean
	{
		return this.#fullYear;
	}

	isAmPmMode(): boolean
	{
		return this.#amPmMode;
	}

	getMinuteStep(): number
	{
		return this.#minuteStep;
	}

	getMinuteStepByDate(date: Date): number
	{
		let step = this.getMinuteStep();
		if (!Type.isDate(date))
		{
			return step;
		}

		const selectedMinute = date.getUTCMinutes();
		if (selectedMinute > 0 && (selectedMinute % step) !== 0)
		{
			// Reduce a step to show a selected minute
			const availableSteps = [30, 15, 10, 5, 1];
			const index = availableSteps.indexOf(selectedMinute);
			const steps = index === -1 ? [1] : availableSteps.slice(index);
			for (const newStep of steps)
			{
				if (selectedMinute % newStep === 0)
				{
					step = newStep;
					break;
				}
			}
		}

		return step;
	}

	getToday(): Date
	{
		return this.createDate(new Date());
	}

	show(): void
	{
		this.updateFromInputFields();

		if (this.isInline())
		{
			if (!this.isRendered())
			{
				this.#render();
			}

			// Dom.removeClass(this.getContainer(), '--hidden');
		}
		else
		{
			this.getPopup().show();
		}
	}

	hide(): void
	{
		if (!this.isRendered() || this.isInline())
		{
			return;
		}

		// if (this.isInline())
		// {
		// Dom.addClass(this.getContainer(), '--hidden');
		// }

		this.getPopup().close();
	}

	isOpen(): boolean
	{
		return this.#popup !== null && this.#popup.isShown();
	}

	adjustPosition(): void
	{
		if (this.isRendered() && this.isOpen())
		{
			this.getPopup().adjustPosition();
		}
	}

	toggle(): void
	{
		if (this.isOpen())
		{
			this.hide();
		}
		else
		{
			this.show();
		}
	}

	focus(): void
	{
		if (this.isRendered())
		{
			this.getContainer().tabIndex = 0;
			this.getContainer().focus({ preventScroll: true });
			this.getContainer().tabIndex = -1;
		}
	}

	setSingleOpening(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#singleOpening = flag;
		}
	}

	isSingleOpening(): boolean
	{
		return this.#singleOpening;
	}

	setDayColors(options: DayColorOptions[]): void
	{
		if (!Type.isArray(options))
		{
			return;
		}

		const dayColors = [];
		for (const option of options)
		{
			if (!Type.isStringFilled(option.bgColor) && !Type.isStringFilled(option.textColor))
			{
				continue;
			}

			const matchers = this.#createDateMatchers(option.matcher);
			if (Type.isArrayFilled(matchers))
			{
				dayColors.push({
					bgColor: Type.isStringFilled(option.bgColor) ? option.bgColor : null,
					textColor: Type.isStringFilled(option.textColor) ? option.textColor : null,
					matchers,
				});
			}
		}

		this.#dayColors = dayColors;

		if (this.isRendered())
		{
			this.getPicker().render();
		}
	}

	getDayColor(day: Date): DayColor | null
	{
		return this.#dayColors.find((dayColor: DayColor): boolean => isDateMatch(day, dayColor.matchers)) || null;
	}

	setDayMarks(options: DayMarkOptions[]): void
	{
		if (!Type.isArray(options))
		{
			return;
		}

		const dayMarks = [];
		for (const option of options)
		{
			if (!Type.isStringFilled(option.bgColor))
			{
				continue;
			}

			const matchers = this.#createDateMatchers(option.matcher);
			if (Type.isArrayFilled(matchers))
			{
				dayMarks.push({
					bgColor: option.bgColor,
					matchers,
				});
			}
		}

		this.#dayMarks = dayMarks;

		if (this.isRendered())
		{
			this.getPicker().render();
		}
	}

	getDayMarks(day: Date): DayMark[]
	{
		return this.#dayMarks.filter((dayMark: DayMark): boolean => isDateMatch(day, dayMark.matchers));
	}

	#createDateMatchers(matcher: DateLikeMatcher | DateLikeMatcher[]): DateMatcher[]
	{
		if (Type.isUndefined(matcher))
		{
			return [];
		}

		const result = [];
		const matchers = Type.isArray(matcher) ? [...matcher] : [matcher];
		matchers.forEach((matcherValue: DateLikeMatcher): void => {
			if (Type.isArray(matcherValue))
			{
				const dates = [];
				matcherValue.forEach((dateLike: DateLike): void => {
					if (!isDateLike(dateLike))
					{
						return;
					}

					const date = this.createDate(matcherValue);
					if (date !== null)
					{
						dates.push(date);
					}
				});

				result.push(dates);
			}
			else if (isDateLike(matcherValue))
			{
				const date = this.createDate(matcherValue);
				if (date !== null)
				{
					result.push(date);
				}
			}
			else if (Type.isBoolean(matcherValue) || Type.isFunction(matcherValue))
			{
				result.push(matcherValue);
			}
		});

		return result;
	}

	getPopup(): Popup
	{
		if (this.#popup !== null)
		{
			return this.#popup;
		}

		const popupOptions = { ...this.#popupOptions };
		const userEvents = popupOptions.events;
		delete popupOptions.events;

		this.#popup = new Popup({
			contentPadding: 0,
			padding: 0,
			offsetTop: 5,
			bindElement: this.getTargetNode(),
			bindOptions: {
				forceBindPosition: true,
			},
			autoHide: this.isAutoHide(),
			closeByEsc: this.shouldHideByEsc(),
			cacheable: this.isCacheable(),
			content: this.getContainer(),
			autoHideHandler: this.#handleAutoHide.bind(this),
			events: {
				onFirstShow: this.#handlePopupFirstShow.bind(this),
				onShow: this.#handlePopupShow.bind(this),
				onClose: this.#handlePopupClose.bind(this),
				onDestroy: this.#handlePopupDestroy.bind(this),
			},
			...popupOptions,
		});

		this.#popup.subscribeFromOptions(userEvents);

		return this.#popup;
	}

	#setSelectionMode(mode: DatePickerSelectionMode): void
	{
		if (this.getType() !== 'date')
		{
			this.#selectionMode = 'single';
		}
		else if (['single', 'multiple', 'range', 'none'].includes(mode))
		{
			this.#selectionMode = mode;
		}
	}

	setHideOnSelect(flag: boolean): void
	{
		if (Type.isBoolean(flag))
		{
			this.#hideOnSelect = flag;
		}
	}

	shouldHideOnSelect(): boolean
	{
		if (this.isInline())
		{
			return false;
		}

		return this.#hideOnSelect;
	}

	setDateSeparator(separator: string): void
	{
		if (Type.isStringFilled(separator))
		{
			this.#dateSeparator = separator;
		}
	}

	getDateSeparator(): string
	{
		return this.#dateSeparator;
	}

	setInputField(field: string | HTMLElement): void
	{
		const input = this.#getInputField(field);
		if (input !== null)
		{
			this.#inputField = input;
			this.#bindInputEvents(input);
		}
	}

	setRangeStartInput(field: string | HTMLElement): void
	{
		const input = this.#getInputField(field);
		if (input !== null)
		{
			this.#rangeStartInput = input;
			this.#bindInputEvents(input);
		}
	}

	setRangeEndInput(field: string | HTMLElement): void
	{
		const input = this.#getInputField(field);
		if (input !== null)
		{
			this.#rangeEndInput = input;
			this.#bindInputEvents(input);
		}
	}

	#getInputField(field: string | HTMLElement): HTMLElement | null
	{
		if (Type.isStringFilled(field))
		{
			const element = document.querySelector(field);
			if (Type.isElementNode(element) || (element.nodeName === 'INPUT' || element.nodeName === 'TEXTAREA'))
			{
				return element;
			}

			console.error(`Date Picker: a form element was not found (${field}).`);
		}
		else if (Type.isElementNode(field) && (field.nodeName === 'INPUT' || field.nodeName === 'TEXTAREA'))
		{
			return field;
		}

		return null;
	}

	#bindInputEvents(input: HTMLElement): void
	{
		if (!this.shouldUseInputEvents())
		{
			return;
		}

		Event.bind(input, 'click', this.#refs.remember('click-handler', () => {
			return this.#handleInputClick.bind(this);
		}));

		Event.bind(input, 'focusout', this.#refs.remember('focusout-handler', () => {
			return this.#handleInputFocusOut.bind(this);
		}));

		Event.bind(input, 'keydown', this.#refs.remember('keydown-handler', () => {
			return this.#handleInputKeyDown.bind(this);
		}));

		Event.bind(input, 'input', this.#refs.remember('change-handler', () => {
			return this.#handleInputChange.bind(this);
		}));
	}

	#unbindInputEvents(input: HTMLElement): void
	{
		Event.unbind(input, 'click', this.#refs.get('click-handler'));
		Event.unbind(input, 'focusout', this.#refs.get('focusout-handler'));
		Event.unbind(input, 'keydown', this.#refs.get('keydown-handler'));
		Event.unbind(input, 'input', this.#refs.get('change-handler'));
	}

	#handleInputClick(event: MouseEvent): void
	{
		if (this.isRangeMode())
		{
			this.setTargetNode(event.target);
			if (!this.isOpen())
			{
				this.show();
			}
		}
		else
		{
			this.show();
		}
	}

	#handleInputFocusOut(event: MouseEvent): void
	{
		if (!this.getContainer().contains(event.relatedTarget))
		{
			this.hide();
		}
	}

	#handleInputKeyDown(event: KeyboardEvent): void
	{
		if (event.key === 'Tab' && !event.shiftKey && this.isOpen())
		{
			event.preventDefault();

			const currentPickerContainer = this.getPicker().getContainer();
			const [, next] = getFocusableBoundaryElements(
				currentPickerContainer,
				(element: HTMLElement) => element.dataset.tabPriority === 'true',
			);

			if (next === null)
			{
				this.focus();
			}
			else
			{
				next.focus({ preventScroll: true, focusVisible: true });
				this.#keyboardNavigation.setLastFocusElement(next);
			}
		}
	}

	#handleInputChange(event: KeyboardEvent): void
	{
		if (this.isOpen())
		{
			this.updateFromInputFields();
		}
	}

	#handleAutoHide(event: MouseEvent): boolean
	{
		const target = event.target;
		const el = this.getPopup().getPopupContainer();
		if (target === el || el.contains(target))
		{
			return false;
		}

		if (this.isRangeMode())
		{
			const anotherInput = (
				(this.getRangeStartInput() === target || this.getRangeEndInput() === target)
				&& this.getTargetNode() !== target
			);

			return !anotherInput;
		}

		return true;
	}

	shouldUseInputEvents(): boolean
	{
		return this.#useInputEvents;
	}

	getInputField(): HTMLInputElement | HTMLTextAreaElement | null
	{
		return this.#inputField;
	}

	getRangeStartInput(): HTMLInputElement | HTMLTextAreaElement | null
	{
		return this.#rangeStartInput;
	}

	getRangeEndInput(): HTMLInputElement | HTMLTextAreaElement | null
	{
		return this.#rangeEndInput;
	}

	updateInputFields(): void
	{
		if (this.isSingleMode())
		{
			if (this.getType() === 'time')
			{
				this.#setInputDate(this.getInputField(), this.getSelectedDate(), this.getTimeFormat());
			}
			else
			{
				this.#setInputDate(this.getInputField(), this.getSelectedDate());
			}
		}
		else if (this.isMultipleMode())
		{
			this.#setInputDate(
				this.getInputField(),
				this.getSelectedDates()
					.map((date: Date) => this.formatDate(date))
					.join(this.getDateSeparator())
				,
			);
		}
		else if (this.isRangeMode())
		{
			this.#setInputDate(this.getRangeStartInput(), this.getRangeStart());
			this.#setInputDate(this.getRangeEndInput(), this.getRangeEnd());
		}
	}

	#focusInputField(): void
	{
		if (this.getInputField() !== null)
		{
			this.getInputField().focus({ preventScroll: true });
		}
		else if (this.getRangeStartInput() !== null)
		{
			this.getRangeStartInput().focus({ preventScroll: true });
		}
	}

	updateFromInputFields(): void
	{
		if (this.isSingleMode() && this.getInputField() !== null)
		{
			const inputDate = this.#getDateFromInput(this.getInputField());
			if (inputDate === null)
			{
				this.deselectAll({ updateInputs: false, emitEvents: false });
			}
			else
			{
				this.selectDate(inputDate, { updateInputs: false, emitEvents: false });
			}
		}
		else if (this.isMultipleMode() && this.getInputField() !== null)
		{
			const value = this.getInputField().value.trim();
			const inputDates: Date[] = value
				.split(this.getDateSeparator().trim())
				.map((part: string) => this.createDate(part.trim()))
				.filter((date: Date | null) => date !== null)
			;

			this.deselectAll({ updateInputs: false, emitEvents: false });
			this.selectDates(inputDates, { updateInputs: false, emitEvents: false });
		}
		else if (this.isRangeMode() && this.getRangeStartInput() !== null)
		{
			const rangeStart = this.#getDateFromInput(this.getRangeStartInput());
			const rangeEnd = this.#getDateFromInput(this.getRangeEndInput());

			if (rangeStart === null)
			{
				this.deselectAll({ updateInputs: false, emitEvents: false });
			}
			else
			{
				this.selectRange(rangeStart, rangeEnd, { updateInputs: false, emitEvents: false });
			}
		}
	}

	#getDateFromInput(input: HTMLInputElement | HTMLTextAreaElement | null): Date | null
	{
		if (input === null)
		{
			return null;
		}

		const value = input.value.trim();
		if (!Type.isStringFilled(value))
		{
			return null;
		}

		if (this.getType() === 'time')
		{
			return createDate(value, this.getTimeFormat());
		}

		return this.createDate(value);
	}

	#setInputDate(input: HTMLInputElement | HTMLTextAreaElement | null, date: Date | null, format: string = null): void
	{
		if (input !== null)
		{
			let value = '';
			if (date === null)
			{
				value = '';
			}
			else if (Type.isString(date))
			{
				value = date;
			}
			else
			{
				value = this.formatDate(date, format);
			}

			// eslint-disable-next-line no-param-reassign
			input.value = value;
		}
	}

	getLocale(): string
	{
		return this.#locale;
	}

	isRendered(): boolean
	{
		return this.#rendered;
	}

	getContainer(): HTMLElement
	{
		return this.#refs.remember('container', () => {
			const classes = ['ui-date-picker'];
			if (this.isInline())
			{
				classes.push('--inline');
			}

			if (this.shouldHideHeader())
			{
				classes.push('--hide-header');
			}

			classes.push(`--${this.getType()}-picker`);

			return Tag.render`
				<div tabindex="-1" onkeyup="${this.#handleContainerKeyUp.bind(this)}" class="${classes.join(' ')}">
					${this.getViewsContainer()}
				</div>
			`;
		});
	}

	getViewsContainer(): HTMLElement
	{
		return this.#refs.remember('views', () => {
			return Tag.render`<div class="ui-date-picker-views"></div>`;
		});
	}

	isMultipleMode(): boolean
	{
		return this.#selectionMode === 'multiple';
	}

	isSingleMode(): boolean
	{
		return this.#selectionMode === 'single';
	}

	isRangeMode(): boolean
	{
		return this.#selectionMode === 'range';
	}

	isInline(): boolean
	{
		return this.#inline;
	}

	isFocused(): boolean
	{
		const rootContainer = this.getContainer();
		const activeElement = rootContainer.ownerDocument.activeElement;

		return rootContainer.contains(activeElement) || rootContainer === activeElement;
	}

	setAutoFocus(flag: boolean): boolean
	{
		if (Type.isBoolean(flag))
		{
			this.#autoFocus = flag;
		}
	}

	isAutoFocus(): boolean
	{
		return this.#autoFocus;
	}

	setTargetNode(node: HTMLElement | { left: number, top: number } | null | MouseEvent): void
	{
		if (!Type.isDomNode(node) && !Type.isNull(node) && !Type.isObject(node))
		{
			return;
		}

		this.#targetNode = node;

		if (this.isRendered())
		{
			this.getPopup().setBindElement(this.#targetNode);
			this.getPopup().adjustPosition();
		}
	}

	getTargetNode(): HTMLElement | null
	{
		return this.#targetNode;
	}

	setAutoHide(enable: boolean): void
	{
		if (Type.isBoolean(enable))
		{
			this.#autoHide = enable;
			if (this.isRendered())
			{
				this.getPopup().setAutoHide(enable);
			}
		}
	}

	isAutoHide(): boolean
	{
		return this.#autoHide;
	}

	setHideByEsc(enable: boolean): void
	{
		if (Type.isBoolean(enable))
		{
			this.#hideByEsc = enable;
			if (this.isRendered())
			{
				this.getPopup().setClosingByEsc(enable);
			}
		}
	}

	shouldHideByEsc(): boolean
	{
		return this.#hideByEsc;
	}

	isCacheable(): boolean
	{
		return this.#cacheable;
	}

	setCacheable(cacheable: boolean): void
	{
		if (Type.isBoolean(cacheable))
		{
			this.#cacheable = cacheable;
			if (this.isRendered())
			{
				this.getPopup().setCacheable(cacheable);
			}
		}
	}

	setHideHeader(enable: boolean): void
	{
		if (Type.isBoolean(enable))
		{
			this.#hideHeader = enable;
			if (this.isRendered())
			{
				if (enable)
				{
					Dom.addClass(this.getContainer(), '--hide-header');
				}
				else
				{
					Dom.removeClass(this.getContainer(), '--hide-header');
				}
			}
		}
	}

	shouldHideHeader(): boolean
	{
		return this.#hideHeader;
	}

	createDate(date: DateLike): Date | null
	{
		return createDate(date, this.getDateFormat());
	}

	formatDate(date: Date, format: string = null): string
	{
		const midnight = date.getUTCHours() === 0 && date.getUTCMinutes() === 0 && date.getUTCSeconds() === 0;
		const dateFormat = format === null ? this.getDateFormat() : format;
		let result = DateTimeFormat.format(dateFormat, date, null, true);

		if (this.isTimeEnabled() && midnight && this.shouldCutZeroTime())
		{
			result = result
				.replaceAll(/\s*12:00:00 am\s*/gi, '')
				.replaceAll(/\s*12:00 am\s*/gi, '')
				.replaceAll(/\s*00:00:00\s*/g, '')
				.replaceAll(/\s*00:00\s*/g, '')
			;
		}

		return result;
	}

	formatTime(date: Date, format: string = null): string
	{
		return DateTimeFormat.format(
			format === null ? this.getTimeFormat() : format,
			date,
			null,
			true,
		);
	}

	getDateFormat(): string
	{
		return this.#dateFormat;
	}

	#getDefaultDateFormat(): string
	{
		if (this.getType() === 'year')
		{
			return 'Y';
		}

		if (this.getType() === 'month')
		{
			return 'f - Y';
		}

		if (this.isTimeEnabled())
		{
			if (this.shouldAllowSeconds())
			{
				return DateTimeFormat.getFormat('FORMAT_DATETIME');
			}

			return DateTimeFormat.getFormat('FORMAT_DATETIME').replace(/:s/i, '');
		}

		return DateTimeFormat.getFormat('FORMAT_DATE');
	}

	getTimeFormat(): string
	{
		return this.#timeFormat;
	}

	#render(): void
	{
		if (this.isRendered())
		{
			return;
		}

		if (this.isInline() && this.getTargetNode() !== null)
		{
			Dom.append(this.getContainer(), this.getTargetNode());
		}

		const views = ['day', 'month', 'year', 'time'];
		const index = views.indexOf(this.getType());
		const view = index === -1 ? 'day' : views[index];

		this.setCurrentView(view);
		this.#rendered = true;

		if (this.#keyboardNavigation !== null)
		{
			this.#keyboardNavigation.init();
		}
	}

	#createPicker(pickerId: string): BasePicker
	{
		if (pickerId === 'day')
		{
			const dayPicker = new DayPicker(this);
			dayPicker.subscribe('onSelect', this.#handleDaySelect.bind(this));
			dayPicker.subscribe('onFocus', this.#handleDayFocus.bind(this));
			dayPicker.subscribe('onBlur', this.#handleDayBlur.bind(this));

			dayPicker.subscribe('onPrevBtnClick', () => {
				const unit = this.isFullYear() ? 'year' : 'month';
				const viewDate = addDate(floorDate(this.getViewDate(), unit), unit, -1);
				this.setViewDate(viewDate);
			});

			dayPicker.subscribe('onNextBtnClick', () => {
				const unit = this.isFullYear() ? 'year' : 'month';
				const viewDate = ceilDate(this.getViewDate(), unit);
				this.setViewDate(viewDate);
			});

			dayPicker.subscribe('onMonthClick', () => this.setCurrentView('month'));
			dayPicker.subscribe('onYearClick', () => this.setCurrentView('year'));
			dayPicker.subscribe('onTimeClick', this.#handleTimeClick.bind(this, 'datetime'));
			dayPicker.subscribe('onRangeStartClick', this.#handleTimeClick.bind(this, 'range-start'));
			dayPicker.subscribe('onRangeEndClick', this.#handleTimeClick.bind(this, 'range-end'));

			return dayPicker;
		}

		if (pickerId === 'month')
		{
			const monthPicker = new MonthPicker(this);
			monthPicker.subscribe('onSelect', this.#handleMonthSelect.bind(this));
			monthPicker.subscribe('onFocus', this.#handleMonthFocus.bind(this));
			monthPicker.subscribe('onBlur', this.#handleMonthBlur.bind(this));

			monthPicker.subscribe('onPrevBtnClick', () => {
				const { year, month } = getDate(this.getViewDate());
				const viewDate = createUtcDate(year - 1, month, 1);
				this.setViewDate(viewDate);
			});
			monthPicker.subscribe('onNextBtnClick', () => {
				const { year, month } = getDate(this.getViewDate());
				const viewDate = createUtcDate(year + 1, month, 1);
				this.setViewDate(viewDate);
			});

			monthPicker.subscribe('onTitleClick', () => this.setCurrentView('year'));

			return monthPicker;
		}

		if (pickerId === 'year')
		{
			const yearPicker = new YearPicker(this);
			yearPicker.subscribe('onSelect', this.#handleYearSelect.bind(this));
			yearPicker.subscribe('onFocus', this.#handleYearFocus.bind(this));
			yearPicker.subscribe('onBlur', this.#handleYearBlur.bind(this));
			yearPicker.subscribe('onPrevBtnClick', () => {
				const { year } = getDate(this.getViewDate());
				const viewDate = createUtcDate(year - 12, 0, 1);
				this.setViewDate(viewDate);
			});
			yearPicker.subscribe('onNextBtnClick', () => {
				const { year } = getDate(this.getViewDate());
				const viewDate = createUtcDate(year + 12, 0, 1);
				this.setViewDate(viewDate);
			});

			return yearPicker;
		}

		if (pickerId === 'time')
		{
			const timePicker = this.getTimePickerStyle() === 'wheel' ? new TimePickerWheel(this) : new TimePickerGrid(this);
			if (this.isRangeMode())
			{
				timePicker.subscribe('onSelect', this.#handleTimeRangeSelect.bind(this));
			}
			else
			{
				timePicker.subscribe('onSelect', this.#handleTimeSelect.bind(this));
			}

			timePicker.subscribe('onFocus', this.#handleTimeFocus.bind(this));
			timePicker.subscribe('onBlur', this.#handleTimeBlur.bind(this));
			timePicker.subscribe('onPrevBtnClick', () => this.setCurrentView('day'));
			timePicker.subscribe('onTitleClick', () => this.setCurrentView('day'));

			return timePicker;
		}

		return null;
	}

	#handleContainerKeyUp(event: KeyboardEvent): void
	{
		if (this.isInline())
		{
			return;
		}

		if (event.key === 'Escape' && this.shouldHideByEsc())
		{
			this.hide();
		}
	}

	#handleTimeClick(mode)
	{
		const timePicker: TimePickerWheel = this.getPicker('time');
		const selectTime = (
			(mode === 'range-start' && this.getRangeStart() !== null)
			|| (mode === 'range-end' && this.getRangeEnd() !== null)
			|| (this.getSelectedDate() !== null)
		);

		if (selectTime)
		{
			timePicker.setMode(mode);
			this.setCurrentView('time');
		}
	}

	#handleDaySelect(event: BaseEvent): void
	{
		const { year, month, day } = event.getData();
		let selectedDate = createUtcDate(year, month, day);
		if (this.isRangeMode())
		{
			const currentRange = this.#selectedDates;
			if (currentRange.length === 0)
			{
				const { hours, minutes, seconds } = this.getDefaultTimeParts();
				selectedDate = setTime(selectedDate, hours, minutes, seconds);
			}
			else if (currentRange.length === 1)
			{
				let { hours, minutes, seconds } = this.getDefaultTimeParts();
				if (this.isDateSelected(selectedDate, 'day'))
				{
					({ hours, minutes, seconds } = getDate(this.getRangeStart()));
					minutes += this.getDefaultTimeSpan();
				}

				selectedDate = setTime(selectedDate, hours, minutes, seconds);
			}

			const range = addToRange(selectedDate, currentRange);
			const [start, end] = range;
			if (range.length === 0)
			{
				this.deselectAll();
			}
			else
			{
				this.selectRange(start, end);
			}
		}
		else if (this.isDateSelected(selectedDate))
		{
			if (this.shouldToggleSelected())
			{
				this.deselectDate(selectedDate);
			}
			else if (this.shouldHideOnSelect() && this.isSingleMode())
			{
				this.hide();
			}
		}
		else
		{
			let { hours, minutes, seconds } = this.getDefaultTimeParts();
			if (this.isSingleMode() && this.getSelectedDate() !== null)
			{
				// save previous time
				({ hours, minutes, seconds } = getDate(this.getSelectedDate()));
			}

			this.selectDate(createUtcDate(year, month, day, hours, minutes, seconds));

			if (this.shouldHideOnSelect() && this.isSingleMode() && !this.isTimeEnabled())
			{
				this.hide();
			}
		}
	}

	#handleDayFocus(event: BaseEvent): void
	{
		const { year, month, day } = event.getData();

		const focusDate = createUtcDate(year, month, day);
		if (!isDatesEqual(focusDate, this.getFocusDate()))
		{
			this.setFocusDate(focusDate);
		}
	}

	#handleDayBlur(event: BaseEvent): void
	{
		this.setFocusDate(null);
	}

	#handleMonthFocus(event: BaseEvent): void
	{
		const { year, month } = event.getData();

		const focusDate = createUtcDate(year, month);
		if (!isDatesEqual(focusDate, this.getFocusDate(), 'month'))
		{
			this.setFocusDate(focusDate);
		}
	}

	#handleMonthBlur(event: BaseEvent): void
	{
		this.setFocusDate(null);
	}

	#handleYearFocus(event: BaseEvent): void
	{
		const { year } = event.getData();

		const focusDate = createUtcDate(year);
		if (!isDatesEqual(focusDate, this.getFocusDate(), 'year'))
		{
			this.setFocusDate(focusDate);
		}
	}

	#handleYearBlur(event: BaseEvent): void
	{
		this.setFocusDate(null);
	}

	#handleTimeFocus(event: BaseEvent): void
	{
		const { hour, minute } = event.getData();
		let focusDate = cloneDate(this.getInitialFocusDate());
		if (Type.isNumber(hour))
		{
			focusDate = setTime(focusDate, hour, null, null);
			this.setFocusDate(focusDate);
		}
		else if (Type.isNumber(minute))
		{
			focusDate = setTime(focusDate, null, minute, null);
			this.setFocusDate(focusDate);
		}
	}

	#handleTimeBlur(event: BaseEvent): void
	{
		this.setFocusDate(null);
	}

	#handleMonthSelect(event: BaseEvent): void
	{
		const { year } = getDate(this.getViewDate());
		const month: number = event.getData().month;
		const date = createUtcDate(year, month);

		if (this.getType() === 'month')
		{
			this.selectDate(date);
			if (this.shouldHideOnSelect())
			{
				this.hide();
			}
		}
		else
		{
			this.setViewDate(date);
			this.setCurrentView('day');
		}
	}

	#handleYearSelect(event: BaseEvent): void
	{
		const { month } = getDate(this.getViewDate());
		const year: number = event.getData().year;
		const date = createUtcDate(year, month);

		if (this.getType() === 'year')
		{
			this.selectDate(createUtcDate(year));
			if (this.shouldHideOnSelect())
			{
				this.hide();
			}
		}
		else
		{
			this.setViewDate(date);
			this.setCurrentView('day');
		}
	}

	#handleTimeSelect(event: BaseEvent<{ hour: number, minute: number }>): void
	{
		let selectedDate = null;
		if (this.getType() === 'time')
		{
			selectedDate = (
				this.getSelectedDate() === null
					? ceilDate(this.getToday(), 'day')
					: cloneDate(this.getSelectedDate())
			);
		}
		else if (this.getSelectedDate() === null)
		{
			return;
		}
		else
		{
			selectedDate = cloneDate(this.getSelectedDate());
		}

		const hideOrSwitchToDayView = () => {
			if (this.shouldHideOnSelect())
			{
				this.hide();
			}
			else if (this.getType() === 'date')
			{
				this.setCurrentView('day');
			}
		};

		const { hour, minute } = event.getData();
		if (Type.isNumber(hour))
		{
			const currentHour = this.getSelectedDate() === null ? -1 : selectedDate.getUTCHours();
			if (currentHour === hour)
			{
				hideOrSwitchToDayView();
			}
			else
			{
				selectedDate.setUTCHours(hour);
				this.selectDate(selectedDate);
			}
		}
		else if (Type.isNumber(minute))
		{
			const currentMinute = this.getSelectedDate() === null ? -1 : selectedDate.getUTCMinutes();
			if (currentMinute !== minute)
			{
				selectedDate.setUTCMinutes(minute);
				this.selectDate(selectedDate);
			}

			if (this.getTimePickerStyle() === 'grid')
			{
				hideOrSwitchToDayView();
			}
		}
	}

	#handleTimeRangeSelect(event: BaseEvent<{ hour: number, minute: number }>): void
	{
		const timePicker: TimePickerWheel = event.getTarget();
		const rangeEndChange = timePicker.getMode() === 'range-end';

		let rangeStart = this.getRangeStart() === null ? null : cloneDate(this.getRangeStart());
		let rangeEnd = this.getRangeEnd() === null ? null : cloneDate(this.getRangeEnd());

		if (rangeStart === null || (rangeEnd === null && rangeEndChange))
		{
			return;
		}

		const switchToDayView = (): boolean => {
			if (this.getType() === 'date' && this.getTimePickerStyle() === 'grid')
			{
				this.setCurrentView('day');
			}
		};

		const { hour, minute } = event.getData();
		if (Type.isNumber(hour))
		{
			if (rangeEndChange)
			{
				const currentHour = rangeEnd.getUTCHours();
				if (currentHour === hour)
				{
					switchToDayView();

					return;
				}

				rangeEnd.setUTCHours(hour);
			}
			else
			{
				const currentHour = rangeStart.getUTCHours();
				if (currentHour === hour)
				{
					switchToDayView();

					return;
				}

				rangeStart.setUTCHours(hour);
			}
		}
		else if (Type.isNumber(minute))
		{
			if (rangeEndChange)
			{
				const currentMinute = rangeEnd.getUTCMinutes();
				if (currentMinute === minute)
				{
					switchToDayView();

					return;
				}

				rangeEnd.setUTCMinutes(minute);
			}
			else
			{
				const currentMinute = rangeStart.getUTCMinutes();
				if (currentMinute === minute)
				{
					switchToDayView();

					return;
				}

				rangeStart.setUTCMinutes(minute);
			}
		}

		if (rangeEnd !== null && rangeStart > rangeEnd)
		{
			if (rangeEndChange)
			{
				rangeStart = addDate(rangeEnd, 'minute', -this.getDefaultTimeSpan());
			}
			else
			{
				rangeEnd = addDate(rangeStart, 'minute', this.getDefaultTimeSpan());
			}
		}

		this.selectRange(rangeStart, rangeEnd);

		if (Type.isNumber(minute))
		{
			switchToDayView();
		}
	}

	#handlePopupShow(): void
	{
		if (!this.isFocused() && this.isAutoFocus())
		{
			this.focus();
		}

		if (this.isSingleOpening())
		{
			if (singleOpenDatePicker !== null)
			{
				singleOpenDatePicker.hide();
			}

			// eslint-disable-next-line unicorn/no-this-assignment
			singleOpenDatePicker = this;
		}

		this.emit('onShow');
	}

	#handlePopupFirstShow(): void
	{
		this.#render();

		this.emit('onFirstShow');
	}

	#handlePopupClose(): void
	{
		if (this.getType() === 'date')
		{
			this.setCurrentView('day');
		}

		this.setFocusDate(null);
		this.setViewDate(this.getDefaultViewDate());

		if (this.isSingleOpening())
		{
			singleOpenDatePicker = null;
		}

		if (this.isFocused())
		{
			this.#focusInputField();
		}

		this.emit('onHide');
	}

	#handlePopupDestroy(): void
	{
		this.destroy();
	}

	destroy(): void
	{
		if (this.#destroying)
		{
			return;
		}

		this.#destroying = true;
		this.emit(DatePickerEvent.DESTROY);

		if (this.isRendered())
		{
			Dom.remove(this.getContainer());
		}

		this.#unbindInputEvents(this.getInputField());
		this.#unbindInputEvents(this.getRangeStartInput());
		this.#unbindInputEvents(this.getRangeEndInput());

		if (this.#popup !== null)
		{
			this.#popup.destroy();
		}

		this.#refs = null;
		this.#views = null;
		this.#selectedDates = null;

		Object.setPrototypeOf(this, null);
	}
}
