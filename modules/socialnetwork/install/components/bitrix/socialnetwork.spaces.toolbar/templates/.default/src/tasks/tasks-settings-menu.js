import { EventEmitter } from 'main.core.events';
import { Menu, MenuItem } from 'main.popup';
import { ReadAllItem } from './menu-items/read-all';

type Params = {
	bindElement: HTMLElement,
}

export class TasksSettingsMenu extends EventEmitter
{
	#menu: Menu;
	#bindElement: HTMLElement;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace(this.#getEventNamespace());

		this.#bindElement = params.bindElement;
	}

	show(): void
	{
		this.#getMenu().show();
	}

	close(): void
	{
		this.#menu?.close();
	}

	#getMenu(): Menu
	{
		if (!this.#menu)
		{
			this.#menu = this.#createMenu(this.#bindElement);
		}

		return this.#menu;
	}

	#createMenu(bindElement: HTMLElement): Menu
	{
		const menu = new Menu({
			id: `spaces-tasks-${this.getViewId()}-settings`,
			bindElement,
			closeByEsc: true,
		});

		for (const menuItem of this.getMenuItems())
		{
			menu.addMenuItem(menuItem);
		}

		return menu;
	}

	#getEventNamespace(): string
	{
		const camelCase = this.getViewId().toLowerCase().replace(/(-[a-z])/g, (group) => group
			.toUpperCase()
			.replace('-', ''));

		const pascalCase = camelCase.charAt(0).toUpperCase() + camelCase.slice(1);

		return `BX.Socialnetwork.Spaces.${pascalCase}Settings`;
	}

	getMenuItems(): MenuItem[]
	{
		return [
			ReadAllItem(this),
		];
	}

	getViewId(): string
	{
		return 'base';
	}
}
