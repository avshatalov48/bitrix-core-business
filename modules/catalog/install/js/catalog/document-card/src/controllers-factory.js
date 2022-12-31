import {type BaseEvent, EventEmitter} from 'main.core.events';
import ProductListController from "./product-list/controller";
import DocumentCardController from "./card/controller";

export default class ControllersFactory
{
	constructor(eventName)
	{
		EventEmitter.subscribe(eventName, (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['entityCard'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'document_card')
		{
			return new DocumentCardController(controlId, settings);
		}

		if (type === 'catalog_store_document_product_list')
		{
			return new ProductListController(controlId, settings);
		}

		return null;
	}
}
