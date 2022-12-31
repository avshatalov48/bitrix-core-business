import {ajax, Type} from 'main.core';
import {PULL as Pull} from 'pull.client';

import {UserCounterManager} from './usercounter';
import {PullControllerSocialnetwork} from './pull/socialnetwork';
import {PullControllerMainUserCounter} from './pull/mainusercounter';
import {PullControllerTasks} from './pull/tasks';
import {PullControllerTasksUserCounter} from './pull/tasksusercounter';

export class PullManager
{
	constructor(options)
	{
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;
		this.urls = options.urls;
		this.useTasksCounters = options.useTasksCounters;
		this.livefeedCounterColumnId = options.livefeedCounterColumnId;
		this.livefeedCounterSliderOptions = options.livefeedCounterSliderOptions;

		this.gridController = options.gridController;
		this.grid = this.gridController.getGrid();

		this.init();
	}

	init()
	{
		this.userCounterManagerInstance = new UserCounterManager({
			gridController: this.gridController,
			url: this.urls.groupLivefeedUrl,
			columnId: this.livefeedCounterColumnId,
			sliderOptions: this.livefeedCounterSliderOptions,
			useTasksCounters: this.useTasksCounters,
		});

		this.pullControllerSocialnetwork = new PullControllerSocialnetwork({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			gridController: this.gridController,
			userCounterManager: this.userCounterManagerInstance,
			pullController: this,
		});

		Pull.subscribe(this.pullControllerSocialnetwork);

		this.pullControllerMainUserCounter = new PullControllerMainUserCounter({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			userCounterManager: this.userCounterManagerInstance,
		});

		Pull.subscribe(this.pullControllerMainUserCounter);

		this.pullControllerTasksUserCounter = new PullControllerTasksUserCounter({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			gridController: this.gridController,
			userCounterManager: this.userCounterManagerInstance,
		});

		Pull.subscribe(this.pullControllerTasksUserCounter);

		if (this.useTasksCounters)
		{
			this.pullControllerTasks = new PullControllerTasks({
				componentName: this.componentName,
				signedParameters: this.signedParameters,
				gridController: this.gridController,
				pullController: this,
			});

			Pull.subscribe(this.pullControllerTasks);
		}
	}

	checkExistence(groupId)
	{
		return new Promise((resolve, reject) => {
			ajax.runComponentAction(this.componentName, 'checkExistence', {
				mode: 'class',
				data: {
					groupIdList: Type.isArray(groupId) ? groupId : [ groupId ],
				},
				signedParameters: this.signedParameters,
			}).then(
				response => resolve(response),
				response => reject(response)
			);
		});
	}

	onCheckExistenceSuccess(response, groupId, params)
	{
		if (Type.isUndefined(response.data[groupId]))
		{
			return;
		}

		if (response.data[groupId] === false)
		{
			this.grid.removeRow(groupId);
			return;
		}

		this.moveToDirectPlace(groupId, response.data[groupId], params);
	}

	isActivityRealtimeMode()
	{
		const sort = this.gridController.getInstance().getSort();

		return (
			sort.DATE_UPDATE
			&& sort.DATE_UPDATE === 'desc'
		);
	}

	updateItem(rowId, rowData, params)
	{
		if (!this.gridController.getInstance().hasItem(rowId))
		{
			this.gridController.getInstance().addItem(rowId);
			this.addRow(rowId, rowData, params);
		}
		else
		{
			this.updateRow(rowId, rowData, params);
		}
	}

	addRow(rowId, rowData, params)
	{
		if (
			this.gridController.getInstance().isRowExist(rowId)
			|| Type.isUndefined(rowData)
			|| Type.isNull(rowData)
		)
		{
			return;
		}

		this.gridController.getInstance().addRow(rowId, rowData, params);
	}

	updateRow(rowId, rowData, params)
	{
		if (
			!this.gridController.getInstance().isRowExist(rowId)
			|| Type.isUndefined(rowData)
		)
		{
			return;
		}

		this.gridController.getInstance().updateRow(rowId, rowData, params);
	}

	removeRow(rowId)
	{
		if (!this.gridController.getInstance().isRowExist(rowId))
		{
			return;
		}

		this.gridController.getInstance().removeRow(rowId);
	}

	moveToDirectPlace(groupId, data, params)
	{
		params = params || {};

		ajax.runComponentAction(this.componentName, 'findWorkgroupPlace', {
			mode: 'class',
			data: {
				groupId,
				currentPage: this.gridController.getInstance().getCurrentPage(),
			},
			signedParameters: this.signedParameters,
		}).then((response) => {
			if (response.data === null)
			{
				return;
			}

			const {workgroupBefore, workgroupAfter} = response.data;

			if (workgroupBefore === false && workgroupAfter === false)
			{
				this.removeRow(groupId);
			}
			else
			{
				if (
					(workgroupBefore && this.gridController.getInstance().isRowExist(workgroupBefore))
					|| (workgroupAfter && this.gridController.getInstance().isRowExist(workgroupAfter))
				)
				{
					params.moveParams = {
						rowBefore: workgroupBefore,
						rowAfter: workgroupAfter,
					};
				}
				else
				{
					params.moveParams = {
						skip: true,
					};
				}

				this.updateItem(groupId, data, params);
			}
		});
	}
}
