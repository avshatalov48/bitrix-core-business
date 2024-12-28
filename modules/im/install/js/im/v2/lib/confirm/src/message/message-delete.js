import { Loc } from 'main.core';

import { showTwoButtonConfirm } from '../base/base';

export const showDeleteChannelPostConfirm = (): Promise<boolean> => {
	return showTwoButtonConfirm({
		title: Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_POST_TITLE'),
		text: Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_POST_TEXT'),
		firstButtonCaption: Loc.getMessage('IM_LIB_EXIT_DELETE_CHANNEL_POST_TEXT_CONFIRM'),
	});
};
