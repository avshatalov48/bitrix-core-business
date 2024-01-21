import Type from '../type';

export default class BaseCache<T>
{
	/**
	 * @private
	 */
	storage: Map<string, T> = new Map();

	/**
	 * Gets cached value or default value
	 */
	get(key: string, defaultValue?: T | () => T): T
	{
		if (!this.storage.has(key))
		{
			if (Type.isFunction(defaultValue))
			{
				return defaultValue();
			}

			if (!Type.isUndefined(defaultValue))
			{
				return defaultValue;
			}
		}

		return this.storage.get(key);
	}

	/**
	 * Sets cache entry
	 */
	set(key: string, value: T)
	{
		this.storage.set(key, value);
	}

	/**
	 * Deletes cache entry
	 */
	delete(key: string)
	{
		this.storage.delete(key);
	}

	/**
	 * Checks that storage contains entry with specified key
	 */
	has(key: string): boolean
	{
		return this.storage.has(key);
	}

	/**
	 * Gets cached value if exists,
	 */
	remember(key: string, defaultValue?: T | () => T): T
	{
		if (!this.storage.has(key))
		{
			if (Type.isFunction(defaultValue))
			{
				this.storage.set(key, defaultValue());
			}
			else if (!Type.isUndefined(defaultValue))
			{
				this.storage.set(key, defaultValue);
			}
		}

		return this.storage.get(key);
	}

	/**
	 * Gets storage size
	 */
	size(): number
	{
		return this.storage.size;
	}

	/**
	 * Gets storage keys
	 */
	keys(): Array<string>
	{
		return [...this.storage.keys()];
	}

	/**
	 * Gets storage values
	 */
	values(): Array<T>
	{
		return [...this.storage.values()];
	}
}
