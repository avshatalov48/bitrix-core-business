(function(){
	var BX = window.BX;
	if (BX.IblockSelectorLanding)
	{
		return;
	}

	BX.IblockSelectorLanding = function() {
		this.id = '';
		this.filter = [];
	};

	BX.IblockSelectorLanding.create = function(id, settings)
	{
		var self = new BX.IblockSelectorLanding();
		self.initialize(id, settings);

		return self;
	};

	BX.IblockSelectorLanding.prototype = {
		initialize: function (id, settings)
		{
			this.id = id;
			this.settings = {
				defaultFilter: [],
				internalFilter: []
			};
			this.converterSettings = {
				useQuickSearch: false,
				quickSearchField: {}
			}
			if (BX.type.isPlainObject(settings))
			{
				if (BX.type.isArray(settings.defaultFilter))
					this.settings.defaultFilter = settings.defaultFilter;
				if (BX.type.isArray(settings.internalFilter))
					this.settings.internalFilter = settings.internalFilter;
				if (BX.type.isBoolean(settings.useQuickSearch))
					this.converterSettings.useQuickSearch = settings.useQuickSearch;
				if (this.converterSettings.useQuickSearch && BX.type.isPlainObject(settings.quickSearchField))
				{
					this.converterSettings.quickSearchField = settings.quickSearchField;
				}
			}

			this.converter = new BX.Landing.UiFilterConverter({
				filterId: this.id,
				useQuickSearch: this.converterSettings.useQuickSearch,
				quickSearchField: this.converterSettings.quickSearchField
			});

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
			this.filter = this.converter.getFilter();
			if (this.settings.internalFilter.length > 0)
				this.filter = [].concat(this.settings.internalFilter, this.filter);

			if (this.filter.length === 0)
				this.filter = this.settings.defaultFilter;
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