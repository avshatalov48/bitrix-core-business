import {Type, Loc, Text, Dom} from 'main.core';

export const TextUtil = {

	convertHtmlEntities(text: string): string
	{
		return Dom.create({
			tag: 'span',
			html: text
		}).innerText;
	},

	convertSnakeToCamelCase(text: string): string
	{
		return text.replace(/(_[a-z])/gi, ($1) => {
			return $1.toUpperCase().replace('_', '');
		});
	},

	escapeRegex(string): string
	{
		return string.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
	},

	getLocalizeForNumber(phrase, number, language = 'en'): string
	{
		let pluralFormType = 1;

		number = parseInt(number);

		if (number < 0)
		{
			number = number * -1;
		}

		if (language)
		{
			switch (language)
			{
				case 'de':
				case 'en':
					pluralFormType = ((number !== 1) ? 1 : 0);
					break;

				case 'ru':
				case 'ua':
					pluralFormType = (((number%10 === 1) && (number%100 !== 11)) ? 0 : (((number%10 >= 2) && (number%10 <= 4) && ((number%100 < 10) || (number%100 >= 20))) ? 1 : 2));
					break;
			}
		}

		return Loc.getMessage(phrase + '_PLURAL_' + pluralFormType);
	},

	getFirstLetters(text): string
	{
		const validSymbolsPattern = /[\p{L}\p{N} ]/u;

		const words = text.split(/[\s,]/).filter(word => {
			const firstLetter = word.charAt(0);
			return validSymbolsPattern.test(firstLetter);
		});

		if (words.length === 0)
		{
			return '';
		}

		if (words.length > 1)
		{
			return words[0].charAt(0) + words[1].charAt(0);
		}

		return words[0].charAt(0);
	},

	insertUnseenWhitespace(text: string, splitIndex: number): string
	{
		if (text.length <= splitIndex)
		{
			return text;
		}

		const UNSEEN_SPACE = '\u200B';

		let firstPart = text.slice(0, splitIndex + 1);
		const secondPart = text.slice(splitIndex + 1);
		const hasWhitespace = /\s/.test(firstPart);
		const hasUserCode = /\[user=(\d+)(\s)?(replace)?](.*?)\[\/user]/gi.test(text);

		if (firstPart.length === splitIndex + 1 && !hasWhitespace && !hasUserCode)
		{
			firstPart += UNSEEN_SPACE;
		}

		return firstPart + secondPart;
	},

	getUuidV4(): string
	{
		return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
			var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);

			return v.toString(16);
		});
	},

	isUuidV4(uuid): boolean
	{
		if (!Type.isString(uuid))
		{
			return false;
		}

		const uuidV4pattern = new RegExp(/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i);

		return uuid.search(uuidV4pattern) === 0;
	},

	isTempMessage(messageId): boolean
	{
		return this.isUuidV4(messageId) || messageId.toString().startsWith('temp');
	},

	checkUrl(url): boolean
	{
		const allowList = [
			"http:",
			"https:",
			"ftp:",
			"file:",
			"tel:",
			"callto:",
			"mailto:",
			"skype:",
			"viber:",
		];

		const checkCorrectStartLink = ['/', ...allowList].find(protocol => {
			return url.startsWith(protocol);
		});
		if (!checkCorrectStartLink)
		{
			return false;
		}

		const element = Dom.create({ tag: 'a', attrs: { href: url }});

		return allowList.indexOf(element.protocol) > -1;
	},

	/**
	 * @deprecated
	 * @use Text.encode from main.core
	 */
	htmlspecialchars(text): string
	{
		return Text.encode(text);
	},

	/**
	 * @deprecated
	 * @use Text.decode from main.core
	 */
	htmlspecialcharsback(text): string
	{
		return Text.decode(text);
	},
};