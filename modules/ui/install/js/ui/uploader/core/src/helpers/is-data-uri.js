import { Type } from 'main.core';

const regexp = /^data:((?:\w+\/(?:(?!;).)+)?)((?:;[\W\w]*?[^;])*),(.+)$/;

const isDataUri = (str: string): boolean => {
	return Type.isString(str) ? str.match(regexp) : false;
};

export default isDataUri;
