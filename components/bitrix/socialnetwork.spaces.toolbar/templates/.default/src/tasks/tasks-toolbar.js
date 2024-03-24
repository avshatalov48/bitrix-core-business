import { Cache, Dom, Event, Loc, Tag, Type } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { ShortView } from 'ui.short-view';
import { Counters, TasksCounters } from './tasks-counters';
import { Filter } from '../filter';
import { TasksRobots } from './tasks-robots';
import { TasksRouter } from './tasks-router';
import { ScrumSettings } from './scrum/scrum-settings';
import { GroupSettings } from './group/group-settings';
import { CurrentCompletedSprint, TasksScrum } from './tasks-scrum';
import { TasksView } from './tasks-view';
import { UserSettings } from './user/user-settings';
import { TasksViewList } from './tasks-view-list';
import { CreationMenu } from 'tasks.creation-menu';

import type { ViewItem } from './tasks-view-list';

import '../css/tasks.css';

type Params = {
	isUserSpace: boolean,
	isScrumSpace: boolean,
	userId?: number,
	groupId?: number,
	filterId: string,
	filterContainer: HTMLElement,
	filterRole: string,
	counters: Counters,
	viewList: Array<ViewItem>,
	pathToGroupTasks?: string,
	pathToGroupTasksTask?: string,
	pathToAddTask: string,
	pathToTemplateList: string,
	pathToTasks: string,
	displayPriority?: string,
	isShortView?: 'Y' | 'N',
	viewMode: string,
	order: string,
	shouldSubtasksBeGrouped: boolean,
	gridId: string,
	sortFields: string[],
	taskSort: { field: string, direction: string },
	syncScript: string,
	permissions: any,
	activeSprintId?: number,
	taskLimitExceeded?: 'Y' | 'N',
	canUseAutomation?: 'Y' | 'N',
	canEditSprint?: 'Y' | 'N',
	currentCompletedSprint?: CurrentCompletedSprint,
}

export class TasksToolbar
{
	#cache = new Cache.MemoryCache();

	#filter: Filter;
	#router: TasksRouter;
	#counters: ?TasksCounters;
	#tasksView: TasksView;
	#tasksScrum: ?TasksScrum;
	#tasksViewList: TasksViewList;
	#settings: UserSettings | GroupSettings | ScrumSettings;

