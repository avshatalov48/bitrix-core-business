BX.namespace('BX.BitrixCloud');
BX.BitrixCloud.MobileMonitorPush = function(app, params)
{
	this.app = app;
	this.domain = params.domain || '';
	this.ajaxUrl = params.ajaxUrl || '';

	this.makeFastButton = function(Id, buttonUrl)
	{
		const el = BX(Id);
		if (el)
		{
			new FastButton(el, BX.delegate(() => {
				if (this.app.enableInVersion(8))
				{
					this.app.showModalDialog({ url: buttonUrl });
				}
				else
				{
					this.app.loadPageBlank({ url: buttonUrl });
				}
			}, this), false);
		}
	};

	this.close = function()
	{
		if (this.app.enableInVersion(8))
		{
			this.app.closeModalDialog();
		}
		else
		{
			this.app.closeController({ drop: true });
		}
	};

	this.getOptions = function()
	{
		const form = BX('mapp_edit_form_id');
		const result = [];

		if (form && form.elements && form.elements.SUBSCRIBE && form.elements.SUBSCRIBE.value)
		{
			result.SUBSCRIBE = form.elements.SUBSCRIBE.value;
		}

		return result;
	};

	this.save = function()
	{
		const postData = {
			options: this.getOptions(),
			domain: this.domain,
			sessid: BX.bitrix_sessid(),
			action: 'save_options',
		};

		app.showPopupLoader({ text: `${BX.Loc.getMessage('BCMMP_JS_SAVING')}...` });

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
					this.close();
				}
				else if (result.ERROR)
				{
					this.app.alert({ text: `ERROR: ${result.ERROR}` });
				}
				else
				{
					this.app.alert({ text: `${BX.Loc.getMessage('BCMMP_JS_SAVE_ERROR')} !result.` });
				}
			}, this),
			onfailure: BX.delegate(() => {
				app.alert({ text: `${BX.Loc.getMessage('BCMMP_JS_SAVE_ERROR')} onfailure.` });
			}, this),
		});
	};
};
