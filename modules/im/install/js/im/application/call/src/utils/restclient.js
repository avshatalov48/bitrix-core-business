import { RestClient } from "rest.client";
import { Utils } from "im.lib.utils";

const RestAuth = Object.freeze({
	guest: 'guest',
});

export class CallRestClient
{
	constructor(params)
	{
		this.queryAuthRestore = false;

		this.setAuthId(RestAuth.guest);

		this.restClient = new RestClient({
			endpoint: params.endpoint,
			queryParams: this.queryParams,
			cors: true
		});
	}

	setAuthId(authId, customAuthId = '')
	{
		if (typeof this.queryParams !== 'object')
		{
			this.queryParams = {};
		}

		if (
			authId == RestAuth.guest
			|| typeof authId === 'string' && authId.match(/^[a-f0-9]{32}$/)
		)
		{
			this.queryParams.call_auth_id = authId;
		}
		else
		{
			console.error(`%CallRestClient.setAuthId: auth is not correct (%c${authId}%c)`, "color: black;", "font-weight: bold; color: red", "color: black");
			return false;
		}

		if (
			authId == RestAuth.guest
			&& typeof customAuthId === 'string' && customAuthId.match(/^[a-f0-9]{32}$/)
		)
		{
			this.queryParams.call_custom_auth_id = customAuthId;
		}

		return true;
	}

	setChatId(chatId)
	{
		if (typeof this.queryParams !== 'object')
		{
			this.queryParams = {};
		}

		this.queryParams.call_chat_id = chatId;
	}

	setConfId(alias)
	{
		if (typeof this.queryParams !== 'object')
		{
			this.queryParams = {};
		}

		this.queryParams.videoconf_id = alias;
	}

	setPassword(password)
	{
		if (typeof this.queryParams !== 'object')
		{
			this.queryParams = {};
		}

		this.queryParams.videoconf_password = password;
	}

	callMethod(method, params, callback, sendCallback, logTag = null)
	{
		if (!logTag)
		{
			logTag = Utils.getLogTrackingParams({
				name: method,
			});
		}

		const promise = new BX.Promise();

		// TODO: Callbacks methods will not work!
		this.restClient.callMethod(method, params, null, sendCallback, logTag).then(result => {

			this.queryAuthRestore = false;
			promise.fulfill(result);

		}).catch(result => {

			let error = result.error();
			if (error.ex.error == 'LIVECHAT_AUTH_WIDGET_USER')
			{
				this.setAuthId(error.ex.hash);

				if (method === RestMethod.widgetUserRegister)
				{
					console.warn(`BX.LiveChatRestClient: ${error.ex.error_description} (${error.ex.error})`);

					this.queryAuthRestore = false;
					promise.reject(result);
					return false;
				}

				if (!this.queryAuthRestore)
				{
					console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');

					this.queryAuthRestore = true;
					this.restClient.callMethod(method, params, null, sendCallback, logTag).then(result => {
						this.queryAuthRestore = false;
						promise.fulfill(result);
					}).catch(result => {
						this.queryAuthRestore = false;
						promise.reject(result);
					});

					return false;
				}
			}

			this.queryAuthRestore = false;
			promise.reject(result);
		});

		return promise;
	};

	callBatch(calls, callback, bHaltOnError, sendCallback, logTag)
	{
		let resultCallback = (result) => {
			let error = null;
			for (let method in calls)
			{
				if (!calls.hasOwnProperty(method))
				{
					continue;
				}

				let error = result[method].error();
				if (error && error.ex.error == 'LIVECHAT_AUTH_WIDGET_USER')
				{
					this.setAuthId(error.ex.hash);
					if (method === RestMethod.widgetUserRegister)
					{
						console.warn(`BX.LiveChatRestClient: ${error.ex.error_description} (${error.ex.error})`);

						this.queryAuthRestore = false;
						callback(result);
						return false;
					}

					if (!this.queryAuthRestore)
					{
						console.warn('BX.LiveChatRestClient: your auth-token has expired, send query with a new token');

						this.queryAuthRestore = true;
						this.restClient.callBatch(calls, callback, bHaltOnError, sendCallback, logTag);

						return false;
					}
				}
			}

			this.queryAuthRestore = false;
			callback(result);

			return true;
		};

		return this.restClient.callBatch(calls, resultCallback, bHaltOnError, sendCallback, logTag);
	};
}