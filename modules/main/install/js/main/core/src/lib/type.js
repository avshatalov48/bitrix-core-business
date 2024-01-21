import getTag from '../internal/get-tag';

const objectCtorString = Function.prototype.toString.call(Object);

/**
 * @memberOf BX
 */
export default class Type
{
	/**
	 * Checks that value is string
	 * @param value
	 * @return {boolean}
	 */
	static isString(value: any): boolean
	{
		return typeof value === 'string';
	}

	/**
	 * Returns true if a value is not empty string
	 * @param value
	 * @returns {boolean}
	 */
	static isStringFilled(value: any): boolean
	{
		return Type.isString(value) && value !== '';
	}

	/**
	 * Checks that value is function
	 * @param value
	 * @return {boolean}
	 */
	static isFunction(value: any): boolean
	{
		return typeof value === 'function';
	}

	/**
	 * Checks that value is object
	 * @param value
	 * @return {boolean}
	 */
	static isObject(value: any): boolean
	{
		return !!value && (typeof value === 'object' || typeof value === 'function');
	}

	/**
	 * Checks that value is object like
	 * @param value
	 * @return {boolean}
	 */
	static isObjectLike(value: any): boolean
	{
		return !!value && typeof value === 'object';
	}

	/**
	 * Checks that value is plain object
	 * @param value
	 * @return {boolean}
	 */
	static isPlainObject(value: any): boolean
	{
		if (!Type.isObjectLike(value) || getTag(value) !== '[object Object]')
		{
			return false;
		}

		const proto = Object.getPrototypeOf(value);
		if (proto === null)
		{
			return true;
		}

		const ctor = proto.hasOwnProperty('constructor') && proto.constructor;

		return (
			typeof ctor === 'function' &&
			Function.prototype.toString.call(ctor) === objectCtorString
		);
	}

	/**
	 * Checks that value is boolean
	 * @param value
	 * @return {boolean}
	 */
	static isBoolean(value: any): boolean
	{
		return value === true || value === false;
	}

	/**
	 * Checks that value is number
	 * @param value
	 * @return {boolean}
	 */
	static isNumber(value: any): boolean
	{
		return !Number.isNaN(value) && typeof value === 'number';
	}

	/**
	 * Checks that value is integer
	 * @param value
	 * @return {boolean}
	 */
	static isInteger(value: any): boolean
	{
		return Type.isNumber(value) && (value % 1) === 0;
	}

	/**
	 * Checks that value is float
	 * @param value
	 * @return {boolean}
	 */
	static isFloat(value: any): boolean
	{
		return Type.isNumber(value) && !Type.isInteger(value);
	}

	/**
	 * Checks that value is nil
	 * @param value
	 * @return {boolean}
	 */
	static isNil(value: any): boolean
	{
		return value === null || value === undefined;
	}

	/**
	 * Checks that value is array
	 * @param value
	 * @return {boolean}
	 */
	static isArray(value: any): boolean
	{
		return !Type.isNil(value) && Array.isArray(value);
	}

	/**
	 * Returns true if a value is an array and it has at least one element
	 * @param value
	 * @returns {boolean}
	 */
	static isArrayFilled(value: any): boolean
	{
		return Type.isArray(value) && value.length > 0;
	}

	/**
	 * Checks that value is array like
	 * @param value
	 * @return {boolean}
	 */
	static isArrayLike(value: any): boolean
	{
		return (
			!Type.isNil(value)
			&& !Type.isFunction(value)
			&& value.length > -1
			&& value.length <= Number.MAX_SAFE_INTEGER
		);
	}

	/**
	 * Checks that value is Date
	 * @param value
	 * @return {boolean}
	 */
	static isDate(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object Date]';
	}

	/**
	 * Checks that is DOM node
	 * @param value
	 * @return {boolean}
	 */
	static isDomNode(value: any): boolean
	{
		return Type.isObjectLike(value) && !Type.isPlainObject(value) && 'nodeType' in value;
	}

	/**
	 * Checks that value is element node
	 * @param value
	 * @return {boolean}
	 */
	static isElementNode(value: any): boolean
	{
		return Type.isDomNode(value) && value.nodeType === Node.ELEMENT_NODE;
	}

	/**
	 * Checks that value is text node
	 * @param value
	 * @return {boolean}
	 */
	static isTextNode(value: any): boolean
	{
		return Type.isDomNode(value) && value.nodeType === Node.TEXT_NODE;
	}

	/**
	 * Checks that value is Map
	 * @param value
	 * @return {boolean}
	 */
	static isMap(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object Map]';
	}

	/**
	 * Checks that value is Set
	 * @param value
	 * @return {boolean}
	 */
	static isSet(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object Set]';
	}

	/**
	 * Checks that value is WeakMap
	 * @param value
	 * @return {boolean}
	 */
	static isWeakMap(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object WeakMap]';
	}

	/**
	 * Checks that value is WeakSet
	 * @param value
	 * @return {boolean}
	 */
	static isWeakSet(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object WeakSet]';
	}

	/**
	 * Checks that value is prototype
	 * @param value
	 * @return {boolean}
	 */
	static isPrototype(value: any): boolean
	{
		return (
			(((typeof (value && value.constructor) === 'function') && value.constructor.prototype) || Object.prototype) === value
		);
	}

	/**
	 * Checks that value is regexp
	 * @param value
	 * @return {boolean}
	 */
	static isRegExp(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object RegExp]';
	}

	/**
	 * Checks that value is null
	 * @param value
	 * @return {boolean}
	 */
	static isNull(value: any): boolean
	{
		return value === null;
	}

	/**
	 * Checks that value is undefined
	 * @param value
	 * @return {boolean}
	 */
	static isUndefined(value: any): boolean
	{
		return typeof value === 'undefined';
	}

	/**
	 * Checks that value is ArrayBuffer
	 * @param value
	 * @return {boolean}
	 */
	static isArrayBuffer(value: any): boolean
	{
		return Type.isObjectLike(value) && getTag(value) === '[object ArrayBuffer]';
	}

	/**
	 * Checks that value is typed array
	 * @param value
	 * @return {boolean}
	 */
	static isTypedArray(value: any): boolean
	{
		const regExpTypedTag = (
			/^\[object (?:Float(?:32|64)|(?:Int|Uint)(?:8|16|32)|Uint8Clamped)]$/
		);
		return Type.isObjectLike(value) && regExpTypedTag.test(getTag(value));
	}

	/**
	 * Checks that value is Blob
	 * @param value
	 * @return {boolean}
	 */
	static isBlob(value: any): boolean
	{
		return (
			Type.isObjectLike(value)
			&& Type.isNumber(value.size)
			&& Type.isString(value.type)
			&& Type.isFunction(value.slice)
		);
	}

	/**
	 * Checks that value is File
	 * @param value
	 * @return {boolean}
	 */
	static isFile(value: any): boolean
	{
		return (
			Type.isBlob(value)
			&& Type.isString(value.name)
			&& (Type.isNumber(value.lastModified) || Type.isObjectLike(value.lastModifiedDate))
		);
	}

	/**
	 * Checks that value is FormData
	 * @param value
	 * @return {boolean}
	 */
	static isFormData(value: any)
	{
		return value instanceof FormData;
	}
}
