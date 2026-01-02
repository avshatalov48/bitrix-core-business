import Type from '../type';
import { renderParam } from './render-param';

export default function buildQueryString(params = {}): string
{
	const queryString = Object.keys(params)
		.reduce((acc, key) => {
			if (Type.isArray(params[key]))
			{
				if (Type.isArrayFilled(params[key]))
				{
					params[key].forEach((paramValue) => {
						acc.push(renderParam(`${key}[]`, paramValue));
					});
				}
				else
				{
					acc.push(renderParam(`${key}[]`, null));
				}
			}

			if (Type.isPlainObject(params[key]))
			{
				Object.keys(params[key]).forEach((paramIndex) => {
					acc.push(renderParam(`${key}[${paramIndex}]`, params[key][paramIndex]));
				});
			}

			if (!Type.isObject(params[key]) && !Type.isArray(params[key]))
			{
				acc.push(renderParam(key, params[key]));
			}

			return acc;
		}, []).join('&');

	if (queryString.length > 0)
	{
		return `?${queryString}`;
	}

	return queryString;
}
