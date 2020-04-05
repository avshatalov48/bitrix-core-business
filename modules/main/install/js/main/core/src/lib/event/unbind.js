import Type from '../type';
import aliases from './aliases';
import registry from './registry';
import fetchSupportedListenerOptions from './fetch-supported-listener-options';

export default function unbind(
	target: Element,
	eventName: string,
	handler: Function,
	options?: {
		capture?: boolean,
		once?: boolean,
		passive?: boolean,
	},
): void
{
	if (
		!Type.isObject(target)
		|| !Type.isFunction(target.removeEventListener)
	)
	{
		return;
	}

	const listenerOptions = fetchSupportedListenerOptions(options);

	if (eventName in aliases)
	{
		aliases[eventName].forEach((key) => {
			target.removeEventListener(key, handler, listenerOptions);
			registry.delete(target, key, handler);
		});

		return;
	}

	target.removeEventListener(eventName, handler, listenerOptions);
	registry.delete(target, eventName, handler);
}