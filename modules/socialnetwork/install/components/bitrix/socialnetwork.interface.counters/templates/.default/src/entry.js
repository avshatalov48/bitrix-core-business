import {Loc, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {PULL as Pull} from 'pull.client';
import {CounterPanel} from 'ui.counterpanel';
import {CounterColor} from 'ui.cnt';

import {Filter} from './counters-helper';

export class Counters
{
	static updateTimeout = false;
	static needUpdate = false;

	static timeoutTTL = 5000;

	constructor(options)
	{
		this.userId = options.userId;
		this.targetUserId = options.targetUserId;
		this.entityType = options.entityType || '';
		this.entityId = parseInt(options.entityId || 0);
		this.role = options.role;
		this.entityTitle = options.entityTitle || '';
		this.counters = options.counters;
		this.renderTo = options.renderTo;

		this.signedParameters = options.signedParameters;
		this.initialCounter = options.initialCounter || '';

		this.panel = null;
		this.tasksCounter = {};

		this.bindEvents();
		this.setData(this.counters);

		this.initPull();

		this.filter = new Filter({
			filterId: options.filterId,
			countersManager: this,
		});
	}

	isWorkgroupList()
	{
		return false;
	}

	initPull()
	{
		Pull.subscribe({
			moduleId: 'main',
			callback: data => this.processPullEvent(data),
		});

		Pull.subscribe({
			moduleId: 'socialnetwork',
			callback: data => this.processPullEvent(data),
		});

		Pull.subscribe({
			moduleId: 'tasks',
			callback: data => this.processPullEvent(data),
		});
	}

	extendWatch()
	{
		if (this.isWorkgroupList())
		{
			const tagId = 'WORKGROUP_LIST';

			BX.PULL.extendWatch(tagId, true);
			setTimeout(() => this.extendWatch(), 29 * 60 * 1000);
		}
	}

	processPullEvent(data)
	{
		const eventHandlers = {
			user_counter: this.onUserCounter.bind(this),
		};
		const has = Object.prototype.hasOwnProperty;
		const {command, params} = data;
		if (has.call(eventHandlers, command))
		{
			const method = eventHandlers[command];
			if (method)
			{
				method.apply(this, [params]);
			}
		}
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	}

	onFilterApply()
	{
		this.filter.updateFields();
	}

	activateCountersByFilter()
	{
		if (!this.panel)
		{
			return;
		}

		this.counterItems.forEach((counter) => {
			if (Type.isObject(counter.filterFields))
			{
				this.filter.isFilteredByFields(counter.filterFields)
					? this.panel.getItemById(counter.type).activate()
					: this.panel.getItemById(counter.type).deactivate()
				;
			}
			else if (Type.isStringFilled(counter.filterPresetId))
			{
				this.filter.isFilteredByPresetId(counter.filterPresetId)
					? this.panel.getItemById(counter.type).activate()
					: this.panel.getItemById(counter.type).deactivate()
				;
			}
		});
	}

	onUserCounter(data)
	{
		const has = Object.prototype.hasOwnProperty;

		if (this.entityType === 'workgroup_detail')
		{
			if (
				!has.call(data, 'workgroupId')
				|| !has.call(data, 'values')
				|| this.entityId !== parseInt(data.workgroupId)
				|| this.userId !== Number(data.userId)
			)
			{
				return;
			}

			Object.entries(data.values).forEach(([type, value]) => {
				this.counterItems.forEach((counter) => {
					if (counter.type === type)
					{
						const item = this.panel.getItemById(counter.type);
						item.updateValue(value.all);

						let baseColor = 'GRAY';
						switch (type)
						{
							case 'workgroup_requests_in':
								baseColor = 'DANGER';
								break;
							case 'workgroup_requests_out':
								baseColor = 'SUCCESS';
								break;
						}

						item.updateColor(value.all > 0 ? baseColor : 'GRAY');
					}
				})
			});
		}
		else if (this.entityType === 'workgroup_list')
		{
			if (
				Type.isPlainObject(data[Loc.getMessage('SITE_ID')])
				&& !Type.isUndefined(data[Loc.getMessage('SITE_ID')]['**SG0'])
			)
			{
				this.counterItems.forEach((counter) => {
					if (counter.type === 'workgroup_list_livefeed')
					{
						const item = this.panel.getItemById(counter.type);
						const value = Number(data[Loc.getMessage('SITE_ID')]['**SG0']);
						item.updateValue(value);
						item.updateColor(value > 0 ? 'DANGER' : 'GRAY');
					}
				})
			}
			else if (
				!Type.isUndefined(data.projects_major)
				|| !Type.isUndefined(data.scrum_total_comments)
			)
			{
				if (!Type.isUndefined(data.projects_major))
				{
					this.tasksCounter.projects_major = Number(data.projects_major)
				}

				if (!Type.isUndefined(data.scrum_total_comments))
				{
					this.tasksCounter.scrum_total_comments = Number(data.scrum_total_comments)
				}

				this.counterItems.forEach((counter) => {
					if (counter.type === 'workgroup_list_tasks')
					{
						let sum = 0;
						Object.entries(this.tasksCounter).map(([key]) => {
							sum += this.tasksCounter[key];
						})
						const item = this.panel.getItemById(counter.type);

						item.updateValue(sum);
						item.updateColor(sum > 0 ? 'DANGER' : 'GRAY');
					}
				})
			}
		}
	}

	getCounterItem(param: Object): Object
	{
		const counterData = {
			type: param.type,
			activeByDefault: (param.type === this.initialCounter),
			filter: this.filter,
		};
		if (Type.isObject(param.filterFields))
		{
			const [key, value] = Object.entries(param.filterFields).pop();
			counterData.filterField = key;
			counterData.filterValue = value;
		}
		else if (Type.isStringFilled(param.filterPresetId))
		{
			counterData.filterPresetId = param.filterPresetId;
		}

		return {
			id: param.type,
			title: param.name,
			value: param.count,
			color: param.color,
			eventsForActive: {
				click: () => {
					EventEmitter.emit('Socialnetwork.Toolbar:onItem', {
						counter: counterData,
					});
				},
				mouseenter: () => {},
				anyEvent: () => {},
			},
			eventsForUnActive: {
				click: () => {
					EventEmitter.emit('Socialnetwork.Toolbar:onItem', {
						counter: counterData,
					});
				},
			},
		};
	}

	setData(counters)
	{
		this.counterItems = [];

		Object.entries(counters).forEach(([type, data]) => {
			this.counterItems.push({
				type: type,
				name: data.TITLE,
				count: this.getCounterSum(data.VALUE),
				color: data.STYLE,
				filterPresetId: data.FILTER_PRESET_ID,
				filterFields: data.FILTER_FIELDS,
			});
		});
	}

	getCounterSum(counterValues)
	{
		let result = 0;
		Object.entries(counterValues).map(([, value]) => {

			result += Number(value);
		});

		return result;
	}

	render()
	{
		this.panel = new CounterPanel({
			target: this.renderTo,
			multiselect: true,
			items: this.counterItems.map((item) => this.getCounterItem(item)),
		});

		this.panel.init();
	}
}