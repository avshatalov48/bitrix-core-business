import { Loc, Type } from 'main.core';
import { MessageBoxButtons } from 'ui.dialogs.messagebox';

import { ChatConfirm } from './classes/confirm';

type ConfirmParams = {
	text: string,
	firstButtonCaption?: string,
	secondButtonCaption?: string,
};

export const showKickUserConfirm = (): Promise<boolean> => {
	const kickText = Loc.getMessage('IM_LIB_CONFIRM_USER_KICK');
	const yesCaption = Loc.getMessage('IM_LIB_CONFIRM_USER_KICK_YES');

	return showTwoButtonConfirm({ text: kickText, firstButtonCaption: yesCaption });
};

export const showLeaveFromChatConfirm = (): Promise<boolean> => {
	const kickText = Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT');
	const yesCaption = Loc.getMessage('IM_LIB_CONFIRM_LEAVE_CHAT_YES');

	return showTwoButtonConfirm({ text: kickText, firstButtonCaption: yesCaption });
};

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

export const showNotificationsModeSwitchConfirm = (): Promise<boolean> => {
	const kickText = Loc.getMessage('IM_LIB_CONFIRM_SWITCH_NOTIFICATION_MODE');
	const yesCaption = Loc.getMessage('IM_LIB_CONFIRM_SWITCH_NOTIFICATION_MODE_YES');

	return showTwoButtonConfirm({ text: kickText, firstButtonCaption: yesCaption });
};

const showTwoButtonConfirm = (params: ConfirmParams): Promise<boolean> => {
	const { text, firstButtonCaption = '', secondButtonCaption = '' } = params;

	return new Promise((resolve) => {
		const options = {
			message: text,
			modal: true,
			buttons: MessageBoxButtons.YES_CANCEL,
			onYes: (messageBox: ChatConfirm) => {
				resolve(true);
				messageBox.close();
			},
			onCancel: (messageBox: ChatConfirm) => {
				resolve(false);
				messageBox.close();
			},
		};

		if (Type.isStringFilled(firstButtonCaption))
		{
			options.yesCaption = firstButtonCaption;
		}

		if (Type.isStringFilled(secondButtonCaption))
		{
			options.cancelCaption = secondButtonCaption;
		}

		ChatConfirm.show(options);
	});
};

const showSingleButtonConfirm = (params: ConfirmParams): Promise<boolean> => {
	const { text, firstButtonCaption = '' } = params;

	return new Promise((resolve) => {
		const options = {
			message: text,
			modal: true,
			buttons: MessageBoxButtons.OK,
			onOk: (messageBox: ChatConfirm) => {
				resolve(true);
				messageBox.close();
			},
		};

		if (Type.isStringFilled(firstButtonCaption))
		{
			options.okCaption = firstButtonCaption;
		}

		ChatConfirm.show(options);
	});
};
