import { Type, clone } from "main.core";
import { EventEmitter, BaseEvent } from "main.core.events";

export class BaseContext extends EventEmitter
{
	#values: Object<string, any>;

	constructor(defaultValue: Object<string, any>)
	{
		super();

		this.setEventNamespace('BX.Bizproc.Automation.Context');
		if (Type.isPlainObject(defaultValue))
		{
			this.#values = defaultValue;
		}
	}

	clone(): this
	{
		return new BaseContext(clone(this.#values));
	}

	getValues(): object
	{
		return this.#values;
	}

	set(name: string, value: any): this
	{
		const isValueChanged = this.has(name);
		this.#values[name] = value;
		this.emit(isValueChanged ? 'valueChanged' : 'valueAdded', {name, value})

		return this;
	}

	get(name: string): any
	{
		return this.#values[name];
	}

	has(name: string): boolean
	{
		return this.#values.hasOwnProperty(name);
	}

	subsribeValueChanges(name: string, listener: (BaseEvent) => void): this
	{
		this.subscribe('valueChanged', (event) => {
			if (event.data.name === name)
			{
				listener(event);
			}
		});

		return this;
	}
}