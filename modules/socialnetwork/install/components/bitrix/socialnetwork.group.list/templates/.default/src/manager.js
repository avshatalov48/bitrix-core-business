import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import {
	Controller as GridController,
	ActionController as GridActionController,
	TagController as GridTagController,
	Filter as GridFilter,
} from 'socialnetwork.ui.grid';

import {Tour as TasksTour} from 'tasks.tour';

import {ActionManager} from './actionmanager';
import {PullManager} from './pull';

export default class Manager
{
	static repo = new Map();

	static getById(id)
	{
		return Manager.repo.get(id);
	}

	constructor(params)
	{
		this.id = params.id;
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.useSlider = params.useSlider;
		this.gridId = params.gridId;
		this.filterId = (Type.isStringFilled(params.filterId) ? params.filterId : null);
		this.sort = params.sort;
		this.items = params.items;
		this.pageSize = (Type.isNumber(params.pageSize) ? parseInt(params.pageSize) : 10);
		this.gridStub = (!Type.isUndefined(params.gridStub) ? params.gridStub : null);
		this.defaultFilterPresetId = (
			Type.isStringFilled(params.defaultFilterPresetId)
			&& ![ 'requests_in', 'requests_out' ].includes(params.defaultFilterPresetId)
				? params.defaultFilterPresetId
				: 'tmp_filter'
		);
		this.defaultCounter = (Type.isStringFilled(params.defaultCounter) ? params.defaultCounter : '');
		this.gridContainer = (Type.isStringFilled(params.gridContainerId) ? document.getElementById(params.gridContainerId) : null);
		this.urls = params.urls;
		this.useTasksCounters = (Type.isBoolean(params.useTasksCounters) ? params.useTasksCounters : false);

		this.livefeedCounterColumnId = (Type.isStringFilled(params.livefeedCounterColumnId) ? params.livefeedCounterColumnId : 'NAME');
		this.livefeedCounterSliderOptions = (Type.isPlainObject(params.livefeedCounterSliderOptions) ? params.livefeedCounterSliderOptions : {});

		this.init(params);
	}

	init(params)
	{
		this.initEvents();

		this.actionManagerInstance = new ActionManager({
			parent: this,
		});

		this.gridController = new GridController({
			id: this.id,
			sort: this.sort,
			items: this.items,
			pageSize: this.pageSize,
			gridStub: this.gridStub,
			componentName: this.componentName,
			signedParameters: this.signedParameters,
		});

		Manager.repo.set(this.id, this);

		GridActionController.setOptions({
			gridInstance: this.gridController.getGrid(),
			componentName: this.componentName,
			signedParameters: this.signedParameters,
		});

		const filter = new GridFilter({
			filterId: this.filterId,
			defaultFilterPresetId: this.defaultFilterPresetId,
			gridId: this.id,
			signedParameters: this.signedParameters,
		});

		if (this.useTasksCounters)
		{
			this.tasksTour = new TasksTour({
				tours: params.tours,
			});
			this.tasksTour.subscribe('FirstProject:afterProjectCreated', this.onAfterGroupCreated.bind(this));
			this.tasksTour.subscribe('FirstScrum:afterScrumCreated', this.onAfterGroupCreated.bind(this));
		}

		this.initPull();

		if (filter)
		{
			GridTagController.setOptions({
				filter,
			});
		}
	}

	initPull()
	{
		this.pullManager = new PullManager({
			componentName: this.componentName,
			signedParameters: this.signedParameters,
			urls: this.urls,
			livefeedCounterColumnId: this.livefeedCounterColumnId,
			livefeedCounterSliderOptions: this.livefeedCounterSliderOptions,
			gridController: this.gridController,
			useTasksCounters: this.useTasksCounters,
		});
	}

	setActionsPanel(actionPanel)
	{
		GridActionController.setActionsPanel(actionPanel)
	}

