;(function(window) {

	function Search(calendar, data)
	{
		this.calendar = calendar;
		this.util = this.calendar.util;
		this.filterId = data.filterId;
		this.minSearchStringLength = 2;

		this.showCounters = false;
		this.counters = [
			{
				id: 'invitation',
				className: 'calendar-counter-invitation',
				pluralMessageId: 'EC_COUNTER_INVITATION',
				value: data.counters.invitation || 0
			}
			//{
			//	id: 'new_comments',
			//	className: 'calendar-counter-new-comments',
			//	pluralMessageId: 'EC_COUNTER_NEW_COMMENTS',
			//	value: 2
			//}
		];

		this.filter = BX.Main.filterManager.getById(this.filterId);
		if (this.filter)
		{
			this.filterApi = this.filter.getApi();
			BX.addCustomEvent('BX.Main.Filter:beforeApply', BX.delegate(this.beforeFilterApply, this));
			BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(this.applyFilter, this));
		}
	}

	Search.prototype = {
		getFilter: function()
		{
			//if(!this.filter)
			//{
			//	this.filter = BX.Main.filterManager.getById('calendar-filter');
			//	this.filterApi = this.filter.getApi();
			//}

			return this.filter;
		},

		updateCounters: function ()
		{
			var i, _this = this;

			this.showCounters = false;

			BX.cleanNode(this.calendar.countersCont);
			this.countersWrap = this.calendar.countersCont.appendChild(BX.create('DIV', {props: {className: 'calendar-counter-title'}}));

			for (i = 0; i < this.counters.length; i++)
			{
				if (this.counters[i] && this.counters[i].value > 0)
				{
					this.showCounters = true;
					break;
				}
			}

			if (this.showCounters)
			{
				this.countersWrap.appendChild(BX.create('span', {
					props: {className: 'calendar-counter-page-name'},
					text: BX.message('EC_COUNTER_TOTAL') + ':'
				}));

				for (i = 0; i < this.counters.length; i++)
				{
					if (this.counters[i] && this.counters[i].value > 0)
					{
						this.countersWrap.appendChild(BX.create('span', {
							props: {className: 'calendar-counter-container' + ' ' + this.counters[i].className},
							attrs: {'data-bx-counter': this.counters[i].id},
							html: '<span class="calendar-counter-inner">' +
								'<span class="calendar-counter-number">' + this.counters[i].value + '</span>' +
								'<span class="calendar-counter-text">' + this.util.getMessagePlural(this.counters[i].pluralMessageId,this.counters[i].value) + '</span>' +
							'</span>',
							events: {
								click: (function (counter)
								{
									return function ()
									{
										_this.appplyCounterEntries(counter.id);
									}
								})(this.counters[i])
							}
						}));
					}
				}
			}
			else
			{
				this.countersWrap.innerHTML = BX.message('EC_NO_COUNTERS');
			}
		},

		appplyCounterEntries: function(counterId)
		{
			if (counterId == 'invitation')
			{
				this.filterApi.setFilter({
					preset_id: "filter_calendar_meeting_status_q"
				});
			}
		},

		beforeFilterApply: function()
		{
			if (!this.isFilterEmpty())
			{
				// 1. Set list view
				//this.calendar.setView('list', {animation: false});
				//setTimeout(BX.delegate(function ()
				//{
				//	this.calendar.getView().applyFilterMode();
				//	// 2. Show animation
				//}, this), 100);
			}
		},

		applyFilter: function(id, data, ctx, promise, params)
		{
			// Turn of autoresoving mode
			params.autoResolve = false;
			if (this.isFilterEmpty())
			{
				if (this.calendar.getView().resetFilterMode)
				{
					this.calendar.getView().resetFilterMode({resetSearchFilter: false});
				}
				promise.fulfill();
			}
			else
			{
				this.calendar.setView('list', {animation: false});
				setTimeout(BX.delegate(function ()
				{
					this.calendar.getView().applyFilterMode();
				}, this), 100);

				this.calendar.request({
					data: {
						action: 'get_filter_data'
					},
					handler: BX.delegate(function(response)
					{
						//this.calendar.setView('list', {animation: false});
						if (response && response.entries)
						{
							//if (this.calendar.currentViewName !== 'list')
							//{
							//}
							if (!this.calendar.getView().filterMode)
							{
								setTimeout(BX.delegate(function ()
								{
									this.calendar.getView().applyFilterMode();
									this.displaySearchResult(response);
									//setTimeout(BX.delegate(function(){this.displaySearchResult(response);}, this), 200);
								}, this), 100);
							}
							else
							{
								this.displaySearchResult(response);
							}
						}

						promise.fulfill();
					}, this)
				});
			}
		},

		displaySearchResult: function(response)
		{
			var i, entries = [];
			for (i = 0; i < response.entries.length; i++)
			{
				entries.push(new window.BXEventCalendar.Entry(this.calendar, response.entries[i]));
			}
			this.calendar.getView().displayResult(entries);

			if (BX.type.isPlainObject(response.counters))
			{
				for (i = 0; i < this.counters.length; i++)
				{
					if (response.counters[this.counters[i].id] !== undefined)
					{
						this.counters[i].value = response.counters[this.counters[i].id] || 0;
					}
				}
				this.updateCounters();
			}
		},

		isFilterEmpty: function()
		{
			var searchField = this.filter.getSearch();
			return !searchField.getLastSquare() && (!searchField.getSearchString() || searchField.getSearchString().length < this.minSearchStringLength);
		},

		searchInput: function()
		{
		},

		resetFilter: function()
		{
			this.filter.resetFilter();
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.Search = Search;
	}
	else
	{
		BX.addCustomEvent(window, "OnBXEventCalendarInit", function()
		{
			window.BXEventCalendar.Search = Search;
		});
	}
})(window);