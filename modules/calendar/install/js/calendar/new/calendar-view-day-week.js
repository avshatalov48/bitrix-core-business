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
})(window);