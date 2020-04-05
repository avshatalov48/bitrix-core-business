(function(){
	var BX = window.BX;
	if (BX.IblockSelectorElement)
	{
		return;
	}

	BX.IblockSelectorElement = function() {
		this.id = '';
		this.filter = [];
	};

	BX.IblockSelectorElement.create = function(id, settings)
	{
		var self = new BX.IblockSelectorElement();
		self.initialize(id, settings);

		return self;
	};

	BX.IblockSelectorElement.prototype = {
		initialize: function (id, settings)
		{
			this.id = id;
			this.settings = {
				defaultFilter: [],
				internalFilter: [],
				quickSearchField: ''
			};
			if (BX.type.isPlainObject(settings))
			{
				if (BX.type.isArray(settings.defaultFilter))
					this.settings.defaultFilter = settings.defaultFilter;
				if (BX.type.isArray(settings.internalFilter))
					this.settings.internalFilter = settings.internalFilter;
				if (BX.type.isNotEmptyString(settings.quickSearchField))
					this.settings.quickSearchField = settings.quickSearchField;
			}

			this.loadFilter();

			BX.addCustomEvent('BX.Main.Filter:apply', BX.proxy(this.applyFilter, this));

			BX.addCustomEvent(BX.UI.ButtonPanel, 'button-click', function(button) {
				var currentSlider;

				if (BX.type.isNotEmptyObject(button))
				{
					if (button.TYPE === 'save')
					{
						top.BX.SidePanel.Instance.postMessageTop(window, 'save', {
							filter: this.filter
						});
						currentSlider = top.BX.SidePanel.Instance.getSliderByWindow(window);
						if(currentSlider)
						{
							currentSlider.close(true);
							top.BX.SidePanel.Instance.destroy(currentSlider.getUrl());
						}
						currentSlider = null;
					}
				}
			}.bind(this));
		},

		loadFilter: function()
		{
			var filter,
				currentPreset,
				currentFields,
				searchFieldData;

			this.filter = this.settings.defaultFilter;
			// eslint-disable-next-line
			filter = BX.Main.filterManager.getById(this.id);

			if (filter !== null)
			{
				currentPreset = filter.getPreset().getCurrentPresetData();
				currentFields = [].concat(currentPreset.FIELDS, currentPreset.ADDITIONAL);
				searchFieldData = filter.getSearch().prepareSquaresData(currentFields);

				this.filter = searchFieldData.map(function (item) {
					var field = currentFields.find(function (currentField) {
							return (
								currentField.NAME === item.value
								&& !filter.getPreset().isEmptyField(currentField)
							);
						}),
						row;

					row = {
						name: item.name,
						key: field.NAME,
						value: (field.VALUE || field.VALUES)
					};
					if (BX.type.isString(row.value))
					{
						row.value = {VALUE: row.value};
					}
					if (BX.type.isPlainObject(field.SUB_TYPE))
					{
						row.value.SUB_TYPE = field.SUB_TYPE.VALUE;
					}
					return row;
				});

				this.loadQuickSearch();

			}
			if (this.settings.internalFilter.length > 0)
				this.filter = [].concat(this.settings.internalFilter, this.filter);

			if (this.filter.length === 0)
				this.filter = this.settings.defaultFilter;
		},

		loadQuickSearch: function()
		{
			var filter,
				field,
				quickSearchValue,
				currentPreset,
				currentFields,
				index,
				name,
				data,
				found = false;

			if (this.settings.quickSearchField === '')
				return;
			filter = BX.Main.filterManager.getById(this.id);
			if (filter !== null)
			{
				currentPreset = filter.getPreset().getCurrentPresetData();
				currentFields = [].concat(currentPreset.FIELDS, currentPreset.ADDITIONAL);
				quickSearchValue = filter.getSearch().getSearchString();
				if (quickSearchValue !== '')
				{
					field = this.settings.quickSearchField;
					data = { VALUE: quickSearchValue, QUICK_SEARCH: 'Y' };
					name = currentFields.find(function(element, index, array){
						return (element.NAME === field);
					});
					name = name.LABEL + ': ';

					if (this.filter.length > 0)
					{
						index = this.filter.findIndex(function(element, index, array) {
							return (element.key === field);
						});
						if (index > -1)
						{
							found = true;
							this.filter[index].name = name + quickSearchValue;
							this.filter[index].value = data;
						}
					}
					if (!found)
					{
						this.filter.push({
							name: name + quickSearchValue,
							key: field,
							value: data
						});
					}
				}
			}
		},

		applyFilter: function(eventFilterId, values, ob, filterPromise, filterParams)
		{
			if (eventFilterId !== this.id)
			{
				return;
			}
			this.loadFilter();
		}
	};
})();