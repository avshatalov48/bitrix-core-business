;(function(){

if (BX.date)
	return;

BX.date = {};


BX.date.format = function(format, timestamp, now, utc)
{
	/*
	PHP to Javascript:
		time() = new Date()
		mktime(...) = new Date(...)
		gmmktime(...) = new Date(Date.UTC(...))
		mktime(0,0,0, 1, 1, 1970) != 0          new Date(1970,0,1).getTime() != 0
		gmmktime(0,0,0, 1, 1, 1970) == 0        new Date(Date.UTC(1970,0,1)).getTime() == 0
		date("d.m.Y H:i:s") = BX.date.format("d.m.Y H:i:s")
		gmdate("d.m.Y H:i:s") = BX.date.format("d.m.Y H:i:s", null, null, true);
	*/
	var date = BX.type.isDate(timestamp) ? new Date(timestamp.getTime()) : BX.type.isNumber(timestamp) ? new Date(timestamp * 1000) : new Date();
	var nowDate = BX.type.isDate(now) ? new Date(now.getTime()) : BX.type.isNumber(now) ? new Date(now * 1000) : new Date();
	var isUTC = !!utc;

	if (BX.type.isArray(format))
		return _formatDateInterval(format, date, nowDate, isUTC);
	else if (!BX.type.isNotEmptyString(format))
		return "";

	var formatRegex = /\\?(sago|iago|isago|Hago|dago|mago|Yago|sdiff|idiff|Hdiff|ddiff|mdiff|Ydiff|sshort|ishort|Hshort|dshort|mhort|Yshort|yesterday|today|tommorow|tomorrow|[a-z])/gi;

	var dateFormats = {
		d : function() {
			// Day of the month 01 to 31
			return BX.util.str_pad_left(getDate(date).toString(), 2, "0");
		},

		D : function() {
			//Mon through Sun
			return BX.message("DOW_" + getDay(date));
		},

		j : function() {
			//Day of the month 1 to 31
			return getDate(date);
		},

		l : function() {
			//Sunday through Saturday
			return BX.message("DAY_OF_WEEK_" + getDay(date));
		},

		N : function() {
			//1 (for Monday) through 7 (for Sunday)
			return getDay(date) || 7;
		},

		S : function() {
			//st, nd, rd or th. Works well with j
			if (getDate(date) % 10 == 1 && getDate(date) != 11)
				return "st";
			else if (getDate(date) % 10 == 2 && getDate(date) != 12)
				return "nd";
			else if (getDate(date) % 10 == 3 && getDate(date) != 13)
				return "rd";
			else
				return "th";
		},

		w : function() {
			//0 (for Sunday) through 6 (for Saturday)
			return getDay(date);
		},

		z : function() {
			//0 through 365
			var firstDay = new Date(getFullYear(date), 0, 1);
			var currentDay = new Date(getFullYear(date), getMonth(date), getDate(date));
			return Math.ceil( (currentDay - firstDay) / (24 * 3600 * 1000) );
		},

		W : function() {
			//ISO-8601 week number of year
			var newDate  = new Date(date.getTime());
		    var dayNumber   = (getDay(date) + 6) % 7;
			setDate(newDate, getDate(newDate) - dayNumber + 3);
		    var firstThursday = newDate.getTime();
			setMonth(newDate, 0, 1);
		    if (getDay(newDate) != 4)
				setMonth(newDate, 0, 1 + ((4 - getDay(newDate)) + 7) % 7);
			var weekNumber = 1 + Math.ceil((firstThursday - newDate) / (7 * 24 * 3600 * 1000));
		    return BX.util.str_pad_left(weekNumber.toString(), 2, "0");
		},

		F : function() {
			//January through December
			return BX.message("MONTH_" + (getMonth(date) + 1) + "_S");
		},

		f : function() {
			//January through December
			return BX.message("MONTH_" + (getMonth(date) + 1));
		},

		m : function() {
			//Numeric representation of a month 01 through 12
			return BX.util.str_pad_left((getMonth(date) + 1).toString(), 2, "0");
		},

		M : function() {
			//A short textual representation of a month, three letters Jan through Dec
			return BX.message("MON_" + (getMonth(date) + 1));
		},

		n : function() {
			//Numeric representation of a month 1 through 12
			return getMonth(date) + 1;
		},

		t : function() {
			//Number of days in the given month 28 through 31
			var lastMonthDay = isUTC ? new Date(Date.UTC(getFullYear(date), getMonth(date) + 1, 0)) : new Date(getFullYear(date), getMonth(date) + 1, 0);
			return getDate(lastMonthDay);
		},

		L : function() {
			//1 if it is a leap year, 0 otherwise.
			var year = getFullYear(date);
			return (year % 4 == 0 && year % 100 != 0 || year % 400 == 0 ? 1 : 0);
		},

		o : function() {
			//ISO-8601 year number
			var correctDate  = new Date(date.getTime());
			setDate(correctDate, getDate(correctDate) - ((getDay(date) + 6) % 7) + 3);
   			return getFullYear(correctDate);
		},

		Y : function() {
			//A full numeric representation of a year, 4 digits
			return getFullYear(date);
		},

		y : function() {
			//A two digit representation of a year
			return getFullYear(date).toString().slice(2);
		},

		a : function() {
			//am or pm
			return getHours(date) > 11 ? "pm" : "am";
		},

		A : function() {
			//AM or PM
			return getHours(date) > 11 ? "PM" : "AM";
		},

		B : function() {
			//000 through 999
			var swatch = ((date.getUTCHours() + 1) % 24) + date.getUTCMinutes() / 60 + date.getUTCSeconds() / 3600;
			return BX.util.str_pad_left(Math.floor(swatch * 1000 / 24).toString(), 3, "0");
		},

		g : function() {
			//12-hour format of an hour without leading zeros 1 through 12
			return getHours(date) % 12 || 12;
		},

		G : function() {
			//24-hour format of an hour without leading zeros 0 through 23
			return getHours(date);
		},

		h : function() {
			//12-hour format of an hour with leading zeros 01 through 12
			return BX.util.str_pad_left((getHours(date) % 12 || 12).toString(), 2, "0");
		},

		H : function() {
			//24-hour format of an hour with leading zeros 00 through 23
			return BX.util.str_pad_left(getHours(date).toString(), 2, "0");
		},

		i : function() {
			//Minutes with leading zeros 00 to 59
			return BX.util.str_pad_left(getMinutes(date).toString(), 2, "0");
		},

		s : function() {
			//Seconds, with leading zeros 00 through 59
			return BX.util.str_pad_left(getSeconds(date).toString(), 2, "0");
		},

		u : function() {
			//Microseconds
			return BX.util.str_pad_left((getMilliseconds(date) * 1000).toString(), 6, "0");
		},

		e : function() {
			if (isUTC)
				return "UTC";
			return "";
		},

		I : function() {
			if (isUTC)
				return 0;

			//Whether or not the date is in daylight saving time 1 if Daylight Saving Time, 0 otherwise
			var firstJanuary = new Date(getFullYear(date), 0, 1);
			var firstJanuaryUTC = Date.UTC(getFullYear(date), 0, 1);
			var firstJuly = new Date(getFullYear(date), 6, 0);
			var firstJulyUTC = Date.UTC(getFullYear(date), 6, 0);
			return 0 + ((firstJanuary - firstJanuaryUTC) !== (firstJuly - firstJulyUTC));
		},

		O : function() {
			if (isUTC)
				return "+0000";

			//Difference to Greenwich time (GMT) in hours +0200
			var timezoneOffset = date.getTimezoneOffset();
			var timezoneOffsetAbs = Math.abs(timezoneOffset);
			return (timezoneOffset > 0 ? "-" : "+") + BX.util.str_pad_left((Math.floor(timezoneOffsetAbs / 60) * 100 + timezoneOffsetAbs % 60).toString(), 4, "0");
		},

		P : function() {
			if (isUTC)
				return "+00:00";

			//Difference to Greenwich time (GMT) with colon between hours and minutes +02:00
			var difference = this.O();
			return difference.substr(0, 3) + ":" + difference.substr(3);
		},

		Z : function() {
			if (isUTC)
				return 0;
			//Timezone offset in seconds. The offset for timezones west of UTC is always negative,
			//and for those east of UTC is always positive.
			return -date.getTimezoneOffset() * 60;
		},

		c : function() {
			//ISO 8601 date
			return "Y-m-d\\TH:i:sP".replace(formatRegex, _replaceDateFormat);
		},

		r : function() {
			//RFC 2822 formatted date
			return "D, d M Y H:i:s O".replace(formatRegex, _replaceDateFormat);
		},

		U : function() {
			//Seconds since the Unix Epoch
			return Math.floor(date.getTime() / 1000);
		},

		sago : function() {
			return _formatDateMessage(intval((nowDate - date) / 1000), {
				"0" : "FD_SECOND_AGO_0",
				"1" : "FD_SECOND_AGO_1",
				"10_20" : "FD_SECOND_AGO_10_20",
				"MOD_1" : "FD_SECOND_AGO_MOD_1",
				"MOD_2_4" : "FD_SECOND_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_SECOND_AGO_MOD_OTHER"
			});
		},

		sdiff : function() {
			return _formatDateMessage(intval((nowDate - date) / 1000), {
				"0" : "FD_SECOND_DIFF_0",
				"1" : "FD_SECOND_DIFF_1",
				"10_20" : "FD_SECOND_DIFF_10_20",
				"MOD_1" : "FD_SECOND_DIFF_MOD_1",
				"MOD_2_4" : "FD_SECOND_DIFF_MOD_2_4",
				"MOD_OTHER" : "FD_SECOND_DIFF_MOD_OTHER"
			});
		},

		sshort : function() {
			return BX.message("FD_SECOND_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 1000));
		},

		iago : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 1000), {
				"0" : "FD_MINUTE_AGO_0",
				"1" : "FD_MINUTE_AGO_1",
				"10_20" : "FD_MINUTE_AGO_10_20",
				"MOD_1" : "FD_MINUTE_AGO_MOD_1",
				"MOD_2_4" : "FD_MINUTE_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_MINUTE_AGO_MOD_OTHER"
			});
		},

		idiff : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 1000), {
				"0" : "FD_MINUTE_DIFF_0",
				"1" : "FD_MINUTE_DIFF_1",
				"10_20" : "FD_MINUTE_DIFF_10_20",
				"MOD_1" : "FD_MINUTE_DIFF_MOD_1",
				"MOD_2_4" : "FD_MINUTE_DIFF_MOD_2_4",
				"MOD_OTHER" : "FD_MINUTE_DIFF_MOD_OTHER"
			});
		},

		isago : function() {
			var minutesAgo = intval((nowDate - date) / 60 / 1000);
			var result = _formatDateMessage(minutesAgo, {
				"0" : "FD_MINUTE_0",
				"1" : "FD_MINUTE_1",
				"10_20" : "FD_MINUTE_10_20",
				"MOD_1" : "FD_MINUTE_MOD_1",
				"MOD_2_4" : "FD_MINUTE_MOD_2_4",
				"MOD_OTHER" : "FD_MINUTE_MOD_OTHER"
			});

			result += " ";

			var secondsAgo = intval((nowDate - date) / 1000) - (minutesAgo * 60);
			result += _formatDateMessage(secondsAgo, {
				"0" : "FD_SECOND_AGO_0",
				"1" : "FD_SECOND_AGO_1",
				"10_20" : "FD_SECOND_AGO_10_20",
				"MOD_1" : "FD_SECOND_AGO_MOD_1",
				"MOD_2_4" : "FD_SECOND_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_SECOND_AGO_MOD_OTHER"
			});
			return result;
		},

		ishort : function() {
		    return BX.message("FD_MINUTE_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 1000));
		},

		Hago : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 1000), {
				"0" : "FD_HOUR_AGO_0",
				"1" : "FD_HOUR_AGO_1",
				"10_20" : "FD_HOUR_AGO_10_20",
				"MOD_1" : "FD_HOUR_AGO_MOD_1",
				"MOD_2_4" : "FD_HOUR_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_HOUR_AGO_MOD_OTHER"
			});
		},

		Hdiff : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 1000), {
				"0" : "FD_HOUR_DIFF_0",
				"1" : "FD_HOUR_DIFF_1",
				"10_20" : "FD_HOUR_DIFF_10_20",
				"MOD_1" : "FD_HOUR_DIFF_MOD_1",
				"MOD_2_4" : "FD_HOUR_DIFF_MOD_2_4",
				"MOD_OTHER" : "FD_HOUR_DIFF_MOD_OTHER"
			});
		},

		Hshort : function() {
			return BX.message("FD_HOUR_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 1000));
		},

		yesterday : function() {
			return BX.message("FD_YESTERDAY");
		},

		today : function() {
			return BX.message("FD_TODAY");
		},

		tommorow : function() {
			return BX.message("FD_TOMORROW");
		},

		tomorrow : function() {
			return BX.message("FD_TOMORROW");
		},

		dago : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 1000), {
				"0" : "FD_DAY_AGO_0",
				"1" : "FD_DAY_AGO_1",
				"10_20" : "FD_DAY_AGO_10_20",
				"MOD_1" : "FD_DAY_AGO_MOD_1",
				"MOD_2_4" : "FD_DAY_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_DAY_AGO_MOD_OTHER"
			});
		},

		ddiff : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 1000), {
				"0" : "FD_DAY_DIFF_0",
				"1" : "FD_DAY_DIFF_1",
				"10_20" : "FD_DAY_DIFF_10_20",
				"MOD_1" : "FD_DAY_DIFF_MOD_1",
				"MOD_2_4" : "FD_DAY_DIFF_MOD_2_4",
				"MOD_OTHER" : "FD_DAY_DIFF_MOD_OTHER"
			});
		},

		dshort : function() {
			return BX.message("FD_DAY_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 24 / 1000));
		},

		mago : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000), {
				"0" : "FD_MONTH_AGO_0",
				"1" : "FD_MONTH_AGO_1",
				"10_20" : "FD_MONTH_AGO_10_20",
				"MOD_1" : "FD_MONTH_AGO_MOD_1",
				"MOD_2_4" : "FD_MONTH_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_MONTH_AGO_MOD_OTHER"
			});
		},

		mdiff : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000), {
				"0" : "FD_MONTH_DIFF_0",
				"1" : "FD_MONTH_DIFF_1",
				"10_20" : "FD_MONTH_DIFF_10_20",
				"MOD_1" : "FD_MONTH_DIFF_MOD_1",
				"MOD_2_4" : "FD_MONTH_DIFF_MOD_2_4",
				"MOD_OTHER" : "FD_MONTH_DIFF_MOD_OTHER"
			});
		},

		mshort : function() {
			return BX.message("FD_MONTH_SHORT").replace(/#VALUE#/g, intval((nowDate - date) / 60 / 60 / 24 / 31 / 1000));
		},

		Yago : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
				"0" : "FD_YEARS_AGO_0",
				"1" : "FD_YEARS_AGO_1",
				"10_20" : "FD_YEARS_AGO_10_20",
				"MOD_1" : "FD_YEARS_AGO_MOD_1",
				"MOD_2_4" : "FD_YEARS_AGO_MOD_2_4",
				"MOD_OTHER" : "FD_YEARS_AGO_MOD_OTHER"
			});
		},

		Ydiff : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
				"0" : "FD_YEARS_DIFF_0",
				"1" : "FD_YEARS_DIFF_1",
				"10_20" : "FD_YEARS_DIFF_10_20",
				"MOD_1" : "FD_YEARS_DIFF_MOD_1",
				"MOD_2_4" : "FD_YEARS_DIFF_MOD_2_4",
				"MOD_OTHER" : "FD_YEARS_DIFF_MOD_OTHER"
			});
		},

		Yshort : function() {
			return _formatDateMessage(intval((nowDate - date) / 60 / 60 / 24 / 365 / 1000), {
				"0" : "FD_YEARS_SHORT_0",
				"1" : "FD_YEARS_SHORT_1",
				"10_20" : "FD_YEARS_SHORT_10_20",
				"MOD_1" : "FD_YEARS_SHORT_MOD_1",
				"MOD_2_4" : "FD_YEARS_SHORT_MOD_2_4",
				"MOD_OTHER" : "FD_YEARS_SHORT_MOD_OTHER"
			});
		},

		x : function() {
			var ampm = BX.isAmPmMode(true);
			var timeFormat = (ampm === BX.AM_PM_LOWER? "g:i a" : (ampm === BX.AM_PM_UPPER? "g:i A" : "H:i"));

			return BX.date.format([
				["tomorrow", "tomorrow, "+timeFormat],
				["-", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME")).replace(/:s/g, "")],
				["s", "sago"],
				["i", "iago"],
				["today", "today, "+ampm],
				["yesterday", "yesterday, "+ampm],
				["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME")).replace(/:s/g, "")]
			], date, nowDate, isUTC);
		},

		X : function() {

			var ampm = BX.isAmPmMode(true);
			var timeFormat = (ampm === BX.AM_PM_LOWER? "g:i a" : (ampm === BX.AM_PM_UPPER? "g:i A" : "H:i"));

			var day = BX.date.format([
				["tomorrow", "tomorrow"],
				["-", BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"))],
				["today", "today"],
				["yesterday", "yesterday"],
				["", BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"))]
			], date, nowDate, isUTC);

			var time = BX.date.format([
				["tomorrow", timeFormat],
				["today", timeFormat],
				["yesterday", timeFormat],
				["", ""]
			], date, nowDate, isUTC);

			if (time.length > 0)
				return BX.message("FD_DAY_AT_TIME").replace(/#DAY#/g, day).replace(/#TIME#/g, time);
			else
				return day;
		},

		Q : function() {
			var daysAgo = intval((nowDate - date) / 60 / 60 / 24 / 1000);
			if(daysAgo == 0)
				return BX.message("FD_DAY_DIFF_1").replace(/#VALUE#/g, 1);
			else
				return BX.date.format([ ["d", "ddiff"], ["m", "mdiff"], ["", "Ydiff"] ], date, nowDate);
		}
	};

	var cutZeroTime = false;
	if (format[0] && format[0] == "^")
	{
		cutZeroTime = true;
		format = format.substr(1);
	}

	var result = format.replace(formatRegex, _replaceDateFormat);

	if (cutZeroTime)
	{
		/* 	15.04.12 13:00:00 => 15.04.12 13:00
			00:01:00 => 00:01
			4 may 00:00:00 => 4 may
			01-01-12 00:00 => 01-01-12
		*/

		result = result.replace(/\s*00:00:00\s*/g, "").
						replace(/(\d\d:\d\d)(:00)/g, "$1").
						replace(/(\s*00:00\s*)(?!:)/g, "");
	}

	return result;

	function _formatDateInterval(formats, date, nowDate, isUTC)
	{
		var secondsAgo = intval((nowDate - date) / 1000);
		for (var i = 0; i < formats.length; i++)
		{
			var formatInterval = formats[i][0];
			var formatValue = formats[i][1];
			var match = null;
			if (formatInterval == "s")
			{
				if (secondsAgo < 60)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if ((match = /^s(\d+)\>?(\d+)?/.exec(formatInterval)) != null)
			{
				if (match[1] && match[2])
				{
					if (
						secondsAgo < match[1]
						&& secondsAgo > match[2]
					)
					{
						return BX.date.format(formatValue, date, nowDate, isUTC);
					}
				}
				else if (secondsAgo < match[1])
				{
					return BX.date.format(formatValue, date, nowDate, isUTC);
				}
			}
			else if (formatInterval == "i")
			{
				if (secondsAgo < 60 * 60)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if ((match = /^i(\d+)\>?(\d+)?/.exec(formatInterval)) != null)
			{
				if (match[1] && match[2])
				{
					if (
						secondsAgo < match[1] * 60
						&& secondsAgo > match[2] * 60
					)
					{
						return BX.date.format(formatValue, date, nowDate, isUTC);
					}
				}
				else if (secondsAgo < match[1] * 60)
				{
					return BX.date.format(formatValue, date, nowDate, isUTC);
				}
			}
			else if (formatInterval == "H")
			{
				if (secondsAgo < 24 * 60 * 60)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if ((match = /^H(\d+)\>?(\d+)?/.exec(formatInterval)) != null)
			{
				if (match[1] && match[2])
				{
					if (
						secondsAgo < match[1] * 60 * 60
						&& secondsAgo > match[2] * 60 * 60
					)
					{
						return BX.date.format(formatValue, date, nowDate, isUTC);
					}
				}
				else if (secondsAgo < match[1] * 60 * 60)
				{
					return BX.date.format(formatValue, date, nowDate, isUTC);
				}
			}
			else if (formatInterval == "d")
			{
				if (secondsAgo < 31 *24 * 60 * 60)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if ((match = /^d(\d+)\>?(\d+)?/.exec(formatInterval)) != null)
			{
				if (match[1] && match[2])
				{
					if (
						secondsAgo < match[1] * 24 * 60 * 60
						&& secondsAgo > match[2] * 24 * 60 * 60
					)
					{
						return BX.date.format(formatValue, date, nowDate, isUTC);
					}
				}
				else if (secondsAgo < match[1] * 24 * 60 * 60)
				{
					return BX.date.format(formatValue, date, nowDate, isUTC);
				}
			}
			else if (formatInterval == "m")
			{
				if (secondsAgo < 365 * 24 * 60 * 60)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if ((match = /^m(\d+)\>?(\d+)?/.exec(formatInterval)) != null)
			{
				if (match[1] && match[2])
				{
					if (
						secondsAgo < match[1] * 31 * 24 * 60 * 60
						&& secondsAgo > match[2] * 31 * 24 * 60 * 60
					)
					{
						return BX.date.format(formatValue, date, nowDate, isUTC);
					}
				}
				else if (secondsAgo < match[1] * 31 * 24 * 60 * 60)
				{
					return BX.date.format(formatValue, date, nowDate, isUTC);
				}
			}
			else if (formatInterval == "now")
			{
				if (date.getTime() == nowDate.getTime())
				{
					return BX.date.format(formatValue, date, nowDate, isUTC);
				}
			}
			else if (formatInterval == "today")
			{
				var year = getFullYear(nowDate), month = getMonth(nowDate), day = getDate(nowDate);
				var todayStart = isUTC ? new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) : new Date(year, month, day, 0, 0, 0, 0);
				var todayEnd = isUTC ? new Date(Date.UTC(year, month, day+1, 0, 0, 0, 0)) : new Date(year, month, day+1, 0, 0, 0, 0);
				if (date >= todayStart && date < todayEnd)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if (formatInterval == "todayFuture")
			{
				var year = getFullYear(nowDate), month = getMonth(nowDate), day = getDate(nowDate);
				var todayStart = nowDate.getTime();
				var todayEnd = isUTC ? new Date(Date.UTC(year, month, day+1, 0, 0, 0, 0)) : new Date(year, month, day+1, 0, 0, 0, 0);
				if (date >= todayStart && date < todayEnd)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if (formatInterval == "yesterday")
			{
				year = getFullYear(nowDate); month = getMonth(nowDate); day = getDate(nowDate);
				var yesterdayStart = isUTC ? new Date(Date.UTC(year, month, day-1, 0, 0, 0, 0)) : new Date(year, month, day-1, 0, 0, 0, 0);
				var yesterdayEnd = isUTC ? new Date(Date.UTC(year, month, day, 0, 0, 0, 0)) : new Date(year, month, day, 0, 0, 0, 0);
				if (date >= yesterdayStart && date < yesterdayEnd)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if (formatInterval == "tommorow" || formatInterval == "tomorrow")
			{
				year = getFullYear(nowDate); month = getMonth(nowDate); day = getDate(nowDate);
				var tomorrowStart = isUTC ? new Date(Date.UTC(year, month, day+1, 0, 0, 0, 0)) : new Date(year, month, day+1, 0, 0, 0, 0);
				var tomorrowEnd = isUTC ? new Date(Date.UTC(year, month, day+2, 0, 0, 0, 0)) : new Date(year, month, day+2, 0, 0, 0, 0);
				if (date >= tomorrowStart && date < tomorrowEnd)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
			else if (formatInterval == "-")
			{
				if (secondsAgo < 0)
					return BX.date.format(formatValue, date, nowDate, isUTC);
			}
		}

		//return formats.length > 0 ? BX.date.format(formats.pop()[1], date, nowDate, isUTC) : "";
		return formats.length > 0 ? BX.date.format(formats[formats.length - 1][1], date, nowDate, isUTC) : "";
	}


	function getFullYear(date) { return isUTC ? date.getUTCFullYear() : date.getFullYear(); }
	function getDate(date) { return isUTC ? date.getUTCDate() : date.getDate(); }
	function getMonth(date) { return isUTC ? date.getUTCMonth() : date.getMonth(); }
	function getHours(date) { return isUTC ? date.getUTCHours() : date.getHours(); }
	function getMinutes(date) { return isUTC ? date.getUTCMinutes() : date.getMinutes(); }
	function getSeconds(date) { return isUTC ? date.getUTCSeconds() : date.getSeconds(); }
	function getMilliseconds(date) { return isUTC ? date.getUTCMilliseconds() : date.getMilliseconds(); }
	function getDay(date) { return isUTC ? date.getUTCDay() : date.getDay(); }
	function setDate(date, dayValue) { return isUTC ? date.setUTCDate(dayValue) : date.setDate(dayValue); }
	function setMonth(date, monthValue, dayValue) { return isUTC ? date.setUTCMonth(monthValue, dayValue) : date.setMonth(monthValue, dayValue); }

	function _formatDateMessage(value, messages)
	{
		var val = value < 100 ? Math.abs(value) : Math.abs(value % 100);
		var dec = val % 10;
		var message = "";

		if(val == 0)
			message = BX.message(messages["0"]);
		else if (val == 1)
			message = BX.message(messages["1"]);
		else if (val >= 10 && val <= 20)
			message = BX.message(messages["10_20"]);
		else if (dec == 1)
			message = BX.message(messages["MOD_1"]);
		else if (2 <= dec && dec <= 4)
			message = BX.message(messages["MOD_2_4"]);
		else
			message = BX.message(messages["MOD_OTHER"]);

		return message.replace(/#VALUE#/g, value);
	}

	function _replaceDateFormat(match, matchFull)
	{
		if (dateFormats[match])
			return dateFormats[match]();
		else
			return matchFull;
	}

	function intval(number)
	{
		return number >= 0 ? Math.floor(number) : Math.ceil(number);
	}
};

BX.date.convertBitrixFormat = function(format)
{
	if (!BX.type.isNotEmptyString(format))
		return "";

	return format.replace("YYYY", "Y")	// 1999
				 .replace("MMMM", "F")	// January - December
				 .replace("MM", "m")	// 01 - 12
				 .replace("M", "M")	// Jan - Dec
				 .replace("DD", "d")	// 01 - 31
				 .replace("G", "g")	//  1 - 12
				 .replace(/GG/i, "G")	//  0 - 23
				 .replace("H", "h")	// 01 - 12
				 .replace(/HH/i, "H")	// 00 - 24
				 .replace("MI", "i")	// 00 - 59
				 .replace("SS", "s")	// 00 - 59
				 .replace("TT", "A")	// AM - PM
				 .replace("T", "a");	// am - pm
};

BX.date.convertToUTC = function(date)
{
	if (!BX.type.isDate(date))
		return null;
	return new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()));
};

/*
 function creates and returns Javascript Date() object from server timestamp regardless of local browser (system) timezone.
 For example can be used to convert timestamp from some exact date on server to the JS Date object with the same value.

 params: {
 timestamp: timestamp in seconds
 }
 */
BX.date.getNewDate = function(timestamp)
{
	return new Date(BX.date.getBrowserTimestamp(timestamp));
};

/*
 function transforms server timestamp (in sec) to javascript timestamp (calculated depend on local browser timezone offset). Returns timestamp in milliseconds.
 Also see BX.date.getNewDate description.

 params: {
 timestamp: timestamp in seconds
 }
 */
BX.date.getBrowserTimestamp = function(timestamp)
{
	timestamp = parseInt(timestamp, 10);
	var browserOffset = new Date(timestamp * 1000).getTimezoneOffset() * 60;
	return (parseInt(timestamp, 10) + parseInt(BX.message('SERVER_TZ_OFFSET')) + browserOffset) * 1000;
};

/*
 function transforms local browser timestamp (in ms) to server timestamp (calculated depend on local browser timezone offset). Returns timestamp in seconds.

 params: {
 timestamp: timestamp in milliseconds
 }
 */
BX.date.getServerTimestamp = function(timestamp)
{
	timestamp = parseInt(timestamp, 10);
	var browserOffset = new Date(timestamp).getTimezoneOffset() * 60;
	return Math.round(timestamp / 1000 - (parseInt(BX.message('SERVER_TZ_OFFSET'), 10) + parseInt(browserOffset, 10)));
};

BX.date.formatLastActivityDate = function(timestamp, now, utc)
{
	var ampm = BX.isAmPmMode(true);
	var timeFormat = (ampm === BX.AM_PM_LOWER? "g:i a" : (ampm === BX.AM_PM_UPPER? "g:i A" : "H:i"));

	var format = [
	   ["tomorrow", "#01#"+timeFormat],
	   ["now" , "#02#"],
	   ["todayFuture", "#03#"+timeFormat],
	   ["yesterday", "#04#"+timeFormat],
	   ["-", BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME")).replace(/:s/g, "")],
	   ["s60", "sago"],
	   ["i60", "iago"],
	   ["H5", "Hago"],
	   ["H24", "#03#"+timeFormat],
	   ["d31", "dago"],
	   ["m12>1", "mago"],
	   ["m12>0", "dago"],
	   ["", "#05#"]
	];
	var formattedDate = BX.date.format(format, timestamp, now, utc);

	if ((match = /^#(\d+)#(.*)/.exec(formattedDate)) != null)
	{
		switch (match[1])
		{
			case "01":
				formattedDate = BX.message('FD_LAST_SEEN_TOMORROW').replace("#TIME#", match[2]);
			break;
			case "02":
				formattedDate = BX.message('FD_LAST_SEEN_NOW');
			break;
			case "03":
				formattedDate = BX.message('FD_LAST_SEEN_TODAY').replace("#TIME#", match[2]);
			break;
			case "04":
				formattedDate = BX.message('FD_LAST_SEEN_YESTERDAY').replace("#TIME#", match[2]);
			break;
			case "05":
				formattedDate = BX.message('FD_LAST_SEEN_MORE_YEAR');
			break;
			default:
				formattedDate = match[2];
			break;
		}
	}

	return formattedDate;
};

/************************************** calendar class **********************************/

var obCalendarSingleton = null;

/*
params: {
	node: bind element || document.body

	value - start value in site format (using 'field' param if 'value' does not exist)
	callback - date check handler. can return false to prevent calendar closing.
	callback_after - another handler, called after date picking

	field - field to read/write data

	bTime = true - whether to enable time control
	bHideTime = false - whether to hide time control by default

	currentTime - current UTC time()

}
*/


BX.calendar = function(params)
{
	return BX.calendar.get().Show(params);
};

BX.calendar.get = function()
{
	if (!obCalendarSingleton)
		obCalendarSingleton = new BX.JCCalendar();

	return obCalendarSingleton;
};

// simple func for compatibility with the oldies
BX.calendar.InsertDaysBack = function(input, days)
{
	if (days != '')
	{
		var d = new Date();
		if(days > 0)
		{
			d.setTime(d.valueOf() - days*86400000);
		}

		input.value = BX.date.format(BX.date.convertBitrixFormat(BX.message('FORMAT_DATE')), d, null);
	}
	else
	{
		input.value = '';
	}
};

BX.calendar.ValueToString = function(value, bTime, bUTC)
{
	return BX.date.format(
		BX.date.convertBitrixFormat(BX.message(bTime ? 'FORMAT_DATETIME' : 'FORMAT_DATE')),
		value,
		null,
		!!bUTC
	);
};

BX.calendar.ValueToStringFormat = function(value, bitrixFormat, bUTC)
{
	return BX.date.format(
		BX.date.convertBitrixFormat(bitrixFormat),
		value,
		null,
		!!bUTC
	);
};


BX.CalendarPeriod =
{
	Init: function(inputFrom, inputTo, selPeriod)
	{
		if((inputFrom.value != "" || inputTo.value != "") && selPeriod.value == "")
			selPeriod.value = "interval";

		selPeriod.onchange();
	},

	ChangeDirectOpts: function(peroidValue, selPParent) // "week" || "others"
	{
		var selDirect = BX.findChild(selPParent, {'className':'adm-select adm-calendar-direction'}, true);

		if(peroidValue == "week")
		{
			selDirect.options[0].text = BX.message('JSADM_CALEND_PREV_WEEK');
			selDirect.options[1].text = BX.message('JSADM_CALEND_CURR_WEEK');
			selDirect.options[2].text = BX.message('JSADM_CALEND_NEXT_WEEK');
		}
		else
		{
			selDirect.options[0].text = BX.message('JSADM_CALEND_PREV');
			selDirect.options[1].text = BX.message('JSADM_CALEND_CURR');
			selDirect.options[2].text = BX.message('JSADM_CALEND_NEXT');
		}
	},

	SaveAndClearInput: function(oInput)
	{
		if(!window.SavedPeriodValues)
			window.SavedPeriodValues = {};

		window.SavedPeriodValues[oInput.id] = oInput.value;
		oInput.value="";
	},

	RestoreInput: function(oInput)
	{
		if(!window.SavedPeriodValues || !window.SavedPeriodValues[oInput.id])
			return;

		oInput.value = window.SavedPeriodValues[oInput.id];
		delete(window.SavedPeriodValues[oInput.id]);
	},

	OnChangeP: function(sel)
	{
		var selPParent = sel.parentNode.parentNode;
		var bShowFrom, bShowTo, bShowDirect, bShowSeparate;
		bShowFrom = bShowTo = bShowDirect = bShowSeparate = false;

		var inputFromWrap = BX.findChild(selPParent, {'className':'adm-input-wrap adm-calendar-inp adm-calendar-first'});
		var inputToWrap = BX.findChild(selPParent, {'className':'adm-input-wrap adm-calendar-second'});
		var selDirectWrap = BX.findChild(selPParent, {'className':'adm-select-wrap adm-calendar-direction'});
		var separator = BX.findChild(selPParent, {'className':'adm-calendar-separate'});
		var inputFrom = BX.findChild(selPParent, {'className':'adm-input adm-calendar-from'},true);
		var inputTo = BX.findChild(selPParent, {'className':'adm-input adm-calendar-to'},true);

		// define who must be shown
		switch (sel.value)
		{
			case "day":
			case "week":
			case "month":
			case "quarter":
			case "year":
				bShowDirect=true;
				BX.CalendarPeriod.OnChangeD(selDirectWrap.children[0]);
				break;

			case "before":
				bShowTo = true;
				break;

			case "after":
				bShowFrom = true;
				break;

			case "exact":
				bShowFrom= true;
				break;

			case "interval":
				bShowFrom = bShowTo = bShowSeparate = true;
				BX.CalendarPeriod.RestoreInput(inputFrom);
				BX.CalendarPeriod.RestoreInput(inputTo);

				break;

			case "":
				BX.CalendarPeriod.SaveAndClearInput(inputFrom);
				BX.CalendarPeriod.SaveAndClearInput(inputTo);
				break;

			default:
				break;

		}

		BX.CalendarPeriod.ChangeDirectOpts(sel.value, selPParent);

		inputFromWrap.style.display = (bShowFrom? 'inline-block':'none');
		inputToWrap.style.display = (bShowTo? 'inline-block':'none');
		selDirectWrap.style.display = (bShowDirect? 'inline-block':'none');
		separator.style.display = (bShowSeparate? 'inline-block':'none');
	},


	OnChangeD: function(sel)
	{
		var selPParent = sel.parentNode.parentNode;
		var inputFrom = BX.findChild(selPParent, {'className':'adm-input adm-calendar-from'},true);
		var inputTo = BX.findChild(selPParent, {'className':'adm-input adm-calendar-to'},true);
		var selPeriod = BX.findChild(selPParent, {'className':'adm-select adm-calendar-period'},true);

		var offset=0;

		switch (sel.value)
		{
			case "previous":
				offset = -1;
				break;

			case "next":
				offset = 1;
				break;

			case "current":
			default:
				break;

		}

		var from = false;
		var to = false;

		var today = new Date();
		var year = today.getFullYear();
		var month = today.getMonth();
		var day = today.getDate();
		var dayW = today.getDay();

		if (dayW == 0)
				dayW = 7;

		switch (selPeriod.value)
		{
			case "day":
				from = new Date(year, month, day+offset, 0, 0, 0);
				to = new Date(year, month, day+offset, 23, 59, 59);
				break;

			case "week":
				from = new Date(year, month, day-dayW+1+offset*7, 0, 0, 0);
				to = new Date(year, month, day+(7-dayW)+offset*7, 23, 59, 59);
				break;

			case "month":
				from = new Date(year, month+offset, 1, 0, 0, 0);
				to = new Date(year, month+1+offset, 0, 23, 59, 59);
				break;

			case "quarter":
				var quarterNum = Math.floor((month/3))+offset;
				from = new Date(year, 3*(quarterNum), 1, 0, 0, 0);
				to = new Date(year, 3*(quarterNum+1), 0, 23, 59, 59);
				break;

			case "year":
				from = new Date(year+offset, 0, 1, 0, 0, 0);
				to = new Date(year+1+offset, 0, 0, 23, 59, 59);
				break;

			default:
				break;
		}

		var format = window[inputFrom.name+"_bTime"] ? BX.message('FORMAT_DATETIME') : BX.message('FORMAT_DATE');

		if(from)
		{
			inputFrom.value = BX.formatDate(from, format);
			BX.addClass(inputFrom,"adm-calendar-inp-setted");
		}

		if(to)
		{
			inputTo.value = BX.formatDate(to, format);
			BX.addClass(inputTo,"adm-calendar-inp-setted");
		}
	}
};


BX.JCCalendar = function()
{
	this.params = {};

	this.bAmPm = BX.isAmPmMode();

	this.popup = null;
	this.popup_month = null;
	this.popup_year = null;
	this.month_popup_classname = '';
	this.year_popup_classname = '';

	this.value = null;

	this.control_id = Math.random();

	this._layers = {};
	this._current_layer = null;

	this.DIV = null;
	this.PARTS = {};

	this.weekStart = 0;
	this.numRows = 6;

	this._create = function(params)
	{
		this.popup = new BX.PopupWindow('calendar_popup_' + this.control_id, params.node, {
			closeByEsc: true,
			autoHide: false,
			content: this._get_content(),
			zIndex: 3000,
			bindOptions: {forceBindPosition: true}
		});

		BX.bind(this.popup.popupContainer, 'click', this.popup.cancelBubble);
	};

	this._auto_hide_disable = function()
	{
		BX.unbind(document, 'click', BX.proxy(this._auto_hide, this));
	};

	this._auto_hide_enable = function()
	{
		BX.bind(document, 'click', BX.proxy(this._auto_hide, this));
	};

	this._auto_hide = function(e)
	{
		this._auto_hide_disable();
		this.popup.close();
	};

	this._get_content = function()
	{
		var _layer_onclick = BX.delegate(function(e) {
			e = e||window.event;
			this.SetDate(new Date(parseInt(BX.proxy_context.getAttribute('data-date'))), e.type=='dblclick')
		}, this);

		this.DIV = BX.create('DIV', {
			props: {className: 'bx-calendar'},
			children: [
				BX.create('DIV', {
					props: {
						className: 'bx-calendar-header'
					},
					children: [
						BX.create('A', {
							attrs: {href: 'javascript:void(0)'},
							props: {className: 'bx-calendar-left-arrow'},
							events: {click: BX.proxy(this._prev, this)}
						}),

						BX.create('SPAN', {
							props: {className: 'bx-calendar-header-content'},
							children: [
								(this.PARTS.MONTH = BX.create('A', {
									attrs: {href: 'javascript:void(0)'},
									props: {className: 'bx-calendar-top-month'},
									events: {click: BX.proxy(this._menu_month, this)}
								})),

								(this.PARTS.YEAR = BX.create('A', {
									attrs: {href: 'javascript:void(0)'},
									props: {className: 'bx-calendar-top-year'},
									events: {click: BX.proxy(this._menu_year, this)}
								}))
							]
						}),

						BX.create('A', {
							attrs: {href: 'javascript:void(0)'},
							props: {className: 'bx-calendar-right-arrow'},
							events: {click: BX.proxy(this._next, this)}
						})
					]
				}),

				(this.PARTS.WEEK = BX.create('DIV', {
					props: {
						className: 'bx-calendar-name-day-wrap'
					}
				})),

				(this.PARTS.LAYERS = BX.create('DIV', {
					props: {
						className: 'bx-calendar-cell-block'
					},
					events: {
						click: BX.delegateEvent({className: 'bx-calendar-cell'}, _layer_onclick),
						dblclick: BX.delegateEvent({className: 'bx-calendar-cell'}, _layer_onclick)
					}
				})),

				(this.PARTS.TIME = BX.create('DIV', {
					props: {
						className: 'bx-calendar-set-time-wrap'
					},
					events: {
						click: BX.delegateEvent(
							{attr: 'data-action'},
							BX.delegate(this._time_actions, this)
						)
					},
					html: '<a href="javascript:void(0)" data-action="time_show" class="bx-calendar-set-time"><i></i>'+BX.message('CAL_TIME_SET')+'</a><div class="bx-calendar-form-block"><span class="bx-calendar-form-text">'+BX.message('CAL_TIME')+'</span><span class="bx-calendar-form"><input type="text" class="bx-calendar-form-input" maxwidth="2" onkeyup="BX.calendar.get()._check_time()" /><span class="bx-calendar-form-separator"></span><input type="text" class="bx-calendar-form-input" maxwidth="2" onkeyup="BX.calendar.get()._check_time()" />'+(this.bAmPm?'<span class="bx-calendar-AM-PM-block"><span class="bx-calendar-AM-PM-text" data-action="time_ampm"></span><span class="bx-calendar-form-arrow-r"><a href="javascript:void(0)" class="bx-calendar-form-arrow-top" data-action="time_ampm_up"><i></i></a><a href="javascript:void(0)" class="bx-calendar-form-arrow-bottom" data-action="time_ampm_down"><i></i></a></span></span>':'')+'</span><a href="javascript:void(0)" data-action="time_hide" class="bx-calendar-form-close"><i></i></a></div>'
				})),

				BX.create('DIV', {
					props: {className: 'bx-calendar-button-block'},
					events: {
						click: BX.delegateEvent(
							{attr: 'data-action'},
							BX.delegate(this._button_actions, this)
						)
					},
					html: '<a href="javascript:void(0)" class="bx-calendar-button bx-calendar-button-select" data-action="submit"><span class="bx-calendar-button-left"></span><span class="bx-calendar-button-text">'+BX.message('CAL_BUTTON')+'</span><span class="bx-calendar-button-right"></span></a><a href="javascript:void(0)" class="bx-calendar-button bx-calendar-button-cancel" data-action="cancel"><span class="bx-calendar-button-left"></span><span class="bx-calendar-button-text">'+BX.message('JS_CORE_WINDOW_CLOSE')+'</span><span class="bx-calendar-button-right"></span></a>'
				})
			]
		});

		this.PARTS.TIME_INPUT_H = BX.findChild(this.PARTS.TIME, {tag: 'INPUT'}, true);
		this.PARTS.TIME_INPUT_M = this.PARTS.TIME_INPUT_H.nextSibling.nextSibling;

		if (this.bAmPm)
			this.PARTS.TIME_AMPM = this.PARTS.TIME_INPUT_M.nextSibling.firstChild;

		var spinner = (new BX.JCSpinner({
			input: this.PARTS.TIME_INPUT_H,
			callback_change: BX.proxy(this._check_time, this),
			bSaveValue: false
		})).Show();
		spinner.className = 'bx-calendar-form-arrow-l';
		this.PARTS.TIME_INPUT_H.parentNode.insertBefore(spinner, this.PARTS.TIME_INPUT_H);

		spinner = (new BX.JCSpinner({
			input: this.PARTS.TIME_INPUT_M,
			callback_change: BX.proxy(this._check_time, this),
			bSaveValue: true
		})).Show();
		spinner.className = 'bx-calendar-form-arrow-r';
		if (!this.PARTS.TIME_INPUT_M.nextSibling)
			this.PARTS.TIME_INPUT_M.parentNode.appendChild(spinner);
		else
			this.PARTS.TIME_INPUT_M.parentNode.insertBefore(spinner, this.PARTS.TIME_INPUT_M.nextSibling);

		for (var i = 0; i < 7; i++)
		{
			this.PARTS.WEEK.appendChild(BX.create('SPAN', {
				props: {
					className: 'bx-calendar-name-day'
				},
				text: BX.message('DOW_' + ((i + this.weekStart) % 7))
			}));
		}

		return this.DIV;
	};

	this._time_actions = function()
	{
		switch (BX.proxy_context.getAttribute('data-action'))
		{
			case 'time_show':
				BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
				this.popup.adjustPosition();
			break;
			case 'time_hide':
				BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
				this.popup.adjustPosition();
			break;
			case 'time_ampm':
				this.PARTS.TIME_AMPM.innerHTML = this.PARTS.TIME_AMPM.innerHTML == 'AM' ? 'PM' : 'AM';
			break;
			case 'time_ampm_up':
				this._check_time({bSaveValue: false}, null, 12);
				return;
			break;
			case 'time_ampm_down':
				this._check_time({bSaveValue: false}, null, -12);
				return;
			break;
		}

		this._check_time();
	};

	this._button_actions = function()
	{
		switch (BX.proxy_context.getAttribute('data-action'))
		{
			case 'submit':
				this.SaveValue();
			break;
			case 'cancel':
				this.Close();
			break;
		}
	};

	this._check_time = function(params, value, direction)
	{
		var h = parseInt(this.PARTS.TIME_INPUT_H.value.substring(0,5),10)||0,
			m = parseInt(this.PARTS.TIME_INPUT_M.value.substring(0,5),10)||0,
			bChanged = false;

		if (!!params && !params.bSaveValue)
		{
			this.value.setUTCHours(this.value.getUTCHours() + direction);
		}
		else if (!isNaN(h))
		{
			if (this.bAmPm)
			{
				if (h != 12 && this.PARTS.TIME_AMPM.innerHTML == 'PM')
				{
					h += 12;
				}
			}

			bChanged = true;
			this.value.setUTCHours(h % 24);
		}

		if (!isNaN(m))
		{
			bChanged = true;
			this.value.setUTCMinutes(m % 60);
		}

		if (bChanged)
		{
			this.SetValue(this.value);
		}
	};

	this._set_layer = function()
	{
		var layerId = parseInt(this.value.getUTCFullYear() + '' + BX.util.str_pad_left(this.value.getUTCMonth()+'', 2, "0"));

		if (!this._layers[layerId])
		{
			this._layers[layerId] = this._create_layer();
			this._layers[layerId].BXLAYERID = layerId;
		}

		if (this._current_layer)
		{
			var v = new Date(this.value.valueOf());
			v.setUTCHours(0); v.setUTCMinutes(0);

			var cur_value = BX.findChild(this._layers[layerId], {
					tag: 'A',
					className: 'bx-calendar-active'
				}, true),
				new_value = BX.findChild(this._layers[layerId], {
					tag: 'A',
					attr: {
						'data-date' : v.valueOf() + ''
					}
				}, true);

			if (cur_value)
			{
				BX.removeClass(cur_value, 'bx-calendar-active');
			}

			if (new_value)
			{
				BX.addClass(new_value, 'bx-calendar-active');
			}

			this._replace_layer(this._current_layer, this._layers[layerId]);
		}
		else
		{
			this.PARTS.LAYERS.appendChild(this._layers[layerId]);
		}

		this._current_layer = this._layers[layerId];
	};

	this._replace_layer = function(old_layer, new_layer)
	{
		if (old_layer != new_layer)
		{
			if (!BX.browser.IsIE() || BX.browser.IsDoctype())
			{
				var dir = old_layer.BXLAYERID > new_layer.BXLAYERID ? 1 : -1;

				var old_top = 0;
				var new_top = -dir * old_layer.offsetHeight;

				old_layer.style.position = 'relative';
				old_layer.style.top = "0px";
				old_layer.style.zIndex = 5;

				new_layer.style.position = 'absolute';
				new_layer.style.top = new_top + 'px';
				new_layer.style.zIndex = 6;

				this.PARTS.LAYERS.appendChild(new_layer);

				var delta = 15;

				var f;
				(f = function() {
					new_top += dir * delta;
					old_top += dir * delta;

					if (dir * new_top < 0)
					{
						old_layer.style.top = old_top + 'px';
						new_layer.style.top = new_top + 'px';
						setTimeout(f, 10);
					}
					else
					{
						old_layer.parentNode.removeChild(old_layer);

						new_layer.style.top = "0px";
						new_layer.style.position = 'static';
						new_layer.style.zIndex = 0;
					}
				})();
			}
			else
			{
				this.PARTS.LAYERS.replaceChild(new_layer, old_layer);
			}
		}
	};

	this._create_layer = function()
	{
		var l = BX.create('DIV', {
			props: {
				className: 'bx-calendar-layer'
			}
		});

		var month_start = new Date(this.value);
		month_start.setUTCHours(0);
		month_start.setUTCMinutes(0);

		month_start.setUTCDate(1);

		if (month_start.getUTCDay() != this.weekStart)
		{
			var d = month_start.getUTCDay() - this.weekStart;
			d += d < 0 ? 7 : 0;
			month_start.setUTCDate(month_start.getUTCDate()-d);
		}

		var cur_month = this.value.getUTCMonth(),
			cur_day = this.value.getUTCDate(),
			s = '';
		for (var i = 0; i < this.numRows; i++)
		{
			s += '<div class="bx-calendar-range'
				+(i == this.numRows-1 ? ' bx-calendar-range-noline' : '')
				+'">';

			for (var j = 0; j < 7; j++)
			{
				d = month_start.getUTCDate();
				var wd = month_start.getUTCDay();
				var className = 'bx-calendar-cell';

				if (cur_month != month_start.getUTCMonth())
					className += ' bx-calendar-date-hidden';
				else if (cur_day == d)
					className += ' bx-calendar-active';


				if (wd == 0 || wd == 6)
					className += ' bx-calendar-weekend';

				s += '<a href="javascript:void(0)" class="'+className+'" data-date="' + month_start.valueOf() + '">' + d + '</a>';

				month_start.setUTCDate(month_start.getUTCDate()+1);
			}
			s += '</div>';
		}

		l.innerHTML = s;

		return l;
	};

	this._prev = function()
	{
		this.SetMonth(this.value.getUTCMonth()-1);
	};

	this._next = function()
	{
		this.SetMonth(this.value.getUTCMonth()+1);
	};

	this._menu_month_content = function()
	{
		var months = '', cur_month = this.value.getMonth(), i;
		for (i = 0; i < 12; i++)
		{
			months += '<span class="bx-calendar-month'+(i == cur_month ? ' bx-calendar-month-active' : '')+'" data-bx-month="' + i + '">' + BX.message('MONTH_' + (i + 1)) + '</span>';
		}

		return '<div class="bx-calendar-month-popup"><div class="bx-calendar-month-title" data-bx-month="' + this.value.getUTCMonth() + '">' + BX.message('MONTH_' + (this.value.getUTCMonth() + 1)) + '</div><div class="bx-calendar-month-content">' + months + '</div></div>';
	};

	this._menu_month = function()
	{
		if (!this.popup_month)
		{
			this.popup_month = new BX.PopupWindow(
				'calendar_popup_month_' + this.control_id, this.PARTS.MONTH,
				{
					content: this._menu_month_content(),
					zIndex: 3001,
					closeByEsc: true,
					autoHide: true,
					offsetTop: -29,
					offsetLeft: -1,
					className: this.month_popup_classname,
					events: {
						onPopupShow: BX.delegate(function() {
							if (this.popup_year)
							{
								this.popup_year.close();
							}
						}, this)
					}
				}
			);

			BX.bind(this.popup_month.popupContainer, 'click', BX.proxy(this.month_popup_click, this));
			this.popup_month.BXMONTH = this.value.getUTCMonth();
		}
		else if (this.popup_month.BXMONTH != this.value.getUTCMonth())
		{
			this.popup_month.setContent(this._menu_month_content());
			this.popup_month.BXMONTH = this.value.getUTCMonth();
		}

		this.popup_month.show();
	};

	this.month_popup_click = function(e)
	{
		var target = e.target || e.srcElement;
		if (target && target.getAttribute && target.getAttribute('data-bx-month'))
		{
			this.SetMonth(parseInt(target.getAttribute('data-bx-month')));
			this.popup_month.close();
		}
	};

	this._menu_year_content = function()
	{
		var s = '<div class="bx-calendar-year-popup"><div class="bx-calendar-year-title" data-bx-year="' + this.value.getUTCFullYear() + '">' + this.value.getUTCFullYear() + '</div><div class="bx-calendar-year-content" id="bx-calendar-year-content">';

		for (var i=-3; i <= 3; i++)
		{
			s += '<span class="bx-calendar-year-number' + (i == 0?' bx-calendar-year-active' : '') + '" data-bx-year="' + (this.value.getUTCFullYear() - i) + '">' + (this.value.getUTCFullYear() - i)+'</span>';
		}

		s += '</div><input data-bx-year-input="Y" type="text" class="bx-calendar-year-input" maxlength="4" /></div>';

		return s;
	};

	this._menu_year = function()
	{
		if (!this.popup_year)
		{
			this.popup_year = new BX.PopupWindow(
				'calendar_popup_year_' + this.control_id, this.PARTS.YEAR,
				{
					content: this._menu_year_content(),
					zIndex: 3001,
					closeByEsc: true,
					autoHide: true,
					offsetTop: -29,
					offsetLeft: -1,
					className: this.year_popup_classname,
					events: {
						onPopupShow: BX.delegate(function() {
							if (this.popup_month)
							{
								this.popup_month.close();
							}
						}, this)
					}
				}
			);

			BX.bind(this.popup_year.popupContainer, 'click', BX.proxy(this.year_popup_click, this));
			BX.bind(this.popup_year.popupContainer, 'keyup', BX.proxy(this.year_popup_keyup, this));
			this.popup_year.BXYEAR = this.value.getUTCFullYear();
		}
		else if (this.popup_year.BXYEAR != this.value.getUTCFullYear())
		{
			this.popup_year.setContent(this._menu_year_content());
			this.popup_year.BXYEAR = this.value.getUTCFullYear();
		}

		this.popup_year.show();
	};

	this.year_popup_click = function(e)
	{
		var target = e.target || e.srcElement;
		if (target && target.getAttribute && target.getAttribute('data-bx-year'))
		{
			this.SetYear(parseInt(target.getAttribute('data-bx-year')));
			this.popup_year.close();
		}
	};
	this.year_popup_keyup = function(e)
	{
		var target = e.target || e.srcElement;
		if (target && target.getAttribute && target.getAttribute('data-bx-year-input') == 'Y')
		{
			var value = parseInt(target.value);
			if(value >= 1900 && value <= 2100)
			{
				this.SetYear(value);
				this.popup_year.close();
			}
		}
	};

	this._check_date = function(v)
	{
		var res = v;

		if (BX.type.isString(v))
		{
			res = BX.parseDate(v, true);
		}

		if (!BX.type.isDate(res) || isNaN(res.valueOf()))
		{
			res = BX.date.convertToUTC(new Date());
			if (this.params.bHideTime)
			{
				res.setUTCHours(0);
				res.setUTCMinutes(0);
			}
		}

		res.setUTCMilliseconds(0);
		res.setUTCSeconds(0);

		res.BXCHECKED = true;

		return res;
	};
};

BX.JCCalendar.prototype.Show = function(params)
{
	if (!BX.isReady)
	{
		BX.ready(BX.delegate(function() {this.Show(params)}, this));
		return;
	}

	params.node = params.node||document.body;

	if (BX.type.isNotEmptyString(params.node))
	{
		var n = BX(params.node);
		if (!n)
		{
			n = document.getElementsByName(params.node);
			if (n && n.length > 0)
			{
				n = n[0]
			}
		}
		params.node = n;
	}

	if (!params.node)
		return;

	if (!!params.field)
	{
		if (BX.type.isString(params.field))
		{
			n = BX(params.field);
			if (!!n)
			{
				params.field = n;
			}
			else
			{
				if (params.form)
				{
					if (BX.type.isString(params.form))
					{
						params.form = document.forms[params.form];
					}
				}

				if (BX.type.isDomNode(params.form) && !!params.form[params.field])
				{
					params.field = params.form[params.field];
				}
				else
				{
					n = document.getElementsByName(params.field);
					if (n && n.length > 0)
					{
						n = n[0];
						params.field = n;
					}
				}
			}

			if (BX.type.isString(params.field))
			{
				params.field = BX(params.field);
			}
		}
	}

	var bShow = !this.popup || !this.popup.isShown() || this.params.node != params.node;

	this.params = params;

	this.params.bTime = typeof this.params.bTime == 'undefined' ? true : !!this.params.bTime;
	this.params.bHideTime = typeof this.params.bHideTime == 'undefined' ? true : !!this.params.bHideTime;
	this.params.bUseSecond = typeof this.params.bUseSecond == 'undefined' ? true : !!this.params.bUseSecond;

	this.weekStart = parseInt(this.params.weekStart || this.params.weekStart || BX.message('WEEK_START'));
	if (isNaN(this.weekStart))
		this.weekStart = 1;

	if (!this.popup)
	{
		this._create(this.params);
	}
	else
	{
		this.popup.setBindElement(this.params.node);
	}

	var bHideTime = !!this.params.bHideTime;
	if (this.params.value)
	{
		this.SetValue(this.params.value);
		bHideTime = this.value.getUTCHours() <= 0 && this.value.getUTCMinutes() <= 0;
	}
	else if (this.params.field)
	{
		this.SetValue(this.params.field.value);
		bHideTime = this.value.getUTCHours() <= 0 && this.value.getUTCMinutes() <= 0;
	}
	else if (!!this.params.currentTime)
	{
		this.SetValue(this.params.currentTime);
	}
	else
	{
		this.SetValue();
	}

	if (!!this.params.bTime)
		BX.removeClass(this.DIV, 'bx-calendar-time-disabled');
	else
		BX.addClass(this.DIV, 'bx-calendar-time-disabled');

	if (!!bHideTime)
		BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
	else
		BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');

	if (bShow)
	{
		this._auto_hide_disable();
		this.popup.show();
		setTimeout(BX.proxy(this._auto_hide_enable, this), 0);
	}

	this.params.bSetFocus = typeof this.params.bSetFocus == 'undefined' ? true : !!this.params.bSetFocus;
	if(this.params.bSetFocus)
	{
		params.node.blur();
	}
	else
	{
		BX.bind(params.node, 'keyup', BX.defer(function(){
			this.SetValue(params.node.value);
			if(!!this.params.bTime)
			{
				if(this.value.getUTCHours() <= 0 && this.value.getUTCMinutes() <= 0)
					BX.removeClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
				else
					BX.addClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');
			}
		}, this));
	}

	return this;
};

BX.JCCalendar.prototype.SetDay = function(d)
{
	this.value.setUTCDate(d);
	return this.SetValue(this.value);
};

BX.JCCalendar.prototype.SetMonth = function(m)
{
	if (this.popup_month)
		this.popup_month.close();

	this.value.setUTCMonth(m);

	if(m < 0)
		m += 12;
	else if (m >= 12)
		m -= 12;

	while(this.value.getUTCMonth() > m)
	{
		this.value.setUTCDate(this.value.getUTCDate()-1);
	}

	return this.SetValue(this.value);
};

BX.JCCalendar.prototype.SetYear = function(y)
{
	if (this.popup_year)
		this.popup_year.close();
	this.value.setUTCFullYear(y);
	return this.SetValue(this.value);
};

BX.JCCalendar.prototype.SetDate = function(v, bSet)
{
	v = this._check_date(v);
	v.setUTCHours(this.value.getUTCHours());
	v.setUTCMinutes(this.value.getUTCMinutes());
	v.setUTCSeconds(this.value.getUTCSeconds());

	if (this.params.bTime && !bSet)
	{
		return this.SetValue(v);
	}
	else
	{
		this.SetValue(v);
		this.SaveValue();
	}
};

BX.JCCalendar.prototype.SetValue = function(v)
{
	this.value = (v && v.BXCHECKED) ? v : this._check_date(v);

	this.PARTS.MONTH.innerHTML = BX.message('MONTH_' + (this.value.getUTCMonth()+1));
	this.PARTS.YEAR.innerHTML = this.value.getUTCFullYear();

	if (!!this.params.bTime)
	{
		var h = this.value.getUTCHours();
		if (this.bAmPm)
		{
			if (h >= 12)
			{
				this.PARTS.TIME_AMPM.innerHTML = 'PM';

				if (h != 12)
					h -= 12;
			}
			else
			{
				this.PARTS.TIME_AMPM.innerHTML = 'AM';

				if (h == 0)
					h = 12;
			}
		}

		this.PARTS.TIME_INPUT_H.value = BX.util.str_pad_left(h.toString(), 2, "0");
		this.PARTS.TIME_INPUT_M.value = BX.util.str_pad_left(this.value.getUTCMinutes().toString(), 2, "0");
	}

	this._set_layer();

	return this;
};

BX.JCCalendar.prototype.SaveValue = function()
{
	if (this.popup_month)
		this.popup_month.close();
	if (this.popup_year)
		this.popup_year.close();

	var bSetValue = true;
	if (!!this.params.callback)
	{
		var res = this.params.callback.apply(this, [new Date(this.value.valueOf()+this.value.getTimezoneOffset()*60000)]);
		if (res === false)
			bSetValue = false;
	}

	if (bSetValue)
	{
		var bTime = !!this.params.bTime && BX.hasClass(this.PARTS.TIME, 'bx-calendar-set-time-opened');

		if (this.params.field)
		{
			var format = BX.message(bTime ? 'FORMAT_DATETIME' : 'FORMAT_DATE');

			if(bTime && !this.params.bUseSecond)
			{
				format = format.replace(':SS', '');
			}

			this.params.field.value = BX.calendar.ValueToStringFormat(this.value, format, true);
			BX.fireEvent(this.params.field, 'change');
		}

		this.popup.close();

		if (!!this.params.callback_after)
		{
			this.params.callback_after.apply(this, [new Date(this.value.valueOf()+this.value.getTimezoneOffset()*60000), bTime]);
		}
	}

	return this;
};

BX.JCCalendar.prototype.Close = function()
{
	if (!!this.popup)
		this.popup.close();

	return this;
};

BX.JCSpinner = function(params)
{
	params = params || {};
	this.params = {
		input: params.input || null,

		delta: params.delta || 1,

		timeout_start: params.timeout_start || 1000,
		timeout_cont: params.timeout_cont || 150,

		callback_start: params.callback_start || null,
		callback_change: params.callback_change || null,
		callback_finish: params.callback_finish || null,

		bSaveValue: typeof params.bSaveValue == 'undefined' ? !!params.input : !!params.bSaveValue
	};

	this.mousedown = false;
	this.direction = 1;
};

BX.JCSpinner.prototype.Show = function()
{
	this.node = BX.create('span', {
		events: {
			mousedown: BX.delegateEvent(
				{attr: 'data-dir'},
				BX.delegate(this.Start, this)
			)
		},
		html: '<a href="javascript:void(0)" class="bx-calendar-form-arrow bx-calendar-form-arrow-top" data-dir="1"><i></i></a><a href="javascript:void(0)" class="bx-calendar-form-arrow bx-calendar-form-arrow-bottom" data-dir="-1"><i></i></a>'
	});
	return this.node;
};

BX.JCSpinner.prototype.Start = function()
{
	this.mousedown = true;
	this.direction = BX.proxy_context.getAttribute('data-dir') > 0 ? 1 : -1;
	BX.bind(document, "mouseup", BX.proxy(this.MouseUp, this));
	this.ChangeValue(true);
};

BX.JCSpinner.prototype.ChangeValue = function(bFirst)
{
	if(!this.mousedown)
		return;

	if (this.params.input)
	{
		var v = parseInt(this.params.input.value, 10) + this.params.delta * this.direction;

		if (this.params.bSaveValue)
			this.params.input.value = v;

		if (!!bFirst && this.params.callback_start)
			this.params.callback_start(this.params, v, this.direction);

		if (this.params.callback_change)
			this.params.callback_change(this.params, v, this.direction);

		setTimeout(
			BX.proxy(this.ChangeValue, this),
			!!bFirst ? this.params.timeout_start : this.params.timeout_cont
		);
	}
};

BX.JCSpinner.prototype.MouseUp = function()
{
	this.mousedown = false;
	BX.unbind(document, "mouseup", BX.proxy(this.MouseUp, this));

	if (this.params.callback_finish)
		this.params.callback_finish(this.params, this.params.input.value);
};

/**************** compatibility hacks ***************************/

window.jsCalendar = {
	Show: function(obj, field, fieldFrom, fieldTo, bTime, serverTime, form_name, bHideTimebar)
	{
		return BX.calendar({
			node: obj, field: field, form: form_name, bTime: !!bTime, currentTime: serverTime, bHideTimebar: !!bHideTimebar
		});
	},

	ValueToString: BX.calendar.ValueToString
};


/************ clock popup transferred from timeman **************/

BX.CClockSelector = function(params)
{
	this.params = params;

	this.params.popup_buttons = this.params.popup_buttons || [
		new BX.PopupWindowButton({
			text : BX.message('CAL_BUTTON'),
			className : "popup-window-button-create",
			events : {click : BX.proxy(this.setValue, this)}
		})
	];

	this.isReady = false;

	this.WND = new BX.PopupWindow(
		this.params.popup_id || 'clock_selector_popup',
		this.params.node,
		this.params.popup_config || {
			titleBar: BX.message('CAL_TIME'),
			offsetLeft: -45,
			offsetTop: -135,
			autoHide: true,
			closeIcon: true,
			closeByEsc: true,
			zIndex: this.params.zIndex
		}
	);

	this.SHOW = false;
	BX.addCustomEvent(this.WND, "onPopupClose", BX.delegate(this.onPopupClose, this));

	this.obClocks = {};
	this.CLOCK_ID = this.params.clock_id || 'clock_selector';
};

BX.CClockSelector.prototype.Show = function()
{
	if (!this.isReady)
	{
		//BX.timeman.showWait(this.parent.DIV);

		BX.addCustomEvent('onClockRegister', BX.proxy(this.onClockRegister, this));
		return BX.ajax.get('/bitrix/tools/clock_selector.php', {start_time: this.params.start_time, clock_id: this.CLOCK_ID, sessid: BX.bitrix_sessid()}, BX.delegate(this.Ready, this));
	}

	this.WND.setButtons(this.params.popup_buttons);
	this.WND.show();

	this.SHOW = true;

	if (window['bxClock_' + this.obClocks[this.CLOCK_ID]])
	{
		setTimeout("window['bxClock_" + this.obClocks[this.CLOCK_ID] + "'].CalculateCoordinates()", 40);
	}

	return true;
};

BX.CClockSelector.prototype.onClockRegister = function(obClocks)
{
	if (obClocks[this.CLOCK_ID])
	{
		this.obClocks[this.CLOCK_ID] = obClocks[this.CLOCK_ID];
		BX.removeCustomEvent('onClockRegister', BX.proxy(this.onClockRegister, this));
	}
};

BX.CClockSelector.prototype.Ready = function(data)
{
	this.content = this.CreateContent(data);
	this.WND.setContent(this.content);

	this.isReady = true;
	//BX.timeman.closeWait();

	setTimeout(BX.proxy(this.Show, this), 30);
};

BX.CClockSelector.prototype.CreateContent = function(data)
{
	return BX.create('DIV', {
		events: {click: BX.PreventDefault},
		html:
			'<div class="bx-tm-popup-clock">' + data + '</div>'
	});
};

BX.CClockSelector.prototype.setValue = function(e)
{
	if (this.params.callback)
	{
		var input = BX.findChild(this.content, {tagName: 'INPUT'}, true);
		this.params.callback.apply(this.params.node, [input.value]);
	}

	return BX.PreventDefault(e);
};

BX.CClockSelector.prototype.closeWnd = function(e)
{
	this.WND.close();
	return (e || window.event) ? BX.PreventDefault(e) : true;
};

BX.CClockSelector.prototype.setNode = function(node)
{
	this.WND.setBindElement(node);
};

BX.CClockSelector.prototype.setTime = function(timestamp)
{
	this.params.start_time = timestamp;
	if (window['bxClock_' + this.obClocks[this.CLOCK_ID]])
	{
		window['bxClock_' +  this.obClocks[this.CLOCK_ID]].SetTime(parseInt(timestamp/3600), parseInt((timestamp%3600)/60));
	}
};

BX.CClockSelector.prototype.setCallback = function(cb)
{
	this.params.callback = cb;
};

BX.CClockSelector.prototype.onPopupClose = function()
{
	this.SHOW = false;
};

})();
