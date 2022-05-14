import {Type} from 'main.core';
import {EventEmitter, Event} from 'main.core.events';
import Options from "./options";

const buildAjaxPromiseToRestoreCsrf = function(config, withoutRestoringCsrf)
{
	withoutRestoringCsrf = withoutRestoringCsrf || false;
	const originalConfig = Object.assign({}, config);
	let request = null;
	config.onrequeststart = (xhr) => {
		request = xhr;
	}
	const promise = BX.ajax.promise(config);

	return promise
		.then(
			function(response) {
				if (!withoutRestoringCsrf
					&& Type.isPlainObject(response)
					&& response['errors']
				)
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
				return response;
			}
		)
		.then(
			function(response){
				var assetsLoaded = new BX.Promise();
				assetsLoaded.fulfill(response);
				return assetsLoaded;
			}
		)
		.catch(
			function({reason, data})
			{
				if (reason === 'status'
					&& data
					&& (String(data).indexOf('503') >= 0
						|| String(data).indexOf('504') >= 0)
				)
				{
					originalConfig['50xCounter'] = (originalConfig['50xCounter'] || 0) + 1;
					if (originalConfig['50xCounter'] <= 2)
					{
						var headers = request.getAllResponseHeaders().trim().split(/[\r\n]+/);
						var headerMap = {};
						headers.forEach(function (line) {
							var parts = line.split(': ');
							var header = parts.shift().toLowerCase();
							headerMap[header] = parts.join(': ');
						});
						let timeoutSec = null;
						if (headerMap['retry-after'] && /\d+/.test(headerMap['retry-after']))
						{
							timeoutSec = parseInt(headerMap['retry-after']);
						}

						const p = new BX.Promise();
						setTimeout(() => {
							p.fulfill();
						}, (timeoutSec || 20) * 1000);
						return p.then(() => {
							return buildAjaxPromiseToRestoreCsrf(originalConfig);
						});
					}
				}

				var ajaxReject = new BX.Promise();

				if (Type.isPlainObject(data)
					&& data.status
					&& data.hasOwnProperty('data'))
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
			}
		);
};

export default class Stream extends EventEmitter
{
	constructor()
	{
		super();
		this.setEventNamespace(Options.getEventNamespace());
		this.onprogress = this.onprogress.bind(this);
		this.onprogressupload = this.onprogressupload.bind(this);
	}

	send(url, formData: FormData)
	{
		this.deltaTime = (-1) * (new Date()).getTime();
		this.totalSize = null;
		buildAjaxPromiseToRestoreCsrf({
			method: 'POST',
			dataType: 'json',
			url: url,
			data: formData,
			timeout: Options.getMaxTimeToUploading(),
			preparePost: false,
			headers: [
				{name: 'X-Bitrix-Csrf-Token', value: BX.bitrix_sessid()},
				{name: 'X-Bitrix-Site-Id', value: (BX.message.SITE_ID || '')}
			],
			onprogress: this.onprogress,
			onprogressupload: this.onprogressupload
		})
		.then((response) => {
			this.done({status: 'success', data: response});
		})
		.catch(({errors, data}) => {
			this.done({status: 'failed', errors: errors.map(({code, message}) => {return message;}), data: data});
		})
		.catch((response) => {
			this.done({status: 'failed', errors: ['Unexpected server response.'], data: response});
		});
	}

	onprogress(e)
	{
	}

	onprogressupload(e)
	{
		var procent = 5;
		if(typeof e == "object" && e.lengthComputable) {
			procent = e.loaded * 100 / (e["total"] || e["totalSize"]);
			this.totalSize = (e["total"] || e["totalSize"]);
		}
		else if (e > procent)
			procent = e;
		procent = (procent > 5 ? procent : 5);
		this.emit('progress', procent);
	}

	done(response)
	{
		this.deltaTime += (new Date()).getTime();
		Options.calibratePostSize(this.deltaTime, this.totalSize);

		this.emit('done', response);
	}

	destroy()
	{
		console.log('Clear all from stream');
	}
}