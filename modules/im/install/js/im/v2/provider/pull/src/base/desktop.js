import {Logger} from 'im.v2.lib.logger';
import {DesktopManager} from 'im.v2.lib.desktop';
import {CounterManager} from 'im.v2.lib.counter';

import type {DesktopOnlineParams} from '../types/desktop';

export class DesktopPullHandler
{
	handleDesktopOnline(params: DesktopOnlineParams)
	{
		Logger.warn('DesktopPullHandler: handleDesktopOnline', params);
		const desktopManager = DesktopManager.getInstance();
		desktopManager.setDesktopActive(true);
		desktopManager.setDesktopVersion(params.version);

		CounterManager.getInstance().removeBrowserTitleCounter();
	}

	handleDesktopOffline()
	{
		Logger.warn('DesktopPullHandler: handleDesktopOffline');
		DesktopManager.getInstance().setDesktopActive(false);
	}
}