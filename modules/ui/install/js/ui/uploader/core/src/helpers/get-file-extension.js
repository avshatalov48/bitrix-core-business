import { Type } from 'main.core';

const getFileExtension = (filename: string) => {
	const position = Type.isStringFilled(filename) ? filename.lastIndexOf('.') : -1;

	return position > 0 ? filename.substring(position + 1) : '';
};

export default getFileExtension;


