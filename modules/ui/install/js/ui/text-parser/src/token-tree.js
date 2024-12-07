import { Type } from 'main.core';

export type TreeIndex = Map<number, [boolean, TreeIndex]>;

export class TokenTree
{
	#index: TreeIndex = new Map();

	getTreeIndex(): TreeIndex
	{
		return this.#index;
	}

	addToken(token: string): void
	{
		if (!Type.isStringFilled(token))
		{
			return;
		}

		let index = this.#index;
		for (let i = 0; i < token.length; i++)
		{
			const codePoint: number = token.codePointAt(i);
			if (i === token.length - 1)
			{
				if (index.has(codePoint))
				{
					index.get(codePoint)[0] = true;
				}
				else
				{
					index.set(codePoint, [true, new Map()]);
				}
			}
			else
			{
				if (!index.has(codePoint))
				{
					index.set(codePoint, [false, new Map()]);
				}

				[, index] = index.get(codePoint);
			}
		}
	}
}
