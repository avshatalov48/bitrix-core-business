function OnYandexMapSettingsEdit_search(arParams)
{
	if (null != window.jsYandexCEOpener_search)
	{
		try {window.jsYandexCEOpener_search.Close();}catch (e) {}
		window.jsYandexCEOpener_search = null;
	}

	window.jsYandexCEOpener_search = new JCEditorOpener_search(arParams);
}

function JCEditorOpener_search(arParams)
{
	this.arParams = arParams;
	this.jsOptions = this.arParams.data.split('||');

	var obButton = this.arParams.oCont.appendChild(BX.create('BUTTON', {
		html: this.jsOptions[1]
	}));
	obButton.onclick = BX.delegate(this.btnClick, this);
	this.saveData = BX.delegate(this.__saveData, this);
}

JCEditorOpener_search.prototype.Close = function(e)
{
	if (false !== e)
		BX.util.PreventDefault(e);

	if (null != window.jsPopup_yandex_map)
	{
		window.jsPopup_yandex_map.Close();
	}
};

JCEditorOpener_search.prototype.btnClick = function ()
{
	this.arElements = this.arParams.getElements();

	if (!this.arElements)
		return false;

	if (null == window.jsPopup_yandex_map)
	{
		var strUrl = '/bitrix/components/bitrix/map.yandex.search/settings/settings.php'
				+ '?lang=' + this.jsOptions[0]
				+ '&INIT_MAP_TYPE=' + BX.util.urlencode(this.arElements.INIT_MAP_TYPE.value),
			strUrlPost = 'MAP_DATA=' + BX.util.urlencode(this.arParams.oInput.value);

		window.jsPopup_yandex_map = new BX.CDialog({
			'content_url': strUrl,
			'content_post': strUrlPost,
			'width':800, 'height':500,
			'resizable':false
		});
	}

	window.jsPopup_yandex_map.Show();
	window.jsPopup_yandex_map.PARAMS.content_url = '';

	return false;
};

JCEditorOpener_search.prototype.__saveData = function(strData, view)
{
	this.arParams.oInput.value = strData;
	if (null != this.arParams.oInput.onchange)
		this.arParams.oInput.onchange();

	if (view)
	{
		this.arElements.INIT_MAP_TYPE.value = view;
		if (null != this.arElements.INIT_MAP_TYPE.onchange)
			this.arElements.INIT_MAP_TYPE.onchange();
	}

	this.Close(false);
};