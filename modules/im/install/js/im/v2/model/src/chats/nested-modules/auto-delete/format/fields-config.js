import { Type } from 'main.core';

import type { FieldsConfig } from '../../../../utils/validate';

export const autoDeleteFieldsConfig: FieldsConfig = [
	{
		fieldName: 'delay',
		targetFieldName: 'delay',
		checkFunction: Type.isNumber,
	},
	{
		fieldName: 'chatId',
		targetFieldName: 'chatId',
		checkFunction: Type.isNumber,
	},
];
