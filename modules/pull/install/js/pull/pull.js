/* PULL manager JS class */

;(function(window){

	if (!window.BX)
	{
		if (typeof(console) == 'object') console.error('PULL notice: bitrix core not loaded');
		return;
	}
	if (window.BX.PULL)
	{
		if (typeof(console) == 'object') console.error('PULL notice: script is already loaded');
		return;
	}

	var BX = window.BX,
	_revision = 17, // api revision - check include.php
	_updateStateVeryFastCount = 0,
	_updateStateFastCount = 0,
	_updateStateStep = 60,
	_updateStateTimeout = null,
	_updateStateStatusTimeout = null,
	_updateStateSend = false,
	_pullTryAfterBxLink = false,
	_pullTryConnect = false,
	_pullPath = null,
	_pullMethod = 'PULL',
	_pullWithHeaders = true,
	_pullCapturePullEvent = false,
	_pullCapturePullEventStatus = false,
	_pullGetPullEventFunctionStatus = false,
	_pullTimeConfig = 0,
	_pullTimeConfigShared = 0,
	_pullTimeConst = (new Date(2022, 2, 19)).toUTCString(),
	_pullTime = _pullTimeConst,
	_pullTag = 1,
	_pullTimeout = 60,
	_pullMid = null,
	_watchTag = {},
	_watchTimeout = null,
	_channelID = null,
	_channelClearReason = 0,
	_channelClear = null,
	_channelLastID = 0,
	_channelStack = {},
	_WS = null,
	_wsPath = '',
	_wsSupport = false,
	_wsConnected = false,
	_wsTryReconnect = 0,
	_wsError1006Count = 0,
	_mobileMode = false,
	_lsSupport = false,
	_escStatus = false,
	_sendAjaxTry = 0,
	_confirm = null,
	_beforeUnload = false,
	_pathToAjax = '/bitrix/components/bitrix/pull.request/ajax.php?',

	_onBeforeUnload = BX.proxy(function(){
		_beforeUnload = true;
		_pullTryConnect = false;
		if (_WS) _WS.close(1000, "onbeforeunload");

		if (BX.PULL.returnPrivateVar('_pullTryAfterBxLink'))
		{
			BX.PULL.tryConnectDelay();
		}

	}, this);

	BX.PULL = function() {};

	BX.PULL.start = function(params)
	{
		if (typeof(params) != "object")
		{
			params = {};
		}

		_pullTryConnect = true;

		_mobileMode = false;
		if (params.MOBILE == 'Y')
			_mobileMode = true;

		_lsSupport = true;
		if (params.LOCAL_STORAGE == 'N')
			_lsSupport = false;

		if (params.HEADERS == 'N')
			_pullWithHeaders = false;

		if (_lsSupport && BX.localStorage.get('prs') !== null)
		{
			_pullTryConnect = false;
		}

		_wsSupport = true;
		if (params.WEBSOCKET == 'N')
			_wsSupport = false;

		_userId = BX.message('USER_ID');
		if (typeof(params.USER_ID) != 'undefined')
			_userId = params.USER_ID;

		BX.bind(window, "offline", function(){
			_pullTryConnect = false;
			if (_WS) _WS.close(1000, "offline");
		});

		BX.bind(window, "online", function(){
			if (!BX.PULL.tryConnect())
				BX.PULL.updateState('10', true);
		});

		if (BX.browser.IsFirefox())
		{
			BX.bind(window, "keypress", function(event){
				if (event.keyCode == 27)
					_escStatus = true;
			});
		}

		if (!BX.PULL.supportWebSocket())
			_wsSupport = false;

		if (params.PATH_COMMAND)
		{
			BX.PULL.setAjaxPath(params.PATH_COMMAND);
		}

		if (params.CHANNEL_ID)
		{
			_channelID = params.CHANNEL_ID;
			_pullPath = params.PATH;
			_wsPath = params.PATH_WS;
			_pullMethod = params.METHOD;

			params.CHANNEL_DT = params.CHANNEL_DT.toString().split('/');
			_pullTimeConfig = params.CHANNEL_DT[0];
			_pullTimeConfigShared = params.CHANNEL_DT[1]? params.CHANNEL_DT[1]: params.CHANNEL_DT[0];

			_pullTimeConfig = parseInt(_pullTimeConfig)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET'));
			_pullTimeConfigShared = parseInt(_pullTimeConfigShared)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET'));
			_channelLastID = parseInt(params.LAST_ID);
		}

		if (!BX.browser.SupportLocalStorage())
			_lsSupport = false;

		if (_lsSupport)
		{
			BX.addCustomEvent(window, "onLocalStorageSet", BX.PULL.storageSet);
			BX.localStorage.set('pset', {'CHANNEL_ID': _channelID, 'LAST_ID': _channelLastID, 'PATH': _pullPath, 'PATH_WS': _wsPath, 'TIME_LAST_GET': _pullTimeConfig, 'TIME_LAST_GET_SHARED': _pullTimeConfigShared, 'METHOD': _pullMethod}, 5);
		}

		BX.addCustomEvent("onImError", function(error) {
			if (error == 'AUTHORIZE_ERROR')
				_sendAjaxTry++;
		});

		BX.addCustomEvent("onPullError", BX.delegate(function(error) {
			if (error == 'AUTHORIZE_ERROR')
			{
				_pullTryConnect = false;
			}
		}, this));

		if (BX.desktop)
		{
			BX.desktop.addCustomEvent("BXLoginSuccess", function (){
				if (_WS) _WS.close(1000, "desktop_login_success");
			});
		}

		BX.PULL.initBeforeUnload();

		BX.onCustomEvent(window, 'onPullInit', []);

		BX.PULL.expireConfig();
		BX.PULL.init();
	}

	BX.PULL.init = function()
	{
		BX.PULL.updateState('init');
		BX.PULL.updateWatch();
	}

	BX.PULL.getNowDate = function(today)
	{
		var currentDate = (new Date);
		if (today == true)
			currentDate = (new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate(), 0, 0, 0));

		return Math.round((+currentDate/1000))+parseInt(BX.message("USER_TZ_OFFSET"));
	};

	BX.PULL.setTryAfterBxLink = function(result)
	{
		_pullTryAfterBxLink = result? true: false;
	}

	BX.PULL.initBeforeUnload = function()
	{
		BX.unbind(window, "beforeunload", _onBeforeUnload);
		BX.bind(window, "beforeunload", _onBeforeUnload);
	}

	BX.PULL.tryConnectDelay = function()
	{
		setTimeout(function(){
			BX.PULL.setPrivateVar('_pullTryConnect', false);
			BX.PULL.tryConnect();
			BX.PULL.setPrivateVar('_pullTryAfterBxLink', false);
		}, 1000);
	}

	BX.PULL.expireConfig = function()
	{
		if (!_channelID)
			return false;

		clearTimeout(_channelClear);
		_channelClear = setTimeout(BX.PULL.expireConfig, 60000);

		if (_channelID && _pullMethod!='PULL' && _pullTimeConfig+43200 < Math.round(+(new Date)/1000)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET')))
		{
			_channelClearReason = 1;
			_channelID = null;
			if (_WS) _WS.close(1000, "expire_config_1");
		}
		else if (_channelID && _pullMethod!='PULL' && _pullTimeConfigShared+43200+((Math.floor(Math.random() * (61)) + 10)*1000) < Math.round(+(new Date)/1000)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET')))
		{
			_channelClearReason = 1;
			_channelID = null;
			if (_WS) _WS.close(1000, "expire_config_2");
		}
	}

	BX.PULL.tryConnect = function()
	{
		if (_pullTryConnect)
			return false;

		_pullTryConnect = true;
		BX.PULL.init();

		return true;
	}

	BX.PULL.getChannelID = function(code, withoutCache, send)
	{
		if (!_pullTryConnect)
			return false;

		send = send != false;
		withoutCache = withoutCache == true;
		code = typeof(code) == 'undefined'? '0': code;

		BX.ajax({
			url: _pathToAjax+'GET_CHANNEL&V='+_revision+'&CR='+_channelClearReason+'&CODE='+code.toUpperCase()+(_mobileMode? '&MOBILE':''),
			method: 'POST',
			skipAuthCheck: true,
			dataType: 'json',
			lsId: 'PULL_GET_CHANNEL',
			lsTimeout: 1,
			timeout: 30,
			data: {'PULL_GET_CHANNEL' : 'Y', 'SITE_ID': (BX.message.SITE_ID? BX.message('SITE_ID'): ''), 'MOBILE': _mobileMode? 'Y':'N', 'CACHE': withoutCache? 'N': 'Y', 'PULL_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				_channelClearReason = 0;
				if (send && BX.localStorage.get('pgc') === null)
					BX.localStorage.set('pgc', withoutCache, 1);

				if (typeof(data) == 'object' && data.ERROR == '')
				{
					if (data.REVISION && !BX.PULL.checkRevision(data.REVISION))
						return false;

					_channelID = data.CHANNEL_ID;
					_pullPath = data.PATH;
					_wsPath = data.PATH_WS;
					_pullMethod = data.METHOD;

					var CHANNEL_DT = data.CHANNEL_DT.toString().split('/');
					_pullTimeConfig = CHANNEL_DT[0];
					_pullTimeConfigShared = CHANNEL_DT[1]? CHANNEL_DT[1]: CHANNEL_DT[0];

					_pullTimeConfig = parseInt(_pullTimeConfig)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET'));
					_pullTimeConfigShared = parseInt(_pullTimeConfigShared)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET'));
					_channelLastID = _pullMethod=='PULL'? data.LAST_ID: _channelLastID;
					data.TIME_LAST_GET = _pullTimeConfig;
					data.TIME_LAST_GET_SHARED = _pullTimeConfigShared;
					BX.PULL.updateState('11');
					BX.PULL.expireConfig();
					if (_lsSupport)
						BX.localStorage.set('pset', data, 600);
				}
				else
				{
					_sendAjaxTry++;
					_channelClearReason = 2;
					_channelID = null;
					clearTimeout(_updateStateStatusTimeout);
					BX.onCustomEvent(window, 'onPullStatus', ['offline']);
					if (typeof(data) == 'object' && data.BITRIX_SESSID)
					{
						BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					}

					if (typeof(data) == 'object' && data.ERROR == 'SESSION_ERROR')
					{
						clearTimeout(_updateStateTimeout);
						_updateStateTimeout = setTimeout(function(){BX.PULL.updateState('12', true)}, (_sendAjaxTry < 2? 2000: BX.PULL.tryConnectTimeout()));
						BX.onCustomEvent(window, 'onPullError', [data.ERROR, data.BITRIX_SESSID]);
					}
					else if (typeof(data) == 'object' && data.ERROR == 'AUTHORIZE_ERROR')
					{
						BX.onCustomEvent(window, 'onPullError', [data.ERROR]);
					}
					else
					{
						clearTimeout(_updateStateTimeout);
						_updateStateTimeout = setTimeout(function(){BX.PULL.updateState('31', true)}, BX.PULL.tryConnectTimeout());
						BX.onCustomEvent(window, 'onPullError', ['NO_DATA']);
					}
					if (send && typeof(console) == 'object')
					{
						var text = "\n========= PULL ERROR ===========\n"+
									"Error type: getChannel error\n"+
									"Error: "+data.ERROR+"\n"+
									"\n"+
									"Data array: "+JSON.stringify(data)+"\n"+
									"================================\n\n";
						console.warn(text);
					}
				}
			}, this),
			onfailure: BX.delegate(function(data)
			{
				_sendAjaxTry++;
				_channelClearReason = 3;
				_channelID = null;
				clearTimeout(_updateStateStatusTimeout);
				BX.onCustomEvent(window, 'onPullStatus', ['offline']);
				if (data == "timeout")
				{
					clearTimeout(_updateStateTimeout);
					_updateStateTimeout = setTimeout(function(){
						BX.PULL.updateState('1')
					}, 10000);
				}
				else
				{
					if (typeof(data) == 'object' && data.ERROR == 'auth')
					{
						BX.onCustomEvent(window, 'onPullError', ['AUTHORIZE_ERROR']);
					}
					if (typeof(console) == 'object')
					{
						var text = "\n========= PULL ERROR ===========\n"+
									"Error type: getChannel onfailure\n"+
									"Error: "+data.ERROR+"\n"+
									"\n"+
									"Data array: "+JSON.stringify(data)+"\n"+
									"================================\n\n";
						console.warn(text);
					}
					clearTimeout(_updateStateTimeout);
					_updateStateTimeout = setTimeout(function(){BX.PULL.updateState('14', true)}, BX.PULL.tryConnectTimeout());
				}
			}, this)
		});
	};

	BX.PULL.updateState = function(code, force)
	{
		if (!_pullTryConnect || _updateStateSend)
			return false;

		code = typeof(code) == 'undefined'? '': code;

		var supportWebsocket = _wsSupport && !BX.localStorage.get('pbws');

		if (_channelID == null || _pullPath == null || supportWebsocket && _wsPath === null)
		{
			clearTimeout(_updateStateTimeout);
			_updateStateTimeout = setTimeout(function(){
				if (code.length>0)
					BX.PULL.getChannelID(code+(_channelID == null? '-02': '-03'));
				else
					BX.PULL.getChannelID(_channelID == null? '2': '3');
			}, Math.floor(Math.random() * (151)) + 50)
		}
		else
		{
			if (supportWebsocket && _wsPath && _wsPath.length > 1 && _pullMethod != 'PULL')
				BX.PULL.connectWebSocket();
			else
				BX.PULL.connectPull(force);
		}
	};

	BX.PULL.connectWebSocket = function()
	{
		_updateStateSend = true;

		var wsPath = _wsPath.replace('#DOMAIN#', location.hostname);
		var _wsServer = wsPath+(_pullTag != null? "&tag="+_pullTag:"")+(_pullTime != null? "&time="+_pullTime:"")+(_pullMid !== null ? "&mid="+_pullMid : "");
		try
		{
			BX.onCustomEvent(window, 'onPullStatus', ['connect']);
			_WS = new WebSocket(_wsServer);
		}
		catch(e)
		{
			if (typeof(console) == 'object')
			{
				var text = "\n========= PULL ERROR ===========\n"+
							"Error type: websocket connect\n"+
							"Error: "+(e.code? 'code: '+e.code: '')+" "+(e.name? 'name: '+e.name: '')+"\n"+
							"Desc: "+e+"\n"+
							"\n"+
							"Connect CHANNEL_ID: "+_channelID+"\n"+
							"================================\n\n";
				console.warn(text);
				console.error(e);
			}
			_wsPath = null;
			_updateStateSend = false;

			BX.onCustomEvent(window, 'onPullError', ['RECONNECT', 1006]);
			if (_wsError1006Count >= 5)
			{
				BX.localStorage.set('pbws', true, 300);
				_wsError1006Count = 0;
			}
			_wsError1006Count++;

			clearTimeout(_updateStateTimeout);
			_updateStateTimeout = setTimeout(function(){
				BX.PULL.updateState('33');
			}, BX.PULL.tryConnectTimeout());

			return false;
		}

		_WS.onopen = function() {
			_wsConnected = true;
			_wsError1006Count = 0;
			clearTimeout(_updateStateStatusTimeout);
			BX.onCustomEvent(window, 'onPullStatus', ['online']);
		};
		_WS.onclose = function(data)
		{
			var code = typeof(data.code) != 'undefined'? data.code: 'NA';
			var reason = "";
			if (data.reason)
			{
				try
				{
					reason = JSON.parse(data.reason);
				}
				catch(e)
				{
					reason = {'reason': data.reason};
				}
			}

			var neverConnect = false;
			_updateStateSend = false;
			var sendConnectRequest = true;
			// if user never connected

			if (!_wsConnected)
			{
				neverConnect = true;
				_channelID = null;
				if (_wsTryReconnect == 1)
				{
					BX.PULL.updateState('ws-'+code+'-1');
				}
				else if (_wsTryReconnect <= 3)
				{
					clearTimeout(_updateStateTimeout);
					_updateStateTimeout = setTimeout(function(){
						BX.PULL.updateState('ws-'+code+'-2');
					}, 10000);
				}
				else
				{
					if (code == 1006 || code == 1008)
					{
						BX.localStorage.set('pbws', true, 600);
					}
					clearTimeout(_updateStateTimeout);
					_updateStateTimeout = setTimeout(function(){
						BX.PULL.updateState('ws-'+code+'-3');
					}, BX.PULL.tryConnectTimeout());
				}
				if (code == 1006)
				{
					if (_wsError1006Count >= 5)
					{
						BX.localStorage.set('pbws', true, 300);
						_wsError1006Count = 0;
					}
					_wsError1006Count++;
				}
			}
			else
			{
				_wsConnected = false;
				if (data.wasClean && reason && reason.http_status == 403)
				{
					_sendAjaxTry++;
					_channelID = null;
					_channelLastID = 0;
					_channelStack = {};

					if (_sendAjaxTry >= 5)
					{
						BX.localStorage.set('pbws', true, 86400);
					}

					clearTimeout(_updateStateTimeout);
					_updateStateTimeout = setTimeout(function(){
						BX.PULL.getChannelID('ws-'+code+'-6', true)
					}, (_sendAjaxTry < 2? 1000: BX.PULL.tryConnectTimeout()));
				}
				else
				{
					clearTimeout(_updateStateTimeout);
					_updateStateTimeout = setTimeout(function(){
						BX.PULL.updateState('ws-'+code+'-5-'+data.wasClean);
					}, (_sendAjaxTry < 2 && data.wasClean === true ? 1000: BX.PULL.tryConnectTimeout()));
				}
			}

			if (_beforeUnload)
			{
				_beforeUnload = false;
			}
			else
			{
				BX.onCustomEvent(window, 'onPullError', ['RECONNECT', code]);

				if (typeof(console) == 'object')
				{
					var text = "\n========= PULL INFO ===========\n"+
								"time: " + new Date() + "\n"+
								"type: websocket close\n"+
								"code: "+code+"\n"+
								"clean: "+(data.wasClean?'Y':'N')+"\n"+
								"never connect: "+(neverConnect?'Y':'N')+"\n"+
								"send connect request: "+(sendConnectRequest?'Y':'N')+"\n"+
								(reason? "reason: "+JSON.stringify(reason)+"\n": "")+
								"\n"+
								"Data array: "+JSON.stringify(data)+"\n"+
								"================================\n\n";
					console.warn(text);
				}
			}
		};
		_WS.onmessage = function(event)
		{
			var messageCount = 0;
			var dataArray = event.data.match(/#!NGINXNMS!#(.*?)#!NGINXNME!#/gm);
			if (dataArray != null)
			{
				_wsTryReconnect = 0;
				_sendAjaxTry = 0;
				for (var i = 0; i < dataArray.length; i++)
				{
					dataArray[i] = dataArray[i].substring(12, dataArray[i].length-12);
					if (dataArray[i].length <= 0)
						continue;

					var message = BX.parseJSON(dataArray[i]);

					if (message.id)
					{
						message.id = parseInt(message.id);
						message.mid = message.mid? message.mid: (message.text.channel? message.text.channel: message.time)+message.id;
						if (!_channelStack[message.mid])
						{
							_channelStack[message.mid] = true;

							if (_channelLastID < message.id)
							{
								_channelLastID = message.id;
							}

							BX.PULL.executeMessages([message.text]);
						}
						else if (typeof(console) == 'object')
						{
							console.warn('PULL: message #'+message.id+' (mid: '+message.mid+') is already received');
						}
					}
					if (message.tag)
					{
						_pullTag = message.tag;
					}
					if (message.time)
					{
						_pullTime = message.time;
					}
					if (message.mid)
					{
						_pullMid = message.mid;
					}
					messageCount++;
				}
			}
			if (_channelID == null)
			{
				if (_WS) _WS.close(1000, "onmessage");
			}
		};
		_WS.onerror = function()
		{
			_updateStateSend = false;
			_wsTryReconnect++;
		};
	}

	BX.PULL.connectPull = function(force)
	{
		force = force == true;
		clearTimeout(_updateStateTimeout);
		_updateStateTimeout = setTimeout(function(){
			if (!_pullPath || typeof(_pullPath) != "string" || _pullPath.length <= 32)
			{
				_pullPath = null;

				clearTimeout(_updateStateTimeout);
				_updateStateTimeout = setTimeout(function(){
					BX.PULL.updateState('17');
				}, 10000);

				return false;
			}

			_updateStateStatusTimeout = setTimeout(function(){
				BX.onCustomEvent(window, 'onPullStatus', ['online']);
			}, 5000);

			_updateStateSend = true;

			var headersForRequest = [];
			if (_pullWithHeaders)
			{
				headersForRequest = [
					{'name':'If-Modified-Since', 'value': _pullTime},
					{'name':'If-None-Match', 'value': _pullTag}
				];
			}

			BX.onCustomEvent(window, 'onPullStatus', ['connect']);

			var pullPath = _pullPath.replace('#DOMAIN#', location.hostname);
			var _ajax = BX.ajax({
				url: _pullMethod=='PULL'? pullPath: (pullPath+(_pullTag != null? "&tag="+_pullTag:"")+(_pullTime != null? "&time="+_pullTime:"")+(_pullMid !== null ? "&mid="+_pullMid : ""))+"&rnd="+(+new Date),
				skipAuthCheck: true,
				skipBxHeader: _pullMethod != 'PULL',
				method: _pullMethod=='PULL'?'POST':'GET',
				dataType: _pullMethod=='PULL'?'json':'html',
				timeout: _pullTimeout,
				headers: headersForRequest,
				data: _pullMethod=='PULL'? {'PULL_UPDATE_STATE' : 'Y', 'CHANNEL_ID': _channelID, 'CHANNEL_LAST_ID': _channelLastID, 'SITE_ID': (BX.message.SITE_ID? BX.message('SITE_ID'): ''), 'PULL_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}: {},
				onsuccess: function(data)
				{
					clearTimeout(_updateStateStatusTimeout);
					_updateStateSend = false;
					if (_WS) _WS.close(1000, "ajax_onsuccess");

					if (_pullMethod=='PULL' && typeof(data) == "object")
					{
						if (data.ERROR == "")
						{
							BX.onCustomEvent(window, 'onPullStatus', ['online']);

							_sendAjaxTry = 0;
							BX.PULL.executeMessages(data.MESSAGE);
							if (_lsSupport)
								BX.localStorage.set('pus', {'MESSAGE':data.MESSAGE}, 5);
						}
						else
						{
							clearTimeout(_updateStateStatusTimeout);
							BX.onCustomEvent(window, 'onPullStatus', ['offline']);

							if (data && data.BITRIX_SESSID)
							{
								BX.message({'bitrix_sessid': data.BITRIX_SESSID});
							}

							if (data.ERROR == 'SESSION_ERROR')
							{
								BX.onCustomEvent(window, 'onPullError', [data.ERROR, data.BITRIX_SESSID]);
							}
							else
							{
								BX.onCustomEvent(window, 'onPullError', [data.ERROR]);
							}
							if (typeof(console) == 'object')
							{
								var text = "\n========= PULL ERROR ===========\n"+
											"Error type: updateState error\n"+
											"Error: "+(data && data.ERROR? data.ERROR: 'unknown')+"\n"+
											"\n"+
											"Connect CHANNEL_ID: "+_channelID+"\n"+
											"Connect PULL_PATH: "+_pullPath+"\n"+
											"\n"+
											"Data array: "+JSON.stringify(data)+"\n"+
											"================================\n\n";
								console.warn(text);
							}
							_channelClearReason = 5;
							_channelID = null;
						}
						if (_channelID != null && _lsSupport)
							BX.localStorage.set('pset', {'CHANNEL_ID': _channelID, 'LAST_ID': _channelLastID, 'PATH': _pullPath, 'PATH_WS': _wsPath, 'TAG': _pullTag, 'MID': _pullMid, 'TIME': _pullTime, 'TIME_LAST_GET': _pullTimeConfig, 'TIME_LAST_GET_SHARED': _pullTimeConfigShared, 'METHOD': _pullMethod}, 600);

						BX.PULL.setUpdateStateStep();
					}
					else
					{
						if (data.length > 0)
						{
							var messageCount = 0;
							_sendAjaxTry = 0;

							var dataArray = data.match(/#!NGINXNMS!#(.*?)#!NGINXNME!#/gm);
							if (dataArray != null)
							{
								for (var i = 0; i < dataArray.length; i++)
								{
									dataArray[i] = dataArray[i].substring(12, dataArray[i].length-12);
									if (dataArray[i].length <= 0)
										continue;

									var message = BX.parseJSON(dataArray[i]);

									if (message.id)
									{
										message.id = parseInt(message.id);
										message.mid = message.mid? message.mid: (message.text.channel? message.text.channel: message.time)+message.id;
										if (!_channelStack[message.mid])
										{
											_channelStack[message.mid] = true;

											if (_channelLastID < message.id)
											{
												_channelLastID = message.id;
											}
											BX.PULL.executeMessages([message.text]);
										}
										else if (typeof(console) == 'object')
										{
											console.warn('PULL: message #'+message.id+' (mid: '+message.mid+') is already received');
										}
									}
									else
									{
										if (typeof(console) == 'object')
										{
											var text = "\n========= PULL ERROR ===========\n"+
														"Error type: updateState parse\n"+
														"\n"+
														"Connect CHANNEL_ID: "+_channelID+"\n"+
														"Connect PULL_PATH: "+_pullPath+"\n"+
														"\n"+
														"Data string: "+dataArray[i]+"\n"+
														"================================\n\n";
											console.warn(text);
										}
										_channelClearReason = 7;
										_channelID = null;
										clearTimeout(_updateStateStatusTimeout);
										BX.onCustomEvent(window, 'onPullStatus', ['offline']);
									}
									if (message.tag)
									{
										_pullTag = message.tag;
									}
									if (message.time)
									{
										_pullTime = message.time;
									}
									if (message.mid)
									{
										_pullMid = message.mid;
									}
									messageCount++;
								}
							}
							else
							{
								if (typeof(console) == 'object')
								{
									var text = "\n========= PULL ERROR ===========\n"+
												"Error type: updateState error getting message\n"+
												"\n"+
												"Connect CHANNEL_ID: "+_channelID+"\n"+
												"Connect PULL_PATH: "+_pullPath+"\n"+
												"\n"+
												"Data string: "+data+"\n"+
												"================================\n\n";
									console.warn(text);
								}
								_channelClearReason = 8;
								_channelID = null;
								clearTimeout(_updateStateStatusTimeout);
								BX.onCustomEvent(window, 'onPullStatus', ['offline']);
							}
							if (messageCount > 0 || _ajax && _ajax.status == 0)
							{
								BX.PULL.updateState(messageCount > 0? '19': '20');
							}
							else
							{
								_channelClearReason = 9;
								_channelID = null;
								clearTimeout(_updateStateTimeout);
								_updateStateTimeout = setTimeout(function(){BX.PULL.updateState('21')}, 10000);
							}
						}
						else
						{
							if (_ajax && (_ajax.status == 304 || _ajax.status == 0))
							{
								if (_ajax.status == 0)
								{
									if (_escStatus)
									{
										_escStatus = false;
										BX.PULL.updateState('22-3');
									}
									else
									{
										_updateStateTimeout = setTimeout(function(){
											BX.PULL.updateState('22-2');
										}, 30000);
									}
								}
								else
								{
									try {
										var expires = _ajax.getResponseHeader("Expires");
										if (expires === "Thu, 01 Jan 1973 11:11:01 GMT")
										{
											var lastMessageId = _ajax.getResponseHeader("Last-Message-Id");
											if (_pullMid === null && lastMessageId && lastMessageId.length > 0)
											{
												_pullMid = lastMessageId;
											}
										}
									}
									catch(event) {

									}

									BX.PULL.updateState('22-1');
								}
							}
							else if (_ajax && (_ajax.status == 502 || _ajax.status == 500))
							{
								clearTimeout(_updateStateStatusTimeout);
								BX.onCustomEvent(window, 'onPullStatus', ['offline']);
								_sendAjaxTry++;
								_channelClearReason = 10;
								_channelID = null;
								clearTimeout(_updateStateTimeout);
								_updateStateTimeout = setTimeout(function(){
									BX.PULL.updateState('23');
								}, BX.PULL.tryConnectTimeout());
							}
							else
							{
								clearTimeout(_updateStateStatusTimeout);
								BX.onCustomEvent(window, 'onPullStatus', ['offline']);

								_sendAjaxTry++;
								_channelClearReason = 11;
								_channelID = null;
								var timeout = BX.PULL.tryConnectTimeout();
								var code = (_ajax && typeof(_ajax.status) != 'undefined'? _ajax.status: 'NaN');
								clearTimeout(_updateStateTimeout);
								_updateStateTimeout = setTimeout(function(){
									BX.PULL.updateState('24-'+code+'-'+(timeout/1000));
								}, timeout);
							}
						}
					}
				},
				onfailure: function(data)
				{
					clearTimeout(_updateStateStatusTimeout);
					BX.onCustomEvent(window, 'onPullStatus', ['offline']);
					_updateStateSend = false;
					_sendAjaxTry++;
					if (_WS) _WS.close(1000, "ajax_onfailure");
					if (data == "timeout")
					{
						if (_pullMethod=='PULL')
							BX.PULL.setUpdateStateStep();
						else
							BX.PULL.updateState('25');
					}
					else if (_ajax && (_ajax.status == 403 || _ajax.status == 404 || _ajax.status == 400))
					{
						if (_ajax.status == 403)
						{
							_channelLastID = 0;
							_channelStack = {};
						}

						_channelClearReason = 12;
						_channelID = null;
						clearTimeout(_updateStateTimeout);
						_updateStateTimeout = setTimeout(function(){
							BX.PULL.getChannelID('7-'+_ajax.status, _ajax.status == 403)
						}, (_sendAjaxTry < 2? 50: BX.PULL.tryConnectTimeout()));
					}
					else if (_ajax && (_ajax.status == 500 || _ajax.status == 502))
					{
						_channelClearReason = 13;
						_channelID = null;
						clearTimeout(_updateStateTimeout);
						_updateStateTimeout = setTimeout(function(){
							BX.PULL.getChannelID('8-'+_ajax.status)
						}, (_sendAjaxTry < 2? 50: BX.PULL.tryConnectTimeout()));
					}
					else
					{
						if (typeof(console) == 'object')
						{
							var text = "\n========= PULL ERROR ===========\n"+
										"Error type: updateState onfailure\n"+
										"\n"+
										"Connect CHANNEL_ID: "+_channelID+"\n"+
										"Connect PULL_PATH: "+_pullPath+"\n"+
										"\n"+
										"Data array: "+JSON.stringify(data)+"\n"+
										"================================\n\n";
							console.warn(text);
						}
						clearTimeout(_updateStateTimeout);
						if (_pullMethod=='PULL')
							_updateStateTimeout = setTimeout(BX.PULL.setUpdateStateStep, 10000);
						else
							_updateStateTimeout = setTimeout(function(){BX.PULL.updateState('26');}, 10000);
					}
				}
			});
		}, force? 150: (_pullMethod == 'PULL'? _updateStateStep: 0.3)*1000);
	}

	BX.PULL.extendWatch = function(tag, force)
	{
		if (!tag || tag.length <= 0)
			return false;

		_watchTag[tag] = true;

		if (force === true)
			BX.PULL.updateWatch(true);
	};

	BX.PULL.clearWatch = function(id)
	{
		if (id == 'undefined')
			_watchTag = {};
		else if (_watchTag[id])
			delete _watchTag[id];
	}

	BX.PULL.updateWatch = function(force)
	{
		if (!_pullTryConnect)
			return false;

		force = force == true;
		clearTimeout(_watchTimeout);
		_watchTimeout = setTimeout(function()
		{
			var arWatchTag = [];
			for(var i in _watchTag)
			{
				if(_watchTag.hasOwnProperty(i))
				{
					arWatchTag.push(i);
				}
			}

			if (arWatchTag.length > 0)
			{
				BX.ajax({
					url: _pathToAjax+'UPDATE_WATCH&V='+_revision+'',
					method: 'POST',
					dataType: 'json',
					timeout: 30,
					lsId: 'PULL_WATCH_'+location.pathname,
					lsTimeout: 5,
					data: {'PULL_UPDATE_WATCH' : 'Y', 'WATCH' : arWatchTag, 'SITE_ID': (BX.message.SITE_ID? BX.message('SITE_ID'): ''), 'PULL_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
					onsuccess: BX.delegate(function(data)
					{
						BX.onCustomEvent(window, "onAfterUpdateWatch", [data.RESULT]);
						for (var i in data.RESULT)
						{
							if (!data.RESULT[i])
							{
								delete _watchTag[i];
							}
						}
						BX.localStorage.set('puw', location.pathname, 5);
					}, this)
				});
			}
			BX.PULL.updateWatch();
		}, force? 5000: 1740000);
	};

	BX.PULL.executeMessages = function(message, pull)
	{
		pull = pull !== false;
		for (var i = 0; i < message.length; i++)
		{
			if (message[i].extra.revision_web && !BX.PULL.checkRevision(message[i].extra.revision_web))
				return false;

			message[i].extra.server_time_ago = (((new Date()).getTime()-(message[i].extra.server_time_unix*1000))/1000);

			if (message[i].module_id == 'pull')
			{
				if (pull)
				{
					if (message[i].command == 'channel_expire' || message[i].command == 'config_expire')
					{
						if (message[i].command == 'channel_expire' && message[i].params.action == 'reconnect')
						{
							_pullTimeConfigShared = new Date(message[i].params.new_channel.end).valueOf()/1000;
							BX.PULL.updateChannelID({
								'METHOD': _pullMethod,
								'LAST_ID': _channelLastID,
								'CHANNEL_ID': _channelID,
								'CHANNEL_DT': _pullTimeConfig+'/'+_pullTimeConfigShared,
								'PATH': _pullPath.replace(message[i].params.channel.id, message[i].params.new_channel.id),
								'PATH_WS': _wsPath? _wsPath.replace(message[i].params.channel.id, message[i].params.new_channel.id): _wsPath
							});
						}
						else
						{
							_channelClearReason = 14;
							_channelID = null;
							_pullPath = null;
							if (_wsPath) _wsPath = null;
							if (_WS) _WS.close(1000, message[i].command);
						}
					}
					else if (message[i].command == 'server_restart')
					{
						BX.PULL.tryConnectSet(0, false);
						BX.localStorage.set('prs', true, 600);
						if (_WS) _WS.close(1000, "server_restart");
						setTimeout(function(){
							BX.PULL.tryConnect();
						}, ((Math.floor(Math.random() * (61)) + 60)*1000)+600000)
					}
				}
			}
			else
			{
				if (!(message[i].module_id == 'main' && message[i].command == 'user_counter'))
					BX.PULL.setUpdateStateStepCount(1,4);

				try
				{
					if (message[i].module_id == 'online')
					{
						if (message[i].extra.server_time_ago < 240)
							BX.onCustomEvent(window, 'onPullOnlineEvent', [message[i].command, message[i].params, message[i].extra], true);
					}
					else
					{
						BX.onCustomEvent(window, 'onPullEvent-'+message[i].module_id, [message[i].command, message[i].params, message[i].extra], true);
						BX.onCustomEvent(window, 'onPullEvent', [message[i].module_id, message[i].command, message[i].params, message[i].extra], true);
					}
				}
				catch(e)
				{
					if (typeof(console) == 'object')
					{
						console.warn(
							"\n========= PULL ERROR ===========\n"+
							"Error type: onPullEvent onfailure\n"+
							"Error event: ", e, "\n"+
							"Message: ", message[i], "\n"+
							"================================\n"
						);
						BX.debug(e);
					}
				}
			}
		}
	}

	BX.PULL.setUpdateStateStep = function(send)
	{
		var send = send != false;
		var step = 60;

		if (_updateStateVeryFastCount > 0)
		{
			step = 10;
			_updateStateVeryFastCount--;
		}
		else if (_updateStateFastCount > 0)
		{
			step = 20;
			_updateStateFastCount--;
		}

		_updateStateStep = parseInt(step);

		BX.PULL.updateState('27');

		if (send && _lsSupport)
			BX.localStorage.set('puss', _updateStateStep, 5);
	}

	BX.PULL.setUpdateStateStepCount = function(veryFastCount, fastCount)
	{
		_updateStateVeryFastCount = parseInt(veryFastCount);
		_updateStateFastCount = parseInt(fastCount);
	}

	BX.PULL.storageSet = function(params)
	{
		if (params.key == 'pus')
		{
			BX.PULL.executeMessages(params.value.MESSAGE, false);
		}
		else if (params.key == 'pgc')
		{
			BX.PULL.getChannelID('9', params.value, false);
		}
		else if (params.key == 'puss')
		{
			_updateStateStep = 70;
			BX.PULL.updateState('28');
		}
		else if (params.key == 'pset')
		{
			_channelID = params.value.CHANNEL_ID;
			_channelLastID = params.value.LAST_ID;
			_pullPath = params.value.PATH;
			_wsPath = params.value.PATH_WS;
			_pullMethod = params.value.METHOD;
			if (params.value.TIME)
				_pullTime = params.value.TIME;
			if (params.value.TAG)
				_pullTag = params.value.TAG;
			if (params.value.MID)
				_pullMid = params.value.MID;
			if (params.value.TIME_LAST_GET)
				_pullTimeConfig = params.value.TIME_LAST_GET;
			if (params.value.TIME_LAST_GET_SHARED)
				_pullTimeConfigShared = params.value.TIME_LAST_GET_SHARED;

			if (_channelID != null)
			{
				if (!BX.PULL.tryConnect())
					BX.PULL.updateState('29', true);
			}
		}
		else if (params.key == 'puw')
		{
			if (params.value == location.pathname)
				BX.PULL.updateWatch();
		}
	}

	BX.PULL.setAjaxPath = function(url)
	{
		_pathToAjax = url.indexOf('?') == -1? url+'?': url+'&';
	}

	BX.PULL.updateChannelID = function(params)
	{
		if (typeof(params) != 'object')
			return false;

		var method = params.METHOD;
		var channelID = params.CHANNEL_ID;

		var pullPath = params.PATH;
		var lastId = params.LAST_ID;
		var wsPath = params.PATH_WS;

		if (typeof(channelID) == 'undefined' || typeof(pullPath) == 'undefined')
			return false;

		if (channelID == _channelID && pullPath == _pullPath && wsPath == _wsPath)
			return false;

		_channelID = channelID;

		params.CHANNEL_DT = params.CHANNEL_DT.toString().split('/');
		_pullTimeConfig = params.CHANNEL_DT[0];
		_pullTimeConfigShared = params.CHANNEL_DT[1]? params.CHANNEL_DT[1]: params.CHANNEL_DT[0];

		_pullTimeConfig = parseInt(_pullTimeConfig)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET'));
		_pullTimeConfigShared = parseInt(_pullTimeConfigShared)+parseInt(BX.message('SERVER_TZ_OFFSET'))+parseInt(BX.message('USER_TZ_OFFSET'));
		_pullPath = pullPath;
		_wsPath = wsPath;
		_channelLastID = _pullMethod=='PULL' && typeof(lastId) == 'number'? lastId: _channelLastID;
		if (typeof(method) == 'string')
			_pullMethod = method;

		if (_lsSupport)
			BX.localStorage.set('pset', {'CHANNEL_ID': _channelID, 'LAST_ID': _channelLastID, 'PATH': _pullPath, 'PATH_WS': _wsPath, 'TAG': _pullTag,'MID': _pullMid, 'TIME': _pullTime, 'TIME_LAST_GET': _pullTimeConfig, 'TIME_LAST_GET_SHARED': _pullTimeConfigShared, 'METHOD': _pullMethod}, 600);

		if (_WS) _WS.close(1000, "channel_update");

		return true;
	}

	BX.PULL.tryConnectTimeout = function()
	{
		var timeout = 0;
		if (_sendAjaxTry <= 2)
			timeout = 15000;
		else if (_sendAjaxTry > 2 && _sendAjaxTry <= 5)
			timeout = 45000;
		else if (_sendAjaxTry > 5 && _sendAjaxTry <= 10)
			timeout = 600000;
		else if (_sendAjaxTry > 10)
		{
			_pullTryConnect = false;
			timeout = 3600000;
		}

		return timeout;
	}

	/* DEBUG commands */
	BX.PULL.tryConnectSet = function(sendAjaxTry, pullTryConnect)
	{
		if (typeof(sendAjaxTry) == 'number')
			_sendAjaxTry = parseInt(sendAjaxTry);

		if (typeof(pullTryConnect) == 'boolean')
			_pullTryConnect = pullTryConnect;
	}

	BX.PULL.getPullServerStatus = function()
	{
		return _pullMethod != 'PULL';
	}

	BX.PULL.capturePullEvent = function(status)
	{
		status = typeof(status) == 'boolean'? status: true;

		if (!_pullCapturePullEvent && status)
		{
			_pullCapturePullEvent = true;
			_pullCapturePullEventStatus = true;
			BX.addCustomEvent("onPullOnlineEvent", function(command, params, extra) {
				if (_pullCapturePullEventStatus)
				{
					console.info('onPullOnlineEvent',command, params, extra);
				}
			});
			BX.addCustomEvent("onPullEvent", function(module_id, command, params, extra) {
				if (_pullCapturePullEventStatus)
				{
					console.info('onPullEvent',module_id, command, params, extra);
				}
				if (_pullGetPullEventFunctionStatus)
				{
					console.info('BX.onCustomEvent(window, "onPullEvent-'+module_id+'", ["'+command+'", '+JSON.stringify(params)+', '+JSON.stringify(extra)+']);');
					console.info('BX.onCustomEvent(window, "onPullEvent", ["'+module_id+'", "'+command+'", '+JSON.stringify(params)+', '+JSON.stringify(extra)+']);');
				}
			});
			return 'Capture "Pull Event" started.';
		}
		else
		{
			_pullCapturePullEventStatus = status;
			return 'Capture "Pull Event" is '+(status? 'ON': 'OFF');
		}
	}

	BX.PULL.capturePullEventFunction = function(status)
	{
		_pullGetPullEventFunctionStatus = typeof(status) == 'boolean'? status: true;
		BX.PULL.capturePullEvent(_pullGetPullEventFunctionStatus);

		return 'Get "Pull Event" function is '+(_pullGetPullEventFunctionStatus? 'ON': 'OFF');
	}


	BX.PULL.getDebugInfo = function()
	{
		if (!console || !console.info || !JSON || !JSON.stringify)
			return false;

		var textWT = JSON.stringify(_watchTag);
		var text = "\n========= PULL DEBUG ===========\n"+
					"UserId: "+_userId+" "+(_userId>0?'': '(guest)')+"\n"+
					"Connect: "+(_updateStateSend? 'Y': 'N')+"\n"+
					"WebSocket support: "+(_wsSupport && _wsPath.length > 0? 'Y': 'N')+(BX.localStorage.get('pbws')? ' (now blocked)':'')+"\n"+
					"WebSocket connect: "+(_wsConnected? 'Y': 'N')+"\n"+
					"LocalStorage status: "+(_lsSupport? 'Y': 'N')+"\n"+
					"Queue Server: "+(_pullMethod == 'PULL'? 'N': 'Y')+"\n"+
					"Try connect: "+(_pullTryConnect? 'Y': 'N')+"\n"+
					"Try number: "+(_sendAjaxTry)+"\n"+
					"\n"+
					"Path: "+_pullPath+"\n"+
					(_wsPath.length > 0? "WebSocket Path: "+_wsPath+"\n": '')+
					"ChannelID: "+_channelID+"\n"+
					"ChannelDie: "+(parseInt(_pullTimeConfig))+"\n"+
					"ChannelDieShared: "+(parseInt(_pullTimeConfigShared))+"\n"+
					"\n"+
					"Last message: "+(_channelLastID > 0? _channelLastID: '-')+"\n"+
					"Time init connect: "+(_pullTimeConst)+"\n"+
					"Time last connect: "+(_pullTime == _pullTimeConst? '-': _pullTime)+"\n"+
					"Watch tags: "+(textWT == '{}'? '-': textWT)+"\n"+
					"================================\n";

		return console.info(text);
	}

	BX.PULL.clearChannelId = function(send)
	{
		send = send != false;

		_channelClearReason = 15;
		_channelID = null;
		_pullPath = null;

		if (_wsPath) _wsPath = null;
		if (_WS) _WS.close(1000, "clear_channel_id");

		_updateStateSend = false;
		clearTimeout(_updateStateTimeout);

		if (send)
			BX.PULL.updateState('30');
	}

	BX.PULL.isWebSoketConnected = function()
	{
		return _wsConnected === true;
	}

	BX.PULL.supportWebSocket = function()
	{
		var result = false;
		if (typeof(WebSocket) != 'undefined')
		{
			if (BX.browser.IsFirefox() || BX.browser.IsChrome() || BX.browser.IsOpera() || BX.browser.IsSafari())
			{
				if (BX.browser.IsFirefox() && navigator.userAgent.substr(navigator.userAgent.indexOf('Firefox/')+8, 2) >= 25)
					result = true;
				else if (BX.browser.IsChrome() && navigator.appVersion.substr(navigator.appVersion.indexOf('Chrome/')+7, 2) >= 28)
					result = true;
				else if (!BX.browser.IsChrome() && BX.browser.IsSafari())
					result = true;
			}
			else if (BX.browser.DetectIeVersion() >= 10 && !BX.browser.IsAndroid())
			{
				result = true;
			}

		}
		return result;
	}

	BX.PULL.getRevision = function()
	{
		return _revision;
	}

	BX.PULL.getDebugInfoArray = function()
	{
		return {
			connected: _updateStateSend,
			websocket: _wsConnected,
			websocketBlocked: BX.localStorage.get('pbws')? true: false,
			path: _pullPath,
			pathWebsocket: _wsPath
		};
	};

	BX.PULL.checkRevision = function(revision)
	{
		revision = parseInt(revision);
		if (typeof(revision) == "number" && _revision < revision)
		{
			BX.PULL.openConfirm(BX.message('PULL_OLD_REVISION'));

			_pullTryConnect = false;
			if (_WS) _WS.close(1000, "check_revision");

			BX.onCustomEvent(window, 'onPullRevisionUp', [revision, _revision]);

			return false;
		}
		return true;
	};

	BX.PULL.returnPrivateVar = function(v)
	{
		return eval(v);
	}

	BX.PULL.setPrivateVar = function(va, ve)
	{
		return eval(va+' = '+ve);
	}

	BX.PULL.openConfirm = function(text, buttons, modal)
	{
		if (_confirm != null)
			_confirm.destroy();

		modal = modal !== false;
		if (typeof(buttons) == "undefined" || typeof(buttons) == "object" && buttons.length <= 0)
		{
			buttons = [new BX.PopupWindowButton({
				text : BX.message('JS_CORE_WINDOW_CLOSE'),
				className : "popup-window-button-decline",
				events : { click : function(e) { this.popupWindow.close(); BX.PreventDefault(e) } }
			})];
		}
		_confirm = new BX.PopupWindow('bx-notifier-popup-confirm', null, {
			zIndex: 200,
			autoHide: buttons === false,
			buttons : buttons,
			closeByEsc: buttons === false,
			overlay : modal,
			events : { onPopupClose : function() { this.destroy() }, onPopupDestroy : BX.delegate(function() { _confirm = null }, this)},
			content : BX.create("div", { props : { className : (buttons === false? " bx-messenger-confirm-without-buttons": "bx-messenger-confirm") }, html: text})
		});
		_confirm.show();
		BX.bind(_confirm.popupContainer, "click", BX.PULL.preventDefault);
		BX.bind(_confirm.contentContainer, "click", BX.PreventDefault);
		BX.bind(_confirm.overlay.element, "click", BX.PreventDefault);
	};

	BX.PULL.closeConfirm = function()
	{
		if (_confirm != null)
			_confirm.destroy();
	};

	BX.PULL.preventDefault = function(event)
	{
		event = event||window.event;

		if (event.stopPropagation)
			event.stopPropagation();
		else
			event.cancelBubble = true;
	};

	BX.PULL();
})(window);