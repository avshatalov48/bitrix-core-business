import { formatFieldsWithConfig } from 'im.v2.model';
import { Type } from 'main.core';

import type { FieldsConfig } from 'im.v2.model';

export const collabFieldsConfig: FieldsConfig = [
	{
		fieldName: 'collabId',
		targetFieldName: 'collabId',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'guestCount',
		targetFieldName: 'guestCount',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'entities',
		targetFieldName: 'entities',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => formatFieldsWithConfig(target, collabEntitiesFieldConfig),
	},
];

const collabEntitiesFieldConfig: FieldsConfig = [
	{
		fieldName: 'tasks',
		targetFieldName: 'tasks',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => formatFieldsWithConfig(target, collabEntityFieldConfig),
	},
	{
		fieldName: 'files',
		targetFieldName: 'files',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => formatFieldsWithConfig(target, collabEntityFieldConfig),
	},
	{
		fieldName: 'calendar',
		targetFieldName: 'calendar',
		checkFunction: Type.isPlainObject,
		formatFunction: (target) => formatFieldsWithConfig(target, collabEntityFieldConfig),
	},
];

const collabEntityFieldConfig: FieldsConfig = [
	{
		fieldName: 'counter',
		targetFieldName: 'counter',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'url',
		targetFieldName: 'url',
		checkFunction: Type.isStringFilled,
	},
];
