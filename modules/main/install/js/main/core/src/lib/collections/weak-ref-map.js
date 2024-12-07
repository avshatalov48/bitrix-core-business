import Type from '../type';

// eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
const IS_WEAK_REF_SUPPORTED = typeof WeakRef !== 'undefined';

export default class WeakRefMap<K, V>
{
	#refs: Map<K, WeakRef> = new Map();
	#registry: FinalizationRegistry = null;

	constructor()
	{
		if (IS_WEAK_REF_SUPPORTED)
		{
			this.#registry = new FinalizationRegistry(this.#cleanupCallback.bind(this));
		}
	}

	clear(): void
	{
		if (!IS_WEAK_REF_SUPPORTED)
		{
			this.#refs.clear();

			return;
		}

		this.#refs.forEach((ref: WeakRef, key: K) => {
			const value = ref?.deref();
			if (!Type.isUndefined(value))
			{
				this.#registry.unregister(value);
			}
		});

		this.#refs.clear();
	}

	delete(key: K): boolean
	{
		if (!IS_WEAK_REF_SUPPORTED)
		{
			return this.#refs.delete(key);
		}

		const value = this.get(key);
		if (!Type.isUndefined(value))
		{
			this.#registry.unregister(value);
		}

		return this.#refs.delete(key);
	}

	get(key: K): V | undefined
	{
		if (!IS_WEAK_REF_SUPPORTED)
		{
			return this.#refs.get(key);
		}

		return this.#refs.get(key)?.deref();
	}

	has(key: K): boolean
	{
		if (!IS_WEAK_REF_SUPPORTED)
		{
			return this.#refs.has(key);
		}

		return !Type.isUndefined(this.#refs.get(key)?.deref());
	}

	set(key: K, value: V): this
	{
		if (!IS_WEAK_REF_SUPPORTED)
		{
			this.#refs.set(key, value);

			return this;
		}

		this.#refs.set(key, new WeakRef(value));
		this.#registry.register(value, key, value);

		return this;
	}

	#cleanupCallback(key: K): void
	{
		const ref = this.#refs.get(key);
		if (ref && !ref.deref())
		{
			this.#refs.delete(key);
		}
	}
}
