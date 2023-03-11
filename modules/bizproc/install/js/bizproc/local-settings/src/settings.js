import {Cache, Type} from 'main.core';

export class Settings
{
	#prefix = 'bp'

	constructor(section: string)
	{
		if (section)
		{
			this.#prefix += ('-' + section);
		}
	}

	getSet(name: string): Set
	{
		const value = this.get(name);

		return value instanceof Array ? new Set(value) : new Set();
	}

	get(name: string): any
	{
		const settings = (new Cache.LocalStorageCache()).remember(this.#prefix, {});

		return settings.hasOwnProperty(name) ? settings[name] : null;
	}

	set(name: string, value: any): this
	{
		if (value instanceof Set)
		{
			value = Array.from(value);
		}

		const cache = new Cache.LocalStorageCache();
		const settings = cache.remember(this.#prefix, {});
		settings[name] = value;

		cache.set(this.#prefix, settings);

		return this;
	}

	remember(key: string, defaultValue)
	{
		const cacheValue = this.get(key);

		if (!Type.isNull(cacheValue))
		{
			return cacheValue;
		}

		this.set(key, defaultValue);

		return this.get(key);
	}

	getAll(): {}
	{
		return (new Cache.LocalStorageCache()).remember(this.#prefix, {});
	}

	deleteAll()
	{
		const cache = new Cache.LocalStorageCache();
		cache.set(this.#prefix, {});
	}
}