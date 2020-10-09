import type SearchField from './search-field';
import type WordIndex from './word-index';

export default class SearchFieldIndex
{
	field: SearchField = null;
	indexes: WordIndex[] = [];

	constructor(field: SearchField, indexes: WordIndex[] = [])
	{
		this.field = field;
		this.addIndexes(indexes);
	}

	getField(): SearchField
	{
		return this.field;
	}

	getIndexes(): WordIndex[]
	{
		return this.indexes;
	}

	addIndex(index: WordIndex)
	{
		this.getIndexes().push(index);
	}

	addIndexes(indexes: WordIndex[])
	{
		indexes.forEach(index => {
			this.addIndex(index);
		});
	}
}