import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

export class UpdateStateManager
{
	#sessionTime: number;

	static init(): UpdateStateManager
	{
		return new UpdateStateManager();
	}

	constructor()
	{
		const { sessionTime } = Core.getApplicationData();
		this.#sessionTime = sessionTime * 1000;

		this.#startUpdateInterval();
	}

	#startUpdateInterval()
	{
		setInterval(() => {
			this.#requestUpdate();
		}, this.#sessionTime);
	}

	#requestUpdate()
	{
		Logger.warn('Desktop: updateStateManager: requesting update');
		runAction(RestMethod.imV2UpdateState)
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('Desktop: updateStateManager: error updating state', error);
			});
	}
}
