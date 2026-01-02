import { Messenger } from 'im.public';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { DesktopBxLink, LegacyDesktopBxLink } from 'im.v2.const';
import { DesktopManager } from 'im.v2.lib.desktop';

import { Encoder } from '../classes/encoder';

import type { DesktopBxLinkKey, LegacyDesktopBxLinkKey, RawParams } from '../classes/event-handlers/bx-link';

export const BxLinkProcessor = {
	handleCommand(command: DesktopBxLinkKey, rawParams: ?RawParams): void
	{
		const params = rawParams ?? {};

		Object.entries(params).forEach(([key, value]) => {
			params[key] = decodeURIComponent(value);
		});

		if (command !== DesktopBxLink.openPage && !DesktopApi.isAirDesignEnabledInDesktop())
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
		else if (command === DesktopBxLink.channel)
		{
			void Messenger.openChannel(params.dialogId);
		}
		else if (command === DesktopBxLink.taskComments)
		{
			const messageId = params.messageId ?? 0;
			void Messenger.openTaskComments(params.dialogId, messageId);
		}
		else if (command === DesktopBxLink.settings)
		{
			void Messenger.openSettings({ onlyPanel: params.section });
		}
		else if (command === DesktopBxLink.chatCreation)
		{
			void Messenger.openChatCreation(params.chatType);
		}
		else if (command === DesktopBxLink.openLayout)
		{
			const { id, entityId } = params;

			void Messenger.openNavigationItem({
				id,
				entityId,
			});
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
		else if (command === DesktopBxLink.botContext)
		{
			const { dialogId, context } = params;
			const decodedContext = Encoder.decodeParamsJson(context);

			void Messenger.openChatWithBotContext(dialogId, decodedContext);
		}
	},
	handleLegacyCommand(command: LegacyDesktopBxLinkKey, rawParams: ?RawParams): void
	{
		const params = rawParams ?? {};

		Object.entries(params).forEach(([key, value]) => {
			params[key] = decodeURIComponent(value);
		});

		if (!DesktopApi.isAirDesignEnabledInDesktop())
		{
			DesktopApi.activateWindow();
		}

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
	},
};
