/* eslint-disable unicorn/prefer-switch */
import { Messenger } from 'im.public';
import { EventType, DesktopBxLink, LegacyDesktopBxLink } from 'im.v2.const';
import { DesktopManager } from '../../desktop-manager';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Encoder } from '../encoder';

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
		DesktopApi.subscribe(EventType.desktop.onBxLink, (command: $Keys<typeof DesktopBxLink>, rawParams) => {
			const params = rawParams ?? {};

			Object.entries(params).forEach(([key, value]) => {
				params[key] = decodeURIComponent(value);
			});

			DesktopApi.activateWindow();

			if (command === DesktopBxLink.chat)
			{
				void Messenger.openChat(params.dialogId);
			}
			else if (command === DesktopBxLink.lines)
			{
				void Messenger.openLines(params.dialogId);
			}
			else if (command === DesktopBxLink.conference)
			{
				void DesktopManager.getInstance().openConference(params.code);
			}
			else if (command === DesktopBxLink.call)
			{
				const withVideo = params.withVideo !== 'N';
				void Messenger.startVideoCall(params.dialogId, withVideo);
			}
			else if (command === DesktopBxLink.phone)
			{
				const decodedParams = Encoder.decodeParamsJson(params.phoneParams);
				void Messenger.startPhoneCall(params.number, decodedParams);
			}
			else if (command === DesktopBxLink.callList)
			{
				const decodedParams = Encoder.decodeParamsJson(params.callListParams);
				void Messenger.startCallList(params.callListId, decodedParams);
			}
			else if (command === DesktopBxLink.notifications)
			{
				void Messenger.openNotifications();
			}
			else if (command === DesktopBxLink.recentSearch)
			{
				void Messenger.openRecentSearch();
			}
			else if (command === DesktopBxLink.copilot)
			{
				void Messenger.openCopilot(params.dialogId);
			}
			else if (command === DesktopBxLink.settings)
			{
				void Messenger.openSettings({ onlyPanel: params.section });
			}
			else if (command === DesktopBxLink.timeManager)
			{
				BX.Timeman?.Monitor?.openReport();
			}
			else if (command === DesktopBxLink.openTab)
			{
				DesktopApi.setActiveTab();
			}
		});
	}

	#subscribeToLegacyBxProtocolEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onBxLink, (command: $Keys<typeof LegacyDesktopBxLink>, rawParams) => {
			const params = rawParams ?? {};

			Object.entries(params).forEach(([key, value]) => {
				params[key] = decodeURIComponent(value);
			});

			DesktopApi.activateWindow();

			if (command === LegacyDesktopBxLink.messenger)
			{
				if (params.dialog)
				{
					void Messenger.openChat(params.dialog);
				}
				else if (params.chat)
				{
					void Messenger.openChat(`chat${params.chat}`);
				}
				else
				{
					void Messenger.openChat();
				}
			}
			else if (command === LegacyDesktopBxLink.chat && params.id)
			{
				void Messenger.openChat(`chat${params.id}`);
			}
			else if (command === LegacyDesktopBxLink.notify)
			{
				void Messenger.openNotifications();
			}
			else if (command === LegacyDesktopBxLink.callTo)
			{
				if (params.video)
				{
					void Messenger.startVideoCall(params.video);
				}
				else if (params.audio)
				{
					void Messenger.startVideoCall(params.audio, false);
				}
				else if (params.phone)
				{
					void Messenger.startPhoneCall(params.phone);
				}
			}
			else if (command === LegacyDesktopBxLink.callList)
			{
				void Messenger.openRecentSearch();
			}
		});
	}
}
