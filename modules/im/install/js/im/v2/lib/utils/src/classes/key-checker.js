import { Type } from 'main.core';

import { PlatformUtil } from '../platform';

const LETTER_CODE_PREFIX = 'Key';
const DIGIT_CODE_PREFIX = 'Digit';

const CTRL = 'Ctrl';
const ALT = 'Alt';
const SHIFT = 'Shift';
const MODIFIERS = new Set([CTRL, ALT, SHIFT]);

export class KeyChecker
{
	#event: KeyboardEvent | PointerEvent;

	constructor(event: KeyboardEvent | PointerEvent)
	{
		this.#event = event;
	}

	isCmdOrCtrl(): boolean
	{
		if (PlatformUtil.isMac())
		{
			return this.#event.metaKey;
		}

		if (PlatformUtil.isLinux() || PlatformUtil.isWindows())
		{
			return this.#event.ctrlKey;
		}

		return false;
	}

	isShift(): boolean
	{
		return this.#event.shiftKey;
	}

	isAltOrOption(): boolean
	{
		return this.#event.altKey;
	}

	isCombination(rawCombination: string | string[]): boolean
	{
		const combinationList = this.#prepareCombination(rawCombination);

		return combinationList.some((combination: string) => {
			return this.#checkCombination(combination);
		});
	}

	isExactCombination(rawCombination: string | string[]): boolean
	{
		const combinationList = this.#prepareCombination(rawCombination);

		return combinationList.some((combination: string) => {
			return this.#checkCombination(combination, true);
		});
	}

	#prepareCombination(combination: string | string[]): string[]
	{
		if (Array.isArray(combination))
		{
			return combination;
		}

		if (Type.isStringFilled(combination))
		{
			return [combination];
		}

		return [];
	}

	#checkCombination(combination: string, exact: boolean = false): boolean
	{
		if (this.#event.isComposing)
		{
			return false;
		}

		if (!this.#checkModifiers(combination, exact))
		{
			return false;
		}

		const keyCodes = this.#splitCombinationIntoKeyCodes(combination);

		let result = true;
		keyCodes.forEach((keyCode) => {
			if (keyCode !== this.#event.code)
			{
				result = false;
			}
		});

		return result;
	}

	#checkModifiers(combination: string, exact: boolean = false): boolean
	{
		let result = true;
		if (
			!this.#checkShift(combination, exact)
			|| !this.#checkAlt(combination, exact)
			|| !this.#checkCtrl(combination, exact)
		)
		{
			result = false;
		}

		return result;
	}

	#checkShift(combination: string, exact: boolean = false): boolean
	{
		let result = true;
		const missingShift = combination.includes(SHIFT) && !this.#event.shiftKey;
		const excessShift = exact && !combination.includes(SHIFT) && this.#event.shiftKey;
		if (missingShift || excessShift)
		{
			result = false;
		}

		return result;
	}

	#checkAlt(combination: string, exact: boolean = false): boolean
	{
		let result = true;
		const missingAlt = combination.includes(ALT) && !this.#event.altKey;
		const excessAlt = exact && !combination.includes(ALT) && this.#event.altKey;
		if (missingAlt || excessAlt)
		{
			result = false;
		}

		return result;
	}

	#checkCtrl(combination: string, exact: boolean = false): boolean
	{
		let result = true;
		const missingCtrl = combination.includes(CTRL) && !this.isCmdOrCtrl();
		const excessCtrl = exact && !combination.includes(CTRL) && this.isCmdOrCtrl();
		if (missingCtrl || excessCtrl)
		{
			result = false;
		}

		return result;
	}

	#splitCombinationIntoKeyCodes(combination: string): string[]
	{
		const split = combination.split('+');
		const withoutModifiers = split.filter((key) => {
			return !MODIFIERS.has(key);
		});

		return withoutModifiers.map((key) => {
			const singleLetterRegexp = /^[A-Za-z]$/;
			const singleDigitRegexp = /^\d$/;
			if (singleLetterRegexp.test(key))
			{
				return `${LETTER_CODE_PREFIX}${key.toUpperCase()}`;
			}

			if (singleDigitRegexp.test(key))
			{
				return `${DIGIT_CODE_PREFIX}${key}`;
			}

			return key;
		});
	}
}
