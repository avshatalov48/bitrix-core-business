import { Type } from 'main.core';

export default class WordIndex
{
	word: string = '';
	startIndex: number = 0;

	constructor(word: string, startIndex: number)
	{
		this.setWord(word);
		this.setStartIndex(startIndex);
	}

	getWord(): string
	{
		return this.word;
	}

	setWord(word: string): this
	{
		if (Type.isStringFilled(word))
		{
			this.word = word;
		}

		return this;
	}

	getStartIndex(): number
	{
		return this.startIndex;
	}

	setStartIndex(index: number): this
	{
		if (Type.isNumber(index) && index >= 0)
		{
			this.startIndex = index;
		}

		return this;
	}
}