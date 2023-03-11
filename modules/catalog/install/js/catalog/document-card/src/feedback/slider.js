import {Type, Uri} from "main.core";

export default class Slider
{
	static openFeedbackForm()
	{
		const url = new Uri('/bitrix/components/bitrix/catalog.feedback/slider.php');
		url.setQueryParams({feedback_type: 'feedback'});

		return Slider.open(url.toString(), {width: 735});
	}

	static openIntegrationRequestForm(event, params={})
	{
		if (event && Type.isFunction(event.preventDefault))
		{
			event.preventDefault();
		}

		if(!Type.isPlainObject(params))
		{
			params = {};
		}

		let url = (new Uri('/bitrix/components/bitrix/catalog.feedback/slider.php'));

		url.setQueryParams({feedback_type: 'integration_request'});
		url.setQueryParams(params);

		return Slider.open(url.toString(), {width: 735});
	}

	static open(url, options)
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