export default class ActionRunner
{
	#path = '';

	constructor(props)
	{
		if(!props.path)
		{
			throw new Error('props.path must not be empty!');
		}

		this.#path = props.path;
	}

	run(action, data)
	{
		if(!action)
		{
			throw new Error('action can not be empty!');
		}

		return BX.ajax.runAction(`${this.#path}.${action}`, {data});
	}
}