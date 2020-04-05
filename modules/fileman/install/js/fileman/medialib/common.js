// * * * * * * MEDIALIBRARY  * * * * * * *
// Common objects for medialibrary
//  * * * * * * * * * * * * * * * * * * * * * * 
function BXMLTypeSelector(Params)
{
	this.oML = Params.oML;
	this.oCallback = Params.oCallback;
	this.Types = Params.Types;
	this.Init();
}

BXMLTypeSelector.prototype = {
	Init: function()
	{
		this.pWnd = BX.create("DIV", {props:{className: "ml-type-sel"}});
		this.pValCont = this.pWnd.appendChild(BX.create("DIV", {props:{className: "mlt-val-cnt"}}));
		this.pPopup = this.pWnd.appendChild(BX.create("DIV", {props:{className: "mlt-popup"}}));
		this.pPopupInner = this.pPopup.appendChild(BX.create("DIV", {props:{className: "mlt-popup-inner"}}));
		this.bOpen = false;

		if (BX.browser.IsIE() && !BX.browser.IsDoctype())
		{
			this.pPopup.style.width = "202px";
			this.pWnd.style.height = "29px";
		}

		this.pIconCont = this.pValCont.appendChild(BX.create("DIV", {props:{className: "mlt-val-ic"}}));
		this.pNameCont = this.pValCont.appendChild(BX.create("DIV", {props:{className: "mlt-val-name"}}));

		var
			_this = this,
			i, it, html, src,
			l = this.Types.length;

		for (i = 0; i < l; i++)
		{
			it = this.pPopupInner.appendChild(BX.create("DIV", {props:{className: "mlt-item", id: "ml_type_item_" + i}}));
			src = this.Types[i].type_icon;

			html = "<table><tr><td class='mlt-ic'>" +
				"<img src='" + src + "' /></td>" +
				"<td class='mlt-title' title='" + this.Types[i].name + "'>" + bxhtmlspecialchars(this.Types[i].name) + "</td>" +
				"</tr></table>";
			it.innerHTML = html;
			it.onclick = function(e)
			{
				_this.SetType(this.id.substr("ml_type_item_".length));
				BX.PreventDefault(e);
			};

			it.onmouseover = function(){this.className = "mlt-item mlt-item-over";};
			it.onmouseout = function(){this.className = "mlt-item";};
		}

		this.pWnd.onclick = function(){_this.ShowPopup();};
	},

	SetType: function(ind, bCallback)
	{
		var Type = this.Types[ind];

		// Callback
		if (bCallback !== false)
			this.oCallback.func.apply(this.oCallback.obj, [{typeInd : parseInt(ind)}]);

		// Set to select
		this.pIconCont.innerHTML = "<img src='" + Type.type_icon + "'/>";
		this.pNameCont.innerHTML = bxhtmlspecialchars(Type.name);

		// Close dialog
		this.ShowPopup(false);
	},

	ShowPopup: function(bOpen)
	{
		if (bOpen == this.bOpen)
			return;

		if (bOpen !== true && bOpen !== false)
			bOpen = !this.bOpen;

		if (bOpen)
		{
			this.pPopup.style.height = '1px';
			this.pPopup.style.display = "block";
		}

		var
			_this = this,
			curHeight = bOpen ? 1 : parseInt(this.pPopup.style.height),
			count = 0,
			timeInt = 10,
			maxHeight = 0,
			dx = 5;

		if (this.Interval)
			clearInterval(this.Interval);

		this.Interval = setInterval(function()
			{
				if (bOpen)
				{
					//this.pPopup.style.visibility = "visible";
					if (maxHeight == 0)
						maxHeight = parseInt(_this.pPopupInner.offsetHeight);

					curHeight += Math.round(dx * count);
					if (curHeight > maxHeight)
					{
						curHeight = maxHeight + 2;
						clearInterval(_this.Interval);
					}
				}
				else
				{
					curHeight -= Math.round(dx * count);
					if (curHeight < 0)
					{
						_this.pPopup.style.display = "none";
						curHeight = 0;
						clearInterval(_this.Interval);
					}
				}

				_this.pPopup.style.height = curHeight + 'px';
				count++;
			},
			timeInt
		);
		
		this.bOpen = bOpen;
		this.oML.bSubdialogOpened = bOpen;
		setTimeout(function()
		{
			if (bOpen)
			{
				BX.bind(document, "keypress", BX.proxy(_this.OnKeyPress, _this));
				BX.bind(document, "mousedown", BX.proxy(_this.OnMouseDown, _this));
			}
			else
			{
				BX.unbind(document, "keypress", BX.proxy(_this.OnKeyPress, _this));
				BX.unbind(document, "mousedown", BX.proxy(_this.OnMouseDown, _this));
			}
		}, 100);
	},
	
	OnKeyPress: function(e)
	{
		if(!e) e = window.event;
		if(e && e.keyCode == 27)
			this.ShowPopup(false);
	},
	
	OnMouseDown: function(e)
	{
		if(!e) e = window.event;
		var targ = e.target || e.srcElement;
		if (targ.nodeType == 3) // defeat Safari bug
			targ = targ.parentNode;

		if (!BX.findParent(targ, {className: 'ml-type-cont'}))
		{
			this.ShowPopup(false);
			return BX.PreventDefault(e);
		}
	}
};

