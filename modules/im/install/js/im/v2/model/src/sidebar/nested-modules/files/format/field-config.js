import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import type { FieldsConfig } from '../../../../utils/validate';

export const sidebarFilesFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'messageId',
		targetFieldName: 'messageId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'chatId',
		targetFieldName: 'chatId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'authorId',
		targetFieldName: 'authorId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: ['dateCreate', 'date'],
		targetFieldName: 'date',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: ['fileId', 'id'],
		targetFieldName: 'fileId',
		checkFunction: Type.isNumber,
	},
];
