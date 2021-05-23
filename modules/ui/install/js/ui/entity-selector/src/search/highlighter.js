import { Type, Text } from 'main.core';
import type MatchIndex from './match-index';
import type { OrderedArray } from 'main.core.collections';

export default class Highlighter
{
	static mark(text: string, matches: OrderedArray<MatchIndex>)
	{
		if (!Type.isStringFilled(text) || !matches || matches.count() === 0)
		{
			return text;
		}

		let result = '';
		let offset = 0;
		matches.forEach((match: MatchIndex) => {

			if (offset > match.getStartIndex())
			{
				return;
			}

			// console.log(match.getStartIndex(), match.getEndIndex(), match.getQueryWord());

			result += Text.encode(text.substring(offset, match.getStartIndex()));
			result += '<span class="ui-selector-highlight-mark">';
			result += Text.encode(text.substring(match.getStartIndex(), match.getEndIndex()));
			result += '</span>';

			offset = match.getEndIndex();

		});

		result += Text.encode(text.substring(offset));

		// console.log(result);

		return result;
	}
}