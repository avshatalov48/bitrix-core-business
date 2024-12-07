import { Type } from 'main.core';

import type { FieldsConfig } from '../../../../utils/validate';

export const tariffRestrictionsFieldsConfig: FieldsConfig = [
	{
		fieldName: 'fullChatHistory',
		targetFieldName: 'fullChatHistory',
		checkFunction: Type.isPlainObject,
	},
];
