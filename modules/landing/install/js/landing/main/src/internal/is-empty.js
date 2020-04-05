import {Type} from 'main.core';

export default function isEmpty(value): boolean
{
	if (Type.isNil(value))
	{
		return true;
	}

	if (Type.isArrayLike(value))
	{
		return !value.length;
	}

	if (Type.isObject(value))
	{
		return Object.keys(value).length <= 0;
	}

	return true;
}