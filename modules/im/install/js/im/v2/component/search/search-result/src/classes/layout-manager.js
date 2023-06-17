import {Core} from 'im.v2.application.core';

export class LayoutManager
{
	#store: Object;

	constructor()
	{
		this.#store = Core.getStore();
	}

	changeLayout(query: string): string
	{
		if (this.#isRussianInterface() && BX.correctText)
		{
			// eslint-disable-next-line bitrix-rules/no-bx
			return BX.correctText(query, {replace_way: 'AUTO'});
		}

		return query;
	}

	needLayoutChange(originalLayoutQuery: string): boolean
	{
		const wrongLayoutQuery = this.changeLayout(originalLayoutQuery);
		const isIdenticalQuery = wrongLayoutQuery === originalLayoutQuery;

		return this.#isRussianInterface() && !isIdenticalQuery;
	}

	#isRussianInterface(): boolean
	{
		return Core.getLanguageId() === 'ru';
	}
}