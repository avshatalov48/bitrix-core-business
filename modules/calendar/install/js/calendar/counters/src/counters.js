import { Loc } from 'main.core';
import { CounterPanel } from 'ui.counterpanel';
import { EventEmitter } from 'main.core.events';

export class Counters extends CounterPanel
{
	static TYPE_INVITATION = 'invitation';
	static TYPE_GROUP_INVITATION_TPL = 'calendar_group_invites_\\d';

	constructor(options)
	{
		super({
			target: options.countersWrap,
			items: Counters.getCountersValue(options.counters),
			multiselect: false,
		});

		this.search = options.search;
		this.userId = options.userId;
		this.counters = options.counters;
		this.countersWrap = options.countersWrap;

		this.bindEvents();
	}

	bindEvents(): void
	{
		EventEmitter.subscribe('BX.UI.CounterPanel.Item:activate', this.onActivateItem.bind(this));
		EventEmitter.subscribe('BX.UI.CounterPanel.Item:deactivate', this.onDeactivateItem.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	}

	onActivateItem(event): void
	{
		const item = event.getData();
		const shouldSetPreset = item.id === Counters.TYPE_INVITATION
			|| Counters.checkCounterIsForGroup(item.id)
		;

		if (shouldSetPreset)
		{
			this.search.setPresetInvitation();
		}
	}

	onDeactivateItem(): void
	{
		this.search.resetPreset();
	}

	recalculateCounters(): void
	{
		Object.entries(this.counters).forEach(([code, data]): void => {
			const item = this.getItemById(code);
			item?.updateValue(data.value);
			item?.updateColor(data.color);
		});
	}

	markCounters(): void
	{
		Object.entries(this.counters).forEach(([code, data]): void => {
			const item = this.getItemById(code);

			if (
				item.id === 'invitation'
				|| item.id.includes('calendar_group_invites_')
			)
			{
				this.fields.MEETING_STATUS === 'Q'
					? item.activate(false)
					: item.deactivate(false)
				;
			}
		});
	}

	setCountersValue(counters): void
	{
		this.counters = counters;
		this.recalculateCounters();
	}

	onFilterApply(): void
	{
		this.fields = this.search.filter.getFilterFieldsValues();
		this.markCounters();
	}

	static checkCounterIsForGroup(counterName: string): boolean
	{
		return new RegExp(Counters.TYPE_GROUP_INVITATION_TPL).test(counterName);
	}

	static getCountersValue(counters): Object
	{
		return Object.entries(counters).map(([code, item]) => {
			return {
				id: code,
				title: Counters.getCountersName(code),
				value: item.value,
				color: item.color,
			};
		});
	}

	static getCountersName(type: string): string | null
	{
		if (
			type === Counters.TYPE_INVITATION
			|| Counters.checkCounterIsForGroup(type)
		)
		{
			return Loc.getMessage('EC_COUNTER_INVITATION');
		}

		return null;
	}

	static getCounterNameByGroupId(groupId: number): string
	{
		return Counters.TYPE_GROUP_INVITATION_TPL.replace('\\d', groupId);
	}
}
