/***********************************************************
Bitrix AJAX library ver 6.5 alpha 
***********************************************************/

/*
private CAjaxThread class - description of current AJAX request thread.
*/
function CAjaxThread(TID)
{
	this.TID = TID;
	this.httpRequest = this._CreateHttpObject();
	this.arAction = [];
}

CAjaxThread.prototype._CreateHttpObject = function()
{
	var obj = null;
	if (window.XMLHttpRequest)
	{
		try {obj = new XMLHttpRequest();} catch(e){}
	}
	else if (window.ActiveXObject)
	{
		try {obj = new ActiveXObject("Microsoft.XMLHTTP");} catch(e){}
		if (!obj)
			try {obj = new ActiveXObject("Msxml2.XMLHTTP");} catch (e){}
	}
	return obj;
}

CAjaxThread.prototype.addAction = function(obHandler)
{
	this.arAction.push(obHandler);
}

CAjaxThread.prototype.clearActions = function()
{
	this.arAction = [];
}

CAjaxThread.prototype.nextAction = function()
{
	return this.arAction.shift();
}

CAjaxThread.prototype.Clear = function()
{
	this.arAction = null;
	this.httpRequest = null;
}

/*
public CAjax main class
*/
function CAjax()
{
	this.arThreads = {};
	this.obTemporary = null;
}

CAjax.prototype._PrepareData = function(arData, prefix)
{
	var data = '';
	if (null != arData)
	{
		for(var i in arData)
		{
			if (data.length > 0) data += '&';
			var name = jsAjaxUtil.urlencode(i);
			if(prefix)
				name = prefix + '[' + name + ']';
			if(typeof arData[i] == 'object')
				data += this._PrepareData(arData[i], name)
			else
				data += name + '=' + jsAjaxUtil.urlencode(arData[i])
		}
	}
	return data;
}

CAjax.prototype.GetThread = function(TID)
{
	return this.arThreads[TID];
}

CAjax.prototype.InitThread = function()
{
	while (true)
	{
		var TID = 'TID' + Math.floor(Math.random() * 1000000);
		if (!this.arThreads[TID]) break;
	}

	this.arThreads[TID] = new CAjaxThread(TID);
	
	return TID;
}

CAjax.prototype.AddAction = function(TID, obHandler)
{
	if (this.arThreads[TID])
	{
		this.arThreads[TID].addAction(obHandler);
	}
}

CAjax.prototype._OnDataReady = function(TID, result)
{
	if (!this.arThreads[TID]) return;

	while (obHandler = this.arThreads[TID].nextAction())
	{
		obHandler(result);
	}
}
	
CAjax.prototype._Close = function(TID)
{
	if (!this.arThreads[TID]) return;

	this.arThreads[TID].Clear();
	this.arThreads[TID] = null;
}
	
CAjax.prototype._SetHandler = function(TID)
{
	var oAjax = this;
	
	function __cancelQuery(e)
	{
		if (!e) e = window.event
		if (!e) return;
		if (e.keyCode == 27)
		{
			oAjax._Close(TID);
			jsEvent.removeEvent(document, 'keypress', this);
		}
	}
	
	function __handlerReadyStateChange()
	{
		if (oAjax.bCancelled) return;
		if (!oAjax.arThreads[TID]) return;
		if (!oAjax.arThreads[TID].httpRequest) return;
		if (oAjax.arThreads[TID].httpRequest.readyState == 4)
		{
			var status = oAjax.arThreads[TID].httpRequest.getResponseHeader('X-Bitrix-Ajax-Status');
			var bRedirect = (status == 'Redirect');
			
			var s = oAjax.arThreads[TID].httpRequest.responseText;
			
			jsAjaxParser.mode = 'implode';
			s = jsAjaxParser.process(s);
			
			if (!bRedirect)
				oAjax._OnDataReady(TID, s);

			oAjax.__prepareOnload();

			if (jsAjaxParser.code.length > 0)
				jsAjaxUtil.EvalPack(jsAjaxParser.code);
			
			oAjax.__runOnload();
			//setTimeout(function() {alert(1); oAjax.__runOnload(); alert(2)}, 30);
			oAjax._Close(TID);
		}
	}

	this.arThreads[TID].httpRequest.onreadystatechange = __handlerReadyStateChange;
	jsEvent.addEvent(document, "keypress", __cancelQuery);
}

CAjax.prototype.__prepareOnload = function()
{
	this.obTemporary = window.onload;
	window.onload = null;
}

CAjax.prototype.__runOnload = function()
{
	if (window.onload) window.onload();
	window.onload = this.obTemporary;
	this.obTemporary = null;
}

CAjax.prototype.Send = function(TID, url, arData)
{
	if (!this.arThreads[TID]) return;

	if (null != arData)
		var data = this._PrepareData(arData);
	else
		var data = '';

	if (data.length > 0) 
	{
		if (url.indexOf('?') == -1)
			url += '?' + data;
		else
			url += '&' + data;	
	}

	if(this.arThreads[TID].httpRequest)
	{
		this.arThreads[TID].httpRequest.open("GET", url, true);
		this._SetHandler(TID);
		return this.arThreads[TID].httpRequest.send("");
	}
}

