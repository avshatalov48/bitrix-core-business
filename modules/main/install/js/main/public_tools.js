function JCPopup(arParams)
{
	if (!arParams) arParams = {};
	this.suffix = arParams.suffix ? '_' + arParams.suffix.toString().toLowerCase() : '';
	this.div_id = 'bx_popup_form_div' + this.suffix;
	this.overlay_id = 'bx_popup_overlay' + this.suffix;
	this.form_name = 'bx-popup-form' + this.suffix;
	this.class_name = 'bx-popup-form';
	this.url = '';
	this.zIndex = arParams.zIndex || 1020;
	this.arParams = null;
	this.bDenyClose = false;
	this.bDenyEscKey = false;
	this.__arRuntimeResize = {};
	this.bodyOverflow = "";
	this.currentScroll = 0;
	this.div = null;
	this.div_inner = null;
	this.x = 0;
	this.y = 0;
	this.error_dy = null;
	this.arAdditionalResize = [];
	this.onClose = [];

	var _this = this;
	// Event handlers
	window['JCPopup_OnKeyPress' + this.suffix] = function(e){_this.__OnKeyPress(e)};
	window['JCPopup_OverlayResize' + this.suffix] = function(e){_this.OverlayResize(e)};
	window['JCPopup_AjaxAction' + this.suffix] = function(result) {_this.AjaxAction(result);};
	window['JCPopup_AjaxPostAction' + this.suffix] = function(result) {_this.__AjaxPostAction(result);};
	window['JCPopup_stopResize' + this.suffix] = function(e) {_this.stopResize(e);};
	window['JCPopup_startResize' + this.suffix] = function(e) {_this.startResize(e);};
	window['JCPopup_doResize' + this.suffix] = function(e) {_this.doResize(e);};

	jsExtLoader.jsPopup_name = 'jsPopup' + this.suffix;
}

JCPopup.prototype.addOnClose = function(func)
{
	this.onClose[this.onClose.length] = func;
}

JCPopup.prototype.addAdditionalResize = function(id)
{
	this.arAdditionalResize[this.arAdditionalResize.length] = document.getElementById(id);
};

JCPopup.prototype.clearAdditionalResize = function()
{
	this.arAdditionalResize = [];
};

JCPopup.prototype.DenyClose = function(bDeny)
{
	if (bDeny !== false)
		bDeny = true;
	this.bDenyClose = bDeny;

	if (!this.obSaveButton)
	{
		this.obSaveButton = document.getElementById('btn_popup_save' + this.suffix);
		this.obCloseButton = document.getElementById('btn_popup_close'  + this.suffix);
		this.obCancelButton = document.getElementById('btn_popup_cancel' + this.suffix);
	}

	if (this.obSaveButton) this.obSaveButton.disabled = bDeny;
	if (this.obCloseButton) this.obCloseButton.disabled = bDeny;
	if (this.obCancelButton) this.obCancelButton.disabled = bDeny;
};

JCPopup.prototype.AllowClose = function()
{
	this.DenyClose(false);
};

JCPopup.prototype.__OnKeyPress = function(e)
{
	if(this.bDenyEscKey) return;
	if (!e) e = window.event
	if (!e) return;
	if (this.bDenyClose) return;
	if (e.keyCode == 27)
	{
		jsUtils.removeEvent(document, "keypress", window['JCPopup_OnKeyPress' + this.suffix]);
		this.CloseDialog();
	}
};

JCPopup.prototype.AjaxAction = function(result)
{
	CloseWaitWindow();
	if (this.suffix)
		jsPopup.bDenyClose = true;
	var div = document.body.appendChild(document.createElement("DIV"));
	div.id = this.div_id;
	div.className = this.class_name;
	div.style.position = 'absolute';
	div.style.zIndex = this.zIndex;

	div.innerHTML = result;

	if (null != this.arParams.height)
		div.style.height = this.arParams.height + 'px';
	if (null != this.arParams.width)
		div.style.width = this.arParams.width + 'px';

	var windowSize = jsUtils.GetWindowInnerSize();
	var windowScroll = jsUtils.GetWindowScrollPos();

	var left = parseInt(windowScroll.scrollLeft + windowSize.innerWidth / 2 - div.offsetWidth / 2);
	var top = parseInt(windowScroll.scrollTop + windowSize.innerHeight / 2 - div.offsetHeight / 2);

	jsFloatDiv.Show(div, left, top, 5, true);
	jsUtils.addEvent(document, "keypress", window['JCPopup_OnKeyPress' + this.suffix]);

	this.div = div;
	this.div_inner = document.getElementById('bx_popup_content' + this.suffix);
	if(this.div_inner)
	{
		if(this.div.style.width)
			this.div_inner.style.width = parseInt(parseInt(this.div.style.width) - 12) + 'px';
		if(this.div.style.height)
		{
			var aDivId = ['bx_popup_title', 'bx_popup_description_container', 'bx_popup_buttons'];
			var h=0;
			for(var i=0; i < aDivId.length; i++)
			{
				var dv = document.getElementById(aDivId[i] + this.suffix);
				if(dv)
					h += dv.offsetHeight;
			}
			this.div_inner.style.height = parseInt(parseInt(this.div.style.height) - h - 16) + 'px';
		}
	}

	var _this = this;
	setTimeout(function() {_this.AdjustShadow();}, 10);
	if (this.arParams.resize && null != this.div && null != this.div_inner)
		this.createResizer();
	return div;
};

