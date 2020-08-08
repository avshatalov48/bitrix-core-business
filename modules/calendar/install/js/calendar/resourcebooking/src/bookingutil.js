import {Type, Loc, Tag, Runtime, CoreDate} from "./resourcebooking";
import {Translit} from "./translit";

export class BookingUtil {
	static simpleTimeList = null;
	static DAY_LENGTH = 86400000;
	static TIME_FORMAT = null;
	static TIME_FORMAT_SHORT = null;
	static DATE_FORMAT = null;
	static DATETIME_FORMAT = null;

	static getDateFormat()
	{
		if (Type.isNull(BookingUtil.DATE_FORMAT))
		{
			BookingUtil.DATE_FORMAT = CoreDate.convertBitrixFormat(Loc.getMessage("FORMAT_DATE"));
		}
		return BookingUtil.DATE_FORMAT;
	}

	static getDateTimeFormat()
	{
		if (Type.isNull(BookingUtil.DATETIME_FORMAT))
		{
			BookingUtil.DATETIME_FORMAT = CoreDate.convertBitrixFormat(Loc.getMessage("FORMAT_DATETIME"));
		}
		return BookingUtil.DATETIME_FORMAT;
	}

	static getTimeFormat()
	{
		if (Type.isNull(BookingUtil.TIME_FORMAT))
		{
			let DATETIME_FORMAT = BookingUtil.getDateTimeFormat();
			let DATE_FORMAT = BookingUtil.getDateFormat();

			if ((DATETIME_FORMAT.substr(0, DATE_FORMAT.length) === DATE_FORMAT))
			{
				BookingUtil.TIME_FORMAT = DATETIME_FORMAT.substr(DATE_FORMAT.length).trim();
			}
			else
			{
				BookingUtil.TIME_FORMAT = CoreDate.convertBitrixFormat(CoreDate.isAmPmMode() ? 'H:MI:SS T' : 'HH:MI:SS');
			}

			BookingUtil.TIME_FORMAT_SHORT = BookingUtil.TIME_FORMAT.replace(':s', '');
		}

		return BookingUtil.TIME_FORMAT;
	}

	static getTimeFormatShort()
	{
		if (Type.isNull(BookingUtil.TIME_FORMAT_SHORT))
		{
			BookingUtil.TIME_FORMAT_SHORT = BookingUtil.getTimeFormat().replace(':s', '');
		}
		return BookingUtil.TIME_FORMAT_SHORT;
	}

	static formatDate(format, timestamp, now, utc)
	{
		if (format === null)
		{
			format = BookingUtil.getDateFormat();
		}

		if (Type.isDate(timestamp))
		{
			timestamp = timestamp.getTime() / 1000;
		}

		return CoreDate.format(format, timestamp, now, utc);
	}

	static parseDate(str, bUTC, formatDate, formatDatetime)
	{
		return CoreDate.parse(str, bUTC, formatDate, formatDatetime);
	}

	static formatTime(h, m)
	{
		let d = new Date();
		d.setHours(h, m, 0);
		return CoreDate.format(BookingUtil.getTimeFormatShort(), d.getTime() / 1000);
	};

	static translit(str)
	{
		return Type.isString(str) ? Translit.run(str).replace(/[^a-z0-9_]/ig, "_") : str;
	}

	static getLoader(size, className)
	{
		return Tag.render`
		<div class="${className || 'calendar-loader'}">
			<svg class="calendar-loader-circular"
				style="width:${parseInt(size)}px; height:${parseInt(size)}px;"
				viewBox="25 25 50 50">
					<circle class="calendar-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
					<circle class="calendar-loader-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
			</svg>
		</div>
`;
	};

	static fireCustomEvent(eventObject, eventName, eventParams, secureParams)
	{
		if (window.BX && Type.isFunction(BX.onCustomEvent))
		{
			return BX.onCustomEvent(eventObject, eventName, eventParams, secureParams);
		}
	}

	static bindCustomEvent(eventObject, eventName, eventHandler)
	{
		if (window.BX && Type.isFunction(BX.addCustomEvent))
		{
			return BX.addCustomEvent(eventObject, eventName, eventHandler);
		}
	}

	static unbindCustomEvent(eventObject, eventName, eventHandler)
	{
		if (window.BX && Type.isFunction(BX.removeCustomEvent))
		{
			return BX.removeCustomEvent(eventObject, eventName, eventHandler);
		}
	}

