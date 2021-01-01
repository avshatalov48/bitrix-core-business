import {Type} from 'main.core';

type Events = {
	[eventName: string]: () => any,
};

type Options = {
	[key: string]: any,
};

export default function fetchEventsFromOptions(options: Options): Events
{
	if (Type.isPlainObject(options))
	{
		return Object.entries(options).reduce((acc, [key, value]) => {
			if (
				Type.isString(key)
				&& key.startsWith('on')
				&& Type.isFunction(value)
			)
			{
				acc[key] = value;
			}

			return acc;
		}, {});
	}

	return {};
}