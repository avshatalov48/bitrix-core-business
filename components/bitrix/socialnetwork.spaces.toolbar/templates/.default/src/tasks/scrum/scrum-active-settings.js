import { MenuItem } from 'main.popup';
import { CompleteSprintItem } from '../menu-items/complete-sprint';
import { KanbanOrder, KanbanSortItems } from '../menu-items/kanban-sort';
import { TasksSettingsMenu } from '../tasks-settings-menu';

type Params = {
	bindElement: HTMLElement,
	order: KanbanOrder,
	canCompleteSprint: boolean,
	activeSprintExists: boolean,
}

export class ScrumActiveSettings extends TasksSettingsMenu
{
	#order: KanbanOrder;
	#canCompleteSprint: boolean;
	#activeSprintExists: boolean;

	constructor(params: Params)
	{
		super(params);

		this.#order = params.order;
		this.#canCompleteSprint = params.canCompleteSprint;
		this.#activeSprintExists = params.activeSprintExists;
	}

	getViewId(): string
	{
		return 'scrum-active';
	}

	canCompleteSprint(): boolean
	{
		return this.#canCompleteSprint && this.#activeSprintExists;
	}

	getMenuItems(): MenuItem[]
	{
		return [
			CompleteSprintItem(this),
			...(new KanbanSortItems(this, this.#order)).getItems(),
		];
	}
}
