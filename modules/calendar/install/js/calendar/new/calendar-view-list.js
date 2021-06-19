;(function(window) {
	var View = window.BXEventCalendarView;

	// List view of the calendar
	function ListView()
	{
		View.apply(this, arguments);
		this.name = 'list';
		this.filterMode = false;
		this.title = BX.message('EC_VIEW_LIST');
		this.hotkey = 'A';
		this.contClassName = 'calendar-list-view';
		this.loadDaysBefore = 30;
		this.animateAmount = 5;
		this.loadLimitPrevious = 5;
		this.loadLimit = 15;
		this.loadDaysAfter = 60;
		this.SCROLL_DELTA_HEIGHT = 200;
		this.todayCode = this.calendar.util.getDayCode(new Date());
		this.DOM = {};

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

		this.centerLoaderWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
			props: {className: 'calendar-center-loader-block'}}));

		this.filterLoaderWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
			props: {className: 'calendar-search-main'}, style: {
				height: this.util.getViewHeight() + 'px',
				display: 'none'
			}
		}));

		this.filterLoaderWrap.appendChild(BX.create('DIV', {
			props: {className: 'calendar-search-empty'},
			html: '<div class="calendar-search-empty-name">' + BX.message('EC_NO_EVENTS') + '</div>'
		}));
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
			end: new Date(initYear, initMonth + 2, 1)
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
		params = params || {};

		// Get list of entries
		this.entiesRequested = true;

		this.entries = this.entryController.getList({
			startDate: this.displayedRange.start,
			finishDate: this.displayedRange.end,
			viewRange: this.displayedRange,
			finishCallback: function(){
				//for load entries when displaying schedule mode
				if (this.entiesRequested !== false)
				{
					this.displayEntries(params);
				}
				this.entiesRequested = false;
			}.bind(this)
		});

		if (this.entries === false)
		{
			return;
		}

		if (this.calendar.util.isFilterEnabled()
			&& !this.calendar.search.isFilterEmpty())
		{
			this.calendar.search.applyFilter();
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
			return this.showEmptyBlock();
		}
		else if (this.noEntriesWrap)
		{
			this.noEntriesWrap.style.display = 'none';
		}

		this.streamContentWrap.style.display = '';
		this.entryParts = [];

		BX.cleanNode(this.centerLoaderWrap);
		this.centerLoaderWrap.appendChild(
			BX.adjust(
				this.calendar.util.getLoader(),
				{style: {height: '180px'}}
			)
		);

		this.attachEntries(
			this.entries,
			!!params.animation,
			function(){
				this.focusOnDate(params.focusDate || null);
			}.bind(this),
			params.focusDate
		);
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

		// this.entiesRequested - is used in displayEntries
		this.entiesRequested = null;

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
			return this.showEmptyBlock();
		}
		else if (this.noEntriesWrap)
		{
			this.noEntriesWrap.style.display = 'none';
		}

		this.streamContentWrap.style.display = '';
		this.entryParts = [];
		this.attachEntries(entries, 'next');
	};

	ListView.prototype.attachEntries = function(entries, animation, focusCallback, focusDate)
	{
		if (!entries && !entries.length)
		{
			if (BX.type.isFunction(focusCallback))
			{
				focusCallback();
			}
			return;
		}

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
			if (fromCode !== toCode)
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
			if (a.dayCode === b.dayCode)
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

		this.focusDate = focusDate || new Date();
		this.today = new Date();
		this.animationMode = animation;
		this.currentDisplayedEntry = 0;
		this.actuallyAnimatedEntryCount = 0;
		this.displayEntry(this.entryParts[this.currentDisplayedEntry], animation, focusCallback);
	};

	ListView.prototype.displayEntry = function(part, animation, focusCallback)
	{
		animation = false;

		var group;
		var focusDayCode = this.calendar.util.getDayCode(this.focusDate);
		if (part.from.getTime() > this.focusDate.getTime() && !this.groups[this.groupsIndex[focusDayCode]] && !this.filterMode)
		{
			this.createEntryGroupForDate(this.focusDate, animation);
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
		BX.cleanNode(this.centerLoaderWrap);

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

			if (
				(parseInt(this.calendar.util.userId) !== parseInt(entry.data.MEETING_HOST))
				&& entry.data.MEETING_STATUS === 'Q'
			)
			{
				wrap.appendChild(BX.create(
					'DIV',
					{
						props: {
							className: 'calendar-timeline-stream-content-event-control',
						},
						children: [
							this.getDecisionButton('Y').getContainer(),
							this.getDecisionButton('N').getContainer(),
						],
					}
				));
			}

			attendesNode = wrap.appendChild(BX.create('DIV', {props: {className: 'calendar-timeline-stream-content-event-members'}}));

			if (entry.isMeeting() && entry.getAttendees().length > 0)
			{
				this.showAttendees(attendesNode, entry.getAttendees().filter(function (user)
				{
					return user.STATUS === 'Y' || user.STATUS === 'H';
				}), entry.getAttendees().length);
			}
			else if (entry.isPersonal())
			{
				attendesNode.innerHTML = BX.message('EC_EVENT_IS_MINE');
			}

			part.DOM = {};

			if (part.dayCode === focusDayCode && BX.type.isFunction(focusCallback))
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
						id: 'simple_view_popup_' + user.USER_ID,
						src: user.AVATAR || ''
					},
					props: {
						title: user.DISPLAY_NAME,
						className: 'calendar-member'
					}}));
				(function (userId){setTimeout(function(){BX.tooltip(userId, "simple_view_popup_" + userId);}, 100)})(user.USER_ID);
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

		if (this.popup)
		{
			this.popup.setAutoHide(false);
		}

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
				width: 220,
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
		if (this.viewCont.style.display === 'none')
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
			if (ind >= 0 && this.groupsDayCodes[ind])
			{
				this.focusOnDate(this.groupsDayCodes[ind], true);
			}
			else
			{
				if (date.getTime() >= this.displayedRange.start.getTime() &&
					date.getTime() <= this.displayedRange.end.getTime())
				{
					if (!this.groups[this.groupsIndex[dayCode]])
					{
						this.createEntryGroupForDate(date, false);
					}
					this.groupsDayCodes.sort();
					this.focusOnDate(date, true);
				}
				else
				{
					this.displayedRange = {
						start: new Date(date.getTime() - BX.Calendar.Util.getDayLength() * 10),
						end: new Date(date.getTime() + BX.Calendar.Util.getDayLength() * 60)
					};
					this.calendar.setDisplayedViewRange(this.displayedRange);
					this.animationMode = false;
					this.displayEntries({
						animation: false,
						focusDate: date
					});
					this.nothingToLoadNext = false;
					this.nothingToLoadPrevious = false;
				}
			}
		}

		return viewRangeDate;
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

		// Hide loader
		BX.cleanNode(this.centerLoaderWrap);

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

				setTimeout(BX.delegate(function(){
					if (this.streamScrollWrap && this.groupsIndex[dayCode] !== undefined && this.groups[this.groupsIndex[dayCode]])
					{
						this.streamScrollWrap.scrollTop = this.groups[this.groupsIndex[dayCode]].wrap.offsetTop;
					}
				}, this), 500);

				setTimeout(BX.delegate(function(){
					if (this.streamScrollWrap && this.groupsIndex[dayCode] !== undefined && this.groups[this.groupsIndex[dayCode]])
					{
						this.streamScrollWrap.scrollTop = this.groups[this.groupsIndex[dayCode]].wrap.offsetTop;
					}
				}, this), 1000);
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
		{
			return;
		}

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

			var dayCode, uid, decision, entry;
			if (
				params.specialTarget
				&& params.e
				&& (uid = params.specialTarget.getAttribute('data-bx-calendar-entry'))
				&& (decision = params.e.target.parentElement.getAttribute('data-bx-decision-button'))
			)
			{
				if (this.resultEntriesIndex && this.resultEntriesIndex[uid] !== undefined)
				{
					entry = this.resultEntries[this.resultEntriesIndex[uid]];
				}
				else
				{
					entry = this.entries[this.entriesIndex[uid]];
				}
				if (entry && ['Y', 'N'].includes(decision))
				{
					BX.Calendar.EntryManager.setMeetingStatus(entry, decision);
				}
			}
			else if (
				params.specialTarget
				&& (uid = params.specialTarget.getAttribute('data-bx-calendar-entry'))
			)
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
				this.showCompactEditFormForNewEntry({
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
		if (BX.Type.isDomNode(this.noEntriesWrap))
		{
			BX.Dom.remove(this.noEntriesWrap);
		}

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
						click: function(){
							BX.Calendar.EntryManager.openEditSlider({
								type: this.calendar.util.type,
								ownerId: this.calendar.util.ownerId,
								userId: parseInt(this.calendar.currentUser.id)
							});
						}.bind(this)
					}
				}));
		}

		this.streamContentWrap.style.display = 'none';
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
		{
			this.calendar.search.resetFilter();
		}

		if (this.isActive())
		{
			this.displayEntries();
		}
	};

	ListView.prototype.handleEntryClick = function(params)
	{
		if (this.filterMode && this.resultEntriesIndex[params.uid] !== undefined)
		{
			params.entry = this.resultEntries[this.resultEntriesIndex[params.uid]];
		}
		View.prototype.handleEntryClick.apply(this, arguments);
	};

	ListView.prototype.getDecisionButton = function(decision)
	{
		return new BX.UI.Button({
			text: BX.message('EC_DESIDE_BUT_' + decision),
			round: true,
			size: BX.UI.Button.Size.EXTRA_SMALL,
			color: decision === 'Y' ? BX.UI.Button.Color.LIGHT_BORDER : BX.UI.Button.Color.LIGHT,
			props: {
				'data-bx-decision-button': decision,
			},
		});
	};

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.CalendarListView = ListView;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.CalendarListView = ListView;
		});
	}
})(window);