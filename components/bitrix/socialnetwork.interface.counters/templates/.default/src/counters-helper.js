import {Type} from 'main.core';
import {EventEmitter} from 'main.core.events';

class Filter
{
	constructor(options)
	{
		this.filterId = options.filterId;
		this.filterManager = BX.Main.filterManager.getById(this.filterId);
		this.countersManager = options.countersManager;

		setTimeout(this.updateFields.bind(this), 100);
	}

	updateFields()
	{
		const filterManager = this.getFilter();
		if (!filterManager)
		{
			return;
		}

		this.presetId = filterManager.getPreset().getCurrentPresetId();
		this.fields = filterManager.getFilterFieldsValues();

		this.countersManager.activateCountersByFilter();
	}

	isFilteredByPresetId(presetId)
	{
		return (presetId === this.presetId);
	}

	isFilteredByFields(filterFields)
	{
		let result = false;
		let breakNeeded = false;

		Object.entries(filterFields).map(([ field, value]) => {
			if (!breakNeeded && !Type.isUndefined(this.fields[field]))
			{
				result = (this.fields[field] === value);
				if (!result)
				{
					breakNeeded = true;
				}
			}
		});

		return result;
	}

	getFilter()
	{
		return this.filterManager;
	}
}

export {
	Filter
}