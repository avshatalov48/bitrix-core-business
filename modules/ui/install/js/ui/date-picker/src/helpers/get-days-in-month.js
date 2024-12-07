export function getDaysInMonth(date: Date): number
{
	const month = date.getUTCMonth();
	const year = date.getUTCFullYear();
	const daysInMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
	if (month !== 1 || ((year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0)))
	{
		return daysInMonth[month];
	}

	return 28;
}
