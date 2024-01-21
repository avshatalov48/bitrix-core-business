import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';
import { Calendar } from './calendar';

type Params = {
	bindElement: ?HTMLElement,
	calendar: Calendar,
	showMenu: ?boolean,
}

export class CalendarAddButtonMenu extends EventEmitter
{
	#menu: Menu;
	#calendar: Calendar;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.CalendarAddButtonMenu');

		if (params.showMenu ?? true)
		{
			this.#menu = this.#createMenu(params.bindElement);
		}

		this.#calendar = params.calendar;
	}

	show(): void
	{
		this.#menu.show();
	}

	#createMenu(bindElement: HTMLElement): Menu
	{
		const menu = new Menu({
			id: 'spaces-calendar-add-button-menu',
			bindElement,
			closeByEsc: true,

		});

		menu.addMenuItem({
			text: Loc.getMessage('SN_SPACES_CALENDAR_CREATE_EVENT'),
			dataset: { id: 'spaces-calendar-add-button-menu-create-event' },
			onclick: () => {
				menu.close();
				this.#calendar.addEvent();
			},
		});

		menu.addMenuItem({
			text: Loc.getMessage('SN_SPACES_CALENDAR_CREATE_TASK'),
			dataset: { id: 'spaces-calendar-add-button-menu-create-task' },
			onclick: () => {
				menu.close();
				this.#calendar.addTask();
			},
		});

		return menu;
	}
}
