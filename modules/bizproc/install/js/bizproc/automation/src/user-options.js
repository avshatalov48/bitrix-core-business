import { Type, Runtime } from 'main.core';

export class UserOptions
{
	#options: Object<string, any>;

	constructor(options: Object<string, any>)
	{
		this.#options = options;
	}

	clone(): this
	{
		return new UserOptions(Runtime.clone(this.#options));
	}

	set(category: string, key: string, value: any): UserOptions
	{
		if (!Type.isPlainObject(this.#options[category]))
		{
			this.#options[category] = {};
		}
		const storedValue = this.#options[category][key];

		if (storedValue !== value)
		{
			BX.userOptions.save(
				'bizproc.automation',
				category,
				key,
				value,
				false
			);
		}

		return this;
	}

	get(category: string, key: string, defaultValue: any): any
	{
		let result = defaultValue;
		if (this.has(category, key))
		{
			result = this.#options[category][key];
		}

		return result;
	}

	has(category: string, key: string): boolean
	{
		return Type.isPlainObject(this.#options[category]) && Type.isPlainObject(this.#options[category][key]);
	}
}