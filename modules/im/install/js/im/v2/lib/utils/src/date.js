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
	},

	isSameDay(firstDate: Date, secondDate: Date): boolean
	{
		return firstDate.getFullYear() === secondDate.getFullYear()
			&& firstDate.getMonth() === secondDate.getMonth()
			&& firstDate.getDate() === secondDate.getDate();
	},

	isSameHour(firstDate: Date, secondDate: Date): boolean
	{
		return firstDate.getFullYear() === secondDate.getFullYear()
			&& firstDate.getMonth() === secondDate.getMonth()
			&& firstDate.getDate() === secondDate.getDate()
			&& firstDate.getHours() === secondDate.getHours();
	},

	formatMediaDurationTime(seconds: number): string
	{
		const padZero = (num: number): string => {
			return num.toString().padStart(2, '0');
		};

		const hours = Math.floor(seconds / 3600);
		const minutes = Math.floor((seconds % 3600) / 60);
		const remainingSeconds = Math.floor(seconds % 60);

		const formattedHours = hours > 0 ? `${hours}:` : '';
		const formattedMinutes = hours > 0 ? padZero(minutes) : minutes.toString();
		const formattedSeconds = padZero(remainingSeconds);

		return `${formattedHours}${formattedMinutes}:${formattedSeconds}`;
	},
};
