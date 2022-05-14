import {EventEmitter} from 'main.core.events';

class Filter
{
	constructor(options)
	{
		this.filterId = options.filterId;
		this.filterManager = BX.Main.filterManager.getById(this.filterId);

		this.bindEvents();
		setTimeout(this.updateFields.bind(this), 100);
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
		const filterManager = this.getFilter();
		if (!filterManager)
		{
			return;
		}

		this.presetId = filterManager.getPreset().getCurrentPresetId();
	}

	isFilteredByPresetId(presetId)
	{
		return (presetId === this.presetId);
	}

	getFilter()
	{
		return this.filterManager;
	}
}

export {
	Filter
}