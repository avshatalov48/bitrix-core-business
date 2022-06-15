import {Event, Reflection, Type, Uri} from 'main.core';
import {EventEmitter} from 'main.core.events'
import {Slider} from 'catalog.store-use'

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
		this.productId = settings.productId;
		this.reservedDealsSliderLink = settings.reservedDealsSliderLink;

		if (this.totalWrapperId)
		{
			this.totalWrapper = BX(this.totalWrapperId);
			this.refreshTotalWrapper();
		}

		this.subscribeEvents();
		this.bindSliderToReservedQuantityNodes();
	}

	subscribeEvents()
	{
		this.onGridUpdatedHandler = this.onGridUpdated.bind(this);
		EventEmitter.subscribe('Grid::updated', this.onGridUpdatedHandler);
	}

	bindSliderToReservedQuantityNodes()
	{
		const rows = this.grid.getRows().getRows();
		rows.forEach((row) => {
			if (row.isBodyChild() && !row.isTemplate())
			{
				const reservedQuantityNode = row.getNode().querySelector(
					'.main-grid-cell-content-store-amount-reserved-quantity'
				);
				if (Type.isDomNode(reservedQuantityNode))
				{
					Event.bind(
						reservedQuantityNode,
						'click',
						this.openDealsWithReservedProductSlider.bind(this, this.productId, row.getId())
					);
				}
			}
		});
	}

	openDealsWithReservedProductSlider(rowId, storeId = 0)
	{
		if (!this.reservedDealsSliderLink)
		{
			return;
		}

		const sliderLink = new Uri(this.reservedDealsSliderLink);
		sliderLink.setQueryParam('productId', rowId);
		if (storeId > 0)
		{
			sliderLink.setQueryParam('storeId', storeId);
		}
		BX.SidePanel.Instance.open(sliderLink.toString(), {
			allowChangeHistory: false,
			cacheable: false
		});
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
			new Slider().open(this.inventoryManagementLink,
				{
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