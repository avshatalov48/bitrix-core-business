/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_date) {
	'use strict';

	const DateCode = {
	  shortTimeFormat: main_date.DateTimeFormat.getFormat('SHORT_TIME_FORMAT'),
	  shortDateFormat: main_date.DateTimeFormat.getFormat('SHORT_DATE_FORMAT'),
	  dayMonthFormat: main_date.DateTimeFormat.getFormat('DAY_MONTH_FORMAT'),
	  longDateFormat: main_date.DateTimeFormat.getFormat('LONG_DATE_FORMAT'),
	  dayOfWeekMonthFormat: main_date.DateTimeFormat.getFormat('DAY_OF_WEEK_MONTH_FORMAT'),
	  fullDateFormat: main_date.DateTimeFormat.getFormat('FULL_DATE_FORMAT'),
	  dayShortMonthFormat: main_date.DateTimeFormat.getFormat('DAY_SHORT_MONTH_FORMAT'),
	  mediumDateFormat: main_date.DateTimeFormat.getFormat('MEDIUM_DATE_FORMAT'),
	  defaultDateTime: main_date.DateTimeFormat.getFormat('FORMAT_DATETIME')
	};
	const Interval = {
	  tomorrow: 'tomorrow',
	  today: 'today',
	  yesterday: 'yesterday',
	  week: 'week',
	  year: 'year',
	  olderThanYear: 'olderThanYear'
	};
	const DateTemplate = {
	  notification: {
	    [Interval.today]: `today, ${DateCode.shortTimeFormat}`,
	    [Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
	    [Interval.year]: `${DateCode.dayMonthFormat}, ${DateCode.shortTimeFormat}`,
	    [Interval.olderThanYear]: `${DateCode.longDateFormat}, ${DateCode.shortTimeFormat}`
	  },
	  dateGroup: {
	    [Interval.today]: 'today',
	    [Interval.yesterday]: 'yesterday',
	    [Interval.year]: DateCode.dayOfWeekMonthFormat,
	    [Interval.olderThanYear]: DateCode.fullDateFormat
	  },
	  meeting: {
	    [Interval.tomorrow]: `tomorrow, ${DateCode.shortTimeFormat}`,
	    [Interval.today]: `today, ${DateCode.shortTimeFormat}`,
	    [Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
	    [Interval.year]: `${DateCode.dayShortMonthFormat}, ${DateCode.shortTimeFormat}`,
	    [Interval.olderThanYear]: `${DateCode.mediumDateFormat}, ${DateCode.shortTimeFormat}`
	  },
	  recent: {
	    [Interval.today]: DateCode.shortTimeFormat,
	    [Interval.week]: 'D',
	    [Interval.year]: DateCode.dayShortMonthFormat,
	    [Interval.olderThanYear]: DateCode.mediumDateFormat
	  },
	  messageReadStatus: {
	    [Interval.today]: `today, ${DateCode.shortTimeFormat}`,
	    [Interval.yesterday]: `yesterday, ${DateCode.shortTimeFormat}`,
	    [Interval.year]: `${DateCode.dayMonthFormat},  ${DateCode.shortTimeFormat}`,
	    [Interval.olderThanYear]: `${DateCode.dayMonthFormat} Y, ${DateCode.shortTimeFormat}`
	  }
	};

	var _date = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("date");
	var _matchingFunctions = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("matchingFunctions");
	var _isYesterday = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isYesterday");
	var _isToday = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isToday");
	var _isTomorrow = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isTomorrow");
	var _isCurrentWeek = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCurrentWeek");
	var _isCurrentYear = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCurrentYear");
	var _isSame = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSame");
	var _shiftDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("shiftDate");
	class DateFormatter {
	  static formatByCode(date, formatCode) {
	    return new DateFormatter(date).formatByCode(formatCode);
	  }
	  static formatByTemplate(date, template = {}) {
	    return new DateFormatter(date).formatByTemplate(template);
	  }
	  formatByCode(formatCode) {
	    return main_date.DateTimeFormat.format(formatCode, babelHelpers.classPrivateFieldLooseBase(this, _date)[_date]);
	  }
	  formatByTemplate(template = {}) {
	    const intervals = Object.keys(Interval);
	    const matchingInterval = intervals.find(interval => {
	      const templateHasInterval = !!template[interval];
	      if (!templateHasInterval) {
	        return false;
	      }
	      const matchingFunction = babelHelpers.classPrivateFieldLooseBase(this, _matchingFunctions)[_matchingFunctions][interval];
	      const intervalIsMatching = matchingFunction();
	      if (!intervalIsMatching) {
	        return false;
	      }

	      // it's a matching code from provided template
	      return true;
	    });
	    if (!matchingInterval) {
	      console.error('DateFormatter: no matching intervals were found for', template);
	      return;
	    }
	    const matchingCode = template[matchingInterval];
	    return this.formatByCode(matchingCode);
	  }
	  constructor(_date2) {
	    Object.defineProperty(this, _shiftDate, {
	      value: _shiftDate2
	    });
	    Object.defineProperty(this, _isSame, {
	      value: _isSame2
	    });
	    Object.defineProperty(this, _isCurrentYear, {
	      value: _isCurrentYear2
	    });
	    Object.defineProperty(this, _isCurrentWeek, {
	      value: _isCurrentWeek2
	    });
	    Object.defineProperty(this, _isTomorrow, {
	      value: _isTomorrow2
	    });
	    Object.defineProperty(this, _isToday, {
	      value: _isToday2
	    });
	    Object.defineProperty(this, _isYesterday, {
	      value: _isYesterday2
	    });
	    Object.defineProperty(this, _date, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _matchingFunctions, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _date)[_date] = _date2;
	    babelHelpers.classPrivateFieldLooseBase(this, _matchingFunctions)[_matchingFunctions] = {
	      [Interval.tomorrow]: () => babelHelpers.classPrivateFieldLooseBase(this, _isTomorrow)[_isTomorrow](),
	      [Interval.today]: () => babelHelpers.classPrivateFieldLooseBase(this, _isToday)[_isToday](),
	      [Interval.yesterday]: () => babelHelpers.classPrivateFieldLooseBase(this, _isYesterday)[_isYesterday](),
	      [Interval.week]: () => babelHelpers.classPrivateFieldLooseBase(this, _isCurrentWeek)[_isCurrentWeek](),
	      [Interval.year]: () => babelHelpers.classPrivateFieldLooseBase(this, _isCurrentYear)[_isCurrentYear](),
	      [Interval.olderThanYear]: () => !babelHelpers.classPrivateFieldLooseBase(this, _isCurrentYear)[_isCurrentYear]()
	    };
	  }
	}
	function _isYesterday2() {
	  const yesterday = babelHelpers.classPrivateFieldLooseBase(this, _shiftDate)[_shiftDate](-1);
	  return babelHelpers.classPrivateFieldLooseBase(this, _isSame)[_isSame](yesterday);
	}
	function _isToday2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _isSame)[_isSame](new Date());
	}
	function _isTomorrow2() {
	  const tomorrow = babelHelpers.classPrivateFieldLooseBase(this, _shiftDate)[_shiftDate](1);
	  return babelHelpers.classPrivateFieldLooseBase(this, _isSame)[_isSame](tomorrow);
	}
	function _isCurrentWeek2() {
	  const date = new Date();
	  const currentWeekNumber = +main_date.DateTimeFormat.format('W', date);
	  const setWeekNumber = +main_date.DateTimeFormat.format('W', babelHelpers.classPrivateFieldLooseBase(this, _date)[_date]);
	  const sameYear = babelHelpers.classPrivateFieldLooseBase(this, _isCurrentYear)[_isCurrentYear]();
	  return currentWeekNumber === setWeekNumber && sameYear;
	}
	function _isCurrentYear2() {
	  const date = new Date();
	  const currentYear = date.getFullYear();
	  const setYear = babelHelpers.classPrivateFieldLooseBase(this, _date)[_date].getFullYear();
	  return currentYear === setYear;
	}
	function _isSame2(date) {
	  const dateLocale = date.toLocaleDateString();
	  const setDateLocale = babelHelpers.classPrivateFieldLooseBase(this, _date)[_date].toLocaleDateString();
	  return dateLocale === setDateLocale;
	}
	function _shiftDate2(shift) {
	  const date = new Date();
	  date.setDate(date.getDate() + shift);
	  return date;
	}

	exports.DateFormatter = DateFormatter;
	exports.DateTemplate = DateTemplate;
	exports.DateCode = DateCode;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Main));
//# sourceMappingURL=date-formatter.bundle.js.map
