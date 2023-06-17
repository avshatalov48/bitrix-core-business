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
		this.SCROLL_DELTA_HEIGHT = 180;
		this.todayCode = this.calendar.util.getDayCode(new Date());
		this.focusDate = new Date();
		this.removeLoadingOnScroll = false;
		this.isBoundaryOfPastReached = false;
		this.isBoundaryOfFutureReached = false;
		this.DOM = {};

		this.loadMoreEntriesDebounce = BX.Runtime.debounce(this.loadMoreEntries, 300, this);

		BX.Event.EventEmitter.unsubscribe('BX.Calendar:onEntryListReload', this.handleOnEntryListReload.bind(this));
		BX.Event.EventEmitter.subscribe('BX.Calendar:onEntryListReload', this.handleOnEntryListReload.bind(this));

		this.preBuild();
	}
	ListView.prototype = Object.create(View.prototype);
	ListView.prototype.constructor = ListView;

	ListView.prototype.preBuild = function()
	{
		this.viewCont = BX.create('DIV', {
			props: {
				className: this.contClassName
			},
			style: {
				display: 'none'
			}
		});

		BX.addCustomEvent(this.calendar, 'afterSetView', BX.delegate(function(params){
			if (params.viewName !== this.name && this.filterMode)
			{
				this.resetFilterMode(params);
			}
		}, this));

	};

	ListView.prototype.build = function()
	{
		this.titleCont = this.viewCont.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-grid-month-row-days-week'
			}
		}));

		this.streamScrollWrap = this.viewCont.appendChild(
			BX.create('DIV', {
				props: {
					className: 'calendar-timeline-stream-container calendar-custom-scroll'
				},
				style: {
					height: this.util.getViewHeight() + 'px',
				},
		}));
		this.streamScrollWrap.addEventListener('scroll', this.scrollHandle.bind(this));
		this.streamScrollWrap.addEventListener('wheel', this.mouseWheelHandle.bind(this));

		this.streamContentWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-stream-container-content'
			}
		}));
		this.topLoaderBlock = this.streamContentWrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-loader --top'
			},
		}));
		this.listWrap = this.streamContentWrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-stream-container-list'
			}
		}));
		this.bottomLoaderBlock = this.streamContentWrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-loader --bottom'
			},
		}));
		this.topLoaderBlock.innerHTML = `
			<div class="calendar-timeline-loader_icon"></div>
			<div class="calendar-timeline-loader_text">${BX.message('EC_CALENDAR_NO_MORE_EVENTS_PAST')}</div>
		`;
		this.bottomLoaderBlock.innerHTML = `
			<div class="calendar-timeline-loader_icon"></div>
			<div class="calendar-timeline-loader_text">${BX.message('EC_CALENDAR_NO_MORE_EVENTS_FUTURE')}</div>
		`;
		this.topLoaderBlock.style.display = 'none';
		this.bottomLoaderBlock.style.display = 'none';

		this.centerLoaderWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-center-loader-block'
			}
		}));
	};

	ListView.prototype.setTitle = function()
	{
		var viewRangeDate = this.calendar.getViewRangeDate();
		View.prototype.setTitle.apply(this, [BX.date.format('f', viewRangeDate.getTime() / 1000)
		+ ', #GRAY_START#'
		+ viewRangeDate.getFullYear()
		+ '#GRAY_END#']);
	};

	ListView.prototype.show = function(params)
	{
		params = params || {};

		if (!this.loaderCircle)
		{
			this.loaderCircle = new BX.Loader({
				target: this.viewCont
			});
			this.loaderCircle.show();
		}

		View.prototype.show.apply(this, arguments);
		this.showNavigationCalendar();
		BX.remove(this.calendar.additionalInfoOuter);

		if (!this.displayedRange)
		{
			const initYear = parseInt(this.calendar.util.config.init_year);
			const initMonth = parseInt(this.calendar.util.config.init_month);
			this.displayedRange = {
				start: new Date(initYear, initMonth - 2, 1),
				end: new Date(initYear, initMonth + 2, 1),
			};
		}

		if (this.calendar.viewsCont.style.height === '')
		{
			this.calendar.viewsCont.style.height = this.util.getViewHeight() + 'px';
		}

		BX.removeClass(this.streamContentWrap, 'calendar-timeline-stream-search-result');
		this.calendar.setDisplayedViewRange(this.displayedRange);
		if (params.displayEntries !== false)
		{
			this.loadEntries({direction: 'both'}).then(entries => {
				this.entries = entries;
				this.displayEntries({focusDate: this.focusDate});
			});
		}
	};

	ListView.prototype.loadEntries = function(params = {})
	{
		return new Promise((resolve) => {
			this.entryController.getList({
				startDate: this.displayedRange.start,
				finishDate: this.displayedRange.end,
				viewRange: {
					start: new Date(this.displayedRange.start),
					end: new Date(this.displayedRange.end),
				},
				...params,
			}).then((entries) => {
				this.displayedRange = this.calendar.entryController.getLoadedEntiesLimits();
				resolve(entries);
			});
		});
	};

	ListView.prototype.redraw = function()
	{
		if (this.filterMode)
		{
			if (!this.calendar.search.isFilterEmpty())
			{
				this.calendar.search.applyFilter();
			}
		}
		else
		{
			this.displayEntries({ dontFocus: true });
		}
	};

	ListView.prototype.reload = function()
	{
		this.loadEntries({direction: 'both'}).then(entries => {
			this.entries = entries;
			this.redraw();
		});
	};

	ListView.prototype.displayEntries = function(params = {})
	{
		if (this.filterMode)
		{
			return;
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
			this.loaderCircle.hide();
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
			() => {
				if (params.dontFocus)
				{
					return;
				}
				this.focusOnDate(params.focusDate ?? this.getCurrentViewDate());
			},
			params.focusDate
		);

		this.loaderCircle.hide();
	};

	ListView.prototype.displayResult = function(entries)
	{
		this.streamContentWrap.style.display = '';

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
			if (this.loaderCircle)
			{
				this.loaderCircle.hide();
			}

			return this.showEmptyBlock();
		}
		else if (this.noEntriesWrap)
		{
			this.noEntriesWrap.style.display = 'none';
		}

		this.streamContentWrap.style.display = '';
		this.entryParts = [];
		this.attachEntries(entries, 'next');
		if (this.loaderCircle)
		{
			this.loaderCircle.hide();
		}
	};

	ListView.prototype.attachEntries = function(entries, animation, focusCallback)
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
			from, to, index,
			fromTs, toTs,
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
				fromTs = new Date(entry.from.getFullYear(), entry.from.getMonth(), entry.from.getDate()).getTime();
				from = new Date(entry.from.getTime());
				toTs = new Date(entry.to.getFullYear(), entry.to.getMonth(), entry.to.getDate()).getTime();
				while (fromTs <= toTs)
				{
					this.entryParts.push({
						index: ++index,
						dayCode: this.calendar.util.getDayCode(from),
						from: from,
						entry: entry
					});
					fromTs +=  this.calendar.util.dayLength;
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

		this.today = new Date();
		for (let partIndex = 0; partIndex < this.entryParts.length; partIndex++)
		{
			this.displayEntry(this.entryParts[partIndex]);
		}
		if (!this.filterMode)
		{
			if (!this.groups[this.groupsIndex[this.todayCode]])
			{
				this.createEntryGroupForDate(this.today);
			}
			this.groupsDayCodes.sort();

			if (BX.type.isFunction(focusCallback))
			{
				focusCallback();
			}
		}

		setTimeout(() => {
			this.streamScrollWrap.style.height = Math.min(this.streamContentWrap.offsetHeight, this.util.getViewHeight()) + 'px';
			if (this.viewCont.offsetHeight > 0)
			{
				this.calendar.viewsCont.style.height = this.viewCont.offsetHeight + 'px';
			}
			this.streamScrollWrap.style.height = this.util.getViewHeight() + 'px';
		}, 300);
	};

	ListView.prototype.displayEntry = function(part)
	{
		var group;
		var focusDayCode = this.calendar.util.getDayCode(this.focusDate);
		if (part.from.getTime() > this.focusDate.getTime() && !this.groups[this.groupsIndex[focusDayCode]] && !this.filterMode)
		{
			this.createEntryGroupForDate(this.focusDate);
		}

		group = this.groups[this.groupsIndex[part.dayCode]];
		if (!group)
		{
			this.createEntryGroupForDate(part.from);
			group = this.groups[this.groupsIndex[part.dayCode]];
		}

		if (group.emptyWarning)
		{
			BX.remove(group.emptyWarning);
		}
		BX.cleanNode(this.centerLoaderWrap);

		var entryPartUid = this.getUniqueId(part);

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
				{
					timeLabel = this.calendar.util.formatTime(entry.from);
				}
				else if (part.index === entry.lastPartIndex)
				{
					timeLabel = BX.message('EC_TILL_TIME').replace('#TIME#', this.calendar.util.formatTime(entry.to));
				}
				else
				{
					timeLabel = BX.message('EC_ALL_DAY');
				}
			}
			else
			{
				timeLabel = this.calendar.util.formatTime(entry.from.getHours(), entry.from.getMinutes()) + ' &ndash; ' + this.calendar.util.formatTime(entry.to.getHours(), entry.to.getMinutes())
			}

			wrap = group.content.appendChild(BX.create('DIV', {
				attrs: {
					'data-bx-calendar-entry': entry.uid
				},
				props: {
					className: 'calendar-timeline-stream-content-event'
						+ (entry.isSharingEvent() ? ' calendar-timeline-stream-content-event-sharing' : '')
				}
			}));

			wrap.appendChild(BX.create('DIV', {
				props: {
					className: 'calendar-timeline-stream-content-event-time'
				},
				html: '<span class="calendar-timeline-stream-content-event-time-link">'
					+ timeLabel
					+ '</span>'
					+ (entry.isRecursive()
					? '<span class="calendar-timeline-stream-content-event-recursive" title="'
						+ (entry.data['~RRULE_DESCRIPTION'] || '')
					+ '">'
					+ BX.message('EC_CALENDAR_REC_EVENT') + '</span>'
					: '')
			}));

			var location = this.calendar.util.getTextLocation(entry.location) || '';
			if (location)
			{
				location = '<span class="calendar-timeline-stream-content-event-location">('
					+ BX.util.htmlspecialchars(location)
					+ ')</span>';
			}

			const titleNode = BX.create('DIV', {
				props: {
					className: 'calendar-timeline-stream-content-event-name',
				},
				html: '<div class="calendar-timeline-stream-content-event-name-link"><span>'
					+ BX.util.htmlspecialchars(entry.name)
					+ '</span></div>'
					+ location,
			});

			if (entry.isInvited())
			{
				wrap.className += ' calendar-timeline-stream-content-event-invited';
				if (this.isFirstVisibleRecursiveEntry(entry))
				{
					titleNode.prepend(BX.create('SPAN', {props: {className: 'calendar-event-invite-counter'}, text: '1'}));
				}
				else
				{
					titleNode.prepend(BX.create('SPAN', {props: {className: 'calendar-event-invite-counter-dot'}}));
				}
			}
			else
			{
				titleNode.prepend(BX.create('SPAN', {
					props: {
						className: 'calendar-timeline-stream-content-event-color',
					},
					style: {
						backgroundColor: entry.color,
					}
				}));
			}

			wrap.append(titleNode);


			if (
				(parseInt(this.calendar.util.userId) !== parseInt(entry.data.MEETING_HOST))
				&& (parseInt(this.calendar.util.userId) === parseInt(entry.data.CREATED_BY))
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
				this.showAttendees(attendesNode, entry.getAttendees());
			}
			else if (entry.isPersonal())
			{
				attendesNode.innerHTML = BX.message('EC_EVENT_IS_MINE');
			}

			part.DOM = {};
		}
	};

	ListView.prototype.showAttendees = function(wrapper, attendees)
	{
		var
			i,
			user,
			MAX_USER_COUNT = 3,
			userLength = attendees.length,
			MAX_USER_COUNT_DISPLAY = 5,
			attendeesCount = 0
		;

		if (userLength > 0)
		{
			if (userLength > MAX_USER_COUNT_DISPLAY)
			{
				userLength = MAX_USER_COUNT;
			}

			for (i = 0; i < attendees.length; i++)
			{
				user = attendees[i] || {};
				if (user.STATUS === 'Y' || user.STATUS === 'H')
				{
					attendeesCount++;
					if (user.AVATAR !== '/bitrix/images/1.gif')
					{
						wrapper.appendChild(BX.create('IMG', {
							attrs: {
								id: 'simple_view_popup_' + user.ID,
								src: encodeURI(user.AVATAR) || '',
								'bx-tooltip-user-id': user.ID,
							},
							props: {
								className: 'calendar-member'
							}
						}));
					}
					else
					{
						if (user.SHARING_USER)
						{
							wrapper.appendChild(BX.create('DIV', {
								attrs: {
									id: 'simple_view_popup_' + user.ID,
									'bx-tooltip-user-id': user.ID,
								},
								props: {
									className: 'calendar-event-block-icon-sharing calendar-event-block-icon-sharing-view-list',
								},
							}));
						}
						else
						{
							var userClassName = user.EMAIL_USER ? 'ui-icon-common-user-mail' : 'ui-icon-common-user';
							wrapper.appendChild(BX.create('DIV', {
								props: {
									title: user.DISPLAY_NAME,
									className: 'ui-icon ' + userClassName,
								},
								html: '<i></i>',
							}));
						}
					}
				}
				if (attendeesCount >= userLength)
				{
					break;
				}
			}

			if (userLength < attendees.length)
			{
				var moreUsersLink = wrapper.appendChild(BX.create('SPAN', {
					attrs: {
						'data-bx-calendar-entry-attendees-control': attendees.length
					},
					props: {
						className: 'calendar-member-more-count'
					},
					text: ' ' + BX.message('EC_ATTENDEES_ALL_COUNT').replace('#COUNT#', attendees.length),
					events: {
						click: BX.delegate(function(){
							this.showUserListPopup(moreUsersLink, attendees);
						}, this)}
				}));
			}
		}
	};

	ListView.prototype.showUserListPopup = function(node, userList)
	{
		(new BX.Calendar.Controls.AttendeesList(
			node,
			BX.Calendar.Controls.AttendeesList.sortAttendees(userList))
		).showPopup();
	};

	ListView.prototype.appendDateGroup = function(wrap, date)
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
				node: BX.create('DIV', {
					attrs: {
						'data-bx-calendar-list-year': year
					}
				}),
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
				node: BX.create('DIV', {
					attrs: {
						'data-bx-calendar-list-month': month
					}
				}),
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
					props: {
						className: 'calendar-timeline-stream-day'
					},
					attrs: {
						'data-bx-calendar-list-day': day
					}
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

		wrap.style.opacity = '1';
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
		animate = false;
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
						this.createEntryGroupForDate(date);
					}
					this.groupsDayCodes.sort();
					this.focusOnDate(date, true);
				}
				else
				{
					const newStart = new Date(date.getTime() - BX.Calendar.Util.getDayLength() * 10);
					const newEnd = new Date(date.getTime() + BX.Calendar.Util.getDayLength() * 60);
					if (newStart.getTime() < this.displayedRange.start.getTime())
					{
						this.displayedRange.start = newStart;
					}
					if (newEnd.getTime() > this.displayedRange.end.getTime())
					{
						this.displayedRange.end = newEnd;
					}
					this.calendar.setDisplayedViewRange(this.displayedRange);
					this.loadEntries().then(entries => {
						this.entries = entries;
						this.displayEntries({
							animation: false,
							focusDate: date
						});
					});
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

	ListView.prototype.createEntryGroupForDate = function(date)
	{
		var
			dayCode = this.calendar.util.getDayCode(date),
			time = Math.round(date.getTime() / 1000) * 1000,
			group = {
				dayCode: dayCode,
				wrap: BX.create('DIV', {props: {className: 'calendar-timeline-stream-section-wrap'}})
			};

		const streamLabelClass = dayCode === this.todayCode ? 'calendar-timeline-stream-today-label' : 'calendar-timeline-stream-label';
		group.titleNode = group.wrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-stream-section calendar-timeline-section-date-label'
			},
			html: `
				<div class="calendar-timeline-stream-section-content">
					<div data-bx-calendar-date="${time}" class="${streamLabelClass}">
						${this.calendar.util.formatDateUsable(date)}
					</div>
				</div>
			`,
		}));
		group.groupNode = group.wrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-stream-section'
			}
		}));
		group.content = group.groupNode.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-stream-section-content'
			}
		}));
		group.emptyWarning = group.content.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-timeline-empty-section'
			},
			text: BX.message('EC_NO_EVENTS')
		}));

		this.groups.push(group);
		this.groupsIndex[dayCode] = this.groups.length - 1;
		this.groupsDayCodes.push(dayCode);

		this.appendDateGroup(this.groups[this.groups.length - 1].wrap, date);
	};

	ListView.prototype.focusOnDate = function(date, animate)
	{
		let dayCode = false;
		if (BX.type.isDate(date))
		{
			dayCode = this.calendar.util.getDayCode(date);
		}
		else if (BX.type.isString(date))
		{
			dayCode = date;
		}
		else
		{
			dayCode = this.todayCode;
		}

		// Hide loader
		BX.cleanNode(this.centerLoaderWrap);

		this.focusedDayCode = dayCode;
		if (this.groupsIndex && this.groups[this.groupsIndex[dayCode]])
		{
			if (animate && this.streamScrollWrap)
			{
				new BX.easing({
					duration: 300,
					start: {scrollTop: this.streamScrollWrap.scrollTop},
					finish: {scrollTop: this.groups[this.groupsIndex[dayCode]].wrap.offsetTop - 10},
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
				if (this.streamScrollWrap && !BX.Type.isUndefined(this.groupsIndex[dayCode]) && this.groups[this.groupsIndex[dayCode]])
				{
					this.streamScrollWrap.scrollTop = this.groups[this.groupsIndex[dayCode]].wrap.offsetTop - 10;
				}
			}
		}
	};

	ListView.prototype.getCurrentViewDate = function()
	{
		var container = document.querySelector('.calendar-timeline-stream-container');
		if (container)
		{
			var curScroll = container.scrollTop;
			var minDiff = Infinity;
			var curDiff;
			var element;
			for (var i = 0; i < this.groups.length; i++)
			{
				curDiff = Math.abs(this.groups[i].wrap.offsetTop - curScroll);
				if (minDiff > curDiff)
				{
					minDiff = curDiff;
					element = this.groups[i];
				}
			}
			if (element)
			{
				return element.dayCode;
			}
			return null;
		}
	};

	ListView.prototype.scrollHandle = function()
	{
		if (!this.filterMode)
		{
			const scrollDeltaHeightTop = this.topLoaderBlock.style.display === '' ? this.SCROLL_DELTA_HEIGHT : 0;
			const scrollDeltaHeightBottom = this.bottomLoaderBlock.style.display === '' ? this.SCROLL_DELTA_HEIGHT : 0;
			if (
				this.isScrollingDown
				&& !this.isBoundaryOfFutureReached
				&& !this.removeLoadingOnScroll
				&& this.streamScrollWrap.scrollHeight - this.util.getViewHeight() - this.streamScrollWrap.scrollTop < scrollDeltaHeightBottom
			)
			{
				this.loadMoreEntriesDebounce({mode: 'next'});
			}
			else if (
				this.isScrollingUp
				&& !this.isBoundaryOfPastReached
				&& !this.removeLoadingOnScroll
				&& this.streamScrollWrap.scrollTop < scrollDeltaHeightTop
			)
			{
				this.loadMoreEntriesDebounce({mode: 'previous'});
			}
			this.focusDate = this.getFirstVisibleDate();
		}
	};

	ListView.prototype.mouseWheelHandle = function(event)
	{
		this.isScrollingUp = event.deltaY < 0;
		this.isScrollingDown = event.deltaY > 0;

		const ranIntoTopBorder = this.isScrollingUp
			&& !this.isBoundaryOfPastReached
			&& this.streamScrollWrap.scrollTop === 0
			&& this.topLoaderBlock.style.display !== '';
		const ranIntoBottomBorder = this.isScrollingDown
			&& !this.isBoundaryOfFutureReached
			&& Math.abs(this.streamScrollWrap.scrollTop - (this.streamScrollWrap.scrollHeight - this.streamScrollWrap.offsetHeight)) < 1
			&& this.bottomLoaderBlock.style.display !== '';

		const didntRanIntoBorder = !ranIntoTopBorder && !ranIntoBottomBorder;
		if (didntRanIntoBorder)
		{
			return;
		}

		if (ranIntoTopBorder && !this.isBoundaryOfPastReached)
		{
			this.topLoaderBlock.style.display = '';
			this.streamScrollWrap.style.height = this.calendar.viewsCont.style.height;
			this.streamScrollWrap.scrollTop += this.topLoaderBlock.offsetHeight;
			this.loadMoreEntriesDebounce({mode: 'previous'});
		}
		if (ranIntoBottomBorder && !this.isBoundaryOfFutureReached)
		{
			this.bottomLoaderBlock.style.display = '';
			this.streamScrollWrap.style.height = this.calendar.viewsCont.style.height;
			this.loadMoreEntriesDebounce({mode: 'next'});
		}
		event.preventDefault();
	};

	ListView.prototype.getFirstVisibleDate = function()
	{
		const scrollViewportTop = this.streamScrollWrap.scrollTop;
		const scrollViewportBottom = scrollViewportTop + this.streamScrollWrap.offsetHeight;
		for (const yearNode of this.listWrap.childNodes)
		{
			const yearNodeTop = yearNode.offsetTop;
			const yearNodeBottom = yearNodeTop + yearNode.offsetHeight;
			if (yearNodeBottom < scrollViewportTop || yearNodeTop > scrollViewportBottom)
			{
				continue;
			}
			for (const monthNode of yearNode.childNodes)
			{
				const monthNodeTop = monthNode.offsetTop;
				const monthNodeBottom = monthNodeTop + monthNode.offsetHeight;
				if (monthNodeBottom < scrollViewportTop || monthNodeTop > scrollViewportBottom)
				{
					continue;
				}
				for (const dayNode of monthNode.childNodes)
				{
					const dayNodeTop = dayNode.offsetTop;
					if (dayNodeTop >= scrollViewportTop)
					{
						const visibleYear = parseInt(yearNode.getAttribute('data-bx-calendar-list-year'));
						const visibleMonth = parseInt(monthNode.getAttribute('data-bx-calendar-list-month')) - 1;
						const visibleDay = parseInt(dayNode.getAttribute('data-bx-calendar-list-day'));
						return new Date(visibleYear, visibleMonth, visibleDay);
					}
				}
			}
		}
		return new Date();
	};

	ListView.prototype.loadMoreEntries = function(params)
	{
		if (this.currentLoadMode || this.filterMode || this.entryController.isAwaitingAnyResponses())
		{
			return;
		}

		const direction = params.mode;
		const savedScrollHeight = this.streamScrollWrap.scrollHeight;

		let loader;
		if (direction === 'next')
		{
			loader = new BX.Loader({
				target: this.bottomLoaderBlock,
			});
			loader.show();
			this.displayedRange.end = new Date(this.displayedRange.end.getFullYear(), this.displayedRange.end.getMonth() + 1, this.displayedRange.end.getDate());
		}
		if (direction === 'previous')
		{
			loader = new BX.Loader({
				target: this.topLoaderBlock,
			});
			loader.show();
			this.displayedRange.start = new Date(this.displayedRange.start.getFullYear(), this.displayedRange.start.getMonth() - 1, this.displayedRange.start.getDate());
		}

		this.calendar.setDisplayedViewRange(this.displayedRange);
		this.loadEntries({ direction }).then((entries) => {
			if (loader)
			{
				loader.destroy();
			}

			this.entries = entries;
			this.attachEntries(entries, direction === 'next' ? 'next' : false);

			if (direction === 'previous')
			{
				this.streamScrollWrap.scrollTop += this.streamScrollWrap.scrollHeight - savedScrollHeight;
			}

			this.updateBoundaryOfPastReached();
			this.updateBoundaryOfFutureReached();

			this.currentLoadMode = false;
		});
	};

	ListView.prototype.handleOnEntryListReload = function(event)
	{
		if (event.getData().isBoundaryOfPastReached === true)
		{
			this.isBoundaryOfPastReached = true;
		}
		if (event.getData().isBoundaryOfFutureReached === true)
		{
			this.isBoundaryOfFutureReached = true;
		}
	};

	ListView.prototype.updateBoundaryOfPastReached = function()
	{
		if (this.isBoundaryOfPastReached)
		{
			if (!BX.hasClass(this.topLoaderBlock, 'calendar-timeline-loader-no-more-events'))
			{
				BX.addClass(this.topLoaderBlock, 'calendar-timeline-loader-no-more-events');
				if (this.streamScrollWrap.scrollTop < 300)
				{
					this.scrollToTheMostTop();
				}
			}
		}
		else
		{
			BX.removeClass(this.topLoaderBlock, 'calendar-timeline-loader-no-more-events');
		}
	};

	ListView.prototype.updateBoundaryOfFutureReached = function()
	{
		if (this.isBoundaryOfFutureReached)
		{
			if (!BX.hasClass(this.bottomLoaderBlock, 'calendar-timeline-loader-no-more-events'))
			{
				BX.addClass(this.bottomLoaderBlock, 'calendar-timeline-loader-no-more-events');
				const maxScroll = this.streamScrollWrap.scrollHeight - this.streamScrollWrap.offsetHeight;
				if (maxScroll - this.streamScrollWrap.scrollTop < 300)
				{
					this.scrollToTheMostBottom();
				}
			}
		}
		else
		{
			BX.removeClass(this.bottomLoaderBlock, 'calendar-timeline-loader-no-more-events');
		}
	};

	ListView.prototype.scrollToTheMostTop = function()
	{
		new BX.easing({
			duration: 300,
			start: {
				scrollTop: this.streamScrollWrap.scrollTop,
			},
			finish: {
				scrollTop: 0,
			},
			transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
			step: (state) => {
				this.streamScrollWrap.scrollTop = state.scrollTop;
			},
			complete: () => {}
		}).animate();
	};

	ListView.prototype.scrollToTheMostBottom = function()
	{
		const initMaxScroll = this.streamScrollWrap.scrollHeight - this.streamScrollWrap.offsetHeight;
		new BX.easing({
			duration: 300,
			start: {
				scrollTop: this.streamScrollWrap.scrollTop,
			},
			finish: {
				scrollTop: initMaxScroll,
			},
			transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
			step: (state) => {
				const transitionMaxScroll = this.streamScrollWrap.scrollHeight - this.streamScrollWrap.offsetHeight;
				this.streamScrollWrap.scrollTop = state.scrollTop * transitionMaxScroll / initMaxScroll;
			},
			complete: () => {}
		}).animate();
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

			const button = params.e.target.closest('[data-bx-decision-button]');

			if (
				params.target
				&& params.target.getAttribute('data-bx-calendar-entry-attendees-control')
			)
			{
				params.e.preventDefault();
			}
			else if (
				params.specialTarget
				&& params.e
				&& (uid = params.specialTarget.getAttribute('data-bx-calendar-entry'))
				&& button
				&& (decision = button.getAttribute('data-bx-decision-button'))
			)
			{
				if (this.resultEntriesIndex && !BX.Type.isUndefined(this.resultEntriesIndex[uid]) && !BX.Type.isUndefined(this.resultEntries[this.resultEntriesIndex[uid]]))
				{
					entry = this.resultEntries[this.resultEntriesIndex[uid]];
				}
				else
				{
					entry = this.getEntryById(uid);
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

	ListView.prototype.showEmptyBlock = function()
	{
		if (BX.Type.isDomNode(this.noEntriesWrap))
		{
			BX.Dom.remove(this.noEntriesWrap);
		}

		const emptyBlockHeight = 500;

		this.noEntriesWrap = this.streamScrollWrap.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-search-main',
			},
			style: {
				height: emptyBlockHeight + 'px',
			},
			html: this.getEmptyStateContent(),
		}));

		const innerWrap = this.noEntriesWrap.firstElementChild;
		innerWrap.appendChild(this.getEmptyStateButton());

		this.streamContentWrap.style.display = 'none';
		this.calendar.viewsCont.style.height = emptyBlockHeight + 'px';
		this.streamScrollWrap.style.height = this.util.getViewHeight() + 'px';
	};

	ListView.prototype.getEmptyStateContent = function()
	{
		if (this.calendar.search && this.calendar.search.isInvitationPresetEnabled())
		{
			return `<div class="calendar-list-view-empty">
				<div class="calendar-list-view-empty-no-invitations-icon"></div>
				<div class="calendar-list-view-empty-title">${BX.message('EC_CALENDAR_NO_INVITATIONS_TITLE')}</div>
			</div>`;
		}

		return `<div class="calendar-list-view-empty">
			<div class="calendar-list-view-empty-icon"></div>
			<div class="calendar-list-view-empty-title">${BX.message('EC_CALENDAR_NO_EVENTS_TITLE')}</div>
			<div class="calendar-list-view-empty-text">${BX.message('EC_CALENDAR_NO_EVENTS_TEXT')}</div>
			<div class="calendar-list-view-empty-list">
				<div class="calendar-list-view-empty-item">
					<span class="calendar-list-view-empty-list-icon --google"></span>
					<span class="calendar-list-view-empty-list-text">${BX.message('EC_CALENDAR_GOOGLE_CALENDAR')}</span>
				</div>
				<div class="calendar-list-view-empty-item">
					<span class="calendar-list-view-empty-list-icon --apple"></span>
					<span class="calendar-list-view-empty-list-text">${BX.message('EC_CALENDAR_APPLE_CALENDAR')}</span>
				</div>
				<div class="calendar-list-view-empty-item">
					<span class="calendar-list-view-empty-list-icon --office"></span>
					<span class="calendar-list-view-empty-list-text">${BX.message('EC_CALENDAR_OFFICE365_CALENDAR')}</span>
				</div>
			</div>
		</div>`;
	};

	ListView.prototype.getEmptyStateButton = function()
	{
		if (this.calendar.util.readOnlyMode())
		{
			return document.createElement('emptyNode');
		}

		if (this.calendar.search && this.calendar.search.isInvitationPresetEnabled())
		{
			return document.createElement('emptyNode');
		}

		if (this.calendar.syncInterface)
		{
			return new BX.UI.Button({
				text: BX.message('STATUS_BUTTON_SYNC_CALENDAR_NEW'),
				round: true,
				color: BX.UI.Button.Color.LIGHT_BORDER,
				events : {
					click : () => {
						this.calendar.syncInterface.syncButton.handleClick();
					},
				},
			}).getContainer();
		}

		return document.createElement('emptyNode');
	};

	ListView.prototype.applyFilterMode = function()
	{
		this.filterMode = true;

		BX.addClass(this.streamContentWrap, 'calendar-timeline-stream-search-result');
		this.showSearchHeader();

		if (this.loaderCircle)
		{
			this.loaderCircle.show();
		}
	};

	ListView.prototype.resetFilterMode = function(params)
	{
		if (!this.filterMode)
		{
			return;
		}
		
		this.filterMode = false;
		this.resultEntries = [];

		this.showCalendarHeader();
		this.streamContentWrap.style.display = '';

		if (!params || params.resetSearchFilter !== false)
		{
			this.calendar.search.resetFilter();
		}

		if (BX.Type.isUndefined(params.viewName) && this.calendar.viewNameBeforeFilter !== this.name)
		{
			this.calendar.setView(this.calendar.viewNameBeforeFilter, {
				animation: true,
				date: this.calendar.dateBeforeFilter,
			});
			return;
		}

		if (BX.Type.isUndefined(params.viewName) || params.viewName === this.name)
		{
			BX.removeClass(this.streamContentWrap, 'calendar-timeline-stream-search-result');
		}

		if (this.isActive())
		{
			this.displayEntries({focusDate: this.focusDate});
		}
	};

	ListView.prototype.showSearchHeader = function()
	{
		this.calendar.viewTitleContainer.style.display = 'none';
		this.calendar.navigationWrap.style.display = 'none';

		if (this.searchHeader)
		{
			BX.remove(this.searchHeader);
		}

		this.searchHeader = this.calendar.topBlock.appendChild(BX.create('DIV', {
			props: {
				className: 'calendar-search-head',
			},
			html: `<div class="calendar-search-name">${this.getSearchHeaderTitle()}</div>`,
		}));

		this.calendar.viewSelector.hide();
	};

	ListView.prototype.showCalendarHeader = function()
	{
		this.calendar.viewTitleContainer.style.display = '';
		this.calendar.navigationWrap.style.display = '';

		if (this.searchHeader)
		{
			BX.remove(this.searchHeader);
		}

		this.calendar.viewSelector.show();

		this.setTitle();
	};

	ListView.prototype.getSearchHeaderTitle = function()
	{
		if (this.calendar.search && this.calendar.search.isInvitationPresetEnabled())
		{
			return BX.message('EC_COUNTER_INVITATION');
		}

		const query = this.calendar.search?.getSearchQuery();
		if (BX.Type.isStringFilled(query))
		{
			return BX.message('EC_SEARCH_RESULT_BY_QUERY').replace('#QUERY#', query);
		}

		return BX.message('EC_SEARCH_RESULT');
	};

	ListView.prototype.handleEntryClick = function(params)
	{
		if (this.filterMode && !BX.Type.isUndefined(this.resultEntriesIndex[params.uid]))
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
			color: decision === 'Y' ? BX.UI.Button.Color.PRIMARY : BX.UI.Button.Color.LIGHT,
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
		BX.addCustomEvent(window, 'onBXEventCalendarInit', function()
		{
			window.BXEventCalendar.CalendarListView = ListView;
		});
	}
})(window);
