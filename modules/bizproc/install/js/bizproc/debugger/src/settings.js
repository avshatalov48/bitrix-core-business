import "ls";

export default class Settings
{
	#ttl = 3 * 86400;
	#prefix = 'bp-atm-dbg-'

	#getName(name: string)
	{
		return this.#prefix + name;
	}

	getSet(name: string): Set
	{
		const value = this.get(name);

		return value instanceof Array ? new Set(value) : new Set();
	}

	get(name: string): any
	{
		return BX.localStorage.get(this.#getName(name));
	}

	set(name: string, value: any): this
	{
		if (value instanceof Set)
		{
			value = Array.from(value);
		}

		BX.localStorage.set(this.#getName(name), value, this.#ttl);

		return this;
	}
}