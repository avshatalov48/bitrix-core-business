(function(){
	var BX = window.BX;
	if (BX.SocialnetworkLandingLivefeedSelector)
	{
		return;
	}

	BX.SocialnetworkLandingLivefeedSelector = function() {
		this.id = "";
	};

	BX.SocialnetworkLandingLivefeedSelector.create = function(id, settings)
	{
		BX.SocialnetworkLandingLivefeedSelector.Instance = new BX.SocialnetworkLandingLivefeedSelector();
		BX.SocialnetworkLandingLivefeedSelector.Instance.initialize(id, settings);

		return BX.SocialnetworkLandingLivefeedSelector.Instance;
	};

	BX.SocialnetworkLandingLivefeedSelector.createWorkgroup = function()
	{
		BX.SidePanel.Instance.open(this.urlToGroupCreate);
	};

	BX.SocialnetworkLandingLivefeedSelector.prototype = {

		initialize: function (id, settings)
		{
			this.id = id;
			this.filter = [];
			this.urlToGroupCreate = '';

			if (
				BX.type.isNotEmptyObject(settings)
				&& BX.type.isNotEmptyObject(settings.filterValue)
			)
			{
				this.filter = settings.filterValue;
			}

			if (
				BX.type.isNotEmptyObject(settings)
				&& BX.type.isNotEmptyString(settings.urlToGroupCreate)
			)
			{
				this.urlToGroupCreate = settings.urlToGroupCreate;
			}

			BX.addCustomEvent("BX.Main.Filter:beforeApply", function(eventFilterId, values, ob, filterPromise) {
				if (eventFilterId != this.id)
				{
					return;
				}

				BX.onCustomEvent(window, 'BX.Livefeed.Filter:beforeApply', [values, filterPromise]);
			}.bind(this));

			BX.addCustomEvent("BX.Main.Filter:apply", function(eventFilterId, values, ob, filterPromise, filterParams) {
				if (eventFilterId != this.id)
				{
					return;
				}

				var filterData = ob.getFilterFieldsValues();

				this.filter = [];

				if (BX.type.isNotEmptyString(filterData.GROUP_ID))
				{
					this.filter.push({
						name: (BX.type.isNotEmptyString(filterData.GROUP_ID_label) ? filterData.GROUP_ID_label : ''),
						value: filterData.GROUP_ID,
						key: 'GROUP_ID'
					});
				}

				if (BX.type.isNotEmptyString(filterData.AUTHOR_ID))
				{
					this.filter.push({
						name: (BX.type.isNotEmptyString(filterData.AUTHOR_ID_label) ? filterData.AUTHOR_ID_label : ''),
						value: filterData.AUTHOR_ID,
						key: 'AUTHOR_ID'
					});
				}

				BX.onCustomEvent(window, 'BX.Livefeed.Filter:apply', [values, filterPromise, filterParams]);
			}.bind(this));

			BX.addCustomEvent('BX.Filter.Search:input', function(eventFilterId, searchString) {
				if (eventFilterId != this.id)
				{
					return;
				}

				var actualSearchString = (BX.type.isNotEmptyString(searchString) ? BX.util.trim(searchString) : '');

				if (this.actualSearchString.length > 0)
				{
					BX.onCustomEvent(window, 'BX.Livefeed.Filter:searchInput', [ searchString ]);
				}
			}.bind(this));

			BX.addCustomEvent(BX.UI.ButtonPanel, 'button-click', function(button) {
				if (BX.type.isNotEmptyObject(button))
				{
					if (button.TYPE == 'save')
					{
						if (this.filter.length > 0)
						{
							top.BX.SidePanel.Instance.postMessageTop(window, 'save', {
								filter: this.filter
							});
						}

						var curSlider = top.BX.SidePanel.Instance.getSliderByWindow(window);
						if(curSlider)
						{
							curSlider.destroy();
						}
					}
				}
			}.bind(this));

			BX.addCustomEvent('SidePanel.Slider:onMessage', function(event){
				if (event.getEventId() == 'sonetGroupEvent')
				{
					var eventData = event.getData();
					if (
						BX.type.isNotEmptyString(eventData.code)
						&& eventData.code == 'afterCreate'
					)
					{
						var filterInstance = BX.Main.filterManager.getById(this.id);
						if (!!filterInstance && (filterInstance instanceof BX.Main.Filter))
						{
							var filterApi = filterInstance.getApi();
							filterApi.setFields({
								GROUP_ID: 'SG' + parseInt(eventData.data.group.ID),
								GROUP_ID_label: eventData.data.group.FIELDS.NAME
							});
							filterApi.apply();
						}
					}
				}
			}.bind(this));


			if (BX('slls_group_create'))
			{
				BX.bind(BX('slls_group_create'), 'click', function() {
					BX.SidePanel.Instance.open(this.urlToGroupCreate);
				}.bind(this));
			}
		},
	};
})();

