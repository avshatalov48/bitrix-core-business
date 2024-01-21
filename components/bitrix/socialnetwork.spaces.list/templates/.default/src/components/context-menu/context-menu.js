import { Menu, MenuManager } from 'main.popup';
import { ContextMenuCollection } from './context-menu-collection';
import { Pin } from './items/pin';
import { Follow } from './items/follow';
import { Open } from './items/open';

type Options = {
	spaceId: number,
	bindElement: HTMLElement,
	path: string,
	isSelected: boolean,
	pinMessage: string,
	followMessage: string,
	openMessage: string,
}

export class ContextMenu
{
	spaceId: number;
	bindElement: HTMLElement;
	path: string;
	isSelected: boolean;
	pinMessage: string;
	followMessage: string;
	openMessage: string;

	collection: ContextMenuCollection
	menu: Menu;

	static ID = 'space-context-menu-';

	constructor(options: Options)
	{
		this.spaceId = options.spaceId;
		this.bindElement = options.bindElement;
		this.path = options.path;
		this.isSelected = options.isSelected;
		this.pinMessage = options.pinMessage;
		this.followMessage = options.followMessage;
		this.openMessage = options.openMessage;

		this.#init();
	}

	createMenu(): void
	{
		const id = ContextMenu.ID + this.spaceId;
		this.menu = MenuManager.create({
			id: id,
			closeByEsc: true,
			bindElement: this.bindElement,
			items: this.getItems(),
		})
	}

	toggle(): void
	{
		this.collection.destroy();
		this.createMenu();
		this.menu.toggle();
	}

	getSpaceId(): number
	{
		return this.spaceId;
	}

	getItems(): JSON[]
	{
		const items = [];
		if (!this.isSelected)
		{
			const open = new Open({
				spaceId: this.spaceId,
				message: this.openMessage,
			});

			items.push(open.setPath(this.path).create());
		}

		const pin = new Pin({
			spaceId: this.spaceId,
			message: this.pinMessage,
		});

		items.push(pin.create())

		const follow = new Follow({
			spaceId: this.spaceId,
			message: this.followMessage,
		});

		items.push(follow.create());

		return items;
	}

	#init(): void
	{
		this.collection = ContextMenuCollection.getInstance();
		this.collection.add(this);
	}
}