import { Tag, Loc } from 'main.core';
import { ProductSearchInputPlacementFooter } from './footer-placement';
import 'ui.icon-set.main';

// currently not in use
export class ProductSearchInputPlacementFooterRetry extends ProductSearchInputPlacementFooter
{
	getContent(): HTMLElement
	{
		return Tag.render`
			<div class="product-selector-placement__container --retry">
				<div class="product-selector-placement__icon-1C">
					<div class="ui-icon-set --1c"></div>
				</div>
				<div class="product-selector-placement__status">
					${this.getOption('text') || ''}
				</div>
				<div class="product-selector-placement__retry">
					<div class="ui-icon-set --refresh-5 product-selector-placement__icon-retry"></div>
					<div class="product-selector-placement__retry-text">
						${Loc.getMessage('CATALOG_SELECTOR_1C_RETRY_TEXT')}
					</div>
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
