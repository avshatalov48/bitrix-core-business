import {Dom, Loc, Reflection, Runtime, Tag, Text, Type} from 'main.core';
import {EventEmitter, Event} from 'main.core.events'
class ProductStoreGridManager
{
	grid = null;
	totalWrapper = null;

	constructor(settings = {})
	{
		this.gridId = settings.gridId;
		this.grid = BX.Main.gridManager.getInstanceById(this.gridId);
		this.signedParameters = settings.signedParameters;
		this.totalWrapperId = settings.totalWrapperId || null;
		this.inventoryManagementLink = settings.inventoryManagementLink || null;

		if (this.totalWrapperId)
		{
			this.totalWrapper = BX(this.totalWrapperId);
			this.refreshTotalWrapper();
		}

		this.subscribeEvents();
	}

	subscribeEvents()
	{
		this.onGridUpdatedHandler = this.onGridUpdated.bind(this);
		EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
	}

	onGridUpdated(event: BaseEvent)
	{
		const [grid, eventArgs] = event.getCompatData();

		if (!grid || grid.getId() !== this.getGridId())
		{
			return;
		}

		this.refreshTotalWrapper();
	}

	getGridId()
	{
		return this.gridId;
	}

	reloadGrid()
	{
		if (this.grid)
		{
			this.grid.reload();
		}
	}

	setTotalData(totalData)
	{
		for (var propertyId in totalData)
		{
			if (totalData.hasOwnProperty(propertyId))
			{
				this.setTotalDataBySelector(`#${propertyId}`, totalData[propertyId]);
			}
		}
	}

	setTotalDataBySelector(selector, data)
	{
		if (this.totalWrapper)
		{
			var totalWrapperItem = this.totalWrapper.querySelector(selector);

			if (totalWrapperItem)
			{
				totalWrapperItem.innerHTML = data;
				return true;
			}
		}
		return false;
	}

	hideTotalData()
	{
		BX.hide(this.totalWrapper);
	}

	showTotalData()
	{
		BX.show(this.totalWrapper);
	}

	refreshTotalWrapper()
	{
		if (this.totalWrapper)
		{
			//this.grid.tableFade();
			BX.ajax.runComponentAction(
				'bitrix:catalog.productcard.store.amount',
				'getStoreAmountTotal',
				{
					mode: 'ajax',
					data: {
						signedParameters: this.signedParameters,
					}
				}
			).then(response => {
				const amount = response.data.AMOUNT || '';
				const quantity = response.data.QUANTITY || '';
				const quantityReserved = response.data.QUANTITY_RESERVED || '';
				const quantityCommon = response.data.QUANTITY_COMMON || '';

				if (amount || quantity || quantityCommon || quantityReserved)
				{
					var totalData = {
						'total_amount': amount,
						'total_quantity': quantity,
						'total_quantity_common': quantityCommon,
						'total_quantity_reserved': quantityReserved,
					};

					this.setTotalData(totalData);

					if (BX.isNodeHidden(this.totalWrapper))
					{
						this.showTotalData();
					}
				}
				else
				{
					this.hideTotalData();
				}
				//this.grid.tableUnfade();
			});
		}
	}

	openInventoryManagementSlider()
	{
		if (this.inventoryManagementLink)
		{
			BX.SidePanel.Instance.open(this.inventoryManagementLink,
				{
					cacheable: false,
					data: {
						openGridOnDone: false,
					},
					events: {
						onCloseComplete: function(event) {
							let slider = event.getSlider();
							if (!slider)
							{
								return;
							}

							if (slider.getData().get('isInventoryManagementEnabled'))
							{
								window.top.location.reload();
							}
						}
					}
				}
			);
		}
	}
}

Reflection.namespace('BX.Catalog').ProductStoreGridManager = ProductStoreGridManager;