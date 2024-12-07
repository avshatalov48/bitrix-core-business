import { EventEmitter } from 'main.core.events';

const MIN_QUERY_LENGTH = 3;

export class Filter extends EventEmitter
{
	#filterId: string;
	#filter: BX.Main.Filter;

	constructor(filterId: string)
	{
		super();

		this.setEventNamespace('Calendar.OpenEvents.Filter');

		this.#filterId = filterId;
		this.#filter = BX.Main.filterManager.getById(this.#filterId);

		this.#bindEvents();
	}

	get id(): string
	{
		return this.#filterId;
	}

	get fields(): any
	{
		return this.#filter.getFilterFieldsValues();
	}

	isDateFieldApplied(): boolean
	{
		return this.fields.DATE_datesel && this.fields.DATE_datesel !== 'NONE';
	}

	getFilterFieldsKey(): string
	{
		return JSON.stringify(this.fields);
	}

	#bindEvents(): void
	{
		this.beforeApplyHandler = this.#beforeApplyHandler.bind(this);
		this.applyHandler = this.#applyHandler.bind(this);

		EventEmitter.subscribe('BX.Main.Filter:beforeApply', this.beforeApplyHandler);
		EventEmitter.subscribe('BX.Main.Filter:apply', this.applyHandler);
	}

	#beforeApplyHandler(event)
	{
		const [ filterId ] = event.getData();
		if (filterId !== this.#filterId)
		{
			return;
		}

		this.emit('beforeApply');
	}

	#applyHandler(event)
	{
		const [ filterId ] = event.getData();
		if (filterId !== this.#filterId)
		{
			return;
		}

		if (this.#isFilterEmpty())
		{
			this.emit('clear');
		}
		else
		{
			this.emit('apply');
		}
	}

	#isFilterEmpty(): boolean
	{
		return this.#arePresetsEmpty() && this.#isSearchEmpty();
	}

	#arePresetsEmpty(): boolean
	{
		return !this.#filter.getSearch().getLastSquare();
	}

	#isSearchEmpty(): boolean
	{
		return this.#filter.getSearch().getSearchString().length < MIN_QUERY_LENGTH;
	}
}