CAjax.prototype.Post = function(TID, url, arData)
{
	var data = '';

	if (null != arData)
		data = this._PrepareData(arData);
	if(this.arThreads[TID].httpRequest)
	{
		this.arThreads[TID].httpRequest.open("POST", url, true);
		this._SetHandler(TID);
		this.arThreads[TID].httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		return this.arThreads[TID].httpRequest.send(data);
	}
}

/*
public CAjaxForm - class to send forms via iframe
*/
function CAjaxForm(obForm, obHandler, bFirst)
{
	this.obForm = obForm;
	this.obHandler = obHandler;
	this.obFrame = null;

	this.isFormProcessed = false;
	
	if (null == bFirst)
		this.bFirst = false;
	else
		this.bFirst = bFirst;
	
	this.__tmpFormTarget = '';
	this.obAJAXIndicator = null;
	
	this.currentBrowserDetected = "";
	if (window.opera)
		this.currentBrowserDetected = "Opera";
	else if (navigator.userAgent)
	{
		if (navigator.userAgent.indexOf("MSIE") != -1)
			this.currentBrowserDetected = "IE";
		else if (navigator.userAgent.indexOf("Firefox") != -1)
			this.currentBrowserDetected = "Firefox";
	}

	this.IsIE9 = !!document.documentMode && document.documentMode >= 9;
}

CAjaxForm.prototype.setProcessedFlag = function(value)
{
	if (null == value) value = true;
	else value = value ? true : false;
	
	this.obForm.bxAjaxProcessed = value;
	this.isFormProcessed = value;
}

CAjaxForm.isFormProcessed = function(obForm)
{
	if (obForm.bxAjaxProcessed)
		return obForm.bxAjaxProcessed;
	else
		return false;
}

CAjaxForm.prototype.process = function()
{
	var _this = this;

	function __formResultHandler()
	{
		if (!_this.obFrame.contentWindow.document || _this.obFrame.contentWindow.document.body.innerHTML.length == 0) return;

		if (null != _this.obHandler)
		{
			_this.obHandler(_this.obFrame.contentWindow.document.body.innerHTML);
		}

		if (_this.obFrame.contentWindow.AJAX_runExternal)
			_this.obFrame.contentWindow.AJAX_runExternal();

		if (_this.obFrame.contentWindow.AJAX_runGlobal)
			_this.obFrame.contentWindow.AJAX_runGlobal();

		if (_this.bFirst)
		{
			try
			{
				_this.obForm.target = _this.__tmpFormTarget;
				_this.obAJAXIndicator.parentNode.removeChild(_this.obAJAXIndicator);
				_this.obForm.bxAjaxProcessed = false;
			}
			catch (e) 
			{
				_this.obForm = null;
			}
			
			_this.obAJAXIndicator = null;

			if (this.currentBrowserDetected != 'IE') 
				jsEvent.removeAllEvents(_this.obFrame);

			// fixing another strange bug. Now for FF
			var TimerID = setTimeout("document.body.removeChild(document.getElementById('" + _this.obFrame.id + "'));", 100);
			_this.obFrame = null;
			
			if (window.onFormLoaded)
			{
				window.onFormLoaded();
				window.onFormLoaded = null;
			}
		}
	}

	if (this.obForm.target && this.obForm.target.substring(0, 5) == 'AJAX_')
		return;

	if (this.currentBrowserDetected == 'IE')
	{
		if (this.IsIE9)
		{
			this.obAJAXIndicator = document.createElement('input');
			this.obAJAXIndicator.setAttribute('name', 'AJAX_CALL');
			this.obAJAXIndicator.setAttribute('type', 'hidden');
		} 
		else
		{
			this.obAJAXIndicator = document.createElement('<input name="AJAX_CALL" type="hidden" />');
		}
	}
	else
	{
		this.obAJAXIndicator = document.createElement('INPUT');
		this.obAJAXIndicator.type = 'hidden';
		this.obAJAXIndicator.name = 'AJAX_CALL';
	}
	
	this.obAJAXIndicator.value = 'Y';
	
	this.obForm.appendChild(this.obAJAXIndicator);

	var frameName = 'AJAX_' + Math.round(Math.random() * 100000);
	
	if (this.currentBrowserDetected == 'IE')
		if (this.IsIE9)
		{
			this.obFrame = document.createElement('iframe');
			this.obFrame.setAttribute('name', frameName);
		}
		else
		{
			this.obFrame = document.createElement('<iframe name="' + frameName + '"></iframe>');
		}
	else
		this.obFrame = document.createElement('IFRAME');
	
	this.obFrame.style.display = 'none';
	this.obFrame.src = 'javascript:\'\'';
	this.obFrame.id = frameName;
	this.obFrame.name = frameName;
	
	document.body.appendChild(this.obFrame);

	this.__tmpFormTarget = this.obForm.target;
	this.obForm.target = frameName;

	// one more strange bug in IE..
	if (this.currentBrowserDetected == 'IE') 
		this.obFrame.attachEvent("onload", __formResultHandler);
	else
		jsEvent.addEvent(this.obFrame, 'load', __formResultHandler);
	this.setProcessedFlag();
}

