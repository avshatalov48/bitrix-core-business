import { EventEmitter } from 'main.core.events';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { TasksView } from '../tasks-view';
import { UserCalendarSettings } from './user-calendar-settings';
import { UserGanttSettings } from './user-gantt-settings';
import { UserKanbanSettings } from './user-kanban-settings';
import { UserListSettings } from './user-list-settings';

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

export class UserSettings extends EventEmitter
{
	#tasksView: TasksView;
	#settings: TasksSettingsMenu;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.TasksUserSettings');

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
				this.#settings = new UserListSettings(params);
				break;
			case 'plan':
			case 'timeline':
				this.#settings = new UserKanbanSettings(params);
				break;
			case 'calendar':
				this.#settings = new UserCalendarSettings(params);
				break;
			case 'gantt':
				this.#settings = new UserGanttSettings(params);
				break;
			default:
				return;
		}

		this.#settings.subscribe('realAll', () => this.emit('realAll'));
	}
}
