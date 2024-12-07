import { Tag } from 'main.core';
import { ProductSearchInputPlacementFooter } from './footer-placement';
import 'ui.icon-set.main';

export class ProductSearchInputPlacementFooterFailure extends ProductSearchInputPlacementFooter
{
	getContent(): HTMLElement
	{
		return Tag.render`
			<div class="product-selector-placement__container --default">
				<div class="product-selector-placement__icon-1C">
					<div class="ui-icon-set --1c"></div>
				</div>
				<div class="ui-icon-set --warning product-selector-placement__icon-error"></div>
				<div class="product-selector-placement__status">
					${this.getOption('text') || ''}
				</div>
				${this.getHelpLink()}
			</div>
		`;
	}

	getContainerClassName(): string
	{
		return 'product-selector-placement__footer-failure';
	}
}
