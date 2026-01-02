import { Type } from 'main.core';

export type CreateHook = {
	on: (handler: () => void) => void,
	off: (handler: () => void) => void,
	trigger: (...args: any) => void,
};

export function createHook(): CreateHook
{
	const handlers = new Set();

	const on = (handler: () => void) => {
		if (Type.isFunction(handler) && !handlers.has(handler))
		{
			handlers.add(handler);
		}
	};

	const off = (handler) => {
		handlers.delete(handler);
	};

	const trigger = (...args) => {
		for (const handler of handlers)
		{
			handler(...args);
		}
	};

	return {
		on,
		off,
		trigger,
	};
}
