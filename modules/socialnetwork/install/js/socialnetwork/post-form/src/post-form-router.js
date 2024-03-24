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
				top.BX.Socialnetwork.Spaces.space.reloadPageContent(
					this.#pathToGroupRedirect.replace('#group_id#', groupId),
				);
			}
			else
			{
				top.BX.Socialnetwork.Spaces.space.reloadPageContent();
			}
		}
		else
		{
			// eslint-disable-next-line no-lonely-if
			if (this.#pathToDefaultRedirect)
			{
				top.BX.Socialnetwork.Spaces.space.reloadPageContent(this.#pathToDefaultRedirect);
			}
			else
			{
				top.BX.Socialnetwork.Spaces.space.reloadPageContent();
			}
		}
	}
}
