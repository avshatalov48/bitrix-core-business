import { Utils } from "im.lib.utils";

export const AttachLinks = {
	methods:
	{
		openLink(event)
		{
			const element = event.element;
			const eventData = event.event;

			if (!Utils.platform.isBitrixMobile() && element.LINK)
			{
				return;
			}

			if (element.LINK && eventData.target.tagName !== 'A')
			{
				Utils.platform.openNewPage(element.LINK);
			}
			else if (!element.LINK)
			{
				const entity = {
					id: null,
					type: null
				};
				if (element.hasOwnProperty('USER_ID') && element.USER_ID > 0)
				{
					entity.id = element.USER_ID;
					entity.type = 'user';
				}
				if (element.hasOwnProperty('CHAT_ID') && element.CHAT_ID > 0)
				{
					entity.id = element.CHAT_ID;
					entity.type = 'chat';
				}

				if (entity.id && entity.type && window.top['BXIM'])
				{
					const popupAngle = !BX.MessengerTheme.isDark();
					window.top['BXIM'].messenger.openPopupExternalData(
						eventData.target,
						entity.type,
						popupAngle,
						{'ID': entity.id}
					);
				}
				else if (navigator.userAgent.toLowerCase().includes('bitrixmobile'))
				{
					let dialogId = '';
					if (entity.type === 'chat')
					{
						dialogId = `chat${entity.id}`;
					}
					else
					{
						dialogId = entity.id;
					}

					if (dialogId !== '')
					{
						BXMobileApp.Events.postToComponent("onOpenDialog", [{dialogId: dialogId}, true], 'im.recent');
					}
				}
			}
		}
	}
};