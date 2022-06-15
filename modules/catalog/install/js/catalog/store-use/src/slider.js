import {Type} from "main.core";

export class Slider
{
	open(url, params={})
	{
		params = Type.isPlainObject(params) ? params:{};

		return new Promise((resolve) =>
		{
			let data = params.hasOwnProperty("data") ? params.data : {};
			let events = params.hasOwnProperty("events") ? params.events : {};
			events.onClose = events.hasOwnProperty("onClose") ? events.onClose : (event) => resolve(event.getSlider());

			url = BX.util.add_url_param(url, {"analyticsLabel": "inventoryManagementEnabled_openSlider"});

			if(Type.isString(url) && url.length > 1)
			{
				BX.SidePanel.Instance.open(url, {
					cacheable: false,
					allowChangeHistory: false,
					events,
					data,
					width: 1130
				});
			}
			else
			{
				resolve();
			}
		});
	}
}
