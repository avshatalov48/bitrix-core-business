import Type from '../type';

export class Registry
{
	registry: WeakMap = new WeakMap();

	set(target: Element, event: string, listener: Function)
	{
		const events = this.get(target);

		if (!Type.isSet(events[event]))
		{
			events[event] = new Set();
		}

		events[event].add(listener);

		this.registry.set(target, events);
	}

	get(target: Element): {[event: string]: Set<Function>}
	{
		return this.registry.get(target) || {};
	}

	has(target: Element, event?: string, listener?: Function): boolean
	{
		if (event && listener)
		{
			return (
				this.registry.has(target)
				&& this.registry.get(target)[event].has(listener)
			);
		}

		return this.registry.has(target);
	}

	delete(target: Element, event?: string, listener?: Function)
	{
		if (!Type.isDomNode(target))
		{
			return;
		}

		if (Type.isString(event) && Type.isFunction(listener))
		{
			const events = this.registry.get(target);

			if (Type.isPlainObject(events) && Type.isSet(events[event]))
			{
				events[event].delete(listener);
			}

			return;
		}

		if (Type.isString(event))
		{
			const events = this.registry.get(target);

			if (Type.isPlainObject(events) && Type.isSet(events[event]))
			{
				events[event] = new Set();
			}

			return;
		}

		this.registry.delete(target);
	}
}

export default new Registry();