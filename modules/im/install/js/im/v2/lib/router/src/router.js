import { Messenger } from 'im.public';
import { GetParameter, NavigationMenuItem } from 'im.v2.const';

export const Router = {
	init()
	{
		Router.checkGetParams();
	},
	checkGetParams(): void
	{
		const urlParams = new URLSearchParams(window.location.search);
		if (urlParams.has(GetParameter.openNotifications))
		{
			void Messenger.openNotifications();
		}
		else if (urlParams.has(GetParameter.openHistory))
		{
			const dialogId = urlParams.get(GetParameter.openHistory);
			void Messenger.openLinesHistory(dialogId);
		}
		else if (urlParams.has(GetParameter.openLines))
		{
			const dialogId = urlParams.get(GetParameter.openLines);
			void Messenger.openLines(dialogId);
		}
		else if (urlParams.has(GetParameter.botContext))
		{
			const dialogId = urlParams.get(GetParameter.openChat);
			const context = urlParams.get(GetParameter.botContext);

			void Messenger.openChatWithBotContext(dialogId, context);
		}
		else if (urlParams.has(GetParameter.openChat))
		{
			const dialogId = urlParams.get(GetParameter.openChat);
			void Messenger.openChat(dialogId, extractMessageId(urlParams));
		}
		else if (urlParams.has(GetParameter.openSettings))
		{
			const settingsSection = urlParams.get(GetParameter.openSettings);
			void Messenger.openSettings({ onlyPanel: settingsSection?.toLowerCase() });
		}
		else if (urlParams.has(GetParameter.openCopilotChat))
		{
			const dialogId = urlParams.get(GetParameter.openCopilotChat);
			void Messenger.openCopilot(dialogId);
		}
		else if (urlParams.has(GetParameter.openChannel))
		{
			const dialogId = urlParams.get(GetParameter.openChannel);
			void Messenger.openNavigationItem({
				id: NavigationMenuItem.channel,
				entityId: dialogId,
			});
		}
		else if (urlParams.has(GetParameter.openCollab))
		{
			const dialogId = urlParams.get(GetParameter.openCollab);
			void Messenger.openCollab(dialogId ?? '');
		}
		else if (urlParams.has(GetParameter.openTaskComments))
		{
			const dialogId = urlParams.get(GetParameter.openTaskComments);

			void Messenger.openTaskComments(dialogId ?? '', extractMessageId(urlParams));
		}
	},
};

const extractMessageId = (urlParams: URLSearchParams): number => {
	let messageId = urlParams.get(GetParameter.openMessage);
	messageId = messageId ? Number(messageId) : 0;

	return messageId;
};
