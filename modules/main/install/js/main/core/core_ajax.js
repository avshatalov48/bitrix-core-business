;(function(window){

if (window.BX.ajax)
	return;

var
	BX = window.BX,

	tempDefaultConfig = {},
	defaultConfig = {
		method: 'GET', // request method: GET|POST
		dataType: 'html', // type of data loading: html|json|script
		timeout: 0, // request timeout in seconds. 0 for browser-default
		async: true, // whether request is asynchronous or not
		processData: true, // any data processing is disabled if false, only callback call
		scriptsRunFirst: false, // whether to run _all_ found scripts before onsuccess call. script tag can have an attribute "bxrunfirst" to turn  this flag on only for itself
		emulateOnload: true,
		skipAuthCheck: false, // whether to check authorization failure (SHOUD be set to true for CORS requests)
		start: true, // send request immediately (if false, request can be started manually via XMLHttpRequest object returned)
		cache: true, // whether NOT to add random addition to URL
		preparePost: true, // whether set Content-Type x-www-form-urlencoded in POST
		headers: false, // add additional headers, example: [{'name': 'If-Modified-Since', 'value': 'Wed, 15 Aug 2012 08:59:08 GMT'}, {'name': 'If-None-Match', 'value': '0'}]
		lsTimeout: 30, //local storage data TTL. useless without lsId.
		lsForce: false //wheter to force query instead of using localStorage data. useless without lsId.
/*
other parameters:
	url: url to get/post
	data: data to post
	onsuccess: successful request callback. BX.proxy may be used.
	onfailure: request failure callback. BX.proxy may be used.
	onprogress: request progress callback. BX.proxy may be used.

	lsId: local storage id - for constantly updating queries which can communicate via localStorage. core_ls.js needed

any of the default parameters can be overridden. defaults can be changed by BX.ajax.Setup() - for all further requests!
*/
	},
	loadedScripts = {},
	loadedScriptsQueue = [],
	r = {
		'url_utf': /[^\034-\254]+/g,
		'script_self': /\/bitrix\/js\/main\/core\/core(_ajax)*.js$/i,
		'script_self_window': /\/bitrix\/js\/main\/core\/core_window.js$/i,
		'script_self_admin': /\/bitrix\/js\/main\/core\/core_admin.js$/i,
		'script_onload': /window.onload/g
	};

// low-level method
BX.ajax = function(config)
{
	var status, data;

	if (!config || !config.url || !BX.type.isString(config.url))
	{
		return false;
	}

	for (var i in tempDefaultConfig)
		if (typeof (config[i]) == "undefined") config[i] = tempDefaultConfig[i];

	tempDefaultConfig = {};

	for (i in defaultConfig)
		if (typeof (config[i]) == "undefined") config[i] = defaultConfig[i];

	config.method = config.method.toUpperCase();

	if (!BX.localStorage)
		config.lsId = null;

	if (BX.browser.IsIE())
	{
		var result = r.url_utf.exec(config.url);
		if (result)
		{
			do
			{
				config.url = config.url.replace(result, BX.util.urlencode(result));
				result = r.url_utf.exec(config.url);
			} while (result);
		}
	}

	if(config.dataType == 'json')
		config.emulateOnload = false;

	if (!config.cache && config.method == 'GET')
		config.url = BX.ajax._uncache(config.url);

	if (config.method == 'POST')
	{
		if (config.preparePost)
		{
			config.data = BX.ajax.prepareData(config.data);
		}
		else if (getLastContentTypeHeader(config.headers) === 'application/json')
		{
			const isJson = (
				BX.Type.isPlainObject(config.data)
				|| BX.Type.isString(config.data)
				|| BX.Type.isNumber(config.data)
				|| BX.Type.isBoolean(config.data)
				|| BX.Type.isArray(config.data)
			);

			if (isJson)
			{
				config.data = JSON.stringify(config.data);
			}
		}
	}

	var bXHR = true;
	if (config.lsId && !config.lsForce)
	{
		var v = BX.localStorage.get('ajax-' + config.lsId);
		if (v !== null)
		{
			bXHR = false;

			var lsHandler = function(lsData) {
				if (lsData.key == 'ajax-' + config.lsId && lsData.value != 'BXAJAXWAIT')
				{
					var data = lsData.value,
						bRemove = !!lsData.oldValue && data == null;
					if (!bRemove)
						BX.ajax.__run(config, data);
					else if (config.onfailure)
						config.onfailure("timeout");

					BX.removeCustomEvent('onLocalStorageChange', lsHandler);
				}
			};

			if (v == 'BXAJAXWAIT')
			{
				BX.addCustomEvent('onLocalStorageChange', lsHandler);
			}
			else
			{
				setTimeout(function() {lsHandler({key: 'ajax-' + config.lsId, value: v})}, 10);
			}
		}
	}

	if (bXHR)
	{
		config.xhr = BX.ajax.xhr();
		if (!config.xhr) return;

		if (config.lsId)
		{
			BX.localStorage.set('ajax-' + config.lsId, 'BXAJAXWAIT', config.lsTimeout);
		}

		if (BX.Type.isFunction(config.onprogress))
		{
			BX.bind(config.xhr, 'progress', config.onprogress);
		}

		if (BX.Type.isFunction(config.onprogressupload) && config.xhr.upload)
		{
			BX.bind(config.xhr.upload, 'progress', config.onprogressupload);
		}

		config.xhr.open(config.method, config.url, config.async);

		if (!config.skipBxHeader && !BX.ajax.isCrossDomain(config.url))
		{
			config.xhr.setRequestHeader('Bx-ajax', 'true');
		}

		if (config.method == 'POST' && config.preparePost)
		{
			config.xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		}
		if (typeof(config.headers) == "object")
		{
			for (i = 0; i < config.headers.length; i++)
				config.xhr.setRequestHeader(config.headers[i].name, config.headers[i].value);
		}

		var bRequestCompleted = false;
		var onreadystatechange = config.xhr.onreadystatechange = function(additional)
		{
			if (bRequestCompleted)
				return;

			if (additional === 'timeout')
			{
				if (config.onfailure)
				{
					config.onfailure('timeout', '', config);
				}

				BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['timeout', '', config]);

				config.xhr.onreadystatechange = BX.DoNothing;
				config.xhr.abort();

				if (config.async)
				{
					config.xhr = null;
				}
			}
			else
			{
				if (config.xhr.readyState == 4 || additional == 'run')
				{
					status = BX.ajax.xhrSuccess(config.xhr) ? "success" : "error";
					bRequestCompleted = true;
					config.xhr.onreadystatechange = BX.DoNothing;

					if (status == 'success')
					{
						var authHeader = (!!config.skipAuthCheck || BX.ajax.isCrossDomain(config.url))
							? false
							: config.xhr.getResponseHeader('X-Bitrix-Ajax-Status');

						if(!!authHeader && authHeader == 'Authorize')
						{
							if (config.onfailure)
							{
								config.onfailure('auth', config.xhr.status, config);
							}

							BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['auth', config.xhr.status, config]);
						}
						else
						{
							var data = config.xhr.responseText;

							if (config.lsId)
							{
								BX.localStorage.set('ajax-' + config.lsId, data, config.lsTimeout);
							}

							BX.ajax.__run(config, data);
						}
					}
					else
					{
						if (config.onfailure)
						{
							config.onfailure('status', config.xhr.status, config);
						}

						BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['status', config.xhr.status, config]);
					}

					if (config.async)
					{
						config.xhr = null;
					}
				}
			}
		};

		if (config.async && config.timeout > 0)
		{
			setTimeout(function() {
				if (config.xhr && !bRequestCompleted)
				{
					onreadystatechange("timeout");
				}
			}, config.timeout * 1000);
		}

		if (config.start)
		{
			config.xhr.send(config.data);

			if (!config.async)
			{
				onreadystatechange('run');
			}
		}

		return config.xhr;
	}
};

