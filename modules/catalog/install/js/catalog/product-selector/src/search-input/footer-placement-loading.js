import { Tag, Loc } from 'main.core';
import { ProductSearchInputPlacementFooter } from './footer-placement';
import 'ui.icon-set.main';

export class ProductSearchInputPlacementFooterLoading extends ProductSearchInputPlacementFooter
{
	getContent(): HTMLElement
	{
		return Tag.render`
			<div class="product-selector-placement__container">
				<div class="product-selector-placement__loader-icon">
					<div class="ui-icon-set --1c"></div>
					<div class="product-selector-placement__loader">
						<div class="product-selector-placement__loader-subtract"></div>
					</div>
				</div>
				<div class="product-selector-placement__status">
					${Loc.getMessage('CATALOG_SELECTOR_1C_CONNECTING')}
				</div>
				${this.getHelpLink()}
			</div>
		`;
	}

	getContainerClassName(): string
	{
		return 'product-selector-placement__footer-loading';
	}
}