JCPopup.prototype.__AjaxPostAction = function(result)
{
	CloseWaitWindow();
	if (this.suffix)
		jsPopup.bDenyClose = true;
	this.div.innerHTML = result;
	this.div_inner = document.getElementById('bx_popup_content' + this.suffix);
	this.AdjustShadow();
	if (this.arParams.resize && null != this.div && null != this.div_inner)
		this.createResizer();
};

JCPopup.prototype.ShowDialog = function(url, arParams)
{
	if (document.getElementById(this.div_id))
		this.CloseDialog();

	if (!arParams) arParams = {};
	if (null == arParams.resize) arParams.resize = true;
	if (!arParams.min_width) arParams.min_width = 250;
	if (!arParams.min_height) arParams.min_height = 200;

	var pos = url.indexOf('?');
	if (pos == -1)
		url += "?mode=public";
	else
		url = url.substring(0, pos) + "?mode=public&" + url.substring(pos+1);

	this.check_url = pos == -1 ? url : url.substring(0, pos);

	if (arParams.resize && null != this.__arRuntimeResize[this.check_url])
	{
		arParams.width = this.__arRuntimeResize[this.check_url].width;
		arParams.height = this.__arRuntimeResize[this.check_url].height;
		var ipos = url.indexOf('bxpiheight');
		if (ipos == -1)
			url += (pos == -1 ? '?' : '&') + 'bxpiheight=' + this.__arRuntimeResize[this.check_url].iheight;
		else
			url = url.substring(0, ipos) + 'bxpiheight=' + this.__arRuntimeResize[this.check_url].iheight;
	}

	this.url = url;
	this.arParams = arParams;
	this.CreateOverlay();
	jsExtLoader.onajaxfinish = window['JCPopup_AjaxAction' + this.suffix];
	if(arParams['postData'])
		jsExtLoader.startPost(url, arParams['postData']);
	else
		jsExtLoader.start(url);
};

JCPopup.prototype.RemoveOverlay = function()
{
	//var overlay = document.getElementById(this.overlay_id);
	if (this.overlay)
		this.overlay.parentNode.removeChild(this.overlay);
	jsUtils.removeEvent(window, "resize", window['JCPopup_OverlayResize' + this.suffix]);
};

JCPopup.prototype.OverlayResize = function()
{
	//var overlay = document.getElementById(this.overlay_id);
	if (!this.overlay)
		return;
	var windowSize = jsUtils.GetWindowScrollSize();
	this.overlay.style.width = windowSize.scrollWidth + "px";
};

JCPopup.prototype.CreateOverlay = function()
{
	var opacity = new COpacity();
	if (!opacity.GetOpacityProperty())
		return;
	//Create overlay
	this.overlay = document.body.appendChild(document.createElement("DIV"));
	this.overlay.className = "bx-popup-overlay";
	this.overlay.id = this.overlay_id;
	this.overlay.style.zIndex = this.zIndex - 5;

	var windowSize = jsUtils.GetWindowScrollSize();

	this.overlay.style.width = windowSize.scrollWidth + "px";
	this.overlay.style.height = windowSize.scrollHeight + "px";

	jsUtils.addEvent(window, "resize", window['JCPopup_OverlayResize' + this.suffix]);
};

JCPopup.prototype.CloseDialog = function()
{
	jsUtils.onCustomEvent('OnBeforeCloseDialog', this.suffix);

	for(var i=0; i<this.onClose.length; i++)
		this.onClose[i]();

	if (this.bDenyClose)
		return false;
	if (this.suffix)
		jsPopup.bDenyClose = false;
	jsUtils.removeEvent(document, "keypress", window['JCPopup_OnKeyPress' + this.suffix]);
	var div = document.getElementById(this.div_id);
	if (!div)
		return;
	jsFloatDiv.Close(div);
	div.parentNode.removeChild(div);
	this.clearAdditionalResize();
	this.RemoveOverlay();

	return true;
};