BX.ajax.xhr = function()
{
	if (window.XMLHttpRequest)
	{
		try {return new XMLHttpRequest();} catch(e){}
	}
	else if (window.ActiveXObject)
	{
		try { return new window.ActiveXObject("Msxml2.XMLHTTP.6.0"); }
			catch(e) {}
		try { return new window.ActiveXObject("Msxml2.XMLHTTP.3.0"); }
			catch(e) {}
		try { return new window.ActiveXObject("Msxml2.XMLHTTP"); }
			catch(e) {}
		try { return new window.ActiveXObject("Microsoft.XMLHTTP"); }
			catch(e) {}
		throw new Error("This browser does not support XMLHttpRequest.");
	}

	return null;
};

BX.ajax.isCrossDomain = function(url, location)
{
	location = location || window.location;

	//Relative URL gets a current protocol
	if (url.indexOf("//") === 0)
	{
		url = location.protocol + url;
	}

	//Fast check
	if (url.indexOf("http") !== 0)
	{
		return false;
	}

	var link = window.document.createElement("a");
	link.href = url;

	return  link.protocol !== location.protocol ||
			link.hostname !== location.hostname ||
			BX.ajax.getHostPort(link.protocol, link.host) !== BX.ajax.getHostPort(location.protocol, location.host);
};

BX.ajax.getHostPort = function(protocol, host)
{
	var match = /:(\d+)$/.exec(host);
	if (match)
	{
		return match[1];
	}
	else
	{
		if (protocol === "http:")
		{
			return "80";
		}
		else if (protocol === "https:")
		{
			return "443";
		}
	}

	return "";
};

BX.ajax.__prepareOnload = function(scripts, ajax_session)
{
	if (scripts.length > 0)
	{
		BX.ajax['onload_' + ajax_session] = null;

		for (var i=0,len=scripts.length;i<len;i++)
		{
			if (scripts[i].isInternal)
			{
				scripts[i].JS = scripts[i].JS.replace(r.script_onload, 'BX.ajax.onload_' + ajax_session);
			}
		}
	}

	BX.CaptureEventsGet();
	BX.CaptureEvents(window, 'load');
};

BX.ajax.__runOnload = function(ajax_session)
{
	if (null != BX.ajax['onload_' + ajax_session])
	{
		BX.ajax['onload_' + ajax_session].apply(window);
		BX.ajax['onload_' + ajax_session] = null;
	}

	var h = BX.CaptureEventsGet();

	if (h)
	{
		for (var i=0; i<h.length; i++)
			h[i].apply(window);
	}
};

BX.ajax.__run = function(config, data)
{
	if (!config.processData)
	{
		if (config.onsuccess)
		{
			config.onsuccess(data);
		}

		BX.onCustomEvent(config.xhr, 'onAjaxSuccess', [data, config]);
	}
	else
	{
		data = BX.ajax.processRequestData(data, config);
	}
};


BX.ajax._onParseJSONFailure = function(data)
{
	this.jsonFailure = true;
	this.jsonResponse = data;
	this.jsonProactive = /^\[WAF\]/.test(data);
};

BX.ajax.processRequestData = function(data, config)
{
	var result, scripts = [], styles = [];
	switch (config.dataType.toUpperCase())
	{
		case 'JSON':

			var context = config.xhr || {};
			BX.addCustomEvent(context, 'onParseJSONFailure', BX.proxy(BX.ajax._onParseJSONFailure, config));
			result = BX.parseJSON(data, context);
			BX.removeCustomEvent(context, 'onParseJSONFailure', BX.proxy(BX.ajax._onParseJSONFailure, config));

			if(!!result && BX.type.isArray(result['bxjs']))
			{
				for(var i = 0; i < result['bxjs'].length; i++)
				{
					if(BX.type.isNotEmptyString(result['bxjs'][i]))
					{
						scripts.push({
							"isInternal": false,
							"JS": result['bxjs'][i],
							"bRunFirst": config.scriptsRunFirst
						});
					}
					else
					{
						scripts.push(result['bxjs'][i])
					}
				}
			}

			if(!!result && BX.type.isArray(result['bxcss']))
			{
				styles = result['bxcss'];
			}

		break;
		case 'SCRIPT':
			scripts.push({"isInternal": true, "JS": data, "bRunFirst": config.scriptsRunFirst});
			result = data;
		break;

		default: // HTML
			var ob = BX.processHTML(data, config.scriptsRunFirst);
			result = ob.HTML; scripts = ob.SCRIPT; styles = ob.STYLE;
		break;
	}

	if (styles.length > 0)
	{
		BX.loadCSS(styles);
	}

	let ajax_session = null;
	if (config.emulateOnload)
	{
		ajax_session = parseInt(Math.random() * 1000000);
		BX.ajax.__prepareOnload(scripts, ajax_session);
	}

	const cb = BX.defer(function()
	{
		if (config.emulateOnload)
		{
			BX.ajax.__runOnload(ajax_session);
		}

		BX.onCustomEvent(config.xhr, 'onAjaxSuccessFinish', [config]);
	});

	try
	{
		if (!!config.jsonFailure)
		{
			throw {type: 'json_failure', data: config.jsonResponse, bProactive: config.jsonProactive};
		}

		config.scripts = scripts;

		BX.ajax.processScripts(config.scripts, true);

		if (config.onsuccess)
		{
			config.onsuccess(result);
		}

		BX.onCustomEvent(config.xhr, 'onAjaxSuccess', [result, config]);

		BX.ajax.processScripts(config.scripts, false, cb);
	}
	catch (e)
	{
		if (config.onfailure)
			config.onfailure("processing", e);
		BX.onCustomEvent(config.xhr, 'onAjaxFailure', ['processing', e, config]);
	}
};

BX.ajax.processScripts = function(scripts, bRunFirst, cb)
{
	var scriptsExt = [], scriptsInt = '';

	cb = cb || BX.DoNothing;

	for (var i = 0, length = scripts.length; i < length; i++)
	{
		if (typeof bRunFirst != 'undefined' && bRunFirst != !!scripts[i].bRunFirst)
			continue;

		if (scripts[i].isInternal)
			scriptsInt += ';' + scripts[i].JS;
		else
			scriptsExt.push(scripts[i].JS);
	}

	scriptsExt = BX.util.array_unique(scriptsExt);
	var inlineScripts = scriptsInt.length > 0 ? function() { BX.evalGlobal(scriptsInt); } : BX.DoNothing;

	if (scriptsExt.length > 0)
	{
		BX.load(scriptsExt, function() {
			inlineScripts();
			cb();
		});
	}
	else
	{
		inlineScripts();
		cb();
	}
};

