import { Type } from 'main.core';

import type SearchField from './search-field';
import MatchIndex from './match-index';
import { OrderedArray } from 'main.core.collections';

const comparator = (a: MatchIndex, b: MatchIndex) => {

	if (a.getStartIndex() === b.getStartIndex())
	{
		return a.getEndIndex() > b.getEndIndex() ? -1 : 1;
	}
	else
	{
		return a.getStartIndex() > b.getStartIndex() ? 1 : -1;
	}
};

export default class MatchField
{
	field: SearchField = null;
	matchIndexes: OrderedArray<MatchIndex> = new OrderedArray(comparator);

	constructor(field: SearchField, indexes: MatchIndex[] = [])
	{
		this.field = field;
		this.addIndexes(indexes);
	}

	getField(): SearchField
	{
		return this.field;
	}

	getMatches(): OrderedArray<MatchIndex>
	{
		return this.matchIndexes;
	}

	addIndex(matchIndex: MatchIndex): void
	{
		this.matchIndexes.add(matchIndex);
	}

	addIndexes(matchIndexes: MatchIndex[]): void
	{
		if (Type.isArray(matchIndexes))
		{
			matchIndexes.forEach(matchIndex => {
				this.addIndex(matchIndex);
			});
		}
	}
}