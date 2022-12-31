import {Loc} from 'main.core';

export class PullControllerTasks
{

	static get events()
	{
		return {
			pinChanged: 'pinChanged',
		};
	}

	static get counterEvents()
	{
		return [
			'onAfterTaskAdd',
			'onAfterTaskDelete',
			'onAfterTaskRestore',
			'onAfterTaskView',
			'onAfterTaskMute',
			'onAfterCommentAdd',
			'onAfterCommentDelete',
			'onProjectPermUpdate',
		];
	}

	static get movingProjectEvents()
	{
		return [
			'onAfterTaskAdd',
			'onAfterCommentAdd',
		];
	}

	constructor(options)
	{
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;

		this.gridController = options.gridController;
		this.pullController = options.pullController;

		this.gridPinController = this.gridController.getInstance().getPinController();
		this.grid = this.gridController.getGrid();

		this.timer = null;
		this.counterData = new Map();
	}

	getModuleId()
	{
		return 'tasks';
	}

	getMap()
	{
		return {
			[ Loc.getMessage('PUSH_EVENT_TASKS_PROJECT_COUNTER') ]: this.onTasksProjectCounter.bind(this),
			[ Loc.getMessage('PUSH_EVENT_TASKS_PROJECT_READ_ALL') ]: this.onTasksProjectCommentsReadAll.bind(this),
			[ Loc.getMessage('PUSH_EVENT_TASKS_SCRUM_READ_ALL') ]: this.onTasksProjectCommentsReadAll.bind(this),
			[ Loc.getMessage('PUSH_EVENT_TASKS_COMMENT_READ_ALL') ]: this.onTasksProjectCommentsReadAll.bind(this),
		};
	}

	onTasksProjectCounter(data)
	{
		const groupId = data.GROUP_ID;
		const event = data.EVENT;

		if (!PullControllerTasks.counterEvents.includes(event))
		{
			return;
		}

		if (!this.timer)
		{
			this.timer = setTimeout(() => {
				this.freeCounterQueue();
			}, 1000);
		}

		if (PullControllerTasks.movingProjectEvents.includes(event) || !this.counterData.has(groupId))
		{
			this.counterData.set(groupId, event);
		}
	}

	freeCounterQueue()
	{
		this.counterData.forEach((event, groupId) => {

			const params = {
				event,
				highlightParams: {
					skip: true,
				},
				updateItemCondition: (event) => {
					return PullControllerTasks.movingProjectEvents.includes(event);
				},
			};

			if (PullControllerTasks.movingProjectEvents.includes(event))
			{
				params.moveParams = {
					rowBefore: (
						this.gridPinController.getIsPinned(groupId)
							? 0
							: this.gridPinController.getLastPinnedRowId()
					),
					rowAfter: this.gridController.getInstance().getFirstRowId(),
				};
				params.highlightParams = {
					skip: false,
				};
			}

			this.pullController.checkExistence(groupId).then(
				response => this.pullController.onCheckExistenceSuccess(response, groupId, params),
				response => console.error(response)
			);
		});
		this.counterData.clear();
		this.timer = null;
	}

	onTasksProjectCommentsReadAll(data)
	{
		const groupId = data.GROUP_ID;

		if (groupId)
		{
			if (this.gridController.getInstance().isRowExist(groupId))
			{
				this.updateCounter([groupId]);
			}
		}
		else
		{
			this.updateCounter(this.gridController.getInstance().getItems());
		}
	}

	updateCounter(rowIds)
	{
		this.pullController.checkExistence(rowIds).then(
			response => {
				const projects = response.data;
				if (projects)
				{
					Object.keys(projects).forEach((projectId) => {
						if (this.gridController.getInstance().isRowExist(projectId))
						{
							this.gridController.getInstance()
								.getRowById(projectId)
								.setCounters(projects[projectId].counters)
							;
						}
					});
				}
			},
			response => console.error(response)
		);
	}
}