import ActionRunner from '../../src/repository/actionrunner';

class ActionTestRunner extends ActionRunner
{
	#response;

	constructor()
	{
		super({path: 'testPath'});
	}

	set response(response)
	{
		this.#response = response;
	}

	run(action, data)
	{
		if(!action)
		{
			throw new Error('action must not be empty!');
		}

		return new Promise(
			() => this.#response,
			(data) => BX.debug(data)
		);
	}
}

export default ActionTestRunner;