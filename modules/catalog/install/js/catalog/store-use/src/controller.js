import { Type, ajax } from 'main.core';

export class Controller
{
	inventoryManagementAnalyticsFromLanding(data = {})
	{
		this.sendAnalyticsLabel(data)
			.then(() => {
				this.unRegisterOnProlog();
			})
			.catch(() => {});
	}

	sendAnalyticsLabel(data = {}): Promise
	{
		const analytics = this.makeAnalyticsData(data);

		return ajax.runAction(
			'catalog.analytics.sendAnalyticsLabel',
			{
				analyticsLabel: analytics,
			},
		);
	}

	unRegisterOnProlog(): Promise
	{
		return ajax.runAction('catalog.config.unRegisterOnProlog');
	}

	inventoryManagementEnabled(data = {}): Promise
	{
		const analytics = this.makeAnalyticsData(data);

		return ajax.runAction(
			'catalog.config.inventoryManagementYAndResetQuantity',
			{
				analyticsLabel: analytics,
			},
		);
	}

	inventoryManagementEnableWithResetDocuments(data = {}): Promise
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementYAndResetQuantityWithDocuments',
			{
				analyticsLabel: this.makeAnalyticsData(data),
				data: {
					costPriceCalculationMethod: data.costPriceAccountingMethod,
				},
			},
		)
			.then((response) => {
				top.BX.onCustomEvent('CatalogWarehouseMasterClear:resetDocuments');

				return response;
			})
		;
	}

	inventoryManagementEnableWithoutReset(data = {}): Promise
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementY',
			{
				analyticsLabel: this.makeAnalyticsData(data),
				data: {
					costPriceCalculationMethod: data.costPriceAccountingMethod,
				},
			},
		);
	}

	makeAnalyticsData(data = {}): Object
	{
		const analyticsData = {
			iME: 'inventoryManagementEnabled',
		};

		if (Type.isStringFilled(data.inventoryManagementSource))
		{
			analyticsData.inventoryManagementSource = data.inventoryManagementSource;
		}

		return analyticsData;
	}

	inventoryManagementDisabled(): Promise
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementN',
			{},
		);
	}
}
