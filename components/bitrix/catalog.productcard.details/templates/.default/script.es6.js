import {Event, Loc, Reflection} from 'main.core';
import {EntityCard} from 'catalog.entity-card';
import {type BaseEvent, EventEmitter} from 'main.core.events';

class ProductCard extends EntityCard
{
	constructor(id, settings = {})
	{
		super(id, settings);
	}

	getEntityType()
	{
		return 'Product';
	}

	onSectionLayout(event: BaseEvent)
	{
		const [, eventData] = event.getCompatData();

		if (eventData.id === 'catalog_parameters')
		{
			eventData.visible = this.isSimpleProduct && this.isCardSettingEnabled('CATALOG_PARAMETERS');
		}
	}

	onGridUpdatedHandler(event: BaseEvent)
	{
		super.onGridUpdatedHandler(event);

		const [grid] = event.getCompatData();
		if ((grid && grid.getId() === this.getVariationGridId()) && (grid.getRows().getCountDisplayed() <= 0))
		{
			document.location.reload();
		}
	}

	onEditorAjaxSubmit(event: BaseEvent)
	{
		super.onEditorAjaxSubmit(event);

		const [, response] = event.getCompatData();

		if (response.data)
		{
			if (response.data.NOTIFY_ABOUT_NEW_VARIATION)
			{
				this.showNotification(Loc.getMessage('CPD_NEW_VARIATION_ADDED'));
			}
		}
	}
}

Reflection.namespace('BX.Catalog').ProductCard = ProductCard;