'use strict';

;(function(){

	/****************** ATTENTION *******************************
	 * Please do not use Bitrix CoreJS in this class.
	 * This class can be called on page without Bitrix Framework
	*************************************************************/

	if (!window.BX)
	{
		window.BX = {};
	}
	else if (window.BX.RestClient)
	{
		return;
	}

	var BX = window.BX;

	BX.RestClient = function (options)
	{
		options = options || {};

		this.endpoint = options.endpoint || '/rest';
		this.queryParams = options.queryParams || '';
		this.cors = options.cors === true;
	};

	BX.RestClient.prototype.callMethod = function(method, params, callback, sendCallback, logTag)
	{
		return ajax({
			method: method,
			data: params,
			callback: callback,
			sendCallback: sendCallback,
			logTag: logTag,
			endpoint: this.endpoint,
			queryParams: this.queryParams,
			cors: this.cors
		});
	};

	/*
	calls = [[method,params],[method,params]];
	calls = [{method:method,params:params},[method,params]];
	calls = {call_id:[method,params],...};
	*/
	BX.RestClient.prototype.callBatch = function(calls, callback, bHaltOnError, sendCallback, logTag)
	{
		var cmd = Utils.isArray(calls) ? [] : {};
		var cnt = 0;
		var cb = function(cmd) {
			ajax.batch(cmd, callback, bHaltOnError, sendCallback, this.endpoint, this.queryParams, this.cors, logTag);
		}.bind(this);

		for(var i in calls)
		{
			var method = null, params = null;

			if(!!calls[i] && calls.hasOwnProperty(i))
			{
				if(Utils.isArray(calls[i]))
				{
					method = calls[i][0];
					params = calls[i][1];
				}
				else if(!!calls[i].method)
				{
					method = calls[i].method;
					params = calls[i].params;
				}

				if(!!method)
				{
					cnt++;
					cmd[i] = [method, params];
				}
			}
		}

		if(cnt > 0)
		{
			var e = function(i)
			{
				return function(str)
				{
					cmd[i] = cmd[i][0] + '?' + str;
					if(--cnt <= 0)
						cb(cmd);
				}
			};

			for(var c in cmd)
			{
				if(cmd.hasOwnProperty(c))
				{
					ajax.prepareData(cmd[c][1], '', e(c));
				}
			}
		}
	};

	BX.RestClient.prototype.setEndpoint = function(url)
	{
		this.endpoint = url;
	};

	BX.RestClient.prototype.enableCorsRequest = function(value)
	{
		this.cors = value === true;
	};

	BX.RestClient.prototype.setQueryParams = function(params)
	{
		this.queryParams = params;
	};

	/* self init for bitrix env */
	if (typeof BX.namespace !== 'undefined')
	{
		var BXRest = new BX.RestClient();

		if (typeof BX.rest == 'undefined')
		{
			BX.rest = {};
		}

		BX.rest.callMethod = function (method, params, callback, sendCallback, logTag)
		{
			return BXRest.callMethod(method, params, callback, sendCallback, logTag);
		};

		/*
		calls = [[method,params],[method,params]];
		calls = [{method:method,params:params},[method,params]];
		calls = {call_id:[method,params],...};
		*/
		BX.rest.callBatch = function (calls, callback, bHaltOnError, sendCallback, logTag)
		{
			return BXRest.callBatch(calls, callback, bHaltOnError, sendCallback, logTag);
		};
	}

	var Utils = {
		isArray: function(item) {
			return item && Object.prototype.toString.call(item) == "[object Array]";
		},
		isFunction: function(item) {
			return item === null ? false : (typeof (item) == "function" || item instanceof Function);
		},
		isString: function(item) {
			return item === '' ? true : (item ? (typeof (item) == "string" || item instanceof String) : false);
		},
		isDomNode: function(item) {
			return item && typeof (item) == "object" && "nodeType" in item;
		},
		isDate: function(item) {
			return item && Object.prototype.toString.call(item) == "[object Date]";
		},
		buildQueryString: function(params)
		{
			var result = '';
			for (var key in params)
			{
				if (!params.hasOwnProperty(key))
				{
					continue;
				}
				var value = params[key];
				if(this.isArray(value))
				{
					value.forEach(function(valueElement, index)
					{
						result += encodeURIComponent(key + "[" + index + "]") + "=" + encodeURIComponent(valueElement) + "&";
					});
				}
				else
				{
					result += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
				}
			}

			if(result.length > 0)
			{
				result = result.substr(0, result.length - 1);
			}
			return result;
		},
		clone: function(obj, bCopyObj)
		{
			var _obj, i, l;
			if (bCopyObj !== false)
				bCopyObj = true;

			if (obj === null)
				return null;

			if (this.isDomNode(obj))
			{
				_obj = obj.cloneNode(bCopyObj);
			}
			else if (typeof obj == 'object')
			{
				if (this.isArray(obj))
				{
					_obj = [];
					for (i=0,l=obj.length;i<l;i++)
					{
						if (typeof obj[i] == "object" && bCopyObj)
							_obj[i] = this.clone(obj[i], bCopyObj);
						else
							_obj[i] = obj[i];
					}
				}
				else
				{
					_obj =  {};
					if (obj.constructor)
					{
						if (this.isDate(obj))
							_obj = new Date(obj);
						else
							_obj = new obj.constructor();
					}

					for (i in obj)
					{
						if (typeof obj[i] == "object" && bCopyObj)
							_obj[i] = this.clone(obj[i], bCopyObj);
						else
							_obj[i] = obj[i];
					}
				}

			}
			else
			{
				_obj = obj;
			}

			return _obj;
		}
	};

	var ajax = function(config)
	{
		var hasCallback = !!config.callback && Utils.isFunction(config.callback);
		var promise = typeof BX.Promise === 'undefined' || hasCallback? null: new BX.Promise();
		var sendCallback = config.sendCallback || function() {};
		var withoutRestoringCsrf = config.withoutRestoringCsrf || false;

		var xhr = ajax.xhr();

		var url = config.endpoint + '/' + ajax.escape(config.method) + '.json'+(config.logTag? '?logTag='+config.logTag: '');

		xhr.open('POST', url);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		if (config.cors)
		{
			xhr.withCredentials = true;
		}

		var bRequestCompleted = false;

		// IE fix
		xhr.onprogress = function(){};
		xhr.ontimeout = function(){};
		xhr.timeout = 0;

		xhr.onload = function()
		{
			if(bRequestCompleted)
				return;

			xhr.onload = function() {};

			var bSuccess = ajax.isSuccess(xhr);

			var status = xhr.status;
			if(bSuccess)
			{
				var data = xhr.responseText;

				if(data.length > 0)
				{
					try
					{
						data = JSON.parse(data);
					}
					catch(e)
					{
						bSuccess = false;
					}
				}

				if (status == 401)
				{
					if (data.sessid && !withoutRestoringCsrf)
					{
						BX.message({'bitrix_sessid': data.sessid});
						console.warn('BX.rest: your csrf-token has expired, send query with a new token');

						config.withoutRestoringCsrf = true;

						if (!hasCallback)
						{
							config.callback = function(result)
							{
								if (result.error())
								{
									promise.reject(result);
								}
								else
								{
									promise.fulfill(result);
								}
							}
						}
						ajax(config);

						return true;
					}
				}
				else if (status == 0)
				{
					data = {result: {}, error: "ERROR_NETWORK", error_description: "A network error occurred while the request was being executed."};
				}
				else
				{
					if (status == 200)
					{
						if (data.length <= 0)
						{
							data = {result: {}, error: "BLANK_ANSWER", error_description: "Empty answer with correct http code, network error possible."};
						}
					}
					else if (data.length <= 0)
					{
						data = {result: {}, error: "BLANK_ANSWER_WITH_ERROR_CODE", error_description: 'Empty answer with error http code: '+status};
					}
				}
			}

			xhr = null;
			if(bSuccess)
			{
				var res = new ajaxResult(data, config, status);
				if(hasCallback)
				{
					config.callback.apply(window, [res]);
				}
				else
				{
					if (res.error())
					{
						promise.reject(res);
					}
					else
					{
						promise.fulfill(res);
					}
				}
			}
			else
			{
				var res = new ajaxResult({
					error: "ERROR_UNEXPECTED_ANSWER",
					error_description: "Server returned an unexpected response.",
					ex: {}
				}, config, 0);
				if(hasCallback)
				{
					config.callback.apply(window, [res]);
				}
				else
				{
					promise.reject(res);
				}
			}
		};

		xhr.onerror = function(e)
		{
			var res = new ajaxResult({
				error: "ERROR_NETWORK",
				error_description: "A network error occurred while the request was being executed.",
				ex: e
			}, config, 0);
			if(hasCallback)
			{
				config.callback.apply(window, [res]);
			}
			else
			{
				promise.reject(res);
			}
		};

		var query_data = '';
		if (config.queryParams)
		{
			query_data = Utils.buildQueryString(config.queryParams);
		}
		else if (typeof BX.bitrix_sessid !== 'undefined')
		{
			query_data = 'sessid=' + BX.bitrix_sessid();
		}

		if(typeof config.start !== 'undefined')
		{
			query_data += '&start=' + parseInt(config.start);
		}

		if(!!config.data)
		{
			ajax.prepareData(config.data, '', function(res)
			{
				query_data += '&' + res;
				xhr.send(query_data);
				sendCallback(xhr);
			});
		}
		else
		{
			xhr.send(query_data);
			sendCallback(xhr);
		}

		return hasCallback || !promise? xhr: promise;
	};

	ajax.batch = function(calls, callback, bHaltOnError, sendCallback, endpoint, queryParams, cors, logTag)
	{
		return ajax({
			method: 'batch',
			data: {halt: !!bHaltOnError ? 1 : 0, cmd: calls},
			callback: function(res, config, status)
			{
				if(!callback)
				{
					return false;
				}

				var error = res.error();
				var data = res.data();
				var result = Utils.isArray(calls) ? [] : {};

				for(var i in calls)
				{
					if(!!calls[i] && calls.hasOwnProperty(i))
					{
						if(Utils.isString(calls[i]))
						{
							var q = calls[i].split('?');
						}
						else
						{
							q = [
								Utils.isArray(calls[i]) ? calls[i][0] : calls[i].method,
								Utils.isArray(calls[i]) ? calls[i][1] : calls[i].data
							];
						}

						if (
							data
							&& typeof data.result !== 'undefined'
							&& (
								typeof data.result[i] !== 'undefined'
								|| typeof data.result_error[i] !== 'undefined'
							)
						)
						{
							result[i] = new ajaxResult({
								result: typeof data.result[i] !== 'undefined' ? data.result[i] : {},
								error: data.result_error[i] || undefined,
								total: data.result_total[i],
								time: data.result_time[i],
								next: data.result_next[i]
							}, {
								method: q[0],
								data: q[1],
								callback: callback,
								endpoint: endpoint,
								queryParams: queryParams,
								cors: cors
							}, res.status);
						}
						else if (error)
						{
							result[i] = new ajaxResult({
								result: {},
								error: error.ex,
								total: 0
							}, {
								method: q[0],
								data: q[1],
								callback: callback,
								endpoint: endpoint,
								queryParams: queryParams,
								cors: cors
							}, res.status);
						}
					}
				}

				callback.apply(window, [result]);
			},
			sendCallback: sendCallback,
			endpoint: endpoint,
			queryParams: queryParams,
			cors: cors,
			logTag: logTag
		});
	};

	ajax.xhr = function()
	{
		return new XMLHttpRequest();
	};

	ajax.escape = function(str)
	{
		return encodeURIComponent(str);
	};

	ajax.prepareData = function(arData, prefix, callback)
	{
		var data = '', objects = [];
		if(Utils.isString(arData) || arData === null)
		{
			callback.call(document, arData || '');
		}
		else
		{
			for(var i in arData)
			{
				if(!arData.hasOwnProperty(i))
				{
					continue;
				}

				var name = ajax.escape(i);

				if(prefix)
					name = prefix + '[' + name + ']';

				if(typeof arData[i] === 'object')
				{
					objects.push([name, arData[i]]);
				}
				else
				{
					if(data.length > 0)
					{
						data += '&';
					}

					if(typeof arData[i] === 'boolean')
					{
						data += name + '=' + (arData[i]? 1: 0);
					}
					else
					{
						data += name + '=' + ajax.escape(arData[i])
					}
				}
			}

			var cnt = objects.length;
			if(cnt > 0)
			{
				var cb = function(str)
				{
					data += (!!str ? '&' : '') + str;
					if(--cnt <= 0)
					{
						callback.call(document, data)
					}
				};

				var cnt1 = cnt;
				for(var i = 0; i < cnt1; i++)
				{
					if(Utils.isDomNode(objects[i][1]))
					{
						if(objects[i][1].tagName.toUpperCase() === 'INPUT' && objects[i][1].type === 'file')
						{
							if(fileReader.canUse())
							{
								fileReader(objects[i][1], (function(name)
								{
									return function(result)
									{
										if(Utils.isArray(result) && result.length > 0)
										{
											cb(name + '[0]=' + ajax.escape(result[0]) + '&' + name + '[1]=' + ajax.escape(result[1]));
										}
										else
										{
											cb(name + '=');
										}
									}
								})(objects[i][0]));
							}
						}
						else if(typeof objects[i][1].value !== 'undefined')
						{
							cb(objects[i][0] + '=' + ajax.escape(objects[i][1].value));
						}
						else
						{
							cb('');
						}
					}
					else if(Utils.isDate(objects[i][1]))
					{
						cb(objects[i][0] + '=' + ajax.escape(objects[i][1].toJSON()));
					}
					else if(Utils.isArray(objects[i][1]) && objects[i][1].length <= 0)
					{
						cb(objects[i][0] + '=');
					}
					else
					{
						ajax.prepareData(objects[i][1], objects[i][0], cb);
					}
				}
			}
			else
			{
				callback.call(document, data)
			}
		}
	};

	ajax.isSuccess = function(xhr)
	{
		return typeof xhr.status === 'undefined' || (xhr.status >= 200 && xhr.status < 300) || xhr.status === 304 || xhr.status >= 400 && xhr.status < 500 || xhr.status === 1223 || xhr.status === 0;
	};

	var ajaxResult = function(answer, query, status)
	{
		this.answer = answer;
		this.query = Utils.clone(query);
		this.status = status;

		if(typeof this.answer.next !== 'undefined')
		{
			this.answer.next = parseInt(this.answer.next);
		}

		if(typeof this.answer.error !== 'undefined')
		{
			this.answer.ex = new ajaxError(this.status, typeof this.answer.error === 'string' ? this.answer : this.answer.error)
		}
	};

	ajaxResult.prototype.data = function()
	{
		return this.answer.result;
	};

	ajaxResult.prototype.time = function()
	{
		return this.answer.time;
	};

	ajaxResult.prototype.error = function()
	{
		return this.answer.ex;
	};

	ajaxResult.prototype.error_description = function()
	{
		return this.answer.error_description;
	};

	ajaxResult.prototype.more = function()
	{
		return !isNaN(this.answer.next);
	};

	ajaxResult.prototype.total = function()
	{
		return parseInt(this.answer.total);
	};

	ajaxResult.prototype.next = function(cb)
	{
		if(this.more())
		{
			this.query.start = this.answer.next;

			if(!!cb && Utils.isFunction(cb))
			{
				this.query.callback = cb;
			}

			return ajax(this.query);
		}

		return false;
	};

	var ajaxError = function(status, ex)
	{
		this.status = status;
		this.ex = ex;
	};

	ajaxError.prototype.getError = function()
	{
		return this.ex;
	};

	ajaxError.prototype.getStatus = function()
	{
		return this.status;
	};

	ajaxError.prototype.toString = function()
	{
		return this.ex.error + (
			!!this.ex.error_description
				? ': ' + this.ex.error_description
				: ''
		) + ' (' + this.status + ')';
	};

	var arrayBufferToData = function(arrayBuffer)
	{
		var uint8 = new Uint8Array(arrayBuffer);
		var data = '';

		// TypedArray.prototype.forEach is not supported in some browsers as IE.
		if (typeof uint8.forEach === 'function')
		{
			uint8.forEach(function (value)
			{
				data += String.fromCharCode(value);
			});
		}
		else
		{
			var length = uint8.length;
			for (var i = 0; i < length; i += 1)
			{
				data += String.fromCharCode(uint8[i]);
			}
		}
		return btoa(data);
	};

	var fileReader = function(fileInput, cb)
	{
		if(fileReader.canUse())
		{
			var files = fileInput.files,
				len = 0,
				result = fileInput.multiple ? [] : null;

			for(var i = 0, f; f = files[i]; i++)
			{
				var reader = new window.FileReader();

				reader.BXFILENAME = files[i].name;

				reader.onload = function(e)
				{
					e = e || window.event;

					var res = [this.BXFILENAME, arrayBufferToData(e.target.result)];

					if(result === null)
						result = res;
					else
						result.push(res);

					if(--len <= 0)
					{
						cb(result);
					}
				};

				reader.readAsArrayBuffer(f);
			}
			len = i;
			if(len <= 0)
			{
				cb(result);
			}
		}
	};

	fileReader.canUse = function()
	{
		return !!window.FileReader;
	};

})();