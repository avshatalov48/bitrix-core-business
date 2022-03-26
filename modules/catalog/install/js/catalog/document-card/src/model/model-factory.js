import {type BaseEvent, EventEmitter} from 'main.core.events'
import DocumentModel from "./model";

export default class ModelFactory
{
	constructor()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorModelFactory:onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['store_document'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'store_document')
		{
			return new DocumentModel(controlId, settings);
		}

		return null;
	}
}
