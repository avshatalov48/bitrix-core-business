import { MenuManager } from 'main.popup';
import { ContextMenu } from './context-menu';

export class ContextMenuCollection
{
	static items = [];
	static instance = null;

	static getInstance(): ContextMenuCollection
	{
		if (ContextMenuCollection.instance === null)
		{
			ContextMenuCollection.instance = new this();
		}

		return ContextMenuCollection.instance;
	}

	add(menu: ContextMenu)
	{
		if (!this.has(menu))
		{
			ContextMenuCollection.items.push(menu);
		}
	}

	has(menu: ContextMenu): boolean
	{
		ContextMenuCollection.items.forEach((item: ContextMenu) => {
			if (item.getSpaceId() === menu.getSpaceId())
			{
				return true;
			}
		})

		return false;
	}

	destroy(): void
	{
		ContextMenuCollection.items.forEach((item: ContextMenu) => {
			const menu = MenuManager.getMenuById(ContextMenu.ID + item.getSpaceId());
			menu?.destroy();
		});
	}
}