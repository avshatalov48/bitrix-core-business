import {Utils} from 'im.v2.lib.utils';

export class UuidManager
{
	static instance: UuidManager;

	#actionIds: Set = new Set();

	static getInstance(): UuidManager
	{
		if (!this.instance)
		{
			this.instance = new this();
		}

		return this.instance;
	}

	getActionUuid(): string
	{
		const uuid = Utils.text.getUuidV4();
		this.#actionIds.add(uuid);

		return uuid;
	}

	hasActionUuid(uuid: string): boolean
	{
		return this.#actionIds.has(uuid);
	}

	removeActionUuid(uuid: string)
	{
		this.#actionIds.delete(uuid);
	}
}