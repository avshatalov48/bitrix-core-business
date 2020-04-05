import Type from '../type';
import unbind from './unbind';
import registry from './registry';

export default function unbindAll(target: any, eventName?: string): void
{
	const events = registry.get(target);

	Object.keys(events).forEach((currentEvent) => {
		events[currentEvent].forEach((handler) => {
			if (!Type.isString(eventName) || eventName === currentEvent)
			{
				unbind(target, currentEvent, handler);
			}
		});
	});
}