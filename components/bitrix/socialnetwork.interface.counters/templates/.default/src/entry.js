import {Dom, Tag} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {PULL as Pull} from 'pull.client';

import {Filter} from './counters-helper';
import CountersItem from './counters-item';

import './style.css'

export class Counters
{
	static get counterTypes()
	{
		return {
			workgroup_detail: [
				'workgroup_requests_in',
				'workgroup_requests_out',
			],
		};
	}

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
		this.initialCounterTypes = options.counterTypes;
		this.renderTo = options.renderTo;

		this.signedParameters = options.signedParameters;
		this.initialCounter = options.initialCounter || '';

		this.$other = {
			cropped: null,
			layout: null
		};
		this.$entityHead = null;

		this.filter = new Filter({
			filterId: options.filterId,
		});

		this.bindEvents();
		this.setData(this.counters);

		this.initPull();
	}

	isWorkgroupList()
	{
		return false;
	}

	initPull()
	{
		Pull.subscribe({
			moduleId: 'socialnetwork',
			callback: data => this.processPullEvent(data),
		});

	}

	extendWatch()
	{
		if (this.isWorkgroupList())
		{
			let tagId = 'WORKGROUP_LIST';

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

		Object.values(this.counterItems).forEach((counter) => {
			if (counter)
			{
				this.filter.isFilteredByPresetId(counter.filterPresetId)
					? counter.active()
					: counter.unActive()
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
				if (this.counterItems[type])
				{
					this.counterItems[type].updateCount(value);
				}
			});
		}
	}

	getCounterItem(param: Object): Object
	{
		return new CountersItem({
			count: param.count,
			name: param.name,
			type: param.type,
			color: param.color,
			filterPresetId: param.filterPresetId,
			activeByDefault: (param.type === this.initialCounter),
			filter: this.filter,
		});
	}

	setData(counters)
	{
		this.counterItems = {};

		const availableTypes = [
			...Counters.counterTypes.workgroup_detail,
		];

		Object.entries(counters).forEach(([type, data]) => {
			if (!availableTypes.includes(type))
			{
				return;
			}

			this.counterItems[type] = this.getCounterItem({
				type,
				name: data.TITLE,
				count: Number(data.VALUE),
				color: data.STYLE,
				filterPresetId: data.FILTER_PRESET_ID,
			});
		});
	}

	isCroppedBlock(node: HTMLElement)
	{
		if (node)
		{
			return node.classList.contains('--cropp');
		}
	}

	getContainer()
	{
		const content = [];
		Object.values(this.counterItems).forEach(counter => content.push(counter.getContainer()));

		this.$entityHead = Tag.render`
			<div class="sonet-counters--group-head">
				${this.entityTitle}
			</div>
		`;

		this.$element = Tag.render`
			<div class="sonet-counters sonet-counters--scope">
				<div class="sonet-counters--group">
					${this.$entityHead}
					<div class="sonet-counters--group-content">${content}</div>
				</div>
			</div>
		`;

		return this.$element;
	}

	render()
	{
		const node = this.getContainer();
		const fakeNode = node.cloneNode(true);
		fakeNode.classList.add('sonet-counters');
		fakeNode.style.position = 'fixed';
		fakeNode.style.opacity = '0';
		fakeNode.style.width = 'auto';
		fakeNode.style.pointerEvents = 'none';
		document.body.appendChild(fakeNode);
		this.nodeWidth = fakeNode.offsetWidth;
		document.body.removeChild(fakeNode);

		Dom.replace(this.renderTo.firstChild, node);
	}
}