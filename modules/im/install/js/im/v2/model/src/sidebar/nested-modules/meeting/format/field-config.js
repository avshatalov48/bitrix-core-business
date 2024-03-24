import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import { formatFieldsWithConfig } from '../../../../utils/validate';

import type { FieldsConfig } from '../../../../utils/validate';

export const sidebarMeetingFieldsConfig: FieldsConfig = [
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
	{
		fieldName: 'calendar',
		targetFieldName: 'meeting',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return formatFieldsWithConfig(target, meetingFieldsConfig);
		},
	},
];

export const meetingFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'title',
		targetFieldName: 'title',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'dateFrom',
		targetFieldName: 'dateFrom',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: 'dateTo',
		targetFieldName: 'dateTo',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: 'source',
		targetFieldName: 'source',
		checkFunction: Type.isString,
	},
];
