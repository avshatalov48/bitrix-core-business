export default class AddressLink
{
	#entityId;
	#entityType;

	constructor(props)
	{
		this.#entityId = props.entityId;
		this.#entityType = props.entityType;
	}

	get entityId()
	{
		return this.#entityId;
	}

	get entityType()
	{
		return this.#entityType
	}
}