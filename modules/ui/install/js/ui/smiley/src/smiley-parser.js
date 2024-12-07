import { TextParser, isDelimiter, TokenTree } from 'ui.text-parser';
import { type Smiley } from './smiley';

export class SmileyParser
{
	#splitOffsets: Array<{ start: number, end: number }> = [];
	#tokenTree: TokenTree = null;
	#textParser: TextParser = null;

	constructor(smileys: Smiley[])
	{
		this.#tokenTree = new TokenTree();
		smileys.forEach((smiley: Smiley) => {
			this.#tokenTree.addToken(smiley.getTyping());
		});
	}

	parse(text: string): Array<{ start: number, end: number }>
	{
		this.#splitOffsets = [];
		this.#textParser = new TextParser(text);

		while (this.#textParser.hasNext())
		{
			let success = false;
			success = success || this.#parseEmoji();
			success = success || this.#parseSmileys();
			success = success || this.#textParser.consumeText();
		}

		return this.#splitOffsets;
	}

	#parseSmileys(): boolean
	{
		if (this.#isWordBoundary())
		{
			return this.#textParser.tryChangePosition(() => {
				const currentPosition = this.#textParser.getCurrentPosition();
				if (this.#consumeSmiley() && this.#isNextWordBoundary())
				{
					this.#splitOffsets.push({
						start: currentPosition,
						end: this.#textParser.getCurrentPosition(),
					});

					this.#textParser.flushText();

					return true;
				}

				return false;
			});
		}

		return false;
	}

	#consumeSmiley(): boolean
	{
		return this.#textParser.consumeTree(this.#tokenTree.getTreeIndex());
	}

	#isWordBoundary(): boolean
	{
		if (!this.#textParser.hasPendingText())
		{
			const last = this.#splitOffsets.at(-1);
			if (last && last.end === this.#textParser.getCurrentPosition())
			{
				return true;
			}
		}

		return this.#textParser.isWordBoundary();
	}

	#isNextWordBoundary(): boolean
	{
		let isSmileyNext = false;
		this.#textParser.tryChangePosition(() => {
			if (this.#consumeSmiley())
			{
				isSmileyNext = true;
			}

			return false;
		});

		if (isSmileyNext)
		{
			return true;
		}

		return isDelimiter(this.#textParser.peek());
	}

	#parseEmoji(): boolean
	{
		return false;
	}
}
