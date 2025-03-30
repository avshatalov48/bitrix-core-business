import { Tag, Dom } from 'main.core';

export type MarketItemOptions = {
	name: string,
	uri: string,
	icon: ?string,
}

export class MarketItem
{
	#name: string;
	#icon: string;

	constructor(options: MarketItemOptions)
	{
		this.#name = options.name;
		this.#icon = options.icon;
	}

	getContainer(): HTMLElement
	{
		return Tag.render`
			<span class="rest-market-item">
				${this.renderIcon()}
				<span class="rest-market-item__name" title="${this.getName()}">${this.getName()}</span>
			</span>
		`;
	}

	renderTo(node: HTMLElement): void
	{
		Dom.append(this.getContainer(), node);
	}

	getName(): string
	{
		return this.#name;
	}

	renderIcon(): HTMLElement
	{
		if (!this.#icon)
		{
			return Tag.render`
				<div class="rest-market-item__icon-container">
					<div class="ui-icon-set --cube-plus rest-market-item__icon"></div>
				</div>
			`;
		}

		return Tag.render`
			<div class="rest-market-item__icon-container" 
				style="
					background-image: url(${this.#icon});
					background-size: cover;
				">
			</div>
		`;
	}
}
