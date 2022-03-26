import {Reflection, Type, Event, Dom, ajax} from 'main.core';

const namespace = Reflection.namespace('BX.Catalog.Store.Document');

class ControlPanel
{
	openSlider(url, options)
	{
		let currentSlider = BX.SidePanel.Instance.getTopSlider();
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
					let slider = event.getSlider();
					if (!slider)
					{
						return;
					}

					if (slider.getData().get('isInventoryManagementEnabled') || slider.getData().get('isInventoryManagementDisabled'))
					{
						if (currentSlider)
						{
							currentSlider.data.set('preventMasterSlider', true);
						}
						document.location.reload();
					}
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

	storeMasterOpenSlider(url, options = {})
	{
		this.openSlider(url, options);
	}

	reloadGrid()
	{
		document.location.reload()
	}
}

namespace.ControlPanel = ControlPanel