import Field from './field';

export default class FieldCollection
{
	#fields = {};

	constructor(props = {})
	{
		this.fields = props.fields ? props.fields : [];
	}

	set fields(fields)
	{
		if(!Array.isArray(fields))
		{
			throw new Error('Items must be array!');
		}

		for(const field of fields)
		{
			this.setField(field);
		}

		return this;
	}

	get fields()
	{
		return this.#fields;
	}

	/**
	 * Checks if field already exist in collection
	 * @param {int} type
	 * @returns {boolean}
	 */
	isFieldExists(type)
	{
		return typeof this.#fields[type] !== 'undefined';
	}

	getField(type)
	{
		return this.isFieldExists(type) ? this.#fields[type] : null;
	}

	setField(field)
	{
		if(!(field instanceof Field))
		{
			throw new Error('Argument field must be instance of Field!');
		}

		this.#fields[field.type] = field;
		return this;
	}

	deleteField(type)
	{
		if(this.isFieldExists(type))
		{
			delete this.#fields[type];
		}
	}

	getMaxFieldType()
	{
		const types = Object.keys(this.#fields).sort((a, b) => {
			return parseInt(a) - parseInt(b);
		});

		let result = 0;

		if(types.length > 0)
		{
			result = types[types.length - 1];
		}

		return result;
	}

	isEqual(addressFieldCollection: FieldCollection, upTo: Number)
	{
		return (
			FieldCollection.areEqual(this, addressFieldCollection, upTo)
			&& FieldCollection.areEqual(addressFieldCollection, this, upTo)
		);
	}

	static areEqual(addressFieldCollection1: FieldCollection, addressFieldCollection2: FieldCollection, upTo: Number)
	{
		for (let type in addressFieldCollection1.fields)
		{
			if (type > upTo)
			{
				continue;
			}

			let field = addressFieldCollection2.getField(type);
			if (!field)
			{
				return false;
			}

			if (addressFieldCollection1.fields[type].value !== field.value)
			{
				return false;
			}
		}

		return true;
	}
}
