import { Type } from 'main.core';

export default class TypeUtils
{
	static createMapFromOptions(options)
	{
		if (Type.isPlainObject(options))
		{
			return new Map(Object.entries(options));
		}

		const map = new Map();
		if (Type.isArrayFilled(options))
		{
			options.forEach((element: Array) => {
				if (Type.isArray(element) && element.length === 2 && Type.isString(element[0]))
				{
					map.set(element[0], element[1]);
				}
			})
		}

		return map;
	}

	static convertMapToObject(map: Map): object
	{
		const obj = {};
		if (Type.isMap(map))
		{
			map.forEach((value, key) => {
				if (Type.isString(key))
				{
					obj[key] = value;
				}
			});
		}

		return obj;
	}
}