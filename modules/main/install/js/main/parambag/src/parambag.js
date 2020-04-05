export class ParamBag
{
	constructor(params = {})
	{
		if (!!params && typeof params === 'object')
		{
			this.params = new Map(Object.entries(params));
		}
		else
		{
			this.params = new Map();
		}
	}

	static create(params = {})
	{
		return new ParamBag(params);
	}

	getParam(key: string, defaultValue = null)
	{
		if (this.params.has(key))
		{
			return this.params.get(key);
		}

		return defaultValue;
	}

	setParam(key, value)
	{
		this.params.set(key, value);
	}

	clear()
	{
		this.params.clear();
	}
}