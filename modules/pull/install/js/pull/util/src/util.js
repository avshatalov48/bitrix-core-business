/* eslint-disable @bitrix24/bitrix24-rules/no-typeof */

export const browser = {
	IsChrome(): boolean
	{
		return navigator.userAgent.toLowerCase().includes('chrome');
	},

	IsFirefox(): boolean
	{
		return navigator.userAgent.toLowerCase().includes('firefox');
	},

	IsIe(): boolean
	{
		return navigator.userAgent.match(/(Trident\/|MSIE\/)/) !== null;
	},
};

export function getTimestamp(): number
{
	return Date.now();
}

/**
 * Reduces errors array to single string.
 * @param {array} errors
 * @return {string}
 */
export function errorsToString(errors): string
{
	if (!isArray(errors))
	{
		return '';
	}

	return errors.reduce((accum, currentValue) => {
		const result = accum === '' ? accum : `${accum}; `;

		return `${result + currentValue.code}: ${currentValue.message}`;
	}, '');
}

export function isString(item): boolean
{
	return item === '' ? true : (item ? (typeof (item) === 'string' || item instanceof String) : false);
}

export function isArray(item): boolean
{
	return item && Object.prototype.toString.call(item) === '[object Array]';
}

export function isFunction(item): boolean
{
	return item === null ? false : (typeof (item) === 'function' || item instanceof Function);
}

export function getFunction(f: Function): Function
{
	return isFunction(f) ? f : function() {};
}

export function isDomNode(item): boolean
{
	return item && typeof (item) === 'object' && 'nodeType' in item;
}

export function isDate(item): boolean
{
	return item && Object.prototype.toString.call(item) === '[object Date]';
}

export function isNumber(item): boolean
{
	return typeof item === 'number' && Number.isFinite(item);
}

export function isObject(item): boolean
{
	return Boolean(item) && typeof item === 'object';
}

export function isPlainObject(item): boolean
{
	return Boolean(item) && typeof item === 'object' && item.constructor === Object;
}

export function isNotEmptyString(item): boolean
{
	return isString(item) ? item.length > 0 : false;
}

export function isJsonRpcRequest(item): boolean
{
	return (
		typeof (item) === 'object'
		&& item
		&& 'jsonrpc' in item
		&& isNotEmptyString(item.jsonrpc)
		&& 'method' in item
		&& isNotEmptyString(item.method)
	);
}

export function isJsonRpcResponse(item): boolean
{
	return (
		typeof (item) === 'object'
		&& item
		&& 'jsonrpc' in item
		&& isNotEmptyString(item.jsonrpc)
		&& 'id' in item
		&& (
			'result' in item
			|| 'error' in item
		)
	);
}

export function buildQueryString(params: Object): string
{
	let result = '';
	for (const key of Object.keys(params))
	{
		const value = params[key];
		if (isArray(value))
		{
			for (const [index, valueElement] of value.entries())
			{
				const left = encodeURIComponent(`${key}[${index}]`);
				const right = `${encodeURIComponent(valueElement)}&`;
				result += `${left}=${right}`;
			}
		}
		else
		{
			result += `${encodeURIComponent(key)}=${encodeURIComponent(value)}&`;
		}
	}

	if (result.length > 0)
	{
		result = result.slice(0, Math.max(0, result.length - 1));
	}

	return result;
}

export function clone(obj: any, bCopyObj = true): any
{
	let _obj, i, l;

	if (obj === null)
	{
		return null;
	}

	if (isDomNode(obj))
	{
		_obj = obj.cloneNode(bCopyObj);
	}
	else if (typeof obj === 'object')
	{
		if (isArray(obj))
		{
			_obj = [];
			for (i = 0, l = obj.length; i < l; i++)
			{
				if (typeof obj[i] === 'object' && bCopyObj)
				{
					_obj[i] = clone(obj[i], bCopyObj);
				}
				else
				{
					_obj[i] = obj[i];
				}
			}
		}
		else
		{
			_obj = {};
			if (obj.constructor)
			{
				if (isDate(obj))
				{
					_obj = new Date(obj);
				}
				else
				{
					_obj = new obj.constructor();
				}
			}

			for (i in obj)
			{
				if (!obj.hasOwnProperty(i))
				{
					continue;
				}

				if (typeof obj[i] === 'object' && bCopyObj)
				{
					_obj[i] = clone(obj[i], bCopyObj);
				}
				else
				{
					_obj[i] = obj[i];
				}
			}
		}
	}
	else
	{
		_obj = obj;
	}

	return _obj;
}

export function getDateForLog(): string
{
	const d = new Date();

	return `${d.getFullYear()}-${lpad(d.getMonth(), 2, '0')}-${lpad(d.getDate(), 2, '0')} ${lpad(d.getHours(), 2, '0')}:${lpad(d.getMinutes(), 2, '0')}`;
}

export function lpad(str: string, length, chr = ' '): string
{
	if (str.length > length)
	{
		return str;
	}

	let result = '';
	for (let i = 0; i < length - result.length; i++)
	{
		result += chr;
	}

	return result + str;
}

export function isWebSocketSupported(): boolean
{
	return typeof 'WebSocket' !== 'undefined';
}

export class CircularBuffer<T>
{
	storage: T[];
	capacity: number;
	pointer = 0;

	constructor(capacity: number)
	{
		if (capacity <= 0)
		{
			throw new Error('capacity must be > 0');
		}

		this.capacity = capacity;
		this.storage = [];
	}

	push(element: T)
	{
		this.storage[this.pointer] = element;
		this.pointer++;
		if (this.pointer >= this.capacity)
		{
			this.pointer = 0;
		}
	}

	getAll(): T[]
	{
		if (this.pointer === 0)
		{
			return this.storage;
		}

		return [...this.storage.slice(this.pointer), ...this.storage.slice(0, this.pointer)];
	}
}
