import { ChannelManager } from 'im.v2.lib.channel';
import { Loc } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType } from 'im.v2.const';

import { showTwoButtonConfirm } from '../base/base';

import type { ConfirmParams } from '../base/base';

export const showKickUserConfirm = (dialogId: string): Promise<boolean> => {
	const { title, text, firstButtonCaption } = getPhrases(dialogId);

	return showTwoButtonConfirm({ title, text, firstButtonCaption });
};

const getPhrases = (dialogId: string): ConfirmParams => {
	if (isCollab(dialogId))
	{
		return {
			title: Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_FROM_COLLAB_TITLE'),
			text: Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_FROM_COLLAB_TEXT'),
			firstButtonCaption: Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES'),
		};
	}

	const isChannel = ChannelManager.isChannel(dialogId);
	if (isChannel)
	{
		return {
			text: Loc.getMessage('IM_LIB_CONFIRM_USER_CHANNEL_KICK'),
			firstButtonCaption: Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES'),
		};
	}

	return {
		text: Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_MSGVER_1'),
		firstButtonCaption: Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES'),
	};
};

const isCollab = (dialogId: string): boolean => {
	const chat = Core.getStore().getters['chats/get'](dialogId, true);

	return chat.type === ChatType.collab;
};
