import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import { convertToNumber, convertToString, isNumberOrString, convertObjectKeysToCamelCase } from '../../utils/format';
import { prepareComponentId, prepareAuthorId, prepareKeyboard } from './format-functions';

import type { FieldsConfig } from '../../utils/validate';

export const messageFieldsConfig: FieldsConfig = [
	{
		fieldName: 'temporaryId',
		targetFieldName: 'id',
		checkFunction: Utils.text.isUuidV4,
	},
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'chatId',
		targetFieldName: 'chatId',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'date',
		targetFieldName: 'date',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: 'text',
		targetFieldName: 'text',
		checkFunction: isNumberOrString,
		formatFunction: convertToString,
	},
	{
		fieldName: ['senderId', 'authorId'],
		targetFieldName: 'authorId',
		checkFunction: isNumberOrString,
		formatFunction: prepareAuthorId,
	},
	{
		fieldName: 'sending',
		targetFieldName: 'sending',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'unread',
		targetFieldName: 'unread',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'viewed',
		targetFieldName: 'viewed',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'viewedByOthers',
		targetFieldName: 'viewedByOthers',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'error',
		targetFieldName: 'error',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'componentId',
		targetFieldName: 'componentId',
		checkFunction: (target): boolean => {
			return Type.isString(target) && target !== '';
		},
		formatFunction: prepareComponentId,
	},
	{
		fieldName: 'componentParams',
		targetFieldName: 'componentParams',
		checkFunction: Type.isPlainObject,
		formatFunction: convertObjectKeysToCamelCase,
	},
	{
		fieldName: ['files', 'fileId'],
		targetFieldName: 'files',
		checkFunction: Type.isArray,
	},
	{
		fieldName: 'attach',
		targetFieldName: 'attach',
		checkFunction: [Type.isArray, Type.isBoolean, Type.isString],
	},
	{
		fieldName: 'keyboard',
		targetFieldName: 'keyboard',
		checkFunction: Type.isArray,
		formatFunction: prepareKeyboard,
	},
	{
		fieldName: 'keyboard',
		targetFieldName: 'keyboard',
		checkFunction: (target): boolean => target === 'N',
		formatFunction: (): [] => [],
	},
	{
		fieldName: 'isEdited',
		targetFieldName: 'isEdited',
		checkFunction: Type.isString,
		formatFunction: (target): boolean => target === 'Y',
	},
	{
		fieldName: 'isEdited',
		targetFieldName: 'isEdited',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'isDeleted',
		targetFieldName: 'isDeleted',
		checkFunction: Type.isString,
		formatFunction: (target): boolean => target === 'Y',
	},
	{
		fieldName: 'isDeleted',
		targetFieldName: 'isDeleted',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'replyId',
		targetFieldName: 'replyId',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'forward',
		targetFieldName: 'forward',
		checkFunction: Type.isPlainObject,
		formatFunction: convertObjectKeysToCamelCase,
	},
];
