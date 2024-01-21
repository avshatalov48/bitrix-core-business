import { Tag, Loc, Type, Dom, Event, Browser } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Util } from 'calendar.util';
import Day from './day';
import { DateTimeFormat } from 'main.date';
import { MenuManager } from 'main.popup';

export default class Calendar
{
	#userIds;
	#accessibility;
	#layout;
	#currentMonth;
	#currentYear;
	#nowTime;
	#months;
	#selectedDay;
	#monthsSlotsMap;
	#timezoneOffsetUtc;
	#selectedTimezoneOffsetUtc;
	#currentMonthIndex;
	#currentDayNumber;
	#timezoneList;
	#calendarSettings;
	#rule;
	#selectedTimezoneId;
	#selectedTimezoneNode;
	#config;
	#loc;
	#timeZonePopup;

	constructor(options)
	{
		this.#layout = {
			wrapper: null,
			monthWrapper: null,
			timezoneWrapper: null,
			month: null,
			currentMonth: null,
			prevNav: null,
			nextNav: null,
			daysOfWeek: null,
			navigation: null,
			back: null,
		};

		this.#userIds = options.userIds;
		this.#accessibility = options.accessibility;
		this.#timezoneList = options.timezoneList;
		this.#calendarSettings = options.calendarSettings;
		this.#rule = options.rule;

		this.#nowTime = new Date();
		this.#currentMonthIndex = 0;
		this.#currentDayNumber = 1;
		this.#selectedTimezoneId = Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC';
		this.#currentMonth = this.#nowTime.getMonth();
		this.#currentYear = this.#nowTime.getFullYear();
		this.#timezoneOffsetUtc = this.#nowTime.getTimezoneOffset();
		this.#selectedTimezoneOffsetUtc = this.#nowTime.getTimezoneOffset();
		this.#months = [];
		this.#monthsSlotsMap = [];

