import { Loc } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';

import { showTwoButtonConfirm, type ConfirmParams } from '../base/base';

export const showDeleteChatConfirm = (dialogId: string): Promise<boolean> => {
	const { title, text, firstButtonCaption } = getPhrases(dialogId);

	return showTwoButtonConfirm({ title, text, firstButtonCaption });
};

const getPhrases = (dialogId: string): ConfirmParams => {
	const isChannel = ChannelManager.isChannel(dialogId);
	if (isChannel)
	{
		return {
			title: Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_TITLE'),
			text: Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_TEXT'),
			firstButtonCaption: Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT_CONFIRM'),
		};
	}

	if (isCollab(dialogId))
	{
		return {
			title: Loc.getMessage('IM_LIB_CONFIRM_DELETE_COLLAB_TITLE'),
			text: Loc.getMessage('IM_LIB_CONFIRM_DELETE_COLLAB_TEXT'),
			firstButtonCaption: Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT_CONFIRM'),
		};
	}

	return {
		title: Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TITLE'),
		text: Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT'),
		firstButtonCaption: Loc.getMessage('IM_LIB_EXIT_DELETE_CHAT_TEXT_CONFIRM'),
	};
};

const isCollab = (dialogId: string): boolean => {
	const chat = Core.getStore().getters['chats/get'](dialogId, true);

	return chat.type === ChatType.collab;
};