	initEvents()
	{
		if (this.gridContainer)
		{
			this.gridContainer.addEventListener('click', this.processClickEvent.bind(this));
		}

		EventEmitter.subscribe('SidePanel.Slider:onMessage', (event: BaseEvent) => {

			const [ sliderEvent ] = event.getCompatData();

			if (
				!BX.PULL
				&& sliderEvent.getEventId() === 'sonetGroupEvent'
				&& !Type.isUndefined(sliderEvent.data)
				&& Type.isStringFilled(sliderEvent.data.code)
				&& [
					'afterCreate',
					'afterEdit',
					'afterInvite',
					'afterJoinRequestSend',
					'afterLeave',
					'afterDelete',
				].includes(sliderEvent.data.code)
			)
			{
				this.reload();
			}
		});

		EventEmitter.subscribe('Tasks.Toolbar:onItem', (event: BaseEvent) => {
			const data = event.getData();

			if (data.counter && data.counter.filter)
			{
				const filter = data.counter.filter.getFilter();

				this.toggleByField({
					[data.counter.filterField]: data.counter.filterValue,
				}, filter);
			}
		});

		EventEmitter.subscribe('Socialnetwork.Toolbar:onItem', (event: BaseEvent) => {
			const data = event.getData();

			if (data.counter && data.counter.filter)
			{
				const filter = data.counter.filter.getFilter();

				this.toggleByField({
					[data.counter.filterField]: data.counter.filterValue,
				}, filter);
			}
		});
	}

	toggleByField(filterData, filter)
	{
		if (!filter)
		{
			return;
		}

		const name = Object.keys(filterData)[0];
		const value = filterData[name];
		const fields = filter.getFilterFieldsValues();

		if (!this.isFilteredByFieldValue(name, value, fields))
		{
			filter.getApi().extendFilter({[name]: value});
			return;
		}

		filter.getFilterFields().forEach((field) => {
			if (field.getAttribute('data-name') === name)
			{
				filter.getFields().deleteField(field);
			}
		});

		filter.getSearch().apply();
	}

	isFilteredByField(field, fields)
	{
		if (!Object.keys(fields).includes(field))
		{
			return false;
		}

		if (Type.isArray(fields[field]))
		{
			return fields[field].length > 0;
		}

		return fields[field] !== '';
	}

	isFilteredByFieldValue(field, value, fields)
	{
		return this.isFilteredByField(field, fields) && fields[field] === value;
	}

	reload()
	{
		const gridInstance = BX.Main.gridManager.getInstanceById(this.gridId);
		if (!gridInstance)
		{
			return;
		}

		gridInstance.reloadTable('POST', {
			apply_filter: 'Y',
		}, () => {
			this.gridController.getInstance().getPinController().colorPinnedRows();
		});
	}

	processClickEvent(e)
	{
		const targetNode = e.target;

		if (!targetNode.classList.contains('sonet-group-grid-action'))
		{
			return;
		}

		const action = targetNode.getAttribute('data-bx-action');

		let groupId = targetNode.getAttribute('data-bx-group-id');

		if (
			!Type.isStringFilled(action)
			|| !Type.isStringFilled(groupId)
		)
		{
			return;
		}

		groupId = parseInt(groupId);
		if (groupId <= 0)
		{
			return;
		}

		targetNode.classList.add('--inactive');

		this.actionManagerInstance.act({
			action: action,
			groupId: groupId,
		}).then(() => {
			targetNode.classList.remove('--inactive');
		}, () => {
			targetNode.classList.remove('--inactive');
		});

		e.preventDefault();
		e.stopPropagation();
	}

	getActionManager()
	{
		return this.actionManagerInstance;
	}

	onAfterGroupCreated(baseEvent: BaseEvent)
	{
		const projectId = baseEvent.getData();

		const isRowExist = this.gridController.getInstance().isRowExist(projectId);
		if (isRowExist)
		{
			const targetRow = this.gridController.getInstance().getRowNodeById(projectId);
			const target = targetRow.querySelector('.sonet-group-grid-name-text');

			this.tasksTour.showFinalStep(projectId, target);
		}
		else
		{
			EventEmitter.subscribeOnce(
				'SocialNetwork.Projects.Grid:RowAdd',
				this.onGridRowAdded.bind(this, projectId)
			);
		}
	}

	onGridRowAdded(projectId, baseEvent: BaseEvent)
	{
		const {id} = baseEvent.getData();

		if (Number(id) === Number(projectId))
		{
			const targetRow = this.gridController.getInstance().getRowNodeById(projectId);
			const target = targetRow.querySelector('.sonet-group-grid-name-text');

			this.tasksTour.showFinalStep(target);
		}
	}
}
