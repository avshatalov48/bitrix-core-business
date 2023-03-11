import {Loc, Type} from 'main.core';

export class PullControllerTasksUserCounter
{
	constructor(options)
	{
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;

		this.gridController = options.gridController;
		this.pullController = options.pullController;

		this.gridPinController = this.gridController.getInstance().getPinController();
		this.grid = this.gridController.getGrid();

		this.userCounterManager = options.userCounterManager;

		this.timer = null;
		this.queueCounterData = new Map();
	}

	getModuleId()
	{
		return 'tasks';
	}

	getMap()
	{
		return {
			[ Loc.getMessage('PUSH_EVENT_TASKS_USER_COUNTER') ]: this.onUserCounter.bind(this),
			[ Loc.getMessage('PUSH_EVENT_TASKS_PROJECT_READ_ALL') ]: this.onProjectReadAllComments.bind(this),
			[ Loc.getMessage('PUSH_EVENT_TASKS_SCRUM_READ_ALL') ]: this.onScrumReadAllComments.bind(this),
		};
	}

	onUserCounter(data)
	{
		if (!this.timer)
		{
			this.timer = setTimeout(() => {
				this.freeCounterQueue();
			}, 1000);
		}

		Object.entries(data).forEach(([ key, value]) => {
			if (
				key == Number(key)
				&& Number(key) > 0
				&& Type.isPlainObject(value.view_all)
			)
			{
				if (!Type.isUndefined(value.view_all.new_comments))
				{
					this.queueCounterData.set(`${key}_new_comments`, {
						groupId: key,
						type: 'tasks_new_comments',
						value: Number(value.view_all.new_comments),
					});
				}

				if (!Type.isUndefined(value.view_all.expired))
				{
					this.queueCounterData.set(`${key}_expired`, {
						groupId: key,
						type: 'tasks_expired',
						value: Number(value.view_all.expired),
					});
				}
			}
		});
	}

	onProjectReadAllComments(data)
	{
		if (
			Number(data.GROUP_ID) !== 0
			&& Number(data.USER_ID) !== Number(Loc.getMessage('USER_ID'))
		)
		{
			return;
		}

		if (!this.timer)
		{
			this.timer = setTimeout(() => {
				this.freeCounterQueue();
			}, 1000);
		}

		this.queueCounterData.set(`0_new_comments`, {
			groupId: 0,
			type: 'tasks_new_comments',
			value: 0,
			scrum: 'N',
		});
	}

	onScrumReadAllComments(data)
	{
		if (
			Number(data.GROUP_ID) !== 0
			&& Number(data.USER_ID) !== Number(Loc.getMessage('USER_ID'))
		)
		{
			return;
		}

		if (!this.timer)
		{
			this.timer = setTimeout(() => {
				this.freeCounterQueue();
			}, 1000);
		}

		this.queueCounterData.set(`0_new_comments`, {
			groupId: 0,
			type: 'tasks_new_comments',
			value: 0,
			scrum: 'Y',
		});
	}

	freeCounterQueue()
	{
		this.queueCounterData.forEach((counterData) => {
			// todo oh this.userCounterManager.processCounterItem(counterData, Number(counterData.groupId));
		});

		this.queueCounterData.clear();
		this.timer = null;
	}

}