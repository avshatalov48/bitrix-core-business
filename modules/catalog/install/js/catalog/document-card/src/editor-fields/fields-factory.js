import {type BaseEvent, EventEmitter} from 'main.core.events'
import ProductRowSummary from "./row-summary";
import Contractor from "./contractor";

export default class FieldsFactory
{
	constructor()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorControlFactory:onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['documentCard'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'product_row_summary')
		{
			return new ProductRowSummary(controlId, settings);
		}
		if (type === 'contractor')
		{
			return new Contractor(controlId, settings);
		}

		return null;
	}
}