var jsAjaxParser = {
	code: [],
	mode: 'implode',
	
	regexp: null,
	regexp_src: null,
	
	process: function(s)
	{
		this.code = [];
		
		if (null == this.regexp)
			this.regexp = /(<script([^>]*)>)([\S\s]*?)(<\/script>)/i;

		do
		{
			var arMatch = s.match(this.regexp);
			
			if (null == arMatch) 
				break;

			var pos = arMatch.index;
			var len = arMatch[0].length;
			
			if (pos > 0)
				this.code.push({TYPE: 'STRING', DATA: s.substring(0, pos)});
			
			if (typeof arMatch[1] == 'undefined' || arMatch[1].indexOf('src=') == -1)
			{
				var script = arMatch[3];
				script = script.replace('<!--', '');

				this.code.push({TYPE: 'SCRIPT', DATA: script});
			}
			else
			{
				if (null == this.regexp_src) 
					this.regexp_src = /src="([^"]*)?"/i;
				var arResult = this.regexp_src.exec(arMatch[1]);
			
				if (null != arResult && arResult[1])
				{
					this.code.push({TYPE: 'SCRIPT_EXT', DATA: arResult[1]});
				}
			}
			
			s = s.substring(pos + len);
		} while (true);

		if (s.length > 0)
		{
			this.code.push({TYPE: 'STRING', DATA: s});
		}
		
		if (this.mode == 'implode')
		{
			s = '';
			for (var i = 0, cnt = this.code.length; i < cnt; i++)
			{
				if (this.code[i].TYPE == 'STRING') 
					s += this.code[i].DATA;
			}
			
			return s;
		}
		else
			return this.code;
	}
}

