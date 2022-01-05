import { Type } from 'main.core';
import { FieldsManager } from './fields';

export class UFManager
{
	constructor(
		params: {
			mandatoryUFList: object,
		}
	)
	{
		if (
			Type.isPlainObject(FieldsManager.mandatoryFieldsByStep)
			&& Type.isArray(FieldsManager.mandatoryFieldsByStep[2])
			&& Type.isArray(params.mandatoryUFList)
		)
		{
			params.mandatoryUFList.forEach((ufData) => {
				if (
					!Type.isStringFilled(ufData.id)
					|| !Type.isStringFilled(ufData.type)
				)
				{
					return;
				}

				FieldsManager.mandatoryFieldsByStep[2].push(ufData);
			});
		}
	}

}
