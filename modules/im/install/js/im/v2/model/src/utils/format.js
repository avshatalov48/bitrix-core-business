import { Type, Text } from 'main.core';
import { Utils } from 'im.v2.lib.utils';

import type { JsonObject, JsonValue } from 'main.core';

export const isNumberOrString = (target: JsonValue): boolean => {
	return Type.isNumber(target) || Type.isString(target);
};

export const convertToString = (target: JsonValue): string => {
	return target.toString();
};

export const convertToNumber = (target: string | number): number => {
	return Number.parseInt(target, 10);
};

export const convertToDate = (target: string) => {
	return Utils.date.cast(target, false);
};

const SNAKE_CASE_REGEXP = /(_[\da-z])/gi;
export const convertObjectKeysToCamelCase = (targetObject: JsonObject): JsonObject => {
	const resultObject = {};
	Object.entries(targetObject).forEach(([key, value]) => {
		const newKey = prepareKey(key);
		if (Type.isPlainObject(value))
		{
			resultObject[newKey] = convertObjectKeysToCamelCase(value);

			return;
		}

		if (Type.isArray(value))
		{
			resultObject[newKey] = convertArrayItemsKeysToCamelCase(value);

			return;
		}
		resultObject[newKey] = value;
	});

	return resultObject;
};

const prepareKey = (rawKey: string): string => {
	let key = rawKey;
	if (key.search(SNAKE_CASE_REGEXP) !== -1)
	{
		key = key.toLowerCase();
	}

	return Text.toCamelCase(key);
};

const convertArrayItemsKeysToCamelCase = (targetArray: JsonValue[]): JsonValue[] => {
	return targetArray.map((arrayItem) => {
		if (!Type.isPlainObject(arrayItem))
		{
			return arrayItem;
		}

		return convertObjectKeysToCamelCase(arrayItem);
	});
};
