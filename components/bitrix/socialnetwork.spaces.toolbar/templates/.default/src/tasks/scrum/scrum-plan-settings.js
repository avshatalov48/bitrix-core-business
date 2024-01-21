import { MenuItem } from 'main.popup';
import { ReadAllItem } from '../menu-items/read-all';
import { PriorityItems } from '../menu-items/priority';
import { TasksSettingsMenu } from '../tasks-settings-menu';

type Params = {
	bindElement: HTMLElement,
	displayPriority: string,
}

export class ScrumPlanSettings extends TasksSettingsMenu
{
	#displayPriority: string;

	constructor(params: Params)
	{
		super(params);

		this.#displayPriority = params.displayPriority;
	}

	getViewId(): string
	{
		return 'scrum-plan';
	}

	getMenuItems(): MenuItem[]
	{
		return [
			ReadAllItem(this, true),
			{
				delimiter: true,
			},
			...PriorityItems(this, this.#displayPriority),
		];
	}
}
