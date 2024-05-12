import { Type } from 'main.core';

export function getByIndex<T>(array: Array<T>, index: number): ?T
{
	if (!Type.isArray(array))
	{
		throw new TypeError('array is not a array');
	}

	if (!Type.isInteger(index))
	{
		throw new TypeError('index is not a integer');
	}

	const preparedIndex = index < 0 ? array.length + index : index;

	return array[preparedIndex];
};
