import { MenuItem } from 'main.popup';
import { ConfigureViewItem } from '../menu-items/configure-view';
import { KanbanOrder, KanbanSortItems } from '../menu-items/kanban-sort';
import { ReadAllItem } from '../menu-items/read-all';
import { TasksSettingsMenu } from '../tasks-settings-menu';

type Params = {
	bindElement: HTMLElement,
	order: KanbanOrder,
}

export class GroupKanbanSettings extends TasksSettingsMenu
{
	#order: KanbanOrder;

	constructor(params: Params)
	{
		super(params);

		this.#order = params.order;
	}

	getViewId(): string
	{
		return 'group-kanban';
	}

	getMenuItems(): MenuItem[]
	{
		return [
			ReadAllItem(this),
			...(new KanbanSortItems(this, this.#order)).getItems(),
			ConfigureViewItem(this),
		];
	}
}
