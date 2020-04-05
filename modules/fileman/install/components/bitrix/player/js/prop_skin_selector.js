function ComponentPropsSkinSelector(arParams)
{
	if (!window.oBXSkinSelectors)
		window.oBXSkinSelectors = [];

	var oSS;
	for(var i = 0, l = window.oBXSkinSelectors.length; i < l; i++)
	{
		oSS = window.oBXSkinSelectors[i];
		if (!oSS)
			continue;

		if (oSS.pWnd.parentNode == arParams.oCont)
		{
			oSS.Suicide();
			oSS = null;
		}
		else
		{
			if (oSS.Popup)
				oSS.ClosePopup();
		}
	}
	var oBXSkinSelector = new BXSkinSelector(arParams);
	arParams.oCont.insertBefore(oBXSkinSelector.pWnd, arParams.oCont.lastChild);
	window.oBXSkinSelectors.push(oBXSkinSelector);
}

function BXSkinSelector(arParams)
{
	jsUtils.loadCSSFile('/bitrix/components/bitrix/player/js/skin_selector.css');
	var _this = this;
	this.arElements = arParams.getElements();
	this.pInput = arParams.oInput;
	this.fChange = arParams.fChange || false;
	var jsParams = {};
	try{jsParams = eval(arParams.data);}catch(e){}

	this.advancedProps = jsParams[2] || [];
	this.Mess = jsParams[1];
	this.arSkins = jsParams[0] || [];

	this.arSkins = [
	{
		name: 'Default Skin',
		filename : ''
	}].concat(this.arSkins);

	this.pWnd = jsUtils.CreateElement("DIV", {className: 'bx-skin-select-div'});
	this.pWnd.onclick = function(){_this.ShowPopup();};
	setTimeout(function(){_this.OnChange(false, false)}, 10);
}

