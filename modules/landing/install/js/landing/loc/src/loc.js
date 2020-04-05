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
		const pageType = (() => {
			const type = Env.getInstance().getType();
			return pageTypeAlias[type] || type;
		})();

		if (pageType)
		{
			const typedMessageKey = `${key}__${pageType}`;

			if (Type.isString(BX.message[typedMessageKey]))
			{
				return MainLoc.getMessage(typedMessageKey);
			}
		}

		return MainLoc.getMessage(key);
	}
}