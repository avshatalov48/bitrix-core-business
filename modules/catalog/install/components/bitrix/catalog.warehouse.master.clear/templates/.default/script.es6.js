/* eslint-disable no-param-reassign */
import { Reflection, Type } from 'main.core';

const namespace = Reflection.namespace('BX.Catalog.Master');

class CatalogWarehouseMasterClear
{
	openSlider(url, options): Promise
	{
		if (!Type.isPlainObject(options))
		{
			options = {};
		}

		options = {
			cacheable: false,
			allowChangeHistory: false,
			events: {},
			...options,
		};

		return new Promise((resolve) => {
			if (Type.isString(url) && url.length > 1)
			{
				options.events.onClose = (event) => {
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

namespace.CatalogWarehouseMasterClear = CatalogWarehouseMasterClear;
