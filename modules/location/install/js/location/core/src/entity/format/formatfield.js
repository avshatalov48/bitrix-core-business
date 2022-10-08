import Field from '../generic/field';

export default class FormatField extends Field
{
	#sort;
	#name;
	#description;

	// todo: Fields validation
	constructor(props)
	{
		super(props);

		this.#sort = parseInt(props.sort);
		this.#name = props.name || '';
		this.#description = props.description || '';
	}

	get sort()
	{
		return this.#sort;
	}

	set sort(sort)
	{
		this.#sort = sort;
	}

	get name()
	{
		return this.#name;
	}

	set name(name)
	{
		this.#name = name;
	}

	get description()
	{
		return this.#description;
	}

	set description(description)
	{
		this.#description = description;
	}
}