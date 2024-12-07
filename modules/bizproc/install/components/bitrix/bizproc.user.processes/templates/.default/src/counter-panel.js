import { Loc } from 'main.core';

type Counters = {
	task: number,
	comment: number,
}

export class CounterPanel
{
	filterId: string;
	counters: Counters;
	uiPanel;

	constructor(options: {
		filterId: string,
		counters: Counters,
	})
	{
		this.filterId = options.filterId;
		this.counters = options.counters;
	}

	renderTo(target: HTMLElement)
	{
		this.uiPanel = new BX.UI.CounterPanel({
			target,
			multiselect: false,
			title: Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_COUNTERS_LABEL'),
			items: [
				{
					id: 'task',
					value: {
						value: this.counters?.task || 0,
						order: 1,
					},
					title: {
						value: Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_COUNTERS_TASK'),
						order: 2,
					},
					color: this.#getTaskColor(this.counters?.task),
					separator: false,
					eventsForActive: {
						click: this.#setFilterPreset.bind(this),
					},
					eventsForUnActive: {
						click: this.#setFilterPreset.bind(this, 'active_task'),
					},
				},
				{
					id: 'comment',
					value: this.counters?.comment || 0,
					title: Loc.getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_COUNTERS_COMMENT'),
					color: this.#getCommentColor(this.counters?.comment),
					eventsForActive: {
						click: this.#setFilterPreset.bind(this),
					},
					eventsForUnActive: {
						click: this.#setFilterPreset.bind(this, 'comment'),
					},
				},
			],
		});

		this.uiPanel.init();
		this.#subscribeToPulls();
	}

	#subscribeToPulls()
	{
		BX.PULL.subscribe({
			moduleId: 'main',
			command: 'user_counter',
			callback: (params) => {
				const taskCounterValue = (params[BX.message('SITE_ID')])?.bp_tasks ?? null;
				if (taskCounterValue !== null)
				{
					this.uiPanel.getItemById('task').updateValue(taskCounterValue);
					this.uiPanel.getItemById('task').updateColor(this.#getTaskColor(taskCounterValue));
				}
			},
		});

		BX.PULL.subscribe({
			moduleId: 'bizproc',
			command: 'comment',
			callback: (params) => {
				const allUnreadValue: number | null = params.counter?.allUnread ?? null;

				if (allUnreadValue !== null)
				{
					this.uiPanel.getItemById('comment').updateValue(allUnreadValue);
					this.uiPanel.getItemById('comment').updateColor(this.#getCommentColor(allUnreadValue));
				}
			},
		});
	}

	#getTaskColor(value): string
	{
		return value > 0 ? 'DANGER' : 'THEME';
	}

	#getCommentColor(value): string
	{
		return value > 0 ? 'SUCCESS' : 'THEME';
	}

	#setFilterPreset(presetId: string): void
	{
		const filterManager = this.#getFilterManager();
		if (!filterManager)
		{
			return;
		}

		const api = filterManager.getApi();
		const fields = {
			SYSTEM_PRESET: presetId ?? 'in_work',
		};

		api.setFields(fields);
		api.apply();
	}

	#getFilterManager(): ?BX.Main.Filter
	{
		if (this.filterId)
		{
			return BX.Main.filterManager?.getById(this.filterId);
		}

		// eslint-disable-next-line no-console
		console.warn('Filter not found');

		return null;
	}
}
