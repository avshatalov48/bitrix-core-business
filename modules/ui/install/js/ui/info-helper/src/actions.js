import { ajax, Extension, Uri } from 'main.core';

export class Actions
{
	static ClosePage()
	{
		BX.UI.InfoHelper.close();
	}

	static openPage()
	{
		window.location.href = BX.UI.InfoHelper.frameUrl;
	}

	static openPageInNewTab()
	{
		window.open(BX.UI.InfoHelper.frameUrl, '_blank');
	}

	static reloadParent()
	{
		BX.UI.InfoHelper.reloadParent();
	}

	static openSlider(data)
	{
		top.BX.SidePanel.Instance.open(data.url);
	}

	static openPriceTable(): void
	{
		Actions.openSlider({ url: '/settings/license_all.php' });
	}

	static openCheckout(data): void
	{
		if (data.mpSubscribe && Extension.getSettings('ui.info-helper').licenseType)
		{
			const url = Uri.addParam('/settings/order/make.php', {
				product: Extension.getSettings('ui.info-helper').licenseType + '12',
				subscr: 'o',
			});
			Actions.openSlider({ url: url });
		}
		else if (data.tariff)
		{
			const url = Uri.addParam('/settings/order/make.php', {
				product: data.period ? data.tariff + data.period : data.tariff + '12',
				subscr: data.mpSubscribe ? 'o' : null,
			});
			Actions.openSlider({ url: url });
		}
	}

	static openToolsSettings(): void
	{
		Actions.openSlider({ url: '/settings/configs/?page=tools' });
	}

	static openInformer(data)
	{
		top.BX.UI.InfoHelper.__showExternal(
			data.code,
			data.option,
		);
	}

	static activateDemoSubscription(data)
	{
		if (data.licenseAgreed === 'Y')
		{
			const ajaxRestPath = '/bitrix/tools/rest.php';
			const callback = (result) => {
				const slider = BX.SidePanel.Instance.getTopSlider();

				if (slider)
				{
					BX.UI.InfoHelper.sliderProviderForOldFormat?.getFrame().contentWindow.postMessage(
						{
							action: 'onActivateDemoSubscriptionResult',
							result: result,
						},
						'*',
					);
				}
			};

			BX.ajax(
				{
					dataType: 'json',
					method: 'POST',
					url: ajaxRestPath,
					data: {
						action: 'activate_demo',
						sessid: BX.bitrix_sessid(),
					},
					onsuccess: callback,
					onfailure: function(error_type, error)
					{
						callback({ error: error_type + (error ? `: ${error}` : '') });
					},
				},
			);
		}
	}

	static activateDemoLicense(): void
	{
		ajax.runAction('ui.infoHelper.activateDemoLicense').then((response) => {
			const slider = BX.SidePanel.Instance.getTopSlider();

			if (slider)
			{
				BX.UI.InfoHelper.sliderProviderForOldFormat?.getFrame().contentWindow.postMessage(
					{
						action: 'onActivateDemoLicenseResult',
						result: response,
					},
					'*',
				);
			}

			if (response.data.success === 'Y')
			{
				BX.onCustomEvent('BX.UI.InfoHelper:onActivateDemoLicenseSuccess', {
					result: response,
				});
			}
		});
	}

	static openBuySubscriptionPage(): void
	{
		ajax.runAction('ui.infoHelper.getBuySubscriptionUrl').then((response) => {
			if (!!response.data && !!response.data.url)
			{
				if (response.data.action === 'blank')
				{
					window.open(response.data.url, '_blank');
				}
				else if (response.data.action === 'redirect')
				{
					window.location.href = response.data.url;
				}
			}
		});
	}

	static activateTrialFeature(data): void
	{
		ajax.runAction(
			'ui.infoHelper.activateTrialFeature',
			{
				data: {
					featureId: data.featureId,
				},
			},
		).then((response) => {
			const slider = BX.SidePanel.Instance.getTopSlider();
			if (slider)
			{
				BX.UI.InfoHelper.sliderProviderForOldFormat?.getFrame().contentWindow.postMessage(
					{
						action: 'onActivateTrialFeature',
						result: response,
					},
					'*',
				);
			}

			if (response.data.success === 'Y')
			{
				BX.onCustomEvent('BX.UI.InfoHelper:onActivateTrialFeatureSuccess', {
					result: response,
					featureId: data.featureId,
					// featureId: this.featureId
				});
			}
		});
	}
}
