import { Type } from 'main.core';

export class StoreSlider
{
	open(url, params = {}): Promise
	{
		const sliderParams = Type.isPlainObject(params) ? params : {};

		return new Promise((resolve) => {
			const data = sliderParams.data ?? {};
			const events = sliderParams.events ?? {};
			events.onClose = events.onClose ?? ((event) => resolve(event.getSlider()));

			const sliderUrl = BX.util.add_url_param(url, { analyticsLabel: 'inventoryManagementEnabled_openSlider' });

			if (Type.isString(sliderUrl) && sliderUrl.length > 1)
			{
				BX.SidePanel.Instance.open(sliderUrl, {
					cacheable: false,
					allowChangeHistory: false,
					events,
					data,
					width: 1170,
				});
			}
			else
			{
				resolve();
			}
		});
	}
}
