function PShowWaitMessage(container_id, bHide)
{
	if (bHide == null) bHide = false;
	PCloseWaitMessage(container_id, bHide);

	var obContainer = document.getElementById(container_id);

	if (obContainer)
	{
		if (window.ajaxMessages == null) window.ajaxMessages = {};
		if (!window.ajaxMessages.wait) window.ajaxMessages.wait = 'Wait...';

		obContainer.innerHTML = window.ajaxMessages.wait;

		if (bHide) obContainer.style.display = 'inline';
	}
}

function PCloseWaitMessage(container_id, bHide)
{
	if (bHide == null) bHide = false;

	var obContainer = document.getElementById(container_id);

	if (obContainer)
	{
		obContainer.innerHTML = '';

		if (bHide) obContainer.style.display = 'none';
	}

}

function JCPHttpRequest()
{
	this.Action = {}; //{TID:function(result){}}

	this.InitThread = function()
	{
		while (true)
		{
			var TID = 'TID' + Math.floor(Math.random() * 1000000);
			if (!this.Action[TID]) break;
		}

		return TID;
	}

	this.SetAction = function(TID, actionHandler)
	{
		this.Action[TID] = actionHandler;
	}

	this._Close = function(TID, httpRequest)
	{
		if (this.Action[TID]) this.Action[TID] = null;
//		httpRequest.onreadystatechange = null;
		httpRequest = null;
	}

	this._OnDataReady = function(TID, result)
	{
		if(this.Action[TID])
		{
			this.Action[TID](result);
		}
	}

	this._CreateHttpObject = function()
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

	this._SetHandler = function(TID, httpRequest)
	{
		var _this = this;

		function __handlerReadyStateChange()
		{
			//alert(httpRequest.readyState);
			if(httpRequest.readyState == 4)
			{
//				try
//				{
					var s = httpRequest.responseText;
					var code = [];
					var start;
					
					while((start = s.indexOf('<script>')) != -1)
					{
						var end = s.indexOf('</script>', start);
						if(end != -1)
						{
							code[code.length] = s.substr(start+8, end-start-8);
							s = s.substr(0, start) + s.substr(end+9);
						}
						else
						{
							s = s.substr(0, start) + s.substr(start+8);
						}
					}
					
					_this._OnDataReady(TID, s);

					for(var i in code)
						if(code[i] != '')
							eval(code[i]);
//				}
//				catch (e)
//				{
//					var w = window.open("about:blank");
//					w.document.write(httpRequest.responseText);
//					//w.document.close();
//				}

				_this._Close(TID, httpRequest);
			}
			//alert('done');
		}

		httpRequest.onreadystatechange = __handlerReadyStateChange;
	}

	this._MyEscape = function(str)
	{
		return escape(str).replace(/\+/g, '%2B');
	}

	this._PrepareData = function(arData, prefix)
	{
		var data = '';
		if (arData != null)
		{
			for(var i in arData)
			{
				if (data.length > 0) data += '&';
				var name = this._MyEscape(i);
				if(prefix)
					name = prefix + '[' + name + ']';
				if(typeof arData[i] == 'object')
					data += this._PrepareData(arData[i], name)
				else
					data += name + '=' + this._MyEscape(arData[i])
			}
		}
		return data;
	}

	this.Send = function(TID, url, arData)
	{
		if (arData != null)
			var data = this._PrepareData(arData);

		if (data.length > 0)
		{
			if (url.indexOf('?') == -1)
		 		url += '?' + data;
		 	else
				url += '&' + data;	
		}

		var httpRequest = this._CreateHttpObject();
		if(httpRequest)
		{
			httpRequest.open("GET", url, true);
			this._SetHandler(TID, httpRequest);
			return httpRequest.send("");
  		}
  		return false;
	}

	this.Post = function(TID, url, arData)
	{
		var data = '';

		if (arData != null)
			data = this._PrepareData(arData);

		var httpRequest = this._CreateHttpObject();
		if(httpRequest)
		{
			httpRequest.open("POST", url, true);
			this._SetHandler(TID, httpRequest);
			httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			return httpRequest.send(data);
  		}
  		return false;
	}

	this.__migrateSetHandler = function(obForm, obFrame, handler)
	{
		function __formResultHandler()
		{
			if (!obFrame.contentWindow.document || obFrame.contentWindow.document.body.innerHTML.length == 0) return;
			if (null != handler) 
				handler(obFrame.contentWindow.document.body.innerHTML);
			
			// uncomment next to return form back after first query
			
			/*
			obForm.target = '';
			obForm.removeChild(obForm.lastChild);
			document.body.removeChild(obFrame);
			*/
		}
		
		if (obFrame.addEventListener) 
		{
			obFrame.addEventListener("load", __formResultHandler, false);
		}
		else if (obFrame.attachEvent) 
		{
			obFrame.attachEvent("onload", __formResultHandler);
		}
	}
	
	this.MigrateFormToAjax = function(obForm, handler)
	{
		if (!obForm) 
			return;
		if (obForm.target && obForm.target.substring(0, 5) == 'AJAX')
			return;
		
		var obAJAXIndicator = document.createElement('INPUT');
		obAJAXIndicator.type = 'hidden';
		obAJAXIndicator.name = 'AJAX_CALL';
		obAJAXIndicator.value = 'Y';
		
		obForm.appendChild(obAJAXIndicator);
		
		var frameName = 'AJAX_' + Math.round(Math.random() * 100000);
		
		if (document.getElementById('frameName'))
			var obFrame = document.getElementById('frameName');
		else
		{
			if (currentBrowserDetected == 'IE')
				var obFrame = document.createElement('<iframe name="' + frameName + '"></iframe>');
			else
				var obFrame = document.createElement('IFRAME');
			
			obFrame.style.display = 'none';
			obFrame.src = '';
			obFrame.id = frameName;
			obFrame.name = frameName;
			
			document.body.appendChild(obFrame);
		}
		
		obForm.target = frameName;
		
		this.__migrateSetHandler(obForm, obFrame, handler);
	}
}

var CPHttpRequest = new JCPHttpRequest();

var currentBrowserDetected = "";
if (window.opera)
	currentBrowserDetected = "Opera";
else if (navigator.userAgent)
{
	if (navigator.userAgent.indexOf("MSIE") != -1)
		currentBrowserDetected = "IE";
	else if (navigator.userAgent.indexOf("Firefox") != -1)
		currentBrowserDetected = "Firefox";
}
		

