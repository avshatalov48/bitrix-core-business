import { Tag, Event } from 'main.core';
import { ProductSearchInputPlacementFooter } from './footer-placement';
import { OneCPlanRestrictionSlider } from 'catalog.tool-availability-manager';
import 'ui.icon-set.main';

export class ProductSearchInputPlacementFooterLock extends ProductSearchInputPlacementFooter
{
	getContent(): HTMLElement
	{
		const statusNode = Tag.render`
			<div class="product-selector-placement__status">
				${this.getOption('text') || ''}
			</div>
		`;

		Event.bind(statusNode, 'click', () => {
			OneCPlanRestrictionSlider.show();
		});

		return Tag.render`
			<div class="product-selector-placement__container --lock">
				<div class="product-selector-placement__icon-1C">
					<div class="ui-icon-set --1c"></div>
				</div>
				${statusNode}
				${this.getHelpLink()}
			</div>
		`;
	}

	getContainerClassName(): string
	{
		return 'product-selector-placement__footer-failure';
	}
}
