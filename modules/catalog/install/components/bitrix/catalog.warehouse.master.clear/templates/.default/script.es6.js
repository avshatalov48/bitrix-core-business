import {Reflection, Type, Event, Dom, ajax} from 'main.core';

const namespace = Reflection.namespace('BX.Catalog.Master');

class CatalogWarehouseMasterClear
{
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
	inventoryManagementEnabled(data={})
	{
		let analytics = {iME: 'inventoryManagementEnabled' + '_' + data.preset.sort().join('_')};

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
	inventoryManagementDisabled()
	{
		return ajax.runAction(
			'catalog.config.inventoryManagementN',
			{}
		)
	}
	openSlider(url, options)
	{
		if(!Type.isPlainObject(options))
		{
				options = {};
		}
		options = {...{cacheable: false, allowChangeHistory: false, events: {}}, ...options};
		return new Promise((resolve) =>
		{
			if(Type.isString(url) && url.length > 1)
			{
				options.events.onClose = function(event)
				{
					resolve(event.getSlider());
				};
				BX.SidePanel.Instance.open(url, options);
			}
			else
			{
				resolve();
			}
		});
	}
}

namespace.CatalogWarehouseMasterClear = CatalogWarehouseMasterClear