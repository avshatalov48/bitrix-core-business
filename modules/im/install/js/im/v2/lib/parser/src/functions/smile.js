import {Dom, Tag, Type} from 'main.core';

import {getCore, getUtils, getSmileManager, getBigSmileOption} from '../utils/core-proxy';

import type {Smile} from '../utils/core-proxy';

export const RatioConfig = Object.freeze({
	Default: 1,
	Big: 1.6,
});
const getSmileRatio = (text: string, pattern: string, config = RatioConfig): number =>
{
	const replacedText = text.replaceAll(new RegExp(pattern, 'g'), '');
	const hasOnlySmiles = replacedText.trim().length === 0;

	const matchOnlySmiles = new RegExp(`(?:(?:${pattern})\\s*){3,}`);
	if (hasOnlySmiles && !matchOnlySmiles.test(text))
	{
		return config.Big;
	}

	return config.Default;
};
const mapTypings = (smiles: Array<Smile>): {[string]: HTMLElement} =>
{
	const typings = smiles.reduce((acc, smile) => {
		const {
			image,
			typing,
			definition,
			name,
			width,
			height
		} = smile;
		const smileImg = Tag.render`
			<img
				src="${image}"
				data-code="${typing}"
				data-definition="${definition}"
				title="${name ?? typing}"
				alt="${typing}"
				class="bx-smile bx-im-message-base__text_smile"
				style="width: ${width}px; height: ${height}px;"
			/>
		`;

		return {...acc, [typing]: smileImg};
	}, {});

	return typings;
};
const lookBehind = function (text, match, offset): string
{
	const substring = text.slice(0, offset + match.length);
	const escaped = getUtils().text.escapeRegex(match);
	const regExp = new RegExp(`(?:^|&quot;|>|(?:${this.pattern})|\\s|<)(?:${escaped})$`);

	return substring.match(regExp);
};

export const ParserSmile = {
	typings: null,
	pattern: '',

	loadSmilePatterns()
	{
		const smileManager = getSmileManager().getInstance();
		const smiles = smileManager.smileList?.smiles ?? [];
		const sortedSmiles = [...smiles].sort((a, b) => {
			return b.typing.localeCompare(a.typing);
		});
		this.pattern = sortedSmiles.map((smile) => {
			return getUtils().text.escapeRegex(smile.typing);
		}).join('|');
		this.typings = mapTypings(sortedSmiles);
	},

	decodeSmile(text: string, options = {}): string // TODO add options types
	{
		if (!this.typings)
		{
			this.loadSmilePatterns();
		}
		if (!this.pattern)
		{
			return text;
		}

		let enableBigSmile;
		if (Type.isBoolean(options.enableBigSmile))
		{
			enableBigSmile = options.enableBigSmile;
		}
		else
		{
			enableBigSmile = getBigSmileOption();
		}

		const ratioConfig = Type.isObjectLike(options.ratioConfig) ? options.ratioConfig : RatioConfig;
		const ratio = enableBigSmile? getSmileRatio(text, this.pattern, ratioConfig): ratioConfig.Default;

		const pattern = `(?:(?:${this.pattern})(?=(?:(?:${this.pattern})|\\s|&quot;|<|$)))`;
		const regExp = new RegExp(pattern, 'g');
		const replacedText = text.replaceAll(regExp, (match, offset) => {
			const behindMatching = lookBehind.call(this, text, match, offset);
			if (!behindMatching)
			{
				return match;
			}

			const image = this.typings[match].cloneNode();
			const {width, height} = image.style;
			Dom.style(image, 'width', `${Number.parseInt(width, 10) * ratio}px`);
			Dom.style(image, 'height', `${Number.parseInt(height, 10) * ratio}px`);

			return image.outerHTML;
		});

		return replacedText;
	}
};