export function isDateBefore(date: Date, dateToCompare: Date): boolean
{
	return date.getTime() < dateToCompare.getTime();
}
