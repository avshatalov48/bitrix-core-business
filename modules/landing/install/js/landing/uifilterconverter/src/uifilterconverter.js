import {Type} from 'main.core';
import {Runtime} from 'main.core';

export class UiFilterConverter
{
	constructor(options = {
		filterId: '',
		useQuickSearch: false,
		quickSearchField: {
			name: '',
			field: ''
		}
	})
	{
		this.filterId = options.filterId;
		this.useQuickSearch = options.useQuickSearch;
		this.quickSearchField = options.quickSearchField;

		this.filter = null; // BX.Main.filterManager.getById

		this.currentPreset = [];
		this.currentFields = [];

		this.sourceFilter = [];
	}

	getFilterId()
	{
		return this.filterId;
	}

	getFilter()
	{
		this.sourceFilter = [];
		this.initFilter();
		if (!Type.isNil(this.filter))
		{
			this.parseFilterRows();
			this.parseQuickSearchValue();
		}
		return this.sourceFilter;
	}

	parseFilterRows()
	{
		const searchFieldData = this.filter.getSearch().prepareSquaresData(
			this.currentFields
		);
		this.sourceFilter = searchFieldData.map((item) => {
			const field = this.currentFields.find((currentField) => {
				return (
					currentField.NAME === item.value
					&& !this.filter.getPreset().isEmptyField(currentField)
				);
			});

			const row = {
				name: item.name,
				key: field.NAME,
				value: Runtime.clone(field.VALUE || field.VALUES)
			};
			if (Type.isString(row.value))
			{
				row.value = {VALUE: row.value};
			}
			if (Type.isPlainObject(field.SUB_TYPE))
			{
				row.value.SUB_TYPE = field.SUB_TYPE.VALUE;
			}

			return row;
		});
	}

	parseQuickSearchValue()
	{
		if (this.useQuickSearch)
		{
			const quickSearchValue = this.filter.getSearch().getSearchString();

			if (quickSearchValue !== '')
			{
				const row = { VALUE: quickSearchValue, QUICK_SEARCH: 'Y' };
				const name = `${this.quickSearchField.name}: ${quickSearchValue}`;

				let found = false;
				if (this.sourceFilter.length > 0)
				{
					const index = this.sourceFilter.findIndex(({key}) => key === this.quickSearchField.field);
					if (index > -1)
					{
						found = true;
						this.sourceFilter[index].name = name;
						this.sourceFilter[index].value = row;
					}
				}
				if (!found)
				{
					this.sourceFilter.push({
						name: name,
						key: this.quickSearchField.field,
						value: row
					});
				}
			}
		}
	}

	initFilter()
	{
		if (this.filter === null)
		{
			// eslint-disable-next-line
			this.filter = BX.Main.filterManager.getById(this.getFilterId());
		}
		if (!Type.isNil(this.filter))
		{
			this.currentPreset = this.filter.getPreset().getCurrentPresetData();
			this.currentFields = [...this.currentPreset.FIELDS, ...this.currentPreset.ADDITIONAL];
		}
	}
}