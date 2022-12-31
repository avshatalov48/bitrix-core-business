import {Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

export class Filter
{
	constructor(options)
	{
		this.filterInstance = BX.Main.filterManager.getById(options.filterId);
		if (!this.filterInstance)
		{
			return;
		}

		this.defaultFilterPresetId = options.defaultFilterPresetId;
		this.gridId = options.gridId;

		this.init();
		this.bindEvents();
	}

	init()
	{
		this.fields = this.filterInstance.getFilterFieldsValues();
	}

	bindEvents()
	{
		EventEmitter.subscribe('BX.Main.Filter:apply', this.onFilterApply.bind(this));
	}

	onFilterApply()
	{
		this.updateFields();
	}

	updateFields()
	{
		this.fields = this.filterInstance.getFilterFieldsValues();
	}

	toggleByField(field)
	{
		const name = Object.keys(field)[0];
		const value = field[name];

		if (!this.isFilteredByFieldValue(name, value))
		{
			this.filterInstance.getApi().extendFilter({[name]: value});
			return;
		}

		this.filterInstance.getFilterFields().forEach((field) => {
			if (field.getAttribute('data-name') === name)
			{
				this.filterInstance.getFields().deleteField(field);
			}
		});

		this.filterInstance.getSearch().apply();
	}

	isFilteredByFieldValue(field, value)
	{
		return (
			this.isFilteredByField(field)
			&& this.fields[field] === value
		);
	}

	isFilteredByField(field)
	{
		if (!Object.keys(this.fields).includes(field))
		{
			return false;
		}

		if (Type.isArray(this.fields[field]))
		{
			return this.fields[field].length > 0;
		}

		return this.fields[field] !== '';
	}
}