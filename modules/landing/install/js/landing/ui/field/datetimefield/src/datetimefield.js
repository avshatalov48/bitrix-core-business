import {Type, Event} from 'main.core';
import {BaseField} from 'landing.ui.field.basefield';
import {Loc} from 'landing.loc';

export class DateTimeField extends BaseField
{
	constructor(options = {})
	{
		super({
			...options,
			content: options.content || options.value || '',
			textOnly: true,
		});

		Event.bind(this.input, 'click', this.onInputClick.bind(this));
	}

	getFormat(): string
	{
		return BX.date.convertBitrixFormat(
			Loc.getMessage(this.options.time ? 'FORMAT_DATETIME' : 'FORMAT_DATE'),
		);
	}

	showDatepicker()
	{
		this.getContext().BX.calendar({
			node: this.input,
			field: this.input,
			bTime: this.options.time,
			value: BX.date.format(this.getFormat(), this.getValue() || (new Date())),
			bHideTime: !this.options.time,
			callback_after: (date) => {
				this.setValue(BX.date.format(this.getFormat(), date));
			},
		});
	}

	setValue(value: string)
	{
		super.setValue(value);
		this.emit('onChange');
	}

	onInputClick()
	{
		this.showDatepicker();
	}
}