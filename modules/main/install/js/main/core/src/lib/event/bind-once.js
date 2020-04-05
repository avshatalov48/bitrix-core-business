import bind from './bind';
import unbind from './unbind';

export default function bindOnce(
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
	const once = function once(...args) {
		unbind(target, eventName, once, options);
		handler(...args);
	};

	bind(target, eventName, once, options);
}