import { Type } from 'main.core';
import { UserId, Timestamp } from 'bizproc.types';

import { TaskStatus } from './task-status';
import { UserStatus } from './user-status';

export { InlineTaskView } from './inline-task-view';

export {
	TaskStatus,
	UserStatus,
};

export type TaskData = {
	id: number,
	name: string,
	description?: string,
	isInline?: boolean,
	status?: number | string,
	controls?: TaskControls,
	url?: string,
	// approveType?: string,
	// modified?: Timestamp,
	// executionTime?: Timestamp,
};

export type TaskUserData = {
	id: UserId,
	status: number,
};

export type TaskButton = {
	targetUserStatus: number,
	text: string,
	name: string,
	value: any,
	onclick: (TaskButton) => void,
};
export type DefaultTaskButton = { default: boolean };

export type TaskControls = {
	buttons?: Array<TaskButton | DefaultTaskButton>,
};

export class Task
{
	#data: TaskData;

	constructor(task: TaskData)
	{
		this.#data = task;
	}

	get id(): number
	{
		return Type.isNumber(this.#data.id) ? this.#data.id : 0;
	}

	get name(): string
	{
		return Type.isString(this.#data.name) ? this.#data.name : '';
	}

	hasDescription(): boolean
	{
		return Type.isString(this.#data.description);
	}

	get description(): string
	{
		return this.hasDescription() ? this.#data.description : '';
	}

	hasUrl(): boolean
	{
		return Type.isStringFilled(this.#data.url);
	}

	get url(): string
	{
		return this.hasUrl() ? this.#data.url : '';
	}

	canShowInPopup(): boolean
	{
		return Type.isBoolean(this.#data.canShowInPopup) ? this.#data.canShowInPopup : false;
	}

	isResponsibleForTask(userId: UserId): boolean
	{
		const responsibleUser = this.users.find((user) => user.id === userId);

		return !Type.isNil(responsibleUser);
	}

	get users(): Array<{ id: number, status: UserStatus }>
	{
		return (
			Type.isArray(this.#data.users)
				? this.#data.users.map((user) => ({
					...user,
					status: new UserStatus(user.status),
				}))
				: []
		);
	}

	hasStatus(): boolean
	{
		return Type.isNumber(this.#data.status) || Type.isStringFilled(this.#data.status);
	}

	getStatus(): TaskStatus
	{
		return new TaskStatus(this.hasStatus() ? this.#data.status : 0);
	}

	get modified(): Timestamp
	{
		return Type.isNumber(this.#data.modified) ? Math.max(this.#data.modified, 0) : 0;
	}

	hasControls(): boolean
	{
		return Type.isPlainObject(this.#data.controls);
	}

	get controls(): TaskControls
	{
		return this.hasControls() ? this.#data.controls : {};
	}

	get buttons(): Array<TaskButton | DefaultTaskButton>
	{
		if (this.hasControls() && Type.isArray(this.controls.buttons))
		{
			return this.controls.buttons;
		}

		return [];
	}

	setControls(controls: TaskControls): Task
	{
		this.#data.controls = controls;

		return this;
	}

	setButtons(buttons: Array<TaskButton | DefaultTaskButton>): Task
	{
		if (!this.hasControls())
		{
			this.#data.controls = {};
		}

		this.#data.controls.buttons = buttons;

		return this;
	}

	isCompleted(): boolean
	{
		return this.hasStatus() ? !this.getStatus().isWaiting() : false;
	}

	isInline(): boolean
	{
		return this.#data.isInline;
	}
}
