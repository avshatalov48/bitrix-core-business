import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';
import {Dom, Tag} from 'main.core';

import './css/style.css';

export class PositionField extends BaseField
{
	constructor(options = {})
	{
		super(options);

		this.horizontal = new BX.Landing.UI.Field.Dropdown({
			selector: 'horizontal',
			content: options.value.horizontal,
			items: [
				{name: Loc.getMessage('LANDING_FORM_EMBED_POSITION_LEFT'), value: 'left'},
				{name: Loc.getMessage('LANDING_FORM_EMBED_POSITION_CENTER'), value: 'center'},
				{name: Loc.getMessage('LANDING_FORM_EMBED_POSITION_RIGHT'), value: 'right'},
			],
		});

		this.vertical = new BX.Landing.UI.Field.Dropdown({
			selector: 'vertical',
			content: options.value.vertical,
			items: [
				{name: Loc.getMessage('LANDING_FORM_EMBED_POSITION_TOP'), value: 'top'},
				{name: Loc.getMessage('LANDING_FORM_EMBED_POSITION_BOTTOM'), value: 'bottom'},
			],
		});

		this.horizontal.subscribe('onChange', () => {
			this.emit('onChange');
		});

		this.vertical.subscribe('onChange', () => {
			this.emit('onChange');
		});

		const fieldsInner = this.getFieldsInner();

		Dom.append(this.horizontal.getLayout(), fieldsInner);
		Dom.append(this.vertical.getLayout(), fieldsInner);

		Dom.replace(this.input, fieldsInner);
	}

	getFieldsInner(): HTMLDivElement
	{
		return this.cache.remember('fieldsInner', () => {
			return Tag.render`
				<div class="landing-ui-position-fields-inner"></div>
			`;
		});
	}

	getValue(): {vertical: string, horizontal: string}
	{
		return {
			vertical: this.vertical.getValue(),
			horizontal: this.horizontal.getValue(),
		};
	}

	setValue(value: {vertical: string, horizontal: string})
	{
		this.vertical.setValue(value.vertical);
		this.horizontal.setValue(value.horizontal);
	}
}