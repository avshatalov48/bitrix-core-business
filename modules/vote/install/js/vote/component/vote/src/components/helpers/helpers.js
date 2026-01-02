import { Loc } from 'main.core';

export const getMessage = (phraseCode: string): string => {
	return Loc.getMessage(phraseCode);
};

export const getMessageWithCount = (phraseCode: string, counter: number): string => {
	return Loc.getMessagePlural(phraseCode, counter, {
		'#COUNT#': counter,
	});
};
