import { Type } from 'main.core';

import type { FieldsConfig } from 'im.v2.model';

export const chatFieldsConfig: FieldsConfig = [
	{
		fieldName: 'dialogId',
		targetFieldName: 'dialogId',
		checkFunction: Type.isString,
	},
	{
		fieldName: 'role',
		targetFieldName: 'role',
		checkFunction: Type.isString,
	},
];