JCPopup.prototype.GetParameters = function(form_name)
{
	if (null == form_name)
		var form = document.forms[this.form_name];
	else
		var form = document.forms[form_name];

	if(!form)
		return "";

	var i, s = "";
	var n = form.elements.length;

	var delim = '';
	for(i=0; i<n; i++)
	{
		if (s != '') delim = '&';
		var el = form.elements[i];
		if (el.disabled)
			continue;

		switch(el.type.toLowerCase())
		{
			case 'text':
			case 'textarea':
			case 'password':
			case 'hidden':
				if (null == form_name && el.name.substr(el.name.length-4) == '_alt' && form.elements[el.name.substr(0, el.name.length-4)])
					break;
				s += delim + el.name + '=' + encodeURIComponent(el.value);
				break;
			case 'radio':
				if(el.checked)
					s += delim + el.name + '=' + encodeURIComponent(el.value);
				break;
			case 'checkbox':
				s += delim + el.name + '=' + encodeURIComponent(el.checked ? 'Y':'N');
				break;
			case 'select-one':
				var val = "";
				if (null == form_name && form.elements[el.name + '_alt'] && el.selectedIndex == 0)
					val = form.elements[el.name+'_alt'].value;
				else
					val = el.value;
				s += delim + el.name + '=' + encodeURIComponent(val);
				break;
			case 'select-multiple':
				var j, bAdded = false;
				var l = el.options.length;
				for (j=0; j<l; j++)
				{
					if (el.options[j].selected)
					{
						s += delim + el.name + '=' + encodeURIComponent(el.options[j].value);
						bAdded = true;
					}
				}
				if (!bAdded)
					s += delim + el.name + '=';
				break;
			default:
				break;
		}
	}

	if (null != this.arParams && this.arParams.resize && this.div_inner)
	{
		var inner_width = parseInt(this.div_inner.style.width);
		var inner_height = parseInt(this.div_inner.style.height);

		if (inner_width > 0)
			s += '&bxpiwidth=' + inner_width;
		if (inner_height > 0)
			s += '&bxpiheight=' + inner_height;
	}

	return s;
};

JCPopup.prototype.PostParameters = function(params)
{
	var _this = this;
	jsExtLoader.onajaxfinish = window['JCPopup_AjaxPostAction' + this.suffix];
	ShowWaitWindow();
	var url = this.url;
	if (null != params)
	{
		index = url.indexOf('?')
		if (index == -1)
			url += '?' + params;
		else
			url = url.substring(0, index) + '?' + params + "&" + url.substring(index+1);
	}

	jsExtLoader.startPost(url, this.GetParameters());
};

JCPopup.prototype.AdjustShadow = function()
{
	if (this.div)
		jsFloatDiv.AdjustShadow(this.div);
};

JCPopup.prototype.HideShadow = function()
{
	if (this.div)
		jsFloatDiv.HideShadow(this.div);
};

JCPopup.prototype.UnhideShadow = function()
{
	if (this.div)
		jsFloatDiv.UnhideShadow(this.div);
};

JCPopup.prototype.DragPanel = function(event, td)
{
	var div = jsUtils.FindParentObject(td, 'div');
	div.style.left = div.offsetLeft+'px';
	div.style.top = div.offsetTop+'px';
	jsFloatDiv.StartDrag(event, div);
};

// ************* resizers ************* //
JCPopup.prototype.createResizer = function()
{
	this.diff_x = null;
	this.diff_y = null;
	this.arPos = jsUtils.GetRealPos(this.div);
	var zIndex = parseInt(jsUtils.GetStyleValue(this.div, jsUtils.IsIE() ? 'zIndex' : 'z-index')) + 1;
	this.obResizer = document.createElement('DIV');
	this.obResizer.className = 'bxresizer';
	this.obResizer.style.position = 'absolute';
	this.obResizer.style.zIndex = zIndex;
	this.obResizer.onmousedown = window['JCPopup_startResize' + this.suffix];
	//this.obResizer.onmousedown = this.startResize;
	this.div.appendChild(this.obResizer);
};

JCPopup.prototype.startResize = function (e)
{
	if(!e) e = window.event;

	this.wndSize = jsUtils.GetWindowScrollPos();
	this.wndSize.innerWidth = jsUtils.GetWindowInnerSize().innerWidth;

	this.x = e.clientX + this.wndSize.scrollLeft;
	this.y = e.clientY + this.wndSize.scrollTop;
	this.obDescr = document.getElementById('bx_popup_description_container' + this.suffix);
	if (jsUtils.IsIE())
	{
		this.arPos = this.div.getBoundingClientRect();
		this.arPos =
		{
			left: this.arPos.left + this.wndSize.scrollLeft,
			top: this.arPos.top + this.wndSize.scrollTop,
			right: this.arPos.right + this.wndSize.scrollLeft,
			bottom: this.arPos.bottom + this.wndSize.scrollTop
		}
		this.arPosInner = this.div_inner.getBoundingClientRect();
		this.arPosInner = {
			left: this.arPosInner.left + this.wndSize.scrollLeft,
			top: this.arPosInner.top + this.wndSize.scrollTop,
			right: this.arPosInner.right + this.wndSize.scrollLeft,
			bottom: this.arPosInner.bottom + this.wndSize.scrollTop
		}
	}
	else
	{
		this.arPos = jsUtils.GetRealPos(this.div);
		this.arPosInner = jsUtils.GetRealPos(this.div_inner);
	}

	document.onmouseup = window['JCPopup_stopResize' + this.suffix];
	jsUtils.addEvent(document, "mousemove", window['JCPopup_doResize' + this.suffix]);

	if(document.body.setCapture)
		document.body.setCapture();

	var b = document.body;
	b.ondrag = jsUtils.False;
	b.onselectstart = jsUtils.False;
	b.style.MozUserSelect = this.div.style.MozUserSelect = 'none';
	b.style.cursor = this.obResizer.style.cursor;

	this.HideShadow();
};

