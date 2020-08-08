import {Text, Type} from 'main.core';

export default class LazyLoader
{
	constructor(id, settings)
	{
		this.id = Type.isStringFilled(id) ? id : Text.getRandom();
		this.settings = Type.isObjectLike(settings) ? settings : {};

		this.container = this.settings.container;
		if (!this.container)
		{
			throw 'Error: Could not find container.';
		}

		this.serviceUrl = this.settings.serviceUrl || '';
		if (!Type.isStringFilled(this.serviceUrl))
		{
			throw 'Error. Could not find service url.';
		}

		this.tabId = this.settings.tabId || '';
		if (!Type.isStringFilled(this.tabId))
		{
			throw 'Error: Could not find tab id.';
		}

		this.params = Type.isObjectLike(this.settings.componentData) ? this.settings.componentData : {};

		this.isRequestRunning = false;
		this.loaded = false;
	}

	isLoaded()
	{
		return this.loaded;
	}

	load()
	{
		if (!this.isLoaded())
		{
			this.startRequest({...this.params, ...{'TABID': this.tabId}});
		}
	}

	startRequest(params)
	{
		if (this.isRequestRunning)
		{
			return false;
		}

		this.isRequestRunning = true;

		BX.ajax({
			url: this.serviceUrl,
			method: 'POST',
			dataType: 'html',
			data: {
				'LOADERID': this.id,
				'PARAMS': params
			},
			onsuccess: this.onRequestSuccess.bind(this),
			onfailure: this.onRequestFailure.bind(this)
		});

		return true;
	}

	onRequestSuccess(data)
	{
		this.isRequestRunning = false;
		this.container.innerHTML = data;
		this.loaded = true;
	}

	onRequestFailure()
	{
		this.isRequestRunning = false;
		this.loaded = true;
	}
}