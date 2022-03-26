import {Dom, Reflection, Tag, Text, Type} from "main.core";
import {typeof BaseEvent, EventEmitter} from "main.core.events";

class StoreAmountDetails
{
	constructor(settings)
	{
		this.gridId = settings.gridId;
		this.productId = settings.productId;
		this.onFilterApplyHandler = this.onFilterApply.bind(this);
		EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApplyHandler);
	}

	getGridId()
	{
		return this.gridId;
	}

	getProductId()
	{
		return this.productId;
	}

	onFilterApply(event: BaseEvent)
	{
		const [gridId] = event.getCompatData();

		if (gridId !== this.getGridId())
		{
			return;
		}

		BX.ajax.runComponentAction(
			'bitrix:catalog.productcard.store.amount.details',
			'updateTotalData',
			{
				mode: 'class',
				data: {
					productId: this.getProductId(),
				}
			}
		).then(response => {
			const totalData = response?.data?.TOTAL_DATA;
			if (!totalData)
			{
				return;
			}

			const quantityAvailableNode = document.getElementById(this.getGridId() + '_total_quantity_available');
			if (quantityAvailableNode)
			{
				quantityAvailableNode.innerHTML = totalData.QUANTITY_AVAILABLE;
			}

			const quantityReservedNode = document.getElementById(this.getGridId() + '_total_quantity_reserved');
			if (quantityReservedNode)
			{
				quantityReservedNode.innerHTML = totalData.QUANTITY_RESERVED;
			}

			const quantityCommonNode = document.getElementById(this.getGridId() + '_total_quantity_common');
			if (quantityCommonNode)
			{
				quantityCommonNode.innerHTML = totalData.QUANTITY_COMMON;
			}

			const totalPriceNode = document.getElementById(this.getGridId() + '_total_price');
			if (totalPriceNode)
			{
				totalPriceNode.innerHTML = totalData.PRICE;
			}
		});
	}
}

Reflection.namespace('BX.Catalog').StoreAmountDetails = StoreAmountDetails;
