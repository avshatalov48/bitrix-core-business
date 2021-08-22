;(function(window) {

	function Calendar(config, data, additionalParams)
	{
		this.DEFAULT_VIEW = 'month';
		this.id = config.id;
		this.showTasks = config.showTasks;
		this.calDavConnections = config.connections;
		this.util = new window.BXEventCalendar.Util(this, config, additionalParams);

		if(this.util.isFilterEnabled())
		{
			this.search = new window.BXEventCalendar.Search(this, {filterId: config.filterId, counters: config.counters});
		}

		this.externalMode = config.externalDataHandleMode;
		this.entityType = config.entityType || '';
		this.newEntryName = config.newEntryName || null;
		this.collapsedLabelMessage = config.collapsedLabelMessage || BX.message('EC_COLLAPSED_MESSAGE');
		this.viewOption = 'view' + (this.entityType ? '_' + this.entityType : '');
		// TODO: replace it with sectionManager
		// this.sectionController = new window.BXEventCalendar.SectionController(this, data, config);

		BX.Calendar.Util.setCalendarContext(this);

		this.sectionManager = new BX.Calendar.SectionManager(data, config);
		this.entryManager = new BX.Calendar.EntryManager(data, config);

		if (BX.Calendar.Controls && BX.Calendar.Controls.Location)
		{
			BX.Calendar.Controls.Location.setLocationList(additionalParams.locationList);
		}

		this.entryController = new window.BXEventCalendar.EntryController(this, data);
		this.currentViewName = this.util.getUserOption(this.viewOption) || this.DEFAULT_VIEW;

		BX.Calendar.Util.setUserSettings(config.userSettings);
		BX.Calendar.Util.setAccessNames(config.accessNames);
		BX.Calendar.Util.setEventWithEmailGuestAmount(config.countEventWithEmailGuestAmount);
		BX.Calendar.Util.setEventWithEmailGuestLimit(config.eventWithEmailGuestLimit);

		BX.Calendar.Util.setCalendarContext(this);

		this.requests = {};
		this.currentUser = config.user;
		this.ownerUser = config.ownerUser || false;
		this.viewRangeDate = new Date();
		this.keyHandlerEnabled = true;

		// build basic DOM structure
		this.build();

		if (!this.isExternalMode())
		{
			if (config.startupEvent)
			{
				this.showStartUpEntry(config.startupEvent);
			}
		}

		BX.addCustomEvent('onPullEvent-calendar', this.handlePullEvent.bind(this));
	}

	Calendar.prototype = {
		build: function()
		{
			this.mainCont = BX(this.id + '-main-container');
			if (this.mainCont)
			{
				// Build top block
				this.topBlock = BX.create('DIV', {props: {className: 'calendar-top-block'}});

				this.buildNavigation();

				// Top title
				this.viewTitleContainer = this.topBlock.appendChild(BX.create('DIV', {props: {className: 'calendar-top-title-container'}}));
				this.viewTitle = this.viewTitleContainer.appendChild(BX.create('H2', {props: {className: 'calendar-top-title'}}));

				this.mainCont.appendChild(this.topBlock);

				// Main views container
				this.viewsCont = BX.create('DIV', {props: {className: 'calendar-views-container calendar-disable-select'}});
				BX.bind(this.viewsCont, 'click', this.handleViewsClick.bind(this));

				this.dragDrop = new window.BXEventCalendar.DragDrop(this);

				if (this.util.isFilterEnabled() && !this.search.isFilterEmpty())
				{
					this.currentViewName = 'list';
				}
				this.buildViews();

				// Build switch view control
				this.buildViewSwitcher();

				// Search & counters
				if (this.util.isFilterEnabled())
				{
					if (!this.search.isFilterEmpty())
					{
						this.search.applyFilter();
					}

					this.searchCont = BX(this.id + '-search-container');
					if (this.searchCont)
					{
						this.buildSearchControll();
					}
				}

				// Top button container
				if (!this.isExternalMode())
				{
					this.buildTopButtons();
				}

				this.mainCont.appendChild(this.viewsCont);
				this.rightBlock = this.mainCont.appendChild(BX.create('DIV', {props: {className: 'calendar-right-container'}}));

				BX.bind(document.body, "keyup", BX.proxy(this.keyUpHandler, this));
				BX.addCustomEvent(this, 'doRefresh', BX.proxy(this.refresh, this));
				BX.bind(window, "beforeunload", BX.Calendar.EntryManager.doDelayedActions);
				BX.addCustomEvent(this, 'changeViewRange', BX.Calendar.EntryManager.doDelayedActions);

				this.topBlock.appendChild(BX.create('DIV', {style: {clear: 'both'}}));

				top.BX.addCustomEvent(top, 'onCalendarBeforeCustomSliderCreate', BX.proxy(this.loadCssList, this));

				top.BX.Event.EventEmitter.subscribe(
					'BX.Calendar:doRefresh',
					this.refresh.bind(this)
				);

				top.BX.Event.EventEmitter.subscribe(
					'BX.Calendar:doReloadCounters',
					top.BX.Runtime.debounce(this.updateCounters, 5000, this)
				);

				if (top !== window)
				{
					if (!top.BX.getClass('top.BX.SocNetLogDestination'))
					{
						top.BX.loadExt('socnetlogdest');
					}
					if (!top.BX.getClass('top.BX.Access'))
					{
						top.BX.loadExt('access');
					}
				}

				if (this.util.userIsOwner())
				{
					this.syncInterface = new BX.Calendar.Sync.Manager.Manager({
						wrapper: document.getElementById(this.id + '-sync-container'),
						syncInfo: this.util.config.syncInfo,
						userId: this.currentUser.id,
						syncLinks: this.util.config.syncLinks,
						isSetSyncCaldavSettings: this.util.config.isSetSyncCaldavSettings,
						//sections: this.sectionController.sections,
						sections: this.sectionManager.getSections(),
						portalAddress: this.util.config.caldav_link_all,
						isRuZone: this.util.config.isRuZone,
						calendar: this,
					});
					this.syncInterface.showSyncButton();
				}

				BX.Event.EventEmitter.subscribe('BX.Calendar.EventEditForm:onSave', function(event)
				{
					if (event instanceof BX.Event.BaseEvent)
					{
						var data = event.getData();
						if (data.options.recursionMode || data.responseData.reload)
						{
							this.reload();
						}
						else if (data.responseData && BX.Type.isArray(data.responseData.eventList))
						{
							this.entryController.handleEntriesList(data.responseData.eventList);
							this.getView().displayEntries();
						}
					}
				}.bind(this));

				BX.Event.EventEmitter.subscribe('BX.Calendar.CompactEventForm:onSave', function(event)
				{
					if (event instanceof BX.Event.BaseEvent)
					{
						var data = event.getData();
						if (data.options.recursionMode || data.responseData.reload)
						{
							this.reload();
						}
						else if (data.responseData && BX.Type.isArray(data.responseData.eventList))
						{
							this.entryController.handleEntriesList(data.responseData.eventList);
							this.getView().displayEntries();
						}
					}
				}.bind(this));

				BX.Event.EventEmitter.subscribe('BX.Calendar.Entry:onChangeMeetingStatus', function(event)
				{
					if (event instanceof BX.Event.BaseEvent)
					{
						var data = event.getData();
						if (BX.Type.isObjectLike(data.counters) && this.search)
						{
							this.search.setCountersValue(data.counters);
						}

						this.reload();
					}
				}.bind(this));

				BX.Event.EventEmitter.subscribe('BX.Calendar.CompactEventForm:doRefresh', function(event)
				{
					this.refresh();
				}.bind(this));
			}
			if (this.util.config.displayMobileBanner)
			{
				new BX.Calendar.Sync.Interface.MobileSyncBanner().showInPopup();
			}
		},

		buildViews: function()
		{
			var
				avilableViews = this.util.getAvilableViews(),
				viewConstuctor = {
					day : window.BXEventCalendar.CalendarDayView,
					week: window.BXEventCalendar.CalendarWeekView,
					month: window.BXEventCalendar.CalendarMonthView,
					list: window.BXEventCalendar.CalendarListView
				};

			this.views = [];
			if (BX.type.isArray(avilableViews))
			{
				avilableViews.forEach(function(viewName){
					if (viewName && viewConstuctor[viewName])
					{
						this.views.push(new viewConstuctor[viewName](this));
					}
				}, this);
			}

			var customViews = this.util.getCustumViews();
			if (BX.type.isArray(customViews))
			{
				customViews.forEach(function(customView)
				{
					this.views.push(new window.BXEventCalendar.CalendarCustomView(this, customView));
				}, this);
			}

			BX.addCustomEvent(this, 'keyup', function(params){

				if (BX.Calendar && BX.Calendar.Util)
				{
					this.views.forEach(function(view){
						if (view.getHotkey() && BX.Calendar.Util.getKeyCode(view.getHotkey()) === params.keyCode)
						{
							BX.Calendar.Util.sendAnalyticLabel({calendarAction: 'viewChange', viewMode:'hotkey', viewType:view.getName()});
							this.setView(view.getName(), {animation: true});
						}
					}, this);
				}
			}.bind(this));

			BX.onCustomEvent(window, 'onCalendarBeforeBuildViews', [this.views, this]);
			this.views.forEach(this.buildView, this);
			this.viewTransition = new window.BXEventCalendar.ViewTransition(this);
			BX.onCustomEvent(window, 'onCalendarAfterBuildViews', [this]);
		},

		buildNavigation:  function()
		{
			this.navigationWrap = this.topBlock.appendChild(BX.create('DIV', {props: {className: 'calendar-navigation-container'}}));
			this.navigationWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-navigation-previous'},
				events: {click: BX.delegate(this.showPrevious, this)}
			}));
			this.navigationWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-navigation-current'},
				text: BX.message('EC_TODAY'),
				events: {click: BX.delegate(this.showToday, this)}
			}));
			this.navigationWrap.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-navigation-next'},
				events: {click: BX.delegate(this.showNext, this)}
			}));
		},

		showNext: function()
		{
			var viewRange = this.getView().increaseViewRangeDate();
			if (viewRange)
			{
				this.triggerEvent('changeViewDate', {viewRange: viewRange});
			}
		},

		showPrevious: function()
		{
			var viewRange = this.getView().decreaseViewRangeDate();
			if (viewRange)
			{
				this.triggerEvent('changeViewDate', {viewRange: viewRange});
			}
		},

		showToday: function()
		{
			var
				view = this.getView(),
				viewRange = view.adjustViewRangeToDate(new Date());

			if (viewRange)
			{
				this.triggerEvent('changeViewDate', {viewRange: viewRange});
			}
		},

		buildView: function(view)
		{
			var viewCont = view.getContainer();
			if (viewCont)
			{
				this.viewsCont.appendChild(viewCont);
			}

			if (this.currentViewName === view.getName())
			{
				this.setView(view.getName(), {first: true});
			}
		},

		buildViewSwitcher: function()
		{
			var views = [];
			var currentViewMode = null;
			this.views.forEach(function(view) {
				views.push({
					name: view.name,
					text: view.title || view.name,
					type: 'base',
					dataset: null,
					hotkey: view.getHotkey()
				});
			}, this);

			if (BX.type.isArray(this.util.config.additionalViewModes))
			{
				this.util.config.additionalViewModes.forEach(function(view) {
					views.push({
						name: view.id,
						text: BX.util.htmlspecialchars(view.label),
						type: 'additional',
						dataset: view
					});
					if (view.selected)
					{
						currentViewMode = view.id;
					}
				}, this);
			}

			this.viewSelector = new BX.Calendar.Controls.ViewSelector({
				views: views,
				currentView: this.getView(),
				currentViewMode: currentViewMode
			});

			this.viewSelector.subscribe('onChange', function(event)
			{
				var data = event.getData();
				if (data && data.name)
				{
					if (data.type === 'base')
					{
						this.setView(data.name, {animation: true});
						BX.Calendar.Util.sendAnalyticLabel({calendarAction: 'viewChange', viewMode:'selector',viewType:data.name});
					}
					else if (data.type === 'additional')
					{
						this.triggerEvent('changeViewMode', data.dataset);
					}
				}
			}.bind(this));
			this.topBlock.appendChild(this.viewSelector.getOuterWrap());


			this.lineViewSelectorWrap = BX(this.id + '-view-switcher-container');
			if (this.lineViewSelectorWrap)
			{
				this.lineViewSelector = new BX.Calendar.Controls.LineViewSelector({
					views: views,
					currentView: this.getView(),
					currentViewMode: currentViewMode
				});
				this.lineViewSelectorWrap.appendChild(this.lineViewSelector.getOuterWrap());

				this.lineViewSelector.subscribe('onChange', function(event)
				{
					var data = event.getData();
					if (data && data.name)
					{
						if (data.type === 'base')
						{
							this.setView(data.name, {animation: true});
							BX.Calendar.Util.sendAnalyticLabel({calendarAction: 'viewChange', viewMode:'topmenu', viewType:data.name});
						}
					}
				}.bind(this));
			}
		},

		setView: function(view, params)
		{
			if (view)
			{
				if (!params)
				{
					params = {};
				}

				var
					currentView = this.getView(),
					viewRange = currentView.getViewRange(),
					newView = this.getView(view);

				if (this.viewSelector)
				{
					this.viewSelector.setValue(newView);
					this.viewSelector.closePopup();
				}

				if (this.lineViewSelector)
				{
					this.lineViewSelector.setValue(newView);
				}

				if (newView && (view !== this.currentViewName || !currentView.getIsBuilt()))
				{
					params.currentViewDate = this.getViewRangeDate();
					if (newView === 'day' && BX.type.isDate(params.date))
					{
						params.newViewDate = params.date;
					}
					else
					{
						params.newViewDate = newView.getAdjustedDate(params.date || false, viewRange, true);
					}

					params.currentView = currentView;
					params.newView = newView;
					this.setViewRangeDate(params.newViewDate);

					this.triggerEvent('beforeSetView', {currentViewName: this.currentViewName, newViewName: view});

					if (currentView.type === 'custom' || newView.type === 'custom')
					{
						params.animation = false;
					}

					if (params.animation)
					{
						this.viewTransition.transit(params);
					}
					else
					{
						if (view !== this.currentViewName)
						{
							currentView.hide();
						}

						if(params.first === true)
						{
							this.initialViewShow = true;
							newView.adjustViewRangeToDate(params.newViewDate);
						}
						else
						{
							newView.adjustViewRangeToDate(params.newViewDate);
						}
						this.currentViewName = newView.getName();
					}

					if(params.first !== true)
					{
						this.util.setUserOption(this.viewOption, view);
					}
					this.triggerEvent('afterSetView', {viewName: view});
					BX.Calendar.Util.setCurrentView(view);
				}
			}
		},

		request : function(params)
		{
			if (!params.url)
				params.url = this.util.getActionUrl();
			if (params.bIter !== false)
				params.bIter = true;
			if (!params.data)
				params.data = {};

			var reqId;

			params.reqId = reqId = Math.round(Math.random() * 1000000);
			params.data.sessid = BX.bitrix_sessid();
			params.data.bx_event_calendar_request = 'Y';
			params.data.reqId = reqId;
			//params.data.action = params.action;

			var _this = this, iter = 0, handler;
			if (params.handler)
			{
				handler = function (result)
				{
					var handleRes = function ()
					{
						if (_this.requests[reqId].status !== 'canceled')
						{
							var erInd = result.toLowerCase().indexOf('bx_event_calendar_action_error');
							if (!result || result.length <= 0 || erInd !== -1)
							{
								var errorText = '';
								if (erInd >= 0)
								{
									var ind1 = erInd + 'BX_EVENT_CALENDAR_ACTION_ERROR:'.length, ind2 = result.indexOf('-->', ind1);
									errorText = result.substr(ind1, ind2 - ind1);
								}
								if (BX.type.isFunction(params.onerror))
								{
									params.onerror();
								}

								return _this.displayError(errorText || params.errorText || '');
							}

							_this.requests[reqId].status = 'complete';

							var res = params.handler(_this.getRequestResult(reqId), result);
							if (res === false && ++iter < 20 && params.bIter)
							{
								setTimeout(handleRes, 5);
							}
							else
							{
								delete top.BXCRES[reqId];
							}
						}
					};

					setTimeout(handleRes, 50);
				};
			}
			else
			{
				handler = BX.DoNothing();
			}

			this.requests[params.reqId] = {
				status: 'sent',
				xhr: params.type === 'post' ? BX.ajax.post(params.url, params.data, handler) : BX.ajax.get(params.url, params.data, handler)
			};

			return params;
		},

		cancelRequest: function(reqId)
		{
			if (this.requests[reqId] && this.requests[reqId].status === 'sent')
			{
				this.requests[reqId].status = 'canceled';
			}
		},

		getRequestResult: function(key)
		{
			if (top.BXCRES && typeof top.BXCRES[key] != 'undefined')
			{
				return top.BXCRES[key];
			}

			return {};
		},

		displayError : function(str, bReloadPage)
		{
			if (BX.type.isArray(str) && str.length > 0)
			{
				var
					errorMessage = '',
					errors = str;
				for (var i = 0; i < errors.length; i++)
				{
					errorMessage += errors[i].message + "\n";
				}
				str = errorMessage;
			}

			var _this = this;
			setTimeout(function(){
				if (!_this.bOnunload)
				{
					alert(str || '[Bitrix Calendar] Request error');
					if (bReloadPage)
					{
						BX.reload();
					}
				}
			}, 200);
		},

		triggerEvent: function(eventName, params)
		{
			BX.onCustomEvent(this, eventName, [params]);
		},

		getView: function(viewName)
		{
			viewName = viewName || this.currentViewName;
			for (var i = 0; i < this.views.length; i++)
			{
				if (this.views[i].getName() === viewName)
				{
					return this.views[i];
				}
			}
			return this.views[0];
		},

		getViewRangeDate: function()
		{
			if (!this.viewRangeDate)
				this.viewRangeDate = new Date();
			this.viewRangeDate.setHours(0,0,0,0);
			return this.viewRangeDate;
		},

		setViewRangeDate: function(date)
		{
			this.viewRangeDate = date;
			this.triggerEvent('changeViewRange', date);
		},

		getDisplayedViewRange: function()
		{
			return this.displayedRange;
		},
		setDisplayedViewRange: function(viewRange)
		{
			this.displayedRange = viewRange;
		},

		handleViewsClick: function(e)
		{
			var
				target = e.target || e.srcElement,
				specTarget = this.util.findTargetNode(target, this.viewsCont);

			if (specTarget)
			{
				if (specTarget.getAttribute('data-bx-calendar-weeknumber'))
				{
					this.setView('week', {
						date:new Date(parseInt(specTarget.getAttribute('data-bx-cal-time'))),
						animation: true
					});
				}
				else if (specTarget.getAttribute('data-bx-calendar-date'))
				{
					// Go to day view
					this.setView('day', {
						date:new Date(parseInt(specTarget.getAttribute('data-bx-calendar-date'))),
						animation: true
					});
				}

				this.triggerEvent('viewOnClick',
					{
						e: e,
						target: target,
						specialTarget: specTarget
				});
			}
		},

		handleViewsMousedown: function(e)
		{
			var
				target = e.target || e.srcElement,
				specTarget = this.util.findTargetNode(target, this.viewsCont);

			if (specTarget)
			{
				this.triggerEvent('viewOnMouseDown',
					{
						e: e,
						target: target,
						specialTarget: specTarget
				});
			}
		},

		disableKeyHandler: function()
		{
			this.keyHandlerEnabled = false;
		},

		enableKeyHandler: function()
		{
			this.keyHandlerEnabled = true;
		},

		isKeyHandlerEnabled: function(e)
		{
			var target = e.target || e.srcElement;

			if (target && BX.Type.isDomNode(target))
			{
				if ({'INPUT':true,'TEXTAREA':true}[target.nodeName])
				{
					return false;
				}
			}

			var res = this.keyHandlerEnabled
				&& !BX.hasClass(document.body, 'bx-im-fullscreen-block-scroll')
				&& !BX.hasClass(document.body, 'side-panel-disable-scrollbar');

			if (res)
			{
				var i, popups = document.body.querySelectorAll(".popup-window");
				for (i = 0; i < popups.length; i++)
				{
					if (popups[i]
						&& popups[i].style.display !== 'none'
						&& !BX.hasClass(popups[i], 'calendar-view-switcher-popup'))
					{
						res = false;
						break;
					}
				}
			}

			return res;
		},

		keyUpHandler: function(e)
		{
			if (this.isKeyHandlerEnabled(e))
			{
				var keyCode = e.keyCode;
				if (keyCode === BX.Calendar.Util.getKeyCode('left'))
				{
					this.showPrevious();
				}
				else if (keyCode === BX.Calendar.Util.getKeyCode('right'))
				{
					this.showNext();
				}

				this.triggerEvent('keyup', {e: e, keyCode: keyCode});
			}
		},

		buildSearchControll:  function()
		{
			this.countersCont = BX(this.id + '-counter-container');
			if (!this.countersCont)
			{
				this.countersCont = this.mainCont.appendChild(BX.create('DIV', {
					props: {className: 'calendar-counter-container'},
					attrs: {id: this.id + '-counter-container'}
				}));
			}
			BX.addClass(this.countersCont, 'calendar-counter');

			this.search.updateCounters();
		},

		buildTopButtons:  function()
		{
			this.buttonsCont = BX(this.id + '-buttons-container');
			if (this.buttonsCont)
			{
				this.sectionButton = this.buttonsCont.appendChild(BX.create("button", {
					props: {className: "ui-btn ui-btn-light-border ui-btn-themes", type: "button"},
					text: BX.message('EC_SECTION_BUTTON')
				}));

				BX.Event.bind(this.sectionButton, 'click', function(){
					this.getSectionInterface()
						.then(function(SectionInterface){
							if (!this.sectionInterface)
							{
								this.sectionInterface = new SectionInterface(
									{
										calendarContext: this,
										readonly: this.util.readOnlyMode(),
										sectionManager: this.sectionManager
									}
								);
							}
							this.sectionInterface.show();
						}.bind(this));
				}.bind(this));

				// new window.BXEventCalendar.SectionSlider({
				// 	calendar: this,
				// 	button: this.sectionButton
				// });

				if (this.util.userIsOwner() || this.util.config.TYPE_ACCESS)
				{
					this.addButton = new window.BXEventCalendar.SettingsMenu(
						{
							calendar: this,
							wrap: this.buttonsCont,
							showMarketPlace: false
						}
					);
				}

				var addButtonWrap = BX(this.id + '-add-button-container');
				if (!this.util.readOnlyMode() && BX.Type.isDomNode(addButtonWrap))
				{
					addButtonWrap.appendChild(new BX.Calendar.Controls.AddButton({
						addEntry: function(){
							BX.Calendar.EntryManager.openEditSlider({
								type: this.util.type,
								ownerId: this.util.ownerId,
								userId: parseInt(this.currentUser.id)
							});
						}.bind(this),
						addTask: this.showTasks ?
							function(){
								BX.SidePanel.Instance.open(this.util.getEditTaskPath(), {loader: "task-new-loader"});
							}.bind(this)
							: null
					}).getWrap());
				}
			}
		},

		refresh: function ()
		{
			this.getView().redraw();
		},

		reload: function (params)
		{
			if (params && params.syncGoogle)
			{
				this.reloadGoogle = true;
			}
			this.entryController.clearLoadIndexCache();
			this.refresh();
		},

		showStartUpEntry: function(startupEntry)
		{
			BX.Calendar.EntryManager.openViewSlider(startupEntry.ID,
				{
					from: BX.Calendar.Util.parseDate(startupEntry['~CURRENT_DATE']),
					timezoneOffset: startupEntry.TZ_OFFSET_FROM || null
				}
			);
		},

		isExternalMode: function()
		{
			return this.externalMode;
		},

		showLoader: function()
		{
			if (this.viewsCont)
			{
				if (this.entryLoaderNode)
				{
					BX.remove(this.entryLoaderNode);
				}
				this.entryLoaderNode = this.viewsCont.appendChild(BX.adjust(
					this.util.getLoader(200), {
						props: {className: 'calendar-entry-loader'}
					}));
			}
		},

		hideLoader: function()
		{

			if (this.entryLoaderNode)
			{
				BX.addClass(this.entryLoaderNode, 'hide');
				setTimeout(BX.delegate(function(){BX.remove(this.entryLoaderNode);}, this), 300);
			}
		},

		getCurrentViewName: function()
		{
			return this.currentViewName;
		},

		loadCssList: function()
		{
			if (window.top && window.top.BX)
			{
				window.top.BX.loadCSS([
					'/bitrix/components/bitrix/calendar.grid/templates/.default/style.css',
					'/bitrix/js/calendar/new/calendar.css',
					'/bitrix/js/calendar/cal-style.css'
				]);
			}
		},

		handlePullEvent: function(command, params)
		{
			params = BX.Type.isObjectLike(params) ? params : {};
			params.command = command;
			switch(command)
			{
				case 'edit_event':
				case 'delete_event':
					if (BX.Calendar.Util.checkRequestId(params.requestUid))
					{
						this.entryManager.handlePullChanges(params);
						this.reload();
					}
					break;
				case 'set_meeting_status':
					if (BX.Calendar.Util.checkRequestId(params.requestUid))
					{
						this.entryManager.handlePullChanges(params);
						this.reload();
					}
					break;
				case 'edit_section':
				case 'delete_section':
				case 'change_section_subscription':
					this.sectionManager.handlePullChanges(params);
					break;
				case 'change_section_customization':
					BX.reload();
					break;
				case 'refresh_sync_status':
					this.syncInterface.updateSyncStatus(params);
					break;
				case 'add_sync_connection':
					this.syncInterface.addSyncConnection(params);
					break;
				case 'delete_sync_connection':
					this.syncInterface.deleteSyncConnection(params);
					break;
			}
		},

		getCalendarType: function()
		{
			return this.util.type;
		},

		getOwnerId: function()
		{
			return parseInt(this.util.ownerId);
		},

		getUserId: function()
		{
			return parseInt(this.util.userId);
		},

		getSectionInterface: function()
		{
			return new Promise(function(reslve){
				var bx = BX.Calendar.Util.getBX();
				if (bx.Calendar.SectionInterface)
				{
					reslve(bx.Calendar.SectionInterface);
				}
				else
				{
					var extensionName = 'calendar.sectioninterface';
					bx.Runtime.loadExtension(extensionName)
						.then(function(exports)
						{
							if (bx.Calendar.SectionInterface)
							{
								reslve(bx.Calendar.SectionInterface);
							}
							else
							{
								console.error('Extension ' + extensionName + ' not found');
							}
						}
					);
				}

			}.bind(this));
		},

		updateCounters: function()
		{
			return new Promise(
				function(resolve)
				{
					BX.ajax.runAction('calendar.api.calendarajax.updateCounters', {
						data: {}
					}).then(function(response)
						{
							if (BX.Type.isObjectLike(response.data.counters) && this.search)
							{
								this.search.setCountersValue(response.data.counters);
							}
							resolve();
						}.bind(this),
						function (response)
						{
							BX.Calendar.Util.displayError(response.errors);
							resolve(response);
						}.bind(this));
				}.bind(this)
			);
		}
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.Core = Calendar;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.Core = Calendar;
		});
	}
})(window);