JCPopup.prototype.doResize = function(e)
{
	if(!e) e = window.event;
	var x = e.clientX + this.wndSize.scrollLeft;
	var y = e.clientY + this.wndSize.scrollTop;

	if(this.x == x && this.y == y || x > this.wndSize.innerWidth + this.wndSize.scrollLeft - 10)
		return;

	this.Resize(x, y);
	this.x = x;
	this.y = y;
};

JCPopup.prototype.Resize = function(x, y)
{
	if (null == this.diff_x)
	{
		this.diff_x = this.div.offsetWidth - this.div_inner.offsetWidth;
		this.diff_y = this.div.offsetHeight - this.div_inner.offsetHeight;

		if (this.arAdditionalResize.length > 0)
		{
			for (var i = 0, cnt = this.arAdditionalResize.length; i < cnt; i++)
			{
				if (null != this.arAdditionalResize[i])
				{
					var borderX = jsUtils.IsOpera() ? 0 :
						parseInt(jsUtils.GetStyleValue(
							this.arAdditionalResize[i], jsUtils.IsIE() ? 'borderLeftWidth' : 'border-left-width'
						)) +
						parseInt(jsUtils.GetStyleValue(
							this.arAdditionalResize[i], jsUtils.IsIE() ? 'borderRightWidth' : 'border-right-width'
						));

					var borderY = jsUtils.IsOpera() || jsUtils.IsIE() ? 0 :
						parseInt(jsUtils.GetStyleValue(this.arAdditionalResize[i], 'border-top-width')) +
						parseInt(jsUtils.GetStyleValue(this.arAdditionalResize[i], 'border-bottom-width'));

					this.arAdditionalResize[i].diff_x = this.div.offsetWidth - this.arAdditionalResize[i].offsetWidth + borderX;
					this.arAdditionalResize[i].diff_y = this.div.offsetHeight - this.arAdditionalResize[i].offsetHeight + borderY;
				}
			}
		}
	}
	var new_width = x - this.arPos.left;
	var new_height = y - this.arPos.top;
	var dx = new_width - this.div.offsetWidth;
	//var dy = y - this.y;

	if (null != this.obDescr)
		var descrHeight = this.obDescr.offsetHeight;

	var bResizeX = false;
	if (new_width > this.arParams.min_width)
	{
		bResizeX = true;
		this.div.style.width = new_width + 'px';
		this.div_inner.style.width = (new_width - this.diff_x) + 'px';
	}

	if (null != this.obDescr)
		var dy = this.obDescr.offsetHeight - descrHeight;
	else
		var dy = 0;

	this.diff_y += dy;
	var bResizeY = false;
	if (new_height > this.arParams.min_height)
	{
		bResizeY = true;
		this.div_inner.style.height = (new_height - this.diff_y) + 'px';
		this.div.style.height = new_height + 'px';
	}

	if (this.arAdditionalResize.length > 0)
	{
		for (var i = 0, cnt = this.arAdditionalResize.length; i < cnt; i++)
		{
			if (null != this.arAdditionalResize[i])
			{
				if (bResizeY) this.arAdditionalResize[i].style.height = (new_height - this.arAdditionalResize[i].diff_y) + 'px';
				if (bResizeX) this.arAdditionalResize[i].style.width = (new_width - this.arAdditionalResize[i].diff_x) + 'px';
			}
		}
	}
	if (jsUtils.IsIE())
		this.AdjustShadow();
};

JCPopup.prototype.stopResize = function ()
{
	if(document.body.releaseCapture)
		document.body.releaseCapture();

	jsUtils.removeEvent(document, "mousemove", window['JCPopup_doResize' + this.suffix]);

	document.onmouseup = null;

	var b = document.body;
	b.ondrag = null;
	b.onselectstart = null;
	b.style.MozUserSelect = this.div.style.MozUserSelect = '';
	b.style.cursor = '';

	this.UnhideShadow();
	this.AdjustShadow();
	this.SavePosition();
};

