import {Type, ajax} from 'main.core';

export class Controller
{
	inventoryManagementAnalyticsFromLanding(data={})
	{
		this.sendAnalyticsLabel(data)
			.then(() => {
				this.unRegisterOnProlog()
			})
	}

	sendAnalyticsLabel(data={})
	{
		let analytics = this.#makeAnalyticsData(data);

		return ajax.runAction(
			'catalog.analytics.sendAnalyticsLabel',
			{
				analyticsLabel: analytics,
				data: {}
			}
		)
	}

	unRegisterOnProlog()
	{
		return ajax.runAction('catalog.config.unRegisterOnProlog');
	}

	inventoryManagementEnabled(data={})
	{
		let analytics = this.#makeAnalyticsData(data);

		return ajax.runAction(
			'catalog.config.inventoryManagementYAndResetQuantity',
			{
				analyticsLabel: analytics,
				data:{
					preset: data.preset
				}
			}
		)
	}

	inventoryManagementEnableWithResetDocuments(data={})
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementYAndResetQuantityWithDocuments',
			{
				analyticsLabel: this.#makeAnalyticsData(data),
				data:{
					preset: data.preset
				}
			}
		)
			.then((response) => {
				top.BX.onCustomEvent('CatalogWarehouseMasterClear:resetDocuments');

				return response;
			})
			;
	}

	inventoryManagementEnableWithoutReset(data={})
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementY',
			{
				analyticsLabel: this.#makeAnalyticsData(data),
				data:{
					preset: data.preset
				}
			}
		)
	}

	#makeAnalyticsData(data={})
	{
		const analyticsData = {iME: 'inventoryManagementEnabled' + '_' + data.preset?.sort().join('_')};
		if (Type.isStringFilled(data.inventoryManagementSource))
		{
			analyticsData.inventoryManagementSource = data.inventoryManagementSource;
		}

		return analyticsData;
	}

	inventoryManagementDisabled()
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementN',
			{}
		)
	}

	inventoryManagementInstallPreset(data={})
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementInstallPreset',
			{
				data:{
					preset: data.preset
				}
			}
		)
	}
}