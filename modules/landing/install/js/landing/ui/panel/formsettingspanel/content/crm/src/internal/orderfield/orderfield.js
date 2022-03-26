import {Dom, Tag} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {Button} from 'ui.buttons';
import {Loc} from 'landing.loc';

import './css/style.css';

type OrderFieldOptions = {
	title: string,
	selector: string,
};

export default class OrderField extends BaseField
{
	constructor(options: OrderFieldOptions)
	{
		super(options);

		Dom.replace(this.input, this.getWrapper());
	}

	getDropdown(): BX.Landing.UI.Field.Dropdown
	{
		return this.cache.remember('dropdown', () => {
			return new BX.Landing.UI.Field.Dropdown({
				items: this.options.items,
			});
		});
	}

	getButton(): Button
	{
		return this.cache.remember('button', () => {
			return new Button({
				text: Loc.getMessage('LANDING_FORM_SETTINGS_ORDER_SETTINGS_BUTTON_LABEL'),
				color: Button.Color.LIGHT_BORDER,
				size: Button.Size.MEDIUM,
				events: {
					click: this.onButtonClick.bind(this),
				},
			});
		});
	}

	onButtonClick()
	{
		const companyId = this.getDropdown().getValue();
		const url = `/crm/company/details/${companyId}/?init_mode=edit`;
		window.open(url, '_blank');
	}

	getWrapper(): HTMLDivElement
	{
		return this.cache.remember('wrapper', () => {
			return Tag.render`
				<div class="landing-ui-field-order-wrapper">
					${this.getDropdown().getLayout()}
					${this.getButton().render()}
				</div>
			`;
		});
	}
}