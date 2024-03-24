import { Dom } from 'main.core';
import { CalendarBaseLoader } from './loader-type/calendar-base-loader';
import { CalendarScheduleLoader } from './loader-type/calendar-schedule-loader';
import { DefaultLoader } from './loader-type/default-loader';
import { DiscussionsLoader } from './loader-type/discussions-loader';
import { FilesListLoader } from './loader-type/files-list-loader';
import { FilesTileLoader } from './loader-type/files-tile-loader';
import { FilesBigTileLoader } from './loader-type/files-big-tile-loader';
import { TasksCalendarLoader } from './loader-type/tasks-calendar-loader';
import { TasksGanttLoader } from './loader-type/tasks-gantt-loader';
import { TasksKanbanLoader } from './loader-type/tasks-kanban-loader';
import { TasksListLoader } from './loader-type/tasks-list-loader';
import { TasksScrumPlanLoader } from './loader-type/tasks-scrum-plan-loader';
import { TasksTimelineLoader } from './loader-type/tasks-timeline-loader';

type Params = {
	pageView: string,
	pageUrl: string,
}

export class Loader
{
	#pageUrl: string;

	#node: ?HTMLElement = null;
	#container: HTMLElement;

	#loader: DefaultLoader;

	constructor(params: Params)
	{
		this.#pageUrl = params.pageUrl;

		this.setLoader(params.pageView);
	}

	show(container: HTMLElement)
	{
		if (this.#node !== null)
		{
			return;
		}

		this.#container = container;

		Dom.addClass(container, '--visible');

		this.#node = this.#loader.render();

		Dom.prepend(this.#node, container);
	}

	hide()
	{
		Dom.removeClass(this.#container, '--visible');

		Dom.remove(this.#node);

		this.#node = null;
	}

	isShown(): boolean
	{
		return this.#node !== null;
	}

	setLoader(pageView: string)
	{
		this.#loader = this.#initLoader(pageView);
	}

	#initLoader(pageView: string): DefaultLoader
	{
		switch (pageView)
		{
			case 'discussions':
				return new DiscussionsLoader();
			case 'tasks-list':
				return new TasksListLoader();
			case 'tasks-timeline':
				return new TasksTimelineLoader();
			case 'tasks-plan':
			case 'tasks-kanban':
				return new TasksKanbanLoader();
			case 'tasks-calendar':
				return new TasksCalendarLoader();
			case 'tasks-gantt':
				return new TasksGanttLoader();
			case 'tasks-scrum-plan-sprint':
			case 'tasks-scrum-plan-backlog':
				return new TasksScrumPlanLoader(pageView);
			case 'tasks-scrum-active':
			case 'tasks-scrum-complete':
				return new TasksKanbanLoader();
			case 'calendar-base':
				return new CalendarBaseLoader();
			case 'calendar-schedule':
				return new CalendarScheduleLoader();
			case 'files-list':
				return new FilesListLoader();
			case 'files-tile-m':
				return new FilesTileLoader();
			case 'files-tile-xl':
				return new FilesBigTileLoader();
			default:
				return new DefaultLoader();
		}
	}
}
