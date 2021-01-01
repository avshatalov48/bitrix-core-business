import {Dom, Tag} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {ListItem} from './internal/list-item';

type ListSettingsFieldItemOptions = {
	label: string,
	value: any,
	checked: boolean,
};

/**
 * @memberOf BX.Landing.UI.Field
 */
export class ListSettingsField extends BaseField
{
	constructor(options)
	{
		super({...options, textOnly: true});
		this.setEventNamespace('BX.Landing.UI.Field.ListSettingsField');

		this.onChange = this.onChange.bind(this);
		this.items = [];

		this.options.items.forEach((item) => {
			this.addItem(item);
		});
	}

	createInput(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-ui-field-list-settings"></div>
		`;
	}

	addItem(options: ListSettingsFieldItemOptions)
	{
		const item = new ListItem(options);
		item.subscribe('onChange', this.onChange);
		Dom.append(item.getLayout(), this.input);
		this.items.push(item);
	}

	onChange()
	{
		this.emit('onChange');
	}

	getValue(): Array<{name: string, value: any}>
	{
		return this.items
			.map((item) => {
				return item.getValue();
			})
			.filter((item) => {
				return item.checked;
			});
	}
}