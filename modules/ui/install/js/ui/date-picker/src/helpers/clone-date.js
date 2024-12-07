export function cloneDate(date: Date): Date
{
	const newDate = new Date(date.getTime());
	if (date.__utc)
	{
		newDate.__utc = true;
	}

	return newDate;
}
