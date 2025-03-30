import { Tag, Type, Loc } from 'main.core';
import { MarketItem } from './market-item';

export type MarketListOptions = {
	items: Array<MarketItem>,
	title: ?string,
	link: ?string,
	count: number,
	onClick: func,
};

export class MarketList
{
	#items: Array<MarketItem>;
	#title: string;
	#link: string;
	#count: number;
	#onClick: func;

	constructor(options: MarketListOptions)
	{
		this.#items = Type.isArray(options.items) ? options.items : [];
		this.#title = options.title;
		this.#link = options.link;
		this.#count = options.count;
		this.#onClick = options.onClick;
	}

	render(): HTMLElement
	{
		return Tag.render`
			<div class="rest-market-list">
				<div class="rest-market-list__header">
					<span class="rest-market-list__title">${this.#title}</span>
					<a class="rest-market-list__link" href="${this.#link}" onclick="${this.#onClick}">
						${Loc.getMessage('REST_MARKET_EXPIRED_POPUP_MARKET_LIST_LINK', {
							'#COUNT#': this.#count,
						})}
					</a>
				</div>
				${this.#renderList()}
			</div>
		`;
	}

	#renderList(): ?HTMLElement
	{
		if (this.#items.length === 0)
		{
			return null;
		}

		const listContainer = Tag.render`
			<div class="rest-market-list__container"></div>
		`;

		this.#items.forEach((item: MarketItem) => {
			item.renderTo(listContainer);
		});

		return listContainer;
	}
}
