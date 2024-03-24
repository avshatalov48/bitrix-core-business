BX.namespace('BX.BitrixCloud');
BX.BitrixCloud.MobileMonitor = function(app, params)
{
	this.app = app;
	this.ajaxUrl = '';

	if (BX.Type.isObject(params))
	{
		this.ajaxUrl = params.ajaxUrl || '';
	}

	this.deleteSite = function(domain)
	{
		const postData = {
			domain,
			action: 'delete',
			sessid: BX.bitrix_sessid(),
		};

		this.app.showPopupLoader({ text: `${BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_DELETING')}...` });

		BX.ajax({
			timeout: 30,
			method: 'POST',
			dataType: 'json',
			url: this.ajaxUrl,
			data: postData,
			onsuccess: BX.delegate((result) => {
				this.app.hidePopupLoader();
				if (result && !result.ERROR)
				{
					this.app.onCustomEvent('onAfterBCMMSiteDelete', { domain });
					BX.onCustomEvent('onAfterBCMMSiteDelete', [{ domain }]);
				}
				else if (result.ERROR)
				{
					this.app.alert({ text: result.ERROR });
				}
				else
				{
					this.app.alert({ text: BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_DEL_ERROR') });
				}
			}, this),
			onfailure: BX.delegate(() => {
				this.app.alert({ text: BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_DEL_ERROR') });
			}, this),
		});
	};

	this.updateSite = function(domain, data)
	{
		const postData = data;
		postData.domain = domain;
		postData.action = 'update';
		postData.sessid = BX.bitrix_sessid();

		this.app.showPopupLoader({ text: `${BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_SAVING')}...` });

		BX.ajax({
			timeout: 30,
			method: 'POST',
			dataType: 'json',
			url: this.ajaxUrl,
			data: postData,
			onsuccess: (result) => {
				this.app.hidePopupLoader();
				if (result && !result.ERROR)
				{
					this.app.onCustomEvent('onAfterBCMMSiteUpdate', { domain });
					BX.onCustomEvent('onAfterBCMMSiteUpdate', [{ domain }]);
				}
				else if (result.ERROR)
				{
					this.app.alert({ text: result.ERROR });
				}
				else
				{
					this.app.alert({ text: BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_SAVE_ERROR') });
				}
			},
			onfailure: () => {
				this.app.alert({ text: BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_SAVE_ERROR') });
			},
		});
	};

	this.showRefreshing = function()
	{
		this.app.showPopupLoader({ text: `${BX.Loc.getMessage('BCL_MOBILE_MONITORING_SITE_REFRESHING')}...` });
	};
};
