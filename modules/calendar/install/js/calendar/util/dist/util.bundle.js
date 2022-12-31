this.BX = this.BX || {};
(function (exports,main_core,ui_notification,main_popup,pull_client) {
	'use strict';

	(function (window) {
	  /****************** ATTENTION *******************************
	   * Please do not use Bitrix CoreJS in this class.
	   * This class can be called on page without Bitrix Framework
	  *************************************************************/
	  if (!window.BX) {
	    window.BX = {};
	  }

	  if (!window.BX.Main) {
	    window.BX.Main = {};
	  } else if (window.BX.Main.Date) {
	    return;
	  }

	  var BX = window.BX;
	  BX.Main.Date = BX.Main.DateTimeFormat = {
	    AM_PM_MODE: {
	      UPPER: 1,
	      LOWER: 2,
	      NONE: false
	    },
	    format: function (format, timestamp, now, utc) {
	      var _this = this;
	      /*
	      PHP to Javascript:
	      	time() = new Date()
	      	mktime(...) = new Date(...)
	      	gmmktime(...) = new Date(Date.UTC(...))
	      	mktime(0,0,0, 1, 1, 1970) != 0          new Date(1970,0,1).getTime() != 0
	      	gmmktime(0,0,0, 1, 1, 1970) == 0        new Date(Date.UTC(1970,0,1)).getTime() == 0
	      	date("d.m.Y H:i:s") = BX.Main.Date.format("d.m.Y H:i:s")
	      	gmdate("d.m.Y H:i:s") = BX.Main.Date.format("d.m.Y H:i:s", null, null, true);
	      */


	      var date = Utils.isDate(timestamp) ? new Date(timestamp.getTime()) : Utils.isNumber(timestamp) ? new Date(timestamp * 1000) : new Date();
	      var nowDate = Utils.isDate(now) ? new Date(now.getTime()) : Utils.isNumber(now) ? new Date(now * 1000) : new Date();
	      var isUTC = !!utc;
	      if (Utils.isArray(format)) return _formatDateInterval(format, date, nowDate, isUTC);else if (!Utils.isNotEmptyString(format)) return "";
	      var replaceMap = (format.match(/{{([^{}]*)}}/g) || []).map(function (x) {
	        return (x.match(/[^{}]+/) || [''])[0];
	      });

	      if (replaceMap.length > 0) {
	        replaceMap.forEach(function (element, index) {
	          format = format.replace("{{" + element + "}}", "{{" + index + "}}");
	        });
	      }

	      var formatRegex = /\\?(sago|iago|isago|Hago|dago|mago|Yago|sdiff|idiff|Hdiff|ddiff|mdiff|Ydiff|sshort|ishort|Hshort|dshort|mhort|Yshort|yesterday|today|tommorow|tomorrow|[a-z])/gi;
	      var dateFormats = {
	        d: function () {
	          // Day of the month 01 to 31
	          return Utils.strPadLeft(getDate(date).toString(), 2, "0");
	        },
	        D: function () {
	          //Mon through Sun
	          return _this._getMessage("DOW_" + getDay(date));
	        },
	        j: function () {
	          //Day of the month 1 to 31
	          return getDate(date);
	        },
	        l: function () {
	          //Sunday through Saturday
	          return _this._getMessage("DAY_OF_WEEK_" + getDay(date));
	        },
	        N: function () {
	          //1 (for Monday) through 7 (for Sunday)
	          return getDay(date) || 7;
	        },
	        S: function () {
	          //st, nd, rd or th. Works well with j
	          if (getDate(date) % 10 == 1 && getDate(date) != 11) return "st";else if (getDate(date) % 10 == 2 && getDate(date) != 12) return "nd";else if (getDate(date) % 10 == 3 && getDate(date) != 13) return "rd";else return "th";
	        },
	        w: function () {
	          //0 (for Sunday) through 6 (for Saturday)
	          return getDay(date);
	        },
	        z: function () {
	          //0 through 365
	          var firstDay = new Date(getFullYear(date), 0, 1);
	          var currentDay = new Date(getFullYear(date), getMonth(date), getDate(date));
	          return Math.ceil((currentDay - firstDay) / (24 * 3600 * 1000));
	        },
	        W: function () {
	          //ISO-8601 week number of year
	          var newDate = new Date(date.getTime());
	          var dayNumber = (getDay(date) + 6) % 7;
	          setDate(newDate, getDate(newDate) - dayNumber + 3);
	          var firstThursday = newDate.getTime();
	          setMonth(newDate, 0, 1);
	          if (getDay(newDate) != 4) setMonth(newDate, 0, 1 + (4 - getDay(newDate) + 7) % 7);
	          var weekNumber = 1 + Math.ceil((firstThursday - newDate) / (7 * 24 * 3600 * 1000));
	          return Utils.strPadLeft(weekNumber.toString(), 2, "0");
	        },
	        F: function () {
	          //January through December
	          return _this._getMessage("MONTH_" + (getMonth(date) + 1) + "_S");
	        },
	        f: function () {
	          //January through December
	          return _this._getMessage("MONTH_" + (getMonth(date) + 1));
	        },
	        m: function () {
	          //Numeric representation of a month 01 through 12
	          return Utils.strPadLeft((getMonth(date) + 1).toString(), 2, "0");
	        },
	        M: function () {
	          //A short textual representation of a month, three letters Jan through Dec
	          return _this._getMessage("MON_" + (getMonth(date) + 1));
	        },
	        n: function () {
	          //Numeric representation of a month 1 through 12
	          return getMonth(date) + 1;
	        },
	        t: function () {
	          //Number of days in the given month 28 through 31
	          var lastMonthDay = isUTC ? new Date(Date.UTC(getFullYear(date), getMonth(date) + 1, 0)) : new Date(getFullYear(date), getMonth(date) + 1, 0);
	          return getDate(lastMonthDay);
	        },
	        L: function () {
	          //1 if it is a leap year, 0 otherwise.
	          var year = getFullYear(date);
	          return year % 4 == 0 && year % 100 != 0 || year % 400 == 0 ? 1 : 0;
	        },
	        o: function () {
	          //ISO-8601 year number
	          var correctDate = new Date(date.getTime());
	          setDate(correctDate, getDate(correctDate) - (getDay(date) + 6) % 7 + 3);
	          return getFullYear(correctDate);
	        },
	        Y: function () {
	          //A full numeric representation of a year, 4 digits
	          return getFullYear(date);
	        },
	        y: function () {
	          //A two digit representation of a year
	          return getFullYear(date).toString().slice(2);
	        },
	        a: function () {
	          //am or pm
	          return getHours(date) > 11 ? "pm" : "am";
	        },
	        A: function () {
	          //AM or PM
	          return getHours(date) > 11 ? "PM" : "AM";
	        },
	        B: function () {
	          //000 through 999
	          var swatch = (date.getUTCHours() + 1) % 24 + date.getUTCMinutes() / 60 + date.getUTCSeconds() / 3600;
	          return Utils.strPadLeft(Math.floor(swatch * 1000 / 24).toString(), 3, "0");
	        },
	        g: function () {
	          //12-hour format of an hour without leading zeros 1 through 12
	          return getHours(date) % 12 || 12;
	        },
	        G: function () {
	          //24-hour format of an hour without leading zeros 0 through 23
	          return getHours(date);
	        },
	        h: function () {
	          //12-hour format of an hour with leading zeros 01 through 12
	          return Utils.strPadLeft((getHours(date) % 12 || 12).toString(), 2, "0");
	        },
	        H: function () {
	          //24-hour format of an hour with leading zeros 00 through 23
	          return Utils.strPadLeft(getHours(date).toString(), 2, "0");
	        },
	        i: function () {
	          //Minutes with leading zeros 00 to 59
	          return Utils.strPadLeft(getMinutes(date).toString(), 2, "0");
	        },
	        s: function () {
	          //Seconds, with leading zeros 00 through 59
	          return Utils.strPadLeft(getSeconds(date).toString(), 2, "0");
	        },
	        u: function () {
	          //Microseconds
	          return Utils.strPadLeft((getMilliseconds(date) * 1000).toString(), 6, "0");
	        },
	        e: function () {
	          if (isUTC) return "UTC";
	          return "";
	        },
	        I: function () {
	          if (isUTC) return 0; //Whether or not the date is in daylight saving time 1 if Daylight Saving Time, 0 otherwise

	          var firstJanuary = new Date(getFullYear(date), 0, 1);
	          var firstJanuaryUTC = Date.UTC(getFullYear(date), 0, 1);
	          var firstJuly = new Date(getFullYear(date), 6, 0);
	          var firstJulyUTC = Date.UTC(getFullYear(date), 6, 0);
	          return 0 + (firstJanuary - firstJanuaryUTC !== firstJuly - firstJulyUTC);
	        },
	        O: function () {
	          if (isUTC) return "+0000"; //Difference to Greenwich time (GMT) in hours +0200

	          var timezoneOffset = date.getTimezoneOffset();
	          var timezoneOffsetAbs = Math.abs(timezoneOffset);
	          return (timezoneOffset > 0 ? "-" : "+") + Utils.strPadLeft((Math.floor(timezoneOffsetAbs / 60) * 100 + timezoneOffsetAbs % 60).toString(), 4, "0");
	        },
	        P: function () {
	          if (isUTC) return "+00:00"; //Difference to Greenwich time (GMT) with colon between hours and minutes +02:00

	          var difference = this.O();
	          return difference.substr(0, 3) + ":" + difference.substr(3);
	        },
	        Z: function () {
	          if (isUTC) return 0; //Timezone offset in seconds. The offset for timezones west of UTC is always negative,
	          //and for those east of UTC is always positive.

	          return -date.getTimezoneOffset() * 60;
	        },
	        c: function () {
	          //ISO 8601 date
	          return "Y-m-d\\TH:i:sP".replace(formatRegex, _replaceDateFormat);
	        },
	        r: function () {
	          //RFC 2822 formatted date
	          return "D, d M Y H:i:s O".replace(formatRegex, _replaceDateFormat);
	        },
	        U: function () {
	          //Seconds since the Unix Epoch
	          return Math.floor(date.getTime() / 1000);
	        },
	        sago: function () {
	          return _formatDateMessage(intval((nowDate - date) / 1000), {
	            "0": "FD_SECOND_AGO_0",
	            "1": "FD_SECOND_AGO_1",
	            "10_20": "FD_SECOND_AGO_10_20",
	            "MOD_1": "FD_SECOND_AGO_MOD_1",
	            "MOD_2_4": "FD_SECOND_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_SECOND_AGO_MOD_OTHER"
	          });
	        },
	        sdiff: function () {
	          return _formatDateMessage(intval((nowDate - date) / 1000), {
	            "0": "FD_SECOND_DIFF_0",
	            "1": "FD_SECOND_DIFF_1",
	            "10_20": "FD_SECOND_DIFF_10_20",
	            "MOD_1": "FD_SECOND_DIFF_MOD_1",
	            "MOD_2_4": "FD_SECOND_DIFF_MOD_2_4",
	            "MOD_OTHER": "FD_SECOND_DIFF_MOD_OTHER"
	          });
	        },
	        sshort: function () {
	          return _this._getMessage("FD_SECOND_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 1000));
	        },
	        iago: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 1000), {
	            "0": "FD_MINUTE_AGO_0",
	            "1": "FD_MINUTE_AGO_1",
	            "10_20": "FD_MINUTE_AGO_10_20",
	            "MOD_1": "FD_MINUTE_AGO_MOD_1",
	            "MOD_2_4": "FD_MINUTE_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_MINUTE_AGO_MOD_OTHER"
	          });
	        },
	        idiff: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 1000), {
	            "0": "FD_MINUTE_DIFF_0",
	            "1": "FD_MINUTE_DIFF_1",
	            "10_20": "FD_MINUTE_DIFF_10_20",
	            "MOD_1": "FD_MINUTE_DIFF_MOD_1",
	            "MOD_2_4": "FD_MINUTE_DIFF_MOD_2_4",
	            "MOD_OTHER": "FD_MINUTE_DIFF_MOD_OTHER"
	          });
	        },
	        isago: function () {
	          var minutesAgo = intval((nowDate - date) / 60 / 1000);

	          var result = _formatDateMessage(minutesAgo, {
	            "0": "FD_MINUTE_0",
	            "1": "FD_MINUTE_1",
	            "10_20": "FD_MINUTE_10_20",
	            "MOD_1": "FD_MINUTE_MOD_1",
	            "MOD_2_4": "FD_MINUTE_MOD_2_4",
	            "MOD_OTHER": "FD_MINUTE_MOD_OTHER"
	          });

	          result += " ";
	          var secondsAgo = intval((nowDate - date) / 1000) - minutesAgo * 60;
	          result += _formatDateMessage(secondsAgo, {
	            "0": "FD_SECOND_AGO_0",
	            "1": "FD_SECOND_AGO_1",
	            "10_20": "FD_SECOND_AGO_10_20",
	            "MOD_1": "FD_SECOND_AGO_MOD_1",
	            "MOD_2_4": "FD_SECOND_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_SECOND_AGO_MOD_OTHER"
	          });
	          return result;
	        },
	        ishort: function () {
	          return _this._getMessage("FD_MINUTE_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 1000));
	        },
	        Hago: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 1000), {
	            "0": "FD_HOUR_AGO_0",
	            "1": "FD_HOUR_AGO_1",
	            "10_20": "FD_HOUR_AGO_10_20",
	            "MOD_1": "FD_HOUR_AGO_MOD_1",
	            "MOD_2_4": "FD_HOUR_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_HOUR_AGO_MOD_OTHER"
	          });
	        },
	        Hdiff: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 1000), {
	            "0": "FD_HOUR_DIFF_0",
	            "1": "FD_HOUR_DIFF_1",
	            "10_20": "FD_HOUR_DIFF_10_20",
	            "MOD_1": "FD_HOUR_DIFF_MOD_1",
	            "MOD_2_4": "FD_HOUR_DIFF_MOD_2_4",
	            "MOD_OTHER": "FD_HOUR_DIFF_MOD_OTHER"
	          });
	        },
	        Hshort: function () {
	          return _this._getMessage("FD_HOUR_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 1000));
	        },
	        yesterday: function () {
	          return _this._getMessage("FD_YESTERDAY");
	        },
	        today: function () {
	          return _this._getMessage("FD_TODAY");
	        },
	        tommorow: function () {
	          return _this._getMessage("FD_TOMORROW");
	        },
	        tomorrow: function () {
	          return _this._getMessage("FD_TOMORROW");
	        },
	        dago: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 1000), {
	            "0": "FD_DAY_AGO_0",
	            "1": "FD_DAY_AGO_1",
	            "10_20": "FD_DAY_AGO_10_20",
	            "MOD_1": "FD_DAY_AGO_MOD_1",
	            "MOD_2_4": "FD_DAY_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_DAY_AGO_MOD_OTHER"
	          });
	        },
	        ddiff: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 1000), {
	            "0": "FD_DAY_DIFF_0",
	            "1": "FD_DAY_DIFF_1",
	            "10_20": "FD_DAY_DIFF_10_20",
	            "MOD_1": "FD_DAY_DIFF_MOD_1",
	            "MOD_2_4": "FD_DAY_DIFF_MOD_2_4",
	            "MOD_OTHER": "FD_DAY_DIFF_MOD_OTHER"
	          });
	        },
	        dshort: function () {
	          return _this._getMessage("FD_DAY_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 24 / 1000));
	        },
	        mago: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000), {
	            "0": "FD_MONTH_AGO_0",
	            "1": "FD_MONTH_AGO_1",
	            "10_20": "FD_MONTH_AGO_10_20",
	            "MOD_1": "FD_MONTH_AGO_MOD_1",
	            "MOD_2_4": "FD_MONTH_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_MONTH_AGO_MOD_OTHER"
	          });
	        },
	        mdiff: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000), {
	            "0": "FD_MONTH_DIFF_0",
	            "1": "FD_MONTH_DIFF_1",
	            "10_20": "FD_MONTH_DIFF_10_20",
	            "MOD_1": "FD_MONTH_DIFF_MOD_1",
	            "MOD_2_4": "FD_MONTH_DIFF_MOD_2_4",
	            "MOD_OTHER": "FD_MONTH_DIFF_MOD_OTHER"
	          });
	        },
	        mshort: function () {
	          return _this._getMessage("FD_MONTH_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000));
	        },
	        Yago: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
	            "0": "FD_YEARS_AGO_0",
	            "1": "FD_YEARS_AGO_1",
	            "10_20": "FD_YEARS_AGO_10_20",
	            "MOD_1": "FD_YEARS_AGO_MOD_1",
	            "MOD_2_4": "FD_YEARS_AGO_MOD_2_4",
	            "MOD_OTHER": "FD_YEARS_AGO_MOD_OTHER"
	          });
	        },
	        Ydiff: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
	            "0": "FD_YEARS_DIFF_0",
	            "1": "FD_YEARS_DIFF_1",
	            "10_20": "FD_YEARS_DIFF_10_20",
	            "MOD_1": "FD_YEARS_DIFF_MOD_1",
	            "MOD_2_4": "FD_YEARS_DIFF_MOD_2_4",
	            "MOD_OTHER": "FD_YEARS_DIFF_MOD_OTHER"
	          });
	        },
	        Yshort: function () {
	          return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
	            "0": "FD_YEARS_SHORT_0",
	            "1": "FD_YEARS_SHORT_1",
	            "10_20": "FD_YEARS_SHORT_10_20",
	            "MOD_1": "FD_YEARS_SHORT_MOD_1",
	            "MOD_2_4": "FD_YEARS_SHORT_MOD_2_4",
	            "MOD_OTHER": "FD_YEARS_SHORT_MOD_OTHER"
	          });
	        },
	        x: function () {
	          var ampm = _this.isAmPmMode(true);

	          var timeFormat = ampm === _this.AM_PM_MODE.LOWER ? "g:i a" : ampm === _this.AM_PM_MODE.UPPER ? "g:i A" : "H:i";
	          return _this.format([["tomorrow", "tomorrow, " + timeFormat], ["-", _this.convertBitrixFormat(_this._getMessage("FORMAT_DATETIME")).replace(/:s/g, "")], ["s", "sago"], ["i", "iago"], ["today", "today, " + timeFormat], ["yesterday", "yesterday, " + timeFormat], ["", _this.convertBitrixFormat(_this._getMessage("FORMAT_DATETIME")).replace(/:s/g, "")]], date, nowDate, isUTC);
	        },
	        X: function () {
	          var ampm = _this.isAmPmMode(true);

	          var timeFormat = ampm === _this.AM_PM_MODE.LOWER ? "g:i a" : ampm === _this.AM_PM_MODE.UPPER ? "g:i A" : "H:i";

	          var day = _this.format([["tomorrow", "tomorrow"], ["-", _this.convertBitrixFormat(_this._getMessage("FORMAT_DATE"))], ["today", "today"], ["yesterday", "yesterday"], ["", _this.convertBitrixFormat(_this._getMessage("FORMAT_DATE"))]], date, nowDate, isUTC);

	          var time = _this.format([["tomorrow", timeFormat], ["today", timeFormat], ["yesterday", timeFormat], ["", ""]], date, nowDate, isUTC);

	          if (time.length > 0) return _this._getMessage("FD_DAY_AT_TIME").replace(/#DAY#/g, day).replace(/#TIME#/g, time);else return day;
	        },
	        Q: function () {
	          var daysAgo = intval((nowDate - date) / 60 / 60 / 24 / 1000);
	          if (daysAgo == 0) return _this._getMessage("FD_DAY_DIFF_1").replace(/#VALUE#/g, 1);else return _this.format([["d", "ddiff"], ["m", "mdiff"], ["", "Ydiff"]], date, nowDate);
	        }
	      };
	      var cutZeroTime = false;

	      if (format[0] && format[0] == "^") {
	        cutZeroTime = true;
	        format = format.substr(1);
	      }

	      var result = format.replace(formatRegex, _replaceDateFormat);

	      if (cutZeroTime) {
	        /* 	15.04.12 13:00:00 => 15.04.12 13:00
	        	00:01:00 => 00:01
	        	4 may 00:00:00 => 4 may
	        	01-01-12 00:00 => 01-01-12
	        */
	        result = result.replace(/\s*00:00:00\s*/g, "").replace(/(\d\d:\d\d)(:00)/g, "$1").replace(/(\s*00:00\s*)(?!:)/g, "");
	      }

	      if (replaceMap.length > 0) {
	        replaceMap.forEach(function (element, index) {
	          result = result.replace("{{" + index + "}}", element);
	        });
	      }

	      return result;

	      function _formatDateInterval(formats, date, nowDate, isUTC) {
	        var secondsAgo = intval((nowDate - date) / 1000);

	        for (var i = 0; i < formats.length; i++) {
	          var formatInterval = formats[i][0];
	          var formatValue = formats[i][1];
	          var match = null;

	          if (formatInterval == "s") {
	            if (secondsAgo < 60) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if ((match = /^s(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] && secondsAgo > match[2]) {
	                return _this.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1]) {
	              return _this.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == "i") {
	            if (secondsAgo < 60 * 60) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if ((match = /^i(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 60 && secondsAgo > match[2] * 60) {
	                return _this.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 60) {
	              return _this.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == "H") {
	            if (secondsAgo < 24 * 60 * 60) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if ((match = /^H(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 60 * 60 && secondsAgo > match[2] * 60 * 60) {
	                return _this.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 60 * 60) {
	              return _this.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == "d") {
	            if (secondsAgo < 31 * 24 * 60 * 60) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if ((match = /^d(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 24 * 60 * 60 && secondsAgo > match[2] * 24 * 60 * 60) {
	                return _this.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 24 * 60 * 60) {
	              return _this.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == "m") {
	            if (secondsAgo < 365 * 24 * 60 * 60) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if ((match = /^m(\d+)\>?(\d+)?/.exec(formatInterval)) != null) {
	            if (match[1] && match[2]) {
	              if (secondsAgo < match[1] * 31 * 24 * 60 * 60 && secondsAgo > match[2] * 31 * 24 * 60 * 60) {
	                return _this.format(formatValue, date, nowDate, isUTC);
	              }
	            } else if (secondsAgo < match[1] * 31 * 24 * 60 * 60) {
	              return _this.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == "now") {
	            if (date.getTime() == nowDate.getTime()) {
	              return _this.format(formatValue, date, nowDate, isUTC);
	            }
	          } else if (formatInterval == "today") {
	            var year = getFullYear(nowDate),
	                month = getMonth(nowDate),
	                day = getDate(nowDate);
	            var todayStart = isUTC ? new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) : new Date(year, month, day, 0, 0, 0, 0);
	            var todayEnd = isUTC ? new Date(Date.UTC(year, month, day + 1, 0, 0, 0, 0)) : new Date(year, month, day + 1, 0, 0, 0, 0);
	            if (date >= todayStart && date < todayEnd) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if (formatInterval == "todayFuture") {
	            var year = getFullYear(nowDate),
	                month = getMonth(nowDate),
	                day = getDate(nowDate);
	            var todayStart = nowDate.getTime();
	            var todayEnd = isUTC ? new Date(Date.UTC(year, month, day + 1, 0, 0, 0, 0)) : new Date(year, month, day + 1, 0, 0, 0, 0);
	            if (date >= todayStart && date < todayEnd) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if (formatInterval == "yesterday") {
	            year = getFullYear(nowDate);
	            month = getMonth(nowDate);
	            day = getDate(nowDate);
	            var yesterdayStart = isUTC ? new Date(Date.UTC(year, month, day - 1, 0, 0, 0, 0)) : new Date(year, month, day - 1, 0, 0, 0, 0);
	            var yesterdayEnd = isUTC ? new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) : new Date(year, month, day, 0, 0, 0, 0);
	            if (date >= yesterdayStart && date < yesterdayEnd) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if (formatInterval == "tommorow" || formatInterval == "tomorrow") {
	            year = getFullYear(nowDate);
	            month = getMonth(nowDate);
	            day = getDate(nowDate);
	            var tomorrowStart = isUTC ? new Date(Date.UTC(year, month, day + 1, 0, 0, 0, 0)) : new Date(year, month, day + 1, 0, 0, 0, 0);
	            var tomorrowEnd = isUTC ? new Date(Date.UTC(year, month, day + 2, 0, 0, 0, 0)) : new Date(year, month, day + 2, 0, 0, 0, 0);
	            if (date >= tomorrowStart && date < tomorrowEnd) return _this.format(formatValue, date, nowDate, isUTC);
	          } else if (formatInterval == "-") {
	            if (secondsAgo < 0) return _this.format(formatValue, date, nowDate, isUTC);
	          }
	        } //return formats.length > 0 ? _this.format(formats.pop()[1], date, nowDate, isUTC) : "";


	        return formats.length > 0 ? _this.format(formats[formats.length - 1][1], date, nowDate, isUTC) : "";
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
	        var val = value < 100 ? Math.abs(value) : Math.abs(value % 100);
	        var dec = val % 10;
	        var message = "";
	        if (val == 0) message = _this._getMessage(messages["0"]);else if (val == 1) message = _this._getMessage(messages["1"]);else if (val >= 10 && val <= 20) message = _this._getMessage(messages["10_20"]);else if (dec == 1) message = _this._getMessage(messages["MOD_1"]);else if (2 <= dec && dec <= 4) message = _this._getMessage(messages["MOD_2_4"]);else message = _this._getMessage(messages["MOD_OTHER"]);
	        return message.replace(/#VALUE#/g, value);
	      }

	      function _replaceDateFormat(match, matchFull) {
	        if (dateFormats[match]) return dateFormats[match]();else return matchFull;
	      }

	      function intval(number) {
	        return number >= 0 ? Math.floor(number) : Math.ceil(number);
	      }
	    },
	    convertBitrixFormat: function (format) {
	      if (!Utils.isNotEmptyString(format)) return "";
	      return format.replace("YYYY", "Y") // 1999
	      .replace("MMMM", "F") // January - December
	      .replace("MM", "m") // 01 - 12
	      .replace("M", "M") // Jan - Dec
	      .replace("DD", "d") // 01 - 31
	      .replace("G", "g") //  1 - 12
	      .replace(/GG/i, "G") //  0 - 23
	      .replace("H", "h") // 01 - 12
	      .replace(/HH/i, "H") // 00 - 24
	      .replace("MI", "i") // 00 - 59
	      .replace("SS", "s") // 00 - 59
	      .replace("TT", "A") // AM - PM
	      .replace("T", "a"); // am - pm
	    },
	    convertToUTC: function (date) {
	      if (!Utils.isDate(date)) return null;
	      return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()));
	    },

	    /**
	     * Function creates and returns Javascript Date() object from server timestamp regardless of local browser (system) timezone.
	     * For example can be used to convert timestamp from some exact date on server to the JS Date object with the same value.
	     *
	     * @param timestamp - timestamp in seconds
	     * @returns {Date}
	     */
	    getNewDate: function (timestamp) {
	      return new Date(this.getBrowserTimestamp(timestamp));
	    },

	    /**
	     * Function transforms server timestamp (in sec) to javascript timestamp (calculated depend on local browser timezone offset). Returns timestamp in milliseconds.
	     * Also see BX.Main.Date.getNewDate description.
	     *
	     * @param timestamp - timestamp in seconds
	     * @returns {number}
	     */
	    getBrowserTimestamp: function (timestamp) {
	      timestamp = parseInt(timestamp, 10);
	      var browserOffset = new Date(timestamp * 1000).getTimezoneOffset() * 60;
	      return (parseInt(timestamp, 10) + parseInt(this._getMessage('SERVER_TZ_OFFSET')) + browserOffset) * 1000;
	    },

	    /**
	     * Function transforms local browser timestamp (in ms) to server timestamp (calculated depend on local browser timezone offset). Returns timestamp in seconds.
	     *
	     * @param timestamp - timestamp in milliseconds
	     * @returns {number}
	     */
	    getServerTimestamp: function (timestamp) {
	      timestamp = parseInt(timestamp, 10);
	      var browserOffset = new Date(timestamp).getTimezoneOffset() * 60;
	      return Math.round(timestamp / 1000 - (parseInt(this._getMessage('SERVER_TZ_OFFSET'), 10) + parseInt(browserOffset, 10)));
	    },
	    formatLastActivityDate: function (timestamp, now, utc) {
	      var ampm = this.isAmPmMode(true);
	      var timeFormat = ampm === this.AM_PM_MODE.LOWER ? "g:i a" : ampm === this.AM_PM_MODE.UPPER ? "g:i A" : "H:i";
	      var format = [["tomorrow", "#01#" + timeFormat], ["now", "#02#"], ["todayFuture", "#03#" + timeFormat], ["yesterday", "#04#" + timeFormat], ["-", this.convertBitrixFormat(this._getMessage("FORMAT_DATETIME")).replace(/:s/g, "")], ["s60", "sago"], ["i60", "iago"], ["H5", "Hago"], ["H24", "#03#" + timeFormat], ["d31", "dago"], ["m12>1", "mago"], ["m12>0", "dago"], ["", "#05#"]];
	      var formattedDate = this.format(format, timestamp, now, utc);
	      var match = null;

	      if ((match = /^#(\d+)#(.*)/.exec(formattedDate)) != null) {
	        switch (match[1]) {
	          case "01":
	            formattedDate = this._getMessage('FD_LAST_SEEN_TOMORROW').replace("#TIME#", match[2]);
	            break;

	          case "02":
	            formattedDate = this._getMessage('FD_LAST_SEEN_NOW');
	            break;

	          case "03":
	            formattedDate = this._getMessage('FD_LAST_SEEN_TODAY').replace("#TIME#", match[2]);
	            break;

	          case "04":
	            formattedDate = this._getMessage('FD_LAST_SEEN_YESTERDAY').replace("#TIME#", match[2]);
	            break;

	          case "05":
	            formattedDate = this._getMessage('FD_LAST_SEEN_MORE_YEAR');
	            break;

	          default:
	            formattedDate = match[2];
	            break;
	        }
	      }

	      return formattedDate;
	    },
	    isAmPmMode: function (returnConst) {
	      if (returnConst === true) {
	        return this._getMessage('AMPM_MODE');
	      }

	      return this._getMessage('AMPM_MODE') !== false;
	    },

	    /**
	     * The method is designed to replace the localization storage on sites without Bitrix Framework.
	     *
	     * @param message
	     * @returns {*}
	     * @private
	     */
	    _getMessage: function (message) {
	      return BX.message(message);
	    },

	    /**
	     * The method used to parse date from string by given format.
	     *
	     * @param {string} str - date in given format
	     * @param {boolean} isUTC - is date in UTC
	     * @param {string} formatDate - format of the date without time
	     * @param {string} formatDatetime - format of the date with time
	     * @returns {Date|null} - returns Date object if string was parsed or null
	     */
	    parse: function (str, isUTC, formatDate, formatDatetime) {
	      if (Utils.isNotEmptyString(str)) {
	        if (!formatDate) formatDate = this._getMessage('FORMAT_DATE');
	        if (!formatDatetime) formatDatetime = this._getMessage('FORMAT_DATETIME');
	        var regMonths = '';

	        for (i = 1; i <= 12; i++) {
	          regMonths = regMonths + '|' + this._getMessage('MON_' + i);
	        }

	        var expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig'),
	            aDate = str.match(expr),
	            aFormat = formatDate.match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
	            i,
	            cnt,
	            aDateArgs = [],
	            aFormatArgs = [],
	            aResult = {};

	        if (!aDate) {
	          return null;
	        }

	        if (aDate.length > aFormat.length) {
	          aFormat = formatDatetime.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
	        }

	        for (i = 0, cnt = aDate.length; i < cnt; i++) {
	          if (aDate[i].trim() !== '') {
	            aDateArgs[aDateArgs.length] = aDate[i];
	          }
	        }

	        for (i = 0, cnt = aFormat.length; i < cnt; i++) {
	          if (aFormat[i].trim() !== '') {
	            aFormatArgs[aFormatArgs.length] = aFormat[i];
	          }
	        }

	        var m = Utils.array_search('MMMM', aFormatArgs);

	        if (m > 0) {
	          aDateArgs[m] = this.getMonthIndex(aDateArgs[m]);
	          aFormatArgs[m] = "MM";
	        } else {
	          m = Utils.array_search('M', aFormatArgs);

	          if (m > 0) {
	            aDateArgs[m] = this.getMonthIndex(aDateArgs[m]);
	            aFormatArgs[m] = "MM";
	          }
	        }

	        for (i = 0, cnt = aFormatArgs.length; i < cnt; i++) {
	          var k = aFormatArgs[i].toUpperCase();
	          aResult[k] = k === 'T' || k === 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
	        }

	        if (aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0) {
	          var d = new Date();

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
	              var bPM = (aResult['T'] || aResult['TT'] || 'am').toUpperCase() === 'PM',
	                  h = parseInt(aResult['H'] || aResult['G'] || 0, 10);

	              if (bPM) {
	                aResult['HH'] = h + (h === 12 ? 0 : 12);
	              } else {
	                aResult['HH'] = h < 12 ? h : 0;
	              }
	            } else {
	              aResult['HH'] = parseInt(aResult['HH'] || aResult['GG'] || 0, 10);
	            }

	            if (isNaN(aResult['SS'])) aResult['SS'] = 0;

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
	    },
	    getMonthIndex: function (month) {
	      var i,
	          q = month.toUpperCase(),
	          wordMonthCut = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'],
	          wordMonth = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

	      for (i = 1; i <= 12; i++) {
	        if (q === this._getMessage('MON_' + i).toUpperCase() || q === this._getMessage('MONTH_' + i).toUpperCase() || q === wordMonthCut[i - 1].toUpperCase() || q === wordMonth[i - 1].toUpperCase()) {
	          return i;
	        }
	      }

	      return month;
	    }
	  };
	  /**
	   * @private
	   */

	  var Utils = {
	    isDate: function (item) {
	      return item && Object.prototype.toString.call(item) == "[object Date]";
	    },
	    isNumber: function (item) {
	      return item === 0 ? true : item ? typeof item == "number" || item instanceof Number : false;
	    },
	    isArray: function (item) {
	      return item && Object.prototype.toString.call(item) == "[object Array]";
	    },
	    isString: function (item) {
	      return item === '' ? true : item ? typeof item == "string" || item instanceof String : false;
	    },
	    isNotEmptyString: function (item) {
	      return this.isString(item) ? item.length > 0 : false;
	    },
	    strPadLeft: function (input, padLength, padString) {
	      var i = input.length,
	          q = padString.length;
	      if (i >= padLength) return input;

	      for (; i < padLength; i += q) input = padString + input;

	      return input;
	    },

	    /**
	     * @deprecated
	     * @use myArr.findIndex(item => item === needle);
	     */
	    array_search: function (needle, haystack) {
	      for (var i = 0; i < haystack.length; i++) {
	        if (haystack[i] == needle) return i;
	      }

	      return -1;
	    }
	  };
	})(window);

	let _ = t => t,
	    _t;
	class Util {
	  static parseTime(str) {
	    let date = Util.parseDate1(BX.date.format(Util.getDateFormat(), new Date()) + ' ' + str, false);
	    return date ? {
	      h: date.getHours(),
	      m: date.getMinutes()
	    } : date;
	  }

	  static getTimeRounded(date) {
	    return Math.round(date.getTime() / 60000) * 60000;
	  }

	  static parseDate(str, bUTC, formatDate, formatDatetime) {
	    return BX.parseDate(str, bUTC, formatDate, formatDatetime);
	  }

	  static parseDate1(str, format, trimSeconds) {
	    let i,
	        cnt,
	        k,
	        regMonths;
	    if (!format) format = main_core.Loc.getMessage('FORMAT_DATETIME');
	    str = BX.util.trim(str);
	    if (trimSeconds !== false) format = format.replace(':SS', '');

	    if (BX.type.isNotEmptyString(str)) {
	      regMonths = '';

	      for (i = 1; i <= 12; i++) {
	        regMonths = regMonths + '|' + main_core.Loc.getMessage('MON_' + i);
	      }

	      let expr = new RegExp('([0-9]+|[a-z]+' + regMonths + ')', 'ig'),
	          aDate = str.match(expr),
	          aFormat = main_core.Loc.getMessage('FORMAT_DATE').match(/(DD|MI|MMMM|MM|M|YYYY)/ig),
	          aDateArgs = [],
	          aFormatArgs = [],
	          aResult = {};

	      if (!aDate) {
	        return null;
	      }

	      if (aDate.length > aFormat.length) {
	        aFormat = format.match(/(DD|MI|MMMM|MM|M|YYYY|HH|H|SS|TT|T|GG|G)/ig);
	      }

	      for (i = 0, cnt = aDate.length; i < cnt; i++) {
	        if (BX.util.trim(aDate[i]) !== '') {
	          aDateArgs[aDateArgs.length] = aDate[i];
	        }
	      }

	      for (i = 0, cnt = aFormat.length; i < cnt; i++) {
	        if (BX.util.trim(aFormat[i]) != '') {
	          aFormatArgs[aFormatArgs.length] = aFormat[i];
	        }
	      }

	      let m = BX.util.array_search('MMMM', aFormatArgs);

	      if (m > 0) {
	        aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
	        aFormatArgs[m] = "MM";
	      } else {
	        m = BX.util.array_search('M', aFormatArgs);

	        if (m > 0) {
	          aDateArgs[m] = BX.getNumMonth(aDateArgs[m]);
	          aFormatArgs[m] = "MM";
	        }
	      }

	      for (i = 0, cnt = aFormatArgs.length; i < cnt; i++) {
	        k = aFormatArgs[i].toUpperCase();
	        aResult[k] = k == 'T' || k == 'TT' ? aDateArgs[i] : parseInt(aDateArgs[i], 10);
	      }

	      if (aResult['DD'] > 0 && aResult['MM'] > 0 && aResult['YYYY'] > 0) {
	        let d = new Date();

	        {
	          d.setDate(1);
	          d.setFullYear(aResult['YYYY']);
	          d.setMonth(aResult['MM'] - 1);
	          d.setDate(aResult['DD']);
	          d.setHours(0, 0, 0);
	        }

	        if ((!isNaN(aResult['HH']) || !isNaN(aResult['GG']) || !isNaN(aResult['H']) || !isNaN(aResult['G'])) && !isNaN(aResult['MI'])) {
	          if (!isNaN(aResult['H']) || !isNaN(aResult['G'])) {
	            let bPM = (aResult['T'] || aResult['TT'] || 'am').toUpperCase() == 'PM';
	            let h = parseInt(aResult['H'] || aResult['G'] || 0, 10);

	            if (bPM) {
	              aResult['HH'] = h + (h == 12 ? 0 : 12);
	            } else {
	              aResult['HH'] = h < 12 ? h : 0;
	            }
	          } else {
	            aResult['HH'] = parseInt(aResult['HH'] || aResult['GG'] || 0, 10);
	          }

	          if (isNaN(aResult['SS'])) aResult['SS'] = 0;

	          {
	            d.setHours(aResult['HH'], aResult['MI'], aResult['SS']);
	          }
	        }

	        return d;
	      }
	    }

	    return null;
	  }

	  static formatTime(hours, minutes) {
	    let day;

	    if (main_core.Type.isDate(hours)) {
	      day = hours;
	    } else {
	      day = new Date();
	      day.setHours(hours, minutes, 0);
	    }

	    return BX.date.format(Util.getTimeFormatShort(), day.getTime() / 1000);
	  }

	  static formatDate(timestamp) {
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime();
	    }

	    return BX.date.format(Util.getDateFormat(), timestamp / 1000);
	  }

	  static formatDateTime(timestamp) {
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime();
	    }

	    return BX.date.format(Util.getDateTimeFormat(), timestamp / 1000);
	  }

	  static formatDateUsable(date, showYear = true, showDayOfWeek = false) {
	    const lang = main_core.Loc.getMessage('LANGUAGE_ID');
	    let format = Util.getDateFormat();

	    if (lang === 'ru' || lang === 'ua') {
	      format = showDayOfWeek ? 'l, j F' : 'j F';

	      if (date.getFullYear && date.getFullYear() !== new Date().getFullYear() && showYear !== false) {
	        format += ' Y';
	      }
	    }

	    return BX.date.format([["today", "today"], ["tommorow", "tommorow"], ["yesterday", "yesterday"], ["", format]], date);
	  }

	  static getDayLength() {
	    if (!Util.DAY_LENGTH) {
	      Util.DAY_LENGTH = 86400000;
	    }

	    return Util.DAY_LENGTH;
	  }

	  static getDefaultColorList() {
	    return ['#86b100', '#0092cc', '#00afc7', '#e89b06', '#00b38c', '#de2b24', '#bd7ac9', '#838fa0', '#c3612c', '#e97090'];
	  }

	  static findTargetNode(node, parentCont) {
	    let res = false;

	    if (node) {
	      let prefix = 'data-bx-calendar',
	          i;

	      if (node.attributes && node.attributes.length) {
	        for (i = 0; i < node.attributes.length; i++) {
	          if (node.attributes[i].name && node.attributes[i].name.substr(0, prefix.length) === prefix) {
	            res = node;
	            break;
	          }
	        }
	      }

	      if (!res) {
	        res = BX.findParent(node, function (n) {
	          let j;

	          if (n.attributes && n.attributes.length) {
	            for (j = 0; j < n.attributes.length; j++) {
	              if (n.attributes[j].name && n.attributes[j].name.substr(0, prefix.length) === prefix) return true;
	            }
	          }

	          return false;
	        }, parentCont);
	      }
	    }

	    return res;
	  }

	  static getFollowedUserList(userId) {
	    return [];
	  }

	  static getWeekDayByInd(index) {
	    return ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'][index];
	  }

	  static getLoader(size, className) {
	    return main_core.Tag.render(_t || (_t = _`
		<div class="${0}">
			<svg class="calendar-loader-circular"
				style="width:${0}px; height:${0}px;"
				viewBox="25 25 50 50">
					<circle class="calendar-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					<circle class="calendar-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
			</svg>
		</div>
`), className || 'calendar-loader', parseInt(size), parseInt(size));
	  }

	  static getDayCode(date) {
	    return date.getFullYear() + '-' + ("0" + ~~(date.getMonth() + 1)).substr(-2, 2) + '-' + ("0" + ~~date.getDate()).substr(-2, 2);
	  }

	  static getTextColor(color) {
	    if (!color) {
	      return false;
	    }

	    if (color.charAt(0) === "#") {
	      color = color.substring(1, 7);
	    }

	    let r = parseInt(color.substring(0, 2), 16),
	        g = parseInt(color.substring(2, 4), 16),
	        b = parseInt(color.substring(4, 6), 16),
	        light = (r * 0.8 + g + b * 0.2) / 510 * 100;
	    return light < 50;
	  }

	  static getKeyCode(key) {
	    if (!main_core.Type.isString(key)) {
	      return false;
	    }

	    let KEY_CODES = {
	      'backspace': 8,
	      'enter': 13,
	      'escape': 27,
	      'space': 32,
	      'delete': 46,
	      'left': 37,
	      'right': 39,
	      'up': 38,
	      'down': 40,
	      'z': 90,
	      'y': 89,
	      'shift': 16,
	      'ctrl': 17,
	      'alt': 18,
	      'cmd': 91,
	      // 93, 224, 17 Browser dependent
	      'cmdRight': 93,
	      // 93, 224, 17 Browser dependent?
	      'pageUp': 33,
	      'pageDown': 34,
	      'd': 68,
	      'w': 87,
	      'm': 77,
	      'a': 65
	    };
	    return KEY_CODES[key.toLowerCase()];
	  }

	  static getUsableDateTime(timestamp, roundMin) {
	    if (main_core.Type.isDate(timestamp)) {
	      timestamp = timestamp.getTime();
	    }

	    let r = (roundMin || 10) * 60 * 1000;
	    timestamp = Math.ceil(timestamp / r) * r;
	    return new Date(timestamp);
	  }

	  static showNotification(message, actions = null) {
	    if (main_core.Type.isString(message) && message !== '') {
	      BX.UI.Notification.Center.notify({
	        content: message,
	        actions: actions
	      });
	    }
	  }

	  static showFieldError(message, wrap, options) {
	    if (main_core.Type.isDomNode(wrap) && main_core.Type.isString(message) && message !== '') {
	      main_core.Dom.remove(wrap.querySelector('.ui-alert'));
	      let alert = new BX.UI.Alert({
	        color: BX.UI.Alert.Color.DANGER,
	        icon: BX.UI.Alert.Icon.DANGER,
	        text: message
	      });
	      let alertWrap = alert.getContainer();
	      wrap.appendChild(alertWrap);
	      return alertWrap;
	    }
	  }

	  static getDateFormat() {
	    if (!Util.DATE_FORMAT) {
	      Util.DATE_FORMAT = BX.Main.Date.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATE"));
	    }

	    return Util.DATE_FORMAT;
	  }

	  static setDayOfWeekMonthFormat(value) {
	    Util.dayOfWeekMonthFormat = value;
	  }

	  static getDayOfWeekMonthFormat() {
	    return Util.dayOfWeekMonthFormat || 'l, j F';
	  }

	  static setDayMonthFormat(value) {
	    Util.dayMonthFormat = value;
	  }

	  static getDayMonthFormat() {
	    return Util.dayMonthFormat || 'j F';
	  }

	  static getDateTimeFormat() {
	    if (!Util.DATETIME_FORMAT) {
	      Util.DATETIME_FORMAT = BX.Main.Date.convertBitrixFormat(main_core.Loc.getMessage("FORMAT_DATETIME"));
	    }

	    return Util.DATETIME_FORMAT;
	  }

	  static getTimeFormat() {
	    if (!Util.TIME_FORMAT) {
	      if (main_core.Loc.getMessage("FORMAT_DATETIME").substr(0, main_core.Loc.getMessage("FORMAT_DATE").length) === main_core.Loc.getMessage("FORMAT_DATE")) {
	        Util.TIME_FORMAT = BX.util.trim(Util.getDateTimeFormat().substr(Util.getDateFormat().length));
	        Util.TIME_FORMAT_BX = BX.util.trim(main_core.Loc.getMessage("FORMAT_DATETIME").substr(main_core.Loc.getMessage("FORMAT_DATE").length));
	      } else {
	        Util.TIME_FORMAT_BX = BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS';
	        Util.TIME_FORMAT = BX.date.convertBitrixFormat(BX.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
	      }
	    }

	    return Util.TIME_FORMAT;
	  }

	  static getTimeFormatShort() {
	    if (!Util.TIME_FORMAT_SHORT) {
	      Util.TIME_FORMAT_SHORT = Util.getTimeFormat().replace(':s', '');
	      Util.TIME_FORMAT_SHORT_BX = Util.TIME_FORMAT_BX.replace(':SS', '');
	    }

	    return Util.TIME_FORMAT_SHORT;
	  }

	  static getCurrentUserId() {
	    if (!Util.currentUserId) {
	      Util.currentUserId = parseInt(main_core.Loc.getMessage('USER_ID'));
	    }

	    return Util.currentUserId;
	  }

	  static getTimeByInt(intValue) {
	    intValue = parseInt(intValue);
	    let h = Math.floor(intValue / 60);
	    return {
	      hour: h,
	      min: intValue - h * 60
	    };
	  }

	  static preventSelection(node) {
	    node.ondrag = BX.False;
	    node.ondragstart = BX.False;
	    node.onselectstart = BX.False;
	  }

	  static getBX() {
	    return window.top.BX || window.BX;
	  }

	  static closeAllPopups() {
	    if (main_popup.PopupManager.isAnyPopupShown()) {
	      for (let i = 0, length = main_popup.PopupManager._popups.length; i < length; i++) {
	        if (main_popup.PopupManager._popups[i] && main_popup.PopupManager._popups[i].isShown()) {
	          main_popup.PopupManager._popups[i].close();
	        }
	      }
	    }
	  }

	  static sendAnalyticLabel(label) {
	    BX.ajax.runAction('calendar.api.calendarajax.sendAnalyticsLabel', {
	      analyticsLabel: label
	    });
	  }

	  static setOptions(config, additionalParams) {
	    Util.config = config;
	    Util.additionalParams = additionalParams;
	  }

	  static setUserSettings(userSettings) {
	    Util.userSettings = userSettings;
	  }

	  static getUserSettings() {
	    return main_core.Type.isObjectLike(Util.userSettings) ? Util.userSettings : {};
	  }

	  static setCalendarContext(calendarContext) {
	    Util.calendarContext = calendarContext;
	  }

	  static getCalendarContext() {
	    return Util.calendarContext || null;
	  }

	  static getMeetingStatusList() {
	    return ['Y', 'N', 'Q', 'H'];
	  }

	  static checkEmailLimitationPopup() {
	    const emailGuestAmount = Util.getEventWithEmailGuestAmount();
	    const emailGuestLimit = Util.getEventWithEmailGuestLimit();
	    return emailGuestLimit > 0 && (emailGuestAmount === 8 || emailGuestAmount === 4 || emailGuestAmount >= emailGuestLimit);
	  }

	  static isEventWithEmailGuestAllowed() {
	    return Util.getEventWithEmailGuestLimit() === -1 || Util.getEventWithEmailGuestAmount() < Util.getEventWithEmailGuestLimit();
	  }

	  static setEventWithEmailGuestAmount(value) {
	    Util.countEventWithEmailGuestAmount = value;
	  }

	  static setEventWithEmailGuestLimit(value) {
	    Util.eventWithEmailGuestLimit = value;
	  }

	  static getEventWithEmailGuestAmount() {
	    return Util.countEventWithEmailGuestAmount;
	  }

	  static getEventWithEmailGuestLimit() {
	    return Util.eventWithEmailGuestLimit;
	  }

	  static setCurrentView(calendarView = null) {
	    Util.currentCalendarView = calendarView;
	  }

	  static getCurrentView() {
	    return Util.currentCalendarView || null;
	  }

	  static adjustDateForTimezoneOffset(date, timezoneOffset = 0, fullDay = false) {
	    if (!main_core.Type.isDate(date)) throw new Error('Wrong type for date attribute. DateTime object expected.');
	    if (!parseInt(timezoneOffset) || fullDay === true) return date;
	    return new Date(date.getTime() - parseInt(timezoneOffset) * 1000);
	  }

	  static randomInt(min, max) {
	    return Math.round(min - 0.5 + Math.random() * (max - min + 1));
	  }

	  static getRandomColor() {
	    const defaultColors = Util.getDefaultColorList();
	    return defaultColors[Util.randomInt(0, defaultColors.length - 1)];
	  }

	  static setAccessNames(accessNames = {}) {
	    Util.accessNames = {};

	    for (let code in accessNames) {
	      if (accessNames.hasOwnProperty(code)) {
	        Util.setAccessName(code, accessNames[code]);
	      }
	    }
	  }

	  static getAccessName(code) {
	    return Util.accessNames[code] || code;
	  }

	  static setAccessName(code, name) {
	    Util.accessNames[code] = name;
	  }

	  static getRandomInt(numCount = 6) {
	    return Math.round(Math.random() * Math.pow(10, numCount));
	  }

	  static displayError(errors, reloadPage) {
	    if (main_core.Type.isArray(errors)) {
	      let errorMessage = '';

	      for (let i = 0; i < errors.length; i++) {
	        errorMessage += errors[i].message + "\n";
	      }

	      errors = errorMessage;
	    }

	    setTimeout(() => {
	      alert(errors || '[Bitrix Calendar] Request error');

	      if (reloadPage) {
	        location.reload();
	      }
	    }, 200);
	  }

	  static convertEntityToAccessCode(entity) {
	    if (main_core.Type.isObjectLike(entity)) {
	      if (entity.entityId === 'meta-user' && entity.id === 'all-users') {
	        return 'UA';
	      } else if (entity.entityId === 'user') {
	        return 'U' + entity.id;
	      } else if (entity.entityId === 'project') {
	        return 'SG' + entity.id + '_K'; // for all members of group
	      } else if (entity.entityId === 'department') {
	        return 'DR' + entity.id;
	      } else if (entity.entityId === 'group') {
	        return entity.id;
	      }
	    }
	  }

	  static extendPlannerWatches({
	    entries,
	    userId
	  }) {
	    entries.forEach(entry => {
	      if (entry.type === 'user' && parseInt(entry.id) !== parseInt(userId)) {
	        const tag = Util.PLANNER_PULL_TAG.replace('#USER_ID#', entry.id);

	        if (!Util.PLANNER_WATCH_LIST.includes(tag)) {
	          pull_client.PULL.extendWatch(tag);
	          Util.PLANNER_WATCH_LIST.push(tag);
	        }
	      }
	    });
	  }

	  static clearPlannerWatches() {
	    Util.PLANNER_WATCH_LIST.forEach(tag => {
	      pull_client.PULL.clearWatch(tag);
	    });
	    Util.PLANNER_WATCH_LIST = [];
	  }

	  static registerRequestId() {
	    const requestUid = BX.Calendar.Util.getRandomInt(8);
	    Util.REQUEST_ID_LIST.push(requestUid);
	    return requestUid;
	  }

	  static unregisterRequestId(requestUid) {
	    Util.REQUEST_ID_LIST = Util.REQUEST_ID_LIST.filter(uid => {
	      return uid !== requestUid;
	    });
	  }

	  static checkRequestId(requestUid) {
	    requestUid = parseInt(requestUid);
	    return !main_core.Type.isInteger(requestUid) || !Util.REQUEST_ID_LIST.includes(requestUid);
	  }

	  static initHintNode(hintNode) {
	    var _bx$UI;

	    const bx = Util.getBX();

	    if (main_core.Type.isElementNode(hintNode) && bx != null && (_bx$UI = bx.UI) != null && _bx$UI.Hint) {
	      var _bx$UI2, _bx$UI2$Hint;

	      if (bx != null && (_bx$UI2 = bx.UI) != null && (_bx$UI2$Hint = _bx$UI2.Hint) != null && _bx$UI2$Hint.popup) {
	        bx.UI.Hint.popup.destroy();
	        bx.UI.Hint.popup = null;
	        bx.UI.Hint.content = null;
	      }

	      bx.UI.Hint.initNode(hintNode);
	    }
	  }

	  static documentIsDisplayingNow() {
	    return !document.hidden;
	  }

	  static removeHash() {
	    if ("pushState" in history) {
	      history.pushState("", document.title, window.location.pathname + window.location.search);
	    } else {
	      // Prevent scrolling by storing the page's current scroll offset
	      let scrollV = document.body.scrollTop;
	      let scrollH = document.body.scrollLeft;
	      window.location.hash = ""; // Restore the scroll offset, should be flicker free

	      document.body.scrollTop = scrollV;
	      document.body.scrollLeft = scrollH;
	    }
	  } // TODO: move to syncManager


	  static setIphoneConnectionStatus(value) {
	    Util.iphoneConnectionStatus = value;
	  }

	  static isIphoneConnected() {
	    return Util.iphoneConnectionStatus;
	  }

	  static setMacConnectionStatus(value) {
	    Util.macConnectionStatus = value;
	  }

	  static isMacConnected() {
	    return Util.macConnectionStatus;
	  }

	  static setIcloudConnectionStatus(value) {
	    Util.icloudConnectionStatus = value;
	  }

	  static isIcloudConnected() {
	    return Util.icloudConnectionStatus;
	  }

	  static setGoogleConnectionStatus(value) {
	    Util.googleConnectionStatus = value;
	  }

	  static isGoogleConnected() {
	    return Util.googleConnectionStatus;
	  }

	}
	Util.PLANNER_PULL_TAG = 'calendar-planner-#USER_ID#';
	Util.PLANNER_WATCH_LIST = [];
	Util.REQUEST_ID_LIST = [];
	Util.accessNames = {};

	exports.Util = Util;

}((this.BX.Calendar = this.BX.Calendar || {}),BX,BX,BX.Main,BX));
//# sourceMappingURL=util.bundle.js.map