	constructor(params: Params)
	{
		this.#setParams(params);

		this.#filter = new Filter({
			filterId: this.#getParam('filterId'),
			filterContainer: this.#getParam('filterContainer'),
		});

		if (this.#getParam('isUserSpace'))
		{
			this.#router = new TasksRouter({
				pathToTasks: this.#getParam('pathToUserSpaceTasks'),
				pathToTasksTask: '',
			});
		}
		else
		{
			this.#router = new TasksRouter({
				pathToTasks: this.#getParam('pathToGroupTasks'),
				pathToTasksTask: this.#getParam('pathToGroupTasksTask'),
			});
		}

		this.#tasksView = new TasksView({
			isUserSpace: this.#getParam('isUserSpace'),
			isScrumSpace: this.#getParam('isScrumSpace'),
			viewMode: this.#getParam('viewMode'),
		});

		if (this.#getParam('isScrumSpace'))
		{
			this.#tasksScrum = new TasksScrum({
				groupId: this.#getParam('groupId'),
				pathToScrumBurnDown: this.#getParam('pathToScrumBurnDown'),
				router: this.#router,
				currentCompletedSprint: this.#getParam('currentCompletedSprint'),
			});
		}
	}

	renderAddBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderAddBtn(), container);
	}

	renderScrumAddBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderScrumAddBtn(), container);
	}

	renderCountersTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for add btn not found');
		}

		const ignoreList = new Set([]);
		if (this.#getParam('isScrumSpace'))
		{
			ignoreList.add('active');
			ignoreList.add('complete');
		}

		if (ignoreList.has(this.#tasksView.getCurrentViewMode()))
		{
			return;
		}

		this.#counters = new TasksCounters({
			filter: this.#filter,
			filterRole: this.#getParam('filterRole'),
			userId: this.#getParam('userId'),
			groupId: this.#getParam('groupId'),
			counters: this.#getParam('counters'),
			isUserSpace: this.#getParam('isUserSpace'),
			isScrumSpace: this.#getParam('isScrumSpace'),
			tasksView: this.#tasksView,
		});

		Dom.append(this.#counters.render(), container);
	}

	renderViewBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for view btn not found');
		}

		Dom.append(this.#renderViewBtn(), container);
	}

	renderScrumShortView(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for short view btn not found');
		}

		const shortView = new ShortView({
			isShortView: this.#getParam('isShortView'),
		});

		shortView.renderTo(container);
		shortView.subscribe('change', (baseEvent: BaseEvent) => {
			BX.Tasks.Scrum.Entry.changeShortView(baseEvent.getData());
		});
	}

	renderScrumSprintSelector(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for sprint selector not found');
		}

		Dom.append(this.#tasksScrum.renderSprintSelector(), container);
	}

	renderScrumRobots(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for robots btn not found');
		}

		if (this.#getParam('activeSprintId') > 0 && this.#getParam('canEditSprint') === 'Y')
		{
			const tasksRobots = new TasksRobots({
				groupId: this.#getParam('groupId'),
				isTaskLimitsExceeded: this.#getParam('isTaskLimitsExceeded'),
				canUseAutomation: this.#getParam('canUseAutomation'),
				sourceAnalytics: 'scrumActiveSprint',
			});

			Dom.append(tasksRobots.renderBtn(), container);
		}
	}

	renderSettingsBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.TasksToolbar: HTMLElement for settings btn not found');
		}

		const ignoreList = new Set([]);

		if (ignoreList.has(this.#tasksView.getCurrentViewMode()))
		{
			return;
		}

		Dom.append(this.#renderSettingsBtn(), container);
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#renderAddBtn(): HTMLElement
	{
		const node = Tag.render`
			<div class="ui-btn-split ui-btn-success ui-btn-round ui-btn-no-caps">
				<a
					data-id="spaces-tasks-add-main-btn"
					class="ui-btn-main"
					href="${this.#getParam('pathToAddTask')}"
				>
					${Loc.getMessage('SN_SPACES_TASKS_ADD_TASK')}
				</a>
				<button class="ui-btn-menu" data-id="spaces-tasks-add-menu-btn"></button>
			</div>
		`;

		Event.bind(node.querySelector('.ui-btn-menu'), 'click', this.#addMenuClick.bind(this));

		return node;
	}

	#renderScrumAddBtn(): HTMLElement
	{
		const node = Tag.render`
			<div class="ui-btn-split ui-btn-round ui-btn-no-caps ui-btn-light-border ui-btn-themes">
				<a
					data-id="spaces-tasks-add-main-btn"
					class="ui-btn-main"
					href="${this.#getParam('pathToAddTask')}"
				>
					${Loc.getMessage('SN_SPACES_TASKS_ADD_TASK')}
				</a>
				<button class="ui-btn-menu" data-id="spaces-tasks-add-menu-btn"></button>
			</div>
		`;

		Event.bind(node.querySelector('.ui-btn-menu'), 'click', this.#addMenuClick.bind(this));

		return node;
	}

	#renderViewBtn(): HTMLElement
	{
		const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-no-caps ui-btn-themes';

		const node = Tag.render`
			<button
				data-id="sn-spaces-tasks-view-mode-btn"
				class="${uiClasses} sn-spaces__toolbar-space_btn-options"
			>
				<div
					class="ui-icon-set --customer-cards"
					style="--ui-icon-set__icon-size: 25px;"
				></div>
				<div class="sn-spaces__toolbar-space_btn-text">
					${Loc.getMessage('SN_SPACES_TASKS_VIEW_BTN')}
				</div>
				<div
					class="ui-icon-set --chevron-down"
					style="--ui-icon-set__icon-size: 19px;"
				></div>
			</button>
		`;

		Event.bind(node, 'click', this.#viewClick.bind(this));

		return node;
	}

	#renderSettingsBtn(): HTMLElement
	{
		const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes';

		const node = Tag.render`
			<button
				data-id="spaces-tasks-settings-btn"
				class="${uiClasses} sn-spaces__toolbar-space_btn-more"
			>
				<div class="ui-icon-set --more"></div>
			</button>
		`;

		Event.bind(node, 'click', this.#settingsClick.bind(this));

		return node;
	}

	#addMenuClick(event)
	{
		CreationMenu.toggle({
			bindElement: event.srcElement,
			createTaskLink: this.#getParam('pathToAddTask'),
			templatesListLink: this.#getParam('pathToTemplateList'),
		});
	}

	#viewClick(event)
	{
		if (!this.#tasksViewList)
		{
			this.#tasksViewList = new TasksViewList({
				bindElement: event.target,
				viewList: this.#getParam('viewList'),
			});

			this.#tasksViewList.subscribe(
				'click',
				(baseEvent: BaseEvent<{ urlParam: string, urlValue: string }>) => {
					const { urlParam, urlValue } = baseEvent.getData();
					this.#router.redirectToTasks(urlParam, urlValue);
				},
			);
		}

		this.#tasksViewList.show();
	}

	#settingsClick(event)
	{
		if (!this.#settings)
		{
			if (this.#getParam('isUserSpace'))
			{
				this.#settings = new UserSettings({
					bindElement: event.target,
					tasksView: this.#tasksView,
					pathToTasks: this.#getParam('pathToTasks'),
					order: this.#getParam('order'),
					shouldSubtasksBeGrouped: this.#getParam('shouldSubtasksBeGrouped'),
					userId: this.#getParam('userId'),
					gridId: this.#getParam('gridId'),
					sortFields: this.#getParam('sortFields'),
					taskSort: this.#getParam('taskSort'),
					syncScript: this.#getParam('syncScript'),
					permissions: this.#getParam('permissions'),
				});
			}
			else
			{
				// eslint-disable-next-line no-lonely-if
				if (this.#getParam('isScrumSpace'))
				{
					this.#settings = new ScrumSettings({
						bindElement: event.target,
						displayPriority: this.#getParam('displayPriority'),
						tasksView: this.#tasksView,
						router: this.#router,
						order: this.#getParam('order'),
						activeSprintExists: this.#getParam('activeSprintId') > 0,
						canCompleteSprint: this.#getParam('canEditSprint') === 'Y',
					});
					this.#settings.subscribe('completeSprint', () => this.#tasksScrum.showCompletionForm());
					this.#settings.subscribe('showBurnDown', () => this.#tasksScrum.showBurnDown());
				}
				else
				{
					this.#settings = new GroupSettings({
						bindElement: event.target,
						tasksView: this.#tasksView,
						pathToTasks: this.#getParam('pathToTasks'),
						order: this.#getParam('order'),
						shouldSubtasksBeGrouped: this.#getParam('shouldSubtasksBeGrouped'),
						userId: this.#getParam('userId'),
						gridId: this.#getParam('gridId'),
						sortFields: this.#getParam('sortFields'),
						taskSort: this.#getParam('taskSort'),
						syncScript: this.#getParam('syncScript'),
						permissions: this.#getParam('permissions'),
					});
				}
			}

			this.#settings.subscribe('realAll', () => {
				if (this.#counters)
				{
					this.#counters.readAll();
				}
			});
		}

		this.#settings.show();
	}
}
