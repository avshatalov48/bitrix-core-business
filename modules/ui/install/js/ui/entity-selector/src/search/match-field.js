import { Type } from 'main.core';
import ItemCollection from '../item/item-collection';

import type SearchField from './search-field';
import type MatchIndex from './match-index';

const comparator = (a: MatchIndex, b: MatchIndex) => {

	if (a.getStartIndex() === b.getStartIndex())
	{
		return a.getEndIndex() > b.getEndIndex() ? 1 : -1;
	}
	else
	{
		return a.getStartIndex() > b.getStartIndex() ? -1 : 1;
	}
};

export default class MatchField
{
	field: SearchField = null;
	matchIndexes: ItemCollection<MatchIndex> = new ItemCollection(comparator);

	constructor(field: SearchField, indexes: MatchIndex[] = [])
	{
		this.field = field;
		this.addIndexes(indexes);
	}

	getField()
	{
		return this.field;
	}

	getMatches()
	{
		return this.matchIndexes;
	}

	addIndex(matchIndex: MatchIndex)
	{
		this.matchIndexes.add(matchIndex);
	}

	addIndexes(matchIndexes: MatchIndex[])
	{
		if (Type.isArray(matchIndexes))
		{
			matchIndexes.forEach(matchIndex => {
				this.addIndex(matchIndex);
			});
		}
	}
}