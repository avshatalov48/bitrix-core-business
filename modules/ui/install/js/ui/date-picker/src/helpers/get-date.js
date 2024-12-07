export type DateComponents = {
	day: number,
	month: number,
	year: number,
	weekDay: number,
	hours: number,
	hours12: number,
	minutes: number,
	seconds: number,
	dayPeriod: 'am' | 'pm',
	fullDay: string,
	fullHours: string,
	fullHours12: string,
	fullMinutes: string,
}

export function getDate(date: Date): DateComponents
{
	const hours = date.getUTCHours();
	const hours12 = hours % 12 === 0 ? 12 : hours % 12;
	const dayPeriod = hours > 11 ? 'pm' : 'am';

	return {
		day: date.getUTCDate(), // 1-31
		month: date.getUTCMonth(), // 0-11
		year: date.getUTCFullYear(),
		weekDay: date.getUTCDay(), // 0-6
		hours, // 0-23
		hours12, // 1-12
		minutes: date.getUTCMinutes(), // 0-59
		seconds: date.getUTCSeconds(), // 0-59
		dayPeriod,
		fullDay: String(date.getUTCDate()).padStart(2, '0'),
		fullHours: String(hours).padStart(2, '0'),
		fullHours12: String(hours12).padStart(2, '0'),
		fullMinutes: String(date.getUTCMinutes()).padStart(2, '0'),
	};
}
