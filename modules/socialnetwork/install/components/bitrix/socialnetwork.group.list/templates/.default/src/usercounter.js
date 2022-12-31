import {Type} from 'main.core';

export class UserCounterManager
{
	constructor(options)
	{
		this.gridController = options.gridController;
		this.url = options.url;
		this.columnId = options.columnId;
		this.sliderOptions = options.sliderOptions;
		this.useTasksCounters = options.useTasksCounters;

		this.timer = null;
		this.queueCounterData = new Map();
	}

	processCounterItem(counterData, groupId)
	{
		if (this.useTasksCounters)
		{
			return;
		}

		if (
			groupId === 0
			&& Number(counterData.value) === 0
		)
		{
			this.gridController.getInstance().getRows().getRows().forEach((targetRow) => {

				if (
					!Type.isUndefined(counterData.scrum)
					&& counterData.scrum !== targetRow.getNode().getAttribute('data-scrum')
				)
				{
					return;
				}

				this.setRowCounter(targetRow, counterData, groupId);
			});

			return;
		}

		if (!this.gridController.getInstance().isRowExist(groupId))
		{
			return;
		}

		this.setRowCounter(this.gridController.getInstance().getRowById(groupId), counterData, groupId);
	}


	setRowCounter(targetRow, counterData, groupId)
	{
		const rowCounterData = {};

		const url = this.url
			.replace('#id#', groupId)
			.replace('#ID#', groupId)
			.replace('#GROUP_ID#', groupId)
			.replace('#group_id#', groupId);

		rowCounterData[this.columnId] = {
			value: 0,
			type: 'right',
			events: (
				groupId > 0
					? {
						click: BX.SidePanel.Instance.open.bind(BX.SidePanel.Instance, url, this.sliderOptions),
					}
					: {}
			),
			color: 'ui-counter-danger',
			class: 'sonet-ui-grid-counter',
		};

		let storedCounterData = {};

		try
		{
			eval(`storedCounterData = ${targetRow.getNode().getAttribute('data-counters')};`);
		}
		catch(e)
		{

		}

		if (
			storedCounterData === null
			|| (
				counterData.type === 'tasks_expired'
				&& targetRow.getNode().getAttribute('data-scrum') === 'Y'
			)
		)
		{
			return;
		}

		let sumValue = 0;
		Object.entries(storedCounterData).forEach(([ key]) =>
		{
			if (key === counterData.type)
			{
				storedCounterData[key] = counterData.value.toString();
			}

			sumValue += Number(storedCounterData[key]);
		});

		targetRow.getNode().setAttribute('data-counters', `(${JSON.stringify(storedCounterData)})`);
		rowCounterData[this.columnId].value = Number(sumValue);
		targetRow.setCounters(rowCounterData);
	}

}
