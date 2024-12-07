import { Utils } from 'im.v2.lib.utils';
import { Type } from 'main.core';

import { convertToString, isNumberOrString } from '../../utils/format';
import { prepareDraft, prepareInvitation } from './format-functions';

import type { FieldsConfig } from '../../utils/validate';

export const recentFieldsConfig: FieldsConfig = [
	{
		fieldName: ['id', 'dialogId'],
		targetFieldName: 'dialogId',
		checkFunction: isNumberOrString,
		formatFunction: convertToString,
	},
	{
		fieldName: 'messageId',
		targetFieldName: 'messageId',
		checkFunction: isNumberOrString,
	},
	{
		fieldName: 'draft',
		targetFieldName: 'draft',
		checkFunction: Type.isPlainObject,
		formatFunction: prepareDraft,
	},
	{
		fieldName: 'invited',
		targetFieldName: 'invitation',
		checkFunction: [Type.isPlainObject, Type.isBoolean],
		formatFunction: prepareInvitation,
	},
	{
		fieldName: 'unread',
		targetFieldName: 'unread',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'pinned',
		targetFieldName: 'pinned',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'liked',
		targetFieldName: 'liked',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: ['defaultUserRecord', 'isFakeElement'],
		targetFieldName: 'isFakeElement',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'isBirthdayPlaceholder',
		targetFieldName: 'isBirthdayPlaceholder',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: ['dateLastActivity', 'lastActivityDate'],
		targetFieldName: 'lastActivityDate',
		checkFunction: [Type.isString, Type.isDate],
		formatFunction: Utils.date.cast,
	},
];
