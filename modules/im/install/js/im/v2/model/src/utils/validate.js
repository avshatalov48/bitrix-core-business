import { Type } from 'main.core';

import { convertObjectKeysToCamelCase } from './format';

import type { JsonObject, JsonValue } from 'main.core';

export type FieldsConfig = FieldsConfigItem[];
type FieldsConfigItem = {
	fieldName: string | Array<string>,
	targetFieldName: string,
	checkFunction?: CheckFunction | Array<CheckFunction>, // OR logic
	formatFunction?: FormatFunction,
};
type CheckFunction = (target: JsonValue) => boolean;
type FormatFunction = (target: JsonValue, currentResult: JsonObject, rawFields: JsonObject) => JsonValue;

export const formatFieldsWithConfig = (fields: JsonObject, config: FieldsConfig): JsonObject => {
	const resultObject = {};
	const rawFields = convertObjectKeysToCamelCase(fields);

	config.forEach((fieldConfig: FieldsConfigItem) => {
		const { fieldName, targetFieldName, checkFunction, formatFunction } = fieldConfig;

		// check if field exists
		const foundFieldName = getValidFieldName(rawFields, fieldName);
		if (!foundFieldName)
		{
			return;
		}

		// validate value
		if (!isFieldValueValid(rawFields[foundFieldName], checkFunction))
		{
			return;
		}

		// format value
		resultObject[targetFieldName] = formatFieldValue({
			fieldValue: rawFields[foundFieldName],
			formatFunction,
			currentResult: resultObject,
			rawFields: fields,
		});
	});

	return resultObject;
};

const getValidFieldName = (fields: JsonObject, fieldName: string | string[]): string | null => {
	let fieldNameList = fieldName;
	if (Type.isStringFilled(fieldNameList))
	{
		fieldNameList = [fieldNameList];
	}

	for (const singleField of fieldNameList)
	{
		if (!Type.isUndefined(fields[singleField]))
		{
			return singleField;
		}
	}

	return null;
};

const isFieldValueValid = (field: JsonValue, checkFunction: CheckFunction | Array<CheckFunction>): boolean => {
	let checkFunctionList = checkFunction;
	if (Type.isUndefined(checkFunctionList))
	{
		return true;
	}

	if (Type.isFunction(checkFunctionList))
	{
		checkFunctionList = [checkFunctionList];
	}

	return checkFunctionList.some((singleFunction) => singleFunction(field));
};

type FormatFieldValueParams = {
	fieldValue: JsonValue,
	formatFunction: FormatFunction,
	currentResult: JsonObject,
	rawFields: JsonObject
};
const formatFieldValue = (params: FormatFieldValueParams) => {
	const { fieldValue, formatFunction, currentResult, rawFields } = params;
	if (Type.isUndefined(formatFunction))
	{
		return fieldValue;
	}

	return formatFunction(fieldValue, currentResult, rawFields);
};
