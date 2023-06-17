import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

export class VersionService
{
	store: Object = null;
	restClient: Object = null;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	disableV2Version(): Promise
	{
		Logger.warn('VersionService: disable v2');
		return this.restClient.callMethod(RestMethod.imVersionV2Disable).catch(error => {
			console.error('VersionService: disable v2 error', error);
		});
	}
}