	static isAmPmMode()
	{
		return CoreDate.isAmPmMode();
	}

	static mergeEx()
	{
		let arg = Array.prototype.slice.call(arguments);
		if(arg.length < 2)
		{
			return {};
		}

		let result = arg.shift();
		for (let i = 0; i < arg.length; i++)
		{
			for (let k in arg[i])
			{
				if (typeof arg[i] === "undefined" || arg[i] == null || !arg[i].hasOwnProperty(k))
				{
					continue;
				}

				if (Type.isPlainObject(arg[i][k]) && Type.isPlainObject(result[k]))
				{
					BookingUtil.mergeEx(result[k], arg[i][k]);
				}
				else
				{
					result[k] = Type.isPlainObject(arg[i][k]) ? Runtime.clone(arg[i][k]) : arg[i][k];
				}
			}
		}

		return result;
	};

	static getDurationList(fullDay)
	{
		let
			values = [5, 10, 15, 20, 25, 30, 40, 45, 50, 60, 90,
				120, 180, 240, 300, 360,
				1440, 1440 * 2, 1440 * 3, 1440 * 4, 1440 * 5, 1440 * 6, 1440 * 7, 1440 * 10],
			val, i, res = [];

		for (i = 0; i < values.length; i++)
		{
			val = values[i];
			if (fullDay && val % 1440 !== 0)
			{
				continue;
			}

			res.push({
				value: val,
				label: BookingUtil.getDurationLabel(val)
			});
		}
		return res;
	}

	static getDurationLabel(val)
	{
		let label;
		if (val % 1440 === 0) // Days
		{
			label = Loc.getMessage('USER_TYPE_DURATION_X_DAY').replace('#NUM#', val / 1440);
		}
		else if (val % 60 === 0 && val !== 60) // Hours
		{
			label = Loc.getMessage('USER_TYPE_DURATION_X_HOUR').replace('#NUM#', val / 60);
		}
		// Minutes
		else
		{
			label = Loc.getMessage('USER_TYPE_DURATION_X_MIN').replace('#NUM#', val);
		}
		return label
	}

	static parseDuration(value)
	{
		let
			stringValue = value,
			numValue = parseInt(value),
			parsed = false,
			dayRegexp = new RegExp('(\\d)\\s*(' + Loc.getMessage('USER_TYPE_DURATION_REGEXP_DAY') + ').*', 'ig'),
			hourRegexp = new RegExp('(\\d)\\s*(' + Loc.getMessage('USER_TYPE_DURATION_REGEXP_HOUR') + ').*', 'ig');

		value = value.replace(dayRegexp, function(str, num){parsed = true;return num;});
		// It's days
		if (parsed)
		{
			value = numValue * 1440;
		}
		else
		{
			value = stringValue.replace(hourRegexp, function(str, num){parsed = true;return num;});
			// It's hours
			if (parsed)
			{
				value = numValue * 60;
			}
			else // Minutes
			{
				value = numValue;
			}
		}

		return parseInt(value) || 0;
	}

	static getSimpleTimeList()
	{
		if (Type.isNull(BookingUtil.simpleTimeList))
		{
			let i, res = [];
			for (i = 0; i < 24; i++)
			{
				res.push({value: i * 60, label: this.formatTime(i, 0)});
				res.push({value: i * 60 + 30, label: this.formatTime(i, 30)});
			}
			BookingUtil.simpleTimeList = res;
		}
		return BookingUtil.simpleTimeList;
	}

	static adaptTimeValue(timeValue)
	{
		timeValue = parseInt(timeValue.h * 60) + parseInt(timeValue.m);
		let
			timeList = BookingUtil.getSimpleTimeList(),
			diff = 24 * 60,
			ind = false,
			i;

		for (i = 0; i < timeList.length; i++)
		{
			if (Math.abs(timeList[i].value - timeValue) < diff)
			{
				diff = Math.abs(timeList[i].value - timeValue);
				ind = i;
				if (diff <= 15)
				{
					break;
				}
			}
		}

		return timeList[ind || 0];
	}

	static getDayLength()
	{
		return BookingUtil.DAY_LENGTH;
	}

	static showLimitationPopup()
	{
		if (top.BX.getClass("BX.UI.InfoHelper"))
		{
			top.BX.UI.InfoHelper.show('limit_crm_booking');
		}
	};
}