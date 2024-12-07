import { DefaultFooter } from 'ui.entity-selector';
import { Event, Loc, Tag } from 'main.core';
import 'ui.info-helper';

export class ProductSearchInputLimitedFooter extends DefaultFooter
{
	getContent(): HTMLElement
	{
		const phrase = Tag.render`
			<div>${Loc.getMessage('CATALOG_SELECTOR_LIMITED_PRODUCT_CREATION')}</div>
		`;

		const infoButton = Tag.render`
			<a class="ui-btn ui-btn-sm ui-btn-primary ui-btn-hover ui-btn-round">
				${Loc.getMessage('CATALOG_SELECTOR_LICENSE_EXPLODE')}
			</a>
		`;

		Event.bind(infoButton, 'click', () => {
			BX.UI.InfoHelper.show('limit_shop_products');
		});

		return Tag.render`
			<div class="ui-selector-search-footer-box">
				<div class="ui-selector-search-footer-box">
					<div class="tariff-lock"></div>
					${phrase}
				</div>
				<div>
					${infoButton}
				</div>
			</div>
		`;
	}
}
