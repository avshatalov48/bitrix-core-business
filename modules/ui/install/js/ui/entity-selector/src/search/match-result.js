import MatchField from './match-field';

import type MatchIndex from './match-index';
import type SearchField from './search-field';
import type Item from '../item/item';

export default class MatchResult
{
	item: Item = null;
	queryWords: string[] = null;
	matchFields: Map<SearchField, MatchField> = new Map();

	constructor(item: Item, queryWords: string[], matchIndexes: MatchIndex[] = [])
	{
		this.item = item;
		this.queryWords = queryWords;
		this.addIndexes(matchIndexes);
	}

	getItem(): Item
	{
		return this.item;
	}

	getQueryWords()
	{
		return this.queryWords;
	}

	getMatchFields(): Map<SearchField, MatchField>
	{
		return this.matchFields;
	}

	addIndex(matchIndex: MatchIndex)
	{
		let matchField = this.matchFields.get(matchIndex.getField());
		if (!matchField)
		{
			matchField = new MatchField(matchIndex.getField());
			this.matchFields.set(matchIndex.getField(), matchField);
		}

		matchField.addIndex(matchIndex);
	}

	addIndexes(matchIndexes: MatchIndex[])
	{
		matchIndexes.forEach(matchIndex => {
			this.addIndex(matchIndex);
		});
	}
}