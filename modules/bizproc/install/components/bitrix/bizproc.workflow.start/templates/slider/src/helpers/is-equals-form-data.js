import { Type } from 'main.core';

export function isEqualsFormData(form1: FormData, form2: FormData): boolean
{
	for (const key of form1.keys())
	{
		if (!form2.has(key))
		{
			return false;
		}

		const values1 = form1.getAll(key);
		const values2 = form2.getAll(key);

		if (values1.length !== values2.length)
		{
			return false;
		}

		for (const singleKey of values1.keys())
		{
			let value1 = values1.at(singleKey);
			let value2 = values2.at(singleKey);

			if (Type.isFile(value1))
			{
				value1 = value1.name;
				value2 = value2.name;
			}

			if (value1 !== value2)
			{
				return false;
			}
		}
	}

	return true;
}
