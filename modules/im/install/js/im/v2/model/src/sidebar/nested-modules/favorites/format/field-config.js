import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import type { FieldsConfig } from '../../../../utils/validate';

export const sidebarFavoritesFieldsConfig: FieldsConfig = [
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
		fieldName: 'dateCreate',
		targetFieldName: 'date',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
];
