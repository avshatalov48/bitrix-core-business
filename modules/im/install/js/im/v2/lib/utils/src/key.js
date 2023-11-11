import { KeyChecker } from './classes/key-checker';

export const KeyUtil = {
	isCmdOrCtrl(event: PointerEvent | KeyboardEvent): boolean
	{
		return (new KeyChecker(event)).isCmdOrCtrl();
	},

	isShift(event: PointerEvent | KeyboardEvent): boolean
	{
		return (new KeyChecker(event)).isShift();
	},

	isAltOrOption(event: PointerEvent | KeyboardEvent): boolean
	{
		return (new KeyChecker(event)).isAltOrOption();
	},

	isCombination(event: KeyboardEvent, rawCombinationList: string | string[]): boolean
	{
		return (new KeyChecker(event)).isCombination(rawCombinationList);
	},

	isExactCombination(event: KeyboardEvent, rawCombinationList: string | string[]): boolean
	{
		return (new KeyChecker(event)).isExactCombination(rawCombinationList);
	},
};
