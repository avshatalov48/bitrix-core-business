import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import { formatFieldsWithConfig } from '../../../../utils/validate';

import type { FieldsConfig } from '../../../../utils/validate';

export const sidebarTaskFieldsConfig: FieldsConfig = [
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
		fieldName: 'task',
		targetFieldName: 'task',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return formatFieldsWithConfig(target, taskFieldsConfig);
		},
	},
];

export const taskFieldsConfig: FieldsConfig = [
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
		fieldName: 'creatorId',
		targetFieldName: 'creatorId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'responsibleId',
		targetFieldName: 'responsibleId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'statusTitle',
		targetFieldName: 'statusTitle',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'deadline',
		targetFieldName: 'deadline',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: 'state',
		targetFieldName: 'state',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'color',
		targetFieldName: 'color',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'source',
		targetFieldName: 'source',
		checkFunction: Type.isString,
	},
];
