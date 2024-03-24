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
		ContextMenuCollection.items.push(menu);
	}

	destroy(): void
	{
		ContextMenuCollection.items.forEach((item: ContextMenu) => {
			const menu = MenuManager.getMenuById(ContextMenu.ID + item.getSpaceId());
			menu?.destroy();
		});
		ContextMenuCollection.items = ContextMenuCollection.items.filter((item: ContextMenu) => item.isShown());
	}
}
