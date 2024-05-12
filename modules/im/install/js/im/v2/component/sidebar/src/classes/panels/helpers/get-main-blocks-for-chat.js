import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { MainPanels, MainPanelType } from '../../panel-config';

export function getMainBlocksForChat(dialogId: string): string[]
{
	const panelType = getMainPanelType(dialogId);

	return Object.entries(MainPanels[panelType])
		.sort(([, order1], [, order2]) => order1 - order2)
		.map(([block]) => block);
}

function getMainPanelType(dialogId: string): $Keys<typeof MainPanelType>
{
	const chatType = getChatType(dialogId);
	if (isSupportChat(chatType))
	{
		return MainPanelType.support24;
	}

	return MainPanelType[chatType] ?? MainPanelType.chat;
}

const isSupportChat = (chatType: $Keys<typeof ChatType>): boolean => {
	// TODO: implement
	return false;
};

const getChatType = (dialogId: string): $Keys<typeof ChatType> => {
	return Core.getStore().getters['chats/get'](dialogId).type;
};
