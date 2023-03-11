import {Type, Event, Loc, Text} from 'main.core';
import {DateTimeFormat} from 'main.date';
import { Menu } from 'main.popup';

export class InlineTimeSelector
{
	#labelNode: HTMLInputElement = null;

	#time: string = '';

	#timeValues: [] = [];
	#timeFormat: string;

	#selector: Menu;

	constructor(options: {
		labelNode: HTMLInputElement,
	})
	{
		if (Type.isPlainObject(options))
		{
			if (Type.isElementNode(options.labelNode))
			{
				this.#labelNode = options.labelNode;
			}
		}

		this.#fillTimeFormat();
		this.#fillTimeValues();
	}

	#fillTimeFormat()
	{
		const getFormat = (formatId) => (
			BX.Main.Date.convertBitrixFormat(Loc.getMessage(formatId)).replace(/:?\s*s/, '')
		);

		const dateFormat = getFormat('FORMAT_DATE');
		const dateTimeFormat = getFormat('FORMAT_DATETIME');
		this.#timeFormat = dateTimeFormat.replace(dateFormat, '').trim();
	}

	#fillTimeValues()
	{
		const self = this;
		const onclick = function(event, item)
		{
			event.preventDefault();
			self.#labelNode.value = Text.encode(item.text);
			this.close();
		};

		for (let hour = 0; hour < 24; hour++)
		{
			this.#timeValues.push({
				id: hour * 60,
				text: this.#formatTime(hour, 0),
				onclick: onclick
			});
			this.#timeValues.push({
				id: hour * 60 + 30,
				text: this.#formatTime(hour, 30),
				onclick: onclick
			});
		}
	}

	#formatTime(hour, minute): string
	{
		const date = new Date();
		date.setHours(hour, minute);

		return DateTimeFormat.format(this.#timeFormat, date.getTime() / 1000);
	}

	init(time: string)
	{
		if (Type.isStringFilled(time))
		{
			this.#time = time;
		}

		this.#setLabelText();
		this.#bindLabelNode();
	}

	#setLabelText()
	{
		if (Type.isElementNode(this.#labelNode))
		{
			this.#labelNode.textContent = this.#time;
		}
	}

	#bindLabelNode()
	{
		if (Type.isElementNode(this.#labelNode))
		{
			Event.bind(this.#labelNode, 'click', this.#onLabelClick.bind(this));
		}
	}

	#onLabelClick(event)
	{
		this.#showTimeSelector();
		event.preventDefault();
	}

	#showTimeSelector()
	{
		if (Type.isNil(this.#selector))
		{
			this.#selector = new Menu({
				autoHide: true,
				bindElement: this.#labelNode,
				items: this.#timeValues,
				maxHeight: 230,
			})
		}

		this.#selector.show();
	}
}