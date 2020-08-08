import Type from '../type';
import Runtime from '../runtime';
import EventEmitter from '../event/event-emitter';
import BaseEvent from '../event/base-event';

type messageParam = string | {[key: string]: string | number};

export default function message(value: messageParam): string | boolean | void
{
	if (Type.isString(value))
	{
		if (Type.isNil(message[value]))
		{
			// eslint-disable-next-line
			EventEmitter.emit('onBXMessageNotFound', new BaseEvent({ compatData: [value] }));

			if (Type.isNil(message[value]))
			{
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