import Type from "../../type";

export default class EventStore
{
	constructor(options: { defaultMaxListeners?: number } = {})
	{
		this.defaultMaxListeners = Type.isNumber(options.defaultMaxListeners) ? options.defaultMaxListeners : 10;
		this.eventStore = new WeakMap();
	}

	add(target: Object, options: { maxListeners?: number } = {})
	{
		const record = this.getRecordScheme();
		if (Type.isNumber(options.maxListeners))
		{
			record.maxListeners = options.maxListeners;
		}

		this.eventStore.set(target, record);

		return record;
	}

	get(target: Object)
	{
		return this.eventStore.get(target);
	}

	getOrAdd(target: Object, options: { maxListeners?: number } = {})
	{
		return this.get(target) || this.add(target, options);
	}

	delete(context: any)
	{
		this.eventStore.delete(context);
	}

	getRecordScheme()
	{
		return {
			eventsMap: new Map(),
			onceMap: new Map(),
			maxListeners: this.getDefaultMaxListeners(),
			eventsMaxListeners: new Map(),
		};
	}

	getDefaultMaxListeners()
	{
		return this.defaultMaxListeners;
	}
}

