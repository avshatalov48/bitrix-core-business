import { EventEmitter } from 'main.core.events';
import { Event } from 'main.core';
import { Menu } from 'ui.system.menu';
import { Store } from 'ui.vue3.vuex';
import { RestClient } from 'rest.client';

import { Core } from 'im.v2.application.core';

import type { PopupTarget } from 'main.popup';
import type { MenuItemOptions, MenuOptions, MenuSectionOptions } from 'ui.system.menu';

const EVENT_NAMESPACE = 'BX.Messenger.v2.Lib.Menu';

export class BaseMenu extends EventEmitter
{
	menuInstance: Menu;
	context: Object;
	target: HTMLElement;
	store: Store;
	restClient: RestClient;
	id: String = 'im-base-context-menu';

	constructor()
	{
		super();
		this.setEventNamespace(EVENT_NAMESPACE);

		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
	}

	// public
	openMenu(context: Object, target: PopupTarget)
	{
		if (this.menuInstance)
		{
			this.close();
		}
		this.context = context;
		this.target = target;
		this.menuInstance = new Menu(this.getMenuOptions());
		this.menuInstance.show(this.target);

		this.#bindBlurEvent();
	}

	getMenuOptions(): MenuOptions
	{
		return {
			id: this.id,
			bindOptions: { forceBindPosition: true, position: 'bottom' },
			targetContainer: document.body,
			cacheable: false,
			closeByEsc: true,
			className: this.getMenuClassName(),
			items: this.#prepareItems(),
			sections: this.getMenuGroups(),
		};
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		return [];
	}

	getMenuGroups(): MenuSectionOptions[]
	{
		return [];
	}

	groupItems(menuItems: MenuItemOptions | null[], group: string): MenuItemOptions[]
	{
		return menuItems.filter((item) => item !== null).map((item: MenuItemOptions) => {
			return {
				...item,
				sectionCode: group,
			};
		});
	}

	getMenuClassName(): string
	{
		return '';
	}

	onClosePopup()
	{
		this.close();
	}

	close()
	{
		if (!this.menuInstance)
		{
			return;
		}

		this.menuInstance.destroy();
		this.menuInstance = null;
	}

	destroy()
	{
		this.close();
	}

	getCurrentUserId(): number
	{
		return Core.getUserId();
	}

	#prepareItems(): MenuItemOptions[]
	{
		return this.getMenuItems().filter((item) => item !== null);
	}

	#bindBlurEvent(): void
	{
		Event.bindOnce(window, 'blur', () => {
			this.destroy();
		});
	}
}
