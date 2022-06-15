import {Reflection, Type, Event, Dom, ajax} from 'main.core';
import {Slider} from 'catalog.store-use'

const namespace = Reflection.namespace('BX.Catalog.Store.Document');

class ControlPanel
{
	openSlider(url, options = {})
	{
		let currentSlider = BX.SidePanel.Instance.getTopSlider();

		options = Type.isPlainObject(options) ? options:{};

		let params = {
			events: options.hasOwnProperty("events") ? options.events : {},
			data: options.hasOwnProperty("data") ? options.data : {},
		};

		params.events.onClose = function(event)
		{
			let slider = event.getSlider();
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
		}

		return new Slider().open(url, params)
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

if (window === window.top && BX.SidePanel.Instance)
{
	BX.SidePanel.Instance.bindAnchors({rules: [
			{
				condition: [
					'/crm/configs/catalog/',
				],
				options: {
					width: 1000,
					allowChangeHistory: false,
					cacheable: false,
				}
			},
		]});
}

namespace.ControlPanel = ControlPanel