import { Loc, Type, Text } from 'main.core';

export class TaskStatus
{
	#status: number | string;

	constructor(rawStatus: number | string)
	{
		this.#status = rawStatus;
	}

	isWaiting(): boolean
	{
		return this.#status === 0;
	}

	isYes(): boolean
	{
		return this.#status === 1;
	}

	isNo(): boolean
	{
		return this.#status === 2;
	}

	isOk(): boolean
	{
		return this.#status === 3;
	}

	isCancel(): boolean
	{
		return this.#status === 4;
	}

	isCustom(): boolean
	{
		return Type.isStringFilled(this.#status);
	}

	get name(): string
	{
		if (this.isCustom())
		{
			return Text.encode(this.#status);
		}

		if (this.isYes())
		{
			return Loc.getMessage('BIZPROC_TASK_STATUS_YES');
		}

		if (this.isNo() || this.isCancel())
		{
			return Loc.getMessage('BIZPROC_TASK_STATUS_NO');
		}

		return Loc.getMessage('BIZPROC_TASK_STATUS_OK');
	}
}
