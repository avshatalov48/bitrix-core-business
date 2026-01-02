import { Extension } from 'main.core';

const DEFAULT_MIN_TOKEN_SIZE = 3;

export const getMinTokenSize = (): number => {
	const settings = Extension.getSettings('im.v2.component.search');

	return settings.get('minTokenSize', DEFAULT_MIN_TOKEN_SIZE);
};
