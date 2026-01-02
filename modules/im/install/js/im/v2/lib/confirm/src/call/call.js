import { Loc } from 'main.core';

import { showTwoButtonConfirm } from '../base/base';

export const showCloseWithActiveCallConfirm = (): Promise<boolean> => {
	return showTwoButtonConfirm({
		title: Loc.getMessage('IM_LIB_CONFIRM_ACTIVE_CALL_CONFIRM'),
	});
};
