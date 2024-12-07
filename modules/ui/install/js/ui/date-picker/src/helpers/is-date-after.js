export function isDateAfter(date: Date, dateToCompare: Date): boolean
{
	return date.getTime() > dateToCompare.getTime();
}
