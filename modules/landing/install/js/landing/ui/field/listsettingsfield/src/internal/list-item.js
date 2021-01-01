import {BaseField} from 'landing.ui.field.basefield';
import {TextField} from 'landing.ui.field.textfield';
import {Tag} from 'main.core';

import './css/style.css';

export type ListItemOptions = {
	id: string,
	label: string,
	value: any,
	checked: boolean,
};

export class ListItem extends BaseField
{
	constructor(options: ListItemOptions)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.ListSettingsField.ListItem');

		this.setValue(options);
	}

	getTextField(): TextField
	{
		return this.cache.remember('textField', () => {
			return new TextField({
				selector: 'label',
				textOnly: true,
				onChange: this.onTextChange.bind(this),
			});
		});
	}

	onTextChange()
	{
		this.emit('onChange');
	}

	createInput(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-field-list-settings-item-container">
					${this.getTextField().getLayout()}
					${this.getCheckboxField().getLayout()}
				</div>
			`;
		});
	}

	getCheckboxField(): BX.Landing.UI.Field.Checkbox
	{
		return this.cache.remember('checkbox', () => {
			return new BX.Landing.UI.Field.Checkbox({
				compact: true,
				items: [
					{
						name: '',
						value: this.options.value,
					},
				],
				onChange: this.onCheckboxChange.bind(this),
			});
		});
	}

	onCheckboxChange()
	{
		this.emit('onChange');
		this.adjustState();
	}

	adjustState()
	{
		const checkboxField = this.getCheckboxField();
		const textField = this.getTextField();

		if (checkboxField.getValue().length > 0)
		{
			textField.enable();
		}
		else
		{
			textField.disable();
		}
	}

	setValue(value)
	{
		this.getTextField().setValue(value.name);
		this.getCheckboxField().setValue([value.checked ? value.value : '']);
		this.adjustState();
	}

	getValue(): {text: string, checked: boolean}
	{
		return {
			label: this.getTextField().getValue(),
			value: this.options.value,
			checked: this.getCheckboxField().getValue().length > 0,
		};
	}
}