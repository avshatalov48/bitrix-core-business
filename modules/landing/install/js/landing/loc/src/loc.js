import {Type, Loc as MainLoc} from 'main.core';
import {Env} from 'landing.env';

const pageTypeAlias = {
	GROUP: 'KNOWLEDGE',
};

/**
 * @memberOf BX.Landing
 */
export class Loc extends MainLoc
{
	static getMessage(key: string): string
	{
		const types = (() => {
			const type = Env.getInstance().getType();
			const specialType = Env.getInstance().getSpecialType() || '';

			return {
				type: pageTypeAlias[type] || type,
				specialType: specialType.toUpperCase(),
			};
		})();

		if (types)
		{
			if (types.specialType.length > 0)
			{
				const specialTypeMessageKey = `${key}__${types.specialType}`;
				if (Type.isString(BX.message[specialTypeMessageKey]))
				{
					return MainLoc.getMessage(specialTypeMessageKey);
				}
			}

			const typedMessageKey = `${key}__${types.type}`;
			if (Type.isString(BX.message[typedMessageKey]))
			{
				return MainLoc.getMessage(typedMessageKey);
			}
		}

		return MainLoc.getMessage(key);
	}
}