import { EventType, DesktopBxLink, LegacyDesktopBxLink, DesktopBroadcastAction, WINDOW_ACTIVATION_DELAY } from 'im.v2.const';
import { DesktopApi, DesktopFeature } from 'im.v2.lib.desktop-api';
import { DesktopBroadcastManager } from 'im.v2.lib.desktop';

import { BxLinkProcessor } from '../../helpers/bx-link-processor';

export type DesktopBxLinkKey = $Keys<typeof DesktopBxLink>;
export type LegacyDesktopBxLinkKey = $Keys<typeof LegacyDesktopBxLink>;
export type RawParams = {
	dialogId: string,
}

export class BxLinkHandler
{
	static init(): BxLinkHandler
	{
		return new BxLinkHandler();
	}

	constructor()
	{
		this.#subscribeToBxProtocolEvent();
		this.#subscribeToLegacyBxProtocolEvent();
	}

	#subscribeToBxProtocolEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onBxLink, async (command: DesktopBxLinkKey, rawParams: ?RawParams) => {
			if (!DesktopApi.isAirDesignEnabledInDesktop())
			{
				BxLinkProcessor.handleCommand(command, rawParams);

				return;
			}

			await DesktopApi.showBrowserWindow();

			if (DesktopApi.isFeatureSupported(DesktopFeature.portalTabActivation.id))
			{
				await DesktopApi.handlePortalTabActivation();
			}

			DesktopBroadcastManager.getInstance().sendActionMessage({
				action: DesktopBroadcastAction.bxLink,
				params: {
					command,
					rawParams,
				},
			});
		});
	}

	#subscribeToLegacyBxProtocolEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onBxLink, (command: LegacyDesktopBxLinkKey, rawParams: ?RawParams) => {
			BxLinkProcessor.handleLegacyCommand(command, rawParams);
		});
	}
}
