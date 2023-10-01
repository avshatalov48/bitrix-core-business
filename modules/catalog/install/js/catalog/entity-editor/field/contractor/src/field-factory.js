import {EventEmitter} from "main.core.events";
import type {BaseEvent} from "main.core.events";
import {ContractorField} from "./contractorfield";

export class ContractorFieldFactory
{
	constructor(entityEditorControlFactory = 'BX.UI.EntityEditorControlFactory')
	{
		EventEmitter.subscribe(entityEditorControlFactory + ':onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['contractor'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'contractor')
		{
			return ContractorField.create(controlId, settings);
		}

		return null;
	}
}