// TODO: extend this function to use with any data objects or forms
BX.ajax.prepareData = function(arData, prefix)
{
	var data = '';
	if (BX.type.isString(arData))
		data = arData;
	else if (null != arData)
	{
		for(var i in arData)
		{
			if (arData.hasOwnProperty(i))
			{
				if (data.length > 0)
					data += '&';
				var name = BX.util.urlencode(i);
				if(prefix)
					name = prefix + '[' + name + ']';
				if(typeof arData[i] == 'object')
					data += BX.ajax.prepareData(arData[i], name);
				else
					data += name + '=' + BX.util.urlencode(arData[i]);
			}
		}
	}
	return data;
};

BX.ajax.xhrSuccess = function(xhr)
{
	return (xhr.status >= 200 && xhr.status < 300) || xhr.status === 304 || xhr.status === 1223 || xhr.status === 0;
};

BX.ajax.Setup = function(config, bTemp)
{
	bTemp = !!bTemp;

	for (var i in config)
	{
		if (bTemp)
			tempDefaultConfig[i] = config[i];
		else
			defaultConfig[i] = config[i];
	}
};

BX.ajax.replaceLocalStorageValue = function(lsId, data, ttl)
{
	if (!!BX.localStorage)
		BX.localStorage.set('ajax-' + lsId, data, ttl);
};


BX.ajax._uncache = function(url)
{
	return url + ((url.indexOf('?') !== -1 ? "&" : "?") + '_=' + (new Date()).getTime());
};

/* simple interface */
BX.ajax.get = function(url, data, callback)
{
	if (BX.type.isFunction(data))
	{
		callback = data;
		data = '';
	}

	data = BX.ajax.prepareData(data);

	if (data)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
		data = '';
	}

	return BX.ajax({
		'method': 'GET',
		'dataType': 'html',
		'url': url,
		'data':  '',
		'onsuccess': callback
	});
};

BX.ajax.getCaptcha = function(callback)
{
	return BX.ajax.loadJSON('/bitrix/tools/ajax_captcha.php', callback);
};

BX.ajax.insertToNode = function(url, node)
{
	node = BX(node);
	if (!!node)
	{
		var eventArgs = { cancel: false };
		BX.onCustomEvent('onAjaxInsertToNode', [{ url: url, node: node, eventArgs: eventArgs }]);
		if(eventArgs.cancel === true)
		{
			return;
		}

		var show = null;
		if (!tempDefaultConfig.denyShowWait)
		{
			show = BX.showWait(node);
			delete tempDefaultConfig.denyShowWait;
		}

		return BX.ajax.get(url, function(data) {
			node.innerHTML = data;
			BX.closeWait(node, show);
		});
	}
};

BX.ajax.post = function(url, data, callback)
{
	data = BX.ajax.prepareData(data);

	return BX.ajax({
		'method': 'POST',
		'dataType': 'html',
		'url': url,
		'data':  data,
		'onsuccess': callback
	});
};

/**
 * BX.ajax with BX.Promise
 *
 * @param config
 * @returns {BX.Promise|false}
 */
BX.ajax.promise = function(config)
{
	var result = new BX.Promise();

	config.onsuccess = function(data)
	{
		result.fulfill(data);
	};
	config.onfailure = function(reason, httpStatus, config)
	{
		result.reject({
			reason: reason,
			data: httpStatus,
			ajaxConfig: config,
			xhr: config.xhr
		});
	};

	var xhr = BX.ajax(config);
	if (xhr)
	{
		if (typeof config.onrequeststart === 'function')
		{
			config.onrequeststart(xhr);
		}
	}
	else
	{
		result.reject({
			reason: "init",
			data: false
		});
	}

	return result;
};

/* load and execute external file script with onload emulation */
BX.ajax.loadScriptAjax = function(script_src, callback, bPreload)
{
	if (BX.type.isArray(script_src))
	{
		for (var i=0,len=script_src.length;i<len;i++)
		{
			BX.ajax.loadScriptAjax(script_src[i], callback, bPreload);
		}
	}
	else
	{
		var script_src_test = script_src.replace(/\.js\?.*/, '.js');

		if (r.script_self.test(script_src_test)) return;
		if (r.script_self_window.test(script_src_test) && BX.CWindow) return;
		if (r.script_self_admin.test(script_src_test) && BX.admin) return;

		if (typeof loadedScripts[script_src_test] == 'undefined')
		{
			if (!!bPreload)
			{
				loadedScripts[script_src_test] = '';
				return BX.loadScript(script_src);
			}
			else
			{
				return BX.ajax({
					url: script_src,
					method: 'GET',
					dataType: 'script',
					processData: true,
					emulateOnload: false,
					scriptsRunFirst: true,
					async: false,
					start: true,
					onsuccess: function(result) {
						loadedScripts[script_src_test] = result;
						if (callback)
							callback(result);
					}
				});
			}
		}
		else if (callback)
		{
			callback(loadedScripts[script_src_test]);
		}
	}
};

/* non-xhr loadings */
BX.ajax.loadJSON = function(url, data, callback, callback_failure)
{
	if (BX.type.isFunction(data))
	{
		callback_failure = callback;
		callback = data;
		data = '';
	}

	data = BX.ajax.prepareData(data);

	if (data)
	{
		url += (url.indexOf('?') !== -1 ? "&" : "?") + data;
		data = '';
	}

	return BX.ajax({
		'method': 'GET',
		'dataType': 'json',
		'url': url,
		'onsuccess': callback,
		'onfailure': callback_failure
	});
};

var getLastContentTypeHeader = function (headers) {
	if (!BX.Type.isArray(headers))
	{
		return null;
	}
	var lastHeader = headers
		.filter(function (header) {
			return header.name === 'Content-Type';
		})
		.pop();

	return lastHeader ? lastHeader.value : null;
};

/**
 * @see isValidAnalyticsData in ui.analytics
* */
const isValidAnalyticsData = function (analytics)
{
	if (!BX.Type.isPlainObject(analytics))
	{
		console.error('BX.ajax: {analytics} must be an object.');

		return false;
	}

	const requiredFields = ['event', 'tool', 'category'];
	for (const field of requiredFields)
	{
		if (!BX.Type.isStringFilled(analytics[field]))
		{
			console.error(`BX.ajax: The "${field}" property in the "analytics" object must be a non-empty string.`);

			return false;
		}
	}

	const additionalFields = ['p1', 'p2', 'p3', 'p4', 'p5'];
	for (const field of additionalFields)
	{
		const value = analytics[field];
		if (!BX.Type.isStringFilled(value))
		{
			continue;
		}

		if (value.split('_').length > 2)
		{
			console.error(`BX.ajax: The "${field}" property (${value}) in the "analytics" object must be a string containing a single underscore.`);

			return false;
		}
	}

	return true;
};

