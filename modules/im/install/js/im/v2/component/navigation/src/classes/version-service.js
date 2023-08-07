import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

export class VersionService
{
	store: Object = null;
	restClient: Object = null;

	constructor()
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	disableBeta(): Promise
	{
		Logger.warn('VersionService: disable v2');

		return runAction(RestMethod.imV2BetaDisable).catch((error) => {
			Logger.error('VersionService: disable v2 error', error);
		});
	}
}
