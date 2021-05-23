import MatchResult from './match-result';
import MatchIndex from './match-index';

import type SearchFieldIndex from './search-field-index';
import type Item from '../item/item';
import type SearchQuery from './search-query';

const collator = new Intl.Collator(undefined, { sensitivity: 'base' });

export default class SearchEngine
{
	static matchItems(items: Item[], searchQuery: SearchQuery): MatchResult[]
	{
		const matchResults = [];
		const queryWords = searchQuery.getQueryWords();
		let limit = searchQuery.getResultLimit();

		for (let i = 0; i < items.length; i++)
		{
			if (limit === 0)
			{
				break;
			}

			const item = items[i];
			if (item.isSelected() || !item.isSearchable() || item.isHidden() || !item.getEntity().isSearchable())
			{
				continue;
			}

			const matchResult = this.matchItem(item, queryWords);
			if (matchResult)
			{
				matchResults.push(matchResult);
				limit--;
			}
		}

		return matchResults;
	}

	static matchItem(item: Item, queryWords: string[]): MatchResult
	{
		let matches = [];
		for (let i = 0; i < queryWords.length; i++)
		{
			const queryWord = queryWords[i];
			const results = this.matchWord(item, queryWord);
			//const match = this.matchWord(item, queryWord);
			//if (match === null)
			if (results.length === 0)
			{
				return null;
			}
			else
			{
				matches = matches.concat(results);
				//matches.push(match);
			}
		}

		if (matches.length > 0)
		{
			return new MatchResult(item, queryWords, matches);
		}
		else
		{
			return null;
		}
	}

	static matchWord(item: Item, queryWord: string): MatchIndex[]
	{
		const searchIndexes = item.getSearchIndex().getIndexes();
		const matches = [];

		for (let i = 0; i < searchIndexes.length; i++)
		{
			const fieldIndex: SearchFieldIndex = searchIndexes[i];
			const indexes = fieldIndex.getIndexes();
			for (let j = 0; j < indexes.length; j++)
			{
				const index = indexes[j];
				const word = index.getWord().substring(0, queryWord.length);
				if (collator.compare(queryWord, word) === 0)
				{
					matches.push(new MatchIndex(fieldIndex.getField(), queryWord, index.getStartIndex()));
					//return new MatchIndex(field, queryWord, index[i][1]);
				}
			}

			if (matches.length > 0)
			{
				break;
			}

		}

		return matches;
		//return null;
	}
}