const processAnalyticsDataToGetParameters = function(config)
{
	const getParameters = {};
	if (BX.Type.isStringFilled(config.analyticsLabel) || BX.Type.isPlainObject(config.analyticsLabel))
	{
		getParameters.analyticsLabel = config.analyticsLabel;
	}

	if (BX.Type.isPlainObject(config.analytics))
	{
		if (config.analyticsLabel)
		{
			delete getParameters.analyticsLabel;
			console.error('BX.ajax: Only {analytics} or {analyticsLabel} should be used. If both are present, {analyticsLabel} will be ignored.');
		}

		if (isValidAnalyticsData(config.analytics))
		{
			getParameters.st = config.analytics;
		}
		else
		{
			console.error('BX.ajax: {analytics} is invalid and is skipped.');
		}
	}

	return getParameters;
};

const prepareAjaxGetParameters = function(config)
{
	let getParameters = config.getParameters || {};
	getParameters = { ...getParameters, ...processAnalyticsDataToGetParameters(config) };

	if (typeof config.mode !== 'undefined')
	{
		getParameters.mode = config.mode;
	}
	if (config.navigation)
	{
		if (config.navigation.page)
		{
			getParameters.nav = 'page-' + config.navigation.page;
		}
		if (config.navigation.size)
		{
			if (getParameters.nav)
			{
				getParameters.nav += '-';
			}
			else
			{
				getParameters.nav = '';
			}
			getParameters.nav += 'size-' + config.navigation.size;
		}
	}

	return getParameters;
};

var prepareAjaxConfig = function(config)
{
	config = BX.type.isPlainObject(config) ? config : {};

	config.headers = config.headers || [];
	config.headers.push({name: 'X-Bitrix-Csrf-Token', value: BX.bitrix_sessid()});
	if (BX.message.SITE_ID)
	{
		config.headers.push({name: 'X-Bitrix-Site-Id', value: BX.message.SITE_ID});
	}

	if (typeof config.json !== 'undefined')
	{
		if (!BX.type.isPlainObject(config.json))
		{
			throw new Error('Wrong `config.json`, plain object expected.')
		}

		config.headers.push({name: 'Content-Type', value: 'application/json'});
		config.data = config.json;
		config.preparePost = false;
	}
	else if (config.data instanceof FormData)
	{
		config.preparePost = false;
		if (typeof config.signedParameters !== 'undefined')
		{
			config.data.append('signedParameters', config.signedParameters);
		}
	}
	else if (BX.type.isPlainObject(config.data) || BX.Type.isNil(config.data))
	{
		config.data = BX.type.isPlainObject(config.data) ? config.data : {};
		if (typeof config.signedParameters !== 'undefined')
		{
			config.data.signedParameters = config.signedParameters;
		}
	}

	if (!config.method)
	{
		config.method = 'POST'
	}

	return config;
};

var buildAjaxPromiseToRestoreCsrf = function(config, withoutRestoringCsrf)
{
	withoutRestoringCsrf = withoutRestoringCsrf || false;
	var originalConfig = BX.clone(config);
	var request = null;

	var onrequeststart = config.onrequeststart;
	config.onrequeststart = function(xhr) {
		request = xhr;
		if (BX.type.isFunction(onrequeststart))
		{
			onrequeststart(xhr);
		}
	};
	var onrequeststartOrig = originalConfig.onrequeststart;
	originalConfig.onrequeststart = function(xhr) {
		request = xhr;
		if (BX.type.isFunction(onrequeststartOrig))
		{
			onrequeststartOrig(xhr);
		}
	};

	var promise = BX.ajax.promise(config);

	return promise.then(function(response) {
		if (!withoutRestoringCsrf && BX.type.isPlainObject(response) && BX.type.isArray(response.errors))
		{
			var csrfProblem = false;
			response.errors.forEach(function(error) {
				if (error.code === 'invalid_csrf' && error.customData.csrf)
				{
					BX.message({'bitrix_sessid': error.customData.csrf});

					originalConfig.headers = originalConfig.headers || [];
					originalConfig.headers = originalConfig.headers.filter(function(header) {
						return header && header.name !== 'X-Bitrix-Csrf-Token';
					});
					originalConfig.headers.push({name: 'X-Bitrix-Csrf-Token', value: BX.bitrix_sessid()});

					csrfProblem = true;
				}
			});

			if (csrfProblem)
			{
				return buildAjaxPromiseToRestoreCsrf(originalConfig, true);
			}
		}

		if (!BX.type.isPlainObject(response) || response.status !== 'success')
		{
			var errorPromise = new BX.Promise();
			errorPromise.reject(response);

			return errorPromise;
		}

		return response;
	}).catch(function(data) {
		var ajaxReject = new BX.Promise();

		var originalJsonResponse;
		if (BX.type.isPlainObject(data) && data.xhr && data.xhr.responseText)
		{
			try
			{
				originalJsonResponse = JSON.parse(data.xhr.responseText);
				data = originalJsonResponse;
			}
			catch (err)
			{}
		}

		if (BX.type.isPlainObject(data) && data.status && data.hasOwnProperty('data'))
		{
			ajaxReject.reject(data);
		}
		else
		{
			ajaxReject.reject({
				status: 'error',
				data: {
					ajaxRejectData: data
				},
				errors: [
					{
						code: 'NETWORK_ERROR',
						message: 'Network error'
					}
				]
			});
		}

		return ajaxReject;
	}).then(function(response){

		var assetsLoaded = new BX.Promise();

		var headers = request.getAllResponseHeaders().trim().split(/[\r\n]+/);
		var headerMap = {};
		headers.forEach(function (line) {
			var parts = line.split(': ');
			var header = parts.shift().toLowerCase();
			headerMap[header] = parts.join(': ');
		});

		if (!headerMap['x-process-assets'])
		{
			assetsLoaded.fulfill(response);

			return assetsLoaded;
		}

		var assets = BX.prop.getObject(BX.prop.getObject(response, "data", {}), "assets", {});

		var inlineScripts = [];
		if (BX.Type.isArrayFilled(assets.string))
		{
			assets.string
				.reduce(function(acc, item) {
					if (String(item).length > 0 && !acc.includes(item))
					{
						acc.push(item);
					}

					return acc;
				}, [])
				.forEach(function(item) {
					if (String(item).startsWith('<script type="extension/settings"'))
					{
						BX.html(document.head, item, { useAdjacentHTML: true });
					}
					else
					{
						inlineScripts.push(item);
					}
				});
		}

		var promise = new Promise(function(resolve, reject) {
			var css = BX.prop.getArray(assets, "css", []);
			BX.load(css, function(){
				BX.loadScript(
					BX.prop.getArray(assets, "js", []),
					resolve
				);
			});
		});

		promise.then(function(){
			var stringAsset = inlineScripts.join('\n');
			BX.html(document.head, stringAsset, { useAdjacentHTML: true }).then(function(){
				assetsLoaded.fulfill(response);
			});
		});

		return assetsLoaded;
	});
};

