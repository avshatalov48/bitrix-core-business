export default class Field
{
	#type;

	constructor(props)
	{
		if(typeof props.type === 'undefined')
		{
			throw new Error('Field type must be defined');
		}

		this.#type = parseInt(props.type);
	}

	get type()
	{
		return this.#type;
	}
}