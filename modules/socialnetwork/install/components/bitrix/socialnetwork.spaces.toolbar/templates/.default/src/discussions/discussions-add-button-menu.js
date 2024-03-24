import { Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu } from 'main.popup';
import { Calendar } from '../calendar/calendar';

type Params = {
	bindElement: HTMLElement,
	calendar: Calendar,
	isDiskStorageWasObtained: boolean,
}

export class DiscussionsAddButtonMenu extends EventEmitter
{
	#menu: Menu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.DiscussionsAddButtonMenu');

		if (Type.isNil(params.calendar))
		{
			throw new TypeError('BX.Socialnetwork.Spaces.DiscussionsAddButtonMenu: calendar is not allowed');
		}

		this.#menu = this.#createMenu(params.bindElement, params.calendar, params.isDiskStorageWasObtained);
	}

	show(): void
	{
		this.#menu.show();
	}

	#createMenu(bindElement: HTMLElement, calendar: Calendar, isDiskStorageWasObtained: boolean): Menu
	{
		const fileUploadItemId = 'spaces-discussions-add-button-menu-file-item';

		const menu = new Menu({
			id: 'spaces-discussions-add-button-menu',
			bindElement,
			closeByEsc: true,
			events: {
				onShow: (event) => {
					if (isDiskStorageWasObtained)
					{
						this.emit('showMenu', {
							fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer(),
						});
					}
				},
				onClose: () => {
					if (isDiskStorageWasObtained)
					{
						this.emit('closeMenu', {
							fileUploadContainer: menu.getMenuItem(fileUploadItemId).getContainer(),
						});
					}
				},
			},
		});

		menu.addMenuItem({
			text: Loc.getMessage('SN_SPACES_DISCUSSIONS_CREATE_TASK'),
			dataset: { id: 'spaces-discussions-add-button-menu-create-task' },
			onclick: () => {
				menu.close();
				calendar.addTask();
			},
		});

		menu.addMenuItem({
			text: Loc.getMessage('SN_SPACES_DISCUSSIONS_ORGANIZE_EVENT'),
			dataset: { id: 'spaces-discussions-add-button-menu-organize_event' },
			onclick: () => {
				menu.close();
				calendar.addEvent();
			},
		});

		if (isDiskStorageWasObtained)
		{
			menu.addMenuItem({
				text: Loc.getMessage('SN_SPACES_DISCUSSIONS_UPLOAD_FILE'),
				dataset: { id: 'spaces-discussions-add-button-menu-file' },
				id: fileUploadItemId,
			});
		}

		return menu;
	}
}
