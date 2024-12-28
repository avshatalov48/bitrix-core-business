/* eslint-disable unicorn/prefer-switch */
import { Encoder } from '../encoder';
import { DesktopManager } from '../../desktop-manager';
import { Messenger } from 'im.public';
import { EventType, DesktopBxLink, LegacyDesktopBxLink } from 'im.v2.const';
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
		this.#subscribeToLegacyBxProtocolEvent();
	}

	#subscribeToBxProtocolEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onBxLink, (command: $Keys<typeof DesktopBxLink>, rawParams) => {
			const params = rawParams ?? {};

			Object.entries(params).forEach(([key, value]) => {
				params[key] = decodeURIComponent(value);
			});

			if (command !== DesktopBxLink.openPage)
			{
				DesktopApi.activateWindow();
			}

			if (command === DesktopBxLink.chat)
			{
				const messageId = params.messageId ?? 0;
				void Messenger.openChat(params.dialogId, messageId);
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
			else if (command === DesktopBxLink.collab)
			{
				void Messenger.openCollab(params.dialogId);
			}
			else if (command === DesktopBxLink.settings)
			{
				void Messenger.openSettings({ onlyPanel: params.section });
			}
			else if (command === DesktopBxLink.chatCreation)
			{
				void Messenger.openChatCreation(params.chatType);
			}
			else if (command === DesktopBxLink.timeManager)
			{
				BX.Timeman?.Monitor?.openReport();
			}
			else if (command === DesktopBxLink.openTab)
			{
				DesktopApi.setActiveTab();
			}
			else if (command === DesktopBxLink.openPage)
			{
				const options = Encoder.decodeParamsJson(params.options);
				DesktopApi.openPage(options.url, options.options);
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
