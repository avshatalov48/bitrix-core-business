import { ajax, Type } from 'main.core';

export default class Client
{
	static async getRelationData(eventId: number | null): Promise
	{
		if (Type.isNil(eventId))
		{
			return false;
		}

		const action = 'calendar.api.calendarentryajax.getEventEntityRelation';
		const data = { eventId };
		const response = await ajax.runAction(action, { data }).then(
			(ajaxResponse) => {
				return ajaxResponse;
			},
			() => {
				return null;
			},
		);

		return response?.data || false;
	}
}
