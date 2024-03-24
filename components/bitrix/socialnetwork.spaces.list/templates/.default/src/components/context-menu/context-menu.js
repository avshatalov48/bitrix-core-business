import { Cache } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Menu, MenuManager } from 'main.popup';
import { RecentService } from '../../api/load/recent-service';
import { Permissions } from '../../model/space-model';
import { ContextMenuCollection } from './context-menu-collection';
import { CopyLink } from './items/copy-link';
import { Logout } from './items/logout';
import { Pin } from './items/pin';
import { Follow } from './items/follow';
import { Open } from './items/open';
import { FilterModeTypes } from '../../const/filter-mode';
import { Modes } from '../../const/mode';

type Options = {
	spaceId: number,
	bindElement: HTMLElement,
	path: string,
	isSelected: boolean,
	pinMessage: string,
	followMessage: string,
	openMessage: string,
	listFilter: string,
	listMode: string,
	permissions: Permissions,
}

export class ContextMenu extends EventEmitter
{
	collection: ContextMenuCollection;
	menu: Menu;

	#cache = new Cache.MemoryCache();

	static ID = 'space-context-menu-';

	constructor(options: Options)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.ContextMenu');

		this.#setOptions(options);

		this.#init();
	}

	#setOptions(options: Options)
	{
		this.#cache.set('options', options);
	}

	#getOption(option: string): any
	{
		return this.#cache.get('options')[option];
	}

	createMenu(): void
	{
		const id = this.getMenuId();
		this.menu = MenuManager.create({
			id,
			closeByEsc: true,
			bindElement: this.#getOption('bindElement'),
			items: this.getItems(),
		});
	}

	getMenuId(): string
	{
		return ContextMenu.ID + this.#getOption('spaceId');
	}

	toggle(): void
	{
		this.collection.destroy();
		this.collection.add(this);
		this.createMenu();
		this.menu.toggle();
	}

	getSpaceId(): number
	{
		return this.#getOption('spaceId');
	}

	isShown(): boolean
	{
		return this.menu.getPopupWindow()?.isShown();
	}

	getItems(): Array<Object>
	{
		const items = [];
		if (!this.#getOption('isSelected'))
		{
			const open = new Open({
				spaceId: this.#getOption('spaceId'),
				message: this.#getOption('openMessage'),
			});
			items.push(open.setPath(this.#getOption('path')).create());
		}

		if (
			this.#getOption('listFilter') === FilterModeTypes.my
			&& this.#getOption('listMode') === Modes.recent
		)
		{
			const pin = new Pin({
				spaceId: this.#getOption('spaceId'),
				message: this.#getOption('pinMessage'),
			});
			items.push(pin.create());
		}

		const follow = new Follow({
			spaceId: this.#getOption('spaceId'),
			message: this.#getOption('followMessage'),
		});
		items.push(follow.create());

		const copyLink = new CopyLink({
			spaceId: this.#getOption('spaceId'),
			message: this.#getOption('copyLinkMessage'),
		});
		items.push(copyLink.create());

		const permissions: Permissions = this.#getOption('permissions');

		if (
			this.#getOption('listFilter') === FilterModeTypes.my
			&& this.#getOption('listMode') === Modes.recent
			&& permissions.canLeave
		)
		{
			const logout = new Logout({
				spaceId: this.#getOption('spaceId'),
				message: this.#getOption('logoutMessage'),
			});
			logout.subscribe('click', () => {
				if (RecentService.getInstance().getSelectedSpaceId() === this.#getOption('spaceId'))
				{
					this.emit('openCommonSpace');
				}
			});
			items.push(logout.create());
		}

		return items;
	}

	#init(): void
	{
		this.collection = ContextMenuCollection.getInstance();
	}
}