JCPopup.prototype.SavePosition = function()
{
	var arPos = {
		width: parseInt(this.div.style.width),
		height: parseInt(this.div.style.height),
		iheight: parseInt(this.div_inner.style.height)
	};

	if (null != this.error_dy)
		arPos.iheight += this.error_dy;

	jsUserOptions.SaveOption('jsPopup' + this.suffix, 'size_' + this.check_url, 'width', arPos.width);
	jsUserOptions.SaveOption('jsPopup' + this.suffix, 'size_' + this.check_url, 'height', arPos.height);
	jsUserOptions.SaveOption('jsPopup' + this.suffix, 'size_' + this.check_url, 'iheight', arPos.iheight);

	for (var i = 0, cnt = this.arAdditionalResize.length; i < cnt; i++)
	{
		if (null != this.arAdditionalResize[i] && null != this.arAdditionalResize[i].BXResizeCacheID)
		{
			jsUserOptions.SaveOption('jsPopup' + this.suffix, 'size_' + this.check_url, this.arAdditionalResize[i].BXResizeCacheID + '_height', parseInt(this.arAdditionalResize[i].style.height));
			jsUserOptions.SaveOption('jsPopup' + this.suffix, 'size_' + this.check_url, this.arAdditionalResize[i].BXResizeCacheID + '_width', parseInt(this.arAdditionalResize[i].style.width));
		}
	}
	this.__arRuntimeResize[this.check_url] = arPos;
};

JCPopup.prototype.IncludePrepare = function()
{
	var obFrame = window.frames.editor;
	if (null == obFrame)
		return false;
	var obSrcForm = obFrame.document.forms.inner_form;
	var obDestForm = document.forms[this.form_name];
	if (null == obSrcForm || null == obDestForm)
		return false;
	obDestForm.include_data.value = obSrcForm.filesrc_pub.value;
	return true;
};

JCPopup.prototype.ShowError = function(error_text)
{
	CloseWaitWindow();
	this.AllowClose();

	this.obDescr = document.getElementById('bx_popup_description_container' + this.suffix);
	if (null != this.obDescr)
	{
		var descrHeight = this.obDescr.offsetHeight;
		var obError = document.getElementById('bx_popup_description_error' + this.suffix);
		if (!obError)
		{
			obError = document.createElement('P');
			obError.id = 'bx_popup_description_error' + this.suffix;
			this.obDescr.firstChild.appendChild(obError);
		}
		obError.innerHTML = '<font class="errortext">' + error_text + '</font>';
		if (this.obDescr.offsetHeight != descrHeight)
		{
			this.error_dy = this.obDescr.offsetHeight - descrHeight;
			if (this.div_inner)
				this.div_inner.style.height = (parseInt(jsUtils.GetStyleValue(this.div_inner, 'height')) - this.error_dy) + 'px';
		}
	}
	else
		alert(error_text);
};

function JCComponentUtils()
{
}

JCComponentUtils.prototype.ClearCache = function(params)
{
	CHttpRequest.Action = function(result){window.location = window.location.href;};
	ShowWaitWindow();
	CHttpRequest.Send('/bitrix/admin/clear_component_cache.php?' + params);
};

JCComponentUtils.prototype.EnableComponent = function(params)
{
	CHttpRequest.Action = function(result){window.location = window.location.href;};
	ShowWaitWindow();
	CHttpRequest.Send('/bitrix/admin/enable_component.php?' + params);
};

function COpacity(element)
{
	this.element = element;
	this.opacityProperty = this.GetOpacityProperty();

	this.startOpacity = null;
	this.finishOpacity = null;
	this.delay = 30;

	this.currentOpacity = null;
	this.fadingTimeoutID = null;
}


COpacity.prototype.SetElementOpacity = function(opacity)
{
	if (!this.opacityProperty)
		return false;

	if (this.opacityProperty == "filter")
	{
		opacity = opacity * 100;
		var alphaFilter = this.element.filters['DXImageTransform.Microsoft.alpha'] || this.element.filters.alpha;
		if (alphaFilter)
			alphaFilter.opacity = opacity;
		else
			this.element.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity="+opacity+")";
	}
	else
		this.element.style[this.opacityProperty] = opacity;

	return true;
}

COpacity.prototype.GetOpacityProperty = function()
{
	var m;
	if (typeof document.body.style.opacity == 'string')
		return 'opacity';
	else if (typeof document.body.style.MozOpacity == 'string')
		return 'MozOpacity';
	else if (typeof document.body.style.KhtmlOpacity == 'string')
		return 'KhtmlOpacity';
	else if (document.body.filters && (m = navigator.appVersion.match(/MSIE ([\d.]+)/)) && m[1] >=5.5)
		return 'filter';

	return false;
}

