import Type from '../type';

function getParser(format)
{
	switch (format)
	{
		case 'index':
			return (sourceKey, value, accumulator) => {
				const result = /\[(\w*)\]$/.exec(sourceKey);
				const key = sourceKey.replace(/\[\w*\]$/, '');

				if (Type.isNil(result))
				{
					accumulator[key] = value;
					return;
				}

				if (Type.isUndefined(accumulator[key]))
				{
					accumulator[key] = {};
				}

				accumulator[key][result[1]] = value;
			};
		case 'bracket':
			return (sourceKey, value, accumulator) => {
				const result = /(\[\])$/.exec(sourceKey);
				const key = sourceKey.replace(/\[\]$/, '');

				if (Type.isNil(result))
				{
					accumulator[key] = value;
					return;
				}

				if (Type.isUndefined(accumulator[key]))
				{
					accumulator[key] = Type.isNil(value) ? [] : [value];
					return;
				}

				accumulator[key] = [].concat(accumulator[key], value);
			};
		default:
			return (sourceKey, value, accumulator) => {
				const key = sourceKey.replace(/\[\]$/, '');
				accumulator[key] = value;
			};
	}
}

function getKeyFormat(key)
{
	if (/^\w+\[([\w]+)\]$/.test(key))
	{
		return 'index';
	}

	if (/^\w+\[\]$/.test(key))
	{
		return 'bracket';
	}

	return 'default';
}

function isAllowedKey(key: string): boolean
{
	return !String(key).startsWith('__proto__');
}

function parseQuery(input)
{
	if (!Type.isString(input))
	{
		return {};
	}

	const url = input.trim().replace(/^[?#&]/, '');

	if (!url)
	{
		return {};
	}

	return {
		...url.split('&')
			.reduce((acc, param) => {
				const [key, value] = param.replace(/\+/g, ' ').split('=');
				if (isAllowedKey(key))
				{
					const keyFormat = getKeyFormat(key);
					const formatter = getParser(keyFormat);
					formatter(key, value, acc);
				}
				return acc;
			}, Object.create(null)),
	};
}

const urlExp = /^((\w+):)?(\/\/((\w+)?(:(\w+))?@)?([^\/\?:]+)(:(\d+))?)?(\/?([^\/\?#][^\?#]*)?)?(\?([^#]+))?(#((?:[\w-?/:@.~!$&'()*+,;=]|%\w{2})*))?/;

function prepareParams(params: { [key: string]: any }): { [key: string]: any }
{
	const paramsEntries: Array<Array<string, ?string>> = Object.entries(params);

	return paramsEntries.reduce((acc, [key, value]) => {
		if (Type.isNil(value))
		{
			acc[key] = '';
		}
		else if (Type.isPlainObject(value))
		{
			acc[key] = prepareParams(value);
		}
		else
		{
			acc[key] = value;
		}

		return acc;
	}, {});
}

export default function parseUrl(url)
{
	const result = url.match(urlExp);

	if (Type.isArray(result))
	{
		const sourceParams: { [key: string]: ?string } = parseQuery(result[14]);
		const preparedParams: { [key: string]: string } = prepareParams(sourceParams);

		return {
			useShort: /^\/\//.test(url),
			href: result[0] || '',
			schema: result[2] || '',
			host: result[8] || '',
			port: result[10] || '',
			path: result[11] || '',
			query: result[14] || '',
			sourceQueryParams: sourceParams,
			queryParams: preparedParams,
			hash: result[16] || '',
			username: result[5] || '',
			password: result[7] || '',
			origin: result[8] || '',
		};
	}

	return {};
}
