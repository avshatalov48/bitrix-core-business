import {Type} from 'main.core';

export const DateUtil = {
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