/* eslint-disable no-param-reassign */

import { Reflection, Type } from 'main.core';
import { StoreSlider } from 'catalog.store-use';

const namespace = Reflection.namespace('BX.Catalog.Store.Document');

class ControlPanel
{
	openSlider(url, options = {}): void
	{
		const currentSlider = BX.SidePanel.Instance.getTopSlider();

		options = Type.isPlainObject(options) ? options : {};

		const params = {
			events: options.events ?? {},
			data: options.data ?? {},
		};

		params.events.onClose = function(event) {
			const slider = event.getSlider();
			if (!slider)
			{
				return;
			}

			if (slider.getData().get('isInventoryManagementEnabled'))
			{
				if (currentSlider)
				{
					currentSlider.data.set('preventMasterSlider', true);
				}
				document.location.reload();
			}
		};

		return new StoreSlider().open(url, params);
	}

	storeMasterOpenSlider(url, options = {})
	{
		this.openSlider(url, options);
	}

	reloadGrid()
	{
		document.location.reload();
	}
}

namespace.ControlPanel = ControlPanel;
