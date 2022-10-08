import ActionRunner from './actionrunner';

export default class BaseRepository
{
	#actionRunner = null;

	constructor(props = {})
	{
		this._path = props.path;

		if(props.actionRunner && props.actionRunner instanceof ActionRunner)
		{
			this.#actionRunner = props.actionRunner;
		}
		else
		{
			this.#actionRunner = new ActionRunner({path: this._path});
		}
	}

	get path()
	{
		return this._path;
	}

	get actionRunner()
	{
		return this.#actionRunner;
	}

	processResponse(response: Object)
	{
		if(response.status !== 'success')
		{
			BX.debug('Request was not successful');
			let message = '';

			if(Array.isArray(response.errors) && response.errors.length > 0)
			{
				for(const error of response.errors)
				{
					if(typeof error.message === 'string' && error.message !== '')
					{
						message += `${error}\n`;
					}
				}
			}

			throw new Error(message);
		}

		return response.data ? response.data : null;
	}
}