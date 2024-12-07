import { ModeList } from 'catalog.store-enable-wizard';
import { Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

export default class Slider
{
	static URL = '/crm/configs/catalog/';
	static URL_RIGHTS = '/shop/settings/permissions/';

	static open(source = null, options = {}): Promise
	{
		Slider.closePopup();
		let url = Slider.URL;
		if (Type.isStringFilled(source))
		{
			url += `?configCatalogSource=${source}`;
		}

		EventEmitter.subscribe('SidePanel.Slider:onMessage', (event) => {
			const [data] = event.getData();

			if (data.eventId === 'BX.Crm.Config.Catalog:onAfterSaveSettings')
			{
				EventEmitter.emit(window, 'onCatalogSettingsSave');
			}
		});

		if (!options.events)
		{
			options.events = {};
		}

		if (!options.events.onClose)
		{
			options.events.onClose = (event) => {
				if (event.getSlider()?.getData().get('isInventoryManagementChanged'))
				{
					if (event.getSlider().getData().get('inventoryManagementMode') === ModeList.MODE_1C)
					{
						top.document.location = '/crm/';
					}
					else
					{
						document.location.reload();
					}
				}
			};
		}

		return new Promise((resolve) => {
			BX.SidePanel.Instance.open(
				url,
				{
					width: 1000,
					allowChangeHistory: false,
					cacheable: false,
					...options,
				},
			);
		});
	}

	static openRigthsSlider(): Promise
	{
		Slider.closePopup();

		return new Promise((resolve) => {
			BX.SidePanel.Instance.open(
				Slider.URL_RIGHTS,
				{},
			);
		});
	}

	static openSeoSlider(url :string): Promise
	{
		Slider.closePopup();

		return new Promise((resolve) => {
			BX.SidePanel.Instance.open(
				url,
				{
					width: 1000,
					allowChangeHistory: false,
					cacheable: false,
				},
			);
		});
	}

	static closePopup(): void
	{
		BX.PopupWindowManager?.getPopups().forEach((popup): void => {
			popup.close();
		});
	}
}
