import { Type } from 'main.core';

import SearchFieldIndex from './search-field-index';
import WordIndex from './word-index';

import type Item from '../item/item';
import type SearchField from './search-field';

import unicodeWordsRegExp from './unicode-words';
const asciiWordRegExp = /[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/g;
const hasUnicodeWordRegExp = /[a-z][A-Z]|[A-Z]{2}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/;
const nonWhitespaceRegExp = /[^\s]+/g;
const specialChars = `!"#$%&'()*+,-.\/:;<=>?@[\\]^_\`{|}`;
const specialCharsRegExp = new RegExp(`[${specialChars}]`);

export default class SearchIndex
{
	indexes: SearchFieldIndex[] = [];

	constructor()
	{
	}

	addIndex(fieldIndex: SearchFieldIndex)
	{
		if (Type.isObject(fieldIndex))
		{
			this.getIndexes().push(fieldIndex);
		}
	}

	getIndexes()
	{
		return this.indexes;
	}

	static create(item: Item)
	{
		const index = new SearchIndex();
		const entity = item.getEntity();

		if (!item.isSearchable() || !entity.isSearchable() || item.isHidden())
		{
			return index;
		}

		const searchFields = entity.getSearchFields();
		searchFields.forEach(field => {
			if (!field.isSearchable())
			{
				return;
			}

			if (field.isSystem())
			{
				if (field.getName() === 'title')
				{
					const textNode = item.getTitleNode();
					const stripTags = textNode !== null && textNode.getType() === 'html';
					index.addIndex(this.createIndex(field, item.getTitle(), stripTags));
				}
				else if (field.getName() === 'subtitle')
				{
					const textNode = item.getSubtitleNode();
					const stripTags = textNode !== null && textNode.getType() === 'html';
					index.addIndex(this.createIndex(field, item.getSubtitle(), stripTags));
				}
				else if (field.getName() === 'supertitle')
				{
					const textNode = item.getSupertitleNode();
					const stripTags = textNode !== null && textNode.getType() === 'html';
					index.addIndex(this.createIndex(field, item.getSupertitle(), stripTags));
				}
				else if (field.getName() === 'caption')
				{
					const textNode = item.getCaptionNode();
					const stripTags = textNode !== null && textNode.getType() === 'html';
					index.addIndex(this.createIndex(field, item.getCaption(), stripTags));
				}
			}
			else
			{
				const customData = item.getCustomData().get(field.getName());
				if (!Type.isUndefined(customData))
				{
					index.addIndex(this.createIndex(field, customData));
				}
			}
		});

		return index;
	}

	static createIndex(field: SearchField, text: string, stripTags = false): SearchFieldIndex
	{
		if (!Type.isStringFilled(text))
		{
			return null;
		}

		if (stripTags)
		{
			text = text.replace(/<\/?[^>]+>/g, (match) => ' '.repeat(match.length));
			text = text.replace(/&(?:#\d+|#x[\da-fA-F]+|[0-9a-zA-Z]+);/g, (match) => ' '.repeat(match.length));
		}

		let index: SearchFieldIndex = null;
		if (field.getType() === 'string')
		{
			const wordIndexes = this.splitText(text);
			if (Type.isArrayFilled(wordIndexes))
			{
				// "GoPro111 Leto15"
				// [go, pro, 111, leto, 15] + [gopro111, leto15]
				this.fillComplexWords(wordIndexes);
				this.fillNonCharWords(wordIndexes, text);

				index = new SearchFieldIndex(field, wordIndexes);
			}
		}
		else if (field.getType() === 'email')
		{
			const position = text.indexOf('@');
			if (position !== -1)
			{
				index = new SearchFieldIndex(
					field,
					[
						new WordIndex(text.toLowerCase(), 0),
						new WordIndex(text.substr(position + 1).toLowerCase(), position + 1)
					]
				);
			}
		}

		return index;
	}

	static splitText(text: string): WordIndex[]
	{
		if (!Type.isStringFilled(text))
		{
			return [];
		}

		return this.hasUnicodeWord(text) ? this.splitUnicodeText(text) : this.splitAsciiText(text);
	}

	static splitUnicodeText(text: string): WordIndex[]
	{
		return this.splitTextInternal(text, unicodeWordsRegExp);
	}

	static splitAsciiText(text: string): WordIndex[]
	{
		return this.splitTextInternal(text, asciiWordRegExp);
	}

	static hasUnicodeWord(text: string): boolean
	{
		return hasUnicodeWordRegExp.test(text);
	}

	static splitTextInternal(text: string, regExp: RegExp): WordIndex[]
	{
		let match;
		const result = [];

		regExp.lastIndex = 0;
		while ((match = regExp.exec(text)) !== null)
		{
			if (match.index === regExp.lastIndex)
			{
				regExp.lastIndex++;
			}

			result.push(new WordIndex(match[0].toLowerCase(), match.index));
		}

		return result;
	}

	/**
	 *  @private
	 */
	static fillComplexWords(indexes: WordIndex[]): void
	{
		if (indexes.length < 2)
		{
			return;
		}

		let complexWord: ?string = null;
		let startIndex: ?number = null;

		indexes.forEach((currentIndex, currentArrayIndex) => {
			const nextIndex = indexes[currentArrayIndex + 1];
			if (nextIndex)
			{
				const sameWord =
					currentIndex.getStartIndex() + currentIndex.getWord().length === nextIndex.getStartIndex()
				;

				if (sameWord)
				{
					if (complexWord === null)
					{
						complexWord = currentIndex.getWord();
						startIndex = currentIndex.getStartIndex();
					}

					complexWord += nextIndex.getWord();
				}
				else if (complexWord !== null)
				{
					indexes.push(new WordIndex(complexWord, startIndex));
					complexWord = null;
					startIndex = null;
				}
			}
			else if (complexWord !== null)
			{
				indexes.push(new WordIndex(complexWord, startIndex));
				complexWord = null;
				startIndex = null;
			}
		});
	}

	/**
	 *  @private
	 */
	static fillNonCharWords(indexes: WordIndex[], text: string): void
	{
		if (!specialCharsRegExp.test(text))
		{
			return;
		}

		let match;
		while ((match = nonWhitespaceRegExp.exec(text)) !== null)
		{
			if (match.index === nonWhitespaceRegExp.lastIndex)
			{
				nonWhitespaceRegExp.lastIndex++;
			}

			const word = match[0];
			if (specialCharsRegExp.test(word))
			{
				indexes.push(new WordIndex(word.toLowerCase(), match.index));

				for (let i = 0; i < word.length; i++)
				{
					const char = word[i];
					if (!specialChars.includes(char))
					{
						break;
					}

					const wordToIndex = word.substr(i + 1);
					if (wordToIndex.length)
					{
						indexes.push(new WordIndex(wordToIndex.toLowerCase(), match.index + i + 1));
					}
				}
			}
		}

		nonWhitespaceRegExp.lastIndex = 0;
	}
}
