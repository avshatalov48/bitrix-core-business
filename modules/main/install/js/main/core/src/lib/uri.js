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

	/**
	 * Gets schema
	 * @return {?string}
	 */
	getSchema()
	{
		return map.get(this).schema;
	}

	/**
	 * Sets schema
	 * @param {string} schema
	 * @return {Uri}
	 */
	setSchema(schema)
	{
		map.get(this).schema = String(schema);
		return this;
	}

	/**
	 * Gets host
	 * @return {?string}
	 */
	getHost()
	{
		return map.get(this).host;
	}

	/**
	 * Sets host
	 * @param {string} host
	 * @return {Uri}
	 */
	setHost(host)
	{
		map.get(this).host = String(host);
		return this;
	}

	/**
	 * Gets port
	 * @return {?string}
	 */
	getPort()
	{
		return map.get(this).port;
	}

	/**
	 * Sets port
	 * @param {String | Number} port
	 * @return {Uri}
	 */
	setPort(port)
	{
		map.get(this).port = String(port);
		return this;
	}

	/**
	 * Gets path
	 * @return {?string}
	 */
	getPath()
	{
		return map.get(this).path;
	}

	/**
	 * Sets path
	 * @param {string} path
	 * @return {Uri}
	 */
	setPath(path)
	{
		if (!/^\//.test(path))
		{
			map.get(this).path = `/${String(path)}`;
			return this;
		}

		map.get(this).path = String(path);
		return this;
	}

	/**
	 * Gets query
	 * @return {?string}
	 */
	getQuery()
	{
		return buildQueryString(map.get(this).queryParams);
	}

	/**
	 * Gets query param value by name
	 * @param {string} key
	 * @return {?string}
	 */
	getQueryParam(key)
	{
		const params = this.getQueryParams();

		if (Object.hasOwn(params, key))
		{
			return params[key];
		}

		return null;
	}

	/**
	 * Sets query param
	 * @param {string} key
	 * @param [value]
	 * @return {Uri}
	 */
	setQueryParam(key, value = '')
	{
		map.get(this).queryParams[key] = prepareParamValue(value);
		return this;
	}

	/**
	 * Gets query params
	 * @return {Object<string, any>}
	 */
	getQueryParams()
	{
		return {...map.get(this).queryParams};
	}

	/**
	 * Sets query params
	 * @param {Object<string, any>} params
	 * @return {Uri}
	 */
	setQueryParams(params = {})
	{
		const currentParams = this.getQueryParams();
		const newParams = {...currentParams, ...params};

		Object.keys(newParams).forEach((key) => {
			newParams[key] = prepareParamValue(newParams[key]);
		});

		map.get(this).queryParams = newParams;
		return this;
	}

	/**
	 * Removes query params by name
	 * @param keys
	 * @return {Uri}
	 */
	removeQueryParam(...keys)
	{
		const currentParams = {...map.get(this).queryParams};

		keys.forEach((key) => {
			if (Object.hasOwn(currentParams, key))
			{
				delete currentParams[key];
			}
		});

		map.get(this).queryParams = currentParams;
		return this;
	}

	/**
	 * Gets fragment
	 * @return {?string}
	 */
	getFragment()
	{
		return map.get(this).hash;
	}

	/**
	 * Sets fragment
	 * @param {string} hash
	 * @return {Uri}
	 */
	setFragment(hash)
	{
		map.get(this).hash = String(hash);
		return this;
	}

	/**
	 * Serializes URI
	 * @return {Object}
	 */
	serialize()
	{
		const serialized = {...map.get(this)};
		serialized.href = this.toString();
		return serialized;
	}

	/**
	 * Gets URI string
	 * @return {string}
	 */
	toString()
	{
		const data = {...map.get(this)};

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
		const query = buildQueryString(data.queryParams);
		const hash = data.hash ? `#${data.hash}` : '';

		return `${host ? protocol : ''}${host}${host ? port : ''}${path}${query}${hash}`;
	}
}
