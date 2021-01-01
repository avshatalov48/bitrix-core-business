import {BaseField} from 'landing.ui.field.basefield';
import {Dom, Text, Type, Event} from 'main.core';

import './css/switch_field.css';

/**
 * @memberOf BX.Landing.UI.Field
 */
export class Switch extends BaseField
{
	constructor(options: any)
	{
		super(options);
		this.setEventNamespace('BX.Landing.UI.Field.Switch');
		Dom.addClass(this.layout, 'landing-ui-field-switch');

		this.value = options.value;
		this.id = `switch_${Text.getRandom()}`;

		this.onValueChangeHandler = Type.isFunction(options.onValueChange) ? options.onValueChange : (() => {});

		this.label = Dom.create('label', {
			props: {className: 'landing-ui-field-switch-label'},
			attrs: {for: this.id},
			html: this.title,
		});

		this.checkbox = Dom.create('input', {
			props: {className: 'landing-ui-field-switch-checkbox'},
			attrs: {type: 'checkbox', id: this.id},
		});

		this.slider = Dom.create('div', {
			props: {className: 'landing-ui-field-switch-slider'},
		});

		Dom.append(this.checkbox, this.label);
		Dom.append(this.slider, this.label);
		Dom.append(this.label, this.input);

		this.setValue(this.value);

		Event.bind(this.checkbox, 'change', this.onChange.bind(this));
	}

	onChange()
	{
		this.onValueChangeHandler();
		this.emit('onChange');
	}

	setValue(value)
	{
		this.checkbox.checked = Text.toBoolean(value);
	}

	getValue(): boolean
	{
		return Text.toBoolean(this.checkbox.checked);
	}
}