import '../../css/sharing.css';
import { Dom } from 'main.core';
import { DateTimeFormat } from "main.date";
import { Util } from 'calendar.util';

export const DateSelector = {
	props: {
		owner: Object,
		calendarSettings: Object,
		userAccessibility: Object,
		timezoneList: Object,
	},
	name: 'DateSelector',
	data()
	{
		return {
			accessibility: this.userAccessibility,
			eventDurability: 3600000,
			stepSize: 3600000,
			nowTime: new Date(),
			timezoneOffsetUtc: new Date().getTimezoneOffset(),
			currentTimezoneOffsetUtc: new Date().getTimezoneOffset(),
			selectedTimezoneId: Intl.DateTimeFormat().resolvedOptions().timeZone,
			currentMonthIndex: 0,
			currentDayNumber: 1,
			isMobileBrowser: false,
			displayedMonth: [0],
			isMonthAnimating: false,
			isSlotListAnimating: false,
			months: [],
			monthsSlots: [],
			monthsSlotsMap: {},
			loc: {
				weekdays: Util.getWeekdaysLoc(),
			},

			config: {
				weekHolidays: [6, 0],
				weekStart: 1,
			},
		};
	},
	created()
	{
		this.setConfig(this.calendarSettings);
		this.isMobileBrowser = Util.isMobileBrowser();
		setInterval(this.incrementTime, 15000);

		const slots = this.calculateDateTimeSlots(this.nowTime.getFullYear(), this.nowTime.getMonth());
		this.monthsSlots.push(slots);
		const slotsMap = this.getDateTimeSlotsMap(slots);
		const arrayKey = (this.nowTime.getMonth() + 1) + '.' + this.nowTime.getFullYear();
		this.monthsSlotsMap[arrayKey] = slotsMap;

		const month = this.createMonth(this.nowTime.getFullYear(), this.nowTime.getMonth());
		this.months.push(month);

		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:onEventAdd', async () => {
			await this.updateEventSlotsList();
			this.openEventSlotList(this.getDayByNumber(this.currentDayNumber));
		});
		this.$Bitrix.eventEmitter.subscribe('calendar:sharing:onShowCalendar', () => {
			this.setPageVisualSettings();
			setTimeout(() => {
				this.resizeSelect();
			}, 100);
		})
		this.setPageVisualSettings();
	},
	mounted()
	{
		this.resizeSelect();
		this.selectFirstAvailableDay();
		this.setMonthHeight();

		this.DOM = {
			monthsContainer: document.querySelector('.calendar-sharing__months-container'),
		};
	},
	updated()
	{
		if (this.displayedMonth.length === 1)
		{
			return;
		}

		if (this.displayedMonth[0] === -1)
		{
			this.DOM.monthsContainer.scrollLeft = this.DOM.monthsContainer.scrollWidth - this.DOM.monthsContainer.offsetWidth;
			this.animateToPreviousMonth();
		}
		if (this.displayedMonth[1] === 1)
		{
			this.animateToNextMonth();
		}
	},
	methods: {
		resizeSelect()
		{
			const resizingSelectContainer = document.querySelector(".calendar-sharing__event-slot-timezone_select_box");
			const resizingSelect = document.querySelector(".calendar-sharing__event-slot-timezone_select");
			const helperElement = document.querySelector(".calendar-sharing__event-slot-timezone_select_helper-element");
			const helperOption = helperElement.querySelector("option");
			const selectOption = document.querySelector(".calendar-sharing__event-slot-timezone_select option:checked");

			if (resizingSelect)
			{
				if (selectOption !== null)
				{
					helperOption.innerHTML = selectOption.innerText;
					let defaultWidth = helperElement.offsetWidth;
					resizingSelectContainer.style.setProperty("--dynamic-size", `${defaultWidth}px`);
				}

				resizingSelect.addEventListener('change', initResize);
			}

			function initResize(event) {
				helperOption.innerHTML = event.target.querySelector("option:checked").innerText;
				resize(helperElement.offsetWidth);
			}

			function resize(width) {
				resizingSelectContainer.style.setProperty("--dynamic-size", `${width}px`);
			}

			this.setMonthHeight();
		},
		setConfig(params)
		{
			if (params.weekHolidays)
			{
				this.config.weekHolidays = params.weekHolidays.map(weekDay => Util.getIndByWeekDay(weekDay));
			}
			if (params.yearHolidays)
			{
				this.config.yearHolidays = params.yearHolidays;
			}
			if (params.weekStart)
			{
				this.config.weekStart = Util.getIndByWeekDay(params.weekStart);
				this.loc.weekdays.push(...this.loc.weekdays.splice(0, this.config.weekStart));
			}

			const hourOffset = Util.getTimeZoneOffset(this.selectedTimezoneId) / 60;
			if (params.workTimeStart)
			{
				const workTimeStart = parseFloat(params.workTimeStart) - hourOffset;
				this.config.workTimeStartHours = workTimeStart - workTimeStart % 1;
				this.config.workTimeStartMinutes = (workTimeStart % 1) * 60;
			}
			if (params.workTimeEnd)
			{
				const workTimeEnd = parseFloat(params.workTimeEnd) - hourOffset;
				this.config.workTimeEndHours = workTimeEnd - workTimeEnd % 1;
				this.config.workTimeEndMinutes = (workTimeEnd % 1) * 60;
			}
		},
		getTimezonePrefix(timezoneOffset)
		{
			const offset = timezoneOffset * 1000 - this.timezoneOffsetUtc * (-60000);
			const date = new Date(this.nowTime.getTime() + offset);

			return DateTimeFormat.format(Util.getTimeFormatShort(), date.getTime() / 1000);
		},
		incrementTime()
		{
			this.nowTime = new Date();
		},
		onTimezoneSelect()
		{
			const selectedTimezone = this.timezoneList[this.selectedTimezoneId];
			this.currentTimezoneOffsetUtc = - (selectedTimezone.offset / 60);

			this.reCreateMonth();

			if (this.currentDayNumber)
			{
				const day = this.getDayByNumber(this.currentDayNumber);
				this.openEventSlotList(day);
			}

			this.$Bitrix.eventEmitter.emit('calendar:sharing:onTimezoneChange', { selectedTimezone });
		},
		async updateEventSlotsList()
		{
			const month = this.months[this.currentMonthIndex];
			const currentYear = month.year;
			const currentMonth = month.month + 1;
			const arrayKey = currentMonth + '.' + currentYear;

			this.accessibility[arrayKey] = await this.loadMonthAccessibility(currentYear, currentMonth);

			this.monthsSlots[this.currentMonthIndex] = this.calculateDateTimeSlots(currentYear, currentMonth - 1);

			this.reCreateMonth();
		},
		getDayByNumber(number)
		{
			const currentMonth = this.months[this.currentMonthIndex];
			const visibleDays = currentMonth.days.flat().filter(d => d.day > 0);
			return visibleDays[number - 1];
		},
		createMonth(year, month)
		{
			return {
				year: year,
				month: month,
				currentTimezoneOffset: this.currentTimezoneOffsetUtc,
				name: this.getMonthName(month),
				days: this.getMonthDays(year, month),
			};
		},
		reCreateMonth()
		{
			const year = this.months[this.currentMonthIndex].year;
			const month = this.months[this.currentMonthIndex].month;

			const monthSlots = this.monthsSlots[this.currentMonthIndex];
			const arrayKey = (month + 1) + '.' + year;
			this.monthsSlotsMap[arrayKey] = this.getDateTimeSlotsMap(monthSlots);

			this.months[this.currentMonthIndex] = this.createMonth(year, month);
		},
		getFirstMonthDay(year, month)
		{
			const firstDayIndex = new Date(year, month, 1).getDay();

			return firstDayIndex === 0 ? 7 : firstDayIndex;
		},
		getMonthName(month)
		{
			const date = new Date();
			const currentMonthDate = new Date(date.getFullYear(), month, 1);

			return DateTimeFormat.format('f', currentMonthDate.getTime() / 1000);
		},
		getMonthDays(year, month)
		{
			const days = [];
			const daysCount = new Date(year, month + 1, 0).getDate();
			const firstDayIndex = this.getFirstMonthDay(year, month);
			const accessibilityArrayKey = (month + 1) + '.' + year;

			for (let w = 1; w <= 6; w++)
			{
				const weekDays = [];
				for (let d = 1; d <= 7; d++)
				{
					const dayIndex = d + this.config.weekStart + (w - 1) * 7 - firstDayIndex;

					if (dayIndex <= 0)
					{
						weekDays.push({
							day: -1,
							unavailable: false,
							weekend: false,
						});
					}
					else if (dayIndex > daysCount)
					{
						weekDays.push({
							day: 0,
							unavailable: false,
							weekend: false,
						});
					}
					else
					{
						const newDay = new Date(year, month, dayIndex);
						const slots = this.monthsSlotsMap[accessibilityArrayKey][newDay.getDate()] ?? [];
						const unavailable = newDay < this.nowTime && newDay.getDate() < this.nowTime.getDate();
						const isWeekend = this.isHoliday(newDay);
						const hasFreeWindows = slots.filter(a => a.available).length > 0;

						weekDays.push({
							slots: slots,
							day: dayIndex,
							unavailable: unavailable,
							isWeekend: isWeekend,
							hasFreeWindows: hasFreeWindows,
						});
					}
				}

				if (w === 1 && weekDays[6].day === -1)
				{
					continue;
				}

				days.push(weekDays);
			}

			return days;
		},
		isHoliday(day): boolean
		{
			const dayMonthKey = day.getDate() + '.' + ('0' + (day.getMonth() + 1)).slice(-2);

			return (this.config.weekHolidays.includes(day.getDay()) || this.config.yearHolidays[dayMonthKey] !== undefined);
		},
		async createNextMonth()
		{
			const currentMonth = this.months[this.currentMonthIndex];
			const currentYear = currentMonth.year;
			const currentMonthIndex = currentMonth.month;

			const nextMonthIndex = (currentMonthIndex + 1) % 12;
			const nextYear = currentYear + Math.floor((currentMonthIndex + 1) / 12);
			const nextMonth = nextMonthIndex + 1;

			const arrayKey = nextMonth + '.' + nextYear;

			this.accessibility[arrayKey] = await this.loadMonthAccessibility(nextYear, nextMonth);

			const slots = this.calculateDateTimeSlots(nextYear, nextMonthIndex);
			this.monthsSlots.push(slots);

			this.monthsSlotsMap[arrayKey] = this.getDateTimeSlotsMap(slots);

			const month = this.createMonth(nextYear, nextMonthIndex);
			this.months.push(month);
		},
		async loadMonthAccessibility(year, month)
		{
			const firstMonthDay = new Date(year, month - 1, 1);
			const lastMonthDay = new Date(year, month, 0, 23, 59);

			const response = await BX.ajax.runAction('calendar.api.sharingajax.getUserAccessibility', {
				data: {
					userId: this.owner.id,
					timestampFrom: firstMonthDay.getTime(),
					timestampTo: lastMonthDay.getTime(),
				}
			});

			return response.data;
		},
		async handleNextMonthArrowClick()
		{
			if (this.isMonthAnimating)
			{
				return;
			}

			this.isMonthAnimating = true;
			this.$Bitrix.eventEmitter.emit('calendar:sharing:onBeforeOpenSlotList');

			if (this.currentMonthIndex === this.months.length - 1)
			{
				await this.createNextMonth();
			}

			this.startNextMonthAnimation();
		},
		handlePreviousMonthArrowClick()
		{
			if (this.isMonthAnimating || this.currentMonthIndex === 0)
			{
				return;
			}

			this.isMonthAnimating = true;
			this.$Bitrix.eventEmitter.emit('calendar:sharing:onBeforeOpenSlotList');

			this.startPreviousMonthAnimation();
		},
		startNextMonthAnimation()
		{
			this.displayedMonth = [0, 1];
		},
		startPreviousMonthAnimation()
		{
			this.displayedMonth = [-1, 0];
		},
		animateToNextMonth()
		{
			this.animateMonthSwitch('next');
		},
		animateToPreviousMonth()
		{
			this.animateMonthSwitch('previous');
		},
		animateMonthSwitch(direction)
		{
			let currentMonthElement, scrollTo, heightTo;

			if (direction === 'next')
			{
				currentMonthElement = this.DOM.monthsContainer.children[0];
				const nextMonthElement = this.DOM.monthsContainer.children[1];

				scrollTo = this.DOM.monthsContainer.scrollWidth - this.DOM.monthsContainer.offsetWidth;
				heightTo = this.getMonthHeight(nextMonthElement, this.currentMonthIndex + 1);
			}

			if (direction === 'previous')
			{
				const previousMonthElement = this.DOM.monthsContainer.children[0];
				currentMonthElement = this.DOM.monthsContainer.children[1];

				scrollTo = 0;
				heightTo = this.getMonthHeight(previousMonthElement, this.currentMonthIndex - 1);
			}

			new BX.easing({
				duration: 300,
				start: {
					scrollLeft: this.DOM.monthsContainer.scrollLeft,
					height: this.getMonthHeight(currentMonthElement, this.currentMonthIndex),
				},
				finish: {
					scrollLeft: scrollTo,
					height: heightTo,
				},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: (state) => {
					this.DOM.monthsContainer.scrollLeft = state.scrollLeft;
					this.DOM.monthsContainer.style.height = state.height + 'px';
				},
				complete: () => {
					if (direction === 'next' && this.currentMonthIndex < this.months.length - 1)
					{
						this.currentMonthIndex++;
					}

					if (direction === 'previous' && this.currentMonthIndex > 0)
					{
						this.currentMonthIndex--;
					}

					if (this.months[this.currentMonthIndex].currentTimezoneOffset !== this.currentTimezoneOffsetUtc)
					{
						this.reCreateMonth();
					}

					this.selectMonthDay();

					this.isMonthAnimating = false;
					this.displayedMonth = [0];
				}
			}).animate();
		},
		openEventSlotList(day)
		{
			this.currentDayNumber = day.day;
			const result = {
				slots: day.slots,
				day: day.day,
				month: this.months[this.currentMonthIndex].month,
				year: this.months[this.currentMonthIndex].year,
			}

			this.$Bitrix.eventEmitter.emit('calendar:sharing:doOpenEventSlotList', result);
		},
		calculateDateTimeSlots(year, month)
		{
			const result = [];
			const daysCount = new Date(year, month + 1, 0).getDate();
			const accessibilityArrayKey = (month + 1) + '.' + year;
			const nowTimestamp = this.nowTime.getTime();
			const browserSelectedTimezoneOffset = (Util.getTimeZoneOffset(this.selectedTimezoneId) - this.nowTime.getTimezoneOffset()) * 60000;
			const offset = this.getDateInSelectedTimezoneFromTimestampUTC(nowTimestamp) - nowTimestamp;

			for (let dayIndex = 1; dayIndex <= daysCount; dayIndex++)
			{
				const currentDate = new Date(year, month, dayIndex);

				const from = new Date(year, month, dayIndex, this.config.workTimeStartHours, this.config.workTimeStartMinutes);
				const to = new Date(year, month, dayIndex, this.config.workTimeEndHours, this.config.workTimeEndMinutes);

				const dayAccessibility = this.accessibility[accessibilityArrayKey].filter((event) => {
					return this.doIntervalsIntersect(
						parseInt(event.timestampFromUTC) * 1000,
						parseInt(event.timestampToUTC) * 1000,
						from.getTime(),
						to.getTime(),
					);
				});

				while (from.getTime() < to.getTime())
				{
					const slotStart = from.getTime();
					const slotEnd = slotStart + this.eventDurability;

					if (slotEnd > to.getTime())
					{
						break;
					}

					const slotAccessibility = dayAccessibility.filter((acc) => {
						return this.doIntervalsIntersect(
							parseInt(acc.timestampFromUTC) * 1000,
							parseInt(acc.timestampToUTC) * 1000,
							slotStart,
							slotEnd,
						);
					});

					const available = slotAccessibility.length === 0 && !this.isHoliday(currentDate) && slotStart > nowTimestamp;
					const timeFrom = new Date(slotStart + browserSelectedTimezoneOffset + offset);
					const timeTo = new Date(timeFrom.getTime() + (slotEnd - slotStart));
					result.push({ timeFrom, timeTo, available });

					from.setTime(from.getTime() + this.stepSize);
				}
			}

			return result;
		},
		doIntervalsIntersect(from1, to1, from2, to2)
		{
			const startsInside = from2 <= from1 && from1 < to2;
			const endsInside = from2 < to1 && to1 <= to2;
			const startsBeforeEndsAfter = from1 <= from2 && to1 >= to2;
			return startsInside || endsInside || startsBeforeEndsAfter;
		},
		getDateInSelectedTimezoneFromTimestampUTC(timestamp)
		{
			const selectedTimezone = this.selectedTimezoneId;
			return Util.getTimezoneDateFromTimestampUTC(timestamp, selectedTimezone);
		},
		getDateTimeSlotsMap(slotList)
		{
			let result = [];
			slotList.forEach((slot) => {
				const timezoneOffset = (this.currentTimezoneOffsetUtc - this.timezoneOffsetUtc) * (-60) * 1000;
				const currentSlot = {
					timeFrom: new Date(slot.timeFrom.getTime() + timezoneOffset),
					timeTo: new Date(slot.timeTo.getTime() + timezoneOffset),
					available: slot.available,
				};
				let dateIndex = currentSlot.timeFrom.getDate();

				if (result[dateIndex] === undefined)
				{
					result[dateIndex] = [];
				}


				if (slot.timeFrom.getMonth() === currentSlot.timeFrom.getMonth())
				{
					result[dateIndex].push(currentSlot);
				}
			});

			return result;
		},
		selectMonthDay()
		{
			const currentMonthDays = this.months[this.currentMonthIndex].days.flat().filter(day => day.day > 0);
			let dayToSelect = currentMonthDays.find(day => day.day === this.currentDayNumber);
			if (dayToSelect === undefined)
			{
				dayToSelect = currentMonthDays[currentMonthDays.length - 1];
			}
			this.currentDayNumber = dayToSelect.day;

			this.openEventSlotList(dayToSelect);
		},
		selectFirstAvailableDay()
		{
			let visibleDays = this.months[this.currentMonthIndex].days.flat();
			if (this.currentMonthIndex === 0)
			{
				const todayDay = new Date().getDate();
				visibleDays = visibleDays.filter(day => day.day >= todayDay).slice(0,14);
			}
			let availableDay = visibleDays.find(day => day.hasFreeWindows);
			if (availableDay === undefined)
			{
				availableDay = visibleDays[0];
			}

			this.openEventSlotList(availableDay);
		},
		setMonthHeight()
		{
			const currentMonth = document.querySelector('.calendar-sharing__month');
			currentMonth.style.height = this.getMonthHeight(currentMonth, this.currentMonthIndex) + 'px';
		},
		getMonthHeight(monthElement, monthIndex): number
		{
			const weekRows = monthElement.querySelector('.calendar-sharing__days-container').children;
			const weekHeight = weekRows[0].offsetHeight;

			if (this.months[monthIndex].days.flat()[35].day === 0)
			{
				return weekHeight * 6;
			}

			return weekHeight * 7;
		},
		setPageVisualSettings()
		{
			const htmlNode = document.querySelector('html');
			const bodyNode = document.querySelector('body');

			if (!Dom.hasClass(bodyNode, 'calendar-sharing--public-body'))
			{
				Dom.addClass(bodyNode, 'calendar-sharing--public-body');
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing--public-html'))
			{
				Dom.addClass(htmlNode, 'calendar-sharing--public-html');
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing--slots'))
			{
				Dom.addClass(htmlNode, 'calendar-sharing--slots');
			}
			if (Dom.hasClass(htmlNode, 'calendar-sharing--bg-green'))
			{
				Dom.removeClass(htmlNode, 'calendar-sharing--bg-green');
			}
			if (Dom.hasClass(htmlNode, 'calendar-sharing--bg-red'))
			{
				Dom.removeClass(htmlNode, 'calendar-sharing--bg-red');
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing--bg-gray') && this.isMobileBrowser)
			{
				Dom.addClass(htmlNode, 'calendar-sharing--bg-gray');
			}
			if (!Dom.hasClass(htmlNode, 'calendar-sharing--bg-blue') && !this.isMobileBrowser)
			{
				Dom.addClass(htmlNode, 'calendar-sharing--bg-blue');
			}
			if (Dom.hasClass(htmlNode, 'calendar-sharing-html-body-center'))
			{
				Dom.removeClass(htmlNode, 'calendar-sharing-html-body-center');
			}
			if (Dom.hasClass(bodyNode, 'calendar-sharing-html-body-center'))
			{
				Dom.removeClass(bodyNode, 'calendar-sharing-html-body-center');
			}

			if (Util.isMobileBrowser())
			{
				if (!Dom.hasClass(bodyNode, 'calendar-sharing--public-body-mobile'))
				{
					Dom.addClass(bodyNode, 'calendar-sharing--public-body-mobile');
				}
				if (!Dom.hasClass(htmlNode, 'calendar-sharing--public-html-mobile'))
				{
					Dom.addClass(htmlNode, 'calendar-sharing--public-html-mobile');
				}
			}
		},
	},
	template: `
		<div class="calendar-sharing-main__container">
			<div class="calendar-sharing_title">
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_TITLE') }}
			</div>
			<div class="calendar-sharing-month__container">
				<div class="calendar-sharing_month-title">
					<div class="calendar-sharing_mount-name">{{ months[currentMonthIndex].name }}, {{ months[currentMonthIndex].year }}</div>
					<div class="calendar-sharing_month_btn-box">
						<div
							class="calendar-sharing_previous-month-arrow"
							:class="{'--unavailable': !currentMonthIndex}"
							@click="handlePreviousMonthArrowClick"
						>
						</div>
						<div
							class="calendar-sharing_next-month-arrow"
							@click="handleNextMonthArrowClick"
						>
						</div>
					</div>
				</div>
			</div>
			<div class="calendar-sharing__months-container">
				<div class="calendar-sharing__month" v-for="i in displayedMonth">
					<div class="calendar-sharing__weekdays-container">
						<div class="calendar-sharing_weekday" v-for="weekday in loc.weekdays">{{ weekday }}</div>
					</div>
					<div class="calendar-sharing__days-container">
						<div class="calendar-sharing_week-line" v-for="w in months[currentMonthIndex + i].days">
							<div class="calendar-sharing__day-container" v-for="d in w">
								<div class="calendar-sharing_day --first-week" v-if="d.day === -1"></div>
								<div class="calendar-sharing_day --empty" v-else-if="d.day === 0"></div>
								<div class="calendar-sharing_day --unavailable" v-else-if="d.unavailable">{{ d.day }}</div>
								<div
									class="calendar-sharing_day"
									:class="
								{
									'--weekend': d.isWeekend,
									'--enable-booking': d.hasFreeWindows,
									'--active': d.day === currentDayNumber,
								}"
									@click="openEventSlotList(d)"
									v-else
								>
									{{ d.day }}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="calendar-sharing-main__bottom-container">
			<div class="calendar-sharing__event-slot-timezone">
				<div class="calendar-sharing__event-slot-timezone_name">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_TIMEZONE') }}:</div>
				<div class="calendar-sharing__event-slot-timezone_select_box">
					<select class="calendar-sharing__event-slot-timezone_select"
							v-model="selectedTimezoneId"
							@change="onTimezoneSelect"
					>
						<option v-for="timezone in timezoneList" :value="timezone.timezone_id">
							{{ this.getTimezonePrefix(timezone.offset) }} - {{ timezone.timezone_id }}
						</option>
					</select>
					<select class="calendar-sharing__event-slot-timezone_select_helper-element" aria-hidden="true">
						<option value="value" selected>value</option>
					</select>
				</div>
			</div>
		</div>
	`
};
