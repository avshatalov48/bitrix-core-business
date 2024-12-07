export class EventApi
{
	static async list(params: {
		categoryId: number,
		fromMonth: number,
		fromYear: number,
		toMonth: number,
		toYear: number,
	}): Promise<EventDto[]>
	{
		const { categoryId, fromMonth, fromYear, toMonth, toYear } = params;

		const response = await BX.ajax.runAction('calendar.open-events.Event.list', {
			data: {
				categoryId,
				fromMonth,
				fromYear,
				toMonth,
				toYear,
			},
		});

		return response.data;
	}

	static async getTsRange(categoryId: number): Promise<DateRange>
	{
		const response = await BX.ajax.runAction('calendar.open-events.Event.getTsRange', {
			data: {
				categoryId,
			},
		});

		return {
			from: new Date(parseInt(response.data.from, 10) * 1000),
			to: new Date(parseInt(response.data.to, 10) * 1000),
		};
	}

	static async setAttendeeStatus(eventId: number, attendeeStatus: boolean): Promise
	{
		const response = await BX.ajax.runAction('calendar.open-events.Event.setAttendeeStatus', {
			data: { eventId, attendeeStatus },
		});

		return response.data;
	}

	static async setWatched(eventIds: number[]): Promise
	{
		const response = await BX.ajax.runAction('calendar.open-events.Event.setWatched', {
			data: { eventIds },
		});

		return response.data;
	}
}
