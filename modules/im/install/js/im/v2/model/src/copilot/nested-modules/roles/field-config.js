import { Type } from 'main.core';

import { formatFieldsWithConfig } from 'im.v2.model';

import type { FieldsConfig } from 'im.v2.model';

export const rolesFieldsConfig: FieldsConfig = [
	{
		fieldName: 'avatar',
		targetFieldName: 'avatar',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => {
			return formatFieldsWithConfig(target, avatarFieldsConfig);
		},
	},
	{
		fieldName: 'code',
		targetFieldName: 'code',
		checkFunction: Type.isString,
	},
	{
		fieldName: ['desc', 'description'],
		targetFieldName: 'desc',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'name',
		targetFieldName: 'name',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'default',
		targetFieldName: 'default',
		checkFunction: Type.isBoolean,
	},
	{
		fieldName: 'prompts',
		targetFieldName: 'prompts',
		checkFunction: Type.isArray,
		formatFunction: (target) => {
			return target.map((prompt) => {
				return formatFieldsWithConfig(prompt, promptsFieldsConfig);
			});
		},
	},
];

const promptsFieldsConfig: FieldsConfig = [
	{
		fieldName: 'code',
		targetFieldName: 'code',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'promptType',
		targetFieldName: 'promptType',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'text',
		targetFieldName: 'text',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'title',
		targetFieldName: 'title',
		checkFunction: Type.isString,
	},
];

const avatarFieldsConfig: FieldsConfig = [
	{
		fieldName: 'small',
		targetFieldName: 'small',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'medium',
		targetFieldName: 'medium',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'large',
		targetFieldName: 'large',
		checkFunction: Type.isString,
	},
];