/**
 *
 * @param {string} action
 * @param {Object} config
 * @param {?string|?Object} [config.analyticsLabel]
 * @param {?Object} [config.analytics]
 * @param {string} [config.analytics.event]
 * @param {string} [config.analytics.tool]
 * @param {string} [config.analytics.category]
 * @param {?string} [config.analytics.c_section]
 * @param {?string} [config.analytics.c_sub_section]
 * @param {?string} [config.analytics.c_element]
 * @param {?string} [config.analytics.type]
 * @param {?string} [config.analytics.p1]
 * @param {?string} [config.analytics.p2]
 * @param {?string} [config.analytics.p3]
 * @param {?string} [config.analytics.p4]
 * @param {?string} [config.analytics.p5]
 * @param {?('success' | 'error' | 'attempt' | 'cancel')} [config.analytics.status]
 * @param {string} [config.method='POST']
 * @param {Object} [config.data]
 * @param {?Object} [config.getParameters]
 * @param {?Object} [config.headers]
 * @param {?Object} [config.timeout]
 * @param {Object} [config.navigation]
 * @param {number} [config.navigation.page]
 */
BX.ajax.runAction = function(action, config)
{
	config = prepareAjaxConfig(config);
	var getParameters = prepareAjaxGetParameters(config);
	getParameters.action = action;

	var url = '/bitrix/services/main/ajax.php?' + BX.ajax.prepareData(getParameters);
	return buildAjaxPromiseToRestoreCsrf({
		method: config.method,
		dataType: 'json',
		url: url,
		data: config.data,
		timeout: config.timeout,
		preparePost: config.preparePost,
		headers: config.headers,
		onrequeststart: config.onrequeststart,
		onprogress: config.onprogress,
		onprogressupload: config.onprogressupload
	});
};

/**
 *
 * @param {string} component
 * @param {string} action
 * @param {Object} config
 * @param {?string|?Object} [config.analyticsLabel]
 * @param {?Object} [config.analytics]
 * @param {string} [config.analytics.event]
 * @param {string} [config.analytics.tool]
 * @param {string} [config.analytics.category]
 * @param {?string} [config.analytics.c_section]
 * @param {?string} [config.analytics.c_sub_section]
 * @param {?string} [config.analytics.c_element]
 * @param {?string} [config.analytics.type]
 * @param {?string} [config.analytics.p1]
 * @param {?string} [config.analytics.p2]
 * @param {?string} [config.analytics.p3]
 * @param {?string} [config.analytics.p4]
 * @param {?string} [config.analytics.p5]
 * @param {?string} [config.signedParameters]
 * @param {string} [config.method='POST']
 * @param {string} [config.mode='ajax'] Ajax or class.
 * @param {Object} [config.data]
 * @param {?Object} [config.getParameters]
 * @param {?array} [config.headers]
 * @param {?number} [config.timeout]
 * @param {Object} [config.navigation]
 */
BX.ajax.runComponentAction = function (component, action, config)
{
	config = prepareAjaxConfig(config);
	config.mode = config.mode || 'ajax';

	var getParameters = prepareAjaxGetParameters(config);
	getParameters.c = component;
	getParameters.action = action;

	var url = '/bitrix/services/main/ajax.php?' + BX.ajax.prepareData(getParameters);

	return buildAjaxPromiseToRestoreCsrf({
		method: config.method,
		dataType: 'json',
		url: url,
		data: config.data,
		timeout: config.timeout,
		preparePost: config.preparePost,
		headers: config.headers,
		onrequeststart: (config.onrequeststart ? config.onrequeststart : null),
		onprogress: config.onprogress,
		onprogressupload: config.onprogressupload
	});
};

/*
arObs = [{
	url: url,
	type: html|script|json|css,
	callback: function
}]
*/
BX.ajax.load = function(arObs, callback)
{
	if (!BX.type.isArray(arObs))
		arObs = [arObs];

	var cnt = 0;

	if (!BX.type.isFunction(callback))
		callback = BX.DoNothing;

	var handler = function(data)
		{
			if (BX.type.isFunction(this.callback))
				this.callback(data);

			if (++cnt >= len)
				callback();
		};

	for (var i = 0, len = arObs.length; i<len; i++)
	{
		switch(arObs[i].type.toUpperCase())
		{
			case 'SCRIPT':
				BX.loadScript([arObs[i].url], BX.proxy(handler, arObs[i]));
			break;
			case 'CSS':
				BX.loadCSS([arObs[i].url]);

				if (++cnt >= len)
					callback();
			break;
			case 'JSON':
				BX.ajax.loadJSON(arObs[i].url, BX.proxy(handler, arObs[i]));
			break;

			default:
				BX.ajax.get(arObs[i].url, '', BX.proxy(handler, arObs[i]));
			break;
		}
	}
};

/* ajax form sending */
BX.ajax.submit = function(obForm, callback)
{
	if (!obForm.target)
	{
		if (null == obForm.BXFormTarget)
		{
			var frame_name = 'formTarget_' + Math.random();
			obForm.BXFormTarget = document.body.appendChild(BX.create('IFRAME', {
				props: {
					name: frame_name,
					id: frame_name,
					src: 'javascript:void(0)'
				},
				style: {
					display: 'none'
				}
			}));
		}

		obForm.target = obForm.BXFormTarget.name;
	}

	obForm.BXFormCallback = callback;
	BX.bind(obForm.BXFormTarget, 'load', BX.proxy(BX.ajax._submit_callback, obForm));

	BX.submit(obForm);

	return false;
};

BX.ajax.submitComponentForm = function(obForm, container, bWait)
{
	if (!obForm.target)
	{
		if (null == obForm.BXFormTarget)
		{
			var frame_name = 'formTarget_' + Math.random();
			obForm.BXFormTarget = document.body.appendChild(BX.create('IFRAME', {
				props: {
					name: frame_name,
					id: frame_name,
					src: 'javascript:void(0)'
				},
				style: {
					display: 'none'
				}
			}));
		}

		obForm.target = obForm.BXFormTarget.name;
	}

	if (!!bWait)
		var w = BX.showWait(container);

	obForm.BXFormCallback = function(d) {
		if (!!bWait)
			BX.closeWait(w);

		var callOnload = function(){
			if(!!window.bxcompajaxframeonload)
			{
				setTimeout(function(){window.bxcompajaxframeonload();window.bxcompajaxframeonload=null;}, 10);
			}
		};

		BX(container).innerHTML = d;
		BX.onCustomEvent('onAjaxSuccess', [null,null,callOnload]);
	};

	BX.bind(obForm.BXFormTarget, 'load', BX.proxy(BX.ajax._submit_callback, obForm));

	return true;
};

// func will be executed in form context
BX.ajax._submit_callback = function()
{
	//opera and IE8 triggers onload event even on empty iframe
	try
	{
		if(this.BXFormTarget.contentWindow.location.href.indexOf('http') != 0)
			return;
	} catch (e) {
		return;
	}

	if (this.BXFormCallback)
		this.BXFormCallback.apply(this, [this.BXFormTarget.contentWindow.document.body.innerHTML]);

	BX.unbindAll(this.BXFormTarget);
};

