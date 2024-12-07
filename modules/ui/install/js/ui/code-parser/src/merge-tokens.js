import { type CodeToken } from './token-types';

export const mergeTokens = (tokens: CodeToken[]): CodeToken[] => {
	const result: CodeToken[] = [];
	let prevToken = null;
	tokens.forEach((token: CodeToken) => {
		// Merge sibling words into one word token
		if (
			(token.type === 'whitespace' || token.type === 'word')
			&& prevToken !== null
			&& (prevToken.type === 'whitespace' || prevToken.type === 'word')
		)
		{
			prevToken.type = 'word';
			prevToken.content += token.content;

			return;
		}

		// Merge operator like '===' or '++' into one token
		if (token.type === 'operator' && prevToken !== null && prevToken.type === 'operator')
		{
			prevToken.content += token.content;

			return;
		}

		prevToken = token;
		result.push(token);
	});

	return result;
};
