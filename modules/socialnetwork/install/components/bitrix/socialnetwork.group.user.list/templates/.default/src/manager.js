import {Type} from 'main.core';
import {EventEmitter, BaseEvent} from 'main.core.events';
import ActionManager from './actionmanager';

export default class Manager
{
	static repo = new Map();

	static getById(id)
	{
		return Manager.repo.get(id);
	}

	constructor(
		params: {
			id: string,
			gridId: string,
			filterId: string,
			defaultFilterPresetId: string,
			defaultCounter: string,
			gridContainerId: string,
			componentName: string,
			signedParameters: string,
			useSlider: boolean,
			urls: {
				users: string,
				requests: string,
				requestsOut: string,
			},
		}
	)
	{
		this.id = params.id;
		this.componentName = params.componentName;
		this.signedParameters = params.signedParameters;
		this.useSlider = params.useSlider;
		this.gridId = params.gridId;
		this.filterId = (Type.isStringFilled(params.filterId) ? params.filterId : null);
		this.defaultFilterPresetId = (
			Type.isStringFilled(params.defaultFilterPresetId)
			&& ![ 'requests_in', 'requests_out' ].includes(params.defaultFilterPresetId)
				? params.defaultFilterPresetId
				: 'tmp_filter'
		);
		this.defaultCounter = (Type.isStringFilled(params.defaultCounter) ? params.defaultCounter : '');
		this.gridContainer = (Type.isStringFilled(params.gridContainerId) ? document.getElementById(params.gridContainerId) : null);
		this.urls = params.urls;

		this.actionManagerInstance = new ActionManager({
			parent: this,
		});

		Manager.repo.set(this.id, this);

		this.init();
	}

	init()
	{
		this.initEvents();
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
				sliderEvent.getEventId() === 'sonetGroupEvent'
				&& !Type.isUndefined(sliderEvent.data)
				&& Type.isStringFilled(sliderEvent.data.code)
				&& [ 'afterInvite', 'afterIncomingRequestCancel' ].includes(sliderEvent.data.code)
			)
			{
				this.reload();
			}
		});

		EventEmitter.subscribe('Socialnetwork.Toolbar:onItem', (event: BaseEvent) => {
			const data = event.getData();

			if (data.counter)
			{
				if (
					data.counter.filter
					&& data.counter.filterPresetId
					&& data.counter.filter.filterManager
				)
				{
					const filterApi = data.counter.filter.getFilter().getApi();
					const filterManager = data.counter.filter.filterManager;

					filterApi.setFilter({
						preset_id: (
							filterManager.getPreset().getCurrentPresetId() === data.counter.filterPresetId
								? this.defaultFilterPresetId
								: data.counter.filterPresetId
						),
					});
				}
				else if (Type.isStringFilled(data.counter.type))
				{
					let url = '';

					if (data.counter.type === this.defaultCounter)
					{
						url = this.urls.users;
					}
					else
					{
						switch (data.counter.type)
						{
							case 'workgroup_requests_in':
								url = this.urls.requests;
								break;
							case 'workgroup_requests_out':
								url = this.urls.requestsOut;
								break;
							default:
						}
					}

					if (Type.isStringFilled(url))
					{
						window.location = url.replace('#group_id#', this.g);
					}
				}
			}
		});

		const filterInstance = BX.Main.filterManager.getById(this.filterId);
		if (filterInstance)
		{
			const filterEmitter = filterInstance.getEmitter();
			const filterApi = filterInstance.getApi();

			filterEmitter.subscribe('init', (event) => {
				const { field } = event.getData();
				if (field.id === 'INITIATED_BY_TYPE')
				{
					field.subscribe('change', () => {

						if (Type.isStringFilled(field.getValue()))
						{
							const filterFieldsValues = filterInstance.getFilterFieldsValues();

							if (Type.isStringFilled(filterFieldsValues.INITIATED_BY_TYPE))
							{
								if (JSON.stringify(Object.values(filterFieldsValues.ROLE)) === JSON.stringify([ 'Z' ]))
								{
									return;
								}

								filterFieldsValues.ROLE = { 0: 'Z' };
								filterApi.setFields(filterFieldsValues);
							}
						}
					});
				}
			});

			EventEmitter.subscribe('BX.Grid.SettingsWindow:save', (event: BaseEvent) => {
				const [ settingsWindow ] = event.getData();
				if (!settingsWindow || !settingsWindow.parent || settingsWindow.parent.getId() !== this.gridId)
				{
					return;
				}

				filterApi.setFilter({
					preset_id: this.defaultFilterPresetId,
				});
			});
		}
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
		});
	}

	processClickEvent(e)
	{
		const targetNode = e.target;

		if (!targetNode.classList.contains('sonet-group-user-grid-action'))
		{
			return;
		}

		const action = targetNode.getAttribute('data-bx-action');

		if (action === 'disconnectDepartment')
		{
			const departmentId = Number(targetNode.getAttribute('data-bx-department'));
			if (departmentId > 0)
			{
				this.getActionManager().disconnectDepartment({
					id: departmentId,
				});
			}

			return;
		}

		let userId = targetNode.getAttribute('data-bx-user-id');

		if (
			!Type.isStringFilled(action)
			|| !Type.isStringFilled(userId)
		)
		{
			return;
		}

		userId = parseInt(userId);
		if (userId <= 0)
		{
			return;
		}

		targetNode.classList.add('--inactive');

		this.actionManagerInstance.act({
			action: action,
			userId: userId,
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

}
