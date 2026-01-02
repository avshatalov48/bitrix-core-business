import { BaseError, Type } from 'main.core';

/**
 * @namespace BX.UI.Uploader
 */
export default class EntityError extends BaseError
{
	#entityId: string;

	setEntityId(entityId: string): void
	{
		if (Type.isStringFilled(entityId))
		{
			this.#entityId = entityId;
		}
	}

	getEntityId(): string
	{
		return this.#entityId;
	}
}
