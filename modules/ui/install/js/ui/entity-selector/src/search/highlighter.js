import { Type, Text } from 'main.core';
import TextNode from '../common/text-node';
import type MatchIndex from './match-index';
import type { OrderedArray } from 'main.core.collections';

export default class Highlighter
{
	static mark(text: string | TextNode, matches: OrderedArray<MatchIndex>)
	{
		let encode = true;
		if (text instanceof TextNode)
		{
			if (text.getType() === 'html')
			{
				encode = false;
			}

			text = text.getText();
		}

		if (!Type.isStringFilled(text) || !matches || matches.count() === 0)
		{
			return text;
		}

		let result = '';
		let offset = 0;
		let chunk = '';
		matches.forEach((match: MatchIndex) => {

			if (offset > match.getStartIndex())
			{
				return;
			}

			chunk = text.substring(offset, match.getStartIndex());
			result += encode ? Text.encode(chunk) : chunk;

			result += '<span class="ui-selector-highlight-mark">';

			chunk = text.substring(match.getStartIndex(), match.getEndIndex());
			result += encode ? Text.encode(chunk) : chunk;

			result += '</span>';

			offset = match.getEndIndex();

		});

		chunk = text.substring(offset);
		result += encode ? Text.encode(chunk) : chunk;

		return result;
	}
}