import { Type } from 'main.core';

const Context = Object.freeze({
	B24: 'b24',
});

export class Interceptor
{
	static try(context: string, userId: ?string, node: HTMLElement): boolean
	{
		if (context !== Context.B24)
		{
			return false;
		}

		if (
			!Type.isStringFilled(userId)
			|| !Type.isNumber(Number(userId)))
		{
			return false;
		}

		if (BX.Intranet?.User?.UserMiniProfileManager)
		{
			BX.Intranet.User.UserMiniProfileManager.create({
				id: userId,
				userId: Number(userId),
				bindElement: node,
			});

			return true;
		}

		return false;
	}
}
