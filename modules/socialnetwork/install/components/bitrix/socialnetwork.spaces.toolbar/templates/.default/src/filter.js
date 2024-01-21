import { Dom, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';

type Params = {
	filterId: string,
	filterContainer: HTMLElement,
}

export class Filter extends EventEmitter
{
	#filter: BX.Main.Filter;
	#filterContainer: HTMLElement;
	#fields: Object;

	constructor(params: Params)
	{
		super(params);

		this.setEventNamespace('BX.Socialnetwork.Spaces.Filter');

		this.#filter = BX.Main.filterManager.getById(params.filterId);
		this.#filterContainer = params.filterContainer;

		this.#fields = this.#filter.getFilterFieldsValues();

		EventEmitter.subscribe('BX.Main.Filter:apply', this.#filterApply.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:show', this.#filterShow.bind(this));
		EventEmitter.subscribe('BX.Main.Filter:blur', this.#filterHide.bind(this));
	}

	applyFilter()
	{
		this.#filter.applyFilter(false, true);
	}

	toggleField(fieldName: string, value: string)
	{
		if (!this.#isFilteredByFieldValue(fieldName, value))
		{
			this.#filter.getApi().extendFilter({ [fieldName]: value });

			return;
		}

		this.#filter.getFilterFields().forEach((field) => {
			if (field.getAttribute('data-name') === fieldName)
			{
				this.#filter.getFields().deleteField(field);
			}
		});

		this.#filter.getSearch().apply();
	}

	#filterApply()
	{
		this.#fields = this.#filter.getFilterFieldsValues();
	}

	#filterShow()
	{
		Dom.addClass(this.#filterContainer, '--active');
	}

	#filterHide()
	{
		Dom.removeClass(this.#filterContainer, '--active');
	}

	#isFilteredByField(field): boolean
	{
		if (!Object.keys(this.#fields).includes(field))
		{
			return false;
		}

		if (Type.isArray(this.#fields[field]))
		{
			return this.#fields[field].length > 0;
		}

		return this.#fields[field] !== '';
	}

	#isFilteredByFieldValue(field, value): boolean
	{
		return this.#isFilteredByField(field) && this.#fields[field].toString() === value.toString();
	}
}
