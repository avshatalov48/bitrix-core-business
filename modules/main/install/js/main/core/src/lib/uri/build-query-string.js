import Type from '../type';

export default function buildQueryString(params = {})
{
	const queryString = Object.keys(params)
		.reduce((acc, key) => {
			if (Type.isArray(params[key]))
			{
				params[key].forEach((paramValue) => {
					acc.push(`${key}[]=${paramValue}`);
				}, '');
			}

			if (Type.isPlainObject(params[key]))
			{
				Object.keys(params[key]).forEach((paramIndex) => {
					acc.push(`${key}[${paramIndex}]=${params[key][paramIndex]}`);
				}, '');
			}

			if (!Type.isObject(params[key]) && !Type.isArray(params[key]))
			{
				acc.push(`${key}=${params[key]}`);
			}

			return acc;
		}, []).join('&');

	if (queryString.length > 0)
	{
		return `?${queryString}`;
	}

	return queryString;
}