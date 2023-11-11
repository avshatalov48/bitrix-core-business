import { Loc, Tag } from 'main.core';

export const ParserLines = {

	decode(text): string
	{
		let result = text;

		result = result.replaceAll(
			/\[like]/gi,
			`<span class="bx-im-lines-vote-like" title="${Loc.getMessage('IM_PARSER_LINES_RATING_LIKE')}"></span>`,
		);

		result = result.replaceAll(
			/\[dislike]/gi,
			`<span class="bx-im-lines-vote-dislike" title="${Loc.getMessage('IM_PARSER_LINES_RATING_DISLIKE')}"></span>`,
		);

		result = result.replaceAll(/\[rating=([1-5])]/gi, (whole, rating) => {
			const tag = Tag.render`
				<span class="bx-im-lines-rating" title="${Loc.getMessage('IM_PARSER_LINES_RATING')} - ${rating}">
					<span class="bx-im-lines-rating-selected" style="width: ${rating * 20}%"></span>
				</span>
			`;

			return tag.outerHTML;
		});

		return result;
	},

	purify(text): string
	{
		let result = text;

		result = result.replaceAll(
			/\[like]/gi,
			Loc.getMessage('IM_PARSER_LINES_RATING_LIKE'),
		);

		result = result.replaceAll(
			/\[dislike]/gi,
			Loc.getMessage('IM_PARSER_LINES_RATING_DISLIKE'),
		);

		result = result.replaceAll(/\[rating=([1-5])]/gi, () => {
			return `[${Loc.getMessage('IM_PARSER_LINES_RATING')}] `;
		});

		return result;
	},
};
