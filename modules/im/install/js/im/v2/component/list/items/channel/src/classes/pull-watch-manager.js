import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';

import type { PULL as Pull } from 'pull.client';

const TAG = 'IM_SHARED_CHANNEL_LIST';

export class PullWatchManager
{
	#pullClient: Pull;

	constructor()
	{
		this.#pullClient = Core.getPullClient();
	}

	subscribe(): void
	{
		this.#pullClient.extendWatch(TAG);
		this.#requestWatchStart();
	}

	unsubscribe(): void
	{
		this.#pullClient.clearWatch(TAG);
	}

	#requestWatchStart(): void
	{
		void runAction(RestMethod.imV2RecentChannelExtendPullWatch);
	}
}
