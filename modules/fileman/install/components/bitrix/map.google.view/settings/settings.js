function OnGoogleMapSettingsEdit(arParams)
{
	if (null != window.jsGoogleCEOpener)
	{
		try {window.jsGoogleCEOpener.Close();}catch (e) {}
		window.jsGoogleCEOpener = null;
	}

	window.jsGoogleCEOpener = new JCEditorOpener(arParams);
}

function JCEditorOpener(arParams)
{
	this.jsOptions = arParams.data.split('||');
	this.arParams = arParams;

	var obButton = document.createElement('INPUT');
    obButton.type = "button";
    obButton.value = this.jsOptions[1];
	this.arParams.oCont.appendChild(obButton);
	
	obButton.onclick = BX.delegate(this.btnClick, this);
	this.saveData = BX.delegate(this.__saveData, this);
}

JCEditorOpener.prototype.Close = function(e)
{
	if (false !== e)
		BX.PreventDefault(e);

	if (null != window.jsPopup_google_map)
	{
		window.jsPopup_google_map.Close();
	}
};

JCEditorOpener.prototype.btnClick = function ()
{
	this.arElements = this.arParams.getElements();
	if (!this.arElements)
		return false;

	var strUrl = '/bitrix/components/bitrix/map.google.view/settings/settings.php'
		+ '?lang=' + this.jsOptions[0]
		+ '&INIT_MAP_TYPE=' + BX.util.urlencode(this.arElements.INIT_MAP_TYPE.value),

	strUrlPost = 'MAP_DATA=' + BX.util.urlencode(this.arParams.oInput.value);

	window.jsPopup_google_map = new BX.CDialog({
		'content_url': strUrl,
		'content_post': strUrlPost,
		'width':800, 'height':500,
		'resizable':false
	});
	
	window.jsPopup_google_map.Show();
	window.jsPopup_google_map.PARAMS.content_url = '';
	return false;
};

JCEditorOpener.prototype.__saveData = function(strData, view)
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