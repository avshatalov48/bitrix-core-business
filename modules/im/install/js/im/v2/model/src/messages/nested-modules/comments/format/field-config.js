import { Type } from 'main.core';

import type { FieldsConfig } from 'im.v2.model';

export const commentFieldsConfig: FieldsConfig = [
	{
		fieldName: 'chatId',
		targetFieldName: 'chatId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'lastUserIds',
		targetFieldName: 'lastUserIds',
		checkFunction: Type.isArray,
	},
	{
		fieldName: 'messageCount',
		targetFieldName: 'messageCount',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'messageId',
		targetFieldName: 'messageId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'isUserSubscribed',
		targetFieldName: 'isUserSubscribed',
		checkFunction: Type.isBoolean,
	},
];
