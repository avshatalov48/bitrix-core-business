;(function(window) {

	function View(calendar)
	{
		this.calendar = calendar;
		this.util = calendar.util;
		this.entryController = calendar.entryController;
		this.name = '#calendar view#';
		this.title = this.name;
		this.enabled = true;
		this.contClassName = '';
		this.isBuilt = false;
		this.animateClass = 'calendar-grid-animate';
		this.collapseOffHours = this.util.getUserOption('collapseOffHours', 'Y') == 'Y';

		this.entries = [];
		this.entriesIndex = {};
		BX.addCustomEvent(this.calendar, 'viewOnClick', BX.proxy(this.handleClick, this));
		//BX.addCustomEvent(this.calendar, 'viewOnMouseDown', BX.proxy(this.handleMouseDown, this));
	}

	View.prototype = {
		build: function()
		{
			this.viewCont = BX.create('DIV', {props: {className: this.contClassName}});
		},

		show: function()
		{
			if (!this.isBuilt)
			{
				this.build();
				this.isBuilt = true;
			}
			this.viewCont.style.display = '';
			this.setTitle();
		},


		refresh: function()
		{
			this.displayEntries();
		},

		hide: function()
		{
			this.viewCont.style.display = 'none';
		},

		getName: function()
		{
			return this.name;
		},

		getContainer: function()
		{
			return this.viewCont;
		},

		setTitle: function(title)
		{
			this.calendar.viewTitle.innerHTML = title.replace('#GRAY_START#', '<span class="calendar-top-title-gray">').replace('#GRAY_END#', '</span>');
		},

		getIsBuilt: function()
		{
			return this.isBuilt;
		},

		fadeAnimation: function(container, duration, callback)
		{
			new BX.easing({
				duration: duration || 200,
				start: {opacity: 100},
				finish: {opacity: 0},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function (state)
				{
					container.style.opacity = state.opacity / 100;
				},
				complete: function()
				{
					if (callback && BX.type.isFunction(callback))
						callback();
				}
			}).animate();
		},

		showAnimation: function(container, duration, callback)
		{
			new BX.easing({
				duration: duration || 200,
				start: {opacity: 0},
				finish: {opacity: 100},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function (state)
				{
					container.style.opacity = state.opacity / 100;
				},
				complete: function()
				{
					container.removeAttribute('style');
					if (callback && BX.type.isFunction(callback))
						callback();
				}
			}).animate();
		},

		getArrow: function(type, color, fill)
		{
			var
				borderColor = BX.util.urlencode(color),
				fillColor = fill ? BX.util.urlencode(color) : 'none',
				imageSource = '', arrowNode;

			if (type == 'left')
			{
				arrowNode = BX.create('DIV', {props: {className: 'calendar-event-angle-start-yesterday'}});
				imageSource = 'url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2215px%22%20height%3D%2218px%22%20viewBox%3D%220%200%2015%2018%22%20version%3D%221.1%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%0A%3Cpath%20fill%3D%22' + fillColor + '%22%20stroke%3D%22' + borderColor + '%22%20stroke-width%3D%221%22%20d%3D%22M14.5%2C17.5%20L14.5%2C0.5%20L2.00049088%2C0.5%20C1.78697323%2C0.5%201.57591593%2C0.545584%201.38143042%2C0.633704227%20C0.626846099%2C0.975601882%200.292297457%2C1.86447615%200.634195112%2C2.61906047%20L3.05787308%2C7.96823256%20C3.35499359%2C8.62399158%203.35499359%2C9.37600842%203.05787308%2C10.0317674%20L0.634195112%2C15.3809395%20C0.546074885%2C15.575425%200.500490885%2C15.7864823%200.500490885%2C16%20C0.500490885%2C16.8284271%201.17206376%2C17.5%202.00049088%2C17.5%20L14.5%2C17.5%20Z%22/%3E%0A%3C/svg%3E)';
			}
			else
			{
				arrowNode = BX.create('DIV', {props: {className: 'calendar-event-angle-finish-tomorrow'}});
				imageSource = 'url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2215px%22%20height%3D%2218px%22%20viewBox%3D%220%200%2015%2018%22%20version%3D%221.1%22%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%3E%0A%3Cpath%20fill%3D%22' + fillColor + '%22%20stroke%3D%22' + borderColor + '%22%20stroke-width%3D%221%22%20d%3D%22M0.5%2C0.5%20L0.5%2C17.5%20L8.7031205%2C17.5%20C9.65559352%2C17.5%2010.5253145%2C16.9587787%2010.9460243%2C16.1042565%20L13.8991717%2C10.1059895%20C14.2418971%2C9.40986472%2014.2419701%2C8.59406382%2013.8993692%2C7.89787777%20L10.9458495%2C1.89614482%20C10.5252214%2C1.04140271%209.65538246%2C0.5%208.70274816%2C0.5%20L0.5%2C0.5%20Z%22/%3E%0A%3C/svg%3E)';
			}

			arrowNode.style.backgroundImage = imageSource;

			return arrowNode;
		},

		occupySlot: function(params)
		{
			if (this.days)
			{
				var i;
				for (i = params.startIndex; i < params.endIndex; i++)
				{
					if (this.days[i])
					{
						this.days[i].slots[params.slotIndex] = false;
					}
				}
			}
		},

		showSimplePopup: function(params)
		{
			if (this.calendar.isExternalMode())
			{
				this.calendar.triggerEvent('createNewEntry', params);
				setTimeout(BX.delegate(function()
				{
					if (params.closeCallback && typeof params.closeCallback == 'function')
					{
						params.closeCallback();
					}
				}, this), 300);
				return;
			}

			if (!this.simpleEntryPopup)
			{
				this.simpleEntryPopup = new window.BXEventCalendar.SimpleAddPopup(this.calendar);
			}

			this.simpleEntryPopup.show(params);
		},

		showSimpleViewPopup : function(params)
		{
			if (!this.simpleViewPopup)
			{
				this.simpleViewPopup = new window.BXEventCalendar.SimpleViewPopup(this.calendar);
			}
			else if (this.simpleViewPopup.isShown())
			{
				this.simpleViewPopup.close();
			}

			this.simpleViewPopup.show(params);
		},

		showEditSlider: function(params)
		{
			if (this.simpleViewPopup)
			{
				this.simpleViewPopup.close();
			}

			if (!params || !params.entry)
			{
				params = {};
			}

			if (this.simpleEntryPopup)
			{
				params.newEntryData = this.simpleEntryPopup.getPopupData();
				this.simpleEntryPopup.close();
			}

			if (!this.calendar.editSlider)
			{
				this.calendar.editSlider = new window.BXEventCalendar.EditEntrySlider(this.calendar);
			}

			this.calendar.editSlider.show(params);
		},

		handleEntryClick: function(params)
		{
			params.entry = params.entry || this.getEntryById(params.uid);

			if (this.calendar.isExternalMode())
			{
				return this.calendar.triggerEvent('entryClick', params);
			}

			if (params.entry.isSelected())
			{
				if (params.entry.isTask())
				{
					BX.SidePanel.Instance.open(this.calendar.util.getViewTaskPath(params.entry.id), {loader: "task-new-loader"});
				}
				else
				{
					this.showViewSlider({
						entry: params.entry
					});
				}
			}

			this.selectEntry(params.entry);
			if (this.name == 'week' || this.name == 'month')
			{
				this.showSimpleViewPopup(params);
			}
		},

		showViewSlider: function(params)
		{
			if (!this.calendar.util.useViewSlider())
			{
				return;
			}

			if (!this.calendar.viewSlider)
			{
				this.calendar.viewSlider = new window.BXEventCalendar.ViewEntrySlider(this.calendar);
			}

			this.calendar.viewSlider.show(params);

			if (this.simpleViewPopup)
			{
				this.simpleViewPopup.close();
			}
			setTimeout(BX.delegate(function(){
				if (this.simpleViewPopup)
				{
					this.simpleViewPopup.close();
				}
			}, this), 200);
		},

		isActive: function()
		{
			return this.calendar.currentViewName === this.name;
		},

		getEntryById: function(uniqueId)
		{
			if (uniqueId && this.entriesIndex[uniqueId] !== undefined && this.entries[this.entriesIndex[uniqueId]])
				return this.entries[this.entriesIndex[uniqueId]];
			return false;
		},

		selectEntry: function(entry)
		{
			if (entry && entry.parts)
			{
				if (this.selectedEntry)
					this.deselectEntry();

				if (entry.select)
					entry.select();

				entry.parts.forEach(function(part)
				{
					part.params = this.selectEntryPart(part.params, entry.color, entry.isExpired());
				}, this);

				this.selectedEntry = entry;

				if (this.name !== 'week' && this.name !== 'month')
				{
					this.showAdditionalInfo(entry);
				}
			}
		},

		selectEntryPart: function(params, color)
		{
			if (params.wrapNode)
			{
				params.backupWrapZIndex = params.wrapNode.style.zIndex || '';
				params.wrapNode.style.zIndex = 4000;
				params.backupWrapNodeClass = params.wrapNode.className;

				BX.addClass(params.wrapNode, 'calendar-event-line-fill');
				BX.addClass(params.wrapNode, 'active');
			}

			if (params.blockBackgroundNode)
			{
				params.backupBlockOpacity = params.blockBackgroundNode.style.opacity;
				params.blockBackgroundNode.style.opacity = 1;
			}

			if (params.innerContainer)
			{
				params.backupBackground = params.innerContainer.style.background;
				params.backupBorderColor = params.innerContainer.style.borderColor;
				params.innerContainer.style.backgroundColor = color;
				params.innerContainer.style.borderColor = color;
			}

			if (params.nameNode)
			{
				params.backupNameColor = params.nameNode.style.color;
				params.nameNode.style.color = '#fff';
			}

			if (params.timeNode)
			{
				params.backupTimeColor = params.timeNode.style.color;
				params.backupTimeZIndex = params.timeNode.style.zIndex || 0;
				params.timeNode.style.color = '#fff';
				params.timeNode.style.zIndex = 200;
			}

			return params;
		},

		deselectEntry: function(entry)
		{
			if (!entry && this.selectedEntry)
				entry = this.selectedEntry;

			if (entry)
			{
				if (entry.deselect)
					entry.deselect();

				entry.parts.forEach(function (part)
				{
					if (part.params.wrapNode)
					{
						part.params.wrapNode.className = part.params.backupWrapNodeClass;
						part.params.wrapNode.style.zIndex = part.params.backupWrapZIndex;
					}

					if (part.params.innerContainer)
					{
						part.params.innerContainer.style.backgroundColor = part.params.backupBackground;
						part.params.innerContainer.style.borderColor = part.params.backupBorderColor;
					}

					if (part.params.blockBackgroundNode)
					{
						part.params.blockBackgroundNode.style.opacity = part.params.backupBlockOpacity;
					}

					if (part.params.nameNode)
					{
						part.params.nameNode.style.color = part.params.backupNameColor;
					}

					if (part.params.timeNode)
					{
						part.params.timeNode.style.color = part.params.backupTimeColor;
						part.params.timeNode.style.zIndex = part.params.backupTimeZIndex;
					}
				}, this);
			}

			BX.remove(this.calendar.additionalInfoOuter);
			this.selectedEntry = false;
		},

		getSelectedEntry: function()
		{
			return this.selectedEntry || false;
		},

		preloadEntries: function()
		{
		},

		showAllEventsInPopup: function(params)
		{
			var
				innerCont,
				popup;

			innerCont = BX.create('DIV', {
				props: {className: 'calendar-all-events-popup calendar-custom-scroll'},
				events: {click : BX.proxy(this.calendar.handleViewsClick, this.calendar)}
			});

			params.day.entries.list.sort(this.calendar.entryController.sort);

			var taskWrap, eventsWrap;
			params.day.entries.list.forEach(function(entryItem)
			{
				if (entryItem.entry)
				{
					if (entryItem.entry.isTask())
					{
						if (!taskWrap)
						{
							innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-event-title'}, text: BX.message('EC_ENTRIES_TASKS')}));
							taskWrap = innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block'}}));
						}

						this.displayEntryPiece({
							entry: entryItem.entry,
							part: entryItem.part,
							holder: taskWrap,
							popupMode: true
						});
					}
					else
					{
						if (!eventsWrap)
						{
							innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-event-title'}, text: BX.message('EC_ENTRIES_EVENTS')}));
							eventsWrap = innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block'}}));
						}

						this.displayEntryPiece({
							entry: entryItem.entry,
							part: entryItem.part,
							holder: eventsWrap,
							popupMode: true
						});
					}
				}
			}, this);


			popup = BX.PopupWindowManager.create(this.calendar.id + "-all-events-popup", params.day.hiddenStorage,
				{
					autoHide: true,
					closeByEsc: true,
					offsetTop: -2,
					offsetLeft: this.getDayWidth() / 2 + 4,
					lightShadow: true,
					content: innerCont
				});

			popup.setAngle({offset: 118});
			popup.show(true);
			this.allEventsPopup = popup;

			BX.addCustomEvent(popup, 'onPopupClose', function()
			{
				popup.destroy();
			});
		},

		showAdditionalInfo: function(entry)
		{
			BX.remove(this.calendar.additionalInfoOuter);

			this.calendar.additionalInfoOuter = this.calendar.rightBlock.appendChild(BX.create('DIV', {props: {className: 'calendar-right-block hide'}}));

			if (!this.simpleViewPopup)
				this.simpleViewPopup = new window.BXEventCalendar.SimpleViewPopup(this.calendar);

			this.calendar.additionalInfoOuter.appendChild(this.simpleViewPopup.createContent({entry: entry}));
		},

		showNavigationCalendar: function()
		{
			setTimeout(BX.delegate(function()
			{
				if(this.calendar.rightBlock)
				{
					if (!this.calendar.navCalendar)
					{
						this.calendar.navCalendar = new window.BXEventCalendar.NavigationCalendar(this.calendar, {
							wrap: this.calendar.rightBlock.appendChild(BX.create('DIV', {props: {className: 'calendar-right-block'}}))
						});
					}

					if (this.calendar.initialViewShow)
					{
						BX.addClass(this.calendar.mainCont, "calendar-main-container-small-calendar");
						this.calendar.initialViewShow = false;
					}
					this.calendar.navCalendar.show();
				}
			}, this), 0);
		},

		getDayWidth: function()
		{
			var result = 200;
			if (this.days && this.days[0] && this.days[0].node)
			{
				result = this.days[0].node.offsetWidth || result;
			}
			return Math.min(result, 400);
		}
	};

	// Day view of the calendar
	function DayView()
	{
		View.apply(this, arguments);
		this.initConfig();
		this.preBuild();
	}
	DayView.prototype = Object.create(View.prototype);
	DayView.prototype.constructor = DayView;

	DayView.prototype.initConfig = function()
	{
		this.name = 'day';
		this.gridLineHeight = 60;
		this.slotHeight = 20;
		this.title = BX.message('EC_VIEW_DAY');
		this.entryWidthOffset = 2;
		this.lastEntryWidthOffset = 14;

		this.contClassName = 'calendar-day-view';
		this.gridWrapClass = 'calendar-grid-wrap';
		this.fullDayContClass = 'calendar-grid-day-full-days-events-holder';
		this.fullDayContHolderClass = 'calendar-grid-week-full-days-events-holder-grid';
		this.topEntryHolderClass = 'calendar-grid-day-events-holder';

		this.outerGridClass = 'calendar-grid-day-container';
		this.gridClass = 'calendar-grid-day';
		this.gridClassCurrent = 'calendar-grid-day-current';

		this.gridClassNext = 'calendar-grid-day-left-slide';
		this.gridClassPrevious = 'calendar-grid-day-right-slide';
		this.changeNextClass = 'calendar-change-day-left-slide';
		this.changePreviousClass = 'calendar-change-day-right-slide';

		this.gridRowClass = 'calendar-grid-day-row';
		this.gridCellClass = 'calendar-grid-day-cell';
		this.gridTimelinesClass = 'calendar-grid-day-time-lines';
		this.gridTimelineHourClass = 'calendar-grid-day-time-line-hour';
		this.gridTimelineHourLabelClass = 'calendar-grid-day-time-line-hour-label';
		this.gridTimelineHourLabelClassInner = 'calendar-grid-week-time-line-hour-label-inner';

		this.gridNowTimeClass = 'calendar-grid-day-time-line-hour-now';
		this.gridNowTimeLabelClass = 'calendar-grid-day-time-line-hour-label';
		this.gridNowTimeLineClass = 'calendar-grid-day-time-line-hour-now-line';
		this.gridNowTimeDotClass = 'calendar-grid-day-time-line-hour-now-dot';
		this.gridTimeTranslucentClass = 'calendar-grid-time-line-translucent';

		this.offHoursClass = 'calendar-grid-off-hours';
		this.offHoursCollapseClass = 'calendar-grid-off-hours-collapse';
		this.offHoursAnimateClass = 'calendar-grid-off-hours-animate';
		this.offHoursFastAnimateClass = 'calendar-grid-off-hours-fast-animate';

		this.dayCount = 1;
	};

	DayView.prototype.preBuild = function()
	{
		this.viewCont = BX.create('DIV', {props: {className: this.contClassName}, style: {display: 'none'}});
	};

	DayView.prototype.build = function()
	{
		this.titleCont = this.viewCont.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-week-row-days-week'}}));

		var workTime = this.util.getWorkTime();
		this.checkTimelineScroll(!this.collapseOffHours || (workTime.end - workTime.start) * this.gridLineHeight + 20 > this.util.getViewHeight());
		this.fullDayEventsCont = this.viewCont.appendChild(BX.create('DIV', {props: {className: this.fullDayContClass}}));

		this.gridWrap = this.viewCont.appendChild(BX.create('DIV', {
			props: {className: this.gridWrapClass},
			style: {height: this.util.getViewHeight() + 'px'}
		}));
		this.outerGrid = this.gridWrap.appendChild(BX.create('DIV', {props: {className: this.outerGridClass}}));

		this.grid = this.outerGrid.appendChild(BX.create('DIV', {props: {className: this.gridClass + ' ' + this.gridClassCurrent}}));

		BX.bind(this.gridWrap, 'mousedown', BX.proxy(this.handleMousedown, this));
	};

	DayView.prototype.show = function()
	{
		View.prototype.show.apply(this, arguments);
		this.buildDaysGrid();

		this.showNavigationCalendar();
		BX.remove(this.calendar.additionalInfoOuter);

		this.displayEntries();
	};

	DayView.prototype.hide = function()
	{
		View.prototype.hide.apply(this, arguments);
	};

	DayView.prototype.setFullDayHolderSize = function(size)
	{
		this.fullDayEventsCont.style.height = (size * (this.slotHeight + 1)) + 'px';
	};

	DayView.prototype.increaseViewRangeDate = function()
	{
		this.changeViewRangeDate(this.dayCount);
		this.setTitle();

		if (this.gridWrap)
			this.gridWrap.style.overflowX = 'hidden';

		var nextGrid = this.outerGrid.appendChild(BX.create('DIV', {props: {className: this.gridClass + ' ' + this.gridClassNext + ' ' + this.animateClass}}));
		BX.addClass(this.grid, this.animateClass);

		this.buildDaysGrid({grid: nextGrid});

		// Prepare entries while animatin goes
		this.preloadEntries();

		setTimeout(BX.delegate(function()
		{
			// Start CSS animation
			BX.addClass(this.outerGrid, this.changeNextClass);

			// Wait till the animation ends
			setTimeout(BX.delegate(function()
			{
				// Clear old grid, now it's hidden and use new as old one
				BX.removeClass(this.outerGrid, this.changeNextClass);
				BX.removeClass(nextGrid, this.gridClassNext);
				BX.addClass(nextGrid, this.gridClassCurrent);
				BX.remove(this.grid);
				this.grid = nextGrid;
				BX.removeClass(this.grid, this.animateClass);
				this.gridWrap.style.overflowX = '';

				// Display loaded entries for new view range
				this.displayEntries();
			}, this), 400);
		}, this), 0);
	};

	DayView.prototype.decreaseViewRangeDate = function()
	{
		this.changeViewRangeDate(-this.dayCount);
		this.setTitle();

		this.gridWrap.style.overflowX = 'hidden';

		var previousGrid = this.outerGrid.appendChild(BX.create('DIV', {props: {className: this.gridClass + ' ' + this.gridClassPrevious + ' ' + this.animateClass}}));
		BX.addClass(this.grid, this.animateClass);
		this.buildDaysGrid({grid: previousGrid});

		setTimeout(BX.delegate(function()
		{
			// Start CSS animation
			BX.addClass(this.outerGrid, this.changePreviousClass);

			// Wait till the animation ends
			setTimeout(BX.delegate(function()
			{
				// Clear old grid, now it's hidden and use new as old one
				BX.removeClass(this.outerGrid, this.changePreviousClass);
				BX.removeClass(previousGrid, this.gridClassPrevious);
				BX.addClass(previousGrid, this.gridClassCurrent);
				BX.remove(this.grid);
				this.grid = previousGrid;
				BX.removeClass(this.grid, this.animateClass);
				this.gridWrap.style.overflowX = '';

				// Display loaded entries for new view range
				this.displayEntries();
			}, this), 400);
		}, this), 0);
	};

	DayView.prototype.changeViewRangeDate = function(value)
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			newDate = new Date(viewRangeDate.getTime());

		newDate.setDate(newDate.getDate() + value);

		this.calendar.setViewRangeDate(newDate);
		return newDate;
	};

	DayView.prototype.getViewRange = function()
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			endDate = new Date(viewRangeDate.getTime());
		endDate.setDate(endDate.getDate() + this.dayCount);
		return {start: viewRangeDate, end: endDate};
	};

	DayView.prototype.getAdjustedDate = function(date, viewRange)
	{
		if (!date)
		{
			date = new Date();
		}

		if (viewRange && date.getTime() < viewRange.start.getTime())
		{
			date = new Date(viewRange.start.getTime());
		}

		if (viewRange && date.getTime() > viewRange.end.getTime())
		{
			date = new Date(viewRange.end.getTime());
		}

		var viewRangeDate = false;

		if (date && date.getTime)
		{
			date.setHours(0, 0, 0, 0);
			viewRangeDate = new Date(date.getTime());
		}

		return viewRangeDate;
	};

	DayView.prototype.adjustViewRangeToDate = function(date, animate)
	{
		var
			currentViewRangeDate = this.calendar.getViewRangeDate(),
			viewRangeDate = false;

		if (date && date.getTime)
		{
			date.setHours(0, 0, 0, 0);

			var diff = (date.getTime() - currentViewRangeDate.getTime()) / this.calendar.util.dayLength;
			if (diff == this.dayCount)
			{
				this.increaseViewRangeDate();
			}
			else if (diff == -this.dayCount)
			{
				this.decreaseViewRangeDate();
			}
			else
			{
				viewRangeDate = new Date(date.getTime());
				viewRangeDate.setHours(0, 0, 0, 0);
				this.calendar.setViewRangeDate(viewRangeDate);
				if (animate === false)
				{
					this.show();
				}
				else
				{
					this.fadeAnimation(this.getContainer(), 100, BX.delegate(function ()
					{
						this.show();
						this.getContainer().style.opacity = 0;
						this.showAnimation(this.getContainer(), 300)
					}, this));
				}
			}
		}

		return viewRangeDate;
	};

	DayView.prototype.buildDaysGrid = function(params)
	{
		if (!params)
			params = {};

		var
			i, dayCode,
			grid = params.grid || this.grid,
			viewRangeDate = this.calendar.getViewRangeDate(),
			date = new Date(viewRangeDate.getTime());

		if (this.dayCount > 1)
		{
			date = this.getAdjustedDate(date);
		}

		BX.cleanNode(grid);
		BX.cleanNode(this.fullDayEventsCont);

		this.holderTitle = this.fullDayEventsCont.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-day-full-days-events-holder-title'}, text: BX.message('EC_VIEW_DAY')}));

		this.fullDayEventsHolderCont = this.fullDayEventsCont.appendChild(BX.create('DIV', {props: {className: this.fullDayContHolderClass}}));
		this.topEntryHolder = this.fullDayEventsCont.appendChild(BX.create('DIV', {props: {className: this.topEntryHolderClass}}));

		this.gridRow = grid.appendChild(BX.create('DIV', {
			props: {className: this.gridRowClass + ' ' + this.animateClass},
			style: {height: this.getDayGridHeight() + 'px'}
		}));

		this.dayIndex = {};
		this.days = [];

		if (this.titleCont)
		{
			BX.cleanNode(this.titleCont);
		}

		this.gridRowShadow = BX.create('DIV', {
			props: {className: 'calendar-grid-week-row-shadow'}
		});

		for (i = 0; i < this.dayCount; i++)
		{
			dayCode = this.util.getDayCode(date);

			this.fullDayEventsHolderCont.appendChild(BX.create('DIV', {
				attrs: {'data-bx-calendar-week-day': dayCode},
				props: {className: this.gridCellClass}
			}));

			this.buildDayCell({date: date, month: 'previous', grid: grid});

			if (this.dayCount > 1)
				date.setDate(date.getDate() + 1);

			this.gridRowShadow.appendChild(BX.create('DIV', {
				attrs: {'data-bx-calendar-timeline-day': dayCode},
				props: {className: 'calendar-grid-week-cell'},
				html: '<span class="calendar-grid-cell-inner"></span>'
			}));
		}

		// time lines
		this.timeLinesCont = this.gridRow.appendChild(BX.create('DIV', {props: {className: this.gridTimelinesClass}}));

		this.timelineEntryHolder = this.gridRow.appendChild(BX.create('DIV', {props: {className: this.topEntryHolderClass}}));

		this.timeLinesIndex = [];
		for (i = 0; i <= 24; i++) // Every hour
		{
			this.timeLinesIndex[i] = this.timeLinesCont.appendChild(BX.create('DIV', {
				props: {className: this.gridTimelineHourClass},
				html: '<div class="' + this.gridTimelineHourLabelClass + '">' + this.calendar.util.formatTime(i, 0, true) + '</div>',
				style: {top: ((i) * this.gridLineHeight) + 'px'}
			}));
		}

		this.gridRow.appendChild(this.gridRowShadow);

		setTimeout(BX.delegate(function()
		{
			if (!this.gridWrap.scrollTop && !this.collapseOffHours)
			{
				var workTime = this.util.getWorkTime();
				this.gridWrap.scrollTop = workTime.start * this.gridLineHeight - 5;
			}
		}, this), 0);


		this.showOffHours();

		// Show "now" red time line
		this.showNowTime(this.timeLinesCont);

		this.gridRow.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-day-events-holder'}}));
	};

	DayView.prototype.buildDayCell = function(params)
	{
		var
			date = params.date,
			titleClassName = '',
			className = '',
			time = Math.round(date.getTime() / 1000) * 1000,
			day = date.getDay(),
			dayCode = this.util.getDayCode(date),
			weekDay = this.util.getWeekDayByInd(day);

		if (params.month == 'previous')
		{
			className += ' calendar-grid-previous-month-day';
		}
		else if (params.month == 'next')
		{
			className += ' calendar-grid-next-month-day';
		}
		if (this.util.isHoliday(date))
		{
			className += ' calendar-grid-holiday';
		}

		if (this.util.isToday(date))
		{
			titleClassName += ' calendar-grid-today';
		}

		if (this.titleCont && this.name == 'week')
		{
			this.titleCont.appendChild(BX.create('DIV', {
				props: {className: this.gridCellClass + titleClassName},
				html: '<span class="calendar-grid-cell-inner" data-bx-calendar-date="' + time + '">' +
				(BX.message('EC_WEEK_TITLE')
					.replace('#DAY_OF_WEEK#', BX.date.format('D', time / 1000))
					.replace('#DATE#', date.getDate())) +
				'</span>'
			}));
		}
		else if (this.titleCont)
		{
			this.titleCont.appendChild(BX.create('DIV', {
				props: {className: this.gridCellClass + titleClassName},
				html: '<span class="calendar-grid-cell-inner" data-bx-calendar-date="' + time + '">' +
				'<span class="calendar-day-of-week-day">' + BX.date.format('l', time / 1000) + '</span>' +
				'</span>'
			}));
		}

		this.days.push({
			date: new Date(date.getTime()),
			dayOffset: this.util.getWeekDayOffset(weekDay),
			node: this.gridRow.appendChild(BX.create('DIV', {
				attrs: {'data-bx-calendar-timeline-day': dayCode},
				props: {
					className: this.gridCellClass + className + ' a1'
				},
				html: '<span class="calendar-grid-cell-inner"></span>'
			})),
			dayCode: dayCode
		});
		this.dayIndex[this.days[this.days.length - 1].dayCode] = this.days.length - 1;

		this.calendar.dragDrop.registerTimelineDay(this.days[this.days.length - 1]);

	};

	DayView.prototype.setTitle = function()
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			time = viewRangeDate.getTime() / 1000;

		View.prototype.setTitle.apply(this, [BX.date.format(BX.message('EC_DATE_FORMAT_1_MAY'), time) + ' #GRAY_START#' + BX.date.format('Y', time) + '#GRAY_END#']);
	};

	DayView.prototype.displayEntries = function(params)
	{
		var
			i, entry, part, dayPos, day, entryStarted,
			maxTopEntryCount = 0,
			viewRange = this.getViewRange();

		if (!params)
			params = {};

		if (params.reloadEntries !== false)
		{
			this.entries = this.entryController.getList({
				startDate: new Date(viewRange.start.getFullYear(), viewRange.start.getMonth(), 1),
				finishDate: new Date(viewRange.end.getFullYear(), viewRange.end.getMonth() + 1, 1),
				viewRange: viewRange,
				finishCallback: BX.proxy(this.displayEntries, this)
			});
		}

		this.partsStorage = [];
		this.timelinePartsStorage = [];

		// Clean holders
		BX.cleanNode(this.topEntryHolder);
		BX.cleanNode(this.timelineEntryHolder);
		this.fullDayEventsCont.style.height = '';

		// Clean days
		this.days.forEach(function(day)
		{
			day.slots = [];
			day.timelineMap = {};
			day.entries = {
				topList: [],
				started: [],
				timeline: [],
				hidden: []
			};
		});

		if (this.entries && this.entries.length)
		{
			// Prepare for arrangement
			for (i = 0; i < this.entries.length; i++)
			{
				entry = this.entries[i];
				this.entriesIndex[entry.uid] = i;
				entry.cleanParts();
				entryStarted = false;

				for (dayPos = this.dayIndex[entry.startDayCode]; dayPos < this.days.length; dayPos++)
				{
					day = this.days[dayPos];
					if (!entry.isLongWithTime()
						&& day.dayCode == entry.startDayCode
						&& day.dayCode == entry.endDayCode && !entry.fullDay)
					{
						part = entry.startPart({
							from: day,
							to: day,
							daysCount: 0,
							fromTimeValue: this.util.getTimeValue(entry.from),
							toTimeValue: this.util.getTimeValue(entry.to)
						});

						day.entries.timeline.push({entry: entry, part: part});
						this.timelinePartsStorage.push({part: part, entry: entry});
						break;
					}
					else
					{
						if (day.dayCode == entry.startDayCode)
						{
							entryStarted = true;
							part = entry.startPart({from: day, daysCount: 0});
							day.entries.started.push({entry: entry, part: part});
						}

						if (entryStarted)
						{
							day.entries.topList.push({entry: entry, part: part});
							part.daysCount++;
							part.to = day;

							if (day.entries.topList.length > maxTopEntryCount)
								maxTopEntryCount = day.entries.topList.length;

							if (day.dayCode == entry.endDayCode ||
								day.dayOffset == this.dayCount - 1 /* for week view */||
								this.dayCount == 1 /*for day view */)
							{
								// here we know where part of event starts and ends
								this.partsStorage.push({part: part, entry: entry});

								// Event finished
								if (day.dayCode == entry.endDayCode)
								{
									break;
								}
							}
						}
					}
				}
			}
		}

		this.setFullDayHolderSize(Math.max(maxTopEntryCount, 1));

		if (this.entries && this.entries.length)
		{
			this.displayTopEntries();
			this.displayTimelineEntries();

			this.slotsCount = 100;

			this.arrangeTopEntries();
			this.arrangeTimelineEntries();
		}

		BX.addClass(this.grid, 'calendar-events-holder-show');
		BX.addClass(this.fullDayEventsCont, 'calendar-events-holder-show');

		var workTime = this.util.getWorkTime();
		this.checkTimelineScroll(!this.collapseOffHours || (workTime.end - workTime.start) * this.gridLineHeight + 20 > this.util.getViewHeight());
	};

	DayView.prototype.arrangeTopEntries = function()
	{
		var
			i, j, dayPos, day, entry, entryPart, entryDisplayed;

		for (dayPos = 0; dayPos < this.days.length; dayPos++)
		{
			day = this.days[dayPos];

			if (day.entries.started.length > 0)
			{
				day.entries.started.sort(this.calendar.entryController.sort);

				for(i = 0; i < day.entries.started.length; i++)
				{
					if (day.entries.started[i])
					{
						entry = day.entries.started[i].entry;
						entryPart = day.entries.started[i].part;

						if (!entry.checkPartIsRegistered(entryPart))
							continue;

						entryDisplayed = false;

						for(j = 0; j < this.slotsCount; j++)
						{
							if (day.slots[j] !== false)
							{
								this.occupySlot({slotIndex: j, startIndex: dayPos, endIndex: dayPos + entryPart.daysCount});
								entryDisplayed = true;

								entry.getWrap(entryPart.partIndex).style.top = (j * this.slotHeight) + 'px';
								break;
							}
						}
					}

					if (day.hiddenStorage && day.entries.hidden.length > 0)
					{
						day.hiddenStorageText.innerHTML = BX.message('EC_SHOW_ALL') + ' (' + day.entries.list.length + ')';
					}
				}
			}
		}
	};

	DayView.prototype.arrangeTimelineEntries = function()
	{
		var
			TIME_ACCURACY = 30, // in minutes
			MIN_BACK_ENTRY_OFFSET = 33,
			MIN_BACK_ENTRY_TIME_OFFSET = 20,
			MIN_TIME_WIDTH = 40,
			ENTRY_NAME_OFFSET = 23,
			LAYER_LEFT_OFFSET = 6,
			LEFT_OFFSET = 2,
			lastCount, count, lastCode,
			entryIndex, i, dayPos, day,
			parallelPosition,
			startList,
			timeFrom, timeTo, l, layerIndex,
			backEntry,backEntryOffset,
			offsetWidthRate,
			backEntriesList,
			entry, entryPart;

		function occupyLayer(params)
		{
			var timeIndex, layerInfo;
			// Occupy Layer
			for (timeIndex = params.timeFrom; timeIndex < params.timeTo; timeIndex++)
			{
				if (!params.layers[timeIndex])
					params.layers[timeIndex] = [];

				layerInfo = params.day.layers[timeIndex][params.layerIndex] || {entries: [], start: []};
				layerInfo.entries.push(params.entryIndex);
				if (timeIndex == params.timeFrom)
				{
					layerInfo.start.push(params.entryIndex);
					params.entryPart.layerParallels = layerInfo.start.length;
				}

				params.day.layers[timeIndex][params.layerIndex] = layerInfo;
			}

			params.entryPart.layerIndex = params.layerIndex;
		}

		function isParallelEntries(timeFrom, layerIndex)
		{
			var layerTimeIndex = day.layers[timeFrom][layerIndex];
			return layerTimeIndex && layerTimeIndex.entries && layerTimeIndex.entries.length == layerTimeIndex.start.length;
		}

		function layerIsFree(layerTimeIndex)
		{
			return !layerTimeIndex;
		}

		function checkBackEntries(params)
		{
			var
				timeIndex,
				prevLayer, backEntryId,
				backEntriesList = [],
				backEntries = {};

			for (timeIndex = params.timeFrom; timeIndex < params.timeTo; timeIndex++)
			{
				if (params.layerIndex > 0 && params.day.layers[timeIndex][params.layerIndex - 1])
				{
					prevLayer = params.day.layers[timeIndex][params.layerIndex - 1].entries;
					if (prevLayer.length > 0)
					{
						backEntryId = prevLayer[prevLayer.length - 1];
						if (!backEntries[backEntryId])
						{
							backEntries[backEntryId] = true;
							backEntriesList.push(backEntryId);
						}
					}
				}
			}
			return backEntriesList;
		}

		function getTimeIndex(date, accuracy)
		{
			if (!accuracy)
				accuracy = TIME_ACCURACY;
			return date.getHours() * 60 + Math.floor(date.getMinutes() / accuracy) * accuracy;
		}

		for (dayPos = 0; dayPos < this.days.length; dayPos++)
		{
			day = this.days[dayPos];

			day.entries.timeline.sort(function(a, b)
			{
				if (a.part.fromTimeValue == b.part.fromTimeValue)
					return (b.part.toTimeValue - b.part.fromTimeValue) - (a.part.toTimeValue - a.part.fromTimeValue);
				return a.part.fromTimeValue - b.part.fromTimeValue;
			});

			lastCount = 0;
			lastCode = '';
			count = 0;
			l = 0;

			day.layers = [];
			for (entryIndex = 0; entryIndex < day.entries.timeline.length; entryIndex++)
			{
				entry = day.entries.timeline[entryIndex].entry;
				entryPart = day.entries.timeline[entryIndex].part;

				timeFrom = getTimeIndex(entry.from);
				timeTo = getTimeIndex(entry.to, 1);
				if (timeFrom == timeTo)
					timeTo += 1;

				if (!day.layers)
					day.layers = [];

				layerIndex = 0;
				while (true)
				{
					// Empty time
					if (!day.layers[timeFrom]
						|| isParallelEntries(timeFrom, layerIndex)
						|| layerIsFree(day.layers[timeFrom][layerIndex])
					)
					{
						occupyLayer({
							day: day,
							timeFrom: timeFrom,
							timeTo: timeTo,
							layers: day.layers,
							entryIndex: entryIndex,
							layerIndex: layerIndex,
							entryPart: entryPart
						});
						break;
					}
					layerIndex++;
				}
			}

			for (entryIndex = 0; entryIndex < day.entries.timeline.length; entryIndex++)
			{
				if (day.entries.timeline[entryIndex])
				{
					entry = day.entries.timeline[entryIndex].entry;
					entryPart = day.entries.timeline[entryIndex].part;

					timeFrom = getTimeIndex(entry.from);
					timeTo = getTimeIndex(entry.to, 1);
					if (timeFrom == timeTo)
						timeTo += 1;

					if (!entry.checkPartIsRegistered(entryPart)
						|| !day.layers[timeFrom]
						|| !day.layers[timeFrom][entryPart.layerIndex]
					)
					{
						continue;
					}

					startList = day.layers[timeFrom][entryPart.layerIndex].start;

					if (entryPart.params && entryPart.params.wrapNode)
					{
						entryPart.params.wrapNode.style.zIndex = timeFrom;
					}

					entryPart.absoluteLeftOffset = LEFT_OFFSET;
					if (entryPart.layerIndex > 0)
					{
						backEntriesList = checkBackEntries({
							day: day,
							entryIndex: entryIndex,
							layerIndex: entryPart.layerIndex,
							timeFrom: timeFrom,
							timeTo: timeTo
						});

						for (i = 0; i < backEntriesList.length; i++)
						{
							backEntry = day.entries.timeline[backEntriesList[i]];
							if (backEntry && backEntry.part && backEntry.part.params && entryPart.params.wrapNode)
							{
								backEntryOffset = parseInt(entryPart.params.wrapNode.style.top) - parseInt(backEntry.part.params.wrapNode.style.top);

								if (backEntryOffset > MIN_BACK_ENTRY_OFFSET)
								{
									entryPart.offsetFractionLeft = backEntry.part.offsetFractionWidth * 0.1;
								}
								else
								{
									entryPart.offsetFractionLeft = backEntry.part.offsetFractionWidth * 0.45;
								}

								entryPart.offsetFractionLeftTotal = backEntry.part.offsetFractionLeftTotal + entryPart.offsetFractionLeft;
								entryPart.offsetFractionWidth = 1 - entryPart.offsetFractionLeftTotal;

								if (this.dayCount > 1) // Weeks
								{
									entryPart.offsetLeftRate = entryPart.from.dayOffset + entryPart.offsetFractionLeftTotal;
								}
								else // One day
								{
									entryPart.offsetLeftRate = entryPart.offsetFractionLeftTotal;
								}

								entryPart.absoluteLeftOffset = (backEntry.absoluteLeftOffset || LEFT_OFFSET) + LAYER_LEFT_OFFSET;
								offsetWidthRate = 1 - entryPart.offsetFractionLeftTotal;

								if (backEntryOffset <= MIN_BACK_ENTRY_OFFSET)
								{
									if (backEntryOffset < MIN_BACK_ENTRY_TIME_OFFSET)
									{
										backEntry.part.params.timeNode.style.maxWidth = 'calc(' + ((1 - entryPart.offsetFractionWidth) * 100) + '% - 4px)';
										if (backEntry.part.params.timeNode.offsetWidth < MIN_TIME_WIDTH)
										{
											backEntry.part.params.timeNode.style.textOverflow = 'clip';
											backEntry.part.params.timeNode.style.maxWidth = MIN_TIME_WIDTH + 'px';
										}
									}

									backEntry.part.params.nameNode.style.maxWidth = 'calc(' + ((1 - entryPart.offsetFractionWidth) * 100) + '% - 4px)';

									if (backEntry.part.params.nameNode.offsetWidth < MIN_TIME_WIDTH)
									{
										backEntry.part.params.nameNode.style.textOverflow = 'clip';
										backEntry.part.params.nameNode.style.maxWidth = 'calc(' + ((1 - entryPart.offsetFractionWidth) * 100) + '% + 5px)';

										this.checkTimelineEntrySize(backEntry.part, backEntry.entry, true);
									}
								}
								else
								{
									backEntry.part.params.nameNode.style.maxHeight = (backEntryOffset - ENTRY_NAME_OFFSET) + 'px';
								}

								entryPart.params.wrapNode.style.left = 'calc((100% / ' + this.dayCount + ') * ' + entryPart.offsetLeftRate + ')';

								entryPart.params.wrapNode.style.width = 'calc(100% / (' + this.dayCount + ') * ' + entryPart.offsetFractionWidth + ' - ' + this.lastEntryWidthOffset + 'px)';

								BX.addClass(entryPart.params.wrapNode, 'calendar-bordered-block');
							}
						}
					}

					if (startList.length > 1)
					{
						parallelPosition = BX.util.array_search(entryIndex, day.layers[timeFrom][entryPart.layerIndex].start);
						var entryWidthOffset = this.entryWidthOffset;
						if (parallelPosition == day.layers[timeFrom][entryPart.layerIndex].start.length - 1)
						{
							entryWidthOffset = this.lastEntryWidthOffset;
							if (entryPart.absoluteLeftOffset > LEFT_OFFSET)
							{
								entryWidthOffset += (entryPart.absoluteLeftOffset / startList.length) + 1;
							}
						}

						if (this.dayCount > 1) // Weeks
						{
							entryPart.params.wrapNode.style.width = 'calc(100% / (' + this.dayCount + ' * ' + startList.length + ') - ' + entryWidthOffset + 'px)';
							entryPart.params.wrapNode.style.left = 'calc((100% / ' + this.dayCount + ') * ' + entryPart.from.dayOffset + ' + 100% * ' + parallelPosition + '/ (' + this.dayCount + ' * ' + startList.length + ') + ' + entryPart.absoluteLeftOffset + 'px)';
						}
						else
						{
							entryPart.params.wrapNode.style.width = 'calc(100% / (' + this.dayCount + ' * ' + startList.length + ') - ' + entryWidthOffset + 'px)';

							entryPart.params.wrapNode.style.left = 'calc(100% * ' + parallelPosition + '/ ' + startList.length + ' + ' + entryPart.absoluteLeftOffset +'px)';
						}
					}

					this.checkTimelineEntrySize(entryPart, entry, true);
				}
			}
		}
	};

	DayView.prototype.fillTimelineMap = function(timelineMap, entry, entryIndex)
	{
		var
			i,
			from = entry.from.getHours() * 60 + entry.from.getMinutes(),
			to = entry.to.getHours() * 60 + entry.to.getMinutes();

		for (i = from; i < to; i++)
		{
			if (!timelineMap[i])
				timelineMap[i] = [];
			timelineMap[i].push(entryIndex);
		}
	};

	DayView.prototype.displayTopEntry = function(params)
	{
		var
			res,
			entry = params.entry,
			from = params.part.from,
			daysCount = params.part.daysCount,
			partWrap, dotNode, innerNode, nameNode, timeNode, endTimeNode, innerContainer,
			entryClassName = 'calendar-event-line-wrap',
			deltaPartWidth = 0,
			startArrow, endArrow;

		if (entry.isFullDay())
		{
			entryClassName += ' calendar-event-line-fill';
		}
		else if (entry.isLongWithTime())
		{
			entryClassName += ' calendar-event-line-border';
		}

		if (entry.isExternal())
		{
			entryClassName += ' calendar-event-line-intranet';
		}

		if (this.util.getDayCode(entry.from) !== this.util.getDayCode(from.date))
		{
			entryClassName += ' calendar-event-line-start-yesterday';
			deltaPartWidth += 8;
			startArrow = this.getArrow('left', entry.color, entry.isFullDay());
		}

		if (this.util.getDayCode(entry.to) !== this.util.getDayCode(params.part.to.date))
		{
			entryClassName += ' calendar-event-line-finish-tomorrow';
			endArrow = this.getArrow('right', entry.color, entry.isFullDay());
			deltaPartWidth += 12;
		}

		if (startArrow && !endArrow)
		{
			deltaPartWidth += 4;
		}

		if (deltaPartWidth == 0)
		{
			deltaPartWidth = 5;
		}

		partWrap = BX.create('DIV', {
			attrs: {'data-bx-calendar-entry': entry.uid},
			props: {className: entryClassName}, style: {
				top: 0,
				left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * (' + (from.dayOffset + 1) + ' - 1) + 2px)' : '2px',
				width: 'calc(' + daysCount + ' * 100% / ' + this.dayCount + ' - ' + deltaPartWidth + 'px)'
			}
		});

		if (startArrow)
		{
			partWrap.appendChild(startArrow);
			partWrap.style.left = '9px';
		}

		if (endArrow)
		{
			partWrap.appendChild(endArrow);
		}

		innerContainer = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner-container'}}));
		innerNode = innerContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner'}}));
		dotNode = innerNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-dot'}}));

		if (entry.isFullDay())
		{
			innerNode.style.maxWidth = 'calc(200% / ' + daysCount + ' - ' + this.lastEntryWidthOffset + 'px)';
		}
		else if (entry.isLongWithTime())
		{
			partWrap.style.borderColor = entry.color;
			innerNode.style.maxWidth = 'calc(200% / ' + daysCount + ' - ' + this.lastEntryWidthOffset + 'px)';

			// first part
			if (params.part.partIndex == 0)
			{
				if (daysCount > 1)
				{
					timeNode = innerNode.appendChild(BX.create('SPAN', {
						props: {className: 'calendar-event-line-time'},
						text: this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes())
					}));
				}
				innerNode.style.width = 'calc(100% / ' + daysCount + ' - ' + this.lastEntryWidthOffset + 'px)';
			}

			if (!timeNode && daysCount == 1 && this.util.getDayCode(entry.from) == params.part.from.dayCode)
			{
				timeNode = innerNode.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-event-line-time'},
					text: this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes())
				}));
			}

			// Last part
			if (params.part.partIndex == entry.parts.length - 1)
			{
				if (daysCount > 1 && entry.parts.length > 1)
				{
					innerNode.style.width = 'calc(' + (daysCount - 1) + '00% / ' + daysCount + ' - ' + this.lastEntryWidthOffset + 'px)';
				}

				if (daysCount > 1)
				{
					endTimeNode = innerNode.appendChild(BX.create('SPAN', {
						props: {className: (entry.parts.length > 1 && daysCount == 1) ? 'calendar-event-line-time' : 'calendar-event-line-expired-time'},
						text: this.calendar.util.formatTime(entry.to.getHours(), entry.to.getMinutes())
					}));
				}
			}

			if (!endTimeNode && daysCount == 1 && this.util.getDayCode(entry.to) == params.part.to.dayCode)
			{
				endTimeNode = innerNode.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-event-line-time'},
					text: this.calendar.util.formatTime(entry.to.getHours(), entry.to.getMinutes())
				}));
			}
		}
		else
		{
			timeNode = innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-time'}, text: this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes())}));
		}
		nameNode = innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-text'}, text: params.entry.name}));

		if (entry.isFullDay())
		{
			innerContainer.style.backgroundColor = this.calendar.util.hexToRgba(entry.color, 0.3);
			innerContainer.style.borderColor = this.calendar.util.hexToRgba(entry.color, 0.3);
		}
		else
		{
			if (entry.isLongWithTime())
			{
				innerContainer.style.borderColor = this.calendar.util.hexToRgba(entry.color, 0.5);
			}
			dotNode.style.backgroundColor = entry.color;
		}

		this.topEntryHolder.appendChild(partWrap);

		res = {
			wrapNode: partWrap,
			nameNode: nameNode,
			innerNode: innerNode,
			innerContainer: innerContainer,
			timeNode: timeNode || false,
			endTimeNode: endTimeNode || false,
			dotNode: dotNode
		};

		if (!params.popupMode)
		{
			params.entry.registerPartNode(params.part, res);
		}

		this.calendar.dragDrop.registerEntry(partWrap, params);

		return res;
	};

	DayView.prototype.displayTopEntries = function()
	{
		// Display top parts (in the top section - full day or several days events)
		var i;
		for (i = 0; i < this.partsStorage.length; i++)
		{
			this.displayTopEntry(this.partsStorage[i]);
		}
	};

	DayView.prototype.displayTimelineEntries = function()
	{
		this.zIndexTimeline = 100;
		this.timelinePartsStorage.sort(function(a, b)
		{
			if (a.part.fromTimeValue == b.part.fromTimeValue)
				return (b.part.toTimeValue - b.part.fromTimeValue) - (a.part.toTimeValue - a.part.fromTimeValue);
			return a.part.fromTimeValue - b.part.fromTimeValue;
		});

		var i;
		for (i = 0; i < this.timelinePartsStorage.length; i++)
		{
			this.displayTimelineEntry(this.timelinePartsStorage[i]);
		}
	};

	DayView.prototype.displayTimelineEntry = function(params)
	{
		var
			res = false,
			top,
			wrapNode, innerNode, nameNode, timeNode, timeLabel, resizerNode,
			bgNode, borderNode,
			workTime = this.util.getWorkTime(),
			entry = params.entry,
			from = params.part.from,
			fromTimeValue = params.part.fromTimeValue,
			toTimeValue = params.part.toTimeValue,
			entryClassName = 'calendar-event-block-wrap';

		if (entry.isExternal())
		{
			entryClassName += ' calendar-event-block-intranet';
		}

		if (!this.collapseOffHours
			|| (toTimeValue > workTime.start
			&& fromTimeValue < workTime.end))
		{
			if (this.collapseOffHours)
			{
				fromTimeValue = Math.max(params.part.fromTimeValue, workTime.start);
				toTimeValue = Math.min(params.part.toTimeValue, workTime.end);

				top = ((fromTimeValue - workTime.start) * this.gridLineHeight + 1) + 'px';
			}
			else
			{
				top = (fromTimeValue * this.gridLineHeight + 1) + 'px';
			}

			wrapNode = BX.create('DIV', {
				attrs: {'data-bx-calendar-entry': entry.uid},
				props: {className: entryClassName}, style: {
					top: top,
					height: ((toTimeValue - fromTimeValue) * this.gridLineHeight - 3) + 'px',
					left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * ' + from.dayOffset + ' + 2px)' : '2px',
					width: 'calc(100% / ' + this.dayCount + ' - ' + this.lastEntryWidthOffset + 'px)'
				}
			});

			borderNode = wrapNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-border'}}));

			innerNode = wrapNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-inner'}}));
			bgNode = innerNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-background'}}));
			timeLabel = this.calendar.util.formatTime(entry.from) + ' &ndash; ' + this.calendar.util.formatTime(entry.to);
			timeNode = innerNode.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-event-block-time'},
				html: timeLabel + '<span class="calendar-event-block-time-shadow">'+ timeLabel +'</span>'
			}));

			nameNode = innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-block-text'}, text: params.entry.name}));

			borderNode.style.backgroundColor = entry.color;
			bgNode.style.backgroundColor = entry.color;

			if (this.calendar.entryController.canDo(entry, 'edit'))
			{
				resizerNode = wrapNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-resizer'}}));
			}

			this.timelineEntryHolder.appendChild(wrapNode);

			res = {
				wrapNode: wrapNode,
				nameNode: nameNode,
				innerNode: innerNode,
				timeNode: timeNode,
				blockBackgroundNode: bgNode,
				resizerNode: resizerNode
			};

			params.part.offsetFractionRate = 1; //!!!!

			params.part.offsetFractionLeft = 0;
			params.part.offsetFractionWidth = 1;
			params.part.offsetFractionLeftTotal = 0;
			params.entry.registerPartNode(params.part, res);

			this.calendar.dragDrop.registerEntry(wrapNode, params);
		}

		return res;
	};

	DayView.prototype.checkTimelineEntrySize = function(entryPart, entry, timeout)
	{
		if (entryPart.params.innerNode.offsetHeight)
		{
			this.setEntryBlockCompact(entryPart, entry);
		}

		if (timeout === true)
		{
			setTimeout(BX.proxy(function(){this.checkTimelineEntrySize(entryPart, entry, false);}, this), 100);
		}
	};

	DayView.prototype.setEntryBlockCompact = function(entryPart, entry)
	{
		var
			lines, from,
			timeLabel,
			LINE_HEIGHT = 16,
			ENTRY_NAME_OFFSET = 23,
			MIN_ENTRY_WIDTH = 60,
			nameNode = entryPart.params.nameNode,
			innerNode = entryPart.params.innerNode,
			innerNodeWidth = innerNode.offsetWidth,
			nameNodeHeight = parseInt(nameNode.style.maxHeight);

		if (nameNodeHeight)
		{
			lines = Math.floor((Math.min(innerNode.offsetHeight - ENTRY_NAME_OFFSET, nameNodeHeight)) / LINE_HEIGHT);
		}
		else
		{
			lines = Math.floor((innerNode.offsetHeight - ENTRY_NAME_OFFSET) / LINE_HEIGHT);
		}

		if (nameNodeHeight
			|| nameNode.offsetHeight + ENTRY_NAME_OFFSET > innerNode.offsetHeight
			|| innerNodeWidth < MIN_ENTRY_WIDTH
		)
		{
			if (lines < 1 || innerNodeWidth < MIN_ENTRY_WIDTH)
			{
				from = (entry.entry && entry.entry.from) ? entry.entry.from : entry.from;
				if (from)
				{
					timeLabel = this.calendar.util.formatTime(from.getHours(), from.getMinutes());
					entryPart.params.timeNode.innerHTML = timeLabel + '<span class="calendar-event-block-time-shadow">'+ timeLabel +'</span>';
				}
				BX.addClass(entryPart.params.wrapNode, 'calendar-event-block-compact');
				if (innerNodeWidth < MIN_ENTRY_WIDTH)
					BX.addClass(entryPart.params.wrapNode, 'narrow-block');
			}
			else if (lines == 1)
			{
				nameNode.style.whiteSpace = 'nowrap';
				nameNode.style.display = 'block';
			}
			else
			{
				if (BX.browser.IsChrome())
				{
					nameNode.style.WebkitLineClamp = lines;
					nameNode.style.display = '-webkit-box';
				}
				else
				{
					nameNode.style.height = lines * LINE_HEIGHT + 'px';
				}
			}
		}
	};

	DayView.prototype.showNowTime = function(cont)
	{
		this.nowTimeCont = cont.appendChild(BX.create('DIV',{props: {className: this.gridNowTimeClass}}));
		this.nowTimeLine = this.nowTimeCont.appendChild(BX.create('DIV', {props: {className: this.gridNowTimeLineClass}}));
		this.nowTimeLine.appendChild(BX.create('DIV', {props: {className: this.gridNowTimeDotClass}}));

		this.nowTimeLabel = this.nowTimeCont.appendChild(BX.create('DIV', {props: {className: this.gridNowTimeLabelClass}}));

		if (this.nowTimeInterval)
			clearInterval(this.nowTimeInterval);

		this.updateNowTime();
		this.nowTimeInterval = setInterval(BX.proxy(this.updateNowTime, this), 15000);
	};

	DayView.prototype.hideNowTime = function()
	{
		BX.cleanNode(this.nowTimeCont, 1);
		delete this.nowTimeCont;
		if (this.nowTimeInterval)
			clearInterval(this.nowTimeInterval);
	};

	DayView.prototype.updateNowTime = function()
	{
		if (!this.nowTimeCont)
			return;

		var
			nowTimeVisualOffsetPx = 10,
			nowTimeResizeVisualOffsetPx = 15,
			workTime = this.util.getWorkTime(),
			time = new Date(),
			viewRange = this.getViewRange(),
			timeValue = this.util.getTimeValue(time),
			showTimeLable = true,
			nearestLineIndex = Math.round(timeValue);

		var translucentLine = document.querySelector("." + this.gridTimeTranslucentClass);
		if (translucentLine)
			BX.removeClass(translucentLine, this.gridTimeTranslucentClass);

		if (time.getTime() > viewRange.start.getTime() && time.getTime() < viewRange.end.getTime())
		{
			if (!this.nowTimeCont)
				this.showNowTime();

			if (this.dayCount > 1)
			{
				var dayOffset = this.util.getWeekDayOffset(this.util.getWeekDayByInd(time.getDay()));
				if (dayOffset == 0)
				{
					this.nowTimeLine.style.left = 0;
				}
				else
				{
					this.nowTimeLine.style.left = 'calc(' + dayOffset + ' * 100% / ' + this.dayCount + ')';
				}
			}
		}
		else
		{
			return this.hideNowTime();
		}

		var timeTextValue = this.calendar.util.formatTime(time.getHours(), time.getMinutes());
		if (BX.isAmPmMode())
			timeTextValue = timeTextValue.replace(/(\sam|pm)/ig, "<small>$1<small>");

		this.nowTimeLabel.innerHTML = timeTextValue;

		if (this.collapseOffHours)
		{
			if (timeValue < workTime.start)
			{
				showTimeLable = false;
				this.nowTimeLabel.style.display = 'none';
				this.nowTimeCont.style.top = '-5px';
			}
			else if (timeValue > workTime.end)
			{
				showTimeLable = false;
				this.nowTimeLabel.style.display = 'none';
				this.nowTimeCont.style.top = ((workTime.end - workTime.start) * this.gridLineHeight) + 4 + 'px';
			}
			else
			{
				if (timeValue < workTime.start + nowTimeVisualOffsetPx / this.gridLineHeight ||
					timeValue > workTime.end - nowTimeVisualOffsetPx / this.gridLineHeight)
				{
					this.nowTimeLabel.style.display = 'none';
				}
				else
				{
					this.nowTimeLabel.style.display = '';
				}
				this.nowTimeCont.style.top = ((timeValue - workTime.start) * this.gridLineHeight + 1) + 'px';
			}
		}
		else
		{
			if ((timeValue < workTime.start + nowTimeResizeVisualOffsetPx / this.gridLineHeight
				&& timeValue > workTime.start)
				|| (timeValue > workTime.end - nowTimeResizeVisualOffsetPx / this.gridLineHeight
				&& timeValue < workTime.end))
			{
				showTimeLable = false;
				this.nowTimeLabel.style.display = 'none';
			}

			this.nowTimeCont.style.top = (timeValue * this.gridLineHeight + 1) + 'px';
		}

		if (showTimeLable && Math.abs((nearestLineIndex - timeValue) * this.gridLineHeight) < nowTimeVisualOffsetPx)
		{
			if (this.timeLinesIndex[nearestLineIndex])
			{
				BX.addClass(this.timeLinesIndex[nearestLineIndex], this.gridTimeTranslucentClass);
			}
		}
	};

	DayView.prototype.getTimeByPos = function(top, roundOff)
	{
		var
			workTime = this.util.getWorkTime(),
			timeFract = top / this.gridLineHeight,
			timeVal = this.util.getTimeByFraction(timeFract, roundOff || 10);

		if (this.collapseOffHours)
		{
			timeVal.h += workTime.start;
		}
		return timeVal;
	};

	DayView.prototype.showOffHours = function()
	{
		var workTime = this.util.getWorkTime();
		this.topOffHours = this.timeLinesCont.appendChild(BX.create('DIV', {
			props: {className: this.offHoursClass + ' ' + this.offHoursAnimateClass},
			style: {
				top: 0,
				height: (workTime.start * this.gridLineHeight + 1) + 'px'
			}
		}));

		this.topOffHoursLabel = this.topOffHours.appendChild(BX.create('DIV', {
			props: {className: this.gridTimelineHourLabelClass},
			children: [
				BX.create('DIV', {
					props: { className: this.gridTimelineHourLabelClassInner},
			html: this.calendar.util.formatTime(0, 0, true) + "<br>" + this.calendar.util.formatTime(workTime.start, 0, true)
				})
			]
		}));
		this.topOffHours.appendChild(BX.create('DIV', {
			props: {className: 'calendar-grid-off-hours-active'},
			events: {
				click: BX.proxy(this.switchOffHours, this),
				mouseover: BX.proxy(function(){
					BX.addClass(this.topOffHours, "calendar-grid-off-hours-hover");
					BX.addClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
				}, this),
				mouseout: BX.proxy(function(){
					BX.removeClass(this.topOffHours, "calendar-grid-off-hours-hover");
					BX.removeClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
				}, this)
			}
		}));
		this.topOffHours.appendChild(BX.create('DIV', {
			props: {className: 'calendar-grid-off-hours-drag-down'},
			attrs: {'data-bx-calendar-off-time-drag': 'top'},
			events: {
				mousedown : BX.proxy(this.offHoursMousedown, this)
			}
		}));

		this.bottomOffHours = this.timeLinesCont.appendChild(BX.create('DIV', {
			props: {className: this.offHoursClass + ' ' + this.offHoursAnimateClass},
			style: {
				top: (workTime.end * this.gridLineHeight + 1) + 'px',
				height: ((24 - workTime.end) * this.gridLineHeight + 1) + 'px'
			}
		}));
		this.bottomOffHoursLabel = this.bottomOffHours.appendChild(BX.create('DIV', {
			props: {className: this.gridTimelineHourLabelClass},
			children: [
				BX.create('DIV', {
					props: { className: this.gridTimelineHourLabelClassInner},
			html: this.calendar.util.formatTime(workTime.end, 0, true)  + "<br>" + this.calendar.util.formatTime(24, 0, true)
				})
			]
		}));
		this.bottomOffHours.appendChild(BX.create('DIV', {
			props: {className: 'calendar-grid-off-hours-active'},
			events: {
				click: BX.proxy(this.switchOffHours, this),
				mouseover: BX.proxy(function(){
					BX.addClass(this.topOffHours, "calendar-grid-off-hours-hover");
					BX.addClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
				}, this),
				mouseout: BX.proxy(function(){
					BX.removeClass(this.topOffHours, "calendar-grid-off-hours-hover");
					BX.removeClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
				}, this)
			}
		}));
		this.bottomOffHours.appendChild(BX.create('DIV', {
			props: {className: 'calendar-grid-off-hours-drag-up'},
			attrs: {'data-bx-calendar-off-time-drag': 'bottom'},
			events: {
				mousedown : BX.proxy(this.offHoursMousedown, this)
			}
		}));

		BX.bind(this.topOffHours, 'click', BX.proxy(function(){if (this.collapseOffHours){this.switchOffHours(true)}}, this));
		BX.bind(this.bottomOffHours, 'click', BX.proxy(function(){if (this.collapseOffHours){this.switchOffHours(true)}}, this));

		if (this.collapseOffHours)
		{
			this.gridRow.style.height = (this.gridLineHeight * (workTime.end - workTime.start)) + 30 + 'px';
			this.collapseOffHours = !this.collapseOffHours;
			this.switchOffHours(false);
			this.updateGridRowShadowHeight();
		}
		else
		{
			this.gridRow.style.height = (this.gridLineHeight * 24) + 40 + 'px';
			this.updateGridRowShadowHeight();
		}
	};

	DayView.prototype.offHoursMousedown = function(e)
	{
		var
			target = e.target || e.srcElement;

		this.lastWorkTime = false;
		this.lastTopCount = false;
		if (target && target.getAttribute)
		{
			this.lastWorkTime = BX.clone(this.util.getWorkTime());
			// Prevent double registration on eventhandlers
			BX.unbind(document, "mousemove", BX.proxy(this.offHoursMousemove, this));
			BX.unbind(document, "mouseup", BX.proxy(this.offHoursMouseup, this));

			BX.bind(document, "mousemove", BX.proxy(this.offHoursMousemove, this));
			BX.bind(document, "mouseup", BX.proxy(this.offHoursMouseup, this));

			BX.removeClass(this.topOffHours, this.offHoursAnimateClass);
			BX.removeClass(this.bottomOffHours, this.offHoursAnimateClass);
			BX.addClass(this.topOffHours, this.offHoursFastAnimateClass);
			BX.addClass(this.bottomOffHours, this.offHoursFastAnimateClass);

			if (target.getAttribute('data-bx-calendar-off-time-drag') == 'top')
			{
				this.offtimeTuneMode = 'top';
			}
			else
			{
				this.offtimeTuneMode = 'bottom';
			}

			this.offtimeTuneBaseZeroPos = BX.pos(this.timeLinesCont).top;
		}
	};

	DayView.prototype.offHoursMousemove = function(e)
	{
		if (this.offtimeTuneMode)
		{
			var
				mousePos = this.util.getMousePos(e),
				topPos = Math.max(Math.round((mousePos.y - this.offtimeTuneBaseZeroPos) / this.gridLineHeight), 0);

			if (this.lastTopCount !== topPos)
			{
				if (this.offtimeTuneMode == 'top')
				{
					topPos = Math.min(this.lastWorkTime.end - 1, topPos);
					this.topOffHours.style.height = (topPos * this.gridLineHeight + 1) + 'px';

					this.lastWorkTime.start = topPos;
				}
				else
				{
					topPos = Math.max(this.lastWorkTime.start + 1, topPos);
					this.bottomOffHours.style.top = topPos * this.gridLineHeight + 'px';
					this.bottomOffHours.style.height = ((24 - topPos) * this.gridLineHeight + 1) + 'px';

					this.lastWorkTime.end = topPos;
				}
				this.lastTopCount = topPos;
			}
		}
	};

	DayView.prototype.offHoursMouseup = function()
	{
		BX.unbind(document, "mousemove", BX.proxy(this.offHoursMousemove, this));
		BX.unbind(document, "mouseup", BX.proxy(this.offHoursMouseup, this));

		BX.addClass(this.topOffHours, this.offHoursAnimateClass);
		BX.addClass(this.bottomOffHours, this.offHoursAnimateClass);
		BX.removeClass(this.topOffHours, this.offHoursFastAnimateClass);
		BX.removeClass(this.bottomOffHours, this.offHoursFastAnimateClass);

		var workTime = this.util.setWorkTime(this.lastWorkTime);
		this.topOffHoursLabel.innerHTML = this.calendar.util.formatTime(0, 0, true) + "<br>" + this.calendar.util.formatTime(workTime.start, 0, true);
		this.bottomOffHoursLabel.innerHTML = this.calendar.util.formatTime(workTime.end, 0, true)  + "<br>" + this.calendar.util.formatTime(24, 0, true);

		this.offtimeTuneMode = false;
		delete this.lastWorkTime;
		delete this.lastTopCount;

		this.collapseOffHours = false;
		this.switchOffHours(true);
	};

	DayView.prototype.switchOffHours = function(animate)
	{
		if (this.denySwitch)
			return;

		animate = animate !== false;
		if (this.nowTimeCont)
			this.nowTimeCont.display = 'none';

		BX.cleanNode(this.timelineEntryHolder);
		this.hideNowTime();

		if (animate)
		{
			BX.removeClass(this.grid, 'calendar-events-holder-show');

			BX.addClass(this.bottomOffHours, this.offHoursAnimateClass);
			BX.addClass(this.topOffHours, this.offHoursAnimateClass);
			BX.addClass(this.timeLinesCont, this.offHoursAnimateClass);
		}
		else
		{
			BX.removeClass(this.bottomOffHours, this.offHoursAnimateClass);
			BX.removeClass(this.topOffHours, this.offHoursAnimateClass);
			BX.removeClass(this.timeLinesCont, this.offHoursAnimateClass);
		}

		this.denySwitch = true;
		var
			_this = this,
			dellay = 300,
			i, item,
			workTime = this.util.getWorkTime();

		setTimeout(BX.delegate(function(){
			this.denySwitch = false;

			if (this.collapseOffHours)
			{
				//this.topOffHoursLabel
				//this.bottomOffHoursLabel
			}
			else
			{
				for (i in this.timeLinesIndex)
				{
					if (this.timeLinesIndex.hasOwnProperty(i))
					{
						this.timeLinesIndex[i].style.opacity = '';
						this.timeLinesIndex[i].style.display = '';
					}
				}
			}

			BX.addClass(this.bottomOffHours, this.offHoursAnimateClass);
			BX.addClass(this.topOffHours, this.offHoursAnimateClass);
			BX.addClass(this.timeLinesCont, this.offHoursAnimateClass);

			if (this.scrollTopInterval)
				clearTimeout(this.scrollTopInterval);

			if (this.timeLinesCont && !this.nowTimeCont)
				this.showNowTime(this.timeLinesCont);

			if (animate)
			{
				BX.addClass(this.grid, 'calendar-events-holder-show');
				this.displayEntries();
			}
		}, this), animate ? 500 : 10);

		function showOffHoursLine(item)
		{
			if (animate)
			{
				setTimeout(function (){item.style.opacity = 1;}, dellay);
			}
			else
			{
				item.style.opacity = 1;
			}
		}

		function hideOffHoursLine(item)
		{
			if (animate)
			{
				setTimeout(function() {item.style.display = "none";}, dellay);
			}
			else
			{
				item.style.display = "none";
			}
		}

		function checkScrollTimeout()
		{
			_this.gridWrap.scrollTop = _this.savedScrollTop || (workTime.start * _this.gridLineHeight - 5);
			_this.scrollTopInterval = setTimeout(checkScrollTimeout, 5);
		}

		var setPadding = true;
		if (!this.collapseOffHours &&
			(workTime.end - workTime.start) * this.gridLineHeight + 20 <= this.util.getViewHeight())
		{
			setPadding = false;
		}
		this.checkTimelineScroll(setPadding);

		if (this.collapseOffHours)
		{
			this.gridRow.style.height = (this.gridLineHeight * 24) + 40 + 'px';
			this.topOffHours.style.height = (this.gridLineHeight * workTime.start) + 1 + 'px';
			this.bottomOffHours.style.height = (this.gridLineHeight * (24 - workTime.end)) + 1 + 'px';
			this.bottomOffHours.style.top = (this.gridLineHeight * workTime.end) + 'px';

			for (i in this.timeLinesIndex)
			{
				if (this.timeLinesIndex.hasOwnProperty(i))
				{
					if (i >= workTime.start && i <= workTime.end)
					{
						hideOffHoursLine(this.timeLinesIndex[i]);
					}
					else
					{
						this.timeLinesIndex[i].style.display = "block";
						showOffHoursLine(this.timeLinesIndex[i]);
					}
					this.timeLinesIndex[i].style.top = (i * this.gridLineHeight) + 'px';
				}
			}

			if (animate && this.savedScrollTop)
			{
				this.scrollTopInterval = setTimeout(checkScrollTimeout, 5);
			}
		}
		else
		{
			this.gridRow.style.height = (this.gridLineHeight * (workTime.end - workTime.start)) + 30 + 'px';
			this.topOffHours.style.height = '10px';

			this.bottomOffHours.style.height = (this.gridLineHeight * (24 - workTime.end)) + 1 + 'px';
			this.bottomOffHours.style.top = (this.gridLineHeight * workTime.end) + 'px';


			for (i in this.timeLinesIndex)
			{
				if (this.timeLinesIndex.hasOwnProperty(i))
				{
					if ((i <= workTime.start || i >= workTime.end))
					{
						item = this.timeLinesIndex[i];
						this.timeLinesIndex[i].style.opacity = 0;
						hideOffHoursLine(this.timeLinesIndex[i]);
					}
					else
					{
						showOffHoursLine(this.timeLinesIndex[i]);
					}

					if (i >= workTime.end)
					{
						this.timeLinesIndex[i].style.top = ((workTime.end - workTime.start) * this.gridLineHeight) + 'px';
					}
					else
					{
						this.timeLinesIndex[i].style.top = ((i - workTime.start) * this.gridLineHeight) + 'px';
					}
				}
			}

			this.bottomOffHours.style.height = '10px';
			this.bottomOffHours.style.top = ((workTime.end - workTime.start) * this.gridLineHeight) + 9 + 'px';
			this.savedScrollTop = parseInt(this.gridWrap.scrollTop);
		}

		this.collapseOffHours = !this.collapseOffHours;
		BX.toggleClass(this.topOffHours, [this.offHoursCollapseClass, this.offHoursClass]);
		BX.toggleClass(this.bottomOffHours, [this.offHoursCollapseClass, this.offHoursClass]);

		this.util.setUserOption('collapseOffHours', this.collapseOffHours ? 'Y' : 'N');

		this.updateGridRowShadowHeight();
	};

	DayView.prototype.checkTimelineScroll = function(setPadding)
	{
		// todo: bug: 2 calling
		// Compensate padding in right title calendar-grid-week-full-days-events-holder
		var wrapOffsetRight = setPadding ? this.util.getScrollbarWidth() : 0;

		if (this.titleCont)
		{
			this.titleCont.style.paddingRight = wrapOffsetRight + "px";
		}

		if(this.fullDayEventsHolderCont && this.topEntryHolder)
		{
			new BX.easing({
				duration: 100,
				start: {width: wrapOffsetRight, paddingRight: 0},
				finish: {width: 0, paddingRight: wrapOffsetRight},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.linear),
				step: BX.delegate(function (state) {
					this.gridWrap.style.width = 'calc(100% + ' + state.width + 'px)';
					this.topEntryHolder.style.right = state.paddingRight + "px";
					this.fullDayEventsHolderCont.style.paddingRight = state.paddingRight + "px";
				}, this),
				complete: function () {

				}
			}).animate();
		}
	};

	DayView.prototype.getDayGridHeight = function()
	{
		return 1040;
	};

	DayView.prototype.updateGridRowShadowHeight = function()
	{
		if (this.collapseOffHours)
		{
			this.gridRowShadow.style.height = (parseInt(this.gridRow.style.height) - 38) + 'px';
			BX.removeClass(this.gridRowShadow, 'calendar-grid-week-row-shadow-off-hours');
		}
		else
		{
			this.gridRowShadow.style.height = (parseInt(this.gridRow.style.height) - 40) + 'px';
			BX.addClass(this.gridRowShadow, 'calendar-grid-week-row-shadow-off-hours');
		}
	};

	DayView.prototype.handleClick = function(params)
	{
		if (this.isActive())
		{
			if (!params)
				params = {};

			var dayCode, uid;
			if (params.specialTarget && (uid = params.specialTarget.getAttribute('data-bx-calendar-entry')))
			{
				this.handleEntryClick({
					uid: uid,
					specialTarget: params.specialTarget,
					target: params.target,
					e: params.e
				});
			}
			else if (params.specialTarget && (dayCode = params.specialTarget.getAttribute('data-bx-calendar-show-all-events')))
			{
			}
			else if (!this.calendar.util.readOnlyMode()
				&& this.entryController.canDo(true, 'add_event')
				&& (dayCode = params.specialTarget && params.specialTarget.getAttribute('data-bx-calendar-week-day')))
			{
				this.deselectEntry();
				this.showSimplePopupForNewEntry({
					entry: this.buildTopNewEntryWrap({
						dayFrom: this.days[this.dayIndex[dayCode]],
						holder: this.topEntryHolder
					})
				});
			}
		}
	};

	DayView.prototype.handleMousedown = function(e)
	{
		if (!this.isActive())
			return;

		var
			dayCode,
			target = this.calendar.util.findTargetNode(e.target || e.srcElement);

		if (!this.calendar.util.readOnlyMode()
			&& this.entryController.canDo(true, 'add_event')
			&& (dayCode = target && target.getAttribute('data-bx-calendar-timeline-day')))
		{
			// Prevent double registration on eventhandlers
			BX.unbind(document, "mousemove", BX.proxy(this.handleMousemove, this));
			BX.unbind(document, "mouseup", BX.proxy(this.handleMouseup, this));
			BX.bind(document, "mousemove", BX.proxy(this.handleMousemove, this));
			BX.bind(document, "mouseup", BX.proxy(this.handleMouseup, this));
			BX.addCustomEvent(this.calendar, 'keyup', BX.proxy(this.checkKeyup, this));

			this.createEntryMode = true;
			this.offtimeTuneBaseZeroPos = BX.pos(this.timeLinesCont).top;
			this.offtimeBottomBasePos = BX.pos(this.bottomOffHours).bottom - 2;

			this.startMousePos = Math.max(this.offtimeTuneBaseZeroPos + this.gridWrap.scrollTop, this.calendar.util.getMousePos(e).y);

			this.newEntry = this.buildTimelineNewEntryWrap({
				dayFrom: this.days[this.dayIndex[dayCode]],
				holder: this.timelineEntryHolder
			});

			this.newEntry.dayFrom = this.days[this.dayIndex[dayCode]];

			this.newEntry.timeFrom = this.getTimeByPos(this.startMousePos - this.offtimeTuneBaseZeroPos, 30, true);
			var
				workTime = this.util.getWorkTime(),
				fromTimeValue = this.newEntry.timeFrom.h + this.newEntry.timeFrom.m / 60;

			if (this.collapseOffHours)
			{
				fromTimeValue = Math.max(fromTimeValue, workTime.start);
				this.startMousePos = this.offtimeTuneBaseZeroPos + ((fromTimeValue - workTime.start) * this.gridLineHeight + 1);
			}
			else
			{
				this.startMousePos = this.offtimeTuneBaseZeroPos + (fromTimeValue * this.gridLineHeight + 1);

			}

			if (this.newEntry.timeFrom.h == 23)
				this.newEntry.timeTo = {h: 23, m: 59};
			else
				this.newEntry.timeTo = {h: this.newEntry.timeFrom.h + 1, m: this.newEntry.timeFrom.m};
			this.newEntry.changeTimeCallback(this.newEntry.timeFrom, this.newEntry.timeTo);

			this.newEntry.entryNode.style.top = this.startMousePos + 'px';
		}
	};

	DayView.prototype.handleMousemove = function(e)
	{
		if (this.createEntryMode)
		{
			var
				mousePos = this.calendar.util.getMousePos(e).y,
				height = Math.min(Math.max(mousePos - this.startMousePos, 10), this.offtimeBottomBasePos - parseInt(this.newEntry.entryNode.style.top));

			this.newEntry.entryNode.style.height = height + 'px';
			this.newEntry.timeTo = this.getTimeByPos(height + this.startMousePos - this.offtimeTuneBaseZeroPos);
			this.newEntry.changeTimeCallback(this.newEntry.timeFrom, this.newEntry.timeTo);
		}
	};

	DayView.prototype.handleMouseup = function(e)
	{
		BX.unbind(document, "mousemove", BX.proxy(this.offHoursMousemove, this));
		BX.unbind(document, "mouseup", BX.proxy(this.offHoursMouseup, this));
		BX.removeCustomEvent(this.calendar, 'keyup', BX.proxy(this.checkKeyup, this));

		if (this.createEntryMode)
		{
			var
				fromDate = new Date(this.newEntry.dayFrom.date.getTime()),
				toDate = new Date(this.newEntry.dayFrom.date.getTime());
			fromDate.setHours(this.newEntry.timeFrom.h, this.newEntry.timeFrom.m, 0, 0);
			toDate.setHours(this.newEntry.timeTo.h, this.newEntry.timeTo.m, 0, 0);

			this.deselectEntry();
			this.showSimplePopupForNewEntry({
				entry: this.newEntry,
				entryTime: {
					from: fromDate,
					to: toDate
				}
			});
			this.createEntryMode = false;
		}
	};

	DayView.prototype.checkKeyup = function(params)
	{
		var KEY_CODES = this.util.getKeyCodes();

		if (params.keyCode == KEY_CODES['escape'] && this.createEntryMode && this.newEntry)
		{
			BX.remove(this.newEntry.entryNode);
			this.createEntryMode = false;
			this.handleMouseup();
		}
	};

	DayView.prototype.buildTopNewEntryWrap = function(params)
	{
		var
			_this = this,
			entryTime, entryName,
			partWrap, innerNode, innerContainer,
			entryClassName = 'calendar-event-line-wrap',
			deltaPartWidth = 0,
			from = params.dayFrom,
			timeNode, nameNode,
			daysCount = 1,
			section = this.calendar.sectionController.getCurrentSection(),
			color = section.color;

		entryTime = this.entryController.getTimeForNewEntry(from.date);
		entryName = this.entryController.getDefaultEntryName();

		partWrap = params.holder.appendChild(BX.create('DIV', {
			props: {className: entryClassName}, style: {
				top: 0,
				left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * (' + (from.dayOffset + 1) + ' - 1) + 2px)' : '2px',
				width: 'calc(' + daysCount + ' * 100% / ' + this.dayCount + ' - ' + deltaPartWidth + 'px)'
			}
		}));

		innerContainer = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner-container'}}));
		innerNode = innerContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner'}}));
		innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-time'}, text: this.calendar.util.formatTime(entryTime.from.getHours(), entryTime.from.getMinutes())}));
		innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-text'}, text: entryName}));

		partWrap.style.backgroundColor = color;
		partWrap.style.borderColor = color;
		partWrap.style.opacity = 0;

		var pos = BX.pos(partWrap);
		var entryClone = BX.adjust(document.body.appendChild(partWrap.cloneNode(true)), {
			props: {className: 'calendar-event-line-clone'},
			style: {
				width: (pos.width + 1) + 'px',
				height: pos.height + 'px',
				top : pos.top + 'px',
				left : pos.left + 'px',
				opacity: 1
			}
		});
		if (partWrap)
		{
			BX.remove(partWrap, true);
		}

		nameNode = entryClone.querySelector('.calendar-event-line-text');
		timeNode = entryClone.querySelector('.calendar-event-line-time');
		innerNode = entryClone.querySelector('.calendar-event-line-inner');

		var entry = {
			entryNode: entryClone,
			innerNode: innerNode,
			section: section,
			entryName: entryName,
			entryTime: entryTime,
			changeTimeCallback: function(from, to)
			{
				if (from.getHours && to.getHours)
				{
					timeNode.innerHTML = _this.calendar.util.formatTime(from.getHours(),from.getMinutes());
				}
				else
				{
					timeNode.innerHTML = _this.calendar.util.formatTime(from.h, from.m);
				}
			},
			changeNameCallback: function(name)
			{
				nameNode.innerHTML = BX.util.htmlspecialchars(name);
			}
		};

		this.selectEntryPart(entry, color, false);

		return entry;
	};


	DayView.prototype.buildTimelineNewEntryWrap = function(params)
	{
		var
			_this = this,
			entryTime, entryName,
			partWrap, innerNode,
			entryClassName = 'calendar-event-block-wrap',
			from = params.dayFrom,
			borderNode, bgNode,timeLabel, timeNode, nameNode, bindNode,
			section = this.calendar.sectionController.getCurrentSection(),
			color = section.color;

		entryTime = this.entryController.getTimeForNewEntry(from.date);
		entryName = this.entryController.getDefaultEntryName();

		partWrap = params.holder.appendChild(BX.create('DIV', {
			props: {className: entryClassName},
			style: {
				top: top,
				height: this.gridLineHeight + 'px',
				minHeight: '20px',
				left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * ' + from.dayOffset + ' + 2px)' : '2px',
				width: 'calc(100% / ' + this.dayCount + ' - ' + this.lastEntryWidthOffset + 'px)'
			}
		}));
		borderNode = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-border'}}));
		innerNode = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-inner'}}));
		bgNode = innerNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-background'}}));
		timeLabel = this.calendar.util.formatTime(entryTime.from.getHours(), entryTime.from.getMinutes()) + ' &ndash; ' + this.calendar.util.formatTime(entryTime.to.getHours(), entryTime.to.getMinutes());
		innerNode.appendChild(BX.create('SPAN', {
			props: {className: 'calendar-event-block-time'},
			style: {color: '#fff'},
			html: timeLabel + '<span class="calendar-event-block-time-shadow">'+ timeLabel +'</span>'
		}));
		innerNode.appendChild(BX.create('SPAN', {
			props: {className: 'calendar-event-block-text'},
			style: {color: '#fff'},
			text: entryName
		}));
		borderNode.style.backgroundColor = color;
		bgNode.style.backgroundColor = color;

		var pos = BX.pos(partWrap);
		var entryClone = BX.adjust(document.body.appendChild(partWrap.cloneNode(true)), {
			props: {className: 'calendar-event-line-clone calendar-event-block-wrap active'},
			style: {
				width: (pos.width + 1) + 'px',
				height: pos.height + 'px',
				top : pos.top + 'px',
				left : pos.left + 'px',
				opacity: 1
			}
		});

		if (partWrap)
		{
			BX.remove(partWrap, true);
		}

		nameNode = entryClone.querySelector('.calendar-event-block-text');
		timeNode = entryClone.querySelector('.calendar-event-block-time');
		innerNode = entryClone.querySelector('.calendar-event-block-inner');
		borderNode = entryClone.querySelector('.calendar-event-block-border');
		bgNode = entryClone.querySelector('.calendar-event-block-background');
		bindNode = entryClone.appendChild(BX.create('DIV', {props: {className: 'calendar-event-bind-node'}}));
		if (this.dayCount == 1)
			bindNode.style.right = '10%';
		else
			bindNode.style.left = '0';

		var entry = {
			entryNode: entryClone,
			innerNode: innerNode,
			section: section,
			entryName: entryName,
			bindNode: bindNode,
			borderNode: borderNode,
			blockBackgroundNode: bgNode,
			changeTimeCallback: function(from, to)
			{
				var timeLabel;
				if (from.getHours && to.getHours)
				{
					timeLabel = _this.calendar.util.formatTime(from.getHours(),from.getMinutes())
						+ ' &ndash; '
						+ _this.calendar.util.formatTime(to.getHours(), to.getMinutes());
				}
				else
				{
					timeLabel = _this.calendar.util.formatTime(from.h, from.m)
						+ ' &ndash; '
						+ _this.calendar.util.formatTime(to.h, to.m);
				}
				timeNode.innerHTML = timeLabel + '<span class="calendar-event-block-time-shadow">'+ timeLabel +'</span>';
			},
			changeNameCallback: function(name)
			{
				nameNode.innerHTML = BX.util.htmlspecialchars(name);
			}
		};

		this.selectEntryPart(entry, color, false);

		return entry;
	};

	DayView.prototype.showSimplePopupForNewEntry = function(params)
	{
		// Show simple add entry popup
		this.showSimplePopup({
			entryNode: params.entry.entryNode,
			bindNode: params.entry.bindNode,
			section: params.entry.section,
			entryTime: params.entryTime || params.entry.entryTime,
			entryName: params.entry.entryName,
			changeTimeCallback: params.entry.changeTimeCallback,
			changeNameCallback: params.entry.changeNameCallback,
			closeCallback: BX.delegate(function()
			{
				BX.remove(params.entry.entryNode);
			}, this),
			changeDateCallback: BX.delegate(function(date)
			{
				//var dayCode = this.util.getDayCode(date);
				//if (dayCode && this.dayIndex[dayCode] && this.days[this.dayIndex[dayCode]])
				//{
				//	var dayFrom = this.days[this.dayIndex[dayCode]];
				//	partWrap.style.left = 'calc((100% / ' + this.dayCount + ') * (' + (dayFrom.dayOffset + 1) + ' - 1) + 2px)';
				//
				//	this.entryHolders[dayFrom.holderIndex].appendChild(partWrap);
				//	var pos = BX.pos(partWrap);
				//	BX.adjust(entryClone, {
				//		style: {
				//			width: (pos.width + 1) + 'px',
				//			height: pos.height + 'px',
				//			top : pos.top + 'px',
				//			left : pos.left + 'px'
				//		}
				//	});
				//}
			}, this),
			changeSectionCallback: function(section)
			{
				var color = section.color;
				if (params.entry.borderNode)
					params.entry.borderNode.style.backgroundColor = color;
				if (params.entry.blockBackgroundNode)
					params.entry.blockBackgroundNode.style.backgroundColor = color;
			},
			saveCallback: function()
			{
			},
			cancelCallback: function()
			{
			},
			fullFormCallback: BX.delegate(this.showEditSlider, this)
		});
	};


	// Week view of the calendar
	function WeekView()
	{
		View.apply(this, arguments);
		this.initConfig();
		this.preBuild();
	}
	WeekView.prototype = Object.create(DayView.prototype);
	WeekView.prototype.constructor = WeekView;

	WeekView.prototype.show = function()
	{
		View.prototype.show.apply(this, arguments);
		this.buildDaysGrid();

		if (this.calendar.navCalendar)
			this.calendar.navCalendar.hide();

		this.displayEntries();
		this.calendar.initialViewShow = false;
	};

	WeekView.prototype.initConfig = function()
	{
		DayView.prototype.initConfig.apply(this, arguments);
		this.name = 'week';
		this.title = BX.message('EC_VIEW_WEEK');
		this.contClassName = 'calendar-week-view';

		this.gridWrapClass = 'calendar-grid-wrap';
		this.fullDayContClass = 'calendar-grid-week-full-days-events-holder';
		this.outerGridClass = 'calendar-grid-week-container';
		this.gridClass = 'calendar-grid-week';
		this.gridClassCurrent = 'calendar-grid-week-current';

		this.gridClassNext = 'calendar-grid-week-left-slide';
		this.gridClassPrevious = 'calendar-grid-week-right-slide';
		this.changeNextClass = 'calendar-change-week-left-slide';
		this.changePreviousClass = 'calendar-change-week-right-slide';

		this.gridRowClass = 'calendar-grid-week-row';
		this.gridCellClass = 'calendar-grid-week-cell';
		this.gridTimelinesClass = 'calendar-grid-week-time-lines';
		this.gridTimelineHourClass = 'calendar-grid-week-time-line-hour';
		this.gridTimelineHourLabelClass = 'calendar-grid-week-time-line-hour-label';
		this.topEntryHolderClass = 'calendar-grid-week-events-holder';

		this.gridNowTimeClass = 'calendar-grid-week-time-line-hour-now';
		this.gridNowTimeLabelClass = 'calendar-grid-week-time-line-hour-label';
		this.gridNowTimeLineClass = 'calendar-grid-week-time-line-hour-now-line';
		this.gridNowTimeDotClass = 'calendar-grid-week-time-line-hour-now-dot';

		this.dayCount = 7;
	};
	WeekView.prototype.setTitle = function()
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			time = viewRangeDate.getTime(),
			dateTo = new Date(viewRangeDate.getTime() + this.dayCount * this.calendar.util.dayLength);

		if (viewRangeDate.getMonth() != dateTo.getMonth())
		{
			View.prototype.setTitle.apply(this, [
				BX.date.format('f', time / 1000) + ' - ' + BX.date.format('f', dateTo.getTime() / 1000) + (this.util.showWeekNumber() ? ', #GRAY_START#' + BX.message('EC_DATE_WEEK_NUMBER').replace('#WEEK_NUMBER#', this.util.getWeekNumber(time)) + '#GRAY_END#' : '')
			]);
		}
		else
		{
			View.prototype.setTitle.apply(this, [
				BX.date.format('f', time / 1000) + (this.util.showWeekNumber() ? ', #GRAY_START#' + BX.message('EC_DATE_WEEK_NUMBER').replace('#WEEK_NUMBER#', this.util.getWeekNumber(time)) + '#GRAY_END#' : '')
			]);
		}
	};

	WeekView.prototype.getAdjustedDate = function(date, viewRange, adjustViewRange)
	{
		// TODO: add logic if we changed to the other date and than changing view
		if (!date)
		{
			date = new Date();
		}

		if (viewRange && date.getTime() < viewRange.start.getTime())
		{
			date = new Date(viewRange.start.getTime());
		}

		if (viewRange && date.getTime() > viewRange.end.getTime())
		{
			date = new Date(viewRange.end.getTime());
		}

		var weekstart = this.util.getWeekStart();

		while (this.util.getWeekDayByInd(date.getDay()) != weekstart)
		{
			date.setDate(date.getDate() - 1);
		}

		if (adjustViewRange)
		{
			viewRange.start.setDate(date.getTime());
			viewRange.end.setDate(date.getTime() + this.calendar.util.dayLength * this.dayCount);
		}

		return DayView.prototype.getAdjustedDate.apply(this, [date, viewRange]);
	};

	WeekView.prototype.adjustViewRangeToDate = function(date)
	{
		var weekstart = this.util.getWeekStart();
		while (this.util.getWeekDayByInd(date.getDay()) != weekstart)
		{
			date.setDate(date.getDate() - 1);
		}
		return DayView.prototype.adjustViewRangeToDate.apply(this, [date]);
	};

	// Day view of the calendar
	function MonthView()
	{
		View.apply(this, arguments);
		this.name = 'month';
		this.title = BX.message('EC_VIEW_MONTH');
		this.contClassName = 'calendar-month-view';
		this.dayCount = 7;
		this.slotHeight = 20;
		this.eventHolderTopOffset = 25;

		this.preBuild();
	}
	MonthView.prototype = Object.create(View.prototype);
	MonthView.prototype.constructor = MonthView;

	MonthView.prototype.preBuild = function()
	{
		this.viewCont = BX.create('DIV', {props: {className: this.contClassName}, style: {display: 'none'}});
	};

	MonthView.prototype.build = function()
	{
		this.titleCont = this.viewCont.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month-row-days-week'}}));

		this.gridWrap = this.viewCont.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-wrap'}}));

		this.gridMonthContainer = this.gridWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month-container'}}));

		this.grid = this.gridMonthContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month calendar-grid-month-current'}}));
	};

	MonthView.prototype.show = function()
	{
		View.prototype.show.apply(this, arguments);

		this.buildDaysTitle();
		this.buildDaysGrid();

		if (this.calendar.navCalendar)
			this.calendar.navCalendar.hide();

		this.displayEntries();
		this.calendar.initialViewShow = false;
	};

	MonthView.prototype.hide = function()
	{
		View.prototype.hide.apply(this, arguments);
	};

	MonthView.prototype.increaseViewRangeDate = function()
	{
		this.changeViewRangeDate(1);

		var nextGrid = this.gridMonthContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month calendar-grid-month-next' + ' ' + this.animateClass}}));
		BX.addClass(this.grid, this.animateClass);
		this.setTitle();

		this.buildDaysGrid({grid: nextGrid});

		// Prepare entries while animatin goes
		this.preloadEntries();

		setTimeout(BX.delegate(function()
		{
			// Start CSS animation
			BX.addClass(this.gridMonthContainer, "calendar-change-month-next");

			// Wait till the animation ends
			setTimeout(BX.delegate(function()
			{
				// Clear old grid, now it's hidden and use new as old one
				BX.removeClass(this.gridMonthContainer, "calendar-change-month-next");
				BX.removeClass(nextGrid, "calendar-grid-month-next");
				BX.addClass(nextGrid, "calendar-grid-month-current");
				BX.remove(this.grid);
				this.grid = nextGrid;
				BX.removeClass(this.grid, this.animateClass);

				// Display loaded entries for new view range
				this.displayEntries();
			}, this), 400);
		}, this), 0);
	};

	MonthView.prototype.decreaseViewRangeDate = function()
	{
		this.changeViewRangeDate(-1);

		var previousGrid = this.gridMonthContainer.insertBefore(BX.create('DIV', {props: {className: 'calendar-grid-month calendar-grid-month-previous' + ' ' + this.animateClass}}), this.grid);
		BX.addClass(this.grid, this.animateClass);

		this.setTitle();
		this.buildDaysGrid({grid: previousGrid});

		// Prepare entries while animatin goes
		this.preloadEntries();

		setTimeout(BX.delegate(function()
		{
			// Start CSS animation
			BX.addClass(this.gridMonthContainer, "calendar-change-month-previous");

			// Wait till the animation ends
			setTimeout(BX.delegate(function()
			{
				// Clear old grid, now it's hidden and use new as old one
				BX.removeClass(this.gridMonthContainer, "calendar-change-month-previous");
				BX.removeClass(previousGrid, "calendar-grid-month-previous");
				BX.addClass(previousGrid, "calendar-grid-month-current");
				BX.remove(this.grid);
				this.grid = previousGrid;
				BX.removeClass(this.grid, this.animateClass);

				// Display loaded entries for new view range
				this.displayEntries();
			}, this), 400);
		}, this), 0);
	};

	MonthView.prototype.getViewRange = function()
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			endDate = new Date(viewRangeDate.getTime());

		endDate.setMonth(viewRangeDate.getMonth() + 1);
		return {start: viewRangeDate, end: endDate};
	};

	MonthView.prototype.changeViewRangeDate = function(value)
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			newDate = new Date(viewRangeDate.getTime());

		newDate.setMonth(newDate.getMonth() + value);

		this.calendar.setViewRangeDate(newDate);
		return newDate;
	};

	MonthView.prototype.adjustViewRangeToDate = function(date)
	{
		var
			currentViewRangeDate = this.calendar.getViewRangeDate(),
			viewRangeDate = false;

		var diff = date.getMonth() - currentViewRangeDate.getMonth();
		if (diff == 1)
		{
			this.increaseViewRangeDate();
		}
		else if (diff == -1)
		{
			this.decreaseViewRangeDate();
		}
		else
		{
			if (date && date.getTime)
			{
				viewRangeDate = new Date(date.getTime());
				viewRangeDate.setDate(1);
				viewRangeDate.setHours(0, 0, 0, 0);
				this.calendar.setViewRangeDate(viewRangeDate);
			}

			this.fadeAnimation(this.getContainer(), 100, BX.delegate(function(){
				this.show();
				this.getContainer().style.opacity = 0;
				this.showAnimation(this.getContainer(), 300)
			}, this));
		}

		return viewRangeDate;
	};

	MonthView.prototype.getAdjustedDate = function(date, viewRange)
	{
		if (!date)
		{
			date = new Date();
		}

		if (date.getTime() < viewRange.start.getTime())
		{
			date = new Date(viewRange.start.getTime());
		}

		if (date.getTime() > viewRange.end.getTime())
		{
			date = new Date(viewRange.end.getTime());
		}

		var
			currentViewRangeDate = this.calendar.getViewRangeDate(),
			viewRangeDate = false;

		if (date && date.getTime)
		{
			viewRangeDate = new Date(date.getTime());
			viewRangeDate.setDate(1);
			viewRangeDate.setHours(0, 0, 0, 0);
		}

		return viewRangeDate;
	};

	MonthView.prototype.buildDaysTitle = function()
	{
		BX.cleanNode(this.titleCont);

		var
			i, day,
			weekDays = this.util.getWeekDays();

		for (i = 0; i < weekDays.length; i++)
		{
			day = weekDays[i];
			this.titleCont.appendChild(BX.create('DIV',
			{
				props: {
					className: 'calendar-grid-month-cell'
				},
				html: '<span class="calendar-grid-cell-inner">' +
					BX.message('EC_MONTH_WEEK_TITLE').replace('#DAY_OF_WEEK#', day[1])
				+ '</span>'
			}));
		}
	};

	MonthView.prototype.buildDaysGrid = function(params)
	{
		if (!params)
			params = {};

		var
			i, dayOffset,
			grid = params.grid || this.grid,
			viewRangeDate = this.calendar.getViewRangeDate(),
			year = viewRangeDate.getFullYear(),
			month = viewRangeDate.getMonth(),
			height = this.util.getViewHeight(),
			displayedRange = BX.clone(this.getViewRange(), true),
			date = new Date();

		BX.cleanNode(grid);
		date.setFullYear(year, month, 1);

		this.dayIndex = {};
		this.days = [];
		this.entryHolders = [];

		this.currentMonthRow = false;
		this.monthRows = [];

		if (this.util.getWeekStart() != this.util.getWeekDayByInd(date.getDay()))
		{
			dayOffset = this.util.getWeekDayOffset(this.util.getWeekDayByInd(date.getDay()));
			date.setDate(date.getDate() - dayOffset);

			displayedRange.start = new Date(date.getTime());
			displayedRange.start.setHours(0, 0, 0, 0);

			for (i = 0; i < dayOffset; i++)
			{
				this.buildDayCell({date: date, month: 'previous', grid: grid});
				date.setDate(date.getDate() + 1);
			}
		}

		date.setFullYear(year, month, 1);
		while(date.getMonth() == month)
		{
			this.buildDayCell({date: date, grid: grid});
			date.setDate(date.getDate() + 1);
		}

		if (this.util.getWeekStart() != this.util.getWeekDayByInd(date.getDay()))
		{
			dayOffset = this.util.getWeekDayOffset(this.util.getWeekDayByInd(date.getDay()));
			date.setFullYear(year, month + 1, 1);
			for (i = dayOffset; i < 7; i++)
			{
				this.buildDayCell({date: date, month: 'next', grid: grid});
				date.setDate(date.getDate() + 1);
			}

			displayedRange.end = new Date(date.getTime());
			displayedRange.end.setHours(23, 59, 59, 59);
		}

		this.calendar.setDisplayedViewRange(displayedRange);

		// Adjusting rows height to the height of the view
		if (this.monthRows.length > 0)
		{
			this.rowHeight = Math.round(height / this.monthRows.length);

			this.slotsCount = Math.floor((this.rowHeight - this.eventHolderTopOffset) / this.slotHeight);
			for (i = 0; i < this.monthRows.length; i++)
			{
				this.monthRows[i].style.height = this.rowHeight + 'px';
			}
		}
	};

	MonthView.prototype.buildDayCell = function(params)
	{
		var
			date = params.date,
			className = '',
			time = Math.round(date.getTime() / 1000) * 1000,
			day = date.getDay(),
			dayCode = this.util.getDayCode(date),
			weekDay = this.util.getWeekDayByInd(day),
			weekNumber = false,
			startNewWeek = this.util.getWeekStart() == weekDay;

		if (startNewWeek)
		{
			this.currentMonthRow = params.grid.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month-row'}}));
			this.monthRows.push(this.currentMonthRow);

			if (this.util.showWeekNumber())
			{
				weekNumber = this.util.getWeekNumber(time);
			}
		}

		if (params.month == 'previous')
		{
			className += ' calendar-grid-previous-month-day';
		}
		else if (params.month == 'next')
		{
			className += ' calendar-grid-next-month-day';
		}

		if (this.util.isHoliday(date))
		{
			className += ' calendar-grid-holiday';
		}

		if (this.util.isToday(date))
		{
			className += ' calendar-grid-today';
		}

		this.days.push({
			date: new Date(date.getTime()),
			dayOffset: this.util.getWeekDayOffset(weekDay),
			rowIndex: this.monthRows.length - 1,
			holderIndex: this.entryHolders.length,
			node: this.currentMonthRow.appendChild(BX.create('DIV', {
				props: {className: BX.util.trim('calendar-grid-month-cell' + className)},
				attrs: {'data-bx-calendar-month-day': dayCode},
				html: '<span class="calendar-grid-cell-inner">' +
				'<span class="calendar-num-day" data-bx-calendar-date="' + time + '">' +
				(date.getDate() == 1 ? BX.message('EC_MONTH_SHORT')
					.replace('#MONTH#', BX.date.format('M', time / 1000))
					.replace('#DATE#', date.getDate())
					: date.getDate()) +
				'</span>' +
				(weekNumber ? '<span class="calendar-num-week"  data-bx-cal-time="' + time + '" data-bx-calendar-weeknumber="' + weekNumber + '">' + weekNumber + '</span>' : '') +
				'</span>'
			})),
			dayCode: dayCode
		});
		this.dayIndex[this.days[this.days.length - 1].dayCode] = this.days.length - 1;

		this.calendar.dragDrop.registerDay(this.days[this.days.length - 1]);

		if (this.currentMonthRow && this.util.getWeekEnd() == weekDay)
		{
			this.entryHolders.push(this.currentMonthRow.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month-events-holder'}})));
		}
	};

	MonthView.prototype.setTitle = function()
	{
		var viewRangeDate = this.calendar.getViewRangeDate();
		View.prototype.setTitle.apply(this, [BX.date.format('f', viewRangeDate.getTime() / 1000) + ', #GRAY_START#' + viewRangeDate.getFullYear() + '#GRAY_END#']);
	};

	MonthView.prototype.displayEntries = function(params)
	{
		var
			prevElement,
			i, j, entry, part, dayPos, entryPart, day, entryStarted,
			partsStorage = [],
			entryDisplayed, showHiddenLink,
			viewRange = this.calendar.getDisplayedViewRange();

		if (!params)
			params = {};

		if (params.reloadEntries !== false)
		{
			// Get list of entries
			this.entries = this.entryController.getList({
				startDate: new Date(viewRange.start.getFullYear(), viewRange.start.getMonth(), 1),
				finishDate: new Date(viewRange.end.getFullYear(), viewRange.end.getMonth() + 1, 1),
				viewRange: viewRange,
				finishCallback: BX.proxy(this.displayEntries, this)
			});
		}

		// Clean holders
		this.entryHolders.forEach(function(holder)
		{
			BX.cleanNode(holder);
		});

		// Clean days
		this.days.forEach(function(day)
		{
			day.slots = [];
			day.entries = {
				list: [],
				started: [],
				hidden: []
			};
		});

		if (this.entries === false || !this.entries || !this.entries.length)
			return;

		// Prepare for arrangement
		for (i = 0; i < this.entries.length; i++)
		{
			entry = this.entries[i];
			this.entriesIndex[entry.uid] = i;
			entry.cleanParts();
			entryStarted = false;

			for (dayPos = this.dayIndex[entry.startDayCode]; dayPos < this.days.length; dayPos++)
			{
				day = this.days[dayPos];
				if (day.dayCode == entry.startDayCode || entryStarted && day.dayOffset == 0)
				{
					entryStarted = true;

					part = entry.startPart({
						from: day,
						daysCount: 0
					});

					day.entries.started.push({
						entry: entry,
						part: part
					});
				}

				if(entryStarted)
				{
					day.entries.list.push({
						entry: entry,
						part: part
					});

					part.daysCount++;
					part.to = day;

					if (day.dayCode == entry.endDayCode || day.dayOffset == this.dayCount - 1)
					{
						// here we know where part of event starts and ends
						partsStorage.push({part: part, entry: entry});

						// Event finished
						if (day.dayCode == entry.endDayCode)
						{
							break;
						}
					}
				}
			}
		}

		// Display parts
		for (i = 0; i < partsStorage.length; i++)
		{
			this.displayEntryPiece(partsStorage[i]);
		}

		// Final arrangement on the grid
		for (dayPos = 0; dayPos < this.days.length; dayPos++)
		{
			day = this.days[dayPos];

			if (day.entries.started.length > 0)
			{
				if (day.entries.started.length > 0)
					day.entries.started.sort(this.calendar.entryController.sort);

				for(i = 0; i < day.entries.started.length; i++)
				{
					element = day.entries.started[i];
					if (element)
					{
						entry = element.entry;
						entryPart = element.part;
						entryDisplayed = false;
						for(j = 0; j < this.slotsCount; j++)
						{
							if (day.slots[j] !== false)
							{
								this.occupySlot({slotIndex: j, startIndex: dayPos, endIndex: dayPos + entryPart.daysCount});
								entryDisplayed = true;
								entry.getWrap(entryPart.partIndex).style.top = (j * this.slotHeight) + 'px';
								break;
							}
						}

						if (!entryDisplayed)
						{
							prevElement = day.entries.started[i - 1];
							if (prevElement)
							{
								day.entries.hidden.push(prevElement);
								prevElement.entry.getWrap(prevElement.part.partIndex).style.display = 'none';
							}
							day.entries.hidden.push(element);
							entry.getWrap(entryPart.partIndex).style.display = 'none';
						}
					}
				}
			}

			// Here we check all entries in the day and if any of it
			// was hidden, we going to show 'show all' link
			if (day.entries.list.length > 0)
			{
				showHiddenLink = false;
				for(i = 0; i < day.entries.list.length; i++)
				{
					if (day.entries.list[i].part.params.wrapNode.style.display == 'none')
					{
						showHiddenLink = true;
						break;
					}
				}

				if (showHiddenLink)
				{
					day.hiddenStorage = this.entryHolders[day.holderIndex].appendChild(BX.create('DIV', {
						props: {
							className: 'calendar-event-line-wrap calendar-event-more-btn-container'
						},
						attrs: {'data-bx-calendar-show-all-events': day.dayCode},
						style: {
							top: (this.rowHeight - 42) + 'px',
							//top: (this.rowHeight < 135 ? (this.rowHeight - 42) : (this.rowHeight - 45)) + 'px',
							left: 'calc((100% / ' + this.dayCount + ') * (' + (day.dayOffset + 1) + ' - 1) + 2px)',
							width: 'calc(100% / ' + this.dayCount + ' - 3px)'
						}
					}));
					day.hiddenStorageText = day.hiddenStorage.appendChild(BX.create('span', {props: {className: 'calendar-event-more-btn'}}));
					day.hiddenStorage.style.display = 'block';
					day.hiddenStorageText.innerHTML = BX.message('EC_SHOW_ALL') + ' ' + day.entries.list.length;
				}
				else if (day.hiddenStorage)
				{
					day.hiddenStorage.style.display = 'none';
				}
			}
		}

		BX.addClass(this.gridMonthContainer, 'calendar-events-holder-show');
	};

	MonthView.prototype.displayEntryPiece = function(params)
	{
		var
			res = false,
			entry = params.entry,
			from = params.part.from,
			daysCount = params.part.daysCount,
			partWrap, dotNode, innerNode, nameNode, timeNode, endTimeNode, innerContainer,
			entryClassName = 'calendar-event-line-wrap',
			deltaPartWidth = 0,
			startArrow, endArrow,
			holder = params.holder || this.entryHolders[from.holderIndex];

		if (holder)
		{
			if (entry.isFullDay())
			{
				entryClassName += ' calendar-event-line-fill';
			}
			else if (entry.isLongWithTime())
			{
				entryClassName += ' calendar-event-line-border';
			}

			if (entry.isExternal())
			{
				entryClassName += ' calendar-event-line-intranet';
			}

			if (!params.popupMode && this.util.getDayCode(entry.from) !== this.util.getDayCode(from.date))
			{
				entryClassName += ' calendar-event-line-start-yesterday';
				deltaPartWidth += 8;
				startArrow = this.getArrow('left', entry.color, entry.isFullDay());
			}

			if (!params.popupMode && this.util.getDayCode(entry.to) !== this.util.getDayCode(params.part.to.date))
			{
				entryClassName += ' calendar-event-line-finish-tomorrow';
				endArrow = this.getArrow('right', entry.color, entry.isFullDay());
				deltaPartWidth += 12;
			}

			if (startArrow && !endArrow)
			{
				deltaPartWidth += 4;
			}

			if (deltaPartWidth == 0)
			{
				deltaPartWidth = 5;
			}

			partWrap = BX.create('DIV', {
				attrs: {'data-bx-calendar-entry': entry.uid},
				props: {className: entryClassName}, style: {
					top: 0,
					left: 'calc((100% / ' + this.dayCount + ') * (' + (from.dayOffset + 1) + ' - 1) + 2px)',
					width: 'calc(' + daysCount + ' * 100% / ' + this.dayCount + ' - ' + deltaPartWidth + 'px)'
				}
			});

			if (startArrow)
			{
				partWrap.appendChild(startArrow);
				partWrap.style.left = '9px';
			}

			if (endArrow)
			{
				partWrap.appendChild(endArrow);
			}

			innerContainer = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner-container'}}));
			innerNode = innerContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner'}}));
			dotNode = innerNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-dot'}}));

			if (entry.isFullDay())
			{
				innerNode.style.maxWidth = 'calc(200% / ' + daysCount + ' - 3px)';
			}
			else if (entry.isLongWithTime())
			{
				partWrap.style.borderColor = entry.color;
				innerNode.style.maxWidth = 'calc(200% / ' + daysCount + ' - 3px)';

				// first part
				if (params.part.partIndex == 0)
				{
					timeNode = innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-time'}, text: this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes())}));
					innerNode.style.width = 'calc(100% / ' + daysCount + ' - 3px)';
				}

				// Last part
				if (params.part.partIndex == entry.parts.length - 1)
				{
					if (daysCount > 1 && entry.parts.length > 1)
					{
						innerNode.style.width = 'calc(' + (daysCount - 1) + '00% / ' + daysCount + ' - 3px)';
					}

					if (!params.popupMode)
					{
						endTimeNode = innerNode.appendChild(BX.create('SPAN', {
							props: {className: (entry.parts.length > 1 && daysCount == 1) ? 'calendar-event-line-time' : 'calendar-event-line-expired-time'},
							text: this.calendar.util.formatTime(entry.to.getHours(), entry.to.getMinutes())
						}));
					}
				}
			}
			else
			{
				timeNode = innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-time'}, text: this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes())}));
			}
			nameNode = innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-text'}, text: params.entry.name}));

			if (entry.isFullDay())
			{
				innerContainer.style.backgroundColor = this.calendar.util.hexToRgba(entry.color, 0.3);
				innerContainer.style.borderColor = this.calendar.util.hexToRgba(entry.color, 0.3);
			}
			else
			{
				if (entry.isLongWithTime())
				{
					innerContainer.style.borderColor = this.calendar.util.hexToRgba(entry.color, 0.5);
				}
				dotNode.style.backgroundColor = entry.color;
			}

			holder.appendChild(partWrap);

			if (entry.opacity !== undefined)
			{
				partWrap.style.opacity = entry.opacity;
			}

			res = {
				wrapNode: partWrap,
				nameNode: nameNode,
				innerContainer: innerContainer,
				innerNode: innerNode,
				timeNode: timeNode || false,
				endTimeNode: endTimeNode || false,
				dotNode: dotNode
			};

			if (!params.popupMode)
			{
				params.entry.registerPartNode(params.part, res);
			}

			this.calendar.dragDrop.registerEntry(partWrap, params);
		}

		return res;
	};


	MonthView.prototype.refreshEventsOnWeek = function(ind)
	{
		var
			startDayInd = ind * 7,
			endDayInd = (ind + 1) * 7,
			day, i, k, arEv, j, ev, arAll, arHid,
			slots = [],
			maxEventCount = 5,
			step = 0;

		for(j = 0; j < maxEventCount; j++)
			slots[j] = 0;

		for (i = startDayInd; i < endDayInd; i++)
		{
			day = this.activeDateObjDays[i];

			if (!day)
				continue;

			day.arEvents.hidden = [];
			arEv = day.arEvents.begining;
			arHid = [];

			if (arEv.length > 0)
			{
				arEv.sort(function(a, b)
				{
					if (b.daysCount == a.daysCount && a.daysCount == 1)
						return a.oEvent.DT_FROM_TS - b.oEvent.DT_FROM_TS;
					return b.daysCount - a.daysCount;
				});

				eventloop:
					for(k = 0; k < arEv.length; k++)
					{
						ev = arEv[k];
						if (!ev)
							continue;

						if (!this.arEvents[ev.oEvent.ind])
						{
							day.arEvents.begining = arEv = BX.util.deleteFromArray(arEv, k);
							ev = arEv[k];
							if (!ev)
								continue; //break ?
						}

						for(j = 0; j < this.maxEventCount; j++)
						{
							if (slots[j] - step <= 0)
							{
								slots[j] = step + ev.daysCount;
								this.ShowEventOnLevel(ev.oEvent.oParts[ev.partInd], j, ind);
								continue eventloop;
							}
						}
						arHid[ev.oEvent.ID] = true;
						day.arEvents.hidden.push(ev);
					}
			}

			// For all events in the day
			arAll = day.arEvents.all;
			for (var x = 0; x < arAll.length; x++)
			{
				ev = arAll[x];
				if (!ev || arHid[ev.oEvent.ID])
				{
					continue;
				}

				if (!this.arEvents[ev.oEvent.ind])
				{
					day.arEvents.all = arAll = BX.util.deleteFromArray(arAll, x);
					ev = arAll[x];
					if (!ev)
					{
						continue;
					}
				}

				if (ev.oEvent.oParts && ev.partInd != undefined && ev.oEvent.oParts[ev.partInd] && ev.oEvent.oParts[ev.partInd].style.display == 'none')
				{
					day.arEvents.hidden.push(ev);
				}
			}
			//this.ShowMoreEventsSelect(day);
			step++;
		}
	};

	MonthView.prototype.handleClick = function(params)
	{
		if (this.isActive())
		{
			if (!params)
				params = {};

			var dayCode, uid;
			if (params.specialTarget && (uid = params.specialTarget.getAttribute('data-bx-calendar-entry')))
			{
				this.handleEntryClick(
					{
						uid: uid,
						specialTarget: params.specialTarget,
						target: params.target,
						e: params.e
					});
			}
			else if (params.specialTarget && (dayCode = params.specialTarget.getAttribute('data-bx-calendar-show-all-events')))
			{
				this.deselectEntry();
				if (this.dayIndex[dayCode] !== undefined && this.days[this.dayIndex[dayCode]])
				{
					this.showAllEventsInPopup({day: this.days[this.dayIndex[dayCode]]});
				}
			}
			else if (!this.calendar.util.readOnlyMode()
				&& this.entryController.canDo(false, 'add_event') &&
				(dayCode = params.specialTarget && params.specialTarget.getAttribute('data-bx-calendar-month-day')))
			{
				this.deselectEntry();
				if (this.dayIndex[dayCode] !== undefined && this.days[this.dayIndex[dayCode]])
				{
					this.showNewEntryWrap({dayFrom: this.days[this.dayIndex[dayCode]]});
				}
			}
		}
	};

	MonthView.prototype.showNewEntryWrap = function(params)
	{
		var
			entryTime, entryName,
			partWrap, innerNode, innerContainer,
			entryClassName = 'calendar-event-line-wrap',
			deltaPartWidth = 0,
			from = params.dayFrom,
			daysCount = 1,
			holder = this.entryHolders[from.holderIndex],
			section = this.calendar.sectionController.getCurrentSection(),
			color = section.color;

		entryTime = this.entryController.getTimeForNewEntry(from.date);
		entryName = this.entryController.getDefaultEntryName();

		partWrap = BX.create('DIV', {
			props: {className: entryClassName}, style: {
				opacity: 0,
				top: 0,
				left: 'calc((100% / ' + this.dayCount + ') * (' + (from.dayOffset + 1) + ' - 1) + 2px)',
				width: 'calc(' + daysCount + ' * 100% / ' + this.dayCount + ' - ' + deltaPartWidth + 'px)'
			}
		});

		innerContainer = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner-container'}}));
		innerNode = innerContainer.appendChild(BX.create('DIV', {props: {className: 'calendar-event-line-inner'}}));
		innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-time'}, style: {color: '#fff'}, text: this.calendar.util.formatTime(entryTime.from.getHours(), entryTime.from.getMinutes())}));
		innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-line-text'}, style: {color: '#fff'}, text: entryName}));

		partWrap.style.backgroundColor = color;
		partWrap.style.borderColor = color;

		holder.appendChild(partWrap);

		var pos = BX.pos(partWrap);
		var entryClone = BX.adjust(document.body.appendChild(partWrap.cloneNode(true)), {
			props: {className: 'calendar-event-line-clone'},
			style: {
				width: (pos.width - 6) + 'px',
				height: pos.height + 'px',
				top : pos.top + 'px',
				left : (pos.left + 1)+ 'px'
			}
		});

		BX.addClass(holder, 'shifted');
		holder.style.height = (this.slotsCount - 1) * this.slotHeight + 'px';

		setTimeout(function(){
			entryClone.style.opacity = '1';
		},100);

		setTimeout(BX.delegate(function(){

			// Show simple add entry popup
			this.showSimplePopup({
				entryTime: entryTime,
				entryName: entryName,
				nameNode: entryClone.querySelector('.calendar-event-line-text'),
				timeNode: entryClone.querySelector('.calendar-event-line-time'),
				entryNode: entryClone,
				section: section,
				closeCallback: function()
				{
					BX.cleanNode(entryClone, true);
					BX.cleanNode(partWrap, true);
					BX.removeClass(holder, 'shifted');
					holder.style.height = '1px';
				},
				changeDateCallback: BX.delegate(function(date)
				{
					var dayCode = this.util.getDayCode(date);
					if (dayCode && this.dayIndex[dayCode] !== undefined && this.days[this.dayIndex[dayCode]])
					{
						var dayFrom = this.days[this.dayIndex[dayCode]];
						partWrap.style.left = 'calc((100% / ' + this.dayCount + ') * (' + (dayFrom.dayOffset + 1) + ' - 1) + 2px)';

						this.entryHolders[dayFrom.holderIndex].appendChild(partWrap);
						var pos = BX.pos(partWrap);
						BX.adjust(entryClone, {
							style: {
								width: (pos.width + 1) + 'px',
								height: pos.height + 'px',
								top : pos.top + 'px',
								left : pos.left + 'px'
							}
						});
					}
				}, this),
				saveCallback: function()
				{

				},
				changeSectionCallback: function(section)
				{
					var color = section.color;
					if (entryClone)
					{
						entryClone.style.background = color;
						entryClone.style.borderColor = color;
					}
				},
				fullFormCallback: BX.delegate(this.showEditSlider, this)
			});
		}, this), 200);
	};

	// Year view of the calendar
	function YearView(params)
	{
		View.apply(this, arguments);
		this.name = 'year';
		this.title = BX.message('EC_VIEW_YEAR');
		this.contClassName = 'calendar-year-view';
		this.build();
	}
	YearView.prototype = Object.create(View.prototype);
	YearView.prototype.constructor = YearView;

	// List view of the calendar
	function ListView()
	{
		View.apply(this, arguments);
		this.name = 'list';
		this.filterMode = false;
		this.title = BX.message('EC_VIEW_LIST');
		this.contClassName = 'calendar-list-view';
		this.loadDaysBefore = 30;
		this.animateAmount = 5;
		this.loadLimitPrevious = 5;
		this.loadLimit = 15;
		this.loadDaysAfter = 60;
		this.SCROLL_DELTA_HEIGHT = 200;
		this.todayCode = this.calendar.util.getDayCode(new Date());

		this.preBuild();
	}
	ListView.prototype = Object.create(View.prototype);
	ListView.prototype.constructor = ListView;

	ListView.prototype.preBuild = function()
	{
		this.viewCont = BX.create('DIV', {props: {className: this.contClassName}, style: {display: 'none'}});

		BX.addCustomEvent(this.calendar, 'afterSetView', BX.delegate(function(params){
			if (params.viewName !== this.name && this.filterMode)
			{
				this.resetFilterMode();
			}
		}, this));

	};

	ListView.prototype.build = function()
	{
		this.titleCont = this.viewCont.appendChild(BX.create('DIV', {props: {className: 'calendar-grid-month-row-days-week'}}));

		this.streamScrollWrap = this.viewCont.appendChild(
			BX.create('DIV', {
				props: {className: 'calendar-timeline-stream-container calendar-custom-scroll'},
				style: {height: this.util.getViewHeight() + 'px'},
				events: {scroll: BX.proxy(this.scrollHandle, this)}
		}));

		this.streamContentWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-container-content'}}));
		this.topLoaderBlock = this.streamContentWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-loader-block'}}));
		this.listWrap = this.streamContentWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-container-list'}}));
		this.bottomLoaderBlock = this.streamContentWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-loader-block'}}));
	};

	ListView.prototype.setTitle = function()
	{
		var viewRangeDate = this.calendar.getViewRangeDate();
		View.prototype.setTitle.apply(this, [BX.date.format('f', viewRangeDate.getTime() / 1000) + ', #GRAY_START#' + viewRangeDate.getFullYear() + '#GRAY_END#']);
	};

	ListView.prototype.show = function(params)
	{
		if (!params)
			params = {};

		View.prototype.show.apply(this, arguments);
		this.showNavigationCalendar();
		BX.remove(this.calendar.additionalInfoOuter);

		var
			initYear = parseInt(this.calendar.util.config.init_year),
			initMonth = parseInt(this.calendar.util.config.init_month);
		this.displayedRange = {
			start: new Date(initYear, initMonth - 2, 1),
			end: new Date(initYear, initMonth + 2, 0)
		};

		this.calendar.setDisplayedViewRange(this.displayedRange);
		if (params.displayEntries !== false)
		{
			this.displayEntries();
		}
		this.nothingToLoadNext = false;
		this.nothingToLoadPrevious = false;
	};

	ListView.prototype.displayEntries = function(params)
	{
		if (!params)
			params = {};

		if (params.reloadEntries !== false)
		{
			// Get list of entries
			this.entries = this.entryController.getList({
				startDate: this.displayedRange.start,
				finishDate: this.displayedRange.end,
				viewRange: this.displayedRange,
				finishCallback: BX.proxy(this.displayEntries, this)
			});
		}

		// Clean holders
		BX.cleanNode(this.listWrap);
		this.dateGroupIndex = {};

		this.groups = [];
		this.groupsDayCodes = [];
		this.groupsIndex = {};
		this.entryListIndex = {};

		if (this.entries === false || !this.entries || !this.entries.length)
		{
			this.showEmptyBlock();
			return;
		}

		if (this.noEntriesWrap)
		{
			this.noEntriesWrap.style.display = 'none';
		}
		this.streamContentWrap.style.display = '';
		this.entryParts = [];

		this.attachEntries(this.entries, false, BX.delegate(this.focusOnDate, this));
		setTimeout(BX.delegate(this.focusOnDate, this), 100);
	};

	ListView.prototype.displayResult = function(entries)
	{
		this.streamContentWrap.style.display = '';
		if (this.filterLoaderWrap)
		{
			this.filterLoaderWrap.style.display = 'none';
		}
		BX.addClass(this.streamContentWrap, 'calendar-timeline-stream-search-result');

		// Clean holders
		BX.cleanNode(this.listWrap);
		this.dateGroupIndex = {};

		this.groups = [];
		this.groupsDayCodes = [];
		this.groupsIndex = {};
		this.entryListIndex = {};

		this.resultEntries = entries;
		this.resultEntriesIndex = {};
		entries.forEach(function(entry, index)
		{
			this.resultEntriesIndex[entry.uid] = index;
		}, this);

		if (entries === false || !entries || !entries.length)
		{
			this.showEmptyBlock();
			return;
		}

		if (this.noEntriesWrap)
		{
			this.noEntriesWrap.style.display = 'none';
		}
		this.streamContentWrap.style.display = '';
		this.entryParts = [];

		this.attachEntries(entries, 'next');
	};

	ListView.prototype.attachEntries = function(entries, animation, focusCallback)
	{
		if (!entries && !entries.length)
			return;

		var
			deltaLength = this.entries.length - entries.length,
			globalIndex = deltaLength,
			from, index,
			fromCode, toCode;

		entries.forEach(function(entry)
		{
			entry.globalIndex = globalIndex++;
			fromCode = this.calendar.util.getDayCode(entry.from);
			toCode = this.calendar.util.getDayCode(entry.to);

			index = 0;
			this.entryParts.push({
				index: index,
				dayCode: fromCode,
				from: entry.from,
				entry: entry
			});
			if (fromCode != toCode)
			{
				from = new Date(entry.from.getTime() + this.calendar.util.dayLength);
				while (from.getTime() < entry.to.getTime())
				{
					this.entryParts.push({
						index: ++index,
						dayCode: this.calendar.util.getDayCode(from),
						from: from,
						entry: entry
					});
					from = new Date(from.getTime() + this.calendar.util.dayLength);
				}
				entry.lastPartIndex = index;
			}
		}, this);

		//var reverseSort = this.currentLoadMode == 'previous';
		var reverseSort = false;
		this.entryParts.sort(function(a, b)
		{
			if (a.dayCode == b.dayCode)
			{
				if (a.entry.isTask() !== b.entry.isTask())
				{
					if (a.entry.isTask())
						return 1;
					if (b.entry.isTask())
						return -1;
				}

				if (a.entry.isFullDay() !== b.entry.isFullDay())
				{
					if (a.entry.isFullDay())
						return -1;
					if (b.entry.isFullDay())
						return 1;
				}
			}

			if (a.index > 0 || b.index > 0)
			{
				return !reverseSort ? a.index - b.index : b.index - a.index;
			}

			return !reverseSort ? a.entry.from.getTime() - b.entry.from.getTime() : b.entry.from.getTime() - a.entry.from.getTime();
		});

		this.today = new Date();
		this.animationMode = animation;
		this.currentDisplayedEntry = 0;
		this.actuallyAnimatedEntryCount = 0;
		this.displayEntry(this.entryParts[this.currentDisplayedEntry], animation, focusCallback);
	};

	ListView.prototype.displayEntry = function(part, animation, focusCallback)
	{
		var group;
		if (part.from.getTime() > this.today.getTime() && !this.groups[this.groupsIndex[this.todayCode]] && !this.filterMode)
		{
			this.createEntryGroupForDate(this.today, animation);
		}

		group = this.groups[this.groupsIndex[part.dayCode]];
		if (!group)
		{
			this.createEntryGroupForDate(part.from, animation);
			group = this.groups[this.groupsIndex[part.dayCode]];
		}

		if (group.emptyWarning)
		{
			BX.remove(group.emptyWarning);
		}

		var entryPartUid = this.getUniqueId(part);

		if (this.actuallyAnimatedEntryCount > this.animateAmount)
		{
			animation = false;
			this.animationMode = false;
		}

		var show = !this.entryListIndex[entryPartUid];
		if (show)
		{
			this.entryListIndex[entryPartUid] = true;

			var
				timeLabel, wrap, attendesNode,
				entry = part.entry;

			this.entriesIndex[entry.uid] = entry.globalIndex;
			if (entry.isFullDay())
			{
				timeLabel = BX.message('EC_ALL_DAY');
			}
			else if (entry.isLongWithTime())
			{
				if (part.index === 0)
					timeLabel = this.calendar.util.formatTime(entry.from);
				else if (part.index === entry.lastPartIndex)
					timeLabel = BX.message('EC_TILL_TIME').replace('#TIME#', this.calendar.util.formatTime(entry.to));
				else
					timeLabel = BX.message('EC_ALL_DAY');
			}
			else
			{
				timeLabel = this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes()) + ' &ndash; ' + this.calendar.util.formatTime(entry.to.getHours(), entry.to.getMinutes())
			}

			wrap = group.content.appendChild(BX.create('DIV', {
				attrs: {'data-bx-calendar-entry': entry.uid},
				props: {
					className: 'calendar-timeline-stream-content-event' + (animation ? ' calendar-timeline-stream-section-event-animate-' + animation : '')
			}}));

			wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-content-event-time'}, html: '<span class="calendar-timeline-stream-content-event-time-link">' + timeLabel + '</span>'}));

			var location = this.calendar.util.getTextLocation(entry.location) || '';
			if (location)
			{
				location = '<span class="calendar-timeline-stream-content-event-location">(' + BX.util.htmlspecialchars(location) + ')</span>';
			}

			wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-content-event-name'}, html: '<span class="calendar-timeline-stream-content-event-color" style="background-color: ' + entry.color + '"></span><span class="calendar-timeline-stream-content-event-name-link">' + BX.util.htmlspecialchars(entry.name) + '</span>' + location}));

			attendesNode = wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-content-event-members'}}));

			if (entry.isMeeting() && entry.getAttendees().length > 0)
			{
				this.showAttendees(attendesNode, entry.getAttendees().filter(function (user)
				{
					return user.STATUS == 'Y' || user.STATUS == 'H';
				}), entry.getAttendees().length);
			}
			else if (entry.isPersonal())
			{
				attendesNode.innerHTML = BX.message('EC_EVENT_IS_MINE');
			}

			part.DOM = {};

			if (part.dayCode == this.todayCode && BX.type.isFunction(focusCallback))
			{
				focusCallback();
			}
		}

		if (this.currentDisplayedEntry < this.entryParts.length - 1)
		{
			this.currentDisplayedEntry++;

			if (animation && show)
			{
				this.actuallyAnimatedEntryCount++;
				setTimeout(BX.delegate(function()
				{
					this.displayEntry(this.entryParts[this.currentDisplayedEntry], animation, focusCallback);
				}, this), 300);
			}
			else
			{
				if (this.currentDisplayedEntry % 30 == 0)
				{
					setTimeout(BX.delegate(function ()
					{
						this.displayEntry(this.entryParts[this.currentDisplayedEntry], animation, focusCallback);
					}, this), 1000);
				}
				else
				{
					this.displayEntry(this.entryParts[this.currentDisplayedEntry], animation, focusCallback);
				}
			}
		}
		else if (this.currentDisplayedEntry == this.entryParts.length - 1 && !this.filterMode)
		{
			this.animationMode = false;
			if (!this.groups[this.groupsIndex[this.todayCode]])
			{
				this.createEntryGroupForDate(this.today, animation);
			}
			this.groupsDayCodes.sort();

			if (BX.type.isFunction(focusCallback))
			{
				focusCallback();
			}
		}
	};

	ListView.prototype.showAttendees = function(wrap, attendees, totalCount)
	{
		var
			i,
			user,
			MAX_USER_COUNT = 5,
			userLength = attendees.length,
			MAX_USER_COUNT_DISPLAY = 7;

		if (userLength > 0)
		{
			if (userLength > MAX_USER_COUNT_DISPLAY)
			{
				userLength = MAX_USER_COUNT;
			}

			for (i = 0; i < userLength; i++)
			{
				user = attendees[i] || {};

				wrap.appendChild(BX.create("IMG", {
					attrs: {
						id: 'simple_view_popup_' + user.ID,
						src: user.AVATAR || ''
					},
					props: {
						title: user.DISPLAY_NAME,
						className: 'calendar-member'
					}}));
				(function (userId){setTimeout(function(){BX.tooltip(userId, "simple_view_popup_" + userId);}, 100)})(user.ID);
			}

			if (userLength < attendees.length)
			{
				var moreUsersLink = wrap.appendChild(BX.create("SPAN", {
					props: {className: 'calendar-member-more-count'},
					text: ' ' + BX.message('EC_ATTENDEES_MORE').replace('#COUNT#', attendees.length - userLength),
					events: {click: BX.delegate(function(){
						this.showUserListPopup(moreUsersLink, attendees);
					}, this)}
				}));
			}

			//this.DOM.allUsersLink = this.DOM.attendeesWrap.appendChild(BX.create("SPAN", {
			//	props: {className: 'calendar-member-total-count'},
			//	text: ' (' + BX.message('EC_ATTENDEES_TOTAL_COUNT').replace('#COUNT#', totalCount) + ')',
			//	events: {click: BX.delegate(function(){
			//		this.showUserListPopup(this.DOM.allUsersLink, this.entry.getAttendees());
			//	}, this)}
			//}));
		}
	};

	ListView.prototype.showUserListPopup = function(node, userList)
	{
		if (this.userListPopup)
			this.userListPopup.close();

		this.popup.setAutoHide(false);

		if (!userList || !userList.length)
			return;

		this.DOM.userListPopupWrap = BX.create('DIV', {props: {className: 'calendar-user-list-popup-block'}});
		userList.forEach(function(user){
			var userWrap = this.DOM.userListPopupWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-container calendar-slider-sidebar-user-card'}}));

			userWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-block-avatar'}}))
				.appendChild(BX.create('DIV', {props: {className: 'calendar-slider-sidebar-user-block-item'}}))
				.appendChild(BX.create('IMG', {props: {width: 34, height: 34, src: user.AVATAR}}));

			userWrap.appendChild(
				BX.create("DIV", {props: {className: 'calendar-slider-sidebar-user-info'}}))
				.appendChild(BX.create("A", {
					props: {
						href: user.URL ? user.URL : '#',
						className: 'calendar-slider-sidebar-user-info-name'
					},
					text: user.DISPLAY_NAME
				}));
		}, this);

		this.userListPopup = new BX.PopupWindow(this.calendar.id + "-view-user-list-popup",
			node,
			{
				autoHide: true,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				width: 200,
				resizable: false,
				lightShadow: true,
				content: this.DOM.userListPopupWrap,
				className: 'calendar-user-list-popup',
				zIndex: 4000
			});

		this.userListPopup.setAngle({offset: 36});
		this.userListPopup.show();
		BX.addCustomEvent(this.userListPopup, 'onPopupClose', BX.delegate(function()
		{
			this.popup.setAutoHide(true);
			this.userListPopup.destroy();
		}, this));
	};

	ListView.prototype.appendDateGroup = function(wrap, date, animation)
	{
		var
			y, m, d, minDelta, minValue, monthIndex,
			day = date.getDate(),
			month = date.getMonth() + 1,
			year = date.getFullYear();

		if (!this.dateGroupIndex[year])
		{
			//this.dateGroupIndex[year] = {};
			minDelta = Infinity;
			minValue = year;
			for (y in this.dateGroupIndex)
			{
				if (this.dateGroupIndex.hasOwnProperty(y))
				{
					if (Math.abs(year - y) < minDelta)
					{
						minDelta = Math.abs(year - y);
						minValue = y;
					}
					if (minDelta == 1)
					{
						break;
					}
				}
			}

			this.dateGroupIndex[year] = {
				node: BX.create('DIV', {attrs: {'data-bx-calendar-list-year': year}}),
				monthIndex: {}
			};

			// it's first wrap
			if (minValue == year)
			{
				this.listWrap.appendChild(this.dateGroupIndex[year].node);
			}
			else
			{
				if (year - minValue > 0)
				{
					if (this.dateGroupIndex[minValue].node.nextSibling)
					{
						BX.insertAfter(this.dateGroupIndex[year].node, this.dateGroupIndex[minValue].node);
					}
					else
					{
						this.listWrap.appendChild(this.dateGroupIndex[year].node);
					}
				}
				else
				{
					this.listWrap.insertBefore(this.dateGroupIndex[year].node, this.dateGroupIndex[minValue].node);
				}
			}
		}

		if (!this.dateGroupIndex[year].monthIndex[month])
		{
			minDelta = Infinity;
			minValue = month;
			for (m in this.dateGroupIndex[year].monthIndex)
			{
				if (this.dateGroupIndex[year].monthIndex.hasOwnProperty(m))
				{
					if (Math.abs(month - m) < minDelta)
					{
						minDelta = Math.abs(month - m);
						minValue = m;
					}
					if (minDelta == 1)
					{
						break;
					}
				}
			}

			this.dateGroupIndex[year].monthIndex[month] = {
				node: BX.create('DIV', {attrs: {'data-bx-calendar-list-month': month}}),
				dayIndex: {}
			};

			// it's first wrap
			if (minValue == month)
			{
				this.dateGroupIndex[year].node.appendChild(this.dateGroupIndex[year].monthIndex[month].node)
			}
			else
			{
				if (month - minValue > 0)
				{
					if (this.dateGroupIndex[year].monthIndex[minValue].node.nextSibling)
					{
						BX.insertAfter(this.dateGroupIndex[year].monthIndex[month].node, this.dateGroupIndex[year].monthIndex[minValue].node);
					}
					else
					{
						this.dateGroupIndex[year].node.appendChild(this.dateGroupIndex[year].monthIndex[month].node);
					}
				}
				else
				{
					this.dateGroupIndex[year].monthIndex[minValue].node.parentNode.insertBefore(this.dateGroupIndex[year].monthIndex[month].node, this.dateGroupIndex[year].monthIndex[minValue].node);
				}
			}
		}

		if (!this.dateGroupIndex[year].monthIndex[month].dayIndex[day])
		{
			minDelta = Infinity;
			minValue = day;
			monthIndex = this.dateGroupIndex[year].monthIndex;
			for (d in monthIndex[month].dayIndex)
			{
				if (monthIndex[month].dayIndex.hasOwnProperty(d))
				{
					if (Math.abs(day - d) < minDelta)
					{
						minDelta = Math.abs(day - d);
						minValue = d;
					}
					if (minDelta == 1)
					{
						break;
					}
				}
			}

			monthIndex[month].dayIndex[day] = {
				node: BX.create('DIV', {
					props: {className: "calendar-timeline-stream-day"},
					attrs: {'data-bx-calendar-list-day': day}
				})
			};

			// it's first wrap
			if (minValue == day)
			{
				monthIndex[month].node.appendChild(monthIndex[month].dayIndex[day].node);
			}
			else
			{
				if (day - minValue > 0)
				{
					if (monthIndex[month].dayIndex[minValue].node.nextSibling)
					{
						BX.insertAfter(monthIndex[month].dayIndex[day].node, monthIndex[month].dayIndex[minValue].node);
					}
					else
					{
						monthIndex[month].node.appendChild(monthIndex[month].dayIndex[day].node);
					}
				}
				else
				{
					monthIndex[month].node.insertBefore(monthIndex[month].dayIndex[day].node, monthIndex[month].dayIndex[minValue].node);
				}
			}
		}

		if (monthIndex[month].dayIndex[day].node)
		{
			monthIndex[month].dayIndex[day].node.appendChild(wrap);
		}
		else
		{
		}

		setTimeout(function(){
			wrap.style.opacity = '1';
			//wrap.style.height = 'auto';
		}, 0);
	};

	ListView.prototype.hide = function()
	{
		View.prototype.hide.apply(this, arguments);
	};

	ListView.prototype.getAdjustedDate = function(date, viewRange)
	{
		if (!date)
		{
			date = new Date();
		}

		var
			currentViewRangeDate = this.calendar.getViewRangeDate(),
			viewRangeDate = false;

		if (date && date.getTime)
		{
			viewRangeDate = new Date(date.getTime());
		}

		return viewRangeDate;
	};

	ListView.prototype.adjustViewRangeToDate = function(date, animate)
	{
		if (this.viewCont.style.display == 'none')
		{
			var viewRangeDate = false;
			if (date && date.getTime)
			{
				viewRangeDate = new Date(date.getTime());
				viewRangeDate.setHours(0, 0, 0, 0);
				this.currentDate = viewRangeDate;
				this.calendar.setViewRangeDate(viewRangeDate);
			}

			if (animate !== false)
			{
				this.fadeAnimation(this.getContainer(), 100, BX.delegate(function(){
					this.show();
					this.getContainer().style.opacity = 0;
					this.showAnimation(this.getContainer(), 300)
				}, this));
			}
			else
			{
				this.show();
			}
		}
		else
		{
			var dayCode = this.calendar.util.getDayCode(date);

			var ind = BX.util.array_search(dayCode, this.groupsDayCodes);
			// TODO: load events for date if it's not loaded yet
			if (ind >= 0 && this.groupsDayCodes[ind])
			{
				this.focusOnDate(this.groupsDayCodes[ind], true);
			}
		}


		return viewRangeDate;
	};

	ListView.prototype.getViewRange = function()
	{
		var
			viewRangeDate = this.calendar.getViewRangeDate(),
			endDate = new Date(viewRangeDate.getTime());
		return {start: viewRangeDate, end: endDate};
	};

	ListView.prototype.increaseViewRangeDate = function()
	{
		var ind = BX.util.array_search(this.focusedDayCode, this.groupsDayCodes);
		if (ind >= 0 && this.groupsDayCodes[ind + 1])
		{
			this.focusOnDate(this.groupsDayCodes[ind + 1], true);
		}
	};

	ListView.prototype.decreaseViewRangeDate = function()
	{
		var ind = BX.util.array_search(this.focusedDayCode, this.groupsDayCodes);
		if (ind > 0 && this.groupsDayCodes[ind - 1])
		{
			this.focusOnDate(this.groupsDayCodes[ind - 1], true);
		}
	};

	ListView.prototype.createEntryGroupForDate = function(date, animation)
	{
		var
			dayCode = this.calendar.util.getDayCode(date),
			time = Math.round(date.getTime() / 1000) * 1000,
			group = {
				dayCode: dayCode,
				wrap: BX.create('DIV', {props: {className: 'calendar-timeline-stream-section-wrap'}})
			};

		group.titleNode = group.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-section calendar-timeline-section-date-label'}, html: '<div data-bx-calendar-date="' + time + '" class="calendar-timeline-stream-section-content"><div class="' + (dayCode == this.todayCode ? 'calendar-timeline-stream-today-label' : 'calendar-timeline-stream-label') + '">' + this.calendar.util.formatDateUsable(date) + '</div></div>'}));
		group.groupNode = group.wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-section'}}));
		group.content = group.groupNode.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-section-content'}}));
		group.emptyWarning = group.content.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-empty-section'}, text: BX.message('EC_NO_EVENTS')}));

		this.groups.push(group);
		this.groupsIndex[dayCode] = this.groups.length - 1;
		this.groupsDayCodes.push(dayCode);

		this.appendDateGroup(this.groups[this.groups.length - 1].wrap, date, animation);
	};

	ListView.prototype.focusOnDate = function(date, animate)
	{
		var dayCode = false;
		if(BX.type.isDate(date))
			dayCode = this.calendar.util.getDayCode(date);
		else if(BX.type.isString(date))
			dayCode = date;
		else
			dayCode = this.todayCode;

		this.focusedDayCode = dayCode;
		if (this.groupsIndex && this.groups[this.groupsIndex[dayCode]])
		{
			this.bottomLoaderBlock.style.height = (this.SCROLL_DELTA_HEIGHT + Math.max(0, this.util.getViewHeight() + this.groups[this.groupsIndex[dayCode]].wrap.offsetTop - this.bottomLoaderBlock.offsetTop)) + 'px';

			if (animate && this.streamScrollWrap)
			{
				new BX.easing({
					duration: 300,
					start: {scrollTop: this.streamScrollWrap.scrollTop},
					finish: {scrollTop: this.groups[this.groupsIndex[dayCode]].wrap.offsetTop},
					transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
					step: BX.delegate(function(state)
					{
						this.streamScrollWrap.scrollTop = state.scrollTop;
					},this),
					complete: BX.delegate(function()
					{
						this.streamScrollWrap.scrollTop = this.groups[this.groupsIndex[dayCode]].wrap.offsetTop;
					},this)
				}).animate();
			}
			else
			{
				setTimeout(BX.delegate(function(){
					if (this.streamScrollWrap && this.groupsIndex[dayCode] !== undefined && this.groups[this.groupsIndex[dayCode]])
					{
						this.streamScrollWrap.scrollTop = this.groups[this.groupsIndex[dayCode]].wrap.offsetTop;
					}
				}, this), 200);
			}
		}
	};

	ListView.prototype.scrollHandle = function()
	{
		if (!this.filterMode)
		{
			if (this.streamScrollWrap.scrollHeight - this.util.getViewHeight() - this.streamScrollWrap.scrollTop < this.SCROLL_DELTA_HEIGHT
				&& !this.nothingToLoadNext
			)
			{
				this.loadMoreEntries({mode: 'next'});
			}
			else if (this.streamScrollWrap.scrollTop < this.SCROLL_DELTA_HEIGHT
				&& !this.nothingToLoadPrevious)
			{
				this.loadMoreEntries({mode: 'previous'});
			}
		}
	};

	ListView.prototype.loadMoreEntries = function(params)
	{
		if (this.currentLoadMode || this.filterMode)
			return;

		this.currentLoadMode = params.mode;
		if (params.mode == 'next')
		{
			// Show loader
			this.loader = this.bottomLoaderBlock.appendChild(BX.adjust(this.calendar.util.getLoader(), {style: {height: '180px'}}));

			this.entryController.getList({
				loadNext: true,
				loadLimit: this.loadLimit,
				startDate: new Date(this.displayedRange.end.getFullYear(), this.displayedRange.end.getMonth(), this.displayedRange.end.getDate() + 1),
				finishCallback: BX.proxy(this.loadMoreCallback, this)
			});
		}
		else
		{
			this.loader = this.topLoaderBlock.appendChild(BX.adjust(this.calendar.util.getLoader(), {style: {height: '180px'}}));
			this.entryController.getList({
				loadPrevious: true,
				loadLimit: this.loadLimitPrevious,
				finishDate: new Date(this.displayedRange.start.getFullYear(), this.displayedRange.start.getMonth(), this.displayedRange.start.getDate() - 1),
				finishCallback: BX.proxy(this.loadMoreCallback, this)
			});
		}
	};

	ListView.prototype.loadMoreCallback = function(params)
	{
		// Hide loader
		BX.remove(this.loader);
		this.displayedRange = this.calendar.entryController.getLoadedEntiesLimits();
		var entries = this.entryController.getList({
			startDate: this.displayedRange.start,
			finishDate: this.displayedRange.end,
			finishCallback: BX.proxy(this.loadMoreCallback, this)
		});

		if (this.currentLoadMode == 'next' && params.finish)
			this.nothingToLoadNext = true;
		if (this.currentLoadMode == 'previous' && params.finish)
			this.nothingToLoadPrevious = true;

		if (!this.entries)
			this.entries = [];

		if (entries && entries.length)
		{
			this.entries = this.entries.concat(entries);
			this.attachEntries(entries, this.currentLoadMode == 'next' ? 'next' : false);
		}

		this.currentLoadMode = false;
	};

	ListView.prototype.getUniqueId = function(entryPart)
	{
		return entryPart.entry.uid + '|' + entryPart.dayCode;
	};

	ListView.prototype.handleClick = function(params)
	{
		if (this.isActive())
		{
			if (!params)
				params = {};

			var dayCode, uid;
			if (params.specialTarget && (uid = params.specialTarget.getAttribute('data-bx-calendar-entry')))
			{
				this.handleEntryClick(
					{
						uid: uid,
						specialTarget: params.specialTarget,
						target: params.target,
						e: params.e
					});
			}
			else if (params.specialTarget && (dayCode = params.specialTarget.getAttribute('data-bx-calendar-show-all-events')))
			{
			}
			else if (!this.calendar.util.readOnlyMode()
				&& this.entryController.canDo(true, 'add_event')
				&& (dayCode = params.specialTarget && params.specialTarget.getAttribute('data-bx-calendar-week-day')))
			{
				this.deselectEntry();
				this.showSimplePopupForNewEntry({
					entry: this.buildTopNewEntryWrap({
						dayFrom: this.days[this.dayIndex[dayCode]],
						holder: this.topEntryHolder
					})
				});
			}
		}
	};

	ListView.prototype.showEmptyBlock = function(params)
	{
		if (!this.noEntriesWrap)
		{
			this.noEntriesWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
				props: {className: 'calendar-search-main'},
				style: {height: this.util.getViewHeight() + 'px'}
			}));
			var innerWrap = this.noEntriesWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-search-empty'},
			html: '<div class="calendar-search-empty-name">' + BX.message('EC_NO_EVENTS') + '</div>'}));

			if (!this.calendar.util.readOnlyMode())
			{
				this.addButton = innerWrap.appendChild(
					BX.create('SPAN', {
						props: {className: 'calendar-search-empty-link'},
						text: BX.message('EC_CREATE_EVENT'),
						events: {
							click: BX.proxy(function(){
								if (!this.calendar.editSlider)
								{
									this.calendar.editSlider = new window.BXEventCalendar.EditEntrySlider(this.calendar);
								}
								this.calendar.editSlider.show({});
							}, this)
						}
					}));
			}
		}

		this.streamContentWrap.style.display = 'none';
		this.noEntriesWrap.style.display = '';
	};

	ListView.prototype.applyFilterMode = function()
	{
		// Hide original title
		this.filterMode = true;
		this.calendar.viewTitleContainer.style.display = 'none';
		// Hide navigation container
		this.calendar.navigationWrap.style.display = 'none';

		// Show search head
		if (this.searchHead)
		{
			BX.remove(this.searchHead);
		}
		this.searchHead = this.calendar.topBlock.appendChild(BX.create('DIV', {props: {className: 'calendar-search-head'}, html: '<div class="calendar-search-name">' + BX.message('EC_SEARCH_RESULT') + '</div>'}));

		if (this.resettFilterWrap)
		{
			BX.remove(this.resettFilterWrap);
		}
		this.resettFilterWrap = this.searchHead.appendChild(BX.create('DIV', {
			props: {className: 'calendar-search-cancel'},
			html: BX.message('EC_SEARCH_RESET_RESULT'),
			events: {click: BX.proxy(this.resetFilterMode, this)}
		}));


		if (this.streamScrollWrap)
		{
			if (!this.filterLoaderWrap)
			{
				this.filterLoaderWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-search-main'}, style: {height: this.util.getViewHeight() + 'px'}
				}));

				var innerWrap = this.filterLoaderWrap.appendChild(BX.create('DIV', {
					props: {className: 'calendar-search-empty'},
					html: '<div class="calendar-search-empty-name">' + BX.message('EC_NO_EVENTS') + '</div>'
				}));
			}

			this.streamContentWrap.style.display = 'none';
			this.filterLoaderWrap.style.display = '';
		}
	};

	ListView.prototype.resetFilterMode = function(params)
	{
		this.filterMode = false;
		this.resultEntries = [];
		this.calendar.viewTitleContainer.style.display = '';
		this.calendar.navigationWrap.style.display = '';

		this.setTitle();

		if (this.searchHead)
		{
			BX.remove(this.searchHead);
		}
		if (this.resettFilterWrap)
		{
			BX.remove(this.resettFilterWrap);
		}

		this.streamContentWrap.style.display = '';
		if (this.filterLoaderWrap)
		{
			this.filterLoaderWrap.style.display = 'none';
		}

		if (!params || params.resetSearchFilter !== false)
			this.calendar.search.resetFilter();

		if (this.isActive())
			this.displayEntries();
	};

	ListView.prototype.handleEntryClick = function(params)
	{
		if (this.filterMode)
		{
			if (this.resultEntriesIndex[params.uid] !== undefined)
			{
				params.entry = this.resultEntries[this.resultEntriesIndex[params.uid]];
			}
		}
		View.prototype.handleEntryClick.apply(this, arguments);
	};


	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.CalendarView = View;
		window.BXEventCalendar.CalendarDayView = DayView;
		window.BXEventCalendar.CalendarWeekView = WeekView;
		window.BXEventCalendar.CalendarMonthView = MonthView;
		window.BXEventCalendar.CalendarYearView = YearView;
		window.BXEventCalendar.CalendarListView = ListView;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.CalendarView = View;
			window.BXEventCalendar.CalendarDayView = DayView;
			window.BXEventCalendar.CalendarWeekView = WeekView;
			window.BXEventCalendar.CalendarMonthView = MonthView;
			window.BXEventCalendar.CalendarYearView = YearView;
			window.BXEventCalendar.CalendarListView = ListView;
		});
	}
})(window);