function BXOverlay(arParams)
{
	this.id = arParams.id || 'bx_trans_overlay';
	this.zIndex = arParams.zIndex || 100;
}

BXOverlay.prototype = {
	Create: function ()
	{
		this.bCreated = true;
		this.bShowed = false;
		var windowSize = BX.GetWindowScrollSize();
		this.pWnd = document.body.appendChild(BX.create("DIV", {props: {id: this.id, className: "bx-trans-overlay"}, style:{zIndex: this.zIndex, width: windowSize.scrollWidth + "px", height: windowSize.scrollHeight + "px"}, events: {drag: BX.False, selectstart: BX.False}}));

		var _this = this;
		window[this.id + '_resize'] = function(){_this.Resize();};
	},

	Show: function(arParams)
	{
		if (!this.bCreated)
			this.Create();
		this.bShowed = true;

		var windowSize = BX.GetWindowScrollSize();

		this.pWnd.style.display = 'block';
		this.pWnd.style.width = windowSize.scrollWidth + "px";
		this.pWnd.style.height = windowSize.scrollHeight + "px";

		if (!arParams)
			arParams = {};

		if (arParams.clickCallback)
		{
			this.pWnd.onclick = function(e)
			{
				var
					clbck = arParams.clickCallback,
					p = clbck.params || [];
				if (clbck.obj)
					clbck.func.apply(clbck.obj, p);
				else
					clbck.func(p);
				return BX.PreventDefault(e);
			};
		}

		if (arParams.zIndex)
			this.pWnd.style.zIndex = arParams.zIndex;

		BX.bind(window, "resize", window[this.id + '_resize']);
		return this.pWnd;
	},

	Hide: function ()
	{
		if (!this.bShowed)
			return;
		this.bShowed = false;
		this.pWnd.style.display = 'none';
		BX.unbind(window, "resize", window[this.id + '_resize']);
		this.pWnd.onclick = null;
	},

	Resize: function ()
	{
		if (this.bCreated)
			this.pWnd.style.width = BX.GetWindowScrollSize().scrollWidth + "px";
	},

	Remove: function ()
	{
		this.Hide();
		if (this.pWnd.parentNode)
			this.pWnd.parentNode.removeChild(this.pWnd);
	}
};

window.bxhtmlspecialchars = function(str)
{
	if(!str.replace)
		return str;
	str = str.replace(/&/g, '&amp;');
	str = str.replace(/"/g, '&quot;');
	str = str.replace(/</g, '&lt;');
	str = str.replace(/>/g, '&gt;');
	return str;
}

window.bxspcharsback = function(str)
{
	if(!(typeof(str) == "string" || str instanceof String))
		return str;

	str = str.replace(/\&quot;/g, '"');
	str = str.replace(/&#39;/g, "'");
	str = str.replace(/\&lt;/g, '<');
	str = str.replace(/\&gt;/g, '>');
	str = str.replace(/\&#33;/g, '!');
	str = str.replace(/\&#36;/g, '$');
	str = str.replace(/\&#37;/g, '%');
	str = str.replace(/\&#126;/g, '~');
	str = str.replace(/\&nbsp;/g, ' ');
	str = str.replace(/\&#35;/g, '#');
	str = str.replace(/\&amp;/g, '&');
	return str;
}

window.ConvertArray2Post = function(arData, prefix)
{
	var data = '', i, name;
	if (null != arData)
	{
		for(i in arData)
		{
			if (data.length > 0) data += '&';
			name = jsUtils.urlencode(i);
			if(prefix)
				name = prefix + '[' + name + ']';
			if(typeof arData[i] == 'object')
				data += ConvertArray2Post(arData[i], name)
			else
				data += name + '=' + jsUtils.urlencode(arData[i])
		}
	}
	return data;
}
