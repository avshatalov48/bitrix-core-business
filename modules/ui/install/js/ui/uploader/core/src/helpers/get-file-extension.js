import { Type } from 'main.core';

const getFileExtension = (filename: string): string => {
	const position: number = Type.isStringFilled(filename) ? filename.lastIndexOf('.') : -1;

	return position > 0 ? filename.slice(Math.max(0, position + 1)) : '';
};

export default getFileExtension;
