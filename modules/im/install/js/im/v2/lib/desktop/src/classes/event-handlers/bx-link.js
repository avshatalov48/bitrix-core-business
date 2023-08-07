import { Monitor } from 'timeman.monitor';

import { Messenger } from 'im.public';
import { EventType, DesktopBxLink } from 'im.v2.const';

import { DesktopApi } from 'im.v2.lib.desktop-api';

export class BxLinkHandler
{
	static init(): BxLinkHandler
	{
		return new BxLinkHandler();
	}

	constructor()
	{
		this.#subscribeToBxProtocolEvent();
	}

	#subscribeToBxProtocolEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onBxLink, (command: $Keys<typeof DesktopBxLink>, rawParams) => {
			const params = rawParams ?? {};

			Object.entries(params).forEach(([key, value]) => {
				params[key] = decodeURIComponent(value);
			});

			DesktopApi.showWindow();
			if (command === DesktopBxLink.chat)
			{
				Messenger.openChat(params.dialogId);
			}
			else if (command === DesktopBxLink.call)
			{
				Messenger.startVideoCall(params.dialogId);
			}
			else if (command === DesktopBxLink.notifications)
			{
				Messenger.openNotifications();
			}
			else if (command === DesktopBxLink.recentSearch)
			{
				Messenger.openRecentSearch();
			}
			else if (command === DesktopBxLink.timeManager)
			{
				DesktopApi.showWindow();
				Monitor?.openReport();
			}
		});
	}
}