/*
public jsAjaxUtil - utility object
*/
var jsAjaxUtil = {
	// remove all DOM node children (with events)
	RemoveAllChild: function(pNode)
	{
		try
		{
			while(pNode.childNodes.length>0)
			{
				jsEvent.clearObject(pNode.childNodes[0]);
				pNode.removeChild(pNode.childNodes[0]);
			}
		}
		catch(e)
		{}
	},

	// evaluate js string in window scope
	EvalGlobal: function(script)
	{
		if (window.execScript)
			window.execScript(script, 'javascript');
		else if (jsAjaxUtil.IsSafari())
			window.setTimeout(script, 0);
		else
			window.eval(script);
	},
	
	arLoadedScripts: [],
	
	__isScriptLoaded: function (script_src)
	{
		for (var i=0; i<jsAjaxUtil.arLoadedScripts.length; i++)
			if (jsAjaxUtil.arLoadedScripts[i] == script_src) return true;
		return false;
	},
	
	// evaluate external script
	EvalExternal: function(script_src)
	{
		if (
			/\/bitrix\/js\/main\/ajax.js$/i.test(script_src)
			||
			/\/bitrix\/js\/main\/core\/core.js$/i.test(script_src)
		) return;
	
		if (jsAjaxUtil.__isScriptLoaded(script_src)) return;
		jsAjaxUtil.arLoadedScripts.push(script_src);

		var obAjaxThread = new CAjaxThread();

		obAjaxThread.httpRequest.open("GET", script_src, false); // make *synchronous* request for script source
		obAjaxThread.httpRequest.send("");
		
		var s = obAjaxThread.httpRequest.responseText;
		obAjaxThread.Clear();
		obAjaxThread = null;
		
		jsAjaxUtil.EvalGlobal(s); // evaluate script source
	},
	
	EvalPack: function(code)
	{
		for (var i = 0, cnt = code.length; i < cnt; i++)
		{
			if (code[i].TYPE == 'SCRIPT_EXT' || code[i].TYPE == 'SCRIPT_SRC')
				jsAjaxUtil.EvalExternal(code[i].DATA);
			else if (code[i].TYPE == 'SCRIPT')
				jsAjaxUtil.EvalGlobal(code[i].DATA);
		}
	},
	
	// urlencode js version
	urlencode: function(s)
	{
		return escape(s).replace(new RegExp('\\+','g'), '%2B');
	},
	
	// trim js version
	trim: function(s)
	{
		var r, re;
		re = /^[ \r\n]+/g;
		r = s.replace(re, "");
		re = /[ \r\n]+$/g;
		r = r.replace(re, "");
		return r;
	},
	
	GetWindowSize: function()
	{
		var innerWidth, innerHeight;

		if (self.innerHeight) // all except Explorer
		{
			innerWidth = self.innerWidth;
			innerHeight = self.innerHeight;
		}
		else if (document.documentElement && document.documentElement.clientHeight) // Explorer 6 Strict Mode
		{
			innerWidth = document.documentElement.clientWidth;
			innerHeight = document.documentElement.clientHeight;
		}
		else if (document.body) // other Explorers
		{
			innerWidth = document.body.clientWidth;
			innerHeight = document.body.clientHeight;
		}

		var scrollLeft, scrollTop;
		if (self.pageYOffset) // all except Explorer
		{
			scrollLeft = self.pageXOffset;
			scrollTop = self.pageYOffset;
		}
		else if (document.documentElement && document.documentElement.scrollTop) // Explorer 6 Strict
		{
			scrollLeft = document.documentElement.scrollLeft;
			scrollTop = document.documentElement.scrollTop;
		}
		else if (document.body) // all other Explorers
		{
			scrollLeft = document.body.scrollLeft;
			scrollTop = document.body.scrollTop;
		}

		var scrollWidth, scrollHeight;

		if ( (document.compatMode && document.compatMode == "CSS1Compat"))
		{
			scrollWidth = document.documentElement.scrollWidth;
			scrollHeight = document.documentElement.scrollHeight;
		}
		else
		{
			if (document.body.scrollHeight > document.body.offsetHeight)
				scrollHeight = document.body.scrollHeight;
			else
				scrollHeight = document.body.offsetHeight;

			if (document.body.scrollWidth > document.body.offsetWidth || 
				(document.compatMode && document.compatMode == "BackCompat") ||
				(document.documentElement && !document.documentElement.clientWidth)
			)
				scrollWidth = document.body.scrollWidth;
			else
				scrollWidth = document.body.offsetWidth;
		}

		return  {"innerWidth" : innerWidth, "innerHeight" : innerHeight, "scrollLeft" : scrollLeft, "scrollTop" : scrollTop, "scrollWidth" : scrollWidth, "scrollHeight" : scrollHeight};
	},

	// get element position relative to the whole window
	GetRealPos: function(el)
	{
		if (el.getBoundingClientRect)
		{
			var obRect = el.getBoundingClientRect();
			var obWndSize = jsAjaxUtil.GetWindowSize();
			var arPos = {
				left: obRect.left + obWndSize.scrollLeft, 
				top: obRect.top + obWndSize.scrollTop, 
				right: obRect.right + obWndSize.scrollLeft, 
				bottom: obRect.bottom + obWndSize.scrollTop
			};
			return arPos;
		}
		
		if(!el || !el.offsetParent)
			return false;

		var res = Array();
		res["left"] = el.offsetLeft;
		res["top"] = el.offsetTop;
		var objParent = el.offsetParent;
		
		while(objParent && objParent.tagName != "BODY")
		{
			res["left"] += objParent.offsetLeft;
			res["top"] += objParent.offsetTop;
			objParent = objParent.offsetParent;
		}
		res["right"] = res["left"] + el.offsetWidth;
		res["bottom"] = res["top"] + el.offsetHeight;
		
		return res;
	},
	
	IsIE: function()
	{
		return (document.attachEvent && !jsAjaxUtil.IsOpera());
	},

	IsOpera: function()
	{
		return (navigator.userAgent.toLowerCase().indexOf('opera') != -1);
	},
	
	IsSafari: function()
	{
		var userAgent = navigator.userAgent.toLowerCase();
		return (/webkit/.test(userAgent));
	},

	// simple ajax data loading method (without any visual effects)
	LoadData: function(url, obHandler)
	{
		if (!obHandler) return;

		var TID = jsAjax.InitThread();
		jsAjax.AddAction(TID, obHandler);
		jsAjax.Send(TID, url);
		
		return TID;
	},
	
	// simple ajax data post method (without any visual effects)
	PostData: function(url, arData, obHandler)
	{
		if (!obHandler) return;

		var TID = jsAjax.InitThread();
		jsAjax.AddAction(TID, obHandler);
		jsAjax.Post(TID, url, arData);
		
		return TID;
	},
	
	__LoadDataToDiv: function(url, cont, bReplace, bShadow)
	{
		if (null == bReplace) bReplace = true;
		if (null == bShadow) bShadow = true;
		
		if (typeof cont == 'string' || typeof cont == 'object' && cont.constructor == String)
			var obContainerNode = document.getElementById(cont);
		else
			var obContainerNode = cont;
		
		if (!obContainerNode) return;

		var rnd_tid = Math.round(Math.random() * 1000000);
		
		function __putToContainer(data)
		{
			if (!obContainerNode) return;
			
			//setTimeout('jsAjaxUtil.CloseLocalWaitWindow(\'' + rnd_tid + '\', \'' + obContainerNode.id + '\')', 100);
			jsAjaxUtil.CloseLocalWaitWindow(rnd_tid, obContainerNode);

			if (bReplace)
			{
				jsAjaxUtil.RemoveAllChild(obContainerNode);
				obContainerNode.innerHTML = data;
			}
			else
				obContainerNode.innerHTML += data;
		}

		jsAjaxUtil.ShowLocalWaitWindow(rnd_tid, obContainerNode, bShadow);
		var TID = jsAjaxUtil.LoadData(url, __putToContainer);
	},
	
	// insert ajax data to container (with visual effects)
	InsertDataToNode: function(url, cont, bShadow)
	{
		if (null == bShadow) bShadow = true;
		jsAjaxUtil.__LoadDataToDiv(url, cont, true, bShadow);
	},

	// append ajax data to container (with visual effects)
	AppendDataToNode: function(url, cont, bShadow)
	{
		if (null == bShadow) bShadow = true;
		jsAjaxUtil.__LoadDataToDiv(url, cont, false, bShadow);
	},
	
	GetStyleValue: function(el, styleProp)
	{
		if(el.currentStyle)
			var res = el.currentStyle[styleProp];
		else if(window.getComputedStyle)
			var res = document.defaultView.getComputedStyle(el, null).getPropertyValue(styleProp);
		return res;
	},
	
	// show ajax visuality
	ShowLocalWaitWindow: function (TID, cont, bShadow)
	{
		if (typeof cont == 'string' || typeof cont == 'object' && cont.constructor == String)
			var obContainerNode = document.getElementById(cont);
		else
			var obContainerNode = cont;
		
		if (obContainerNode.getBoundingClientRect)
		{
			var obRect = obContainerNode.getBoundingClientRect();
			var obWndSize = jsAjaxUtil.GetWindowSize();

			var arContainerPos = {
				left: obRect.left + obWndSize.scrollLeft, 
				top: obRect.top + obWndSize.scrollTop, 
				right: obRect.right + obWndSize.scrollLeft, 
				bottom: obRect.bottom + obWndSize.scrollTop
			};
		}
		else
			var arContainerPos = jsAjaxUtil.GetRealPos(obContainerNode);
		
		var container_id = obContainerNode.id;
		
		if (!arContainerPos) return;
		
		if (null == bShadow) bShadow = true;
		
		if (bShadow)
		{
			var obWaitShadow = document.body.appendChild(document.createElement('DIV'));
			obWaitShadow.id = 'waitshadow_' + container_id + '_' + TID;
			obWaitShadow.className = 'waitwindowlocalshadow';
			obWaitShadow.style.top = (arContainerPos.top - 5) + 'px';
			obWaitShadow.style.left = (arContainerPos.left - 5) + 'px';
			obWaitShadow.style.height = (arContainerPos.bottom - arContainerPos.top + 10) + 'px';
			obWaitShadow.style.width = (arContainerPos.right - arContainerPos.left + 10) + 'px';
		}
		
		var obWaitMessage = document.body.appendChild(document.createElement('DIV'));
		obWaitMessage.id = 'wait_' + container_id + '_' + TID;
		obWaitMessage.className = 'waitwindowlocal';
		
		var div_top = arContainerPos.top + 5;
		if (div_top < document.body.scrollTop) div_top = document.body.scrollTop + 5;
		
		obWaitMessage.style.top = div_top + 'px';
		obWaitMessage.style.left = (arContainerPos.left + 5) + 'px';
		
		if(jsAjaxUtil.IsIE())
		{
			var frame = document.createElement("IFRAME");
			frame.src = "javascript:''";
			frame.id = 'waitframe_' + container_id + '_' + TID;
			frame.className = "waitwindowlocal";
			frame.style.width = obWaitMessage.offsetWidth + "px";
			frame.style.height = obWaitMessage.offsetHeight + "px";
			frame.style.left = obWaitMessage.style.left;
			frame.style.top = obWaitMessage.style.top;
			document.body.appendChild(frame);
		}
		
		function __Close(e)
		{
			if (!e) e = window.event
			if (!e) return;
			if (e.keyCode == 27)
			{
				jsAjaxUtil.CloseLocalWaitWindow(TID, cont);
				jsEvent.removeEvent(document, 'keypress', __Close);
			}
		}
		
		jsEvent.addEvent(document, 'keypress', __Close);
	},

	// hide ajax visuality
	CloseLocalWaitWindow: function(TID, cont)
	{
		if (typeof cont == 'string' || typeof cont == 'object' && cont.constructor == String)
			var obContainerNode = document.getElementById(cont);
		else
			var obContainerNode = cont;
	
		var container_id = obContainerNode.id;
		
		var obWaitShadow = document.getElementById('waitshadow_' + container_id + '_' + TID);
		if (obWaitShadow)
			document.body.removeChild(obWaitShadow);
		var obWaitMessageFrame = document.getElementById('waitframe_' + container_id + '_' + TID);
		if (obWaitMessageFrame)
			document.body.removeChild(obWaitMessageFrame);
		var obWaitMessage = document.getElementById('wait_' + container_id + '_' + TID);
		if (obWaitMessage)
			document.body.removeChild(obWaitMessage);
	},

	// simple form sending vithout visual effects. use onsubmit="SendForm(this, MyFunction)"
	SendForm: function(obForm, obHandler)
	{
		if (typeof obForm == 'string' || typeof obForm == 'object' && obForm.constructor == String)
			var obFormHandler = document.getElementById(obForm);
		else
			var obFormHandler = obForm;
			
		if (!obFormHandler.name || obFormHandler.name.length <= 0)
		{
			obFormHandler.name = 'AJAXFORM_' + Math.floor(Math.random() * 1000000);
		}
	
		var obFormMigrate = new CAjaxForm(obFormHandler, obHandler, true);
		obFormMigrate.process();

		return true;
	},
	
	// ajax form submit with visuality and put data to container. use onsubmit="InsertFormDataToNode(this, 'cont_id')"
	InsertFormDataToNode: function(obForm, cont, bShadow)
	{
		if (null == bShadow) bShadow = true;
		return jsAjaxUtil.__LoadFormToDiv(obForm, cont, true, bShadow);
	},

	// similiar with InsertFormDataToNode but append data to container
	AppendFormDataToNode: function(obForm, cont, bShadow)
	{
		if (null == bShadow) bShadow = true;
		return jsAjaxUtil.__LoadFormToDiv(obForm, cont, false, bShadow);
	},
	
	__LoadFormToDiv: function(obForm, cont, bReplace, bShadow)
	{
		if (null == bReplace) bReplace = true;
		if (null == bShadow) bShadow = true;
		
		if (typeof cont == 'string' || typeof cont == 'object' && cont.constructor == String)
			var obContainerNode = document.getElementById(cont);
		else
			var obContainerNode = cont;
		
		if (!obContainerNode) return;

		function __putToContainer(data)
		{
			if (!obContainerNode) return;
			
			if (bReplace)
			{
				jsAjaxUtil.RemoveAllChild(obContainerNode);
				obContainerNode.innerHTML = data;
			}
			else
				obContainerNode.innerHTML += data;
				
			jsAjaxUtil.CloseLocalWaitWindow(obContainerNode.id, obContainerNode);
		}

		jsAjaxUtil.ShowLocalWaitWindow(obContainerNode.id, obContainerNode, bShadow);
		
		return jsAjaxUtil.SendForm(obForm, __putToContainer);
	},

	// load to page new title, css files or script code strings
	UpdatePageData: function (arData)
	{
		if (arData.TITLE) jsAjaxUtil.UpdatePageTitle(arData.TITLE);
		if (arData.NAV_CHAIN) jsAjaxUtil.UpdatePageNavChain(arData.NAV_CHAIN);
		if (arData.CSS && arData.CSS.length > 0) jsAjaxUtil.UpdatePageCSS(arData.CSS);
		if (arData.SCRIPTS && arData.SCRIPTS.length > 0) jsAjaxUtil.UpdatePageScripts(arData.SCRIPTS);
	},
	
	UpdatePageScripts: function(arScripts)
	{
		for (var i = 0; i < arScripts.length; i++)
		{
			jsAjaxUtil.EvalExternal(arScripts[i]);
		}
	},
	
	UpdatePageCSS: function (arCSS)
	{
		jsStyle.UnloadAll();
		for (var i = 0; i < arCSS.length; i++)
		{
			jsStyle.Load(arCSS[i]);
		}
	},
	
	UpdatePageTitle: function(title)
	{
		var obTitle = document.getElementById('pagetitle');
		if (obTitle) 
		{
			obTitle.removeChild(obTitle.firstChild);
			if (!obTitle.firstChild)
				obTitle.appendChild(document.createTextNode(title));
			else
				obTitle.insertBefore(document.createTextNode(title), obTitle.firstChild);
		}
		
		document.title = title;
	},
	
	UpdatePageNavChain: function(nav_chain)
	{
		var obNavChain = document.getElementById('navigation');
		if (obNavChain)
		{
			obNavChain.innerHTML = nav_chain;
		}
	},
	
	ScrollToNode: function(node)
	{
		if (typeof node == 'string' || typeof node == 'object' && node.constructor == String)
			var obNode = document.getElementById(node);
		else
			var obNode = node;
		
		if (obNode.scrollIntoView)
			obNode.scrollIntoView(true);
		else
		{
			var arNodePos = jsAjaxUtil.GetRealPos(obNode);
			window.scrollTo(arNodePos.left, arNodePos.top);
		}
	}
}

