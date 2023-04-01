;(function(window) {
	var View = window.BXEventCalendarView;

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
		this.offHoursCollapsedHeight = 15;
		this.title = BX.message('EC_VIEW_DAY');
		this.entryWidthOffset = 2;
		this.lastEntryWidthOffset = 8;
		this.hotkey = 'D';

		this.contClassName = 'calendar-day-view';
		this.gridWrapClass = 'calendar-grid-wrap';
		if (BX.isAmPmMode())
		{
			this.gridWrapClass += ' is-am-pm-mode';
		}
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

		this.fullDayEventsCont = this.viewCont.appendChild(BX.create('DIV', {props: {className: this.fullDayContClass}}));

		this.gridWrap = this.viewCont.appendChild(BX.create('DIV', {
			props: {className: this.gridWrapClass},
			style: {height: this.util.getViewHeight() + 'px'}
		}));
		this.checkTimelineScroll();
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

		this.loadEntries().then(entries => {
			this.entries = entries;
			this.displayEntries();
		});
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
		this.highlightAll();
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
				this.loadEntries().then(entries => {
					this.entries = entries;
					this.displayEntries();
				});
			}, this), 400);
		}, this), 0);
	};

	DayView.prototype.decreaseViewRangeDate = function()
	{
		this.changeViewRangeDate(-this.dayCount);
		this.highlightAll();
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
				this.loadEntries().then(entries => {
					this.entries = entries;
					this.displayEntries();
				});
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
			if (diff === this.dayCount)
			{
				this.increaseViewRangeDate();
			}
			else if (diff === -this.dayCount)
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

		var displayedRange = BX.clone(this.getViewRange(), true);

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
			if (i === 0)
			{
				displayedRange.start = new Date(date.getTime());
				displayedRange.start.setHours(0, 0, 0, 0);
			}
			else if (i === this.dayCount - 1)
			{
				displayedRange.end = new Date(date.getTime());
				displayedRange.end.setHours(0, 0, 0, 0);
			}

			dayCode = this.util.getDayCode(date);

			this.fullDayEventsHolderCont.appendChild(BX.create('DIV', {
				attrs: {'data-bx-calendar-week-day': dayCode},
				props: {className: this.gridCellClass}
			}));

			this.buildDayCell({date: date, month: 'previous', grid: grid});

			if (this.dayCount > 1)
			{
				date.setDate(date.getDate() + 1);
			}

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
			if (!this.gridWrap.scrollTop && !this.isCollapsedOffHours)
			{
				var workTime = this.util.getWorkTime();
				this.gridWrap.scrollTop = workTime.start * this.gridLineHeight - 5;
			}
		}, this), 0);

		this.showOffHours();
		this.calendar.setDisplayedViewRange(displayedRange);

		// Show "now" red time line
		this.showNowTime();
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

		if (params.month === 'previous')
		{
			className += ' calendar-grid-previous-month-day';
		}
		else if (params.month === 'next')
		{
			className += ' calendar-grid-next-month-day';
		}
		if (this.util.isHoliday(date))
		{
			className += ' calendar-grid-holiday';
		}

		let todayClass = '';
		if (this.util.isToday(date))
		{
			titleClassName += ' calendar-grid-today';
			todayClass = 'calendar-grid-today';
		}

		if (this.titleCont && this.name === 'week')
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
					className: this.gridCellClass + className + ' a1' + ' ' + todayClass
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

		View.prototype.setTitle.apply(this, [BX.date.format(BX.Calendar.Util.getLongDateFormat(), time)]);
	};

	DayView.prototype.setDraggedEntry = function(entry)
	{
		this.draggedEntry = this.getRealEntry(entry);
		if (!this.draggedEntry)
		{
			return null;
		}
		for (const key in this.draggedEntry.parts)
		{
			this.draggedEntry.parts[key].params.wrapNode.style.transition = 'none';
			this.draggedEntry.parts[key].params.wrapNode.style.opacity = '0.3';
		}
	};

	DayView.prototype.setResizedEntry = function(entry)
	{
		if (!entry)
		{
			this.resizedEntry = null;
		}
		else
		{
			this.resizedEntry = this.entries.find(e => e.uid === entry.uid);
		}
	};

	DayView.prototype.loadEntries = function()
	{
		return new Promise((resolve) => {
			const viewRange = this.getViewRange();
			this.entryController.getList({
				showLoader: (this.entries && !this.entries.length),
				startDate: new Date(viewRange.start.getFullYear(), viewRange.start.getMonth(), 1),
				finishDate: new Date(viewRange.end.getFullYear(), viewRange.end.getMonth() + 1, 1),
				viewRange: viewRange,
			}).then((entries) => {
				resolve(entries);
			});
		});
	};

	DayView.prototype.displayEntries = function()
	{
		if (this.draggedEntry || this.resizedEntry)
		{
			return;
		}

		this.entries = this.getUndeletedEntries();

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
			if (day.collapsedWrap && day.collapsedWrap.top)
			{
				day.collapsedWrap.top.destroy();
			}
			if (day.collapsedWrap && day.collapsedWrap.bottom)
			{
				day.collapsedWrap.bottom.destroy();
			}
			day.collapsedWrap = {top: null, bottom: null};

			day.entries = {
				topList: [],
				started: [],
				timeline: [],
				hidden: []
			};
		});

		let maxTopEntryCount = 0;
		if (this.entries && this.entries.length)
		{
			// Prepare for arrangement
			for (let i = 0; i < this.entries.length; i++)
			{
				const entry = this.entries[i];
				this.entriesIndex[entry.uid] = i;
				entry.cleanParts();
				let entryStarted = false;

				let part;
				for (let dayPos = this.dayIndex[entry.startDayCode]; dayPos < this.days.length; dayPos++)
				{
					const day = this.days[dayPos];
					if (!entry.isLongWithTime()
						&& day.dayCode === entry.startDayCode
						&& day.dayCode === entry.endDayCode && !entry.fullDay)
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
						if (day.dayCode === entry.startDayCode)
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

							if (day.dayCode === entry.endDayCode ||
								day.dayOffset === this.dayCount - 1 /* for week view */||
								this.dayCount === 1 /*for day view */)
							{
								// here we know where part of event starts and ends
								this.partsStorage.push({part: part, entry: entry});

								// Event finished
								if (day.dayCode === entry.endDayCode)
								{
									break;
								}
							}
						}
					}
				}
			}
		}

		if (this.entries && this.entries.length)
		{
			this.displayTopEntries();
			this.displayTimelineEntries();

			this.SLOTS_COUNT = 10;

			this.arrangeTopEntries();
			this.arrangeTimelineEntries();
		}

		if (this.draggedEntry)
		{
			this.draggedEntry = this.entries.find(e => e.uid === this.draggedEntry.uid);
			for (const key in this.draggedEntry.parts)
			{
				this.draggedEntry.parts[key].params.wrapNode.style.transition = 'none';
				this.draggedEntry.parts[key].params.wrapNode.style.opacity = '0.3';
			}
		}

		this.setFullDayHolderSize(Math.min(Math.max(maxTopEntryCount, 1), this.SLOTS_COUNT));

		// Final arrangement on the grid
		for (const day of this.days)
		{
			// Here we check all entries in the day and if any of it
			// was hidden, we going to show 'show all' link
			if (day.entries.topList.length > 0)
			{
				let showHiddenLink = false;
				for(let i = 0; i < day.entries.topList.length; i++)
				{
					if (day.entries.topList[i].part.params.wrapNode.style.display === 'none')
					{
						showHiddenLink = true;
						break;
					}
				}

				if (showHiddenLink)
				{
					day.hiddenStorage = this.topEntryHolder.appendChild(BX.create('DIV', {
						props: {
							className: 'calendar-event-line-wrap calendar-event-more-btn-container'
						},
						attrs: {'data-bx-calendar-show-all-events': day.dayCode},
						style: {
							top: (parseInt(this.fullDayEventsCont.style.height) - 20) + 'px',
							left: this.dayCount === 1
								? '0' /*for day view */
								: 'calc((100% / ' + this.dayCount + ') * (' + (day.dayOffset + 1) + ' - 1) + 2px)',
							width: 'calc(100% / ' + this.dayCount + ' - 3px)'
						}
					}));

					day.hiddenStorageText = day.hiddenStorage.appendChild(BX.create('span', {props: {className: 'calendar-event-more-btn'}}));
					day.hiddenStorage.style.display = 'block';
					day.hiddenStorageText.innerHTML = BX.message('EC_SHOW_ALL') + ' ' + day.entries.topList.length;
				}
				else if (day.hiddenStorage)
				{
					day.hiddenStorage.style.display = 'none';
				}
			}
		}

		BX.addClass(this.grid, 'calendar-events-holder-show');
		BX.addClass(this.fullDayEventsCont, 'calendar-events-holder-show');

		this.checkTimelineScroll();
	};

	DayView.prototype.arrangeTopEntries = function()
	{
		var
			element, prevElement,
			i, j, dayPos, day, entry, entryPart, entryDisplayed;

		for (dayPos = 0; dayPos < this.days.length; dayPos++)
		{
			day = this.days[dayPos];

			if (day.entries.started.length > 0)
			{
				day.entries.started.sort(this.calendar.entryController.sort);

				for(i = 0; i < day.entries.started.length; i++)
				{
					element = day.entries.started[i];
					if (element)
					{
						entry = element.entry;
						entryPart = element.part;

						if (!entry.checkPartIsRegistered(entryPart))
							continue;

						entryDisplayed = false;

						for(j = 0; j < this.SLOTS_COUNT; j++)
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

					if (day.hiddenStorage && day.entries.hidden.length > 0)
					{
						day.hiddenStorageText.innerHTML = BX.message('EC_SHOW_ALL') + ' (' + day.entries.topList.length + ')';
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
			return layerTimeIndex && layerTimeIndex.entries && layerTimeIndex.entries.length === layerTimeIndex.start.length;
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
				if (a.part.fromTimeValue === b.part.fromTimeValue)
				{
					return (b.part.toTimeValue - b.part.fromTimeValue) - (a.part.toTimeValue - a.part.fromTimeValue);
				}

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

				timeFrom = getTimeIndex(entry.from, 1);
				timeTo = getTimeIndex(entry.to, 1);
				if (timeFrom === timeTo)
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

					timeFrom = getTimeIndex(entry.from, 1);
					timeTo = getTimeIndex(entry.to, 1);
					if (timeFrom === timeTo)
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

									//backEntry.part.params.nameNode.style.maxWidth = 'calc(' + ((1 - entryPart.offsetFractionWidth) * 100) + '% - 4px)';

									if (backEntry.part.params.nameNode.offsetWidth < MIN_TIME_WIDTH)
									{
										backEntry.part.params.nameNode.style.textOverflow = 'clip';
										backEntry.part.params.nameNode.style.maxWidth = 'calc(' + ((1 - entryPart.offsetFractionWidth) * 100) + '% + 5px)';
									}
								}
								else if (backEntry.part.params.nameNode)
								{
									backEntry.part.params.nameNode.style.whiteSpace = 'nowrap';
									backEntry.part.params.nameNode.style.lineHeight = '11px';
									backEntry.part.params.timeNode.style.lineHeight = '11px';
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

						entryPart.params.wrapNode.style.zIndex = parseInt(entryPart.params.wrapNode.style.zIndex) - parallelPosition;
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
					if (entryPart.params && entryPart.params.wrapNode)
					{
						this.updateCompactness(entryPart.params.wrapNode);
					}
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
		if (entry.getCurrentStatus() === 'N')
		{
			entryClassName += ' calendar-event-line-refused';
		}
		if (entry.isInvited())
		{
			entryClassName += ' calendar-event-animate-counter-highlight';
		}

		let arrowColor = entry.color;
		if (entry.isFullDay())
		{
			arrowColor = this.calendar.util.addOpacityToHex(entry.color, 0.3);
		}
		else if (entry.isLongWithTime())
		{
			arrowColor = this.calendar.util.addOpacityToHex(entry.color, 0.5);
		}

		if (this.util.getDayCode(entry.from) !== this.util.getDayCode(from.date))
		{
			entryClassName += ' calendar-event-line-start-yesterday';
			deltaPartWidth += 8;
			startArrow = this.getArrow('left', arrowColor, entry.isFullDay());
		}

		if (this.util.getDayCode(entry.to) !== this.util.getDayCode(params.part.to.date))
		{
			entryClassName += ' calendar-event-line-finish-tomorrow';
			endArrow = this.getArrow('right', arrowColor, entry.isFullDay());
			deltaPartWidth += 12;
		}

		if (startArrow && !endArrow)
		{
			deltaPartWidth += 4;
		}

		if (deltaPartWidth === 0)
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
			if (params.part.partIndex === 0)
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

			if (!timeNode && daysCount === 1
				&& this.util.getDayCode(entry.from) === params.part.from.dayCode)
			{
				timeNode = innerNode.appendChild(BX.create('SPAN', {
					props: {className: 'calendar-event-line-time'},
					text: this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes())
				}));
			}

			// Last part
			if (params.part.partIndex === entry.parts.length - 1)
			{
				if (daysCount > 1 && entry.parts.length > 1)
				{
					innerNode.style.width = 'calc(' + (daysCount - 1) + '00% / ' + daysCount + ' - ' + this.lastEntryWidthOffset + 'px)';
				}

				if (daysCount > 1)
				{
					endTimeNode = innerNode.appendChild(BX.create('SPAN', {
						props: {className: (entry.parts.length > 1 && daysCount === 1) ? 'calendar-event-line-time' : 'calendar-event-line-expired-time'},
						text: this.calendar.util.formatTime(entry.to.getHours(), entry.to.getMinutes())
					}));
				}
			}

			if (!endTimeNode && daysCount === 1 && this.util.getDayCode(entry.to) === params.part.to.dayCode)
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
			innerContainer.style.backgroundColor = this.calendar.util.addOpacityToHex(entry.color, 0.3);
			innerContainer.style.borderColor = this.calendar.util.addOpacityToHex(entry.color, 0.3);
		}
		else
		{
			if (entry.isLongWithTime())
			{
				innerContainer.style.borderColor = this.calendar.util.addOpacityToHex(entry.color, 0.5);
			}
			dotNode.style.backgroundColor = entry.color;
		}

		if (entry.isInvited() && this.isFirstVisibleRecursiveEntry(entry))
		{
			innerNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-invite-counter'}, text: '1'}));
		}

		(params.holder || this.topEntryHolder).appendChild(partWrap);

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
			if (a.part.fromTimeValue === b.part.fromTimeValue)
			{
				return (b.part.toTimeValue - b.part.fromTimeValue)
					- (a.part.toTimeValue - a.part.fromTimeValue);
			}
			return a.part.fromTimeValue - b.part.fromTimeValue;
		});

		for (var i = 0; i < this.timelinePartsStorage.length; i++)
		{
			this.displayTimelineEntry(this.timelinePartsStorage[i]);
		}
	};

	DayView.prototype.displayTimelineEntry = function(params)
	{
		var
			res = false,
			top,
			wrapNode, innerNode, nameNode, timeNode, timeLabel,
			workTime = this.util.getWorkTime(),
			entry = params.entry,
			from = params.part.from,
			fromTimeValue = params.part.fromTimeValue,
			toTimeValue = params.part.toTimeValue,
			entryClassName = 'calendar-event-block-wrap';

		if (entry.hasEmailAttendees()
			|| entry.ownerIsEmailUser()
			|| entry.getCurrentStatus() === 'N'
			|| entry.isSharingEvent()
		)
		{
			entryClassName += ' calendar-event-wrap-icon';
		}

		if (entry.isExpired())
		{
			entryClassName += ' calendar-event-block-wrap-past';
		}

		if (entry.isSharingEvent())
		{
			entryClassName += ' calendar-event-block-wrap-sharing';
		}

		if (!this.isCollapsedOffHours
			|| (toTimeValue > workTime.start
				&& fromTimeValue < workTime.end))
		{
			if (this.isCollapsedOffHours)
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
				props: {
					className: entryClassName
				},
				style: {
					top: top,
					height: ((toTimeValue - fromTimeValue) * this.gridLineHeight - 1) + 'px',
					left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * ' + from.dayOffset + ' + 2px)' : '2px',
					width: 'calc(100% / ' + this.dayCount + ' - ' + this.lastEntryWidthOffset + 'px)'
				}
			});

			innerNode = wrapNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-inner'}}));
			const titleNode = innerNode.appendChild(
				BX.create('SPAN',{
					props: {
						className: 'calendar-event-block-title',
					},
				})
			);

			if (entry.isInvited())
			{
				innerNode.className += ' calendar-event-animate-counter-highlight';
				if (this.isFirstVisibleRecursiveEntry(entry))
				{
					titleNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-invite-counter'}, text: '1'}));
				}
				else
				{
					titleNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-invite-counter-dot'}}));
				}
			}
			else if (entry.getCurrentStatus() === 'N')
			{
				titleNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-block-icon-refused'}}));
			}
			else if (entry.isSharingEvent())
			{
				titleNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-block-icon-sharing'}}));
			}
			else if (entry.hasEmailAttendees() || entry.ownerIsEmailUser())
			{
				titleNode.appendChild(BX.create('SPAN', {props: {className: 'calendar-event-block-icon-mail'}}));
			}

			nameNode = titleNode.appendChild(
				BX.create('SPAN',{
					props: {
						className: 'calendar-event-block-text'
					},
					text:  params.entry.name,
				})
			);

			if (!this.calendar.util.isDarkColor(entry.color))
			{
				BX.Dom.addClass(innerNode, 'calendar-event-text-dark');
			}

			timeNode = innerNode.appendChild(BX.create('SPAN', {
				props: {className: 'calendar-event-block-time'},
				html: this.calendar.util.formatTime(entry.from) + ' &ndash; ' + this.calendar.util.formatTime(entry.to)
			}));

			innerNode.style.backgroundColor = entry.color;

			let resizerNodeTop, resizerNodeBottom;
			if (this.calendar.util.type !== 'location' && this.calendar.entryController.canDo(entry, 'edit'))
			{
				resizerNodeTop = wrapNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-resizer calendar-event-resizer-top'}}));
				resizerNodeBottom = wrapNode.appendChild(BX.create('DIV', {props: {className: 'calendar-event-resizer calendar-event-resizer-bottom'}}));
			}

			this.timelineEntryHolder.appendChild(wrapNode);

			res = {
				wrapNode: wrapNode,
				nameNode: nameNode,
				innerNode: innerNode,
				timeNode: timeNode,
				blockBackgroundNode: innerNode,
				resizerNodeTop: resizerNodeTop,
				resizerNodeBottom: resizerNodeBottom,
			};

			params.part.offsetFractionRate = 1; //!!!!
			params.part.offsetFractionLeft = 0;
			params.part.offsetFractionWidth = 1;
			params.part.offsetFractionLeftTotal = 0;
			params.entry.registerPartNode(params.part, res);

			this.calendar.dragDrop.registerEntry(wrapNode, params);
		}
		else // event displayed on hidden timeline
		{
			this.addHiddenEntry(
				{
					position: fromTimeValue < workTime.end ? 'top' : 'bottom',
					entry: entry
				}
			);
		}

		return res;
	};

	DayView.prototype.addHiddenEntry = function(params)
	{
		this.getCollapsedWrap({
			position: params.position,
			dayCode: this.util.getDayCode(params.entry.from)
		}).addEntry(params.entry);
	};

	DayView.prototype.getCollapsedWrap = function(params)
	{
		if (this.dayIndex[params.dayCode] !== undefined && this.days[this.dayIndex[params.dayCode]])
		{
			var day = this.days[this.dayIndex[params.dayCode]];
			if (!day.collapsedWrap[params.position]
				|| !day.collapsedWrap[params.position].inited())
			{
				day.collapsedWrap[params.position] = new CollapsedTimeWrap({
					position: params.position,
					wrap: this.timelineEntryHolder,
					workTime: this.util.getWorkTime(),
					dayOffset: day.dayOffset,
					dayCount: this.dayCount,
					lastEntryWidthOffset: this.lastEntryWidthOffset,
					gridLineHeight: this.gridLineHeight,
					labelMessage: this.calendar.collapsedLabelMessage,
					clickHandler: (event) => {
						if (this.isCollapsedOffHours)
						{
							const entryWrap = event.target.closest('.calendar-event-block-wrap');
							const top = parseInt(entryWrap.style.top);
							if (top < 0)
							{
								this.switchOffHours(true, 'top');
							}
							else
							{
								this.switchOffHours(true, 'bottom');
							}
						}
					},
					mouseoverHandler: function(){
						BX.addClass(this.topOffHours, "calendar-grid-off-hours-hover");
						BX.addClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
					}.bind(this),
					mouseoutHandler: function(){
						BX.removeClass(this.topOffHours, "calendar-grid-off-hours-hover");
						BX.removeClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
					}.bind(this)
				});
			}

			return day.collapsedWrap[params.position];
		}
		return null;
	};

	DayView.prototype.displayTimelineCollapsedEntry = function(params)
	{
	};

	DayView.prototype.showNowTime = function()
	{
		this.nowTimeLabel = BX.create('DIV', { props: { className: this.gridNowTimeLabelClass } });

		this.nowTimeLine = BX.create('DIV', { props: { className: this.gridNowTimeLineClass } });
		this.nowTimeLine.append(this.nowTimeLabel);

		this.nowTimeCont = BX.create('DIV', { props: { className: this.gridNowTimeClass } });
		this.nowTimeCont.append(this.nowTimeLine);

		this.gridRow.append(this.nowTimeCont);

		if (this.nowTimeInterval)
		{
			clearInterval(this.nowTimeInterval);
		}

		this.updateNowTime();
		this.nowTimeInterval = setInterval(BX.proxy(this.updateNowTime, this), 15000);
	};

	DayView.prototype.hideNowTime = function()
	{
		if (!this.nowTimeCont)
		{
			return;
		}
		BX.cleanNode(this.nowTimeCont, 1);
		delete this.nowTimeCont;
		if (this.nowTimeInterval)
		{
			clearInterval(this.nowTimeInterval);
		}
	};

	DayView.prototype.resetNowTime = function()
	{
		this.hideNowTime();
		this.showNowTime();
	};

	DayView.prototype.hideOffHoursNowTime = function()
	{
		const workTime = this.util.getWorkTime();
		const timeValue = this.util.getTimeValue(new Date());
		if (timeValue < workTime.start || timeValue > workTime.end)
		{
			this.hideNowTime();
		}
	};

	DayView.prototype.getUserTime = function()
	{
		const userSettings = this.util.config.userSettings;
		const timeZone = userSettings.timezoneName;
		return new Date(new Date().toLocaleString("en-US", { timeZone }));
	};

	DayView.prototype.updateNowTime = function()
	{
		if (!this.nowTimeCont)
		{
			return;
		}

		const time = this.getUserTime();
		const timeValue = this.util.getTimeValue(time);

		const translucentLine = document.querySelector("." + this.gridTimeTranslucentClass);
		if (translucentLine)
		{
			BX.removeClass(translucentLine, this.gridTimeTranslucentClass);
		}

		const dayOffset = this.util.getWeekDayOffset(this.util.getWeekDayByInd(time.getDay()));
		const viewRange = this.getViewRange();
		if (time.getTime() > viewRange.start.getTime() && time.getTime() < viewRange.end.getTime())
		{
			if (this.dayCount > 1)
			{
				if (dayOffset === 0)
				{
					this.nowTimeLine.style.left = 0;
				}
				else
				{
					this.nowTimeLine.style.left = 'calc(' + dayOffset + ' * 100% / ' + this.dayCount + ' + 5px)';
				}
			}
		}
		else
		{
			this.hideNowTime();
			return;
		}

		let timeTextValue = this.calendar.util.formatTime(time.getHours(), time.getMinutes());
		if (BX.isAmPmMode())
		{
			timeTextValue = timeTextValue.replace(/(\sam|pm)/ig, "<small>$1<small>");
		}

		this.nowTimeLabel.innerHTML = timeTextValue;

		this.nowTimeCont.style.marginTop = '';
		this.nowTimeLine.classList.remove('calendar-hour-now-line-translucent');
		const workTime = this.util.getWorkTime();
		if (this.isCollapsedOffHours)
		{
			if (timeValue < workTime.start)
			{
				this.nowTimeCont.style.top = '-5px';
				this.nowTimeCont.style.marginTop = '23px';
				this.nowTimeLine.classList.add('calendar-hour-now-line-translucent');
			}
			else if (timeValue > workTime.end)
			{
				this.nowTimeCont.style.top = ((workTime.end - workTime.start) * this.gridLineHeight) + 4 + 'px';
				this.nowTimeCont.style.marginTop = '22px';
				this.nowTimeLine.classList.add('calendar-hour-now-line-translucent');
			}
			else
			{
				this.nowTimeCont.style.top = ((timeValue - workTime.start) * this.gridLineHeight  + this.timeLinesCont.offsetTop)  + 'px';
			}
		}
		else
		{
			this.nowTimeCont.style.top = (timeValue * this.gridLineHeight + this.timeLinesCont.offsetTop) + 'px';
		}

		if (this.isCollapsedOffHours && (dayOffset === 0 || this.dayCount === 1))
		{
			if (timeValue < workTime.start)
			{
				BX.addClass(this.topOffHoursLabel, this.gridTimeTranslucentClass);
			}
			if (timeValue > workTime.end)
			{
				BX.addClass(this.bottomOffHoursLabel, this.gridTimeTranslucentClass);
			}
		}
		else
		{
			BX.removeClass(this.topOffHoursLabel, this.gridTimeTranslucentClass);
			BX.removeClass(this.bottomOffHoursLabel, this.gridTimeTranslucentClass);
		}

		const nearestLineIndex = Math.round(timeValue);
		const nowTimeVisualOffsetPx = 10;
		if ((dayOffset === 0 || this.dayCount === 1)
			&& Math.abs((nearestLineIndex - timeValue) * this.gridLineHeight) < nowTimeVisualOffsetPx
			&& this.timeLinesIndex[nearestLineIndex]
		)
		{
			BX.addClass(this.timeLinesIndex[nearestLineIndex], this.gridTimeTranslucentClass);
		}
	};

	DayView.prototype.getPosByTime = function(time)
	{
		const startTime = this.getTimeByPos(0, 1);
		const startMinutes = startTime.h * 60 + startTime.m;
		const currentMinutes = time.h * 60 + time.m;

		let top = currentMinutes - startMinutes + 4;
		let t = this.getTimeByPos(top, 5);
		while (top >= 0 && t.h === time.h && t.m === time.m)
		{
			top--;
			t = this.getTimeByPos(top, 5);
		}
		return top + 1;
	};

	DayView.prototype.getTimeByPos = function(top, roundOff)
	{
		var
			workTime = this.util.getWorkTime(),
			timeFract = top / this.gridLineHeight,
			timeVal = this.util.getTimeByFraction(timeFract, roundOff || 10);

		if (this.isCollapsedOffHours)
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
			html: '<span>' + this.calendar.util.formatTime(0, 0, true) + "</span><span>" + this.calendar.util.formatTime(workTime.start, 0, true) + '</span>',
			events: {
				click: () => {
					this.switchOffHours(true, 'top');
				},
				mouseover: () => {
					BX.addClass(this.topOffHours, "calendar-grid-off-hours-hover");
				},
				mouseout: () => {
					BX.removeClass(this.topOffHours, "calendar-grid-off-hours-hover");
				}
			}
		}));
		this.timelineEntryHolder.addEventListener('mouseover', (e) => {
			if (e.target === this.timelineEntryHolder)
			{
				BX.addClass(this.topOffHours, "calendar-grid-off-hours-hover");
			}
		});
		this.timelineEntryHolder.addEventListener('mouseout', (e) => {
			if (e.target === this.timelineEntryHolder)
			{
				BX.removeClass(this.topOffHours, "calendar-grid-off-hours-hover");
			}
		});

		this.topOffHours.appendChild(BX.create('DIV', {
			props: {className: 'calendar-grid-off-hours-active'},
			events: {
				click: () => {
					this.switchOffHours(true, 'top');
				},
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
			html: '<span>' + this.calendar.util.formatTime(workTime.end, 0, true) + "</span><span>" + this.calendar.util.formatTime(24, 0, true) + '</span>',
			events: {
				click: () => {
					this.switchOffHours(true, 'bottom');
				},
				mouseover: () => {
					BX.addClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
				},
				mouseout: () => {
					BX.removeClass(this.bottomOffHours, "calendar-grid-off-hours-hover");
				}
			}
		}));

		this.bottomOffHours.appendChild(BX.create('DIV', {
			props: {className: 'calendar-grid-off-hours-active'},
			events: {
				click: () => {
					this.switchOffHours(true, 'bottom');
				},
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

		BX.bind(this.topOffHours, 'click', BX.proxy(function(){if (this.isCollapsedOffHours){this.switchOffHours(true, 'top')}}, this));
		BX.bind(this.bottomOffHours, 'click', BX.proxy(function(){if (this.isCollapsedOffHours){this.switchOffHours(true, 'bottom')}}, this));

		if (this.isCollapsedOffHours)
		{
			this.gridRow.style.height = (this.gridLineHeight * (workTime.end - workTime.start)) + 30 + 'px';
			this.isCollapsedOffHours = !this.isCollapsedOffHours;
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
				this.preventSwichOffHours = true;
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

	DayView.prototype.offHoursMouseup = function(event)
	{
		BX.unbind(document, "mousemove", BX.proxy(this.offHoursMousemove, this));
		BX.unbind(document, "mouseup", BX.proxy(this.offHoursMouseup, this));

		BX.addClass(this.topOffHours, this.offHoursAnimateClass);
		BX.addClass(this.bottomOffHours, this.offHoursAnimateClass);
		BX.removeClass(this.topOffHours, this.offHoursFastAnimateClass);
		BX.removeClass(this.bottomOffHours, this.offHoursFastAnimateClass);

		var workTime = this.util.setWorkTime(this.lastWorkTime);
		this.topOffHoursLabel.innerHTML = '<span>' + this.calendar.util.formatTime(0, 0, true) + "</span><span>" + this.calendar.util.formatTime(workTime.start, 0, true) + '</span>';
		this.bottomOffHoursLabel.innerHTML = '<span>' + this.calendar.util.formatTime(workTime.end, 0, true) + "</span><span>" + this.calendar.util.formatTime(24, 0, true) + '</span>';

		this.offtimeTuneMode = false;
		delete this.lastWorkTime;
		delete this.lastTopCount;

		this.isCollapsedOffHours = false;
		if (!this.preventSwichOffHours)
		{
			if (event.target.className === 'calendar-grid-off-hours-drag-up')
			{
				this.switchOffHours(true, 'bottom');
			}
			else
			{
				this.switchOffHours(true, 'top');
			}
		}
		this.preventSwichOffHours = false;
	};

	DayView.prototype.switchOffHours = function(animate, state)
	{
		if (this.denySwitch)
		{
			return;
		}
		this.denySwitch = true;
		this.removeOffHoursEntries();

		if (animate)
		{
			this.animateSwitchOffHours(state, this.isCollapsedOffHours);
		}
		else
		{
			this.setSwitchOffHours(this.isCollapsedOffHours);
		}

		this.isCollapsedOffHours = !this.isCollapsedOffHours;
	};

	DayView.prototype.setSwitchOffHours = function(isExpand)
	{
		BX.removeClass(this.bottomOffHours, this.offHoursAnimateClass);
		BX.removeClass(this.topOffHours, this.offHoursAnimateClass);
		BX.removeClass(this.timeLinesCont, this.offHoursAnimateClass);
		this.switchOffHoursProps(isExpand);

		if (isExpand)
		{
			this.showHourLines();
		}

		this.displayEntries();
		this.denySwitch = false;
		this.checkTimelineScroll();
	};

	DayView.prototype.animateSwitchOffHours = function(state, isExpandAnimation)
	{
		BX.addClass(this.bottomOffHours, this.offHoursAnimateClass);
		BX.addClass(this.topOffHours, this.offHoursAnimateClass);
		BX.addClass(this.timeLinesCont, this.offHoursAnimateClass);
		this.switchOffHoursProps(isExpandAnimation);
		this.hideOffHoursNowTime();

		let startCorrect, endCorrect;
		if (isExpandAnimation)
		{
			startCorrect = this.offHoursCollapsedHeight;
			endCorrect = 0;
		}
		else
		{
			startCorrect = 0;
			endCorrect = this.offHoursCollapsedHeight;
		}

		const correctingDiv = BX.create('DIV', {
			style: {
				position: 'absolute',
				width: 1 + 'px',
				height: startCorrect + 'px',
				top: (this.gridWrap.clientHeight + this.topOffHours.clientHeight) + 'px',
				transition: '400ms all ease'
			}
		});
		this.timelineEntryHolder.append(correctingDiv);

		correctingDiv.style.top = (this.gridWrap.clientHeight + this.topOffHours.clientHeight - endCorrect * 2) + 'px';
		correctingDiv.style.height = endCorrect + 'px';

		const savedTopOffHoursHeight = this.topOffHours.clientHeight;
		const savedScrollTop = this.gridWrap.scrollTop;
		const entryNodes = [...this.timelineEntryHolder.childNodes].filter(node => node !== correctingDiv);
		new BX.easing({
			duration: 400,
			start: {},
			finish: {},
			step: () => {
				let offset = this.topOffHours.clientHeight - correctingDiv.clientHeight;
				if (!isExpandAnimation)
				{
					offset -= savedTopOffHoursHeight;
				}

				this.gridWrap.scrollTop = savedScrollTop + offset;
				if (isExpandAnimation && state === 'top')
				{
					this.gridWrap.scrollTop = savedScrollTop;
				}

				this.timelineEntryHolder.style.transform = `translateY(${offset}px)`;

				if (this.nowTimeCont)
				{
					this.nowTimeCont.style.transform = `translateY(${offset}px)`;
				}

				this.cutEntryNodesByGrid(entryNodes, offset);
				this.checkTimelineScroll();
			},
			complete: () => {
				correctingDiv.remove();
				this.timelineEntryHolder.style.transform = 'none';
				this.resetNowTime();
				if (isExpandAnimation)
				{
					this.showHourLines();
				}
				this.displayEntries();
				this.denySwitch = false;
				this.checkTimelineScroll();
			}
		}).animate();
	};

	DayView.prototype.cutEntryNodesByGrid = function(entryNodes, offset)
	{
		for (const node of entryNodes)
		{
			const childTop = parseInt(node.offsetTop) + offset;
			const childBottom = childTop + node.offsetHeight;
			const gridBottom = parseInt(this.bottomOffHours.offsetTop) + this.bottomOffHours.offsetHeight - this.offHoursCollapsedHeight;
			if (childBottom > gridBottom)
			{
				const height = (node.offsetHeight - (childBottom - gridBottom));
				if (height > 0)
				{
					node.style.height = height + 'px';
				}
				else
				{
					node.remove();
				}
			}
			if (childTop < 0 && node.querySelector('.calendar-event-block-text'))
			{
				const height = (node.offsetHeight + childTop);
				node.style.top = (parseInt(node.style.top) - childTop) + 'px';
				if (height > 0)
				{
					node.style.height = height + 'px';
				}
				else
				{
					node.remove();
				}
			}
		}
	};

	DayView.prototype.switchOffHoursProps = function(isExpand)
	{
		const workTime = this.util.getWorkTime();
		if (isExpand)
		{
			this.toggleOffHoursClasses(this.offHoursCollapseClass, this.offHoursClass);
			this.setExpandedOffHoursHeight(workTime);
			this.displayOffHourLines(workTime);
			this.util.setUserOption('collapseOffHours', 'N');
		}
		else
		{
			this.toggleOffHoursClasses(this.offHoursClass, this.offHoursCollapseClass);
			this.setCollapsedOffHoursHeight(workTime);
			this.hideOffHourLines(workTime);
			this.util.setUserOption('collapseOffHours', 'Y');
		}
	};

	DayView.prototype.toggleOffHoursClasses = function(classBeforeAnimation, classAfterAnimation)
	{
		this.topOffHours.classList.add(classAfterAnimation);
		this.bottomOffHours.classList.add(classAfterAnimation);
		this.topOffHours.classList.remove(classBeforeAnimation);
		this.bottomOffHours.classList.remove(classBeforeAnimation);
	};

	DayView.prototype.setExpandedOffHoursHeight = function(workTime)
	{
		this.gridRow.style.height = (this.gridLineHeight * 24) + 40 + 'px';
		this.topOffHours.style.height = (this.gridLineHeight * workTime.start) + 1 + 'px';
		this.bottomOffHours.style.height = (this.gridLineHeight * (24 - workTime.end)) + 1 + 'px';
		this.bottomOffHours.style.top = (this.gridLineHeight * workTime.end) + 'px';
		this.updateGridRowShadowHeight();
	};

	DayView.prototype.setCollapsedOffHoursHeight = function(workTime)
	{
		this.gridRow.style.height = (this.gridLineHeight * (workTime.end - workTime.start)) + 30 + 'px';
		this.topOffHours.style.height = this.offHoursCollapsedHeight + 'px';
		this.bottomOffHours.style.height = this.offHoursCollapsedHeight + 'px';
		this.bottomOffHours.style.top = ((workTime.end - workTime.start) * this.gridLineHeight) + 9 + 'px';
		this.updateGridRowShadowHeight();
	};

	DayView.prototype.displayOffHourLines = function(workTime)
	{
		for (let i in this.timeLinesIndex)
		{
			if (this.timeLinesIndex.hasOwnProperty(i))
			{
				if (i < workTime.start || i > workTime.end)
				{
					this.timeLinesIndex[i].style.display = "block";
					this.timeLinesIndex[i].style.opacity = 1;
				}
				this.timeLinesIndex[i].style.top = (i * this.gridLineHeight) + 'px';
			}
		}
	};

	DayView.prototype.hideOffHourLines = function(workTime)
	{
		for (let i in this.timeLinesIndex)
		{
			if (this.timeLinesIndex.hasOwnProperty(i))
			{
				if (i <= workTime.start || i >= workTime.end)
				{
					this.timeLinesIndex[i].style.opacity = 0;
					this.timeLinesIndex[i].style.pointerEvents = 'none';
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
	};

	DayView.prototype.showHourLines = function()
	{
		for (const i in this.timeLinesIndex)
		{
			if (this.timeLinesIndex.hasOwnProperty(i))
			{
				this.timeLinesIndex[i].style.opacity = '';
				this.timeLinesIndex[i].style.display = '';
			}
		}
	};

	DayView.prototype.removeOffHoursEntries = function()
	{
		const workTime = this.util.getWorkTime();
		for (const entry of this.entries)
		{
			if (!entry.fullDay
				&& (entry.from.getHours() >= workTime.end || entry.to.getHours() <= workTime.start)
				&& entry.parts[0] && entry.parts[0].params
			)
			{
				entry.parts[0].params.wrapNode.style.minHeight = 0 + 'px';
			}
		}
		this.days.forEach((day) =>
		{
			if (day.collapsedWrap && day.collapsedWrap.top)
			{
				day.collapsedWrap.top.destroy();
			}
			if (day.collapsedWrap && day.collapsedWrap.bottom)
			{
				day.collapsedWrap.bottom.destroy();
			}
		});
	};

	DayView.prototype.checkTimelineScroll = function()
	{
		if (!this.scrollbarWidth)
		{
			this.scrollbarWidth = this.util.getScrollbarWidth();
		}

		// Compensate padding in right title calendar-grid-week-full-days-events-holder
		const wrapOffsetRight = this.gridWrap.scrollHeight > this.gridWrap.offsetHeight ? this.scrollbarWidth : 0;
		if (this.titleCont)
		{
			this.titleCont.style.paddingRight = wrapOffsetRight + "px";
		}

		if(this.fullDayEventsHolderCont
			&& this.topEntryHolder
			&& parseInt(this.topEntryHolder.style.right) !== parseInt(wrapOffsetRight)
		)
		{
			this.gridWrap.style.width = '100%';
			this.topEntryHolder.style.right = wrapOffsetRight + "px";
			this.fullDayEventsHolderCont.style.paddingRight = wrapOffsetRight + "px";
			// new BX.easing({
			// 	duration: 100,
			// 	start: {width: wrapOffsetRight, paddingRight: 0},
			// 	finish: {width: 0, paddingRight: wrapOffsetRight},
			// 	transition: BX.easing.makeEaseOut(BX.easing.transitions.linear),
			// 	step: BX.delegate(function (state) {
			// 		this.gridWrap.style.width = 'calc(100% + ' + state.width + 'px)';
			// 		this.topEntryHolder.style.right = state.paddingRight + "px";
			// 		this.fullDayEventsHolderCont.style.paddingRight = state.paddingRight + "px";
			// 	}, this),
			// 	complete: function (){}
			// }).animate();
		}
	};

	DayView.prototype.getDayGridHeight = function()
	{
		return 756;
	};

	DayView.prototype.updateGridRowShadowHeight = function()
	{
		if (this.isCollapsedOffHours)
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
				this.deselectEntry();
				if (this.dayIndex[dayCode] !== undefined && this.days[this.dayIndex[dayCode]])
				{
					this.showAllEventsInPopup({
						day: this.days[this.dayIndex[dayCode]],
						entrieList: this.days[this.dayIndex[dayCode]].entries.topList
					});
				}
			}
			else if ((!this.calendar.util.readOnlyMode())
				&& this.entryController.canDo(true, 'add_event')
				&& (dayCode = params.specialTarget && params.specialTarget.getAttribute('data-bx-calendar-week-day')))
			{
				this.deselectEntry();
				this.showCompactEditFormForNewEntry({
					entry: this.buildTopNewEntryWrap({
						dayFrom: this.days[this.dayIndex[dayCode]],
						holder: this.topEntryHolder
					})
				});
			}
		}
	};

	DayView.prototype.getEvents = function(day)
	{
		const items = this.name === 'week'
			? this.days[day.dayOffset].entries.timeline
			: this.days[0].entries.timeline;
		const events = items.map(item => item.entry);
		return events.filter(event => {
			let isEventDeleted = false;
			if (event.parts[0].params)
			{
				isEventDeleted = event.parts[0].params.wrapNode.style.opacity === '0';
			}
			return event.accessibility !== 'free' && event !== this.draggedEntry && !isEventDeleted;
		});
	};

	DayView.prototype.correctDuration = function (entry)
	{
		let isToDateChange = false;
		let fromDate = new Date(entry.dayFrom.date.getTime());
		let toDate = new Date(entry.dayFrom.date.getTime());
		fromDate.setHours(entry.timeFrom.h, entry.timeFrom.m, 0, 0);
		toDate.setHours(entry.timeTo.h, entry.timeTo.m, 0, 0);

		const fromDateOrig = new Date(fromDate.getTime());
		const toDateOrig = new Date(toDate.getTime());
		const items =
			this.name === 'week'
				? this.days[entry.dayFrom.dayOffset].entries.timeline
				: this.days[0].entries.timeline;

		for (var i = 0; i < items.length; i++)
		{
			if (items[i].entry.accessibility === 'free')
			{
				continue;
			}

			if (
				fromDate < items[i].entry.to
				&& fromDate >= items[i].entry.from
			)
			{
				fromDate = items[i].entry.to;
				if (!isToDateChange)
				{
					toDate.setHours(fromDate.getHours() + 1);
					toDate.setMinutes(fromDate.getMinutes())
				}
			}

			if (
				toDate > items[i].entry.from
				&& fromDate <= items[i].entry.from
			)
			{
				isToDateChange = true;
				toDate = items[i].entry.from;
			}
		}

		const shiftTimeMinutes = ((fromDate - fromDateOrig) / 60000);
		if (shiftTimeMinutes >= 30)
		{
			entry.timeFrom.h = fromDateOrig.getHours();
			entry.timeFrom.m = fromDateOrig.getMinutes();
			entry.timeTo.h = toDateOrig.getHours();
			entry.timeTo.m = toDateOrig.getMinutes();
		}
		else
		{
			entry.timeFrom.h = fromDate.getHours();
			entry.timeFrom.m = fromDate.getMinutes();
			entry.timeTo.h = toDate.getHours();
			entry.timeTo.m = toDate.getMinutes();
		}
	}

	DayView.prototype.correctEntryWrap = function(entry)
	{
		var fromTimeValue = entry.timeFrom.h + entry.timeFrom.m / 60;
		var toTimeValue = entry.timeTo.h + entry.timeTo.m / 60;
		entry.entryNode.style.height = ((toTimeValue - fromTimeValue) * this.gridLineHeight - 3) + 'px';

		var workTime = this.util.getWorkTime();

		if (this.isCollapsedOffHours)
		{
			fromTimeValue = Math.max(fromTimeValue, workTime.start);
			this.startMousePos = this.offtimeTuneBaseZeroPos + ((fromTimeValue - workTime.start) * this.gridLineHeight + 1);
		}
		else
		{
			this.startMousePos = this.offtimeTuneBaseZeroPos + (fromTimeValue * this.gridLineHeight + 1);
		}
	}

	DayView.prototype.getDayByCode = function(dayCode)
	{
		return this.days[this.dayIndex[dayCode]];
	}

	DayView.prototype.handleMousedown = function(e)
	{
		if (!this.isActive())
		{
			return;
		}

		var compactForm = BX.Calendar.EntryManager.getCompactViewForm(false);
		if (compactForm && compactForm.isShown())
		{
			return;
		}

		var dayCode;
		var target = this.calendar.util.findTargetNode(e.target || e.srcElement);

		if ((this.calendar.util.type === 'location' || !this.calendar.util.readOnlyMode())
			&& this.entryController.canDo(true, 'add_event')
			&& (dayCode = target && target.getAttribute('data-bx-calendar-timeline-day')))
		{
			// Prevent double registration on eventhandlers
			BX.unbind(document, "mousemove", BX.proxy(this.handleMousemove, this));
			BX.unbind(document, "mouseup", BX.proxy(this.handleMouseup, this));
			BX.bind(document, "mousemove", BX.proxy(this.handleMousemove, this));
			BX.bind(document, "mouseup", BX.proxy(this.handleMouseup, this));
			BX.addCustomEvent(this.calendar, 'keyup', BX.proxy(this.checkKeyup, this));

			this.canMoveOnCreate = false;
			setTimeout(() => {
				this.canMoveOnCreate = true;
			}, 100);
			this.createEntryMode = true;
			this.offtimeTuneBaseZeroPos = BX.pos(this.timeLinesCont).top;
			this.startMousePos = Math.max(this.offtimeTuneBaseZeroPos + this.gridWrap.scrollTop, this.calendar.util.getMousePos(e).y);

			this.newEntry = this.buildTimelineNewEntryWrap({
				dayFrom: this.days[this.dayIndex[dayCode]],
				holder: this.timelineEntryHolder
			});

			this.newEntry.dayFrom = this.days[this.dayIndex[dayCode]];

			this.newEntry.timeFrom = this.getTimeByPos(this.startMousePos - this.offtimeTuneBaseZeroPos, 30, true);
			var workTime = this.util.getWorkTime();
			var fromTimeValue = this.newEntry.timeFrom.h + this.newEntry.timeFrom.m / 60;

			if (this.isCollapsedOffHours)
			{
				fromTimeValue = Math.max(fromTimeValue, workTime.start);
				this.startMousePos = this.offtimeTuneBaseZeroPos + ((fromTimeValue - workTime.start) * this.gridLineHeight + 1);
			}
			else
			{
				this.startMousePos = this.offtimeTuneBaseZeroPos + (fromTimeValue * this.gridLineHeight + 1);
			}

			if (this.newEntry.timeFrom.h === 23)
			{
				this.newEntry.timeTo = {h: 23, m: 59};
			}
			else
			{
				this.newEntry.timeTo = {
					h: this.newEntry.timeFrom.h + 1,
					m: this.newEntry.timeFrom.m
				};
			}

			this.correctDuration(this.newEntry);
			this.correctEntryWrap(this.newEntry);

			this.updateCompactness(this.newEntry.entryNode);

			this.newEntry.changeTimeCallback(this.newEntry.timeFrom, this.newEntry.timeTo);
			this.newEntry.entryNode.style.top = (this.startMousePos - BX.pos(this.outerGrid).top) + 'px';
		}
	};

	DayView.prototype.handleMousemove = function(e)
	{
		if (this.createEntryMode && this.canMoveOnCreate)
		{
			var offset = this.isCollapsedOffHours ? 9 : 20;
			var mousePos = this.calendar.util.getMousePos(e).y;
			var height = Math.min(
				Math.max(mousePos - this.startMousePos, 10),
				parseInt(this.gridRow.style.height) - parseInt(this.newEntry.entryNode.style.top) - offset
			);

			this.newEntry.entryNode.style.height = height + 'px';

			this.updateCompactness(this.newEntry.entryNode);

			this.newEntry.timeTo = this.getTimeByPos(height + this.startMousePos - this.offtimeTuneBaseZeroPos);
			this.newEntry.changeTimeCallback(this.newEntry.timeFrom, this.newEntry.timeTo);
		}
	};

	DayView.prototype.handleMouseup = function(e)
	{
		// BX.unbind(document, "mousemove", BX.proxy(this.offHoursMousemove, this));
		// BX.unbind(document, "mouseup", BX.proxy(this.offHoursMouseup, this));
		BX.removeCustomEvent(this.calendar, 'keyup', BX.proxy(this.checkKeyup, this));

		if (this.createEntryMode)
		{
			var
				fromDate = new Date(this.newEntry.dayFrom.date.getTime()),
				toDate = new Date(this.newEntry.dayFrom.date.getTime());
			fromDate.setHours(this.newEntry.timeFrom.h, this.newEntry.timeFrom.m, 0, 0);
			toDate.setHours(this.newEntry.timeTo.h, this.newEntry.timeTo.m, 0, 0);

			this.deselectEntry();
			this.showCompactEditFormForNewEntry({
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

		if (params.keyCode === KEY_CODES['escape'] && this.createEntryMode && this.newEntry)
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
			sectionId = BX.Calendar.SectionManager.getNewEntrySectionId(),
			section = this.calendar.sectionManager.getSection(sectionId) || this.calendar.roomsManager.getRoom(sectionId),
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

		var entryClone = BX.adjust(this.fullDayEventsCont.appendChild(partWrap.cloneNode(true)), {
			props: {className: 'calendar-event-line-clone'},
			style: {
				width: (partWrap.offsetWidth - 4) + 'px',
				height: partWrap.offsetHeight + 'px',
				top : 3 + 'px',
				left : (partWrap.offsetLeft + 43)+ 'px',
				opacity: 1
			}
		});

		this.updateCompactness(entryClone);

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
			timeLabel, timeNode, nameNode, bindNode,
			sectionId = BX.Calendar.SectionManager.getNewEntrySectionId(),
			section = this.calendar.sectionManager.getSection(sectionId) || this.calendar.roomsManager.getRoom(sectionId),
			color = section.color;

		entryTime = this.entryController.getTimeForNewEntry(from.date);
		entryName = this.entryController.getDefaultEntryName();

		partWrap = params.holder.appendChild(BX.create('DIV', {
			props: {className: entryClassName},
			style: {
				height: this.gridLineHeight + 'px',
				minHeight: '20px',
				left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * ' + from.dayOffset + ' + 2px)' : '2px',
				width: 'calc(100% / ' + this.dayCount + ' - ' + this.lastEntryWidthOffset + 'px)'
			}
		}));
		innerNode = partWrap.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block-inner'}}));
		timeLabel = this.calendar.util.formatTime(entryTime.from.getHours(), entryTime.from.getMinutes()) + ' &ndash; ' + this.calendar.util.formatTime(entryTime.to.getHours(), entryTime.to.getMinutes());
		innerNode.appendChild(BX.create('SPAN', {
			props: {className: 'calendar-event-block-text'},
			style: {color: '#fff'},
			text: entryName
		}));
		innerNode.appendChild(BX.create('SPAN', {
			props: {className: 'calendar-event-block-time'},
			style: {color: '#fff'},
			html: timeLabel
		}));
		innerNode.style.backgroundColor = color;

		var entryClone = BX.adjust(this.outerGrid.appendChild(partWrap.cloneNode(true)), {
			props: {className: 'calendar-event-line-clone calendar-event-block-wrap active'},
			style: {
				width: (partWrap.offsetWidth - 3) + 'px',
				height: partWrap.offsetHeight + 'px',
				left : (partWrap.offsetLeft + 42)+ 'px',
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
		bindNode = entryClone.appendChild(BX.create('DIV', {props: {className: 'calendar-event-bind-node'}}));

		if (this.dayCount === 1)
			bindNode.style.right = '10%';
		else
			bindNode.style.left = '0';

		var entry = {
			entryNode: entryClone,
			innerNode: innerNode,
			section: section,
			entryName: entryName,
			bindNode: bindNode,
			blockBackgroundNode: innerNode,
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
				timeNode.innerHTML = timeLabel;
			},
			changeNameCallback: function(name)
			{
				nameNode.innerHTML = BX.util.htmlspecialchars(name);
			}
		};

		this.selectEntryPart(entry, color, false);

		return entry;
	};

	DayView.prototype.updateCompactness = function(entryNode)
	{
		const innerNode = entryNode.querySelector('.calendar-event-block-inner');
		const nameNode = entryNode.querySelector('.calendar-event-block-text');
		const timeNode = entryNode.querySelector('.calendar-event-block-time');
		if (!nameNode || !timeNode || !innerNode)
		{
			return;
		}
		// clear all compact levels
		BX.removeClass(entryNode, 'calendar-event-block-compact');
		BX.removeClass(entryNode, 'calendar-event-block-super-compact');
		nameNode.style.overflow = 'visible';

		// add compactness level by level
		if (nameNode.offsetHeight + timeNode.offsetHeight > innerNode.offsetHeight - 10)
		{
			const lineHeight = parseInt(window.getComputedStyle(nameNode).lineHeight);
			const fitHeight = innerNode.offsetHeight - timeNode.offsetHeight - 10;
			const lineCount = Math.floor(fitHeight / lineHeight);

			if (lineCount <= 1)
			{
				BX.addClass(entryNode, 'calendar-event-block-compact');
				BX.addClass(entryNode, 'calendar-event-block-super-compact');
			}
		}
		nameNode.style.overflow = '';
	}

	DayView.prototype.showCompactEditFormForNewEntry = function(params)
	{
		// Show simple add entry popup
		this.showCompactEditForm({
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
			}, this)
		});

		BX.Event.EventEmitter.unsubscribeAll('BX.Calendar.CompactEventForm:onChange');
		BX.Event.EventEmitter.subscribe('BX.Calendar.CompactEventForm:onChange', function(event)
		{
			if (event instanceof BX.Event.BaseEvent)
			{
				var data = event.getData();
				var dateTime = data.form.dateTimeControl.getValue();
				// var dayCode = this.util.getDayCode(dateTime.from);
				//
				// if (dayCode && this.dayIndex[dayCode] !== undefined && this.days[this.dayIndex[dayCode]])
				// {
				// 	var dayFrom = this.days[this.dayIndex[dayCode]];
				// 	partWrap.style.left = 'calc((100% / ' + this.dayCount + ') * (' + (dayFrom.dayOffset + 1) + ' - 1) + 2px)';
				//
				// 	BX.removeClass(this.entryHolders[dayFrom.holderIndex], 'shifted');
				// 	this.entryHolders[dayFrom.holderIndex].appendChild(partWrap);
				// 	var pos = BX.pos(partWrap);
				// 	if (entryClone)
				// 	{
				// 		BX.adjust(entryClone, {
				// 			style: {
				// 				width: (pos.width - 6) + 'px',
				// 				height: pos.height + 'px',
				// 				top : pos.top + 'px',
				// 				left : pos.left + 'px'
				// 			}
				// 		});
				// 	}
				// }
				//
				// var color = data.form.colorSelector.getValue();
				// if (entryClone)
				// {
				// 	entryClone.style.background = color;
				// 	entryClone.style.borderColor = color;
				// }
			}
		}.bind(this));


	};

	DayView.prototype.showAllEventsInPopup = function(params)
	{
		var
			entrieList = params.entrieList || params.day.entries.list,
			innerCont,
			popup;

		innerCont = BX.create('DIV', {
			props: {className: 'calendar-all-events-popup calendar-custom-scroll'},
			events: {click : BX.proxy(this.calendar.handleViewsClick, this.calendar)}
		});

		entrieList.sort(this.calendar.entryController.sort);

		var taskWrap, eventsWrap;
		entrieList.forEach(function(entryItem)
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

					this.displayTopEntry({
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

					this.displayTopEntry({
						entry: entryItem.entry,
						part: entryItem.part,
						holder: eventsWrap,
						popupMode: true
					});
				}
			}
		}, this);


		popup = BX.PopupWindowManager.create(this.calendar.id + "-all-events-popup", params.day.hiddenStorageText,
			{
				autoHide: true,
				closeByEsc: true,
				offsetTop: -2,
				offsetLeft: -50,
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

		this.loadEntries().then(entries => {
			this.entries = entries;
			this.displayEntries();
		});
		this.calendar.initialViewShow = false;
	};

	WeekView.prototype.initConfig = function()
	{
		DayView.prototype.initConfig.apply(this, arguments);
		this.name = 'week';
		this.title = BX.message('EC_VIEW_WEEK');
		this.contClassName = 'calendar-week-view';
		this.hotkey = 'W';
		this.gridWrapClass = 'calendar-grid-wrap';
		if (BX.isAmPmMode())
		{
			this.gridWrapClass += ' is-am-pm-mode';
		}
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

		if (viewRangeDate.getMonth() !== dateTo.getMonth())
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

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.CalendarDayView = DayView;
		window.BXEventCalendar.CalendarWeekView = WeekView;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.CalendarDayView = DayView;
			window.BXEventCalendar.CalendarWeekView = WeekView;
		});
	}


	function CollapsedTimeWrap(params)
	{
		this.position = params.position;
		this.outerWrap = params.wrap;
		this.workTime = params.workTime;
		this.dayOffset = params.dayOffset;
		this.dayCount = params.dayCount;
		this.lastEntryWidthOffset = params.lastEntryWidthOffset;
		this.gridLineHeight = params.gridLineHeight;
		this.labelMessage = params.labelMessage;

		this.clickHandler = params.clickHandler;
		this.mouseoutHandler = params.mouseoutHandler;
		this.mouseoverHandler = params.mouseoverHandler;

		this.isInited = false;
		this.entryCount = 0;
		this.create();
	}

	CollapsedTimeWrap.prototype =
	{
		create: function ()
		{
			this.wrap = this.outerWrap.appendChild(BX.create('DIV', {
				props: {
					className: 'calendar-event-block-wrap calendar-event-block-wrap-more'
				},
				style:
					{
						top: (this.position === 'bottom') ? ((this.workTime.end - this.workTime.start) * this.gridLineHeight) + 'px' : '-9px',
						left: this.dayCount > 1 ? 'calc((100% / ' + this.dayCount + ') * ' + this.dayOffset + ' + 2px)' : '2px',
						width: 'calc(100% / ' + this.dayCount + ' - ' + this.lastEntryWidthOffset + 'px)'
					}
			})).appendChild(BX.create('DIV', {
				props: {className: 'calendar-event-block-inner'},
				html: '<div class="calendar-event-block-background" style="background-color: #808080;"></div>'
			}));

			if (BX.type.isFunction(this.clickHandler))
			{
				BX.bind(this.wrap, 'click', this.clickHandler);
			}

			if (BX.type.isFunction(this.mouseoverHandler))
			{
				BX.bind(this.wrap, 'mouseover', this.mouseoverHandler);
			}

			if (BX.type.isFunction(this.mouseoutHandler))
			{
				BX.bind(this.wrap, 'mouseout', this.mouseoutHandler);
			}

			this.countContainer = this.wrap.appendChild(BX.create('span', {
				props: {
					className: 'calendar-event-block-text'
				},
				html: '<span class="calendar-event-block-text-subtitle">' + this.labelMessage + '</span>'
			})).appendChild(BX.create('span', {
				props: {
					className: 'calendar-event-block-text-total'
				}
			}));

			this.isInited = true;
		},

		inited: function()
		{
			return this.isInited && BX.isNodeInDom(this.wrap);
		},

		destroy: function()
		{
			BX.remove(this.wrap);
			this.isInited = false;
		},

		addEntry: function(entry)
		{
			this.entryCount++;
			this.countContainer.innerHTML = this.entryCount;
		}
	}
})(window);