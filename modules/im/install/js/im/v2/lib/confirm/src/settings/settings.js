import { Loc } from 'main.core';

import { showTwoButtonConfirm } from '../base/base';

export const showNotificationsModeSwitchConfirm = (): Promise<boolean> => {
	const kickText = Loc.getMessage('IM_LIB_CONFIRM_SWITCH_NOTIFICATION_MODE');
	const yesCaption = Loc.getMessage('IM_LIB_CONFIRM_SWITCH_NOTIFICATION_MODE_YES');

	return showTwoButtonConfirm({ text: kickText, firstButtonCaption: yesCaption });
};
