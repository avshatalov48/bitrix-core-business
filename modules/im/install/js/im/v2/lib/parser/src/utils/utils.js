import {Type} from 'main.core';

import {getCore} from './core-proxy';

const RECURSIVE_LIMIT = 10;
export const ParserUtils = {

	recursiveReplace(text, pattern, replacement): string
	{
		if (!Type.isStringFilled(text))
		{
			return text;
		}

		let count = 0;
		let deep = true;
		do
		{
			deep = false;
			count++;
			text = text.replace(pattern, (...params) => {
				deep = true;
				return replacement(...params);
			});
		}
		while (deep && count <= RECURSIVE_LIMIT);

		return text;
	},

	getFinalContextTag(contextTag)
	{
		const match = contextTag.match(/(chat\d+|(\d+):(\d+))\/(\d+)/i);
		if (!match)
		{
			return '';
		}

		let [, dialogId, user1, user2, messageId] = match;
		if (dialogId.toString().startsWith('chat'))
		{
			if (dialogId === 'chat0')
			{
				return '';
			}

			return contextTag;
		}

		user1 = Number.parseInt(user1, 10);
		user2 = Number.parseInt(user2, 10);
		if (getCore().getUserId() === user1)
		{
			return `${user2}/${messageId}}`;
		}
		if (getCore().getUserId() === user2)
		{
			return `${user1}/${messageId}}`;
		}

		return '';
	},
};