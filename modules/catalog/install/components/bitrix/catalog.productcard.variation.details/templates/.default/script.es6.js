import {Reflection} from 'main.core';
import {EntityCard} from 'catalog.entity-card';
import {type BaseEvent, EventEmitter} from 'main.core.events';

class VariationCard extends EntityCard
{
	constructor(id, settings = {})
	{
		super(id, settings);

		EventEmitter.subscribe('BX.UI.EntityEditorSection:onLayout', this.onSectionLayout.bind(this));
	}

	getEntityType()
	{
		return 'Variation';
	}

	onSectionLayout(event: BaseEvent)
	{
		const [, eventData] = event.getCompatData();

		/*if (eventData.id === 'catalog_parameters')
		{
			eventData.visible = this.isCardSettingEnabled('CATALOG_PARAMETERS');
		}*/
	}
}

Reflection.namespace('BX.Catalog').VariationCard = VariationCard;