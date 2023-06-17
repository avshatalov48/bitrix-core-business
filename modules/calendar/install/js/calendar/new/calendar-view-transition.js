;(function(window) {

	function ViewTransition(calendar)
	{
		this.calendar = calendar;
		this.util = calendar.util;
		this.WEEK_TOP_MIN_HEIGHT = 21;

		this.defaultBg = {r:255, g:255, b:255};
		this.currentBg = {r:250, g:250, b:250};
	}

	ViewTransition.prototype = {
		transit: function (params)
		{
			var
				currentView = params.currentView,
				newView = params.newView,
				currentViewName = params.currentView.getName(),
				newViewName = params.newView.getName();

			this.currentViewDate = params.currentViewDate;
			this.newViewDate = params.newViewDate;

			//month > week
			if (currentViewName == 'month' && newViewName == 'week')
			{
				this.fromMonthToWeek(currentView, newView);
			}
			//month > day
			else if (currentViewName == 'month' && newViewName == 'day')
			{
				this.fromMonthToDay(currentView, newView);
			}
			//month > list
			else if (currentViewName == 'month' && newViewName == 'list')
			{
				this.fromMonthToList(currentView, newView);
			}
			//week > month
			else if (currentViewName == 'week' && newViewName == 'month')
			{
				this.fromWeekToMonth(currentView, newView);
			}
			//week > day
			else if (currentViewName == 'week' && newViewName == 'day')
			{
				this.fromWeekToDay(currentView, newView);
			}
			//week > list
			else if (currentViewName == 'week' && newViewName == 'list')
			{
				this.fromWeekToList(currentView, newView);
			}
			//day > week
			else if (currentViewName == 'day' && newViewName == 'week')
			{
				this.fromDayToWeek(currentView, newView);
			}
			//day > month
			else if (currentViewName == 'day' && newViewName == 'month')
			{
				this.fromDayToMonth(currentView, newView);
			}
			//day > list
			else if (currentViewName == 'day' && newViewName == 'list')
			{
				this.fromDayToList(currentView, newView);
			}
			// list > month
			else if (currentViewName == 'list' && newViewName == 'month')
			{
				this.fromListToMonth(currentView, newView);
			}
			// list > week
			else if (currentViewName == 'list' && newViewName == 'week')
			{
				this.fromListToWeek(currentView, newView);
			}
			// list > day
			else if (currentViewName == 'list' && newViewName == 'day')
			{
				this.fromListToDay(currentView, newView);
			}

			this.calendar.currentViewName = newView.getName();

			if (newView.switchNode)
			{
				BX.addClass(newView.switchNode, 'calendar-view-switcher-list-item-active');
			}

			if (currentView.switchNode)
			{
				BX.removeClass(currentView.switchNode, 'calendar-view-switcher-list-item-active');
			}
		},

		fromMonthToWeek: function (monthView, weekView)
		{
			var
				_this = this,
				red = 255, green = 255, blue = 255,
				curRed = 234, curGreen = 249, curBlue = 254,
				duration1 = 100,
				duration2 = 310,
				duration3 = 100;

			weekView.viewCont.style.opacity = 0;
			weekView.show();

			BX.removeClass(weekView.grid, 'calendar-events-holder-show');
			BX.removeClass(weekView.fullDayEventsCont, 'calendar-events-holder-show');
			BX.removeClass(monthView.gridMonthContainer, "calendar-events-holder-show");

			var
				topHolderWeekHeight = parseInt(weekView.fullDayEventsCont.style.height) || this.WEEK_TOP_MIN_HEIGHT,
				weekViewOffsets = BX.pos(weekView.viewCont),
				monthRowsLength = monthView.monthRows.length,
				newViewDateCode = this.util.getDayCode(this.newViewDate),
				monthRowCurrent = Math.ceil((monthView.dayIndex[newViewDateCode] + 1) / monthView.dayCount) - 1;

			if (!monthView.monthRows[monthRowCurrent])
				monthRowCurrent = 0;

			var
				nodes = monthView.monthRows[monthRowCurrent].querySelectorAll('.calendar-grid-cell-inner');

			weekView.viewCont.style.display = "block";
			weekView.viewCont.style.position = "absolute";
			weekView.viewCont.style.top = 0;
			weekView.viewCont.style.left = 0;
			weekView.viewCont.style.width = weekViewOffsets.width + "px";
			weekView.viewCont.style.height = weekViewOffsets.height + "px";
			weekView.viewCont.style.zIndex = 100;
			monthView.gridWrap.style.overflow = "hidden";
			monthView.gridWrap.style.height = BX.pos(weekView.gridWrap).height + "px";
			weekView.outerGrid.style.paddingLeft = 0;
			weekView.fullDayEventsCont.style.height = 0;
			weekView.titleCont.style.paddingLeft = 0;

			BX.addClass(monthView.viewCont, "calendar-animate-mod");

			this.firstStage = new BX.easing({
				duration: duration1,
				start: {
					red: red,
					green: green,
					blue: blue
				},
				finish: {
					red: curRed,
					green: curGreen,
					blue: curBlue
				},
				step: function (state)
				{
					monthView.monthRows[monthRowCurrent].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
					for (var i = 0; i < nodes.length; i++)
					{
						nodes[i].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
					}
				},
				complete: function ()
				{
					setTimeout(function ()
					{
						BX.addClass(monthView.viewCont, "calendar-change-animate-month-to-week");
						BX.removeClass(monthView.gridMonthContainer, "calendar-events-holder-show");

						for (var i = 0; i < monthRowsLength; i++)
						{
							if (i != monthRowCurrent)
							{
								monthView.monthRows[i].style.height = 0;
								if ((i + 1) < monthRowCurrent)
								{
									monthView.monthRows[i].style.borderBottom = "none";
								}
							}
							else
							{
								monthView.monthRows[i].style.height = BX.pos(weekView.outerGrid).height + "px"
							}
						}

						setTimeout(function ()
						{
							_this.secontStage.animate();
						}, 50);
					}, 100)
				}
			});

			this.secontStage = new BX.easing({
				duration: duration2,
				start: {
					fadeShow: 0,
					fadeHide: 100
				},
				finish: {
					fadeShow: 100,
					fadeHide: 0
				},
				step: function (state)
				{
					monthView.viewCont.style.opacity = state.fadeHide / 100;
					weekView.viewCont.style.opacity = state.fadeShow / 100;
				},
				complete: BX.delegate(function ()
				{
					this.thirdStage.animate();
					BX.addClass(weekView.grid, 'calendar-events-holder-show');
					BX.addClass(weekView.fullDayEventsCont, 'calendar-events-holder-show');
				}, this)
			});

			this.thirdStage = new BX.easing({
				duration: duration3,
				start: {
					paddingLeft: 0,
					topHolderHeight: 0
				},
				finish: {
					paddingLeft: 43,
					topHolderHeight: topHolderWeekHeight
				},
				step: function (state)
				{
					weekView.outerGrid.style.paddingLeft = state.paddingLeft + "px";
					weekView.titleCont.style.paddingLeft = state.paddingLeft + "px";
					weekView.fullDayEventsCont.style.height = state.topHolderHeight + 'px';
				},
				complete: BX.delegate(function ()
				{

					weekView.titleCont.style.paddingLeft = '';
					monthView.gridMonthContainer.style.paddingLeft = "";
					monthView.monthRows[monthRowCurrent].style.background = "";
					BX.removeClass(monthView.viewCont, "calendar-change-animate-month-to-week");
					this.adjustViewAnimate(monthView, weekView);
				}, this)
			});

			this.firstStage.animate();
		},

		fromMonthToDay: function (monthView, dayView)
		{
			//BX.removeClass(this.calendar.mainCont, "calendar-main-container-small-calendar");
			monthView.viewCont.style.width = monthView.viewCont.offsetWidth + "px";
			dayView.viewCont.style.opacity = 0;
			dayView.viewCont.style.zIndex = 110;

			dayView.show();
			this.calendar.viewsCont.style.height = dayView.viewCont.offsetHeight + 'px';

			monthView.viewCont.style.position = "absolute";
			monthView.viewCont.style.top = 0;
			monthView.viewCont.style.left = 0;

			var dayCode = this.util.getDayCode(this.newViewDate);
			if (monthView.dayIndex[dayCode] !== undefined && monthView.days[monthView.dayIndex[dayCode]])
			{
				var day = monthView.days[monthView.dayIndex[dayCode]];

				var
					red = 255, green = 255, blue = 255,
					curRed = 234, curGreen = 249, curBlue = 254,
					duration1 = 300,
					duration2 = 200,
					dayPos = BX.pos(day.node),
					gridParams = BX.pos(monthView.gridWrap),
					casperDay = document.body.appendChild(BX.create('DIV', {
					props: {
						className: "calendar-animate-casper-day"
					},
					style: {
						top: dayPos.top + "px",
						right: dayPos.right + "px",
						bottom: dayPos.bottom + "px",
						left: dayPos.left + "px",
						height: dayPos.height + "px",
						width: dayPos.width + "px"
					}
				}));

				this.firstStage = new BX.easing({
					duration: duration1,
					start: {
						top: dayPos.top,
						right: dayPos.right,
						bottom: dayPos.bottom,
						left: dayPos.left,
						height: dayPos.height,
						width: dayPos.width,
						dayRed: red,
						dayGreen: green,
						dayBlue: blue
					},
					finish: {
						top: gridParams.top,
						right: gridParams.right,
						bottom: gridParams.bottom,
						left: gridParams.left,
						height: gridParams.height,
						width: gridParams.width,
						dayRed: curRed,
						dayGreen: curGreen,
						dayBlue: curBlue
					},
					step: function (state)
					{
						casperDay.style.top = state.top + "px";
						casperDay.style.right = state.right + "px";
						casperDay.style.bottom = state.bottom + "px";
						casperDay.style.left = state.left + "px";
						casperDay.style.height = state.height + "px";
						casperDay.style.width = state.width + "px";
						casperDay.style.background = "rgb(" + state.dayRed + "," + state.dayGreen + "," + state.dayBlue + ")";
					},
					complete: BX.delegate(function ()
					{
						this.secondStage.animate();
						monthView.viewCont.style.opacity = 0
					}, this)
				});

				this.secondStage = new BX.easing({
					duration: duration2,
					start: {
						hide: 100,
						show: 0
					},
					finish: {
						hide: 0,
						show: 100
					},
					step: function (state)
					{
						dayView.viewCont.style.opacity = state.show / 100;
						casperDay.style.opacity = state.hide / 100;
					},
					complete: BX.delegate(function ()
					{
						BX.addClass(this.calendar.mainCont, "calendar-main-container-small-calendar");
						BX.remove(casperDay);
						this.adjustViewAnimate(monthView, dayView);
					}, this)
				});

				this.firstStage.animate();
			}
		},

		fromMonthToList: function (monthView, listView)
		{
			BX.addClass(this.calendar.mainCont, "calendar-main-container-small-calendar");
			listView.show();
			this.adjustViewAnimate(monthView, listView);
		},

		fromWeekToMonth: function (weekView, monthView)
		{
			var
				i,
				_this = this,
				red = 255, green = 255, blue = 255,
				selRed = 234, selGreen = 249, selBlue = 254,
				duration1 = 100,
				duration2 = 75,
				duration3 = 75;

			monthView.viewCont.style.opacity = 0;
			monthView.show();

			// 1. hide events
			BX.removeClass(weekView.grid, 'calendar-events-holder-show');
			BX.removeClass(weekView.fullDayEventsCont, 'calendar-events-holder-show');
			BX.removeClass(monthView.gridMonthContainer, "calendar-events-holder-show");

			monthView.viewCont.style.display = "block";
			var monthViewPos = BX.pos(monthView.viewCont);
			monthView.viewCont.style.position = "absolute";
			monthView.viewCont.style.top = 0;
			monthView.viewCont.style.left = 0;
			monthView.viewCont.style.width = monthViewPos.width + "px";
			monthView.viewCont.style.height = monthViewPos.height + "px";
			monthView.viewCont.style.zIndex = 100;

			var
				currentViewDateCode = this.util.getDayCode(this.currentViewDate),
				monthRowCurrent = Math.ceil((monthView.dayIndex[currentViewDateCode] + 1) / monthView.dayCount) - 1,
				bgWeekNodes = weekView.outerGrid.querySelectorAll('.calendar-grid-cell-inner'), // nodes to color in week view
				monthRowsLength = monthView.monthRows.length;

				if (!monthView.monthRows[monthRowCurrent])
					monthRowCurrent = 0;

			var
				bgMonthNodes = monthView.monthRows[monthRowCurrent].querySelectorAll('.calendar-grid-cell-inner'); // nodes to color in month view

			BX.addClass(weekView.viewCont, "calendar-change-animate-week-to-month");
			for (i = 0; i < bgMonthNodes.length; i++)
			{
				bgMonthNodes[i].style.background = "rgb(" + selRed + "," + selGreen + "," + selBlue + ")";
			}

			for (i = 0; i < monthRowsLength; i++)
			{
				monthView.monthRows[i].style.height = (i != monthRowCurrent) ? 0 : BX.pos(weekView.outerGrid).height + "px";
			}

			BX.addClass(weekView.viewCont, "calendar-animate-mod");
			BX.addClass(monthView.viewCont, "calendar-animate-mod");
			this.calendar.viewsCont.style.height = monthView.viewCont.offsetHeight + 'px';

			this.firstStage = new BX.easing({
				duration: duration1,
				start: {
					topHolderHeight: parseInt(weekView.fullDayEventsCont.style.height) || 0,
					paddingLeft: 43,
					fadeHide: 100,
					red: red,
					green: green,
					blue: blue
				},
				finish:{
					topHolderHeight: 0,
					paddingLeft: 0,
					fadeHide: 0,
					red: selRed,
					green: selGreen,
					blue: selBlue
				},
				step: function (state)
				{
					for (i = 0; i < bgWeekNodes.length; i++)
					{
						bgWeekNodes[i].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
					}

					weekView.timeLinesCont.style.opacity = state.fadeHide / 100;
					weekView.outerGrid.style.paddingLeft = state.paddingLeft + "px";
					weekView.titleCont.style.paddingLeft = state.paddingLeft + "px";
					weekView.fullDayEventsCont.style.height = state.topHolderHeight + 'px';
				},
				complete: function ()
				{
					setTimeout(function ()
					{
						monthView.viewCont.style.opacity = 1;
						weekView.viewCont.style.opacity = 0;
						_this.secondStage.animate();
					}, 100)
				}
			});

			this.secondStage = new BX.easing({
				duration: duration2,
				start: {fadeHide: 100},
				finish: {fadeHide: 0},
				step: function (state)
				{
					weekView.viewCont.style.opacity = state.fadeHide / 100;
				},
				complete: function ()
				{
					for (i = 0; i < monthRowsLength; i++)
					{
						monthView.monthRows[i].style.height = monthView.rowHeight + "px";
					}

					setTimeout(function ()
					{
						BX.addClass(monthView.gridMonthContainer, "calendar-events-holder-show");
						_this.thirdStage.animate();
					}, 410)
				}
			});

			this.thirdStage = new BX.easing({
				duration: duration3,
				start:  {
					red: selRed,
					green: selGreen,
					blue: selBlue
				},
				finish: {
					red: red,
					green: green,
					blue: blue
				},
				step: function (state)
				{
					for (i = 0; i < bgMonthNodes.length; i++)
					{
						bgMonthNodes[i].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
					}
				},
				complete: BX.delegate(function ()
				{
					weekView.outerGrid.style.paddingLeft = '';
					weekView.titleCont.style.paddingLeft = '';
					weekView.fullDayEventsCont.style.height = '';
					weekView.viewCont.style.opacity = 0;

					for (i = 0; i < bgWeekNodes.length; i++)
					{
						bgWeekNodes[i].style.background = '';
					}
					for (i = 0; i < bgMonthNodes.length; i++)
					{
						bgMonthNodes[i].style.background = '';
					}
					BX.removeClass(weekView.viewCont, "calendar-change-animate-week-to-month");
					this.adjustViewAnimate(weekView, monthView);
				}, this)
			});

			this.firstStage.animate();
		},

		fromWeekToDay: function (weekView, dayView)
		{
			dayView.viewCont.style.opacity = 0;
			dayView.show();
			dayView.viewCont.style.display = "block";
			var dayViewOffsets = BX.pos(dayView.viewCont);
			dayView.viewCont.style.position = "absolute";
			dayView.viewCont.style.top = "0px";
			dayView.viewCont.style.left = "0px";
			dayView.viewCont.style.width = dayViewOffsets.width + "px";
			dayView.viewCont.style.height = dayViewOffsets.height + "px";
			this.calendar.viewsCont.style.height = dayView.viewCont.offsetHeight + 'px';
			dayView.viewCont.style.zIndex = "100";

			BX.removeClass(weekView.grid, 'calendar-events-holder-show');
			BX.removeClass(weekView.fullDayEventsCont, 'calendar-events-holder-show');
			BX.removeClass(dayView.grid, 'calendar-events-holder-show');
			BX.removeClass(dayView.fullDayEventsCont, 'calendar-events-holder-show');

			var
				red = 255, green = 255, blue = 255,
				selRed = 234, selGreen = 249, selBlue = 254,
				dayCode = this.util.getDayCode(this.newViewDate),
				day = weekView.days[weekView.dayIndex[dayCode]],
				duration1 = 75,
				duration2 = 310,
				weekCellLength = weekView.titleCont.children.length;

			if (!day)
			{
				day = weekView.days[this.util.getWeekDayOffset(this.util.getWeekDayByInd(this.newViewDate.getDay()))]
			}

			BX.addClass(weekView.viewCont, "calendar-animate-mod");

			var firstStage = new BX.easing({
				duration: duration1,
				start: {
					topHolderHeight: parseInt(weekView.fullDayEventsCont.style.height) || this.WEEK_TOP_MIN_HEIGHT,
					red: red,
					green: green,
					blue: blue
				},
				finish: {
					topHolderHeight: this.WEEK_TOP_MIN_HEIGHT,
					red: selRed,
					green: selGreen,
					blue: selBlue
				},
				step: function (state)
				{
					day.node.style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
					weekView.fullDayEventsCont.style.height = state.topHolderHeight + 'px';

					if (weekView.titleCont.children[day.dayOffset])
					{
						weekView.titleCont.children[day.dayOffset].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
					}
				},
				complete: function ()
				{
					setTimeout(function ()
					{
						BX.addClass(weekView.viewCont, "calendar-change-animate-week-to-day");
						BX.removeClass(weekView.gridMonthContainer, "calendar-events-holder-show");

						for (var i = 0; i < weekCellLength; i++)
						{
							if (i != day.dayOffset)
							{
								weekView.gridRow.children[i].style.flex = 0;
								weekView.titleCont.children[i].style.flex = 0;
							}
						}

						setTimeout(function ()
						{
							secondStage.animate();
							BX.addClass(dayView.grid, 'calendar-events-holder-show');
							BX.addClass(dayView.fullDayEventsCont, 'calendar-events-holder-show');
						}, 210)

					}, 150)
				}
			});

			var secondStage = new BX.easing({
				duration: duration2,
				start:
				{
					fadeShow: 0,
					fadeHide: 100
				},
				finish:
				{
					fadeShow: 100,
					fadeHide: 0
				},
				step: function (state)
				{
					weekView.viewCont.style.opacity = state.fadeHide / 100;
					dayView.viewCont.style.opacity = state.fadeShow / 100;
				},
				complete: BX.delegate(function ()
				{
					day.node.style.background = "";
					if (weekView.titleCont.children[day.dayOffset])
					{
						weekView.titleCont.children[day.dayOffset].style.background = '';
					}
					BX.addClass(this.calendar.mainCont, "calendar-main-container-small-calendar");

					this.adjustViewAnimate(weekView, dayView);
				}, this)
			});

			firstStage.animate();
		},

		fromWeekToList: function (weekView, listView)
		{
			BX.addClass(this.calendar.mainCont, "calendar-main-container-small-calendar");
			listView.show();
			this.adjustViewAnimate(weekView, listView);
		},

		fromDayToMonth: function (dayView, monthView)
		{
			BX.removeClass(this.calendar.mainCont, "calendar-main-container-small-calendar");
			var
				dayCode = dayView.days[0].dayCode,
				red = 255, green = 255, blue = 255,
				curRed = 234, curGreen = 249, curBlue = 254,
				day,
				duration2 = 250,
				duration3 = 300;

			this.firstStage = BX.delegate(function()
			{
				monthView.viewCont.style.opacity = 0;
				monthView.viewCont.style.zIndex = 110;
				monthView.show();
				monthView.viewCont.style.width = dayView.viewCont.offsetWidth + "px";
				monthView.viewCont.style.position = "absolute";
				monthView.viewCont.style.top = 0;
				monthView.viewCont.style.left = 0;
				this.calendar.viewsCont.style.height = monthView.viewCont.offsetHeight + 'px';

				var
					day = monthView.days[monthView.dayIndex[dayCode]],
					dayPos = BX.pos(day.node),
					gridParams = BX.pos(monthView.gridWrap),
					casperDay = document.body.appendChild(BX.create('DIV', {
					props: {
						className: "calendar-animate-casper-day"
					},
					style: {
						opacity: 0,
						top: gridParams.top + "px",
						right: gridParams.right + "px",
						bottom: gridParams.bottom + "px",
						left: gridParams.left + "px",
						height: gridParams.height + "px",
						width: gridParams.width + "px",
						background: "#000"
					}
				}));

				this.secondStage = new BX.easing({
					duration: duration2,
					start: {
						hide: 100,
						show: 0,
						dayRed: red,
						dayGreen: green,
						dayBlue: blue
					},
					finish: {
						hide: 0,
						show: 100,
						dayRed: curRed,
						dayGreen: curGreen,
						dayBlue: curBlue
					},
					step: function (state)
					{
						dayView.viewCont.style.opacity = state.hide / 100;
						casperDay.style.opacity = state.show / 100;
						casperDay.style.background = "rgb(" + state.dayRed + "," + state.dayGreen + "," + state.dayBlue + ")";
					},
					complete: BX.delegate(function ()
					{
						monthView.viewCont.style.opacity = 1;
						this.thirdStage.animate();
					}, this)
				});

				this.thirdStage = new BX.easing(
					{
						duration: duration3,
						start: {
							top: gridParams.top,
							right: gridParams.right,
							bottom: gridParams.bottom,
							left: gridParams.left,
							height: gridParams.height,
							width: gridParams.width,
							dayRed: curRed,
							dayGreen: curGreen,
							dayBlue: curBlue
						},
						finish: {
							top: dayPos.top,
							right: dayPos.right,
							bottom: dayPos.bottom,
							left: dayPos.left,
							height: dayPos.height,
							width: dayPos.width,
							dayRed: red,
							dayGreen: green,
							dayBlue: blue
						},
						step: function (state)
						{
							casperDay.style.show = state.top + "px";
							casperDay.style.top = state.top + "px";
							casperDay.style.right = state.right + "px";
							casperDay.style.bottom = state.bottom + "px";
							casperDay.style.left = state.left + "px";
							casperDay.style.height = state.height + "px";
							casperDay.style.width = state.width + "px";
							casperDay.style.background = "rgb(" + state.dayRed + "," + state.dayGreen + "," + state.dayBlue + ")";
						},
						complete: BX.delegate(function ()
						{
							BX.remove(casperDay);
							this.adjustViewAnimate(dayView, monthView);
						}, this)
				});

				this.secondStage.animate();
			}, this);

			setTimeout(BX.delegate(this.firstStage, this), 300);
		},

		fromDayToWeek: function (dayView, weekView)
		{
			BX.removeClass(this.calendar.mainCont, "calendar-main-container-small-calendar");

			var
				red = 255, green = 255, blue = 255,
				selRed = 234, selGreen = 249, selBlue = 254,
				duration1 = 100,
				duration2 = 100;

			setTimeout(BX.delegate(function()
			{
				weekView.viewCont.style.opacity = 0;
				weekView.show();
				weekView.viewCont.style.display = "block";
				var
					topHolderHeight = parseInt(weekView.fullDayEventsCont.style.height) || this.WEEK_TOP_MIN_HEIGHT,
					weekViewOffsets = BX.pos(weekView.viewCont);

				weekView.viewCont.style.position = "absolute";
				weekView.viewCont.style.top = 0;
				weekView.viewCont.style.left = 0;
				weekView.viewCont.style.width = weekViewOffsets.width + "px";
				weekView.viewCont.style.height = weekViewOffsets.height + "px";
				weekView.viewCont.style.zIndex = 100;
				weekView.fullDayEventsCont.style.height = this.WEEK_TOP_MIN_HEIGHT + 'px';
				this.calendar.viewsCont.style.height = weekView.viewCont.offsetHeight + 'px';

				BX.removeClass(weekView.grid, 'calendar-events-holder-show');
				BX.removeClass(weekView.fullDayEventsCont, 'calendar-events-holder-show');
				BX.removeClass(dayView.grid, 'calendar-events-holder-show');
				BX.removeClass(dayView.fullDayEventsCont, 'calendar-events-holder-show');

				var
					dayCode = this.util.getDayCode(this.currentViewDate),
					day = weekView.days[weekView.dayIndex[dayCode]],
					weekCellLength = weekView.titleCont.children.length;

				if (!day)
				{
					dayCode = this.util.getDayCode(this.newViewDate);
					day = weekView.days[weekView.dayIndex[dayCode]];
				}

				day.node.style.background = "rgb(" + selRed + "," + selGreen + "," + selBlue + ")";
				if (weekView.titleCont.children[day.dayOffset])
				{
					weekView.titleCont.children[day.dayOffset].style.background = "rgb(" + selRed + "," + selGreen + "," + selBlue + ")";
				}

				BX.addClass(dayView.viewCont, "calendar-change-animate-day-to-week");

				for (var i = 0; i < weekCellLength; i++)
				{
					if (i != day.dayOffset)
					{
						weekView.gridRow.children[i].style.flex = 0;
						weekView.titleCont.children[i].style.flex = 0;
					}
				}

				var firstStage = new BX.easing({
					duration: duration1,
					start: {
						topHolderHeight: parseInt(dayView.fullDayEventsCont.style.height) || this.WEEK_TOP_MIN_HEIGHT,
						topPadding: 10,
						red: red,
						green: green,
						blue: blue
					},
					finish: {
						topHolderHeight: this.WEEK_TOP_MIN_HEIGHT,
						topPadding: 0,
						red: selRed,
						green: selGreen,
						blue: selBlue
					},
					step: function (state)
					{
						dayView.fullDayEventsCont.style.height = state.topHolderHeight + 'px';
						dayView.outerGrid.children[0].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
						dayView.viewCont.style.paddingTop = state.topPadding + 'px';
					},
					complete: BX.delegate(function ()
					{
						BX.removeClass(dayView.viewCont, "calendar-animate-mod");
						setTimeout(function ()
						{
							dayView.outerGrid.children[0].style.background = "";
							weekView.viewCont.style.opacity = 1;
							BX.addClass(weekView.viewCont, "calendar-animate-mod");

							for (var i = 0; i < weekCellLength; i++)
							{
								weekView.gridRow.children[i].style.flex = 1;
								weekView.titleCont.children[i].style.flex = 1;
							}
							setTimeout(function ()
							{
								secondStage.animate();
							}, 310);
						}, 100);
					}, this)
				});

				var secondStage = new BX.easing({
					duration: duration2,
					start:
					{
						fadeHide: 100,
						topHolderHeight: this.WEEK_TOP_MIN_HEIGHT,
						red: selRed,
						green: selGreen,
						blue: selBlue
					},
					finish:
					{
						fadeHide: 0,
						topHolderHeight: topHolderHeight,
						red: red,
						green: green,
						blue: blue
					},
					step: function (state)
					{
						dayView.viewCont.style.opacity = state.fadeHide / 100;
						weekView.fullDayEventsCont.style.height = state.topHolderHeight + 'px';

						day.node.style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
						if (weekView.titleCont.children[day.dayOffset])
						{
							weekView.titleCont.children[day.dayOffset].style.background = "rgb(" + state.red + "," + state.green + "," + state.blue + ")";
						}
					},
					complete: BX.delegate(function ()
					{
						dayView.outerGrid.children[0].style.background = "";
						if (weekView.titleCont.children[day.dayOffset])
						{
							weekView.titleCont.children[day.dayOffset].style.background = "";
						}
						day.node.style.background = "";
						BX.removeClass(dayView.viewCont, "calendar-change-animate-day-to-week");

						BX.addClass(weekView.grid, 'calendar-events-holder-show');
						BX.addClass(weekView.fullDayEventsCont, 'calendar-events-holder-show');

						this.adjustViewAnimate(dayView, weekView);
					}, this)
				});

				firstStage.animate();
			}, this), 200);
		},

		fromDayToList: function (dayView, listView)
		{
			listView.show();
			this.adjustViewAnimate(dayView, listView);
		},

		fromListToMonth: function (listView, monthView)
		{
			var
				duration1 = 300,
				duration2 = 350;
			BX.removeClass(monthView.gridMonthContainer, "calendar-events-holder-show");
			BX.removeClass(this.calendar.mainCont, "calendar-main-container-small-calendar");

			setTimeout(BX.delegate(function(){
				monthView.viewCont.style.opacity = 0;
				monthView.show();
				var monthViewPos = BX.pos(monthView.viewCont);
				monthView.viewCont.style.display = "block";
				monthView.viewCont.style.overflow = "hidden";
				monthView.viewCont.style.position = "absolute";
				monthView.viewCont.style.top = 0;
				monthView.viewCont.style.left = 0;
				monthView.viewCont.style.width = monthViewPos.width + "px";
				monthView.viewCont.style.height = monthViewPos.height + "px";
				this.calendar.viewsCont.style.height = monthView.viewCont.offsetHeight + 'px';
				monthView.viewCont.style.zIndex = 100;
				this.calendar.viewsCont.style.overflow = "hidden";
				this.firstStage.animate();

			}, this), duration1);

			this.firstStage = new BX.easing({
				duration: duration2,
				start: {
					fadeShow: 0,
					fadeHide: 100
				},
				finish: {
					fadeShow: 100,
					fadeHide: 0
				},
				step: function (state)
				{
					listView.viewCont.style.opacity = state.fadeHide / 100;
					monthView.viewCont.style.opacity = state.fadeShow / 100;
				},
				complete: BX.delegate(function ()
				{
					this.adjustViewAnimate(listView, monthView);
				}, this)
			});
		},

		fromListToWeek: function (listView, weekView)
		{
			var
				duration1 = 300,
				duration2 = 350;
			BX.removeClass(weekView.gridMonthContainer, "calendar-events-holder-show");
			BX.removeClass(this.calendar.mainCont, "calendar-main-container-small-calendar");

			setTimeout(BX.delegate(function(){
				weekView.viewCont.style.opacity = 0;
				weekView.show();
				var weekViewPos = BX.pos(weekView.viewCont);
				weekView.viewCont.style.display = "block";
				weekView.viewCont.style.overflow = "hidden";
				weekView.viewCont.style.position = "absolute";
				weekView.viewCont.style.top = 0;
				weekView.viewCont.style.left = 0;
				weekView.viewCont.style.width = weekViewPos.width + "px";
				weekView.viewCont.style.height = weekViewPos.height + "px";
				this.calendar.viewsCont.style.height = weekView.viewCont.offsetHeight + 'px';
				weekView.viewCont.style.zIndex = 100;
				this.calendar.viewsCont.style.overflow = "hidden";
				this.firstStage.animate();

			}, this), duration1);

			this.firstStage = new BX.easing({
				duration: duration2,
				start: {
					fadeShow: 0,
					fadeHide: 100
				},
				finish: {
					fadeShow: 100,
					fadeHide: 0
				},
				step: function (state)
				{
					listView.viewCont.style.opacity = state.fadeHide / 100;
					weekView.viewCont.style.opacity = state.fadeShow / 100;
				},
				complete: BX.delegate(function ()
				{
					this.adjustViewAnimate(listView, weekView);
				}, this)
			});
		},

		fromListToDay: function (listView, dayView)
		{
			dayView.show();
			this.calendar.viewsCont.style.height = dayView.viewCont.offsetHeight + 'px';
			this.adjustViewAnimate(listView, dayView);
		},

		adjustViewAnimate: function (currentView, newView)
		{
			newView.viewCont.style.cssText = "";
			currentView.viewCont.style.cssText = "";

			BX.removeClass(currentView.viewCont, "calendar-animate-mod");
			BX.removeClass(newView.viewCont, "calendar-animate-mod");

			currentView.viewCont.style.display = "none";

			BX.addClass(newView.gridMonthContainer, "calendar-events-holder-show");

			if (newView.getName() !== this.calendar.currentViewName)
			{
				currentView.hide();
			}
		},
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.ViewTransition = ViewTransition;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.ViewTransition = ViewTransition;
		});
	}
})(window);