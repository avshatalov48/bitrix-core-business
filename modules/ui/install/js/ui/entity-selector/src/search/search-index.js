import { Type } from 'main.core';

import SearchFieldIndex from './search-field-index';
import WordIndex from './word-index';

import type Item from '../item/item';
import type SearchField from './search-field';

import unicodeWordsRegExp from './unicode-words';
const asciiWordRegExp = /[^\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f]+/g;
const hasUnicodeWordRegExp = /[a-z][A-Z]|[A-Z]{2}[a-z]|[0-9][a-zA-Z]|[a-zA-Z][0-9]|[^a-zA-Z0-9 ]/;

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
			if (!field.isSeachable())
			{
				return;
			}

			if (field.isSystem())
			{
				if (field.getName() === 'title')
				{
					index.addIndex(this.createIndex(field, item.getTitle()));
				}
				else if (field.getName() === 'subtitle')
				{
					index.addIndex(this.createIndex(field, item.getSubtitle()));
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

	static createIndex(field: SearchField, text: string): SearchFieldIndex
	{
		if (!Type.isStringFilled(text))
		{
			return null;
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
}