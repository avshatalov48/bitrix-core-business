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
		this.sectionController = new window.BXEventCalendar.SectionController(this, data, config);
		this.entryController = new window.BXEventCalendar.EntryController(this, data);
		//this.section = new window.BXEventCalendar.Section(this, data);
		this.currentViewName = this.util.getUserOption('view') || this.DEFAULT_VIEW;
		this.requests = {};
		this.currentUser = config.user;
		this.ownerUser = config.ownerUser || false;
		this.viewRangeDate = new Date();
		this.keyHandlerEnabled = true;

		// build basic dom structure
		this.build();

		if (!this.externalMode)
		{
			if (config.startupEvent)
			{
				this.showStartUpEntry(config.startupEvent);
			}

			if (config.showNewEventDialog && !this.util.readOnlyMode() && this.entryController.canDo(true, 'add_event'))
			{
				setTimeout(BX.delegate(function(){
					this.getView().showEditSlider();
				}, this), 1000);
			}
		}
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
				BX.bind(this.viewsCont, 'click', BX.proxy(this.handleViewsClick, this));
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

				this.topBlock.appendChild(BX.create('DIV', {style: {clear: 'both'}}));

				this.util.applyHacksHandlersForPopupzIndex();
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

			if (this.currentViewName == view.getName())
			{
				this.setView(view.getName(), {first: true});
			}
		},

		buildViewSwitcher: function()
		{
			this.viewSwitcherCont = BX(this.id + '-view-switcher-container');

			var dropDownMode = !this.viewSwitcherCont;

			if (dropDownMode)
			{
				this.viewSwitcherCont = this.topBlock.appendChild(BX.create('DIV', {props: {className: 'calendar-view-switcher-selector'}}));
			}

			this.viewSwitcher = new window.BXEventCalendar.ViewSwitcher({
				calendar: this,
				wrap: this.viewSwitcherCont,
				dropDownMode: dropDownMode
			});
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

				if (newView && (view != this.currentViewName || !currentView.getIsBuilt()))
				{
					params.currentViewDate = this.getViewRangeDate();
					params.newViewDate = newView.getAdjustedDate(params.date || false, viewRange, true);

					params.currentView = currentView;
					params.newView = newView;
					this.setViewRangeDate(params.newViewDate);

					this.triggerEvent('beforeSetView', {currentViewName: this.currentViewName, newViewName: view});

					if (params.animation)
					{
						this.viewTransition.transit(params);
					}
					else
					{
						if (view != this.currentViewName)
						{
							currentView.hide();
						}

						if(params.first === true)
						{
							this.initialViewShow = true;
							newView.adjustViewRangeToDate(params.newViewDate, false);
						}
						else
						{
							newView.adjustViewRangeToDate(params.newViewDate);
						}
						this.currentViewName = newView.getName();
					}

					this.util.setUserOption('view', view);
					this.triggerEvent('afterSetView', {viewName: view});
				}
			}
		},

		buildCounters: function()
		{
		},

		registerEventHandlers: function()
		{

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
							if (!result || result.length <= 0 || erInd != -1)
							{
								var errorText = '';
								if (erInd >= 0)
								{
									var ind1 = erInd + 'BX_EVENT_CALENDAR_ACTION_ERROR:'.length, ind2 = result.indexOf('-->', ind1);
									errorText = result.substr(ind1, ind2 - ind1);
								}
								if (params.onerror && typeof params.onerror == 'function')
									params.onerror();

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
				xhr: params.type == 'post' ? BX.ajax.post(params.url, params.data, handler) : BX.ajax.get(params.url, params.data, handler)
			};

			return params;
		},

		cancelRequest: function(reqId)
		{
			if (this.requests[reqId] && this.requests[reqId].status == 'sent')
				this.requests[reqId].status = 'canceled';
		},

		getRequestResult: function(key)
		{
			if (top.BXCRES && typeof top.BXCRES[key] != 'undefined')
				return top.BXCRES[key];

			return {};
		},

		displayError : function(str, bReloadPage)
		{
			var _this = this;
			setTimeout(function(){
				if (!_this.bOnunload)
				{
					alert(str || '[Bitrix Calendar] Request error');
					if (bReloadPage)
						BX.reload();
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
				if (this.views[i].getName() == viewName)
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

		isKeyHandlerEnabled: function()
		{
			var res = this.keyHandlerEnabled
				&& !BX.hasClass(document.body, 'bx-im-fullscreen-block-scroll')
				&& !BX.hasClass(document.body, 'side-panel-disable-scrollbar');

			if (res)
			{
				var i, popups = document.body.querySelectorAll(".popup-window");
				for (i = 0; i < popups.length; i++)
				{
					if (popups[i] && popups[i].style.display != 'none')
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
			if (this.isKeyHandlerEnabled())
			{
				var
					KEY_CODES = this.util.getKeyCodes(),
					keyCode = e.keyCode;

				if (keyCode == KEY_CODES['escape'])
				{
					this.getView().deselectEntry();
				}
				else if (keyCode == KEY_CODES['delete'])
				{
					var selectedEntry = this.getView().getSelectedEntry();
					if (selectedEntry)
					{
						this.entryController.deleteEntry(selectedEntry);
					}
				}

				if (keyCode == KEY_CODES['left'])
				{
					this.showPrevious();
				}
				else if (keyCode == KEY_CODES['right'])
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
				this.sectionButton = this.buttonsCont.appendChild(BX.create("SPAN", {
					props: {className: "webform-small-button webform-small-button-transparent"},
					html: '<span class="">' + BX.message('EC_SECTION_BUTTON') + '</span>'
				}));
				new window.BXEventCalendar.SectionSlider({
					calendar: this,
					button: this.sectionButton
				});

				if (this.util.userIsOwner())
				{
					this.syncButton = this.buttonsCont.appendChild(BX.create("DIV", {
						props: {className: "webform-small-button webform-small-button-transparent calendar-sync-button"},
						html: '<span class="webform-small-button-icon"></span>'
					}));
					this.syncSlider = new window.BXEventCalendar.SyncSlider({
						calendar: this,
						button: this.syncButton
					});
				}

				if (this.util.userIsOwner() || this.util.config.TYPE_ACCESS)
				{
					this.settingsButton = this.buttonsCont.appendChild(BX.create("DIV", {
						props: {className: "webform-small-button webform-small-button-transparent webform-cogwheel"},
						html: '<span class="webform-button-icon"></span>'
					}));

					new window.BXEventCalendar.SettingsSlider({
						calendar: this,
						button: this.settingsButton
					});
				}

				if (!this.util.readOnlyMode())
				{
					this.addButton = new window.BXEventCalendar.AddButton(
						{
							wrap: this.buttonsCont.appendChild(BX.create("SPAN")),
							calendar: this
						}
					);
				}
			}
		},

		refresh: function ()
		{
			this.triggerEvent('beforeRefresh');
			this.getView().refresh();
			this.triggerEvent('afterRefresh');
		},

		reload: function (params)
		{
			this.triggerEvent('beforeReload');
			if (params && params.syncGoogle)
			{
				this.reloadGoogle = true;
			}
			this.entryController.clearLoadIndexCache();
			this.refresh();
			this.triggerEvent('afterReload');
		},

		showStartUpEntry: function(startupEntry)
		{
			var entryObj = new window.BXEventCalendar.Entry(this, startupEntry);
			this.getView().showViewSlider({entry: entryObj});
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