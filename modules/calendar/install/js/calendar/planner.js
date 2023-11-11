// # # #  #  #  # Planner for Event Calendar  # # #  #  #  #
;(function(window) {
function CalendarPlanner(params, initialUpdateParams)
{
	if (!params)
	{
		params = {};
	}
	this.config = params;
	this.id = params.id;
	this.userId = params.userId || BX.message('USER_ID');
	this.shown = false;
	this.built = false;
	this.dayLength = 86400000;
	this.shownScaleTimeFrom = 24;
	this.shownScaleTimeTo = 0;
	this.timelineCellWidthOrig = false;
	this.proposeTimeLimit = 60; // in days
	this.expandTimelineDelay = 600;
	this.limitScaleSizeMode = false;
	this.globalAnimation = true;
	this.checkTimeCache = {};

	this.DATE_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATE"));
	this.DATETIME_FORMAT = BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"));
	if ((this.DATETIME_FORMAT.substr(0, this.DATE_FORMAT.length) == this.DATE_FORMAT))
		this.TIME_FORMAT = BX.util.trim(this.DATETIME_FORMAT.substr(this.DATE_FORMAT.length));
	else
		this.TIME_FORMAT = BX.date.convertBitrixFormat(this.bAMPM ? 'H:MI:SS T' : 'HH:MI:SS');
	this.TIME_FORMAT_SHORT = this.TIME_FORMAT.replace(':s', '');
	this.SCALE_TIME_FORMAT = BX.isAmPmMode() ? 'g a' : 'G';

	this.entryStatusMap = {
		h : 'user-status-h',
		y : 'user-status-y',
		q : 'user-status-q',
		n : 'user-status-n'
	};

	this.SetConfig(params);
	this.SetLoadedDataLimits(this.scaleDateFrom, this.scaleDateTo);

	BX.addCustomEvent('OnCalendarPlannerDoUpdate', BX.proxy(this.DoUpdate, this));
	BX.addCustomEvent('OnCalendarPlannerDoExpand', BX.proxy(this.DoExpand, this));
	BX.addCustomEvent('OnCalendarPlannerDoResize', BX.proxy(this.DoResize, this));
	BX.addCustomEvent('OnCalendarPlannerDoSetConfig', BX.proxy(this.DoSetConfig, this));
	BX.addCustomEvent('OnCalendarPlannerDoUninstall', BX.proxy(this.DoUninstall, this));

	//BX.addCustomEvent('OnCalendarPlannerDoProposeTime', BX.proxy(this.DoProposeTime, this));
	if (initialUpdateParams)
	{
		initialUpdateParams.plannerId = this.id;
		if (initialUpdateParams.selector)
		{
			if (initialUpdateParams.selector.from && !initialUpdateParams.selector.from.getTime)
			{
				initialUpdateParams.selector.from = BX.parseDate(initialUpdateParams.selector.from);
			}
			if (initialUpdateParams.selector.to && !initialUpdateParams.selector.to.getTime)
			{
				initialUpdateParams.selector.to = BX.parseDate(initialUpdateParams.selector.to);
			}
		}
		this.globalAnimation = false;
		this.DoUpdate(initialUpdateParams);
		setTimeout(BX.delegate(function(){this.globalAnimation = true;}, this), 2000);
	}
}

CalendarPlanner.prototype =
{
	Show: function(animation)
	{
		if (!this.compactMode || this.globalAnimation === false)
			animation = false;

		if (this.hideAnimation)
		{
			this.hideAnimation.stop();
			this.hideAnimation = null;
		}

		this.outerWrap = BX(this.id);

		if (!this.outerWrap)
			return;

		if (!this.built)
		{
			this.build();
			this.BindEventHandlers();
		}
		else
		{
			this.ResizePlannerWidth(this.width);
		}

		this.HideSelector();
		this.BuildTimeline();

		if (this.adjustWidth)
		{
			this.ResizePlannerWidth(this.timelineInnerWrap.offsetWidth);
		}

		this.outerWrap.style.display = '';

		if (this.readonly)
			BX.addClass(this.mainContWrap, 'calendar-planner-readonly');
		else
			BX.removeClass(this.mainContWrap, 'calendar-planner-readonly');

		if (this.compactMode)
			BX.addClass(this.mainContWrap, 'calendar-planner-compact');
		else
			BX.removeClass(this.mainContWrap, 'calendar-planner-compact');

		this.entriesListOuterWrap.style.display = this.compactMode ? 'none' : '';

		if (animation)
		{
			var
				_this = this;

			if (this.showAnimation)
			{
				this.showAnimation.stop();
				this.showAnimation = null;
			}

			this.showAnimation = new BX.easing({
				duration: 300,
				start: {height: 0},
				finish: {height: this.height},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),

				step: function (state)
				{
					_this.outerWrap.style.height = state.height + 'px';
				},

				complete: BX.proxy(function ()
				{
					if (parseInt(_this.outerWrap.style.height) < _this.height)
						_this.outerWrap.style.height = this.height + 'px';
					this.showAnimation = null;
				}, this)
			});

			this.showAnimation.animate();
		}
		else
		{
			if (parseInt(this.outerWrap.style.height) < this.height)
				this.outerWrap.style.height = this.height + 'px';

			this.AdjustPlannerHeight();
		}

		this.shown = true;
	},

	Hide: function(animation)
	{
		if (this.showAnimation)
		{
			this.showAnimation.stop();
			this.showAnimation = null;
		}

		if (this.shown)
		{
			this.shown = false;

			if (animation)
			{
				if (this.hideAnimation)
				{
					this.hideAnimation.stop();
					this.hideAnimation = null;
				}

				var _this = this;

				this.hideAnimation = new BX.easing({
					duration: 300,
					start: {height: this.height},
					finish: {height: 0},
					transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),

					step: function (state)
					{
						_this.outerWrap.style.height = state.height + 'px';
					},

					complete: BX.proxy(function ()
					{
						this.hideAnimation = null;
						this.Hide(false);
					}, this)
				});

				this.hideAnimation.animate();
			}
			else
			{
				this.outerWrap.style.display = 'none';
				if (this.timelineScaleCont)
					BX.cleanNode(this.timelineScaleCont);
				this.timelineInnerWrap.removeAttribute('style');
				this.outerWrap.removeAttribute('style');
				this.mainContWrap.removeAttribute('style');
				this.entriesListOuterWrap.removeAttribute('style');
			}
		}
	},

	SetConfig: function(params)
	{
		// scaleType: 15min|30min|1hour|2hour|1day
		if (params.scaleType &&  {'15min' : 1,'30min' : 1,'1hour' : 1, '2hour' : 1, '1day' : 1}[params.scaleType])
		{
			this.scaleType = params.scaleType;
		}
		if (!this.scaleType)
		{
			this.scaleType = '1hour';
		}
		this.SetScaleType(this.scaleType);

		// showTimelineDayTitle
		if (params.showTimelineDayTitle !== undefined)
			this.showTimelineDayTitle = !!params.showTimelineDayTitle;
		else if(this.showTimelineDayTitle === undefined)
			this.showTimelineDayTitle = true;

		// compactMode
		if (params.compactMode !== undefined)
			this.compactMode = !!params.compactMode;
		else if (this.compactMode === undefined)
			this.compactMode = false;

		// readonly
		if (params.readonly !== undefined)
			this.readonly = !!params.readonly;
		else if (this.readonly === undefined)
			this.readonly = false;

		if (this.compactMode)
		{
			var compactHeight = 50;
			if (this.showTimelineDayTitle && this.scaleType != '1day')
				compactHeight += 20;
			this.height = this.minHeight = compactHeight;
		}

		// Select mode
		if (params.selectEntriesMode !== undefined)
			this.selectMode = !!params.selectEntriesMode;
		else if (this.selectMode === undefined)
			this.selectMode = false;


		this.scaleLimitOffsetLeft = parseInt(params.scaleLimitOffsetLeft) || this.scaleLimitOffsetLeft || 3;
		this.scaleLimitOffsetRight = parseInt(params.scaleLimitOffsetRight) || this.scaleLimitOffsetRight || 5;

		this.maxTimelineSize = parseInt(params.maxTimelineSize) || this.maxTimelineSize || 20;

		this.minEntryRows = parseInt(params.minEntryRows) || this.minEntryRows || 3;
		this.maxEntryRows = parseInt(params.maxEntryRows) || this.maxEntryRows || 30;

		this.width = parseInt(params.width) || this.width || 700;
		this.height = parseInt(params.height) || this.height || 84;
		this.minWidth = parseInt(params.minWidth) || this.minWidth || 700;
		this.minHeight = parseInt(params.minHeight) || this.minHeight || 84;
		if (this.width < this.minWidth)
			this.width = this.minWidth;
		if (this.height < this.minHeight)
			this.height = this.minHeight;

		this.workTime = params.workTime || this.workTime || [9,18];
		this.ExtendScaleTime(this.workTime[0], this.workTime[1]);

		this.weekHolidays = params.weekHolidays || this.weekHolidays || [];
		this.yearHolidays = params.yearHolidays || this.yearHolidays || [];
		this.accuracy = params.accuracy || this.accuracy || 300; // 5 min
		this.clickSelectorScaleAccuracy = params.clickSelectorScaleAccuracy || this.accuracy; // 5 min
		this.selectorAccuracy = parseInt(params.selectorAccuracy) || this.selectorAccuracy || 300; // 5 min
		this.entriesListWidth = parseInt(params.entriesListWidth) || this.entriesListWidth || 200;
		this.timelineCellWidth = params.timelineCellWidth || this.timelineCellWidth || 40;

		this.showEntiesHeader = params.showEntiesHeader === undefined ? true : !!params.showEntiesHeader;
		this.showEntryName = params.showEntryName === undefined ? true : !!params.showEntryName;

		if (this.scaleType == '1day' && this.timelineCellWidth < 100)
		{
			this.timelineCellWidthOrig = this.timelineCellWidth;
			this.timelineCellWidth = 100;
		}
		else if(this.timelineCellWidthOrig && this.scaleType != '1day')
		{
			this.timelineCellWidth = this.timelineCellWidthOrig;
			this.timelineCellWidthOrig = false;
		}

		if (this.adjustCellWidth === undefined || params.adjustCellWidth !== undefined)
			this.adjustCellWidth = this.readonly && this.compactMode && params.adjustCellWidth !== false;

		this.AdjustCellWidth();

		// Scale params
		if (params.scaleDateFrom !== undefined)
		{
			this.scaleDateFrom = typeof params.scaleDateFrom == 'string' ? BX.parseDate(params.scaleDateFrom) : params.scaleDateFrom;
		}
		else if (!this.scaleDateFrom)
		{
			if (this.compactMode && this.readonly)
			{
				this.scaleDateFrom = new Date();
			}
			else
			{
				this.scaleDateFrom = new Date(new Date().getTime() - this.dayLength * this.scaleLimitOffsetLeft /* days before */);
			}
		}
		this.scaleDateFrom.setHours(this.scaleType == '1day' ? 0 : this.shownScaleTimeFrom, 0, 0, 0);

		if (params.scaleDateTo !== undefined)
		{
			this.scaleDateTo = BX.type.isString(params.scaleDateTo) ? BX.parseDate(params.scaleDateTo) : params.scaleDateTo;
		}
		else if (!this.scaleDateTo)
		{
			if (this.compactMode && this.readonly)
			{
				this.scaleDateTo = new Date();
			}
			else
			{
				this.scaleDateTo = new Date(new Date().getTime() + this.dayLength * this.scaleLimitOffsetRight /* days after */);
			}
		}
		this.scaleDateTo.setHours(this.scaleType === '1day' ? 0 : this.shownScaleTimeTo, 0, 0, 0);
	},

	SetLoadedDataLimits: function(from, to)
	{
		if (from)
			this.loadedDataFrom = from.getTime ? from : BX.parseDate(from);
		if (to)
			this.loadedDataTo = to.getTime ? to : BX.parseDate(to);
	},

	ExtendScaleTime: function(fromTime, toTime)
	{
		if (fromTime !== false && !isNaN(parseInt(fromTime)))
		{
			this.shownScaleTimeFrom = Math.min(parseInt(fromTime), this.shownScaleTimeFrom, 23);
			this.shownScaleTimeFrom = Math.max(this.shownScaleTimeFrom, 0);

			if (this.scaleDateFrom)
				this.scaleDateFrom.setHours(this.shownScaleTimeFrom, 0,0,0);
		}

		if (toTime !== false && !isNaN(parseInt(toTime)))
		{
			this.shownScaleTimeTo = Math.max(parseInt(toTime), this.shownScaleTimeTo, 1);
			this.shownScaleTimeTo = Math.min(this.shownScaleTimeTo, 24);

			if (this.scaleDateTo)
				this.scaleDateTo.setHours(this.shownScaleTimeTo, 0,0,0);
		}

		this.checkSelectorPosition = this.shownScaleTimeFrom !== 0 || this.shownScaleTimeTo !== 24;
	},

	AdjustCellWidth: function()
	{
		if (this.adjustCellWidth)
		{
			this.timelineCellWidth = Math.round(this.width / ((this.shownScaleTimeTo - this.shownScaleTimeFrom) * 3600 / this.scaleSize));
		}
	},

	build: function()
	{
		this.outerWrap.style.width = this.width + 'px';

		// Left part - list of users and other resourses
		var entriesListWidth = this.compactMode ? 0 : this.entriesListWidth;

		// Timeline with accessibility information
		this.mainContWrap = this.outerWrap.appendChild(BX.create("DIV", {
			props: {className: 'calendar-planner-main-container calendar-planner-main-container-resource'}, style: {
				minHeight: this.minHeight + 'px', height: this.height + 'px', width: this.width + 'px'
			}
		}));

		if (!this.showEntryName)
		{
			BX.addClass(this.mainContWrap, 'calendar-planner-entry-icons-only');
		}

		if (this.readonly)
		{
			BX.addClass(this.mainContWrap, 'calendar-planner-readonly');
		}

		this.entriesListOuterWrap = this.mainContWrap.appendChild(BX.create("DIV", {
			props: {className: 'calendar-planner-user-container'},
			style: {
				width: entriesListWidth + 'px',
				height: this.height + 'px'
			}
		}));

		this.PreventSelection(this.entriesListOuterWrap);
		if (this.compactMode)
		{
			BX.addClass(this.mainContWrap, 'calendar-planner-compact');
			this.entriesListOuterWrap.style.display = 'none';
		}

		if (this.scaleType === '1day')
		{
			BX.addClass(this.entriesListOuterWrap, 'calendar-planner-no-daytitle');
		}
		else
		{
			BX.removeClass(this.entriesListOuterWrap, 'calendar-planner-no-daytitle');
		}

		if (this.showEntiesHeader !== false)
		{
			this.entriesListHeader = this.entriesListOuterWrap.appendChild(
					BX.create("DIV", {props: {className: 'calendar-planner-header'}})
				).appendChild(
					BX.create("DIV", {props: {className: 'calendar-planner-general-info'}})
				).appendChild(
					BX.create("DIV", {props: {className: 'calendar-planner-users-header'}})
				);


			this.entriesListTitleCounter = this.entriesListHeader.appendChild(BX.create("span", {
				props: {className: 'calendar-planner-users-item'},
				text: BX.message('EC_PL_ATTENDEES_TITLE') + ' '
			})).appendChild(BX.create("span"));
		}
		//this.goToNowButton = this.entriesListHeader.appendChild(BX.create("span", {props: {className: 'calendar-planner-add-icon', title: BX.message('EC_PL_GOTO_NOW')}}));


		this.entriesListWrap = this.entriesListOuterWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-user-container-inner'}}));

		// Fixed cont with specific width and height
		this.timelineFixedWrap = this.mainContWrap.appendChild(BX.create("DIV", {
			props: {className: 'calendar-planner-timeline-wrapper'}, style: {
				height: this.height + 'px'
			}
		}));

		// Movable cont - used to easy and simultaniously move scale and data containers
		this.timelineInnerWrap = this.timelineFixedWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-timeline-inner-wrapper'}}));
		this.timelineInnerWrap.setAttribute('data-bx-planner-meta', 'timeline');

		// Scale container
		this.timelineScaleCont = this.timelineInnerWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-time'}}));
		this.PreventSelection(this.timelineScaleCont);

		// Accessibility container
		this.timelineDataCont = this.timelineInnerWrap.appendChild(BX.create("DIV", {
			props: {className: 'calendar-planner-timeline-container'},
			style: {
				height: (this.height) + 'px'
			}
		}));
		// Container with accessibility entries elements
		this.accessibilityWrap = this.timelineDataCont.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-acc-wrap'}}));

		// Selector
		this.selector = this.buildSelector();
		this.selectorTitle = this.selector.selectorTitle;
		this.selectorProposeIcon = this.selector.selectorProposeIcon;

//	<div class="calendar-planner-time-arrow-left">
//		<span class="calendar-planner-time-arrow-left-item"></span>
//		<span class="calendar-planner-time-arrow-right-text"></span>
//	</div>
//	<div class="calendar-planner-time-arrow-right">
//		<span class="calendar-planner-time-arrow-right-text">BX.message('EC_PL_PROPOSE')</span>
//		<span class="calendar-planner-time-arrow-right-item"></span>
//	</div>

		if (this.selectMode)
		{
			this.selectedEntriesWrap = this.mainContWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-timeline-select-entries-wrap'}}));

			this.hoverRow = this.mainContWrap.appendChild(BX.create("DIV", {
				props: {className: 'calendar-planner-timeline-hover-row'},
				style: {
					top: 0,
					width: parseInt(this.mainContWrap.offsetWidth) + 'px'
				}
			}));

			BX.unbind(document, 'mousemove', BX.proxy(this.mouseMoveHandler, this));
			BX.bind(document, 'mousemove', BX.proxy(this.mouseMoveHandler, this));
		}

		if (!this.compactMode)
		{
			this.settingsButton = this.mainContWrap.appendChild(BX.create("div", {
				props: {
					className: 'calendar-planner-settings-icon-container',
					title: BX.message('EC_PL_SETTINGS_SCALE')
				},
				html: '<span class="calendar-planner-settings-title">' + BX.message('EC_PL_SETTINGS_SCALE') + '</span><span class="calendar-planner-settings-icon"></span>'
			}));
			BX.bind(this.settingsButton, 'click', BX.proxy(this.ShowSettingsPopup, this));
		}

		this.built = true;
	},

	buildSelector: function()
	{
		var
			result,
			wrap = this.timelineDataCont.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-timeline-selector'}, html: '<span data-bx-planner-meta="selector-resize-left" class="calendar-planner-timeline-drag-left"></span><span class="calendar-planner-timeline-selector-grip"></span><span data-bx-planner-meta="selector-resize-right" class="calendar-planner-timeline-drag-right"></span>'}));
		wrap.setAttribute('data-bx-planner-meta', 'selector');

		// prefent draging selector and activating uploader controll in livefeed
		wrap.ondrag = BX.False;
		wrap.ondragstart = BX.False;

		var selectorTitle = this.mainContWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-selector-notice'}, style: {display: 'none'}}));

		result = {
			wrap: wrap,
			selectorTitle: selectorTitle
		};

		if (this.selectMode)
		{
			result.controlWrap = wrap.appendChild(BX.create("DIV", {
				props: {className: 'calendar-planner-selector-control'}
			}));
		}

		return result;
	},

	BuildTimeline: function()
	{
		if (this.timelineScaleCont)
		{
			BX.cleanNode(this.timelineScaleCont);
		}

		this.GetScaleData();

		var
			outerDayCont, dayTitle,
			cont = this.timelineScaleCont;

		for (var i = 0; i < this.scaleData.length; i++)
		{
			if (this.showTimelineDayTitle && this.scaleType !== '1day')
			{
				if (this.scaleDayTitles[this.scaleData[i].daystamp])
				{
					cont = this.scaleDayTitles[this.scaleData[i].daystamp];
				}
				else
				{
					outerDayCont = this.timelineScaleCont.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-time-day-outer'}}));

					const format = BX.Main.DateTimeFormat.getFormat('DAY_OF_WEEK_MONTH_FORMAT');
					dayTitle = outerDayCont.appendChild(BX.create("DIV", {
						props: {className: 'calendar-planner-time-day-title'},
						html: '<span>' + BX.Main.DateTimeFormat.format(format, this.scaleData[i].timestamp / 1000) + '</span>' +
							'<div class="calendar-planner-time-day-border"></div>'
					}));

					cont = outerDayCont.appendChild(BX.create("DIV", {
						props: {className: 'calendar-planner-time-day'}
					}));

					this.scaleDayTitles[this.scaleData[i].daystamp] = cont;

				}
			}

			var className = 'calendar-planner-time-hour-item' + (this.scaleData[i].dayStart ? ' calendar-planner-day-start' : '');
			if ((this.scaleType == '15min' || this.scaleType == '30min') &&
				this.scaleData[i].title !== ''
			)
			{
				className += ' calendar-planner-time-hour-bold';
			}

			this.scaleData[i].cell = cont.appendChild(BX.create("DIV", {
				props: {className: className},
				style: {
					width: this.timelineCellWidth + 'px',
					minWidth: this.timelineCellWidth + 'px'
				},
				html: this.scaleData[i].title != '' ? '<i>' + this.scaleData[i].title + '</i>' : this.scaleData[i].title
			}));

			if (this.scaleType !== '1day' && this.scaleData[i + 1] && this.scaleData[i + 1].dayStart)
			{
				cont.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-timeline-border'}}));
			}
		}
		var mapDatePosRes = this.MapDatePos();
		this.datePosMap = mapDatePosRes.datePosMap;
		this.posDateMap = mapDatePosRes.posDateMap;

		var timelineOffset = this.timelineScaleCont.offsetWidth;
		this.timelineInnerWrap.style.width = timelineOffset + 'px';
		this.entriesListWrap.style.top = (parseInt(this.timelineDataCont.offsetTop) + 10) + 'px';
		this.CheckRebuildTimeout(timelineOffset);
	},

	CheckRebuildTimeout: function(timelineOffset, timeout)
	{
		var _this = this;
		if (!timeout)
			timeout = 200;

		if (!this._checkRebuildTimeoutCount)
			this._checkRebuildTimeoutCount = 0;

		if (this.rebuildTimeout)
			this.rebuildTimeout = !!clearTimeout(this.rebuildTimeout);

		if (this._checkRebuildTimeoutCount <= 5)
		{
			this._checkRebuildTimeoutCount++;
			this.rebuildTimeout = setTimeout(function ()
			{
				if (timelineOffset !== _this.timelineScaleCont.offsetWidth)
				{
					if (_this.rebuildTimeout)
						_this.rebuildTimeout = !!clearTimeout(_this.rebuildTimeout);

					_this.RebuildPlanner();
					_this.FocusSelector(true, 200);
				}
				else
				{
					_this.CheckRebuildTimeout(timelineOffset, timeout);
				}
			}, timeout);
		}
		else
		{
			delete this._checkRebuildTimeoutCount;
		}
	},

	RebuildPlanner: function(params)
	{
		if (!params || typeof params != 'object')
			params = {};

		this.BuildTimeline();
		this.ClearAccessibilityData();
		this.UpdateData({accessibility: this.accessibility, entries: this.entries});
		this.AdjustPlannerHeight();
		this.ResizePlannerWidth(this.width);

		if (params.updateSelector !== false)
		{
			this.UpdateSelector(params.selectorParams || false);
		}
		this.clearCacheTime();
	},

	GetScaleData: function()
	{
		this.scaleData = [];
		this.scaleDayTitles = {};

		var
			ts, scaleFrom, scaleTo,
			time, daystamp, title,
			curDaystamp = false,
			timeFrom = this.scaleType === '1day' ? 0 : parseInt(this.shownScaleTimeFrom),
			timeTo = this.scaleType === '1day' ? 0 : parseInt(this.shownScaleTimeTo);

		this.scaleDateFrom.setHours(timeFrom, 0, 0, 0);
		this.scaleDateTo.setHours(timeTo, 0, 0, 0);
		scaleFrom = this.scaleDateFrom.getTime();
		scaleTo = this.scaleDateTo.getTime();

		for (ts = scaleFrom; ts < scaleTo; ts += this.scaleSize * 1000)
		{
			time = parseFloat(BX.date.format('H.i', ts / 1000));

			if (this.scaleType == '1day')
				title = BX.date.format('d F, D', ts / 1000);
			else
				title = BX.date.format('i', ts / 1000) == '00' ? BX.date.format(this.SCALE_TIME_FORMAT, ts / 1000) : '';

			if (this.scaleType == '1day' || (time >= timeFrom && time < timeTo))
			{
				daystamp = BX.date.format('d.m.Y', ts / 1000);
				this.scaleData.push({
					daystamp: daystamp,
					timestamp: ts,
					value: ts,
					title: title,
					dayStart: curDaystamp !== daystamp
				});
				curDaystamp = daystamp;
			}
		}

		return this.scaleData;
	},

	/**
	 * Updates data on scale of planner
	 *
	 * @params array array of parameters
	 * @params[entries] - array of entries to display on scale
	 * @params[accessibility] - object contains informaton about accessibility for entries
	 * @return null
	 */
	UpdateData:  function(params)
	{
		if (!params.accessibility)
		{
			params.accessibility = {};
		}

		this.accessibility = params.accessibility;
		this.entries = params.entries;

		var i, k, entry, acc, userId = this.userId;
		// Compact mode
		if (this.compactMode)
		{
			var data = [];
			for (k in params.accessibility)
			{
				if (params.accessibility.hasOwnProperty(k) && params.accessibility[k] && params.accessibility[k].length > 0)
				{
					for (i = 0; i < params.accessibility[k].length; i++)
					{
						data.push(this.HandleAccessibilityEntry(params.accessibility[k][i]));
					}
				}
			}

			// Fuse access
			//data = this.FuseAccessibility(data);
			this.compactRowWrap = this.accessibilityWrap.appendChild(BX.create("DIV", {
				props: {className: 'calendar-planner-timeline-space'},
				style: {}
			}));

			this.currentData = [data];
			for (i = 0; i < data.length; i++)
			{
				this.DisplayAccessibilityEntry(data[i], this.compactRowWrap);
			}
		}
		else
		{
			// sort entries list by amount of accessibilities data
			// Enties without accessibilitity data should be in the end of the array
			// But first in the list will be meeting room
			// And second (or first) will be owner-host of the event

			if (params.entries && params.entries.length)
			{
				params.entries.sort(function(a, b)
				{
					if (b.status === 'h' || b.id == userId && a.status !== 'h')
					{
						return 1;
					}
					if (a.status === 'h' || a.id == userId && b.status !== 'h')
					{
						return  -1;
					}
					return 0;
				});

				if (this.selectedEntriesWrap)
				{
					BX.cleanNode(this.selectedEntriesWrap);

					if (this.selector && this.selector.controlWrap)
					{
						BX.cleanNode(this.selector.controlWrap);
					}
				}

				var
					cutData = [],
					usersCount = 0,
					cutAmount = 0,
					dispDataCount = 0,
					cutDataTitle = [];

				for (i = 0; i < params.entries.length; i++)
				{
					entry = params.entries[i];
					acc = params.accessibility[entry.id] || [];
					entry.uid = this.getEntryUniqueId(entry);

					if (entry.type === 'user')
						usersCount++;

					if (this.minEntryRows && (i < this.minEntryRows || params.entries.length === this.minEntryRows + 1))
					{
						dispDataCount++;
						this.displayEntryRow(entry, acc);
					}
					else
					{
						cutAmount++;
						cutDataTitle.push(entry.name);
						if (acc.length > 0)
						{
							for (k = 0; k < acc.length; k++)
							{
								cutData.push(this.HandleAccessibilityEntry(acc[k]));
							}
						}
					}
				}

				// Update entries title count
				if (this.entriesListTitleCounter)
				{
					this.entriesListTitleCounter.innerHTML = usersCount > this.maxEntryRows ? '(' + usersCount + ')' : '';
				}

				if (cutAmount > 0)
				{
					if (dispDataCount === this.maxEntryRows)
					{
						this.displayEntryRow({name: BX.message('EC_PL_ATTENDEES_LAST') + ' (' + cutAmount + ')', type: 'lastUsers', title: cutDataTitle.join(', ')}, cutData);
					}
					else
					{
						this.displayEntryRow({name: BX.message('EC_PL_ATTENDEES_SHOW_MORE') + ' (' + cutAmount + ')', type: 'moreLink'}, cutData);
					}
				}
			}
		}
		this.AdjustPlannerHeight();

		BX.onCustomEvent('OnCalendarPlannerUpdated', [this, {
			plannerId: this.id,
			entries: this.entries
		}]);
	},

	ClearAccessibilityData:  function()
	{
		BX.cleanNode(this.entriesListWrap);
		BX.cleanNode(this.accessibilityWrap);
	},

	HandleAccessibilityEntry: function(entry)
	{
		if (!entry.from)
		{
			entry.from = BX.parseDate(entry.dateFrom);
			entry.from.setSeconds(0,0);
			entry.fromTimestamp = entry.from.getTime();
		}

		if (!entry.to)
		{
			entry.to = BX.parseDate(entry.dateTo);
			entry.to.setSeconds(0,0);
			entry.toTimestamp = entry.to.getTime();
		}

		if (!entry.toReal)
		{
			entry.toReal = entry.to;
			entry.toTimestampReal = entry.toTimestamp;
		}

		return entry;
	},

	DisplayAccessibilityEntry: function(entry, wrap)
	{
		var
			timeFrom, timeTo,
			hidden = false,
			fromTimestamp = entry.fromTimestamp,
			toTimestamp = entry.toTimestampReal || entry.toTimestamp,
			shownScaleTimeFrom = this.scaleType === '1day' ? 0 : this.shownScaleTimeFrom,
			shownScaleTimeTo = this.scaleType === '1day' ? 24 : this.shownScaleTimeTo,
			from = new Date(fromTimestamp),
			to = new Date(toTimestamp);

		timeFrom = parseInt(from.getHours()) + from.getMinutes() / 60;
		timeTo = parseInt(to.getHours()) + to.getMinutes() / 60;

		if (timeFrom > shownScaleTimeTo)
		{
			from = new Date(from.getTime() + this.dayLength - 1);
			from.setHours(shownScaleTimeFrom, 0, 0, 0);
			if (from.getTime() >= to.getTime())
				hidden = true;
		}

		if (!hidden && timeFrom < shownScaleTimeFrom)
		{
			from.setHours(shownScaleTimeFrom, 0, 0, 0);
			if (from.getTime() >= to.getTime())
				hidden = true;
		}

		if (!hidden && timeTo > shownScaleTimeTo)
		{
			to.setHours(shownScaleTimeTo, 0, 0, 0);
			if (from.getTime() >= to.getTime())
				hidden = true;
		}

		if (!hidden && timeTo < shownScaleTimeFrom)
		{
			to = new Date(to.getTime() - this.dayLength + 1);
			to.setHours(shownScaleTimeTo, 0, 0, 0);
			if (from.getTime() >= to.getTime())
				hidden = true;
		}

		if (!hidden)
		{
			var
				fromPos = this.GetPosByDate(from),
				toPos = this.GetPosByDate(to);

			entry.node = wrap.appendChild(BX.create("DIV", {
				props: {
					className: 'calendar-planner-acc-entry' + (entry.type && entry.type === 'hr' ? ' calendar-planner-acc-entry-hr' : '')
				},
				style: {
					left: fromPos + 'px',
					width: Math.max((toPos - fromPos), 3) + 'px'
				}
			}));

			if (entry.title)
			{
				entry.node.title = entry.title;
			}
		}
	},

	//FuseAccessibility: function(data)
	//{
	//	// sort
	//	data.sort(function(a, b){return a.fromTimestamp - b.fromTimestamp});
	//
	//	var i, res = [], resCurInd = false, dataIToTimestamp, resResCurIndToTimestampReal;
	//	for (i = 0; i < data.length; i++)
	//	{
	//		if (resCurInd !== false && res[resCurInd])
	//		{
	//			if (data[i].fromTimestamp <= res[resCurInd].toTimestampReal)
	//			{
	//				dataIToTimestamp = data[i].toTimestampReal || data[i].toTimestamp;
	//				resResCurIndToTimestampReal = res[resCurInd].toTimestampReal || res[resCurInd].toTimestamp;
	//
	//				if (dataIToTimestamp > resResCurIndToTimestampReal)
	//				{
	//					res[resCurInd].toTimestamp = dataIToTimestamp;
	//					res[resCurInd].to = data[i].toReal || data[i].to;
	//
	//					res[resCurInd].toTimestampReal = dataIToTimestamp;
	//					res[resCurInd].toReal = data[i].toReal || data[i].to;
	//				}
	//				continue;
	//			}
	//		}
	//
	//		res.push({
	//			fromTimestamp: data[i].fromTimestamp,
	//			from: data[i].from,
	//			toTimestamp: data[i].toTimestampReal,
	//			toTimestampReal: data[i].toTimestampReal,
	//			to: data[i].to,
	//			toReal: data[i].toReal || data[i].to
	//		});
	//		resCurInd = res.length - 1;
	//	}
	//
	//	return res;
	//},

	displayEntryRow: function(entry, accessibility)
	{
		if (entry.type === 'moreLink')
		{
			entry.rowWrap = this.entriesListWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-user'}}));

			if (this.showEntryName)
			{
				this.showMoreUsers = entry.rowWrap.appendChild(BX.create("DIV", {
					props: {
						className: 'calendar-planner-all-users', title: entry.title || ''
					},
					text: entry.name,
					events: {'click': BX.proxy(this.ShowMoreUsers, this)}
				}));
			}
			else
			{
				this.showMoreUsers = entry.rowWrap.appendChild(BX.create("DIV", {
					props: {className: 'calendar-planner-users-more', title: entry.name},
					html: '<span class="calendar-planner-users-more-btn"></span>',
					events: {'click': BX.proxy(this.ShowMoreUsers, this)}
				}));
			}
		}
		else if (entry.type === 'lastUsers')
		{
			entry.rowWrap = this.entriesListWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-user'}}));

			if (this.showEntryName)
			{
				this.showMoreUsers = entry.rowWrap.appendChild(BX.create("DIV", {
					props: {
						className: 'calendar-planner-all-users calendar-planner-last-users',
						title: entry.title || ''
					},
					text: entry.name
				}));
			}
			else
			{
				this.showMoreUsers = entry.rowWrap.appendChild(BX.create("DIV", {
					props: {className: 'calendar-planner-users-more', title: entry.title || entry.name},
					html: '<span class="calendar-planner-users-last-btn"></span>'
				}));
			}
		}
		else if (entry.id && entry.type === 'user')
		{
			entry.rowWrap = this.entriesListWrap.appendChild(BX.create("DIV", {
				attrs: {
					'data-bx-planner-entry' : entry.uid,
					className: 'calendar-planner-user' + (entry.emailUser ? ' calendar-planner-email-user' : '')
				}
			}));

			if (entry.status && this.entryStatusMap[entry.status])
			{
				entry.rowWrap.appendChild(BX.create("span", {props: {className: 'calendar-planner-user-status-icon ' + this.entryStatusMap[entry.status], title: BX.message('EC_PL_STATUS_' + entry.status.toUpperCase())}}));
			}

			entry.rowWrap.appendChild(this.getEntryAvatarNode(entry));
			//
			// if (entry.avatar)
			// {
			// 	entry.rowWrap.appendChild(BX.create("div", {
			// 		props: {
			// 			className: 'ui-icon ui-icon-common-user calendar-planner-user-image-icon',
			// 			// src: entry.avatar
			// 		},
			// 		attrs: {
			// 			'bx-tooltip-user-id': entry.id,
			// 			'bx-tooltip-classname': 'calendar-planner-user-tooltip'
			// 		},
			// 		children: [
			// 			BX.create("i", {
			// 				style: {
			// 					backgroundImage: 'url('+entry.avatar+')'
			// 				}
			// 			})
			// 		]
			// 	}));
			// }

			if (this.showEntryName)
			{
				entry.rowWrap.appendChild(
					BX.create("span", {props: {className: 'calendar-planner-user-name'}})).appendChild(
					BX.create("span", {
							props: {
								className: 'calendar-planner-entry-name'
							},
							attrs: {
								'bx-tooltip-user-id': entry.id,
								'bx-tooltip-classname': 'calendar-planner-user-tooltip'
							},
							style: {
								width: (this.entriesListWidth - 42) + 'px'
							},
							text: entry.name
					}));
			}
		}
		else if (entry.id && entry.type === 'room')
		{
			entry.rowWrap = this.entriesListWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-user'}}));

			if (this.showEntryName)
			{
				entry.rowWrap.appendChild(BX.create("span", {props: {className: 'calendar-planner-user-name'}})).appendChild(BX.create("A", {
					props: {
						href: entry.url ? entry.url : '#',
						className: 'calendar-planner-entry-name'
					},
					style: {
						width: (this.entriesListWidth - 20) + 'px'
					},
					text: entry.name
				}));
			}
			else
			{
				entry.rowWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-location-image-icon', title: entry.name}}));
			}
		}
		else if (entry.type === 'resource')
		{
			if (!this.entriesResourceListWrap || !BX.isNodeInDom(this.entriesResourceListWrap))
			{
				this.entriesResourceListWrap = this.entriesListWrap.appendChild(BX.create("DIV", {
					props: {className: 'calendar-planner-container-resource'},
					html: '<div class="calendar-planner-resource-header"><span class="calendar-planner-users-item">' + BX.message('EC_PL_RESOURCE_TITLE') + '</span></div>'
				}));
			}

			entry.rowWrap = this.entriesResourceListWrap.appendChild(BX.create("DIV", {
				attrs: {
					'data-bx-planner-entry' : entry.uid,
					className: 'calendar-planner-user'
				}
			}));

			if (this.showEntryName)
			{
				entry.rowWrap.appendChild(BX.create("span", {props: {className: 'calendar-planner-user-name'}})).appendChild(BX.create("span", {
					props: {
						className: 'calendar-planner-entry-name'
					},
					style: {
						width: (this.entriesListWidth - 20) + 'px'
					},
					text: entry.name
				}));
			}
			else
			{
				entry.rowWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-location-image-icon', title: entry.name}}));
			}
		}
		else
		{
			entry.rowWrap = this.entriesListWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-user'}}));

			entry.rowWrap.appendChild(BX.create("DIV", {
				props: {className: 'calendar-planner-all-users'},
				text: entry.name
			}));
		}

		// Fuse access
		for (i = 0; i < accessibility.length; i++)
		{
			accessibility[i] = this.HandleAccessibilityEntry(accessibility[i]);
		}
		//accessibility = this.FuseAccessibility(accessibility);

		var
			i,
			top = entry.rowWrap.offsetTop + 13;

		entry.dataRowWrap = this.accessibilityWrap.appendChild(BX.create("DIV", {
			attrs: {
				'data-bx-planner-entry' : entry.uid,
				className: 'calendar-planner-timeline-space'
			},
			style: {
				top: top + 'px'
			}
		}));

		for (i = 0; i < accessibility.length; i++)
		{
			this.DisplayAccessibilityEntry(accessibility[i], entry.dataRowWrap);
		}

		if (this.selectMode)
		{
			entry.selectorControlWrap = this.selector.controlWrap.appendChild(BX.create("DIV", {
				attrs: {
					'data-bx-planner-entry' : entry.uid,
					className: 'calendar-planner-selector-control-row'
				},
				style: {
					top: (top - 4) + 'px'
				}
			}));

			if (entry.selected)
			{
				this.selectEntryRow(entry);
			}
		}
	},

	getEntryAvatarNode: function(entry)
	{
		var imageNode;
		var img = entry.avatar;

		if (!img || img === "/bitrix/images/1.gif")
		{
			let defaultAvatarClass = 'ui-icon-common-user';
			if (entry.emailUser)
			{
				defaultAvatarClass = 'ui-icon-common-user-mail';
			}
			if (entry.sharingUser)
			{
				defaultAvatarClass += ' ui-icon-common-user-sharing';
			}

			imageNode = BX.create("DIV", {
				props: {
					className: 'ui-icon calendar-planner-user-image-icon ' + defaultAvatarClass,
				},
				attrs: {
					'bx-tooltip-user-id': entry.id,
					'bx-tooltip-classname': 'calendar-planner-user-tooltip',
					'title': BX.Text.encode(entry.name)
				},
				html: '<i></i>'
			});
		}
		else
		{
			imageNode = BX.create("DIV", {
				props: {
					className: 'ui-icon calendar-planner-user-image-icon'
				},
				attrs: {
					'bx-tooltip-user-id': entry.id,
					'bx-tooltip-classname': 'calendar-planner-user-tooltip',
					'title': BX.Text.encode(entry.name)
				},
				html: '<i style="background-image: url(\'' + (encodeURI(entry.avatar)) + '\')"></i>'
			});
		}
		return imageNode;
	},

	selectEntryRow: function(entry)
	{
		if (BX.type.isPlainObject(entry))
		{
			var top = parseInt(entry.dataRowWrap.offsetTop);
			if (!entry.selectWrap || !BX.isParentForNode(this.selectedEntriesWrap, entry.selectWrap))
			{
				entry.selectWrap = this.selectedEntriesWrap.appendChild(BX.create("DIV", {props: {className: 'calendar-planner-timeline-selected'}}));
			}

			entry.selectWrap.style.display = '';
			entry.selectWrap.style.top = (top + 36) + 'px';
			entry.selectWrap.style.width = (parseInt(this.mainContWrap.offsetWidth) + 5) + 'px';

			BX.addClass(entry.selectorControlWrap, 'active');
			entry.selected = true;

			this.clearCacheTime();
		}
	},

	isEntrySelected: function(entry)
	{
		return entry && entry.selected;
	},

	deSelectEntryRow: function(entry)
	{
		if (BX.type.isPlainObject(entry))
		{
			if (entry.selectWrap)
			{
				entry.selectWrap.style.display = 'none';
			}
			if (entry.selectorControlWrap)
			{
				BX.removeClass(entry.selectorControlWrap, 'active');
			}
			entry.selected = false;
			this.clearCacheTime();
		}
	},

	getEntryUniqueId: function(entry)
	{
		return entry.type + '-' + entry.id;
	},

	getEntryByUniqueId: function(entryUniqueId)
	{
		if (BX.type.isArray(this.entries))
		{
			return this.entries.find(function(entry){return entry.uid == entryUniqueId;})
		}
		return null;
	},

	/**
	 *
	 *
	 * @params array array of parameters
	 * @params[from]
	 * @params[to]
	 * @params[updateScaleType] bool
	 * @params[updateScaleLimits] bool
	 *
	 * @return null
	 */
	UpdateSelector: function(params)
	{
		if (!params)
			params = {};

		params.updateScaleType = !!params.updateScaleType;
		params.updateScaleLimits = !!params.updateScaleLimits;
		params.animation = !!params.animation;

		var
			_this = this,
			rebuildTimeline = false,
			dateFrom, dateTo, fullDay;

		dateFrom = (params.from && params.from.getTime ? params.from : BX.parseDate(params.from)) || this.currentSelectorDateFrom;
		dateTo = (params.to && params.to.getTime ? params.to : BX.parseDate(params.to)) || this.currentSelectorDateTo;
		fullDay = params.fullDay !== undefined ? params.fullDay : this.currentSelectorFullDay;

		if (dateTo && dateTo.getTime() && dateFrom && dateFrom.getTime())
		{
			this.currentSelectorDateFrom = dateFrom;
			this.currentSelectorDateTo = dateTo;
			this.currentSelectorFullDay = fullDay;

			this.SetFullDayMode(fullDay);
			if (fullDay)
			{
				dateTo = new Date(dateTo.getTime() + this.dayLength);
				dateFrom.setHours(0, 0, 0,0);
				dateTo.setHours(0, 0, 0,0);

				if (this.scaleType !== '1day')
				{
					this.SetScaleType('1day');
					rebuildTimeline = true;
				}
			}

			// Update limits of scale
			if (params.updateScaleLimits && this.scaleType !== '1day')
			{
				var timeFrom = parseInt(dateFrom.getHours()) + Math.floor(dateFrom.getMinutes() / 60);
				var timeTo = parseInt(dateTo.getHours()) + Math.ceil(dateTo.getMinutes() / 60);

				if (this.FormatDate(dateFrom) !== this.FormatDate(dateTo))
				{
					this.ExtendScaleTime(0, 24);
					rebuildTimeline = true;
				}
				else
				{
					if (timeFrom < this.shownScaleTimeFrom)
					{
						this.ExtendScaleTime(timeFrom, false);
						rebuildTimeline = true;
					}

					if (timeTo > this.shownScaleTimeTo)
					{
						this.ExtendScaleTime(false, timeTo);
						rebuildTimeline = true;
					}

					if (rebuildTimeline)
					{
						this.AdjustCellWidth();
					}
				}
			}

			//if (params.RRULE)
			//{
			//	this.HandleRecursion(params);
			//}

			if (rebuildTimeline)
			{
				this.RebuildPlanner({updateSelector: false});
			}

			// Update selector
			_this.ShowSelector({
				dateFrom: dateFrom,
				dateTo: dateTo,
				animation: params.animation,
				focus: params.focus
			});
		}
	},

	ShowSelector: function(params)
	{
		var
			selector = params.selector || this.selector.wrap,
			dateFrom = params.dateFrom,
			dateTo = params.dateTo,
			animation = params.animation && this.globalAnimation !== false,
			focus = params.focus;

		selector.style.display = 'block';

		if (dateFrom && dateTo)
		{
			var
				fromPos = this.GetPosByDate(dateFrom),
				toPos = this.GetPosByDate(dateTo);

			selector.style.width = (toPos - fromPos) + 'px';

			if (this.fullDayMode)
				animation = false;

			if (animation && selector.style.left)
			{
				this.TransitSelector({
					toX: fromPos,
					triggerChangeEvents: false,
					focus: focus === true
				});
			}
			else
			{
				selector.style.left = fromPos + 'px';
				selector.style.width = (toPos - fromPos) + 'px';
				if (focus === true)
				{
					this.FocusSelector(false, 200, true);
				}
				this.CheckSelectorStatus(fromPos);
			}
		}
	},

	HideSelector: function()
	{
		if (this.selector && this.selector.wrap)
		{
			this.selector.wrap.style.display = 'none';
		}
	},

	StartMovingSelector: function()
	{
		this.selectorIsDraged = true;
		this.selectorRoundedPos = false;
		this.selectorStartLeft = parseInt(this.selector.wrap.style.left);
		this.selectorStartScrollLeft = this.timelineFixedWrap.scrollLeft;

		if (this.currentSelectorInstances)
		{
			for (var i = 0; i < this.currentSelectorInstances.length; i++)
			{
				this.currentSelectorInstances[i].selectorStartLeft = parseInt(this.currentSelectorInstances[i].selector.style.left);
			}
		}

		BX.addClass(document.body, 'calendar-planner-unselectable');
	},

	MoveSelector: function(x)
	{
		var
			selectorWidth = parseInt(this.selector.wrap.style.width),
			pos = this.selectorStartLeft + x;

		// Correct cursor position acording to changes of scrollleft
		pos -= this.selectorStartScrollLeft - this.timelineFixedWrap.scrollLeft;

		if (this.posDateMap[pos])
		{
			this.selectorRoundedPos = pos;
		}
		else
		{
			var roundedPos = this.RoundPos(pos);
			if (this.posDateMap[roundedPos])
			{
				this.selectorRoundedPos = roundedPos;
			}
		}

		var checkedPos = this.CheckSelectorPosition(this.selectorRoundedPos);
		if (checkedPos !== this.selectorRoundedPos)
		{
			this.selectorRoundedPos = checkedPos;
			pos = checkedPos;
		}

		this.selector.wrap.style.left = pos + 'px';
		this.ShowSelectorTitle({fromPos: pos, toPos: this.selectorRoundedPos + selectorWidth});

		if (this.currentSelectorInstances)
		{
			var
				i, posi,
				deltaX = pos - this.selectorStartLeft,
				selector;

			for (i = 0; i < this.currentSelectorInstances.length; i++)
			{
				selector = this.currentSelectorInstances[i].selector;
				posi = this.currentSelectorInstances[i].selectorStartLeft + deltaX;
				selector.style.left = posi + 'px';
				this.ShowSelectorTitle({
					fromPos: posi,
					toPos: posi + selectorWidth,
					selector: selector,
					selectorTitle: this.currentSelectorInstances[i].selectorTitle
				});
			}
		}

		this.CheckSelectorStatus(this.selectorRoundedPos, true);
	},

	EndMovingSelector: function()
	{
		if (this.selectorRoundedPos)
		{
			this.selector.wrap.style.left = this.selectorRoundedPos + 'px';
			this.selectorRoundedPos = false;
			this.HideSelectorTitle();
			this.OnSelectorChanged(this.selectorRoundedPos);

			if (this.currentSelectorInstances)
			{
				for (var i = 0; i < this.currentSelectorInstances.length; i++)
				{
					this.HideSelectorTitle({
						selectorTitle: this.currentSelectorInstances[i].selectorTitle,
						selectorIndex: i
					});
				}
			}
		}
		this.selectorIsDraged = false;
	},

	StartResizeSelector: function()
	{
		this.selectorIsResized = true;
		this.selectorRoundedPos = false;

		this.selectorStartLeft = parseInt(this.selector.wrap.style.left);
		this.selectorStartWidth = parseInt(this.selector.wrap.style.width);
		this.selectorStartScrollLeft = this.timelineFixedWrap.scrollLeft;

		if (this.currentSelectorInstances)
		{
			for (var i = 0; i < this.currentSelectorInstances.length; i++)
			{
				this.currentSelectorInstances[i].selectorStartLeft = parseInt(this.currentSelectorInstances[i].selector.style.left);
				this.currentSelectorInstances[i].selectorStartWidth = parseInt(this.currentSelectorInstances[i].selector.style.width);
			}
		}
	},

	ResizeSelector: function(x)
	{
		var
			toDate, timeTo,
			width = this.selectorStartWidth + x;
		// Correct cursor position acording to changes of scrollleft
		width -= this.selectorStartScrollLeft - this.timelineFixedWrap.scrollLeft;
		var rightPos = this.selectorStartLeft + width;

		if (rightPos > parseInt(this.timelineInnerWrap.style.width))
			rightPos = parseInt(this.timelineInnerWrap.style.width);

		toDate = this.GetDateByPos(rightPos, true);

		if (this.fullDayMode)
		{
			timeTo = parseInt(toDate.getHours()) + Math.round((toDate.getMinutes() / 60) * 10) / 10;
			toDate.setHours(0, 0, 0, 0);
			if (timeTo > 12)
			{
				toDate = new Date(toDate.getTime() + this.dayLength);
				toDate.setHours(0, 0, 0, 0);
			}
			rightPos = this.GetPosByDate(toDate);
			width = rightPos - this.selectorStartLeft;

			if (width <= 10)
			{
				toDate = this.GetDateByPos(this.selectorStartLeft);
				toDate = new Date(toDate.getTime() + this.dayLength);
				toDate.setHours(0, 0, 0, 0);
				width = this.GetPosByDate(toDate) - this.selectorStartLeft;
				rightPos = this.selectorStartLeft + width;
			}
		}
		else if (this.shownScaleTimeFrom != 0 || this.shownScaleTimeTo != 24)
		{
			var fromDate = this.GetDateByPos(this.selectorStartLeft);
			if (toDate && fromDate && this.FormatDate(fromDate) != this.FormatDate(toDate))
			{
				toDate = new Date(fromDate.getTime());
				toDate.setHours(this.shownScaleTimeTo, 0, 0, 0);
				rightPos = this.GetPosByDate(toDate);
				width = rightPos - this.selectorStartLeft;
			}
		}

		if (this.posDateMap[rightPos])
		{
			this.selectorRoundedRightPos = rightPos;
		}
		else
		{
			var roundedPos = this.RoundPos(rightPos);
			if (this.posDateMap[roundedPos])
			{
				this.selectorRoundedRightPos = roundedPos;
			}
		}

		this.selector.wrap.style.width = width + 'px';
		this.ShowSelectorTitle({fromPos: this.selectorStartLeft, toPos: this.selectorRoundedRightPos});

		if (this.currentSelectorInstances)
		{
			var
				i, posi,
				selector;

			for (i = 0; i < this.currentSelectorInstances.length; i++)
			{
				selector = this.currentSelectorInstances[i].selector;
				posi = this.currentSelectorInstances[i].selectorStartLeft;
				selector.style.width = width + 'px';
				this.ShowSelectorTitle({
					fromPos: posi,
					toPos: posi + width,
					selector: selector,
					selectorTitle: this.currentSelectorInstances[i].selectorTitle
				});
			}
		}

		this.CheckSelectorStatus(this.selectorStartLeft, true);
	},

	EndResizeSelector: function()
	{
		if (this.selectorRoundedRightPos)
		{
			this.selector.wrap.style.width = (this.selectorRoundedPos - parseInt(this.selector.wrap.style.left)) + 'px';
			this.selectorRoundedRightPos = false;
			this.HideSelectorTitle();
			this.OnSelectorChanged();

			if (this.currentSelectorInstances)
			{
				for (var i = 0; i < this.currentSelectorInstances.length; i++)
				{
					this.HideSelectorTitle({
						selectorTitle: this.currentSelectorInstances[i].selectorTitle,
						selectorIndex: i
					});
				}
			}
		}
		this.selectorIsResized = false;
	},

	CheckSelectorStatus: function(selectorPos, checkPosition)
	{
		checkPosition = checkPosition === true;

		if (this.config.useSolidBlueSelector)
		{
			BX.removeClass(this.selector.wrap, 'calendar-planner-timeline-selector-warning');
			BX.removeClass(this.mainContWrap, 'calendar-planner-selector-warning');
			BX.addClass(this.selector.wrap, 'solid');
		}
		else
		{
			if (!selectorPos)
			{
				selectorPos = this.RoundPos(this.selector.wrap.style.left);
			}

			var
				fromDate,toDate,
				i,periodIsFree, fromDateI, toDateI;

			if (checkPosition || !this.currentSelectorDateFrom)
			{
				var
					selectorWidth = parseInt(this.selector.wrap.style.width),
					fromPos = parseInt(selectorPos),
					toPos = fromPos + selectorWidth;

				if (!fromPos && !toPos && !selectorWidth && this.selector.lastFromDate)
				{
					fromDate = this.selector.lastFromDate;
					toDate = this.selector.lastToDate;
				}
				else
				{
					fromDate = this.GetDateByPos(fromPos);
					toDate = this.GetDateByPos(toPos, true);
					this.selector.lastFromDate = fromDate;
					this.selector.lastToDate = toDate;
				}
			}
			else
			{
				fromDate = this.currentSelectorDateFrom;
				toDate = this.currentSelectorDateTo;
				//this.currentSelectorFullDay = fullDay;
			}

			if (fromDate && toDate)
			{
				periodIsFree = this.checkTimePeriod(fromDate, toDate) === true;
				if (this.currentSelectorInstances && periodIsFree)
				{
					for (i = 0; i < this.currentSelectorInstances.length; i++)
					{
						selectorPos = this.RoundPos(this.currentSelectorInstances[i].selector.style.left);
						fromPos = parseInt(selectorPos);
						toPos = fromPos + selectorWidth;

						fromDateI = this.GetDateByPos(fromPos);
						toDateI = this.GetDateByPos(toPos, true);
						fromDateI.setHours(fromDate.getHours(), fromDate.getMinutes(), 0, 0);
						toDateI.setHours(toDate.getHours(), toDate.getMinutes(), 0, 0);

						periodIsFree = this.checkTimePeriod(fromDateI, toDateI) === true;
						if (!periodIsFree)
						{
							break;
						}
					}
				}

				if (this.selectorIsFree !== periodIsFree)
				{
					this.selectorIsFree = periodIsFree;
					if (this.selectorIsFree)
					{
						BX.removeClass(this.selector.wrap, 'calendar-planner-timeline-selector-warning');
						BX.removeClass(this.mainContWrap, 'calendar-planner-selector-warning');
						if (this.currentSelectorInstances)
						{
							for (i = 0; i < this.currentSelectorInstances.length; i++)
							{
								BX.removeClass(this.currentSelectorInstances[i].selector, 'calendar-planner-timeline-selector-warning');
							}
						}
						this.HideProposeControl();
					}
					else
					{
						BX.addClass(this.selector.wrap, 'calendar-planner-timeline-selector-warning');
						BX.addClass(this.mainContWrap, 'calendar-planner-selector-warning');

						if (this.currentSelectorInstances)
						{
							for (i = 0; i < this.currentSelectorInstances.length; i++)
							{
								BX.addClass(this.currentSelectorInstances[i].selector, 'calendar-planner-timeline-selector-warning');
							}
						}
						this.ShowProposeControl();
					}

					BX.onCustomEvent('OnCalendarPlannerSelectorStatusOnChange', [{
						plannerId: this.id,
						status: this.selectorIsFree ? 'free' : 'busy'
					}]);
				}
			}
		}
	},

	OnSelectorChanged: function(selectorPos, selectorWidth)
	{
		if (!selectorPos)
			selectorPos = parseInt(this.selector.wrap.style.left);

		if (selectorPos < 0)
			selectorPos = 0;

		if (!selectorWidth)
			selectorWidth = parseInt(this.selector.wrap.style.width);

		if (selectorPos + selectorWidth > parseInt(this.timelineInnerWrap.style.width))
		{
			selectorPos = parseInt(this.timelineInnerWrap.style.width) - selectorWidth;
		}

		var
			dateFrom = this.GetDateByPos(selectorPos),
			dateTo = this.GetDateByPos(selectorPos + selectorWidth, true);

		if (dateFrom && dateTo)
		{
			this.currentSelectorDateFrom = dateFrom;
			this.currentSelectorDateTo = dateTo;

			if (this.fullDayMode)
			{
				dateTo = new Date(dateTo.getTime() - this.dayLength);
			}

			this.currentSelectorFullDay = this.fullDayMode;

			BX.onCustomEvent('OnCalendarPlannerSelectorChanged', [{
				plannerId: this.id,
				dateFrom: dateFrom,
				dateTo: dateTo,
				fullDay: this.fullDayMode,
				planner: this
			}]);
		}
	},

	CheckSelectorPosition: function(fromPos, selectorWidth, toPos)
	{
		if (this.checkSelectorPosition && (this.scaleType != '1day' || this.fullDayMode))
		{
			if (!fromPos)
				fromPos = parseInt(this.selector.wrap.style.left);

			if (!selectorWidth)
				selectorWidth = parseInt(this.selector.wrap.style.width);

			if (!toPos)
				toPos = fromPos + selectorWidth;

			if (toPos > parseInt(this.timelineInnerWrap.style.width))
			{
				fromPos = parseInt(this.timelineInnerWrap.style.width) - selectorWidth;
			}
			else
			{
				var
					fromDate = this.GetDateByPos(fromPos),
					toDate = this.GetDateByPos(toPos, true),
					timeFrom, timeTo,
					scaleTimeFrom = parseInt(this.shownScaleTimeFrom),
					scaleTimeTo = parseInt(this.shownScaleTimeTo);

				if (fromDate && toDate)
				{
					if (this.fullDayMode)
					{
						timeFrom = parseInt(fromDate.getHours()) + Math.round((fromDate.getMinutes() / 60) * 10) / 10;
						fromDate.setHours(0, 0, 0, 0);

						if (timeFrom > 12)
						{
							fromDate = new Date(fromDate.getTime() + this.dayLength);
							fromDate.setHours(0, 0, 0, 0);
						}

						fromPos = this.GetPosByDate(fromDate);
					}
					else if (fromDate.getDay() != toDate.getDay())
					{
						timeFrom = parseInt(fromDate.getHours()) + Math.round((fromDate.getMinutes() / 60) * 10) / 10;
						timeTo = parseInt(toDate.getHours()) + Math.round((toDate.getMinutes() / 60) * 10) / 10;
						if (Math.abs(scaleTimeTo - timeFrom) > Math.abs(scaleTimeFrom - timeTo))
						{
							fromDate.setHours(this.shownScaleTimeTo, 0, 0,0);
							fromPos = this.GetPosByDate(fromDate) - selectorWidth;
						}
						else
						{
							toDate.setHours(this.shownScaleTimeFrom, 0, 0,0);
							fromPos = this.GetPosByDate(toDate);
						}
					}
				}
			}
		}
		return fromPos;
	},

	TransitSelector: function(params)
	{
		var
			fromX = params.fromX || false,
			toX = params.toX || false,
			triggerChangeEvents = params.triggerChangeEvents !== false,
			focus = params.focus;

		var _this = this;
		toX = this.RoundPos(toX);
		if (!fromX)
			fromX = parseInt(this.selector.wrap.style.left);

		var width = parseInt(this.selector.wrap.offsetWidth);

		// triggerChangeEvents - it means that selector transition (animation caused from mouse ebents)
		if (toX > (fromX + width) && triggerChangeEvents)
		{
			toX -= width;
		}

		if (fromX != toX)
		{
			if (this.animation)
			{
				this.animation.stop();
			}

			this.animation = new BX.easing({
				duration: 300,
				start: {left: fromX},
				finish: {left: toX},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),

				step: function (state)
				{
					_this.selector.wrap.style.left = state.left + 'px';
				},

				complete: BX.proxy(function ()
				{
					this.animation = null;
					var
						fromPos = parseInt(this.selector.wrap.style.left),
						checkedPos = this.CheckSelectorPosition(fromPos);

					if (checkedPos !== fromPos)
					{
						this.selector.wrap.style.left = checkedPos + 'px';
					}

					if (triggerChangeEvents)
					{
						this.OnSelectorChanged(checkedPos);
					}

					//if (focus === true)
					//{
					//	this.FocusSelector(true, 300);
					//}

					setTimeout(BX.delegate(function(){
						this.ShowSelector({
							dateFrom: this.currentSelectorDateFrom,
							dateTo: this.currentSelectorDateTo,
							animation: false,
							focus: focus === true
						});
					},this), 200);

					this.CheckSelectorStatus(checkedPos);
				}, this)
			});

			this.animation.animate();
		}
		else
		{
			if (triggerChangeEvents)
			{
				this.OnSelectorChanged();
			}

			if (focus === true)
			{
				this.FocusSelector(true, 300);
			}

			this.CheckSelectorStatus();
		}
	},

	ShowSelectorTitle: function(params)
	{
		var
			fromPos = params.fromPos,
			toPos = params.toPos,
			selectorTitle = params.selectorTitle || this.selectorTitle,
			selector = params.selectorTitle || this.selector.wrap,
			_this = this, fromDate, toDate;

		if (fromPos && toPos)
		{
			if (toPos > parseInt(this.timelineInnerWrap.style.width))
			{
				fromPos = parseInt(this.timelineInnerWrap.style.width) - parseInt(selector.style.width);
				toPos = parseInt(this.timelineInnerWrap.style.width);
			}

			fromDate = this.GetDateByPos(fromPos);
			toDate = this.GetDateByPos(toPos, true);
			if (fromDate && toDate)
			{
				if (this.fullDayMode)
				{
					if (Math.abs(toDate.getTime() - fromDate.getTime() - this.dayLength) < 1000)
					{
						selectorTitle.innerHTML = BX.date.format('d F, D', fromDate.getTime() / 1000);
					}
					else
					{
						selectorTitle.innerHTML =
							BX.date.format('d F', fromDate.getTime() / 1000)
							+ ' - '
							+ BX.date.format('d F', toDate.getTime() / 1000);
					}
				}
				else
				{
					selectorTitle.removeAttribute('style');
					selectorTitle.innerHTML = this.FormatTime(fromDate) + ' - ' + this.FormatTime(toDate);
				}

				if (this.selectMode && this.lastTouchedEntry)
				{
					var
						entriesListWidth = this.compactMode ? 0 : this.entriesListWidth,
						selectorTitleLeft = parseInt(selector.style.left) - this.timelineFixedWrap.scrollLeft + entriesListWidth + parseInt(selector.style.width) / 2,
						selectorTitleTop = parseInt(this.timelineDataCont.offsetTop) + parseInt(this.lastTouchedEntry.style.top) - 12;

					selectorTitle.style.top = selectorTitleTop + 'px';
					selectorTitle.style.left = selectorTitleLeft + 'px';
				}
				else
				{
					selector.appendChild(selectorTitle);
				}
			}
		}

		if (selectorTitle == this.selectorTitle)
		{
			if (selectorTitle.style.display == 'none' || this.selectorHideTimeout)
			{
				this.selectorHideTimeout = clearTimeout(this.selectorHideTimeout);
				// Opacity animation
				this.selectorTitle.style.display = '';
				this.selectorTitle.style.opacity = 0;
				new BX.easing({
					duration: 400,
					start: {opacity: 0},
					finish: {opacity: 100},
					transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
					step: function (state)
					{
						_this.selectorTitle.style.opacity = state.opacity / 100;
					},
					complete: function ()
					{
						_this.selectorTitle.removeAttribute('style');
					}
				}).animate();
			}
		}
		else
		{
			selectorTitle.removeAttribute('style');
		}
	},

	HideSelectorTitle: function(params)
	{
		//return;
		if (!params || typeof params !== 'object')
			params = {};

		var
			timeoutName = params.selectorIndex === undefined ? 'selectorHideTimeout' : 'selectorHideTimeout_' + params.selectorIndex,
			selectorTitle = params.selectorTitle || this.selectorTitle,
			_this = this;

		if (this[timeoutName])
			this[timeoutName] = clearTimeout(this[timeoutName]);

		if (params.timeout !== false)
		{
			this[timeoutName] = setTimeout(function()
			{
				params.timeout = false;
				_this.HideSelectorTitle(params);
			}, 700);
		}
		else
		{
			// Opacity animation
			selectorTitle.style.display = '';
			selectorTitle.style.opacity = 1;
			new BX.easing({
				duration: 400,
				start: {opacity: 100},
				finish: {opacity: 0},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
				step: function (state)
				{
					selectorTitle.style.opacity = state.opacity / 100;
				},
				complete: function ()
				{
					selectorTitle.removeAttribute('style');
					selectorTitle.style.display = 'none';
				}
			}).animate();
		}
	},

	BindEventHandlers: function()
	{
		BX.bind(this.outerWrap, 'click', BX.proxy(this.Click, this));
		BX.bind(this.outerWrap, 'mousedown', BX.proxy(this.Mousedown, this));
		BX.bind(document, "mousemove", BX.proxy(this.MouseMove, this));
		BX.bind(document, "mouseup", BX.proxy(this.MouseUp, this));

		if ('onwheel' in document)
			BX.bind(this.timelineFixedWrap, "wheel", BX.proxy(this.MousewheelTimelineHandler, this));
		else
			BX.bind(this.timelineFixedWrap, "mousewheel", BX.proxy(this.MousewheelTimelineHandler, this));
	},

	Click: function(e)
	{
		if (!e)
		{
			e = window.event;
		}

		this.clickMousePos = this.GetMousePos(e);
		var
			nodeTarget = e.target || e.srcElement,
			accuracyMouse = 5;


		if (this.selectMode &&
			BX.hasClass(nodeTarget, 'calendar-planner-selector-control-row'))
		{
			var entry = this.getEntryByUniqueId(nodeTarget.getAttribute('data-bx-planner-entry'));
			if (entry)
			{
				if (!this.isEntrySelected(entry))
				{
					this.selectEntryRow(entry);
				}
				else
				{
					this.deSelectEntryRow(entry);
				}

				this.CheckSelectorStatus();

				BX.onCustomEvent('OnCalendarPlannerSelectedEntriesOnChange', [{
					plannerId: this.id,
					entries: this.entries
				}]);
			}
			return;
		}

		// currentSelectorInstances - it's repetetive event, so in this case
		// it's hard to deside which one of the instance we shoud move
		if (!this.readonly && !this.currentSelectorInstances)
		{
			var
				timeline = this.FindTarget(nodeTarget, 'timeline'),
				selector = this.FindTarget(nodeTarget, 'selector');

			if (timeline && !selector && Math.abs(this.clickMousePos.x - this.mouseDownMousePos.x) < accuracyMouse && Math.abs(this.clickMousePos.y - this.mouseDownMousePos.y) < accuracyMouse)
			{
				var left = this.clickMousePos.x - BX.pos(this.timelineFixedWrap).left + this.timelineFixedWrap.scrollLeft;

				if (this.clickSelectorScaleAccuracy !== this.accuracy)
				{
					var mapDatePosRes = this.MapDatePos(this.clickSelectorScaleAccuracy);
					var dateFrom = this.GetDateByPos(left, false, mapDatePosRes.posDateMap);
					left = this.GetPosByDate(dateFrom);
				}

				this.TransitSelector({
					toX: left
				});
			}
		}
	},

	Mousedown: function(e)
	{
		if (!e)
			e = window.event;

		var nodeTarget = e.target || e.srcElement;
		this.mouseDownMousePos = this.GetMousePos(e);
		this.mouseDown = true;

		if (!this.readonly)
		{
			var selector = this.FindTarget(nodeTarget, 'selector');
			this.startMousePos = this.mouseDownMousePos;

			if (selector)
			{
				if (this.FindTarget(nodeTarget, 'selector-resize-right'))
				{
					this.StartResizeSelector();
				}
				else
				{
					this.StartMovingSelector();
				}
			}
			else if (this.FindTarget(nodeTarget, 'timeline'))
			{
				this.StartScrollTimeline();
			}
		}
	},

	MouseUp: function()
	{
		if (this.selectorIsDraged)
		{
			this.EndMovingSelector();
		}

		if (this.selectorIsResized)
		{
			this.EndResizeSelector();
		}

		if(this.timelineIsDraged)
		{
			this.EndScrollTimeline();
		}

		if (this.shown && !this.readonly && this.mouseDown)
		{
			this.CheckTimelineScroll();
		}

		this.mouseDown = false;

		BX.removeClass(document.body, 'calendar-planner-unselectable');
	},

	MouseMove: function(e)
	{
		var
			mousePos,
			target = e.target || e.srcElement;

		if (this.selectMode && target && target.getAttribute && target.getAttribute('data-bx-planner-entry'))
		{
			this.lastTouchedEntry = target;
		}

		if (this.selectorIsDraged)
		{
			mousePos = this.GetMousePos(e);
			this.MoveSelector(mousePos.x - this.startMousePos.x);
		}

		if (this.selectorIsResized)
		{
			mousePos = this.GetMousePos(e);
			this.ResizeSelector(mousePos.x - this.startMousePos.x);
		}

		if(this.timelineIsDraged)
		{
			mousePos = this.GetMousePos(e);
			this.ScrollTimeline(mousePos.x - this.startMousePos.x);
		}
	},

	MousewheelTimelineHandler: function(e)
	{
		e = e || window.event;

		if (this.shown && !this.readonly)
		{
			var delta = e.deltaY || e.detail || e.wheelDelta;
			if (Math.abs(delta) > 0)
			{
				if (!BX.browser.IsMac())
				{
					delta = delta * 5;
				}
				var newScroll = this.timelineFixedWrap.scrollLeft + delta;
				this.timelineFixedWrap.scrollLeft = Math.max(newScroll, 0);
				this.CheckTimelineScroll();
				return BX.PreventDefault(e);
			}
		}
	},

	CheckTimelineScroll: function()
	{
		var
			minScroll = this.GelTimelineScrollOffset(),
			maxScroll = this.timelineFixedWrap.scrollWidth - this.timelineFixedWrap.offsetWidth - this.GelTimelineScrollOffset();

		// Check and expand only if it is visible
		if (this.timelineFixedWrap.offsetWidth > 0)
		{
			if (this.timelineFixedWrap.scrollLeft <= minScroll)
			{
				this.ExpandTimeline('left');
			}
			else if (this.timelineFixedWrap.scrollLeft >= maxScroll)
			{
				this.ExpandTimeline('right');
			}
		}
	},

	StartScrollTimeline: function()
	{
		this.timelineIsDraged = true;
		this.timelineStartScrollLeft = this.timelineFixedWrap.scrollLeft;
	},
	ScrollTimeline: function(x)
	{
		this.timelineFixedWrap.scrollLeft = Math.max(this.timelineStartScrollLeft - x, 0);
	},
	EndScrollTimeline: function()
	{
		this.timelineIsDraged = false;
	},

	FindTarget: function(node, nodeMetaType, parentCont)
	{
		if (!parentCont)
			parentCont = this.mainContWrap;

		var type = (node && node.getAttribute) ? node.getAttribute('data-bx-planner-meta') : null;

		if (type !== nodeMetaType)
		{
			if (node)
			{
				node = BX.findParent(node, function(n)
				{
					return n.getAttribute && n.getAttribute('data-bx-planner-meta') === nodeMetaType;
				}, parentCont);
			}
			else
			{
				node = null;
			}
		}

		return node;
	},

	GetMousePos: function(e)
	{
		if (!e)
			e = window.event;

		var x = 0, y = 0;
		if (e.pageX || e.pageY)
		{
			x = e.pageX;
			y = e.pageY;
		}
		else if (e.clientX || e.clientY)
		{
			x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
			y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
		}

		return {x: x, y: y};
	},

	FormatDate: function(date)
	{
		return BX.date.format(this.DATE_FORMAT, date.getTime() / 1000);
	},

	FormatTime: function(date, seconds)
	{
		var timestamp = (date && typeof date === 'object') ? date.getTime() : date;
		return BX.date.format(seconds === true ? this.TIME_FORMAT : this.TIME_FORMAT_SHORT, timestamp / 1000);
	},

	FormatDateTime: function(date)
	{
		return BX.date.format(this.DATETIME_FORMAT, date.getTime() / 1000);
	},

	SetScaleType: function(scaleType)
	{
		this.scaleType = scaleType;
		this.scaleSize = this.GetScaleSize(scaleType);

		if (this.scaleType == '1day' && this.timelineCellWidth < 100)
		{
			this.timelineCellWidthOrig = this.timelineCellWidth;
			this.timelineCellWidth = 100;
		}
		else if(this.timelineCellWidthOrig && this.scaleType != '1day')
		{
			this.timelineCellWidth = this.timelineCellWidthOrig;
			this.timelineCellWidthOrig = false;
		}

		if (this.scaleType == '1day')
		{

			BX.addClass(this.mainContWrap, 'calendar-planner-fulldaymode');
			if (this.entriesListOuterWrap)
			{
				BX.addClass(this.entriesListOuterWrap, 'calendar-planner-no-daytitle');
			}
		}
		else
		{
			BX.removeClass(this.mainContWrap, 'calendar-planner-fulldaymode');
			if (this.entriesListOuterWrap)
			{
				BX.removeClass(this.entriesListOuterWrap, 'calendar-planner-no-daytitle');
			}
		}
	},

	//GetScaleByDuration: function(duration)
	//{
	//	var
	//		hour = 3600,
	//		scaleType = '1hour';
	//
	//	if (duration > hour * 3 && duration < hour * 24)
	//	{
	//		scaleType = '2hour';
	//	}
	//	else if (duration < hour / 2)
	//	{
	//		scaleType = '30min';
	//	}
	//	else if (duration < hour / 4)
	//	{
	//		scaleType = '15min';
	//	}
	//	else if (duration > hour * 24)
	//	{
	//		scaleType = '1day';
	//	}
	//	return scaleType;
	//},

	GetScaleSize: function(scaleType)
	{
		var
			hour = 3600,
			map = {
				'15min' : Math.round(hour / 4),
				'30min' : Math.round(hour / 2),
				'1hour' : hour,
				'2hour' : hour * 2,
				'1day' : hour * 24
			};

		return map[scaleType] || hour;
	},

	//GetScaleLimitByDate: function(date, isFromLimit)
	//{
	//	var scaleLimit;
	//	if (isFromLimit)
	//	{
	//		scaleLimit = new Date(Math.floor(date.getTime() / (this.scaleSize * 1000)) * this.scaleSize * 1000);
	//	}
	//	else
	//	{
	//		scaleLimit = new Date(Math.ceil(date.getTime() / (this.scaleSize * 1000)) * this.scaleSize * 1000);
	//	}
	//
	//	return scaleLimit;
	//},

	MapDatePos: function(accuracy)
	{
		if (!accuracy)
		{
			accuracy = this.accuracy;
		}

		var datePosMap = {};
		var posDateMap = {};

		var i, j, tsi, xi, tsj, xj, cellWidth;

		this.substeps = Math.round(this.scaleSize / accuracy);
		this.posAccuracy = this.timelineCellWidth / this.substeps;

		accuracy = accuracy * 1000;
		var scaleSize = this.scaleData[1].timestamp - this.scaleData[0].timestamp;

		for (i = 0; i < this.scaleData.length; i++)
		{
			tsi = this.scaleData[i].timestamp;
			xi = parseInt(this.scaleData[i].cell.offsetLeft);
			cellWidth = parseInt(this.scaleData[i].cell.offsetWidth);

			if (!datePosMap[tsi])
			{
				datePosMap[tsi] = xi;
			}
			posDateMap[xi] = tsi;

			for (j = 1; j <= cellWidth; j++)
			{
				tsj = tsi + Math.round((j * scaleSize / cellWidth) / accuracy) * accuracy;
				xj = xi + j;
				if (!datePosMap[tsi])
				{
					datePosMap[tsj] = xj;
				}
				posDateMap[xj] = tsj;

				if (j == cellWidth &&
					(!this.scaleData[i + 1] || this.scaleData[i + 1].dayStart))
				{
					datePosMap[xj + '_end'] = tsj;
				}
			}
		}

		return {
			datePosMap: datePosMap,
			posDateMap: posDateMap
		}
	},

	GetPosByDate: function(date)
	{
		var x = 0;
		if (date && typeof date !== 'object')
		{
			date = BX.parseDate(date);
		}

		if (date && typeof date === 'object')
		{
			var
				i, curInd = 0,
				timestamp = date.getTime();

			for (i = 0; i < this.scaleData.length; i++)
			{
				if (timestamp >= this.scaleData[i].timestamp)
				{
					curInd = i;
				}
				else
				{
					break;
				}
			}

			if (this.scaleData[curInd] && this.scaleData[curInd].cell)
			{
				x = this.scaleData[curInd].cell.offsetLeft;
				var cellWidth = this.scaleData[curInd].cell.offsetWidth, deltaTs = Math.round((timestamp - this.scaleData[curInd].timestamp) / 1000);

				if (deltaTs > 0)
				{
					x += Math.round(deltaTs * 10 / this.scaleSize * cellWidth) / 10;
				}
			}
		}

		return x;
	},

	GetDateByPos: function(x, end, posDateMap)
	{
		if (!posDateMap)
		{
			posDateMap = this.posDateMap;
		}
		var
			date,
			timestamp = (end && posDateMap[x + '_end']) ? posDateMap[x + '_end'] : posDateMap[x];

		if (!timestamp)
		{
			x = Math.round(x);
			timestamp = (end && posDateMap[x + '_end']) ?  posDateMap[x + '_end'] : posDateMap[x];
		}

		if (timestamp)
		{
			date = new Date(timestamp);
		}

		return date;
	},

	RoundPos: function(x)
	{
		return Math.round(parseFloat(x));
		//return Math.round(Math.round(parseInt(x) / this.posAccuracy) * this.posAccuracy * 10) / 10;
	},

	ShowMoreUsers: function()
	{
		this.minEntryRows = this.maxEntryRows;
		this.ClearAccessibilityData();
		this.UpdateData({accessibility: this.accessibility, entries: this.entries});
	},

	AdjustPlannerHeight: function()
	{
		var
			newHeight = this.entriesListWrap.offsetHeight + this.entriesListWrap.offsetTop + 30,
			currentHeight = parseInt(this.outerWrap.style.height) || this.height;

		if (this.compactMode && currentHeight < newHeight || !this.compactMode)
		{
			this.ResizePlannerHeight(newHeight, Math.abs(newHeight - currentHeight) > 10);
		}
	},

	ResizePlannerHeight: function(height, animation)
	{
		this.height = height;

		if (animation)
		{
			// Stop animation befor starting another one
			if(this.resizeAnimation)
			{
				this.resizeAnimation.stop();
				this.resizeAnimation = null;
			}

			var
				startHeight = parseInt(this.outerWrap.style.height),
				_this = this;

			this.resizeAnimation = new BX.easing({
				duration: 800,
				start: {height: startHeight},
				finish: {height: height},
				transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),

				step: function (state)
				{
					_this.ResizePlannerHeight(state.height, false);
				},

				complete: BX.proxy(function ()
				{
					this.resizeAnimation = null;
				}, this)
			});

			this.resizeAnimation.animate();
		}
		else
		{
			this.outerWrap.style.height = height + 'px';
			this.mainContWrap.style.height = height + 'px';
			this.timelineFixedWrap.style.height = height + 'px';
			var timelineDataContHeight = this.entriesListWrap.offsetHeight + 3;
			this.timelineDataCont.style.height = timelineDataContHeight + 'px';
			this.selector.wrap.style.height = (timelineDataContHeight + 10) + 'px';
			this.entriesListOuterWrap.style.height = height + 'px';

			if (this.proposeTimeButton && this.proposeTimeButton.style.display != "none")
			{
				this.proposeTimeButton.style.top = (this.timelineDataCont.offsetTop + timelineDataContHeight / 2 - 16) + "px";
			}
		}
	},

	ResizePlannerWidth: function(width, animation)
	{
		if (!animation && this.outerWrap && this.mainContWrap)
		{
			this.outerWrap.style.width = width + 'px';
			var entriesListWidth = this.compactMode ? 0 : this.entriesListWidth;
			this.mainContWrap.style.width = width + 'px';
			this.entriesListOuterWrap.style.width = entriesListWidth + 'px';
		}
	},

	ExpandFromCompactMode: function()
	{
		this.readonly = false;
		this.compactMode = false;
		this.showTimelineDayTitle = true;
		BX.removeClass(this.mainContWrap, 'calendar-planner-readonly');
		BX.removeClass(this.mainContWrap, 'calendar-planner-compact');
		this.entriesListOuterWrap.style.display = '';

		if (this.scaleDateFrom && this.scaleDateFrom.getTime)
			this.scaleDateFrom = new Date(this.scaleDateFrom.getTime() - this.dayLength * this.scaleLimitOffsetLeft /* days before */);

		if (this.scaleDateTo && this.scaleDateTo.getTime)
			this.scaleDateTo = new Date(this.scaleDateTo.getTime() + this.dayLength * this.scaleLimitOffsetRight /* days after */);

		this.RebuildPlanner();

		this.FocusSelector(false, 300);
	},

	FocusSelector: function(animation, timeout, alignCenter)
	{
		var _this = this;
		alignCenter = alignCenter === true;

		if (this.focusSelectorTimeout)
			this.focusSelectorTimeout = !!clearTimeout(this.focusSelectorTimeout);

		if (this.globalAnimation === false)
			animation = false;

		if (timeout)
		{
			this.focusSelectorTimeout = setTimeout(function(){_this.FocusSelector(animation, false, alignCenter);}, timeout);
		}
		else
		{
			var
				selectorLeft = parseInt(this.selector.wrap.style.left),
				selectorWidth = parseInt(this.selector.wrap.style.width),
				screenDelta = 10,
				viewWidth = this.timelineFixedWrap.offsetWidth,
				viewLeft = this.timelineFixedWrap.scrollLeft,
				viewRight = viewLeft + viewWidth,
				newScrollLeft = viewLeft;

			if (selectorLeft < viewLeft + screenDelta ||
				selectorLeft > viewRight - screenDelta
				|| alignCenter
			)
			{
				// Selector is smaller than view - we puting it in the middle of the view
				if (selectorWidth <= viewWidth)
				{
					newScrollLeft = Math.max(Math.round(selectorLeft - ((viewWidth - selectorWidth) / 2 )), screenDelta);

				}
				else // Selector is wider, so we adjust by left side
				{
					newScrollLeft = Math.max(Math.round(selectorLeft - screenDelta), screenDelta);
				}
			}

			if (newScrollLeft != viewLeft)
			{
				if (animation === false)
				{
					this.timelineFixedWrap.scrollLeft = newScrollLeft;
				}
				else
				{
					new BX.easing({
						duration: 300,
						start: {scrollLeft: this.timelineFixedWrap.scrollLeft},
						finish: {scrollLeft: newScrollLeft},
						transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
						step: function (state)
						{
							_this.timelineFixedWrap.scrollLeft = state.scrollLeft;
						},
						complete: function () {}
					}).animate();
				}
			}
		}

	},

	ExpandTimeline: function(direction, loadedDataFrom, loadedDataTo, focusSelector)
	{
		if (this.loadDataLock)
		{
			this.lastExpandparams = {
				direction: direction,
				loadedDataFrom: loadedDataFrom,
				loadedDataTo: loadedDataTo,
				focusSelector: focusSelector
			};
			return;
		}
		this.lastExpandparams = false;

		var
			loadedTimelineSize,
			leftOffset = 3,
			rightOffset = 3,
			scrollLeft,
			_this = this;

		if (!loadedDataFrom)
			loadedDataFrom = this.loadedDataFrom || this.scaleDateFrom;
		if (!loadedDataTo)
			loadedDataTo = this.loadedDataTo || this.scaleDateTo;

		if (direction == 'left')
		{
			var oldScaleDateFrom = new Date(this.scaleDateFrom.getTime());
			this.scaleDateFrom = loadedDataFrom = new Date(loadedDataFrom - this.dayLength  * leftOffset);

			loadedTimelineSize = (this.scaleDateTo.getTime() - this.scaleDateFrom.getTime()) / this.dayLength;
			if (loadedTimelineSize > this.maxTimelineSize)
			{
				this.scaleDateTo = new Date(this.scaleDateFrom.getTime() + this.dayLength  * this.maxTimelineSize);
				this.loadedDataFrom = this.scaleDateFrom;
				this.loadedDataTo = this.scaleDateTo;

				loadedDataTo = this.loadedDataTo;
				this.limitScaleSizeMode = true;
			}

			this.RebuildPlanner();
			scrollLeft = this.GetPosByDate(oldScaleDateFrom);
		}
		else if (direction == 'right')
		{
			var oldDateTo = this.scaleDateTo;

			scrollLeft = _this.timelineFixedWrap.scrollLeft;
			this.scaleDateTo = loadedDataTo = new Date(loadedDataTo.getTime() + this.dayLength * rightOffset);

			loadedTimelineSize = (this.scaleDateTo.getTime() - this.scaleDateFrom.getTime()) / this.dayLength;
			if (loadedTimelineSize > this.maxTimelineSize)
			{
				this.scaleDateFrom = new Date(this.scaleDateTo.getTime() - this.dayLength  * this.maxTimelineSize);
				this.loadedDataFrom = this.scaleDateFrom;
				this.loadedDataTo = this.scaleDateTo;
				loadedDataFrom = this.loadedDataFrom;

				scrollLeft = this.GetPosByDate(oldDateTo) - this.timelineFixedWrap.offsetWidth;
				setTimeout(function(){
					_this.timelineFixedWrap.scrollLeft = _this.GetPosByDate(oldDateTo) - _this.timelineFixedWrap.offsetWidth;
				}, 1);

				this.limitScaleSizeMode = true;
			}

			this.scaleDateTo = loadedDataTo;
			this.RebuildPlanner();
		}
		else
		{
			loadedDataFrom = new Date(loadedDataFrom - this.dayLength  * leftOffset);
			loadedDataTo = new Date(loadedDataTo.getTime() + this.dayLength * rightOffset);

			this.scaleDateFrom = loadedDataFrom;
			this.scaleDateTo = loadedDataTo;

			this.RebuildPlanner();
		}

		if (scrollLeft !== undefined)
			_this.timelineFixedWrap.scrollLeft = scrollLeft;

		var i, entry, entrieIds = [];

		if (!BX.type.isArray(this.entries))
			this.entries = [];

		for (i = 0; i < this.entries.length; i++)
		{
			entry = this.entries[i];
			entrieIds.push(entry.id);
		}

		this.loadDataLock = true;
		BX.onCustomEvent('OnCalendarPlannerScaleChanged', [{
			from: this.FormatDate(loadedDataFrom),
			to: this.FormatDate(loadedDataTo),
			entrieIds: entrieIds,
			entries: this.entries,
			focusSelector: focusSelector === true
		}]);
	},

	GelTimelineScrollOffset: function()
	{
		return 10;
	},

	DoUpdate: function(params)
	{
		if (this.id === params.plannerId)
		{
			var rebuild = false;
			if (params.selector && params.selector.fullDay)
			{
				this.SetFullDayMode(params.selector.fullDay);
			}

			if (params.config)
			{
				if (this.fullDayMode && params.config.changeFromFullDay)
				{
					params.config.scaleType = params.config.changeFromFullDay.scaleType;
					params.config.timelineCellWidth = params.config.changeFromFullDay.timelineCellWidth;
					delete params.config.changeFromFullDay;
				}

				// Check if we should rebuild scale
				if (params.config.scaleDateFrom && params.config.scaleDateFrom !== this.scaleDateFrom)
					rebuild = true;
				if (!rebuild && params.config.scaleDateTo && params.config.scaleDateTo !== this.scaleDateTo)
					rebuild = true;
				if (!rebuild && params.config.scaleType && params.config.scaleType !== this.scaleType)
					rebuild = true;
				if (params.config.shownScaleTimeFrom && params.config.shownScaleTimeFrom !== this.shownScaleTimeFrom)
				{
					this.shownScaleTimeFrom = params.config.shownScaleTimeFrom;
					rebuild = true;
				}
				if (params.config.shownScaleTimeTo && params.config.shownScaleTimeTo !== this.shownScaleTimeTo)
				{
					this.shownScaleTimeTo = params.config.shownScaleTimeTo;
					rebuild = true;
				}

				this.SetConfig(params.config);
			}

			if (!this.shown && params.show)
			{
				this.Show(true);
			}
			else if (rebuild)
			{
				this.RebuildPlanner({updateSelector: false});
			}

			if (params.hide && this.shown)
			{
				this.Hide(params.hideAnimation !== false);
			}

			if (this.shown)
			{
				if (params.data !== undefined && params.data !== false)
				{
					this.ClearAccessibilityData();
					this.UpdateData(params.data);
					this.SetLoadedDataLimits(params.loadedDataFrom, params.loadedDataTo);
				}

				if (params.selector !== undefined &&
					params.selector.from && params.selector.to)
				{
					params.selector.focus = params.focusSelector === true;
					params.selector.updateScaleType = false;

					// this.limitScaleSizeMode - is true if timeline scrolled by mouse or
					// mousewheel and we load some data from deep future or from past
					// (so we should't expand timeline in this case)
					if (params.selector.to.getTime() > this.loadedDataTo.getTime()
						&& !this.limitScaleSizeMode)
					{
						this.ExpandTimeline('right', false, params.selector.to, true);
					}
					else if (params.selector.from.getTime() < this.loadedDataFrom.getTime()
						&& !this.limitScaleSizeMode)
					{
						this.ExpandTimeline('left', params.selector.from, false, true);
					}
					else
					{
						if (!this.readonly)
						{
							this.scaleDateFrom = this.loadedDataFrom;
							this.scaleDateTo = this.loadedDataTo;
						}

						this.RebuildPlanner({
							updateSelector: true,
							selectorParams: params.selector
						});
					}
				}

				if (!this.compactMode && this.loadedDataTo !== this.scaleDateTo)
				{
					this.CheckTimelineScroll();
				}
			}

			if (params.params && params.params.callback)
			{
				params.params.callback();
			}

			if (this.expandTimeLineTimeout)
				this.expandTimeLineTimeout = !!clearTimeout(this.expandTimeLineTimeout);

			var _this = this;
			this.expandTimeLineTimeout = setTimeout(
				function()
				{
					_this.loadDataLock = false;
					if (_this.lastExpandparams)
					{
						var p = _this.lastExpandparams;
						_this.ExpandTimeline(p.direction, p.loadedDataFrom, p.loadedDataTo, p.focusSelector);
					}
				},
				this.expandTimelineDelay
			);

			// We reset value to the false to allow user to extend timeline manually
			this.limitScaleSizeMode = false;

			BX.onCustomEvent('OnCalendarPlannerUpdated', [this, {
				plannerId: this.id,
				entries: this.entries
			}]);
		}
	},

	DoExpand: function(params)
	{
		if (this.id == params.plannerId)
		{
			if (this.compactMode)
			{
				if (params.config)
				{
					this.SetConfig(params.config);
				}

				this.ExpandFromCompactMode();
			}
		}
	},

	DoSetConfig: function(params)
	{
		if (this.id == params.plannerId && params.config)
		{
			this.SetConfig(params.config);
		}
	},

	DoResize: function(params)
	{
		if (this.id == params.plannerId)
		{
			var _this = this;

			if (params.width)
				params.width = parseInt(params.width) || this.width;
			params.width = Math.max(params.width, this.minWidth);

			this.width = params.width;
			this.AdjustCellWidth();

			this.ResizePlannerWidth(params.width, false);

			if (this.resizeRebuildTimeout)
				this.resizeRebuildTimeout = clearTimeout(this.resizeRebuildTimeout);

			this.resizeRebuildTimeout = setTimeout(function()
			{
				_this.RebuildPlanner();
			}, 200);
		}
	},

	DoUninstall: function(params)
	{
		if (params && this.id == params.plannerId)
		{
			BX.cleanNode(this.outerWrap, 1);
			BX.removeCustomEvent('OnCalendarPlannerDoUpdate', BX.proxy(this.DoUpdate, this));
			BX.removeCustomEvent('OnCalendarPlannerDoExpand', BX.proxy(this.DoExpand, this));
			BX.removeCustomEvent('OnCalendarPlannerDoResize', BX.proxy(this.DoResize, this));
			BX.removeCustomEvent('OnCalendarPlannerDoSetConfig', BX.proxy(this.DoSetConfig, this));
			BX.removeCustomEvent('OnCalendarPlannerDoUninstall', BX.proxy(this.DoUninstall, this));

			if (this.settingsPopup)
			{
				this.settingsPopup.close();
			}
		}
	},

	ProposeTime: function(params)
	{
		if (!params || typeof params !== 'object' || params.target)
			params = {};

		var
			_this = this,
			curTimestamp, curDate,
			duration = Math.round((this.currentSelectorDateTo - this.currentSelectorDateFrom) / 1000) * 1000,
			data = [], k, i;

		if (this.fullDayMode)
			duration += this.dayLength;

		curTimestamp = Math.round(this.currentSelectorDateFrom.getTime() / (this.accuracy * 1000)) * this.accuracy * 1000;
		curDate = new Date(curTimestamp);
		curDate.setSeconds(0,0);
		curTimestamp = curDate.getTime();

		for (k in this.accessibility)
		{
			if (this.accessibility.hasOwnProperty(k) && this.accessibility[k] && this.accessibility[k].length > 0)
			{
				for (i = 0; i < this.accessibility[k].length; i++)
				{
					if (this.accessibility[k][i].toTimestampReal >= curTimestamp)
						data.push(this.HandleAccessibilityEntry(this.accessibility[k][i]));
				}
			}
		}
		data.sort(function(a, b){return a.fromTimestamp - b.fromTimestamp});

		var
			ts = curTimestamp,
			checkRes,
			dateFrom, dateTo, timeTo, timeFrom;

		while (true)
		{
			dateFrom = new Date(ts);
			dateTo = new Date(ts + duration);

			if (this.scaleType !== '1day')
			{
				timeFrom = parseInt(dateFrom.getHours()) + dateFrom.getMinutes() / 60;
				timeTo = parseInt(dateTo.getHours()) + dateTo.getMinutes() / 60;
				if (timeFrom <= this.shownScaleTimeFrom)
				{
					dateFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
					ts = dateFrom.getTime();
					dateTo = new Date(ts + duration);
				}

				if (timeTo >= this.shownScaleTimeTo)
				{
					dateFrom = new Date(ts + this.dayLength - 1000); // next day
					dateFrom.setHours(this.shownScaleTimeFrom, 0, 0, 0);
					ts = dateFrom.getTime();
					dateTo = new Date(ts + duration);
				}
			}

			if (this.fullDayMode)
			{
				dateFrom.setHours(0, 0, 0, 0);
				dateTo.setHours(0, 0, 0, 0);
			}

			checkRes = this.checkTimePeriod(dateFrom, dateTo, data);
			if (checkRes === true)
			{
				if (dateTo.getTime() > this.loadedDataTo.getTime())
				{
					if ((dateTo.getTime() - this.loadedDataTo.getTime()) > this.proposeTimeLimit * this.dayLength
						||
						params.checkedFuture === true)
					{
						this.ShowNoResultNotification();
					}
					else if (params.checkedFuture !== true)
					{
						var scrollLeft = this.timelineFixedWrap.scrollLeft;
						var loadedDataTo = new Date(this.loadedDataTo.getTime() + this.dayLength * this.proposeTimeLimit);
						this.scaleDateTo = loadedDataTo;
						this.RebuildPlanner();
						this.timelineFixedWrap.scrollLeft = scrollLeft;

						var entry, entrieIds = [];
						for (i = 0; i < this.entries.length; i++)
						{
							entry = this.entries[i];
							entrieIds.push(entry.id);
						}

						BX.onCustomEvent('OnCalendarPlannerScaleChanged', [{
							from: this.FormatDate(this.loadedDataFrom),
							to: this.FormatDate(loadedDataTo),
							entrieIds: entrieIds,
							entries: this.entries,
							focusSelector: true,
							params: {
								callback: function(){_this.ProposeTime({checkedFuture: true});}
							}
						}]);
					}
				}
				else
				{
					if (this.fullDayMode)
						dateTo = new Date(dateTo.getTime() - this.dayLength);

					//if (this.currentSelectorFullDay)
					//	duration += this.dayLength;

					this.UpdateSelector({
						from: dateFrom,
						to:dateTo,
						updateScaleType:false,
						updateScaleLimits:true,
						animation: true,
						focus: true
					});

					BX.onCustomEvent('OnCalendarPlannerSelectorChanged', [{
						plannerId: this.id,
						dateFrom: dateFrom,
						dateTo: dateTo,
						fullDay: this.fullDayMode
					}]);
				}
				break;
			}
			else if (checkRes && checkRes.toTimestampReal)
			{
				ts = checkRes.toTimestampReal;
				if (this.fullDayMode)
				{
					var dt = new Date(ts + this.dayLength - 1000); // next day
					dt.setHours(0, 0, 0, 0);
					ts = dt.getTime();
				}
			}
		}

		//this.HideSelectorWarningPopup();
	},

	checkTimePeriod: function(fromDate, toDate, data)
	{
		var
			result = true, entry,
			fromTimestamp = fromDate.getTime(),
			toTimestamp = toDate.getTime(),
			cacheKey = fromTimestamp + '_' + toTimestamp,
			accuracy = 60 * 1000, // 1min
			item, i;

		if (BX.type.isArray(data))
		{
			for (i = 0; i < data.length; i++)
			{
				item = data[i];
				if (item.type && item.type === 'hr')
					continue;

				if ((item.fromTimestamp + accuracy) <= toTimestamp && ((item.toTimestampReal || item.toTimestamp) - accuracy) >= fromTimestamp)
				{
					result = item;
					break;
				}
			}
		}
		else if (BX.type.isArray(this.entries))
		{
			var
				entriesAccessibleIndex = {},
				selectorAccuracy = this.selectorAccuracy * 1000,
				entryId;

			if (this.checkTimeCache[cacheKey] !== undefined)
			{
				result = this.checkTimeCache[cacheKey];
			}
			else
			{
				for (entryId in this.accessibility)
				{
					if (this.accessibility.hasOwnProperty(entryId))
					{
						if (this.selectMode)
						{
							entry = this.entries.find(function(el){return parseInt(el.id) === parseInt(entryId);});
							if (entry && !entry.selected)
							{
								continue;
							}
						}

						entriesAccessibleIndex[entryId] = true;
						if (BX.type.isArray(this.accessibility[entryId]))
						{
							for (i = 0; i < this.accessibility[entryId].length; i++)
							{
								item = this.accessibility[entryId][i];
								if (item.type && item.type === 'hr')
								{
									continue;
								}

								if ((item.fromTimestamp + selectorAccuracy) <= toTimestamp && ((item.toTimestampReal || item.toTimestamp) - selectorAccuracy) >= fromTimestamp)
								{
									entriesAccessibleIndex[entryId] = false;
									result = item;
									break;
								}
							}
						}
					}
				}

				for (i = 0; i < this.entries.length; i++)
				{
					if (entriesAccessibleIndex[this.entries[i].id] !== undefined)
					{
						this.entries[i].currentStatus = !!entriesAccessibleIndex[this.entries[i].id];
					}
					else
					{
						this.entries[i].currentStatus = true;
					}
				}

				this.checkTimeCache[cacheKey] = result;
			}
		}

		return result;
	},

	clearCacheTime: function()
	{
		this.checkTimeCache = {};
	},

	checkEntryTimePeriod: function(entry, fromDate, toDate)
	{
		var data = [], i;
		if (entry && entry.id && BX.type.isArray(this.accessibility[entry.id]))
		{
			for (i = 0; i < this.accessibility[entry.id].length; i++)
			{
				data.push(this.HandleAccessibilityEntry(this.accessibility[entry.id][i]));
			}
		}
		return this.checkTimePeriod(fromDate, toDate, data) === true;
	},

	ShowSettingsPopup: function()
	{
		var
			_this = this,
			i, scales = ['15min', '30min', '1hour','2hour','1day'],
			settingsDialogCont = BX.create('DIV', {props: {className: 'calendar-planner-settings-popup'}}),
			scaleRow = settingsDialogCont.appendChild(BX.create('DIV', {props: {className: 'calendar-planner-settings-row'}, html: '<i>' + BX.message('EC_PL_SETTINGS_SCALE') + ':</i>'})),
			scaleWrap = scaleRow.appendChild(BX.create('span', {props: {className: 'calendar-planner-option-container'}}));

		if (this.fullDayMode)
		{
			scaleRow.title = BX.message('EC_PL_SETTINGS_SCALE_READONLY_TITLE');
			BX.addClass(scaleRow, 'calendar-planner-option-container-disabled');
		}

		for (i = 0; i < scales.length; i++)
		{
			scaleWrap.appendChild(BX.create('span', {
				props: {className: 'calendar-planner-option-tab' + (scales[i] === this.scaleType ? ' calendar-planner-option-tab-active' : '')},
				attrs: {'data-bx-planner-scale': scales[i]},
				text: BX.message('EC_PL_SETTINGS_SCALE_' + scales[i].toUpperCase())
			}));
		}

		// Create and show settings popup
		var popup = BX.PopupWindowManager.create(this.id + "-settings-popup", this.settingsButton,
			{
				autoHide: true,
				closeByEsc: true,
				offsetTop: -1,
				offsetLeft: 7,
				lightShadow: true,
				content: settingsDialogCont,
				zIndex: 4000,
				angle: {postion: 'top'}
			});
		popup.show(true);

		// handlers to change scale

		BX.bind(scaleWrap, 'click', BX.proxy(function(e)
		{
			if (!this.fullDayMode)
			{
				var
					nodeTarget = e.target || e.srcElement,
					scale = nodeTarget && nodeTarget.getAttribute && nodeTarget.getAttribute('data-bx-planner-scale');

				if (scale)
				{
					this.ChangeScaleType(scale);
					popup.close();
				}
			}
		}, this));


		function destroyPopup()
		{
			if(popup && popup.destroy)
			{
				BX.removeCustomEvent(popup, 'onPopupClose', destroyPopup);
				popup.destroy();
				popup = null;
				_this.settingsPopup = null;
			}
		}
		BX.addCustomEvent(popup, 'onPopupClose', destroyPopup);
		this.settingsPopup = popup;
	},

	ChangeScaleType: function(scaleType)
	{
		if (scaleType !== this.scaleType)
		{
			this.SetScaleType(scaleType);
			this.RebuildPlanner();
			this.FocusSelector(true, 300);
		}
	},

	SetFullDayMode: function(fullDayMode)
	{
		this.fullDayMode = fullDayMode;
	},

	PreventSelection: function(node)
	{
		node.ondrag = BX.False;
		node.ondragstart = BX.False;
		node.onselectstart = BX.False;
	},

	ShowNoResultNotification: function()
	{
		alert(BX.message('EC_PL_PROPOSE_NO_RESULT'));
	},

	HandleRecursion: function(params)
	{
		if (!params.instances)
			params.instances = [];

		var instance = new Date();
		instance.setFullYear(2016, 6, 2);
		instance.setHours(0, 0, 0, 0);
		params.instances.push({date: instance});

		instance = new Date();
		instance.setFullYear(2016, 6, 3);
		instance.setHours(0, 0, 0, 0);
		params.instances.push({date: instance});

		var i, selHtml;
		for (i = 0; i < params.instances.length; i++)
		{
			selHtml = this.buildSelector();
			params.instances[i].selector = selHtml.wrap;
			params.instances[i].selectorTitle = selHtml.selectorTitle;

			var dateFrom = new Date(params.instances[i].date.getTime());
			dateFrom.setHours(12, 0, 0, 0);
			var dateTo = new Date(params.instances[i].date.getTime());
			dateTo.setHours(13, 0, 0, 0);

			this.ShowSelector(
				{
					selector: params.instances[i].selector,
					dateFrom: dateFrom,
					dateTo: dateTo,
					focus: false,
					animation: false
				}
			);
		}

		this.currentSelectorInstances = params.instances;
	},

	ShowProposeControl: function()
	{
		if (!this.proposeTimeButton)
		{
			this.proposeTimeButton = this.mainContWrap.appendChild(BX.create("DIV", {
				props: {className: 'calendar-planner-time-arrow-right'},
				html: '<span class="calendar-planner-time-arrow-right-text">' + BX.message('EC_PL_PROPOSE') + '</span><span class="calendar-planner-time-arrow-right-item"></span>'
			}));
			BX.bind(this.proposeTimeButton, 'click', BX.proxy(this.ProposeTime, this));
		}
		this.proposeTimeButton.style.display = "block";
		this.proposeTimeButton.style.top = (this.timelineDataCont.offsetTop + this.timelineDataCont.offsetHeight / 2 - 16) + "px";
	},

	HideProposeControl: function()
	{
		if (this.proposeTimeButton)
			this.proposeTimeButton.style.display = "none";
	},

	mouseMoveHandler: function(e)
	{
		var
			i, nodes,
			entryUid, parentTarget,
			prevEntry,
			mainContWrap = this.mainContWrap,
			target = e.target || e.srcElement;

		entryUid = target.getAttribute('data-bx-planner-entry');
		if (!entryUid)
		{
			parentTarget = BX.findParent(target,
				function(node)
				{
					if (node == mainContWrap ||
						node.getAttribute && node.getAttribute('data-bx-planner-entry')
					)
					{
						return true;
					}
				},
				mainContWrap
			);

			if (parentTarget)
			{
				entryUid = target.getAttribute('data-bx-planner-entry')
			}
			else
			{
				BX.removeClass(this.hoverRow, 'show');
				nodes = this.selector.controlWrap.querySelectorAll('.calendar-planner-selector-control-row.hover');
				for (i = 0; i < nodes.length; i++)
				{
					BX.removeClass(nodes[i], 'hover');
				}
				prevEntry = this.getEntryByUniqueId(this.howerEntryId);
				if (prevEntry && prevEntry.selectWrap)
				{
					prevEntry.selectWrap.style.opacity = 1;
				}
			}
		}

		if (entryUid)
		{
			if (this.howerEntryId !== entryUid)
			{
				this.howerEntryId = entryUid;
				var entry = this.getEntryByUniqueId(entryUid);
				if (entry)
				{
					var top = parseInt(entry.dataRowWrap.offsetTop);
					BX.addClass(this.hoverRow, 'show');
					this.hoverRow.style.top = (top + 36) + 'px';
					this.hoverRow.style.width = (parseInt(this.mainContWrap.offsetWidth) + 5) + 'px';

					if (entry.selectorControlWrap)
					{
						nodes = this.selector.controlWrap.querySelectorAll('.calendar-planner-selector-control-row.hover');
						for (i = 0; i < nodes.length; i++)
						{
							BX.removeClass(nodes[i], 'hover');
						}
						BX.addClass(entry.selectorControlWrap, 'hover');
					}
				}
			}
		}
	}
};

	window.CalendarPlanner = CalendarPlanner;

	if (!Array.prototype.find) {
		Object.defineProperty(Array.prototype, 'find', {
			value: function(predicate) {
				if (this == null) {
					throw new TypeError('"this" is null or not defined');
				}
				var o = Object(this);
				var len = o.length >>> 0;
				if (typeof predicate !== 'function') {
					throw new TypeError('predicate must be a function');
				}
				var thisArg = arguments[1];
				var k = 0;
				while (k < len) {
					var kValue = o[k];
					if (predicate.call(thisArg, kValue, k, o)) {
						return kValue;
					}
					k++;
				}
				return undefined;
			}
		});
	}
})(window);
