import { ajax, Extension, Uri } from 'main.core';
import { sendData } from 'ui.analytics';
import { FeaturePromotersRegistry } from 'ui.info-helper';

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

	static openChatWithHead(data): void
	{
		const opener = top.BX.Messenger.Public.openChat();
		const analyticData = {
			tool: 'InfoHelper',
			c_section: document.location.href,
			event: 'create_chatforrequest',
		};

		if (data.toolId)
		{
			ajax.runAction('intranet.tools.tool.createHeadChat', {
				data: {
					toolId: data.toolId,
				},
			}).then((response) => {
				opener.then(() => {
					top.BX.Messenger.Public.openChat(`chat${response.data.chatId}`);
				});
				analyticData.type = data.toolId;
				analyticData.category = 'tool_off';
				sendData(analyticData);
			});
		}

		if (data.featureCode)
		{
			ajax.runAction('bitrix24.license.upgraderequest.createHeadChat', {
				data: {
					code: data.featureCode,
				},
			}).then((response) => {
				opener.then(() => {
					top.BX.Messenger.Public.openChat(`chat${response.data.chatId}`);
				});
				analyticData.type = data.featureCode;
				analyticData.category = 'limit';
				sendData(analyticData);
			});
		}
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
		Actions.openSlider({ url: Extension.getSettings('ui.info-helper').settingsUrl + '?page=tools' });
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

				if (slider && result.error)
				{
					BX.UI.InfoHelper.sliderProviderForOldFormat?.getFrame().contentWindow.postMessage(
						{
							action: 'onActivateDemoSubscriptionResult',
							result: result,
						},
						'*',
					);
				}

				if (!result.error)
				{
					const settings = Extension.getSettings('ui.info-helper');

					if (settings.region === 'ru' && settings.licenseNeverPayed)
					{
						Actions.openInformer({ code: 'limit_market_trial_active' });
					}
					else if (settings.marketUrl)
					{
						Actions.openSlider({ url: settings.marketUrl });
					}
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
