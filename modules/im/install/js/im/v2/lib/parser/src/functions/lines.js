import {Loc} from 'main.core';

export const ParserLines = {

	decode(text): string
	{
		text = text.replace(
			/\[LIKE]/gi,
			`<span style="color: #004d00">${Loc.getMessage('IM_PARSER_LINES_RATING_LIKE')}</span>`
		);

		text = text.replace(
			/\[DISLIKE]/gi,
			`<span style="color: #cc0000">${Loc.getMessage('IM_PARSER_LINES_RATING_DISLIKE')}</span>`
		);

		text = text.replace(/\[RATING=([1-5])]/gi, (rating, group) => {
			rating = parseInt(group);
			return `<span class="bx-smile bx-im-smile-rating bx-im-smile-rating-'+rating+'">${Loc.getMessage('IM_PARSER_LINES_RATING')} - ${rating}</span>`;
		});

		return text;
	},

	purify(text): string
	{
		text = text.replace(
			/\[LIKE]/gi,
			Loc.getMessage('IM_PARSER_LINES_RATING_LIKE')
		);

		text = text.replace(
			/\[DISLIKE]/gi,
			Loc.getMessage('IM_PARSER_LINES_RATING_DISLIKE')
		);

		text = text.replace(/\[RATING=([1-5])]/gi, () => {
			return '['+Loc.getMessage('IM_PARSER_LINES_RATING')+'] ';
		});

		return text;
	}
}