COpacity.prototype.Fading = function(startOpacity, finishOpacity, callback)
{
	if (!this.opacityProperty)
		return;

	this.startOpacity = startOpacity;
	this.finishOpacity = finishOpacity;
	this.currentOpacity = this.startOpacity;

	if (this.fadingTimeoutID)
		clearInterval(this.fadingTimeoutID);

	var _this = this;
	this.fadingTimeoutID = setInterval(function () {_this.Run(callback)}, this.delay);
}

COpacity.prototype.Run = function(callback)
{
	this.currentOpacity = Math.round((this.currentOpacity + 0.1*(this.finishOpacity - this.startOpacity > 0 ? 1: -1) )*10) / 10;
	this.SetElementOpacity(this.currentOpacity);

	if (this.currentOpacity == this.startOpacity || this.currentOpacity == this.finishOpacity)
	{
		clearInterval(this.fadingTimeoutID);
		if (typeof(callback) == "function")
			callback(this);
	}
}

COpacity.prototype.Undo = function()
{
}

// this object can be used to load any pages with huge scripts structure via AJAX
var jsExtLoader = {
	obContainer: null,
	obContainerInner: null,
	jsPopup_name: 'jsPopup',
	url: '',

	httpRequest: null,
	httpRequest2: null, // for Opera bug fix

	obTemporary: null,

	onajaxfinish: null,

	obFrame: null,

	start: function(url)
	{
		this.url = url;

		this.obContainer = null;

		ShowWaitWindow();

		this.httpRequest = this._CreateHttpObject();
		this.httpRequest.onreadystatechange = jsExtLoader.stepOne;

		this.httpRequest.open("GET", this.url, true);
		this.httpRequest.send("");
	},

	startPost: function(url, data)
	{
		this.url = url;
		this.obContainer = null;

		ShowWaitWindow();

		this.httpRequest = this._CreateHttpObject();
		this.httpRequest.onreadystatechange = jsExtLoader.stepOne;

		this.httpRequest.open("POST", this.url, true);
		this.httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		this.httpRequest.send(data);
	},

	post: function(form_name)
	{
		var obForm = document.forms[form_name];
		if (null == obForm)
			return;

		if (null == this.obFrame)
		{
			if (jsUtils.IsIE())
				this.obFrame = document.createElement('<iframe src="javascript:void(0)" name="frame_' + form_name + '">');
			else
			{
				this.obFrame = document.createElement('IFRAME');
				this.obFrame.name = 'frame_' + form_name;
				this.obFrame.src = 'javascript:void(0)';
			}

			this.obFrame.style.display = 'none';

			document.body.appendChild(this.obFrame);
		}

		obForm.target = this.obFrame.name;

		if (obForm.action.length <= 0)
			obForm.action = this.url;

		window[jsExtLoader.jsPopup_name].DenyClose();
		ShowWaitWindow();

		obForm.save.click();

		if (false === obForm.BXReturnValue)
		{
			window[jsExtLoader.jsPopup_name].AllowClose();
			CloseWaitWindow();
		}

		obForm.BXReturnValue = true;
	},

	urlencode: function(s)
	{
		return escape(s).replace(new RegExp('\\+','g'), '%2B');
	},

	__prepareOnload: function()
	{
		this.obTemporary = window.onload;
		window.onload = null;
	},

	__runOnload: function()
	{
		if (window.onload) window.onload();
		window.onload = this.obTemporary;
		this.obTemporary = null;
	},

	stepOne: function()
	{
		if (jsExtLoader.httpRequest.readyState == 4)
		{
			var content = jsExtLoader.httpRequest.responseText;
			var arCode = [];
			var matchScript;

			var regexp = new RegExp('<script([^>]*)>', 'i');
			var regexp1 = new RegExp('src=["\']([^"\']+)["\']', 'i');

			while ((matchScript = content.match(regexp)) !== null)
			{
				var end = content.search('<\/script>', 'i');
				if (end == -1)
					break;

				var bRunFirst = matchScript[1].indexOf('bxrunfirst') != '-1';

				var matchSrc;
				if ((matchSrc = matchScript[1].match(regexp1)) !== null)
					arCode[arCode.length] = {"bRunFirst": bRunFirst, "isInternal": false, "JS": matchSrc[1]};
				else
				{
					var start = matchScript.index + matchScript[0].length;
					var js = content.substr(start, end-start);

					if (false && arCode.length > 0 && arCode[arCode.length - 1].isInternal && arCode[arCode.length - 1].bRunFirst == bRunFirst)
						arCode[arCode.length - 1].JS += "\r\n\r\n" + js;
					else
						arCode[arCode.length] = {"bRunFirst": bRunFirst, "isInternal": true, "JS": js};
				}

				content = content.substr(0, matchScript.index) + content.substr(end+9);
			}

			jsExtLoader.__prepareOnload();
			jsExtLoader.processResult(content, arCode);
			CloseWaitWindow();
			jsExtLoader.__runOnload();
		}
	},

	EvalGlobal: function(script)
	{
		if (window.execScript)
			window.execScript(script, 'javascript');
		else if (jsUtils.IsSafari())
			window.setTimeout(script, 0);
		else
			window.eval(script);
	},

	arLoadedScripts: [],

	__isScriptLoaded: function (script_src)
	{
		for (var i=0; i<jsExtLoader.arLoadedScripts.length; i++)
			if (jsExtLoader.arLoadedScripts[i] == script_src) return true;
		return false;
	},

	// evaluate external script
	EvalExternal: function(script_src)
	{
		if (/\/bitrix\/js\/main\/public_tools.js$/i.test(script_src)) return; // sorry guys, i cannot execute myself :-)
		if (jsExtLoader.__isScriptLoaded(script_src)) return;

		jsExtLoader.arLoadedScripts.push(script_src);

		if (script_src.substring(0, 8) != '/bitrix/')
			script_src = '/bitrix/admin/' + script_src;

		// fix Opera bug with combining syncronous and asynchronuos requests using one XHR object.
		if (jsUtils.IsOpera())
		{
			if (null == this.httpRequest2)
				this.httpRequest2 = this._CreateHttpObject();

			var httpRequest = this.httpRequest2;
		}
		else
		{
			var httpRequest = this.httpRequest;
		}

		httpRequest.onreadystatechange = function (str) {};
		httpRequest.open("GET", script_src, false);
		httpRequest.send("");

		var s = httpRequest.responseText;

		httpRequest = null;

		try
		{
			this.EvalGlobal(s);
		}
		catch(e)
		{
			//alert('script_src: ' + script_src + '<pre>' + s + '</pre>');
		}
	},

	processResult: function(content, arCode)
	{
		//Javascript
		jsExtLoader.processScripts(arCode, true);

		if (null == jsExtLoader.obContainer)
			jsExtLoader.obContainer = jsExtLoader.onajaxfinish(content);
		else
			jsExtLoader.obContainer.innerHTML = content;

		//Javascript
		jsExtLoader.processScripts(arCode, false);
	},

	processScripts: function(arCode, bRunFirst)
	{
		for (var i = 0, length = arCode.length; i < length; i++)
		{
			if (arCode[i].bRunFirst != bRunFirst)
				continue;

			if (arCode[i].isInternal)
			{
				arCode[i].JS = arCode[i].JS.replace('<!--', '');
				jsExtLoader.EvalGlobal(arCode[i].JS);
			}
			else
			{
				jsExtLoader.EvalExternal(arCode[i].JS);
			}
		}
	},

	_CreateHttpObject: function()
	{
		var obj = null;
		if(window.XMLHttpRequest)
		{
			try {obj = new XMLHttpRequest();} catch(e){}
		}
		else if(window.ActiveXObject)
		{
			try {obj = new ActiveXObject("Microsoft.XMLHTTP");} catch(e){}
			if(!obj)
				try {obj = new ActiveXObject("Msxml2.XMLHTTP");} catch (e){}
		}
		return obj;
	}
}

