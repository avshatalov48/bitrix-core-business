import Type from '../type';
import aliases from './aliases';
import registry from './registry';
import fetchSupportedListenerOptions from './fetch-supported-listener-options';

export default function bind(
	target: Element,
	eventName: string,
	handler: (event: Event) => void,
	options?: {
		capture?: boolean,
		once?: boolean,
		passive?: boolean,
	},
): void
{
	if (
		!Type.isObject(target)
		|| !Type.isFunction(target.addEventListener)
	)
	{
		return;
	}

	const listenerOptions = fetchSupportedListenerOptions(options);

	if (eventName in aliases)
	{
		aliases[eventName].forEach((key) => {
			target.addEventListener(key, handler, listenerOptions);
			registry.set(target, eventName, handler);
		});

		return;
	}

	target.addEventListener(eventName, handler, listenerOptions);
	registry.set(target, eventName, handler);
}