/*
public jsStyle - external CSS manager
*/
var jsStyle = {

	arCSS: {},
	bInited: false,
	
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
		if (!this.bInited) 
			this.Init();
	
		if (null != this.arCSS[filename])
		{
			this.arCSS[filename].disabled = false;
			return;
		}

		/*
		var cssNode = document.createElement('link');
		cssNode.type = 'text/css';
		cssNode.rel = 'stylesheet';
		cssNode.href = filename;
		document.getElementsByTagName("head")[0].appendChild(cssNode);
		*/
		
		var link = document.createElement("STYLE");
		link.type = 'text/css';

		var head = document.getElementsByTagName("HEAD")[0];
		head.insertBefore(link, head.firstChild);
		//head.appendChild(link);
		
		if (jsAjaxUtil.IsIE())
		{
			link.styleSheet.addImport(filename);
		}
		else
		{
			var obAjaxThread = new CAjaxThread();
			obAjaxThread.httpRequest.onreadystatechange = null;

			obAjaxThread.httpRequest.open("GET", filename, false); // make *synchronous* request for css source
			obAjaxThread.httpRequest.send("");
			
			var s = obAjaxThread.httpRequest.responseText;
			
			// convert relative resourse paths in css to absolute. current path to css will be lost.
			var pos = filename.lastIndexOf('/');
			if (pos != -1)
			{
				var dirname = filename.substring(0, pos);
				s = s.replace(/url\(([^\/\\].*?)\)/gi, 'url(' + dirname + '/$1)');
			}
			
			obAjaxThread.Clear();
			obAjaxThread = null;

			link.appendChild(document.createTextNode(s));
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

/*
public jsEvent - cross-browser event manager object
*/
var jsEvent = {
	
	objectList: [null],
	objectEventList: [null],

	__eventManager: function(e)
	{
		if (!e) e = window.event
		var result = true;
	
		// browser comptiability
		try
		{
			if (e.srcElement)
				e.currentTarget = e.srcElement;
		}
		catch (e) {}
		
		if (this.bxEventIndex && jsEvent.objectEventList[this.bxEventIndex] && jsEvent.objectEventList[this.bxEventIndex][e.type])
		{
			var len = jsEvent.objectEventList[this.bxEventIndex][e.type].length;
			for (var i=0; i<len; i++)
			{
				if (jsEvent.objectEventList[this.bxEventIndex][e.type] && jsEvent.objectEventList[this.bxEventIndex][e.type][i])
				{
					var tmp_result = jsEvent.objectEventList[this.bxEventIndex][e.type][i](e);
					if ('boolean' == typeof tmp_result) result = result && tmp_result;
					if (!result) return false;
				}
			}
		}

		return true;
	},
	
	addEvent: function(obElement, event, obHandler)
	{
		if (!obElement.bxEventIndex)
		{
			obElement.bxEventIndex = jsEvent.objectList.length;
			jsEvent.objectList[obElement.bxEventIndex] = obElement;
		}
		
		if (!jsEvent.objectEventList[obElement.bxEventIndex])
			jsEvent.objectEventList[obElement.bxEventIndex] = {};

		if (!jsEvent.objectEventList[obElement.bxEventIndex][event])
		{
			jsEvent.objectEventList[obElement.bxEventIndex][event] = [];
			
			if (obElement['on' + event]) 
				jsEvent.objectEventList[obElement.bxEventIndex][event].push(obElement['on' + event]);
			
			obElement['on' + event] = null;
			obElement['on' + event] = jsEvent.__eventManager;
		}
		
		jsEvent.objectEventList[obElement.bxEventIndex][event].push(obHandler);
	},
	
	removeEvent: function(obElement, event, obHandler)
	{
		if (obElement.bxEventIndex)
		{
			if (jsEvent.objectEventList[obElement.bxEventIndex][event])
			{
				for (var i=0; i<jsEvent.objectEventList[obElement.bxEventIndex][event].length; i++)
				{
					if (obHandler == jsEvent.objectEventList[obElement.bxEventIndex][event][i])
					{
						delete jsEvent.objectEventList[obElement.bxEventIndex][event][i];
						return;
					}
				}
			}
		}
	},
	
	removeAllHandlers: function(obElement, event)
	{
		if (obElement.bxEventIndex)
		{
			if (jsEvent.objectEventList[obElement.bxEventIndex][event])
			{
				// possible memory leak. must be checked;
				jsEvent.objectEventList[obElement.bxEventIndex][event] = [];
			}
		}
	},

	removeAllEvents: function(obElement)
	{
		if (obElement.bxEventIndex)
		{
			if (jsEvent.objectEventList[obElement.bxEventIndex])
			{
				// possible memory leak. must be checked;
				jsEvent.objectEventList[obElement.bxEventIndex] = [];
			}
		}
	},
	
	clearObject: function(obElement)
	{
		if (obElement.bxEventIndex)
		{
			if (jsEvent.objectEventList[obElement.bxEventIndex])
			{
				// possible memory leak. must be checked;
				delete jsEvent.objectEventList[obElement.bxEventIndex];
			}
			
			if (jsEvent.objectList[obElement.bxEventIndex])
			{
				// possible memory leak. must be checked;
				delete jsEvent.objectList[obElement.bxEventIndex];
			}
			
			delete obElement.bxEventIndex;
		}
	}
}

var jsAjaxHistory = {
	expected_hash: '',
	counter: 0,
	bInited: false,
	
	obFrame: null,
	obImage: null,
	bHashCollision: false,
	
	obTimer: null,
	
	__hide_object: function(ob)
	{
		ob.style.position = 'absolute';
		ob.style.top = '-1000px';
		ob.style.left = '-1000px';
		ob.style.height = '10px';
		ob.style.width = '10px';
	},
	
	init: function(node)
	{
		if (jsAjaxHistory.bInited) return;
		
		jsAjaxHistory.expected_hash = window.location.hash;

		if (!jsAjaxHistory.expected_hash || jsAjaxHistory.expected_hash == '#') jsAjaxHistory.expected_hash = '__bx_no_hash__';
		
		var obCurrentState = {'node': node, 'title':window.document.title, 'data': document.getElementById(node).innerHTML};
		var obNavChain = document.getElementById('navigation');
		if (null != obNavChain)
			obCurrentState.nav_chain = obNavChain.innerHTML;
		
		jsAjaxHistoryContainer.put(jsAjaxHistory.expected_hash, obCurrentState);

		jsAjaxHistory.obTimer = setTimeout(jsAjaxHistory.__hashListener, 500);
		
		if (jsAjaxUtil.IsIE())
		{
			jsAjaxHistory.obFrame = document.createElement('IFRAME');
			jsAjaxHistory.__hide_object(jsAjaxHistory.obFrame);
			
			document.body.appendChild(jsAjaxHistory.obFrame);
			
			jsAjaxHistory.obFrame.contentWindow.document.open();
			jsAjaxHistory.obFrame.contentWindow.document.write(jsAjaxHistory.expected_hash);
			jsAjaxHistory.obFrame.contentWindow.document.close();
			jsAjaxHistory.obFrame.contentWindow.document.title = window.document.title;
		}
		else if (jsAjaxUtil.IsOpera())
		{
			jsAjaxHistory.obImage = document.createElement('IMG');
			jsAjaxHistory.__hide_object(jsAjaxHistory.obImage);
			
			document.body.appendChild(jsAjaxHistory.obImage);
			
			jsAjaxHistory.obImage.setAttribute('src', 'javascript:location.href = \'javascript:jsAjaxHistory.__hashListener();\';');
		}
		
		jsAjaxHistory.bInited = true;
	},

	__hashListener: function()
	{
		if (jsAjaxHistory.obTimer)
		{
			window.clearTimeout(jsAjaxHistory.obTimer);
			jsAjaxHistory.obTimer = null;
		}
	
		if (null != jsAjaxHistory.obFrame)
			var current_hash = jsAjaxHistory.obFrame.contentWindow.document.body.innerText;
		else
			var current_hash = window.location.hash;

		if (!current_hash || current_hash == '#') current_hash = '__bx_no_hash__';
		
		if (current_hash.indexOf('#') == 0) current_hash = current_hash.substring(1);
		
		if (current_hash != jsAjaxHistory.expected_hash)
		{
			var state = jsAjaxHistoryContainer.get(current_hash);
			if (state)
			{
				document.getElementById(state.node).innerHTML = state.data;
				jsAjaxUtil.UpdatePageTitle(state.title);
				if (state.nav_chain) 
					jsAjaxUtil.UpdatePageNavChain(state.nav_chain);
				
				jsAjaxHistory.expected_hash = current_hash;
				if (null != jsAjaxHistory.obFrame)
				{
					var __hash = current_hash == '__bx_no_hash__' ? '' : current_hash;
					if (window.location.hash != __hash && window.location.hash != '#' + __hash)
						window.location.hash = __hash;
				}
			}
		}
		
		jsAjaxHistory.obTimer = setTimeout(jsAjaxHistory.__hashListener, 500);
	},

	put: function(node, new_hash)
	{
		//alert(new_hash);
		var state = {
			'node': node,
			'title': window.document.title,
			'data': document.getElementById(node).innerHTML
		};
		
		var obNavChain = document.getElementById('navigation');
		if (obNavChain)
			state.nav_chain = obNavChain.innerHTML;
		
		//var new_hash = '#cnt' + (++jsAjaxHistory.counter);
		jsAjaxHistoryContainer.put(new_hash, state);
		jsAjaxHistory.expected_hash = new_hash;

		window.location.hash = jsAjaxUtil.urlencode(new_hash);

		if (null != jsAjaxHistory.obFrame)
		{
			jsAjaxHistory.obFrame.contentWindow.document.open();
			jsAjaxHistory.obFrame.contentWindow.document.write(new_hash);
			jsAjaxHistory.obFrame.contentWindow.document.close();
			jsAjaxHistory.obFrame.contentWindow.document.title = state.title;
		}
	},

	checkRedirectStart: function(param_name, param_value)
	{
		var current_hash = window.location.hash;
		if (current_hash.substring(0, 1) == '#') current_hash = current_hash.substring(1);
		
		if (current_hash.substring(0, 5) == 'view/')
		{
			jsAjaxHistory.bHashCollision = true;
			document.write('<' + 'div id="__ajax_hash_collision_' + param_value + '" style="display: none;">');
		}
	},
	
	checkRedirectFinish: function(param_name, param_value)
	{
		document.write('</div>');
		
		var current_hash = window.location.hash;
		if (current_hash.substring(0, 1) == '#') current_hash = current_hash.substring(1);
		
		jsEvent.addEvent(window, 'load', function () 
		{
			//alert(current_hash);
			if (current_hash.substring(0, 5) == 'view/')
			{
				var obColNode = document.getElementById('__ajax_hash_collision_' + param_value);
				var obNode = obColNode.firstChild;
				jsAjaxUtil.RemoveAllChild(obNode);
				obColNode.style.display = 'block';
				
				// IE, Opera and Chrome automatically modifies hash with urlencode, but FF doesn't ;-(
				if (!jsAjaxUtil.IsIE() && !jsAjaxUtil.IsOpera() && !jsAjaxUtil.IsSafari())
					current_hash = jsAjaxHistory.urlencode(current_hash);
				
				current_hash += (current_hash.indexOf('%3F') == -1 ? '%3F' : '%26') + param_name + '=' + param_value;
				
				var url = '/bitrix/tools/ajax_redirector.php?hash=' + current_hash; //jsAjaxHistory.urlencode(current_hash);
				jsAjaxUtil.InsertDataToNode(url, obNode, false);
			}
		});
	},
	
	urlencode: function(s)
	{
		if (window.encodeURIComponent)
			return encodeURIComponent(s);
		else if (window.encodeURI)
			return encodeURI(s);
		else
			return jsAjaxUtil.urlencode(s);
	}
}

var jsAjaxHistoryContainer = {
	arHistory: {},
	
	put: function(hash, state)
	{
		this.arHistory[hash] = state;
	},
	
	get: function(hash)
	{
		return this.arHistory[hash];
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

var jsAjax = new CAjax();

