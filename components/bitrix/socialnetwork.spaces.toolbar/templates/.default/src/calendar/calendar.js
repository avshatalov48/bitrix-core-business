import { Type } from 'main.core';
import { EntryManager } from 'calendar.entry';

type Params = {
	type: string;
	locationAccess: boolean;
	userId: number;
	ownerId: number;
}

export class Calendar
{
	#type: string;
	#locationAccess: boolean;
	#userId: number;
	#ownerId: number;
	#calendarInstance = null;

	constructor(params: Params)
	{
		if (!this.#isTypeAllowed(params.type))
		{
			throw new Error('BX.Socialnetwork.Spaces.Calendar: calendar type is not allowed');
		}

		this.#type = params.type;
		this.#userId = params.userId;
		this.#ownerId = params.ownerId;
		this.#locationAccess = params.locationAccess;
	}

	#isTypeAllowed(type: string): boolean
	{
		const allowedType = ['user', 'group'];

		return allowedType.includes(type);
	}

	#setCalendarInstance(): void
	{
		if (Type.isUndefined(window.BXEventCalendar))
		{
			throw new TypeError('BX.Socialnetwork.Spaces.CalendarSettings: BXEventCalendar is not allowed');
		}

		const calendarId = Object.keys(window.BXEventCalendar.instances)[0];
		this.#calendarInstance = window.BXEventCalendar.instances[calendarId];
	}

	getCalendarInstance(): BXEventCalendar.instances
	{
		if (!this.#calendarInstance)
		{
			this.#setCalendarInstance();
		}

		return this.#calendarInstance;
	}

	addEvent(): void
	{
		EntryManager.openEditSlider({
			type: this.#type,
			isLocationCalendar: false,
			locationAccess: this.#locationAccess,
			ownerId: this.#ownerId,
			userId: this.#userId,
		});
	}

	addTask(): void
	{
		BX.SidePanel.Instance.open(this.#getUrlForAddTask(this.#type), { loader: 'task-new-loader' });
	}

	#getUrlForAddTask(type: string): string
	{
		let url = '';
		switch (type)
		{
			case 'group':
				url = `/workgroups/group/${this.#ownerId}`;
				break;
			case 'user':
				url = `/company/personal/user/${this.#ownerId}`;
				break;
			default:
				throw new Error('BX.Socialnetwork.Spaces.Calendar: url for add task is empty');
		}

		return (`${url}/tasks/task/edit/0/`);
	}
}
