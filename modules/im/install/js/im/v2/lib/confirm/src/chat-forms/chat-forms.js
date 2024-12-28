import { Loc } from 'main.core';

import { ChannelManager } from 'im.v2.lib.channel';

import { showTwoButtonConfirm, type ConfirmParams } from '../base/base';

export const showExitUpdateChatConfirm = (dialogId: string): Promise<boolean> => {
	const { title, firstButtonCaption } = getPhrases(dialogId);

	return showTwoButtonConfirm({ title, firstButtonCaption });
};

const getPhrases = (dialogId: string): ConfirmParams => {
	const isChannel = ChannelManager.isChannel(dialogId);
	if (isChannel)
	{
		return {
			title: Loc.getMessage('IM_LIB_EXIT_UPDATE_CHANNEL_TITLE'),
			firstButtonCaption: Loc.getMessage('IM_LIB_EXIT_UPDATE_CHAT_TEXT_CONFIRM'),
		};
	}

	return {
		title: Loc.getMessage('IM_LIB_EXIT_UPDATE_CHAT_TITLE'),
		firstButtonCaption: Loc.getMessage('IM_LIB_EXIT_UPDATE_CHAT_TEXT_CONFIRM'),
	};
};
