import { Type } from 'main.core';

type Params = {
	pathToDefaultRedirect: string,
	pathToGroupRedirect: string,
}

export class PostFormRouter
{
	#pathToDefaultRedirect: string;
	#pathToGroupRedirect: string;

	constructor(params: Params)
	{
		this.#pathToDefaultRedirect = Type.isString(params.pathToDefaultRedirect)
			? params.pathToDefaultRedirect
			: ''
		;
		this.#pathToGroupRedirect = Type.isString(params.pathToGroupRedirect)
			? params.pathToGroupRedirect
			: ''
		;
	}

	redirectTo(groupId: number)
	{
		if (groupId)
		{
			if (this.#pathToGroupRedirect)
			{
				location.href = this.#pathToGroupRedirect.replace('#group_id#', groupId);
			}
			else
			{
				location.reload();
			}
		}
		else
		{
			// eslint-disable-next-line no-lonely-if
			if (this.#pathToDefaultRedirect)
			{
				location.href = this.#pathToDefaultRedirect;
			}
			else
			{
				location.reload();
			}
		}
	}
}
