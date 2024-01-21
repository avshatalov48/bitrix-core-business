import { EventEmitter } from 'main.core.events';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { TasksView } from '../tasks-view';
import { GroupCalendarSettings } from './group-calendar-settings';
import { GroupGanttSettings } from './group-gantt-settings';
import { GroupKanbanSettings } from './group-kanban-settings';
import { GroupListSettings } from './group-list-settings';

type Params = {
	bindElement: HTMLElement,
	tasksView: TasksView,
	pathToTasks: string,
	order: string,
	shouldSubtasksBeGrouped: boolean,
	userId: number,
	gridId: string,
	sortFields: string[],
	taskSort: { field: string, direction: string },
	syncScript: string,
	permissions: any,
}

export class GroupSettings extends EventEmitter
{
	#tasksView: TasksView;
	#settings: TasksSettingsMenu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.TasksGroupSettings');

		this.#tasksView = params.tasksView;

		this.#createSettings(params);
	}

	show(): void
	{
		this.#settings.show();
	}

	#createSettings(params: Params): Menu
	{
		switch (this.#tasksView.getCurrentViewMode())
		{
			case 'list':
				this.#settings = new GroupListSettings(params);
				break;
			case 'kanban':
			case 'plan':
			case 'timeline':
				this.#settings = new GroupKanbanSettings(params);
				break;
			case 'calendar':
				this.#settings = new GroupCalendarSettings(params);
				break;
			case 'gantt':
				this.#settings = new GroupGanttSettings(params);
				break;
			default:
				return;
		}

		this.#settings.subscribe('realAll', () => this.emit('realAll'));
	}
}
