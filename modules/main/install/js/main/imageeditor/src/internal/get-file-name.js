import {Type} from 'main.core';

export default function getFileName(src: string)
{
	if (Type.isString(src))
	{
		return src.split('/').pop();
	}

	return '';
}