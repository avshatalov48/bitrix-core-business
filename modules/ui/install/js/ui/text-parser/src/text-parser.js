import { Type } from 'main.core';
import { CodePoint } from './code-point';
import type { TreeIndex } from './token-tree';

export class TextParser
{
	#currentPosition: number;
	#text: string;
	#textStart: number = -1;
	#textEnd: number = -1;

	constructor(text: string, position = 0)
	{
		this.#text = text;
		this.#currentPosition = position;
	}

	getCurrentPosition(): number
	{
		return this.#currentPosition;
	}

	tryChangePosition(fn: Function): boolean
	{
		const currentPosition = this.#currentPosition;
		const success = fn();
		if (!success)
		{
			this.#currentPosition = currentPosition;
		}

		return success;
	}

	peek(): number | undefined
	{
		return this.#text.codePointAt(this.#currentPosition);
	}

	moveNext(): number
	{
		return this.hasNext() ? this.#moveNext(this.peek()) : NaN;
	}

	#moveNext(code: number): number
	{
		this.#currentPosition += code > 0xFFFF ? 2 : 1;

		return code;
	}

	peekPrevious(): number
	{
		return this.#text.codePointAt(this.#currentPosition - 1);
	}

	hasNext(): boolean
	{
		return this.#currentPosition < this.#text.length;
	}

	hasPendingText(): boolean
	{
		return this.#textStart !== this.#textEnd;
	}

	flushText(): void
	{
		if (this.hasPendingText())
		{
			this.#textStart = -1;
			this.#textEnd = -1;
		}
	}

	consume(match: number | Function): boolean
	{
		const codePoint = this.peek();
		const success = Type.isFunction(match) ? match(codePoint) : codePoint === match;
		if (success)
		{
			this.moveNext(codePoint);
		}

		return success;
	}

	consumeWhile(match: number | Function): boolean
	{
		const start = this.#currentPosition;
		while (this.hasNext() && this.consume(match))
		{
			/* */
		}

		return this.#currentPosition !== start;
	}

	consumePoints(codePoints: number[]): boolean
	{
		const currentPosition = this.#currentPosition;
		for (const codePoint of codePoints)
		{
			const currentCodePoint = this.moveNext();
			if (codePoint !== currentCodePoint)
			{
				this.#currentPosition = currentPosition;

				return false;
			}
		}

		return true;
	}

	consumeTree(treeIndex: TreeIndex): boolean
	{
		const currentPosition = this.#currentPosition;
		let node = treeIndex;
		while (this.hasNext())
		{
			const codePoint = this.moveNext();
			const index = node.get(codePoint);
			if (Type.isUndefined(index))
			{
				break;
			}

			const [isLeaf, entry] = index;
			if (isLeaf === true)
			{
				this.consumeTree(entry);

				return true;
			}

			node = entry;
		}

		this.#currentPosition = currentPosition;

		return false;
	}

	consumeText(): true
	{
		if (this.#textStart === -1)
		{
			this.#textStart = this.#currentPosition;
			this.#textEnd = this.#currentPosition;
		}

		this.moveNext();

		this.#textEnd = this.#currentPosition;

		return true;
	}

	isWordBoundary(): boolean
	{
		if (this.#currentPosition === 0)
		{
			return true;
		}

		if (this.hasPendingText())
		{
			return isDelimiter(this.peekPrevious());
		}

		return false;
	}
}

// [.,;:!?#-*|[](){}]
const wordBoundaries = new Set([
	CodePoint.DOT,
	CodePoint.COMMA,
	CodePoint.SEMI_COLON,
	CodePoint.COLON,
	CodePoint.EXCLAMATION,
	CodePoint.QUESTION,
	CodePoint.HASH,
	CodePoint.HYPHEN,
	CodePoint.ASTERISK,
	CodePoint.PIPE,
	CodePoint.ROUND_BRACKET_OPEN,
	CodePoint.ROUND_BRACKET_CLOSE,
	CodePoint.SQUARE_BRACKET_OPEN,
	CodePoint.SQUARE_BRACKET_CLOSE,
	CodePoint.CURLY_BRACKET_OPEN,
	CodePoint.CURLY_BRACKET_CLOSE,
]);

export function isWordBoundary(ch: number): boolean
{
	return wordBoundaries.has(ch);
}

export function isTextBound(codePoint: number): boolean
{
	return Type.isUndefined(codePoint) || Number.isNaN(codePoint) || isNewLine(codePoint) || isWhitespace(codePoint);
}

export function isDelimiter(codePoint: number): boolean
{
	return isTextBound(codePoint) || isWordBoundary(codePoint);
}

export function isWhitespace(codePoint: number): boolean
{
	return codePoint === CodePoint.SPACE || codePoint === CodePoint.TAB || codePoint === CodePoint.NBSP;
}

export function isNewLine(codePoint: number): boolean
{
	return codePoint === CodePoint.NEW_LINE || codePoint === CodePoint.RETURN || codePoint === CodePoint.LINE_FEED;
}
