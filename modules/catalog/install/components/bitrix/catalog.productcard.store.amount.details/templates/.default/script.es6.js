import { Reflection, Loc} from "main.core";
import {typeof BaseEvent, EventEmitter} from "main.core.events";
import { AccessDeniedInput } from "catalog.access-denied-input";

class StoreAmountDetails
{
	constructor(settings)
	{
		this.gridId = settings.gridId;
		this.productId = settings.productId;
		this.allowPurchasingPrice = settings.allowPurchasingPrice;

		this.onFilterApplyHandler = this.onFilterApply.bind(this);
		EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApplyHandler);

		if (!this.allowPurchasingPrice)
		{
			this.#initPurchasingPrice();
			EventEmitter.subscribe('Grid::updated', this.#initPurchasingPrice.bind(this));
		}
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

	#getGrid()
	{
		if (!Reflection.getClass('BX.Main.gridManager.getInstanceById'))
		{
			throw Error(`Cannot find grid`)
		}

		return BX.Main.gridManager.getInstanceById(this.getGridId());
	}

	#initPurchasingPrice(): void
	{
		this.#getGrid().getContainer().querySelectorAll('purchasing-price').forEach((element) => {
			const input = new AccessDeniedInput({
				hint: Loc.getMessage('CATALOG_PRODUCTCARD_STORE_AMOUNT_DETAILS_PURCHASING_PRICE_HINT'),
				isReadOnly: true,
			});
			input.renderTo(element);
		});
	}
}

Reflection.namespace('BX.Catalog').StoreAmountDetails = StoreAmountDetails;