BX.ajax.prepareForm = function(obForm, data)
{
	data = (!!data ? data : {});
	var i, ii, el,
		_data = [],
		n = obForm.elements.length,
		files = 0, length = 0;
	if(!!obForm)
	{
		for (i = 0; i < n; i++)
		{
			el = obForm.elements[i];
			if (el.disabled)
				continue;

			if(!el.type)
				continue;

			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'number':
				case 'hidden':
				case 'select-one':
					_data.push({name: el.name, value: el.value});
					length += (el.name.length + el.value.length);
					break;
				case 'file':
					if (!!el.files)
					{
						for (ii = 0; ii < el.files.length; ii++)
						{
							files++;
							_data.push({name: el.name, value: el.files[ii], file : true});
							length += el.files[ii].size;
						}
					}
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
					{
						_data.push({name: el.name, value: el.value});
						length += (el.name.length + el.value.length);
					}
					break;
				case 'select-multiple':
					for (var j = 0; j < el.options.length; j++)
					{
						if (el.options[j].selected)
						{
							_data.push({name : el.name, value : el.options[j].value});
							length += (el.name.length + el.options[j].length);
						}
					}
					break;
				default:
					break;
			}
		}

		i = 0; length = 0;
		var current = data, name, rest, pp, tmpKey;

		while(i < _data.length)
		{
			var p = _data[i].name.indexOf('[');
			if (tmpKey)
			{
				current[_data[i].name] = {};
				current[_data[i].name][tmpKey.replace(/\[|\]/gi, '')] = _data[i].value;
				current = data;
				tmpKey = null;
				i++;
			}
			else if (p == -1)
			{
				current[_data[i].name] = _data[i].value;
				current = data;
				i++;
			}
			else
			{
				name = _data[i].name.substring(0, p);
				rest = _data[i].name.substring(p+1);
				pp = rest.indexOf(']');

				if(pp == -1)
				{
					if (!current[name])
						current[name] = [];
					current = data;
					i++;
				}
				else if(pp == 0)
				{
					if (!current[name])
						current[name] = [];
					//No index specified - so take the next integer
					current = current[name];
					_data[i].name = '' + current.length;
					if (rest.substring(pp+1).indexOf('[') === 0)
						tmpKey = rest.substring(0, pp) + rest.substring(pp+1);
				}
				else
				{
					if (!current[name])
						current[name] = {};
					//Now index name becomes and name and we go deeper into the array
					current = current[name];
					_data[i].name = rest.substring(0, pp) + rest.substring(pp+1);
				}
			}
		}
	}
	return {data : data, filesCount : files, roughSize : length};
};
BX.ajax.submitAjax = function(obForm, config)
{
	config = (config !== null && typeof config == "object" ? config : {});
	config.url = (config["url"] || obForm.getAttribute("action"));

	var additionalData = (config["data"] || {});
	config.data = BX.ajax.prepareForm(obForm).data;
	for (var ii in additionalData)
	{
		if (additionalData.hasOwnProperty(ii))
		{
			config.data[ii] = additionalData[ii];
		}
	}

	if (!window["FormData"])
	{
		BX.ajax(config);
	}
	else
	{
		var isFile = function(item)
		{
			var res = Object.prototype.toString.call(item);
			return (res == '[object File]' || res == '[object Blob]');
		},
		appendToForm = function(fd, key, val)
		{
			if (!!val && typeof val == "object" && !isFile(val))
			{
				for (var ii in val)
				{
					if (val.hasOwnProperty(ii))
					{
						appendToForm(fd, (key == '' ? ii : key + '[' + ii + ']'), val[ii]);
					}
				}
			}
			else
				fd.append(key, (!!val ? val : ''));
		},
		prepareData = function(arData)
		{
			var data = {};
			if (null != arData)
			{
				if(typeof arData == 'object')
				{
					for(var i in arData)
					{
						if (arData.hasOwnProperty(i))
						{
							var name = BX.util.urlencode(i);
							if(typeof arData[i] == 'object' && arData[i]["file"] !== true)
								data[name] = prepareData(arData[i]);
							else if (arData[i]["file"] === true)
								data[name] = arData[i]["value"];
							else
								data[name] = BX.util.urlencode(arData[i]);
						}
					}
				}
				else
					data = BX.util.urlencode(arData);
			}
			return data;
		},
		fd = new window.FormData();

		if (config.method !== 'POST')
		{
			config.data = BX.ajax.prepareData(config.data);
			if (config.data)
			{
				config.url += (config.url.indexOf('?') !== -1 ? "&" : "?") + config.data;
				config.data = '';
			}
		}
		else
		{
			if (config.preparePost === true)
				config.data = prepareData(config.data);
			appendToForm(fd, '', config.data);
			config.data = fd;
		}

		config.preparePost = false;
		config.start = false;

		var xhr = BX.ajax(config);
		if (!!config["onprogress"])
			xhr.upload.addEventListener(
				'progress',
				function(e){
					var percent = null;
					if(e.lengthComputable && (e.total || e["totalSize"])) {
						percent = e.loaded * 100 / (e.total || e["totalSize"]);
					}
					config["onprogress"](e, percent);
				}
			);
		xhr.send(fd);
	}
};

BX.ajax.UpdatePageData = function (arData)
{
	if (arData.TITLE)
		BX.ajax.UpdatePageTitle(arData.TITLE);
	if (arData.WINDOW_TITLE || arData.TITLE)
		BX.ajax.UpdateWindowTitle(arData.WINDOW_TITLE || arData.TITLE);
	if (arData.NAV_CHAIN)
		BX.ajax.UpdatePageNavChain(arData.NAV_CHAIN);
	if (arData.CSS && arData.CSS.length > 0)
		BX.loadCSS(arData.CSS);
	if (arData.SCRIPTS && arData.SCRIPTS.length > 0)
	{
		var f = function(result,config,cb){

			if(!!config && BX.type.isArray(config.scripts))
			{
				for(var i=0,l=arData.SCRIPTS.length;i<l;i++)
				{
					config.scripts.push({isInternal:false,JS:arData.SCRIPTS[i]});
				}
			}
			else
			{
				BX.loadScript(arData.SCRIPTS,cb);
			}

			BX.removeCustomEvent('onAjaxSuccess',f);
		};
		BX.addCustomEvent('onAjaxSuccess',f);
	}
	else
	{
		var f1 = function(result,config,cb){
			if(BX.type.isFunction(cb))
			{
				cb();
			}
			BX.removeCustomEvent('onAjaxSuccess',f1);
		};
		BX.addCustomEvent('onAjaxSuccess', f1);
	}
};

BX.ajax.UpdatePageTitle = function(title)
{
	var obTitle = BX('pagetitle');
	if (obTitle)
	{
		BX.remove(obTitle.firstChild);
		if (!obTitle.firstChild)
			obTitle.appendChild(document.createTextNode(title));
		else
			obTitle.insertBefore(document.createTextNode(title), obTitle.firstChild);
	}
};

BX.ajax.UpdateWindowTitle = function(title)
{
	document.title = title;
};

BX.ajax.UpdatePageNavChain = function(nav_chain)
{
	var obNavChain = BX('navigation');
	if (obNavChain)
	{
		obNavChain.innerHTML = nav_chain;
	}
};