/*
public jsAdminStyle - external CSS manager
*/

var jsAdminStyle = {

	arCSS: {},
	bInited: false,

	httpRequest: null,

	Init: function()
	{
		var arStyles = document.getElementsByTagName('LINK');
		if (arStyles.length > 0)
		{
			for (var i = 0; i<arStyles.length; i++)
			{
				if (arStyles[i].href)
				{
					var filename = arStyles[i].href;
					var pos = filename.indexOf('://');
					if (pos != -1)
						filename = filename.substr(filename.indexOf('/', pos + 3));

					arStyles[i].bxajaxflag = false;
					this.arCSS[filename] = arStyles[i];
				}
			}
		}

		this.bInited = true;
	},

	Load: function(filename)
	{
		if (!this.bInited) this.Init();

		if (null != this.arCSS[filename])
		{
			this.arCSS[filename].disabled = false;
			return;
		}

		var link = document.createElement("STYLE");
		link.type = 'text/css';

		var head = document.getElementsByTagName("HEAD")[0];
		head.insertBefore(link, head.firstChild);
		//head.appendChild(link);

		if (jsUtils.IsIE())
		{
			link.styleSheet.addImport(filename);
		}
		else
		{
			try
			{
				if (null == this.httpRequest)
					this.httpRequest = jsExtLoader._CreateHttpObject();

				this.httpRequest.onreadystatechange = null;

				this.httpRequest.open("GET", filename, false); // make *synchronous* request for css source
				this.httpRequest.send("");

				var s = this.httpRequest.responseText;

				// convert relative resourse paths in css to absolute. current path to css will be lost.
				var pos = filename.lastIndexOf('/');
				if (pos != -1)
				{
					var dirname = filename.substring(0, pos);
					s = s.replace(/url\(([^\/\\].*?)\)/gi, 'url(' + dirname + '/$1)');
				}

				link.appendChild(document.createTextNode(s));
			}
			catch (e) {}
		}
	},

	Unload: function(filename)
	{
		if (!this.bInited) this.Init();

		if (null != this.arCSS[filename])
		{
			this.arCSS[filename].disabled = true;
		}
	},

	UnloadAll: function()
	{
		if (!this.bInited) this.Init();
		else
			for (var i in this.arCSS)
			{
				if (this.arCSS[i].bxajaxflag)
					this.Unload(i);
			}
	}
}

