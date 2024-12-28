import { Loc } from 'main.core';

import { showSingleButtonConfirm, showTwoButtonConfirm } from '../base/base';

export const showDesktopConfirm = (): Promise<boolean> => {
	const restartText = Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP');
	const okText = Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP_OK');

	return showSingleButtonConfirm({ text: restartText, firstButtonCaption: okText });
};

export const showDesktopRestartConfirm = (): Promise<boolean> => {
	const restartText = Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP');
	const restartCaption = Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP_RESTART');
	const laterCaption = Loc.getMessage('IM_LIB_CONFIRM_RESTART_DESKTOP_LATER');

	return showTwoButtonConfirm({
		text: restartText,
		firstButtonCaption: restartCaption,
		secondButtonCaption: laterCaption,
	});
};

export const showDesktopDeleteConfirm = (): Promise<boolean> => {
	const deleteText = Loc.getMessage('IM_LIB_CONFIRM_DELETE_DESKTOP').replace('#BR#', '<br>');
	const confirmCaption = Loc.getMessage('IM_LIB_CONFIRM_DELETE_DESKTOP_CONFIRM');

	return showTwoButtonConfirm({
		text: deleteText,
		firstButtonCaption: confirmCaption,
	});
};
