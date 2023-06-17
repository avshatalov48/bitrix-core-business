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
					accumulator[key] = [value];
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

const urlExp = /^((\w+):)?(\/\/((\w+)?(:(\w+))?@)?([^\/\?:]+)(:(\d+))?)?(\/?([^\/\?#][^\?#]*)?)?(\?([^#]+))?(#(\w*))?/;

export default function parseUrl(url)
{
	const result = url.match(urlExp);

	if (Type.isArray(result))
	{
		const queryParams = parseQuery(result[14]);

		return {
			useShort: /^\/\//.test(url),
			href: result[0] || '',
			schema: result[2] || '',
			host: result[8] || '',
			port: result[10] || '',
			path: result[11] || '',
			query: result[14] || '',
			queryParams,
			hash: result[16] || '',
			username: result[5] || '',
			password: result[7] || '',
			origin: result[8] || '',
		};
	}

	return {};
}