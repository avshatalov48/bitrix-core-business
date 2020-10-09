import SearchField from './search-field';

export default class MatchIndex
{
	field: SearchField = null;
	queryWord: string = null;
	startIndex: number = null;
	endIndex: number = null;

	constructor(field: SearchField, queryWord: string, startIndex: number)
	{
		this.field = field;
		this.queryWord = queryWord;
		this.startIndex = startIndex;
		this.endIndex = startIndex + queryWord.length;
	}

	getField(): SearchField
	{
		return this.field;
	}

	getQueryWord(): string
	{
		return this.queryWord;
	}

	getStartIndex(): number
	{
		return this.startIndex;
	}

	getEndIndex(): number
	{
		return this.endIndex;
	}
}