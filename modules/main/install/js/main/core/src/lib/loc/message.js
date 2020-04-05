import Type from '../type';
import Runtime from '../runtime';

type messageParam = string | {[key: string]: string | number};

export default function message(value: messageParam): string | boolean | void
{
	if (Type.isString(value))
	{
		if (Type.isNil(message[value]))
		{
			// eslint-disable-next-line
			BX.onCustomEvent(window, 'onBXMessageNotFound', [value]);

			if (Type.isNil(message[value]))
			{
				// eslint-disable-next-line
				BX.onCustomEvent(window, 'onBXMessageNotFound', [value]);
				Runtime.debug(`message undefined: ${value}`);
				message[value] = '';
			}
		}
	}

	if (Type.isPlainObject(value))
	{
		Object.keys(value).forEach((key) => {
			message[key] = value[key];
		});
	}

	return message[value];
}

if (
	!Type.isNil(window.BX)
	&& Type.isFunction(window.BX.message)
)
{
	Object.keys(window.BX.message).forEach((key) => {
		message({[key]: window.BX.message[key]});
	});
}