import Field from '../generic/field';

export default class LocationField extends Field
{
	#value;

	// todo: Fields validation
	constructor(props)
	{
		super(props);
		this.#value = props.value || '';
	}

	get value(): string
	{
		return this.#value;
	}

	set value(value: string)
	{
		this.#value = value;
	}
}