/* user options handling */
BX.userOptions = {
	options: null,
	bSend: false,
	delay: 5000,
	path: '/bitrix/admin/user_options.php?'
};

BX.userOptions.setAjaxPath = function(url)
{
	// eslint-disable-next-line no-console
	console.warn('BX.userOptions.setAjaxPath is deprecated. There is no way to change ajax path.');
};
BX.userOptions.save = function(category, name, valueName, value, common)
{
	if (BX.userOptions.options === null)
	{
		BX.userOptions.options = {};
	}

	common = Boolean(common);
	BX.userOptions.options[`${category}.${name}.${valueName}`] = [category, name, valueName, value, common];

	const stringPackedValue = BX.userOptions.__get();
	if (stringPackedValue)
	{
		document.cookie = `${BX.message('COOKIE_PREFIX')}_LAST_SETTINGS=${encodeURIComponent(stringPackedValue)}&sessid=${BX.bitrix_sessid()}; expires=Thu, 31 Dec ${(new Date()).getFullYear() + 1} 23:59:59 GMT; path=/;`;
	}

	if (!BX.userOptions.bSend)
	{
		BX.userOptions.bSend = true;
		setTimeout(() => {
			BX.userOptions.send(null);
		}, BX.userOptions.delay);
	}
};

BX.userOptions.send = function(callback)
{
	const values = BX.userOptions.__get_values({ backwardCompatibility: true});

	BX.userOptions.options = null;
	BX.userOptions.bSend = false;

	if (values)
	{
		document.cookie = `${BX.message('COOKIE_PREFIX')}_LAST_SETTINGS=; path=/;`;

		BX.ajax.runAction(
			'main.userOption.saveOptions',
			{
				json: {
					newValues: values,
				},
			},
		).then((response) => {
			if (BX.type.isFunction(callback))
			{
				callback(response);
			}
		});
	}
};

BX.userOptions.del = function(category, name, common, callback)
{
	BX.ajax.runAction(
		'main.userOption.deleteOption',
		{
			json: {
				category,
				name,
				common,
			},
		},
	).then((response) => {
		if (BX.type.isFunction(callback))
		{
			callback(response);
		}
	});
};

BX.userOptions.__get_values = function({ backwardCompatibility })
{
	if (!BX.userOptions || !BX.Type.isPlainObject(BX.userOptions.options))
	{
		return null;
	}

	const CATEGORY = 0;
	const NAME = 1;
	const VALUE_NAME = 2;
	const VALUE = 3;
	const IS_DEFAULT = 4;

	const packedValues = { p: [] };
	let currentIndex = -1;
	let previousOptionIdentifier = '';

	Object.entries(BX.userOptions.options).forEach(([key, userOption]) => {
		const category = userOption[CATEGORY];
		const name = userOption[NAME];
		const currentOptionIdentifier = `${category}.${name}`;

		if (previousOptionIdentifier !== currentOptionIdentifier)
		{
			currentIndex++;
			packedValues.p.push({
				c: category,
				n: name,
				v: {},
			});
			if (userOption[IS_DEFAULT] === true)
			{
				packedValues.p[currentIndex].d = 'Y';
			}
			previousOptionIdentifier = currentOptionIdentifier;
		}

		if (userOption[VALUE_NAME] === null)
		{
			packedValues.p[currentIndex].v = userOption[VALUE];
		}
		else
		{
			let data = userOption[VALUE];
			if (backwardCompatibility && Array.isArray(userOption[VALUE]))
			{
				data = userOption[VALUE].join(',');
			}
			packedValues.p[currentIndex].v[userOption[VALUE_NAME]] = data;
		}
	});

	return packedValues.p.length > 0 ? packedValues.p : null;
};

/**
 * @deprecated Use instead BX.userOptions.__get_values.
 * */
BX.userOptions.__get = function()
{
	if (!BX.userOptions.options) return '';

	var sParam = '', n = -1, prevParam = '', aOpt, i;

	for (i in BX.userOptions.options)
	{
		if(BX.userOptions.options.hasOwnProperty(i))
		{
			aOpt = BX.userOptions.options[i];

			if (prevParam != aOpt[0]+'.'+aOpt[1])
			{
				n++;
				sParam += '&p['+n+'][c]='+BX.util.urlencode(aOpt[0]);
				sParam += '&p['+n+'][n]='+BX.util.urlencode(aOpt[1]);
				if (aOpt[4] == true)
					sParam += '&p['+n+'][d]=Y';
				prevParam = aOpt[0]+'.'+aOpt[1];
			}

			var valueName = aOpt[2];
			var value = aOpt[3];

			if (valueName === null)
			{
				sParam += '&p['+n+'][v]='+BX.util.urlencode(value);
			}
			else
			{
				sParam += '&p['+n+'][v]['+BX.util.urlencode(valueName)+']='+BX.util.urlencode(value);
			}
		}
	}

	return sParam.substr(1);
};

