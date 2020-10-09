import Type from '../../type';

export default function deepFreeze(target: {[key: string]: any})
{
	if (Type.isObject(target))
	{
		Object.values(target).forEach((value) => {
			deepFreeze(value);
		});

		return Object.freeze(target);
	}

	return target;
}