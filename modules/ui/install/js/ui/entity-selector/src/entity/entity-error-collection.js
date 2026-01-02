import { Type } from 'main.core';
import EntityError from './entity-error';
import type { EntityErrorOptions } from './entity-error-options';

/**
 * @namespace BX.UI.Uploader
 */
export default class EntityErrorCollection
{
	#errors: EntityError[] = [];

	static create(errorOptions: EntityErrorOptions[]): this
	{
		const errorCollection = new this();
		errorOptions.forEach((errorOption: EntityErrorOptions) => {
			if (!Type.isStringFilled(errorOption.entityId))
			{
				return;
			}

			const error = new EntityError();
			error.setEntityId(errorOption.entityId);

			if (Type.isStringFilled(errorOption.message))
			{
				error.setMessage(errorOption.message);
			}

			if (!Type.isNil(errorOption.code))
			{
				error.setCode(errorOption.code);
			}

			if (Type.isArrayFilled(errorOption.customData))
			{
				error.setCustomData(errorOption.customData);
			}

			errorCollection.add(error);
		});

		return errorCollection;
	}

	getByEntityId(entityId: string): Array
	{
		return this.#errors.filter((error: EntityError) => error.getEntityId() === entityId);
	}

	add(item: EntityError): void
	{
		this.#errors.push(item);
	}

	has(item: EntityError): boolean
	{
		return this.#errors.includes(item);
	}

	clear(): void
	{
		this.#errors = [];
	}

	getIndex(item: EntityError): number
	{
		return this.#errors.indexOf(item);
	}

	getByIndex(index: number): ?EntityError
	{
		if (Type.isNumber(index) && index >= 0)
		{
			const error = this.#errors[index];

			return Type.isUndefined(error) ? null : error;
		}

		return null;
	}

	[Symbol.iterator](): IterableIterator<EntityError>
	{
		return this.#errors[Symbol.iterator]();
	}
}
