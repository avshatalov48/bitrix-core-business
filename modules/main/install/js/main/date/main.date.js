/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function convertBitrixFormat(format) {
	  if (!main_core.Type.isStringFilled(format)) {
	    return '';
	  }
	  return format.replace('YYYY', 'Y') // 1999
	  .replace('MMMM', 'F') // January - December
	  .replace('MM', 'm') // 01 - 12
	  .replace('M', 'M') // Jan - Dec
	  .replace('DD', 'd') // 01 - 31
	  .replace('G', 'g') //  1 - 12
	  .replace(/GG/i, 'G') //  0 - 23
	  .replace('H', 'h') // 01 - 12
	  .replace(/HH/i, 'H') // 00 - 24
	  .replace('MI', 'i') // 00 - 59
	  .replace('SS', 's') // 00 - 59
	  .replace('TT', 'A') // AM - PM
	  .replace('T', 'a'); // am - pm
	}

	const formatsCache = new main_core.Cache.MemoryCache();

	/**
	 * Returns culture-specific datetime format by code.
	 * The full list with examples can be found in config.php of this extension in ['settings']['formats'].
	 * All formats are compatible with this.format() without any additional transformations.
	 *
	 * @param code
	 * @returns {string|null}
	 */
	function getFormat(code) {
	  return formatsCache.remember(`main.date.format.${code}`, () => {
	    let format = main_core.Extension.getSettings('main.date').get(`formats.${code}`);
	    if (main_core.Type.isStringFilled(format) && (code === 'FORMAT_DATE' || code === 'FORMAT_DATETIME')) {
	      format = convertBitrixFormat(format);
	    }
	    return format;
	  });
	}

	/**
	 * @memberOf BX.Main
	 * @alias Date
	 */
	let DateTimeFormat = /*#__PURE__*/function () {
	  function DateTimeFormat() {
	    babelHelpers.classCallCheck(this, DateTimeFormat);
	  }
	  babelHelpers.createClass(DateTimeFormat, null, [{
	    key: "isAmPmMode",
	    value: function isAmPmMode(returnConst) {
	      if (returnConst === true) {
	        return this._getMessage('AMPM_MODE');
	      }
	      return this._getMessage('AMPM_MODE') !== false;
	    }
	  }, {
	    key: "convertToUTC",
	    value: function convertToUTC(date) {
	      if (!main_core.Type.isDate(date)) {
	        return null;
	      }
	      return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()));
	    }
	    /**
	     * Function creates and returns Javascript Date() object from server timestamp regardless of local browser (system) timezone.
	     * For example can be used to convert timestamp from some exact date on server to the JS Date object with the same value.
	     *
	     * @param timestamp - timestamp in seconds
	     * @returns {Date}
	     */
	  }, {
	    key: "getNewDate",
	    value: function getNewDate(timestamp) {
	      return new Date(this.getBrowserTimestamp(timestamp));
	    }
	    /**
	     * Function transforms server timestamp (in sec) to javascript timestamp (calculated depend on local browser timezone offset). Returns timestamp in milliseconds.
	     * Also see BX.Main.Date.getNewDate description.
	     *
	     * @param timestamp - timestamp in seconds
	     * @returns {number}
	     */
	  }, {
	    key: "getBrowserTimestamp",
	    value: function getBrowserTimestamp(timestamp) {
	      timestamp = parseInt(timestamp, 10);
	      const browserOffset = new Date(timestamp * 1000).getTimezoneOffset() * 60;
	      return (parseInt(timestamp, 10) + parseInt(this._getMessage('SERVER_TZ_OFFSET')) + browserOffset) * 1000;
	    }
	    /**
	     * Function transforms local browser timestamp (in ms) to server timestamp (calculated depend on local browser timezone offset). Returns timestamp in seconds.
	     *
	     * @param timestamp - timestamp in milliseconds
	     * @returns {number}
	     */
	  }, {
	    key: "getServerTimestamp",
	    value: function getServerTimestamp(timestamp) {
	      timestamp = parseInt(timestamp, 10);
	      const browserOffset = new Date(timestamp).getTimezoneOffset() * 60;
	      return Math.round(timestamp / 1000 - (parseInt(this._getMessage('SERVER_TZ_OFFSET'), 10) + parseInt(browserOffset, 10)));
	    }
	  }, {
	    key: "formatLastActivityDate",
	    value: function formatLastActivityDate(timestamp, now, utc) {
	      const ampm = this.isAmPmMode(true);
	      const timeFormat = ampm === this.AM_PM_MODE.LOWER ? 'g:i a' : ampm === this.AM_PM_MODE.UPPER ? 'g:i A' : 'H:i';
	      const format = [['tomorrow', '#01#' + timeFormat], ['now', '#02#'], ['todayFuture', '#03#' + timeFormat], ['yesterday', '#04#' + timeFormat], ['-', this.convertBitrixFormat(this._getMessage('FORMAT_DATETIME')).replace(/:s/g, '')], ['s60', 'sago'], ['i60', 'iago'], ['H5', 'Hago'], ['H24', '#03#' + timeFormat], ['d31', 'dago'], ['m12>1', 'mago'], ['m12>0', 'dago'], ['', '#05#']];
	      let formattedDate = this.format(format, timestamp, now, utc);
	      let match = null;
	      if ((match = /^#(\d+)#(.*)/.exec(formattedDate)) != null) {
	        switch (match[1]) {
	          case '01':
	            formattedDate = this._getMessage('FD_LAST_SEEN_TOMORROW').replace('#TIME#', match[2]);
	            break;
	          case '02':
	            formattedDate = this._getMessage('FD_LAST_SEEN_NOW');
	            break;
	          case '03':
	            formattedDate = this._getMessage('FD_LAST_SEEN_TODAY').replace('#TIME#', match[2]);
	            break;
	          case '04':
	            formattedDate = this._getMessage('FD_LAST_SEEN_YESTERDAY').replace('#TIME#', match[2]);
	            break;
	          case '05':
	            formattedDate = this._getMessage('FD_LAST_SEEN_MORE_YEAR');
	            break;
	          default:
	            formattedDate = match[2];
	            break;
	        }
	      }
	      return formattedDate;
	    }
	    /**
	     * The method is designed to replace the localization storage on sites without Bitrix Framework.
	     * It gets overloaded with custom implementation:
	     *
	     * const CustomDate = Object.create(BX.Main.Date);
	     * CustomDate._getMessage = () => ...new implementation...;
	     *
	     * This class should get messages only via this method.
	     * Otherwise, the class won't work on sites without Bitrix Framework.
	     *
	     * @param message
	     * @returns {*}
	     * @private
	     */
	  }, {
	    key: "_getMessage",
	    value: function _getMessage(message) {
	      return main_core.Loc.getMessage(message);
	    }
	    /**
	     * The method used to parse date from string by given format.
	     *
	     * @param {string} str - date in given format
	     * @param {boolean} isUTC - is date in UTC
	     * @param {string} formatDate - format of the date without time
	     * @param {string} formatDatetime - format of the date with time
	     * @returns {Date|null} - returns Date object if string was parsed or null
	     */
	  }, {
	    key: "parse",
	    value: function parse(str, isUTC, formatDate, formatDatetime) {
	      if (main_core.Type.isStringFilled(str)) {
	        if (!formatDate) {
	          formatDate = this._getMessage('FORMAT_DATE');
	        }
	        if (!formatDatetime) {
	          formatDatetime = this._getMessage('FORMAT_DATETIME');
	        }
	        let regMonths = '';
	        for (let i = 1; i <= 12; i++) {
	          regMonths = regMonths + '|' + this._getMessage('MON_' + i);
	        }
	        const expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig');
	        const aDate = str.match(expr);
	        let aFormat = formatDate.match(/(DD|MI|MMMM|MM|M|YYYY)/ig);
	        const aDateArgs = [];
	        const aFormatArgs = [];
	        const aResult = {};
	        if (!aDate) {
	          return null;
	        }
	        if (aDate.length > aFormat.length) {
	          aFormat = formatDatetime.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
	        }
	        for (let i = 0, cnt = aDate.length; i < cnt; i++) {
	          if (aDate[i].trim() !== '') {
	            aDateArgs[aDateArgs.length] = aDate[i];
	          }
	        }
	        for (let i = 0, cnt = aFormat.length; i < cnt; i++) {
	          if (aFormat[i].trim() !== '') {
	            aFormatArgs[aFormatArgs.length] = aFormat[i];
	          }
	        }
	        let m = aFormatArgs.findIndex(item => item === 'MMMM');
	        if (m > 0) {
	          aDateArgs[m] = this.getMonthIndex(aDateArgs[m]);
	          aFormatArgs[m] = 'MM';
	        } else {
	          m = aFormatArgs.findIndex(item => item === 'M');
	          if (m > 0) {
	            aDateArgs[m] = this.getMonthIndex(aDateArgs[m]);
	            aFormatArgs[m] = 'MM';
	          }
	        }
	        for (let i = 0, cnt = aFormatArgs.length; i < cnt; i++) {
	          const k = aFormatArgs[i].toUpperCase();
	          aResult[k] = k === 'T' || k === 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
	        }
	        if (aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0) {
	          const d = new Date();
	          if (isUTC) {
	            d.setUTCDate(1);
	            d.setUTCFullYear(aResult['YYYY']);
	            d.setUTCMonth(aResult['MM'] - 1);
	            d.setUTCDate(aResult['DD']);
	            d.setUTCHours(0, 0, 0, 0);
	          } else {
	            d.setDate(1);
	            d.setFullYear(aResult['YYYY']);
	            d.setMonth(aResult['MM'] - 1);
	            d.setDate(aResult['DD']);
	            d.setHours(0, 0, 0, 0);
	          }
	          if ((!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G'])) && !isNaN(aResult['MI'])) {
	            if (!isNaN(aResult['H']) || !isNaN(aResult['G'])) {
	              const bPM = (aResult['T'] || aResult['TT'] || 'am').toUpperCase() === 'PM',
	                h = parseInt(aResult['H'] || aResult['G'] || 0, 10);
	              if (bPM) {
	                aResult['HH'] = h + (h === 12 ? 0 : 12);
	              } else {
	                aResult['HH'] = h < 12 ? h : 0;
	              }
	            } else {
	              aResult['HH'] = parseInt(aResult['HH'] || aResult['GG'] || 0, 10);
	            }
	            if (isNaN(aResult['SS'])) {
	              aResult['SS'] = 0;
	            }
	            if (isUTC) {
	              d.setUTCHours(aResult['HH'], aResult['MI'], aResult['SS']);
	            } else {
	              d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
	            }
	          }
	          return d;
	        }
	      }
	      return null;
	    }
	  }, {
	    key: "getMonthIndex",
	    value: function getMonthIndex(month) {
	      const q = month.toUpperCase();
	      const wordMonthCut = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
	      const wordMonth = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
	      for (let i = 1; i <= 12; i++) {
	        if (q === this._getMessage('MON_' + i).toUpperCase() || q === this._getMessage('MONTH_' + i).toUpperCase() || q === this._getMessage('MONTH_' + i + '_S').toUpperCase() || q === wordMonthCut[i - 1].toUpperCase() || q === wordMonth[i - 1].toUpperCase()) {
	          return i;
	        }
	      }
	      return month;
	    }
	  }, {
	    key: "format",
	    value: function format(_format, timestamp, now, utc) {
	      /*
	      PHP to Javascript:
	      	time() = new Date()
	      	mktime(...) = new Date(...)
	      	gmmktime(...) = new Date(Date.UTC(...))
	      	mktime(0,0,0, 1, 1, 1970) != 0          new Date(1970,0,1).getTime() != 0
	      	gmmktime(0,0,0, 1, 1, 1970) == 0        new Date(Date.UTC(1970,0,1)).getTime() == 0
	      	date('d.m.Y H:i:s') = BX.Main.Date.format('d.m.Y H:i:s')
	      	gmdate('d.m.Y H:i:s') = BX.Main.Date.format('d.m.Y H:i:s', null, null, true);
	      */
	      const date = main_core.Type.isDate(timestamp) ? new Date(timestamp.getTime()) : main_core.Type.isNumber(timestamp) ? new Date(timestamp * 1000) : new Date();
	      const nowDate = main_core.Type.isDate(now) ? new Date(now.getTime()) : main_core.Type.isNumber(now) ? new Date(now * 1000) : new Date();
	      const isUTC = !!utc;
	      // used in hoisting inner functions, like _formatDateInterval
	      const thisDateTimeFormat = this;
	      if (main_core.Type.isArray(_format)) {
	        return _formatDateInterval(_format, date, nowDate, isUTC);
	      } else {
	        if (!main_core.Type.isStringFilled(_format)) {
	          return '';
	        }
	      }
	      const replaceMap = (_format.match(/{{([^{}]*)}}/g) || []).map(x => {
	        return (x.match(/[^{}]+/) || [''])[0];
	      });
	      if (replaceMap.length > 0) {
	        replaceMap.forEach((element, index) => {
	          _format = _format.replace('{{' + element + '}}', '{{' + index + '}}');
	        });
	      }
	      const formatRegex = /\\?(sago|iago|isago|Hago|dago|mago|Yago|sdiff|idiff|Hdiff|ddiff|mdiff|Ydiff|sshort|ishort|Hshort|dshort|mshort|Yshort|yesterday|today|tommorow|tomorrow|.)/gi;
	      const dateFormats = {
	        d: () => {
	          // Day of the month 01 to 31
	          return getDate(date).toString().padStart(2, '0');
	        },
	        D: () => {
	          //Mon through Sun
	          return this._getMessage('DOW_' + getDay(date));
	        },
	        j: () => {
	          //Day of the month 1 to 31
	          return getDate(date);
	        },
	        l: () => {
	          //Sunday through Saturday
	          return this._getMessage('DAY_OF_WEEK_' + getDay(date));
	        },
	        N: () => {
	          //1 (for Monday) through 7 (for Sunday)
	          return getDay(date) || 7;
	        },
	        S: () => {
	          //st, nd, rd or th. Works well with j
	          if (getDate(date) % 10 == 1 && getDate(date) != 11) {
	            return 'st';
	          } else if (getDate(date) % 10 == 2 && getDate(date) != 12) {
	            return 'nd';
	          } else if (getDate(date) % 10 == 3 && getDate(date) != 13) {
	            return 'rd';
	          } else {
	            return 'th';
	          }
	        },
	        w: () => {
	          //0 (for Sunday) through 6 (for Saturday)
	          return getDay(date);
	        },
	        z: () => {
	          //0 through 365
	          const firstDay = new Date(getFullYear(date), 0, 1);
	          const currentDay = new Date(getFullYear(date), getMonth(date), getDate(date));
	          return Math.ceil((currentDay - firstDay) / (24 * 3600 * 1000));
	        },
	        W: () => {
	          //ISO-8601 week number of year
	          const newDate = new Date(date.getTime());
	          const dayNumber = (getDay(date) + 6) % 7;
	          setDate(newDate, getDate(newDate) - dayNumber + 3);
	          const firstThursday = newDate.getTime();
	          setMonth(newDate, 0, 1);
	          if (getDay(newDate) != 4) {
	            setMonth(newDate, 0, 1 + (4 - getDay(newDate) + 7) % 7);
	          }
	          const weekNumber = 1 + Math.ceil((firstThursday - newDate) / (7 * 24 * 3600 * 1000));
	          return weekNumber.toString().padStart(2, '0');
	        },
	        F: () => {
	          //January through December
	          return this._getMessage('MONTH_' + (getMonth(date) + 1) + '_S');
	        },
	        f: () => {
	          //January through December
	          return this._getMessage('MONTH_' + (getMonth(date) + 1));
	        },
	        m: () => {
	          //Numeric representation of a month 01 through 12
	          return (getMonth(date) + 1).toString().padStart(2, '0');
	        },
	        M: () => {
	          //A short textual representation of a month, three letters Jan through Dec
	          return this._getMessage('MON_' + (getMonth(date) + 1));
	        },
	        n: () => {
	          //Numeric representation of a month 1 through 12
	          return getMonth(date) + 1;
	        },
	        t: () => {
	          //Number of days in the given month 28 through 31
	          const lastMonthDay = isUTC ? new Date(Date.UTC(getFullYear(date), getMonth(date) + 1, 0)) : new Date(getFullYear(date), getMonth(date) + 1, 0);
	          return getDate(lastMonthDay);
	        },
	        L: () => {
	          //1 if it is a leap year, 0 otherwise.
	          const year = getFullYear(date);
	          return year % 4 == 0 && year % 100 != 0 || year % 400 == 0 ? 1 : 0;
	        },
	        o: () => {
	          //ISO-8601 year number
	          const correctDate = new Date(date.getTime());
	          setDate(correctDate, getDate(correctDate) - (getDay(date) + 6) % 7 + 3);
	          return getFullYear(correctDate);
	        },
	        Y: () => {
	          //A full numeric representation of a year, 4 digits
	          return getFullYear(date);
	        },
	        y: () => {
	          //A two digit representation of a year
	          return getFullYear(date).toString().slice(2);
	        },
	        a: () => {
	          //am or pm
	          return getHours(date) > 11 ? 'pm' : 'am';
	        },
	        A: () => {
	          //AM or PM
	          return getHours(date) > 11 ? 'PM' : 'AM';
	        },
	        B: () => {
	          //000 through 999
	          const swatch = (date.getUTCHours() + 1) % 24 + date.getUTCMinutes() / 60 + date.getUTCSeconds() / 3600;
	          return Math.floor(swatch * 1000 / 24).toString().padStart(3, '0');
	        },
	        g: () => {
	          //12-hour format of an hour without leading zeros 1 through 12
	          return getHours(date) % 12 || 12;
	        },
	        G: () => {
	          //24-hour format of an hour without leading zeros 0 through 23
	          return getHours(date);
	        },
	        h: () => {
	          //12-hour format of an hour with leading zeros 01 through 12
	          return (getHours(date) % 12 || 12).toString().padStart(2, '0');
	        },
	        H: () => {
	          //24-hour format of an hour with leading zeros 00 through 23
	          return getHours(date).toString().padStart(2, '0');
	        },
	        i: () => {
	          //Minutes with leading zeros 00 to 59
	          return getMinutes(date).toString().padStart(2, '0');
	        },
	        s: () => {
	          //Seconds, with leading zeros 00 through 59
	          return getSeconds(date).toString().padStart(2, '0');
	        },
	        u: () => {
	          //Microseconds
	          return (getMilliseconds(date) * 1000).toString().padStart(6, '0');
	        },
	        e: () => {
	          if (isUTC) {
	            return 'UTC';
	          }
	          return '';
	        },
	        I: () => {
	          if (isUTC) {
	            return 0;
	          }

	          //Whether or not the date is in daylight saving time 1 if Daylight Saving Time, 0 otherwise
	          const firstJanuary = new Date(getFullYear(date), 0, 1);
	          const firstJanuaryUTC = Date.UTC(getFullYear(date), 0, 1);
	          const firstJuly = new Date(getFullYear(date), 6, 0);
	          const firstJulyUTC = Date.UTC(getFullYear(date), 6, 0);
	          return 0 + (firstJanuary - firstJanuaryUTC !== firstJuly - firstJulyUTC);
	        },
	        O: () => {
	          if (isUTC) {
	            return '+0000';
	          }

	          //Difference to Greenwich time (GMT) in hours +0200
	          const timezoneOffset = date.getTimezoneOffset();
	          const timezoneOffsetAbs = Math.abs(timezoneOffset);
	          return (timezoneOffset > 0 ? '-' : '+') + (Math.floor(timezoneOffsetAbs / 60) * 100 + timezoneOffsetAbs % 60).toString().padStart(4, '0');
	        },
	        //this method references 'O' method of the same object, arrow function is not suitable here
	        P: function () {
	          if (isUTC) {
	            return '+00:00';
	          }

	          //Difference to Greenwich time (GMT) with colon between hours and minutes +02:00
	          const difference = this.O();
	          return difference.substr(0, 3) + ':' + difference.substr(3);
	        },
	        Z: () => {
	          if (isUTC) {
	            return 0;
	          }
	          //Timezone offset in seconds. The offset for timezones west of UTC is always negative,
	          //and for those east of UTC is always positive.
	          return -date.getTimezoneOffset() * 60;
	        },
	        c: () => {
	          //ISO 8601 date
	          return 'Y-m-d\\TH:i:sP'.replace(formatRegex, _replaceDateFormat);
	        },
	        r: () => {
	          //RFC 2822 formatted date
	          return 'D, d M Y H:i:s O'.replace(formatRegex, _replaceDateFormat);
	        },
	        U: () => {
	          //Seconds since the Unix Epoch
	          return Math.floor(date.getTime() / 1000);
	        },
	        sago: () => {
	          return _formatDateMessage(intval((nowDate - date) / 1000), {
	            '0': 'FD_SECOND_AGO_0',
	            '1': 'FD_SECOND_AGO_1',
	            '10_20': 'FD_SECOND_AGO_10_20',
	            'MOD_1': 'FD_SECOND_AGO_MOD_1',
	            'MOD_2_4': 'FD_SECOND_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_SECOND_AGO_MOD_OTHER'
	          });
	        },
	        sdiff: () => {
	          return _formatDateMessage(intval((nowDate - date) / 1000), {
	            '0': 'FD_SECOND_DIFF_0',
	            '1': 'FD_SECOND_DIFF_1',
	            '10_20': 'FD_SECOND_DIFF_10_20',
	            'MOD_1': 'FD_SECOND_DIFF_MOD_1',
	            'MOD_2_4': 'FD_SECOND_DIFF_MOD_2_4',
	            'MOD_OTHER': 'FD_SECOND_DIFF_MOD_OTHER'
	          });
	        },
	        sshort: () => {
	          return this._getMessage('FD_SECOND_SHORT').replace(/#VALUE#/g, intval((nowDate - date) / 1000));
	        },
	        iago: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 1000), {
	            '0': 'FD_MINUTE_AGO_0',
	            '1': 'FD_MINUTE_AGO_1',
	            '10_20': 'FD_MINUTE_AGO_10_20',
	            'MOD_1': 'FD_MINUTE_AGO_MOD_1',
	            'MOD_2_4': 'FD_MINUTE_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_MINUTE_AGO_MOD_OTHER'
	          });
	        },
	        idiff: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 1000), {
	            '0': 'FD_MINUTE_DIFF_0',
	            '1': 'FD_MINUTE_DIFF_1',
	            '10_20': 'FD_MINUTE_DIFF_10_20',
	            'MOD_1': 'FD_MINUTE_DIFF_MOD_1',
	            'MOD_2_4': 'FD_MINUTE_DIFF_MOD_2_4',
	            'MOD_OTHER': 'FD_MINUTE_DIFF_MOD_OTHER'
	          });
	        },
	        isago: () => {
	          const minutesAgo = intval((nowDate - date) / 60 / 1000);
	          let result = _formatDateMessage(minutesAgo, {
	            '0': 'FD_MINUTE_0',
	            '1': 'FD_MINUTE_1',
	            '10_20': 'FD_MINUTE_10_20',
	            'MOD_1': 'FD_MINUTE_MOD_1',
	            'MOD_2_4': 'FD_MINUTE_MOD_2_4',
	            'MOD_OTHER': 'FD_MINUTE_MOD_OTHER'
	          });
	          result += ' ';
	          const secondsAgo = intval((nowDate - date) / 1000) - minutesAgo * 60;
	          result += _formatDateMessage(secondsAgo, {
	            '0': 'FD_SECOND_AGO_0',
	            '1': 'FD_SECOND_AGO_1',
	            '10_20': 'FD_SECOND_AGO_10_20',
	            'MOD_1': 'FD_SECOND_AGO_MOD_1',
	            'MOD_2_4': 'FD_SECOND_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_SECOND_AGO_MOD_OTHER'
	          });
	          return result;
	        },
	        ishort: () => {
	          return this._getMessage('FD_MINUTE_SHORT').replace(/#VALUE#/g, intval((nowDate - date) / 60 / 1000));
	        },
	        Hago: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 1000), {
	            '0': 'FD_HOUR_AGO_0',
	            '1': 'FD_HOUR_AGO_1',
	            '10_20': 'FD_HOUR_AGO_10_20',
	            'MOD_1': 'FD_HOUR_AGO_MOD_1',
	            'MOD_2_4': 'FD_HOUR_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_HOUR_AGO_MOD_OTHER'
	          });
	        },
	        Hdiff: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 1000), {
	            '0': 'FD_HOUR_DIFF_0',
	            '1': 'FD_HOUR_DIFF_1',
	            '10_20': 'FD_HOUR_DIFF_10_20',
	            'MOD_1': 'FD_HOUR_DIFF_MOD_1',
	            'MOD_2_4': 'FD_HOUR_DIFF_MOD_2_4',
	            'MOD_OTHER': 'FD_HOUR_DIFF_MOD_OTHER'
	          });
	        },
	        Hshort: () => {
	          return this._getMessage('FD_HOUR_SHORT').replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 1000));
	        },
	        yesterday: () => {
	          return this._getMessage('FD_YESTERDAY');
	        },
	        today: () => {
	          return this._getMessage('FD_TODAY');
	        },
	        tommorow: () => {
	          return this._getMessage('FD_TOMORROW');
	        },
	        tomorrow: () => {
	          return this._getMessage('FD_TOMORROW');
	        },
	        dago: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 1000), {
	            '0': 'FD_DAY_AGO_0',
	            '1': 'FD_DAY_AGO_1',
	            '10_20': 'FD_DAY_AGO_10_20',
	            'MOD_1': 'FD_DAY_AGO_MOD_1',
	            'MOD_2_4': 'FD_DAY_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_DAY_AGO_MOD_OTHER'
	          });
	        },
	        ddiff: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 1000), {
	            '0': 'FD_DAY_DIFF_0',
	            '1': 'FD_DAY_DIFF_1',
	            '10_20': 'FD_DAY_DIFF_10_20',
	            'MOD_1': 'FD_DAY_DIFF_MOD_1',
	            'MOD_2_4': 'FD_DAY_DIFF_MOD_2_4',
	            'MOD_OTHER': 'FD_DAY_DIFF_MOD_OTHER'
	          });
	        },
	        dshort: () => {
	          return this._getMessage('FD_DAY_SHORT').replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 24 / 1000));
	        },
	        mago: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000), {
	            '0': 'FD_MONTH_AGO_0',
	            '1': 'FD_MONTH_AGO_1',
	            '10_20': 'FD_MONTH_AGO_10_20',
	            'MOD_1': 'FD_MONTH_AGO_MOD_1',
	            'MOD_2_4': 'FD_MONTH_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_MONTH_AGO_MOD_OTHER'
	          });
	        },
	        mdiff: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000), {
	            '0': 'FD_MONTH_DIFF_0',
	            '1': 'FD_MONTH_DIFF_1',
	            '10_20': 'FD_MONTH_DIFF_10_20',
	            'MOD_1': 'FD_MONTH_DIFF_MOD_1',
	            'MOD_2_4': 'FD_MONTH_DIFF_MOD_2_4',
	            'MOD_OTHER': 'FD_MONTH_DIFF_MOD_OTHER'
	          });
	        },
	        mshort: () => {
	          return this._getMessage('FD_MONTH_SHORT').replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000));
	        },
	        Yago: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
	            '0': 'FD_YEARS_AGO_0',
	            '1': 'FD_YEARS_AGO_1',
	            '10_20': 'FD_YEARS_AGO_10_20',
	            'MOD_1': 'FD_YEARS_AGO_MOD_1',
	            'MOD_2_4': 'FD_YEARS_AGO_MOD_2_4',
	            'MOD_OTHER': 'FD_YEARS_AGO_MOD_OTHER'
	          });
	        },
	        Ydiff: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
	            '0': 'FD_YEARS_DIFF_0',
	            '1': 'FD_YEARS_DIFF_1',
	            '10_20': 'FD_YEARS_DIFF_10_20',
	            'MOD_1': 'FD_YEARS_DIFF_MOD_1',
	            'MOD_2_4': 'FD_YEARS_DIFF_MOD_2_4',
	            'MOD_OTHER': 'FD_YEARS_DIFF_MOD_OTHER'
	          });
	        },
	        Yshort: () => {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
	            '0': 'FD_YEARS_SHORT_0',
	            '1': 'FD_YEARS_SHORT_1',
	            '10_20': 'FD_YEARS_SHORT_10_20',
	            'MOD_1': 'FD_YEARS_SHORT_MOD_1',
	            'MOD_2_4': 'FD_YEARS_SHORT_MOD_2_4',
	            'MOD_OTHER': 'FD_YEARS_SHORT_MOD_OTHER'
	          });
	        },
	        x: () => {
	          const ampm = this.isAmPmMode(true);
	          const timeFormat = ampm === this.AM_PM_MODE.LOWER ? 'g:i a' : ampm === this.AM_PM_MODE.UPPER ? 'g:i A' : 'H:i';
	          return this.format([['tomorrow', 'tomorrow, ' + timeFormat], ['-', this.convertBitrixFormat(this._getMessage('FORMAT_DATETIME')).replace(/:s/g, '')], ['s', 'sago'], ['i', 'iago'], ['today', 'today, ' + timeFormat], ['yesterday', 'yesterday, ' + timeFormat], ['', this.convertBitrixFormat(this._getMessage('FORMAT_DATETIME')).replace(/:s/g, '')]], date, nowDate, isUTC);
	        },
	        X: () => {
	          const ampm = this.isAmPmMode(true);
	          const timeFormat = ampm === this.AM_PM_MODE.LOWER ? 'g:i a' : ampm === this.AM_PM_MODE.UPPER ? 'g:i A' : 'H:i';
	          const day = this.format([['tomorrow', 'tomorrow'], ['-', this.convertBitrixFormat(this._getMessage('FORMAT_DATE'))], ['today', 'today'], ['yesterday', 'yesterday'], ['', this.convertBitrixFormat(this._getMessage('FORMAT_DATE'))]], date, nowDate, isUTC);
	          const time = this.format([['tomorrow', timeFormat], ['today', timeFormat], ['yesterday', timeFormat], ['', '']], date, nowDate, isUTC);
	          if (time.length > 0) {
	            return this._getMessage('FD_DAY_AT_TIME').replace(/#DAY#/g, day).replace(/#TIME#/g, time);
	          } else {
	            return day;
	          }
	        },
	        Q: () => {
	          const daysAgo = intval((nowDate - date) / 60 / 60 / 24 / 1000);
	          if (daysAgo == 0) {
	            return this._getMessage('FD_DAY_DIFF_1').replace(/#VALUE#/g, 1);
	          } else {
	            return this.format([['d', 'ddiff'], ['m', 'mdiff'], ['', 'Ydiff']], date, nowDate);
	          }
	        }
	      };
	      let cutZeroTime = false;
	      if (_format[0] && _format[0] == '^') {
	        cutZeroTime = true;
	        _format = _format.substr(1);
	      }
	      let result = _format.replace(formatRegex, _replaceDateFormat);
	      if (cutZeroTime) {
	        /* 	15.04.12 13:00:00 => 15.04.12 13:00
	        	00:01:00 => 00:01
	        	4 may 00:00:00 => 4 may
	        	01-01-12 00:00 => 01-01-12
	        */

	        result = result.replace(/\s*00:00:00\s*/g, '').replace(/(\d\d:\d\d)(:00)/g, '$1').replace(/(\s*00:00\s*)(?!:)/g, '');
	      }
	      if (replaceMap.length > 0) {
	        replaceMap.forEach(function (element, index) {
	          result = result.replace('{{' + index + '}}', element);
	        });
	      }
	      return result;
	      function _formatDateInterval(formats, date, nowDate, isUTC) {
	        const secondsAgo = intval((nowDate - date) / 1000);
	        for (let i = 0; i < formats.length; i++) {
	          const formatInterval = formats[i][0];
	          const formatValue = formats[i][1];
	          let match = null;
	          if (formatInterval == 's') {
	            if (secondsAgo < 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if ((match = /^s(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] && secondsAgo > match[2]) {
	                return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1]) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'i') {
	            if (secondsAgo < 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if ((match = /^i(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 60 && secondsAgo > match[2] * 60) {
	                return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'H') {
	            if (secondsAgo < 24 * 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if ((match = /^H(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 60 * 60 && secondsAgo > match[2] * 60 * 60) {
	                return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'd') {
	            if (secondsAgo < 31 * 24 * 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if ((match = /^d(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 24 * 60 * 60 && secondsAgo > match[2] * 24 * 60 * 60) {
	                return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 24 * 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'm') {
	            if (secondsAgo < 365 * 24 * 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if ((match = /^m(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 31 * 24 * 60 * 60 && secondsAgo > match[2] * 31 * 24 * 60 * 60) {
	                return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 31 * 24 * 60 * 60) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'now') {
	            if (date.getTime() == nowDate.getTime()) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'today') {
	            const year = getFullYear(nowDate);
	            const month = getMonth(nowDate);
	            const day = getDate(nowDate);
	            const todayStart = isUTC ? new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) : new Date(year, month, day, 0, 0, 0, 0);
	            const todayEnd = isUTC ? new Date(Date.UTC(year, month, day + 1, 0, 0, 0, 0)) : new Date(year, month, day + 1, 0, 0, 0, 0);
	            if (date >= todayStart && date < todayEnd) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'todayFuture') {
	            const year = getFullYear(nowDate);
	            const month = getMonth(nowDate);
	            const day = getDate(nowDate);
	            const todayStart = nowDate.getTime();
	            const todayEnd = isUTC ? new Date(Date.UTC(year, month, day + 1, 0, 0, 0, 0)) : new Date(year, month, day + 1, 0, 0, 0, 0);
	            if (date >= todayStart && date < todayEnd) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'yesterday') {
	            const year = getFullYear(nowDate);
	            const month = getMonth(nowDate);
	            const day = getDate(nowDate);
	            const yesterdayStart = isUTC ? new Date(Date.UTC(year, month, day - 1, 0, 0, 0, 0)) : new Date(year, month, day - 1, 0, 0, 0, 0);
	            const yesterdayEnd = isUTC ? new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) : new Date(year, month, day, 0, 0, 0, 0);
	            if (date >= yesterdayStart && date < yesterdayEnd) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == 'tommorow' || formatInterval == 'tomorrow') {
	            const year = getFullYear(nowDate);
	            const month = getMonth(nowDate);
	            const day = getDate(nowDate);
	            const tomorrowStart = isUTC ? new Date(Date.UTC(year, month, day + 1, 0, 0, 0, 0)) : new Date(year, month, day + 1, 0, 0, 0, 0);
	            const tomorrowEnd = isUTC ? new Date(Date.UTC(year, month, day + 2, 0, 0, 0, 0)) : new Date(year, month, day + 2, 0, 0, 0, 0);
	            if (date >= tomorrowStart && date < tomorrowEnd) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == '-') {
	            if (secondsAgo < 0) {
	              return thisDateTimeFormat.format(formatValue, date, nowDate, isUTC);
	            }
	          }
	        }

	        //return formats.length > 0 ? thisDateTimeFormat.format(formats.pop()[1], date, nowDate, isUTC) : '';
	        return formats.length > 0 ? thisDateTimeFormat.format(formats[formats.length - 1][1], date, nowDate, isUTC) : '';
	      }
	      function getFullYear(date) {
	        return isUTC ? date.getUTCFullYear() : date.getFullYear();
	      }
	      function getDate(date) {
	        return isUTC ? date.getUTCDate() : date.getDate();
	      }
	      function getMonth(date) {
	        return isUTC ? date.getUTCMonth() : date.getMonth();
	      }
	      function getHours(date) {
	        return isUTC ? date.getUTCHours() : date.getHours();
	      }
	      function getMinutes(date) {
	        return isUTC ? date.getUTCMinutes() : date.getMinutes();
	      }
	      function getSeconds(date) {
	        return isUTC ? date.getUTCSeconds() : date.getSeconds();
	      }
	      function getMilliseconds(date) {
	        return isUTC ? date.getUTCMilliseconds() : date.getMilliseconds();
	      }
	      function getDay(date) {
	        return isUTC ? date.getUTCDay() : date.getDay();
	      }
	      function setDate(date, dayValue) {
	        return isUTC ? date.setUTCDate(dayValue) : date.setDate(dayValue);
	      }
	      function setMonth(date, monthValue, dayValue) {
	        return isUTC ? date.setUTCMonth(monthValue, dayValue) : date.setMonth(monthValue, dayValue);
	      }
	      function _formatDateMessage(value, messages) {
	        const val = value < 100 ? Math.abs(value) : Math.abs(value % 100);
	        const dec = val % 10;
	        let message = '';
	        if (val == 0) {
	          message = thisDateTimeFormat._getMessage(messages['0']);
	        } else if (val == 1) {
	          message = thisDateTimeFormat._getMessage(messages['1']);
	        } else if (val >= 10 && val <= 20) {
	          message = thisDateTimeFormat._getMessage(messages['10_20']);
	        } else if (dec == 1) {
	          message = thisDateTimeFormat._getMessage(messages['MOD_1']);
	        } else if (2 <= dec && dec <= 4) {
	          message = thisDateTimeFormat._getMessage(messages['MOD_2_4']);
	        } else {
	          message = thisDateTimeFormat._getMessage(messages['MOD_OTHER']);
	        }
	        return message.replace(/#VALUE#/g, value);
	      }
	      function _replaceDateFormat(match, matchFull) {
	        if (dateFormats[match]) {
	          return dateFormats[match]();
	        } else {
	          return matchFull;
	        }
	      }
	      function intval(number) {
	        return number >= 0 ? Math.floor(number) : Math.ceil(number);
	      }
	    }
	  }]);
	  return DateTimeFormat;
	}();
	babelHelpers.defineProperty(DateTimeFormat, "AM_PM_MODE", {
	  UPPER: 1,
	  LOWER: 2,
	  NONE: false
	});
	babelHelpers.defineProperty(DateTimeFormat, "convertBitrixFormat", convertBitrixFormat);
	babelHelpers.defineProperty(DateTimeFormat, "getFormat", getFormat);

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	/**
	 * Available units: `Y` - years, `m` - months, `d` - days, `H` - hours, `i` - minutes, `s` - seconds.
	 */

	const defaultOptions = {
	  format: 'Y m d H i s',
	  style: 'long'
	};
	var _getSeparator = /*#__PURE__*/new WeakSet();
	var _getMaxUnit = /*#__PURE__*/new WeakSet();
	var _formatUnit = /*#__PURE__*/new WeakSet();
	var _getUnitPropertyModByFormat = /*#__PURE__*/new WeakSet();
	var _getUnitPropertyByFormat = /*#__PURE__*/new WeakSet();
	var _getUnitDuration = /*#__PURE__*/new WeakSet();
	let DurationFormat = /*#__PURE__*/function () {
	  function DurationFormat(milliseconds) {
	    babelHelpers.classCallCheck(this, DurationFormat);
	    _classPrivateMethodInitSpec(this, _getUnitDuration);
	    _classPrivateMethodInitSpec(this, _getUnitPropertyByFormat);
	    _classPrivateMethodInitSpec(this, _getUnitPropertyModByFormat);
	    _classPrivateMethodInitSpec(this, _formatUnit);
	    _classPrivateMethodInitSpec(this, _getMaxUnit);
	    _classPrivateMethodInitSpec(this, _getSeparator);
	    this.milliseconds = Math.abs(milliseconds);
	  }
	  babelHelpers.createClass(DurationFormat, [{
	    key: "format",
	    /**
	     * @example new DurationFormat(5070000).format() // 1 hour, 24 minutes, 30 seconds
	     * @example new DurationFormat(5070000).format({ style: 'short' }) // 1 h 24 m 30 s
	     * @example new DurationFormat(5070000).format({ format: 'd H i' }) // 1 hour, 24 minutes
	     * @example new DurationFormat(5070000).format({ format: 'i s' }) // 84 minutes, 30 seconds
	     */
	    value: function format(formatOptions = defaultOptions) {
	      const options = {
	        ...defaultOptions,
	        ...formatOptions
	      };
	      const orderedUnits = main_core.Loc.getMessage('FD_UNIT_ORDER').split(' ');
	      const separator = _classPrivateMethodGet(this, _getSeparator, _getSeparator2).call(this, options.style);
	      const formatUnits = new Set(options.format.split(' '));
	      const maxUnit = _classPrivateMethodGet(this, _getMaxUnit, _getMaxUnit2).call(this, options.format);
	      return orderedUnits.filter(unit => formatUnits.has(unit)).map(unit => _classPrivateMethodGet(this, _formatUnit, _formatUnit2).call(this, unit, unit !== maxUnit, options.style)).filter(unit => unit !== '').join(separator);
	    }
	    /**
	     * @example new DurationFormat(5070000).formatClosest() // 1 hour
	     * @example new DurationFormat(5070000).formatClosest({ format: 'i s' }) // 84 minutes
	     */
	  }, {
	    key: "formatClosest",
	    value: function formatClosest(formatOptions = defaultOptions) {
	      const options = {
	        ...defaultOptions,
	        ...formatOptions
	      };
	      const maxUnit = _classPrivateMethodGet(this, _getMaxUnit, _getMaxUnit2).call(this, options.format);
	      return _classPrivateMethodGet(this, _formatUnit, _formatUnit2).call(this, maxUnit, false, options.style);
	    }
	  }, {
	    key: "seconds",
	    get: function () {
	      return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().s);
	    }
	  }, {
	    key: "minutes",
	    get: function () {
	      return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().i);
	    }
	  }, {
	    key: "hours",
	    get: function () {
	      return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().H);
	    }
	  }, {
	    key: "days",
	    get: function () {
	      return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().d);
	    }
	    /**
	     * Considering month is 31 days
	     */
	  }, {
	    key: "months",
	    get: function () {
	      return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().m);
	    }
	    /**
	     * Considering year is 365 days
	     */
	  }, {
	    key: "years",
	    get: function () {
	      return Math.floor(this.milliseconds / DurationFormat.getUnitDurations().Y);
	    }
	  }], [{
	    key: "createFromSeconds",
	    value: function createFromSeconds(seconds) {
	      return new DurationFormat(seconds * DurationFormat.getUnitDurations().s);
	    }
	  }, {
	    key: "createFromMinutes",
	    value: function createFromMinutes(minutes) {
	      return new DurationFormat(minutes * DurationFormat.getUnitDurations().i);
	    }
	  }, {
	    key: "getUnitDurations",
	    value: function getUnitDurations() {
	      return {
	        s: 1000,
	        i: 60000,
	        H: 3600000,
	        d: 86400000,
	        m: 2678400000,
	        Y: 31536000000
	      };
	    }
	  }]);
	  return DurationFormat;
	}();
	function _getSeparator2(style) {
	  if (style === 'short') {
	    return main_core.Loc.getMessage('FD_SEPARATOR_SHORT').replaceAll('&#32;', ' ');
	  }
	  return main_core.Loc.getMessage('FD_SEPARATOR').replaceAll('&#32;', ' ');
	}
	function _getMaxUnit2(format) {
	  const formatUnits = new Set(format.split(' '));
	  const units = Object.entries(DurationFormat.getUnitDurations()).filter(([unit]) => formatUnits.has(unit));
	  return units.reduce((closestDuration, unitDuration) => {
	    const whole = Math.floor(this.milliseconds / unitDuration[1]) >= 1;
	    const max = unitDuration[1] > closestDuration[1];
	    return whole && max ? unitDuration : closestDuration;
	  }, units[0])[0];
	}
	function _formatUnit2(unitStr, mod, style) {
	  const value = mod ? _classPrivateMethodGet(this, _getUnitPropertyModByFormat, _getUnitPropertyModByFormat2).call(this, unitStr) : _classPrivateMethodGet(this, _getUnitPropertyByFormat, _getUnitPropertyByFormat2).call(this, unitStr);
	  if (mod && value === 0) {
	    return '';
	  }
	  const now = Date.now() / 1000;
	  const unitDuration = value * _classPrivateMethodGet(this, _getUnitDuration, _getUnitDuration2).call(this, unitStr) / 1000;
	  const format = style === 'short' ? `${unitStr}short` : `${unitStr}diff`;
	  return DateTimeFormat.format(format, now - unitDuration, now);
	}
	function _getUnitPropertyModByFormat2(unitStr) {
	  const propsMod = {
	    s: this.seconds % 60,
	    i: this.minutes % 60,
	    H: this.hours % 24,
	    d: this.days % 31,
	    m: this.months % 12,
	    Y: this.years
	  };
	  return propsMod[unitStr];
	}
	function _getUnitPropertyByFormat2(unitStr) {
	  const props = {
	    s: this.seconds,
	    i: this.minutes,
	    H: this.hours,
	    d: this.days,
	    m: this.months,
	    Y: this.years
	  };
	  return props[unitStr];
	}
	function _getUnitDuration2(unitStr) {
	  return DurationFormat.getUnitDurations()[unitStr];
	}

	const cache = new main_core.Cache.MemoryCache();
	/**
	 * @memberOf BX.Main.Timezone
	 *
	 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
	 * It is not designed to handle this case and will definitely break.
	 */
	const Offset = {
	  get SERVER_TO_UTC() {
	    return cache.remember('SERVER_TO_UTC', () => {
	      return main_core.Text.toInteger(main_core.Loc.getMessage('SERVER_TZ_OFFSET'));
	    });
	  },
	  get USER_TO_SERVER() {
	    return cache.remember('USER_TO_SERVER', () => {
	      return main_core.Text.toInteger(main_core.Loc.getMessage('USER_TZ_OFFSET'));
	    });
	  },
	  // Date returns timezone offset in minutes by default, change it to seconds
	  // Also offset is negative in UTC+ timezones and positive in UTC- timezones.
	  // By convention Bitrix uses the opposite approach, so change offset sign.
	  get BROWSER_TO_UTC() {
	    return cache.remember('BROWSER_TO_UTC', () => {
	      const offset = main_core.Text.toInteger(new Date().getTimezoneOffset() * 60);
	      return -offset;
	    });
	  }
	};
	Object.freeze(Offset);

	function normalizeTimeValue(timeValue) {
	  if (main_core.Type.isDate(timeValue)) {
	    return getTimestampFromDate(timeValue);
	  }
	  return main_core.Text.toInteger(timeValue);
	}
	function createDateFromTimestamp(timestampInSeconds) {
	  return new Date(timestampInSeconds * 1000);
	}
	function getTimestampFromDate(date) {
	  return Math.floor(date.getTime() / 1000);
	}

	let offset = Offset;
	let now = null;
	function getOffset() {
	  return offset;
	}
	function getNowTimestamp() {
	  var _now;
	  return (_now = now) !== null && _now !== void 0 ? _now : getTimestampFromDate(new Date());
	}

	/**
	 * @memberOf BX.Main.Timezone
	 *
	 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
	 * It is not designed to handle this case and will definitely break.
	 */
	let BrowserTime = /*#__PURE__*/function () {
	  function BrowserTime() {
	    babelHelpers.classCallCheck(this, BrowserTime);
	  }
	  babelHelpers.createClass(BrowserTime, null, [{
	    key: "getDate",
	    /**
	     * Returns a Date object with time and date that represent a specific moment in Browser (device) timezone.
	     *
	     * @param utcTimestamp - normal utc timestamp in seconds. 'now' by default
	     * @returns {Date}
	     */
	    value: function getDate(utcTimestamp = null) {
	      const timestamp = main_core.Type.isNumber(utcTimestamp) ? utcTimestamp : this.getTimestamp();
	      return createDateFromTimestamp(timestamp);
	    }
	    /**
	     * Transforms a moment in Browser (device) timezone to a moment in User timezone.
	     *
	     * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	     * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	     * a Date object.
	     *
	     * @param browserTime - a moment in Browser (device) timezone. Either a Date object (recommended way). Or timestamp
	     * in seconds in Browser timezone (see this.getTimestamp for details).
	     * @returns {Date}
	     */
	  }, {
	    key: "toUserDate",
	    value: function toUserDate(browserTime) {
	      return createDateFromTimestamp(this.toUser(browserTime));
	    }
	    /**
	     * Transforms a moment in Browser (device) timezone to a moment in Server timezone.
	     *
	     * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	     * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	     * a Date object.
	     *
	     * @param browserTime - a moment in Browser (device) timezone. Either a Date object (recommended way). Or timestamp
	     * in seconds in Browser timezone (see this.getTimestamp for details).
	     * @returns {Date}
	     */
	  }, {
	    key: "toServerDate",
	    value: function toServerDate(browserTime) {
	      return createDateFromTimestamp(this.toServer(browserTime));
	    }
	    /**
	     * Transforms a moment in Browser (device) timezone to a timestamp in User timezone.
	     * It's recommended to use this.toUserDate for more clear code.
	     *
	     * @param browserTime - a moment in Browser timezone. Either a Date object (recommended way). Or timestamp in seconds
	     * in Browser timezone (see this.getTimestamp for details).
	     * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	     * the time in User timezone
	     */
	  }, {
	    key: "toUser",
	    value: function toUser(browserTime) {
	      return this.toServer(browserTime) + getOffset().USER_TO_SERVER;
	    }
	    /**
	     * Transforms a moment in Browser (device) timezone to a timestamp in Server timezone.
	     * It's recommended to use this.toServerDate for more clear code.
	     *
	     * @param browserTime - a moment in Browser timezone. Either a Date object (recommended way). Or timestamp in seconds
	     * in Browser timezone (see this.getTimestamp for details).
	     * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	     * the time in Server timezone
	     */
	  }, {
	    key: "toServer",
	    value: function toServer(browserTime) {
	      return normalizeTimeValue(browserTime) - getOffset().BROWSER_TO_UTC + getOffset().SERVER_TO_UTC;
	    }
	    /**
	     * Returns 'now' timestamp in Browser (device) timezone - when it's passed to a 'new Date', it will create an object
	     * with absolute time matching the time as if it was in Browser (device) timezone.
	     *
	     * @returns {number}
	     */
	  }, {
	    key: "getTimestamp",
	    value: function getTimestamp() {
	      // since 'Date' class in JS is hardcoded to use device timezone, 'browser timestamp' is just normal UTC timestamp :)

	      return getNowTimestamp();
	    }
	  }]);
	  return BrowserTime;
	}();

	/**
	 * @memberOf BX.Main.Timezone
	 *
	 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
	 * It is not designed to handle this case and will definitely break.
	 */
	let ServerTime = /*#__PURE__*/function () {
	  function ServerTime() {
	    babelHelpers.classCallCheck(this, ServerTime);
	  }
	  babelHelpers.createClass(ServerTime, null, [{
	    key: "getDate",
	    /**
	     * Returns a Date object with time and date that represent a specific moment in Server timezone.
	     *
	     * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	     * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	     * a Date object.
	     *
	     * @param utcTimestamp - normal utc timestamp in seconds. 'now' by default
	     * @returns {Date}
	     */
	    value: function getDate(utcTimestamp = null) {
	      if (main_core.Type.isNumber(utcTimestamp)) {
	        const browserToServerOffset = getOffset().SERVER_TO_UTC - getOffset().BROWSER_TO_UTC;
	        return createDateFromTimestamp(utcTimestamp + browserToServerOffset);
	      }
	      return BrowserTime.toServerDate(BrowserTime.getDate());
	    }
	    /**
	     * Transforms a moment in Server timezone to a moment in User timezone.
	     *
	     * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	     * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	     * a Date object.
	     *
	     * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * Server timezone (see this.getTimestamp for details).
	     * @returns {Date}
	     */
	  }, {
	    key: "toUserDate",
	    value: function toUserDate(serverTime) {
	      return createDateFromTimestamp(this.toUser(serverTime));
	    }
	    /**
	     * Transforms a moment in Server timezone to a moment in Browser (device) timezone.
	     *
	     * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * Server timezone (see this.getTimestamp for details).
	     * @returns {Date}
	     */
	  }, {
	    key: "toBrowserDate",
	    value: function toBrowserDate(serverTime) {
	      return createDateFromTimestamp(this.toBrowser(serverTime));
	    }
	    /**
	     * Transforms a moment in Server timezone to a timestamp in User timezone.
	     * It's recommended to use this.toServerDate for more clear code.
	     *
	     * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * Server timezone (see this.getTimestamp for details).
	     * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	     * the time in User timezone
	     */
	  }, {
	    key: "toUser",
	    value: function toUser(serverTime) {
	      return normalizeTimeValue(serverTime) + getOffset().USER_TO_SERVER;
	    }
	    /**
	     * Transforms a moment in Server timezone to a timestamp in Browser (device) timezone.
	     * It's recommended to use this.toBrowserDate for more clear code.
	     *
	     * @param serverTime - a moment in Server timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * Server timezone (see this.getTimestamp for details).
	     * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	     * the time in Browser (device) timezone
	     */
	  }, {
	    key: "toBrowser",
	    value: function toBrowser(serverTime) {
	      return normalizeTimeValue(serverTime) + getOffset().BROWSER_TO_UTC - getOffset().SERVER_TO_UTC;
	    }
	    /**
	     * Returns 'now' timestamp in Server timezone - when it's passed to a 'new Date', it will create an object with
	     * absolute time matching the time as if it was in Server timezone.
	     *
	     * @returns {number}
	     */
	  }, {
	    key: "getTimestamp",
	    value: function getTimestamp() {
	      return BrowserTime.toServer(BrowserTime.getTimestamp());
	    }
	  }]);
	  return ServerTime;
	}();

	function _classStaticPrivateFieldSpecGet(receiver, classConstructor, descriptor) { _classCheckPrivateStaticAccess(receiver, classConstructor); _classCheckPrivateStaticFieldDescriptor(descriptor, "get"); return _classApplyDescriptorGet(receiver, descriptor); }
	function _classCheckPrivateStaticFieldDescriptor(descriptor, action) { if (descriptor === undefined) { throw new TypeError("attempted to " + action + " private static field before its declaration"); } }
	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	function _classApplyDescriptorGet(receiver, descriptor) { if (descriptor.get) { return descriptor.get.call(receiver); } return descriptor.value; }

	/**
	 * @memberOf BX.Main.Timezone
	 *
	 * WARNING! Don't use this class or any classes from Timezone namespace on sites without Bitrix Framework.
	 * It is not designed to handle this case and will definitely break.
	 *
	 * ATTENTION! In Bitrix user timezone !== browser timezone. Users can change their timezone from their profile settings
	 * and the timezone will be different from browser timezone.
	 */
	let UserTime = /*#__PURE__*/function () {
	  function UserTime() {
	    babelHelpers.classCallCheck(this, UserTime);
	  }
	  babelHelpers.createClass(UserTime, null, [{
	    key: "getDate",
	    /**
	     * Returns a Date object with time and date that represent a specific moment in User timezone.
	     *
	     * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	     * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	     * a Date object.
	     *
	     * @param utcTimestamp - normal utc timestamp in seconds. 'now' by default
	     * @returns {Date}
	     */
	    value: function getDate(utcTimestamp = null) {
	      if (main_core.Type.isNumber(utcTimestamp)) {
	        return createDateFromTimestamp(utcTimestamp + _classStaticPrivateFieldSpecGet(this, UserTime, _userToBrowserOffset));
	      }
	      return createDateFromTimestamp(this.getTimestamp());
	    }
	  }, {
	    key: "toBrowserDate",
	    /**
	     * Transforms a moment in User timezone to a moment in Browser (device) timezone.
	     *
	     * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * User timezone (see this.getTimestamp for details).
	     * @returns {Date}
	     */
	    value: function toBrowserDate(userTime) {
	      return createDateFromTimestamp(this.toBrowser(userTime));
	    }
	    /**
	     * Transforms a moment in User timezone to a moment in Server timezone.
	     *
	     * ATTENTION! Date.getTime() and Date.getTimezoneOffset() will return inaccurate data. Since a native Date object
	     * doesn't support timezone other that device timezone, we have to manually change timestamp to shift time value in
	     * a Date object.
	     *
	     * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * User timezone (see this.getTimestamp for details).
	     * @returns {Date}
	     */
	  }, {
	    key: "toServerDate",
	    value: function toServerDate(userTime) {
	      return createDateFromTimestamp(this.toServer(userTime));
	    }
	  }, {
	    key: "toUTCTimestamp",
	    value: function toUTCTimestamp(userTime) {
	      return normalizeTimeValue(userTime) - _classStaticPrivateFieldSpecGet(this, UserTime, _userToBrowserOffset);
	    }
	    /**
	     * Transforms a moment in User timezone to a timestamp in Browser timezone.
	     * It's recommended to use this.toBrowserDate for more clear code.
	     *
	     * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * User timezone (see this.getTimestamp for details).
	     * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	     * the time in Browser (device) timezone
	     */
	  }, {
	    key: "toBrowser",
	    value: function toBrowser(userTime) {
	      return normalizeTimeValue(userTime) + getOffset().BROWSER_TO_UTC - getOffset().SERVER_TO_UTC - getOffset().USER_TO_SERVER;
	    }
	    /**
	     * Transforms a moment in User timezone to a timestamp in Server timezone.
	     * It's recommended to use this.toServerDate for more clear code.
	     *
	     * @param userTime - a moment in User timezone. Either a Date object (recommended way). Or timestamp in seconds in
	     * User timezone (see this.getTimestamp for details).
	     * @returns {number} - timestamp that when passed to 'new Date' will create an object with absolute time matching
	     * the time in Server timezone
	     */
	  }, {
	    key: "toServer",
	    value: function toServer(userTime) {
	      return normalizeTimeValue(userTime) - getOffset().USER_TO_SERVER;
	    }
	    /**
	     * Returns 'now' timestamp in User timezone - when it's passed to a 'new Date', it will create an object with absolute
	     * time matching the time as if it was in User timezone.
	     *
	     * @returns {number}
	     */
	  }, {
	    key: "getTimestamp",
	    value: function getTimestamp() {
	      return BrowserTime.toUser(BrowserTime.getTimestamp());
	    }
	  }]);
	  return UserTime;
	}();
	function _get_userToBrowserOffset() {
	  const userToUTCOffset = getOffset().SERVER_TO_UTC + getOffset().USER_TO_SERVER;
	  return userToUTCOffset - getOffset().BROWSER_TO_UTC;
	}
	var _userToBrowserOffset = {
	  get: _get_userToBrowserOffset,
	  set: void 0
	};

	const Timezone = Object.freeze({
	  BrowserTime,
	  Offset,
	  ServerTime,
	  UserTime
	});

	exports.Timezone = Timezone;
	exports.Date = DateTimeFormat;
	exports.DateTimeFormat = DateTimeFormat;
	exports.DurationFormat = DurationFormat;

}((this.BX.Main = this.BX.Main || {}),BX));
//# sourceMappingURL=main.date.js.map
