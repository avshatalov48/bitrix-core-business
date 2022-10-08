import Field from "../generic/field";

export default class AddressField extends Field
{
	#value;

	//todo: Fields validation
	constructor(props)
	{
		super(props);
		this.#value = props.value || '';
	}

	get value()
	{
		return this.#value;
	}

	set value(value)
	{
		this.#value = value;
		return this;
	}
}