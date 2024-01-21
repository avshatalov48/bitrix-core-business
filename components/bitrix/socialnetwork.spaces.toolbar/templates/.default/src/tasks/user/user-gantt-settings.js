import { ExportItem } from '../menu-items/export';
import { GroupSubtasksItem } from '../menu-items/group-subtasks';
import { ImportCsvItem } from '../menu-items/import-csv';
import { ReadAllItem } from '../menu-items/read-all';
import { SortItem } from '../menu-items/sort';
import { SyncItem } from '../menu-items/sync';
import { TasksExcelManager } from '../tasks-excel-manager';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { TasksSortManager } from '../tasks-sort-manager';

type Params = {
	bindElement: HTMLElement,
	pathToTasks: string,
	shouldSubtasksBeGrouped: boolean,
	userId: number,
	gridId: string,
	sortFields: string[],
	taskSort: { field: string, direction: string },
	syncScript: string,
	permissions: any,
}

export class UserGanttSettings extends TasksSettingsMenu
{
	#excelManager: TasksExcelManager;
	#sortManager: TasksSortManager;
	#params: Params;

	constructor(params: Params)
	{
		super(params);

		this.#params = params;

		this.#excelManager = new TasksExcelManager({
			pathToTasks: params.pathToTasks,
		});

		this.#sortManager = new TasksSortManager({
			gridId: params.gridId,
		});
	}

	getViewId(): string
	{
		return 'user-gantt';
	}

	getMenuItems(): MenuItem[]
	{
		const menuItems = [
			ReadAllItem(this, true),
			GroupSubtasksItem(this, this.#params),
			{
				delimiter: true,
			},
			new SortItem(this, {
				sortFields: this.#params.sortFields,
				taskSort: this.#params.taskSort,
				sortManager: this.#sortManager,
			}).getItem(),
			{
				delimiter: true,
			},
		];

		if (this.#params.permissions.import)
		{
			menuItems.push(ImportCsvItem(this, this.#excelManager));
		}

		if (this.#params.permissions.export)
		{
			menuItems.push(ExportItem(this, this.#excelManager));
		}

		if (this.#params.permissions.import && this.#params.permissions.export)
		{
			menuItems.push(SyncItem(this, this.#params.syncScript));
		}

		return menuItems;
	}
}
