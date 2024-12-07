/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_popup,main_core_events,main_date,main_core_cache,main_core) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4;
	var _datePicker = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("datePicker");
	var _refs = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _rendered = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rendered");
	class BasePicker extends main_core_events.EventEmitter {
	  constructor(datePicker) {
	    super();
	    Object.defineProperty(this, _datePicker, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _refs, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	    Object.defineProperty(this, _rendered, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('BX.UI.DatePicker.BasePicker');
	    babelHelpers.classPrivateFieldLooseBase(this, _datePicker)[_datePicker] = datePicker;
	  }
	  getContainer() {
	    throw new Error('You must implement getContainer method');
	  }
	  getHeaderContainer(...children) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs)[_refs].remember('header', () => {
	      return main_core.Tag.render(_t || (_t = _`<div class="ui-date-picker-header">${0}</div>`), children);
	    });
	  }
	  getContentContainer(...children) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs)[_refs].remember('content', () => {
	      return main_core.Tag.render(_t2 || (_t2 = _`<div class="ui-date-picker-content">${0}</div>`), children);
	    });
	  }
	  getPrevBtn() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs)[_refs].remember('prev-button', () => {
	      return main_core.Tag.render(_t3 || (_t3 = _`
				<button type="button" class="ui-date-picker-button --left-arrow" onclick="${0}">
					<span class="ui-icon-set --chevron-left" style="--ui-icon-set__icon-size: 20px"></span>
				</button>
			`), this.handlePrevBtnClick.bind(this));
	    });
	  }
	  getNextBtn() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs)[_refs].remember('next-button', () => {
	      return main_core.Tag.render(_t4 || (_t4 = _`
				<button type="button" class="ui-date-picker-button --right-arrow" onclick="${0}">
					<span class="ui-icon-set --chevron-right" style="--ui-icon-set__icon-size: 20px"></span>
				</button>
			`), this.handleNextBtnClick.bind(this));
	    });
	  }
	  handlePrevBtnClick() {
	    this.emit('onPrevBtnClick');
	  }
	  handleNextBtnClick() {
	    this.emit('onNextBtnClick');
	  }
	  render() {
	    throw new Error('You must implement render method');
	  }
	  onShow() {
	    // you can override this method
	  }
	  onHide() {
	    // you can override this method
	  }
	  getDatePicker() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _datePicker)[_datePicker];
	  }
	  isRendered() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rendered)[_rendered];
	  }
	  renderTo(container) {
	    main_core.Dom.append(this.getContainer(), container);
	    babelHelpers.classPrivateFieldLooseBase(this, _rendered)[_rendered] = true;
	  }
	}

	function cloneDate(date) {
	  const newDate = new Date(date.getTime());
	  if (date.__utc) {
	    newDate.__utc = true;
	  }
	  return newDate;
	}

	function getDaysInMonth(date) {
	  const month = date.getUTCMonth();
	  const year = date.getUTCFullYear();
	  const daysInMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	  if (month !== 1 || year % 4 === 0 && year % 100 !== 0 || year % 400 === 0) {
	    return daysInMonth[month];
	  }
	  return 28;
	}

	function addDate(date, unit, increment) {
	  let newDate = cloneDate(date);
	  if (!unit || increment === 0) {
	    return newDate;
	  }
	  switch (unit.toLowerCase()) {
	    case 'milli':
	      newDate = new Date(date.getTime() + increment);
	      break;
	    case 'second':
	      newDate = new Date(date.getTime() + increment * 1000);
	      break;
	    case 'minute':
	      newDate = new Date(date.getTime() + increment * 60000);
	      break;
	    case 'hour':
	      newDate = new Date(date.getTime() + increment * 3600000);
	      break;
	    case 'day':
	      newDate.setUTCDate(date.getUTCDate() + increment);
	      break;
	    case 'week':
	      newDate.setUTCDate(date.getUTCDate() + increment * 7);
	      break;
	    case 'month':
	      {
	        let day = date.getUTCDate();
	        if (day > 28) {
	          const firstDayOfMonth = new Date(Date.UTC(date.getUTCFullYear(), date.getUTCMonth(), 1));
	          day = Math.min(day, getDaysInMonth(addDate(firstDayOfMonth, 'month', increment)));
	        }
	        newDate.setUTCDate(day);
	        newDate.setUTCMonth(newDate.getUTCMonth() + increment);
	        break;
	      }
	    case 'quarter':
	      newDate = addDate(date, 'month', increment * 3);
	      break;
	    case 'year':
	      newDate.setUTCFullYear(date.getUTCFullYear() + increment);
	      break;
	    default:
	    // nothing
	  }

	  if (date.__utc) {
	    newDate.__utc = true;
	  }
	  return newDate;
	}

	function floorDate(date, unit, firstWeekDay) {
	  let newDate = cloneDate(date);
	  switch (unit) {
	    case 'day':
	      newDate.setUTCHours(0, 0, 0, 0);
	      break;
	    case 'week':
	      {
	        const day = newDate.getUTCDay();
	        newDate.setUTCHours(0, 0, 0, 0);
	        if (day !== firstWeekDay) {
	          newDate = addDate(newDate, 'day', -(day > firstWeekDay ? day - firstWeekDay : 7 - day - firstWeekDay));
	        }
	        break;
	      }
	    case 'month':
	      newDate.setUTCHours(0, 0, 0, 0);
	      newDate.setUTCDate(1);
	      break;
	    case 'hour':
	      newDate.setUTCMinutes(0, 0, 0);
	      break;
	    case 'minute':
	      newDate.setUTCSeconds(0);
	      newDate.setUTCMilliseconds(0);
	      break;
	    case 'second':
	      newDate.setUTCMilliseconds(0);
	      break;
	    case 'year':
	      newDate = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
	      break;
	    case 'quarter':
	      {
	        newDate.setUTCHours(0, 0, 0, 0);
	        newDate.setUTCDate(1);
	        newDate = addDate(newDate, 'month', -(newDate.getUTCMonth() % 3));
	        break;
	      }
	    default:
	    // No default
	  }

	  if (date.__utc) {
	    newDate.__utc = true;
	  }
	  return newDate;
	}

	function getNextDate(date, unit, increment = 1, firstWeekDay = 0) {
	  let newDate = cloneDate(date);
	  switch (unit) {
	    case 'day':
	      newDate.setUTCMinutes(0, 0, 0);
	      newDate = addDate(newDate, 'day', increment);
	      break;
	    case 'week':
	      {
	        const dayOfWeek = newDate.getUTCDay();
	        newDate = addDate(newDate, 'day', 7 * (increment - 1) + (dayOfWeek < firstWeekDay ? firstWeekDay - dayOfWeek : 7 - dayOfWeek + firstWeekDay));
	        break;
	      }
	    case 'month':
	      newDate = addDate(newDate, 'month', increment);
	      newDate.setUTCDate(1);
	      break;
	    case 'quarter':
	      newDate = addDate(newDate, 'month', (increment - 1) * 3 + (3 - newDate.getUTCMonth() % 3));
	      break;
	    case 'year':
	      newDate = new Date(Date.UTC(newDate.getUTCFullYear() + increment, 0, 1));
	      break;
	    default:
	      newDate = addDate(date, unit, increment);
	  }
	  if (date.__utc) {
	    newDate.__utc = true;
	  }
	  return newDate;
	}

	function ceilDate(date, unit, increment, firstWeekDay) {
	  const newDate = cloneDate(date);
	  if (unit === 'week') {
	    newDate.setUTCHours(0, 0, 0, 0);
	    return addDate(floorDate(newDate, unit, firstWeekDay), unit, 1);
	  }
	  switch (unit) {
	    case 'hour':
	      newDate.setUTCMinutes(0, 0, 0);
	      break;
	    case 'minute':
	      newDate.setUTCSeconds(0, 0);
	      break;
	    case 'second':
	      newDate.setUTCMilliseconds(0);
	      break;
	    default:
	      newDate.setUTCHours(0, 0, 0, 0);
	  }
	  return getNextDate(newDate, unit, increment);
	}

	function createUtcDate(year, monthIndex = 0, day = 1, hours = 0, minutes = 0, seconds = 0, ms = 0) {
	  const date = new Date(Date.UTC(year, monthIndex, day, hours, minutes, seconds, ms));

	  // The year from 0 to 99 will be incremented by 1900 automatically.
	  if (year < 100 && year >= 0) {
	    date.setUTCFullYear(year);
	  }
	  date.__utc = true;
	  return date;
	}

	function getDate(date) {
	  const hours = date.getUTCHours();
	  const hours12 = hours % 12 === 0 ? 12 : hours % 12;
	  const dayPeriod = hours > 11 ? 'pm' : 'am';
	  return {
	    day: date.getUTCDate(),
	    // 1-31
	    month: date.getUTCMonth(),
	    // 0-11
	    year: date.getUTCFullYear(),
	    weekDay: date.getUTCDay(),
	    // 0-6
	    hours,
	    // 0-23
	    hours12,
	    // 1-12
	    minutes: date.getUTCMinutes(),
	    // 0-59
	    seconds: date.getUTCSeconds(),
	    // 0-59
	    dayPeriod,
	    fullDay: String(date.getUTCDate()).padStart(2, '0'),
	    fullHours: String(hours).padStart(2, '0'),
	    fullHours12: String(hours12).padStart(2, '0'),
	    fullMinutes: String(date.getUTCMinutes()).padStart(2, '0')
	  };
	}

	function isDatesEqual(dateA, dateB, precision = 'day') {
	  if (!main_core.Type.isDate(dateA) || !main_core.Type.isDate(dateB)) {
	    return false;
	  }
	  const {
	    day: dayA,
	    month: monthA,
	    year: yearA,
	    hours: hoursA,
	    minutes: minutesA,
	    seconds: secondsA
	  } = getDate(dateA);
	  const {
	    day: dayB,
	    month: monthB,
	    year: yearB,
	    hours: hoursB,
	    minutes: minutesB,
	    seconds: secondsB
	  } = getDate(dateB);
	  if (precision === 'day') {
	    return dayA === dayB && monthA === monthB && yearA === yearB;
	  }
	  if (precision === 'datetime') {
	    return dayA === dayB && monthA === monthB && yearA === yearB && hoursA === hoursB && minutesA === minutesB && secondsA === secondsB;
	  }
	  if (precision === 'month') {
	    return monthA === monthB && yearA === yearB;
	  }
	  if (precision === 'year') {
	    return yearA === yearB;
	  }
	  return false;
	}

	let _2 = t => t,
	  _t$1,
	  _t2$1,
	  _t3$1,
	  _t4$1,
	  _t5,
	  _t6,
	  _t7,
	  _t8,
	  _t9,
	  _t10,
	  _t11,
	  _t12,
	  _t13,
	  _t14,
	  _t15,
	  _t16,
	  _t17,
	  _t18,
	  _t19,
	  _t20,
	  _t21;
	var _refs$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _weekdays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("weekdays");
	var _mouseOutTimeout = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mouseOutTimeout");
	var _renderMonthContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMonthContainer");
	var _renderMonthHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMonthHeader");
	var _renderWeekDays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderWeekDays");
	var _renderWeek = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderWeek");
	var _renderWeekNumber = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderWeekNumber");
	var _renderDay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderDay");
	var _renderTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderTime");
	var _getStartMonthDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStartMonthDate");
	var _getRangeDates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getRangeDates");
	var _handleDayClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDayClick");
	var _handleDayMouseOver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDayMouseOver");
	var _handleDayMouseOut = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDayMouseOut");
	var _handleMonthClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMonthClick");
	var _handleYearClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleYearClick");
	var _handleTimeClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeClick");
	var _handleTimeRangeStartClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeRangeStartClick");
	var _handleTimeRangeEndClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeRangeEndClick");
	class DayPicker extends BasePicker {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _handleTimeRangeEndClick, {
	      value: _handleTimeRangeEndClick2
	    });
	    Object.defineProperty(this, _handleTimeRangeStartClick, {
	      value: _handleTimeRangeStartClick2
	    });
	    Object.defineProperty(this, _handleTimeClick, {
	      value: _handleTimeClick2
	    });
	    Object.defineProperty(this, _handleYearClick, {
	      value: _handleYearClick2
	    });
	    Object.defineProperty(this, _handleMonthClick, {
	      value: _handleMonthClick2
	    });
	    Object.defineProperty(this, _handleDayMouseOut, {
	      value: _handleDayMouseOut2
	    });
	    Object.defineProperty(this, _handleDayMouseOver, {
	      value: _handleDayMouseOver2
	    });
	    Object.defineProperty(this, _handleDayClick, {
	      value: _handleDayClick2
	    });
	    Object.defineProperty(this, _getRangeDates, {
	      value: _getRangeDates2
	    });
	    Object.defineProperty(this, _getStartMonthDate, {
	      value: _getStartMonthDate2
	    });
	    Object.defineProperty(this, _renderTime, {
	      value: _renderTime2
	    });
	    Object.defineProperty(this, _renderDay, {
	      value: _renderDay2
	    });
	    Object.defineProperty(this, _renderWeekNumber, {
	      value: _renderWeekNumber2
	    });
	    Object.defineProperty(this, _renderWeek, {
	      value: _renderWeek2
	    });
	    Object.defineProperty(this, _renderWeekDays, {
	      value: _renderWeekDays2
	    });
	    Object.defineProperty(this, _renderMonthHeader, {
	      value: _renderMonthHeader2
	    });
	    Object.defineProperty(this, _renderMonthContainer, {
	      value: _renderMonthContainer2
	    });
	    Object.defineProperty(this, _refs$1, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	    Object.defineProperty(this, _weekdays, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _mouseOutTimeout, {
	      writable: true,
	      value: null
	    });
	  }
	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('container', () => {
	      return main_core.Tag.render(_t$1 || (_t$1 = _2`
				<div class="ui-day-picker${0}">
					${0}
					${0}
					${0}
				</div>
			`), this.getDatePicker().isFullYear() ? ' --full-year' : '', this.getHeader(), this.getContentContainer(this.getMonthContainer()), this.getDatePicker().isTimeEnabled() ? this.getDatePicker().isRangeMode() ? this.getTimeRangeContainer() : this.getTimeContainer() : null);
	    });
	  }
	  getHeader() {
	    const numberOfMonths = this.getDatePicker().getNumberOfMonths();
	    if (this.getDatePicker().isFullYear()) {
	      return this.getHeaderContainer(this.getPrevBtn(), main_core.Tag.render(_t2$1 || (_t2$1 = _2`
					<div class="ui-date-picker-header-title">
						${0}
					</div>
				`), this.getFullYearHeader()), this.getNextBtn());
	    }
	    return this.getHeaderContainer(this.getPrevBtn(), ...Array.from({
	      length: numberOfMonths
	    }).map((_, monthNumber) => {
	      return main_core.Tag.render(_t3$1 || (_t3$1 = _2`
					<div class="ui-date-picker-header-title">
						${0}
						${0}
					</div>
				`), this.getHeaderMonth(monthNumber), this.getHeaderYear(monthNumber));
	    }), this.getNextBtn());
	  }
	  getFullYearHeader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('header-full-year', () => {
	      return main_core.Tag.render(_t4$1 || (_t4$1 = _2`
				<span class="ui-date-picker-header-full-year"></span>
			`));
	    });
	  }
	  getHeaderMonth(monthNumber) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(`header-month-${monthNumber}`, () => {
	      return main_core.Tag.render(_t5 || (_t5 = _2`
				<button type="button" class="ui-date-picker-header-month" onclick="${0}"></button>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleMonthClick)[_handleMonthClick].bind(this));
	    });
	  }
	  getMonthContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('month-container', () => {
	      return main_core.Tag.render(_t6 || (_t6 = _2`
				<div class="ui-day-picker-content" 
					onclick="${0}"
					onmouseover="${0}"
					onmouseout="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleDayClick)[_handleDayClick].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleDayMouseOver)[_handleDayMouseOver].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleDayMouseOut)[_handleDayMouseOut].bind(this));
	    });
	  }
	  getHeaderYear(monthNumber) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(`header-year-${monthNumber}`, () => {
	      return main_core.Tag.render(_t7 || (_t7 = _2`
				<button type="button" class="ui-date-picker-header-year" onclick="${0}"></button>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleYearClick)[_handleYearClick].bind(this));
	    });
	  }
	  getTimeContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('date-time-container', () => {
	      return main_core.Tag.render(_t8 || (_t8 = _2`
				<div class="ui-date-picker-time-container">
					<button type="button" class="ui-date-picker-time-box" onclick="${0}">
						<span class="ui-date-picker-time-clock"></span>
						${0}
					</button>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleTimeClick)[_handleTimeClick].bind(this), this.getTimeValueContainer());
	    });
	  }
	  getTimeRangeContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('range-time-container', () => {
	      return main_core.Tag.render(_t9 || (_t9 = _2`
				<div class="ui-date-picker-time-container --range">
					<div class="ui-date-picker-time-range-slot">
						<button 
							type="button" 
							class="ui-date-picker-time-box --range-start" 
							onclick="${0}"
						>
							<span class="ui-date-picker-time-clock"></span>
							${0}
						</button>
					</div>
					<div class="ui-date-picker-time-range-slot">
						<button 
							type="button" 
							class="ui-date-picker-time-box --range-end" 
							onclick="${0}"
						>
							<span class="ui-date-picker-time-clock"></span>
							${0}
						</button>
					</div>
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleTimeRangeStartClick)[_handleTimeRangeStartClick].bind(this), this.getTimeRangeStartContainer(), babelHelpers.classPrivateFieldLooseBase(this, _handleTimeRangeEndClick)[_handleTimeRangeEndClick].bind(this), this.getTimeRangeEndContainer());
	    });
	  }
	  getTimeValueContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('time-value', () => {
	      return main_core.Tag.render(_t10 || (_t10 = _2`<div class="ui-date-picker-time-value"></div>`));
	    });
	  }
	  getTimeRangeStartContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('time-range-start', () => {
	      return main_core.Tag.render(_t11 || (_t11 = _2`<div class="ui-date-picker-time-value"></div>`));
	    });
	  }
	  getTimeRangeEndContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember('time-range-end', () => {
	      return main_core.Tag.render(_t12 || (_t12 = _2`<div class="ui-date-picker-time-value"></div>`));
	    });
	  }
	  getWeekDays() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _weekdays)[_weekdays] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _weekdays)[_weekdays];
	    }
	    const firstWeekDay = this.getDatePicker().getFirstWeekDay();
	    const weekDays = [main_core.Loc.getMessage('DOW_0'), main_core.Loc.getMessage('DOW_1'), main_core.Loc.getMessage('DOW_2'), main_core.Loc.getMessage('DOW_3'), main_core.Loc.getMessage('DOW_4'), main_core.Loc.getMessage('DOW_5'), main_core.Loc.getMessage('DOW_6')];
	    babelHelpers.classPrivateFieldLooseBase(this, _weekdays)[_weekdays] = [...[...weekDays].slice(firstWeekDay), ...[...weekDays].splice(0, firstWeekDay)];
	    return babelHelpers.classPrivateFieldLooseBase(this, _weekdays)[_weekdays];
	  }
	  render() {
	    let focusButton = null;
	    const isFocused = this.getDatePicker().isFocused();
	    this.getMonths().forEach((month, monthNumber) => {
	      if (this.getDatePicker().isFullYear()) {
	        this.getFullYearHeader().textContent = main_date.DateTimeFormat.format('Y', month.date, null, true);
	      } else {
	        this.getHeaderMonth(monthNumber).textContent = main_date.DateTimeFormat.format('f', month.date, null, true);
	        this.getHeaderYear(monthNumber).textContent = main_date.DateTimeFormat.format('Y', month.date, null, true);
	      }
	      const monthContainer = babelHelpers.classPrivateFieldLooseBase(this, _renderMonthContainer)[_renderMonthContainer](monthNumber);
	      if (this.getDatePicker().isFullYear()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderMonthHeader)[_renderMonthHeader](monthNumber, monthContainer);
	      }
	      if (this.getDatePicker().shouldShowWeekDays()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _renderWeekDays)[_renderWeekDays](monthNumber, monthContainer);
	      }
	      month.weeks.forEach((week, weekNumber) => {
	        const weekContainer = babelHelpers.classPrivateFieldLooseBase(this, _renderWeek)[_renderWeek](monthNumber, weekNumber, monthContainer);
	        if (this.getDatePicker().shouldShowWeekNumbers()) {
	          babelHelpers.classPrivateFieldLooseBase(this, _renderWeekNumber)[_renderWeekNumber](monthNumber, weekNumber, week, weekContainer);
	        }
	        week.forEach((day, dayIndex) => {
	          const id = `day-${monthNumber}-${weekNumber}-${dayIndex}`;
	          const button = babelHelpers.classPrivateFieldLooseBase(this, _renderDay)[_renderDay](id, day, weekContainer);
	          if (day.focused) {
	            focusButton = button;
	          }
	        });
	      });
	    });
	    if (focusButton !== null && isFocused) {
	      focusButton.focus({
	        preventScroll: true
	      });
	    }
	    if (this.getDatePicker().isTimeEnabled()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _renderTime)[_renderTime]();
	    }
	  }
	  getMonths() {
	    const months = [];
	    const picker = this.getDatePicker();
	    let date = picker.getViewDate();
	    const numberOfMonths = picker.getNumberOfMonths();
	    const today = picker.getToday();
	    const focusDate = picker.getFocusDate();
	    const initialFocusDate = this.getDatePicker().getInitialFocusDate();
	    const showOutsideDays = picker.shouldShowOutsideDays();
	    const {
	      year,
	      month
	    } = picker.getViewDateParts();
	    const firstAvailableDay = createUtcDate(year, month);
	    const lastAvailableDay = ceilDate(createUtcDate(year, month + numberOfMonths - 1), 'month');
	    const [from, to] = babelHelpers.classPrivateFieldLooseBase(this, _getRangeDates)[_getRangeDates]();
	    const rangeSelected = picker.isRangeMode() && picker.getRangeStart() !== null && picker.getRangeEnd() !== null;
	    for (let index = 0; index < numberOfMonths; index++) {
	      const weeks = [];
	      const firstMonthDay = floorDate(date, 'month');
	      const currentMonthIndex = date.getUTCMonth();
	      date = babelHelpers.classPrivateFieldLooseBase(this, _getStartMonthDate)[_getStartMonthDate](date);
	      for (let weekIndex = 0; weekIndex < 6; weekIndex++) {
	        const week = [];
	        let prevDay = null;
	        for (let weekDay = 0; weekDay < 7; weekDay++) {
	          let available = true;
	          const outside = date.getUTCMonth() !== currentMonthIndex;
	          if (outside) {
	            if (showOutsideDays && numberOfMonths > 1) {
	              available = date.getTime() < firstAvailableDay || date.getTime() >= lastAvailableDay;
	            } else if (!showOutsideDays) {
	              available = false;
	            }
	          }
	          const selected = available && picker.isDateSelected(date, 'day');
	          const rangeFrom = available && from && to && isDatesEqual(date, from);
	          const rangeTo = available && from && to && isDatesEqual(date, to);
	          const rangeIn = available && from && to && (rangeFrom || rangeTo || date.getTime() >= from.getTime() && date.getTime() <= to.getTime());
	          const rangeInStart = rangeIn && (weekDay === 0 || !prevDay.rangeIn);
	          const rangeInEnd = rangeIn && weekDay === 6;
	          if (!rangeIn && prevDay && prevDay.rangeIn) {
	            prevDay.rangeInEnd = true;
	          }
	          const rangeInSelected = selected && rangeIn && !rangeFrom && !rangeTo;
	          const focused = available && isDatesEqual(date, focusDate, 'day');
	          const tabIndex = available && (isDatesEqual(date, focusDate, 'day') || isDatesEqual(date, initialFocusDate, 'day')) ? 0 : -1;
	          const dayColor = this.getDatePicker().getDayColor(date);
	          const marks = this.getDatePicker().getDayMarks(date).map(dayMark => {
	            return dayMark.bgColor;
	          });
	          const day = {
	            date: cloneDate(date),
	            day: date.getUTCDate(),
	            month: date.getUTCMonth(),
	            year: date.getUTCFullYear(),
	            outside,
	            current: isDatesEqual(date, today, 'day'),
	            selected,
	            hidden: outside && !showOutsideDays,
	            dayOff: picker.isDayOff(date),
	            rangeSelected: selected && rangeSelected,
	            focused,
	            tabIndex,
	            rangeFrom,
	            rangeTo,
	            rangeIn,
	            rangeInStart,
	            rangeInEnd,
	            rangeInSelected,
	            bgColor: dayColor === null ? null : dayColor.bgColor,
	            textColor: dayColor === null ? null : dayColor.textColor,
	            marks
	          };
	          week.push(day);
	          prevDay = day;
	          date = addDate(date, 'day', 1);
	        }
	        weeks.push(week);
	      }
	      months.push({
	        weeks,
	        date: firstMonthDay
	      });
	    }
	    return months;
	  }
	  getFirstDay() {
	    const viewDate = this.getDatePicker().getViewDate();
	    const currentMonthIndex = viewDate.getUTCMonth();
	    const showOutsideDays = this.getDatePicker().shouldShowOutsideDays();
	    const firstViewDay = babelHelpers.classPrivateFieldLooseBase(this, _getStartMonthDate)[_getStartMonthDate](this.getDatePicker().getViewDate());
	    const outside = firstViewDay.getUTCMonth() !== currentMonthIndex;
	    if (outside && !showOutsideDays) {
	      return floorDate(viewDate, 'month');
	    }
	    return firstViewDay;
	  }
	  getLastDay() {
	    const numberOfMonths = this.getDatePicker().getNumberOfMonths();
	    const showOutsideDays = this.getDatePicker().shouldShowOutsideDays();
	    const {
	      year,
	      month
	    } = this.getDatePicker().getViewDateParts();
	    let lastAvailableDay = ceilDate(createUtcDate(year, month + numberOfMonths - 1), 'month');
	    if (showOutsideDays) {
	      const firstAvailableDay = createUtcDate(year, month + numberOfMonths - 1);
	      const firstViewDay = babelHelpers.classPrivateFieldLooseBase(this, _getStartMonthDate)[_getStartMonthDate](firstAvailableDay);
	      lastAvailableDay = addDate(firstViewDay, 'day', 6 * 7);
	    }
	    return lastAvailableDay;
	  }
	}
	function _renderMonthContainer2(monthNumber) {
	  const cacheId = `month-${monthNumber}`;
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].has(cacheId)) {
	    const monthContainer = main_core.Tag.render(_t13 || (_t13 = _2`<div class="ui-day-picker-month"></div>`));
	    babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].set(cacheId, monthContainer);
	    main_core.Dom.append(monthContainer, this.getMonthContainer());
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].get(cacheId);
	}
	function _renderMonthHeader2(monthNumber, monthContainer) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(`month-header-${monthNumber}`, () => {
	    const monthName = main_date.DateTimeFormat.format('f', createUtcDate(2000, monthNumber), null, true);
	    const container = main_core.Tag.render(_t14 || (_t14 = _2`<div class="ui-day-picker-month-header">${0}</div>`), main_core.Text.encode(monthName));
	    main_core.Dom.append(container, monthContainer);
	    return container;
	  });
	}
	function _renderWeekDays2(monthNumber, monthContainer) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(`week-day-${monthNumber}`, () => {
	    const weekDayContainer = main_core.Tag.render(_t15 || (_t15 = _2`<div class="ui-day-picker-week --week-days"></div>`));
	    main_core.Dom.append(weekDayContainer, monthContainer);
	    if (this.getDatePicker().shouldShowWeekNumbers()) {
	      const dayContainer = main_core.Tag.render(_t16 || (_t16 = _2`<div class="ui-day-picker-week-day"></div>`));
	      main_core.Dom.append(dayContainer, weekDayContainer);
	    }
	    this.getWeekDays().forEach(weekDayName => {
	      const dayContainer = main_core.Tag.render(_t17 || (_t17 = _2`<div class="ui-day-picker-week-day">${0}</div>`), main_core.Text.encode(weekDayName));
	      main_core.Dom.append(dayContainer, weekDayContainer);
	    });
	    return weekDayContainer;
	  });
	}
	function _renderWeek2(monthNumber, weekNumber, monthContainer) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(`week-${monthNumber}-${weekNumber}`, () => {
	    const weekContainer = main_core.Tag.render(_t18 || (_t18 = _2`<div class="ui-day-picker-week"></div>`));
	    main_core.Dom.append(weekContainer, monthContainer);
	    return weekContainer;
	  });
	}
	function _renderWeekNumber2(monthNumber, weekNumber, week, weekContainer) {
	  const container = babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(`week-number-${monthNumber}-${weekNumber}`, () => {
	    const weekNumberContainer = main_core.Tag.render(_t19 || (_t19 = _2`<div class="ui-day-picker-week-number">${0}</div>`), main_date.DateTimeFormat.format('W', week[0].date, null, true));
	    main_core.Dom.append(weekNumberContainer, weekContainer);
	    return weekNumberContainer;
	  });
	  container.textContent = main_date.DateTimeFormat.format('W', week[0].date, null, true);
	}
	function _renderDay2(id, day, weekContainer) {
	  const button = babelHelpers.classPrivateFieldLooseBase(this, _refs$1)[_refs$1].remember(id, () => {
	    const dayContainer = main_core.Tag.render(_t20 || (_t20 = _2`
				<button 
					type="button"
					class="ui-day-picker-day"
					data-day="${0}"
					data-month="${0}"
					data-year="${0}"
					data-tab-priority="true"
					role="gridcell"
				>
					<span class="ui-day-picker-day-inner">${0}</span>
					<span class="ui-day-picker-day-marks"></span>
				</button>
			`), day.day, day.month, day.year, day.day);
	    main_core.Dom.append(dayContainer, weekContainer);
	    return dayContainer;
	  });
	  const currentDay = Number(button.dataset.day);
	  const currentMonth = Number(button.dataset.month);
	  const currentYear = Number(button.dataset.year);
	  if (currentDay !== day.day || currentMonth !== day.month || currentYear !== day.year) {
	    button.dataset.day = day.day;
	    button.dataset.month = day.month;
	    button.dataset.year = day.year;
	    button.firstElementChild.textContent = day.day;
	  }
	  const statuses = {
	    '--outside': day.outside,
	    '--current': !day.outside && day.current,
	    '--day-off': !day.outside && day.dayOff,
	    '--selected': day.selected,
	    '--hidden': day.hidden,
	    '--range-from': day.rangeFrom,
	    '--range-to': day.rangeTo,
	    '--range-in': day.rangeIn,
	    '--range-in-start': day.rangeInStart,
	    '--range-in-end': day.rangeInEnd,
	    '--range-in-selected': day.rangeInSelected,
	    '--range-selected': day.rangeSelected,
	    '--focused': day.focused
	  };
	  let classNames = 'ui-day-picker-day';
	  for (const [className, enabled] of Object.entries(statuses)) {
	    if (enabled) {
	      classNames = `${classNames} ${className}`;
	    }
	  }
	  if (button.className !== classNames) {
	    button.className = classNames;
	  }

	  // Day Colors
	  const currentBgColor = button.dataset.bgColor || null;
	  const currentTextColor = button.dataset.textColor || null;
	  if (currentBgColor !== day.bgColor) {
	    main_core.Dom.style(button.firstElementChild, '--ui-day-picker-day-bg-color', day.bgColor);
	    main_core.Dom.attr(button, 'data-bg-color', day.bgColor);
	  }
	  if (currentTextColor !== day.textColor) {
	    main_core.Dom.style(button.firstElementChild, '--ui-day-picker-day-text-color', day.textColor);
	    main_core.Dom.attr(button, 'data-text-color', day.textColor);
	  }

	  // Day Marks
	  const currentMarks = button.dataset.marks || '';
	  if (currentMarks !== day.marks.toString()) {
	    main_core.Dom.clean(button.lastElementChild);
	    if (day.marks.length > 0) {
	      for (const mark of day.marks) {
	        main_core.Dom.append(main_core.Tag.render(_t21 || (_t21 = _2`
							<span class="ui-day-picker-day-mark" style="background-color: ${0}"></span>
						`), mark), button.lastElementChild);
	      }
	    }
	    main_core.Dom.attr(button, 'data-marks', day.marks.toString());
	  }
	  button.tabIndex = day.tabIndex;
	  return button;
	}
	function _renderTime2() {
	  if (this.getDatePicker().isRangeMode()) {
	    const rangeStart = this.getDatePicker().getRangeStart();
	    const startBtn = this.getTimeRangeStartContainer().parentNode;
	    if (rangeStart === null) {
	      main_core.Dom.removeClass(this.getTimeRangeContainer(), '--range-start-set');
	      startBtn.disabled = true;
	    } else {
	      main_core.Dom.addClass(this.getTimeRangeContainer(), '--range-start-set');
	      startBtn.disabled = false;
	      this.getTimeRangeStartContainer().textContent = this.getDatePicker().formatTime(rangeStart);
	    }
	    const rangeEnd = this.getDatePicker().getRangeEnd();
	    const endBtn = this.getTimeRangeEndContainer().parentNode;
	    if (rangeEnd === null) {
	      main_core.Dom.removeClass(this.getTimeRangeContainer(), '--range-end-set');
	      endBtn.disabled = true;
	    } else {
	      main_core.Dom.addClass(this.getTimeRangeContainer(), '--range-end-set');
	      endBtn.disabled = false;
	      this.getTimeRangeEndContainer().textContent = this.getDatePicker().formatTime(rangeEnd);
	    }
	  } else {
	    const selectedDate = this.getDatePicker().getSelectedDate();
	    const button = this.getTimeContainer().firstElementChild;
	    if (selectedDate === null) {
	      main_core.Dom.removeClass(this.getTimeContainer(), '--time-set');
	      button.disabled = true;
	    } else {
	      main_core.Dom.addClass(this.getTimeContainer(), '--time-set');
	      button.disabled = false;
	      this.getTimeValueContainer().textContent = this.getDatePicker().formatTime(selectedDate);
	    }
	  }
	}
	function _getStartMonthDate2(date) {
	  const picker = this.getDatePicker();
	  const firstWeekDay = picker.getFirstWeekDay();
	  const firstMonthDay = floorDate(date, 'month');
	  let daysFromPrevMonth = firstMonthDay.getUTCDay() - firstWeekDay;
	  daysFromPrevMonth = daysFromPrevMonth < 0 ? daysFromPrevMonth + 7 : daysFromPrevMonth;
	  return addDate(firstMonthDay, 'day', -daysFromPrevMonth);
	}
	function _getRangeDates2() {
	  let from = null;
	  let to = null;
	  const focusDate = this.getDatePicker().getFocusDate();
	  if (this.getDatePicker().isRangeMode()) {
	    const range = this.getDatePicker().getSelectedDates();
	    from = range[0] || null;
	    to = range[1] || null;
	    if (focusDate !== null) {
	      if (range.length === 1) {
	        if (focusDate > from.getTime()) {
	          to = focusDate;
	        } else {
	          to = from;
	          from = focusDate;
	        }
	      }
	      /* else if (range.length === 2)
	      {
	      	if (focusDate > to.getTime())
	      	{
	      		to = focusDate;
	      	}
	      	else if (focusDate < from.getTime())
	      	{
	      		from = focusDate;
	      	}
	      } */
	    }
	  }

	  return [from, to];
	}
	function _handleDayClick2(event) {
	  const dayElement = event.target.closest('.ui-day-picker-day');
	  if (dayElement === null) {
	    return;
	  }
	  const dataset = dayElement.dataset;
	  const year = main_core.Text.toInteger(dataset.year);
	  const month = main_core.Text.toInteger(dataset.month);
	  const day = main_core.Text.toInteger(dataset.day);
	  this.emit('onSelect', {
	    year,
	    month,
	    day
	  });
	}
	function _handleDayMouseOver2(event) {
	  const dayElement = event.target.closest('.ui-day-picker-day');
	  if (dayElement === null) {
	    const weekElement = event.target.closest('.ui-day-picker-week');
	    if (weekElement !== null && babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout] !== null && this.getDatePicker().getSelectedDates().length === 1) {
	      clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout]);
	    }
	    return;
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout] !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout]);
	  }
	  const dataset = dayElement.dataset;
	  const year = main_core.Text.toInteger(dataset.year);
	  const month = main_core.Text.toInteger(dataset.month);
	  const day = main_core.Text.toInteger(dataset.day);
	  this.emit('onFocus', {
	    year,
	    month,
	    day
	  });
	}
	function _handleDayMouseOut2(event) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout] !== null) {
	    clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout]);
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout] = setTimeout(() => {
	    this.emit('onBlur');
	    babelHelpers.classPrivateFieldLooseBase(this, _mouseOutTimeout)[_mouseOutTimeout] = null;
	  }, 100);
	}
	function _handleMonthClick2() {
	  this.emit('onMonthClick');
	}
	function _handleYearClick2() {
	  this.emit('onYearClick');
	}
	function _handleTimeClick2() {
	  const selectedDate = this.getDatePicker().getSelectedDate();
	  if (selectedDate !== null) {
	    this.emit('onTimeClick');
	  }
	}
	function _handleTimeRangeStartClick2() {
	  const rangeStart = this.getDatePicker().getRangeStart();
	  if (rangeStart !== null) {
	    this.emit('onRangeStartClick');
	  }
	}
	function _handleTimeRangeEndClick2() {
	  const rangeEnd = this.getDatePicker().getRangeEnd();
	  if (rangeEnd !== null) {
	    this.emit('onRangeEndClick');
	  }
	}

	const DatePickerEvent = {
	  SELECT_CHANGE: 'onSelectChange',
	  BEFORE_SELECT: 'onBeforeSelect',
	  SELECT: 'onSelect',
	  BEFORE_DESELECT: 'onBeforeDeselect',
	  DESELECT: 'onDeselect',
	  DESTROY: 'onDestroy'
	};

	function isDateAfter(date, dateToCompare) {
	  return date.getTime() > dateToCompare.getTime();
	}

	function isDateBefore(date, dateToCompare) {
	  return date.getTime() < dateToCompare.getTime();
	}

	function copyTime(from, to) {
	  to.setUTCHours(from.getUTCHours());
	  to.setUTCMinutes(from.getUTCMinutes());
	  to.setUTCSeconds(from.getUTCSeconds());
	}

	function addToRange(date, range = []) {
	  const [from = null, to = null] = main_core.Type.isArray(range) ? range : [];
	  if (from !== null && to !== null) {
	    if (isDatesEqual(to, date) && isDatesEqual(from, date)) {
	      return [];
	    }
	    if (isDatesEqual(to, date)) {
	      return [to];
	    }
	    if (isDatesEqual(from, date)) {
	      // return [from];
	      return [];
	    }
	    if (isDateAfter(from, date)) {
	      copyTime(from, date);
	      return [date, to];
	    }
	    copyTime(to, date);
	    return [from, date];
	  }
	  if (to !== null) {
	    if (isDateAfter(date, to)) {
	      return [to, date];
	    }
	    return [date, to];
	  }
	  if (from !== null) {
	    if (isDateBefore(date, from)) {
	      return [date, from];
	    }
	    return [from, date];
	  }
	  return [date];
	}

	const replacements = {
	  Y: 'YYYY',
	  // 1999
	  M: 'MMM',
	  // Jan - Dec
	  f: 'MMMM',
	  // January - December
	  m: 'MM',
	  // 01 - 12
	  d: 'DD',
	  // 01 - 31
	  A: 'TT',
	  // AM - PM
	  a: 'T',
	  // am - pm
	  i: 'MI',
	  // 00 - 59
	  s: 'SS',
	  // 00 - 59
	  H: 'HH',
	  // 00 - 24
	  h: 'H',
	  // 01 - 12
	  G: 'GG',
	  // 0 - 24
	  g: 'G',
	  // 1 - 12
	  j: 'DD',
	  // 1 to 31
	  n: 'MM' // 1 to 31
	};

	function convertToDbFormat(format) {
	  let result = format;
	  for (const [from, to] of Object.entries(replacements)) {
	    result = result.replace(from, to);
	  }
	  return result;
	}

	// const tests = {
	// 	'Y-m-d H:i': 'YYYY-MM-DD HH:MI:SS',
	// 	'Y/m/d G:i': 'YYYY/MM/DD HH:MI:SS',
	// 	'd-m-Y H:i': 'DD/MM/YYYY HH:MI:SS',
	// 	'd.m.Y H:i': 'DD.MM.YYYY HH:MI:SS',
	// 	'd/m/Y H:i': 'DD/MM/YYYY HH:MI:SS',
	// 	'd/m/Y H:i \น\.': 'DD/MM/YYYY HH:MI:SS',
	// 	'd/m/Y g:i a': 'DD/MM/YYYY H:MI:SS T',
	// 	'd/m/Y g:i a': 'DD/MM/YYYY HH:MI:SS',
	// 	'j.m.Y H:i': 'DD.MM.YYYY HH:MI:SS',
	// 	'j/n/Y G:i': 'DD.MM.YYYY HH:MI:SS',
	// 	'j/n/Y G:i': 'DD/MM/YYYY HH:MI:SS',
	// 	'j/n/Y H:i': 'DD/MM/YYYY HH:MI:SS',
	// 	'j/n/Y g:i a': 'DD/MM/YYYY HH:MI:SS', //
	// 	'j/n/Y g:i a': 'DD/MM/YYYY H:MI:SS T', // co
	// 	'n/j/Y g:i a': 'MM/DD/YYYY H:MI:SS T',
	// 	// 'n/j/Y g:i a': 'DD-MM-YYYY H:MI:SS T', // hi
	// };

	const WORD_REGEX = /[^\p{L}\p{N}\u0600-\u06FF_]/u;
	const YEAR_REGEX = /^[1-9]\d{3}$/;
	const DAY_REGEX = /^(0?[1-9]|[12]\d|3[01])$/;
	const MONTH_REGEX = /^(0?[1-9]|1[0-2])$/;
	const HOURS24_REGEX = /^(\d|0\d|1\d|2[0-3])$/;
	// const HOURS12_REGEX = /^(1[0-2]|0?[1-9])$/;
	const MINUTES_REGEX = /^(\d|[0-5]\d)$/;
	const SECONDS_REGEX = /^(\d|[0-5]\d)$/;
	function parseDate(dateValue, format) {
	  const tokens = format.split(WORD_REGEX);
	  const values = dateValue.split(WORD_REGEX);
	  const parts = {};
	  const errors = new Map();
	  for (const [i, token] of tokens.entries()) {
	    const valuePart = getDatePart(token, values[i]);
	    if (valuePart !== null) {
	      const [part, value, initialValue] = valuePart;
	      if (value === 'error') {
	        errors.set(part, initialValue);
	        continue;
	      }
	      parts[part] = value;
	    }
	  }
	  const hasDay = main_core.Type.isNumber(parts.day);
	  const hasMonth = main_core.Type.isNumber(parts.month);
	  const hasYear = main_core.Type.isNumber(parts.year);
	  if (errors.size > 0) {
	    const hasDate = hasYear && hasMonth && hasDay;
	    const emptyTime = errors.has('hours') && errors.has('minutes') && main_core.Type.isUndefined(errors.get('hours')) && main_core.Type.isUndefined(errors.get('minutes')) && (errors.has('seconds') && main_core.Type.isUndefined(errors.get('seconds')) || !errors.has('seconds'));
	    if (!hasDate || !emptyTime) {
	      return null;
	    }
	  }
	  const today = createDate(new Date());
	  const {
	    day: currentDay,
	    month: currentMonth,
	    year: currentYear
	  } = getDate(today);
	  const defaultYear = currentYear;
	  const defaultMonth = hasYear ? 0 : currentMonth;
	  const defaultDay = hasYear || hasMonth ? 1 : currentDay;
	  const {
	    meridiem
	  } = parts;
	  const is12Hours = tokens.includes('H') || tokens.includes('G');
	  const isPM = main_core.Type.isStringFilled(meridiem) && meridiem.toLowerCase() === 'pm';
	  let {
	    hours
	  } = parts;
	  if (is12Hours) {
	    if (isPM) {
	      hours += hours === 12 ? 0 : 12;
	    } else {
	      hours = hours < 12 ? hours : 0;
	    }
	  }
	  const {
	    year = defaultYear,
	    month = defaultMonth,
	    day = defaultDay,
	    minutes = 0,
	    seconds = 0
	  } = parts;
	  return createUtcDate(year, month, day, hours, minutes, seconds);
	}
	function getDatePart(token, value) {
	  // DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G
	  switch (token) {
	    case 'YYYY':
	      {
	        if (!YEAR_REGEX.test(value)) {
	          return ['year', 'error', value];
	        }
	        const year = main_core.Text.toInteger(value);
	        return ['year', year, value];
	      }
	    case 'MMMM':
	    case 'MMM':
	      {
	        const monthIndex = main_date.DateTimeFormat.getMonthIndex(value);
	        if (main_core.Type.isNumber(monthIndex)) {
	          return ['month', monthIndex - 1, value];
	        }
	        return ['month', 'error', value];
	      }
	    case 'MM':
	    case 'M':
	      {
	        if (!MONTH_REGEX.test(value)) {
	          return ['month', 'error', value];
	        }
	        const monthIndex = main_core.Text.toInteger(value);
	        return ['month', monthIndex === 0 ? monthIndex : Math.min(Math.max(monthIndex, 1), 12) - 1, value];
	      }
	    case 'DD':
	    case 'D':
	      {
	        if (!DAY_REGEX.test(value)) {
	          return ['day', 'error', value];
	        }
	        const day = main_core.Text.toInteger(value);
	        return ['day', Math.min(Math.max(day, 1), 31), value];
	      }
	    case 'HH':
	    case 'GG':
	      {
	        if (!HOURS24_REGEX.test(value)) {
	          return ['hours', 'error', value];
	        }
	        const hours = main_core.Text.toInteger(value);
	        return ['hours', Math.min(Math.max(hours, 0), 23), value];
	      }
	    case 'H':
	    case 'G':
	      {
	        if (!HOURS24_REGEX.test(value)) {
	          return ['hours', 'error', value];
	        }
	        const hours = main_core.Text.toInteger(value);
	        return ['hours', hours > 12 ? hours - 12 : hours, value];
	      }
	    case 'MI':
	      {
	        if (!MINUTES_REGEX.test(value)) {
	          return ['minutes', 'error', value];
	        }
	        const minutes = main_core.Text.toInteger(value);
	        return ['minutes', Math.min(Math.max(minutes, 0), 59), value];
	      }
	    case 'SS':
	      {
	        if (main_core.Type.isStringFilled(value) && ['am', 'pm'].includes(value.toLowerCase())) {
	          return ['meridiem', value, value];
	        }
	        if (main_core.Type.isStringFilled(value) && !SECONDS_REGEX.test(value)) {
	          return ['seconds', 'error', value];
	        }
	        const seconds = main_core.Text.toInteger(value);
	        return ['seconds', Math.min(Math.max(seconds, 0), 59), value];
	      }
	    case 'T':
	    case 'TT':
	      if (main_core.Type.isStringFilled(value)) {
	        return ['meridiem', value, value];
	      }
	      return null;
	    default:
	      return null;
	  }
	}

	function createDate(value, formatDate = null) {
	  let date = null;
	  if (main_core.Type.isStringFilled(value) && main_core.Type.isStringFilled(formatDate)) {
	    date = parseDate(value, convertToDbFormat(formatDate));
	  } else if (main_core.Type.isNumber(value)) {
	    date = new Date(value);
	    date = createUTC(date);
	  } else if (main_core.Type.isDate(value)) {
	    date = value.__utc ? value : createUTC(value);
	  }
	  if (date === null) {
	    console.warn(`DatePicker: invalid date or format (${value}).`);
	  } else {
	    date.__utc = true;
	  }
	  return date;
	}
	function createUTC(date) {
	  return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), 0));
	}

	const FOCUSABLE_ELEMENTS_SELECTOR = ['button:not([disabled])', '[tabindex]:not([tabindex="-1"]):not([disabled])'].join(', ');
	function isElementFocused(element) {
	  return element.ownerDocument.activeElement === element;
	}
	function getFocusableBoundaryElements(element, matcher = null) {
	  const matcherFn = main_core.Type.isFunction(matcher) ? matcher : () => true;
	  const elements = [...element.querySelectorAll(FOCUSABLE_ELEMENTS_SELECTOR)].filter(el => {
	    return el.tabIndex !== -1 && matcherFn(el);
	  });
	  if (elements.length === 0) {
	    return [];
	  }
	  if (elements.length === 1) {
	    return [elements[0], elements[0]];
	  }
	  let next = elements.at(0);
	  let prev = elements.at(-1);
	  for (const [index, currentElement] of elements.entries()) {
	    if (isElementFocused(currentElement)) {
	      prev = index > 0 ? elements[index - 1] : elements.at(-1);
	      next = main_core.Type.isUndefined(elements[index + 1]) ? elements.at(0) : elements[index + 1];
	      break;
	    }
	  }
	  return [prev, next];
	}

	function isDateLike(date) {
	  return main_core.Type.isStringFilled(date) || main_core.Type.isNumber(date) || main_core.Type.isDate(date);
	}

	function setTime(date, hours = 0, minutes = 0, seconds = 0) {
	  const newDate = cloneDate(date);
	  if (hours !== null) {
	    newDate.setUTCHours(hours);
	  }
	  if (minutes !== null) {
	    newDate.setUTCMinutes(minutes);
	  }
	  if (seconds !== null) {
	    newDate.setUTCSeconds(seconds);
	  }
	  return newDate;
	}

	function isDateMatch(day, matchers) {
	  return matchers.some(matcher => {
	    if (main_core.Type.isFunction(matcher)) {
	      return matcher(day);
	    }
	    if (main_core.Type.isDate(matcher)) {
	      return isDatesEqual(day, matcher);
	    }
	    if (main_core.Type.isArray(matcher)) {
	      return matcher.some(date => {
	        return isDatesEqual(day, date);
	      });
	    }
	    if (main_core.Type.isBoolean(matcher)) {
	      return matcher;
	    }
	    return false;
	  });
	}

	const keyMap = {
	  ArrowRight: {
	    day: 1,
	    month: 1,
	    year: 1,
	    hours: 1,
	    minutes: 1
	  },
	  ArrowLeft: {
	    day: -1,
	    month: -1,
	    year: -1,
	    hours: -1,
	    minutes: -1
	  },
	  ArrowUp: {
	    day: -7,
	    month: -3,
	    year: -3,
	    hours: -4,
	    minutes: -2
	  },
	  ArrowDown: {
	    day: 7,
	    month: 3,
	    year: 3,
	    hours: 4,
	    minutes: 2
	  }
	};
	var _datePicker$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("datePicker");
	var _lastFocusElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastFocusElement");
	var _handleKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleKeyDown");
	var _isRootContainerFocused = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRootContainerFocused");
	var _handleFocusChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFocusChange");
	var _adjustLastFocusElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustLastFocusElement");
	var _handleFocusIn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFocusIn");
	var _handleFocusOut = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFocusOut");
	class KeyboardNavigation {
	  constructor(datePicker) {
	    Object.defineProperty(this, _handleFocusOut, {
	      value: _handleFocusOut2
	    });
	    Object.defineProperty(this, _handleFocusIn, {
	      value: _handleFocusIn2
	    });
	    Object.defineProperty(this, _adjustLastFocusElement, {
	      value: _adjustLastFocusElement2
	    });
	    Object.defineProperty(this, _handleFocusChange, {
	      value: _handleFocusChange2
	    });
	    Object.defineProperty(this, _isRootContainerFocused, {
	      value: _isRootContainerFocused2
	    });
	    Object.defineProperty(this, _handleKeyDown, {
	      value: _handleKeyDown2
	    });
	    Object.defineProperty(this, _datePicker$1, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _lastFocusElement, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1] = datePicker;
	  }
	  init() {
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getContainer(), 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _handleKeyDown)[_handleKeyDown].bind(this));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getContainer(), 'focusin', babelHelpers.classPrivateFieldLooseBase(this, _handleFocusIn)[_handleFocusIn].bind(this));
	    main_core.Event.bind(babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getContainer(), 'focusout', babelHelpers.classPrivateFieldLooseBase(this, _handleFocusOut)[_handleFocusOut].bind(this));
	  }
	  setLastFocusElement(element) {
	    this.resetLastFocusElement();
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFocusElement)[_lastFocusElement] = element;
	    main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _lastFocusElement)[_lastFocusElement], '--focus-visible');
	  }
	  resetLastFocusElement() {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _lastFocusElement)[_lastFocusElement], '--focus-visible');
	    babelHelpers.classPrivateFieldLooseBase(this, _lastFocusElement)[_lastFocusElement] = null;
	  }
	}
	function _handleKeyDown2(event) {
	  const picker = babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1];
	  if (event.key === 'Backspace' && picker.getType() === 'date' && ['year', 'month', 'time'].includes(picker.getCurrentView())) {
	    event.preventDefault();
	    this.resetLastFocusElement();
	    picker.setCurrentView('day');
	    return;
	  }
	  if (event.key === 'Tab' && !picker.isInline()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _handleFocusChange)[_handleFocusChange](event);
	    return;
	  }
	  const view = picker.getCurrentView();
	  if (view === 'time' && picker.getTimePickerStyle() === 'wheel') {
	    return;
	  }
	  if (event.key === 'Space' || event.key === 'Enter' || event.key === ' ') {
	    event.preventDefault();
	    this.resetLastFocusElement();
	    event.target.click();
	  } else if (!main_core.Type.isUndefined(keyMap[event.key])) {
	    event.preventDefault();
	    this.resetLastFocusElement();
	    const initialFocus = picker.getFocusDate() === null && babelHelpers.classPrivateFieldLooseBase(this, _isRootContainerFocused)[_isRootContainerFocused]();
	    if (view === 'time') {
	      const timePicker = babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getPicker('time');
	      let currentFocusDate = cloneDate(picker.getInitialFocusDate(timePicker.getMode()));
	      let {
	        hours,
	        minutes
	      } = getDate(currentFocusDate);
	      if (initialFocus) {
	        picker.setFocusDate(currentFocusDate);
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustLastFocusElement)[_adjustLastFocusElement]();
	      } else if (timePicker.getFocusColumn() === 'hours') {
	        const increment = keyMap[event.key].hours;
	        hours += increment;
	        if (hours < 0) {
	          hours += 24;
	        } else if (hours > 23) {
	          hours -= 24;
	        }
	        currentFocusDate = setTime(currentFocusDate, hours, null, null);
	        picker.setFocusDate(currentFocusDate);
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustLastFocusElement)[_adjustLastFocusElement]();
	      } else if (timePicker.getFocusColumn() === 'minutes') {
	        const increment = keyMap[event.key].minutes;
	        minutes += timePicker.getCurrentMinuteStep() * increment;
	        if (minutes < 0) {
	          minutes += 60;
	        } else if (minutes > 59) {
	          minutes -= 60;
	        }
	        currentFocusDate = setTime(currentFocusDate, null, minutes, null);
	        picker.setFocusDate(currentFocusDate);
	        timePicker.adjustMinuteFocusPosition();
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustLastFocusElement)[_adjustLastFocusElement]();
	      }
	    } else {
	      const currentFocusDate = cloneDate(picker.getInitialFocusDate());
	      if (initialFocus) {
	        picker.setFocusDate(currentFocusDate);
	      } else {
	        const increment = keyMap[event.key][view];
	        const focusDate = addDate(currentFocusDate, view, increment);
	        picker.setFocusDate(focusDate);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustLastFocusElement)[_adjustLastFocusElement]();
	    }
	  }
	}
	function _isRootContainerFocused2() {
	  const rootContainer = babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getContainer();
	  return rootContainer.ownerDocument.activeElement === rootContainer;
	}
	function _handleFocusChange2(event) {
	  let prev = null;
	  let next = null;
	  const currentPickerContainer = babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getPicker().getContainer();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _isRootContainerFocused)[_isRootContainerFocused]()) {
	    [prev = null, next = null] = getFocusableBoundaryElements(currentPickerContainer, element => element.dataset.tabPriority === 'true');
	  }
	  if (prev === null && next === null) {
	    [prev, next] = getFocusableBoundaryElements(currentPickerContainer);
	  }
	  if (event.shiftKey) {
	    var _prev;
	    (_prev = prev) == null ? void 0 : _prev.focus({
	      preventScroll: true,
	      focusVisible: true
	    });
	    this.setLastFocusElement(prev);
	  } else {
	    var _next;
	    (_next = next) == null ? void 0 : _next.focus({
	      preventScroll: true,
	      focusVisible: true
	    });
	    this.setLastFocusElement(next);
	  }
	  event.preventDefault();
	}
	function _adjustLastFocusElement2() {
	  const rootContainer = babelHelpers.classPrivateFieldLooseBase(this, _datePicker$1)[_datePicker$1].getContainer();
	  const activeElement = rootContainer.ownerDocument.activeElement;
	  if (rootContainer.contains(activeElement)) {
	    this.setLastFocusElement(activeElement);
	  }
	}
	function _handleFocusIn2(event) {
	  this.resetLastFocusElement();
	  // this.#lastFocusElement = event.target;
	}
	function _handleFocusOut2(event) {
	  this.resetLastFocusElement();
	  // this.#lastFocusElement = event.target;
	}

	let _$1 = t => t,
	  _t$2,
	  _t2$2,
	  _t3$2,
	  _t4$2;
	var _refs$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _renderQuarter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderQuarter");
	var _renderMonth = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMonth");
	var _handleMouseEnter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMouseEnter");
	var _handleMouseLeave = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMouseLeave");
	var _handleMonthClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMonthClick");
	var _handleTitleClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTitleClick");
	class MonthPicker extends BasePicker {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _handleTitleClick, {
	      value: _handleTitleClick2
	    });
	    Object.defineProperty(this, _handleMonthClick$1, {
	      value: _handleMonthClick2$1
	    });
	    Object.defineProperty(this, _handleMouseLeave, {
	      value: _handleMouseLeave2
	    });
	    Object.defineProperty(this, _handleMouseEnter, {
	      value: _handleMouseEnter2
	    });
	    Object.defineProperty(this, _renderMonth, {
	      value: _renderMonth2
	    });
	    Object.defineProperty(this, _renderQuarter, {
	      value: _renderQuarter2
	    });
	    Object.defineProperty(this, _refs$2, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	  }
	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$2)[_refs$2].remember('container', () => {
	      return main_core.Tag.render(_t$2 || (_t$2 = _$1`
				<div class="ui-month-picker">
					${0}
					${0}
				</div>
			`), this.getHeaderContainer(this.getPrevBtn(), this.getHeaderTitle(), this.getNextBtn()), this.getContentContainer());
	    });
	  }
	  getHeaderTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$2)[_refs$2].remember('header-title', () => {
	      return main_core.Tag.render(_t2$2 || (_t2$2 = _$1`
				<button type="button" class="ui-month-picker-header-title" onclick="${0}"></button>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleTitleClick)[_handleTitleClick].bind(this));
	    });
	  }
	  getMonths() {
	    const {
	      year
	    } = getDate(this.getDatePicker().getViewDate());
	    const today = this.getDatePicker().getToday();
	    const focusDate = this.getDatePicker().getFocusDate();
	    const initialFocusDate = this.getDatePicker().getInitialFocusDate();
	    // const formatter = new Intl.DateTimeFormat(
	    // 	this.getDatePicker().getLocale(),
	    // 	{ month: 'short', timeZone: 'UTC' },
	    // );

	    const months = [];
	    let currentMonthIndex = 0;
	    for (let quarterIndex = 0; quarterIndex < 4; quarterIndex++) {
	      const quarter = [];
	      for (let monthIndex = 0; monthIndex < 3; monthIndex++) {
	        const date = createUtcDate(year, currentMonthIndex);
	        const focused = isDatesEqual(date, focusDate, 'month');
	        const month = {
	          name: main_date.DateTimeFormat.format('f', date, null, true),
	          // name: formatter.format(date),
	          date,
	          year,
	          month: currentMonthIndex,
	          current: isDatesEqual(date, today, 'month'),
	          selected: this.getDatePicker().isDateSelected(date, 'month'),
	          focused,
	          tabIndex: focused || isDatesEqual(date, initialFocusDate, 'month') ? 0 : -1
	        };
	        quarter.push(month);
	        currentMonthIndex++;
	      }
	      months.push(quarter);
	    }
	    return months;
	  }
	  renderTo(container) {
	    super.renderTo(container);
	    main_core.Event.bind(this.getContentContainer(), 'click', babelHelpers.classPrivateFieldLooseBase(this, _handleMonthClick$1)[_handleMonthClick$1].bind(this));
	  }
	  render() {
	    const isFocused = this.getDatePicker().isFocused();
	    let focusButton = null;
	    this.getMonths().forEach((quarter, index) => {
	      const quarterContainer = babelHelpers.classPrivateFieldLooseBase(this, _renderQuarter)[_renderQuarter](index);
	      quarter.forEach(month => {
	        const button = babelHelpers.classPrivateFieldLooseBase(this, _renderMonth)[_renderMonth](month, quarterContainer);
	        if (month.focused) {
	          focusButton = button;
	        }
	      });
	    });
	    if (focusButton !== null && isFocused) {
	      focusButton.focus({
	        preventScroll: true
	      });
	    }
	    const {
	      year: currentYear
	    } = getDate(this.getDatePicker().getViewDate());
	    this.getHeaderTitle().textContent = currentYear;
	  }
	}
	function _renderQuarter2(index) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _refs$2)[_refs$2].remember(`quarter-${index}`, () => {
	    const container = main_core.Tag.render(_t3$2 || (_t3$2 = _$1`<div class="ui-month-picker-quarter"></div>`));
	    main_core.Dom.append(container, this.getContentContainer());
	    return container;
	  });
	}
	function _renderMonth2(month, quarterContainer) {
	  const button = babelHelpers.classPrivateFieldLooseBase(this, _refs$2)[_refs$2].remember(`month-${month.month}`, () => {
	    const monthButton = main_core.Tag.render(_t4$2 || (_t4$2 = _$1`
				<button
					type="button"
					class="ui-month-picker-month"
					data-year="${0}"
					data-month="${0}"
					data-tab-priority="true"
					onmouseenter="${0}"
					onmouseleave="${0}"
				>${0}</button>
			`), month.year, month.month, babelHelpers.classPrivateFieldLooseBase(this, _handleMouseEnter)[_handleMouseEnter].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleMouseLeave)[_handleMouseLeave].bind(this), main_core.Text.encode(month.name));
	    main_core.Dom.append(monthButton, quarterContainer);
	    return monthButton;
	  });
	  const currentYear = Number(button.dataset.year);
	  if (currentYear !== month.year) {
	    button.dataset.year = month.year;
	  }
	  if (month.current) {
	    main_core.Dom.addClass(button, '--current');
	  } else {
	    main_core.Dom.removeClass(button, '--current');
	  }
	  if (month.selected) {
	    main_core.Dom.addClass(button, '--selected');
	  } else {
	    main_core.Dom.removeClass(button, '--selected');
	  }
	  if (month.focused) {
	    main_core.Dom.addClass(button, '--focused');
	  } else {
	    main_core.Dom.removeClass(button, '--focused');
	  }
	  button.tabIndex = month.tabIndex;
	  return button;
	}
	function _handleMouseEnter2(event) {
	  const dataset = event.target.dataset;
	  const year = main_core.Text.toInteger(dataset.year);
	  const month = main_core.Text.toInteger(dataset.month);
	  this.emit('onFocus', {
	    year,
	    month
	  });
	}
	function _handleMouseLeave2(event) {
	  this.emit('onBlur');
	}
	function _handleMonthClick2$1(event) {
	  if (!main_core.Dom.hasClass(event.target, 'ui-month-picker-month')) {
	    return;
	  }
	  const year = main_core.Text.toInteger(event.target.dataset.year);
	  const month = main_core.Text.toInteger(event.target.dataset.month);
	  this.emit('onSelect', {
	    year,
	    month
	  });
	}
	function _handleTitleClick2(event) {
	  this.emit('onTitleClick');
	}

	var _mode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("mode");
	var _currentMinuteStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentMinuteStep");
	var _focusColumn = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("focusColumn");
	class TimePickerBase extends BasePicker {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _mode, {
	      writable: true,
	      value: 'datetime'
	    });
	    Object.defineProperty(this, _currentMinuteStep, {
	      writable: true,
	      value: Infinity
	    });
	    Object.defineProperty(this, _focusColumn, {
	      writable: true,
	      value: 'hours'
	    });
	  }
	  getTimeDate() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] === 'range-start') {
	      return this.getDatePicker().getRangeStart();
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] === 'range-end') {
	      return this.getDatePicker().getRangeEnd();
	    }
	    return this.getDatePicker().getSelectedDate();
	  }
	  setMode(mode) {
	    babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode] = mode;
	  }
	  getMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _mode)[_mode];
	  }
	  getFocusColumn() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _focusColumn)[_focusColumn];
	  }
	  setFocusColumn(column) {
	    if (main_core.Type.isStringFilled(column) && ['hours', 'minutes'].includes(column)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _focusColumn)[_focusColumn] = column;
	    }
	  }
	  getHours() {
	    const selectedDate = this.getTimeDate();
	    const selectedHour = selectedDate === null ? -1 : selectedDate.getUTCHours();
	    const isAmPmMode = this.getDatePicker().isAmPmMode();
	    const focusDate = this.getDatePicker().getFocusDate();
	    const focusHour = focusDate === null ? selectedHour : focusDate.getUTCHours();
	    const initialFocusHour = this.getDatePicker().getInitialFocusDate(this.getMode()).getUTCHours();
	    const hours = [];
	    for (let hour = 0, index = 0; hour < 24; hour++, index++) {
	      let hourToDisplay = hour;
	      if (isAmPmMode) {
	        hourToDisplay %= 12;
	        hourToDisplay = hourToDisplay === 0 ? 12 : hourToDisplay;
	      }
	      hours.push({
	        index,
	        name: isAmPmMode ? hourToDisplay : String(hourToDisplay).padStart(2, '0'),
	        value: hour,
	        selected: selectedHour === hour,
	        focused: focusHour === hour && this.getFocusColumn() === 'hours',
	        tabIndex: focusHour === hour || initialFocusHour === hour ? 0 : -1
	      });
	    }
	    return hours;
	  }
	  getMinutes() {
	    const selectedDate = this.getTimeDate();
	    const selectedMinute = selectedDate === null ? -1 : selectedDate.getUTCMinutes();
	    const step = Math.min(this.getDatePicker().getMinuteStepByDate(selectedDate), babelHelpers.classPrivateFieldLooseBase(this, _currentMinuteStep)[_currentMinuteStep]);
	    const focusDate = this.getDatePicker().getFocusDate();
	    const focusMinute = focusDate === null ? selectedMinute : focusDate.getUTCMinutes();
	    const initialFocusMinute = this.getDatePicker().getInitialFocusDate(this.getMode()).getUTCMinutes();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentMinuteStep)[_currentMinuteStep] = step;
	    const minutes = [];
	    for (let minute = 0, index = 0; minute < 60; minute++) {
	      const hidden = minute % step !== 0;
	      minutes.push({
	        index,
	        name: String(minute).padStart(2, '0'),
	        value: minute,
	        selected: selectedMinute === minute,
	        hidden,
	        focused: !hidden && focusMinute === minute && this.getFocusColumn() === 'minutes',
	        tabIndex: !hidden && (focusMinute === minute || initialFocusMinute === minute) ? 0 : -1
	      });
	      if (!hidden) {
	        index++;
	      }
	    }
	    return minutes;
	  }
	  getMeridiems() {
	    const selectedDate = this.getTimeDate();
	    const selectedHour = selectedDate === null ? -1 : selectedDate.getUTCHours();
	    const isPm = selectedHour >= 12;
	    return [{
	      index: 0,
	      name: 'AM',
	      value: 'am',
	      selected: !isPm
	    }, {
	      index: 1,
	      name: 'PM',
	      value: 'pm',
	      selected: isPm
	    }];
	  }
	  getCurrentMinuteStep() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentMinuteStep)[_currentMinuteStep];
	  }
	  onHide() {
	    this.setFocusColumn('hours');
	  }
	  render() {
	    const picker = this.getDatePicker();
	    const timeDate = this.getTimeDate();
	    if (timeDate === null) {
	      this.getHeaderTitle().textContent = '';
	    } else {
	      this.getHeaderTitle().textContent = picker.getType() === 'time' ? picker.formatTime(timeDate) : picker.formatDate(timeDate);
	    }
	  }
	}

	let _$2 = t => t,
	  _t$3,
	  _t2$3,
	  _t3$3,
	  _t4$3,
	  _t5$1,
	  _t6$1,
	  _t7$1,
	  _t8$1,
	  _t9$1,
	  _t10$1;
	var _refs$3 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _focusSelectorId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("focusSelectorId");
	var _selectorScrollHandler = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectorScrollHandler");
	var _renderHour = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHour");
	var _renderMinute = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMinute");
	var _renderMeridiem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMeridiem");
	var _adjustScrollHeight = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustScrollHeight");
	var _adjustScrollPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustScrollPosition");
	var _handleItemClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleItemClick");
	var _handleTitleClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTitleClick");
	var _handleSelectorMouseEnter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSelectorMouseEnter");
	var _handleFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFocus");
	var _handleSelectorScroll = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleSelectorScroll");
	var _selectTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectTime");
	class TimePickerWheel extends TimePickerBase {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _selectTime, {
	      value: _selectTime2
	    });
	    Object.defineProperty(this, _handleSelectorScroll, {
	      value: _handleSelectorScroll2
	    });
	    Object.defineProperty(this, _handleFocus, {
	      value: _handleFocus2
	    });
	    Object.defineProperty(this, _handleSelectorMouseEnter, {
	      value: _handleSelectorMouseEnter2
	    });
	    Object.defineProperty(this, _handleTitleClick$1, {
	      value: _handleTitleClick2$1
	    });
	    Object.defineProperty(this, _handleItemClick, {
	      value: _handleItemClick2
	    });
	    Object.defineProperty(this, _adjustScrollPosition, {
	      value: _adjustScrollPosition2
	    });
	    Object.defineProperty(this, _adjustScrollHeight, {
	      value: _adjustScrollHeight2
	    });
	    Object.defineProperty(this, _renderMeridiem, {
	      value: _renderMeridiem2
	    });
	    Object.defineProperty(this, _renderMinute, {
	      value: _renderMinute2
	    });
	    Object.defineProperty(this, _renderHour, {
	      value: _renderHour2
	    });
	    Object.defineProperty(this, _refs$3, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	    Object.defineProperty(this, _focusSelectorId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _selectorScrollHandler, {
	      writable: true,
	      value: main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _handleSelectorScroll)[_handleSelectorScroll], 200, this)
	    });
	  }
	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember('container', () => {
	      return main_core.Tag.render(_t$3 || (_t$3 = _$2`
				<div class="ui-time-picker">
					${0}
					<div class="ui-time-picker-content">
						${0}
						<div 
							class="ui-time-picker-selector"
							data-selector-id="hour" 
							onmouseenter="${0}"
						>
							<div class="ui-time-picker-selector-title">${0}</div>
							<div class="ui-time-picker-viewport">
								<div class="ui-time-picker-scroll-container" 
									tabindex="0" 
									onscroll="${0}"
									onfocus="${0}"
								>
									${0}
								</div>
							</div>
						</div>
						<div class="ui-time-picker-time-separator"></div>
						<div 
							class="ui-time-picker-selector"
							data-selector-id="minute" 
							onmouseenter="${0}"
						>
							<div class="ui-time-picker-selector-title">${0}</div>
							<div class="ui-time-picker-viewport">
								<div class="ui-time-picker-scroll-container" 
									tabindex="0" 
									onscroll="${0}"
									onfocus="${0}"
								>
									${0}
								</div>
							</div>
						</div>
						${0}
					</div>
				</div>
			`), this.getDatePicker().getType() === 'time' ? null : this.getHeaderContainer(this.getPrevBtn(), this.getHeaderTitle()), this.getTimeHighlighter(), babelHelpers.classPrivateFieldLooseBase(this, _handleSelectorMouseEnter)[_handleSelectorMouseEnter].bind(this), main_core.Loc.getMessage('UI_DATE_PICKER_HOURS'), babelHelpers.classPrivateFieldLooseBase(this, _selectorScrollHandler)[_selectorScrollHandler], babelHelpers.classPrivateFieldLooseBase(this, _handleFocus)[_handleFocus].bind(this), this.getHoursContainer(), babelHelpers.classPrivateFieldLooseBase(this, _handleSelectorMouseEnter)[_handleSelectorMouseEnter].bind(this), main_core.Loc.getMessage('UI_DATE_PICKER_MINUTES'), babelHelpers.classPrivateFieldLooseBase(this, _selectorScrollHandler)[_selectorScrollHandler], babelHelpers.classPrivateFieldLooseBase(this, _handleFocus)[_handleFocus].bind(this), this.getMinutesContainer(), this.getDatePicker().isAmPmMode() ? main_core.Tag.render(_t2$3 || (_t2$3 = _$2`
									<div 
										class="ui-time-picker-selector" 
										onmouseenter="${0}"
										data-selector-id="meridiem"
									>
										<div class="ui-time-picker-selector-title">AM/PM</div>
										<div class="ui-time-picker-viewport">
											<div class="ui-time-picker-scroll-container" 
												tabindex="0" 
												onscroll="${0}"
												onfocus="${0}"
											>
												${0}
											</div>
										</div>
									</div>
								`), babelHelpers.classPrivateFieldLooseBase(this, _handleSelectorMouseEnter)[_handleSelectorMouseEnter].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _selectorScrollHandler)[_selectorScrollHandler], babelHelpers.classPrivateFieldLooseBase(this, _handleFocus)[_handleFocus].bind(this), this.getMeridiemsContainer()) : null);
	    });
	  }
	  getHeaderTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember('header-title', () => {
	      return main_core.Tag.render(_t3$3 || (_t3$3 = _$2`
				<div class="ui-time-picker-header-title" onclick="${0}"></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleTitleClick$1)[_handleTitleClick$1].bind(this));
	    });
	  }
	  getHoursContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember('hours', () => {
	      return main_core.Tag.render(_t4$3 || (_t4$3 = _$2`
				<div 
					class="ui-time-picker-list-container" 
					onclick="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleItemClick)[_handleItemClick].bind(this));
	    });
	  }
	  getMinutesContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember('minutes', () => {
	      return main_core.Tag.render(_t5$1 || (_t5$1 = _$2`
				<div 
					class="ui-time-picker-list-container" 
					onclick="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleItemClick)[_handleItemClick].bind(this));
	    });
	  }
	  getMeridiemsContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember('meridiems', () => {
	      return main_core.Tag.render(_t6$1 || (_t6$1 = _$2`
				<div 
					class="ui-time-picker-list-container" 
					onclick="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleItemClick)[_handleItemClick].bind(this));
	    });
	  }
	  getTimeHighlighter() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember('time-highlighter', () => {
	      return main_core.Tag.render(_t7$1 || (_t7$1 = _$2`<div class="ui-time-picker-time-highlighter"></div>`));
	    });
	  }
	  onShow() {
	    super.onShow();
	    this.focusSelector('hour', !this.getDatePicker().isInline());
	  }
	  renderTo(container) {
	    super.renderTo(container);
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollHeight)[_adjustScrollHeight](this.getHoursContainer());
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollHeight)[_adjustScrollHeight](this.getMinutesContainer());
	    if (this.getDatePicker().isAmPmMode()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollHeight)[_adjustScrollHeight](this.getMeridiemsContainer());
	    }
	  }
	  render() {
	    super.render();
	    let selectedHourIndex = 0;
	    this.getHours().forEach(hour => {
	      if (hour.selected) {
	        selectedHourIndex = hour.index;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _renderHour)[_renderHour](hour);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition)[_adjustScrollPosition](this.getHoursContainer(), selectedHourIndex, false);
	    let selectedMinuteIndex = 0;
	    this.getMinutes().forEach(minute => {
	      if (minute.selected) {
	        selectedMinuteIndex = minute.index;
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _renderMinute)[_renderMinute](minute);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition)[_adjustScrollPosition](this.getMinutesContainer(), selectedMinuteIndex, false);
	    const picker = this.getDatePicker();
	    if (picker.isAmPmMode()) {
	      let selectedMeridiemIndex = 0;
	      this.getMeridiems().forEach(meridiem => {
	        if (meridiem.selected) {
	          selectedMeridiemIndex = meridiem.index;
	        }
	        babelHelpers.classPrivateFieldLooseBase(this, _renderMeridiem)[_renderMeridiem](meridiem);
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition)[_adjustScrollPosition](this.getMeridiemsContainer(), selectedMeridiemIndex, false);
	    }
	  }
	  getItemHeight() {
	    return 30;
	  }
	  focusSelector(id, changePageFocus = true) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _focusSelectorId)[_focusSelectorId] === id) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _focusSelectorId)[_focusSelectorId] !== null) {
	      const currentSelector = this.getContainer().querySelector(`[data-selector-id="${babelHelpers.classPrivateFieldLooseBase(this, _focusSelectorId)[_focusSelectorId]}"]`);
	      main_core.Dom.removeClass(currentSelector, '--focused');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _focusSelectorId)[_focusSelectorId] = id;
	    const newSelector = this.getContainer().querySelector(`[data-selector-id="${id}"]`);
	    const scrollContainer = newSelector.querySelector('[tabindex]:not([tabindex="-1"])');
	    main_core.Dom.addClass(newSelector, '--focused');
	    if (changePageFocus) {
	      scrollContainer.focus({
	        preventScroll: true
	      });
	    }
	  }
	}
	function _renderHour2(hour) {
	  const div = babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember(`hour-${hour.value}`, () => {
	    const hourContainer = main_core.Tag.render(_t8$1 || (_t8$1 = _$2`
				<div 
					class="ui-time-picker-list-item" 
					data-index="${0}" 
					data-value="${0}"
				>${0}</div>
			`), hour.index, hour.value, hour.name);
	    main_core.Dom.append(hourContainer, this.getHoursContainer());
	    return hourContainer;
	  });
	  if (hour.selected) {
	    main_core.Dom.addClass(div, '--selected');
	  } else {
	    main_core.Dom.removeClass(div, '--selected');
	  }
	}
	function _renderMinute2(minute) {
	  const div = babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember(`minute-${minute.value}`, () => {
	    const minuteContainer = main_core.Tag.render(_t9$1 || (_t9$1 = _$2`
				<div 
					class="ui-time-picker-list-item"
					data-index="${0}" 
					data-value="${0}"
				>${0}</div>
			`), minute.index, minute.value, minute.name);
	    main_core.Dom.append(minuteContainer, this.getMinutesContainer());
	    return minuteContainer;
	  });
	  if (minute.selected) {
	    main_core.Dom.addClass(div, '--selected');
	  } else {
	    main_core.Dom.removeClass(div, '--selected');
	  }
	  if (minute.hidden) {
	    div.dataset.index = '';
	    main_core.Dom.addClass(div, '--hidden');
	  } else {
	    div.dataset.index = minute.index;
	    main_core.Dom.removeClass(div, '--hidden');
	  }
	}
	function _renderMeridiem2(meridiem) {
	  const div = babelHelpers.classPrivateFieldLooseBase(this, _refs$3)[_refs$3].remember(`meridiem-${meridiem.value}`, () => {
	    const meridiemContainer = main_core.Tag.render(_t10$1 || (_t10$1 = _$2`
				<div 
					class="ui-time-picker-list-item"
					data-index="${0}" 
					data-value="${0}"
				>${0}</div>
			`), meridiem.index, meridiem.value, meridiem.name);
	    main_core.Dom.append(meridiemContainer, this.getMeridiemsContainer());
	    return meridiemContainer;
	  });
	  if (meridiem.selected) {
	    main_core.Dom.addClass(div, '--selected');
	  } else {
	    main_core.Dom.removeClass(div, '--selected');
	  }
	}
	function _adjustScrollHeight2(listContainer) {
	  const viewport = listContainer.parentNode.parentNode;
	  const offset = viewport.offsetHeight / 2 - this.getItemHeight() / 2;
	  main_core.Dom.style(listContainer, {
	    marginTop: `${offset}px`,
	    marginBottom: `${offset}px`
	  });
	}
	function _adjustScrollPosition2(listContainer, index, smooth = true) {
	  const scrollContainer = listContainer.parentNode;
	  const scrollTop = this.getItemHeight() * index;
	  if (scrollContainer.scrollTop !== scrollTop) {
	    scrollContainer.scrollTo({
	      top: scrollTop,
	      behavior: smooth ? 'smooth' : 'instant'
	    });
	    return true;
	  }
	  return false;
	}
	function _handleItemClick2(event) {
	  const item = event.target;
	  if (!item.closest('.ui-time-picker-list-item')) {
	    return;
	  }
	  const listContainer = item.parentNode;
	  const index = Number(item.dataset.index);
	  const scrollChanged = babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition)[_adjustScrollPosition](listContainer, index);
	  if (!scrollChanged) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectTime)[_selectTime](listContainer.parentNode);
	  }
	}
	function _handleTitleClick2$1(event) {
	  this.emit('onTitleClick');
	}
	function _handleSelectorMouseEnter2(event) {
	  this.focusSelector(event.target.dataset.selectorId);
	}
	function _handleFocus2(event) {
	  this.focusSelector(event.target.parentNode.parentNode.dataset.selectorId);
	}
	function _handleSelectorScroll2(event) {
	  const scrollContainer = event.target;
	  const scrollTop = scrollContainer.scrollTop;
	  const atSnappingPoint = scrollTop % this.getItemHeight() === 0;
	  if (atSnappingPoint) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectTime)[_selectTime](scrollContainer);
	  }
	}
	function _selectTime2(scrollContainer) {
	  const scrollTop = scrollContainer.scrollTop;
	  const index = scrollTop / this.getItemHeight();
	  const selector = scrollContainer.parentNode.parentNode;
	  const selectorId = selector.dataset.selectorId;
	  const item = selector.querySelector(`[data-index="${index}"]`);
	  const selectedDate = this.getTimeDate();
	  const currentHour = selectedDate === null ? -1 : selectedDate.getUTCHours();
	  const currentMinute = selectedDate === null ? -1 : selectedDate.getUTCMinutes();
	  switch (selectorId) {
	    case 'hour':
	      {
	        const hour = Number(item.dataset.value);
	        if (currentHour !== hour) {
	          this.emit('onSelect', {
	            hour
	          });
	        }
	        break;
	      }
	    case 'minute':
	      {
	        const minute = Number(item.dataset.value);
	        if (currentMinute !== minute) {
	          this.emit('onSelect', {
	            minute
	          });
	        }
	        break;
	      }
	    case 'meridiem':
	      {
	        const meridiem = item.dataset.value;
	        if (meridiem === 'am' && currentHour >= 12) {
	          const hour = currentHour - 12;
	          this.emit('onSelect', {
	            hour
	          });
	        } else if (meridiem === 'pm' && currentHour >= 0 && currentHour < 12) {
	          const hour = currentHour + 12;
	          this.emit('onSelect', {
	            hour
	          });
	        }
	        break;
	      }
	    default:
	      break;
	  }
	}

	let _$3 = t => t,
	  _t$4,
	  _t2$4,
	  _t3$4,
	  _t4$4,
	  _t5$2,
	  _t6$2;
	var _refs$4 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _firstRender = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("firstRender");
	var _renderHour$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderHour");
	var _renderMinute$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderMinute");
	var _adjustScrollPosition$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustScrollPosition");
	var _adjustScrollShadows = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("adjustScrollShadows");
	var _handleItemClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleItemClick");
	var _handleMouseEnter$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMouseEnter");
	var _handleMouseLeave$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMouseLeave");
	var _handleFocus$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleFocus");
	var _handleTitleClick$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTitleClick");
	class TimePickerGrid extends TimePickerBase {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _handleTitleClick$2, {
	      value: _handleTitleClick2$2
	    });
	    Object.defineProperty(this, _handleFocus$1, {
	      value: _handleFocus2$1
	    });
	    Object.defineProperty(this, _handleMouseLeave$1, {
	      value: _handleMouseLeave2$1
	    });
	    Object.defineProperty(this, _handleMouseEnter$1, {
	      value: _handleMouseEnter2$1
	    });
	    Object.defineProperty(this, _handleItemClick$1, {
	      value: _handleItemClick2$1
	    });
	    Object.defineProperty(this, _adjustScrollShadows, {
	      value: _adjustScrollShadows2
	    });
	    Object.defineProperty(this, _adjustScrollPosition$1, {
	      value: _adjustScrollPosition2$1
	    });
	    Object.defineProperty(this, _renderMinute$1, {
	      value: _renderMinute2$1
	    });
	    Object.defineProperty(this, _renderHour$1, {
	      value: _renderHour2$1
	    });
	    Object.defineProperty(this, _refs$4, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	    Object.defineProperty(this, _firstRender, {
	      writable: true,
	      value: true
	    });
	  }
	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$4)[_refs$4].remember('container', () => {
	      return main_core.Tag.render(_t$4 || (_t$4 = _$3`
				<div class="ui-time-picker-grid${0}">
					${0}
					<div class="ui-time-picker-grid-content">
						<div class="ui-time-picker-grid-column">
							<div class="ui-time-picker-grid-column-title">${0}</div>
							<div class="ui-time-picker-grid-column-content">
								${0}
							</div>
						</div>
						<div class="ui-time-picker-grid-column-separator"></div>
						<div class="ui-time-picker-grid-column">
							<div class="ui-time-picker-grid-column-title">${0}</div>
							<div class="ui-time-picker-grid-column-content">
								${0}
							</div>
						</div>
					</div>
				</div>
			`), this.getDatePicker().isAmPmMode() ? ' --am-pm' : '', this.getDatePicker().getType() === 'time' ? null : this.getHeaderContainer(this.getPrevBtn(), this.getHeaderTitle()), main_core.Loc.getMessage('UI_DATE_PICKER_HOURS'), this.getHoursContainer(), main_core.Loc.getMessage('UI_DATE_PICKER_MINUTES'), this.getMinutesContainer());
	    });
	  }
	  getHeaderTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$4)[_refs$4].remember('header-title', () => {
	      return main_core.Tag.render(_t2$4 || (_t2$4 = _$3`
				<div class="ui-time-picker-grid-header-title" onclick="${0}"></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleTitleClick$2)[_handleTitleClick$2].bind(this));
	    });
	  }
	  getHoursContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$4)[_refs$4].remember('hours', () => {
	      return main_core.Tag.render(_t3$4 || (_t3$4 = _$3`
				<div 
					class="ui-time-picker-grid-column-items --hours" 
					onclick="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleItemClick$1)[_handleItemClick$1].bind(this));
	    });
	  }
	  getMinutesContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$4)[_refs$4].remember('minutes', () => {
	      return main_core.Tag.render(_t4$4 || (_t4$4 = _$3`
				<div 
					class="ui-time-picker-grid-column-items --minutes" 
					onclick="${0}"
					onscroll="${0}"
				></div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleItemClick$1)[_handleItemClick$1].bind(this), main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollShadows)[_adjustScrollShadows], 100, this));
	    });
	  }
	  onHide() {
	    super.onHide();
	    babelHelpers.classPrivateFieldLooseBase(this, _firstRender)[_firstRender] = true;
	  }
	  render() {
	    super.render();
	    let focusedHourBtn = null;
	    this.getHours().forEach(hour => {
	      const button = babelHelpers.classPrivateFieldLooseBase(this, _renderHour$1)[_renderHour$1](hour, this.getHoursContainer());
	      if (hour.focused) {
	        focusedHourBtn = button;
	      }
	    });
	    let selectedMinute = null;
	    let focusedMinute = null;
	    this.getMinutes().forEach(minute => {
	      const button = babelHelpers.classPrivateFieldLooseBase(this, _renderMinute$1)[_renderMinute$1](minute, this.getMinutesContainer());
	      if (minute.selected) {
	        selectedMinute = button;
	      }
	      if (minute.focused) {
	        focusedMinute = button;
	      }
	    });
	    if (babelHelpers.classPrivateFieldLooseBase(this, _firstRender)[_firstRender]) {
	      main_core.Dom.style(this.getMinutesContainer(), 'height', `${this.getHoursContainer().offsetHeight}px`);
	      if (selectedMinute !== null) {
	        babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition$1)[_adjustScrollPosition$1](selectedMinute, false);
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollShadows)[_adjustScrollShadows]();
	      babelHelpers.classPrivateFieldLooseBase(this, _firstRender)[_firstRender] = false;
	    }
	    if (this.getDatePicker().isFocused()) {
	      if (this.getFocusColumn() === 'hours' && focusedHourBtn !== null) {
	        focusedHourBtn.focus({
	          preventScroll: true
	        });
	      } else if (this.getFocusColumn() === 'minutes' && focusedMinute !== null) {
	        focusedMinute.focus({
	          preventScroll: true
	        });
	      }
	    }
	  }
	  adjustMinuteFocusPosition() {
	    const item = this.getContainer().ownerDocument.activeElement;
	    if (!item.closest('.ui-time-picker-grid-item')) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition$1)[_adjustScrollPosition$1](item);
	  }
	}
	function _renderHour2$1(hour, container) {
	  const button = babelHelpers.classPrivateFieldLooseBase(this, _refs$4)[_refs$4].remember(`hour-${hour.value}`, () => {
	    const hourContainer = main_core.Tag.render(_t5$2 || (_t5$2 = _$3`
				<button
					type="button"
					class="ui-time-picker-grid-item" 
					data-index="${0}" 
					data-hour="${0}"
					data-tab-priority="true"
					onmouseenter="${0}"
					onmouseleave="${0}"
					onfocus="${0}"
				><span class="ui-time-picker-grid-item-inner">${0}</span></button>
			`), hour.index, hour.value, babelHelpers.classPrivateFieldLooseBase(this, _handleMouseEnter$1)[_handleMouseEnter$1].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleMouseLeave$1)[_handleMouseLeave$1].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleFocus$1)[_handleFocus$1].bind(this), hour.name);
	    if (this.getDatePicker().isAmPmMode()) {
	      if (hour.value === 0) {
	        hourContainer.dataset.meridiem = 'AM';
	        main_core.Dom.addClass(hourContainer, '--has-meridiem');
	      } else if (hour.value === 12) {
	        hourContainer.dataset.meridiem = 'PM';
	        main_core.Dom.addClass(hourContainer, '--has-meridiem');
	      }
	    }
	    main_core.Dom.append(hourContainer, container);
	    return hourContainer;
	  });
	  if (hour.selected) {
	    main_core.Dom.addClass(button, '--selected');
	  } else {
	    main_core.Dom.removeClass(button, '--selected');
	  }
	  if (hour.focused) {
	    main_core.Dom.addClass(button, '--focused');
	  } else {
	    main_core.Dom.removeClass(button, '--focused');
	  }
	  button.tabIndex = hour.tabIndex;
	  return button;
	}
	function _renderMinute2$1(minute, container) {
	  const button = babelHelpers.classPrivateFieldLooseBase(this, _refs$4)[_refs$4].remember(`minute-${minute.value}`, () => {
	    const minuteContainer = main_core.Tag.render(_t6$2 || (_t6$2 = _$3`
				<button
					type="button"
					class="ui-time-picker-grid-item"
					data-index="${0}" 
					data-minute="${0}"
					onmouseenter="${0}"
					onmouseleave="${0}"
					onfocus="${0}"
				><span class="ui-time-picker-grid-item-inner">${0}</span></button>
			`), minute.index, minute.value, babelHelpers.classPrivateFieldLooseBase(this, _handleMouseEnter$1)[_handleMouseEnter$1].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleMouseLeave$1)[_handleMouseLeave$1].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleFocus$1)[_handleFocus$1].bind(this), minute.name);
	    main_core.Dom.append(minuteContainer, container);
	    return minuteContainer;
	  });
	  if (minute.selected) {
	    main_core.Dom.addClass(button, '--selected');
	  } else {
	    main_core.Dom.removeClass(button, '--selected');
	  }
	  if (minute.hidden) {
	    button.dataset.index = '';
	    main_core.Dom.addClass(button, '--hidden');
	  } else {
	    button.dataset.index = minute.index;
	    main_core.Dom.removeClass(button, '--hidden');
	  }
	  if (minute.focused) {
	    main_core.Dom.addClass(button, '--focused');
	  } else {
	    main_core.Dom.removeClass(button, '--focused');
	  }
	  button.tabIndex = minute.tabIndex;
	  return button;
	}
	function _adjustScrollPosition2$1(selectedMinute, smooth = true) {
	  const shadowHeight = 20;
	  const scrollTop = this.getMinutesContainer().scrollTop;
	  const viewportTop = scrollTop + shadowHeight;
	  const offsetTop = selectedMinute.offsetTop;
	  const offsetBottom = offsetTop + selectedMinute.offsetHeight;
	  const viewportHeight = this.getMinutesContainer().offsetHeight;
	  const viewportBottom = scrollTop + viewportHeight - shadowHeight;
	  const isVisible = offsetTop >= viewportTop && offsetTop <= viewportBottom && offsetBottom <= viewportBottom && offsetBottom >= viewportTop;
	  if (!isVisible) {
	    this.getMinutesContainer().scrollTo({
	      top: selectedMinute.offsetTop - viewportHeight / 2,
	      behavior: smooth ? 'smooth' : 'instant'
	    });
	  }
	}
	function _adjustScrollShadows2() {
	  const scrollTop = this.getMinutesContainer().scrollTop;
	  const scrollHeight = this.getMinutesContainer().scrollHeight;
	  const offsetHeight = this.getMinutesContainer().offsetHeight;
	  const columnContainer = this.getMinutesContainer().parentNode.parentNode;
	  if (scrollTop > 0) {
	    main_core.Dom.addClass(columnContainer, '--top-shadow');
	  } else {
	    main_core.Dom.removeClass(columnContainer, '--top-shadow');
	  }
	  if (scrollTop === scrollHeight - offsetHeight) {
	    main_core.Dom.removeClass(columnContainer, '--bottom-shadow');
	  } else {
	    main_core.Dom.addClass(columnContainer, '--bottom-shadow');
	  }
	}
	function _handleItemClick2$1(event) {
	  const item = event.target;
	  if (!item.closest('.ui-time-picker-grid-item')) {
	    return;
	  }
	  if (main_core.Type.isStringFilled(item.dataset.hour)) {
	    this.setFocusColumn('hours');
	    const hour = Number(item.dataset.hour);
	    this.emit('onSelect', {
	      hour
	    });
	  } else if (main_core.Type.isStringFilled(item.dataset.minute)) {
	    this.setFocusColumn('minutes');
	    babelHelpers.classPrivateFieldLooseBase(this, _adjustScrollPosition$1)[_adjustScrollPosition$1](item);
	    const minute = Number(item.dataset.minute);
	    this.emit('onSelect', {
	      minute
	    });
	  }
	}
	function _handleMouseEnter2$1(event) {
	  const {
	    hour,
	    minute
	  } = event.target.dataset;
	  if (main_core.Type.isStringFilled(hour)) {
	    this.setFocusColumn('hours');
	    this.emit('onFocus', {
	      hour: main_core.Text.toInteger(hour)
	    });
	  } else if (main_core.Type.isStringFilled(minute)) {
	    this.setFocusColumn('minutes');
	    this.emit('onFocus', {
	      minute: main_core.Text.toInteger(minute)
	    });
	  }
	}
	function _handleMouseLeave2$1(event) {
	  this.emit('onBlur');
	}
	function _handleFocus2$1(event) {
	  const {
	    hour,
	    minute
	  } = event.target.dataset;
	  const currentColumn = this.getFocusColumn();
	  if (main_core.Type.isStringFilled(hour)) {
	    this.setFocusColumn('hours');
	  } else if (main_core.Type.isStringFilled(minute)) {
	    this.setFocusColumn('minutes');
	  }
	  if (currentColumn !== this.getFocusColumn()) {
	    this.render();
	  }
	}
	function _handleTitleClick2$2(event) {
	  this.emit('onTitleClick');
	}

	let _$4 = t => t,
	  _t$5,
	  _t2$5,
	  _t3$5,
	  _t4$5;
	var _refs$5 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _getStartYear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getStartYear");
	var _renderQuarter$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderQuarter");
	var _renderYear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("renderYear");
	var _handleMouseEnter$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMouseEnter");
	var _handleMouseLeave$2 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMouseLeave");
	var _handleYearClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleYearClick");
	class YearPicker extends BasePicker {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _handleYearClick$1, {
	      value: _handleYearClick2$1
	    });
	    Object.defineProperty(this, _handleMouseLeave$2, {
	      value: _handleMouseLeave2$2
	    });
	    Object.defineProperty(this, _handleMouseEnter$2, {
	      value: _handleMouseEnter2$2
	    });
	    Object.defineProperty(this, _renderYear, {
	      value: _renderYear2
	    });
	    Object.defineProperty(this, _renderQuarter$1, {
	      value: _renderQuarter2$1
	    });
	    Object.defineProperty(this, _getStartYear, {
	      value: _getStartYear2
	    });
	    Object.defineProperty(this, _refs$5, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	  }
	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$5)[_refs$5].remember('container', () => {
	      return main_core.Tag.render(_t$5 || (_t$5 = _$4`
				<div class="ui-year-picker">
					${0}
					${0}
				</div>
			`), this.getHeaderContainer(this.getPrevBtn(), this.getHeaderTitle(), this.getNextBtn()), this.getContentContainer());
	    });
	  }
	  getHeaderTitle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$5)[_refs$5].remember('header-title', () => {
	      return main_core.Tag.render(_t2$5 || (_t2$5 = _$4`
				<div class="ui-year-picker-header-title"></div>
			`));
	    });
	  }
	  getYears() {
	    const {
	      year: currentYear
	    } = getDate(this.getDatePicker().getToday());
	    const focusDate = this.getDatePicker().getFocusDate();
	    const initialFocusYear = this.getDatePicker().getInitialFocusDate().getUTCFullYear();
	    const years = [];
	    let index = 0;
	    let year = babelHelpers.classPrivateFieldLooseBase(this, _getStartYear)[_getStartYear]();
	    for (let i = 0; i < 4; i++) {
	      const quarter = [];
	      for (let j = 0; j < 3; j++) {
	        const focused = focusDate !== null && focusDate.getUTCFullYear() === year;
	        quarter.push({
	          index,
	          year,
	          name: year,
	          current: currentYear === year,
	          selected: this.getDatePicker().isDateSelected(createUtcDate(year), 'year'),
	          focused,
	          tabIndex: focused || year === initialFocusYear ? 0 : -1
	        });
	        year++;
	        index++;
	      }
	      years.push(quarter);
	    }
	    return years;
	  }
	  getFirstYear() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getStartYear)[_getStartYear]();
	  }
	  getLastYear() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getStartYear)[_getStartYear]() + 11;
	  }
	  renderTo(container) {
	    super.renderTo(container);
	    main_core.Event.bind(this.getContentContainer(), 'click', babelHelpers.classPrivateFieldLooseBase(this, _handleYearClick$1)[_handleYearClick$1].bind(this));
	  }
	  render() {
	    let focusButton = null;
	    const isFocused = this.getDatePicker().isFocused();
	    const years = this.getYears();
	    years.forEach((quarter, index) => {
	      const quarterContainer = babelHelpers.classPrivateFieldLooseBase(this, _renderQuarter$1)[_renderQuarter$1](index);
	      quarter.forEach(year => {
	        const button = babelHelpers.classPrivateFieldLooseBase(this, _renderYear)[_renderYear](year, quarterContainer);
	        if (year.focused) {
	          focusButton = button;
	        }
	      });
	    });
	    if (focusButton !== null && isFocused) {
	      focusButton.focus({
	        preventScroll: true
	      });
	    }
	    const firstYear = years[0][0].name;
	    const lastYear = years.at(-1).at(-1).name;
	    this.getHeaderTitle().textContent = `${firstYear} — ${lastYear}`;
	  }
	}
	function _getStartYear2() {
	  const {
	    year: viewYear
	  } = this.getDatePicker().getViewDateParts();
	  const {
	    year: currentYear
	  } = getDate(this.getDatePicker().getToday());
	  let year = currentYear - 4;
	  year -= 12 * Math.ceil((year - viewYear) / 12);
	  return year;
	}
	function _renderQuarter2$1(index) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _refs$5)[_refs$5].remember(`quarter-${index}`, () => {
	    const container = main_core.Tag.render(_t3$5 || (_t3$5 = _$4`<div class="ui-year-picker-trio"></div>`));
	    main_core.Dom.append(container, this.getContentContainer());
	    return container;
	  });
	}
	function _renderYear2(year, quarterContainer) {
	  const button = babelHelpers.classPrivateFieldLooseBase(this, _refs$5)[_refs$5].remember(`year-${year.index}`, () => {
	    const yearButton = main_core.Tag.render(_t4$5 || (_t4$5 = _$4`
				<button
					type="button"
					class="ui-year-picker-year"
					data-year="${0}"
					data-tab-priority="true"
					onmouseenter="${0}"
					onmouseleave="${0}"
				>${0}</button>
			`), year, babelHelpers.classPrivateFieldLooseBase(this, _handleMouseEnter$2)[_handleMouseEnter$2].bind(this), babelHelpers.classPrivateFieldLooseBase(this, _handleMouseLeave$2)[_handleMouseLeave$2].bind(this), main_core.Text.encode(year.name));
	    main_core.Dom.append(yearButton, quarterContainer);
	    return yearButton;
	  });
	  const currentYear = Number(button.dataset.year);
	  if (currentYear !== year.year) {
	    button.dataset.year = year.year;
	    button.textContent = year.name;
	  }
	  if (year.current) {
	    main_core.Dom.addClass(button, '--current');
	  } else {
	    main_core.Dom.removeClass(button, '--current');
	  }
	  if (year.selected) {
	    main_core.Dom.addClass(button, '--selected');
	  } else {
	    main_core.Dom.removeClass(button, '--selected');
	  }
	  if (year.focused) {
	    main_core.Dom.addClass(button, '--focused');
	  } else {
	    main_core.Dom.removeClass(button, '--focused');
	  }
	  button.tabIndex = year.tabIndex;
	  return button;
	}
	function _handleMouseEnter2$2(event) {
	  const dataset = event.target.dataset;
	  const year = main_core.Text.toInteger(dataset.year);
	  this.emit('onFocus', {
	    year
	  });
	}
	function _handleMouseLeave2$2(event) {
	  this.emit('onBlur');
	}
	function _handleYearClick2$1(event) {
	  if (!main_core.Dom.hasClass(event.target, 'ui-year-picker-year')) {
	    return;
	  }
	  const year = main_core.Text.toInteger(event.target.dataset.year);
	  this.emit('onSelect', {
	    year
	  });
	}

	let _$5 = t => t,
	  _t$6,
	  _t2$6;
	let singleOpenDatePicker = null;

	/**
	 * @namespace BX.UI.DatePicker
	 */
	var _viewDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("viewDate");
	var _startDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startDate");
	var _selectedDates = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectedDates");
	var _focusDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("focusDate");
	var _type = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("type");
	var _currentView = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentView");
	var _selectionMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("selectionMode");
	var _views = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("views");
	var _firstWeekDay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("firstWeekDay");
	var _showWeekDays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showWeekDays");
	var _showWeekNumbers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showWeekNumbers");
	var _showOutsideDays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("showOutsideDays");
	var _numberOfMonths = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("numberOfMonths");
	var _maxDays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("maxDays");
	var _minDays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minDays");
	var _fullYear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("fullYear");
	var _weekends = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("weekends");
	var _holidays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("holidays");
	var _workdays = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("workdays");
	var _enableTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enableTime");
	var _allowSeconds = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allowSeconds");
	var _amPmMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("amPmMode");
	var _minuteStep = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("minuteStep");
	var _defaultTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultTime");
	var _defaultTimeSpan = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultTimeSpan");
	var _timePickerStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timePickerStyle");
	var _cutZeroTime = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cutZeroTime");
	var _targetNode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("targetNode");
	var _inputField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inputField");
	var _rangeStartInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rangeStartInput");
	var _rangeEndInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rangeEndInput");
	var _useInputEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useInputEvents");
	var _dateSeparator = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateSeparator");
	var _popup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popup");
	var _popupOptions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("popupOptions");
	var _hideByEsc = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideByEsc");
	var _autoHide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoHide");
	var _cacheable = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("cacheable");
	var _singleOpening = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("singleOpening");
	var _refs$6 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("refs");
	var _rendered$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("rendered");
	var _inline = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("inline");
	var _autoFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("autoFocus");
	var _dateFormat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dateFormat");
	var _timeFormat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timeFormat");
	var _toggleSelected = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("toggleSelected");
	var _hideOnSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideOnSelect");
	var _locale = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("locale");
	var _hideHeader = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideHeader");
	var _dayColors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dayColors");
	var _dayMarks = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dayMarks");
	var _keyboardNavigation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("keyboardNavigation");
	var _destroying = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroying");
	var _canSelectDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canSelectDate");
	var _canDeselectDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canDeselectDate");
	var _setType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setType");
	var _createDateMatchers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createDateMatchers");
	var _setSelectionMode = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setSelectionMode");
	var _getInputField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getInputField");
	var _bindInputEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindInputEvents");
	var _unbindInputEvents = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("unbindInputEvents");
	var _handleInputClick = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInputClick");
	var _handleInputFocusOut = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInputFocusOut");
	var _handleInputKeyDown = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInputKeyDown");
	var _handleInputChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleInputChange");
	var _handleAutoHide = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleAutoHide");
	var _focusInputField = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("focusInputField");
	var _getDateFromInput = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDateFromInput");
	var _setInputDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setInputDate");
	var _getDefaultDateFormat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultDateFormat");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	var _createPicker = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createPicker");
	var _handleContainerKeyUp = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleContainerKeyUp");
	var _handleTimeClick$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeClick");
	var _handleDaySelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDaySelect");
	var _handleDayFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDayFocus");
	var _handleDayBlur = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleDayBlur");
	var _handleMonthFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMonthFocus");
	var _handleMonthBlur = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMonthBlur");
	var _handleYearFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleYearFocus");
	var _handleYearBlur = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleYearBlur");
	var _handleTimeFocus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeFocus");
	var _handleTimeBlur = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeBlur");
	var _handleMonthSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMonthSelect");
	var _handleYearSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleYearSelect");
	var _handleTimeSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeSelect");
	var _handleTimeRangeSelect = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleTimeRangeSelect");
	var _handlePopupShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupShow");
	var _handlePopupFirstShow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupFirstShow");
	var _handlePopupClose = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupClose");
	var _handlePopupDestroy = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlePopupDestroy");
	class DatePicker extends main_core_events.EventEmitter {
	  constructor(pickerOptions) {
	    super();
	    Object.defineProperty(this, _handlePopupDestroy, {
	      value: _handlePopupDestroy2
	    });
	    Object.defineProperty(this, _handlePopupClose, {
	      value: _handlePopupClose2
	    });
	    Object.defineProperty(this, _handlePopupFirstShow, {
	      value: _handlePopupFirstShow2
	    });
	    Object.defineProperty(this, _handlePopupShow, {
	      value: _handlePopupShow2
	    });
	    Object.defineProperty(this, _handleTimeRangeSelect, {
	      value: _handleTimeRangeSelect2
	    });
	    Object.defineProperty(this, _handleTimeSelect, {
	      value: _handleTimeSelect2
	    });
	    Object.defineProperty(this, _handleYearSelect, {
	      value: _handleYearSelect2
	    });
	    Object.defineProperty(this, _handleMonthSelect, {
	      value: _handleMonthSelect2
	    });
	    Object.defineProperty(this, _handleTimeBlur, {
	      value: _handleTimeBlur2
	    });
	    Object.defineProperty(this, _handleTimeFocus, {
	      value: _handleTimeFocus2
	    });
	    Object.defineProperty(this, _handleYearBlur, {
	      value: _handleYearBlur2
	    });
	    Object.defineProperty(this, _handleYearFocus, {
	      value: _handleYearFocus2
	    });
	    Object.defineProperty(this, _handleMonthBlur, {
	      value: _handleMonthBlur2
	    });
	    Object.defineProperty(this, _handleMonthFocus, {
	      value: _handleMonthFocus2
	    });
	    Object.defineProperty(this, _handleDayBlur, {
	      value: _handleDayBlur2
	    });
	    Object.defineProperty(this, _handleDayFocus, {
	      value: _handleDayFocus2
	    });
	    Object.defineProperty(this, _handleDaySelect, {
	      value: _handleDaySelect2
	    });
	    Object.defineProperty(this, _handleTimeClick$1, {
	      value: _handleTimeClick2$1
	    });
	    Object.defineProperty(this, _handleContainerKeyUp, {
	      value: _handleContainerKeyUp2
	    });
	    Object.defineProperty(this, _createPicker, {
	      value: _createPicker2
	    });
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _getDefaultDateFormat, {
	      value: _getDefaultDateFormat2
	    });
	    Object.defineProperty(this, _setInputDate, {
	      value: _setInputDate2
	    });
	    Object.defineProperty(this, _getDateFromInput, {
	      value: _getDateFromInput2
	    });
	    Object.defineProperty(this, _focusInputField, {
	      value: _focusInputField2
	    });
	    Object.defineProperty(this, _handleAutoHide, {
	      value: _handleAutoHide2
	    });
	    Object.defineProperty(this, _handleInputChange, {
	      value: _handleInputChange2
	    });
	    Object.defineProperty(this, _handleInputKeyDown, {
	      value: _handleInputKeyDown2
	    });
	    Object.defineProperty(this, _handleInputFocusOut, {
	      value: _handleInputFocusOut2
	    });
	    Object.defineProperty(this, _handleInputClick, {
	      value: _handleInputClick2
	    });
	    Object.defineProperty(this, _unbindInputEvents, {
	      value: _unbindInputEvents2
	    });
	    Object.defineProperty(this, _bindInputEvents, {
	      value: _bindInputEvents2
	    });
	    Object.defineProperty(this, _getInputField, {
	      value: _getInputField2
	    });
	    Object.defineProperty(this, _setSelectionMode, {
	      value: _setSelectionMode2
	    });
	    Object.defineProperty(this, _createDateMatchers, {
	      value: _createDateMatchers2
	    });
	    Object.defineProperty(this, _setType, {
	      value: _setType2
	    });
	    Object.defineProperty(this, _canDeselectDate, {
	      value: _canDeselectDate2
	    });
	    Object.defineProperty(this, _canSelectDate, {
	      value: _canSelectDate2
	    });
	    Object.defineProperty(this, _viewDate, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _startDate, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _selectedDates, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _focusDate, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _type, {
	      writable: true,
	      value: 'date'
	    });
	    Object.defineProperty(this, _currentView, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _selectionMode, {
	      writable: true,
	      value: 'single'
	    });
	    Object.defineProperty(this, _views, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _firstWeekDay, {
	      writable: true,
	      value: 1
	    });
	    Object.defineProperty(this, _showWeekDays, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _showWeekNumbers, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _showOutsideDays, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _numberOfMonths, {
	      writable: true,
	      value: 1
	    });
	    Object.defineProperty(this, _maxDays, {
	      writable: true,
	      value: Infinity
	    });
	    Object.defineProperty(this, _minDays, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _fullYear, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _weekends, {
	      writable: true,
	      value: [0, 6]
	    });
	    Object.defineProperty(this, _holidays, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _workdays, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _enableTime, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _allowSeconds, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _amPmMode, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _minuteStep, {
	      writable: true,
	      value: 5
	    });
	    Object.defineProperty(this, _defaultTime, {
	      writable: true,
	      value: '00:00:00'
	    });
	    Object.defineProperty(this, _defaultTimeSpan, {
	      writable: true,
	      value: 60
	    });
	    Object.defineProperty(this, _timePickerStyle, {
	      writable: true,
	      value: 'grid'
	    });
	    Object.defineProperty(this, _cutZeroTime, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _targetNode, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _inputField, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _rangeStartInput, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _rangeEndInput, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _useInputEvents, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _dateSeparator, {
	      writable: true,
	      value: ', '
	    });
	    Object.defineProperty(this, _popup, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _popupOptions, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _hideByEsc, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _autoHide, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _cacheable, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _singleOpening, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _refs$6, {
	      writable: true,
	      value: new main_core_cache.MemoryCache()
	    });
	    Object.defineProperty(this, _rendered$1, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _inline, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _autoFocus, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _dateFormat, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _timeFormat, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _toggleSelected, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _hideOnSelect, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _locale, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _hideHeader, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _dayColors, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _dayMarks, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _keyboardNavigation, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _destroying, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('BX.UI.DatePicker');
	    const settings = main_core.Extension.getSettings('ui.date-picker');
	    const options = main_core.Type.isPlainObject(pickerOptions) ? pickerOptions : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _setType)[_setType](options.type);
	    babelHelpers.classPrivateFieldLooseBase(this, _setSelectionMode)[_setSelectionMode](options.selectionMode);
	    babelHelpers.classPrivateFieldLooseBase(this, _locale)[_locale] = main_core.Type.isStringFilled(options.locale) ? options.locale : settings.get('locale', 'en');
	    babelHelpers.classPrivateFieldLooseBase(this, _enableTime)[_enableTime] = main_core.Type.isBoolean(options.enableTime) ? options.enableTime : babelHelpers.classPrivateFieldLooseBase(this, _enableTime)[_enableTime];
	    if (this.isMultipleMode()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _enableTime)[_enableTime] = false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _allowSeconds)[_allowSeconds] = main_core.Type.isBoolean(options.allowSeconds) ? options.allowSeconds : babelHelpers.classPrivateFieldLooseBase(this, _allowSeconds)[_allowSeconds];
	    babelHelpers.classPrivateFieldLooseBase(this, _amPmMode)[_amPmMode] = main_core.Type.isBoolean(options.amPmMode) ? options.amPmMode : main_date.DateTimeFormat.isAmPmMode();
	    babelHelpers.classPrivateFieldLooseBase(this, _cutZeroTime)[_cutZeroTime] = main_core.Type.isBoolean(options.cutZeroTime) ? options.cutZeroTime : babelHelpers.classPrivateFieldLooseBase(this, _cutZeroTime)[_cutZeroTime];
	    babelHelpers.classPrivateFieldLooseBase(this, _dateFormat)[_dateFormat] = main_core.Type.isStringFilled(options.dateFormat) ? options.dateFormat : babelHelpers.classPrivateFieldLooseBase(this, _getDefaultDateFormat)[_getDefaultDateFormat]();
	    this.setDefaultTime(options.defaultTime);
	    this.setDefaultTimeSpan(options.defaultTimeSpan);
	    babelHelpers.classPrivateFieldLooseBase(this, _timeFormat)[_timeFormat] = main_core.Type.isStringFilled(options.timeFormat) ? options.timeFormat : main_date.DateTimeFormat.getFormat(babelHelpers.classPrivateFieldLooseBase(this, _allowSeconds)[_allowSeconds] ? 'LONG_TIME_FORMAT' : 'SHORT_TIME_FORMAT');
	    babelHelpers.classPrivateFieldLooseBase(this, _minuteStep)[_minuteStep] = main_core.Type.isNumber(options.minuteStep) && [1, 5, 10, 15, 30].includes(options.minuteStep) ? options.minuteStep : babelHelpers.classPrivateFieldLooseBase(this, _minuteStep)[_minuteStep];
	    babelHelpers.classPrivateFieldLooseBase(this, _timePickerStyle)[_timePickerStyle] = options.timePickerStyle === 'wheel' ? 'wheel' : babelHelpers.classPrivateFieldLooseBase(this, _timePickerStyle)[_timePickerStyle];
	    babelHelpers.classPrivateFieldLooseBase(this, _viewDate)[_viewDate] = this.getToday();
	    babelHelpers.classPrivateFieldLooseBase(this, _useInputEvents)[_useInputEvents] = main_core.Type.isBoolean(options.useInputEvents) ? options.useInputEvents : babelHelpers.classPrivateFieldLooseBase(this, _useInputEvents)[_useInputEvents];
	    this.setAutoFocus(options.autoFocus);
	    this.setInputField(options.inputField);
	    this.setRangeStartInput(options.rangeStartInput);
	    this.setRangeEndInput(options.rangeEndInput);
	    this.setDateSeparator(options.dateSeparator);
	    this.selectDates(options.selectedDates, {
	      emitEvents: false
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _startDate)[_startDate] = isDateLike(options.startDate) ? this.createDate(options.startDate) : null;
	    const _viewDate2 = this.getDefaultViewDate();
	    this.setViewDate(_viewDate2);
	    babelHelpers.classPrivateFieldLooseBase(this, _inline)[_inline] = options.inline === true;
	    let firstWeekDay = settings.get('firstWeekDay', babelHelpers.classPrivateFieldLooseBase(this, _firstWeekDay)[_firstWeekDay]);
	    firstWeekDay = main_core.Type.isNumber(options.firstWeekDay) ? options.firstWeekDay : firstWeekDay;
	    babelHelpers.classPrivateFieldLooseBase(this, _firstWeekDay)[_firstWeekDay] = Math.min(Math.max(0, firstWeekDay), 6);
	    babelHelpers.classPrivateFieldLooseBase(this, _numberOfMonths)[_numberOfMonths] = main_core.Type.isNumber(options.numberOfMonths) ? options.numberOfMonths : babelHelpers.classPrivateFieldLooseBase(this, _numberOfMonths)[_numberOfMonths];
	    babelHelpers.classPrivateFieldLooseBase(this, _fullYear)[_fullYear] = options.fullYear === true;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _fullYear)[_fullYear]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _enableTime)[_enableTime] = false;
	      babelHelpers.classPrivateFieldLooseBase(this, _numberOfMonths)[_numberOfMonths] = 12;
	      this.setViewDate(createUtcDate(_viewDate2.getUTCFullYear(), 0, 1));
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _showWeekDays)[_showWeekDays] = main_core.Type.isBoolean(options.showWeekDays) ? options.showWeekDays : babelHelpers.classPrivateFieldLooseBase(this, _showWeekDays)[_showWeekDays];
	    babelHelpers.classPrivateFieldLooseBase(this, _showWeekNumbers)[_showWeekNumbers] = main_core.Type.isBoolean(options.showWeekNumbers) ? options.showWeekNumbers : babelHelpers.classPrivateFieldLooseBase(this, _showWeekNumbers)[_showWeekNumbers];
	    const defaultWeekends = settings.get('weekends', []);
	    babelHelpers.classPrivateFieldLooseBase(this, _weekends)[_weekends] = main_core.Type.isArray(options.weekends) ? options.weekends : main_core.Type.isArrayFilled(defaultWeekends) ? defaultWeekends : babelHelpers.classPrivateFieldLooseBase(this, _weekends)[_weekends];
	    const defaultHolidays = settings.get('holidays', []);
	    babelHelpers.classPrivateFieldLooseBase(this, _holidays)[_holidays] = main_core.Type.isArray(options.holidays) ? options.holidays : defaultHolidays;
	    const defaultWorkdays = settings.get('workdays', []);
	    babelHelpers.classPrivateFieldLooseBase(this, _workdays)[_workdays] = main_core.Type.isArray(options.workdays) ? options.workdays : defaultWorkdays;
	    babelHelpers.classPrivateFieldLooseBase(this, _showOutsideDays)[_showOutsideDays] = babelHelpers.classPrivateFieldLooseBase(this, _numberOfMonths)[_numberOfMonths] > 1 ? false : babelHelpers.classPrivateFieldLooseBase(this, _showOutsideDays)[_showOutsideDays];
	    babelHelpers.classPrivateFieldLooseBase(this, _showOutsideDays)[_showOutsideDays] = main_core.Type.isBoolean(options.showOutsideDays) ? options.showOutsideDays : babelHelpers.classPrivateFieldLooseBase(this, _showOutsideDays)[_showOutsideDays];
	    babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions] = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions];
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
	    babelHelpers.classPrivateFieldLooseBase(this, _keyboardNavigation)[_keyboardNavigation] = new KeyboardNavigation(this);
	  }
	  setViewDate(date) {
	    let newDate = this.createDate(date);
	    if (newDate === null) {
	      return;
	    }
	    newDate = setTime(newDate, 0, 0, 0);
	    babelHelpers.classPrivateFieldLooseBase(this, _viewDate)[_viewDate] = newDate;
	    if (this.isDateOutOfView(this.getFocusDate())) {
	      this.setFocusDate(null, {
	        adjustViewDate: false,
	        render: false
	      });
	    }
	    if (this.isRendered()) {
	      this.getPicker().render();
	    }
	  }
	  getViewDate() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _viewDate)[_viewDate];
	  }
	  getDefaultViewDate() {
	    return this.getSelectedDate() || babelHelpers.classPrivateFieldLooseBase(this, _startDate)[_startDate] || this.getToday();
	  }
	  adjustViewDate(date) {
	    if (this.isSingleMode()) {
	      if (this.getNumberOfMonths() === 1) {
	        if (!isDatesEqual(date, this.getViewDate(), 'month')) {
	          this.setViewDate(createUtcDate(date.getUTCFullYear(), date.getUTCMonth()));
	        }
	      } else {
	        const {
	          year,
	          month
	        } = this.getViewDateParts();
	        const firstMonth = createUtcDate(year, month);
	        const lastMonth = ceilDate(createUtcDate(year, month + this.getNumberOfMonths() - 1), 'month');
	        if (date < firstMonth || date >= lastMonth) {
	          this.setViewDate(createUtcDate(date.getUTCFullYear(), date.getUTCMonth()));
	        }
	      }
	    } else {
	      const dayPicker = this.getPicker('day');
	      const months = dayPicker.getMonths();
	      const firstDay = months[0].weeks[0][0].date;
	      const lastDay = months.at(-1).weeks.at(-1).at(-1).date;
	      if (date < firstDay || date > lastDay) {
	        this.setViewDate(createUtcDate(date.getUTCFullYear(), date.getUTCMonth()));
	      }
	    }
	  }
	  getViewDateParts() {
	    return getDate(babelHelpers.classPrivateFieldLooseBase(this, _viewDate)[_viewDate]);
	  }
	  selectDate(date, options = {}) {
	    if (this.isRangeMode()) {
	      throw new Error('DatePicker: to select a range use selectRange method.');
	    }
	    if (!isDateLike(date)) {
	      return false;
	    }
	    const selectedDate = this.createDate(date);
	    if (this.isDateSelected(selectedDate, 'datetime')) {
	      return false;
	    }
	    const updateTime = this.isDateSelected(selectedDate, 'day');
	    if (!updateTime && this.isMultipleMode() && babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].length >= this.getMaxDays()) {
	      return false;
	    }
	    const {
	      emitEvents,
	      render,
	      updateInputs
	    } = {
	      emitEvents: true,
	      render: true,
	      updateInputs: true,
	      ...options
	    };
	    if (emitEvents && !babelHelpers.classPrivateFieldLooseBase(this, _canSelectDate)[_canSelectDate](selectedDate)) {
	      return false;
	    }
	    if (this.isMultipleMode()) {
	      if (updateTime) {
	        const index = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].findIndex(currentDate => {
	          return isDatesEqual(currentDate, selectedDate, 'day');
	        });

	        // replace existing date
	        if (index !== -1) {
	          babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].splice(index, 1, selectedDate);
	        }
	      } else {
	        const index = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].findIndex(currentDate => {
	          return currentDate > selectedDate;
	        });
	        if (index === -1) {
	          babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].push(selectedDate);
	        } else if (index === 0) {
	          babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].unshift(selectedDate);
	        } else {
	          babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].splice(index, 0, selectedDate);
	        }
	      }
	    } else {
	      const currentDate = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates][0] || null;
	      if (emitEvents && currentDate !== null) {
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _canDeselectDate)[_canDeselectDate](currentDate)) {
	          return false;
	        }
	        this.deselectDate(currentDate, {
	          emitEvents: false,
	          render: false
	        });
	        this.emit(DatePickerEvent.DESELECT, {
	          date: currentDate
	        });
	      }
	      babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates] = [selectedDate];
	    }
	    this.adjustViewDate(selectedDate);
	    if (this.isRendered() && render) {
	      this.getPicker().render();
	    }
	    if (updateInputs) {
	      this.updateInputFields();
	    }
	    if (emitEvents) {
	      this.emit(DatePickerEvent.SELECT, {
	        date: selectedDate
	      });
	      this.emit(DatePickerEvent.SELECT_CHANGE);
	    }
	    return true;
	  }
	  selectDates(dates, options = {}) {
	    if (!main_core.Type.isArrayFilled(dates)) {
	      return;
	    }
	    if (this.isRangeMode()) {
	      const [start, end] = dates;
	      this.selectRange(start, end, options);
	    } else {
	      dates.forEach(date => {
	        this.selectDate(date, options);
	      });
	    }
	  }
	  selectRange(start, end = null, options = {}) {
	    if (!this.isRangeMode()) {
	      throw new Error('DatePicker: to select a date use selectDate method.');
	    }
	    if (!isDateLike(start) || end !== null && !isDateLike(end)) {
	      return false;
	    }
	    let newStart = this.createDate(start);
	    let newEnd = end === null ? null : this.createDate(end);
	    if (newStart === null && newEnd === null) {
	      return false;
	    }
	    if (newStart !== null && newEnd !== null && newStart > newEnd) {
	      [newStart, newEnd] = [newEnd, newStart];
	    }
	    const currentStart = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates][0] || null;
	    const currentEnd = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates][1] || null;
	    if (isDatesEqual(newStart, currentStart, 'datetime') && (newEnd === null && currentEnd === null || isDatesEqual(newEnd, currentEnd, 'datetime'))) {
	      return false;
	    }
	    const {
	      emitEvents,
	      updateInputs
	    } = {
	      emitEvents: true,
	      updateInputs: true,
	      ...options
	    };
	    const deselectStart = currentStart !== null && emitEvents && !isDatesEqual(newStart, currentStart, 'datetime') && !isDatesEqual(newEnd, currentStart, 'datetime');
	    const deselectEnd = currentEnd !== null && emitEvents && !isDatesEqual(newStart, currentEnd, 'datetime') && !isDatesEqual(newEnd, currentEnd, 'datetime');
	    const selectStart = !this.isDateSelected(newStart, 'datetime');
	    const selectEnd = newEnd !== null && (!this.isDateSelected(newEnd, 'datetime') || currentEnd === null && isDatesEqual(newEnd, newStart, 'datetime'));
	    if (deselectStart && !babelHelpers.classPrivateFieldLooseBase(this, _canDeselectDate)[_canDeselectDate](currentStart)) {
	      return false;
	    }
	    if (deselectEnd && !babelHelpers.classPrivateFieldLooseBase(this, _canDeselectDate)[_canDeselectDate](currentEnd)) {
	      return false;
	    }
	    if (selectStart && !babelHelpers.classPrivateFieldLooseBase(this, _canSelectDate)[_canSelectDate](newStart)) {
	      return false;
	    }
	    if (selectEnd && !babelHelpers.classPrivateFieldLooseBase(this, _canSelectDate)[_canSelectDate](newEnd)) {
	      return false;
	    }
	    if (deselectStart) {
	      this.deselectDate(currentStart, {
	        emitEvents: false,
	        render: false
	      });
	      this.emit(DatePickerEvent.DESELECT, {
	        date: currentStart
	      });
	    }
	    if (deselectEnd) {
	      this.deselectDate(currentEnd, {
	        emitEvents: false,
	        render: false
	      });
	      this.emit(DatePickerEvent.DESELECT, {
	        date: currentEnd
	      });
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates] = newEnd === null ? [newStart] : [newStart, newEnd];
	    this.adjustViewDate(newStart);
	    if (this.isRendered()) {
	      this.getPicker().render();
	    }
	    if (updateInputs) {
	      this.updateInputFields();
	    }
	    if (emitEvents) {
	      if (selectStart) {
	        this.emit(DatePickerEvent.SELECT, {
	          date: newStart
	        });
	      }
	      if (selectEnd) {
	        this.emit(DatePickerEvent.SELECT, {
	          date: newEnd
	        });
	      }
	      this.emit(DatePickerEvent.SELECT_CHANGE);
	    }
	    return true;
	  }
	  deselectDate(date, options = {}) {
	    if (!isDateLike(date)) {
	      return false;
	    }
	    const dateToDeselect = this.createDate(date);
	    const {
	      emitEvents,
	      render,
	      updateInputs
	    } = {
	      emitEvents: true,
	      render: true,
	      updateInputs: true,
	      ...options
	    };
	    if (emitEvents && !babelHelpers.classPrivateFieldLooseBase(this, _canDeselectDate)[_canDeselectDate](dateToDeselect)) {
	      return false;
	    }
	    if (this.isMultipleMode() && babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].length <= this.getMinDays()) {
	      return false;
	    }
	    const index = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].findIndex(selectedDate => {
	      return isDatesEqual(dateToDeselect, selectedDate);
	    });
	    if (index === -1) {
	      return false;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].splice(index, 1);
	    if (emitEvents) {
	      this.emit(DatePickerEvent.DESELECT, {
	        date: dateToDeselect
	      });
	      this.emit(DatePickerEvent.SELECT_CHANGE);
	    }
	    if (this.isRendered() && render) {
	      this.getPicker().render();
	    }
	    if (updateInputs) {
	      this.updateInputFields();
	    }
	    return true;
	  }
	  deselectAll(options = {}) {
	    const dates = [...babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates]];
	    dates.forEach(date => {
	      this.deselectDate(date, options);
	    });
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].length === 0;
	  }
	  getSelectedDates() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates];
	  }
	  getSelectedDate() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates][0] || null;
	  }
	  getRangeStart() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates][0] || null;
	  }
	  getRangeEnd() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates][1] || null;
	  }
	  isDateSelected(date, precision = 'day') {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates].some(selectedDate => {
	      return isDatesEqual(date, selectedDate, precision);
	    });
	  }
	  setFocusDate(date, options = {}) {
	    if (!isDateLike(date) && date !== null) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _focusDate)[_focusDate] = date === null ? null : this.createDate(date);
	    const {
	      render,
	      adjustViewDate
	    } = {
	      render: true,
	      adjustViewDate: true,
	      ...options
	    };
	    if (adjustViewDate && this.isDateOutOfView(babelHelpers.classPrivateFieldLooseBase(this, _focusDate)[_focusDate])) {
	      this.setViewDate(createUtcDate(babelHelpers.classPrivateFieldLooseBase(this, _focusDate)[_focusDate].getUTCFullYear(), babelHelpers.classPrivateFieldLooseBase(this, _focusDate)[_focusDate].getUTCMonth()));
	    }
	    if (this.isRendered() && render) {
	      this.getPicker().render();
	    }
	  }
	  getFocusDate() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _focusDate)[_focusDate];
	  }
	  getInitialFocusDate(mode = 'datetime') {
	    const focusDate = this.getFocusDate();
	    if (focusDate !== null) {
	      return focusDate;
	    }
	    if (mode === 'range-start') {
	      const {
	        year,
	        month,
	        day
	      } = this.getViewDateParts();
	      return this.getRangeStart() || createUtcDate(year, month, day);
	    }
	    if (mode === 'range-end') {
	      const {
	        year,
	        month,
	        day
	      } = this.getViewDateParts();
	      return this.getRangeEnd() || createUtcDate(year, month, day);
	    }
	    const selectedDates = this.getSelectedDates();
	    if (main_core.Type.isArrayFilled(selectedDates)) {
	      const date = selectedDates.find(selectedDate => {
	        return !this.isDateOutOfView(selectedDate);
	      });
	      if (main_core.Type.isDate(date)) {
	        return date;
	      }
	    }
	    return this.getViewDate();
	  }
	  isDateOutOfView(date) {
	    if (date === null) {
	      return false;
	    }
	    let isOutOfView = false;
	    const {
	      year: currentViewYear
	    } = this.getViewDateParts();
	    const {
	      year: focusYear
	    } = getDate(date);
	    if (this.getCurrentView() === 'day') {
	      const dayPicker = this.getPicker('day');
	      const firstDay = dayPicker.getFirstDay();
	      const lastDay = dayPicker.getLastDay();
	      const focusDate = createUtcDate(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate());
	      isOutOfView = focusDate < firstDay || focusDate >= lastDay;
	    } else if (this.getCurrentView() === 'month') {
	      isOutOfView = currentViewYear !== focusYear;
	    } else if (this.getCurrentView() === 'year') {
	      const yearPicker = this.getPicker('year');
	      const firstYear = yearPicker.getFirstYear();
	      const lastYear = yearPicker.getLastYear();
	      isOutOfView = focusYear < firstYear || focusYear > lastYear;
	    }
	    return isOutOfView;
	  }
	  setCurrentView(view) {
	    var _this$getPicker, _this$getPicker2, _this$getPicker3;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentView)[_currentView] === view) {
	      return;
	    }
	    const picker = this.getPicker(view);
	    if (picker === null) {
	      return;
	    }
	    main_core.Dom.style((_this$getPicker = this.getPicker()) == null ? void 0 : _this$getPicker.getContainer(), 'display', 'none');
	    main_core.Dom.attr((_this$getPicker2 = this.getPicker()) == null ? void 0 : _this$getPicker2.getContainer(), 'inert', true);
	    (_this$getPicker3 = this.getPicker()) == null ? void 0 : _this$getPicker3.onHide();
	    babelHelpers.classPrivateFieldLooseBase(this, _currentView)[_currentView] = view;
	    this.setFocusDate(null, {
	      render: false
	    });
	    if (!picker.isRendered()) {
	      picker.renderTo(this.getViewsContainer());
	    }
	    this.focus();
	    main_core.Dom.style(picker.getContainer(), 'display', null);
	    main_core.Dom.attr(picker.getContainer(), 'inert', null);
	    picker.onShow();
	    picker.render();
	  }
	  getCurrentView() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentView)[_currentView];
	  }
	  getPicker(pickerId) {
	    const currentPickerId = main_core.Type.isStringFilled(pickerId) ? pickerId : babelHelpers.classPrivateFieldLooseBase(this, _currentView)[_currentView];
	    let view = babelHelpers.classPrivateFieldLooseBase(this, _views)[_views].get(currentPickerId) || null;
	    if (view === null) {
	      view = babelHelpers.classPrivateFieldLooseBase(this, _createPicker)[_createPicker](currentPickerId);
	      if (view !== null) {
	        babelHelpers.classPrivateFieldLooseBase(this, _views)[_views].set(currentPickerId, view);
	      }
	    }
	    return view;
	  }
	  getType() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _type)[_type];
	  }
	  getFirstWeekDay() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _firstWeekDay)[_firstWeekDay];
	  }
	  getNumberOfMonths() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _numberOfMonths)[_numberOfMonths];
	  }
	  shouldShowWeekDays() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _showWeekDays)[_showWeekDays];
	  }
	  shouldShowWeekNumbers() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _showWeekNumbers)[_showWeekNumbers];
	  }
	  shouldShowOutsideDays() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _showOutsideDays)[_showOutsideDays];
	  }
	  getWeekends() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _weekends)[_weekends];
	  }
	  isWeekend(date) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _weekends)[_weekends].includes(date.getUTCDay());
	  }
	  isHoliday(date) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _holidays)[_holidays].some(([day, month]) => {
	      return date.getUTCDate() === day && date.getUTCMonth() === month;
	    });
	  }
	  isWorkday(date) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _workdays)[_workdays].some(([day, month]) => {
	      return date.getUTCDate() === day && date.getUTCMonth() === month;
	    });
	  }
	  isDayOff(date) {
	    return !this.isWorkday(date) && (this.isWeekend(date) || this.isHoliday(date));
	  }
	  isTimeEnabled() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _enableTime)[_enableTime];
	  }
	  setDefaultTime(time) {
	    if (main_core.Type.isStringFilled(time) && /([01]{1,2}\d|2[0-3]):[0-5]\d(:[0-5]\d)?/.test(time)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _defaultTime)[_defaultTime] = time;
	    }
	  }
	  getDefaultTime() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _defaultTime)[_defaultTime];
	  }
	  setDefaultTimeSpan(minutes) {
	    if (main_core.Type.isNumber(minutes) && minutes >= 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _defaultTimeSpan)[_defaultTimeSpan] = minutes;
	    }
	  }
	  getDefaultTimeSpan() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _defaultTimeSpan)[_defaultTimeSpan];
	  }
	  getDefaultTimeParts() {
	    const parts = this.getDefaultTime().split(':');
	    return {
	      hours: Number(parts[0] || 0),
	      minutes: Number(parts[1] || 0),
	      seconds: Number(parts[2] || 0)
	    };
	  }
	  getTimePickerStyle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _timePickerStyle)[_timePickerStyle];
	  }
	  shouldCutZeroTime() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cutZeroTime)[_cutZeroTime];
	  }
	  shouldAllowSeconds() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _allowSeconds)[_allowSeconds];
	  }
	  setToggleSelected(flag) {
	    if (main_core.Type.isBoolean(flag) || main_core.Type.isNull(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _toggleSelected)[_toggleSelected] = flag;
	    }
	  }
	  shouldToggleSelected() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _toggleSelected)[_toggleSelected] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _toggleSelected)[_toggleSelected];
	    }
	    return this.isMultipleMode();
	  }
	  setMaxDays(days) {
	    if (main_core.Type.isNumber(days) && days > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _maxDays)[_maxDays] = days;
	    }
	  }
	  getMaxDays() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _maxDays)[_maxDays];
	  }
	  setMinDays(days) {
	    if (main_core.Type.isNumber(days) && days > 0) {
	      babelHelpers.classPrivateFieldLooseBase(this, _minDays)[_minDays] = days;
	    }
	  }
	  getMinDays() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _minDays)[_minDays];
	  }
	  isFullYear() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _fullYear)[_fullYear];
	  }
	  isAmPmMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _amPmMode)[_amPmMode];
	  }
	  getMinuteStep() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _minuteStep)[_minuteStep];
	  }
	  getMinuteStepByDate(date) {
	    let step = this.getMinuteStep();
	    if (!main_core.Type.isDate(date)) {
	      return step;
	    }
	    const selectedMinute = date.getUTCMinutes();
	    if (selectedMinute > 0 && selectedMinute % step !== 0) {
	      // Reduce a step to show a selected minute
	      const availableSteps = [30, 15, 10, 5, 1];
	      const index = availableSteps.indexOf(selectedMinute);
	      const steps = index === -1 ? [1] : availableSteps.slice(index);
	      for (const newStep of steps) {
	        if (selectedMinute % newStep === 0) {
	          step = newStep;
	          break;
	        }
	      }
	    }
	    return step;
	  }
	  getToday() {
	    return this.createDate(new Date());
	  }
	  show() {
	    this.updateFromInputFields();
	    if (this.isInline()) {
	      if (!this.isRendered()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _render)[_render]();
	      }

	      // Dom.removeClass(this.getContainer(), '--hidden');
	    } else {
	      this.getPopup().show();
	    }
	  }
	  hide() {
	    if (!this.isRendered() || this.isInline()) {
	      return;
	    }

	    // if (this.isInline())
	    // {
	    // Dom.addClass(this.getContainer(), '--hidden');
	    // }

	    this.getPopup().close();
	  }
	  isOpen() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] !== null && babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].isShown();
	  }
	  adjustPosition() {
	    if (this.isRendered() && this.isOpen()) {
	      this.getPopup().adjustPosition();
	    }
	  }
	  toggle() {
	    if (this.isOpen()) {
	      this.hide();
	    } else {
	      this.show();
	    }
	  }
	  focus() {
	    if (this.isRendered()) {
	      this.getContainer().tabIndex = 0;
	      this.getContainer().focus({
	        preventScroll: true
	      });
	      this.getContainer().tabIndex = -1;
	    }
	  }
	  setSingleOpening(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _singleOpening)[_singleOpening] = flag;
	    }
	  }
	  isSingleOpening() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _singleOpening)[_singleOpening];
	  }
	  setDayColors(options) {
	    if (!main_core.Type.isArray(options)) {
	      return;
	    }
	    const dayColors = [];
	    for (const option of options) {
	      if (!main_core.Type.isStringFilled(option.bgColor) && !main_core.Type.isStringFilled(option.textColor)) {
	        continue;
	      }
	      const matchers = babelHelpers.classPrivateFieldLooseBase(this, _createDateMatchers)[_createDateMatchers](option.matcher);
	      if (main_core.Type.isArrayFilled(matchers)) {
	        dayColors.push({
	          bgColor: main_core.Type.isStringFilled(option.bgColor) ? option.bgColor : null,
	          textColor: main_core.Type.isStringFilled(option.textColor) ? option.textColor : null,
	          matchers
	        });
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _dayColors)[_dayColors] = dayColors;
	    if (this.isRendered()) {
	      this.getPicker().render();
	    }
	  }
	  getDayColor(day) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _dayColors)[_dayColors].find(dayColor => isDateMatch(day, dayColor.matchers)) || null;
	  }
	  setDayMarks(options) {
	    if (!main_core.Type.isArray(options)) {
	      return;
	    }
	    const dayMarks = [];
	    for (const option of options) {
	      if (!main_core.Type.isStringFilled(option.bgColor)) {
	        continue;
	      }
	      const matchers = babelHelpers.classPrivateFieldLooseBase(this, _createDateMatchers)[_createDateMatchers](option.matcher);
	      if (main_core.Type.isArrayFilled(matchers)) {
	        dayMarks.push({
	          bgColor: option.bgColor,
	          matchers
	        });
	      }
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _dayMarks)[_dayMarks] = dayMarks;
	    if (this.isRendered()) {
	      this.getPicker().render();
	    }
	  }
	  getDayMarks(day) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _dayMarks)[_dayMarks].filter(dayMark => isDateMatch(day, dayMark.matchers));
	  }
	  getPopup() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] !== null) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	    }
	    const popupOptions = {
	      ...babelHelpers.classPrivateFieldLooseBase(this, _popupOptions)[_popupOptions]
	    };
	    const userEvents = popupOptions.events;
	    delete popupOptions.events;
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] = new main_popup.Popup({
	      contentPadding: 0,
	      padding: 0,
	      offsetTop: 5,
	      bindElement: this.getTargetNode(),
	      bindOptions: {
	        forceBindPosition: true
	      },
	      autoHide: this.isAutoHide(),
	      closeByEsc: this.shouldHideByEsc(),
	      cacheable: this.isCacheable(),
	      content: this.getContainer(),
	      autoHideHandler: babelHelpers.classPrivateFieldLooseBase(this, _handleAutoHide)[_handleAutoHide].bind(this),
	      events: {
	        onFirstShow: babelHelpers.classPrivateFieldLooseBase(this, _handlePopupFirstShow)[_handlePopupFirstShow].bind(this),
	        onShow: babelHelpers.classPrivateFieldLooseBase(this, _handlePopupShow)[_handlePopupShow].bind(this),
	        onClose: babelHelpers.classPrivateFieldLooseBase(this, _handlePopupClose)[_handlePopupClose].bind(this),
	        onDestroy: babelHelpers.classPrivateFieldLooseBase(this, _handlePopupDestroy)[_handlePopupDestroy].bind(this)
	      },
	      ...popupOptions
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].subscribeFromOptions(userEvents);
	    return babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup];
	  }
	  setHideOnSelect(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hideOnSelect)[_hideOnSelect] = flag;
	    }
	  }
	  shouldHideOnSelect() {
	    if (this.isInline()) {
	      return false;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _hideOnSelect)[_hideOnSelect];
	  }
	  setDateSeparator(separator) {
	    if (main_core.Type.isStringFilled(separator)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _dateSeparator)[_dateSeparator] = separator;
	    }
	  }
	  getDateSeparator() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _dateSeparator)[_dateSeparator];
	  }
	  setInputField(field) {
	    const input = babelHelpers.classPrivateFieldLooseBase(this, _getInputField)[_getInputField](field);
	    if (input !== null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _inputField)[_inputField] = input;
	      babelHelpers.classPrivateFieldLooseBase(this, _bindInputEvents)[_bindInputEvents](input);
	    }
	  }
	  setRangeStartInput(field) {
	    const input = babelHelpers.classPrivateFieldLooseBase(this, _getInputField)[_getInputField](field);
	    if (input !== null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _rangeStartInput)[_rangeStartInput] = input;
	      babelHelpers.classPrivateFieldLooseBase(this, _bindInputEvents)[_bindInputEvents](input);
	    }
	  }
	  setRangeEndInput(field) {
	    const input = babelHelpers.classPrivateFieldLooseBase(this, _getInputField)[_getInputField](field);
	    if (input !== null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _rangeEndInput)[_rangeEndInput] = input;
	      babelHelpers.classPrivateFieldLooseBase(this, _bindInputEvents)[_bindInputEvents](input);
	    }
	  }
	  shouldUseInputEvents() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _useInputEvents)[_useInputEvents];
	  }
	  getInputField() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _inputField)[_inputField];
	  }
	  getRangeStartInput() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rangeStartInput)[_rangeStartInput];
	  }
	  getRangeEndInput() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rangeEndInput)[_rangeEndInput];
	  }
	  updateInputFields() {
	    if (this.isSingleMode()) {
	      if (this.getType() === 'time') {
	        babelHelpers.classPrivateFieldLooseBase(this, _setInputDate)[_setInputDate](this.getInputField(), this.getSelectedDate(), this.getTimeFormat());
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _setInputDate)[_setInputDate](this.getInputField(), this.getSelectedDate());
	      }
	    } else if (this.isMultipleMode()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setInputDate)[_setInputDate](this.getInputField(), this.getSelectedDates().map(date => this.formatDate(date)).join(this.getDateSeparator()));
	    } else if (this.isRangeMode()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _setInputDate)[_setInputDate](this.getRangeStartInput(), this.getRangeStart());
	      babelHelpers.classPrivateFieldLooseBase(this, _setInputDate)[_setInputDate](this.getRangeEndInput(), this.getRangeEnd());
	    }
	  }
	  updateFromInputFields() {
	    if (this.isSingleMode() && this.getInputField() !== null) {
	      const inputDate = babelHelpers.classPrivateFieldLooseBase(this, _getDateFromInput)[_getDateFromInput](this.getInputField());
	      if (inputDate === null) {
	        this.deselectAll({
	          updateInputs: false,
	          emitEvents: false
	        });
	      } else {
	        this.selectDate(inputDate, {
	          updateInputs: false,
	          emitEvents: false
	        });
	      }
	    } else if (this.isMultipleMode() && this.getInputField() !== null) {
	      const value = this.getInputField().value.trim();
	      const inputDates = value.split(this.getDateSeparator().trim()).map(part => this.createDate(part.trim())).filter(date => date !== null);
	      this.deselectAll({
	        updateInputs: false,
	        emitEvents: false
	      });
	      this.selectDates(inputDates, {
	        updateInputs: false,
	        emitEvents: false
	      });
	    } else if (this.isRangeMode() && this.getRangeStartInput() !== null) {
	      const rangeStart = babelHelpers.classPrivateFieldLooseBase(this, _getDateFromInput)[_getDateFromInput](this.getRangeStartInput());
	      const rangeEnd = babelHelpers.classPrivateFieldLooseBase(this, _getDateFromInput)[_getDateFromInput](this.getRangeEndInput());
	      if (rangeStart === null) {
	        this.deselectAll({
	          updateInputs: false,
	          emitEvents: false
	        });
	      } else {
	        this.selectRange(rangeStart, rangeEnd, {
	          updateInputs: false,
	          emitEvents: false
	        });
	      }
	    }
	  }
	  getLocale() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _locale)[_locale];
	  }
	  isRendered() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _rendered$1)[_rendered$1];
	  }
	  getContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].remember('container', () => {
	      const classes = ['ui-date-picker'];
	      if (this.isInline()) {
	        classes.push('--inline');
	      }
	      if (this.shouldHideHeader()) {
	        classes.push('--hide-header');
	      }
	      classes.push(`--${this.getType()}-picker`);
	      return main_core.Tag.render(_t$6 || (_t$6 = _$5`
				<div tabindex="-1" onkeyup="${0}" class="${0}">
					${0}
				</div>
			`), babelHelpers.classPrivateFieldLooseBase(this, _handleContainerKeyUp)[_handleContainerKeyUp].bind(this), classes.join(' '), this.getViewsContainer());
	    });
	  }
	  getViewsContainer() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].remember('views', () => {
	      return main_core.Tag.render(_t2$6 || (_t2$6 = _$5`<div class="ui-date-picker-views"></div>`));
	    });
	  }
	  isMultipleMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectionMode)[_selectionMode] === 'multiple';
	  }
	  isSingleMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectionMode)[_selectionMode] === 'single';
	  }
	  isRangeMode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _selectionMode)[_selectionMode] === 'range';
	  }
	  isInline() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _inline)[_inline];
	  }
	  isFocused() {
	    const rootContainer = this.getContainer();
	    const activeElement = rootContainer.ownerDocument.activeElement;
	    return rootContainer.contains(activeElement) || rootContainer === activeElement;
	  }
	  setAutoFocus(flag) {
	    if (main_core.Type.isBoolean(flag)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _autoFocus)[_autoFocus] = flag;
	    }
	  }
	  isAutoFocus() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _autoFocus)[_autoFocus];
	  }
	  setTargetNode(node) {
	    if (!main_core.Type.isDomNode(node) && !main_core.Type.isNull(node) && !main_core.Type.isObject(node)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode] = node;
	    if (this.isRendered()) {
	      this.getPopup().setBindElement(babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode]);
	      this.getPopup().adjustPosition();
	    }
	  }
	  getTargetNode() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _targetNode)[_targetNode];
	  }
	  setAutoHide(enable) {
	    if (main_core.Type.isBoolean(enable)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _autoHide)[_autoHide] = enable;
	      if (this.isRendered()) {
	        this.getPopup().setAutoHide(enable);
	      }
	    }
	  }
	  isAutoHide() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _autoHide)[_autoHide];
	  }
	  setHideByEsc(enable) {
	    if (main_core.Type.isBoolean(enable)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hideByEsc)[_hideByEsc] = enable;
	      if (this.isRendered()) {
	        this.getPopup().setClosingByEsc(enable);
	      }
	    }
	  }
	  shouldHideByEsc() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _hideByEsc)[_hideByEsc];
	  }
	  isCacheable() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _cacheable)[_cacheable];
	  }
	  setCacheable(cacheable) {
	    if (main_core.Type.isBoolean(cacheable)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _cacheable)[_cacheable] = cacheable;
	      if (this.isRendered()) {
	        this.getPopup().setCacheable(cacheable);
	      }
	    }
	  }
	  setHideHeader(enable) {
	    if (main_core.Type.isBoolean(enable)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _hideHeader)[_hideHeader] = enable;
	      if (this.isRendered()) {
	        if (enable) {
	          main_core.Dom.addClass(this.getContainer(), '--hide-header');
	        } else {
	          main_core.Dom.removeClass(this.getContainer(), '--hide-header');
	        }
	      }
	    }
	  }
	  shouldHideHeader() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _hideHeader)[_hideHeader];
	  }
	  createDate(date) {
	    return createDate(date, this.getDateFormat());
	  }
	  formatDate(date, format = null) {
	    const midnight = date.getUTCHours() === 0 && date.getUTCMinutes() === 0 && date.getUTCSeconds() === 0;
	    const dateFormat = format === null ? this.getDateFormat() : format;
	    let result = main_date.DateTimeFormat.format(dateFormat, date, null, true);
	    if (this.isTimeEnabled() && midnight && this.shouldCutZeroTime()) {
	      result = result.replaceAll(/\s*12:00:00 am\s*/gi, '').replaceAll(/\s*12:00 am\s*/gi, '').replaceAll(/\s*00:00:00\s*/g, '').replaceAll(/\s*00:00\s*/g, '');
	    }
	    return result;
	  }
	  formatTime(date, format = null) {
	    return main_date.DateTimeFormat.format(format === null ? this.getTimeFormat() : format, date, null, true);
	  }
	  getDateFormat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _dateFormat)[_dateFormat];
	  }
	  getTimeFormat() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _timeFormat)[_timeFormat];
	  }
	  destroy() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _destroying)[_destroying]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _destroying)[_destroying] = true;
	    this.emit(DatePickerEvent.DESTROY);
	    if (this.isRendered()) {
	      main_core.Dom.remove(this.getContainer());
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindInputEvents)[_unbindInputEvents](this.getInputField());
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindInputEvents)[_unbindInputEvents](this.getRangeStartInput());
	    babelHelpers.classPrivateFieldLooseBase(this, _unbindInputEvents)[_unbindInputEvents](this.getRangeEndInput());
	    if (babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup] !== null) {
	      babelHelpers.classPrivateFieldLooseBase(this, _popup)[_popup].destroy();
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _views)[_views] = null;
	    babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates] = null;
	    Object.setPrototypeOf(this, null);
	  }
	}
	function _canSelectDate2(date) {
	  const event = new main_core_events.BaseEvent({
	    data: {
	      date
	    }
	  });
	  this.emit(DatePickerEvent.BEFORE_SELECT, event);
	  return !event.isDefaultPrevented();
	}
	function _canDeselectDate2(date) {
	  const event = new main_core_events.BaseEvent({
	    data: {
	      date
	    }
	  });
	  this.emit(DatePickerEvent.BEFORE_DESELECT, event);
	  return !event.isDefaultPrevented();
	}
	function _setType2(type) {
	  if (['date', 'year', 'month', 'time'].includes(type)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _type)[_type] = type;
	  }
	}
	function _createDateMatchers2(matcher) {
	  if (main_core.Type.isUndefined(matcher)) {
	    return [];
	  }
	  const result = [];
	  const matchers = main_core.Type.isArray(matcher) ? [...matcher] : [matcher];
	  matchers.forEach(matcherValue => {
	    if (main_core.Type.isArray(matcherValue)) {
	      const dates = [];
	      matcherValue.forEach(dateLike => {
	        if (!isDateLike(dateLike)) {
	          return;
	        }
	        const date = this.createDate(matcherValue);
	        if (date !== null) {
	          dates.push(date);
	        }
	      });
	      result.push(dates);
	    } else if (isDateLike(matcherValue)) {
	      const date = this.createDate(matcherValue);
	      if (date !== null) {
	        result.push(date);
	      }
	    } else if (main_core.Type.isBoolean(matcherValue) || main_core.Type.isFunction(matcherValue)) {
	      result.push(matcherValue);
	    }
	  });
	  return result;
	}
	function _setSelectionMode2(mode) {
	  if (this.getType() !== 'date') {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectionMode)[_selectionMode] = 'single';
	  } else if (['single', 'multiple', 'range', 'none'].includes(mode)) {
	    babelHelpers.classPrivateFieldLooseBase(this, _selectionMode)[_selectionMode] = mode;
	  }
	}
	function _getInputField2(field) {
	  if (main_core.Type.isStringFilled(field)) {
	    const element = document.querySelector(field);
	    if (main_core.Type.isElementNode(element) || element.nodeName === 'INPUT' || element.nodeName === 'TEXTAREA') {
	      return element;
	    }
	    console.error(`Date Picker: a form element was not found (${field}).`);
	  } else if (main_core.Type.isElementNode(field) && (field.nodeName === 'INPUT' || field.nodeName === 'TEXTAREA')) {
	    return field;
	  }
	  return null;
	}
	function _bindInputEvents2(input) {
	  if (!this.shouldUseInputEvents()) {
	    return;
	  }
	  main_core.Event.bind(input, 'click', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].remember('click-handler', () => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _handleInputClick)[_handleInputClick].bind(this);
	  }));
	  main_core.Event.bind(input, 'focusout', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].remember('focusout-handler', () => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _handleInputFocusOut)[_handleInputFocusOut].bind(this);
	  }));
	  main_core.Event.bind(input, 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].remember('keydown-handler', () => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _handleInputKeyDown)[_handleInputKeyDown].bind(this);
	  }));
	  main_core.Event.bind(input, 'input', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].remember('change-handler', () => {
	    return babelHelpers.classPrivateFieldLooseBase(this, _handleInputChange)[_handleInputChange].bind(this);
	  }));
	}
	function _unbindInputEvents2(input) {
	  main_core.Event.unbind(input, 'click', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].get('click-handler'));
	  main_core.Event.unbind(input, 'focusout', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].get('focusout-handler'));
	  main_core.Event.unbind(input, 'keydown', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].get('keydown-handler'));
	  main_core.Event.unbind(input, 'input', babelHelpers.classPrivateFieldLooseBase(this, _refs$6)[_refs$6].get('change-handler'));
	}
	function _handleInputClick2(event) {
	  if (this.isRangeMode()) {
	    this.setTargetNode(event.target);
	    if (!this.isOpen()) {
	      this.show();
	    }
	  } else {
	    this.show();
	  }
	}
	function _handleInputFocusOut2(event) {
	  if (!this.getContainer().contains(event.relatedTarget)) {
	    this.hide();
	  }
	}
	function _handleInputKeyDown2(event) {
	  if (event.key === 'Tab' && !event.shiftKey && this.isOpen()) {
	    event.preventDefault();
	    const currentPickerContainer = this.getPicker().getContainer();
	    const [, next] = getFocusableBoundaryElements(currentPickerContainer, element => element.dataset.tabPriority === 'true');
	    if (next === null) {
	      this.focus();
	    } else {
	      next.focus({
	        preventScroll: true,
	        focusVisible: true
	      });
	      babelHelpers.classPrivateFieldLooseBase(this, _keyboardNavigation)[_keyboardNavigation].setLastFocusElement(next);
	    }
	  }
	}
	function _handleInputChange2(event) {
	  if (this.isOpen()) {
	    this.updateFromInputFields();
	  }
	}
	function _handleAutoHide2(event) {
	  const target = event.target;
	  const el = this.getPopup().getPopupContainer();
	  if (target === el || el.contains(target)) {
	    return false;
	  }
	  if (this.isRangeMode()) {
	    const anotherInput = (this.getRangeStartInput() === target || this.getRangeEndInput() === target) && this.getTargetNode() !== target;
	    return !anotherInput;
	  }
	  return true;
	}
	function _focusInputField2() {
	  if (this.getInputField() !== null) {
	    this.getInputField().focus({
	      preventScroll: true
	    });
	  } else if (this.getRangeStartInput() !== null) {
	    this.getRangeStartInput().focus({
	      preventScroll: true
	    });
	  }
	}
	function _getDateFromInput2(input) {
	  if (input === null) {
	    return null;
	  }
	  const value = input.value.trim();
	  if (!main_core.Type.isStringFilled(value)) {
	    return null;
	  }
	  if (this.getType() === 'time') {
	    return createDate(value, this.getTimeFormat());
	  }
	  return this.createDate(value);
	}
	function _setInputDate2(input, date, format = null) {
	  if (input !== null) {
	    let value = '';
	    if (date === null) {
	      value = '';
	    } else if (main_core.Type.isString(date)) {
	      value = date;
	    } else {
	      value = this.formatDate(date, format);
	    }

	    // eslint-disable-next-line no-param-reassign
	    input.value = value;
	  }
	}
	function _getDefaultDateFormat2() {
	  if (this.getType() === 'year') {
	    return 'Y';
	  }
	  if (this.getType() === 'month') {
	    return 'f - Y';
	  }
	  if (this.isTimeEnabled()) {
	    if (this.shouldAllowSeconds()) {
	      return main_date.DateTimeFormat.getFormat('FORMAT_DATETIME');
	    }
	    return main_date.DateTimeFormat.getFormat('FORMAT_DATETIME').replace(/:s/i, '');
	  }
	  return main_date.DateTimeFormat.getFormat('FORMAT_DATE');
	}
	function _render2() {
	  if (this.isRendered()) {
	    return;
	  }
	  if (this.isInline() && this.getTargetNode() !== null) {
	    main_core.Dom.append(this.getContainer(), this.getTargetNode());
	  }
	  const views = ['day', 'month', 'year', 'time'];
	  const index = views.indexOf(this.getType());
	  const view = index === -1 ? 'day' : views[index];
	  this.setCurrentView(view);
	  babelHelpers.classPrivateFieldLooseBase(this, _rendered$1)[_rendered$1] = true;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _keyboardNavigation)[_keyboardNavigation] !== null) {
	    babelHelpers.classPrivateFieldLooseBase(this, _keyboardNavigation)[_keyboardNavigation].init();
	  }
	}
	function _createPicker2(pickerId) {
	  if (pickerId === 'day') {
	    const dayPicker = new DayPicker(this);
	    dayPicker.subscribe('onSelect', babelHelpers.classPrivateFieldLooseBase(this, _handleDaySelect)[_handleDaySelect].bind(this));
	    dayPicker.subscribe('onFocus', babelHelpers.classPrivateFieldLooseBase(this, _handleDayFocus)[_handleDayFocus].bind(this));
	    dayPicker.subscribe('onBlur', babelHelpers.classPrivateFieldLooseBase(this, _handleDayBlur)[_handleDayBlur].bind(this));
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
	    dayPicker.subscribe('onTimeClick', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeClick$1)[_handleTimeClick$1].bind(this, 'datetime'));
	    dayPicker.subscribe('onRangeStartClick', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeClick$1)[_handleTimeClick$1].bind(this, 'range-start'));
	    dayPicker.subscribe('onRangeEndClick', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeClick$1)[_handleTimeClick$1].bind(this, 'range-end'));
	    return dayPicker;
	  }
	  if (pickerId === 'month') {
	    const monthPicker = new MonthPicker(this);
	    monthPicker.subscribe('onSelect', babelHelpers.classPrivateFieldLooseBase(this, _handleMonthSelect)[_handleMonthSelect].bind(this));
	    monthPicker.subscribe('onFocus', babelHelpers.classPrivateFieldLooseBase(this, _handleMonthFocus)[_handleMonthFocus].bind(this));
	    monthPicker.subscribe('onBlur', babelHelpers.classPrivateFieldLooseBase(this, _handleMonthBlur)[_handleMonthBlur].bind(this));
	    monthPicker.subscribe('onPrevBtnClick', () => {
	      const {
	        year,
	        month
	      } = getDate(this.getViewDate());
	      const viewDate = createUtcDate(year - 1, month, 1);
	      this.setViewDate(viewDate);
	    });
	    monthPicker.subscribe('onNextBtnClick', () => {
	      const {
	        year,
	        month
	      } = getDate(this.getViewDate());
	      const viewDate = createUtcDate(year + 1, month, 1);
	      this.setViewDate(viewDate);
	    });
	    monthPicker.subscribe('onTitleClick', () => this.setCurrentView('year'));
	    return monthPicker;
	  }
	  if (pickerId === 'year') {
	    const yearPicker = new YearPicker(this);
	    yearPicker.subscribe('onSelect', babelHelpers.classPrivateFieldLooseBase(this, _handleYearSelect)[_handleYearSelect].bind(this));
	    yearPicker.subscribe('onFocus', babelHelpers.classPrivateFieldLooseBase(this, _handleYearFocus)[_handleYearFocus].bind(this));
	    yearPicker.subscribe('onBlur', babelHelpers.classPrivateFieldLooseBase(this, _handleYearBlur)[_handleYearBlur].bind(this));
	    yearPicker.subscribe('onPrevBtnClick', () => {
	      const {
	        year
	      } = getDate(this.getViewDate());
	      const viewDate = createUtcDate(year - 12, 0, 1);
	      this.setViewDate(viewDate);
	    });
	    yearPicker.subscribe('onNextBtnClick', () => {
	      const {
	        year
	      } = getDate(this.getViewDate());
	      const viewDate = createUtcDate(year + 12, 0, 1);
	      this.setViewDate(viewDate);
	    });
	    return yearPicker;
	  }
	  if (pickerId === 'time') {
	    const timePicker = this.getTimePickerStyle() === 'wheel' ? new TimePickerWheel(this) : new TimePickerGrid(this);
	    if (this.isRangeMode()) {
	      timePicker.subscribe('onSelect', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeRangeSelect)[_handleTimeRangeSelect].bind(this));
	    } else {
	      timePicker.subscribe('onSelect', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeSelect)[_handleTimeSelect].bind(this));
	    }
	    timePicker.subscribe('onFocus', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeFocus)[_handleTimeFocus].bind(this));
	    timePicker.subscribe('onBlur', babelHelpers.classPrivateFieldLooseBase(this, _handleTimeBlur)[_handleTimeBlur].bind(this));
	    timePicker.subscribe('onPrevBtnClick', () => this.setCurrentView('day'));
	    timePicker.subscribe('onTitleClick', () => this.setCurrentView('day'));
	    return timePicker;
	  }
	  return null;
	}
	function _handleContainerKeyUp2(event) {
	  if (this.isInline()) {
	    return;
	  }
	  if (event.key === 'Escape' && this.shouldHideByEsc()) {
	    this.hide();
	  }
	}
	function _handleTimeClick2$1(mode) {
	  const timePicker = this.getPicker('time');
	  const selectTime = mode === 'range-start' && this.getRangeStart() !== null || mode === 'range-end' && this.getRangeEnd() !== null || this.getSelectedDate() !== null;
	  if (selectTime) {
	    timePicker.setMode(mode);
	    this.setCurrentView('time');
	  }
	}
	function _handleDaySelect2(event) {
	  const {
	    year,
	    month,
	    day
	  } = event.getData();
	  let selectedDate = createUtcDate(year, month, day);
	  if (this.isRangeMode()) {
	    const currentRange = babelHelpers.classPrivateFieldLooseBase(this, _selectedDates)[_selectedDates];
	    if (currentRange.length === 0) {
	      const {
	        hours,
	        minutes,
	        seconds
	      } = this.getDefaultTimeParts();
	      selectedDate = setTime(selectedDate, hours, minutes, seconds);
	    } else if (currentRange.length === 1) {
	      let {
	        hours,
	        minutes,
	        seconds
	      } = this.getDefaultTimeParts();
	      if (this.isDateSelected(selectedDate, 'day')) {
	        ({
	          hours,
	          minutes,
	          seconds
	        } = getDate(this.getRangeStart()));
	        minutes += this.getDefaultTimeSpan();
	      }
	      selectedDate = setTime(selectedDate, hours, minutes, seconds);
	    }
	    const range = addToRange(selectedDate, currentRange);
	    const [start, end] = range;
	    if (range.length === 0) {
	      this.deselectAll();
	    } else {
	      this.selectRange(start, end);
	    }
	  } else if (this.isDateSelected(selectedDate)) {
	    if (this.shouldToggleSelected()) {
	      this.deselectDate(selectedDate);
	    } else if (this.shouldHideOnSelect() && this.isSingleMode()) {
	      this.hide();
	    }
	  } else {
	    let {
	      hours,
	      minutes,
	      seconds
	    } = this.getDefaultTimeParts();
	    if (this.isSingleMode() && this.getSelectedDate() !== null) {
	      // save previous time
	      ({
	        hours,
	        minutes,
	        seconds
	      } = getDate(this.getSelectedDate()));
	    }
	    this.selectDate(createUtcDate(year, month, day, hours, minutes, seconds));
	    if (this.shouldHideOnSelect() && this.isSingleMode() && !this.isTimeEnabled()) {
	      this.hide();
	    }
	  }
	}
	function _handleDayFocus2(event) {
	  const {
	    year,
	    month,
	    day
	  } = event.getData();
	  const focusDate = createUtcDate(year, month, day);
	  if (!isDatesEqual(focusDate, this.getFocusDate())) {
	    this.setFocusDate(focusDate);
	  }
	}
	function _handleDayBlur2(event) {
	  this.setFocusDate(null);
	}
	function _handleMonthFocus2(event) {
	  const {
	    year,
	    month
	  } = event.getData();
	  const focusDate = createUtcDate(year, month);
	  if (!isDatesEqual(focusDate, this.getFocusDate(), 'month')) {
	    this.setFocusDate(focusDate);
	  }
	}
	function _handleMonthBlur2(event) {
	  this.setFocusDate(null);
	}
	function _handleYearFocus2(event) {
	  const {
	    year
	  } = event.getData();
	  const focusDate = createUtcDate(year);
	  if (!isDatesEqual(focusDate, this.getFocusDate(), 'year')) {
	    this.setFocusDate(focusDate);
	  }
	}
	function _handleYearBlur2(event) {
	  this.setFocusDate(null);
	}
	function _handleTimeFocus2(event) {
	  const {
	    hour,
	    minute
	  } = event.getData();
	  let focusDate = cloneDate(this.getInitialFocusDate());
	  if (main_core.Type.isNumber(hour)) {
	    focusDate = setTime(focusDate, hour, null, null);
	    this.setFocusDate(focusDate);
	  } else if (main_core.Type.isNumber(minute)) {
	    focusDate = setTime(focusDate, null, minute, null);
	    this.setFocusDate(focusDate);
	  }
	}
	function _handleTimeBlur2(event) {
	  this.setFocusDate(null);
	}
	function _handleMonthSelect2(event) {
	  const {
	    year
	  } = getDate(this.getViewDate());
	  const month = event.getData().month;
	  const date = createUtcDate(year, month);
	  if (this.getType() === 'month') {
	    this.selectDate(date);
	    if (this.shouldHideOnSelect()) {
	      this.hide();
	    }
	  } else {
	    this.setViewDate(date);
	    this.setCurrentView('day');
	  }
	}
	function _handleYearSelect2(event) {
	  const {
	    month
	  } = getDate(this.getViewDate());
	  const year = event.getData().year;
	  const date = createUtcDate(year, month);
	  if (this.getType() === 'year') {
	    this.selectDate(createUtcDate(year));
	    if (this.shouldHideOnSelect()) {
	      this.hide();
	    }
	  } else {
	    this.setViewDate(date);
	    this.setCurrentView('day');
	  }
	}
	function _handleTimeSelect2(event) {
	  let selectedDate = null;
	  if (this.getType() === 'time') {
	    selectedDate = this.getSelectedDate() === null ? ceilDate(this.getToday(), 'day') : cloneDate(this.getSelectedDate());
	  } else if (this.getSelectedDate() === null) {
	    return;
	  } else {
	    selectedDate = cloneDate(this.getSelectedDate());
	  }
	  const hideOrSwitchToDayView = () => {
	    if (this.shouldHideOnSelect()) {
	      this.hide();
	    } else if (this.getType() === 'date') {
	      this.setCurrentView('day');
	    }
	  };
	  const {
	    hour,
	    minute
	  } = event.getData();
	  if (main_core.Type.isNumber(hour)) {
	    const currentHour = this.getSelectedDate() === null ? -1 : selectedDate.getUTCHours();
	    if (currentHour === hour) {
	      hideOrSwitchToDayView();
	    } else {
	      selectedDate.setUTCHours(hour);
	      this.selectDate(selectedDate);
	    }
	  } else if (main_core.Type.isNumber(minute)) {
	    const currentMinute = this.getSelectedDate() === null ? -1 : selectedDate.getUTCMinutes();
	    if (currentMinute !== minute) {
	      selectedDate.setUTCMinutes(minute);
	      this.selectDate(selectedDate);
	    }
	    if (this.getTimePickerStyle() === 'grid') {
	      hideOrSwitchToDayView();
	    }
	  }
	}
	function _handleTimeRangeSelect2(event) {
	  const timePicker = event.getTarget();
	  const rangeEndChange = timePicker.getMode() === 'range-end';
	  let rangeStart = this.getRangeStart() === null ? null : cloneDate(this.getRangeStart());
	  let rangeEnd = this.getRangeEnd() === null ? null : cloneDate(this.getRangeEnd());
	  if (rangeStart === null || rangeEnd === null && rangeEndChange) {
	    return;
	  }
	  const switchToDayView = () => {
	    if (this.getType() === 'date' && this.getTimePickerStyle() === 'grid') {
	      this.setCurrentView('day');
	    }
	  };
	  const {
	    hour,
	    minute
	  } = event.getData();
	  if (main_core.Type.isNumber(hour)) {
	    if (rangeEndChange) {
	      const currentHour = rangeEnd.getUTCHours();
	      if (currentHour === hour) {
	        switchToDayView();
	        return;
	      }
	      rangeEnd.setUTCHours(hour);
	    } else {
	      const currentHour = rangeStart.getUTCHours();
	      if (currentHour === hour) {
	        switchToDayView();
	        return;
	      }
	      rangeStart.setUTCHours(hour);
	    }
	  } else if (main_core.Type.isNumber(minute)) {
	    if (rangeEndChange) {
	      const currentMinute = rangeEnd.getUTCMinutes();
	      if (currentMinute === minute) {
	        switchToDayView();
	        return;
	      }
	      rangeEnd.setUTCMinutes(minute);
	    } else {
	      const currentMinute = rangeStart.getUTCMinutes();
	      if (currentMinute === minute) {
	        switchToDayView();
	        return;
	      }
	      rangeStart.setUTCMinutes(minute);
	    }
	  }
	  if (rangeEnd !== null && rangeStart > rangeEnd) {
	    if (rangeEndChange) {
	      rangeStart = addDate(rangeEnd, 'minute', -this.getDefaultTimeSpan());
	    } else {
	      rangeEnd = addDate(rangeStart, 'minute', this.getDefaultTimeSpan());
	    }
	  }
	  this.selectRange(rangeStart, rangeEnd);
	  if (main_core.Type.isNumber(minute)) {
	    switchToDayView();
	  }
	}
	function _handlePopupShow2() {
	  if (!this.isFocused() && this.isAutoFocus()) {
	    this.focus();
	  }
	  if (this.isSingleOpening()) {
	    if (singleOpenDatePicker !== null) {
	      singleOpenDatePicker.hide();
	    }

	    // eslint-disable-next-line unicorn/no-this-assignment
	    singleOpenDatePicker = this;
	  }
	  this.emit('onShow');
	}
	function _handlePopupFirstShow2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _render)[_render]();
	  this.emit('onFirstShow');
	}
	function _handlePopupClose2() {
	  if (this.getType() === 'date') {
	    this.setCurrentView('day');
	  }
	  this.setFocusDate(null);
	  this.setViewDate(this.getDefaultViewDate());
	  if (this.isSingleOpening()) {
	    singleOpenDatePicker = null;
	  }
	  if (this.isFocused()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _focusInputField)[_focusInputField]();
	  }
	  this.emit('onHide');
	}
	function _handlePopupDestroy2() {
	  this.destroy();
	}

	function isValidDate(date) {
	  if (!main_core.Type.isDate(date)) {
	    return false;
	  }
	  return !Number.isNaN(date.getTime());
	}

	exports.DatePicker = DatePicker;
	exports.DatePickerEvent = DatePickerEvent;
	exports.addDate = addDate;
	exports.addToRange = addToRange;
	exports.ceilDate = ceilDate;
	exports.cloneDate = cloneDate;
	exports.convertToDbFormat = convertToDbFormat;
	exports.copyTime = copyTime;
	exports.createDate = createDate;
	exports.createUtcDate = createUtcDate;
	exports.floorDate = floorDate;
	exports.getDate = getDate;
	exports.getDaysInMonth = getDaysInMonth;
	exports.getFocusableBoundaryElements = getFocusableBoundaryElements;
	exports.getNextDate = getNextDate;
	exports.isDateAfter = isDateAfter;
	exports.isDateBefore = isDateBefore;
	exports.isDateLike = isDateLike;
	exports.isDateMatch = isDateMatch;
	exports.isDatesEqual = isDatesEqual;
	exports.isValidDate = isValidDate;
	exports.parseDate = parseDate;
	exports.setTime = setTime;

}((this.BX.UI.DatePicker = this.BX.UI.DatePicker || {}),BX.Main,BX.Event,BX.Main,BX.Cache,BX));
//# sourceMappingURL=date-picker.bundle.js.map
