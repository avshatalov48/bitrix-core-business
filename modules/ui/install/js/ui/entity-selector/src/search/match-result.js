import MatchField from './match-field';

import type MatchIndex from './match-index';
import type SearchField from './search-field';
import type Item from '../item/item';

export default class MatchResult
{
	item: Item = null;
	queryWords: string[] = null;
	matchFields: Map<SearchField, MatchField> = new Map();
	sort: ?number = null;

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

	getQueryWords(): string[]
	{
		return this.queryWords;
	}

	getMatchFields(): Map<SearchField, MatchField>
	{
		return this.matchFields;
	}

	getSort(): ?number
	{
		return this.sort;
	}

	addIndex(matchIndex: MatchIndex): void
	{
		let matchField = this.matchFields.get(matchIndex.getField());
		if (!matchField)
		{
			matchField = new MatchField(matchIndex.getField());
			this.matchFields.set(matchIndex.getField(), matchField);

			const fieldSort = matchIndex.getField().getSort();
			if (fieldSort !== null)
			{
				this.sort = this.sort === null ? fieldSort : Math.min(this.sort, fieldSort);
			}
		}

		matchField.addIndex(matchIndex);
	}

	addIndexes(matchIndexes: MatchIndex[]): void
	{
		matchIndexes.forEach(matchIndex => {
			this.addIndex(matchIndex);
		});
	}
}