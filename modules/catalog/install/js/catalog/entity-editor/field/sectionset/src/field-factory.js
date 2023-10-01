import { EventEmitter } from 'main.core.events';
import type { BaseEvent } from 'main.core.events';
import { SectionSetField } from './sectionset';

export class SectionSetFieldFactory
{
	constructor(entityEditorControlFactory = 'BX.UI.EntityEditorControlFactory')
	{
		EventEmitter.subscribe(`${entityEditorControlFactory}:onInitialize`, (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods.sectionSet = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'sectionSet')
		{
			return SectionSetField.create(controlId, settings);
		}

		return null;
	}
}
