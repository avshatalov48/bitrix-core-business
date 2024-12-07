export class FilterApi
{
	static async query(params: {
		filterId: number,
		fromMonth: number,
		fromYear: number,
		toMonth: number,
		toYear: number,
	}): Promise<EventDto[]>
	{
		const { filterId, fromDate, fromMonth, fromYear, toDate, toMonth, toYear } = params;

		const response = await BX.ajax.runAction('calendar.open-events.Filter.query', {
			data: {
				filterId,
				fromDate,
				fromMonth,
				fromYear,
				toDate,
				toMonth,
				toYear,
			},
		});

		return response.data;
	}

	static async getTsRange(filterId: number): Promise<DateRange>
	{
		const response = await BX.ajax.runAction('calendar.open-events.Filter.getTsRange', {
			data: {
				filterId,
			},
		});

		return {
			from: new Date(parseInt(response.data.from, 10) * 1000),
			to: new Date(parseInt(response.data.to, 10) * 1000),
		};
	}
}
