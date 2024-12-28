import { Loc } from 'main.core';

import { ChatType } from 'im.v2.const';
import { ChannelManager } from 'im.v2.lib.channel';
import { Core } from 'im.v2.application.core';

import { showTwoButtonConfirm, type ConfirmParams } from '../base/base';

export const showLeaveChatConfirm = (dialogId: string): Promise<boolean> => {
	const { title, text, firstButtonCaption } = getPhrases(dialogId);

	return showTwoButtonConfirm({ text, title, firstButtonCaption });
};

const getPhrases = (dialogId: string): ConfirmParams => {
	if (isCollab(dialogId))
	{
		return {
			title: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_COLLAB_TITLE'),
			text: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_COLLAB_TEXT'),
			firstButtonCaption: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_YES_MSGVER_1'),
		};
	}

	if (ChannelManager.isChannel(dialogId))
	{
		return {
			text: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHANNEL_TEXT'),
			firstButtonCaption: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHANNEL_YES'),
		};
	}

	return {
		text: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_MSGVER_1'),
		firstButtonCaption: Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_YES_MSGVER_1'),
	};
};

const isCollab = (dialogId: string): boolean => {
	const chat = Core.getStore().getters['chats/get'](dialogId, true);

	return chat.type === ChatType.collab;
};
