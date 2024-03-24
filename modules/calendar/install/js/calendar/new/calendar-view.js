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
		this.isCollapsedOffHours = this.util.getUserOption('collapseOffHours', 'Y') === 'Y';
		this.hotkey = null;

		this.entries = [];
		this.entriesIndex = {};
		BX.addCustomEvent(this.calendar, 'viewOnClick', BX.proxy(this.handleClick, this));

		this.deletedEntriesIds = [];
		BX.addCustomEvent('BX.Calendar.Entry:beforeDelete', (data) => {
			if (!data.recursionMode || (data.recursionMode === 'this' && data.entryData))
			{
				const uid = this.calendar.entryController.getUniqueId(data.entryData);
				this.deletedEntriesIds.push(uid);
			}
		});
		BX.addCustomEvent('BX.Calendar.Entry:cancelDelete', (data) => {
			const uid = this.calendar.entryController.getUniqueId(data.entryData);
			const index = this.deletedEntriesIds.indexOf(uid);
			if (index !== -1)
			{
				this.deletedEntriesIds.splice(index, 1);
			}
		});
	}

	View.prototype = {
		getUndeletedEntries: function()
		{
			return this.entries.filter(entry => {
				return !this.deletedEntriesIds.find(uid => uid === entry.uid);
			});
		},

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
			this.setTitle('');
			this.highlightAll();
		},

		isFirstVisibleRecursiveEntry: function(entry)
		{
			if (!entry.isRecursive())
			{
				return true;
			}

			const recursiveEntries = this.entries.filter((e) => {
				return e.uid.split('|')[0] === entry.parentId && !e.isHiddenInPopup
			}).sort((e1, e2) => {
				return (e1.from.getTime() > e2.from.getTime()) ? 1 : (e1.from.getTime() < e2.from.getTime()) ? -1 : 0;
			});

			if (recursiveEntries.length === 0)
			{
				return true;
			}

			return recursiveEntries[0] && recursiveEntries[0]?.uid === entry?.uid;
		},

		highlightAll: function()
		{
			BX.addClass(this.viewCont, 'calendar-grid-highlight-all');
			clearTimeout(this.hightlightAll);
			this.hightlightAll = setTimeout(() => {
				BX.removeClass(this.viewCont, 'calendar-grid-highlight-all');
			}, 6000);
		},

		redraw: function()
		{
			this.displayEntries();
		},

		reload: function()
		{
			this.loadEntries().then(entries => {
				this.entries = entries;
				this.redraw();
			});
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
					{
						callback();
					}
				}
			}).animate();
		},

		getArrow: function(type, color, doFill)
		{
			const fill = doFill ? color : '#ffffff00';
			const arrowNodeContainer = document.createElement('div');

			if (type === 'left')
			{
				arrowNodeContainer.innerHTML = `
					<svg class="calendar-event-angle-start-yesterday" viewBox="0 0 6 18" version="1.1" xmlns="http://www.w3.org/2000/svg" style="stroke: ${color}; fill: ${fill};">
						<path stroke-width="1" d="M14.5,17.5 L14.5,0.5 L2.00049088,0.5 C1.78697323,0.5 1.57591593,0.545584 1.38143042,0.633704227 C0.626846099,0.975601882 0.292297457,1.86447615 0.634195112,2.61906047 L3.05787308,7.96823256 C3.35499359,8.62399158 3.35499359,9.37600842 3.05787308,10.0317674 L0.634195112,15.3809395 C0.546074885,15.575425 0.500490885,15.7864823 0.500490885,16 C0.500490885,16.8284271 1.17206376,17.5 2.00049088,17.5 L14.5,17.5 Z"/>
					</svg>
				`;
			}

			if (type === 'right')
			{
				arrowNodeContainer.innerHTML = `
					<svg class="calendar-event-angle-finish-tomorrow" viewBox="8 0 15 18" version="1.1" xmlns="http://www.w3.org/2000/svg" style="stroke: ${color}; fill: ${fill};">
						<path stroke-width="1" d="M0.5,0.5 L0.5,17.5 L8.7031205,17.5 C9.65559352,17.5 10.5253145,16.9587787 10.9460243,16.1042565 L13.8991717,10.1059895 C14.2418971,9.40986472 14.2419701,8.59406382 13.8993692,7.89787777 L10.9458495,1.89614482 C10.5252214,1.04140271 9.65538246,0.5 8.70274816,0.5 L0.5,0.5 Z"></path>
					</svg>
				`;
			}

			return arrowNodeContainer.firstElementChild;
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

		showCompactEditForm: function(params)
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
			}
			else
			{
				if (this.calendar.util.type === 'location')
				{
					BX.Calendar.EntryManager.openCompactEditForm({
						type: 'user',
						isLocationCalendar: true,
						locationAccess: this.calendar.util.config.locationAccess,
						dayOfWeekMonthFormat: this.calendar.util.config.dayOfWeekMonthFormat,
						ownerId: this.calendar.util.userId,
						sections: this.calendar.roomsManager.getSections(),
						roomsManager: this.calendar.roomsManager,
						trackingUserList: this.calendar.util.getSuperposedTrackedUsers(),
						entryTime: params.entryTime || null,
						closeCallback: params.closeCallback,
						userSettings: this.calendar.util.config.userSettings,
						locationFeatureEnabled: this.calendar.util.isRichLocationEnabled(),
						locationList: BX.Calendar.Controls.Location.getLocationList(),
						iblockMeetingRoomList: this.calendar.util.getMeetingRoomList(),
						plannerFeatureEnabled: this.calendar.util.config.plannerFeatureEnabled
					});
				}
				else
				{
					BX.Calendar.EntryManager.openCompactEditForm({
						type: this.calendar.util.type,
						isLocationCalendar: false,
						locationAccess: this.calendar.util.config.locationAccess,
						dayOfWeekMonthFormat: this.calendar.util.config.dayOfWeekMonthFormat,
						ownerId: this.calendar.util.ownerId,
						sections: this.calendar.sectionManager.getSections(),
						trackingUserList: this.calendar.util.getSuperposedTrackedUsers(),
						entryTime: params.entryTime || null,
						closeCallback: params.closeCallback,
						userSettings: this.calendar.util.config.userSettings,
						locationFeatureEnabled: this.calendar.util.isRichLocationEnabled(),
						locationList: BX.Calendar.Controls.Location.getLocationList(),
						iblockMeetingRoomList: this.calendar.util.getMeetingRoomList(),
						plannerFeatureEnabled: this.calendar.util.config.plannerFeatureEnabled
					});
				}
			}
		},

		showCompactViewForm : function(params)
		{
			BX.Calendar.EntryManager.openCompactViewForm({
				entry: params.entry,
				calendarContext: BX.Calendar.Util.getCalendarContext(),
				type: this.calendar.util.type,
				isLocationCalendar: this.calendar.util.type === 'location',
				locationAccess: this.calendar.util.config.locationAccess,
				dayOfWeekMonthFormat: this.calendar.util.config.dayOfWeekMonthFormat,
				ownerId: this.calendar.util.ownerId,
				sections: this.calendar.util.type === 'location'
					? this.calendar.roomsManager.getSections()
					: this.calendar.sectionManager.getSections(),
				trackingUserList: this.calendar.util.getSuperposedTrackedUsers(),
				userSettings: this.calendar.util.config.userSettings,
				locationFeatureEnabled: this.calendar.util.isRichLocationEnabled(),
				locationList: BX.Calendar.Controls.Location.getLocationList(),
				iblockMeetingRoomList: this.calendar.util.getMeetingRoomList(),
				plannerFeatureEnabled: this.calendar.util.config.plannerFeatureEnabled
			});
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
			if (this.calendar.util.type === 'location')
			{
				BX.Calendar.EntryManager.openEditSlider({
					entry: params.entry,
					type: 'user',
					isLocationCalendar: true,
					locationAccess: this.calendar.util.config.locationAccess,
					dayOfWeekMonthFormat: this.calendar.util.config.dayOfWeekMonthFormat,
					roomsManager: this.calendar.roomsManager,
					ownerId: this.calendar.util.ownerId,
					userId: parseInt(this.calendar.currentUser.id)
				});
			}
			else
			{
				BX.Calendar.EntryManager.openEditSlider({
					calendarContext: this.util.calendarContext,
					entry: params.entry,
					type: this.calendar.util.type,
					isLocationCalendar: false,
					locationAccess: this.calendar.util.config.locationAccess,
					dayOfWeekMonthFormat: this.calendar.util.config.dayOfWeekMonthFormat,
					ownerId: this.calendar.util.ownerId,
					userId: parseInt(this.calendar.currentUser.id)
				});
			}
		},

		handleEntryClick: function(params)
		{
			params.entry = params.entry || this.getEntryById(params.uid);

			if (params.entry)
			{
				if (this.calendar.isExternalMode())
				{
					return this.calendar.triggerEvent('entryClick', params);
				}
				if (params.entry.isTask())
				{
					const viewTaskPath = BX.Uri.addParam(this.calendar.util.getViewTaskPath(params.entry.id), {
						ta_sec: 'calendar',
						ta_el: 'title_click',
					});

					BX.SidePanel.Instance.open(viewTaskPath, {loader: "task-new-loader"});
				}
				else if (!this.calendar.dragDrop.isDragging)
				{
					this.showCompactViewForm(params);
				}
			}
		},

		showViewSlider: function(params)
		{
			if (!this.calendar.util.useViewSlider())
			{
				return;
			}

			if (params.entry && params.entry.id)
			{
				BX.Calendar.EntryManager.openViewSlider(params.entry.id,
					{
						from: params.entry.from,
						timezoneOffset: params.entry && params.entry.data ? params.entry.data.TZ_OFFSET_FROM : null
					}
				);
			}

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
			const entry = this.entries.find(entry => entry.uid === uniqueId);
			if (!entry)
			{
				return false;
			}

			return entry;
		},

		getRealEntry: function(entry)
		{
			if (!entry)
			{
				return null;
			}
			for (const realEntry of this.entries)
			{
				if (realEntry.uid === entry.uid)
				{
					return realEntry;
				}
			}
			for (const realEntry of this.entries)
			{
				if (realEntry.data.RELATIONS)
				{
					const id = entry.uid.split("|")[0];
					const date = entry.uid.split("|")[1];
					if (realEntry.data.RELATIONS.COMMENT_XML_ID === 'EVENT_' + id + '_' + date)
					{
						return realEntry;
					}
				}
			}
			for (const realEntry of this.entries)
			{
				if (realEntry.data.RELATIONS)
				{
					const id = entry.uid.split("|")[0];
					if (realEntry.data.RELATIONS.COMMENT_XML_ID === 'EVENT_' + id)
					{
						return realEntry;
					}
				}
			}
			return null;
		},

		selectEntryPart: function(params, color)
		{
			if (params.wrapNode)
			{
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
				entrieList = params.entrieList || params.day.entries.list,
				innerCont,
				popup;

			innerCont = BX.create('DIV', {
				props: {className: 'calendar-all-events-popup calendar-custom-scroll'},
				events: {click : BX.proxy(this.calendar.handleViewsClick, this.calendar)}
			});

			entrieList.sort(this.calendar.entryController.sort);

			var taskWrap, eventsWrap;
			let tasksTitle, eventsTitle;
			entrieList.forEach(function(entryItem)
			{
				if (entryItem.entry)
				{
					if (entryItem.entry.isTask())
					{
						if (!taskWrap)
						{
							tasksTitle = BX.create('DIV', {
								props: { className: 'calendar-event-title' },
								text: BX.message('EC_ENTRIES_TASKS')
							});
							innerCont.appendChild(tasksTitle);
							taskWrap = innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block'}}));
						}

						this.displayEntryPiece({
							dayInCell: params.day.date,
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
							const time = params.day.date.getTime();
							eventsTitle = BX.create('DIV', {
								props: { className: 'calendar-event-title calendar-event-title-button' },
								attrs: { 'data-bx-calendar-date': time },
								text: BX.message('EC_ENTRIES_EVENTS') + ', ' + BX.date.format('d F', time / 1000)
							});
							innerCont.appendChild(eventsTitle);
							eventsWrap = innerCont.appendChild(BX.create('DIV', {props: {className: 'calendar-event-block'}}));
						}

						this.displayEntryPiece({
							dayInCell: params.day.date,
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
					offsetLeft: 0,
					lightShadow: true,
					content: innerCont
				});

			popup.setAngle({offset: 2 * this.getDayWidth() / 3});
			popup.show(true);
			this.allEventsPopup = popup;

			if (eventsTitle)
			{
				eventsTitle.addEventListener('click', () => {
					popup.destroy();
				});
			}

			BX.addCustomEvent(popup, 'onPopupClose', function()
			{
				popup.destroy();
			});
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
		},

		getAdjustedDate: function(date, viewRange)
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
		},

		getViewRange: function()
		{
			var
				viewRangeDate = this.calendar.getViewRangeDate(),
				endDate = new Date(viewRangeDate.getTime());
			return {start: viewRangeDate, end: endDate};
		},

		getHotkey: function()
		{
			return this.hotkey || null;
		},
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

	if (window.BXEventCalendar)
	{
		window.BXEventCalendar.CalendarView = View;
		window.BXEventCalendar.CalendarYearView = YearView;
	}
	else
	{
		BX.addCustomEvent(window, "onBXEventCalendarInit", function()
		{
			window.BXEventCalendar.CalendarView = View;
			window.BXEventCalendar.CalendarYearView = YearView;
		});
	}

	window.BXEventCalendarView = View;
})(window);