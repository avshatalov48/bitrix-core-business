import { Core } from 'im.v2.application.core';
import { Settings } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

const sendMessageCombinations = {
	enterMode: ['Enter', 'NumpadEnter'],
	ctrlEnterMode: ['Ctrl+Enter', 'Ctrl+NumpadEnter'],
};
// only for non-default hotkeys
const newLineCombinations = {
	enterMode: ['Ctrl+Enter'],
	ctrlEnterMode: [],
};

export const isSendMessageCombination = (event: KeyboardEvent): boolean => {
	return Utils.key.isExactCombination(event, getSendMessageCombination());
};

export const isNewLineCombination = (event: KeyboardEvent): boolean => {
	return Utils.key.isExactCombination(event, getNewLineCombination());
};

const getSendMessageCombination = (): string[] => {
	const sendByEnter = Core.getStore().getters['application/settings/get'](Settings.hotkey.sendByEnter);
	if (sendByEnter)
	{
		return sendMessageCombinations.enterMode;
	}

	return sendMessageCombinations.ctrlEnterMode;
};

const getNewLineCombination = (): string[] => {
	const sendByEnter = Core.getStore().getters['application/settings/get'](Settings.hotkey.sendByEnter);
	if (sendByEnter)
	{
		return newLineCombinations.enterMode;
	}

	return newLineCombinations.ctrlEnterMode;
};
