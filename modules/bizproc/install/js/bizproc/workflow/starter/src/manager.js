import { EventEmitter } from 'main.core.events';
import type { Starter } from './starter';

export class Manager
{
	#instances: Set<Starter> = new Set();

	put(starter: Starter): Manager
	{
		this.#instances.add(starter);

		return this;
	}

	remove(starter: Starter)
	{
		this.#instances.delete(starter);
	}

	fireEvent(starter: Starter, eventName: string, parameters: {})
	{
		const instances = this.#findSimilar(starter);
		instances.forEach((target) => {
			target.emit(eventName, parameters);
			EventEmitter.emit(target, eventName, parameters, { useGlobalNaming: true }); // compatibility
		});
	}

	#findSimilar(target: Starter): Array<Starter>
	{
		const result = [target];

		this.#instances.forEach((starter) => {
			if (starter !== target && this.#isEqual(target, starter))
			{
				result.push(starter);
			}
		});

		return result;
	}

	#isEqual(target: Starter, starter: Starter): boolean
	{
		if (target.signedDocumentType && starter.signedDocumentType)
		{
			return target.signedDocumentType === starter.signedDocumentType;
		}

		if (target.complexDocumentType)
		{
			return (
				target.complexDocumentType.isEqual(
					starter.complexDocumentType || starter.signedDocumentType,
				)
			);
		}

		return (
			starter.complexDocumentType.isEqual(
				target.complexDocumentType || target.signedDocumentType,
			)
		);
	}
}
