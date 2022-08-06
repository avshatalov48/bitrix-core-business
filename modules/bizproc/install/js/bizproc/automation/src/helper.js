import { Type } from "main.core";

export class Helper
{
	static #idIncrement = 0;

	static generateUniqueId()
	{
		++Helper.#idIncrement;
		return 'bizproc-automation-cmp-' + Helper.#idIncrement;
	}

	static toJsonString(data)
	{
		return JSON.stringify(data, function (i, v)
		{
			if (typeof(v) == 'boolean')
			{
				return v ? '1' : '0';
			}
			return v;
		});
	}

	static getResponsibleUserExpression(fields: Array<Object>): ?string
	{
		if (Type.isArray(fields))
		{
			for (const field of fields)
			{
				if (field['Id'] === 'ASSIGNED_BY_ID' || field['Id'] === 'RESPONSIBLE_ID')
				{
					return '{{'+field['Name']+'}}';
				}
			}
		}

		return null;
	};
}