import {ajax, Text, Type} from "main.core";
import {ProductModel} from "catalog.product-model";

export class StoreCollection
{
	#map: Map = new Map();

	constructor(model: ProductModel = {})
	{
		this.model = model;
	}

	init(map: {})
	{
		Object.keys(map).forEach((key) => {
			const item = map[key];
			if (item['STORE_ID'] > 0)
			{
				this.#map.set(
					Text.toNumber(item['STORE_ID']),
					{
						AMOUNT: Text.toNumber(item['AMOUNT']),
						QUANTITY_RESERVED: Text.toNumber(item['QUANTITY_RESERVED']),
						STORE_ID: Text.toNumber(item['STORE_ID']),
						STORE_TITLE: Text.encode(item['STORE_TITLE']),
					}
				);
			}
		});
	}

	refresh(): {}
	{
		this.clear();
		if (this.model.getSkuId() > 0)
		{
			ajax.runAction(
				'catalog.storeSelector.getProductStores',
				{
					json: {
						productId: this.model.getSkuId(),
					}
				}
			)
				.then((response) => {
					response.data.forEach((item) => {
						if (!Type.isNil(item['STORE_ID']))
						{
							this.#map.set(
								Text.toNumber(item['STORE_ID']),
								{
									AMOUNT: Text.toNumber(item['AMOUNT']),
									QUANTITY_RESERVED: Text.toNumber(item['QUANTITY_RESERVED']),
									STORE_ID: Text.toNumber(item['STORE_ID']),
									STORE_TITLE: item['STORE_TITLE'],
								}
							);
						}
					});

					this.model.onChangeStoreData();
				});
		}
	}

	getStoreAmount(storeId): any
	{
		return this.#map.get(Text.toNumber(storeId))?.AMOUNT || 0;
	}

	getStoreReserved(storeId): any
	{
		return this.#map.get(Text.toNumber(storeId))?.QUANTITY_RESERVED || 0;
	}

	getStoreAvailableAmount(storeId): any
	{
		return this.getStoreAmount(storeId) - this.getStoreReserved(storeId);
	}

	getMaxFilledStore(): {}
	{
		let result = {
			'STORE_ID': 0,
			'AMOUNT': 0,
			'STORE_TITLE': '',
			'QUANTITY_RESERVED': 0,
		};
		this.#map.forEach((item) => {
			result =
				item.AMOUNT > result.AMOUNT
					? item
					: result
			;
		});

		return result;
	}

	clear(): StoreCollection
	{
		this.#map.clear();

		return this;
	}
}
