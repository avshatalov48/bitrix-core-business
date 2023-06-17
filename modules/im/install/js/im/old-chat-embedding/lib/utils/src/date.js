import {Loc, Type} from 'main.core';
import 'main.date';

import {DateFormat} from 'im.old-chat-embedding.const';

export const DateUtil = {

	getFormatType(type = DateFormat.default): Object
	{
		let format = [];
		if (type === DateFormat.groupTitle)
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", Loc.getMessage("IM_UTILS_FORMAT_DATE")]
			];
		}
		else if (type === DateFormat.message)
		{
			format = [
				["", Loc.getMessage("IM_UTILS_FORMAT_TIME")]
			];
		}
		else if (type === DateFormat.recentTitle)
		{
			format = [
				["tommorow", "today"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", Loc.getMessage("IM_UTILS_FORMAT_DATE_RECENT")]
			]
		}
		else if (type === DateFormat.recentLinesTitle)
		{
			format = [
				["tommorow", "tommorow"],
				["today", "today"],
				["yesterday", "yesterday"],
				["", Loc.getMessage("IM_UTILS_FORMAT_DATE_RECENT")]
			]
		}
		else if (type === DateFormat.readedTitle)
		{
			format = [
				["tommorow", "tommorow, "+Loc.getMessage("IM_UTILS_FORMAT_TIME")],
				["today", "today, "+Loc.getMessage("IM_UTILS_FORMAT_TIME")],
				["yesterday", "yesterday, "+Loc.getMessage("IM_UTILS_FORMAT_TIME")],
				["", Loc.getMessage("IM_UTILS_FORMAT_READED")]
			];
		}
		else if (type === DateFormat.vacationTitle)
		{
			format = [
				["", Loc.getMessage("IM_UTILS_FORMAT_DATE_SHORT")]
			];
		}
		else
		{
			format = [
				["tommorow", "tommorow, "+Loc.getMessage("IM_UTILS_FORMAT_TIME")],
				["today", "today, "+Loc.getMessage("IM_UTILS_FORMAT_TIME")],
				["yesterday", "yesterday, "+Loc.getMessage("IM_UTILS_FORMAT_TIME")],
				["", Loc.getMessage("IM_UTILS_FORMAT_DATE_TIME")]
			];
		}

		return format;
	},

	getDateFunction(localize = null): function
	{
		if (this.dateFormatFunction)
		{
			return this.dateFormatFunction;
		}

		this.dateFormatFunction = Object.create(BX.Main.Date);
		if (localize)
		{
			// eslint-disable-next-line bitrix-rules/no-pseudo-private
			this.dateFormatFunction._getMessage = (phrase) => localize[phrase];
		}

		return this.dateFormatFunction;
	},

	format(timestamp, format = null, localize = null): function
	{
		if (!format)
		{
			format = this.getFormatType(DateFormat.default, localize);
		}

		return this.getDateFunction(localize).format(format, timestamp);
	},

	cast(date, def = new Date()): Date
	{
		let result = def;

		if (date instanceof Date)
		{
			result = date;
		}
		else if (Type.isString(date))
		{
			result = new Date(date);
		}
		else if (Type.isNumber(date))
		{
			result = new Date(date*1000);
		}

		if (
			result instanceof Date
			&& Number.isNaN(result.getTime())
		)
		{
			result = def;
		}

		return result;
	},

	getTimeToNextMidnight(): number
	{
		const nextMidnight = new Date(new Date().setHours(24, 0, 0)).getTime();
		return nextMidnight - Date.now();
	},

	getStartOfTheDay(): Date
	{
		return new Date((new Date()).setHours(0, 0));
	},

	isToday(date): boolean
	{
		return this.cast(date).toDateString() === (new Date()).toDateString();
	}

};