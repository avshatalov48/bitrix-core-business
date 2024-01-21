import { EventEmitter } from 'main.core.events';
import { KanbanOrder } from '../menu-items/kanban-order';
import { TasksSettingsMenu } from '../tasks-settings-menu';
import { TasksView } from '../tasks-view';
import { ScrumActiveSettings } from './scrum-active-settings';
import { ScrumCompleteSettings } from './scrum-complete-settings';
import { ScrumPlanSettings } from './scrum-plan-settings';

type Params = {
	bindElement: HTMLElement,
	displayPriority: string,
	tasksView: TasksView,
	order: KanbanOrder,
	canCompleteSprint: boolean,
	activeSprintExists: boolean,
}

export class ScrumSettings extends EventEmitter
{
	#settings: TasksSettingsMenu;

	#tasksView: TasksView;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.TasksScrumSettings');

		this.#tasksView = params.tasksView;

		this.#createSettings(params);
	}

	show(): void
	{
		this.#settings.show();
	}

	#createSettings(params: Params): void
	{
		switch (this.#tasksView.getCurrentViewMode())
		{
			case 'plan':
				this.#settings = new ScrumPlanSettings(params);
				this.#settings.subscribe('realAll', () => this.emit('realAll'));
				break;
			case 'active':
				this.#settings = new ScrumActiveSettings(params);
				this.#settings.subscribe('completeSprint', () => this.emit('completeSprint'));
				break;
			case 'complete':
				this.#settings = new ScrumCompleteSettings(params);
				this.#settings.subscribe('showBurnDown', () => this.emit('showBurnDown'));
				break;
			default:
				break;
		}
	}
}
