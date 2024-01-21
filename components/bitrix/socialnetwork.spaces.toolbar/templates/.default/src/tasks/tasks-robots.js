import { Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

import '../css/robots.css';

type Params = {
	groupId: number,
	isTaskLimitsExceeded: boolean,
	canUseAutomation: boolean,
	sourceAnalytics: string,
}

export class TasksRobots extends EventEmitter
{
	#groupId: number;
	#isTaskLimitsExceeded: boolean;
	#canUseAutomation: boolean;
	#sourceAnalytics: string;

	#sidePanelManager: BX.SidePanel.Manager;

	constructor(params: Params)
	{
		super(params);

		this.#groupId = params.groupId;
		this.#isTaskLimitsExceeded = params.isTaskLimitsExceeded;
		this.#canUseAutomation = params.canUseAutomation;
		this.#sourceAnalytics = params.sourceAnalytics;

		this.#sidePanelManager = BX.SidePanel.Instance;

		this.setEventNamespace('BX.Socialnetwork.Spaces.TasksRobots');
	}

	renderBtn(): HTMLElement
	{
		let className = 'tasks-scrum-robot-btn ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes ui-btn-no-caps ui-btn-themes ';

		if (this.#isShowLimitSidePanel())
		{
			className += ' ui-btn-icon-lock';
		}

		const node = Tag.render`
			<button class="${className}">
				${Loc.getMessage('SN_SPACES_TASKS_ROBOTS_BUTTON')}
			</button>
		`;

		Event.bind(node, 'click', this.#onClick.bind(this));

		return node;
	}

	#onClick()
	{
		if (this.#isShowLimitSidePanel())
		{
			BX.UI.InfoHelper.show('limit_tasks_robots', {
				isLimit: true,
				limitAnalyticsLabels: {
					module: 'tasks',
					source: this.#sourceAnalytics,
				},
			});
		}
		else
		{
			const url = `/bitrix/components/bitrix/tasks.automation/slider.php?site_id=${
				Loc.getMessage('SITE_ID')}&project_id=${this.#groupId}`;

			this.#sidePanelManager.open(url, {
				customLeftBoundary: 0,
				cacheable: false,
				loader: 'bizproc:automation-loader',
			});
		}
	}

	#isShowLimitSidePanel(): boolean
	{
		return (this.#isTaskLimitsExceeded && !this.#canUseAutomation);
	}
}
