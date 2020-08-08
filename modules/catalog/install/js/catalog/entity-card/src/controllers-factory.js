import IblockSectionController from './iblock-section/controller';
import {type BaseEvent, EventEmitter} from 'main.core.events'
import VariationGridController from './variation-grid/controller';

export default class ControllersFactory
{
	constructor()
	{
		EventEmitter.subscribe('BX.UI.EntityEditorControllerFactory:onInitialize', (event: BaseEvent) => {
			const [, eventArgs] = event.getCompatData();
			eventArgs.methods['entityCard'] = this.factory.bind(this);
		});
	}

	factory(type, controlId, settings)
	{
		if (type === 'iblock_section')
		{
			return new IblockSectionController(controlId, settings);
		}

		if (type === 'variation_grid')
		{
			return new VariationGridController(controlId, settings);
		}

		return null;
	}
}