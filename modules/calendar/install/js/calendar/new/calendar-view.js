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
		this.collapseOffHours = this.util.getUserOption('collapseOffHours', 'Y') === 'Y';
		this.hotkey = null;

		this.entries = [];
		this.entriesIndex = {};
		BX.addCustomEvent(this.calendar, 'viewOnClick', BX.proxy(this.handleClick, this));
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
			this.setTitle('');
		},

		redraw: function()
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
				BX.Calendar.EntryManager.openCompactEditForm({
					type: this.calendar.util.type,
					ownerId: this.calendar.util.ownerId,
					sections: this.calendar.sectionManager.getSectionListForEdit(),
					trackingUserList: this.calendar.util.getSuperposedTrackedUsers(),
					entryTime: params.entryTime || null,
					closeCallback: params.closeCallback,
					userSettings: this.calendar.util.config.userSettings,
					locationFeatureEnabled: this.calendar.util.isRichLocationEnabled(),
					locationList: BX.Calendar.Controls.Location.getLocationList(),
					iblockMeetingRoomList: this.calendar.util.getMeetingRoomList(),
				});
			}
		},

		showCompactViewForm : function(params)
		{
			BX.Calendar.EntryManager.openCompactViewForm({
				entry: params.entry,
				type: this.calendar.util.type,
				ownerId: this.calendar.util.ownerId,
				sections: this.calendar.sectionManager.getSections(),
				trackingUserList: this.calendar.util.getSuperposedTrackedUsers(),
				userSettings: this.calendar.util.config.userSettings,
				locationFeatureEnabled: this.calendar.util.isRichLocationEnabled(),
				locationList: BX.Calendar.Controls.Location.getLocationList(),
				iblockMeetingRoomList: this.calendar.util.getMeetingRoomList()
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

			BX.Calendar.EntryManager.openEditSlider({
				entry: params.entry,
				type: this.calendar.util.type,
				ownerId: this.calendar.util.ownerId,
				userId: parseInt(this.calendar.currentUser.id)
			});
		},

		handleEntryClick: function(params)
		{
			params.entry = params.entry || this.getEntryById(params.uid);

			if (this.calendar.isExternalMode())
			{
				return this.calendar.triggerEvent('entryClick', params);
			}

			// if (params.entry.isSelected())
			// {
				if (params.entry.isTask())
				{
					BX.SidePanel.Instance.open(this.calendar.util.getViewTaskPath(params.entry.id), {loader: "task-new-loader"});
				}
				else
				{
					this.showCompactViewForm(params);

					// this.showViewSlider({
					// 	entry: params.entry
					// });
				}
			//}

			//this.selectEntry(params.entry);
			// if (this.name === 'week' || this.name === 'month')
			// {
			// 	this.showCompactViewForm(params);
			// }
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
			if (uniqueId && this.entriesIndex[uniqueId] !== undefined && this.entries[this.entriesIndex[uniqueId]])
				return this.entries[this.entriesIndex[uniqueId]];
			return false;
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
		}
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