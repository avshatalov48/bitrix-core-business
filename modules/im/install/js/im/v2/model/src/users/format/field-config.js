import { Type, Text } from 'main.core';

import { Utils } from 'im.v2.lib.utils';
import {
	convertToNumber,
	convertToDate,
	isNumberOrString,
} from '../../utils/format';
import { prepareAvatar, prepareDepartments, preparePhones } from './format-functions';

import type { FieldsConfig } from '../../utils/validate';

export const userFieldsConfig: FieldsConfig = [
	{
		fieldName: 'id',
		targetFieldName: 'id',
		checkFunction: isNumberOrString,
		formatFunction: convertToNumber,
	},
	{
		fieldName: 'networkId',
		targetFieldName: 'id',
		checkFunction: Utils.user.isNetworkUserId,
	},
	{
		fieldName: 'firstName',
		targetFieldName: 'firstName',
		checkFunction: Type.isString,
		formatFunction: Text.decode,
	},
	{
		fieldName: 'lastName',
		targetFieldName: 'lastName',
		checkFunction: Type.isString,
		formatFunction: Text.decode,
	},
	{
		fieldName: 'name',
		targetFieldName: 'name',
		checkFunction: Type.isString,
		formatFunction: Text.decode,
	},
	{
		fieldName: 'color',
		targetFieldName: 'color',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'avatar',
		targetFieldName: 'avatar',
		checkFunction: Type.isString,
		formatFunction: prepareAvatar,
	},
	{
		fieldName: 'workPosition',
		targetFieldName: 'workPosition',
		checkFunction: Type.isString,
		formatFunction: Text.decode,
	},
	{
		fieldName: 'gender',
		targetFieldName: 'gender',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'birthday',
		targetFieldName: 'birthday',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'isBirthday',
		targetFieldName: 'isBirthday',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'isAdmin',
		targetFieldName: 'isAdmin',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'extranet',
		targetFieldName: 'extranet',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'network',
		targetFieldName: 'network',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'bot',
		targetFieldName: 'bot',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'connector',
		targetFieldName: 'connector',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'externalAuthId',
		targetFieldName: 'externalAuthId',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'status',
		targetFieldName: 'status',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'idle',
		targetFieldName: 'idle',
		formatFunction: convertToDate,
	},
	{
		fieldName: 'lastActivityDate',
		targetFieldName: 'lastActivityDate',
		formatFunction: convertToDate,
	},
	{
		fieldName: 'mobileLastDate',
		targetFieldName: 'mobileLastDate',
		formatFunction: convertToDate,
	},
	{
		fieldName: 'absent',
		targetFieldName: 'absent',
		formatFunction: convertToDate,
	},
	{
		fieldName: 'isAbsent',
		targetFieldName: 'isAbsent',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'departments',
		targetFieldName: 'departments',
		checkFunction: Type.isArray,
		formatFunction: prepareDepartments,
	},
	{
		fieldName: 'phones',
		targetFieldName: 'phones',
		checkFunction: Type.isPlainObject,
		formatFunction: preparePhones,
	},
];
