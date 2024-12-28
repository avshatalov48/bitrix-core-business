import { Extension, Text } from 'main.core';
import { Collab } from './collab';

export class CollabManager
{
	constructor(data, config)
	{
		let dataCollabs = data.collabs || [];
		if (!dataCollabs.length)
		{
			const extensionConfig = Extension.getSettings('calendar.collabmanager');
			dataCollabs = extensionConfig.collabs || [];
		}
		this.updateCollabs(dataCollabs);
	}

	updateCollabs(collabs): void
	{
		this.collabs = collabs.map(c => new Collab(c));
	}

	getById(id): Collab|null
	{
		return this.collabs.find((c: Collab): boolean => c.getId() === Text.toNumber(id));
	}
}
