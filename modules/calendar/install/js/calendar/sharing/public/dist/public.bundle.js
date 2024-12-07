this.BX = this.BX || {};
this.BX.Calendar = this.BX.Calendar || {};
(function (exports,ui_vue3,main_date,calendar_util,main_core,ui_confetti) {
	'use strict';

	const DateSelector = {
	  props: {
	    owner: Object,
	    calendarSettings: Object,
	    userAccessibility: Object,
	    timezoneList: Object
	  },
	  name: 'DateSelector',

	  data() {
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
	        weekdays: calendar_util.Util.getWeekdaysLoc()
	      },
	      config: {
	        weekHolidays: [6, 0],
	        weekStart: 1
	      }
	    };
	  },

	  created() {
	    this.setConfig(this.calendarSettings);
	    this.isMobileBrowser = calendar_util.Util.isMobileBrowser();
	    setInterval(this.incrementTime, 15000);
	    const slots = this.calculateDateTimeSlots(this.nowTime.getFullYear(), this.nowTime.getMonth());
	    this.monthsSlots.push(slots);
	    const slotsMap = this.getDateTimeSlotsMap(slots);
	    const arrayKey = this.nowTime.getMonth() + 1 + '.' + this.nowTime.getFullYear();
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
	    });
	    this.setPageVisualSettings();
	  },

	  mounted() {
	    this.resizeSelect();
	    this.selectFirstAvailableDay();
	    this.setMonthHeight();
	    this.DOM = {
	      monthsContainer: document.querySelector('.calendar-sharing__months-container')
	    };
	  },

	  updated() {
	    if (this.displayedMonth.length === 1) {
	      return;
	    }

	    if (this.displayedMonth[0] === -1) {
	      this.DOM.monthsContainer.scrollLeft = this.DOM.monthsContainer.scrollWidth - this.DOM.monthsContainer.offsetWidth;
	      this.animateToPreviousMonth();
	    }

	    if (this.displayedMonth[1] === 1) {
	      this.animateToNextMonth();
	    }
	  },

	  methods: {
	    resizeSelect() {
	      const resizingSelectContainer = document.querySelector(".calendar-sharing__event-slot-timezone_select_box");
	      const resizingSelect = document.querySelector(".calendar-sharing__event-slot-timezone_select");
	      const helperElement = document.querySelector(".calendar-sharing__event-slot-timezone_select_helper-element");
	      const helperOption = helperElement.querySelector("option");
	      const selectOption = document.querySelector(".calendar-sharing__event-slot-timezone_select option:checked");

	      if (resizingSelect) {
	        if (selectOption !== null) {
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

	    setConfig(params) {
	      if (params.weekHolidays) {
	        this.config.weekHolidays = params.weekHolidays.map(weekDay => calendar_util.Util.getIndByWeekDay(weekDay));
	      }

	      if (params.yearHolidays) {
	        this.config.yearHolidays = params.yearHolidays;
	      }

	      if (params.weekStart) {
	        this.config.weekStart = calendar_util.Util.getIndByWeekDay(params.weekStart);
	        this.loc.weekdays.push(...this.loc.weekdays.splice(0, this.config.weekStart));
	      }

	      const hourOffset = calendar_util.Util.getTimeZoneOffset(this.selectedTimezoneId) / 60;

	      if (params.workTimeStart) {
	        const workTimeStart = parseFloat(params.workTimeStart) - hourOffset;
	        this.config.workTimeStartHours = workTimeStart - workTimeStart % 1;
	        this.config.workTimeStartMinutes = workTimeStart % 1 * 60;
	      }

	      if (params.workTimeEnd) {
	        const workTimeEnd = parseFloat(params.workTimeEnd) - hourOffset;
	        this.config.workTimeEndHours = workTimeEnd - workTimeEnd % 1;
	        this.config.workTimeEndMinutes = workTimeEnd % 1 * 60;
	      }
	    },

	    getTimezonePrefix(timezoneOffset) {
	      const offset = timezoneOffset * 1000 - this.timezoneOffsetUtc * -60000;
	      const date = new Date(this.nowTime.getTime() + offset);
	      return main_date.DateTimeFormat.format(calendar_util.Util.getTimeFormatShort(), date.getTime() / 1000);
	    },

	    incrementTime() {
	      this.nowTime = new Date();
	    },

	    onTimezoneSelect() {
	      const selectedTimezone = this.timezoneList[this.selectedTimezoneId];
	      this.currentTimezoneOffsetUtc = -(selectedTimezone.offset / 60);
	      this.reCreateMonth();

	      if (this.currentDayNumber) {
	        const day = this.getDayByNumber(this.currentDayNumber);
	        this.openEventSlotList(day);
	      }

	      this.$Bitrix.eventEmitter.emit('calendar:sharing:onTimezoneChange', {
	        selectedTimezone
	      });
	    },

	    async updateEventSlotsList() {
	      const month = this.months[this.currentMonthIndex];
	      const currentYear = month.year;
	      const currentMonth = month.month + 1;
	      const arrayKey = currentMonth + '.' + currentYear;
	      this.accessibility[arrayKey] = await this.loadMonthAccessibility(currentYear, currentMonth);
	      this.monthsSlots[this.currentMonthIndex] = this.calculateDateTimeSlots(currentYear, currentMonth - 1);
	      this.reCreateMonth();
	    },

	    getDayByNumber(number) {
	      const currentMonth = this.months[this.currentMonthIndex];
	      const visibleDays = currentMonth.days.flat().filter(d => d.day > 0);
	      return visibleDays[number - 1];
	    },

	    createMonth(year, month) {
	      return {
	        year: year,
	        month: month,
	        currentTimezoneOffset: this.currentTimezoneOffsetUtc,
	        name: this.getMonthName(month),
	        days: this.getMonthDays(year, month)
	      };
	    },

	    reCreateMonth() {
	      const year = this.months[this.currentMonthIndex].year;
	      const month = this.months[this.currentMonthIndex].month;
	      const monthSlots = this.monthsSlots[this.currentMonthIndex];
	      const arrayKey = month + 1 + '.' + year;
	      this.monthsSlotsMap[arrayKey] = this.getDateTimeSlotsMap(monthSlots);
	      this.months[this.currentMonthIndex] = this.createMonth(year, month);
	    },

	    getFirstMonthDay(year, month) {
	      const firstDayIndex = new Date(year, month, 1).getDay();
	      return firstDayIndex === 0 ? 7 : firstDayIndex;
	    },

	    getMonthName(month) {
	      const date = new Date();
	      const currentMonthDate = new Date(date.getFullYear(), month, 1);
	      return main_date.DateTimeFormat.format('f', currentMonthDate.getTime() / 1000);
	    },

	    getMonthDays(year, month) {
	      const days = [];
	      const daysCount = new Date(year, month + 1, 0).getDate();
	      const firstDayIndex = this.getFirstMonthDay(year, month);
	      const accessibilityArrayKey = month + 1 + '.' + year;

	      for (let w = 1; w <= 6; w++) {
	        const weekDays = [];

	        for (let d = 1; d <= 7; d++) {
	          const dayIndex = d + this.config.weekStart + (w - 1) * 7 - firstDayIndex;

	          if (dayIndex <= 0) {
	            weekDays.push({
	              day: -1,
	              unavailable: false,
	              weekend: false
	            });
	          } else if (dayIndex > daysCount) {
	            weekDays.push({
	              day: 0,
	              unavailable: false,
	              weekend: false
	            });
	          } else {
	            var _this$monthsSlotsMap$;

	            const newDay = new Date(year, month, dayIndex);
	            const slots = (_this$monthsSlotsMap$ = this.monthsSlotsMap[accessibilityArrayKey][newDay.getDate()]) != null ? _this$monthsSlotsMap$ : [];
	            const unavailable = newDay < this.nowTime && newDay.getDate() < this.nowTime.getDate();
	            const isWeekend = this.isHoliday(newDay);
	            const hasFreeWindows = slots.filter(a => a.available).length > 0;
	            weekDays.push({
	              slots: slots,
	              day: dayIndex,
	              unavailable: unavailable,
	              isWeekend: isWeekend,
	              hasFreeWindows: hasFreeWindows
	            });
	          }
	        }

	        if (w === 1 && weekDays[6].day === -1) {
	          continue;
	        }

	        days.push(weekDays);
	      }

	      return days;
	    },

	    isHoliday(day) {
	      const dayMonthKey = day.getDate() + '.' + ('0' + (day.getMonth() + 1)).slice(-2);
	      return this.config.weekHolidays.includes(day.getDay()) || this.config.yearHolidays[dayMonthKey] !== undefined;
	    },

	    async createNextMonth() {
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

	    async loadMonthAccessibility(year, month) {
	      const firstMonthDay = new Date(year, month - 1, 1);
	      const lastMonthDay = new Date(year, month, 0, 23, 59);
	      const response = await BX.ajax.runAction('calendar.api.sharingajax.getUserAccessibility', {
	        data: {
	          userId: this.owner.id,
	          timestampFrom: firstMonthDay.getTime(),
	          timestampTo: lastMonthDay.getTime()
	        }
	      });
	      return response.data;
	    },

	    async handleNextMonthArrowClick() {
	      if (this.isMonthAnimating) {
	        return;
	      }

	      this.isMonthAnimating = true;
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:onBeforeOpenSlotList');

	      if (this.currentMonthIndex === this.months.length - 1) {
	        await this.createNextMonth();
	      }

	      this.startNextMonthAnimation();
	    },

	    handlePreviousMonthArrowClick() {
	      if (this.isMonthAnimating || this.currentMonthIndex === 0) {
	        return;
	      }

	      this.isMonthAnimating = true;
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:onBeforeOpenSlotList');
	      this.startPreviousMonthAnimation();
	    },

	    startNextMonthAnimation() {
	      this.displayedMonth = [0, 1];
	    },

	    startPreviousMonthAnimation() {
	      this.displayedMonth = [-1, 0];
	    },

	    animateToNextMonth() {
	      this.animateMonthSwitch('next');
	    },

	    animateToPreviousMonth() {
	      this.animateMonthSwitch('previous');
	    },

	    animateMonthSwitch(direction) {
	      let currentMonthElement, scrollTo, heightTo;

	      if (direction === 'next') {
	        currentMonthElement = this.DOM.monthsContainer.children[0];
	        const nextMonthElement = this.DOM.monthsContainer.children[1];
	        scrollTo = this.DOM.monthsContainer.scrollWidth - this.DOM.monthsContainer.offsetWidth;
	        heightTo = this.getMonthHeight(nextMonthElement, this.currentMonthIndex + 1);
	      }

	      if (direction === 'previous') {
	        const previousMonthElement = this.DOM.monthsContainer.children[0];
	        currentMonthElement = this.DOM.monthsContainer.children[1];
	        scrollTo = 0;
	        heightTo = this.getMonthHeight(previousMonthElement, this.currentMonthIndex - 1);
	      }

	      new BX.easing({
	        duration: 300,
	        start: {
	          scrollLeft: this.DOM.monthsContainer.scrollLeft,
	          height: this.getMonthHeight(currentMonthElement, this.currentMonthIndex)
	        },
	        finish: {
	          scrollLeft: scrollTo,
	          height: heightTo
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	        step: state => {
	          this.DOM.monthsContainer.scrollLeft = state.scrollLeft;
	          this.DOM.monthsContainer.style.height = state.height + 'px';
	        },
	        complete: () => {
	          if (direction === 'next' && this.currentMonthIndex < this.months.length - 1) {
	            this.currentMonthIndex++;
	          }

	          if (direction === 'previous' && this.currentMonthIndex > 0) {
	            this.currentMonthIndex--;
	          }

	          if (this.months[this.currentMonthIndex].currentTimezoneOffset !== this.currentTimezoneOffsetUtc) {
	            this.reCreateMonth();
	          }

	          this.selectMonthDay();
	          this.isMonthAnimating = false;
	          this.displayedMonth = [0];
	        }
	      }).animate();
	    },

	    openEventSlotList(day) {
	      this.currentDayNumber = day.day;
	      const result = {
	        slots: day.slots,
	        day: day.day,
	        month: this.months[this.currentMonthIndex].month,
	        year: this.months[this.currentMonthIndex].year
	      };
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:doOpenEventSlotList', result);
	    },

	    calculateDateTimeSlots(year, month) {
	      const result = [];
	      const daysCount = new Date(year, month + 1, 0).getDate();
	      const accessibilityArrayKey = month + 1 + '.' + year;
	      const nowTimestamp = this.nowTime.getTime();
	      const browserSelectedTimezoneOffset = (calendar_util.Util.getTimeZoneOffset(this.selectedTimezoneId) - this.nowTime.getTimezoneOffset()) * 60000;
	      const offset = this.getDateInSelectedTimezoneFromTimestampUTC(nowTimestamp) - nowTimestamp;

	      for (let dayIndex = 1; dayIndex <= daysCount; dayIndex++) {
	        const currentDate = new Date(year, month, dayIndex);
	        const from = new Date(year, month, dayIndex, this.config.workTimeStartHours, this.config.workTimeStartMinutes);
	        const to = new Date(year, month, dayIndex, this.config.workTimeEndHours, this.config.workTimeEndMinutes);
	        const dayAccessibility = this.accessibility[accessibilityArrayKey].filter(event => {
	          return this.doIntervalsIntersect(parseInt(event.timestampFromUTC) * 1000, parseInt(event.timestampToUTC) * 1000, from.getTime(), to.getTime());
	        });

	        while (from.getTime() < to.getTime()) {
	          const slotStart = from.getTime();
	          const slotEnd = slotStart + this.eventDurability;

	          if (slotEnd > to.getTime()) {
	            break;
	          }

	          const slotAccessibility = dayAccessibility.filter(acc => {
	            return this.doIntervalsIntersect(parseInt(acc.timestampFromUTC) * 1000, parseInt(acc.timestampToUTC) * 1000, slotStart, slotEnd);
	          });
	          const available = slotAccessibility.length === 0 && !this.isHoliday(currentDate) && slotStart > nowTimestamp;
	          const timeFrom = new Date(slotStart + browserSelectedTimezoneOffset + offset);
	          const timeTo = new Date(timeFrom.getTime() + (slotEnd - slotStart));
	          result.push({
	            timeFrom,
	            timeTo,
	            available
	          });
	          from.setTime(from.getTime() + this.stepSize);
	        }
	      }

	      return result;
	    },

	    doIntervalsIntersect(from1, to1, from2, to2) {
	      const startsInside = from2 <= from1 && from1 < to2;
	      const endsInside = from2 < to1 && to1 <= to2;
	      const startsBeforeEndsAfter = from1 <= from2 && to1 >= to2;
	      return startsInside || endsInside || startsBeforeEndsAfter;
	    },

	    getDateInSelectedTimezoneFromTimestampUTC(timestamp) {
	      const selectedTimezone = this.selectedTimezoneId;
	      return calendar_util.Util.getTimezoneDateFromTimestampUTC(timestamp, selectedTimezone);
	    },

	    getDateTimeSlotsMap(slotList) {
	      let result = [];
	      slotList.forEach(slot => {
	        const timezoneOffset = (this.currentTimezoneOffsetUtc - this.timezoneOffsetUtc) * -60 * 1000;
	        const currentSlot = {
	          timeFrom: new Date(slot.timeFrom.getTime() + timezoneOffset),
	          timeTo: new Date(slot.timeTo.getTime() + timezoneOffset),
	          available: slot.available
	        };
	        let dateIndex = currentSlot.timeFrom.getDate();

	        if (result[dateIndex] === undefined) {
	          result[dateIndex] = [];
	        }

	        if (slot.timeFrom.getMonth() === currentSlot.timeFrom.getMonth()) {
	          result[dateIndex].push(currentSlot);
	        }
	      });
	      return result;
	    },

	    selectMonthDay() {
	      const currentMonthDays = this.months[this.currentMonthIndex].days.flat().filter(day => day.day > 0);
	      let dayToSelect = currentMonthDays.find(day => day.day === this.currentDayNumber);

	      if (dayToSelect === undefined) {
	        dayToSelect = currentMonthDays[currentMonthDays.length - 1];
	      }

	      this.currentDayNumber = dayToSelect.day;
	      this.openEventSlotList(dayToSelect);
	    },

	    selectFirstAvailableDay() {
	      let visibleDays = this.months[this.currentMonthIndex].days.flat();

	      if (this.currentMonthIndex === 0) {
	        const todayDay = new Date().getDate();
	        visibleDays = visibleDays.filter(day => day.day >= todayDay).slice(0, 14);
	      }

	      let availableDay = visibleDays.find(day => day.hasFreeWindows);

	      if (availableDay === undefined) {
	        availableDay = visibleDays[0];
	      }

	      this.openEventSlotList(availableDay);
	    },

	    setMonthHeight() {
	      const currentMonth = document.querySelector('.calendar-sharing__month');
	      currentMonth.style.height = this.getMonthHeight(currentMonth, this.currentMonthIndex) + 'px';
	    },

	    getMonthHeight(monthElement, monthIndex) {
	      const weekRows = monthElement.querySelector('.calendar-sharing__days-container').children;
	      const weekHeight = weekRows[0].offsetHeight;

	      if (this.months[monthIndex].days.flat()[35].day === 0) {
	        return weekHeight * 6;
	      }

	      return weekHeight * 7;
	    },

	    setPageVisualSettings() {
	      const htmlNode = document.querySelector('html');
	      const bodyNode = document.querySelector('body');

	      if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing--public-body')) {
	        main_core.Dom.addClass(bodyNode, 'calendar-sharing--public-body');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--public-html')) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--public-html');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--slots')) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--slots');
	      }

	      if (main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-green')) {
	        main_core.Dom.removeClass(htmlNode, 'calendar-sharing--bg-green');
	      }

	      if (main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-red')) {
	        main_core.Dom.removeClass(htmlNode, 'calendar-sharing--bg-red');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-gray') && this.isMobileBrowser) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-gray');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-blue') && !this.isMobileBrowser) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-blue');
	      }

	      if (main_core.Dom.hasClass(htmlNode, 'calendar-sharing-html-body-center')) {
	        main_core.Dom.removeClass(htmlNode, 'calendar-sharing-html-body-center');
	      }

	      if (main_core.Dom.hasClass(bodyNode, 'calendar-sharing-html-body-center')) {
	        main_core.Dom.removeClass(bodyNode, 'calendar-sharing-html-body-center');
	      }

	      if (calendar_util.Util.isMobileBrowser()) {
	        if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing--public-body-mobile')) {
	          main_core.Dom.addClass(bodyNode, 'calendar-sharing--public-body-mobile');
	        }

	        if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--public-html-mobile')) {
	          main_core.Dom.addClass(htmlNode, 'calendar-sharing--public-html-mobile');
	        }
	      }
	    }

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

	const EventSlotItem = {
	  props: ['item', 'key'],

	  data() {
	    return {};
	  },

	  computed: {
	    timeInput() {
	      return calendar_util.Util.formatTimeInterval(this.item.timeFrom, this.item.timeTo);
	    }

	  },
	  methods: {
	    handleSetEventButtonClick() {
	      this.$emit('handleSetEventButtonClick', {
	        timeFrom: this.item.timeFrom,
	        timeTo: this.item.timeTo
	      });
	    }

	  },
	  template: `
		<div class="calendar-sharing-event-slot-item" :class="{'calendar-sharing-event-slot-item-hidden': !item.available}">
			<div class="calendar-sharing-event-slot-item-time">
				{{ timeInput }}
			</div>
			<button
				class="ui-btn ui-btn-success ui-btn-xs ui-btn-round"
				@click="handleSetEventButtonClick"
			>
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_CREATE_MEETING') }}
			</button>
		</div>
	`
	};

	const Timer = {
	  template: `
		<div class="calendar-sharing__timer_box">
			<div class="calendar-sharing__time_count-box">
				<div class="calendar-sharing__time_count-item">18</div>
				<div class="calendar-sharing__time_count-item">19</div>
				<div class="calendar-sharing__time_count-item">20</div>
			</div>
			<div class="calendar-sharing__timer_title">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_TIMER_TITLE') }}</div>
			<div class="calendar-sharing__timer_desc">{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_TIMER_DESC') }}</div>
		</div>
	`
	};

	const EventSlotList = {
	  name: 'EventSlotList',
	  props: {
	    timezoneList: Object
	  },
	  components: {
	    EventSlotItem,
	    Timer
	  },

	  data() {
	    return {
	      itemList: [],
	      isItemSelected: false,
	      selectedTimezone: null,
	      nowTime: new Date(),
	      currentTimezoneName: Intl.DateTimeFormat().resolvedOptions().timeZone
	    };
	  },

	  created() {
	    this.selectCurrentTimezone();
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:onTimezoneChange', event => {
	      this.updateTimezoneParams(event);
	    });
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:doOpenEventSlotList', event => {
	      this.openEventSlotList(event);
	    });
	  },

	  mounted() {
	    this.DOM = {
	      slotContainer: document.querySelector('.calendar-sharing-event-slot-container')
	    };
	  },

	  methods: {
	    selectCurrentTimezone() {
	      for (let [key, timezone] of Object.entries(this.timezoneList)) {
	        if (key === this.currentTimezoneName) {
	          this.selectedTimezone = timezone;
	          break;
	        }
	      }
	    },

	    openEventSlotList(event) {
	      const data = event.getData();
	      this.itemList = data.slots;
	      this.isItemSelected = true;
	    },

	    updateTimezoneParams(event) {
	      this.selectedTimezone = event.data.selectedTimezone;
	    },

	    handleSetEventButtonClick(event) {
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:onOpenEventAddForm');
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:changeEventData', {
	        timeFrom: event.timeFrom,
	        timeTo: event.timeTo,
	        timezone: this.selectedTimezone
	      });
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {
	        type: 'addForm'
	      });
	    }

	  },
	  template: `
		<div class="calendar-sharing-event-slot-container" v-show="isItemSelected">
			<EventSlotItem
				v-for="(item, index) in itemList"
				:key="index"
				:item="item"
				:index="index + 1"
				@handleSetEventButtonClick="handleSetEventButtonClick"
			/>
			<div class="calendar-sharing-event-slot-empty" :class="{'calendar-sharing-event-slot-item-hidden': itemList.filter(a => a.available).length}">
				<Timer/>
			</div>
		</div>
	`
	};

	const Header = {
	  template: `
		<div class="calendar-sharing_header">
			<slot/>
		</div>
	`
	};

	const StartInfo = {
	  props: {
	    event: {
	      type: Object,
	      required: true
	    },
	    showClockIcon: Boolean
	  },

	  data() {
	    return {
	      loc: {
	        today: main_core.Loc.getMessage('CALENDAR_SHARING_TODAY'),
	        tomorrow: main_core.Loc.getMessage('CALENDAR_SHARING_TOMORROW')
	      }
	    };
	  },

	  methods: {
	    getEventWeekDayShort() {
	      return main_date.DateTimeFormat.format('D', this.event.timeFrom.getTime() / 1000).toLowerCase();
	    },

	    getEventMonthDay() {
	      return this.event.timeFrom.getDate();
	    },

	    getEventDate() {
	      let dayPhrase = '';
	      const dateFormat = calendar_util.Util.getDayMonthFormat();
	      const today = new Date();
	      const eventDay = new Date(this.event.timeFrom.getFullYear(), this.event.timeFrom.getMonth(), this.event.timeFrom.getDate());

	      if (today.getTime() > eventDay.getTime() && today.getTime() < eventDay.getTime() + 86000000) {
	        dayPhrase = this.loc.today;
	      } else if (today.getTime() < eventDay.getTime() && today.getTime() > eventDay.getTime() - 86000000) {
	        dayPhrase = this.loc.tomorrow;
	      } else {
	        dayPhrase = main_date.DateTimeFormat.format('l', this.event.timeFrom.getTime() / 1000).toLowerCase();
	      }

	      return main_date.DateTimeFormat.format(dateFormat, this.event.timeFrom.getTime() / 1000) + ', ' + dayPhrase;
	    },

	    getEventTime() {
	      return calendar_util.Util.formatTimeInterval(this.event.timeFrom, this.event.timeTo);
	    },

	    getEventTimezone() {
	      return calendar_util.Util.getFormattedTimezone(this.event.timezone.timezone_id);
	    },

	    getEventName() {
	      return BX.util.htmlspecialchars(this.event.name);
	    }

	  },
	  template: `
		<div
			class="calendar-sharing-event-start__info_container" 
			:class="{'calendar-sharing--bg-gray': !this.event.name}"
		>
			<div class="calendar-sharing-event-start__info-icon" :class="{'--xl': this.event.name}">
				<div class="calendar-sharing-event-start__info-icon_status" v-if="showClockIcon"></div>
				<div class="calendar-sharing-event-start__info-icon_day" :class="{'--xl': this.event.name}">
					{{ getEventWeekDayShort() }}
				</div>
				<div class="calendar-sharing-event-start__info-icon_date" :class="{'--xl': this.event.name}">
					{{ getEventMonthDay() }}
				</div>
			</div>
			<div class="calendar-sharing-event-start__info_datetime">
				<div class="calendar-sharing-event-start__event-name" v-if="this.event.name">
					{{ getEventName() }}
				</div>
				<div class="calendar-sharing-event-start__info_date">
					{{ getEventDate() }}
				</div>
				<div class="calendar-sharing-event-start__info_time_box">
					<div class="calendar-sharing-event-start__info_time">
						{{ getEventTime() }}
					</div>
				</div>
				<div class="calendar-sharing-event-start__info_timezone">
					{{ getEventTimezone() }}
				</div>
			</div>
		</div>
	`
	};

	const HeaderTitle = {
	  props: {
	    hasBackButton: {
	      type: Boolean,
	      default: false
	    },
	    backButtonCallback: {
	      type: Function,
	      default: () => {}
	    },
	    text: {
	      type: String,
	      required: true
	    }
	  },
	  template: `
		<div class="calendar-sharing-header-title_container" :class="{'--center': !hasBackButton}">
			<div class="calendar-sharing-header-title_icon"></div>
			<div
				v-show="hasBackButton"
				@click="backButtonCallback"
				class="calendar-sharing-header-title_back-button"
			>
			</div>
			<div class="calendar-sharing-header-title_text">{{text}}</div>
		</div>
	`
	};

	const SharingLoader = {
	  template: `
		<div class="calendar-sharing__loader_box">
			<svg class="calendar-sharing_circular" viewBox="25 25 50 50">
				<circle class="calendar-sharing_path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
			</svg>
		</div>
	`
	};

	const AddForm = {
	  components: {
	    Header,
	    HeaderTitle,
	    StartInfo,
	    SharingLoader
	  },
	  name: 'EventAddForm',
	  props: {
	    event: {
	      type: Object,
	      required: true
	    },
	    owner: {
	      type: Object,
	      required: true
	    },
	    phoneFeatureEnabled: {
	      type: Boolean,
	      required: true
	    },
	    sharingUser: Object,
	    lastEventName: String,
	    userLinkHash: String
	  },

	  data() {
	    return {
	      eventName: this.lastEventName,
	      authorName: this.sharingUser.userName,
	      contactData: this.sharingUser.personalMailbox || this.sharingUser.personalPhone,
	      isEmptyContactName: false,
	      contactDataError: false,
	      isEmptyContactData: false,
	      saveButton: {
	        text: main_core.Loc.getMessage('CALENDAR_SHARING_SEND_REQUEST'),
	        disabled: false
	      },
	      validated: true,
	      loadingProcess: false
	    };
	  },

	  mounted() {
	    this.DOM = {
	      inputContact: document.getElementById('calendar-sharing-event-add-form_input-contact'),
	      calendarContainer: document.querySelector('.calendar-sharing__calendar'),
	      addFormElement: document.querySelector('.calendar-sharing__add-form').firstElementChild
	    };
	  },

	  updated() {
	    if (this.DOM.addFormElement.offsetHeight > 0) {
	      this.DOM.calendarContainer.style.height = this.DOM.addFormElement.offsetHeight + 'px';
	    }
	  },

	  methods: {
	    async handleSaveEventButton() {
	      this.fillEventNameIfEmpty();
	      this.clearContactDataError();
	      this.clearContactNameError();

	      if (!this.validateData()) {
	        this.validated = false;
	        return;
	      }

	      this.validated = true;
	      this.disableSaveButton();
	      this.loadingProcess = true;
	      const isSuccessful = await this.saveEvent();
	      this.enableSaveButton();
	      this.loadingProcess = false;

	      if (!isSuccessful && !this.validated) {
	        return;
	      }

	      this.$Bitrix.eventEmitter.emit('calendar:sharing:onEventAdd');
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:setEventViewError', {
	        viewFormError: !isSuccessful
	      });
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:updateAddFormDefaultParams', {
	        userName: this.authorName,
	        contactData: this.contactData,
	        eventName: this.eventName
	      });
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {
	        type: 'viewForm'
	      });
	    },

	    clearContactNameError() {
	      this.isEmptyContactName = false;
	    },

	    clearContactDataError() {
	      this.contactDataError = false;
	      this.isEmptyContactData = false;
	    },

	    validateData() {
	      if (this.authorName.length === 0) {
	        this.isEmptyContactName = true;
	      }

	      if (this.contactData.length === 0) {
	        this.isEmptyContactData = true;
	      }

	      if (!this.isEmptyContactData) {
	        this.contactDataError = !this.validatePhone() && !this.validateEmail();
	      }

	      return !this.isEmptyContactName && !this.isEmptyContactData && !this.contactDataError;
	    },

	    async saveEvent() {
	      let response = null;
	      this.loadingProcess = true;

	      try {
	        response = await BX.ajax.runAction('calendar.api.sharingajax.saveEvent', {
	          data: {
	            ownerCreated: this.sharingUser.ownerCreated,
	            ownerId: this.owner.id,
	            eventName: this.eventName,
	            userName: this.authorName,
	            userContact: this.contactData,
	            dateFrom: this.parseDate(this.event.timeFrom),
	            dateTo: this.parseDate(this.event.timeTo),
	            timezone: this.event.timezone.timezone_id,
	            userLinkHash: this.userLinkHash
	          }
	        });
	      } catch (e) {
	        response = e;
	      }

	      if (response.errors.length === 0) {
	        this.$Bitrix.eventEmitter.emit('calendar:sharing:onSetEventData', {
	          eventId: response.data.eventId,
	          eventLinkId: response.data.eventLinkId,
	          eventLinkHash: response.data.eventLinkHash,
	          eventLinkShortUrl: response.data.eventLinkShortUrl,
	          eventName: response.data.eventName
	        });
	        return true;
	      } else {
	        this.$Bitrix.eventEmitter.emit('calendar:sharing:onSetEventData', {
	          eventName: this.eventName
	        });
	      }

	      if (response.data.contactDataError === true) {
	        this.contactDataError = true;
	        this.validated = false;
	      }

	      if (response.data.isEmptyContactName === true) {
	        this.isEmptyContactName = true;
	        this.validated = false;
	      }

	      return false;
	    },

	    disableSaveButton() {
	      this.saveButton.text = this.$Bitrix.Loc.getMessage('CALENDAR_SHARING_SEND_REQUEST_PROCESSING');
	      this.saveButton.disabled = true;
	    },

	    enableSaveButton() {
	      this.saveButton.text = this.$Bitrix.Loc.getMessage('CALENDAR_SHARING_SEND_REQUEST');
	      this.saveButton.disabled = false;
	    },

	    parseDate(date) {
	      const dateInFormat = main_date.DateTimeFormat.format(calendar_util.Util.getDateFormat(), date.getTime() / 1000);
	      const timeInFormat = main_date.DateTimeFormat.format(calendar_util.Util.getTimeFormat(), date.getTime() / 1000);
	      return dateInFormat + ' ' + timeInFormat;
	    },

	    returnToDateSelector() {
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {
	        type: 'calendar'
	      });
	    },

	    fillEventNameIfEmpty() {
	      if (this.eventName.length === 0) {
	        this.eventName = main_core.Loc.getMessage('CALENDAR_SHARING_NEW_EVENT');
	      }

	      return true;
	    },

	    validatePhone() {
	      let isValidated = false;

	      if (this.phoneFeatureEnabled) {
	        const phone = this.contactData.replace(/[()\s\-]+/g, '');
	        const match = phone.match(/(^\+?\d{4,25}$)/i);
	        isValidated = (match == null ? void 0 : match[0]) === phone;
	      }

	      return isValidated;
	    },

	    validateEmail() {
	      const match = this.contactData.match(/(^[^@]+@.+$)/i);
	      return (match == null ? void 0 : match[0]) === this.contactData;
	    },

	    getContactDataPlaceholder() {
	      let messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_PHONE_FEATURE_DISABLED';

	      if (this.phoneFeatureEnabled) {
	        messageCode = 'CALENDAR_SHARING_AUTHOR_CONTACT_DATA_PLACEHOLDER_PHONE_FEATURE_ENABLED';
	      }

	      return this.$Bitrix.Loc.getMessage(messageCode);
	    },

	    onPhoneInput() {
	      this.clearContactDataError();

	      if (!this.isPhoneTypeInput()) {
	        return;
	      }

	      const textBeforeCursor = this.getTextBeforeCursor(this.DOM.inputContact);
	      this.contactData = this.formatPhone(this.contactData);
	      this.DOM.inputContact.value = this.contactData;
	      this.setCursorToFormattedPosition(this.DOM.inputContact, textBeforeCursor);
	    },

	    getTextBeforeCursor(input) {
	      const selectionStart = input.selectionStart;
	      return input.value.slice(0, selectionStart);
	    },

	    setCursorToFormattedPosition(input, textBeforeCursor) {
	      const firstPart = this.getTextEscapedForRegex(textBeforeCursor.slice(0, -1));
	      const lastCharacter = this.getTextEscapedForRegex(textBeforeCursor.slice(-1));
	      const match = input.value.match(`${firstPart}.*?${lastCharacter}`)[0];
	      const formattedPosition = input.value.indexOf(match) + match.length;
	      input.setSelectionRange(formattedPosition, formattedPosition);
	    },

	    getTextEscapedForRegex(text) {
	      return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
	    },

	    onPhoneInputKeyDown(e) {
	      if (!this.isPhoneTypeInput()) {
	        return;
	      }

	      if (!this.isDigit(e.key) && !this.isControlKey(e.key) && !calendar_util.Util.isAnyModifierKeyPressed(e)) {
	        e.preventDefault();
	      }
	    },

	    isPhoneTypeInput() {
	      return this.contactData.slice(0, 1) === '+';
	    },

	    isDigit(key) {
	      return /^\d+$/.test(key);
	    },

	    isControlKey(key) {
	      return ['Esc', 'Delete', 'Backspace', 'Tab'].indexOf(key) >= 0 || key.includes('Arrow');
	    },

	    formatPhone(value) {
	      var _value;

	      (_value = value) != null ? _value : value = '';
	      let hasPlus = value.indexOf('+') === 0;
	      value = value.replace(/\D/g, '');

	      if (!this.phoneDb) {
	        this.phoneDb = "247,ac,___-____|376,ad,___-___-___|971,ae,___-_-___-____|93,af,__-__-___-____|1268,ag,_ (___) ___-____|1264,ai,_ (___) ___-____|355,al,___ (___) ___-___|374,am,___-__-___-___|599,bq,___-___-____|244,ao,___ (___) ___-___|6721,aq,___-___-___|54,ar,__ (___) ___-____|1684,as,_ (___) ___-____|43,at,__ (___) ___-____|61,au,__-_-____-____|297,aw,___-___-____|994,az,___ (__) ___-__-__|387,ba,___-__-____|1246,bb,_ (___) ___-____|880,bd,___-__-___-___|32,be,__ (___) ___-___|226,bf,___-__-__-____|359,bg,___ (___) ___-___|973,bh,___-____-____|257,bi,___-__-__-____|229,bj,___-__-__-____|1441,bm,_ (___) ___-____|673,bn,___-___-____|591,bo,___-_-___-____|55,br,__-(__)-____-____|1242,bs,_ (___) ___-____|975,bt,___-_-___-___|267,bw,___-__-___-___|375,by,___ (__) ___-__-__|501,bz,___-___-____|243,cd,___ (___) ___-___|236,cf,___-__-__-____|242,cg,___-__-___-____|41,ch,__-__-___-____|225,ci,___-__-___-___|682,ck,___-__-___|56,cl,__-_-____-____|237,cm,___-____-____|86,cn,__ (___) ____-___|57,co,__ (___) ___-____|506,cr,___-____-____|53,cu,__-_-___-____|238,cv,___ (___) __-__|357,cy,___-__-___-___|420,cz,___ (___) ___-___|49,de,__-___-___|253,dj,___-__-__-__-__|45,dk,__-__-__-__-__|1767,dm,_ (___) ___-____|1809,do,_ (___) ___-____|,do,_ (___) ___-____|213,dz,___-__-___-____|593,ec,___-_-___-____|372,ee,___-___-____|20,eg,__ (___) ___-____|291,er,___-_-___-___|34,es,__ (___) ___-___|251,et,___-__-___-____|358,fi,___ (___) ___-__-__|679,fj,___-__-_____|500,fk,___-_____|691,fm,___-___-____|298,fo,___-___-___|262,fr,___-_____-____|33,fr,__ (___) ___-___|508,fr,___-__-____|590,fr,___ (___) ___-___|241,ga,___-_-__-__-__|1473,gd,_ (___) ___-____|995,ge,___ (___) ___-___|594,gf,___-_____-____|233,gh,___ (___) ___-___|350,gi,___-___-_____|299,gl,___-__-__-__|220,gm,___ (___) __-__|224,gn,___-__-___-___|240,gq,___-__-___-____|30,gr,__ (___) ___-____|502,gt,___-_-___-____|1671,gu,_ (___) ___-____|245,gw,___-_-______|592,gy,___-___-____|852,hk,___-____-____|504,hn,___-____-____|385,hr,___-__-___-___|509,ht,___-__-__-____|36,hu,__ (___) ___-___|62,id,__-__-___-__|353,ie,___ (___) ___-___|972,il,___-_-___-____|91,in,__ (____) ___-___|246,io,___-___-____|964,iq,___ (___) ___-____|98,ir,__ (___) ___-____|354,is,___-___-____|39,it,__ (___) ____-___|1876,jm,_ (___) ___-____|962,jo,___-_-____-____|81,jp,__ (___) ___-___|254,ke,___-___-______|996,kg,___ (___) ___-___|855,kh,___ (__) ___-___|686,ki,___-__-___|269,km,___-__-_____|1869,kn,_ (___) ___-____|850,kp,___-___-___|82,kr,__-__-___-____|965,kw,___-____-____|1345,ky,_ (___) ___-____|77,kz,_ (___) ___-__-__|856,la,___-__-___-___|961,lb,___-_-___-___|1758,lc,_ (___) ___-____|423,li,___ (___) ___-____|94,lk,__-__-___-____|231,lr,___-__-___-___|266,ls,___-_-___-____|370,lt,___ (___) __-___|352,lu,___ (___) ___-___|371,lv,___-__-___-___|218,ly,___-__-___-___|212,ma,___-__-____-___|377,mc,___-__-___-___|373,md,___-____-____|382,me,___-__-___-___|261,mg,___-__-__-_____|692,mh,___-___-____|389,mk,___-__-___-___|223,ml,___-__-__-____|95,mm,__-___-___|976,mn,___-__-__-____|853,mo,___-____-____|1670,mp,_ (___) ___-____|596,mq,___ (___) __-__-__|222,mr,___ (__) __-____|1664,ms,_ (___) ___-____|356,mt,___-____-____|230,mu,___-___-____|960,mv,___-___-____|265,mw,___-_-____-____|52,mx,__-__-__-____|60,my,__-_-___-___|258,mz,___-__-___-___|264,na,___-__-___-____|687,nc,___-__-____|227,ne,___-__-__-____|6723,nf,___-___-___|234,ng,___-__-___-__|505,ni,___-____-____|31,nl,__-__-___-____|47,no,__ (___) __-___|977,np,___-__-___-___|674,nr,___-___-____|683,nu,___-____|64,nz,__-__-___-___|968,om,___-__-___-___|507,pa,___-___-____|51,pe,__ (___) ___-___|689,pf,___-__-__-__|675,pg,___ (___) __-___|63,ph,__ (___) ___-____|92,pk,__ (___) ___-____|48,pl,__ (___) ___-___|970,ps,___-__-___-____|351,pt,___-__-___-____|680,pw,___-___-____|595,py,___ (___) ___-___|974,qa,___-____-____|40,ro,__-__-___-____|381,rs,___-__-___-____|7,ru,_ (___) ___-__-__|250,rw,___ (___) ___-___|966,sa,___-_-___-____|677,sb,___-_____|248,sc,___-_-___-___|249,sd,___-__-___-____|46,se,__-__-___-____|65,sg,__-____-____|386,si,___-__-___-___|421,sk,___ (___) ___-___|232,sl,___-__-______|378,sm,___-____-______|221,sn,___-__-___-____|252,so,___-_-___-___|597,sr,___-___-___|211,ss,___-__-___-____|239,st,___-__-_____|503,sv,___-__-__-____|1721,sx,_ (___) ___-____|963,sy,___-__-____-___|268,sz,___ (__) __-____|1649,tc,_ (___) ___-____|235,td,___-__-__-__-__|228,tg,___-__-___-___|66,th,__-__-___-___|992,tj,___-__-___-____|690,tk,___-____|670,tl,___-___-____|993,tm,___-_-___-____|216,tn,___-__-___-___|676,to,___-_____|90,tr,__ (___) ___-____|1868,tt,_ (___) ___-____|688,tv,___-_____|886,tw,___-____-____|255,tz,___-__-___-____|380,ua,___ (__) ___-__-__|256,ug,___ (___) ___-___|44,gb,__-__-____-____|598,uy,___-_-___-__-__|998,uz,___-__-___-____|396698,va,__-_-___-_____|1784,vc,_ (___) ___-____|58,ve,__ (___) ___-____|1284,vg,_ (___) ___-____|1340,vi,_ (___) ___-____|84,vn,__-__-____-___|678,vu,___-_____|681,wf,___-__-____|685,ws,___-__-____|967,ye,___-_-___-___|27,za,__-__-___-____|260,zm,___ (__) ___-____|263,zw,___-_-______|1,us,_ (___) ___-____|".split('|').map(item => {
	          item = item.split(',');
	          return {
	            code: item[0],
	            id: item[1],
	            mask: item[2]
	          };
	        });
	      }

	      if (value.length > 0) {
	        let mask = this.findMask(value);
	        mask += ((mask.indexOf('-') >= 0 ? '-' : ' ') + '__').repeat(10);

	        for (let i = 0; i < value.length; i++) {
	          mask = mask.replace('_', value.slice(i, i + 1));
	        }

	        value = mask.replace(/\D+$/, '').replace(/_/g, '0');
	      }

	      if (hasPlus || value.length > 0) {
	        value = '+' + value;
	      }

	      return value;
	    },

	    findMask(value) {
	      let r = this.phoneDb.filter(item => {
	        return value.indexOf(item.code) === 0;
	      }).sort((a, b) => {
	        return b.code.length - a.code.length;
	      })[0];
	      return r ? r.mask : '_ ___ __ __ __';
	    }

	  },
	  template: `
		<Header>
			<template v-slot>
				<HeaderTitle
					:has-back-button="true"
					:back-button-callback="returnToDateSelector"
					:text="$Bitrix.Loc.getMessage('CALENDAR_SHARING_ADD_FORM_HEADER_TITLE')"
				/>
				<StartInfo
					:show-clock-icon="false"
					:event="this.event"
				/>
			</template>
		</Header>
		<div class="calendar-sharing-event-add-form">
			<div class="calendar-sharing-event-add-form_content">
				<div class="calendar-sharing-event-add-form_input_box">
					<input
						class="calendar-sharing-event-add-form_input"
						type="text"
						:placeholder="$Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_NAME_PLACEHOLDER')"
						v-model="eventName"
					>
				</div>
				<div class="calendar-sharing-event-add-form_input_box" :class="{
					'--error': isEmptyContactName
				}">
					<input
						class="calendar-sharing-event-add-form_input"
						type="text"
						:placeholder="$Bitrix.Loc.getMessage('CALENDAR_SHARING_AUTHOR_NAME_PLACEHOLDER')"
						v-model="authorName"
						@input="clearContactNameError"
						@focus="clearContactNameError"
					>
					<span class="calendar-sharing-event-add-form_input_note" v-if="isEmptyContactName">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_REQUIRED_FIELD') }}
					</span>
				</div>
				<div class="calendar-sharing-event-add-form_input_box" :class="{
					'--error': contactDataError || isEmptyContactData
				}">
					<input
						id="calendar-sharing-event-add-form_input-contact"
						class="calendar-sharing-event-add-form_input"
						type="text"
						:placeholder="getContactDataPlaceholder()"
						v-model="contactData"
						@focus="clearContactDataError"
						@keydown="onPhoneInputKeyDown"
						@input="onPhoneInput"
					>
					<span class="calendar-sharing-event-add-form_input_note" v-if="contactDataError">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_ERROR_FIELD') }}
					</span>
					<span class="calendar-sharing-event-add-form_input_note" v-if="isEmptyContactData">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_REQUIRED_FIELD') }}
					</span>
				</div>
			</div>
			<div class="calendar-sharing-event-add-form_buttons">
				<button
					class="ui-btn ui-btn-success ui-btn-round"
					@click="handleSaveEventButton"
					:disabled="saveButton.disabled"
				>
					{{ saveButton.text }}
				</button>
			</div>
			<SharingLoader v-if="loadingProcess"/>
		</div>
	`
	};

	let _ = t => t,
	    _t;
	const ViewForm = {
	  components: {
	    Header,
	    HeaderTitle,
	    StartInfo
	  },
	  name: 'ViewForm',
	  props: {
	    event: {
	      type: Object,
	      required: true
	    },
	    owner: {
	      type: Object,
	      required: true
	    },
	    viewFormError: Boolean
	  },

	  data() {
	    return {
	      backButton: null,
	      icsFileSrc: null
	    };
	  },

	  created() {
	    const htmlNode = document.querySelector('html');
	    const bodyNode = document.querySelector('body');
	    this.backButton = main_core.Tag.render(_t || (_t = _`<div class="calendar-sharing-view-form__back-button">
			<div class="calendar-sharing_previous-month-arrow"></div>
			<div class="calendar-sharing-view-form__text">${0}</div>
		</div>`), main_core.Loc.getMessage('CALENDAR_SHARING_BACK'));
	    main_core.Event.bind(this.backButton, 'click', this.returnToDateSelector.bind(this));
	    main_core.Dom.append(this.backButton, bodyNode);

	    if (main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-gray')) {
	      main_core.Dom.removeClass(htmlNode, 'calendar-sharing--bg-gray');
	    }

	    if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing-html-body-center')) {
	      main_core.Dom.addClass(htmlNode, 'calendar-sharing-html-body-center');
	    }

	    if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing-html-body-center')) {
	      main_core.Dom.addClass(bodyNode, 'calendar-sharing-html-body-center');
	    }

	    if (this.viewFormError) {
	      main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-red');
	    } else {
	      main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-green');
	    }
	  },

	  methods: {
	    async downloadIcsFile() {
	      if (!this.icsFile) {
	        const response = await BX.ajax.runAction('calendar.api.sharingajax.getIcsFileContent', {
	          data: {
	            eventLinkHash: this.event.linkHash
	          }
	        });
	        this.icsFile = response.data;
	      }

	      calendar_util.Util.downloadIcsFile(this.icsFile, 'event');
	    },

	    returnToDateSelector() {
	      if (this.backButton) {
	        main_core.Dom.remove(this.backButton);
	      }

	      this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {
	        type: 'calendar'
	      });
	    }

	  },
	  template: `
		<div 
			class="calendar-sharing-main__container calendar-sharing--subtract"
			:class="{
				'calendar-sharing--success': !viewFormError,
				'calendar-sharing--error': viewFormError
			}"
		>
			<Header>
				<template v-slot>
					<HeaderTitle
						:has-back-button="false"
						:back-button-callback="returnToDateSelector"
						:text="$Bitrix.Loc.getMessage('CALENDAR_SHARING_VIEW_FORM_HEADER_TITLE_ERROR')"
						v-if="viewFormError"
					/>
					<HeaderTitle
						:has-back-button="false"
						:back-button-callback="returnToDateSelector"
						:text="$Bitrix.Loc.getMessage('CALENDAR_SHARING_VIEW_FORM_HEADER_TITLE')"
						v-else
					/>
					<StartInfo
						:event="this.event"
						:show-clock-icon="!viewFormError"
					/>
					<div class="calendar-sharing-view-form__owner_container">
						<div class="calendar-sharing-view-form__owner_icon_container ui-icon ui-icon-common-user">
							<img class="calendar-sharing-view-form__owner_icon" :src="owner.photo" alt="" v-if="owner.photo">
							<i class="calendar-sharing-view-form__owner_icon" v-else></i>
							<div class="calendar-sharing-view-form__owner_icon_status" v-if="!viewFormError"></div>
						</div>
						<div>
							<div class="calendar-sharing-view-form__owner_name">
								{{ owner.name }} {{ owner.lastName }}
							</div>
							<div class="calendar-sharing-view-form__owner_status" v-if="viewFormError">
								{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_CREATE_ERROR') }}
							</div>
							<div class="calendar-sharing-view-form__owner_status" v-else>
								{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_CREATE_SUCCESS') }}
							</div>
						</div>
					</div>
				</template>
			</Header>
			<div class="calendar-sharing-event-add-form">
				<div class="calendar-sharing-event-add-form_buttons">
					<button class="ui-btn ui-btn-success ui-btn-round" @click="returnToDateSelector" v-if="viewFormError">
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_RETURN_TO_CALENDAR') }}
					</button>
					<button class="ui-btn ui-btn-success ui-btn-round" @click="downloadIcsFile" v-else>
						{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_DOWNLOAD_ICS') }}
					</button>
				</div>
			</div>
		</div>
	`
	};

	const WelcomePage = {
	  props: {
	    owner: Object
	  },

	  mounted() {
	    this.setPageVisualSettings();
	  },

	  methods: {
	    async closeWelcomePage() {
	      // await BX.ajax.runAction('calendar.api.sharingajax.saveFirstEntry');
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:changeApplicationType', {
	        type: 'calendar'
	      });
	    },

	    setPageVisualSettings() {
	      const htmlNode = document.querySelector('html');
	      const bodyNode = document.querySelector('body');

	      if (main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-gray')) {
	        main_core.Dom.removeClass(htmlNode, 'calendar-sharing--bg-gray');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing--bg-blue')) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing--bg-blue');
	      }

	      if (!main_core.Dom.hasClass(htmlNode, 'calendar-sharing-html-body-center')) {
	        main_core.Dom.addClass(htmlNode, 'calendar-sharing-html-body-center');
	      }

	      if (!main_core.Dom.hasClass(bodyNode, 'calendar-sharing-html-body-center')) {
	        main_core.Dom.addClass(bodyNode, 'calendar-sharing-html-body-center');
	      }
	    }

	  },
	  template: `
		<div class="calendar-sharing-welcome-page__container calendar-sharing--subtract">
			<div class="calendar-sharing-welcome-page__photo ui-icon ui-icon-common-user">
				<img class="calendar-sharing-welcome-page__photo_item" :src="owner.photo" alt="" v-if="owner.photo">
				<i class="calendar-sharing-welcome-page__photo_item" v-else></i>
			</div>
			<div class="calendar-sharing-welcome-page_title">
				{{ owner.name }} {{ owner.lastName }}
			</div>
			<div class="calendar-sharing-welcome-page_subtitle">
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_WELCOME_PAGE_TEXT') }}
			</div>
			<button class="ui-btn ui-btn-success ui-btn-round" @click="closeWelcomePage">
				{{ $Bitrix.Loc.getMessage('CALENDAR_SHARING_WELCOME_PAGE_NEXT') }}
			</button>
		</div>
	`
	};

	const Application = {
	  props: {
	    link: Object,
	    owner: Object,
	    sharingUser: Object,
	    calendarSettings: Object,
	    userAccessibility: Object,
	    timezoneList: Object,
	    welcomePageVisited: String
	  },
	  components: {
	    DateSelector,
	    EventSlotList,
	    AddForm,
	    ViewForm,
	    WelcomePage
	  },
	  name: 'Application',

	  data() {
	    return {
	      type: '',
	      lastEventName: '',
	      viewFormError: false,
	      eventData: {
	        timeFrom: new Date(),
	        timeTo: new Date(),
	        timezone: this.timezoneList[Intl.DateTimeFormat().resolvedOptions().timeZone]
	      }
	    };
	  },

	  created() {
	    this.type = this.welcomePageVisited ? 'calendar' : 'welcomePage';
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:changeApplicationType', event => {
	      this.changeApplicationTypeHandler(event);
	    });
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:changeEventData', event => {
	      this.changeEventDataHandler(event);
	    });
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:onSetEventData', event => {
	      this.onSetEventDataHandler(event);
	    });
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:setEventViewError', event => {
	      this.viewFormError = event.data.viewFormError;
	    });
	    this.$Bitrix.eventEmitter.subscribe('calendar:sharing:updateAddFormDefaultParams', event => {
	      this.updateAddFormDefaultParams(event);
	    });
	  },

	  mounted() {
	    this.DOM = {
	      welcomePage: document.querySelector('.calendar-sharing-welcome-page'),
	      calendarContainer: document.querySelector('.calendar-sharing__calendar'),
	      dateSelectorContainer: document.querySelector('.calendar-sharing__date-selector'),
	      addFormElement: document.querySelector('.calendar-sharing__add-form').firstElementChild
	    };
	  },

	  methods: {
	    changeApplicationTypeHandler(event) {
	      const currentType = this.type;
	      const newType = event.data.type;

	      if (currentType === 'welcomePage' && newType === 'calendar') {
	        this.transitFromWelcomePageToCalendar();
	      }

	      if (currentType === 'calendar' && newType === 'addForm') {
	        this.transitFromCalendarToAddForm();
	      }

	      if (currentType === 'addForm' && newType === 'calendar') {
	        this.transitFromAddFormToCalendar();
	      }

	      if (currentType === 'addForm' && newType === 'viewForm') {
	        this.transitFromAddFormToViewForm();
	      }

	      if (currentType === 'viewForm' && newType === 'calendar') {
	        this.transitFromViewFormToCalendar();
	      }

	      this.type = newType;
	    },

	    changeEventDataHandler(event) {
	      this.eventData = event.data;
	    },

	    onSetEventDataHandler(event) {
	      if (event.data.eventId) {
	        this.eventData.id = event.data.eventId;
	      }

	      if (event.data.eventName) {
	        this.eventData.name = event.data.eventName;
	      }

	      if (event.data.eventLinkId) {
	        this.eventData.linkId = event.data.eventLinkId;
	      }

	      if (event.data.eventLinkHash) {
	        this.eventData.linkHash = event.data.eventLinkHash;
	      }

	      if (event.data.eventLinkShortUrl) {
	        this.eventData.eventLinkShortUrl = event.data.eventLinkShortUrl;
	      }
	    },

	    updateAddFormDefaultParams(event) {
	      this.sharingUser.userName = event.data.userName;
	      this.sharingUser.personalMailbox = event.data.contactData;

	      if (this.viewFormError) {
	        this.lastEventName = event.data.eventName;
	      } else {
	        this.lastEventName = '';
	      }
	    },

	    transitFromWelcomePageToCalendar() {
	      this.DOM.calendarContainer.style.transform = 'scale(1.5)';
	      this.DOM.calendarContainer.style.filter = 'blur(1px)';
	      this.DOM.calendarContainer.style.opacity = 0;
	      this.DOM.calendarContainer.style.transition = '300ms all ease';
	      this.DOM.welcomePage.style.transition = '300ms all ease';
	      this.DOM.welcomePage.style.transform = 'scale(1.5)';
	      this.DOM.welcomePage.style.filter = 'blur(1px)';
	      this.DOM.welcomePage.style.opacity = 0;
	      setTimeout(() => {
	        this.DOM.welcomePage.remove();
	        this.DOM.calendarContainer.style.display = '';
	        this.$Bitrix.eventEmitter.emit('calendar:sharing:onShowCalendar');
	        setTimeout(() => {
	          this.DOM.calendarContainer.style.transform = '';
	          this.DOM.calendarContainer.style.filter = '';
	          this.DOM.calendarContainer.style.opacity = '';
	          setTimeout(() => {
	            this.DOM.calendarContainer.style.transition = '';
	          }, 300);
	        }, 100);
	      }, 300);
	    },

	    transitFromCalendarToAddForm() {
	      this.DOM.calendarContainer.style.height = this.DOM.dateSelectorContainer.offsetHeight + 'px';
	      new BX.easing({
	        duration: 150,
	        start: {
	          scrollLeft: this.DOM.calendarContainer.scrollLeft,
	          height: this.DOM.dateSelectorContainer.offsetHeight
	        },
	        finish: {
	          scrollLeft: this.DOM.calendarContainer.scrollWidth - this.DOM.calendarContainer.offsetWidth,
	          height: this.DOM.addFormElement.offsetHeight
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	        step: state => {
	          this.DOM.calendarContainer.scrollLeft = state.scrollLeft;
	          this.DOM.calendarContainer.style.height = state.height + 'px';
	        },
	        complete: () => {}
	      }).animate();
	    },

	    transitFromAddFormToCalendar() {
	      this.DOM.calendarContainer.style.height = this.DOM.addFormElement.offsetHeight + 'px';
	      new BX.easing({
	        duration: 150,
	        start: {
	          scrollLeft: this.DOM.calendarContainer.scrollLeft,
	          height: this.DOM.addFormElement.offsetHeight
	        },
	        finish: {
	          scrollLeft: 0,
	          height: this.DOM.dateSelectorContainer.offsetHeight
	        },
	        transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
	        step: state => {
	          this.DOM.calendarContainer.scrollLeft = state.scrollLeft;
	          this.DOM.calendarContainer.style.height = state.height + 'px';
	        },
	        complete: () => {
	          this.DOM.calendarContainer.style.height = '';
	        }
	      }).animate();
	    },

	    transitFromAddFormToViewForm() {
	      this.DOM.calendarContainer.scrollLeft = 0;
	      this.DOM.calendarContainer.style.height = '';
	      this.DOM.calendarContainer.style.display = 'none';

	      if (!this.viewFormError) {
	        ui_confetti.Confetti.fire({
	          particleCount: 240,
	          spread: 70,
	          origin: {
	            y: 0.3,
	            x: 0.5
	          },
	          zIndex: 2
	        });
	      }
	    },

	    transitFromViewFormToCalendar() {
	      this.DOM.calendarContainer.style.display = '';
	      this.eventData.name = false;
	      this.$Bitrix.eventEmitter.emit('calendar:sharing:onShowCalendar');
	    }

	  },
	  template: `
		<div class="calendar-sharing-welcome-page">
			<WelcomePage
				:owner="owner"
			/>
		</div>
		<div class="calendar-sharing__calendar" style="display: none;">
			<div class="calendar-sharing__date-selector calendar-sharing--bg-gray">
				<DateSelector
					:userAccessibility="userAccessibility"
					:calendarSettings="calendarSettings"
					:timezoneList="timezoneList"
					:owner="owner"
				/>
				<EventSlotList
					:timezoneList="timezoneList"
				/>
			</div>
			<div class="calendar-sharing__add-form">
				<div class="calendar-sharing-main__container calendar-sharing__form_box">
					<AddForm
						:owner="owner"
						:sharingUser="sharingUser"
						:event="eventData"
						:last-event-name="lastEventName"
						:phone-feature-enabled="calendarSettings.phoneFeatureEnabled"
						:userLinkHash="link.hash"
					/>
				</div>
			</div>
		</div>
		<div v-if="type === 'viewForm'">
			<ViewForm
				:owner="owner"
				:event="eventData"
				:view-form-error="viewFormError"
			/>
			<div 
				class="calendar-sharing-event-created-info" v-if="!viewFormError"
				v-html="$Bitrix.Loc.getMessage('CALENDAR_SHARING_EVENT_CREATED_INFO').replace('#EVENT_LINK#', eventData.eventLinkShortUrl)"
			>
			</div>
		</div>
	`
	};

	class Public {
	  constructor(options = {}) {
	    this.owner = options.owner;
	    this.sharingUser = options.sharingUser;
	    this.link = options.link;
	    this.calendarSettings = options.calendarSettings;
	    this.userAccessibility = options.userAccessibility;
	    this.timezoneList = options.timezoneList;
	    this.welcomePageVisited = options.welcomePageVisited;
	    this.rootNode = BX('calendar-sharing-main');
	    this.buildViews();
	  }

	  buildViews() {
	    this.application = ui_vue3.BitrixVue.createApp(Application, {
	      link: this.link,
	      owner: this.owner,
	      sharingUser: this.sharingUser,
	      calendarSettings: this.calendarSettings,
	      userAccessibility: this.userAccessibility,
	      timezoneList: this.timezoneList,
	      welcomePageVisited: this.welcomePageVisited
	    }).mount(this.rootNode);
	  }

	}

	exports.Public = Public;

}((this.BX.Calendar.Sharing = this.BX.Calendar.Sharing || {}),BX.Vue3,BX.Main,BX.Calendar,BX,BX.UI));
//# sourceMappingURL=public.bundle.js.map
