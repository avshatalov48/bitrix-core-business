import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import type { FieldsConfig } from '../../../../utils/validate';

export const sidebarMultidialogFieldsConfig: FieldsConfig = [
	{
		fieldName: 'dialogId',
		targetFieldName: 'dialogId',
		checkFunction: Type.String,
	},
	{
		fieldName: 'chatId',
		targetFieldName: 'chatId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'openSessionsLimit',
		targetFieldName: 'openSessionsLimit',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'status',
		targetFieldName: 'status',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'dateMessage',
		targetFieldName: 'date',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
];
