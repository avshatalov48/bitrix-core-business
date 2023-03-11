import { Type } from 'main.core';
import { Operator } from 'bizproc.condition';

export class Condition
{
	#object: string;
	#field: string;
	#operator: string;
	#value: string;

	parentGroup;

	constructor(params: ?Object, group)
	{
		this.#object = 'Document';
		this.#field = '';
		this.#operator = '!empty';
		this.#value = '';

		this.parentGroup = null;

		if (Type.isPlainObject(params))
		{
			if (params['object'])
			{
				this.setObject(params['object']);
			}
			if (params['field'])
			{
				this.setField(params['field']);
			}
			if (params['operator'])
			{
				this.setOperator(params['operator']);
			}
			if ('value' in params)
			{
				this.setValue(params['value']);
			}
		}
		if (group)
		{
			this.parentGroup = group;
		}
	}

	clone(): Condition
	{
		return new Condition(
			{
				object: this.#object,
				field: this.#field,
				operator: this.#operator,
				value: this.#value,
			},
			this.parentGroup,
		);
	}

	setObject(object)
	{
		if (Type.isStringFilled(object))
		{
			this.#object = object;
		}
	}

	get object()
	{
		return this.#object;
	}

	setField(field)
	{
		if (Type.isStringFilled(field))
		{
			this.#field = field;
		}
	}

	get field()
	{
		return this.#field;
	}

	setOperator(operator)
	{
		if (!operator)
		{
			operator = Operator.EQUAL;
		}

		this.#operator = operator;
	}

	get operator(): string
	{
		return this.#operator;
	}

	setValue(value)
	{
		this.#value = value;
		if (this.#operator === Operator.EQUAL && this.#value === '')
		{
			this.#operator = 'empty';
		}
		else if (this.#operator === Operator.NOT_EQUAL && this.#value === '')
		{
			this.#operator = '!empty';
		}
	}

	get value()
	{
		return this.#value;
	}

	serialize(): Object
	{
		return {
			object: this.#object,
			field: this.#field,
			operator: this.#operator,
			value: this.#value
		}
	}
}