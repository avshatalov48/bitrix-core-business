import { Type } from 'main.core';

import { Utils } from 'im.v2.lib.utils';

import { formatFieldsWithConfig } from '../../../../utils/validate';

import type { FieldsConfig } from '../../../../utils/validate';

export const sidebarLinksFieldsConfig: FieldsConfig = [
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
		fieldName: 'url',
		targetFieldName: 'source',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return target.source ?? '';
		},
	},
	{
		fieldName: 'dateCreate',
		targetFieldName: 'date',
		checkFunction: Type.isString,
		formatFunction: Utils.date.cast,
	},
	{
		fieldName: 'url',
		targetFieldName: 'richData',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return formatFieldsWithConfig(target.richData, richDataFieldsConfig);
		},
	},
];

export const richDataFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'description',
		targetFieldName: 'description',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'link',
		targetFieldName: 'link',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'name',
		targetFieldName: 'name',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'previewUrl',
		targetFieldName: 'previewUrl',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'type',
		targetFieldName: 'type',
		checkFunction: Type.isString,
	},
];
