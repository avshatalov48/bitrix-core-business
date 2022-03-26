(function(){

	if (BX.desktopUtils)
		return;

	BX.desktopUtils = function (){
		this.runningCheckTimeout = {};
		this.checkUrl = "http://127.0.0.1:20141/";
		this._openedBxLink = false;
	};

	BX.desktopUtils.prototype.canDownload = function()
	{
		return (
			typeof BXDesktopSystem !== 'undefined'
			&& typeof BXDesktopSystem.DownloadFile === 'function'
		);
	}

	BX.desktopUtils.prototype.downloadFile = function(url, name)
	{
		url = new URL(url, document.baseURI).href;

		return BXDesktopSystem.DownloadFile(url, name);
	}

	BX.desktopUtils.prototype.runningCheck = function(successCallback, failureCallback, successOnlyWithNewApp)
	{
		if (typeof(successCallback) == 'undefined')
		{
			return false;
		}
		if (typeof(failureCallback) == 'undefined')
		{
			failureCallback = function(){};
		}

		successOnlyWithNewApp = typeof (successOnlyWithNewApp) == 'undefined' || !successOnlyWithNewApp? false: true;

		var dateCheck = (+new Date());
		if (typeof (BXDesktopSystem) !== 'undefined')
		{
			if (BX.MessengerCommon.isDesktop())
			{
				failureCallback(false, dateCheck);
			}
			else
			{
				successCallback(true, dateCheck);
			}
			return true;
		}
		else if (typeof(BXIM) == 'undefined' || BX.MessengerCommon.isDesktop() || !BXIM.desktopStatus || BXIM.desktopVersion < 18)
		{
			failureCallback(false, dateCheck);
			return false;
		}
		else if (BXIM.desktopVersion < 35)
		{
			if (successOnlyWithNewApp)
			{
				failureCallback(false, dateCheck);
			}
			else
			{
				successCallback(true, dateCheck);
			}
			return true;
		}

		var alreadyRunFailureCallback = false;
		var checkElement = BX.create("img", {
			attrs : {
				"src" : this.checkUrl+"icon.png?"+dateCheck,
				"data-id": dateCheck
			},
			props : {className : "bx-messenger-out-of-view"},
			events : {
				"error" : function () {
					if (alreadyRunFailureCallback)
					{
						return;
					}

					var checkId = this.getAttribute('data-id');
					failureCallback(false, checkId);
					clearTimeout(BX.desktopUtils.runningCheckTimeout[checkId]);
					BX.remove(this);
				},
				"load" : function () {
					var checkId = this.getAttribute('data-id');
					successCallback(true, checkId);
					clearTimeout(BX.desktopUtils.runningCheckTimeout[checkId]);
					BX.remove(this);
				}
			}
		});
		document.body.appendChild(checkElement);
		this.runningCheckTimeout[dateCheck] = setTimeout(function(){
			failureCallback(false, dateCheck);
			clearTimeout(BX.desktopUtils.runningCheckTimeout[dateCheck]);
			BX.remove(this);

			alreadyRunFailureCallback = true;
		}, 500);

		return true;
	};

	BX.desktopUtils.prototype.goToBx = function (url)
	{
		if (typeof(BXIM) != 'undefined' && BXIM.desktopVersion >= 36 && !url.match(/^bx:\/\/v(\d)\//))
		{
			url = url.replace('bx://', 'bx://v'+BXIM.desktopProtocolVersion+'/' + location.hostname + '/');
		}
		this._setOpenedBxLink();
		location.href = url;
	};

	BX.desktopUtils.prototype._setOpenedBxLink = function()
	{
		this._openedBxLink = true;
		setTimeout(function()
		{
			BX.onCustomEvent("BXLinkOpened", []);
			this._openedBxLink = false;
		}.bind(this), 1000);
	};

	BX.desktopUtils.prototype.isChangedLocationToBx = function ()
	{
		return this._openedBxLink;
	};

	BX.desktopUtils.prototype.encodeParams = function(params)
	{
		if(!BX.type.isPlainObject(params))
			return '';

		var stringParams = '';
		var first = true;
		for (var i in params)
		{
			stringParams = stringParams+(first ? '' : '!!')+i+'!!'+params[i];
			first = false;
		}
		return stringParams;
	};

	BX.desktopUtils.prototype.decodeParams = function(encodedParams)
	{
		var result = {};
		if(!BX.type.isNotEmptyString(encodedParams))
			return result;

		var chunks = encodedParams.split('!!');
		for (var i = 0; i < chunks.length; i=i+2)
		{
			result[chunks[i]] = chunks[i+1];
		}
		return result;
	};

	BX.desktopUtils.prototype.encodeParamsJson = function(params)
	{
		if(!BX.type.isPlainObject(params))
			return '{}';

		var result;
		try
		{
			result = encodeURIComponent(JSON.stringify(params));
		}
		catch (e)
		{
			console.error("Could not encode params.", e);
			result = '{}';
		}
		return result;
	}

	BX.desktopUtils.prototype.decodeParamsJson = function(encodedParams)
	{
		var result = {};
		if(!BX.type.isNotEmptyString(encodedParams))
			return result;

		try
		{
			result = JSON.parse(decodeURIComponent(encodedParams));
		}
		catch (e)
		{
			console.error("Could not decode encoded params.", e);
		}

		return result;
	}

	BX.desktopUtils = new BX.desktopUtils();
})();