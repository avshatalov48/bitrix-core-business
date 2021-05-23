import { Type } from 'main.core';

export default class TextNodeType
{
	static TEXT: string = 'text';
	static HTML: string = 'html';

	static isValid(type: string)
	{
		return Type.isString(type) && (type === this.HTML || type === this.TEXT);
	}
}