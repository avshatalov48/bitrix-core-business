import { Type } from 'main.core';
import { TaskUserData, TaskStatus } from 'bizproc.task';
import type { Timestamp } from 'bizproc.types';

export type TimelineTaskData = {
	canView: boolean,
	status: number,
	id?: number,
	name?: string,
	modified?: Timestamp,
	users?: TaskUserData[],
	executionTime: ?Timestamp,
	approveType?: string,
	url: string,
};

export class TimelineTask
{
	#data: TimelineTaskData = {};

	constructor(data: TimelineTaskData)
	{
		if (Type.isPlainObject(data))
		{
			this.#data = data;
		}
	}

	canView(): boolean
	{
		return Type.isBoolean(this.#data.canView) ? this.#data.canView : false;
	}

	get status(): TaskStatus
	{
		return new TaskStatus(this.#data.status);
	}

	get id(): number
	{
		return Type.isInteger(this.#data.id) ? this.#data.id : 0;
	}

	get name(): string
	{
		return Type.isString(this.#data.name) ? this.#data.name : '';
	}

	get modified(): Timestamp
	{
		return Type.isInteger(this.#data.modified) ? Math.max(this.#data.modified, 0) : 0;
	}

	get users(): TaskUserData[]
	{
		return Type.isArray(this.#data.users) ? this.#data.users : [];
	}

	get executionTime(): ?number
	{
		return Type.isInteger(this.#data.executionTime) ? Math.max(this.#data.executionTime, 0) : null;
	}

	get approveType(): string
	{
		return Type.isString(this.#data.approveType) ? this.#data.approveType : '';
	}

	get url(): ?null
	{
		return Type.isStringFilled(this.#data.url) ? this.#data.url : null;
	}
}
