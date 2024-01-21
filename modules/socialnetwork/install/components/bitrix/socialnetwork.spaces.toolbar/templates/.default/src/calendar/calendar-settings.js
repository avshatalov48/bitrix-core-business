import { Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';
import { Calendar } from './calendar';

type Params = {
	bindElement: HTMLElement,
	calendar: Calendar,
}

export class CalendarSettings extends EventEmitter
{
	#menu: Menu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.CalendarSettings');

		if (Type.isNil(params.calendar))
		{
			throw new TypeError('BX.Socialnetwork.Spaces.CalendarSettings: calendar is not available');
		}

		this.#menu = this.#createMenu(params.bindElement, params.calendar);
	}

	show(): void
	{
		this.#menu.show();
	}

	#createMenu(bindElement: HTMLElement, calendar: Calendar): Menu
	{
		const menu = new Menu({
			id: 'spaces-calendar-settings',
			bindElement,
			closeByEsc: true,
		});

		if (
			calendar.getCalendarInstance().util.userIsOwner()
			|| calendar.getCalendarInstance().util.config.TYPE_ACCESS
		)
		{
			menu.addMenuItem({
				text: Loc.getMessage('SN_SPACES_CALENDAR_SETTINGS_SETTINGS'),
				onclick: () => {
					menu.close();
					this.#openSettings(calendar);
				},
			});
		}

		menu.addMenuItem({
			text: Loc.getMessage('SN_SPACES_CALENDAR_SETTINGS_CALENDARS'),
			onclick: () => {
				menu.close();
				this.#openCalendars(calendar);
			},
		});

		return menu;
	}

	#openCalendars(calendar: Calendar): void
	{
		// eslint-disable-next-line promise/catch-or-return
		this.#getSectionInterface()
			.then((SectionInterface) => {
				if (!this.sectionInterface)
				{
					const calendarInstance = calendar.getCalendarInstance();

					this.sectionInterface = new SectionInterface(
						{
							calendarContext: calendarInstance,
							readonly: calendarInstance.util.readOnlyMode(),
							sectionManager: calendarInstance.sectionManager,
						},
					);
				}
				this.sectionInterface.show();
			});
	}

	#openSettings(calendar: Calendar): void
	{
		// eslint-disable-next-line promise/catch-or-return
		this.#getSettingsInterface()
			.then((SettingsInterface) => {
				if (!this.settingsInterface)
				{
					const calendarInstance = calendar.getCalendarInstance();

					if (Type.isNull(calendarInstance))
					{
						throw new TypeError('BX.Socialnetwork.Spaces.CalendarSettings: calendar instance is not available');
					}

					this.settingsInterface = new SettingsInterface(
						{
							calendarContext: calendarInstance,
							showPersonalSettings: calendarInstance.util.userIsOwner(),
							showGeneralSettings: Boolean(calendarInstance.util.config.perm
								&& calendarInstance.util.config.perm.access),
							settings: calendarInstance.util.config.settings,
						},
					);
				}
				this.settingsInterface.show();
			});
	}

	#getSectionInterface(): Promise
	{
		return new Promise((resolve) => {
			const bx = BX.Calendar.Util.getBX();
			if (bx.Calendar.SectionInterface)
			{
				resolve(bx.Calendar.SectionInterface);
			}
			else
			{
				const extensionName = 'calendar.sectioninterface';
				// eslint-disable-next-line promise/catch-or-return
				bx.Runtime.loadExtension(extensionName)
					.then(() => {
						if (bx.Calendar.SectionInterface)
						{
							resolve(bx.Calendar.SectionInterface);
						}
						else
						{
							console.error(`Extension ${extensionName} not found`);
						}
					});
			}
		});
	}

	#getSettingsInterface(): Promise
	{
		return new Promise((resolve) => {
			const bx = BX.Calendar.Util.getBX();
			if (bx.Calendar.SettingsInterface)
			{
				resolve(bx.Calendar.SettingsInterface);
			}
			else
			{
				const extensionName = 'calendar.settingsinterface';
				// eslint-disable-next-line promise/catch-or-return
				bx.Runtime.loadExtension(extensionName)
					.then(() => {
						if (bx.Calendar.SettingsInterface)
						{
							resolve(bx.Calendar.SettingsInterface);
						}
						else
						{
							console.error(`Extension ${extensionName} not found`);
						}
					});
			}
		});
	}
}