BXSkinSelector.prototype = {
OnChange: function(Skin, bFChange)
{
	var
		val = this.pInput.value,
		i, l = this.arSkins.length;

	if (!Skin)
	{
		for (i = 0; i < l; i++)
		{
			if (this.arSkins[i].filename == val)
			{
				Skin = this.arSkins[i];
				break;
			}
		}
	}

	if (!Skin) // Skin  not found, set default
	{
		this.pInput.value = '';
		return this.OnChange();
	}

	while (this.pWnd.firstChild)
		this.pWnd.removeChild(this.pWnd.firstChild);

	if (bFChange !== false && this.fChange && typeof this.fChange == 'function')
		this.fChange();

	this.pWnd.appendChild(document.createTextNode(Skin.name));
},

ShowPopup: function()
{
	var _this = this;
	if (this.bPopupShowed)
		return this.ClosePopup();

	if (!this.Popup)
		this.CreatePopup();

	this.Popup.style.display = 'block';
	this.bPopupShowed = true;

	this.Popup.style.zIndex = 4000;
	var pos = BX.pos(this.pWnd);

	this.pCont = BX('bx_popup_content');
	if (!this.pCont)
		this.pCont = jsUtils.FindParentObject(this.pInput, "DIV", "c2dialog_propdiv"); // Editor component Edit Properties Dialog

	if (!this.pCont)
		this.pCont = jsUtils.FindParentObject(this.pInput, "DIV", "bxtaskbarprops"); // Editor properties taskbar

	if (this.pCont)
		jsUtils.addEvent(this.pCont, "scroll", window.BX_PLAYER_SKIN_PARAM_CLOSE);
	jsFloatDiv.Show(this.Popup, pos.left, pos.top + 20, 5 , false, false);

	var pDiv = this.arDivs[this.pInput.value || '-'];
	pDiv.appendChild(this.pSelectMask);

	// Deny closing
	if (window.jsPopup) // From public
		window.jsPopup.DenyClose(true);

	if (window.GLOBAL_pMainObj) // From editor
	{
		for (var el in GLOBAL_pMainObj)
		{
			if (typeof GLOBAL_pMainObj[el] == 'object' && GLOBAL_pMainObj[el].pC2PropsDialog)
				GLOBAL_pMainObj[el].pC2PropsDialog.bDenyClose = true;
		}
	}

	// Add events
	jsUtils.addEvent(document, "keypress", window.BX_PLAYER_SKIN_PARAM_CLOSE);
	setTimeout(function(){jsUtils.addEvent(document, "click", window.BX_PLAYER_SKIN_PARAM_CLOSE);}, 10);
},

ClosePopup: function()
{
	this.Popup.style.display = 'none';
	this.bPopupShowed = false;
	jsFloatDiv.Close(this.Popup);
	jsUtils.removeEvent(document, "keypress", window.BX_PLAYER_SKIN_PARAM_CLOSE);
	jsUtils.removeEvent(document, "click", window.BX_PLAYER_SKIN_PARAM_CLOSE);
	if (this.pCont)
		jsUtils.removeEvent(this.pCont, "scroll", window.BX_PLAYER_SKIN_PARAM_CLOSE);

	if (window.jsPopup)
		window.jsPopup.DenyClose(false);

	if (window.GLOBAL_pMainObj) // From editor
	{
		for (var el in GLOBAL_pMainObj)
		{
			if (typeof GLOBAL_pMainObj[el] == 'object' && GLOBAL_pMainObj[el].pC2PropsDialog)
				GLOBAL_pMainObj[el].pC2PropsDialog.bDenyClose = false;
		}
	}
},

CreatePopup: function()
{
	var
		_this = this,
		i, pDiv, pImg,
		//imgPath = this.arElements["SKIN_PATH"].value,
		l = this.arSkins.length;

	this.arDivs = {};

	this.Popup = document.body.appendChild(BX.create("DIV", {props: {className: "bx-skin-popup"}}));
	var PopupCont = this.Popup.appendChild(BX.create("DIV", {props: {className: "bx-skin-popup-inner"}}));
	this.pSelectMask = BX.create("DIV", {props: {className: "bx-skin-sel-mask"}}); // For mark selected skin

	for (i = 0; i < l; i++)
	{
		pDiv = PopupCont.appendChild(BX.create("DIV", {props: {id: 'bx_par_skin_' + i, className: 'bx-preview-pic', title: this.arSkins[i].name}}));
		pTitle = pDiv.appendChild(BX.create("DIV", {props: {className: 'bx-skin-prev-title'}, text: this.arSkins[i].name}));

		if (this.arSkins[i].filename == '')
		{
			if (this.advancedProps.defaultImage)
			{
				pDiv.appendChild(jsUtils.CreateElement("IMG", {src: this.advancedProps.defaultImage}));
			}
			else
			{
				pDiv.appendChild(jsUtils.CreateElement("IMG", {src: '/bitrix/components/bitrix/player/images/default_skin.png'}));
			}
		}
		else if (this.arSkins[i].preview) // preview exists
		{
			pDiv.appendChild(jsUtils.CreateElement("IMG", {src: this.arSkins[i].the_path + '/' + this.arSkins[i].preview}));
		}
		else
		{
			pTitle.className += ' bx-no-preview';
			pDiv.appendChild(jsUtils.CreateElement("DIV", {className: 'bx-skin-no-preview'})).appendChild(document.createTextNode(' - ' + this.Mess.NoPreview + ' - '));
		}

		pDiv.onmouseover = function(){this.className = 'bx-preview-pic bx-preview-pic-over';};
		pDiv.onmouseout = function(){this.className = 'bx-preview-pic';};

		pDiv.onclick = function()
		{
			var cur = _this.arSkins[this.id.substr('bx_par_skin_'.length)];
			_this.pInput.value = cur.filename;
			_this.OnChange(cur);
			_this.ClosePopup();
		};

		this.arDivs[this.arSkins[i].filename || '-'] = pDiv;
	}

	window.BX_PLAYER_SKIN_PARAM_CLOSE = function(e){_this.ClosePopup();};
},

Suicide: function()
{
	this.ClosePopup();

	if (this.Popup.parentNode)
		this.Popup.parentNode.removeChild(this.Popup);
	if (this.pWnd.parentNode)
		this.pWnd.parentNode.removeChild(this.pWnd);
}
};

