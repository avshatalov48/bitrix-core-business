import { Dom, Tag, Loc, Event } from 'main.core';
import { BaseFooter } from 'ui.entity-selector';

export class ProductSearchInputPlacementFooter extends BaseFooter
{
	render(): HTMLElement
	{
		const container = Tag.render`<div>${this.getContent()}</div>`;

		Dom.addClass(container, this.getContainerClassName());

		return container;
	}

	getHelpLink(): HTMLElement
	{
		const helpLink = Tag.render`
			<div class="product-selector-placement__help-link">
				${Loc.getMessage('CATALOG_SELECTOR_1C_HELP_LINK')}
			</div>
		`;

		Event.bind(helpLink, 'click', () => {
			if (top.BX && top.BX.Helper)
			{
				top.BX.Helper.show('redirect=detail&code=20233654');
			}
		});

		return helpLink;
	}

	/**
	 * @abstract
	 */
	getContent(): HTMLElement
	{
		throw new Error('Method "getContent" should be overridden');
	}

	/**
	 * @abstract
	 */
	getContainerClassName(): string
	{
		throw new Error('Method "getContainerClassName" should be overridden');
	}
}
