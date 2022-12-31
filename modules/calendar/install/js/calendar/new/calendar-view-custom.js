;(function(window) {
	var View = window.BXEventCalendarView;

	function CustomView(calendar, customViewDefinition)
	{
		View.apply(this, arguments);
		this.appView = customViewDefinition;
		this.name = 'custom_' + customViewDefinition.ID;
		this.type = 'custom';
		this.placementCode = this.calendar.util.config.placementParams.gridPlacementCode;
		this.title = customViewDefinition.TITLE || this.appView.APP_NAME || BX.message('EC_REQUEST_APP_NONAME_TAB');
		this.contClassName = 'calendar-custom-view';
		this.preBuild();
	}
	CustomView.prototype = Object.create(View.prototype);
	CustomView.prototype.constructor = CustomView;

	CustomView.prototype.preBuild = function()
	{
		this.viewCont = BX.create('DIV', {props: {className: this.contClassName}, style: {display: 'none'}});
		var MessageInterface = BX.rest.AppLayout.initializePlacement(this.placementCode);
		if (MessageInterface)
		{
			MessageInterface.prototype.getEvents = BX.proxy(this.appGetEntries, this);
			MessageInterface.prototype.viewEvent = BX.proxy(this.appViewEvent, this);
			MessageInterface.prototype.addEvent = BX.proxy(this.appAddEvent, this);
			MessageInterface.prototype.editEvent = BX.proxy(this.appEditEvent, this);
			MessageInterface.prototype.deleteEvent = BX.proxy(this.appDeleteEvent, this);

			MessageInterface.prototype.events.push('Calendar.customView:refreshEntries');
			MessageInterface.prototype.events.push('Calendar.customView:decreaseViewRangeDate');
			MessageInterface.prototype.events.push('Calendar.customView:increaseViewRangeDate');
			MessageInterface.prototype.events.push('Calendar.customView:adjustToDate');
		}
	};

	CustomView.prototype.build = function()
	{
		this.titleCont = this.viewCont.appendChild(BX.create('DIV', {props: {className: 'calendar-custom-view-title'}}));

		this.appWrap = this.viewCont.appendChild(BX.create('DIV', {
			props: {className: 'calendar-app-wrap'},
			style: {height: this.calendar.util.getViewHeight() + 'px'}
		}));
	};

	CustomView.prototype.adjustViewRangeToDate = function(date)
	{
		if (this.calendar.currentViewName !== this.name || !this.isBuilt)
		{
			this.show();
			// Show loader
			if (this.name)
			this.appLoader = this.appWrap.appendChild(BX.adjust(this.calendar.util.getLoader(), {style: {height: '100px'}}));

			var viewRange = this.calendar.getDisplayedViewRange();

			this.appRequestIsRunning = true;
			BX.ajax(
				{
					url: this.calendar.util.config.placementParams.serviceUrl,
					method: "POST",
					dataType: "html",
					data:
					{
						"LOADER_ID": Math.round(Math.random() * 1000000),
						"PARAMS": {
							'params' : {
								'ID': this.appView.APP_ID,
								'PLACEMENT': this.calendar.util.config.placementParams.gridPlacementCode,
								'PLACEMENT_ID': this.appView.ID,
								"PLACEMENT_OPTIONS": {
									viewRangeFrom: BX.date.format('Y-m-d', viewRange ? viewRange.start : this.calendar.getViewRangeDate()),
									viewRangeTo: BX.date.format('Y-m-d', viewRange ? viewRange.end : this.calendar.getViewRangeDate())
								}
							}
						}
					},
					onsuccess: BX.delegate(this.appRequestOnSuccess, this),
					onfailure: BX.delegate(this.appRequestOnFailure, this)
				}
			);
		}

		BX.onCustomEvent('Calendar.customView:adjustToDate', [BX.date.format('Y-m-d', date)]);
	};

	CustomView.prototype.appRequestOnSuccess = function(html)
	{
		BX.remove(this.appLoader);
		this.appRequestIsRunning = false;
		BX.html(this.appWrap, html).then(BX.defer(function(){
			var appLayout = BX.rest.AppLayout.get(this.calendar.util.config.placementParams.gridPlacementCode);
			if (appLayout)
			{
				appLayout.allowInterface(['resizeWindow']);
			}
		}, this));
	};

	CustomView.prototype.appRequestOnFailure = function()
	{
		BX.remove(this.appLoader);
		this.appRequestIsRunning = false;
		this.appWrap.innerHTML = '<div class="ui-alert ui-alert-warning"><span class="ui-alert-message">' + BX.message('EC_REQUEST_APP_FAILURE').replace('#APPNAME#', this.appView.APP_NAME) + '</span></div>';
	};

	CustomView.prototype.loadEntries = function(from, to)
	{
		return new Promise((resolve) => {
			this.entryController.getList({
				startDate: from,
				finishDate: to,
				viewRange: this.calendar.getDisplayedViewRange(),
			}).then((entries) => {
				resolve(entries);
			});
		});
	};

	CustomView.prototype.appGetEntries = function(params, callback)
	{
		var
			dateFrom = new Date(params.dateFrom) || new Date(),
			dateTo = new Date(params.dateTo) || (new Date(dateFrom.getFullYear(), dateFrom.getMonth() + 1, dateFrom.getDate()));

		dateFrom.setHours(0, 0, 0, 0);
		dateTo.setHours(0, 0, 0, 0);

		this.calendar.setDisplayedViewRange({start: dateFrom, end:dateTo});

		this.loadEntries(dateFrom, dateTo).then(entries => {
			params.entries.forEach(function(entry)
			{
				entry.UID = this.calendar.entryController.getUniqueId(entry);
			}, this);
			this.entries = entries;
			if (BX.type.isArray(this.entries))
			{
				var i, entry;
				for (i = 0; i < this.entries.length; i++)
				{
					entry = this.entries[i];
					this.entriesIndex[entry.uid] = i;
				}
			}
			callback(params.entries);
		});

		if (BX.type.isArray(this.entries))
		{
			var entries = [];
			this.entries.forEach(function(entry)
			{
				entry.data.UID = this.calendar.entryController.getUniqueId(entry.data);
				entries.push(entry.data);
			}, this);
			callback(entries);

			var i, entry;
			for (i = 0; i < this.entries.length; i++)
			{
				entry = this.entries[i];
				this.entriesIndex[entry.uid] = i;
			}
		}
	};

	CustomView.prototype.getEntryByParams = function(params)
	{
		var uid = params.uid;
		if (!uid && params.id)
		{
			if (params.dateFrom && this.getEntryById(params.id + '|' + params.dateFrom))
			{
				uid = params.id + '|' + params.dateFrom;
			}
			else if (this.getEntryById(params.id))
			{
				uid = params.id;
			}
		}

		return this.getEntryById(uid) || false;
	};

	CustomView.prototype.appViewEvent = function(params, callback)
	{
		var entry = this.getEntryByParams(params);
		if (entry)
		{
			this.showViewSlider({entry: entry});
		}
		callback({result: !!entry});
	};
	CustomView.prototype.appAddEvent = function(params, callback)
	{
		this.showEditSlider();
		callback({result: true});
	};
	CustomView.prototype.appEditEvent = function(params, callback)
	{
		var entry = this.getEntryByParams(params);
		if (entry)
		{
			this.calendar.entryController.editEntry({entry: entry});
		}
		callback({result: !!entry});
	};
	CustomView.prototype.appDeleteEvent = function(params, callback)
	{
		var entry = this.getEntryByParams(params);
		if (entry)
		{
			this.calendar.entryManager.deleteEntry(entry);
		}
		callback();
	};

	CustomView.prototype.displayEntries = function()
	{
		BX.onCustomEvent('Calendar.customView:refreshEntries', [{}]);
	};

	CustomView.prototype.decreaseViewRangeDate = function()
	{
		BX.onCustomEvent('Calendar.customView:decreaseViewRangeDate', [{}]);
	};
	CustomView.prototype.increaseViewRangeDate = function()
	{
		BX.onCustomEvent('Calendar.customView:increaseViewRangeDate', [{}]);
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.CalendarCustomView = CustomView;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.CalendarCustomView = CustomView;
		});
	}
})(window);