// for compatibility with IE 5.0 browser
if (![].pop)
{
	Array.prototype.pop = function()
	{
		if (this.length <= 0) return false;
		var element = this[this.length-1];
		delete this[this.length-1];
		this.length--;
		return element;
	}

	Array.prototype.shift = function()
	{
		if (this.length <= 0) return false;
		var tmp = this.reverse();
		var element = tmp.pop();
		this.prototype = tmp.reverse();
		return element;
	}

	Array.prototype.push = function(element)
	{
		this[this.length] = element;
	}
}
//************************************************************

function jsWizard()
{
	this.currentStep = null;
	this.firstStep = null;

	this.arSteps = {};

	this.nextButtonID = "btn_popup_next";
	this.prevButtonID = "btn_popup_prev";
	this.finishButtonID = "btn_popup_finish";

	this.arButtons = {};
}

jsWizard.prototype.AddStep = function(stepID, arButtons)
{
	var element = document.getElementById(stepID);
	if (!element)
		return;

	if (typeof(arButtons) != "object")
		arButtons = {};

	this.arSteps[stepID] = {"element": element};

	//Actions
	for (var button in arButtons)
		this.arSteps[stepID][button] = arButtons[button];

	if (this.firstStep === null)
		this.firstStep = stepID;
}

jsWizard.prototype.SetCurrentStep = function(stepID)
{
	this.currentStep = stepID;
}

jsWizard.prototype.SetFirstStep = function(stepID)
{
	this.firstStep = stepID;
}

jsWizard.prototype.SetNextButtonID = function(buttonID)
{
	this.nextButtonID = buttonID;
}

jsWizard.prototype.SetPrevButtonID = function(buttonID)
{
	this.prevButtonID = buttonID;
}

jsWizard.prototype.SetFinishButtonID = function(buttonID)
{
	this.finishButtonID = buttonID;
}

jsWizard.prototype.SetCancelButtonID = function(buttonID)
{
	this.cancelButtonID = buttonID;
}


jsWizard.prototype.SetButtonDisabled = function(button, disabled)
{
	if (this.arButtons[button])
		this.arButtons[button].disabled = disabled;
}

jsWizard.prototype.IsStepExists = function(stepID)
{
	if (this.arSteps[stepID])
		return true;
	else
		return false;
}

jsWizard.prototype.Display = function()
{
	if (this.firstStep === null)
		return;

	this.currentStep = this.firstStep;

	var _this = this;
	var arButtons = {"next" : this.nextButtonID, "prev" : this.prevButtonID, "finish" : this.finishButtonID};
	for (var button in arButtons)
	{
		var buttonElement = document.getElementById(arButtons[button]);
		if (buttonElement && buttonElement.tagName == "INPUT")
		{
			buttonElement.buttonID = button;
			buttonElement.onclick = function() {_this._OnButtonClick(this.buttonID)};
			this.arButtons[button] = buttonElement;
		}
		else
			this.arButtons[button] = null;
	}

	this._OnStepShow();
}

jsWizard.prototype._OnButtonClick = function(button)
{
	if (this.arSteps[this.currentStep] )
	{
		var callback = this.arSteps[this.currentStep]["on" + button];
		if (callback && typeof(callback) == "function")
		{
			if (callback(this) === false)
				return;
		}
	}

	if (!this.arSteps[this.currentStep])
	{
		if (!this.arSteps[this.firstStep])
			return;

		this.currentStep = this.firstStep;
	}
	else if (this.arSteps[this.currentStep][button])
		this.currentStep = this.arSteps[this.currentStep][button];

	this._OnStepShow();
}

jsWizard.prototype._OnStepShow = function()
{
	//Display current step and hide others steps
	for (var stepID in this.arSteps)
		this.arSteps[stepID].element.style.display = (stepID == this.currentStep ? "" : "none");

	//Activate and disable buttons
	for (var button in this.arButtons)
	{
		if (this.arButtons[button])
		{
			var stepID = this.arSteps[this.currentStep][button];
			this.arButtons[button].disabled = (stepID && this.arSteps[stepID] ? false : true);
		}
	}

	//Execute onshow function
	if (this.arSteps[this.currentStep])
	{
		var callback = this.arSteps[this.currentStep]["onshow"];
		if (callback && typeof(callback) == "function")
			callback(this);
	}
}

var jsPopup = new JCPopup();
var jsComponentUtils = new JCComponentUtils();