		this.#config = {
			slotSize: this.#rule.slotSize,
			freeTime: {},
			stepSize: 30,
			weekHolidays: [6, 0],
			weekStart: 1,
		};
		this.#loc = {
			weekdays: Util.getWeekdaysLoc(),
		};
		this.#timeZonePopup = null;

		this.#initConfig();
		this.#initCurrentMonthSlots();
		this.#bindEvents();

		// preload the next month's accessibilities
		const nextYear = this.#getNextYear();
		const nextMonth = this.#getNextMonth();
		this.#loadMonthAccessibility(nextYear, nextMonth, false);

		setInterval(this.#incrementTime.bind(this), 15000);
	}

	#bindEvents()
	{
		EventEmitter.subscribe('selectDate', (event) => {
			const newSelectedDay = event.data;
			if (this.#selectedDay !== newSelectedDay)
			{
				this.#selectedDay?.unSelect();
			}

			this.#selectedDay = newSelectedDay;
			this.#currentDayNumber = this.#selectedDay.getDay();
		});

		EventEmitter.subscribe('onSaveEvent', async (event) => {
			if (event.data.state === 'created' || event.data.state === 'not-created')
			{
				await this.updateEventSlotsList();
				this.highlightMonthDay();
			}
		});

		EventEmitter.subscribe('onDeleteEvent', async () => {
			await this.updateEventSlotsList();
		});

		EventEmitter.subscribe('onCreateAnotherEventButtonClick', () => {
			this.selectFirstAvailableDay();
		});
	}

	#incrementTime()
	{
		this.#nowTime = new Date();

		const timezoneNode = this.#getNodeTimeZone();
		Dom.clean(this.#getNodeTimezoneWrapper());
		Dom.append(timezoneNode, this.#getNodeTimezoneWrapper());
	}

	#initConfig()
	{
		if (this.#calendarSettings.weekHolidays)
		{
			this.#config.weekHolidays = this.#calendarSettings.weekHolidays.map(
				(weekDay) => Util.getIndByWeekDay(weekDay),
			);
		}

		if (this.#calendarSettings.yearHolidays)
		{
			this.#config.yearHolidays = this.#calendarSettings.yearHolidays;
		}

		if (this.#calendarSettings.weekStart)
		{
			this.#config.weekStart = Util.getIndByWeekDay(this.#calendarSettings.weekStart);
			this.#loc.weekdays.push(...this.#loc.weekdays.splice(0, this.#config.weekStart));
		}

		for (const range of this.#rule.ranges)
		{
			for (const weekday of range.weekdays)
			{
				this.#config.freeTime[weekday] ??= [];
				this.#config.freeTime[weekday].push({
					from: parseInt(range.from, 10),
					to: parseInt(range.to, 10),
				});

				const [intersected, notIntersected] = this.#separate((interval) => this.#doIntervalsIntersect(
					interval.from,
					interval.to,
					parseInt(range.from, 10),
					parseInt(range.to, 10),
				), this.#config.freeTime[weekday]);

				if (intersected.length > 0)
				{
					const from = Math.min(...intersected.map(interval => interval.from));
					const to = Math.max(...intersected.map(interval => interval.to));

					this.#config.freeTime[weekday] = [...notIntersected, { from, to }];
				}
			}
		}

		const timezoneOffset = Util.getTimeZoneOffset(this.#selectedTimezoneId);
		const serverOffset = parseInt(this.#calendarSettings.serverOffset, 10);
		const offset = serverOffset + timezoneOffset;
		for (const weekday in this.#config.freeTime)
		{
			this.#config.freeTime[weekday] = this.#config.freeTime[weekday].map((range) => {
				return {
					from: range.from - offset,
					to: range.to - offset,
				};
			});
		}

		if (this.#selectedTimezoneId === 'UTC' || !this.#timezoneList[this.#selectedTimezoneId])
		{
			this.#selectedTimezoneId = 'Africa/Dakar';
		}
	}

	#separate(take, array)
	{
		return array.reduce(([t, f], e) => (take(e) ? [[...t, e], f] : [t, [...f, e]]), [[], []]);
	}

	#initCurrentMonthSlots()
	{
		this.#calculateDateTimeSlots(this.#nowTime.getFullYear(), this.#nowTime.getMonth());

		const month = this.#createMonth(this.#nowTime.getFullYear(), this.#nowTime.getMonth());
		this.#months.push(month);
	}

	#calculateDateTimeSlots(year, month)
	{
		const map = [];
		const daysCount = new Date(year, month + 1, 0).getDate();
		const accessibilityArrayKey = `${month + 1}.${year}`;
		const nowTimestamp = this.#nowTime.getTime();
		const timezoneOffset = (this.#selectedTimezoneOffsetUtc - this.#timezoneOffsetUtc) * (-60) * 1000;

		for (let dayIndex = 1; dayIndex <= daysCount; dayIndex++)
		{
			const currentDate = new Date(year, month, dayIndex);

			if (this.#isYearHoliday(currentDate))
			{
				continue;
			}

			const freeTime = this.#config.freeTime[currentDate.getDay()];
			if (Type.isUndefined(freeTime))
			{
				continue;
			}

			for (const range of freeTime)
			{
				const from = new Date(year, month, dayIndex, Math.floor(range.from / 60), range.from % 60);
				const to = new Date(year, month, dayIndex, Math.floor(range.to / 60), range.to % 60);

				const dayAccessibility = this.#accessibility[accessibilityArrayKey].filter((event) => {
					const parseUTC = !event.isFullDay;
					return this.#doIntervalsIntersect(
						BX.parseDate(event.from, parseUTC).getTime(),
						BX.parseDate(event.to, parseUTC).getTime(),
						from.getTime(),
						to.getTime(),
					);
				});

				while (from.getTime() < to.getTime())
				{
					const slotStart = from.getTime();
					const slotEnd = slotStart + this.#config.slotSize * 60 * 1000;

					if (slotEnd > to.getTime())
					{
						break;
					}

					const slotAccessibility = dayAccessibility.filter((event) => {
						const parseUTC = !event.isFullDay;
						return this.#doIntervalsIntersect(
							BX.parseDate(event.from, parseUTC).getTime(),
							BX.parseDate(event.to, parseUTC).getTime(),
							slotStart,
							slotEnd,
						);
					});

					const available = slotAccessibility.length === 0 && slotStart > nowTimestamp;
					if (available)
					{
						const timeFrom = new Date(slotStart + timezoneOffset);
						const timeTo = new Date(timeFrom.getTime() + (slotEnd - slotStart));
						const dateIndex = timeFrom.getDate();
						map[dateIndex] ??= [];
						if (timeFrom.getMonth() === month)
						{
							map[dateIndex].push({ timeFrom, timeTo });
						}
					}

					from.setTime(from.getTime() + this.#config.stepSize * 60 * 1000);
				}
			}
		}

		this.#monthsSlotsMap[accessibilityArrayKey] = map;
	}

	#doIntervalsIntersect(from1, to1, from2, to2)
	{
		const startsInside = from2 <= from1 && from1 < to2;
		const endsInside = from2 < to1 && to1 <= to2;
		const startsBeforeEndsAfter = from1 <= from2 && to1 >= to2;

		return startsInside || endsInside || startsBeforeEndsAfter;
	}

	#createMonth(year, month)
	{
		return {
			year,
			month,
			currentTimezoneOffset: this.#selectedTimezoneOffsetUtc,
			name: this.#getMonthName(month),
			days: this.#getMonthDays(year, month),
		};
	}

	async updateEventSlotsList()
	{
		const month = this.#months[this.#currentMonthIndex];
		const currentYear = month.year;
		const currentMonth = month.month + 1;

		await this.#loadMonthAccessibility(currentYear, currentMonth);

		this.#reCreateCurrentMonth();
	}

	#reCreateCurrentMonth()
	{
		this.#updateMonth(this.#currentMonthIndex);
		this.#updateCalendar();
	}

	async #createNextMonth()
	{
		this.nextMonthCreating = true;
		const currentMonth = this.#months[this.#currentMonthIndex];
		const currentYear = currentMonth.year;
		const currentMonthIndex = currentMonth.month;

		const nextMonthIndex = (currentMonthIndex + 1) % 12;
		const nextYear = currentYear + Math.floor((currentMonthIndex + 1) / 12);
		const nextMonth = nextMonthIndex + 1;

		await this.#loadMonthAccessibility(nextYear, nextMonth);

		this.#calculateDateTimeSlots(nextYear, nextMonthIndex);

		const month = this.#createMonth(nextYear, nextMonthIndex);
		this.#months.push(month);
		this.nextMonthCreating = false;
	}

	#getNextMonth()
	{
		const currentMonth = this.#months[this.#currentMonthIndex];
		const currentMonthIndex = currentMonth.month;

		const nextMonthIndex = (currentMonthIndex + 1) % 12;
		return nextMonthIndex + 1;
	}

	#getNextYear()
	{
		const currentMonth = this.#months[this.#currentMonthIndex];
		const currentYear = currentMonth.year;
		const currentMonthIndex = currentMonth.month;

		return currentYear + Math.floor((currentMonthIndex + 1) / 12);
	}

	async #loadMonthAccessibility(year, month, preloadNextMonth = true)
	{
		const arrayKey = `${month}.${year}`;
		const firstMonthDay = new Date(year, month - 1, 1);
		const lastMonthDay = new Date(year, month, 0, 23, 59);

		if (!this.#accessibility[arrayKey])
		{
			const response = await BX.ajax.runAction('calendar.api.sharingajax.getUsersAccessibility', {
				data: {
					userIds: this.#userIds,
					timestampFrom: firstMonthDay.getTime(),
					timestampTo: lastMonthDay.getTime(),
				},
			});

			this.#accessibility[arrayKey] = response.data;
		}

		if (preloadNextMonth === false)
		{
			return;
		}

		const nextMonthIndex = (month) % 12;
		const nextYear = year + Math.floor((month) / 12);
		const nextMonth = nextMonthIndex + 1;

		this.#loadMonthAccessibility(nextYear, nextMonth, false);
	}

	#getMonthName(month)
	{
		const currentMonthDate = new Date(this.#nowTime.getFullYear(), month, 1);

		return DateTimeFormat.format('f', currentMonthDate.getTime() / 1000);
	}

	#getMonthDays(year, month)
	{
		const days = [];
		const daysCount = new Date(year, month + 1, 0).getDate();
		const accessibilityArrayKey = `${month + 1}.${year}`;

		for (let dayIndex = 1; dayIndex <= daysCount; dayIndex++)
		{
			const newDay = new Date(year, month, dayIndex);
			const slots = this.#monthsSlotsMap[accessibilityArrayKey][newDay.getDate()] ?? [];

			const params = {
				value: dayIndex,
				slots,
				weekend: this.#isHoliday(newDay),
				enableBooking: slots.length > 0,
			};

			const day = new Day(params);
			days.push(day);
		}

		return days;
	}

	selectFirstAvailableDay()
	{
		let visibleDays = this.#months[this.#currentMonthIndex].days;
		if (this.#currentMonthIndex === 0)
		{
			const todayDay = this.#nowTime.getDate();
			visibleDays = visibleDays.filter((day) => day.getDay() >= todayDay).slice(0, 14);
		}
		let dayToSelect = visibleDays.find((day) => day.isEnableBooking());
		if (dayToSelect === undefined)
		{
			dayToSelect = visibleDays[0];
		}

		dayToSelect.select();
	}

	selectMonthDay()
	{
		const dayToSelect = this.#getDayToSelect();

		this.#currentDayNumber = dayToSelect.day;
		dayToSelect.select();
	}

	highlightMonthDay()
	{
		const dayToSelect = this.#getDayToSelect();

		this.#currentDayNumber = dayToSelect.day;
		dayToSelect.highlight();
	}

	#getDayToSelect()
	{
		const monthDays = this.#months[this.#currentMonthIndex].days;
		let dayToSelect = monthDays.find((day) => day.getDay() === this.#currentDayNumber);
		if (dayToSelect === undefined)
		{
			dayToSelect = monthDays[monthDays.length - 1];
		}

		return dayToSelect;
	}

	#isHoliday(day)
	{
		const monthKey = (`0${day.getMonth() + 1}`).slice(-2);
		const dayMonthKey = `${day.getDate()}.${monthKey}`;

		return (this.#config.weekHolidays.includes(day.getDay()) || this.#config.yearHolidays[dayMonthKey] !== undefined);
	}

	#isYearHoliday(day)
	{
		const dayMonthKey = day.getDate() + '.' + ('0' + (day.getMonth() + 1)).slice(-2);

		return this.#config.yearHolidays[dayMonthKey] !== undefined;
	}

	getSelectedTimezoneId()
	{
		return this.#selectedTimezoneId;
	}

	#getNodeTimeZone()
	{
		this.#selectedTimezoneNode = Tag.render`
			<div class="calendar-sharing__timezone-value">
				${this.#getFormattedTimezone(this.#selectedTimezoneId)}
			</div>
		`;

		const timezoneSelect = Tag.render`
			<div class="calendar-sharing__timezone">
				${Browser.isMobile() ? this.#getNodeTimezoneSelect() : ''}
				<div class="calendar-sharing__timezone-area">
					<div class="calendar-sharing__timezone-title">${Loc.getMessage('CALENDAR_SHARING_YOR_TIME')}:</div>
					${this.#selectedTimezoneNode}
				</div>
			</div>
		`;

		this.#getPopupTimezoneSelect();

		if (!Browser.isMobile())
		{
			Event.bind(timezoneSelect, 'click', () => {
				const timezonesPopup = this.#getPopupTimezoneSelect().getPopupWindow();
				timezonesPopup.show();

				const popupContent = timezonesPopup.getContentContainer();
				const selectedTimezoneItem = popupContent.querySelector('.menu-popup-item.--selected');
				const selectOffset = timezoneSelect.getBoundingClientRect().top
					+ timezoneSelect.offsetHeight / 4
					- popupContent.getBoundingClientRect().top
				;
				popupContent.scrollTop = selectedTimezoneItem.offsetTop - selectOffset;
			});
		}

		return timezoneSelect;
	}

	#getPopupTimezoneSelect()
	{
		if (this.#timeZonePopup?.getPopupWindow().isShown())
		{
			return this.#timeZonePopup;
		}

		this.#timeZonePopup?.destroy();
		const items = Object.keys(this.#timezoneList).map((timezoneId) => ({
			text: this.#getFormattedTimezone(timezoneId),
			className: (timezoneId === this.#selectedTimezoneId) ? 'menu-popup-no-icon --selected' : 'menu-popup-no-icon',
			onclick: () => {
				this.#updateTimezone(timezoneId);
				this.#timeZonePopup.close();
			},
		}));

		this.#timeZonePopup = MenuManager.create({
			id: 'momomiomsiomx92984j',
			className: 'calendar-sharing-timezone-select-popup',
			items,
			autoHide: true,
			maxHeight: window.innerHeight - 150,
		});

		return this.#timeZonePopup;
	}

	#getNodeTimezoneSelect()
	{
		const selectNode = Tag.render`
			<select class="calendar-sharing__timezone-select">
				${Object.keys(this.#timezoneList).map((timezoneId) => Tag.render`
					<option value="${timezoneId}" ${timezoneId === this.#selectedTimezoneId ? 'selected' : ''}>
						${this.#getFormattedTimezone(timezoneId)}
					</option>
				`)}
			</select>
		`;

		Event.bind(selectNode, 'change', () => this.#updateTimezone(selectNode.value));

		return selectNode;
	}

	#updateTimezone(timezoneId)
	{
		this.#selectedTimezoneId = timezoneId;
		this.#selectedTimezoneOffsetUtc = -(this.#timezoneList[this.#selectedTimezoneId].offset / 60);
		EventEmitter.emit('updateTimezone', { timezone: timezoneId });
		this.#selectedTimezoneNode.innerHTML = this.#getFormattedTimezone(this.#selectedTimezoneId);
		this.#reCreateCurrentMonth();
		this.selectMonthDay();
	}

	#getFormattedTimezone(timezoneId)
	{
		return `${this.getTimezonePrefix(this.#timezoneList[timezoneId].offset)} - ${timezoneId}`;
	}

	getTimezonePrefix(timezoneOffset)
	{
		const offset = timezoneOffset * 1000 - this.#timezoneOffsetUtc * (-60000);
		const date = new Date(this.#nowTime.getTime() + offset);

		return DateTimeFormat.format(Util.getTimeFormatShort(), date.getTime() / 1000);
	}

	#getNodeDaysOfWeek(): HTMLElement
	{
		if (!this.#layout.daysOfWeek)
		{
			const nodesWeekDays = this.#loc.weekdays.map((weekDay) => {
				return Tag.render`
					<div class="calendar-sharing__month-col --day-of-week">${weekDay}</div>
				`;
			});

			this.#layout.daysOfWeek = Tag.render`
				<div class="calendar-sharing__month-row">${nodesWeekDays}</div>
			`;
		}

		return this.#layout.daysOfWeek;
	}

	#getNodeDay(param = {}): HTMLElement
	{
		param.selected = this.#selectedDay?.getDay() === param.value && param.currentMonth === true;
		const day = new Day(param);

		return day.render();
	}

	#getNodeMonth()
	{
		const monthInfo = this.#months[this.#currentMonthIndex];
		const year = monthInfo.year;
		const month = monthInfo.month;

		const firstDayOfMonth = (new Date(year, month, 7).getDay() - (this.#config.weekStart - 1) + 7) % 7;
		const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
		const lastDayOfLastMonth = month === 0
			? new Date(year - 1, 11, 0).getDate()
			: new Date(year, month, 0).getDate()
		;

		const nodeMonth = Tag.render`<div class="calendar-sharing__month-row"></div>`;

		let k = lastDayOfLastMonth - firstDayOfMonth + 1;
		for (let j = 0; j < firstDayOfMonth; j++)
		{
			Dom.append(this.#getNodeDay({
				value: k,
				notCurrentMonth: true,
			}), nodeMonth);
			k++;
		}

		for (let i = 0; i <= lastDateOfMonth - 1; i++)
		{
			const day = monthInfo.days[i];
			Dom.append(day.render(), nodeMonth);
		}

		let dayOfWeek = (new Date(year, month, lastDateOfMonth).getDay() - this.#config.weekStart + 7) % 7;
		for (dayOfWeek, k = 1; dayOfWeek < 6; dayOfWeek++)
		{
			Dom.append(this.#getNodeDay({
				value: k,
				notCurrentMonth: true,
			}), nodeMonth);
			k++;
		}

		const result = Tag.render`
			<div class="calendar-sharing__month">
				${this.#getNodeDaysOfWeek()}
				${nodeMonth}
			</div>
		`;

		const touchPosition = {
			x: null,
		};

		const touchMove = (ev) => {
			touchPosition.x = ev.changedTouches[0].clientX;
		};

		Event.bind(result, 'touchstart', (ev) => {
			touchMove(ev);
		});

		Event.bind(result, 'touchend', (ev) => {
			if (touchPosition.x < ev.changedTouches[0].clientX - 100)
			{
				this.#handlePreviousMonthArrowClick();
			}

			if (touchPosition.x > ev.changedTouches[0].clientX + 100)
			{
				this.#handleNextMonthArrowClick();
			}

			result.style.removeProperty('transform');
		});

		Event.bind(result, 'touchmove', (ev) => {
			ev.preventDefault();
		});

		return result;
	}

	#getNodeMonthWrapper(): HTMLElement
	{
		if (!this.#layout.monthWrapper)
		{
			this.#layout.monthWrapper = Tag.render`
				<div class="calendar-sharing__calendar-block --month">
					${this.#getNodeMonth()}
				</div>
			`;
		}

		return this.#layout.monthWrapper;
	}

	#getNodeTimezoneWrapper(): HTMLElement
	{
		if (!this.#layout.timezoneWrapper)
		{
			this.#layout.timezoneWrapper = Tag.render`
				<div class="calendar-sharing__calendar-block">
					${this.#getNodeTimeZone()}
				</div>
			`;
		}

		return this.#layout.timezoneWrapper;
	}

	#getNodeCurrentMonth()
	{
		if (!this.#layout.currentMonth)
		{
			const currentMonthName = this.#months[this.#currentMonthIndex].name;
			const currentYear = this.#months[this.#currentMonthIndex].year;
			this.#layout.currentMonth = Tag.render`
				<div class="calendar-sharing__calendar-title-day calendar-pub-ui__typography-title">${currentMonthName}, ${currentYear}</div>
			`;

			EventEmitter.subscribe(this, 'updateCalendar', () => {
				const currentMonthName = this.#months[this.#currentMonthIndex].name;
				const currentYear = this.#months[this.#currentMonthIndex].year;

				this.#layout.currentMonth.innerHTML = `${currentMonthName}, ${currentYear}`;
			});
		}

		return this.#layout.currentMonth;
	}

	#updateCalendar(direction)
	{
		Dom.clean(this.#getNodeMonthWrapper());
		const nodeMonth = this.#getNodeMonth();

		if (Type.isString(direction))
		{
			Dom.addClass(nodeMonth, `--animate-${direction}`);
			Event.bind(nodeMonth, 'animationend', () => {
				Dom.removeClass(nodeMonth, `--animate-${direction}`);
			}, { once: true });
		}

		Dom.append(nodeMonth, this.#getNodeMonthWrapper());
		EventEmitter.emit(this, 'updateCalendar');

		if (this.#currentMonthIndex === 0)
		{
			Dom.addClass(this.#layout.prevNav, '--disabled');
		}
		else
		{
			Dom.removeClass(this.#layout.prevNav, '--disabled');
		}
	}

	#getNodePrevNav(): HTMLElement
	{
		if (!this.#layout.prevNav)
		{
			this.#layout.prevNav = Tag.render`
				<div class="calendar-sharing__calendar-nav_prev --disabled" title="${Loc.getMessage('CALENDAR_SHARING_NAV_PREV')}"></div>
			`;

			Event.bind(this.#layout.prevNav, 'click', this.#handlePreviousMonthArrowClick.bind(this));
		}

		return this.#layout.prevNav;
	}

	#getNodeNextNav(): HTMLElement
	{
		if (!this.#layout.nextNav)
		{
			this.#layout.nextNav = Tag.render`
				<div class="calendar-sharing__calendar-nav_next" title="${Loc.getMessage('CALENDAR_SHARING_NAV_NEXT')}"></div>
			`;
			Event.bind(this.#layout.nextNav, 'click', this.#handleNextMonthArrowClick.bind(this));
		}

		return this.#layout.nextNav;
	}

	#getNodeNavigation(): HTMLElement
	{
		if (!this.#layout.navigation)
		{
			this.#layout.navigation = Tag.render`
				<div class="calendar-sharing__calendar-nav">
					${this.#getNodePrevNav()}
					${this.#getNodeNextNav()}
				</div>
			`;
		}

		return this.#layout.navigation;
	}

	async #handleNextMonthArrowClick()
	{
		if (this.#currentMonthIndex === this.#months.length - 1)
		{
			if (this.nextMonthCreating)
			{
				return;
			}

			await this.#createNextMonth();
		}

		this.#currentMonthIndex += 1;
		this.#updateMonth(this.#currentMonthIndex);

		EventEmitter.emit(this, 'clickNextMonth');
		this.#updateCalendar('next');
		this.selectMonthDay();
	}

	#handlePreviousMonthArrowClick()
	{
		if (this.#currentMonthIndex === 0)
		{
			return;
		}

		this.#currentMonthIndex -= 1;
		this.#updateMonth(this.#currentMonthIndex);

		EventEmitter.emit(this, 'clickPrevMonth');
		this.#updateCalendar('prev');
		this.selectMonthDay();
	}

	#updateMonth(monthIndex)
	{
		const year = this.#months[monthIndex].year;
		const month = this.#months[monthIndex].month;
		this.#calculateDateTimeSlots(year, month);

		this.#months[this.#currentMonthIndex] = this.#createMonth(year, month);
	}

	#getNodeBack(): HTMLElement
	{
		if (!this.#layout.back)
		{
			this.#layout.back = Tag.render`
				<div class="calendar-sharing__calendar-back"></div>
			`;

			Event.bind(this.#layout.back, 'click', () => {
				EventEmitter.emit('hideSlotSelector', this);
			});
		}

		return this.#layout.back;
	}

	#getNodeWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#layout.wrapper = Tag.render`
				<div class="calendar-sharing__calendar">
					<div class="calendar-sharing__calendar-bar">
						${this.#getNodeBack()}
						${this.#getNodeCurrentMonth()}
						${this.#getNodeNavigation()}
					</div>
					${this.#getNodeMonthWrapper()}
					${this.#getNodeTimezoneWrapper()}
				</div>
			`;
		}

		return this.#layout.wrapper;
	}

	render(): HTMLElement
	{
		return this.#getNodeWrapper();
	}
}