BX.ajax.history = {
	expected_hash: '',

	obParams: null,

	obFrame: null,
	obImage: null,

	obTimer: null,

	bInited: false,
	bHashCollision: false,
	bPushState: !!(history.pushState && BX.type.isFunction(history.pushState)),

	startState: null,

	init: function(obParams)
	{
		if (BX.ajax.history.bInited)
			return;

		this.obParams = obParams;
		var obCurrentState = this.obParams.getState();

		if (BX.ajax.history.bPushState)
		{
			BX.ajax.history.expected_hash = window.location.pathname;
			if (window.location.search)
				BX.ajax.history.expected_hash += window.location.search;

			BX.ajax.history.put(obCurrentState, BX.ajax.history.expected_hash, '', true);
			// due to some strange thing, chrome calls popstate event on page start. so we should delay it
			setTimeout(function(){BX.bind(window, 'popstate', BX.ajax.history.__hashListener);}, 500);
		}
		else
		{
			BX.ajax.history.expected_hash = window.location.hash;

			if (!BX.ajax.history.expected_hash || BX.ajax.history.expected_hash == '#')
				BX.ajax.history.expected_hash = '__bx_no_hash__';

			jsAjaxHistoryContainer.put(BX.ajax.history.expected_hash, obCurrentState);
			BX.ajax.history.obTimer = setTimeout(BX.ajax.history.__hashListener, 500);

			if (BX.browser.IsIE())
			{
				BX.ajax.history.obFrame = document.createElement('IFRAME');
				BX.hide_object(BX.ajax.history.obFrame);

				document.body.appendChild(BX.ajax.history.obFrame);

				BX.ajax.history.obFrame.contentWindow.document.open();
				BX.ajax.history.obFrame.contentWindow.document.write(BX.ajax.history.expected_hash);
				BX.ajax.history.obFrame.contentWindow.document.close();
			}
			else if (BX.browser.IsOpera())
			{
				BX.ajax.history.obImage = document.createElement('IMG');
				BX.hide_object(BX.ajax.history.obImage);

				document.body.appendChild(BX.ajax.history.obImage);

				BX.ajax.history.obImage.setAttribute('src', 'javascript:location.href = \'javascript:BX.ajax.history.__hashListener();\';');
			}
		}

		BX.ajax.history.bInited = true;
	},

	__hashListener: function(e)
	{
		e = e || window.event || {state:false};

		if (BX.ajax.history.bPushState)
		{
			BX.ajax.history.obParams.setState(e.state||BX.ajax.history.startState);
		}
		else
		{
			if (BX.ajax.history.obTimer)
			{
				window.clearTimeout(BX.ajax.history.obTimer);
				BX.ajax.history.obTimer = null;
			}

			var current_hash;
			if (null != BX.ajax.history.obFrame)
				current_hash = BX.ajax.history.obFrame.contentWindow.document.body.innerText;
			else
				current_hash = window.location.hash;

			if (!current_hash || current_hash == '#')
				current_hash = '__bx_no_hash__';

			if (current_hash.indexOf('#') == 0)
				current_hash = current_hash.substring(1);

			if (current_hash != BX.ajax.history.expected_hash)
			{
				var state = jsAjaxHistoryContainer.get(current_hash);
				if (state)
				{
					BX.ajax.history.obParams.setState(state);

					BX.ajax.history.expected_hash = current_hash;
					if (null != BX.ajax.history.obFrame)
					{
						var __hash = current_hash == '__bx_no_hash__' ? '' : current_hash;
						if (window.location.hash != __hash && window.location.hash != '#' + __hash)
							window.location.hash = __hash;
					}
				}
			}

			BX.ajax.history.obTimer = setTimeout(BX.ajax.history.__hashListener, 500);
		}
	},

	put: function(state, new_hash, new_hash1, bStartState)
	{
		if (this.bPushState)
		{
			if(!bStartState)
			{
				history.pushState(state, '', new_hash);
			}
			else
			{
				BX.ajax.history.startState = state;
			}
		}
		else
		{
			if (typeof new_hash1 != 'undefined')
				new_hash = new_hash1;
			else
				new_hash = 'view' + new_hash;

			jsAjaxHistoryContainer.put(new_hash, state);
			BX.ajax.history.expected_hash = new_hash;

			window.location.hash = BX.util.urlencode(new_hash);

			if (null != BX.ajax.history.obFrame)
			{
				BX.ajax.history.obFrame.contentWindow.document.open();
				BX.ajax.history.obFrame.contentWindow.document.write(new_hash);
				BX.ajax.history.obFrame.contentWindow.document.close();
			}
		}
	},

	checkRedirectStart: function(param_name, param_value)
	{
		var current_hash = window.location.hash;
		if (current_hash.substring(0, 1) == '#') current_hash = current_hash.substring(1);

		var test = current_hash.substring(0, 5);
		if (test == 'view/' || test == 'view%')
		{
			BX.ajax.history.bHashCollision = true;
			document.write('<' + 'div id="__ajax_hash_collision_' + param_value + '" style="display: none;">');
		}
	},

	checkRedirectFinish: function(param_name, param_value)
	{
		document.write('</div>');

		var current_hash = window.location.hash;
		if (current_hash.substring(0, 1) == '#') current_hash = current_hash.substring(1);

		BX.ready(function ()
		{
			var test = current_hash.substring(0, 5);
			if (test == 'view/' || test == 'view%')
			{
				var obColNode = BX('__ajax_hash_collision_' + param_value);
				var obNode = obColNode.firstChild;
				BX.cleanNode(obNode);
				obColNode.style.display = 'block';

				// IE, Opera and Chrome automatically modifies hash with urlencode, but FF doesn't ;-(
				if (test != 'view%')
					current_hash = BX.util.urlencode(current_hash);

				current_hash += (current_hash.indexOf('%3F') == -1 ? '%3F' : '%26') + param_name + '=' + param_value;

				var url = '/bitrix/tools/ajax_redirector.php?hash=' + current_hash;

				BX.ajax.insertToNode(url, obNode);
			}
		});
	}
};

BX.ajax.component = function(node)
{
	this.node = node;
};

BX.ajax.component.prototype.getState = function()
{
	var state = {
		'node': this.node,
		'title': window.document.title,
		'data': BX(this.node).innerHTML
	};

	var obNavChain = BX('navigation');
	if (null != obNavChain)
		state.nav_chain = obNavChain.innerHTML;

	BX.onCustomEvent(BX(state.node), "onComponentAjaxHistoryGetState", [state]);

	return state;
};

BX.ajax.component.prototype.setState = function(state)
{
	BX(state.node).innerHTML = state.data;
	BX.ajax.UpdatePageTitle(state.title);

	if (state.nav_chain)
	{
		BX.ajax.UpdatePageNavChain(state.nav_chain);
	}

	BX.onCustomEvent(BX(state.node), "onComponentAjaxHistorySetState", [state]);
};

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
};


BX.ajax.FormData = function()
{
	this.elements = [];
	this.files = [];
	this.features = {};
	this.isSupported();
	this.log('BX FormData init');
};

BX.ajax.FormData.isSupported = function()
{
	var f = new BX.ajax.FormData();
	var result = f.features.supported;
	f = null;
	return result;
};

BX.ajax.FormData.prototype.log = function(o)
{
	if (false) {
		try {
			if (BX.browser.IsIE()) o = JSON.stringify(o);
			console.log(o);
		} catch(e) {}
	}
};

BX.ajax.FormData.prototype.isSupported = function()
{
	var f = {};
	f.fileReader = (window.FileReader && window.FileReader.prototype.readAsBinaryString);
	f.readFormData = f.sendFormData = !!(window.FormData);
	f.supported = !!(f.readFormData && f.sendFormData);
	this.features = f;
	this.log('features:');
	this.log(f);

	return f.supported;
};

BX.ajax.FormData.prototype.append = function(name, value)
{
	if (typeof(value) === 'object') { // seems to be files element
		this.files.push({'name': name, 'value':value});
	} else {
		this.elements.push({'name': name, 'value':value});
	}
};

BX.ajax.FormData.prototype.send = function(url, callbackOk, callbackProgress, callbackError)
{
	this.log('FD send');
	this.xhr = BX.ajax({
			'method': 'POST',
			'dataType': 'html',
			'url': url,
			'onsuccess': callbackOk,
			'onfailure': callbackError,
			'start': false,
			'preparePost':false
		});

	if (callbackProgress)
	{
		this.xhr.upload.addEventListener(
			'progress',
			function(e) {
				if (e.lengthComputable)
					callbackProgress(e.loaded / (e.total || e.totalSize));
			},
			false
		);
	}

	if (this.features.readFormData && this.features.sendFormData)
	{
		var fd = new FormData();
		this.log('use browser formdata');
		for (var i in this.elements)
		{
			if(this.elements.hasOwnProperty(i))
				fd.append(this.elements[i].name,this.elements[i].value);
		}
		for (i in this.files)
		{
			if(this.files.hasOwnProperty(i))
				fd.append(this.files[i].name, this.files[i].value);
		}
		this.xhr.send(fd);
	}

	return this.xhr;
};

BX.addCustomEvent('onAjaxFailure', BX.debug);
})(window);
