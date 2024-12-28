import { Type } from 'main.core';
import { MessageBoxButtons } from 'ui.dialogs.messagebox';

import { ChatConfirm } from './confirm';

export type ConfirmParams = {
	text: string,
	title?: string,
	firstButtonCaption?: string,
	secondButtonCaption?: string,
};

export const showTwoButtonConfirm = (params: ConfirmParams): Promise<boolean> => {
	const { text = '', firstButtonCaption = '', secondButtonCaption = '', title = '' } = params;

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

		if (Type.isStringFilled(title))
		{
			options.title = title;
		}

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

export const showSingleButtonConfirm = (params: ConfirmParams): Promise<boolean> => {
	const { text, firstButtonCaption = '', title = '' } = params;

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

		if (Type.isStringFilled(title))
		{
			options.title = title;
		}

		if (Type.isStringFilled(firstButtonCaption))
		{
			options.okCaption = firstButtonCaption;
		}

		ChatConfirm.show(options);
	});
};
