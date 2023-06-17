import {Type} from 'main.core';

import {PlatformUtil} from './platform';

const LETTER_CODE_PREFIX = 'Key';
const DIGIT_CODE_PREFIX = 'Digit';

const CTRL = 'Ctrl';
const ALT = 'Alt';
const SHIFT = 'Shift';
const MODIFIERS = new Set([CTRL, ALT, SHIFT]);

export const KeyUtil = {
	isCmdOrCtrl(event: PointerEvent | KeyboardEvent): boolean
	{
		if (PlatformUtil.isMac())
		{
			return event.metaKey;
		}

		if (PlatformUtil.isLinux() || PlatformUtil.isWindows())
		{
			return event.ctrlKey;
		}

		return false;
	},

	isAltOrOption(event: PointerEvent | KeyboardEvent): boolean
	{
		return event.altKey;
	},

	isCombination(event: KeyboardEvent, combinationList: string | string[]): boolean
	{
		if (Type.isString(combinationList))
		{
			combinationList = [combinationList];
		}

		return combinationList.some((combination: string) => {
			return checkCombination(event, combination);
		});
	}
};

function checkCombination(event: KeyboardEvent, combination: string)
{
	if (combination.includes(SHIFT) && !event.shiftKey)
	{
		return false;
	}
	if (combination.includes(ALT) && !event.altKey)
	{
		return false;
	}
	if (combination.includes(CTRL) && !this.isCmdOrCtrl(event))
	{
		return false;
	}

	const keys = combination.split('+').filter(key => {
		return !MODIFIERS.has(key);
	}).map(key => {
		const singleLetterRegexp = /^[A-Za-z]$/;
		const singleDigitRegexp = /^\d$/;
		if (singleLetterRegexp.test(key))
		{
			return `${LETTER_CODE_PREFIX}${key.toUpperCase()}`;
		}
		else if (singleDigitRegexp.test(key))
		{
			return `${DIGIT_CODE_PREFIX}${key}`;
		}

		return key;
	});

	let result = true;
	keys.forEach(key => {
		if (key !== event.code)
		{
			result = false;
		}
	});

	return result;
}