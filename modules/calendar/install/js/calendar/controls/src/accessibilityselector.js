import { Event, Loc } from 'main.core';
import { Util } from 'calendar.util';
import { Menu, MenuItem } from 'main.popup';

export class AccessibilitySelector
{
	#accessibilityMenu: Menu;

	constructor(params)
	{
		this.uid = params.uid;
		this.readonly = params.readonly;
		this.input = params.input;

		if (!this.readonly)
		{
			Event.bind(this.input, 'click', this.#showMenu);
		}
	}

	#showMenu = () => {
		this.#accessibilityMenu ??= new Menu({
			id: `${this.uid}-calendar-accessibility-menu`,
			bindElement: this.input,
			closeByEsc: true,
			items: this.#getMenuItems(),
		});

		this.#accessibilityMenu.show();
	};

	#getMenuItems(): Array
	{
		const result = [];
		const items = ['busy', 'quest', 'free'];

		if (Util.getAbsenceAvailable())
		{
			items.push('absent');
		}

		for (const item of items)
		{
			result.push(new MenuItem({
				text: this.getItemName(item),
				onclick: () => this.#selectItem(item),
			}));
		}

		return result;
	}

	#selectItem = (item) => {
		this.#accessibilityMenu.close();
		this.input.value = this.getItemName(item);
		this.input.dataset.value = item;
	};

	getItemName(value): string
	{
		switch (value)
		{
			case 'busy': {
				return Loc.getMessage('EC_CONTROL_ACC_B');
			}

			case 'quest': {
				return Loc.getMessage('EC_CONTROL_ACC_Q');
			}

			case 'free': {
				return Loc.getMessage('EC_CONTROL_ACC_F');
			}

			case 'absent': {
				return Loc.getMessage('EC_CONTROL_ACC_A');
			}

			default: {
				return '';
			}
		}
	}

	setValue(value)
	{
		this.input.value = this.getItemName(value);
		this.input.dataset.value = value;
	}
}
