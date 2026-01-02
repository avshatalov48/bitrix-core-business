import parseUrl from './uri/parse-url';
import buildQueryString from './uri/build-query-string';
import prepareParamValue from './uri/prepare-param-value';
import Type from './type';

const map = new WeakMap();

/**
 * Implements interface for works with URI
 * @memberOf BX
 */
export default class Uri
{
	static addParam(url: string, params = {}): string
	{
		return (new Uri(url)).setQueryParams(params).toString();
	}

	static removeParam(url: string, params: Array<string> | string): string
	{
		const removableParams = Type.isArray(params) ? params : [params];

		return (new Uri(url)).removeQueryParam(...removableParams).toString();
	}

	constructor(url = '')
	{
		map.set(this, parseUrl(url));
	}

	getSchema(): ?string
	{
		return map.get(this).schema;
	}

	setSchema(schema: string): Uri
	{
		map.get(this).schema = String(schema);

		return this;
	}

	getHost(): ?string
	{
		return map.get(this).host;
	}

	setHost(host: string): Uri
	{
		map.get(this).host = String(host);

		return this;
	}

	getPort(): string
	{
		return map.get(this).port;
	}

	setPort(port: string | number): Uri
	{
		map.get(this).port = String(port);

		return this;
	}

	getPath(): string
	{
		return map.get(this).path;
	}

	setPath(path: string): Uri
	{
		if (!/^\//.test(path))
		{
			map.get(this).path = `/${String(path)}`;

			return this;
		}

		map.get(this).path = String(path);

		return this;
	}

	getQuery(): string
	{
		return buildQueryString(map.get(this).queryParams);
	}

	getQueryParam(key: string): ?string
	{
		const params = this.getQueryParams();

		if (Object.hasOwn(params, key))
		{
			return params[key];
		}

		return null;
	}

	setQueryParam(key: string, value: string = ''): Uri
	{
		map.get(this).queryParams[key] = prepareParamValue(value);
		map.get(this).sourceQueryParams[key] = prepareParamValue(value);

		return this;
	}

	getQueryParams(): { [key: string]: string }
	{
		return { ...map.get(this).queryParams };
	}

	setQueryParams(params: { [key: string]: string } = {}): Uri
	{
		if (Type.isPlainObject(params))
		{
			const { queryParams, sourceQueryParams } = map.get(this);

			Object.entries(params).forEach(([key: string, value: string]) => {
				const preparedValue = prepareParamValue(value);
				queryParams[key] = preparedValue;
				sourceQueryParams[key] = preparedValue;
			});
		}

		return this;
	}

	removeQueryParam(...keys: Array<string>): Uri
	{
		const { queryParams, sourceQueryParams } = map.get(this);

		keys.forEach((key: string) => {
			delete queryParams[key];
			delete sourceQueryParams[key];
		});

		return this;
	}

	getFragment(): ?string
	{
		return map.get(this).hash;
	}

	setFragment(hash: string): Uri
	{
		map.get(this).hash = String(hash);

		return this;
	}

	serialize(): { [key: string]: string }
	{
		const serialized = { ...map.get(this) };
		delete serialized.sourceQueryParams;
		serialized.href = this.toString();

		return serialized;
	}

	toString(): string
	{
		const data = { ...map.get(this) };

		let protocol = data.schema ? `${data.schema}://` : '';

		if (data.useShort)
		{
			protocol = '//';
		}

		const port = (() => {
			if (Type.isString(data.port) && !['', '80'].includes(data.port))
			{
				return `:${data.port}`;
			}

			return '';
		})();

		const host = this.getHost();
		const path = this.getPath();
		const query = buildQueryString(data.sourceQueryParams);
		const hash = data.hash ? `#${data.hash}` : '';

		return `${host ? protocol : ''}${host}${host ? port : ''}${path}${query}${hash}`;
	}
}
