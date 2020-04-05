import Type from '../type';
import getTag from '../../internal/get-tag';

const cloneableTags = [
	'[object Object]',
	'[object Array]',
	'[object RegExp]',
	'[object Arguments]',
	'[object Date]',
	'[object Error]',
	'[object Map]',
	'[object Set]',
	'[object ArrayBuffer]',
	'[object DataView]',
	'[object Float32Array]',
	'[object Float64Array]',
	'[object Int8Array]',
	'[object Int16Array]',
	'[object Int32Array]',
	'[object Uint8Array]',
	'[object Uint16Array]',
	'[object Uint32Array]',
	'[object Uint8ClampedArray]',
];

function isCloneable(value: any): boolean %checks
{
	const isCloneableValue = Type.isObjectLike(value)
		&& cloneableTags.includes(getTag(value));

	return isCloneableValue || Type.isDomNode(value);
}

export function internalClone(value: any, map: WeakMap<any, any>): any
{
	if (map.has(value))
	{
		return map.get(value);
	}

	if (isCloneable(value))
	{
		if (Type.isArray(value))
		{
			const cloned = Array.from(value);

			map.set(value, cloned);

			value.forEach((item, index) => {
				cloned[index] = internalClone(item, map);
			});

			return map.get(value);
		}

		if (Type.isDomNode(value))
		{
			return value.cloneNode(true);
		}

		if (Type.isMap(value))
		{
			const result = new Map();

			map.set(value, result);

			value.forEach((item, key) => {
				result.set(
					internalClone(key, map),
					internalClone(item, map)
				);
			});

			return result;
		}

		if (Type.isSet(value))
		{
			const result = new Set();

			map.set(value, result);

			value.forEach((item) => {
				result.add(internalClone(item, map));
			});

			return result;
		}

		if (Type.isDate(value))
		{
			return new Date(value);
		}

		if (Type.isRegExp(value))
		{
			const regExpFlags = /\w*$/;
			const flags = regExpFlags.exec(value);

			let result = new RegExp(value.source);

			if (flags && Type.isArray(flags))
			{
				result = new RegExp(value.source, flags[0]);
			}

			result.lastIndex = value.lastIndex;

			return result;
		}

		const proto = Object.getPrototypeOf(value);
		const result = Object.assign(Object.create(proto), value);

		map.set(value, result);

		Object.keys(value).forEach((key) => {
			result[key] = internalClone(value[key], map);
		});

		return result;
	}

	return value;
}

/**
 * Clones any cloneable object
 * @param value
 * @return {*}
 */
export default function clone(value: any): any
{
	return internalClone(value, new WeakMap());
}