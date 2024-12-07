import { Type, Loc } from 'main.core';

const ENABLED_SESSION_KEY = 'b24:catalog:inventory-management:enabled';

export class EnableWizardOpener
{
	open(url, params = {}): Promise
	{
		const sliderParams = Type.isPlainObject(params) ? params : {};

		return new Promise((resolve) => {
			const data = sliderParams.data ?? {};
			const events = sliderParams.events ?? {};
			events.onClose = events.onClose ?? ((event) => resolve(event.getSlider()));

			const urlParams = params.urlParams || {};
			const sliderUrl = BX.util.add_url_param(
				url,
				{
					analyticsLabel: 'inventoryManagementEnabled_openSlider',
					...urlParams,
				},
			);

			if (Type.isString(sliderUrl) && sliderUrl.length > 1)
			{
				BX.SidePanel.Instance.open(sliderUrl, {
					cacheable: false,
					allowChangeHistory: false,
					events,
					data,
					width: 930,
				});
			}
			else
			{
				resolve();
			}
		});
	}

	static saveEnabledFlag(): void
	{
		if (!window.sessionStorage)
		{
			return;
		}

		sessionStorage.setItem(ENABLED_SESSION_KEY, 'y');
	}

	static showEnabledNotificationIfNeeded(): void
	{
		if (!window.sessionStorage)
		{
			return;
		}

		if (sessionStorage.getItem(ENABLED_SESSION_KEY) === 'y')
		{
			sessionStorage.removeItem(ENABLED_SESSION_KEY);

			window.top.BX.UI.Notification.Center.notify({
				content: Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ENABLED'),
				autoHide: true,
				autoHideDelay: 4000,
				width: 'auto',
			});
		}
	}
}
