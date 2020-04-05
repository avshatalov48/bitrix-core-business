;(function(){
	'use strict';

	BX.namespace('BX.rest');

	var endpoint = '/rest';

	if(!!BX.rest.callMethod)
	{
		return;
	}

	BX.rest.callMethod = function(method, params, callback, sendCallback)
	{
		return ajax({
			method: method,
			data: params,
			callback: callback,
			sendCallback: sendCallback
		});
	};

	/*
	calls = [[method,params],[method,params]];
	calls = [{method:method,params:params},[method,params]];
	calls = {call_id:[method,params],...};
	*/
	BX.rest.callBatch = function(calls, callback, bHaltOnError, sendCallback)
	{
		var cmd = BX.type.isArray(calls) ? [] : {};
		var cnt = 0;
		var cb = function(cmd)
		{
			ajax.batch(cmd, callback, bHaltOnError, sendCallback);
		};

		for(var i in calls)
		{
			var method = null, params = null;

			if(!!calls[i] && calls.hasOwnProperty(i))
			{
				if(BX.type.isArray(calls[i]))
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

	var ajax = function(config)
	{
		var hasCallback = !!config.callback && BX.type.isFunction(config.callback);
		var promise = hasCallback? null: new BX.Promise();
		var sendCallback = config.sendCallback || function() {};

		var xhr = ajax.xhr();

		var url = endpoint + '/' + ajax.escape(config.method) + '.json';

		xhr.open('POST', url);
		xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

		var bRequestCompleted = false;

		// IE fix
		xhr.onprogress = function(){};
		xhr.ontimeout = function(){};
		xhr.timeout = 0;

		xhr.onload = function()
		{
			if(bRequestCompleted)
				return;

			xhr.onload = BX.DoNothing;

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
				else if (status == 200)
				{
					data = {result: {}};
				}
				else if (status == 0)
				{
					data = {result: {}, error: "ERROR_NETWORK", error_description: "A network error occurred while the request was being executed."};
				}
				else
				{
					data = {result: {}, error: "BLANK_ANSWER_WITH_ERROR_CODE", error_description: 'Blank answer with error http code: '+status};
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

		var query_data = 'sessid=' + BX.bitrix_sessid();

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

		return hasCallback? xhr: promise;
	};

	ajax.batch = function(calls, callback, bHaltOnError, sendCallback)
	{
		return ajax({
			method: 'batch',
			data: {halt: !!bHaltOnError ? 1 : 0, cmd: calls},
			callback: function(res, config, status)
			{
				if(!!callback)
				{
					var error = res.error();
					var data = res.data();
					var result = BX.type.isArray(calls) ? [] : {};

					for(var i in calls)
					{
						if(!!calls[i] && calls.hasOwnProperty(i))
						{
							if(BX.type.isString(calls[i]))
							{
								var q = calls[i].split('?');
							}
							else
							{
								q = [
									BX.type.isArray(calls[i]) ? calls[i][0] : calls[i].method,
									BX.type.isArray(calls[i]) ? calls[i][1] : calls[i].data
								];
							}

							if(data && (typeof data.result[i] !== 'undefined' || typeof data.result_error[i] !== 'undefined'))
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
									callback: callback
								}, res.status);
							}
							else if (error)
							{
								result[i] = new ajaxResult({
									result: {},
									error: {error: error.ex.error, description: error.ex.error_description},
									total: 0
								}, {
									method: q[0],
									data: q[1],
									callback: callback
								}, res.status);
							}
						}
					}

					callback.apply(window, [result]);
				}
			},
			sendCallback: sendCallback
		});
	};

	ajax.xhr = function()
	{
		return new XMLHttpRequest();
	};

	ajax.escape = function(str)
	{
		return BX.util.urlencode(str);
	};

	ajax.prepareData = function(arData, prefix, callback)
	{
		var data = '', objects = [];
		if(BX.type.isString(arData) || arData === null)
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
					if(BX.type.isDomNode(objects[i][1]))
					{
						if(objects[i][1].tagName.toUpperCase() === 'INPUT' && objects[i][1].type === 'file')
						{
							if(fileReader.canUse())
							{
								fileReader(objects[i][1], (function(name)
								{
									return function(result)
									{
										if(BX.type.isArray(result) && result.length > 0)
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
					else if(BX.type.isDate(objects[i][1]))
					{
						cb(objects[i][0] + '=' + ajax.escape(objects[i][1].toJSON()));
					}
					else if(BX.type.isArray(objects[i][1]) && objects[i][1].length <= 0)
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
		this.query = BX.clone(query);
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

			if(!!cb && BX.type.isFunction(cb))
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

					var res = [this.BXFILENAME, btoa(e.target.result)];

					if(result === null)
						result = res;
					else
						result.push(res);

					if(--len <= 0)
					{
						cb(result);
					}
				};

				reader.readAsBinaryString(f);
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